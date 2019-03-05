<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace HootSuite\DashboardBundle\Services;

use DirkGroenen\Pinterest\Pinterest;

/**
 * Extends the LinkedIn class 
 */
class PinterestService extends Pinterest
{  

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config['apiKey'],$config['apiSecret']);
    }
    public function setAccessToken($token)
    {
        $this->request->setAccessToken($token);
    }
}
?>
