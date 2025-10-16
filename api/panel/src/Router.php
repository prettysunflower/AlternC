<?php

namespace Alternc\API;

use AltoRouter;

abstract class Router {
    abstract public function __construct(AltoRouter $router);
}