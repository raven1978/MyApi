<?php

require_once './define.php';

require_once './Class/Controller.php';
require_once './Class/Connect.php';

$config = include './Config/config.inc.php';

//            echo "<pre>";
//            print_r($_REQUEST);
//            echo "</pre>";exit();

//echo "<pre>";
//print_r($_REQUEST['payload']);
////print_r($config);
//echo "</pre>";

$uri = explode("/", $_REQUEST['payload']);

$headers = apache_request_headers();

//echo "<pre>";
//print_r($_SERVER);
//echo "</pre>";
//die();

//DB CONNECTION
$db = Connect::getInstance($config);

$controller = new Controller($headers, $_SERVER['REQUEST_METHOD'], $uri, $db);

$controller->analyzeRequest();

echo $controller->getResponse();
