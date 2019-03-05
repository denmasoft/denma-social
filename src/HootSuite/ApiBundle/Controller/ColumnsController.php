<?php

namespace HootSuite\ApiBundle\Controller;
use Facebook\Facebook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use HootSuite\BackofficeBundle\Entity\TabColumn;
use HootSuite\BackofficeBundle\Entity\TabColumnTerms;

class ColumnsController extends Controller
{
    private function parseEventsData($data,$event)
    {
        $events_array = array();
        for($x=0; $x<count($data); $x++){

            $start_date = date( 'l, F d, Y', strtotime($data[$x]['start_time']));
  
            // in my case, I had to subtract 9 hours to sync the time set in facebook
            $start_time = date('g:i a', strtotime($data[$x]['start_time']) - 60 * 60 * 9);
              
            $pic_big = isset($data[$x]['cover']['source']) ? $data[$x]['cover']['source'] : "https://fbcdn-profile-a.akamaihd.net/static-ak/rsrc.php/v2/yE/r/tKlGLd_GmXe.png";
             
            $eid = $data[$x]['id'];
            $name = $data[$x]['name'];
            $owner = $data[$x]['owner']['name'];
            $description = isset($data[$x]['description']) ? $data[$x]['description'] : "";
             
            // place
            $place_name = isset($data[$x]['place']['name']) ? $data[$x]['place']['name'] : "";
            $city = isset($data[$x]['place']['location']['city']) ? $data[$x]['place']['location']['city'] : "";
            $country = isset($data[$x]['place']['location']['country']) ? $data[$x]['place']['location']['country'] : "";
            $zip = isset($data[$x]['place']['location']['zip']) ? $data[$x]['place']['location']['zip'] : "";
             
            $location=$place_name;
             
            /*if($place_name && $city && $country && $zip){
                $location="$place_name, $city, $country, $zip";
            }
            else
            {
                $location="Indefinido";
            }*/
            $events_array[$x]['picture'] = $pic_big;
            $events_array[$x]['what'] = $name;
            $events_array[$x]['when'] = $start_date.' a las '.$start_time;
            $events_array[$x]['where'] = $location;
            $events_array[$x]['description'] = $description;
            $events_array[$x]['event_link'] = 'https://www.facebook.com/events/'.$eid.'/'; 
            $events_array[$x]['event'] = $event;  
            $events_array[$x]['owner'] = $owner;                      
        }
        
        return $events_array;
        
    }
    public function loadAction(Request $request)
    {
        // buscar columna por el id
        $id     = $request->get('id');
        $max_id = $request->get('max_id');
        $min_id = $request->get('min_id');
        $em     = $this->getDoctrine()->getManager();
        $col    = $em->getRepository('BackofficeBundle:TabColumn')->find($id);
        $method = $col->getColumn()->getApi();       
        $result = null;
        $profile= $col->getProfileUsuario();
        $parameters = array();
        $template = '';
        $max = $min = 0;
        switch ($col->getColumn()->getSocialNetwork()->getUniquename()) {
            case 'TWITTER':
                $api    = $this->get('twitter');
                $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
                //terminos de busqueda si tiene.
                $terms = $col->getTerms();
                foreach( $terms as $t ){
                    $parameters[$t->getName()] = $t->getValue();
                }
                // otros paramatros
                if( $col->getColumn()->getCount() ){
                    $parameters["count"] = $col->getColumn()->getCount();
                }
                if( $max_id ){
                    $parameters["since_id"] = $max_id;
                }
                if( $min_id ){
                    $parameters["max_id"] = $this->decrement_string($min_id);
                }
                if( $col->getColumn()->getUserId() ){
                    $parameters["user_id"] = $profile->getUserid();
                }
                //por tipos de columna
                if( $col->getColumn()->getType() == 'PENDING' ){
                    $pendings = $em->getRepository('BackofficeBundle:Message')->findPendings($col->getProfileUsuario()->getId());
                    $messages = array();
                    foreach( $pendings as $msg ){
                        $messages[] = $msg->toArray($col->getProfileUsuario());
                    }
                    $result = $messages;
                }
                else if( $col->getColumn()->getType() == 'SEARCH' ){
                    $result = $api->get($method, $parameters);
                    if( isset($result->statuses) && count($result->statuses) ){
                        $result = $result->statuses;
                    }
                }
                else if( $col->getColumn()->getType() == 'FOLLOWERS' ){
                    $result = $api->get($method, $parameters);
                    if( isset($result->users) && count($result->users) ){
                        $result = $result->users;
                    }
                }
                else{
                    $result = $api->get($method, $parameters);
                }
                
//$result = $this->testResult();
                if( count($result) && isset($result[0]->id_str) && isset($result[count($result)-1]->id_str) ){
                    $max = $result[0]->id_str;
                    $min = $result[count($result)-1]->id_str;
                }
//$max = $result[0]['id_str'];
//$min = $result[count($result)-1]['id_str'];
                
                break;
            case 'FACEBOOK':
                    $em = $this->getDoctrine()->getManager();
                    /*$fb = new Facebook([
                        'app_id' => '494431310714400',
                        'app_secret' => '77c12744c08512239c6933b09ea64743',
                        'default_graph_version' => 'v2.3',
                    ]);*/
                    $fb = $this->get('facebookv2');
                    $api_result = NULL;
                    $op = "?";
                    // otros paramatros
                    if( $col->getColumn()->getCount() ){
                        $method .= $op.'limit='.$col->getColumn()->getCount();
                        $op = '&';
                    }
                    if( $max_id ){
                        $method .= $op.'until='.$max_id;
                        $op = '&';
                    }
                    if( $min_id ){
                        $method .= $op.'since='.$min_id;
                        $op = '&';
                    }
                    if( $col->getColumn()->getUserId() ){
                        $method = str_replace('{object}', $profile->getUserid(), $method);
                    }

                    if( strstr($col->getColumn()->getType(), 'PENDING') ){
                        $pendings = $em->getRepository('BackofficeBundle:Message')->findPendings($col->getProfileUsuario()->getId());
                        $messages = array();
                        foreach( $pendings as $msg ){
                            $messages[] = $msg->toArray($col->getProfileUsuario());
                        }
                        $result = $messages;
                    }
                    else if( strstr($col->getColumn()->getType(), 'SEARCH') ){
                        $terms = $col->getTerms();
                        foreach( $terms as $t ){
                            $method .= $op.$t->getName().'='.$t->getValue();
                        }
                        $op = '&';
                        //var_dump($profile->getToken());exit;
                        $fb->setDefaultAccessToken($profile->getToken());
                        $response = $fb->get('/search?q=comida&type=ad');
                        $result = $api_result->getGraphEdge()->asArray() ? $api_result->getGraphEdge()->asArray() : array();
                    }
                    else if( strstr($col->getColumn()->getType(), 'PAGE') ){
                        $terms = $col->getTerms();
                        $page_id = null;
                        $method = '';
                        if( $col->getColumn()->getCount() ){
                            $method .= '&limit='.$col->getColumn()->getCount();
                            $op = '&';
                        }
                        if( $max_id ){
                            $method .= $op.'until='.$max_id;
                        }
                        foreach( $terms as $t ){
                            if($t->getName()==='page')
                            {
                                $page_id = $t->getValue();
                            }
                        }
                        $api_result = $fb->get("$page_id/feed?fields=id,message,link,full_picture,story,created_time,picture,likes,comments$method",$profile->getToken());

                        $result = $api_result->getGraphEdge()->asArray() ? $api_result->getGraphEdge()->asArray() : array();
                        $paging= $api_result->getGraphEdge()->getMetaData()['paging'];
                        $previous = $paging['previous'];
                        $next = $paging['next'];
                        $min = $previous;
                        $max = $next;
                    }
                    else if( $col->getColumn()->getType() == 'COMMENTS' ){
                        $terms = $col->getTerms();
                        $object = $comment = null;
                        foreach( $terms as $t ){
                            $object = $t->getValue();
                        }
                        if( $object ){
                            //var_dump($object);exit;
                            $api_result = $fb->get("/$object/comments",$profile->getToken());
                            //$method = str_replace('{object}', $object, $method);
                           // $api_result = $fb->get($method,$profile->getToken());
                        }
                        $result = $api_result->getGraphEdge()->asArray() ? $api_result->getGraphEdge()->asArray() : array();
                        array_unshift($result, $comment);
                    }
                    else if($col->getColumn()->getName()=="Eventos")
                    {
                        $since = strtotime(date('Y-m-d',strtotime('-1 month')));
                        $until = strtotime(date('Y-m-d'));
                        $options='owner,id,name,description,place,timezone,start_time,cover';
                        $events_status = ['attending', 'created', 'declined', 'maybe', 'not_replied'];
                        $count = 0;
                        $api_data;
                        $temp;
                        $x = 0;
                        $attending = $fb->request('GET', 'me/events/attending?fields=name,id,start_time,end_time,picture,description,category,type,owner,rsvp_status', array(), $profile->getToken());
                        $not_replied = $fb->request('GET', 'me/events/not_replied?fields=name,id,start_time,end_time,picture,description,category,type,owner,rsvp_status', array(), $profile->getToken());
                        $created = $fb->request('GET', 'me/events/created?fields=name,id,start_time,end_time,picture,description,category,type,owner,rsvp_status', array(), $profile->getToken());
                        $declined = $fb->request('GET', 'me/events/declined?fields=name,id,start_time,end_time,picture,description,category,type,owner,rsvp_status', array(), $profile->getToken());
                        $maybe = $fb->request('GET', 'me/events/maybe?fields=name,id,start_time,end_time,picture,description,category,type,owner,rsvp_status', array(), $profile->getToken());
                        $api_result = $fb->sendBatchRequest(array($attending,$not_replied,$created,$declined,$maybe),$profile->getToken());
                       //var_dump($api_result->getGraphEvent()->asArray());exit;
                        //$result = $api_result->getGraphEdge()->asArray() ? $api_result->getGraphEdge()->asArray() : array();
                        foreach($api_result->getGraphEvent()->asArray() as $event){
                            $ed = json_decode($event['body']);
                            $event_data = $ed->data;
                            foreach($event_data as $ev)
                            {
                                $start_date = date( 'l, F d, Y', strtotime($ev->start_time));
                                $start_time = date('g:i a', strtotime($ev->start_time) - 60 * 60 * 9);
                                $temp[$x]['picture'] = $ev->picture->data->url;
                                $temp[$x]['what'] = $ev->name;
                                $temp[$x]['when'] = $start_date.' a las '.$start_time;
                                $temp[$x]['description'] = $ev->description;
                                $temp[$x]['event_link'] = 'https://www.facebook.com/events/'.$ev->id;
                                $temp[$x]['event'] = ($ev->rsvp_status=='not_replied') ? 'Respond' : 'Attend';
                                $temp[$x]['owner_link'] = 'http://www.facebook.com/event.php?eid='.$ev->id;
                                $temp[$x]['owner'] = $ev->owner->name;
                                $x++;
                            }
                        }
                        /*foreach($events_status as $event)
                        {
                            $api_result = $fb->get("me/events/$event",$profile->getToken());
                            //$api_data['name'] = $event;
                            $events_array = $this->parseEventsData($api_result->getGraphEdge()->asArray(),($event=='not_replied') ? 'Respond' : 'Attend');
                            
                            if($events_array!=null)
                            {
                                foreach ($events_array as $value) {
                                   $temp[$x]['picture'] = $value['picture'];
                                   $temp[$x]['what'] = $value['what'];
                                   $temp[$x]['when'] = $value['when'];
                                   $temp[$x]['where'] = $value['where'];
                                   $temp[$x]['description'] = $value['description'];
                                   $temp[$x]['event_link'] = $value['event_link'];
                                   $temp[$x]['event'] = $value['event'];  
                                   $temp[$x]['owner'] = $value['owner'];
                                   $x++;
                               }   
                            }
                        }*/
                        $api_data['data'] = $temp ?: null;
                        $result = isset($api_data['data']) ? $api_data['data']: array();
                    }
                    else{
                        $api_result = $fb->get($method,$profile->getToken());
                        //var_dump($api_result->getGraphEdge()->asArray());
                        $result = $api_result->getGraphEdge()->asArray() ? $api_result->getGraphEdge()->asArray() : array();
                    }
                    /*if(isset($api_result)){
                        $max = explode('until=', $api_result['paging']['next']);
                        $max = isset($max[1]) ? $max[1] : NULL;
                    }
                    if(isset($api_result['paging']['previous'])){
                        $min = explode('since=', $api_result['paging']['previous']);
                        $min = isset($min[1]) ? $min[1] : NULL;
                    }*/
                break;
            case 'LINKEDIN':  
                    $linkedIn = $this->get('linkedin');
                    $linkedIn->setAccessToken($profile->getToken());
                    $api_result = NULL;
                    $terms = $col->getTerms();
                    foreach( $terms as $t ){
                        $parameters[$t->getName()] = $t->getValue();
                    }
                    // otros paramatros
                    if( $col->getColumn()->getCount() ){
                        $parameters["count"] = $col->getColumn()->getCount();
                    }
                    if( $max_id ){
                        $parameters["start"] = $max_id;
                    }
                    if( $min_id ){
                        $parameters["max_id"] = $this->decrement_string($min_id);
                    }
                    if( $col->getColumn()->getUserId() ){
                        $method = str_replace('/~/', '/id='.$profile->getUserid().'/', $method);
                    }

                    if( strstr($col->getColumn()->getType(), 'PENDING') ){
                        $pendings = $em->getRepository('BackofficeBundle:Message')->findPendings($col->getProfileUsuario()->getId());
                        $messages = array();
                        foreach( $pendings as $msg ){
                            $messages[] = $msg->toArray($col->getProfileUsuario());
                        }
                        $result = $messages;
                    }
                    else if( strstr($col->getColumn()->getType(), 'SEARCH') ){
                        $terms = $col->getTerms();
                        $method = str_replace('{type}', $parameters['type'], $method);
                        if( $parameters['type'] == 'job' ){
                            $method .= ':(jobs:(id,customer-job-code,active,posting-date,expiration-date,posting-timestamp,expiration-timestamp,company:(id,name),position:(title,location,job-functions,industries,job-type,experience-level),skills-and-experience,description-snippet,description,salary,job-poster:(id,first-name,last-name,headline),referral-bonus,site-job-url,location-description))';
                        }
                        else if( $parameters['type'] == 'people' ){
                            $method .= ':(people:(id,first-name,last-name,picture-url,headline),num-results)';
                        }
                        else if( $parameters['type'] == 'compamy' ){
                            $method .= ':(facets,companies:(id,name,universal-name,website-url,industries,status,logo-url,blog-rss-url,twitter-id,employee-count-range,specialties,locations,description,stock-exchange,founded-year,end-year,num-followers))';
                        }
                        $template = str_replace('{type}', $parameters['type'], $col->getColumn()->getTemplate());
                        $parameters['keyworks'] = $parameters['q'];
                        $api_result = $linkedIn->api($method, $parameters);
                    }
                    else{
                        if(strstr($method, '?scope=self')){
                            $parameters['scope'] = 'self';
                            $method = str_replace('?scope=self', '', $method);
                        }
                        $api_result = $linkedIn->api('GET',$method);
//$api_result = $this->resultLi();
                        $result = isset($api_result['values']) ? $api_result['values'] : array();
                    }

                    if( isset($api_result['_start']) && isset($api_result['_total']) ){
                        $max = $api_result['_start'];
                        $min = $api_result['_total'];
                    }
                break;                
            case 'WORDPRESS':
                    $wordpress = $this->get('wordpress');
                    $path = parse_url($profile->getSite(), PHP_URL_PATH);
                    $site_url = explode('/', $path);
                    $site_id = end($site_url);
                    $method = str_replace('{site}', $site_id, $method);
                    
//                    $wordpress->setAccessToken($profile->getToken());
                    $terms = $col->getTerms();
                    foreach( $terms as $t ){
                        $parameters[$t->getName()] = $t->getValue();
                    }
                    // otros paramatros
                    if( $col->getColumn()->getCount() ){
                        $parameters["number"] = $col->getColumn()->getCount();
                    }
                    if( $max_id ){
                        $parameters["offset"] = $max_id;
                    }
                    if( $min_id ){
                        $parameters["page"] = $this->decrement_string($min_id);
                    }

                    if( strstr($col->getColumn()->getType(), 'SEARCH') ){
                        $parameters["search"] = '';
                        $terms = $col->getTerms();
                        foreach( $terms as $t ){
                            $parameters["search"] .= $t->getValue();
                        }
                    }
                    /*else if($col->getColumn()->getType() == 'BOX-SEND'){
                        $parameters["author"] .= $profile->getUsuario()->getId();
                    }*/
                    
                    $api_result = $wordpress->api($method, $parameters);
                    $result = isset($api_result->posts) ? $api_result->posts : array();
                    
                    if( isset($api_result->_start) && isset($api_result->_total) ){
                        $max = $api_result->_start;
                        $min = $api_result->_total;
                    }
                    
                    break;
            case 'INSTAGRAM':
                $instagram = $this->get('instagram');               
                $instagram->setAccessToken($profile->getToken());                
                if($method=='feed')
                {
                    $result = $instagram->getUserFeed(30)->data;                    
                }
                elseif($method=='followed-by')
                {
                 $result = $instagram->getUserFollower()->data;
                }
                elseif($method=='follows')
                {
                 $result = $instagram->getUserFollows()->data;
                }
                elseif($method=='liked')
                {
                 $result = $instagram->getUserLikes(30)->data;
                }
                elseif($method=='popular')
                {
                 $result = $instagram->getPopularMedia()->data;
                }
                elseif($method=='my_posts')
                {
                 $result = $instagram->getUserMedia('self',30)->data;
                }
                elseif($method=='user')
                {
                    $terms = $col->getTerms();
                    $search = null;
                    foreach( $terms as $t ){
                            $search .= $t->getValue();
                        }
                 $result = $instagram->searchUser($search,30)->data;
                }
                elseif($method=='hashtag')
                {
                    $terms = $col->getTerms();
                    $search = null;
                    foreach( $terms as $t ){
                        $search .= $t->getValue();
                    }
                    $result = $instagram->getTagMedia($search,30)->data;
                }

            case 'PINTEREST':
                $pinterest = $this->get('pinterest');
                $pinterest->setAccessToken($profile->getToken());
                if($method=='mePins')
                {
                    $result = $pinterest->users->getMePins()->all();

                }
                elseif($method=='boards')
                {
                    $result = $pinterest->users->getMeBoards()->all();                    
                }
                elseif($method=='meFollowers')
                {
                    $result = $pinterest->users->getMeFollowers();
                }
                elseif($method=='meFollows')
                {
                    $result = $pinterest->following->users()->all();
                }
                elseif($method=='liked')
                {
                    $result = $pinterest->users->getMeLikes();
                }
            default:
                break;
        }
        return new Response(json_encode(array(
            "success"   => true,
            "template"  => $template?$template:$col->getColumn()->getTemplate().'.html.twig',
            "object"    => $result,
            "html"      => $this->renderView('ApiBundle:Columns:'.($template?$template:$col->getColumn()->getTemplate()).'.html.twig'),
            "max_id"    => $max,
            "min_id"    => $min
        )));
    }
    
