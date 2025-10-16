<?php

namespace Alternc\API\Auth;

use Alternc\API\APIResponse;
use Alternc\API\Auth\Exceptions\DisabledAccount;
use Alternc\API\Auth\Exceptions\PasswordInvalid;
use Alternc\API\DB;
use AltoRouter;
use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    public function __construct(AltoRouter $router) {
        $router->map("POST", "/auth/login", [$this, "login"]);
    }

    private static function get_signing_keypair(Connection $db)
    {
        $db_request = $db
            ->createQueryBuilder()
            ->select('value')
            ->from('variable')
            ->where('name = "api_singing_keypair"');

        $keyPair = $db_request->fetchAssociative();

        if (empty($keyPair)) {
            $keyPair = sodium_crypto_sign_keypair();
            $keyPairEncoded = sodium_bin2hex($keyPair);
            $queryBuilder = $db
                ->createQueryBuilder()
                ->insert('variable')
                ->values([
                    'name' => 'api_singing_keypair',
                    'value' => ':value',
                    'comment' => 'The NaCl signing keypair for API authentication',
                ])
                ->setParameter('value', $keyPairEncoded);
            $db_keypair_request = $queryBuilder->executeQuery();

            if ($db_keypair_request->rowCount() === 0) {
                die("Failed to insert keypair into database");
            }

            return $keyPair;
        }

        return sodium_hex2bin($keyPair["value"]);
    }

    public function login(): APIResponse
    {
        $db = DB::pdo();
        $keyPair = Auth::get_signing_keypair($db);

        if (!isset($_POST["username"]) || !isset($_POST["password"])) {
            return APIResponse::unauthorized(["error" => "Missing required parameters"]);
        }

        $username = $_POST["username"];
        $password = $_POST["password"];

        try {
            $user = User::login($username, $password, $db);
        } catch (DisabledAccount) {
            return APIResponse::unauthorized(["error" => "Account disabled"]);
        } catch (PasswordInvalid) {
            return APIResponse::unauthorized(["error" => "Invalid password"]);
        }

        $privateKey = base64_encode(sodium_crypto_sign_secretkey($keyPair));

        $iat = time();
        $exp = $iat + (7 * 60 * 60 * 24);

        $payload = [
            'iss' => 'alternc.com',
            'aud' => 'alternc.com',
            'iat' => $iat,
            'nbf' => $iat,
            'exp' => $exp,
            'sub' => $user->uid,
            'su' => $user->is_admin,
        ];

        $jwt = JWT::encode($payload, $privateKey, 'EdDSA');
        return APIResponse::ok(
            ["refresh_token" => $jwt],
        );
    }

    public static function verify_auth(Connection $db): int {
        $keyPair = Auth::get_signing_keypair($db);
        $publicKey = base64_encode(sodium_crypto_sign_publickey($keyPair));

        $authorization = getallheaders()["Authorization"];
        $jwt = explode(" ", $authorization)[1];

        $decoded = JWT::decode($jwt, new Key($publicKey, 'EdDSA'));
        $uid = $decoded->sub;

        return $uid;
    }
}