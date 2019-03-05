<?php

namespace HootSuite\ApiBundle\Controller;
use Facebook\Facebook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use HootSuite\BackofficeBundle\Entity\Groups;
use HootSuite\BackofficeBundle\Entity\ProfilesUsuario;

class FacebookController extends Controller
{
    
    public function addCommentAction(Request $request)
    {   
        $profile_id = $request->get('p_id');
        $id         = $request->get("id");   
        $comment    = $request->get("comment");
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        /*$fb = new Facebook([
            'app_id' => '494431310714400',
            'app_secret' => '77c12744c08512239c6933b09ea64743',
            'default_graph_version' => 'v2.3',
        ]);*/
        $fb = $this->get('facebookv2');
        $response = $fb->post("$id/comments",array('message' => $comment), $profile->getToken());
        $response = $response->getGraphNode()->asArray();
        //$response = $api->api("$id/comments", "POST", array('message' => $comment));
        if( isset($response['id']) && $response['id']){
            $comment = $fb->get("/".$response['id'],$profile->getToken());

            $response =  new Response(json_encode(array(
                "success"   => true, 
                "object"    => $comment->getGraphNode()->asArray()
            )));
        }
        else{
            $response = new Response('{"success" : false, "msg" : "El comentario no ha podido ser publicado."}');
        }
        return $response;
    }
    
    public function likesAction(Request $request)
    {  
        
        $profile_id = $request->get('p_id');
        $id         = $request->get("id");   
        $on         = $request->get("on");
        if( $on ){
            $method = 'POST';
            $msg = 'El post ha sido agregado a favoritos';
        }
        else{
            $method = 'DELETE';
            $msg = 'El post ha sido eliminado de sus favoritos';
        }
//return new Response('{"success" : true, "msg" : "'.$msg.'"}');         
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        /*$api = $this->get('fos_facebook.api');
        $api->setAccessToken($profile->getToken());*/
        /*$fb = new Facebook([
            'app_id' => '494431310714400',
            'app_secret' => '77c12744c08512239c6933b09ea64743',
            'default_graph_version' => 'v2.3',
        ]);*/
        $fb = $this->get('facebookv2');
        $response = $fb->post("$id/likes",array(), $profile->getToken());
        $response = $response->getGraphNode()->asArray();
       // $response = $api->api("$id/likes", $method);
       
        if( isset($response['success']) && $response['success']){
            $msg = $on ? 'El post ha sido agregado a favoritos' : 'El post ha sido eliminado de sus favoritos';
            $response = new Response('{"success" : true, "msg" : "'.$msg.'"}');
        }
        else{
            $response = new Response('{"success" : false, "msg" : "No se ha podido realizar la operación."}');
        }
        return $response;
    }
    
    public function addGroupAction(Request $request)
    {
        /*$fb = new Facebook([
            'app_id' => '494431310714400',
            'app_secret' => '77c12744c08512239c6933b09ea64743',
            'default_graph_version' => 'v2.3',
        ]);*/
        $fb = $this->get('facebookv2');
//        $profile_id = $request->get('p_id');
        $id         = $request->get("g_id"); 
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findOneBy(array('userid'=>$id));
        if($profile)
        {
            $object = json_encode(array("success" => true, "message" => 'Ya ese grupo ha sido agregado.'));
            $response = new Response();
            $response->setContent($object);
            return $response;
        }
        else
        {
            $id_usuario = $this->get("session")->get('registered') ? $this->get("session")->get('registered') : $this->get('session')->get('user');
            if( $id_usuario ){
                $accessToken = $this->get('session')->get('facebook_accesstoken');
                $user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
                $group = $fb->get("$id",$accessToken);
                $group = $group->getGraphGroup();
                if( isset($group['id']) && $group['id'] == $id ){
                    $profile = new ProfilesUsuario();
                    $profile->setToken($accessToken);
                    $profile->setTokenSecret($accessToken);
                    $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find(2); // FACEBOOK
                    $profile->setSocialNetwork($red);
                    $profile->setUsuario($user);
                    $profile->setCreatedAt(new \DateTime('now'));
                    $profile->setUsername($group['name']);
                    $profile->setUserid($group['id']);
                    $profile->setFullName($group['name']);
                    $profile->setAvatar('https://graph.facebook.com/'.$group['id'].'/picture');
                    $profile->setUrl('http://www.facebook.com/profile.php?id='.$group['id']);
                    $profile->setFavorite(0);
                    $profile->setSelected(0);
                    $em->persist($profile);
                    $em->flush();
                    $new_group = new Groups();
                    $new_group->setNetGroupId($group['id']);
                    $new_group->setNetGroupName($group['name']);
                    $new_group->setProfileUsuario($profile);
                    $em->persist($new_group);
                    $em->flush();

                    $html = $this->renderView('FrontendBundle:Autorize:red_agregada.html.twig', array(
                        'profile' => $profile
                    ));
                    $profile_array = array(
                        'id'            => $profile->getId(),
                        'redid'         => $profile->getSocialNetwork()->getId(),
                        'red'           => $profile->getSocialNetwork()->getName(),
                        'name'          => $profile->getUsername(),
                        'avatar'        => $profile->getAvatar(),
                        'user'        => $profile->getUsuario()->getId()
                    );
                    $object = json_encode(array("success" => true, "html" => $html, "profile" => $profile_array));
                    if( $profile->getUsuario()->getActive() == false ){
                        $profile->getUsuario()->setActive(1);
                        $em->persist($profile->getUsuario());
                        $em->flush($profile->getUsuario());
                    }
                    $response = new Response();
                    $response->setContent($object);
                    return $response;
                }
            }
        }

        return new Response('{"success" : false}');
    }

}
