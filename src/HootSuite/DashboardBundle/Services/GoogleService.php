<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace HootSuite\DashboardBundle\Services;

use Google_Client;
use Google_Service_Oauth2;
use Google_Service_Plus;

class GoogleService
{
    private $redirecturi = '';
    private $config = array();
    private $_client;
    private $_service;
    private $_user;

    public function __construct(array $config)
    {
        $this->config = $config;
        $client = new Google_Client();
        if(isset($this->config['client_id']))
        {
            $client->setClientId($this->config['client_id']);
            $client->setClientSecret($this->config['client_secret']);
            $client->setRedirectUri($this->config['redirectUri']);
        }
        $client->addScope(array('https://www.googleapis.com/auth/plus.login',
            'https://www.googleapis.com/auth/plus.me',
            'https://www.googleapis.com/auth/plus.circles.read',
            'https://www.googleapis.com/auth/plus.circles.write',
            'https://www.googleapis.com/auth/plus.stream.read',
            'https://www.googleapis.com/auth/plus.stream.write',
            'https://www.googleapis.com/auth/plus.pages.manage',
            'https://www.googleapis.com/auth/plus.media.readwrite',
            'https://www.googleapis.com/auth/plus.media.upload','email','profile'));
        $this->_client = $client;
        $this->_user = new Google_Service_Oauth2($client);
        $this->_service = new Google_Service_Plus($client);
    }
    public function setAccessToken($token)
    {
        $this->_client->setAccessToken($token);
    }
    public function loginUrl() {
        return $this->_client->createAuthUrl();
    }
    public function fetchAccessToken($code) {
        $this->_client->authenticate($code);
        return $this->_client->getAccessToken();
    }
    public function getUserInfo(){
        return $this->_user->userinfo->get();
    }
    public function getPages($user_id){
        return $this->_service->people->get($user_id)->getUrls();
    }
}