    public function addAction(Request $request)
    {
        $tab_id     = $request->get('tab');
        $col_id     = $request->get('col');
        $profile_id = $request->get('profile');
        $type       = $request->get('type');
        $terms      = $request->get('terms');
     
        $em     = $this->getDoctrine()->getManager();
        $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($profile_id);
        $col_tab = new TabColumn();
        if($col_id){
            $col    = $em->getRepository('BackofficeBundle:Column')->find($col_id);
        }
        else{
            $col    = $em->getRepository('BackofficeBundle:Column')->findOneBy(array(
                'type' => $type,
                'social_network'   => $profile->getSocialNetwork()
            ));
        }
        $col_tab->setColumn($col);
        
        $tab    = $em->getRepository('BackofficeBundle:Tab')->find($tab_id);
        
        $col_tab->setTab($tab);
        $col_tab->setProfileUsuario($profile);        
        $col_tab->setName($col->getDescription() ? $col->getDescription() : $col->getName());
        
        $position = $em->getRepository('BackofficeBundle:Tab')->countColumns($tab_id);
        $col_tab->setPosition($position+1);
        $em->persist($col_tab);
        
        if( $terms ){
            $terms_str = '';
            foreach($terms as $param=>$value){
                $new_term = new TabColumnTerms();
                $new_term->setName($param);
                $new_term->setValue($value);
                $new_term->setTabColumn($col_tab);
                $em->persist($new_term);
                $col_tab->addTerm($new_term);
                if($param!=='page')
                {
                    $terms_str .= $value.',';
                }
            }
            $name = $col->getName().': '.trim($terms_str,',');
            $col_tab->setName($name);
            $em->persist($col_tab);
        }
        $em->flush();
        
        return new Response(json_encode(array("success" => true, "object" => array(
            "id"        => $col_tab->getId(), 
            "name"      => $col_tab->getName(), 
            "social"    => $profile->getSocialNetwork()->getId(),
            "type"      => $col_tab->getColumn()->getType(),
            "prof_name" => $profile->getUsername(),
            "glyphicon" => $profile->getSocialNetwork()->getGlyphicon(),
            "profile"   => $profile_id,
            "column"    => $col_id,
            "tab"       => $tab_id,
            "terms"     => $terms
            ))
        )) ;
    }

    public function delAction(Request $request)
    {
        $id     = $request->get('id');
        $em     = $this->getDoctrine()->getManager();
        $col    = $em->getRepository('BackofficeBundle:TabColumn')->find($id);
        $em->remove($col);
        $em->flush();
        return new Response(json_encode(array("success" => true, "message" => "Columna eliminada.")));
    }

    public function searchAction(Request $request)
    {
        $prof_id    = $request->get('p_id');
        $em     = $this->getDoctrine()->getManager();
        $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($prof_id);
        $result = null;
        switch ($profile->getSocialNetwork()->getUniquename()) {
            case 'TWITTER':
                $api    = $this->get('twitter');
                $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
                $result = $api->get('trends/place', array('id' => 1));
                if( isset($result[0]->trends) && count($result[0]->trends) ){
                    $result = $result[0]->trends;
                }
                break;
            
            case 'FACEBOOK': 
                break;
        }
        
        return new Response(json_encode(array("success" => true, "object" => $result)));
    
//        return new Response(json_encode(array("success" => true, "object" => array(
//            array('name'  => '#Result 1', 'query' => 'query1'),
//            array('name'  => 'Result 2', 'query' => 'query2'),
//            array('name'  => 'Result 3', 'query' => 'query3'),
//            array('name'  => 'Result 4', 'query' => 'query4'),
//            array('name'  => 'Result 5', 'query' => 'query5'),
//            array('name'  => 'Result 6', 'query' => 'query6'),
//            array('name'  => 'Result 7', 'query' => 'query7'),
//            array('name'  => 'Result 8', 'query' => 'query8'),
//            array('name'  => 'Result 9', 'query' => 'query9'),
//            array('name'  => 'Result 16', 'query' => 'query16'),
//            array('name'  => 'Result 10', 'query' => 'query10'),
//            array('name'  => 'Result 11', 'query' => 'query11'),
//            array('name'  => 'Result 12', 'query' => 'query12'),
//            array('name'  => 'Result 13', 'query' => 'query13'),
//            array('name'  => 'Result 14', 'query' => 'query14'),
//            array('name'  => 'Result 15', 'query' => 'query15')
//        ))));
    }
    
