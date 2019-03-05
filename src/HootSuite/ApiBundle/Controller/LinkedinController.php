<?php

namespace HootSuite\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class LinkedinController extends Controller
{
    
    public function addCommentAction(Request $request)
    {   
        $profile_id = $request->get('p_id');
        $id         = $request->get("id");   
        $comment    = $request->get("comment");
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api = $this->get('linkedin');
        $api->setAccessToken($profile->getToken());
        $response = $api->api("$id/comments", array('message' => $comment), "POST");
        if( isset($response['id']) && $response['id']){
            $comment = $api->api("/".$response['id']);
            $response =  new Response(json_encode(array(
                "success"   => true, 
                "object"    => $comment
            )));
        }
        else{
            $response = new Response('{"success" : false, "msg" : "El comentario no ha podido ser publicado."}');
        }
        return $response;
    }
    
    public function updatesAction(Request $request)
    {   
        $profile_id = $request->get('p_id');
        $id         = $request->get("id");   
        $type       = $request->get("ty");
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api = $this->get('linkedin');
        $api->setAccessToken($profile->getToken());
        $url_api = "/v1/people/id=$id/network/updates";
        if( $type == 'CMP' ){
            $url_api = "/v1/companies/id=$id/network/updates";
        }
        $parameters['scope'] = 'self';
        $response = $api->api($url_api, $parameters);
        $result = isset($response['values']) ? $response['values'] : array();
        return new Response(json_encode(array(
            "success"   => true, 
            "object"    => $result,
            "html"      => $this->renderView('ApiBundle:Usuarios:usr_linkedin_updates.html.twig')
        )));
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
        $api = $this->get('linkedin');
        $api->setAccessToken($profile->getToken());
        $response = $api->api("$id/likes", "", $method);
       
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
//        $profile_id = $request->get('p_id');
        $id         = $request->get("g_id");
        $em = $this->getDoctrine()->getManager();
//        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $id_usuario = $this->get("session")->get('registered') ? $this->get("session")->get('registered') : $this->get('session')->get('user');
        if( $id_usuario ){
            $api = $this->get('linkedin');
            $accessToken = $api->getAccessToken();
            $group = $api->api("$id");
            if( isset($group['id']) && $group['id'] == $id ){
                $profile = new ProfilesUsuario();
                $profile->setToken($accessToken);
                $profile->setTokenSecret($accessToken);

                $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find(4); // FACEBOOK
                $profile->setSocialNetwork($red);
                $user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
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
                    'avatar'        => $profile->getAvatar()
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
        return new Response('{"success" : false}');
    }

}
