<?php

namespace HootSuite\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class RedesController extends Controller
{
    public function loadAction()
    {       
        $em = $this->getDoctrine()->getManager();
        $redes = $em->getRepository('BackofficeBundle:SocialNetwork')->findWidthColumns();
        $objects = array();
        foreach( $redes as $red ){  
            $columns = $red->getColumns();
            $cols = array();
            foreach( $columns as $col ){
                if( $col->getVisible() ){
                $cols[] = array(
                    "id"        => $col->getId(), 
                    "name"      => $col->getName(), 
                    "glyphicon" => $col->getGlyphicon(), 
                    "type"      => $col->getType(),
                    "group"     => strstr($col->getType(), 'GROUP_') ? 1 : 0
                    );
                }
            }
            $objects[] = array(
                'id'        => $red->getId(),
                'name'      => $red->getName(),
                'glyphicon' => $red->getGlyphicon(),
                'glyphicon1'=> $red->getGlyphicon1(),
                'search'    => $red->getSearch(),
                'uniquename'=> $red->getUniquename(),
                'keyword'   => $red->getKeyword(),
                'list'      => ($red->getUniquename() == 'TWITTER' ? 1 : 0),
                'columns'   => $cols
            );
        }
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $objects
        )));
    }
}
