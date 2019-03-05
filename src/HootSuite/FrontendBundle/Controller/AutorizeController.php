<?php

namespace HootSuite\FrontendBundle\Controller;

use Facebook\Facebook;
use HootSuite\BackofficeBundle\Entity\ProfilesUsuario;
use HootSuite\BackofficeBundle\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use HootSuite\DashboardBundle\Form\UsuarioType;
use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken,
    Symfony\Component\Security\Http\Event\InteractiveLoginEvent,
    Symfony\Component\BrowserKit\Cookie;

class AutorizeController extends Controller
{
    public function twitterAction($id)
    {
        $request = $this->get('request');
        $twitter = $this->get('twitter');
        $this->get("session")->set('waiting_twitter', $id);
        $authURL = $twitter->getLoginUrl($request);
        $response = new RedirectResponse($authURL);
        return $response;
    }

    public function twitterVerifyEmailAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $email=$request->request->get('email');
        $id=$request->request->get('twitteruser');
        $profile_id=$request->request->get('twitterprofile');
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id);
        $user->setEmail($email);
        $em->persist($user);
        $em->flush();
        $profile = $em->getRepository('BackofficeBundle:ProfileUsuario')->find($profile_id);
        return $this->render('FrontendBundle:Autorize:twitter.html.twig', array('auth' => true,'email'=>true,'response_type'=>null, 'profile' => $profile));
    }

    public function twitterSuccessAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $rtype = $this->get('session')->get('rtype');
        $red_id = $this->get("session")->get('waiting_twitter');
        $id_usuario = $this->get("session")->get('registered') ? $this->get("session")->get('registered') : $this->get('session')->get('user');
        $twitter = $this->get('twitter');
        //$oauth_token = $request->query->get('oauth_token');
        $oauth_verifier = $request->query->get('oauth_verifier');
        $accessToken = $twitter->getAccessToken($oauth_verifier);
        $twitter->setOauthToken($accessToken['oauth_token'],$accessToken['oauth_token_secret']);
        $twitter_user = $twitter->get('users/show', array("screen_name" => $accessToken["screen_name"]));        
       // $twitter_userEmail = $twitter->get('account/verify_credentials', array("include_email" => true));
        $auth = false;
        $profile = NULL;
        $response_type = null;
        $id_usuario = ($id_usuario===null) ? 0 : $id_usuario; 
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
            $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findOneBy(array('userid'=>$accessToken["user_id"]));
            if ($profile) 
            {
                $profile->setSelected(1);
                $em->persist($profile);
                $em->flush();         
                if($user)
                {
                    $profile->setUsuario($user);
                    $profile->setSelected(1);
                    $em->persist($profile);
                    $em->flush();                 
                    $this->get("session")->set('created_profile_'.$red_id, $profile->getId());                
                    $response_type = 'setup';
                    $auth = true;
                }
                else
                {
                    $user = $profile->getUsuario();                
                    $this->get('session')->set('registered', $user->getId());
                    $this->get('session')->set('created_profile_' . $red_id, $profile->getId());
                    $this->get('session')->remove('waiting_twitter');
                    $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                    $this->get('security.context')->setToken($token);
                    $this->get('session')->set('_security_users', serialize($token));
                    $event = new InteractiveLoginEvent($request, $token);
                    $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);
                    $response_type = 'authenticated';
                    $auth = true;
                }
                
            } else {
                if(!$user)
                {
                    $user = new Usuario();
                    $user->setName($accessToken["screen_name"]);
                    $user->setEmail('@twitter');
                    $plan = $em->getRepository('BackofficeBundle:Plan')->find(1);
                    $user->setPlan($plan);
                    $user->setActive(1);
                    $user->setSalt();
                    $encoder = $this->container->get('security.encoder_factory')
                        ->getEncoder($user);
                    $password = $encoder->encodePassword($accessToken["oauth_token"], $user->getSalt());
                    $user->setPassword($password);
                    $em->persist($user);
                    $em->flush();
                    $this->get("session")->set('registered', $user->getId());
                    $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                    $this->get('security.context')->setToken($token);
                    $this->get('session')->set('_security_users', serialize($token));
                    $event = new InteractiveLoginEvent($request, $token);
                    $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                    $response_type = 'created';
                }                
                $profile = new ProfilesUsuario();
                $profile->setToken($accessToken["oauth_token"]);
                $profile->setTokenSecret($accessToken["oauth_token_secret"]);
                $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find($red_id); // TWITTER
                $profile->setSocialNetwork($red);
                //$user = $em->getRepository('BackofficeBundle:Usuario')->find($entity); // RECUPERAMOS EL ID DEL USUARIO
                $profile->setUsuario($user);
                $profile->setCreatedAt();
                $profile->setFavorite(0);
                $profile->setSelected(0);
                $profile->setUsername($accessToken["screen_name"]);
                $profile->setUserid($accessToken["user_id"]);
                $profile->setFullName($twitter_user->name);
                $profile->setAvatar($twitter_user->profile_image_url);
                $profile->setUrl('https://twitter.com/' . $twitter_user->screen_name);
                $profile->setSite('twitter');
                $em->persist($profile);
                $em->flush();                
                $this->get("session")->set('created_profile_' . $red_id, $profile->getId());
                $this->get("session")->remove('waiting_twitter');
                $response_type = 'authenticated';
                $auth = true;
            }
        $response_type = $rtype ?: $response_type;
        $this->get('session')->remove('rtype');
        return $this->render('FrontendBundle:Autorize:twitter.html.twig', array('auth' => $auth,'response_type'=>$response_type, 'profile' => $profile,'id'=>$user->getId()));
    }

    public function isAuthorizedAction($red)
    {
        $id_profile = $this->get("session")->get("created_profile_$red");
        $em = $this->getDoctrine()->getManager();
        $response = new Response();
        if ($id_profile) {
            $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($id_profile);
            $profile_array = array(
                'id' => $profile->getId(),
                'redid' => $profile->getSocialNetwork()->getId(),
                'red' => $profile->getSocialNetwork()->getName(),
                'name' => $profile->getUsername(),
                'avatar' => $profile->getAvatar(),
                'user' => $profile->getUsuario()->getId()
            );


            $html = $this->renderView('FrontendBundle:Autorize:red_agregada.html.twig', array(
                'profile' => $profile
            ));
            $groups_html = null;
            switch ($profile->getSocialNetwork()->getUniquename()) {
                case 'GOOGLE':
                    $google = $this->get('google');
                    $google->setAccessToken($profile->getToken(),$profile->getTokenSecret());
                    $pages = $google->getPages($profile->getUserid());


                    if (count($pages)) {
                        $groups_html = $this->renderView('FrontendBundle:Autorize:google_pages.html.twig', array(
                            'pages' => $pages
                        ));
                    }
                    break;
                case 'FACEBOOK':
                    /*$facebook = new Facebook([
                        'app_id' => '494431310714400',
                        'app_secret' => '77c12744c08512239c6933b09ea64743',
                        'default_graph_version' => 'v2.3',
                    ]);*/
                    $facebook = $this->get('facebookv2');
                    $fbApi = $facebook->get('/me/groups', $this->get("session")->get('facebook_accesstoken'));
                    $facebookGroups = $fbApi->getGraphEdge('GraphGroup');
                    $groups = $facebookGroups->asArray();
                    //$facebook = $this->get('fos_facebook.api');
                    //$facebook->setAccessToken($profile->getToken());
                    //$groups = $facebook->api('me/groups');
                    $groups = $groups ? $groups : array();
                    if (count($groups)) {
                        $groups_html = $this->renderView('FrontendBundle:Autorize:facebook_groups.html.twig', array(
                            'groups' => $groups
                        ));
                    }
                    break;
                /*case 'LINKEDIN':
                    $linkedin = $this->get('linkedin');
                    $linkedin->setAccessToken($profile->getToken());
                    $groups = $linkedin->api('GET', '/v1/people/');
                    $groups = isset($groups['data']) ? $groups['data'] : array();
                    if (count($groups)) {
                        $groups_html = $this->renderView('FrontendBundle:Autorize:linkedin_groups.html.twig', array(
                            'groups' => $groups
                        ));
                    }
                    break;*/
            }

            $object = json_encode(array("success" => true, "html" => $html, "profile" => $profile_array, "groups" => $groups_html));
            if ($profile->getUsuario()->getActive() == false) {
                $profile->getUsuario()->setActive(1);
                $em->persist($profile->getUsuario());
                $em->flush($profile->getUsuario());
            }
            $response->setContent($object);
            $this->get("session")->remove("created_profile_$red");
            return $response;
        } else {
            $object = json_encode(array("success" => false));
        }

        $response->setContent($object);
        return $response;
    }

    public function facebookAction($id)
    {        
        session_start();
        $facebook = $this->get('facebookv2');
        /*$facebook = new Facebook([
            'app_id' => '494431310714400',
            'app_secret' => '77c12744c08512239c6933b09ea64743',
            'default_graph_version' => 'v2.3',
        ]);*/
        $helper = $facebook->getRedirectLoginHelper();
        $permissions = ['public_profile,user_friends,email,user_about_me,user_events,user_photos,user_status,user_posts,read_custom_friendlists,read_page_mailboxes,manage_pages,publish_actions,read_insights,publish_pages,user_videos,user_likes,user_managed_groups,ads_management,pages_manage_cta']; // Optional permissions

        $loginUrl = $helper->getLoginUrl($facebook->getCallbackUrl(), $permissions);
        //$facebook = $this->get('fos_facebook.api');
        $this->get("session")->set('waiting_facebook', $id);
        /*$authURL = $facebook->getLoginUrl(array(
            'scope' => 'email,read_stream,publish_actions,user_about_me,user_birthday,user_education_history,user_events,user_groups,user_hometown,user_likes,user_location,user_photos,user_relationship_details,user_relationships,user_religion_politics,user_status,user_tagged_places,user_videos,user_website,user_work_history',
            'display' => 'popup'
        ),
            $this->generateUrl('connect_facebook_success', array(), true));*/
        $response = new RedirectResponse($loginUrl);
        return $response;
    }

    public function loadFacebookGroupAction(Request $request)
    {
        $groups = $request->request->get('groups');

        $response = new Response();
        $html = $this->renderView('FrontendBundle:Autorize:facebook_groups.html.twig', array(
            "groups" => $groups
        ));
        $object = json_encode(array("html" => $html));
        $response->setContent($object);
        return $response;
    }

    public function setupFacebookInfoAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackofficeBundle:Usuario')->findOneBy(array('id'=>$id));
        return $this->render('FrontendBundle:Usuario:setup.html.twig', array('entity' => $entity));
    }

    public function checkFacebookUserAction(Request $request)
    {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $link = $request->request->get('link');
        $picture = $request->request->get('picture');
        $accessToken = $request->request->get('accessToken');
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findOneBy(array('userid'=>$id));
        if ($profile) {
            $user = $profile->getUsuario();
            $this->get("session")->set('registered', $user->getId());
            $this->get("session")->set('created_profile_2', $profile->getId());
            $this->get("session")->remove('waiting_facebook');
            $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
            $this->get('security.context')->setToken($token);
            $this->get('session')->set('_security_users', serialize($token));
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

            $object = json_encode(array("logged" => true,'profile' => $profile->getId(),'id'=>$user->getId()));
            $response->setContent($object);

        } else {
            $entity = new Usuario();
            $entity->setName($name);
            $entity->setEmail($email);
            $plan = $em->getRepository('BackofficeBundle:Plan')->find(1);
            $entity->setPlan($plan);
            $entity->setActive(0);
            $entity->setSalt();
            $encoder = $this->container->get('security.encoder_factory')
                ->getEncoder($entity);
            $password = $encoder->encodePassword($accessToken, $entity->getSalt());
            $entity->setPassword($password);
            $em->persist($entity);
            $em->flush();
            $profile = new ProfilesUsuario();
            $profile->setToken($accessToken);
            $profile->setTokenSecret($accessToken);
            $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find(2); // FACEBOOK
            $profile->setSocialNetwork($red);
            $user = $em->getRepository('BackofficeBundle:Usuario')->find($entity);
            $profile->setUsuario($user);
            $profile->setCreatedAt(new \DateTime('now'));
            $profile->setUsername($name);
            $profile->setUserid($id);
            $profile->setFullName($name);
            $profile->setAvatar($picture);
            $profile->setUrl($link);
            $profile->setFavorite(0);
            $profile->setSelected(0);
            $em->persist($profile);
            $em->flush();
            $this->get("session")->set('registered', $entity->getId());
            $this->get("session")->set('created_profile_2', $profile->getId());
            $this->get("session")->remove('waiting_facebook');
            $token = new UsernamePasswordToken($entity, $entity->getPassword(), "users", $entity->getRoles());
            $this->get('security.context')->setToken($token);
            $this->get('session')->set('_security_users', serialize($token));
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
            $object = json_encode(array("logged" => false, 'profile' => $profile->getId(),'user'=>$user->getId()));
            $response->setContent($object);
        }
        return $response;
    }

    public function checkLinkedinUserAction(Request $request)
    {
        $id = $request->request->get('id');
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $link = $request->request->get('link');
        $picture = $request->request->get('picture');
        $accessToken = $request->request->get('accessToken');
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findOneBy(array('userid'=>$id));
        if ($profile) {
            $user = $profile->getUsuario();
            $this->get("session")->set('registered', $user->getId());
            $this->get("session")->set('created_profile_4', $profile->getId());
            $this->get("session")->remove('waiting_facebook');
            $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
            $this->get('security.context')->setToken($token);
            $this->get('session')->set('_security_users', serialize($token));
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
            $object = json_encode(array("logged" => true,'profile' => $profile->getId(),'id'=>$user->getId()));
            $response->setContent($object);
        } else {
            $entity = new Usuario();
            $entity->setName($name);
            $entity->setEmail($email);
            $plan = $em->getRepository('BackofficeBundle:Plan')->find(1);
            $entity->setPlan($plan);
            $entity->setActive(0);
            $entity->setSalt();
            $encoder = $this->container->get('security.encoder_factory')
                ->getEncoder($entity);
            $password = $encoder->encodePassword($accessToken, $entity->getSalt());
            $entity->setPassword($password);
            $em->persist($entity);
            $em->flush();
            $profile = new ProfilesUsuario();
            $profile->setToken($accessToken);
            $profile->setTokenSecret($accessToken);
            $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find(4); // LINKEDIN
            $profile->setSocialNetwork($red);
            $user = $em->getRepository('BackofficeBundle:Usuario')->find($entity); // RECUPERAMOS EL ID DEL USUARIO
            $profile->setUsuario($user);
            $profile->setCreatedAt();
            $profile->setFavorite(0);
            $profile->setSelected(0);
            $profile->setUsername($name);
            $profile->setUserid($id);
            $profile->setFullName($name);
            $profile->setAvatar($picture);
            $profile->setUrl($link);
            $em->persist($profile);
            $em->flush();
            $this->get("session")->set('registered', $entity->getId());
            $this->get("session")->set('created_profile_4', $profile->getId());
            $token = new UsernamePasswordToken($entity, $entity->getPassword(), "users", $entity->getRoles());
            $this->get('security.context')->setToken($token);
            $this->get('session')->set('_security_users', serialize($token));
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
            $object = json_encode(array("logged" => false, 'profile' => $profile->getId(),'user'=>$user->getId()));
            $response->setContent($object);
        }
        return $response;
    }

    public function loadLinkedinGroupAction(Request $request)
    {
        $groups = $request->request->get('groups');

        $response = new Response();
        $html = $this->renderView('FrontendBundle:Autorize:linkedin_groups.html.twig', array(
            "groups" => json_encode($groups)
        ));
        $object = json_encode(array("html" => $html));
        $response->setContent($object);
        return $response;
    }

    public function facebookSuccessAction(Request $request)
    {
        session_start();
        $em = $this->getDoctrine()->getManager();
        
        $fb = $this->get('facebookv2');

        $helper = $fb->getRedirectLoginHelper();
        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $oAuth2Client = $fb->getOAuth2Client();
        $rtype = $this->get('session')->get('rtype');
        $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        $this->get("session")->set('facebook_accesstoken', $accessToken->getValue());
        $response = $fb->get('/me?fields=id,name,email,picture,link', $accessToken->getValue());
        $facebookUser = $response->getGraphUser();
        $red_id = $this->get("session")->get('waiting_facebook');
        $id_usuario = $this->get("session")->get('registered') ? $this->get("session")->get('registered') : $this->get('session')->get('user');
        $id_usuario = ($id_usuario===null) ? 0 : $id_usuario; 
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
        $auth = false;
        $response_type = null;
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findOneBy(array('userid'=>$facebookUser->getId()));
        if ($profile)
        {
            $profile->setSelected(1);
            $em->persist($profile);
            $em->flush();
            if($user)
            {
                $profile->setUsuario($user);
                $em->persist($profile);
                $em->flush();                 
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());                
                $response_type = 'setup';
                $auth = true;
            }
            else{
                $user = $profile->getUsuario();
                $this->get("session")->set('registered', $user->getId());
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());
                $this->get("session")->remove('waiting_facebook');
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'authenticated';
                $auth = true;
            }            
        }
        else
        {
            if(!$user)
            {
                $user = new Usuario();
                $user->setName($facebookUser->getName());
                $user->setEmail($facebookUser['email']);
                $plan = $em->getRepository('BackofficeBundle:Plan')->find(1);
                $user->setPlan($plan);
                $user->setActive(1);
                $user->setSalt();
                $encoder = $this->container->get('security.encoder_factory')
                    ->getEncoder($user);
                $password = $encoder->encodePassword($accessToken, $user->getSalt());
                $user->setPassword($password);
                $em->persist($user);
                $em->flush(); 
                $this->get("session")->set('registered', $user->getId());
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'created';
            }            
            $profile = new ProfilesUsuario();
            $profile->setToken($accessToken->getValue());
            $profile->setTokenSecret($accessToken->getValue());
            $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find(2); // FACEBOOK
            $profile->setSocialNetwork($red);
            //$user = $em->getRepository('BackofficeBundle:Usuario')->find($entity);
            $profile->setUsuario($user);
            $profile->setCreatedAt(new \DateTime('now'));
            $profile->setUsername($facebookUser->getName());
            $profile->setUserid($facebookUser->getId());
            $profile->setFullName($facebookUser->getName());
            $profile->setAvatar($facebookUser->getPicture()->getUrl());
            $profile->setUrl($facebookUser->getLink());
            $profile->setFavorite(0);
            $profile->setSelected(0);
            $profile->setSite('facebook');
            $em->persist($profile);
            $em->flush();            
            $this->get("session")->set('created_profile_2', $profile->getId());
            $this->get("session")->remove('waiting_facebook');            
            $auth = true;
            $response_type = 'authenticated';
        }
        $response_type = $rtype ?: $response_type;
        $this->get('session')->remove('rtype');
        return $this->render('FrontendBundle:Autorize:facebook.html.twig', array('auth' => $auth,'response_type'=>$response_type, 'profile' => $profile,'id'=>$user->getId()));

    }

    public function googleAction($id)
    {
        $google = $this->get('google');
        $authorizeUrl = $google->loginUrl();
        $response = new RedirectResponse($authorizeUrl);
        $this->get("session")->set('waiting_google', $id);
        return $response;
    }

    public function googleSuccessAction(Request $request)
    {
        $red_id = $this->get("session")->get('waiting_google');
        $id_usuario = $this->get("session")->get('registered') ? $this->get("session")->get('registered') : $this->get('session')->get('user');
        $auth = false;
        $profile = NULL;
        $em = $this->getDoctrine()->getManager();
        $google = $this->get('google');
        $access_token = $google->fetchAccessToken($request->query->get('code'));
        $access_token = json_decode($access_token);
        $userInfo = $google->getUserInfo();
        //$pages = $google->getPages($userInfo->getId());
        //var_dump($pages);exit;
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findOneBy(array('userid'=>$userInfo->getId()));
        $id_usuario = ($id_usuario===null) ? 0 : $id_usuario; 
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
        $rtype = $this->get('session')->get('rtype');
        if ($profile)
        {
            $profile->setSelected(1);
            $em->persist($profile);
            $em->flush();            
            if($user)
            {
                $profile->setUsuario($user);
                $em->persist($profile);
                $em->flush();                 
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());                
                $response_type = 'setup';
                $auth = true;
            }
            else{
                $user = $profile->getUsuario();
                $this->get("session")->set('registered', $user->getId());
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());
                $this->get("session")->remove('waiting_facebook');
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'authenticated';
                $auth = true;
            }            
        }
        else
        {
            if(!$user)
            {
                $user = new Usuario();
                $user->setName($userInfo->getGivenName().' '.$userInfo->getFamilyName());
                $user->setEmail($userInfo->getEmail());
                $plan = $em->getRepository('BackofficeBundle:Plan')->find(1);
                $user->setPlan($plan);
                $user->setActive(1);
                $user->setSalt();
                $encoder = $this->container->get('security.encoder_factory')
                    ->getEncoder($user);
                $password = $encoder->encodePassword($access_token->access_token, $user->getSalt());
                $user->setPassword($password);
                $em->persist($user);
                $em->flush();  
                $this->get("session")->set('registered', $user->getId());
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'created';
            }            
            $profile = new ProfilesUsuario();
            $profile->setToken($access_token->access_token);
            $profile->setTokenSecret($access_token->id_token);
            $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find($red_id); // FACEBOOK
            $profile->setSocialNetwork($red);
            //$user = $em->getRepository('BackofficeBundle:Usuario')->find($entity);
            $profile->setUsuario($user);
            $profile->setCreatedAt(new \DateTime('now'));
            $profile->setUsername($userInfo->getGivenName().' '.$userInfo->getFamilyName());
            $profile->setUserid($userInfo->getId());
            $profile->setFullName($userInfo->getGivenName().' '.$userInfo->getFamilyName());
            $profile->setAvatar($userInfo->getPicture());
            $profile->setUrl($userInfo->getPicture());
            $profile->setFavorite(0);
            $profile->setSelected(0);
            $profile->setSite('google');
            $em->persist($profile);
            $em->flush();            
            $this->get("session")->set('created_profile_' . $red_id, $profile->getId());
            $this->get("session")->remove('waiting_google');            
            $auth = true;
            $response_type = 'authenticated';
        }
        $response_type = $rtype ?: $response_type;
        $this->get('session')->remove('rtype');
        return $this->render('FrontendBundle:Autorize:google.html.twig', array('auth' => $auth,'response_type'=>$response_type, 'profile' => $profile,'id'=>$user->getId()));
    }

    public function linkedinAction($id)
    {

        $linkedIn = $this->get('linkedin');
        $url = $linkedIn->getLoginUrl();
        $this->get("session")->set('waiting_linkedin', $id);
        return $this->redirect($url);
    }

    public function linkedinSuccessAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $red_id = $this->get("session")->get('waiting_linkedin');
        $id_usuario = $this->get("session")->get('registered') ? $this->get("session")->get('registered') : $this->get('session')->get('user');
        $auth = false;
        $linkedIn = $this->get('linkedin');
        $li_user = $linkedIn->getUser();
        $accessToken = $linkedIn->getAccessToken();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findOneBy(array('userid'=>$li_user["id"]));
        $id_usuario = ($id_usuario===null) ? 0 : $id_usuario; 
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
        $response_type = null;
        $rtype = $this->get('session')->get('rtype');
        if ($profile)
        {
            $profile->setSelected(1);
            $em->persist($profile);
            $em->flush();            
            if($user)
            {
                //$user = $em->getReference('BackOfficeBundle\Entity\Usuario', $id_usuario);
                $profile->setUsuario($user);
                $em->persist($profile);
                $em->flush();                 
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());                
                $response_type = 'setup';
                $auth = true;
            }
            else{
                $user = $profile->getUsuario();
                $this->get("session")->set('registered', $user->getId());
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());
                $this->get("session")->remove('waiting_linkedin');
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'authenticated';
                $auth = true;
            }            
        }
        else
        {
            if(!$user){
                $user = new Usuario();
                $user->setName($li_user["formattedName"]);
                $user->setEmail($li_user["emailAddress"]);
                $plan = $em->getRepository('BackofficeBundle:Plan')->find(1);
                $user->setPlan($plan);
                $user->setActive(1);
                $user->setSalt();
                $encoder = $this->container->get('security.encoder_factory')
                    ->getEncoder($user);
                $password = $encoder->encodePassword($accessToken, $user->getSalt());
                $user->setPassword($password);
                $em->persist($user);
                $em->flush();
                $id_usuario = $user->getId();
                $this->get("session")->set('registered', $user->getId());
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'created';                
            }
            $formattedName = $li_user["firstName"].' '.$li_user["lastName"];
            $profile = new ProfilesUsuario();
            $profile->setToken($accessToken);
            $profile->setTokenSecret($accessToken);
            $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find(4);
            $profile->setSocialNetwork($red);
            //$user = $em->getRepository('BackofficeBundle:Usuario')->find($entity);
            $profile->setUsuario($user);
            $profile->setCreatedAt(new \DateTime('now'));
            $profile->setUsername($formattedName);
            $profile->setUserid($li_user["id"]);
            $profile->setFullName($formattedName);
            $profile->setAvatar(isset($li_user["siteStandardProfileRequest"]['url']) ? $li_user["siteStandardProfileRequest"]['url'] : 'https://static.licdn.com/scds/common/u/img/icon/icon_no_photo_40x40.png');
            $profile->setUrl($li_user["siteStandardProfileRequest"]['url']);
            $profile->setFavorite(0);
            $profile->setSelected(0);
            $profile->setSite('linkedin');
            $em->persist($profile);
            $em->flush();           
            $this->get("session")->set('created_profile_4', $profile->getId());
            $this->get("session")->remove('waiting_linkedin');           
            $auth = true;
            $this->get("session")->set('created_profile_'.$red_id, $profile->getId());
            $this->get("session")->remove('waiting_wordpress');
            $response_type = 'authenticated';
            $auth = true;
        }
        $response_type = $rtype ?: $response_type;
        $this->get('session')->remove('rtype');
        return $this->render('FrontendBundle:Autorize:linkedin.html.twig', array('auth' => $auth,'response_type'=>$response_type, 'profile' => $profile,'id'=>$user->getId()));
    }

    public function delProfileAction($id)
    {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($id);
        $em->remove($profile);
        $em->flush();
        $response->setContent(json_encode(array("success" => true)));
        return $response;
    }

    public function wordpressAction($id)
    {

        $wordpress = $this->get('wordpress');
        $url = $wordpress->getLoginUrl();
        $this->get("session")->set('waiting_wordpress', $id);
        return $this->redirect($url);
    }

    public function wordpressSuccessAction(Request $request)
    {
        $red_id = $this->get("session")->get('waiting_wordpress');
        $id_usuario = $this->get("session")->get('registered') ? $this->get("session")->get('registered') : $this->get('session')->get('user');
        $auth = false;
        $profile = NULL;
        $response_type = NULL;
        $code = $request->query->get('code');
        $wordpress = $this->get('wordpress');
        $accessToken = $wordpress->getAccessToken($code);
        $wp_user = $wordpress->api('/me');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findOneBy(array('userid'=>$wp_user->ID));
        $id_usuario = ($id_usuario===null) ? 0 : $id_usuario; 
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
        $rtype = $this->get('session')->get('rtype');
        if ($profile)
        {
            $profile->setSelected(1);
            $em->persist($profile);
            $em->flush();            
            if($user)
            {
                //$user = $em->getReference('BackOfficeBundle\Entity\Usuario', $id_usuario);
                $profile->setUsuario($user);
                $em->persist($profile);
                $em->flush();                 
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());                
                $response_type = 'setup';
                $auth = true;
            }
            else
            {
                $user = $profile->getUsuario();
                $this->get("session")->set('registered', $user->getId());
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());
                $this->get("session")->remove('waiting_wordpress');
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'authenticated';
                $auth = true;                  
            }            
        }
        else
        {
            if(!$user){
                $user = new Usuario();
                $user->setName($wp_user->username);
                $user->setEmail($wp_user->email);
                $plan = $em->getRepository('BackofficeBundle:Plan')->find(1);
                $user->setPlan($plan);
                $user->setActive(1);
                $user->setSalt();
                $encoder = $this->container->get('security.encoder_factory')
                  ->getEncoder($user);
                $password = $encoder->encodePassword($accessToken, $user->getSalt());
                $user->setPassword($password);
                $em->persist($user);
                $em->flush(); 
                $id_usuario = $user->getId();
                $this->get("session")->set('registered', $user->getId());
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'created';
                
            } 
            $profile = new ProfilesUsuario();
            $profile->setToken($accessToken);
            $profile->setTokenSecret($accessToken);
            $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find(5);
            $profile->setSocialNetwork($red);
            //$user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
            $profile->setUsuario($user);
            $profile->setCreatedAt(new \DateTime('now'));
            $profile->setUsername($wp_user->username);
            $profile->setUserid($wp_user->ID);
            $profile->setFullName($wp_user->display_name);
            $profile->setAvatar(isset($wp_user->avatar_URL) ? $wp_user->avatar_URL : '');
            $profile->setUrl($wp_user->profile_URL);
            $profile->setSite($wp_user->meta->links->site);
            $profile->setFavorite(0);
            $profile->setSelected(0);
            $profile->setSite('wordpress');
            $em->persist($profile);
            $em->flush(); 
            $auth = true;
            $this->get("session")->set('created_profile_'.$red_id, $profile->getId());
            $this->get("session")->remove('waiting_wordpress');
            $response_type = 'authenticated';
        }
        $response_type = $rtype ?: $response_type;
        $this->get('session')->remove('rtype');
        return $this->render('FrontendBundle:Autorize:wordpress.html.twig', array('auth' => $auth,'response_type'=>$response_type, 'profile' => $profile,'id'=>$user->getId()));
    }

    public function instagramAction($id)
    {
        $instagram = $this->get('instagram');
        $this->get("session")->set('waiting_instagram', $id);
        $loginUrl = $instagram->getLoginUrl(array('basic', 'likes', 'comments', 'relationships'));
        $response = new RedirectResponse($loginUrl);
        return $response;
    }

    public function instagramSuccessAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $instagram = $this->get('instagram');
        $code = $request->query->get('code');
        $data = $instagram->getOAuthToken($code);
        $instagram->setAccessToken($data);
        $instagramUser = $instagram->getUser();
        $accessToken = $data->access_token;       
        $this->get("session")->set('instagram_accesstoken', $data);

        $red_id = $this->get("session")->get('waiting_instagram');
        $id_usuario = $this->get("session")->get('registered') ? $this->get("session")->get('registered') : $this->get('session')->get('user');
        $id_usuario = ($id_usuario===null) ? 0 : $id_usuario; 
        $auth = false;
        $response_type = null;
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findOneBy(array('userid'=>$instagramUser->data->id));
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
        $rtype = $this->get('session')->get('rtype');
        if ($profile)
        {
            $profile->setSelected(1);
            $em->persist($profile);
            $em->flush();            
            if($user)
            {
                $profile->setUsuario($user);
                $em->persist($profile);
                $em->flush();
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());
                $response_type = 'setup';
                $auth = true;
            }
            else{
                $user = $profile->getUsuario();
                $this->get("session")->set('registered', $user->getId());
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());
                $this->get("session")->remove('waiting_instagram');
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'authenticated';
                $auth = true;
            }
        }
        else
        {           
            if(!$user)
            {
                $user = new Usuario();
                $user->setName($instagramUser->data->full_name);
                $user->setEmail($instagramUser->data->username);
                $plan = $em->getRepository('BackofficeBundle:Plan')->find(1);
                $user->setPlan($plan);
                $user->setActive(1);
                $user->setSalt();
                $encoder = $this->container->get('security.encoder_factory')
                    ->getEncoder($user);
                $password = $encoder->encodePassword($accessToken, $user->getSalt());
                $user->setPassword($password);
                $em->persist($user);
                $em->flush();
                $this->get("session")->set('registered', $user->getId());
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'created';
                $id_usuario = $user->getId();
            }
            $profile = new ProfilesUsuario();
            $profile->setToken($accessToken);
            $profile->setTokenSecret($accessToken);
            $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find(6);
            $profile->setSocialNetwork($red);
            //$user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
            $profile->setUsuario($user);
            $profile->setCreatedAt(new \DateTime('now'));
            $profile->setUsername($instagramUser->data->username);
            $profile->setUserid($instagramUser->data->id);
            $profile->setFullName($instagramUser->data->full_name);
            $profile->setAvatar($instagramUser->data->profile_picture);
            $profile->setUrl($instagramUser->data->profile_picture);
            $profile->setFavorite(0);
            $profile->setSelected(0);
            $profile->setSite('instagram');
            $em->persist($profile);
            $em->flush();
            $this->get("session")->set('created_profile_6', $profile->getId());
            $this->get("session")->remove('waiting_instagram');
            $auth = true;
            $response_type = 'authenticated';
        }
        /*else if ($id_usuario) {
            if($profile)
            {
                $auth = true;
                $response_type = 'registered';
            }
            else{
                $profile = new ProfilesUsuario();
                $profile->setToken($accessToken->getValue());
                $profile->setTokenSecret($accessToken->getValue());
                $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find(2); // FACEBOOK
                $profile->setSocialNetwork($red);
                $user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
                $profile->setUsuario($user);
                $profile->setCreatedAt(new \DateTime('now'));
                $profile->setUsername($facebookUser->getName());
                $profile->setUserid($facebookUser->getId());
                $profile->setFullName($facebookUser->getName());
                $profile->setAvatar($facebookUser->getPicture()->getUrl());
                $profile->setUrl($facebookUser->getLink());
                $profile->setFavorite(0);
                $profile->setSelected(0);
                $em->persist($profile);
                $em->flush();
                $this->get("session")->set('created_profile_' . $red_id, $profile->getId());
                $this->get("session")->remove('waiting_facebook');
                $auth = true;
            }
        }*/
        $response_type = $rtype ?: $response_type;
        $this->get('session')->remove('rtype');
        return $this->render('FrontendBundle:Autorize:instagram.html.twig', array('auth' => $auth,'response_type'=>$response_type, 'profile' => $profile,'id'=>$user->getId()));

    }
    
    public function pinterestAction($id)
    {
        $pinterest = $this->get('pinterest');
        $this->get("session")->set('waiting_pinterest', $id);
        $loginUrl = $pinterest->auth->getLoginUrl("https://".$_SERVER['HTTP_HOST'].'/pinterest_success', array('read_public','write_public','read_relationships','write_relationships'));
        $response = new RedirectResponse($loginUrl);
        return $response;
    }

    public function pinterestSuccessAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $pinterest = $this->get('pinterest');
        $code = $request->query->get('code');
        $token = $pinterest->auth->getOAuthToken($code);
        $pinterest->auth->setOAuthToken($token->access_token);
        $me = $pinterest->users->me(array('fields' => 'username,first_name,last_name,image[small],url'
        ))->toArray();
        $accessToken = $token->access_token;       
        $this->get("session")->set('pinterest_accesstoken', $accessToken);

        $red_id = $this->get("session")->get('waiting_pinterest');
        $id_usuario = $this->get("session")->get('registered') ? $this->get("session")->get('registered') : $this->get('session')->get('user');
        $id_usuario = ($id_usuario===null) ? 0 : $id_usuario; 
        $auth = false;
        $response_type = null;
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->findOneBy(array('userid'=>$me['id']));
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
        $rtype = $this->get('session')->get('rtype');
        if ($profile)
        {
            $profile->setSelected(1);
            $em->persist($profile);
            $em->flush();            
            if($user)
            {
                $profile->setUsuario($user);
                $em->persist($profile);
                $em->flush();
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());
                $response_type = 'setup';
                $auth = true;
            }
            else{
                $user = $profile->getUsuario();
                $this->get("session")->set('registered', $user->getId());
                $this->get("session")->set('created_profile_'.$red_id, $profile->getId());
                $this->get("session")->remove('waiting_pinterest');
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'authenticated';
                $auth = true;
            }
        }
        else
        {           
            if(!$user)
            {
                $user = new Usuario();
                $user->setName($me['first_name'].' '.$me['last_name']);
                $user->setEmail('@');
                $plan = $em->getRepository('BackofficeBundle:Plan')->find(1);
                $user->setPlan($plan);
                $user->setActive(1);
                $user->setSalt();
                $encoder = $this->container->get('security.encoder_factory')
                    ->getEncoder($user);
                $password = $encoder->encodePassword($accessToken, $user->getSalt());
                $user->setPassword($password);
                $em->persist($user);
                $em->flush();
                $this->get("session")->set('registered', $user->getId());
                $token = new UsernamePasswordToken($user, $user->getPassword(), "users", $user->getRoles());
                $this->get('security.context')->setToken($token);
                $this->get('session')->set('_security_users', serialize($token));
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $response_type = 'created';
                $id_usuario = $user->getId();
            }
            $profile = new ProfilesUsuario();
            $profile->setToken($accessToken);
            $profile->setTokenSecret($accessToken);
            $red = $em->getRepository('BackofficeBundle:SocialNetwork')->find(7);
            $profile->setSocialNetwork($red);
            //$user = $em->getRepository('BackofficeBundle:Usuario')->find($id_usuario);
            $profile->setUsuario($user);
            $profile->setCreatedAt(new \DateTime('now'));
            $profile->setUsername($me['username']);
            $profile->setUserid($me['id']);
            $profile->setFullName($me['first_name'].' '.$me['last_name']);
            $profile->setAvatar($me['image']['small']['url']);
            $profile->setUrl($me['url']);
            $profile->setFavorite(0);
            $profile->setSelected(0);
            $profile->setSite('pinterest');
            $em->persist($profile);
            $em->flush();
            $this->get("session")->set('created_profile_7', $profile->getId());
            $this->get("session")->remove('waiting_pinterest');
            $auth = true;
            $response_type = 'authenticated';
        }
        $response_type = $rtype ?: $response_type;
        $this->get('session')->remove('rtype');
        return $this->render('FrontendBundle:Autorize:pinterest.html.twig', array('auth' => $auth,'response_type'=>$response_type, 'profile' => $profile,'id'=>$user->getId()));
    }
}
