<?php

namespace HootSuite\FrontendBundle\Controller;

use HootSuite\BackofficeBundle\Entity\Usuario;
use HootSuite\DashboardBundle\Form\UsuarioType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
class DefaultController extends Controller
{
    public function indexAction()
    {
       /* $entity = new Usuario();
        $form = $this->createForm(new UsuarioType(), $entity);

        return $this->render('FrontendBundle:Seguridad:login.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));*/
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $error = $peticion->attributes->get(
            SecurityContext::AUTHENTICATION_ERROR,
            $sesion->get(SecurityContext::AUTHENTICATION_ERROR)
        );
        return $this->render('FrontendBundle:Seguridad:login.html.twig', array(
            'last_username' => $sesion->get(SecurityContext::LAST_USERNAME),
            'error' => $error
        ));
    }

    public function plansAction()
    {
        return $this->render('FrontendBundle:Default:plans.html.twig');
    }
}
