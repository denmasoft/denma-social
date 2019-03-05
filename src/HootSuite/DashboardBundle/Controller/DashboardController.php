<?php

namespace HootSuite\DashboardBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContext;
use HootSuite\BackofficeBundle\Entity\Usuario;
use HootSuite\DashboardBundle\Form\UsuarioType;
use HootSuite\DashboardBundle\Form\UsuarioProfileType;
use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken,
    Symfony\Component\Security\Http\Event\InteractiveLoginEvent,
    Symfony\Component\BrowserKit\Cookie;

class DashboardController extends Controller
{
    public function indexAction()
    {
//        $em = $this->getDoctrine()->getManager();
//        $redes = $em->getRepository('BackofficeBundle:SocialNetwork')->findWidthColumns();
//             
//        $objects = array();
//        foreach( $redes as $red ){  
////    ladybug_dump_die($red->getColumns()); 
//            $objects[] = array(
//                'name'      => $red->getName(),
//                'glyphicon' => $red->getGlyphicon(),
//                'glyphicon1'=> $red->getGlyphicon1(),
//                'search'    => $red->getSearch(),
//                'uniquename'=> $red->getUniquename(),
//                'keyword'   => $red->getKeyword(),
//                'list'      => ($red->getUniquename() == 'TWIITER' ? 1 : 0),
//                'columns'   => $red->getColumns()
//            );
//        }
        return $this->render('DashboardBundle:Default:dashboard.html.twig');
    }
    public function adjustmentAction(Request $request){         
        
        $em = $this->getDoctrine()->getManager();               
        $html = $this->renderView('DashboardBundle:Default:ajustes.html.twig',array());
        $object = array(
            "success" => true, 
            "html"  => $html
        );       
        return new Response(json_encode($object));
    }
}
