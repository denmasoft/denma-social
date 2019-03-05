<?php

namespace HootSuite\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ModalController extends Controller
{
    public function redesAction($id){
        return $this->render('DashboardBundle:Modals:add_red.html.twig', array('redes' => $this->getRedes(), 'id' => $id));
    }
    
    public function typeColsAction(){
        return $this->render('DashboardBundle:Modals:type_cols.html.twig', array("redes" => $this->getRedes(), "perfiles" => $this->getPerfiles()));
    }
    
    function getRedes(){
        $em = $this->getDoctrine()->getManager();
        $redes = $em->getRepository('BackofficeBundle:SocialNetwork')->findWidthColumns();
        $objects = array();
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($this->get('session')->get('user'));
        $profiles = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findProfilesRedes($user->getId());
        foreach( $redes as $red ){  
            $columns = $red->getColumns();
            $cols = array();
            foreach( $columns as $col ){
                $cols[] = array(
                    "id"        => $col->getId(),
                    "name"      => $col->getName(), 
                    "glyphicon" => $col->getGlyphicon(), 
                    "type"      => $col->getType(),
                    "filter"    => $col->getFilter());
            }
            $objects[] = array(
                'id'        => $red->getId(),
                'name'      => $red->getName(),
                'glyphicon' => $red->getGlyphicon(),
                'glyphicon1'=> $red->getGlyphicon1(),
                'search'    => $red->getSearch(),
                'uniquename'=> $red->getUniquename(),
                'keyword'   => $red->getKeyword(),
                "hasProfile"=> $this->hasProfile($profiles, $red->getId()),
                'list'      => ($red->getUniquename() == 'TWITTER' ? 1 : 0),
                'columns'   => $cols
            );
        }
        return $objects;
    }
    
    public function hasProfile($profiles, $red_id){
        foreach( $profiles as $p ){
            if( $p->getSocialNetwork()->getId() == $red_id ){
                return true;
            }
        }
        return false;
    }
    
    public function getPerfiles(){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($this->get('session')->get('user'));
        $profiles = $user->getProfiles();
        $response = array();
        foreach( $profiles as $profile ){
            $response[] = array(
                    "id"        => $profile->getId(), 
                    "redid"     => $profile->getSocialNetwork()->getId(), 
                    "red"       => $profile->getSocialNetwork()->getName(), 
                    "name"      => $profile->getUsername(), 
                    "imagen"    => $profile->getAvatar()
            );
        }
        return $response;
//        return array(
//                array( "id" => 1, "redid" => 1, "red" => "Twitter", "name" => "ylnunez84", "imagen" => ""),
//                array( "id" => 2, "redid" => 2, "red" => "Facebook", "name" => "Yandy Leon", "imagen" => ""),
//                array( "id" => 3, "redid" => 3, "red" => "Linkedln", "name" => "Yandy Leon Nuñez", "imagen" => ""),
//                array( "id" => 4, "redid" => 4, "red" => "Google Plus", "name" => "Yandy Leon Nuñez", "imagen" => ""),
//                array( "id" => 5, "redid" => 5, "red" => "WordPress", "name" => "Mi Wordpress", "imagen" => ""),
//                array( "id" => 6, "redid" => 1, "red" => "Twitter", "name" => "ydnay84", "imagen" => ""),
//                array( "id" => 6, "redid" => 2, "red" => "Facebook", "name" => "Yaquelin Cintra", "imagen" => "")
//            );
    }
}
