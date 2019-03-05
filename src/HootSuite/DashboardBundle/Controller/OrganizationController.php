<?php

namespace HootSuite\DashboardBundle\Controller;

use HootSuite\BackofficeBundle\Entity\Organization;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class OrganizationController extends Controller
{
   public function createOrganizationAction(Request $request)
   {
       $file = $request->files->get('file');
       $name = $request->request->get('name');
       $em = $this->getDoctrine()->getManager();
       $org = new Organization();
       $response = null;
       $org->setName($name);
       $user = $this->get('session')->get('user');
       $org->setCreatedBy($em->getReference('BackofficeBundle:Usuario',$user));
       $org->setCreatedAt(new \DateTime('NOW'));
       $em->persist($org);
       $em->flush();
       try{
           $em->persist($org);
           $em->flush();
           if($file)
           {
               $dir = $this->get('kernel')->getRootDir().'/../web/uploads/Organizations';
               try{
                   $ext = $file->getClientOriginalExtension();
                   $file->move($dir,$name.".$ext");
               }catch(\Exception $e){}

           }
           $response = new JsonResponse(array('success'=>true,'data'=>array('id'=>$org->getId(),'name'=>$org->getName())));
       }catch (\Exception $e){
           $response = new JsonResponse(array('success'=>false));
       }
       return $response;
   }

}
