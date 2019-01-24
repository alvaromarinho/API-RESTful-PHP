<?php

class Route
{
    private $_uri;
    private $_version;
    private $_resource;
    private $_entity;
    private $_queryParams;

    public function __construct()
    {
        $_requestUri = trim(substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '/api/')), '/');

        if (strstr($_requestUri, '?')) {
            $_requestUriArray = explode('?', $_requestUri);
            $_queryParamsString = end($_requestUriArray);
            $_queryParams = explode('&', $_queryParamsString);
            foreach ($_queryParams as $q) {
                $_qp = explode('=', $q);
                $this->_queryParams[reset($_qp)] = end($_qp);
            }
            $_requestUri = reset($_requestUriArray);
        }

        $this->_uri = $_requestUri;
        $_uri = explode('/', $_requestUri);

        if (isset($_uri[1])) {
            $this->_version = $_uri[1];
        } else {
            die(Resources::response(400, 'Version not specified.'));
        }

        if (isset($_uri[2])) {
            $this->_resource = $_uri[2];
        } else {
            die(Resources::response(400, 'Resource not specified.'));
        }

        if (isset($_uri[3])) {
            $this->_entity = $_uri[3];
        }
    }

    public function getUri()
    {
        return $this->_uri;
    }

    public function getVersion()
    {
        return $this->_version;
    }

    public function getResource()
    {
        return $this->_resource;
    }

    public function getEntity()
    {
        return $this->_entity;
    }

    public function getQueryParams()
    {
        return $this->_queryParams;
    }

    public function unsetQueryParams($param)
    {
        unset($this->_queryParams[$param]);
    }

}
