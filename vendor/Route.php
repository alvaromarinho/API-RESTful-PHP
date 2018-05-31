<?php 

class Route
{
    private static $_args;
    private static $_version;
    private static $_resource;

    public function __construct()
    {
        $uri = explode('/', trim(substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '/api/')), '/'));

        if (isset($uri[1]))
            self::$_version = $uri[1];
        else
            die(Resources::response(400, 'Version not specified.'));

        if (isset($uri[2]))
            self::$_resource = $uri[2];
        else
            die(Resources::response(400, 'Resource not specified.'));

        if (isset($uri[4]) && $_SERVER['REQUEST_METHOD'] == 'GET')
            self::$_args = isset($uri[3]) ? self::setArgs($uri[4], $uri[3]) : null;
        else
            self::$_args = isset($uri[3]) ? self::setArgs($uri[3]) : null;

    }
    /**
     * STATIC: FIELDS, ORDER, R_ORDER, GROUP, LIMIT, OFFSET
     */
    private function setArgs($string, $id = null)
    {
        $statics = ['fields', 'filter', 'order', 'group', 'limit', 'offset'];

        if (strstr($string, '&')) {
            $array = explode("&", $string);
            foreach ($array as $parameter) {
                $values = explode("=", $parameter);
                $args[reset($values)] = end($values);
            }
        } else if (strstr($string, '=')) {
            $array = explode("=", $string);
            $args = [reset($array) => end($array)];
        } else
            return $string;

        foreach ($statics as $static) {
            if (array_key_exists($static, $args)) {
                $result[$static] = $args[$static];
                unset($args[$static]);
            }
        }

        if ($id)
            $result['id'] = $id;

        return $result;
    }

    public function getArgs()
    {
        return self::$_args;
    }

    public function getVersion()
    {
        return self::$_version;
    }

    public function getResource()
    {
        return self::$_resource;
    }
}
