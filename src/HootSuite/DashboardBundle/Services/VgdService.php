<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace HootSuite\DashboardBundle\Services;


/**
 * Extends the LinkedIn class 
 */
class VgdService
{

    private $Vgd_api = 'http://Vgdurl.com/api-create.php';


    private $Vgd_oauth_api ='http://Vgdurl.com/api-create.php';

    /**
     * The URI for OAuth access token requests.
     */
    private $Vgd_oauth_access_token = 'http://Vgdurl.com/api-create.php';

    private $accesstoken;

    private $uid;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {}
    public function shorten($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://v.gd/create.php?format=simple&url='.$url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result);
    }
    public function vgdShorten($url,$shorturl = null,$logstats = false)
    {
        //$url - The original URL you want shortened
        //$shorturl - Your desired short URL (optional)
        //This function returns an array giving the results of your shortening
        //If successful $result["shortURL"] will give your new shortened URL
        //If unsuccessful $result["errorMessage"] will give an explanation of why
        //and $result["errorCode"] will give a code indicating the type of error
        //See http://v.gd/apishorteningreference.php#errcodes for an explanation of what the
        //error codes mean. In addition to that list this function can return an
        //error code of -1 meaning there was an internal error e.g. if it failed
        //to fetch the API page.
        $url = urlencode($url);
        $basepath = "http://v.gd/create.php?format=simple";
        //if you want to use is.gd instead, just swap the above line for the commented out one below
        //$basepath = "http://is.gd/create.php?format=simple";
        $result = array();
        $result["errorCode"] = -1;
        $result["shortURL"] = null;
        $result["errorMessage"] = null;
        //We need to set a context with ignore_errors on otherwise PHP doesn't fetch
        //page content for failure HTTP status codes (v.gd needs this to return error
        //messages when using simple format)
        $opts = array("http" => array("ignore_errors" => true));
        $context = stream_context_create($opts);
        if($shorturl)
            $path = $basepath."&shorturl=$shorturl&url=$url";
        else
            $path = $basepath."&url=$url";

        if($logstats)
            $path .= "&logstats=1";
        $response = @file_get_contents($path,false,$context);

        if(!isset($http_response_header))
        {
            $result["errorMessage"] = "Local error: Failed to fetch API page";
            return($result);
        }
        //Hacky way of getting the HTTP status code from the response headers
        if (!preg_match("{[0-9]{3}}",$http_response_header[0],$httpStatus))
        {
            $result["errorMessage"] = "Local error: Failed to extract HTTP status from result request";
            return($result);
        }
        $errorCode = -1;
        switch($httpStatus[0])
        {
            case 200:
                $errorCode = 0;
                break;
            case 400:
                $errorCode = 1;
                break;
            case 406:
                $errorCode = 2;
                break;
            case 502:
                $errorCode = 3;
                break;
            case 503:
                $errorCode = 4;
                break;
        }
        if($errorCode==-1)
        {
            $result["errorMessage"] = "Local error: Unexpected response code received from server";
            return($result);
        }
        $result["errorCode"] = $errorCode;
        if($errorCode==0)
            $result["shortURL"] = $response;
        else
            $result["errorMessage"] = $response;
        return($result);
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
     * @see http://code.google.com/p/Vgd-api/wiki/ApiDocumentation#/oauth/access_token
     */
    public function Vgd_oauth_access_token($code, $redirect, $client_id, $client_secret) {
        $results = array();
        $url = $this->Vgd_oauth_access_token . "access_token";
        $params = array();
        $params['client_id'] = $client_id;
        $params['client_secret'] = $client_secret;
        $params['code'] = $code;
        $params['redirect_uri'] = $redirect;
        $output = $this->Vgd_post_curl($url, $params);
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
     *   The user's Vgd username
     * @param $password
     *   The user's Vgd password
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

    public function Vgd_oauth_access_token_via_password($username, $password, $client_id, $client_secret) {
        //$results = array();
        $url = $this->Vgd_oauth_access_token . "access_token";

        $headers = array();
        $headers[] = 'Authorization: Basic '.base64_encode($client_id . ":" . $client_secret);

        $params = array();
        $params['grant_type'] = "password";
        $params['username'] = $username;
        $params['password'] = $password;

        $output = $this->Vgd_post_curl($url, $params, $headers);

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
     * @see http://code.google.com/p/Vgd-api/wiki/ApiDocumentation#/v3/validate
     */
    public function Vgd_get($url,$shorturl = null) {
        //$url - The original URL you want shortened
        //$shorturl - Your desired short URL (optional)

        //This function returns an array giving the results of your shortening
        //If successful $result["shortURL"] will give your new shortened URL
        //If unsuccessful $result["errorMessage"] will give an explanation of why
        //and $result["errorCode"] will give a code indicating the type of error

        //See http://v.gd/apishorteningreference.php#errcodes for an explanation of what the
        //error codes mean. In addition to that list this function can return an
        //error code of -1 meaning there was an internal error e.g. if it failed
        //to fetch the API page.

        $url = urlencode($url);
        $basepath = "http://v.gd/create.php?format=simple";
        //if you want to use is.gd instead, just swap the above line for the commented out one below
        //$basepath = "http://is.gd/create.php?format=simple";
        $result = array();
        $result["errorCode"] = -1;
        $result["shortURL"] = null;
        $result["errorMessage"] = null;

        //We need to set a context with ignore_errors on otherwise PHP doesn't fetch
        //page content for failure HTTP status codes (v.gd needs this to return error
        //messages when using simple format)
        $opts = array("http" => array("ignore_errors" => true));
        $context = stream_context_create($opts);

        if($shorturl)
            $path = $basepath."&shorturl=$shorturl&url=$url";
        else
            $path = $basepath."&url=$url";

        $response = @file_get_contents($path,false,$context);

        if(!isset($http_response_header))
        {
            $result["errorMessage"] = "Local error: Failed to fetch API page";
            return($result);
        }

        //Hacky way of getting the HTTP status code from the response headers
        if (!preg_match("{[0-9]{3}}",$http_response_header[0],$httpStatus))
        {
            $result["errorMessage"] = "Local error: Failed to extract HTTP status from result request";
            return($result);
        }

        $errorCode = -1;
        switch($httpStatus[0])
        {
            case 200:
                $errorCode = 0;
                break;
            case 400:
                $errorCode = 1;
                break;
            case 406:
                $errorCode = 2;
                break;
            case 502:
                $errorCode = 3;
                break;
            case 503:
                $errorCode = 4;
                break;
        }

        if($errorCode==-1)
        {
            $result["errorMessage"] = "Local error: Unexpected response code received from server";
            return($result);
        }

        $result["errorCode"] = $errorCode;
        if($errorCode==0)
            $result["shortURL"] = $response;
        else
            $result["errorMessage"] = $response;

        return($result);
    }

    /**
     * Format a POST call to the bit.ly API.
     *
     * @param $uri
     *   URI to call.
     * @param $fields
     *   Array of fields to send.
     */
   public function Vgd_post($endpoint, $params) {
        //$result = array();
        $api_endpoint = $endpoint;
        $url = $this->Vgd_oauth_api . $api_endpoint;
        $output = json_decode($this->Vgd_post_curl($url, $params), true);
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
    public function Vgd_get_curl($uri) {
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
    public function Vgd_post_curl($uri, $fields, $header_array = array()) {
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
