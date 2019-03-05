<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace HootSuite\DashboardBundle\Services;

use Abraham\TwitterOAuth\TwitterOAuth;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Extends the LinkedIn class 
 */
class TwitterService extends TwitterOAuth
{

    private $callbackRoute;
    private $callbackURL;
    private $session;
    /**
     * Set API URLS
     */
    function accessTokenURL()  { return 'https://api.twitter.com/oauth/access_token'; }
    function authenticateURL() { return 'https://api.twitter.com/oauth/authenticate'; }
    function authorizeURL()    { return 'https://api.twitter.com/oauth/authorize'; }
    function requestTokenURL() { return 'https://api.twitter.com/oauth/request_token'; }

    /**
     * Debug helpers
     */
    function lastStatusCode() { return $this->http_code; }
    function lastAPICall() { return $this->last_api_call; }
    /**
     * @param array $config
     */
    public function __construct(array $config,Session $session)
    {
        parent::__construct($config['consumer_key'],$config['consumer_secret']);
        $this->callbackURL = $config['callback_url'];
        $this->session = $session;
        //$this->setProxy(array());
    }
    public function getLoginUrl()
    {
        $requestToken = $this->oauth('oauth/request_token', array('oauth_callback' => $this->callbackURL));
        if (!isset($requestToken['oauth_token']) || !isset($requestToken['oauth_token_secret'])) {
            throw new \RuntimeException('Failed to validate oauth signature and token.');
        }
        $this->session->set('oauth_token', $requestToken['oauth_token']);
        $this->session->set('oauth_token_secret', $requestToken['oauth_token_secret']);
        switch ($this->getLastHttpCode()) {
            case 200:
                $redirectURL = $this->url('oauth/authorize',array('oauth_token' => $requestToken['oauth_token']));
                return $redirectURL;
                break;
            default:
                return null;
        }
    }
    public function getAccessToken($oauth_verifier){
        
        $this->setOauthToken($this->session->get('oauth_token'),$this->session->get('oauth_token_secret'));
        return $this->oauth("oauth/access_token", ["oauth_verifier" =>$oauth_verifier]);
    }
}
?>
