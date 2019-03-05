<?php

namespace HootSuite\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MiembroController extends Controller
{
    public function loadAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($this->get('session')->get('user'));
        $organizations = $em->getRepository('BackofficeBundle:Usuario')->getUserOrganizations($this->get('session')->get('user'));
        return new Response(json_encode(array(
            "success"   => true, 
            "object"    => array(
                'id'    => $user->getId(),
                'avatar'=> "",
                'name'  => $user->getName(),
                'email' => $user->getEmail(),
                'organizations'=>$organizations
            )
        )));
    }
    public function synchronizeAction(Request $request)
    {
        $profile   = $request->get('profile');
        $em = $this->getDoctrine()->getManager();
        $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile);
        $object = null;
        switch ($profile->getSocialNetwork()->getUniquename()) {
        case 'TWITTER':
            $api    = $this->get('twitter');
            $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
            $twitter_user = $api->get('users/show');
            $profile->setUsername($twitter_user->screen_name);
            $profile->setUserid($twitter_user->user_id);
            $profile->setFullName($twitter_user->name);
            $profile->setAvatar($twitter_user->profile_image_url);
            $profile->setUrl('https://twitter.com/' . $twitter_user->screen_name);
            $profile->setSite('twitter');
            $em->persist($profile);
            $em->flush();
            $object = array("success"   => true, "msg" => "Perfil sincronizado.");
            break;
        case 'FACEBOOK':
            $fb = $this->get('facebookv2');
            $fb->setDefaultAccessToken($profile->getToken());
            $response = $fb->get('/me?fields=id,name,email,picture,link', $profile->getToken());
            $facebookUser = $response->getGraphUser();
            $profile->setUsername($facebookUser->getName());
            $profile->setUserid($facebookUser->getId());
            $profile->setFullName($facebookUser->getName());
            $profile->setAvatar($facebookUser->getPicture()->getUrl());
            $profile->setUrl($facebookUser->getLink());
            $profile->setSite('facebook');
            $em->persist($profile);
            $em->flush();
            $object = array("success"=> true, "msg" => "Perfil sincronizado.");
            break;
        case 'LINKEDIN':
            $linkedin = $this->get('linkedin');
            $linkedin->setAccessToken($profile->getToken());
            $li_user = $linkedin->getUser();
            $formattedName = $li_user["firstName"].' '.$li_user["lastName"];
            $profile->setUsername($formattedName);
            $profile->setUserid($li_user["id"]);
            $profile->setFullName($formattedName);
            $profile->setAvatar(isset($li_user["siteStandardProfileRequest"]['url']) ? $li_user["siteStandardProfileRequest"]['url'] : 'https://static.licdn.com/scds/common/u/img/icon/icon_no_photo_40x40.png');
            $profile->setUrl($li_user["siteStandardProfileRequest"]['url']);
            $profile->setSite('linkedin');
            $em->persist($profile);
            $em->flush();
            $object = array("success"=> true, "msg" => "Perfil sincronizado.");
            break;
        case 'WORDPRESS':
            $wordpress = $this->get('wordpress');
            $wordpress->setAccessToken($profile->getToken());
            $wp_user = $wordpress->api('/me');
            $profile->setUsername($wp_user->username);
            $profile->setUserid($wp_user->ID);
            $profile->setFullName($wp_user->display_name);
            $profile->setAvatar(isset($wp_user->avatar_URL) ? $wp_user->avatar_URL : '');
            $profile->setUrl($wp_user->profile_URL);
            $profile->setSite($wp_user->meta->links->site);
            $em->persist($profile);
            $em->flush();
            $object = array("success"=> true, "msg" => "Perfil sincronizado.");
            break;
        case 'INSTAGRAM':
            $instagram = $this->get('instagram');
            $instagram->setAccessToken($profile->getToken());
            $instagramUser = $instagram->getUser();
            $profile->setUsername($instagramUser->data->username);
            $profile->setUserid($instagramUser->data->id);
            $profile->setFullName($instagramUser->data->full_name);
            $profile->setAvatar($instagramUser->data->profile_picture);
            $profile->setUrl($instagramUser->data->profile_picture);
            $profile->setSite('instagram');
            $em->persist($profile);
            $em->flush();
            $object = array("success"=> true, "msg" => "Perfil sincronizado.");
            break;
        case 'PINTEREST':
            $pinterest = $this->get('pinterest');
            $pinterest->auth->setOAuthToken($profile->getToken());
            $me = $pinterest->users->me(array('fields' => 'username,first_name,last_name,image[small],url'
            ))->toArray();
            $profile->setUsername($me['username']);
            $profile->setUserid($me['id']);
            $profile->setFullName($me['first_name'].' '.$me['last_name']);
            $profile->setAvatar($me['image']['small']['url']);
            $profile->setUrl($me['url']);
            $profile->setSite('pinterest');
            $em->persist($profile);
            $em->flush();
            $object = array("success"=> true, "msg" => "Perfil sincronizado.");
            break;
    }
        return new Response(json_encode($object));
    }
    public function updateAction(Request $request)
    {
        $id   = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('BackofficeBundle:Usuario')->find($id);
        $fullName = $request->request->get('fullName');
        $password = $request->request->get('password');
        $userEmail = $request->request->get('userEmail');
        $desc = $request->request->get('desc');
        if($password!==null)
        {
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $user->setSalt(md5(time()));
            $passwordCodificado = $encoder->encodePassword($password,$user->getSalt());
            $user->setPassword($passwordCodificado);
        }
        $user->setName($fullName);
        $user->setEmail($userEmail);
        $user->setDescription($desc);
        $em->persist($user);
        $em->flush();
        $object = array("success"=> true, "msg" => "Datos actualizados.");
        return new Response(json_encode($object));
    }
}
