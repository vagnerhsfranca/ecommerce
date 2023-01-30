<?php 

require_once ("vendor/autoload.php");

use \Slim\Slim;

$app = new Slim();

$app->config("debug", true);

require_once ("includes.php");

$app->run();

?>