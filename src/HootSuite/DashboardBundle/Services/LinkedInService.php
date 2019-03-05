<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace HootSuite\DashboardBundle\Services;

use Happyr\LinkedIn\LinkedIn;


/**
 * Extends the LinkedIn class 
 */
class LinkedInService extends LinkedIn
{
    /**
     * @var string scope
     *
     */
    protected $scope;

    private $redirect_uri;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->scope = $config['scope'];
        $this->redirect_uri = $config['redirect_uri'];
        parent::__construct($config['app_id'], $config['app_secret']);
    }

    /**
     * Set the scope 
     *
     * @param array $params
     *
     * @return string
     */
    public function getLoginUrl($params = array())
    {
        if (!isset($params['scope']) || $params['scope'] == "") {
            $params['scope'] = $this->scope;
        }
        $params['redirect_uri'] = $this->redirect_uri;
        return parent::getLoginUrl($params);
    }

    /**
     * I overrided this function because I want the default user array to include email-address
     */
    protected function getUserFromAccessToken() {
        try {
            return $this->api('GET', '/v1/people/~');
        } catch (LinkedInApiException $e) {
            return null;
        }
    }

    public function getUser(){
        //:(id,firstName,lastName,formatted-name,email-address,headline,picture-url,public-profile-url)
        return $this->api('GET', '/v1/people/~');
    }
}
?>
