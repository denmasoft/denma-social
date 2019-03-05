<?php

namespace HootSuite\ApiBundle\Controller;
use Facebook\Facebook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class UsuarioController extends Controller
{
    public function userProfileAction(Request $request){
        $profile_id = $request->get('p_id');
        $id     = $request->get('id');
        $em     = $this->getDoctrine()->getManager();
        $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $object = null;
        switch ($profile->getSocialNetwork()->getUniquename()) {
            case 'TWITTER':
                $api    = $this->get('twitter');
                $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
                $result = $api->get('users/show', array("screen_name" => $id));
//$result = $this->getUserData(); 
//$result['html'] = $this->renderView('ApiBundle:Usuarios:usr_twitter.html.twig');
//$object = array(
//    "success" => true, 
//    "object"  => $result
//);
                if( isset( $result->screen_name ) && $result->screen_name == $id ){
                    $result->html = $this->renderView('ApiBundle:Usuarios:usr_twitter.html.twig');
                    $object = array(
                        "success" => true, 
                        "object"  => $result
                    );
                    
                }
                else{
                    $object = array("success" => false, "msg" => "No se ha podido obtener la información del usuario.");
                }
                break;
            case 'FACEBOOK':
;
                $fb = $this->get('facebookv2');
                $response = $fb->get("/$id", $profile->getToken());
//                $accessToken = $api->getAccessToken();
                $result = $response->getGraphNode()->asArray();
               /// var_dump($result);exit;
//$result = $this->fbUser();                 
                if( isset( $result['id'] ) && $result['id'] ){
                    $result['html'] = $this->renderView('ApiBundle:Usuarios:usr_facebook.html.twig');
                    $object = array(
                        "success" => true, 
                        "object"  => $result
                    );
                }
                else{
                    $object = array("success" => false, "msg" => "No se ha podido obtener la información del usuario.");
                }
                break;
            case 'LINKEDIN':
                $api = $this->get('linkedin');
                $api->setAccessToken($profile->getToken());
                $url_api = "/v1/people/id=$id:(id,firstName,lastName,location:(name),interests,industry,educations,specialties,formatted-name,headline,picture-url,public-profile-url,positions,date-of-birth,main-address,num-connections)";
                $view_user = 'ApiBundle:Usuarios:usr_linkedin.html.twig';
                if($request->get('ty') && $request->get('ty') == 'CMP'){
                    $url_api = "/v1/companies/id=$id:(id,name,logo-url,company-type,industries,website-url,num-followers,description,specialties,status,founded-year,employee-count-range,locations)";
                    $view_user = 'ApiBundle:Usuarios:usr_linkedin_cmp.html.twig';
                }
                $result = $api->api($url_api);
                if( isset( $result['id'] ) && $result['id'] ){
                    $result['html'] = $this->renderView($view_user);
                    $object = array(
                        "success" => true,
                        "object"  => $result
                    );
                }
                else{
                    $object = array("success" => false, "msg" => "No se ha podido obtener la información del usuario.");
                }
                break;
            case 'INSTAGRAM':
                $instagram = $this->get('instagram');
                $instagram->setAccessToken($profile->getToken());
                $result = $instagram->getUser($id)->data;

                if( isset($result )){
                    $result->html = $this->renderView('ApiBundle:Usuarios:usr_instagram.html.twig');
                    $object = array(
                        "success" => true,                        
                        "object"  => $result
                    );

                }
                else{
                    $object = array("success" => false, "msg" => "No se ha podido obtener la información del usuario.");
                }
                break;
        }
        
        return new Response(json_encode($object));
    }
    
    public function getUserData(){
        $data = array(
            "name" => "Ryan Sarver",
            "profile_image_url"=> "http://localhost/desarrollo/hootsuite/web/images/avatar.jpg",
            "created_at"=> "Mon Feb 26 18:05:55 +0000 2007",
            "location"=> "San Francisco, CA",
            "id_str"=> "795649",
            "default_profile"=> false,
            "favourites_count"=> 3162,
            "url"=> null,
            "id"=> 795649,
            "listed_count"=> 1586,
            "followers_count"=> 276334,
            "time_zone"=> "Pacific Time (US & Canada)",
            "description"=> "Director, Platform at Twitter. Detroit and Boston export. Foodie and over-the-hill hockey player. @devons lesser half",
            "statuses_count"=> 13728,
            "friends_count"=> 1780,
            "following"=> true,
            "show_all_inline_media"=> true,
            "screen_name"=> "rsarver"
          );
        
        return $data;
    }
    
    public function fbUser(){
        return json_decode('{
                "id": "891401077539321",
                "avatar" : "http://localhost/desarrollo/hootsuite/web/images/avatar.jpg",
                "bio": "Something about me..",
                "birthday": "05/12/1984",
                "education": [
                  {
                    "school": {
                      "id": "106330726072402",
                      "name": "UCI"
                    },
                    "type": "High School"
                  },
                  {
                    "school": {
                      "id": "110039752358547",
                      "name": "UCI"
                    },
                    "type": "College",
                    "year": {
                      "id": "141778012509913",
                      "name": "2008"
                    }
                  }
                ],
                "first_name": "Yandy",
                "gender": "male",
                "hometown": {
                  "id": "108533992510247",
                  "name": "Sagua la Grande, Cuba"
                },
                "languages": [
                  {
                    "id": "110867825605119",
                    "name": "English"
                  },
                  {
                    "id": "105636559469628",
                    "name": "Middle English"
                  }
                ],
                "last_name": "León Nuñez",
                "link": "https://www.facebook.com/app_scoped_user_id/891401077539321/",
                "location": {
                  "id": "115186165161937",
                  "name": "Havana, Cuba"
                },
                "locale": "es_ES",
                "name": "Yandy León Nuñez",
                "political": "Otra",
                "relationship_status": "Married",
                "significant_other": {
                  "id": "956875067662117",
                  "name": "Yaquelin Cintra Almaguer"
                },
                "updated_time": "2014-10-22T15:47:02+0000",
                "work": [
                  {
                    "employer": {
                      "id": "1387586028138912",
                      "name": "Radio Progreso"
                    },
                    "position": {
                      "id": "363175403745751",
                      "name": "Jefe Grupo informática"
                    },
                    "start_date": "2011-01-01"
                  },
                  {
                    "end_date": "2011-01-01",
                    "employer": {
                      "id": "107192309314273",
                      "name": "Universidad de las Ciencias Informáticas"
                    },
                    "position": {
                      "id": "148147798652262",
                      "name": "Progesor de Programación WEB"
                    },
                    "start_date": "2009-01-01"
                  }
                ]
              }', true);
    }
}
