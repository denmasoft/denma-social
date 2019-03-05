<?php

namespace HootSuite\DashboardBundle\Controller;

use HootSuite\BackofficeBundle\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class TagController extends Controller
{
    public function loadAction()
    {
        $em = $this->getDoctrine()->getManager();
        $tags = $em->getRepository('BackofficeBundle:Tag')->findAll();
        $objects = array();
        foreach( $tags as $tag ){
            $objects[] = array(
                "id"    => $tag->getId(),
                "name"  => $tag->getName()
            );
        }
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $objects
        )));
    }
    public function removeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $tag = $em->getRepository('BackofficeBundle:Tag')->find($id);

       $response = Response(json_encode(array(
            "success"   => true,
            "object"    =>  array(
                "id"    => $tag->getId(),
                "name"  => $tag->getName()
            )
        )));
        $em->remove($tag);
        $em->persist($tag);$em->flush();
        return $response;
    }
   public function createTagAction(Request $request)
   {
       $name = $request->request->get('name');
       $id = $request->request->get('id');
       $em = $this->getDoctrine()->getManager();
       $tag = null;
       if($id!==null)
       {
        $tag = $em->getRepository('BackofficeBundle:Tag')->find($id);
       }
       else{
           $tag = new Tag();
        }

       $response = null;
       $tag->setName($name);
       $user = $this->get('session')->get('user');
       $tag->setCreatedBy($em->getReference('BackofficeBundle:Usuario',$user));
       $tag->setCreatedAt(new \DateTime('NOW'));
       $em->persist($tag);
       $em->flush();
       try{
           $em->persist($tag);
           $em->flush();
           $response = new JsonResponse(array('success'=>true,'object'=>array(
               "id"    => $tag->getId(),
               "name"  => $tag->getName()
           )));
       }catch (\Exception $e){
           $response = new JsonResponse(array('success'=>false));
       }
       return $response;
   }
}
