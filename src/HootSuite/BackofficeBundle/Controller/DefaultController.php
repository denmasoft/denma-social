<?php

namespace HootSuite\BackofficeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BackofficeBundle:Default:index.html.twig', array('name' => $name));
    }
}
