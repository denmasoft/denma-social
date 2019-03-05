<?php

namespace HootSuite\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use HootSuite\BackofficeBundle\Entity\Tab;
use HootSuite\BackofficeBundle\Entity\TabColumn;
use HootSuite\BackofficeBundle\Entity\Workspace;

class TabsController extends Controller
{
    public function loadAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($this->get('session')->get('user'));
        $workspace = $em->getRepository('BackofficeBundle:Workspace')->findOneBy(array('usuario' => $user));
        if( !$workspace ){
            $tab = new Tab();
            $tab->setActive(1);
            $tab->setCreatedAt();
            $tab->setName("New Tab");
            $tab->setRefreshInterval(2);
            $tab->setVisibleColumns(4);
            $em->persist($tab);
            
            $workspace = new Workspace();
            $workspace->setUsuario($user);
            $workspace->addTab($tab);
            
            $em->persist($workspace);
            $tab->setWorkspace($workspace);
            $em->flush();
        }
        $objects = array();
        foreach( $workspace->getTabs() as $tab ){
            $cols = 0;
            if( $tab->getActive()){
                $cols = array();
                $cols_all = $em->getRepository('BackofficeBundle:TabColumn')->findAllColumns($tab->getId());
                foreach( $cols_all as $col ){
                    $cols[] = array(
                        "id"        => $col->getId(),
                        "name"      => $col->getColumn()->getName(),
                        "social"    => $col->getProfileUsuario()->getSocialNetwork()->getId(),
                        "type"      => $col->getColumn()->getType(),
                        "prof_name" => $col->getProfileUsuario()->getUsername(),
                        "glyphicon" => $col->getProfileUsuario()->getSocialNetwork()->getGlyphicon(),
                        "profile"   => $col->getProfileUsuario()->getId(),
                        "column"    => $col->getColumn()->getId(),
                        "tab"       => $tab->getId(),
                        "terms"     => ""
                    );
                }
            }
            $objects[] = array(
                "id"        => $tab->getId(), 
                "name"      => $tab->getName(), 
                "active"    => $tab->getActive(),
                "interval"  => $tab->getRefreshInterval(),
                "visible"   => $tab->getVisibleColumns(),
                "worksp"    => $tab->getWorkSpace()->getId(),
                "columns"   => $cols
            );
        }
        return new Response(json_encode(array(
            "success" => true,
            "object"  => $objects
        )));
    }
    /*
     * $a = id_tab
     * $b = id_columna
     * $c = id_perfil
     */
    public function addAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($this->get('session')->get('user'));
        $workspace = $em->getRepository('BackofficeBundle:Workspace')->findOneBy(array('usuario' => $user));
        $tab = new Tab();
        $tab->setActive(0);
        $tab->setCreatedAt();
        $tab->setName("New Tab");
        $tab->setRefreshInterval(2);
        $tab->setVisibleColumns(4);
        $tab->setWorkspace($workspace);
        $em->persist($tab);
        $workspace->addTab($tab);
        $em->persist($workspace);
        $em->flush();
        return new Response(json_encode(array("success" => true, "object" => array(
            "id"        => $tab->getId(), 
            "name"      => $tab->getName(), 
            "active"    => 0, 
            "editing"   => 0,
            "interval"  => $tab->getRefreshInterval(),
            "visible"   => $tab->getVisibleColumns(),
            "worksp"    => $workspace->getId(),
            "columns" => array()))
        ));
    }

    public function delAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tab = $em->getRepository('BackofficeBundle:Tab')->find($id);
        $em->remove($tab);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }

    public function renameAction(Request $request)
    {
        $id     = $request->get('id');
        $name   = $request->get('name');
        $em = $this->getDoctrine()->getManager();
        $tab = $em->getRepository('BackofficeBundle:Tab')->find($id);
        $tab->setName($name);
        $em->persist($tab);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }
    
    public function columnsAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $tab = $em->getRepository('BackofficeBundle:Tab')->find($id);
        $objects = array();
        $cols_all = $em->getRepository('BackofficeBundle:TabColumn')->findAllColumns($tab->getId());
        foreach( $cols_all as $col ){
            $objects[] =  array(
                "id"        => $col->getId(),
                "name"      => $col->getName(),
                "social"    => $col->getProfileUsuario()->getSocialNetwork()->getId(),
                "type"      => $col->getColumn()->getType(),
                "prof_name" => $col->getProfileUsuario()->getUsername(),
                "glyphicon" => $col->getProfileUsuario()->getSocialNetwork()->getGlyphicon(),
                "profile"   => $col->getProfileUsuario()->getId(),
                "column"    => $col->getId(),
                "tab"       => $col->getTab()->getId(),
                "terms"     => ""
            );
        }
        return new Response(json_encode(array("success" => true, "object" => $objects)));
    }
    
    public function automaticUpdateAction(Request $request){
        $id     = $request->get('id');
        $mins   = $request->get('value');
        $em     = $this->getDoctrine()->getManager();
        $tab    = $em->getRepository('BackofficeBundle:Tab')->find($id);
        $tab->setRefreshInterval($mins);
        $em->persist($tab);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }
    
    public function activateAction(Request $request){
        $id     = $request->get('id');
        $em     = $this->getDoctrine()->getManager();
        $tab    = $em->getRepository('BackofficeBundle:Tab')->find($id);
        $tabs   = $tab->getWorkspace()->getTabs();
        foreach( $tabs as $t ){
            if( $t->getId() == $id ){
                $t->setActive(true);
            }
            else{
                $t->setActive(false);
            }
            $em->persist($t);
        }
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }
    
    public function visibleAction(Request $request){
        $id     = $request->get('id');
        $value  = $request->get('value');
        $em     = $this->getDoctrine()->getManager();
        $tab    = $em->getRepository('BackofficeBundle:Tab')->find($id);
        $tab->setVisibleColumns($value);
        $em->persist($tab);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }
    
    public function orderAction(Request $request){
        $columns     = $request->get('order');
        $em     = $this->getDoctrine()->getManager();
        foreach( $columns as $ord => $obj){
            $col = $em->getRepository('BackofficeBundle:TabColumn')->find($obj['id']);
            $col->setPosition($ord+1);
            $em->persist($col);
        }
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }
}
