<?php

namespace HootSuite\DashboardBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use HootSuite\BackofficeBundle\Entity\Usuario;
use HootSuite\DashboardBundle\Form\UsuarioType;
use HootSuite\DashboardBundle\Form\UsuarioProfileType;
use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken,
    Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Payum\Core\Request\BinaryMaskStatusRequest;
use Payum\Core\Request\GetHumanStatus;

class UsuarioController extends Controller
{
    public function indexAction()
    {

    }

    public function requestPassAction(){

        $peticion = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $error = $procesed = NULL;
        if ($peticion->getMethod() == 'POST') {
            $email = $peticion->get("email");
            $entity = $em->getRepository('FrontendBundle:Usuario')->findOneBy(array('email' => $email));
            if( !$entity ){
                $error = $this->get('translator')->trans('error.mail_not_found');
            }
            else{
                $entity->setConfirmationToken();
                $entity->setPasswordRequestedAt();
                $em->persist($entity);
                $em->flush();
                $procesed = true;
                $body = $this->renderView('FrontendBundle:Usuario:mail_change_pass.html.twig', array(
                    'url' => $this->generateUrl('usuario_change_pass', array('token' => $entity->getConfirmationToken()), true)
                ));
                $mensaje = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('proyecto.title.request_pass'))
                    ->setFrom($this->container->getParameter("contact.mail"), 'SiteName')
                    ->setTo($entity->getEmail())
                    ->setBody($body, 'text/html')
                ;
                $this->get('mailer')->send($mensaje);
            }
        }
        return $this->render('FrontendBundle:Usuario:request_pass.html.twig', array(
            'error'      => $error,
            'procesed'   => $procesed
        ));
    }

    public function changePassAction($token){
        $peticion = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $invalid = NULL;
        $entity = $em->getRepository('FrontendBundle:Usuario')->findOneBy(array('confirmation_token' => $token));
        if (!$entity) {
            return $this->render('FrontendBundle:Usuario:change_pass.html.twig', array(
                'invalid' => true
            ));
        }
        else{
            $form = $this->createForm(new PasswordType(), $entity);
            if ($peticion->getMethod() == 'POST') {
                $form->bind($peticion);
                if ($form->isValid()) {
                    $entity->setSalt();
                    $encoder = $this->container->get('security.encoder_factory')
                        ->getEncoder($entity);
                    $password = $encoder->encodePassword($entity->getPassword(),$entity->getSalt());
                    $entity->setPassword($password);
                    $entity->setConfirmationToken("");
                    $entity->setPasswordRequestedAt("");
                    $em->persist($entity);
                    $em->flush();
                    $this->get('session')->getFlashBag()->set('ok', $this->get('translator')->trans('proyecto.label.password_changed'));
                    return $this->redirect($this->generateUrl('login'));
                }
            }
        }
        return $this->render('FrontendBundle:Usuario:change_pass.html.twig', array(
            'invalid'    => $invalid,
            'token'      => $token,
            'form'       => $form->createView()
        ));
    }

    public function recoverPassAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userEmail = $request->request->get('userEmail');
        $user = $em->getRepository('BackofficeBundle:Usuario')->findOneBy(array('email'=>$userEmail));
        $tokenId = md5(time());
        $token = $this->get('security.csrf.token_manager')->getToken($tokenId);
        $user->setConfirmationToken($token->getValue());
        $em->persist($user);
        $em->flush();
        if($user)
        {
            return new JsonResponse(array('user'=>$user->getId(),'token'=>$token->getValue()));
        }
        else{
            return new JsonResponse(array('message'=>'Por favor, revise su información.'));
        }
    }

    public function newPassFormAction(Request $request,$id,$token)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id);
        return $this->render('FrontendBundle:Seguridad:newPass.html.twig', array(
            'user' => $user->getId(),'token'=>$token
        ));
    }

    public function newPassAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userpassword = $request->request->get('password');
        $token = $request->request->get('token');
        $user=$em->getRepository('BackofficeBundle:Usuario')->findOneBy(array('confirmation_token',$token));
        $tokenId = md5(time());
        $token = $this->get('security.csrf.token_manager')->getToken($tokenId);
        if($user){
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $user->setSalt(md5(time()));
            $passwordCodificado = $encoder->encodePassword($userpassword,$user->getSalt());
            $user->setPassword($passwordCodificado);
            $user->setConfirmationToken($token->getValue());
            $em->persist($user);
            $em->flush();
            return new JsonResponse(array('message'=>'Ahora puede entrar al sistema.'));
        }
    }

    public function newUserAction(Request $request,$plan)
    {
        $user = new Usuario();
        $em = $this->getDoctrine()->getManager();
        $fullName = $request->request->get('fullName');
        $password = $request->request->get('password');
        $userEmail = $request->request->get('userEmail');
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $user->setSalt(md5(time()));
        $passwordCodificado = $encoder->encodePassword($password,$user->getSalt());
        $user->setPassword($passwordCodificado);
        $user->setName($fullName);
        $user->setEmail($userEmail);
        $user->setActive(0);
        $user->setPlan($em->getReference('HootSuite\BackofficeBundle\Entity\Plan',$plan));
        $em->persist($user);
        $em->flush();
        $this->get('session')->set('registered', $user->getId());
        $this->get('session')->set('plan',$plan);
        $this->get('session')->set('rtype', 'setup');
        return new JsonResponse(array('user'=>$user->getId()));
    }
    public function createUserAction(Request $request, $plan = NULL)
    {
        $entity  = new Usuario();
        $form = $this->createForm(new UsuarioType(), $entity);
        $em = $this->getDoctrine()->getManager();
        $plan = $em->getRepository('BackofficeBundle:Plan')->find($plan);
        $token = $this->get('security.csrf.token_manager')->getToken('user_create_token');

        /*if ($form->isValid()) {

            $entity->setCreatedAt();
            $entity->setPlan($plan);
            $entity->setActive(0);
            $entity->setSalt();
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($entity);
            $password = $encoder->encodePassword($entity->getPassword(),$entity->getSalt());
            $entity->setPassword($password);
            $em->persist($entity);
            $em->flush();
            $this->get("session")->set('registered', $entity->getId());
            if($plan->getPrice()!=0)
            {
                $this->get('session')->set('plan',$plan->getId());
            }
            else{
                $this->get('session')->remove('plan');
            }
            return $this->redirect($this->generateUrl('usuario_setup', array('id' => $entity->getId())));
        }*/

        return $this->render('FrontendBundle:Usuario:new.html.twig', array(
            'entity' => $entity,
            'plan'=>$plan,
            'user_create_token'=>$token->getValue(),
            'form'=>$form->createView(),
        ));
    }

    /**
     * Finish register setup.
     *
     */
    public function setupAction(Request $request, $id)
    {
        if(!$this->get('session')->get('registered')){
            return $this->redirect($this->generateUrl('homepage'));
        }
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackofficeBundle:Usuario')->find($id);
        $plan = null;
        if($this->get('session')->get('plan')!==null)
        {
            $plan = $em->getRepository('BackofficeBundle:Plan')->find($this->get('session')->get('plan'));
        }

        if ($request->getMethod() == 'POST') {
            $entity->setActive(1);
            $em->persist($entity);
            $em->flush();
            $token = new UsernamePasswordToken($entity, $entity->getPassword(), "users", $entity->getRoles());
            $this->get('security.context')->setToken($token);
            $this->get('session')->set('_security_users', serialize($token));
            // Fire the login event
            // Logging the user in above the way we do it doesn't do this automatically
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
            $this->get("session")->remove('registered');
            $this->get('session')->remove('plan');
            return $this->redirect($this->generateUrl('dashboard'));
        }
        return $this->render('FrontendBundle:Usuario:setup.html.twig', array(
            'entity' => $entity,'plan'=>$plan
        ));
    }
    public function userProfileAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $id  = $this->get('session')->get('user');
        if($this->get("session")->get('registered')!==null)
        {
            $id = $this->get("session")->get('registered');
        }
        $user_profile = $em->getRepository('BackofficeBundle:Usuario')->getUserProfile($id);
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id);
        $form = $this->createForm(new UsuarioProfileType(), $user);
        $plan = $user->getPlan();
        $html = $this->renderView('FrontendBundle:Usuario:user_profile.html.twig',array('user_profile'=>$user_profile,'form'=>$form->createView(),'plan'=>$plan));
        $object = array(
            "success" => true,
            "html"  => $html,
            "test"=>$user_profile
        );
        return new Response(json_encode($object));
    }
    public function purchaseAction(Request $request)
    {

        $paymentName = 'paypal_express_checkout_and_doctrine_orm';
        $storage = $this->get('payum')->getStorage('HootSuite\PaymentBundle\Entity\PaymentDetails');
        $paymentDetails = $storage->create();
        $paymentDetails['PAYMENTREQUEST_0_CURRENCYCODE'] = 'USD';
        $paymentDetails['PAYMENTREQUEST_0_AMT'] = 20;
        $storage->update($paymentDetails);
        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $paymentName,
            $paymentDetails,
            'payment_done_pro'
        );
        $paymentDetails['INVNUM'] = $paymentDetails->getId();
        $storage->update($paymentDetails);

        return $this->redirect($captureToken->getTargetUrl());

    }
    public function purchaseDoneAction(Request $request)
    {
        $token = $this->get('payum.security.http_request_verifier')->verify($request);
        $identity = $token->getDetails();
        $model = $this->get('payum')->getStorage($identity->getClass())->find($identity);

        $gateway = $this->get('payum')->getGateway($token->getGatewayName());
        $gateway->execute($status = new GetHumanStatus($token));
        $details = $status->getFirstModel();
        var_dump($status->getValue());
        exit;
    }
}
