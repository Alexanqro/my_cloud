<?php

try {
    $connection = new PDO('mysql:host=localhost;dbname=my_cloud;charset=utf8', 'root', '');
} catch (\PDOException $e) {
    echo $e->getMessage();
}

require_once 'autoload.php';

$routes = include 'routes.php';

$url = $_SERVER['REDIRECT_URL'];

$method = $_SERVER['REQUEST_METHOD'];


$url = preg_replace('/(\d+\/){2,}/', '', $url);
$url = preg_replace('/\d+/', '', $url);



if (isset($routes[$url][$method])) {
    $handler = $routes[$url][$method];
    $parts = explode('::', $handler);
    $className = $parts[0];
    $methodName = $parts[1];

    if (class_exists($className) && method_exists($className, $methodName)) {
        call_user_func(array($className, $methodName));


    } else {
        http_response_code(404);
        echo '404 NOT FOUND';
    }
} else {
    http_response_code(404);
    echo '404 NOT FOUND';
}