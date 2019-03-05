<?php

namespace HootSuite\FrontendBundle\Controller;
use HootSuite\BackofficeBundle\Entity\Usuario;
use HootSuite\DashboardBundle\Form\UsuarioType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class FrontendController extends Controller
{
    public function indexAction()
    {}

    public function terminosServiciosAction(Request $request)
    {
        $entity = new Usuario();
        $form = $this->createForm(new UsuarioType(), $entity);
        return $this->render('FrontendBundle:Default:terminos.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView()));
    }
    public function privacidadAction(Request $request)
    {
        $entity = new Usuario();
        $form = $this->createForm(new UsuarioType(), $entity);
        return $this->render('FrontendBundle:Default:privacidad.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),));
    }
    public function copyrightAction(Request $request)
    {
        $entity = new Usuario();
        $form = $this->createForm(new UsuarioType(), $entity);
        return $this->render('FrontendBundle:Default:copyright.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),));
    }
}