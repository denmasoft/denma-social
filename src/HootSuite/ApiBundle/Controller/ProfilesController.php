<?php

namespace HootSuite\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProfilesController extends Controller
{
    public function loadAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($this->get('session')->get('user'));
     
        $profiles = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findBy(array('usuario' => $user));
        $objects = array();
        foreach( $profiles as $profile ){
            $group = $profile->getGroup();
            $objects[] = array(
                "id"    => $profile->getId(), 
                "redid" => $profile->getSocialNetwork()->getId(), 
                "red"   => $profile->getSocialNetwork()->getName(), 
                "name"  => $profile->getUsername(), 
                "avatar"=> $profile->getAvatar(),
                "group" => $group ? array(
                    "id"    => $group->getNetGroupId(),
                    "name"  => $group->getNetGroupName()
                ) : "",
            );
        }
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $objects
        )));
    }
    public function removeAction(Request $request){
        $id = $request->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($id);
        $em->remove($profile);
        $em->flush();
        return new JsonResponse(array('success'=>'Red Eliminada.'));
    }
}
