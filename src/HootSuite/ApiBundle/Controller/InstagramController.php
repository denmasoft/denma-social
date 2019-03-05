<?php

namespace HootSuite\ApiBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use HootSuite\BackofficeBundle\Entity\Groups;
use HootSuite\BackofficeBundle\Entity\ProfilesUsuario;

class InstagramController extends Controller
{
    
    public function addCommentAction(Request $request)
    {   
        $profile_id = $request->get('p_id');
        $id         = $request->get("id");   
        $comment    = $request->get("comment");
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);        
        $instagram = $this->get('instagram');
        $response = $instagram->post("$id/comments",array('message' => $comment), $profile->getToken());
        $response = $response->getGraphNode()->asArray();
        //$response = $api->api("$id/comments", "POST", array('message' => $comment));
        if( isset($response['id']) && $response['id']){
            $comment = $instagram->get("/".$response['id'],$profile->getToken());

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
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);        
        $instagram = $this->get('instagram');
        $instagram->setAccessToken($profile->getToken());
        $response = $instagram->likeMedia("$id");
        if( isset($response['success']) && $response['success']){
            $msg = $on ? 'Te gusta' : 'Ya no te gusta';
            $response = new Response('{"success" : true, "msg" : "'.$msg.'"}');
        }
        else{
            $response = new Response('{"success" : false, "msg" : "No se ha podido realizar la operación."}');
        }
        return $response;
    }
    
    public function followsAction(Request $request)
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
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);        
        $instagram = $this->get('instagram');
        $response = $instagram->post("$id/likes",array(), $profile->getToken());
        $response = $response->getGraphNode()->asArray();      
       
        if( isset($response['success']) && $response['success']){
            $msg = $on ? 'El post ha sido agregado a favoritos' : 'El post ha sido eliminado de sus favoritos';
            $response = new Response('{"success" : true, "msg" : "'.$msg.'"}');
        }
        else{
            $response = new Response('{"success" : false, "msg" : "No se ha podido realizar la operación."}');
        }
        return $response;
    }
    
    public function userFeedAction(Request $request)
    {    
        $profile_id = $request->get('p_id');
        $id         = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $instagram = $this->get('instagram');               
        $instagram->setAccessToken($profile->getToken());
        $result = $instagram->getUserMedia($id,30)->data;

        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $result,
            "html"      => $this->renderView('ApiBundle:Usuarios:usr_instagram_feed.html.twig', array("profile_showing" => true))
        )));
    }

    public function searchTermsAction(Request $request)
    {
        $profile_id = $request->get('p_id');
        $term = $request->get('term');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $instagram = $this->get('instagram');
        $instagram->setAccessToken($profile->getToken());
        $result = $instagram->searchTags($term)->data;
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $result,
            "html"      => ""
        )));
    }

    public function searchUsersAction(Request $request)
    {
        $profile_id = $request->get('p_id');
        $terms = $request->get('terms');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $instagram = $this->get('instagram');
        $instagram->setAccessToken($profile->getToken());
        $result = $instagram->searchUser($terms,30)->data;

        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $result,
            "html"      => ""
        )));
    }
}
