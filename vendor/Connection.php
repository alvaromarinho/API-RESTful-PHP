<?php 

define('DB_NAME', 'api');
define('DB_HOST', 'localhost');
define('DB_PASS', 'root');
define('DB_USER', 'root');

class Connection
{
    protected static $db;

    private function __construct()
    {
        try {
            self::$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
    }

    private static function getConnection()
    {
        if (!self::$db)
            new Connection();

        return self::$db;
    }

    public static function prepare(string $sql)
    {
        return self::getConnection()->prepare($sql);
    }

    public static function lastInsertId()
    {
        return self::getConnection()->lastInsertId();
    }

}
