<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace HootSuite\DashboardBundle\Services;

/**
 * Extends the LinkedIn class 
 */
class WordpressService
{
    private $config = array();
    
    private $api_head = 'https://public-api.wordpress.com/rest/v1.1';
    
    private $access_token = '';
    
    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Set the scope 
     *
     * @param array $params
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return 'https://public-api.wordpress.com/oauth2/authorize?client_id='.$this->config['api_client_id'].'&redirect_uri='.$this->config['api_redirect_uri'].'&response_type=code';
    }
    
    public function getAccessToken($code){
        $curl = curl_init( 'https://public-api.wordpress.com/oauth2/token' );
        curl_setopt( $curl, CURLOPT_POST, true );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, array(
            'client_id'     => $this->config['api_client_id'],
            'redirect_uri'  => $this->config['api_redirect_uri'],
            'client_secret' => $this->config['api_secret'],
            'code'          => $code, 
            'grant_type'    => 'authorization_code'
        ) );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
        $auth = curl_exec( $curl );
        $secret = json_decode($auth);
        $this->access_token = $secret->access_token;
        return $this->access_token;
    }
    
    public function setAccessToken($token){
        $this->access_token = $token;
    }
    
    public function api($resource, array $urlParams=array(), $method='GET', $postParams=array()){
          $url = $this->generateUrl($resource, $urlParams);
          return $this->send($url, $postParams, $method);
    }
    
    public function send($url, $params = array(), $method = 'GET')
    {
        if( $method === 'POST' ){
            $options = array(
                'http' =>
                array(
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' =>
                    array(
                        0 => 'authorization: Bearer '.$this->access_token,
                        1 => 'Content-Type: application/x-www-form-urlencoded',
                    ),
                    'content' => http_build_query($params),
                ),
            );
        }
        else{
            $options = array(
                'http' =>
                    array(
                        'ignore_errors' => true,
                        'method' => 'GET',
                        'header' =>
                            array(
                                0 => 'authorization: Bearer '.$this->access_token
                            ),
                        'content' => http_build_query($params),
                    ),
            );

            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);
        }
        $response = json_decode($response);
        return $response;
    }
    
    public function generateUrl($url, $params){
        if ($params) {
            //it needs to be PHP_QUERY_RFC3986. We want to have %20 between scopes
            // we cant run http_build_query($params, null, '&', PHP_QUERY_RFC3986); because it is not supported in php 5.3 or hhvm
            $url .= '?';
            foreach ($params as $key => $value) {
                $url .= sprintf('%s=%s&', rawurlencode($key), rawurlencode($value));
            }
            $url=rtrim($url, '&');
        }
        return $this->api_head.$url;
    }

}
?>
