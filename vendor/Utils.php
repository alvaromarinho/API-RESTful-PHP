<?php

class Utils
{
    public static function parseRawHttpRequest()
    {
        // read incoming data
        $input = file_get_contents('php://input');

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block) {
            if (empty($block)) {
                continue;
            }

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== false) {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
            }
            // parse all other fields
            else {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            $a_data[$matches[1]] = $matches[2];
        }

        return isset($a_data) ? $a_data : [];
    }

    public static function response(string $code, string $message, array $data = [])
    {
        $status = [1 => 'info', 2 => 'success', 3 => 'redirect', 4 => 'error', 5 => 'error'];
        header('url:' . $_SERVER['REQUEST_URI']);
        header('HTTP/1.1 ' . $code);
        return json_encode([
            'status' => $status[$code[0]],
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function mountSql($route, $table)
    {
        /*
            REQUEST
            /api/v1/users/1?fields=username,role&filter=bio LIKE '%The Boss%' AND situation:'A' OR situation!:'I' AND role BETWEEN 'ROLE_ADMIN' AND 'ROLE_USER'&group=situation&order=name,id&limit=1&offset=0&planId=3

            RESULT
            SELECT username,role FROM users WHERE id = 1 AND bio LIKE '%The Boss%' AND situation='A' OR situation!='I' AND role BETWEEN 'ROLE_ADMIN' AND 'ROLE_USER' GROUP BY situation ORDER BY name,id LIMIT 1 OFFSET 0
        */

        $params = (is_object($route) && $route->getQueryParams()) ? $route->getQueryParams() : (is_array($route) ? $route : []);

        $fields = '*';
        if (array_key_exists('fields', (array) $params)) {
            $fields = $params['fields'];
            $route->unsetQueryParams('fields');
        }

        $filter = '';
        if (array_key_exists('filter', (array) $params)) {
            $filter = str_replace(':', '=', $params['filter']);
            $route->unsetQueryParams('filter');
        }

        $group = '';
        if (array_key_exists('group', (array) $params)) {
            $group = ' GROUP BY ' . $params['group'];
            $route->unsetQueryParams('group');
        }

        $order = '';
        if (array_key_exists('order', (array) $params)) {
            $order = ' ORDER BY ' . $params['order'];
            $route->unsetQueryParams('order');
        }

        $limit = '';
        if (array_key_exists('limit', (array) $params)) {
            $limit = ' LIMIT ' . $params['limit'];
            $route->unsetQueryParams('limit');
        }

        $offset = '';
        if (array_key_exists('offset', (array) $params)) {
            $offset = ' OFFSET ' . $params['offset'];
            $route->unsetQueryParams('offset');
        }

        $id = (is_object($route) && $route->getEntity()) ? ' id = ' . $route->getEntity() : (is_array($route) ? ' id = ' . $route['id'] : []);

        $where = !empty($id) || !empty($filter) ? ' WHERE ' : '';
        $where .= !empty($id) ? $id : '';
        $where .= !empty($id) && !empty($filter) ? ' AND ' : '';
        $where .= urldecode($filter);

        return 'SELECT ' . $fields . ' FROM ' . $table . $where . $group . $order . $limit . $offset;
    }
}
