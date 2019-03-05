<?php

namespace HootSuite\ApiBundle\Controller;

use Facebook\Exceptions\FacebookSDKException;
use HootSuite\BackofficeBundle\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MessageController extends Controller
{
    private function persistMessage($data,$state=0, $show=false){
        $em         = $this->getDoctrine()->getManager();
        $message = new Message();
        $message->setText($data['text']);
        $message->setAvatar('');
        $message->setState($state);
        if($data['programmed']!=null && \DateTime::createFromFormat("Y/m/d H:i:s",$data['programmed']))
            $message->setProgramed(\DateTime::createFromFormat("Y/m/d H:i:s",$data['programmed']));
        $message->setCreatedAt(new \DateTime());
        if(isset($data['files']))
        {
            $archives = null;
            foreach($data['files'] as $file)
            {
                $name = uniqid('cq',true).'.'.$file->getClientOriginalExtension();
                $file->move($_SERVER['DOCUMENT_ROOT'].'/uploads',$name);
                $archives[] = $name;
            }
            $message->setAttachment(serialize($archives));
        }
        $em->persist($message);
        $em->flush();
        foreach( $data['profiles'] as $p ){
            $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($p);
            $profile->addMessage($message);
            $em->persist($profile);
            $em->flush();
        }
    }
    public function sendAction(Request $request)
    {
        $text       = $request->get('message');
        $profiles   = $request->get('profiles');
        $files = $request->files->get('file');
        $data = array('text'=>$text,'profiles'=>$profiles,'files'=>$files);
        $em         = $this->getDoctrine()->getManager();
        foreach( $profiles as $p ){
            $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($p);
            switch ($profile->getSocialNetwork()->getUniquename()) {
                case 'TWITTER':
                    $api    = $this->get('twitter');
                    $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
                    $result   = $api->post('statuses/update', array("status" => ($text)));
                    if( isset( $result->id_str ) && $result->id_str ){
                        $object = array("success"   => true, "msg" => "Mensaje enviado.");
                        $this->persistMessage($data);
                    }
                    else{
                        $object = array("success"   => false, "msg" => "El mensaje no ha podido ser enviado.");
                    }
                    break;
                case 'FACEBOOK':
                    $fb = $this->get('facebookv2');
                    $fb->setDefaultAccessToken($profile->getToken());
                    $regex = '/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';
                    preg_match_all($regex, $text, $matches);
                    $file = null;
                    if($files!=null)
                    {
                        foreach($files as $_file)
                        {
                            $file = $_file;
                            $file->move($_SERVER['DOCUMENT_ROOT'].'/uploads',$file->getClientOriginalName());
                        }
                    }
                    $urls = $matches[0];
                    $picture = $file!=null ? 'http://'.$_SERVER['HTTP_HOST'].'/uploads/'.$file->getClientOriginalName():'';
                    $linkData = [
                        'link' => $urls,
                        'message' => $text,
                        'picture' =>$picture
                    ];
                    try {
                        $response = $fb->post('/me/feed', $linkData,$profile->getToken());
                    }catch(FacebookResponseException $e) {
                        $object = array("success"   => false, "msg" => $e->getMessage());
                        return new Response(json_encode($object));
                    } catch(FacebookSDKException $e) {
                        $object = array("success"   => false, "msg" => $e->getMessage());
                        return new Response(json_encode($object));
                    }
                    $object = array("success"   => true, "msg" => "Mensaje enviado.");
                    $this->persistMessage($data);
                    break;
            }
        }
        return new Response(json_encode($object));
    }

    public function draftAction(Request $request)
    {
        $message       = $request->get('message');
        $profiles   = $request->get('profiles');
        $files = $request->files->get('file');
        $data = array();
        if(isset($message['message']))
        {
            $data['text'] = $message['message'];
        }
        if(isset($message['programmed']))
        {
            $data['programmed'] = $message['programmed'];
        }
        if(isset($message['location']))
        {
            $data['location'] = $message['location'];
        }
        $data['profiles'] = $profiles;
        $data['files'] = $files;
        //$data = array('text'=>$message['message'],'programmed'=>$message['programmed'],'location'=>$message['location'],'profiles'=>$profiles,'files'=>$files);
        $result = $this->persistMessage($data,1,true);
        $object = array('success'=>true,'msg'=>'Guardado como borrador','data'=>$result);
        return new Response(json_encode($object));
    }

    public function scheduleAction(Request $request)
    {
        $message       = $request->get('message');
        $profiles   = $request->get('profiles');
        $files = $request->files->get('file');
        $data = array('text'=>$message['message'],'programmed'=>$message['programmed'],'location'=>$message['location'],'profiles'=>$profiles,'files'=>$files);
        $result = $this->persistMessage($data,2,true);
        return new Response(json_encode($result));
    }
    
    public function delAction(Request $request){
        $profile_id = $request->get('p_id');
        $id     = $request->get('id');
        $type   = $request->get('tp');
        $em     = $this->getDoctrine()->getManager();
        $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $object = null;
        switch ($profile->getSocialNetwork()->getUniquename()) {
            case 'TWITTER':
                $api    = $this->get('twitter');
                $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
                if( $type == 'MD' ){
                    $result = $api->post('direct_messages/destroy', array('id' => $id));
                }
                else{
                    $result = $api->post('statuses/destroy/'.$id, array('id' => $id));
                }
                if( isset( $result->id_str ) && $result->id_str == $id ){
                    $object = array("success"   => true);
                }
                else{
                    $object = array("success"   => false, "msg" => "El mensaje no ha podido ser eliminado.");
                }
                break;
            case 'FACEBOOK':
                break;
        }
        return new Response(json_encode($object));
    }

    public function shortenAction(Request $request)
    {
        $text = $request->get('url');
        $response = null;
        $service = $request->get('service');
        $object = null;
        switch ($service){
            case 'adf.ly':
                $response = $this->get('adfly')->shorten($text);
                if($response->data)
                {
                    $url = $response->data[0]->short_url;
                    $object = array("success"   => true,"url"=>$url, "msg" => "Url acortada.");
                }
                else{
                    $object = array("success"   => false, "msg" => $response->errors[0]->msg);
                }
                break;
            case 'bit.ly':
                $response = $this->get('bitly')->shorten($text);
                if($response->status_code===200) {

                    $url = $response->data->url;
                    $object = array("success"   => true,"url"=>$url, "msg" => "Url acortada.");
                }
                else{
                    $object = array("success"   => false, "msg" => $response->status_txt);
                }
                break;
            case 'tinyurl.com':
                if (!filter_var($text, FILTER_VALIDATE_URL) === false) {
                    $response = $this->get('tiny')->create($text);
                    $object = array("success"   => true,"url"=>$response, "msg" => "Url acortada.");
                } else {
                    $object = array("success"   => false, "msg" => 'Url inválida.');
                }

                break;
            case 'v.gd':
                $response = $this->get('vgd')->shorten($text);
                if($response['shortURL'])
                {
                    $object = array("success"   => true,"url"=>$response['shortURL'], "msg" => "Url acortada.");
                }
                else{
                    $object = array("success"   => false, "msg" => $response);
                }
                break;
            Default: break;
        }
        return new Response(json_encode($object));
    }

    public function countriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('BackofficeBundle:Country')->fetchAll();
        $object = array('success'=>true,'countries'=>$countries);
        return new Response(json_encode($object));
    }
    private function processMessage($message){
        $profiles = $message->getProfilesUsuario();
        $networks = array();
        $user = array();
        foreach ($profiles as $profile)
        {
            $networks[] =array(
                "red"   => $profile->getSocialNetwork()->getName(),
                "name"  => $profile->getUsername(),
                "avatar"=> $profile->getAvatar()
            );
            $user =array(
                "name"  => $profile->getUsuario()->getName(),
                "avatar"=> $profile->getUsuario()->getImage()
            );
        }
        $object = array("draftid" => $message->getId(),
            "message"=>$message->getText(),
            "createdAt"=>$message->getCreatedAt(),
            "programmed"=>$message->getProgramed(),
            "networks" => $networks,'users'=>$user);
        return $object;
    }
    public function loadDraftsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $drafts = $em->getRepository('BackofficeBundle:Message')->findBy(array('state' => 1));
        $objects = array();
        foreach ($drafts as $draft)
        {
            $dft = $this->processMessage($draft);
            $objects[] = $dft;
        }
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $objects
        )));
    }
    public function loadScheduledAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('BackofficeBundle:Message')->findBy(array('state' => 2));
        $programed = array();
        $scheduled = array();
        foreach ($messages as $message)
        {
            $k = array_search($message->getProgramed(), $programed,false);
            if($k)
            {
                $msg = $this->processMessage($message);
                array_push($scheduled[$k]['messages'], $msg);
            }
            else{
                $programed[] = $message->getProgramed();
                $msg = $this->processMessage($message);
                $scheduled[]=array('date'=>$message->getProgramed(),'messages'=>array($msg));
            }
        }
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $scheduled
        )));
    }
}