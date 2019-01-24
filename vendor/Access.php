<?php

class Access
{
    private $_method;
    private $_route;
    private $_role;

    public function __construct($_method, $_route, $_role = true)
    {
        $this->_method = $_method;
        $this->_route = $_route;
        $this->_role = $_role;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function getRoute()
    {
        return $this->_route;
    }

    public function getRole()
    {
        return $this->_role;
    }

    public static function hasAccess($route)
    {
        $result = array_filter(array_map(function ($a) use ($route) {
            if ($a->getMethod() != $_SERVER['REQUEST_METHOD']) {
                return false;
            }

            $_u = explode('/', $route->getUri());
            $_r = explode('/', trim($a->getRoute(), '/'));

            foreach ($_r as $key => $value) {
                if (substr($value, 0, 2) == '**')
                    break;
                if (substr($value, 0, 2) == '{$')
                    continue;
                if ($_u[$key] != $_r[$key])
                    return false;
            }

            return $a;
        }, [
            new Access('POST', '/api/v1/auth'),

            // new Access('GET', '/api/v1/tags/**', ['ROLE_ADMIN', 'ROLE_USER']),
            // new Access('POST', '/api/v1/tags/{$id}', ['ROLE_ADMIN', 'ROLE_USER']),
            // new Access('PUT', '/api/v1/tags/{$id}', ['ROLE_ADMIN', 'ROLE_USER']),
            // new Access('DELETE', '/api/v1/tags/{$id}', ['ROLE_ADMIN', 'ROLE_USER']),
        ]));

        return reset($result);
    }
}
