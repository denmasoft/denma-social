<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace HootSuite\DashboardBundle\Services;

use Facebook\Facebook;

/**
 * Extends the LinkedIn class 
 */
class FacebookService extends Facebook
{  
	private $callback_uri;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->callback_uri = $config['api_redirect_uri'];
    }
    public function getCallbackUrl(){
    	return $this->callback_uri;
    }    
}
?>
