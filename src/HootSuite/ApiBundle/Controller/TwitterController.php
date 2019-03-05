<?php

namespace HootSuite\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class TwitterController extends Controller
{
    
    public function retweetAction(Request $request)
    {   
        $profile_id = $request->get('p_id');
        $id         = $request->get("id");   
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $response   = $api->post("statuses/retweet/$id", array("id" => $id));
        if( isset($response->id_str) && $response->id_str ){
            $response = new Response('{"success" : true}');
        }
        else{
            $response = new Response('{"success" : false, "msg" : "El status no ha podido ser retweeteado."}');
        }
        return $response;
    }
    public function spammerAction(Request $request)
    {    
        $profile_id = $request->get('p_id');
        $id         = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $response = $result = $api->post('users/report_spam', array('user_id' => $id));
        if( isset($response->id_str) && $response->id_str ){
            $response = new Response('{"success" : true}');
        }
        else{
            $response = new Response('{"success" : false, "msg" : "No se ha podido registrar como spammer."}');
        }
        return $response;
    }
    public function favoriteAction(Request $request)
    {    
        $profile_id = $request->get('p_id');
        $id         = $request->get('id');
        $em         = $this->getDoctrine()->getManager();
        $profile    = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api        = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $response = $result = $api->post('favorites/create', array('id' => $id));
        if( isset($response->id_str) && $response->id_str ){
            $response = new Response('{"success" : true, "msg" => "Agregado a favoritos."}');
        }
        else{
            $response = new Response('{"success" : false, "msg" : "No se ha podido agregar a favoritos."}');
        }
        return $response;
    }
    public function followAction(Request $request)
    {    
        $profile_id = $request->get('p_id');
        $follow     = $request->get('follow');
        $id         = $request->get('id');
        $em         = $this->getDoctrine()->getManager();
        $profile    = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api        = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $method = 'friendships/destroy';
        if( $follow ){
            $method = 'friendships/create';
        }
        $response = $api->post($method, array("user_id" => $id));
        if( isset($response->id_str) && $response->id_str ){
            $response = new Response('{"success" : true}');
        }
        else{
            $response = new Response('{"success" : false, "msg" : "No se ha podido realizar el seguimiento."}');
        }
        return $response;
    }
    public function createListAction(Request $request)
    {
        $profile_id = $request->get('p_id');
        $name       = $request->get('name');
        $mode       = $request->get('mode');
        $desc       = $request->get('description');
        if( $name && $mode ){
            $em = $this->getDoctrine()->getManager();
            $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
            $api    = $this->get('twitter');
            $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
            $response = $api->post("lists/create", array("name" => $name, "mode" => $mode, "description" => $desc));
            if( isset($response->id_str) && $response->id_str ){
                return new Response(json_encode(array(
                    "success"   => true,
                    "object"    => array('name'  => $name, 'id_str' => $response->id_str, 'mode' => $mode, 'member_count' => $response->member_count),
                )));
            }
            return new Response('{"success" : false, "msg" : "No se ha podido agregar a favoritos."}');
            
//return new Response(json_encode(array(
//    "success"   => true,
//    "object"    => array('name'  => 'Lista nueva', 'id_str' => '100', 'mode' => 'private', 'member_count' => '0')  ,
//)));
        }
    }
    public function createListMemberAction(Request $request)
    {   
        $profile_id = $request->get('p_id');
        $id         = $request->get('id');
        $u_id       = $request->get('u_id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $response = $api->post("lists/members/create", array("list_id" => $id, "user_id" => $u_id));
        if( isset($response->id_str) && $response->id_str ){
            return new Response(json_encode(array(
                "success"   => true
            )));
        }
        return new Response('{"success" : false, "msg" : "No se ha podido agregar el usuario a la lista."}');
        
    }
    public function showConversationAction(Request $request)
    {    
        $profile_id = $request->get('p_id');
        $id         = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        
        $status = $api->get("statuses/show", array("id" => $id));
        if( $status->id_str ){
            $params = array(
                'q'         => 'to:' . $status->user->screen_name,
                'count'     => 100,
                'since_id'  => $id
            );
            $feed = $api->get("search/tweets", $params);
            $comments = array();
            for ($index = 0; $index < count($feed->statuses); $index++) {
                if ($feed->statuses[$index]->in_reply_to_status_id_str == $id) {
                    array_push($comments, $feed->statuses[$index]);
                }
            }
            array_push($comments, $status);
//$comments = $this->testResult();        
            return new Response(json_encode(array(
                "success"   => true,
                "object"    => $comments
            )));
        }
        else{
            return new Response(json_encode(array(
                "success"   => false,
                "object"    => null,
                "msg"       => "El status enviado no es correcto."
            )));
        }
            
    }
    public function userCronologyAction(Request $request)
    {    
        $profile_id = $request->get('p_id');
        $id         = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $response = $api->get("statuses/user_timeline", array("user_id" => $id));
//$response = $this->testResult();
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $response,
            "html"      => $this->renderView('ApiBundle:Usuarios:usr_twitter_crono.html.twig', array("profile_showing"   => true))
        )));
    }
    public function userMentionsAction(Request $request)
    {    
        $profile_id = $request->get('p_id');
        $id         = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $response = $api->get("search/tweets", array("q" => "@$id"));
        if( isset($response->statuses) && count($response->statuses) ){
            $response = $response->statuses;
        }
//$response = $this->testResult();
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $response,
            "html"      => $this->renderView('ApiBundle:Usuarios:usr_twitter_mentions.html.twig', array("profile_showing"   => true))
        )));
    }
    public function userFavoritesAction(Request $request)
    {    
        $profile_id = $request->get('p_id');
        $id         = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $response = $api->get("favorites/list", array("user_id" => $id));
//$response = $this->testResult(); 
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $response,
            "html"      => $this->renderView('ApiBundle:Usuarios:usr_twitter_favorites.html.twig', array("profile_showing"   => true))
        )));
    }
    public function getRetweetsAction(Request $request)
    {    
        $profile_id = $request->get('p_id');
        $id         = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $statuses = $api->get("statuses/retweets/$id", array("id" => $id));
        $response =  array(
            "success"   => true,
            "object"    => $statuses,
            "msg"       => "No se han podido obtener los usuarios que retuitearon."
        );
       
        return new Response(json_encode($response));
    }
    public function hashsearchAction(Request $request)
    {    
        $profile_id = $request->get('p_id');
        $hash       = $request->get('hash');
        $min        = $request->get('min_id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $params = array("q" => $hash, 'count' => 20);
        if( $min ){
            $params['max_id'] = $this->decrement_string($min);
        }
        $response = $api->get("search/tweets", $params);

        if( isset($response->statuses) && count($response->statuses) ){
            $response = $response->statuses;
        }
//$response = $this->testResult();         
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $response,
            "html"      => $this->renderView('ApiBundle:Searchs:tw_search.html.twig')
        )));
    }
    public function searchUsersAction(Request $request)
    {
        $profile_id = $request->get('p_id');
        $hash       = $request->get('hash');
        $min        = $request->get('min_id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $params = array("q" => $hash, 'count' => 20);
        if( $min ){
            $params['max_id'] = $this->decrement_string($min);
        }
        $response = $api->get("users/search", $params);

        if( isset($response->statuses) && count($response->statuses) ){
            $response = $response->statuses;
        }
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $response,
            "html"      => $this->renderView('ApiBundle:Searchs:tw_search.html.twig')
        )));
    }
    public function listsSubscriptions(Request $request)
    {
        $prof_id    = $request->get('p_id');
        $em     = $this->getDoctrine()->getManager();
        $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($prof_id);
        switch ($profile->getSocialNetwork()->getUniquename()) {
            case 'TWITTER':
                $api    = $this->get('twitter');
                $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
                $result = $api->get('lists/subscriptions', array('user_id' => $profile->getUserid()));
                break;
            
            case 'FACEBOOK': 
                break;
        }
    
        return new Response(json_encode(array("success" => true, "object" => $result)));
        
//return new Response(json_encode(array("success" => true, "object" => array(
//    array('name'  => 'Lista 1', 'id_str' => '1', 'mode' => 'private', 'member_count' => '1'),
//    array('name'  => 'Lista 2', 'id_str' => '2', 'mode' => 'private', 'member_count' => '3'),
//    array('name'  => 'Lista 3', 'id_str' => '3', 'mode' => 'public', 'member_count' => '4'),
//    array('name'  => 'Lista 4', 'id_str' => '4', 'mode' => 'public', 'member_count' => '1'),
//    array('name'  => 'Lista 5', 'id_str' => '5', 'mode' => 'public', 'member_count' => '0'),
//    array('name'  => 'Lista 6', 'id_str' => '6', 'mode' => 'private', 'member_count' => '1'),
//    array('name'  => 'Lista 7', 'id_str' => '7', 'mode' => 'public', 'member_count' => '10'),
//    array('name'  => 'Lista 8', 'id_str' => '8', 'mode' => 'private', 'member_count' => '1')
//))));
    }
    public function testResult(){
        $text = "Lorem ipsum dolor sit amet,sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
        return array(
            array(
                'id_str'    => 1,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => '#QueBonito ' .str_shuffle($text).' @ylnunez http://google.com',
                'retweet_count'    => rand(0,100)
            ),
            array(
                'id_str'    => 21,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            ),
            array(
                'id_str'    => 3,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            ),
            array(
                'id_str'    => 4,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            ),
            array(
                'id_str'    => 5,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            ),
            array(
                'id_str'    => 6,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            ),
            array(
                'id_str'    => 7,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            ),
            array(
                'id_str'    => 7,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            ),
            array(
                'id_str'    => 7,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            ),
            array(
                'id_str'    => 7,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            ),
            array(
                'id_str'    => 7,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'recipient' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            )
        );
    }
    public function decrement_string($str) {
        // 1 and 0 are special cases with this method
        if ($str == 1 || $str == 0)
            return (string) ($str - 1);

        // Determine if number is negative
        $negative = $str[0] == '-';
        // Strip sign and leading zeros
        $str = ltrim($str, '0-+');
        // Loop characters backwards
        for ($i = strlen($str) - 1; $i >= 0; $i--) {

            if ($negative) { // Handle negative numbers
                if ($str[$i] < 9) {
                    $str[$i] = $str[$i] + 1;
                    break;
                } else {
                    $str[$i] = 0;
                }
            } else { // Handle positive numbers
                if ($str[$i]) {
                    $str[$i] = $str[$i] - 1;
                    break;
                } else {
                    $str[$i] = 9;
                }
            }
        }
        return ($negative ? '-' : '') . ltrim($str, '0');
    }
    public function trendTopicsAction(Request $request)
    {
        $profile_id = $request->get('p_id');
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $api    = $this->get('twitter');
        $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
        $params = array("id" => 1);
        $response = $api->get("trends/place", $params);
        if( isset($response->statuses) && count($response->statuses) ){
            $response = $response->statuses;
        }
//$response = $this->testResult();
        return new Response(json_encode(array(
            "success"   => true,
            "object"    => $response,
            "html"      => ""
        )));
    }
}
