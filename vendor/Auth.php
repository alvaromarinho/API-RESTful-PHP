<?php

require_once "php-jwt/BeforeValidException.php";
require_once "php-jwt/ExpiredException.php";
require_once "php-jwt/SignatureInvalidException.php";
require_once "php-jwt/JWT.php";

use \Firebase\JWT\JWT;

class Auth
{
    private static $key = "SUA_KEY";

    public static function login($array, $_access)
    {
        if (array_key_exists('Authorization', $array)) {
            try {
                $decode = JWT::decode($array['Authorization'], self::$key, array('HS512'));
                if (!in_array($decode->data->role, $_access->getRole())) {
                    throw new Exception("Forbidden", 403);
                }
                return true;
            } catch (SignatureInvalidException $e) {
                die(Utils::response(500, $e->getMessage()));
            } catch (ExpiredException $e) {
                die(Utils::response(400, $e->getMessage()));
            } catch (Exception $e) {
                die(Utils::response($e->getCode(), $e->getMessage()));
            }
        }
        return false;
    }

    public static function token($array)
    {
        $sql = 'SELECT id, password, role FROM users WHERE username = ?';
        $stmt = Connection::prepare($sql);
        $stmt->bindParam(1, $array['username']);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            die(Utils::response(500, $e->getMessage()));
        }
        $fetch = $stmt->fetch();
        $pass = $fetch->password;
        if ($pass && crypt($array['password'], $pass) == $pass) {
            $config = array(
                'iat' => time(), // time when the token was generated
                'exp' => time() + 60 * 60, // time when the token was expired
                'iss' => $_SERVER['SERVER_NAME'], // A string containing the name or identifier of the application
                'data' => ['id' => $fetch->id, 'role' => $fetch->role],
            );
            return ['Authorization' => JWT::encode($config, self::$key, 'HS512')];
        } else {
            die(Utils::response(401, 'Wrong username or password.'));
        }

    }

    public static function crypt($password)
    {
        $salt = uniqid();
        $rounds = '5042';
        $cryptSalt = '$6$rounds=' . $rounds . '$' . $salt . '$';
        return ['crypt' => crypt($password, $cryptSalt)];
    }
}
