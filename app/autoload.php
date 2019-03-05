<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->setPsr4('Happyr\\LinkedIn\\',__DIR__.'/../vendor/happyr/linkedin-api-client/src');
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
return $loader;
