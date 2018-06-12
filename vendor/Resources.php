<?php 

class Resources
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
			if (empty($block))
				continue;

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
		header('url:'.$_SERVER['REQUEST_URI']);
		header('HTTP/1.1 '.$code);
		return json_encode([
			'status' => $status[$code[0]],
			'message' => $message,
			'data' => $data
		]);
	}

	public static function mountSql($args, string $table)
	{
		$fields = '*';
		$filter = '';
		$order = '';
		$group = '';
		$limit = '';
		$offset = '';
		$where = '';
		$id = '';

		if (is_array($args)) {
			if (array_key_exists('fields', $args))
				$fields = str_replace(['+'], [" "], $args['fields']);
			if (array_key_exists('group', $args))
				$group = ' GROUP BY ' . $args['group'];
			if (array_key_exists('order', $args))
				$order = ' ORDER BY ' . str_replace(',', ' ', $args['order']);
			if (array_key_exists('limit', $args))
				$limit = ' LIMIT ' . $args['limit'];
			if (array_key_exists('offset', $args))
				$offset = ' OFFSET ' . $args['offset'];
			if (array_key_exists('id', $args))
				$id = ' id = ' . $args['id'];
			if (array_key_exists('filter', $args))
				$filter = str_replace(
					[':', '!:', ',', ';', '[]', '@', '*', '+'],
					["=", "!=", " AND ", " OR ", " BETWEEN ", " LIKE ", "%", " "],
					$args['filter']
				);
		} elseif (!empty($args))
			$id = ' id = ' . $args;

		if (!empty($id)) {
			$where = ' WHERE ' . $id;
			if (!empty($filter))
				$where .= ' AND ' . $filter;
		} else if (!empty($filter))
			$where = ' WHERE ' . $filter;

		return 'SELECT ' . $fields . ' FROM ' . $table . $where . $group . $order . $limit . $offset;
	}
}
