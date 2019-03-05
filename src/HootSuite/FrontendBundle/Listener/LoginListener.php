<?php

namespace HootSuite\FrontendBundle\Listener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private $doctrine;
    private $session;
    public function __construct( $doctrine, $session ) {
        $this->doctrine = $doctrine;
        $this->session  = $session;
    }
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event){
        $usuario = $event->getAuthenticationToken()->getUser();
        $this->session->set('user', $usuario->getId());
//        $usuario->setUltimoLogin(new \DateTime());
//        $em = $this->doctrine->getEntityManager();
//        $em->flush();
    }
}