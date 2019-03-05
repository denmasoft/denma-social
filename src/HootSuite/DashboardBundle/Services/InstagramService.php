<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace HootSuite\DashboardBundle\Services;

use MetzWeb\Instagram\Instagram;

/**
 * Extends the LinkedIn class 
 */
class InstagramService extends Instagram
{  

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {        

        parent::__construct($config);
    }    
}
?>
