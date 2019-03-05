<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace HootSuite\DashboardBundle\Services;


/**
 * Extends the LinkedIn class 
 */
class AdfService
{

    private $adfly_api = 'http://api.adf.ly/api.php';


    private $adfly_oauth_api ='http://api.adf.ly/api.php';

    /**
     * The URI for OAuth access token requests.
     */
    private $adfly_oauth_access_token = 'http://api.adf.ly/api.php';

    private $accesstoken;

    private $uid;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->adfly_api;
        $this->accesstoken = $config['accesstoken'];
        $this->uid = $config['uid'];

    }
    public function shorten($url){
        /*$params = array();
        $params['key'] = $this->accesstoken;
        $params['uid'] = $this->uid;
        $params['advert_type'] ="int";
        $params['domain'] ="adf.ly";
        $params['url'] =$url;
        $results = $this->adfly_get($params);
        return $results;*/
        $curl = curl_init();
        $post_data = array('key' => $this->accesstoken,
            'uid' => $this->uid,
            'advert_type' => 'int',
            'domain' => 'adf.ly',
            'url' => $url );
        curl_setopt($curl, CURLOPT_URL, $this->adfly_oauth_api);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result);
    }
    /**
     * Returns an OAuth access token as well as API users for a given code.
     *
     * @param $code
     *   The OAuth verification code acquired via OAuth’s web authentication
     *   protocol.
     * @param $redirect
     *   The page to which a user was redirected upon successfully authenticating.
     * @param $client_id
     *   The client_id assigned to your OAuth app. (http://bit.ly/a/account)
     * @param $client_secret
     *   The client_secret assigned to your OAuth app. (http://bit.ly/a/account)
     *
     * @return
     *   An associative array containing:
     *   - login: The corresponding bit.ly users username.
     *   - api_key: The corresponding bit.ly users API key.
     *   - access_token: The OAuth access token for specified user.
     *
     * @see http://code.google.com/p/adfly-api/wiki/ApiDocumentation#/oauth/access_token
     */
    public function adfly_oauth_access_token($code, $redirect, $client_id, $client_secret) {
        $results = array();
        $url = $this->adfly_oauth_access_token . "access_token";
        $params = array();
        $params['client_id'] = $client_id;
        $params['client_secret'] = $client_secret;
        $params['code'] = $code;
        $params['redirect_uri'] = $redirect;
        $output = $this->adfly_post_curl($url, $params);
        $parts = explode('&', $output);
        foreach ($parts as $part) {
            $bits = explode('=', $part);
            $results[$bits[0]] = $bits[1];
        }
        return $results;
    }

    /**
     * Returns an OAuth access token via the user's bit.ly login Username and Password
     *
     * @param $username
     *   The user's adfly username
     * @param $password
     *   The user's adfly password
     * @param $client_id
     *   The client_id assigned to your OAuth app. (http://bit.ly/a/account)
     * @param $client_secret
     *   The client_secret assigned to your OAuth app. (http://bit.ly/a/account)
     *
     * @return
     *   An associative array containing:
     *   - access_token: The OAuth access token for specified user.
     *
     */

    public function adfly_oauth_access_token_via_password($username, $password, $client_id, $client_secret) {
        //$results = array();
        $url = $this->adfly_oauth_access_token . "access_token";

        $headers = array();
        $headers[] = 'Authorization: Basic '.base64_encode($client_id . ":" . $client_secret);

        $params = array();
        $params['grant_type'] = "password";
        $params['username'] = $username;
        $params['password'] = $password;

        $output = $this->adfly_post_curl($url, $params, $headers);

        $decoded_output = json_decode($output,1);

        $results = array(
            "access_token" => $decoded_output['access_token']
        );

        return $results;
    }

    /**
     * Format a GET call to the bit.ly API.
     *
     * @param $endpoint
     *   bit.ly API endpoint to call.
     * @param $params
     *   associative array of params related to this call.
     * @param $complex
     *   set to true if params includes associative arrays itself (or using <php5)
     *
     * @return
     *   associative array of bit.ly response
     *
     * @see http://code.google.com/p/adfly-api/wiki/ApiDocumentation#/v3/validate
     */
    public function adfly_get($params, $complex=false) {
        //$result = array();
        if ($complex) {
            $url_params = "";
            foreach ($params as $key => $val) {
                if (is_array($val)) {
                    // we need to flatten this into one proper command
                    $recs = array();
                    foreach ($val as $rec) {
                        $tmp = explode('/', $rec);
                        $tmp = array_reverse($tmp);
                        array_push($recs, $tmp[0]);
                    }
                    $val = implode('&' . $key . '=', $recs);
                }
                $url_params .= '&' . $key . "=" . $val;
            }
            $url = $this->adfly_oauth_api. "?" . substr($url_params, 1);
        } else {
            $url = $this->adfly_oauth_api. "?" . http_build_query($params);
        }

        $result = json_decode($this->adfly_get_curl($url), true);

        return $result;
    }

    /**
     * Format a POST call to the bit.ly API.
     *
     * @param $uri
     *   URI to call.
     * @param $fields
     *   Array of fields to send.
     */
   public function adfly_post($endpoint, $params) {
        //$result = array();
        $api_endpoint = $endpoint;
        $url = $this->adfly_oauth_api . $api_endpoint;
        $output = json_decode($this->adfly_post_curl($url, $params), true);
        $result = $output['data'][str_replace('/', '_', $api_endpoint)];
        $result['status_code'] = $output['status_code'];
        return $result;
    }

    /**
     * Make a GET call to the bit.ly API.
     *
     * @param $uri
     *   URI to call.
     */
    public function adfly_get_curl($uri) {
        $output = "";
        try {
            $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $output = curl_exec($ch);
        } catch (\Exception $e) {
        }
        return $output;
    }

    /**
     * Make a POST call to the bit.ly API.
     *
     * @param $uri
     *   URI to call.
     * @param $fields
     *   Array of fields to send.
     */
    public function adfly_post_curl($uri, $fields, $header_array = array()) {
        $output = "";
        $fields_string = "";
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
        rtrim($fields_string,'&');
        try {
            $ch = curl_init($uri);

            if(is_array($header_array) && !empty($header_array)){
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);
            }

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch,CURLOPT_POST,count($fields));
            curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $output = curl_exec($ch);
        } catch (\Exception $e) {
        }
        return $output;
    }

}
?>
