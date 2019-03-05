<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace HootSuite\UsuarioBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Listener del evento SecurityInteractive que se utiliza para redireccionar
 * al usuario recién logueado a la portada de su ciudad
 */
class LoginListener
{
    private $contexto, $router, $apodo = null, $enabled = null;

    public function __construct(SecurityContext $context, Router $router)
    {
        $this->contexto = $context;
        $this->router   = $router;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $this->apodo = $token->getUser()->getApodo();
        
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (null != $this->apodo) {
            if ($this->contexto->isGranted('ROLE_EMPLEADOR')) {
                $dashboard = $this->router->generate('empleador_dashboard', array(
                    'empleador' => $this->apodo
                ));
            }
            if ($this->contexto->isGranted('ROLE_ADMIN')) {
                $dashboard = $this->router->generate('dashboard');
            } else {
                $dashboard = $this->router->generate('frontend_homepage');
            }

            $event->setResponse(new RedirectResponse($dashboard));
            $event->stopPropagation();
        }
    }
}