    public function listsAction(Request $request)
    {
        $prof_id    = $request->get('p_id');
        $em     = $this->getDoctrine()->getManager();
        $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($prof_id);
        switch ($profile->getSocialNetwork()->getUniquename()) {
            case 'TWITTER':
                $api    = $this->get('twitter');
                $api->setOAuthToken($profile->getToken(), $profile->getTokenSecret());
                $result = $api->get('lists/list', array('user_id' => $profile->getUserid()));
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
        $text = "Consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
        return array(
            array(
                'id_str'    => 1,
                'in_reply_to_status_id_str' => 21,
                'created_at'=> 'Tue Aug 28 21:16:23 +0000 2012',
                'source'    => '<a href="//realitytechnicians.com\"" rel="\"nofollow\"">OAuth Dancer Reborn</a>',
                'user' => array(
                    'screen_name' => 'ylnunez84',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                ),
                'text'  => '#QueBonito ' .str_shuffle($text).' @ylnunez http://google.com',
                'retweet_count'    => 1,
                'retweeted_status' => array('user' => array(
                    'screen_name' => 'pepe500',
                    'id_str' => rand(1, 100000),
                    'profile_image_url' => 'http://localhost/desarrollo/hootsuite/web/images/avatar.jpg'
                )),
                'entities'  => array('user_mentions' => array(
                    array('screen_name' => 'lolo 1'),
                    array('screen_name' => 'lolo 2'),
                    array('screen_name' => 'lolo 3')
                ))
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
                'text'  => str_shuffle($text),
                'retweet_count'    => rand(0,100)
            )
        );
    }
    public function resultLi(){
        return Array
(
    '_count' => 10,
    '_start' => 0,
    '_total' => 19,
    'values' => Array
        (
            '0' => Array
                (
                    'isCommentable' => 1,
                    'isLikable' => 1,
                    'isLiked' => '',
                    'numLikes' => 0,
                    'timestamp' => '1416416716078',
                    'updateComments' => Array
                        (
                            '_total' => 0
                        ),

                    'updateContent' => Array
                        (
                            'person' => Array
                                (
                                    'apiStandardProfileRequest' => Array
                                        (
                                            'headers' => Array
                                                (
                                                    '_total' => 1,
                                                    'values' => Array
                                                        (
                                                            '0' => Array
                                                                (
                                                                    'name' => 'x-li-auth-token',
                                                                    'value' => 'name:qdhY'
                                                                )

                                                        )

                                                ),

                                            'url' => 'https://api.linkedin.com/v1/people/d8E36tN6UC'
                                        ),

                                    'firstName' => 'Yandy',
                                    'headline' => 'Jefe de Informática en Radio Progreso',
                                    'id' => 'd8E36tN6UC',
                                    'lastName' => 'León Nuñez',
                                    'pictureUrl' => 'https://media.licdn.com/mpr/mprx/0_xrBNYrc_Svk2YZ9TjqNFYPc-DnnuyjbTgN_IYtQt-PCg_MK319bZr--yi49t0VQSY1vElzqVsAhN',
                                    'siteStandardProfileRequest' => Array
                                        (
                                            'url' => 'https://www.linkedin.com/profile/view?id=382849957&authType=name&authToken=qdhY&trk=api*a4161231*s4225391*'
                                        )

                                )

                        ),

                    'updateKey' => 'UPDATE-382849957-5940882297950605312',
                    'updateType' => 'PICU',
                ),

            '1' => Array
                (
                    'isCommentable' => 1,
                    'isLikable' => 1,
                    'isLiked' => '',
                    'numLikes' => 0,
                    'timestamp' => '1416408361749',
                    'updateComments' => Array
                        (
                            '_total' => 0
                        ),

                    'updateContent' => Array
                        (
                            'company' => Array
                                (
                                    'id' => '1110',
                                    'name' => 'Orange'
                                ),

                            'companyJobUpdate' => Array
                                (
                                    'action' => Array
                                        (
                                            'code' => 'none'
                                        ),

                                    'job' => Array
                                        (
                                            'company' => Array
                                                (
                                                    'id' => '1110',
                                                    'name' => 'Orange'
                                                ),

                                            'description' => 'Sous la responsabilité de lIngénieur Qualité de la direction SMA, vous accompagnerez les chefs de projets dans la mise en œuvre du nouveau SMQ de la direction OLPS : processus et documents projets. Vous accompagnerez lingénieur qualité dans le soutien à quelques projets majeurs de lentité SMA : - Soutien des Chefs de projet dans la préparation des lancements de projet o soutien à la rédaction d',
                                            'id' => '12321912',
                                            'locationDescription' => 'Cesson Sévigné',
                                            'position' => Array
                                                (
                                                    'title' => 'Stage assistant ingénieur qualité à la Direction Smart Access (f/h)'
                                                ),

                                            'siteJobRequest' => Array
                                                (
                                                    'url' => 'https://www.linkedin.com/jobs?viewJob=&jobId=12321912'
                                                ),

                                        )

                                )

                        ),

                    'updateKey' => 'UPDATE-c1110-5940847257308917760',
                    'updateType' => 'CMPY'
                ),

            '2' => Array
                (
                    'isCommentable' => '',
                    'isLikable' => '',
                    'timestamp' => '1416403645376',
                    'updateContent' => Array
                        (
                            'person' => Array
                                (
                                    'apiStandardProfileRequest' => Array
                                        (
                                            'headers' => Array
                                                (
                                                    '_total' => 1,
                                                    'values' => Array
                                                        (
                                                            '0' => Array
                                                                (
                                                                    'name' => 'x-li-auth-token',
                                                                    'value' => 'name:9fxX'
                                                                )

                                                        ),

                                                ),

                                            'url' => 'https://api.linkedin.com/v1/people/6qrOmSY7De'
                                        ),

                                    'connections' => Array
                                        (
                                            '_total' => 1,
                                            'values' => Array
                                                (
                                                    '0' => Array
                                                        (
                                                            'apiStandardProfileRequest' => Array
                                                                (
                                                                    'headers' => Array
                                                                        (
                                                                            '_total' => 1,
                                                                            'values' => Array
                                                                                (
                                                                                    '0' => Array
                                                                                        (
                                                                                            'name' => 'x-li-auth-token',
                                                                                            'value' => 'name:7Wu0'
                                                                                        )

                                                                                )

                                                                        ),

                                                                    'url' => 'https://api.linkedin.com/v1/people/KRSKW7d-KV'
                                                                ),

                                                            'firstName' => 'Ionel',
                                                            'headline' => 'ITS',
                                                            'id' => 'KRSKW7d-KV',
                                                            'lastName' => 'Niku',
                                                            'siteStandardProfileRequest' => Array
                                                                (
                                                                    'url' => 'https://www.linkedin.com/profile/view?id=325550285&authType=name&authToken=7Wu0&trk=api*a4161231*s4225391*'
                                                                )

                                                        )

                                                )

                                        ),

                                    'firstName' => 'Giorbis',
                                    'headline' => 'Ing. Informático Radio Progreso',
                                    'id' => '6qrOmSY7De',
                                    'lastName' => 'Miguel Lorié',
                                    'pictureUrl' => 'https://media.licdn.com/mpr/mprx/0_9fXpR8joYLDOwBZTq2FfRiYbpCVpwBJTqar_RiDsibaDN1a3sdqrv_MN-2sGHK4Sc26Gqkxlpk9e',
                                    'siteStandardProfileRequest' => Array
                                        (
                                            'url' => 'https://www.linkedin.com/profile/view?id=189251740&authType=name&authToken=9fxX&trk=api*a4161231*s4225391*'
                                        )

                                )

                        ),

                    'updateKey' => 'UPDATE-189251740-5940827475075407872',
                    'updateType' => 'CONN'
                ),

            '3' => Array
                (
                    'isCommentable' => '',
                    'isLikable' => '',
                    'timestamp' => '1416337662358',
                    'updateContent' => Array
                        (
                            'person' => Array
                                (
                                    'apiStandardProfileRequest' => Array
                                        (
                                            'headers' => Array
                                                (
                                                    '_total' => 1,
                                                    'values' => Array
                                                        (
                                                            '0' => Array
                                                                (
                                                                    'name' => 'x-li-auth-token',
                                                                    'value' => 'name:abpe'
                                                                )

                                                        )

                                                ),

                                            'url' => 'https://api.linkedin.com/v1/people/U6O-beEwxZ'
                                        ),

                                    'connections' => Array
                                        (
                                            '_total' => 1,
                                            'values' => Array
                                                (
                                                    '0' => Array
                                                        (
                                                            'apiStandardProfileRequest' => Array
                                                                (
                                                                    'headers' => Array
                                                                        (
                                                                            '_total' => 1,
                                                                            'values' => Array
                                                                                (
                                                                                    '0' => Array
                                                                                        (
                                                                                            'name' => 'x-li-auth-token',
                                                                                            'value' => 'name:_z4b'
                                                                                        )

                                                                                )

                                                                        ),

                                                                    'url' => 'https://api.linkedin.com/v1/people/e-PfDC7TXj'
                                                                ),

                                                            'firstName' => 'Ronni',
                                                            'headline' => 'Public Relations, Events',
                                                            'id' => 'e-PfDC7TXj',
                                                            'lastName' => 'Kairey',
                                                            'pictureUrl' => 'https://media.licdn.com/mpr/mprx/0_JE55f_oHiOuKdZGkUWFVf3xLTMRhwZ_kUHlVf3JVYyfrNpNXvuvWTTSq7cU0HxCe4WbUhQbbW2S3',
                                                            'siteStandardProfileRequest' => Array
                                                                (
                                                                    'url' => 'https://www.linkedin.com/profile/view?id=41937211&authType=name&authToken=_z4b&trk=api*a4161231*s4225391*'
                                                                )

                                                        )

                                                )

                                        ),

                                    'firstName' => 'Yunior',
                                    'headline' => 'Ejecutivo Marketing, Producto y Publicidad',
                                    'id' => 'U6O-beEwxZ',
                                    'lastName' => 'Yanes Torres',
                                    'pictureUrl' => 'https://media.licdn.com/mpr/mprx/0_ZPIPybkY4ADxKQza4lU8yXz74qEayQLasA02yXkYFAY0_G-mqvRx-krK9sor0TXGMKdSP_920jto',
                                    'siteStandardProfileRequest' => Array
                                        (
                                            'url' => 'https://www.linkedin.com/profile/view?id=229103310&authType=name&authToken=abpe&trk=api*a4161231*s4225391*'
                                        )

                                )

                        ),

                    'updateKey' => 'UPDATE-229103310-5940550722381684736',
                    'updateType' => 'CONN'
                ),

            '4' => Array
                (
                    'isCommentable' => 1,
                    'isLikable' => 1,
                    'isLiked' => '',
                    'numLikes' => 0,
                    'timestamp' => '1416238284367',
                    'updateComments' => Array
                        (
                            '_total' => 0
                        ),

                    'updateContent' => Array
                        (
                            'company' => Array
                                (
                                    'id' => '1110',
                                    'name' => 'Orange'
                                ),

                            'companyJobUpdate' => Array
                                (
                                    'action' => Array
                                        (
                                            'code' => 'none'
                                        ),

                                    'job' => Array
                                        (
                                            'company' => Array
                                                (
                                                    'id' => '1110',
                                                    'name' => 'Orange'
                                                ),

                                            'description' => 'Véritable expert dans le domaine des solutions communicantes et de lintégration, vous êtes le vendeur dédié à ces solutions. Vous êtes le référent auprès de laccount manager, de lingénieur commercial et du responsable commercial de territoire sur les affaires complexes. Vous veillez à la rentabilité financière des affaires que vous portez en défendant la marge. Vous garantissez la qualité des d',
                                            'id' => '12278436',
                                            'locationDescription' => 'Orléans',
                                            'position' => Array
                                                (
                                                    'title' => 'Ingénieur commercial spécialisé solutions communicantes (f/h)'
                                                ),

                                            'siteJobRequest' => Array
                                                (
                                                    'url' => 'https://www.linkedin.com/jobs?viewJob=&jobId=12278436'
                                                )

                                        )

                                )

                        ),

                    'updateKey' => 'UPDATE-c1110-5940133901061103617',
                    'updateType' => 'CMPY'
                ),

            '5' => Array
                (
                    'isCommentable' => 1,
                    'isLikable' => 1,
                    'isLiked' => 1,
                    'likes' => Array
                        (
                            '_total' => 2,
                            'values' => Array
                                (
                                    '0' => Array
                                        (
                                            'person' => Array
                                                (
                                                    'firstName' => 'Carlos',
                                                    'id' => 'pIprRyF4SI',
                                                    'lastName' => 'Hunter, PMP'
                                                )

                                        ),

                                    '1' => Array
                                        (
                                            'person' => Array
                                                (
                                                    'firstName' => 'Luciano',
                                                    'id' => 'WEo1SmOOY4',
                                                    'lastName' => 'Ladeira'
                                                )

                                        )

                                )

                        ),

                    'numLikes' => '29',
                    'timestamp' => '1416215344774',
                    'updateComments' => Array
                        (
                            '_total' => 0
                        ),

                    'updateContent' => Array
                        (
                            'company' => Array
                                (
                                    'id' => '1110',
                                    'name' => 'Orange'
                                ),

                            'companyStatusUpdate' => Array
                                (
                                    'share' => Array
                                        (
                                            'comment' => 'Discover the three winners of the Orange African Social Venture Prize, and the winner of the new award, Orange Partner, to reward a project using an Orange API. Congratulations to all the participants.',
                                            'content' => Array
                                                (
                                                    'description' => 'This prize, which has enjoyed considerable success since its launch in 2011, aims to support the development of entrepreneurs and start-ups that offer solutions using information and communication technologies (ICT) to meet the needs of people...',
                                                    'eyebrowUrl' => 'http://www.orange.com/en/press/press-releases/press-releases-2014/Orange-announces-winners-of-the-Orange-African-Social-Venture-Prize',
                                                    'shortenedUrl' => 'http://www.orange.com/en/press/press-releases/press-releases-2014/Orange-announces-winners-of-the-Orange-African-Social-Venture-Prize',
                                                    'submittedImageUrl' => 'http://image-store.slidesharecdn.com/a31b74e5-942e-455b-be71-802fad0db68f-large.jpeg',
                                                    'submittedUrl' => 'http://www.orange.com/en/press/press-releases/press-releases-2014/Orange-announces-winners-of-the-Orange-African-Social-Venture-Prize',
                                                    'thumbnailUrl' => 'https://media.licdn.com/media-proxy/ext?w=80&h=100&hash=C160PkLra%2FojNlWHgBIS7s0JzNI%3D&url=http%3A%2F%2Fimage-store.slidesharecdn.com%2Fa31b74e5-942e-455b-be71-802fad0db68f-large.jpeg',
                                                    'title' => 'Orange announces winners of the Orange African Social Venture Prize'
                                                ),

                                            'id' => 's5940037685388275712',
                                            'source' => Array
                                                (
                                                    'serviceProvider' => Array
                                                        (
                                                            'name' => 'LINKEDIN'
                                                        ),

                                                    'serviceProviderShareId' => 's5940037685388275712'
                                                ),

                                            'timestamp' => '1416215344774',
                                            'visibility' => Array
                                                (
                                                    'code' => 'anyone'
                                                )

                                        )

                                )

                        ),

                    'updateKey' => 'UPDATE-c1110-5940037685325361152',
                    'updateType' => 'CMPY'
                ),

            '6' => Array
                (
                    'isCommentable' => 1,
                    'isLikable' => 1,
                    'isLiked' => '',
                    'numLikes' => 0,
                    'timestamp' => '1415963026446',
                    'updateComments' => Array
                        (
                            '_total' => 0
                        ),

                    'updateContent' => Array
                        (
                            'company' => Array
                                (
                                    'id' => '1110',
                                    'name' => 'Orange'
                                ),

                            'companyJobUpdate' => Array
                                (
                                    'action' => Array
                                        (
                                            'code' => 'none'
                                        ),

                                    'job' => Array
                                        (
                                            'company' => Array
                                                (
                                                    'id' => '1110',
                                                    'name' => 'Orange'
                                                ),

                                            'description' => 'Dans le cadre des réflexions menées sur les évolutions de loffre Open, vous reprenez, sous la responsabilité du responsable de la ligne de marché Open, la coordination des réflexions, formalisez les propositions et recommandations, et mettez en œuvre les évolutions validées. Dans ce cadre : Vous participez activement aux chantiers suivants : - écriture de brief, en collaboration avec les autres l',
                                            'id' => '12242497',
                                            'locationDescription' => 'Arcueil',
                                            'position' => Array
                                                (
                                                    'title' => 'Chef de produit Open (f/h)'
                                                ),

                                            'siteJobRequest' => Array
                                                (
                                                    'url' => 'https://www.linkedin.com/jobs?viewJob=&jobId=12242497'
                                                )

                                        )

                                )

                        ),

                    'updateKey' => 'UPDATE-c1110-5938979385670410240',
                    'updateType' => 'CMPY',
                ),

            '7' => Array
                (
                    'isCommentable' => 1,
                    'isLikable' => 1,
                    'isLiked' => '',
                    'likes' => Array
                        (
                            '_total' => 2,
                            'values' => Array
                                (
                                    '0' => Array
                                        (
                                            'person' => Array
                                                (
                                                    'firstName' => 'Manar',
                                                    'id' => 'SN1eepu6v7',
                                                    'lastName' => 'Aljamal'
                                                )

                                        ),

                                    '1' => Array
                                        (
                                            'person' => Array
                                                (
                                                    'firstName' => 'Hani',
                                                    'id' => 'E-Zh7T6L7c',
                                                    'lastName' => 'Elhasan'
                                                )

                                        )

                                )

                        ),

                    'numLikes' => 53,
                    'timestamp' => '1415951532527',
                    'updateComments' => Array
                        (
                            '_total' => 2,
                            'values' => Array
                                (
                                    '0' => Array
                                        (
                                            'comment' => 'nice' ,
                                            'id' => '5.9403518382042E+18',
                                            'person' => Array
                                                (
                                                    'apiStandardProfileRequest' => Array
                                                        (
                                                            'headers' => Array
                                                                (
                                                                    '_total' => 1,
                                                                    'values' => Array
                                                                        (
                                                                            '0' => Array
                                                                                (
                                                                                    'name' => 'x-li-auth-token',
                                                                                    'value' => 'name:U9jq'
                                                                                )

                                                                        )

                                                                ),

                                                            'url' => 'https://api.linkedin.com/v1/people/f3IgFKrP8W'
                                                        ),

                                                    'firstName' => 'faheem',
                                                    'headline' => 'MIS Officer at Al Ghurair Resources',
                                                    'id' => 'f3IgFKrP8W',
                                                    'lastName' => 'akhtar',
                                                    'siteStandardProfileRequest' => Array
                                                        (
                                                            'url' => 'https://www.linkedin.com/profile/view?id=381455753&authType=name&authToken=U9jq&trk=api*a4161231*s4225391*'
                                                        )

                                                ),

                                            'sequenceNumber' => 0,
                                            'timestamp' => '1416290244628',
                                        ),

                                    '1' => Array
                                        (
                                            'comment' => 'Hello, nice idea ! Some new services through LinkedIn should indeed be released before the end of the year in orange.jobs',
                                            'company' => Array
                                                (
                                                    'id' => '1110',
                                                    'name' => 'Orange'
                                                ),

                                            'id' => '5.9390209097582E+18',
                                            'sequenceNumber' => 1,
                                            'timestamp' => '1415972926559'
                                        )

                                )

                        ),

                    'updateContent' => Array
                        (
                            'company' => Array
                                (
                                    'id' => '1110',
                                    'name' => 'Orange'
                                ),

                            'companyStatusUpdate' => Array
                                (
                                    'share' => Array
                                        (
                                            'comment' => 'A new version of the Groups career website www.orange.jobs has just landed at 3am, Paris time. Please feel free to test it on desktop, tablet, or mobile and tell us what you think.',
                                            'content' => Array
                                                (
                                                    'description' => 'Orange Jobs',
                                                    'eyebrowUrl' => 'http://www.orange.jobs',
                                                    'shortenedUrl' => 'http://www.orange.jobs',
                                                    'submittedImageUrl' => 'http://image-store.slidesharecdn.com/5a9bcd6c-0b11-482e-86e0-0d9586cd3151-large.jpeg',
                                                    'submittedUrl' => 'http://www.orange.jobs',
                                                    'thumbnailUrl' => 'https://media.licdn.com/media-proxy/ext?w=80&h=100&hash=yb3f%2BWey9%2BsE4Vj8lWNjM8A6Ak8%3D&url=http%3A%2F%2Fimage-store.slidesharecdn.com%2F5a9bcd6c-0b11-482e-86e0-0d9586cd3151-large.jpeg',
                                                    'title' => 'Orange Jobs'
                                                ),

                                            'id' => 's5938931176650600448',
                                            'source' => Array
                                                (
                                                    'serviceProvider' => Array
                                                        (
                                                            'name' => 'LINKEDIN'
                                                        ),

                                                    'serviceProviderShareId' => 's5938931176650600448'
                                                ),

                                            'timestamp' => '1415951532527',
                                            'visibility' => Array
                                                (
                                                    'code' => 'anyone'
                                                )

                                        )

                                )

                        ),

                    'updateKey' => 'UPDATE-c1110-5938931176642211840',
                    'updateType' => 'CMPY'
                ),

            '8' => Array
                (
                    'isCommentable' => 1,
                    'isLikable' => 1,
                    'isLiked' => '',
                    'numLikes' => 0,
                    'timestamp' => '1415908910555',
                    'updateComments' => Array
                        (
                            '_total' => 0
                        ),

                    'updateContent' => Array
                        (
                            'person' => Array
                                (
                                    'apiStandardProfileRequest' => Array
                                        (
                                            'headers' => Array
                                                (
                                                    '_total' => 1,
                                                    'values' => Array
                                                        (
                                                            '0' => Array
                                                                (
                                                                    'name' => 'x-li-auth-token',
                                                                    'value' => 'name:abpe'
                                                                )

                                                        )

                                                ),

                                            'url' => 'https://api.linkedin.com/v1/people/U6O-beEwxZ'
                                        ),

                                    'currentShare' => Array
                                        (
                                            'author' => Array
                                                (
                                                    'firstName' => 'Yunior',
                                                    'id' => 'U6O-beEwxZ',
                                                    'lastName' => 'Yanes Torres'
                                                ),

                                            'comment' => 'probando',
                                            'content' => Array
                                                (
                                                    'description' => 'Orgullosos de presentar a los ganadores de Latin American Fotografía 3...',
                                                    'eyebrowUrl' => 'http://www.linkedin.com/groups/Latin-American-Fotograf%C3%ADa-3-Ganadores-5056800%2ES%2E5930400417589526531',
                                                    'shortenedUrl' => 'http://www.linkedin.com/groups/Latin-American-Fotograf%C3%ADa-3-Ganadores-5056800%2ES%2E5930400417589526531',
                                                    'submittedImageUrl' => 'https://media.licdn.com/mpr/mpr/shrink_200_200/p/4/000/159/368/1caf4d4.jpg',
                                                    'submittedUrl' => 'http://www.linkedin.com/groups/Latin-American-Fotograf%C3%ADa-3-Ganadores-5056800%2ES%2E5930400417589526531',
                                                    'thumbnailUrl' => 'https://media.licdn.com/media-proxy/ext?w=80&h=100&hash=ON1h9INJtCmed3lXtc08e1zKDzk%3D&url=https%3A%2F%2Fmedia.licdn.com%2Fmpr%2Fmpr%2Fshrink_200_200%2Fp%2F4%2F000%2F159%2F368%2F1caf4d4.jpg',
                                                    'title' => 'Latin American Fotografía 3 - Ganadores.'
                                                ),

                                            'id' => 's5938752407134564368',
                                            'source' => Array
                                                (
                                                    'serviceProvider' => Array
                                                        (
                                                            'name' => 'LINKEDIN'
                                                        ),

                                                    'serviceProviderShareId' => 's5938752407134564368'
                                                ),

                                            'timestamp' => '1415908910555',
                                            'visibility' => Array
                                                (
                                                    'code' => 'anyone'
                                                )

                                        ),

                                    'firstName' => 'Yunior',
                                    'headline' => 'Ejecutivo Marketing, Producto y Publicidad',
                                    'id' => 'U6O-beEwxZ',
                                    'lastName' => 'Yanes Torres',
                                    'pictureUrl' => 'https://media.licdn.com/mpr/mprx/0_ZPIPybkY4ADxKQza4lU8yXz74qEayQLasA02yXkYFAY0_G-mqvRx-krK9sor0TXGMKdSP_920jto',
                                    'siteStandardProfileRequest' => Array
                                        (
                                            'url' => 'https://www.linkedin.com/profile/view?id=229103310&authType=name&authToken=abpe&trk=api*a4161231*s4225391*'
                                        )

                                )

                        ),

                    'updateKey' => 'UPDATE-229103310-5938752406962610176',
                    'updateType' => 'SHAR'
                ),

            '9' => Array
                (
                    'isCommentable' => 1,
                    'isLikable' => 1,
                    'isLiked' => '',
                    'numLikes' => 0,
                    'timestamp' => '1415874676694',
                    'updateComments' => Array
                        (
                            '_total' => 0
                        ),

                    'updateContent' => Array
                        (
                            'company' => Array
                                (
                                    'id' => '1110',
                                    'name' => 'Orange'
                                ),

                            'companyJobUpdate' => Array
                                (
                                    'action' => Array
                                        (
                                            'code' => 'none'
                                        ),

                                    'job' => Array
                                        (
                                            'company' => Array
                                                (
                                                    'id' => '1110',
                                                    'name' => 'Orange'
                                                ),

                                            'description' => 'Au sein de lquipe VIA (Video and Image Anticipation) dédiée à lnticipation de services TV innovants, vous serez en charge détudier  pour un segment de marché particulier les opportunités dOrange dun point de vue service, fonctionnalités, design et multi-écrans :- Vous participez à une veille de marché concernant les grandes tendances de la TV, en sappuyant, notamment, sur les enseignements',
                                            'id' => '12216197',
                                            'locationDescription' => 'Cesson Sevigné',
                                            'position' => Array
                                                (
                                                    'title' => 'Stage analyste marketing concurrentiel offres TV - VOD - replay (f/h)'
                                                ),

                                            'siteJobRequest' => Array
                                                (
                                                    'url' => 'https://www.linkedin.com/jobs?viewJob=&jobId=12216197'
                                                )

                                        )

                                )

                        ),
                    'updateKey' => 'UPDATE-c1110-5938608819939602435',
                    'updateType' => 'CMPY'
                )

        )

);
    }
    
    public function resultFb(){
        return json_decode('{
  "data": [
    {
      "id": "1510993805807342_1510993252474064", 
      "from": {
        "id": "1510993805807342", 
        "name": "Magalys Menendez Verde"
      }, 
      "story": "Magalys Menendez Verde shared El Circos photo.", 
      "picture": "https://scontent-b.xx.fbcdn.net/hphotos-xap1/v/t1.0-9/s130x130/10417641_10152407293866006_8205545241350408809_n.jpg?oh=c90e586e07492a9f27e2fbee656e6ee7&oe=54F64502", 
      "link": "https://www.facebook.com/elcircodelamega/photos/a.275266786005.152472.29918501005/10152407293866006/?type=1", 
      "name": "Timeline Photos", 
      "caption": "¡Esto enamora! #CircoMEGA", 
      "properties": [
        {
          "name": "By", 
          "text": "El Circo", 
          "href": "https://www.facebook.com/elcircodelamega?ref=stream"
        }
      ], 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yD/r/aS8ecmYRys0.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/1510993805807342/posts/1510993252474064"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/1510993805807342/posts/1510993252474064"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "photo", 
      "status_type": "shared_story", 
      "object_id": "10152407293866006", 
      "application": {
        "name": "Photos", 
        "id": "2305272732"
      }, 
      "created_time": "2014-10-22T16:08:49+0000", 
      "updated_time": "2014-10-22T16:08:49+0000"
    }, 
    {
      "id": "291112394281236_791518410907296", 
      "from": {
        "category": "Telecommunication", 
        "category_list": [
          {
            "id": "2253", 
            "name": "Telecommunication"
          }
        ], 
        "name": "Casiencuba", 
        "id": "291112394281236"
      }, 
      "message": "Las organizaciones Panamericana y Mundial de la Salud han elogiado la calidad de la Sanidad cubana. José Luis Di Fabio, representante en la isla de ambas instituciones, hizo especial hincapié en la ejemplar formación de nuestros médicos durante la la II Conferencia Inter¬nacional Educación Médica para el siglo XXI, recientemente celebrada. Esperamos que estos elogios sirvan como estímulo a los responsables de nuestra salud pública para seguir mejorando.", 
      "picture": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQB8x38uKqvEmXu6&w=158&h=158&url=http%3A%2F%2Fwww.granma.cu%2Ffile%2Fimg%2F2014%2F10%2Fmedium%2Ff0019538.jpg", 
      "link": "http://www.granma.cu/cuba/2014-10-04/califican-de-ejemplar-formacion-medica-cubana", 
      "name": "Califican de ejemplar formación médica cubana", 
      "caption": "www.granma.cu", 
      "description": "El representante en Cuba de las Organizaciones Pana­me­ricana y Mundial de la Salud dijo que Cuba es un ejemplo por mantener la salud como número...", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yD/r/aS8ecmYRys0.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/291112394281236/posts/791518410907296"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/291112394281236/posts/791518410907296"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "link", 
      "status_type": "shared_story", 
      "application": {
        "name": "Hootsuite", 
        "id": "183319479511"
      }, 
      "created_time": "2014-10-22T16:04:10+0000", 
      "updated_time": "2014-10-22T16:04:10+0000", 
      "shares": {
        "count": 1
      }
    }, 
    {
      "id": "885874194770854_885843874773886", 
      "from": {
        "id": "885874194770854", 
        "name": "Luis José Tristá Rodríguez"
      }, 
      "message": "Que rico suena!!!!!!asdfasdfasdfasdfasdfasffasdfasdfsdfasdfasdfasd", 
      "picture": "https://fbexternal-a.akamaihd.net/app_full_proxy.php?app=87741124305&v=1&size=p&cksum=37d9425a81544658951766f6becdf2c0&src=https%3A%2F%2Fi.ytimg.com%2Fvi%2FebXbLfLACGM%2Fmaxresdefault.jpg", 
      "link": "http://www.youtube.com/watch?v=ebXbLfLACGM&feature=share", 
      "source": "https://www.youtube.com/v/ebXbLfLACGM?version=3&autohide=1&showinfo=1&autoplay=1&feature=share", 
      "name": "Calvin Harris - Summer", 
      "caption": "www.youtube.com", 
      "description": "Pre-order the new album Motion:\r\nDigital: http://smarturl.it/CHMotion?IQid=YT CD: http://smarturl.it/CHMotionCD?IQid=YT Official Store: http://smarturl.it/CHStore?IQid=YT \r\n\r\nVote for Calvin at the EMA’s: http://calvin-harris.mtvema.com \r\n\r\nSummer - Available from iTunes now: http://smarturl.it/CHSummer?IQid=YT Follow Calvin on Spotify http://smartur...", 
      "icon": "https://fbcdn-photos-b-a.akamaihd.net/hphotos-ak-xap1/t39.2081-0/10173489_10152389525269306_987289533_n.png", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/885874194770854/posts/885843874773886"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/885874194770854/posts/885843874773886"
        }, 
        {
          "name": "Suscríbete en Youtube", 
          "link": "http://www.youtube.com/channel/UCaHNFIob5Ixv74f5on3lvIw?sub_confirmation=1"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "swf", 
      "status_type": "app_created_story", 
      "application": {
        "name": "YouTube", 
        "namespace": "yt-fb-app", 
        "id": "87741124305"
      }, 
      "created_time": "2014-10-22T14:54:46+0000", 
      "updated_time": "2014-10-22T14:54:46+0000"
    }, 
    {
      "id": "96513776979_10152387504471980", 
      "from": {
        "category": "Musician/band", 
        "name": "Diana Fuentes", 
        "id": "96513776979"
      }, 
      "message": "Buenos dias!!! aqui les dejo mi nuevo Video: \"La última vez\" dirigido por Alvaro Aponte..!!! \nhttps://www.youtube.com/watch?v=Sm86tAqkqIA", 
      "picture": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQDvWu-MZ0-E7fcC&w=130&h=130&url=http%3A%2F%2Fi.ytimg.com%2Fvi%2FSm86tAqkqIA%2Fmaxresdefault.jpg", 
      "link": "https://www.youtube.com/watch?v=Sm86tAqkqIA", 
      "source": "http://www.youtube.com/v/Sm86tAqkqIA?version=3&autohide=1&autoplay=1", 
      "name": "Diana Fuentes - La Ultima Vez", 
      "description": "Official video by Diana Fuentes performing La Ultima Vez (C) 2014 Sony Music Entertainment Listen to “La Ultima Vez” now on iTunes: http://smarturl.it/Planet...", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yj/r/v2OnaTyTQZE.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/96513776979/posts/10152387504471980"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/96513776979/posts/10152387504471980"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "video", 
      "status_type": "shared_story", 
      "created_time": "2014-10-22T14:40:07+0000", 
      "updated_time": "2014-10-22T16:02:50+0000", 
      "shares": {
        "count": 31
      }, 
      "likes": {
        "data": [
          {
            "id": "143064862378924", 
            "name": "Radio Caribe Sano"
          }, 
          {
            "id": "10152509555539472", 
            "name": "John E. Blais Alemany"
          }, 
          {
            "id": "387687291390264", 
            "name": "Claudia Romero de los Reyes"
          }, 
          {
            "id": "10152337335043629", 
            "name": "Juan E Shamizo"
          }, 
          {
            "id": "10154734992320433", 
            "name": "Cynthia Gonzalez Belen"
          }, 
          {
            "id": "389268217888003", 
            "name": "Michel Rodriguez"
          }, 
          {
            "id": "1543212659248385", 
            "name": "Rebeca Garcia Mena"
          }, 
          {
            "id": "382697031886918", 
            "name": "Lazaro Sanchez Florido"
          }, 
          {
            "id": "10152566204287772", 
            "name": "Pau Bucci"
          }, 
          {
            "id": "1479893882285195", 
            "name": "Yaqui Muñoz Molina"
          }, 
          {
            "id": "10152697004398658", 
            "name": "Rodrigo Alexis Lillo"
          }, 
          {
            "id": "10152546122116676", 
            "name": "Orlando L. Ferrer"
          }, 
          {
            "id": "342610499250915", 
            "name": "Arianni Rodriguez"
          }, 
          {
            "id": "1492460441011654", 
            "name": "Susana Trecu Bencomo"
          }, 
          {
            "id": "1571323363090560", 
            "name": "Leydis Sucel Reyes Escalona"
          }, 
          {
            "id": "542285455905865", 
            "name": "Maite Yandricevicthz"
          }, 
          {
            "id": "1526648920904461", 
            "name": "Papi Fragela"
          }, 
          {
            "id": "1552905738273691", 
            "name": "Maykel Michel Lopéz Rodriguez"
          }, 
          {
            "id": "537031129762432", 
            "name": "Jorge Hidalgo"
          }, 
          {
            "id": "809870359035800", 
            "name": "Alejandro Gispert"
          }, 
          {
            "id": "286405404890217", 
            "name": "Anelis Alvarez Luna"
          }, 
          {
            "id": "856482171029546", 
            "name": "Yanet González Sotolongo"
          }, 
          {
            "id": "1490794721197065", 
            "name": "Yaneilis Legra Arceo"
          }, 
          {
            "id": "1577098749186810", 
            "name": "Mercedes Perez"
          }, 
          {
            "id": "1494365147517190", 
            "name": "Kenia Pavon"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "MTQ5NDM2NTE0NzUxNzE5MA==", 
            "before": "MTQzMDY0ODYyMzc4OTI0"
          }, 
          "next": "https://graph.facebook.com/v2.1/96513776979_10152387504471980/likes?limit=25&after=MTQ5NDM2NTE0NzUxNzE5MA=="
        }
      }, 
      "comments": {
        "data": [
          {
            "id": "10152387504471980_10152387523516980", 
            "from": {
              "id": "1533583706871071", 
              "name": "Alejandro Navarro Riambau"
            }, 
            "message": "bello todo y bella tu. vente a moscu!!", 
            "can_remove": false, 
            "created_time": "2014-10-22T14:49:00+0000", 
            "like_count": 1, 
            "user_likes": false
          }, 
          {
            "id": "10152387504471980_10152387534981980", 
            "from": {
              "id": "4801108680892", 
              "name": "Fausto Alejandro"
            }, 
            "message": "Preciosa, como tú y todo lo que haces. Cuando vas a dar algún concierto en Miami?", 
            "can_remove": false, 
            "created_time": "2014-10-22T14:51:11+0000", 
            "like_count": 1, 
            "user_likes": false
          }, 
          {
            "id": "10152387504471980_10152387554196980", 
            "from": {
              "id": "855675431164750", 
              "name": "Yoyi Vivo"
            }, 
            "message": "Muy bonita canción", 
            "can_remove": false, 
            "created_time": "2014-10-22T14:54:17+0000", 
            "like_count": 1, 
            "user_likes": false
          }, 
          {
            "id": "10152387504471980_10152387624021980", 
            "from": {
              "id": "10201122670307736", 
              "name": "Danaisy Gonzalez"
            }, 
            "message": "Uyyyy!!! lindo tema y el video clip super!!! me encanto>>>", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:16:58+0000", 
            "like_count": 1, 
            "user_likes": false
          }, 
          {
            "id": "10152387504471980_10152387630226980", 
            "from": {
              "id": "829295237081879", 
              "name": "Lauren Alvarez Valdes"
            }, 
            "message": "Me gusto mucho ! Felicidades Dianita !", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:21:10+0000", 
            "like_count": 1, 
            "user_likes": false
          }, 
          {
            "id": "10152387504471980_10152387633631980", 
            "from": {
              "id": "10203610998769685", 
              "name": "Haydee Lavastida"
            }, 
            "message": "Preciosa cancion y el video tambien deja que las ninas lo vean ,  te deseamos muchos exitos , talento sobra \nMuaaa", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:23:21+0000", 
            "like_count": 2, 
            "user_likes": false
          }, 
          {
            "id": "10152387504471980_10152387637051980", 
            "from": {
              "id": "323807157787376", 
              "name": "Arianna Diaz"
            }, 
            "message": "??????????", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:25:26+0000", 
            "like_count": 1, 
            "user_likes": false
          }, 
          {
            "id": "10152387504471980_10152387664941980", 
            "from": {
              "id": "10202985594388517", 
              "name": "Carmen Garmendia Hernandez"
            }, 
            "message": "Me encantó,muchos exitos.", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:45:14+0000", 
            "like_count": 1, 
            "user_likes": false
          }, 
          {
            "id": "10152387504471980_10152387665211980", 
            "from": {
              "id": "809870359035800", 
              "name": "Alejandro Gispert"
            }, 
            "message": "super! Lo mismo te deseamos!", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:45:23+0000", 
            "like_count": 1, 
            "user_likes": false
          }, 
          {
            "id": "10152387504471980_10152387685266980", 
            "from": {
              "id": "1543212659248385", 
              "name": "Rebeca Garcia Mena"
            }, 
            "message": "bella cancion", 
            "can_remove": false, 
            "created_time": "2014-10-22T16:02:50+0000", 
            "like_count": 1, 
            "user_likes": false
          }
        ], 
        "paging": {
          "cursors": {
            "after": "WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF4TlRJek9EYzJPRFV5TmpZNU9EQTZNVFF4TXprNU16YzNNRG94TUE9PQ==", 
            "before": "WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF4TlRJek9EYzFNak0xTVRZNU9EQTZNVFF4TXprNE9UTTBNRG94"
          }
        }
      }
    }, 
    {
      "id": "896065950420720_920011414692840", 
      "from": {
        "id": "896065950420720", 
        "name": "Adrian Muñiz Apaceiro"
      }, 
      "message": "PIENSA SIEMPRE QUE TODO TIENE UN MOTIVO Y QUE NADA PASA POR GUSTO...A VECES PENSAMOS QUE NOS LAS SABEMOS TODAS PERO NO ES ASI, EN EL FONDO ESTAMOS CHOCANDO DIA A DIA CON NUEVAS EXPERIENCIAS ..LEVANTATE CADA DIA CON MUCHA FE Y DESEOS DE VIVIR, ENTONCES COMPRENDERAS QUE NADA DE LO QUE TE PASE SERA EN VANO, QUE PASO PORQUE TENIA QUE PASAR ,LEVANTA LA CABEZA, SONRIE Y SIGUE TU CAMINO ..FELIZ DIA", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/896065950420720/posts/920011414692840"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/896065950420720/posts/920011414692840"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "status", 
      "status_type": "mobile_status_update", 
      "created_time": "2014-10-22T14:20:42+0000", 
      "updated_time": "2014-10-22T14:29:01+0000", 
      "likes": {
        "data": [
          {
            "id": "279531948910238", 
            "name": "Sonia La Rosa Herrera"
          }, 
          {
            "id": "292263820972851", 
            "name": "Irian Flores"
          }, 
          {
            "id": "1563514883871529", 
            "name": "Camila Franci Gonzalez Romero"
          }, 
          {
            "id": "750919841629583", 
            "name": "Alexis Cesar Triay Jerez"
          }, 
          {
            "id": "10205031230800401", 
            "name": "Cindy Elena Loredo Veloz"
          }, 
          {
            "id": "10204719234517715", 
            "name": "Susel Barcelo Castillo"
          }, 
          {
            "id": "636571656455800", 
            "name": "Dinorah Valdes"
          }, 
          {
            "id": "644268995671570", 
            "name": "Xiomara Alvarez"
          }, 
          {
            "id": "703359513085916", 
            "name": "Alina Obregon Fernandez"
          }, 
          {
            "id": "1431593610451358", 
            "name": "Ileana Pérez Lorenzo"
          }, 
          {
            "id": "896065950420720", 
            "name": "Adrian Muñiz Apaceiro"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "ODk2MDY1OTUwNDIwNzIw", 
            "before": "Mjc5NTMxOTQ4OTEwMjM4"
          }
        }
      }, 
      "comments": {
        "data": [
          {
            "id": "920011414692840_920014874692494", 
            "from": {
              "id": "10204719234517715", 
              "name": "Susel Barcelo Castillo"
            }, 
            "message": "gracias amigo", 
            "can_remove": false, 
            "created_time": "2014-10-22T14:29:01+0000", 
            "like_count": 0, 
            "user_likes": false
          }
        ], 
        "paging": {
          "cursors": {
            "after": "WTI5dGJXVnVkRjlqZFhKemIzSTZPVEl3TURFME9EYzBOamt5TkRrME9qRTBNVE01T0RneE5ERTZNUT09", 
            "before": "WTI5dGJXVnVkRjlqZFhKemIzSTZPVEl3TURFME9EYzBOamt5TkRrME9qRTBNVE01T0RneE5ERTZNUT09"
          }
        }
      }
    }, 
    {
      "id": "891401077539321_891350917544337", 
      "from": {
        "id": "891401077539321", 
        "name": "Yandy León Nuñez"
      }, 
      "message": "hello friend", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/891401077539321/posts/891350917544337"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/891401077539321/posts/891350917544337"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "status", 
      "status_type": "mobile_status_update", 
      "application": {
        "name": "Hootsuite", 
        "id": "183319479511"
      }, 
      "created_time": "2014-10-22T14:08:51+0000", 
      "updated_time": "2014-10-22T14:08:51+0000"
    }, 
    {
      "id": "291112394281236_791472330911904", 
      "from": {
        "category": "Telecommunication", 
        "category_list": [
          {
            "id": "2253", 
            "name": "Telecommunication"
          }
        ], 
        "name": "Casiencuba", 
        "id": "291112394281236"
      }, 
      "message": "Te presentamos la forma más económica y segura de hablar con Cuba, disfrutando de una calidad insuperable.\n\nUtilizando nuestro servicio de tarjetas prepago para llamar a Cuba, podrás hablar con fijos y móviles desde 0.42 EUR o 0.59 USD el minuto.\n\nAdquiere nuestras tarjetas prepago a través de los siguientes enlaces:\n\nSi eres cliente registrado:\n\nhttp://clientes1.casiencuba.com/newui_tarjeta_prepago.php\n\nSi aún no eres cliente registrado, puedes hacerlo directamente aquí:\n\nhttp://clientes1.casiencuba.com/tarjeta_prepago/\n\nDpto. de relación con el cliente,\n\nCasiencuba.com", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/291112394281236/posts/791472330911904"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/291112394281236/posts/791472330911904"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "status", 
      "status_type": "mobile_status_update", 
      "application": {
        "name": "Hootsuite", 
        "id": "183319479511"
      }, 
      "created_time": "2014-10-22T14:04:56+0000", 
      "updated_time": "2014-10-22T14:04:56+0000", 
      "likes": {
        "data": [
          {
            "id": "1520676011511746", 
            "name": "Divany Fernandez"
          }, 
          {
            "id": "590828724376166", 
            "name": "Ismael Bender del Busto"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "NTkwODI4NzI0Mzc2MTY2", 
            "before": "MTUyMDY3NjAxMTUxMTc0Ng=="
          }
        }
      }
    }, 
    {
      "id": "10201844618515741_10201843949499016", 
      "from": {
        "id": "10201844618515741", 
        "name": "Ana Brihanna Piñon"
      }, 
      "to": {
        "data": [
          {
            "id": "1567822230114659", 
            "name": "Andriele Barros"
          }
        ]
      }, 
      "message": "Parabéns linda :)", 
      "picture": "https://fbcdn-sphotos-f-a.akamaihd.net/hphotos-ak-xpf1/v/t1.0-9/s480x480/10635978_10201843949339012_7229758524783033130_n.jpg?oh=f2929effedaee5f43c757254b53bf639&oe=54F00729&__gda__=1425427519_f3503b90b2e69485bcd70f355720d441", 
      "link": "https://www.facebook.com/photo.php?fbid=10201843949339012&set=p.10201843949339012&type=1", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yz/r/StEh3RhPvjk.gif", 
      "privacy": {
        "value": ""
      }, 
      "type": "photo", 
      "object_id": "10201843949339012", 
      "application": {
        "name": "Facebook for Android", 
        "namespace": "fbandroid", 
        "id": "350685531728"
      }, 
      "created_time": "2014-10-22T13:04:14+0000", 
      "updated_time": "2014-10-22T13:04:14+0000"
    }, 
    {
      "id": "10205443235949982_10205441707871781", 
      "from": {
        "id": "10205443235949982", 
        "name": "Maikel Pereira Ojeda"
      }, 
      "story": "Maikel Pereira Ojeda shared I love Cubas photo.", 
      "picture": "https://scontent-a.xx.fbcdn.net/hphotos-xpa1/v/t1.0-9/p130x130/1911853_995154150501253_6731354800586414110_n.jpg?oh=348adcc7af963bff5b9cb232fc35a1d9&oe=54B4FC91", 
      "link": "https://www.facebook.com/LovingCuba/photos/a.853527841330552.1073741828.853492701334066/995154150501253/?type=1", 
      "name": "Timeline Photos", 
      "caption": "I <3 #Cuba", 
      "properties": [
        {
          "name": "By", 
          "text": "I love Cuba", 
          "href": "https://www.facebook.com/LovingCuba?ref=stream"
        }
      ], 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yD/r/aS8ecmYRys0.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/10205443235949982/posts/10205441707871781"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/10205443235949982/posts/10205441707871781"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "photo", 
      "status_type": "shared_story", 
      "object_id": "995154150501253", 
      "application": {
        "name": "Photos", 
        "id": "2305272732"
      }, 
      "created_time": "2014-10-22T12:47:32+0000", 
      "updated_time": "2014-10-22T13:18:36+0000", 
      "likes": {
        "data": [
          {
            "id": "958012210891626", 
            "name": "Claudia Amaro"
          }, 
          {
            "id": "10203994518360963", 
            "name": "Yendy Ponce Castañón"
          }, 
          {
            "id": "924274200924347", 
            "name": "Yossel González García"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "OTI0Mjc0MjAwOTI0MzQ3", 
            "before": "OTU4MDEyMjEwODkxNjI2"
          }
        }
      }, 
      "comments": {
        "data": [
          {
            "id": "10205441707871781_10205441739432570", 
            "from": {
              "id": "845661128812136", 
              "name": "Alvarez Dayrán"
            }, 
            "message": "me encanta esa foto", 
            "can_remove": false, 
            "created_time": "2014-10-22T12:54:18+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10205441707871781_10205441833834930", 
            "from": {
              "id": "10203994518360963", 
              "name": "Yendy Ponce Castañón"
            }, 
            "message": "Mi Habana...", 
            "can_remove": false, 
            "created_time": "2014-10-22T13:18:36+0000", 
            "like_count": 0, 
            "user_likes": false
          }
        ], 
        "paging": {
          "cursors": {
            "after": "WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF5TURVME5ERTRNek00TXpRNU16QTZNVFF4TXprNE16a3hOam95", 
            "before": "WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF5TURVME5ERTNNemswTXpJMU56QTZNVFF4TXprNE1qUTFPRG94"
          }
        }
      }
    }, 
    {
      "id": "1555234328025386_1555171944698291", 
      "from": {
        "id": "1555234328025386", 
        "name": "Mi Cubita Libre"
      }, 
      "message": "Bella", 
      "picture": "https://fbcdn-sphotos-e-a.akamaihd.net/hphotos-ak-xfp1/v/t1.0-9/s130x130/10521098_1555171794698306_2376074964388202400_n.jpg?oh=f4871b2ebbf7aa9e19183c8433dcf6a0&oe=54F22299&__gda__=1424543304_07c200fc5dbbd4e787793e3902dc8baf", 
      "link": "https://www.facebook.com/photo.php?fbid=1555171794698306&set=pcb.1555171944698291&type=1&relevant_count=3", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yx/r/og8V99JVf8G.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/1555234328025386/posts/1555171944698291"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/1555234328025386/posts/1555171944698291"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "photo", 
      "status_type": "mobile_status_update", 
      "object_id": "1555171794698306", 
      "application": {
        "name": "Facebook for Android", 
        "namespace": "fbandroid", 
        "id": "350685531728"
      }, 
      "created_time": "2014-10-22T12:27:15+0000", 
      "updated_time": "2014-10-22T12:27:15+0000", 
      "likes": {
        "data": [
          {
            "id": "1539979382914016", 
            "name": "Catrachita Altamirano"
          }, 
          {
            "id": "10201916647832801", 
            "name": "Katja Rodriguez"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "MTAyMDE5MTY2NDc4MzI4MDE=", 
            "before": "MTUzOTk3OTM4MjkxNDAxNg=="
          }
        }
      }
    }, 
    {
      "id": "1555234328025386_1555171274698358", 
      "from": {
        "id": "1555234328025386", 
        "name": "Mi Cubita Libre"
      }, 
      "message": "Happy al fin hoy tendre a mii maridoo esperarlo como se merese hoy estare bien ocupada preparando rica cena mmmm", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/1555234328025386/posts/1555171274698358"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/1555234328025386/posts/1555171274698358"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "status", 
      "status_type": "mobile_status_update", 
      "application": {
        "name": "Facebook for Android", 
        "namespace": "fbandroid", 
        "id": "350685531728"
      }, 
      "created_time": "2014-10-22T12:25:09+0000", 
      "updated_time": "2014-10-22T12:25:09+0000", 
      "likes": {
        "data": [
          {
            "id": "651160875002746", 
            "name": "Nilsa Bermudez"
          }, 
          {
            "id": "1507266766190772", 
            "name": "Mariceliz Muriel"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "MTUwNzI2Njc2NjE5MDc3Mg==", 
            "before": "NjUxMTYwODc1MDAyNzQ2"
          }
        }
      }
    }, 
    {
      "id": "291112394281236_791430740916063", 
      "from": {
        "category": "Telecommunication", 
        "category_list": [
          {
            "id": "2253", 
            "name": "Telecommunication"
          }
        ], 
        "name": "Casiencuba", 
        "id": "291112394281236"
      }, 
      "message": "Aprovecha a tope la nueva OFERTA DE DOBLE RECARGA a móviles Cubacel que tenemos para ti.\n\n Hasta las 23:59 (hora de Cuba) del próximo viernes 24 de septiembre puedes disfrutar de la Oferta de Doble Recarga a Móviles Cubacel, para importes de entre 20 y 50 CUC. \n\nAdemás, por cada recarga de móvil que realices, añadiremos 50 SMS a tu cuenta de usuario en Casiencuba.com, a consumir durante los próximos 30 días. \n\nRealizando la doble recarga a través de nuestros sistemas, el móvil en Cuba recibirá el saldo al instante, de forma fácil y segura, sin importar el país en que te encuentres.\n\nAccede en:\n\nhttp://casiencuba.com/index.php/servicios#recargas\n\nDpto. de relación con el cliente,\n\nCasiencuba.com", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/291112394281236/posts/791430740916063"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/291112394281236/posts/791430740916063"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "status", 
      "status_type": "mobile_status_update", 
      "application": {
        "name": "Hootsuite", 
        "id": "183319479511"
      }, 
      "created_time": "2014-10-22T12:15:30+0000", 
      "updated_time": "2014-10-22T12:15:30+0000", 
      "likes": {
        "data": [
          {
            "id": "10205229352922540", 
            "name": "M Yuri CH C"
          }, 
          {
            "id": "1487268251532778", 
            "name": "Sandro Velazquez"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "MTQ4NzI2ODI1MTUzMjc3OA==", 
            "before": "MTAyMDUyMjkzNTI5MjI1NDA="
          }
        }
      }
    }, 
    {
      "id": "10203961489650066_10203960449224056", 
      "from": {
        "id": "10203961489650066", 
        "name": "Maydelis Gómez"
      }, 
      "message": "Qué me compraré?", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/10203961489650066/posts/10203960449224056"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/10203961489650066/posts/10203960449224056"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "status", 
      "status_type": "mobile_status_update", 
      "application": {
        "name": "Mobile", 
        "id": "2915120374"
      }, 
      "created_time": "2014-10-22T12:08:36+0000", 
      "updated_time": "2014-10-22T15:34:50+0000", 
      "likes": {
        "data": [
          {
            "id": "994260557268445", 
            "name": "Julio Cesar"
          }, 
          {
            "id": "855801057798347", 
            "name": "Aida Isabel Delgado Larrinaga"
          }, 
          {
            "id": "529724873838581", 
            "name": "Loraine Bosch Taquechel"
          }, 
          {
            "id": "10201760129845079", 
            "name": "Alina Llerena Rosell"
          }, 
          {
            "id": "935933013093161", 
            "name": "Dariana Rdguez Barral"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "OTM1OTMzMDEzMDkzMTYx", 
            "before": "OTk0MjYwNTU3MjY4NDQ1"
          }
        }
      }, 
      "comments": {
        "data": [
          {
            "id": "10203960449224056_10203960457624266", 
            "from": {
              "id": "935933013093161", 
              "name": "Dariana Rdguez Barral"
            }, 
            "message": "no t emociones...", 
            "can_remove": false, 
            "created_time": "2014-10-22T12:11:06+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10203960449224056_10203961344926448", 
            "from": {
              "id": "994260557268445", 
              "name": "Julio Cesar"
            }, 
            "message": "cuidado que los precios te aplastan!", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:34:50+0000", 
            "like_count": 0, 
            "user_likes": false
          }
        ], 
        "paging": {
          "cursors": {
            "after": "WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF5TURNNU5qRXpORFE1TWpZME5EZzZNVFF4TXprNU1qQTVNRG95", 
            "before": "WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF5TURNNU5qQTBOVGMyTWpReU5qWTZNVFF4TXprM09UZzJOam94"
          }
        }
      }
    }, 
    {
      "id": "10153260336688502_10153259807733502", 
      "from": {
        "id": "10153260336688502", 
        "name": "Luis Manuel Monzon Hernandez"
      }, 
      "story": "Luis Manuel Monzon Hernandez shared Havana Colorss photo.", 
      "picture": "https://fbcdn-sphotos-a-a.akamaihd.net/hphotos-ak-xpa1/v/t1.0-9/s130x130/65497_580881822057778_4666590503049454731_n.jpg?oh=e2ee65cd94275c7cfde859fbdb7f1bf4&oe=54ED65D3&__gda__=1420584792_f3e64613b84db5faf0ee7158b0cfeefc", 
      "link": "https://www.facebook.com/photo.php?fbid=580881822057778&set=a.107864496026182.15970.100004078964520&type=1", 
      "name": "Timeline Photos", 
      "caption": "Fresh Watermelon\nAcrylic on canvas.\nFor more artworks visit www.havanacolors.com", 
      "properties": [
        {
          "name": "By", 
          "text": "Havana Colors", 
          "href": "https://www.facebook.com/havana.colors.14"
        }
      ], 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yD/r/aS8ecmYRys0.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/10153260336688502/posts/10153259807733502"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/10153260336688502/posts/10153259807733502"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "photo", 
      "status_type": "shared_story", 
      "object_id": "580881822057778", 
      "application": {
        "name": "Facebook for iPhone", 
        "namespace": "fbiphone", 
        "id": "6628568379"
      }, 
      "created_time": "2014-10-22T12:04:46+0000", 
      "updated_time": "2014-10-22T12:04:46+0000"
    }, 
    {
      "id": "291112394281236_791389014253569", 
      "from": {
        "category": "Telecommunication", 
        "category_list": [
          {
            "id": "2253", 
            "name": "Telecommunication"
          }
        ], 
        "name": "Casiencuba", 
        "id": "291112394281236"
      }, 
      "message": "¿SABÍAS QUE... aún es necesario el trámite de la Carta de Invitación, aunque las autoridades cubanas no lo exijan?\n \nTe aclaramos esta y otras consultas a través de nuestro servicio de gestoría, asesoría y documentación:\n\nhttp://www.casiencuba.com/index.php/servicios#servicio_gestoria\n\nDpto. de relación con el cliente,\n\nCasiencuba.com", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/291112394281236/posts/791389014253569"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/291112394281236/posts/791389014253569"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "status", 
      "status_type": "mobile_status_update", 
      "application": {
        "name": "Hootsuite", 
        "id": "183319479511"
      }, 
      "created_time": "2014-10-22T10:01:59+0000", 
      "updated_time": "2014-10-22T10:01:59+0000", 
      "likes": {
        "data": [
          {
            "id": "1441352202806165", 
            "name": "Sandra Rodriguez"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "MTQ0MTM1MjIwMjgwNjE2NQ==", 
            "before": "MTQ0MTM1MjIwMjgwNjE2NQ=="
          }
        }
      }
    }, 
    {
      "id": "291112394281236_791347857591018", 
      "from": {
        "category": "Telecommunication", 
        "category_list": [
          {
            "id": "2253", 
            "name": "Telecommunication"
          }
        ], 
        "name": "Casiencuba", 
        "id": "291112394281236"
      }, 
      "message": "En Casiencuba estamos haciendo un recorrido por todas las ciudades y pueblos de nuestra querida isla. Hoy te traemos esta información sobre Consolación del Norte, en la provincia de Pinar del Río. ¿Dónde naciste y viviste en Cuba? ¿Te apetece hablarnos de tu pueblo o tu ciudad? Queremos que este sea un punto de encuentro en el que los cubanos podamos compartir anécdotas y recuerdos.", 
      "picture": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQBUULpp-zR2vd6P&w=158&h=158&url=http%3A%2F%2Fwww.guije.com%2Fpueblo%2Fmunicipios%2Fpnorte%2Ff2.jpg", 
      "link": "http://www.guije.com/pueblo/municipios/pnorte/index.htm", 
      "name": "Municipio de Consolacin del Norte en Ciudades, Pueblos y Lugares de Cuba", 
      "caption": "www.guije.com", 
      "description": "El Municipio de Consolacin del Norte, Pinar del Ro, en Ciudades, Pueblos y Lugares de Cuba. Barrios, poblaciones, informacin y fotos del Municipio...", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yD/r/aS8ecmYRys0.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/291112394281236/posts/791347857591018"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/291112394281236/posts/791347857591018"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "link", 
      "status_type": "shared_story", 
      "application": {
        "name": "Hootsuite", 
        "id": "183319479511"
      }, 
      "created_time": "2014-10-22T07:01:34+0000", 
      "updated_time": "2014-10-22T07:01:34+0000", 
      "likes": {
        "data": [
          {
            "id": "1531176277129667", 
            "name": "Lili Disle"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "MTUzMTE3NjI3NzEyOTY2Nw==", 
            "before": "MTUzMTE3NjI3NzEyOTY2Nw=="
          }
        }
      }
    }, 
    {
      "id": "10202809752512843_10202807413814377", 
      "from": {
        "id": "10202809752512843", 
        "name": "Duanys Hernández Torres"
      }, 
      "message": "Con artistas cubanos en Fiesta de la Cubanía en Bayamo", 
      "picture": "https://scontent-a.xx.fbcdn.net/hphotos-xpa1/v/t1.0-9/s130x130/1962683_10202807413294364_3432288106125526459_n.jpg?oh=6166928b8be14531d6d07d27baab87d7&oe=54EE1AF1", 
      "link": "https://www.facebook.com/photo.php?fbid=10202807413294364&set=pcb.10202807413814377&type=1&relevant_count=2", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yz/r/StEh3RhPvjk.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/10202809752512843/posts/10202807413814377"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/10202809752512843/posts/10202807413814377"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "photo", 
      "status_type": "mobile_status_update", 
      "object_id": "10202807413294364", 
      "created_time": "2014-10-22T04:35:47+0000", 
      "updated_time": "2014-10-22T15:39:52+0000", 
      "likes": {
        "data": [
          {
            "id": "1578749849011961", 
            "name": "Roberto Alfonso Lara"
          }, 
          {
            "id": "10203201693188487", 
            "name": "Maydiel Valle Lazo"
          }, 
          {
            "id": "10203012637546649", 
            "name": "Alexis Boentes Arias"
          }, 
          {
            "id": "566897700108189", 
            "name": "Nurienar Pons García"
          }, 
          {
            "id": "737699049642356", 
            "name": "Carlos Luis Sotolongo Puig"
          }, 
          {
            "id": "891401077539321", 
            "name": "Yandy León Nuñez"
          }, 
          {
            "id": "731350760274705", 
            "name": "Yoicel Rodriguez Martinez"
          }, 
          {
            "id": "750292508377827", 
            "name": "Luis Orlando Leon Carpio"
          }, 
          {
            "id": "653824228027162", 
            "name": "Yisliany Plasencia Castro"
          }, 
          {
            "id": "769110099823192", 
            "name": "Edel Dapia Perez"
          }, 
          {
            "id": "950077345021349", 
            "name": "Yunisleidy Navelo Ruiz"
          }, 
          {
            "id": "10203998812844762", 
            "name": "Javier Rodriguez Ortiz"
          }, 
          {
            "id": "1492870654329598", 
            "name": "Jenny Pérez"
          }, 
          {
            "id": "779029295493899", 
            "name": "Liana Consuegra Cogle"
          }, 
          {
            "id": "865056063527226", 
            "name": "Milena Hernandez"
          }, 
          {
            "id": "632809506831993", 
            "name": "Aliuska Brizuela Vega"
          }, 
          {
            "id": "1517559741816883", 
            "name": "Anabel Fernandez Niubo"
          }, 
          {
            "id": "10203574627186119", 
            "name": "Pilar Leiva"
          }, 
          {
            "id": "1474077812823707", 
            "name": "AnaBel Pérez Del Sol"
          }, 
          {
            "id": "262125177314250", 
            "name": "Dany Juvier"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "MjYyMTI1MTc3MzE0MjUw", 
            "before": "MTU3ODc0OTg0OTAxMTk2MQ=="
          }
        }
      }, 
      "comments": {
        "data": [
          {
            "id": "10202807413814377_10202809149057757", 
            "from": {
              "id": "950077345021349", 
              "name": "Yunisleidy Navelo Ruiz"
            }, 
            "message": "Te veo bien como te gusta", 
            "can_remove": false, 
            "created_time": "2014-10-22T14:00:48+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10202807413814377_10202809550947804", 
            "from": {
              "id": "750292508377827", 
              "name": "Luis Orlando Leon Carpio"
            }, 
            "message": "te veo a tu aire jajajajaja bien!!!", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:22:34+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10202807413814377_10202809598268987", 
            "from": {
              "id": "737699049642356", 
              "name": "Carlos Luis Sotolongo Puig"
            }, 
            "message": "Está usted muy farandulero, compañero!!!", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:33:54+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10202807413814377_10202809615269412", 
            "from": {
              "id": "566897700108189", 
              "name": "Nurienar Pons García"
            }, 
            "message": "Qué villa te falta cariño?", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:38:30+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10202807413814377_10202809619669522", 
            "from": {
              "id": "750292508377827", 
              "name": "Luis Orlando Leon Carpio"
            }, 
            "message": "jajaja exacto Nurienar si alguien conoce Cuba por dentro, ese es Duanys!!!", 
            "can_remove": false, 
            "created_time": "2014-10-22T15:39:52+0000", 
            "like_count": 0, 
            "user_likes": false
          }
        ], 
        "paging": {
          "cursors": {
            "after": "WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF5TURJNE1EazJNVGsyTmprMU1qSTZNVFF4TXprNU1qTTVNam8x", 
            "before": "WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF5TURJNE1Ea3hORGt3TlRjM05UYzZNVFF4TXprNE5qUTBPRG94"
          }
        }
      }
    }, 
    {
      "id": "10202809752512843_10202807283771126", 
      "from": {
        "id": "10202809752512843", 
        "name": "Duanys Hernández Torres"
      }, 
      "message": "Esa foto fue en  los vitrales del Teatro Bayamo en la Fiesta de la Cubanía", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/10202809752512843/posts/10202807283771126"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/10202809752512843/posts/10202807283771126"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "status", 
      "status_type": "mobile_status_update", 
      "application": {
        "name": "Mobile", 
        "id": "2915120374"
      }, 
      "created_time": "2014-10-22T03:56:24+0000", 
      "updated_time": "2014-10-22T03:56:24+0000"
    }, 
    {
      "id": "10202809752512843_10202807280251038", 
      "from": {
        "id": "10202809752512843", 
        "name": "Duanys Hernández Torres"
      }, 
      "story": "Duanys Hernández Torres added 2 new photos.", 
      "picture": "https://scontent-b.xx.fbcdn.net/hphotos-xpa1/v/t1.0-9/s130x130/10698660_10202807411814327_5106295384441638929_n.jpg?oh=f9b91df72f3df970f4cc94034ff79a2c&oe=54E4C0E3", 
      "link": "https://www.facebook.com/photo.php?fbid=10202807411814327&set=a.10202807280211037.1073741826.1471819103&type=1&relevant_count=2", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yx/r/og8V99JVf8G.gif", 
      "privacy": {
        "value": ""
      }, 
      "type": "photo", 
      "status_type": "added_photos", 
      "object_id": "10202807411814327", 
      "created_time": "2014-10-22T03:55:15+0000", 
      "updated_time": "2014-10-22T03:55:15+0000"
    }, 
    {
      "id": "857152930973438_856914404330624", 
      "from": {
        "id": "857152930973438", 
        "name": "Solangel Rodriguez Vazquez"
      }, 
      "story": "Solangel Rodriguez Vazquez shared a link.", 
      "picture": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQDi2hX57pHRbvqt&w=158&h=158&url=http%3A%2F%2Fappdiscoveryengine.com%2Fstatic%2Fhoro14.jpg", 
      "link": "http://appdiscoveryengine.com/gh/give-hearts/redir2/?i=KL&hi=en_US-37-22-1-21&t=Check+Out+Your+Daily+Heart&p=ODg4MjgxMjYzMDAwMDAx&hl=es_LA", 
      "name": "Check Out Your Daily Heart", 
      "caption": "Check Out Your Daily Heart", 
      "description": "Speaking your mind without feeling guilty is a craft you still need to learn. You are determined to succeed and get the upper hand in the end. You should take time off to relax. You will be able to make money through your effort. ", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yz/r/StEh3RhPvjk.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/857152930973438/posts/856914404330624"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/857152930973438/posts/856914404330624"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "link", 
      "status_type": "shared_story", 
      "application": {
        "name": "Give Hearts", 
        "namespace": "give-hearts", 
        "id": "65496494327"
      }, 
      "created_time": "2014-10-22T03:15:39+0000", 
      "updated_time": "2014-10-22T03:15:39+0000"
    }, 
    {
      "id": "352811692664_10152335316877665", 
      "from": {
        "category": "Musician/band", 
        "name": "Pablo Alborán", 
        "id": "352811692664"
      }, 
      "message": "No entiendo el despertar sin un beso de esos...\" \n\nFeliz despertar!", 
      "icon": "https://fbcdn-photos-c-a.akamaihd.net/hphotos-ak-xpa1/t39.2080-0/851565_10151397911967544_632525583_n.png", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/352811692664/posts/10152335316877665"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/352811692664/posts/10152335316877665"
        }, 
        {
          "name": "@pabloalboran on Twitter", 
          "link": "https://twitter.com/pabloalboran?utm_source=fb&utm_medium=fb&utm_campaign=pabloalboran&utm_content=524715394196865024"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "status", 
      "status_type": "app_created_story", 
      "application": {
        "name": "Twitter", 
        "namespace": "twitter", 
        "id": "2231777543"
      }, 
      "created_time": "2014-10-22T00:14:35+0000", 
      "updated_time": "2014-10-22T15:57:33+0000", 
      "shares": {
        "count": 855
      }, 
      "likes": {
        "data": [
          {
            "id": "10202872539128483", 
            "name": "Juanjo Rodriguez Santiago"
          }, 
          {
            "id": "10203559450481666", 
            "name": "Grace Osorio Vega"
          }, 
          {
            "id": "554600381336816", 
            "name": "Alexis Muñoz"
          }, 
          {
            "id": "10202725054907570", 
            "name": "Yessica Ferrer Manrique"
          }, 
          {
            "id": "384702998338322", 
            "name": "Maria José Robles Montoya"
          }, 
          {
            "id": "892848710742790", 
            "name": "Meolvide Andre"
          }, 
          {
            "id": "933412763355550", 
            "name": "Noelia Aulló Barragán"
          }, 
          {
            "id": "10204866146515297", 
            "name": "Vanesa Soledad"
          }, 
          {
            "id": "10202907067293460", 
            "name": "Ana Belen Rodriguez Piñero"
          }, 
          {
            "id": "373957766111828", 
            "name": "Isabel Davila"
          }, 
          {
            "id": "825259884184883", 
            "name": "Maria Angeles Gonzalez Rodriguez"
          }, 
          {
            "id": "10152786988326880", 
            "name": "Mila Julio Barrios"
          }, 
          {
            "id": "1532030543701276", 
            "name": "Sara Rubén Rdgz Pichardo"
          }, 
          {
            "id": "10204535287800487", 
            "name": "Beka Carnero"
          }, 
          {
            "id": "713238638729313", 
            "name": "Esdrie Mejìa"
          }, 
          {
            "id": "10203563431740634", 
            "name": "Sonia Barragan Blanco"
          }, 
          {
            "id": "520401794760575", 
            "name": "Marisol Blanco Sierra"
          }, 
          {
            "id": "1402328946723662", 
            "name": "Nikito Millache"
          }, 
          {
            "id": "10152740124810837", 
            "name": "Vanessa Olivera"
          }, 
          {
            "id": "583037671824149", 
            "name": "Melina Asuncion"
          }, 
          {
            "id": "311381639047267", 
            "name": "Pepi Perenales"
          }, 
          {
            "id": "10203469963123529", 
            "name": "Jess QB"
          }, 
          {
            "id": "373973766103946", 
            "name": "Laura Piñeiro Comesaña"
          }, 
          {
            "id": "946954688654867", 
            "name": "Alejandra Bastias"
          }, 
          {
            "id": "10152740854181488", 
            "name": "Wendy Onofre"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "MTAxNTI3NDA4NTQxODE0ODg=", 
            "before": "MTAyMDI4NzI1MzkxMjg0ODM="
          }, 
          "next": "https://graph.facebook.com/v2.1/352811692664_10152335316877665/likes?limit=25&after=MTAxNTI3NDA4NTQxODE0ODg="
        }
      }, 
      "comments": {
        "data": [
          {
            "id": "10152335316877665_10152335317447665", 
            "from": {
              "id": "784391808271387", 
              "name": "Eli Marchant"
            }, 
            "message": "Aww <3", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:15:10+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335317497665", 
            "from": {
              "id": "697048830376290", 
              "name": "Lucia del Valle"
            }, 
            "message": "Te quiero muchissimo Pablo sos un genioo <3", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:15:12+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335317762665", 
            "from": {
              "id": "839732199393668", 
              "name": "Elysita Roge Cami"
            }, 
            "message": "Que ROMANTICO ??", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:15:27+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335318002665", 
            "from": {
              "id": "822289307823266", 
              "name": "Anaa Ramiirez"
            }, 
            "message": "<3", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:15:41+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335318132665", 
            "from": {
              "id": "10204919948700840", 
              "name": "Alondra Ramirez Diaz"
            }, 
            "message": "Hermoso ??????", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:15:50+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335318242665", 
            "from": {
              "id": "742815502466106", 
              "name": "Diana Mora"
            }, 
            "message": "??", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:15:57+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335320417665", 
            "from": {
              "id": "10205292008490719", 
              "name": "Anita Salvador Quesada"
            }, 
            "message": "Yo te mando el mio. Mua", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:16:10+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335320447665", 
            "from": {
              "id": "938657462830711", 
              "name": "Manuela Perez"
            }, 
            "message": "Igualmente yo tampoco lo entiendo! Buen despertar amigo", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:16:13+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335320492665", 
            "from": {
              "id": "10205376656355801", 
              "name": "Maura Herrera Poblete"
            }, 
            "message": "ola sigameeeeeeee en twitter", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:16:15+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335320547665", 
            "from": {
              "id": "1073403502685854", 
              "name": "Arantza CM"
            }, 
            "message": "Sin tu aliento en mi cuello <3", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:16:19+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335320557665", 
            "from": {
              "id": "838853766154207", 
              "name": "Donna Gierova"
            }, 
            "message": "Te amo pablito,te amo!", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:16:19+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335320577665", 
            "from": {
              "id": "1559992554230453", 
              "name": "Esthela Partida"
            }, 
            "message": "Eres mi S0L ?", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:16:22+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335320772665", 
            "from": {
              "id": "1493612650906213", 
              "name": "Violetta Lujan"
            }, 
            "message": "<3", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:16:32+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335320892665", 
            "from": {
              "id": "10203143019600838", 
              "name": "Luisito Noriega"
            }, 
            "message": "Buenas noches en México, saludos idolo!!!!!", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:16:38+0000", 
            "like_count": 1, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335320987665", 
            "from": {
              "id": "757745837612152", 
              "name": "Michell Sanchez"
            }, 
            "message": "<3 Eres el mejor :)", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:16:43+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335321492665", 
            "from": {
              "id": "10205033351896288", 
              "name": "Claudia Andrea Tamayo Espinoza"
            }, 
            "message": "Pablo con un beso de esos me quedo en cama, y a apachacharnos.", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:17:15+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335321557665", 
            "from": {
              "id": "665905340174666", 
              "name": "Micaela Saba"
            }, 
            "message": "Yo tampoco entiendo ", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:17:18+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335321687665", 
            "from": {
              "id": "836318146378534", 
              "name": "Glendy C. Guerra"
            }, 
            "message": "??", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:17:22+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335322057665", 
            "from": {
              "id": "1494995034111442", 
              "name": "Deskansa LaAna Vó"
            }, 
            "message": "Ss tan tierno divino<3", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:17:35+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335322097665", 
            "from": {
              "id": "759835480727805", 
              "name": "Carla Constanza Cordova Arredondo"
            }, 
            "message": "Sin ti yo me pierdo sin ti me vuelvo veneno #PasosDeCero", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:17:38+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335322292665", 
            "from": {
              "id": "10203901767163473", 
              "name": "Carina Gomez"
            }, 
            "message": "Hola belleza espanhola,", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:17:48+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335322537665", 
            "from": {
              "id": "10205262020129180", 
              "name": "Paulina Galdamez"
            }, 
            "message": "Se extraña un beso, un abrazo y un suspiro para comenzar", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:18:01+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335322702665", 
            "from": {
              "id": "10205261395640855", 
              "name": "Ronald Arango Saucedo"
            }, 
            "message": "Saludos de Perú", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:18:11+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335322817665", 
            "from": {
              "id": "10204975593136157", 
              "name": "Zimmty Dalle"
            }, 
            "message": "AMO tu manera de componer música y letras. Increíble. ", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:18:17+0000", 
            "like_count": 0, 
            "user_likes": false
          }, 
          {
            "id": "10152335316877665_10152335322902665", 
            "from": {
              "id": "632247690188882", 
              "name": "Silvia Caetano"
            }, 
            "message": "Que bonito", 
            "can_remove": false, 
            "created_time": "2014-10-22T00:18:24+0000", 
            "like_count": 0, 
            "user_likes": false
          }
        ], 
        "paging": {
          "cursors": {
            "after": "WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF4TlRJek16VXpNakk1TURJMk5qVTZNVFF4TXprek56RXdORG96TUE9PQ==", 
            "before": "WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF4TlRJek16VXpNVGMwTkRjMk5qVTZNVFF4TXprek5qa3hNRG94"
          }, 
          "next": "https://graph.facebook.com/v2.1/352811692664_10152335316877665/comments?limit=25&after=WTI5dGJXVnVkRjlqZFhKemIzSTZNVEF4TlRJek16VXpNakk1TURJMk5qVTZNVFF4TXprek56RXdORG96TUE9PQ=="
        }
      }
    }, 
    {
      "id": "827086993979085_826795954008189", 
      "from": {
        "id": "827086993979085", 
        "name": "José Luis Muñoz Suárez"
      }, 
      "message": "si el test lo dice por algo será ....", 
      "picture": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQBeeAsUGReioCKj&w=158&h=158&url=http%3A%2F%2Fi.imgur.com%2FYORETqk.png", 
      "link": "http://comoeres.rysnoticias.com/santo/comp/2", 
      "name": "Eres mayormente Santo - Santo o malvado? Conoce tu resultado", 
      "caption": "comoeres.rysnoticias.com", 
      "description": "Descubre como eres en realidad y que predomina en ti, Santo? o malvado?", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yD/r/aS8ecmYRys0.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/827086993979085/posts/826795954008189"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/827086993979085/posts/826795954008189"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "link", 
      "status_type": "shared_story", 
      "application": {
        "name": "Share_bookmarklet", 
        "id": "5085647995"
      }, 
      "created_time": "2014-10-22T00:12:07+0000", 
      "updated_time": "2014-10-22T00:12:07+0000"
    }, 
    {
      "id": "4876997298535_4874988528317", 
      "from": {
        "id": "4876997298535", 
        "name": "Leyvis Luis Valdes Lopez"
      }, 
      "message": "Hola a todos. Ahora podemos llamar a Cuba(fijos y moviles) por 0.42Eur/min IVA incluido, calidad es excelente y garantizada.", 
      "picture": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQDK4lTvEld2xwqa&w=158&h=158&url=http%3A%2F%2Fclientes1.casiencuba.com%2Fsms_casiencuba%2Ftarjeta_prepago_117_eur.png", 
      "link": "http://clientes1.casiencuba.com/tarjeta_prepago", 
      "name": "CASIENCUBA.COM", 
      "caption": "Llamadas a Cuba a 0.42Eur/min. IVA incluido", 
      "description": "Llama a Cuba(fijos y moviles) desde 0.42Eur/min IVA incluido con la tarjeta prepago con una calidad excelente garantizada.", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yD/r/aS8ecmYRys0.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/4876997298535/posts/4874988528317"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/4876997298535/posts/4874988528317"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "link", 
      "status_type": "shared_story", 
      "application": {
        "name": "SMS POR CASIENCUBA.COM", 
        "namespace": "sms_casiencuba", 
        "id": "114896895339165"
      }, 
      "created_time": "2014-10-21T23:59:45+0000", 
      "updated_time": "2014-10-21T23:59:45+0000"
    }, 
    {
      "id": "10201844618515741_10201841875087157", 
      "from": {
        "id": "10201844618515741", 
        "name": "Ana Brihanna Piñon"
      }, 
      "to": {
        "data": [
          {
            "id": "367607740081575", 
            "name": "Elane Medeiros"
          }, 
          {
            "id": "391425607681498", 
            "name": "Camila Freitas"
          }
        ]
      }, 
      "with_tags": {
        "data": [
          {
            "id": "367607740081575", 
            "name": "Elane Medeiros"
          }, 
          {
            "id": "391425607681498", 
            "name": "Camila Freitas"
          }
        ]
      }, 
      "message": "Hoje foi o  dia de atenção a recém nacidos e da realização do test do pezinho no PSF Vila Progresso. .", 
      "story": "Ana Brihanna Piñon added 17 photos.", 
      "picture": "https://fbcdn-sphotos-h-a.akamaihd.net/hphotos-ak-xaf1/v/t1.0-9/s130x130/1797971_10201841869007005_1543636524621772929_n.jpg?oh=0c667c7fe0fe35e6e2785744fb561e19&oe=54ABA8AA&__gda__=1421427622_ca45291cd9d054beccf7301ca01654a4", 
      "link": "https://www.facebook.com/photo.php?fbid=10201841869007005&set=pcb.10201841875087157&type=1&relevant_count=9", 
      "icon": "https://fbstatic-a.akamaihd.net/rsrc.php/v2/yx/r/og8V99JVf8G.gif", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/10201844618515741/posts/10201841875087157"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/10201844618515741/posts/10201841875087157"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "photo", 
      "status_type": "mobile_status_update", 
      "object_id": "10201841869007005", 
      "application": {
        "name": "Facebook for Android", 
        "namespace": "fbandroid", 
        "id": "350685531728"
      }, 
      "created_time": "2014-10-21T23:59:17+0000", 
      "updated_time": "2014-10-21T23:59:17+0000", 
      "likes": {
        "data": [
          {
            "id": "1498108010441116", 
            "name": "Lívia Vasconcelos"
          }, 
          {
            "id": "1477135102552010", 
            "name": "Elberth Leitão Santos Junior"
          }, 
          {
            "id": "777044982341391", 
            "name": "Viowi Y. Cabrisas Amuedo"
          }, 
          {
            "id": "712859818807829", 
            "name": "Leticia Hernandez Victor"
          }, 
          {
            "id": "1487373674858044", 
            "name": "Adriana Peñate"
          }, 
          {
            "id": "636269073156524", 
            "name": "Lester Manuel Santana Diaz"
          }, 
          {
            "id": "1498342550431766", 
            "name": "Adalis Santovenia"
          }, 
          {
            "id": "575792145863282", 
            "name": "Kelly Keciane"
          }, 
          {
            "id": "483999451702526", 
            "name": "Alexandre Carvalho"
          }, 
          {
            "id": "367607740081575", 
            "name": "Elane Medeiros"
          }, 
          {
            "id": "539827869467352", 
            "name": "Diana Rosa Alejo Lopez"
          }, 
          {
            "id": "615072121948090", 
            "name": "Edvando Rocha"
          }, 
          {
            "id": "730488553702996", 
            "name": "Anay Luna"
          }, 
          {
            "id": "1494312950854943", 
            "name": "Dagna Torres"
          }, 
          {
            "id": "707881149288028", 
            "name": "Ana Luiza Martins"
          }, 
          {
            "id": "526761154094742", 
            "name": "Luquinha Martins Silva"
          }, 
          {
            "id": "892124397466644", 
            "name": "Yuleidy Sánchez"
          }, 
          {
            "id": "262125177314250", 
            "name": "Dany Juvier"
          }, 
          {
            "id": "656524671110353", 
            "name": "Yani Mena"
          }, 
          {
            "id": "824634380934690", 
            "name": "Yunia Aguiar Mesa"
          }, 
          {
            "id": "962753973740013", 
            "name": "Odalis Almeida"
          }, 
          {
            "id": "712281938814789", 
            "name": "Sergio Gonzalez"
          }, 
          {
            "id": "351505261678282", 
            "name": "Posto Celsolandia"
          }, 
          {
            "id": "1480184292262327", 
            "name": "Liudmila Soler Alvarez"
          }, 
          {
            "id": "1031629566863390", 
            "name": "Magzi Yury Estupinan"
          }
        ], 
        "paging": {
          "cursors": {
            "after": "MTAzMTYyOTU2Njg2MzM5MA==", 
            "before": "MTQ5ODEwODAxMDQ0MTExNg=="
          }, 
          "next": "https://graph.facebook.com/v2.1/1791171608_10201841875087157/likes?limit=25&after=MTAzMTYyOTU2Njg2MzM5MA=="
        }
      }
    }, 
    {
      "id": "291112394281236_791085894283881", 
      "from": {
        "category": "Telecommunication", 
        "category_list": [
          {
            "id": "2253", 
            "name": "Telecommunication"
          }
        ], 
        "name": "Casiencuba", 
        "id": "291112394281236"
      }, 
      "message": "Disfruta de nuestro nuevo servicio de tarjetas prepago. Comprando una tarjeta prepago Casiencuba.com, podrás llamar a las mejores tarifas, abonando sólo los minutos que hables y sin pagar ni un céntimo por el establecimiento de llamada. Infórmate y accede en:\n\nhttp://www.casiencuba.com/index.php/servicios#servicio_tarjetas\n\nDpto. de relación con el cliente,\n\nCasiencuba.com", 
      "actions": [
        {
          "name": "Comment", 
          "link": "https://www.facebook.com/291112394281236/posts/791085894283881"
        }, 
        {
          "name": "Like", 
          "link": "https://www.facebook.com/291112394281236/posts/791085894283881"
        }
      ], 
      "privacy": {
        "value": ""
      }, 
      "type": "status", 
      "status_type": "mobile_status_update", 
      "application": {
        "name": "Hootsuite", 
        "id": "183319479511"
      }, 
      "created_time": "2014-10-21T23:02:01+0000", 
      "updated_time": "2014-10-21T23:02:01+0000"
    }
  ], 
  "paging": {
    "previous": "https://graph.facebook.com/v2.1/891401077539321/home?limit=25&since=1413994129", 
    "next": "https://graph.facebook.com/v2.1/891401077539321/home?limit=25&until=1413932520"
  }
}', true);
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
    public function facebookPagesAction(Request $request)
    {
         $prof_id    = $request->get('p_id');
         $em     = $this->getDoctrine()->getManager();
        $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($prof_id);        
        $fb = $this->get('facebookv2');
        $api_result = $fb->get('/me/likes?fields=name,id,picture,likes&limit=10000',$profile->getToken());
        $result = $api_result->getGraphEdge()->asArray();
        return new Response(json_encode(array("success" => true, "object" => $result)));
        
    }
    public function instagramHashTagAction(Request $request)
    {
         $prof_id    = $request->get('p_id');
         $query   = $request->get('query');
         $em     = $this->getDoctrine()->getManager();
        $profile= $em->getRepository('BackofficeBundle:ProfilesUsuario')->find($prof_id);        
        $instagram = $this->get('instagram');
        $instagram->setAccessToken($profile->getToken());    
        $api_result = $instagram->searchTags($query);
        $result = $api_result->data;        
        return new Response(json_encode(array("success" => true, "object" => $result)));
        
    }
}