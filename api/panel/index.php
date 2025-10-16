<?php

use Alternc\API\APIResponse;
use Alternc\API\Auth\Auth;
use Alternc\API\Auth\User;
use Alternc\API\DB;
use JetBrains\PhpStorm\NoReturn;

include 'vendor/autoload.php';
include_once 'bootstrap.php';

function post_bool($key)
{
    return isset($_POST[$key]) && (
            $_POST[$key] == 'on' ||
            $_POST[$key] == '1' ||
            $_POST[$key] == 'true');
}

#[NoReturn]
function return_api_response(APIResponse $api_response): void
{
    header("Content-Type: application/json");
    http_response_code($api_response->status_code);
    echo json_encode($api_response->message);
    die();
}

$router = new AltoRouter();

Alternc\API\RouterInit::init($router);

$match = $router->match();

if( is_array($match) && is_callable( $match['target'] ) ) {
    $params = $match['params'];
    $db = DB::pdo();

    try {
        // Check if the target function has a user parameter
        // If so, authenticate the user and add it to the parameters

        $userParameter = new ReflectionParameter($match['target'], 'user');
        try {
            $uid = Auth::verify_auth($db);
            $user = User::from_uid($uid, $db);
        } catch (Exception) {
            return_api_response(APIResponse::unauthorized(["error" => "Unauthorized"]));
        }

        if (is_null($user)) {
            return_api_response(APIResponse::unauthorized(["error" => "Unauthorized"]));
        }

        global $cuid;
        $cuid = $uid;
        $params["user"] = $user;
    } catch (ReflectionException $e) {
        // No user parameter, do not proceed with authentication
    }

    try {
        $dbParameter = new ReflectionParameter($match['target'], 'db');
        $params["db"] = $db;
    } catch (ReflectionException $e) {
        // No db parameter, do not a database connection to the function
    }

    $result = call_user_func_array( $match['target'], $params);

    if ($result instanceof APIResponse) {
        return_api_response($result);
    }
} else {
    header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}