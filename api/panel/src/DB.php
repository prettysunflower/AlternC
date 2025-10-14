<?php

namespace Alternc\API;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
class DB {
    static function pdo(): Connection {
        global $L_MYSQL_HOST, $L_MYSQL_DATABASE, $L_MYSQL_LOGIN, $L_MYSQL_PWD;

        $connectionParams = [
            'dbname' => $L_MYSQL_DATABASE,
            'user' => $L_MYSQL_LOGIN,
            'password' => $L_MYSQL_PWD,
            'host' => $L_MYSQL_HOST,
            'driver' => 'pdo_mysql',
        ];
        return DriverManager::getConnection($connectionParams);
    }
}