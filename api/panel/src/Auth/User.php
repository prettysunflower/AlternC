<?php

namespace Alternc\API\Auth;

use Alternc\API\Auth\Exceptions\DisabledAccount;
use Alternc\API\Auth\Exceptions\PasswordInvalid;
use Doctrine\DBAL\Connection;

class User {
     public string $name;
     public int $uid;
     public bool $is_admin;
     public bool $is_enabled;
     public string $email;

     public function __construct(string $name, int $uid, bool $is_admin, bool $is_enabled, string $email) {
        $this->name = $name;
        $this->uid = $uid;
        $this->is_admin = $is_admin;
        $this->is_enabled = $is_enabled;
        $this->email = $email;
     }

     private static function queryBuilder(Connection $db)
     {
         $queryBuilder = $db->createQueryBuilder();
         return $queryBuilder
             ->select(
                 'login AS name',
                 'uid',
                 'su AS is_admin',
                 'enabled AS is_enabled',
                 'mail AS email'
             )
             ->from('membres');
     }

     static function login(string $username, string $password, Connection $db) {
         $queryBuilder = User::queryBuilder($db)
             ->select('pass AS password')
             ->where('login = :login')
             ->setParameter('login', $username);

         $user = $queryBuilder->fetchAssociative();

         if (empty($user) || !password_verify($password, $user["password"])) {
             throw new PasswordInvalid("Invalid username or password");
         }

         $user = new User(...$user);

         if (!$user->is_enabled) {
             throw new DisabledAccount("The account is disabled");
         }

         return $user;
     }

     static function from_uid(int $uid, Connection $db): ?User
     {
         $db_request = User::queryBuilder($db)
             ->where('uid = :uid')
             ->setParameter('uid', $uid);

         $user = $db_request->fetchAssociative();

         if (empty($user)) {
             return null;
         }

         return new User(...$user);
     }
}