<?php

require_once "php-jwt/BeforeValidException.php";
require_once "php-jwt/ExpiredException.php";
require_once "php-jwt/SignatureInvalidException.php";
require_once "php-jwt/JWT.php";

use \Firebase\JWT\JWT;

class Auth
{
    private static $key = "SUA_KEY";

    public static function login($array)
    {
        if (array_key_exists('Authtoken', $array)) {
            try {
                JWT::decode($array['Authtoken'], self::$key, array('HS512'));
                return true;
            } catch (Exception $e) {
                die(Resources::response(401, 'Wrong token.'));
            }
        }
        return false;
    }

    public static function token($array)
    {
        $sql = 'SELECT password FROM users WHERE username = ?';
        $stmt = Connection::prepare($sql);
        $stmt->bindParam(1, $array['username']);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            die(Resources::response(500, $e->getMessage()));
        }
        $pass = $stmt->fetch()->password;
        if ($pass && crypt($array['password'], $pass) == $pass) {
            $config = array(
                "iat" => time(), 					// time when the token was generated
                "exp" => time() + 60 * 60,        // time when the token was expired	
                "iss" => $_SERVER['SERVER_NAME'],	// A string containing the name or identifier of the application
            );
            return ['Authtoken' => JWT::encode($config, self::$key, 'HS512')];
        } else
            die(Resources::response(401, 'Wrong username or password.'));
    }

    public static function crypt($password)
    {
        $salt = uniqid();
        $rounds = '5042';
        $cryptSalt = '$6$rounds=' . $rounds . '$' . $salt . '$';
        return ['crypt' => crypt($password, $cryptSalt)];
    }
}
