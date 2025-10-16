<?php

namespace Alternc\API;

enum RequestType: string {
    case GET = "GET";
    case POST = "POST";
    case PUT = "PUT";
    case DELETE = "DELETE";

    function route(string $name, mixed $target): array
    {
        return [$this->value, $name, $target];
    }
}