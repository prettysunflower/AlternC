<?php

use Alternc\API\APIResponse;

include 'vendor/autoload.php';
include_once 'bootstrap.php';

function post_bool($key)
{
    return isset($_POST[$key]) && (
            $_POST[$key] == 'on' ||
            $_POST[$key] == '1' ||
            $_POST[$key] == 'true');
}

$router = new AltoRouter();

Alternc\API\RouterInit::init($router);

$match = $router->match();

if( is_array($match) && is_callable( $match['target'] ) ) {
    $result = call_user_func_array( $match['target'], $match['params'] );
    if ($result instanceof APIResponse) {
        header("Content-Type: application/json");
        http_response_code($result->status_code);
        echo json_encode($result->message);
    }
} else {
    header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}