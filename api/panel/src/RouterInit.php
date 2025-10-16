<?php

namespace Alternc\API;

use Alternc\API\Auth\Auth;
use Alternc\API\Domain\DomainRouter;
use AltoRouter;

class RouterInit {
    public static function init(AltoRouter $router) {
        $router->setBasePath("/api");

        $router->map('GET', '/', function() {
            echo "Hello World!";
        });

        $authRouter = new Auth($router);
        $domainRouter = new DomainRouter($router);
    }
}