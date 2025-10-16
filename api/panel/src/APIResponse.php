<?php

namespace Alternc\API;

class APIResponse {
    public string|array $message;
    public int $status_code;

    private function __construct($message, $status_code)
    {
        $this->message = $message;
        $this->status_code = $status_code;
    }

    public static function ok($data)
    {
        return new self($data, 200);
    }

    public static function created($data)
    {
        return new self($data, 201);
    }

    public static function bad_request($data)
    {
        return new self($data, 400);
    }

    public static function not_found($data)
    {
        return new self($data, 404);
    }

    public static function unauthorized($data)
    {
        return new self($data, 401);
    }

    public static function forbidden($data)
    {
        return new self($data, 403);
    }

    public static function internal_server_error($data) {
        return new self($data, 500);
    }

    public static function conflict($data) {
        return new self($data, 409);
    }
}