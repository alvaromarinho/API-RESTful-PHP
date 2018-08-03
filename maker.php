<?php 

// error_reporting(E_ALL);
// ini_set("display_errors", 1);

define('DS', DIRECTORY_SEPARATOR);

require_once "vendor/Connection.php";

$sql = "SELECT table_name AS 'name', table_comment AS 'relationship' FROM information_schema.tables WHERE table_schema = SCHEMA()";
$stmt = Connection::prepare($sql);
$stmt->execute();
$tables = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<title>Maker API</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">

		<style>
			html { position: relative; min-height: 100%; }
			body { margin-top: 55px !important; margin-bottom: 30px !important; }
			footer { position: absolute; bottom: 0; width: 100%; height: 30px; line-height: 30px; font-size: 12px; }
			.btn input[type=checkbox] { position: absolute; clip: rect(0,0,0,0); pointer-events: none; }
			.btn-checkbox label { margin-bottom: 0px; }
			.card .bg-dark { color: #fff; }
		</style>

	</head>
	<body>
		<nav class="navbar navbar-expand-md bg-dark navbar-dark fixed-top">
			<a class="navbar-brand" href="#">API RESTful PHP</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
				<span class="navbar-toggler-icon"></span>
			</button>
		</nav>
		<div class="container">

			<div class="row pt-3 mb-3">
				<div class="col-12">
					<h1 class="display-4">Maker API script</h1>
				</div>
			</div>

			<form method="post" action="">

				<div class="card border-dark mb-3">
					<div class="card-header bg-dark">Tables</div>
					<div class="card-body">
						<div class="btn-checkbox" data-toggle="buttons">
							<div class="row">
								<?php foreach ($tables as $table) {
								echo "<div class='col-3 form-group'><label class='btn btn-outline-secondary btn-block'><input type='checkbox' name='tables[]' id='" . $table->name . "' value='" . $table->name . "'>" . ucwords($table->name) . "</label></div>";
							} ?>
							</div>
						</div>
					</div>
				</div>

				<div class="card border-dark mb-3">
					<div class="card-header bg-dark">Options</div>
					<div class="card-body">
						<div class="row">
							<div class="col-3">
								<label for="version">Version</label>
								<input type="number" class="form-control" name="version" id="version" step="0.1">
							</div>
						</div>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-12 mb-3">
						<div class="col-2 offset-5 text-center">
							<button class="btn btn-lg btn-primary btn-block">MAKE</button>
						</div>
					</div>
				</div>

			</form>

		</div>
		<footer class="footer bg-dark">
			<div class="container text-center">
				<span class="text-light">By <a href="https://alvaromarinho.com.br" target="_blank">Alvaro Marinho</a></span>
			</div>
		</footer>

		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
	</body>
</html>

<?php 

if (!empty($_POST)) {

	if (!isset($_POST['tables'])) {
		echo "<script>alert('Select one table!')</script>";
		die();
	}

	if (empty($_POST['version'])) {
		echo "<script>alert('Version can not be empty!')</script>";
		die();
	}

	foreach ($_POST['tables'] as $table) {

		$html = null;
		$count = null;
		$default = array();
		$fields = array();

		$sql = "SELECT 
						column_name     AS 'field', 
						column_default  AS 'default'
					FROM information_schema.columns 
					WHERE table_name = '" . $table . "' AND table_schema = SCHEMA()";
		$stmt = Connection::prepare($sql);
		$stmt->execute();
		$describe = $stmt->fetchAll();

		foreach ($describe as $d) {
			$fields[] = $d->field;
			if (!empty($d->default))
				$default[$d->field] = ($d->default == "CURRENT_TIMESTAMP") ? "date('Y-m-d H:i:s')" : $d->default;
		}

		// CREATE
		$html = "<?php\n\nclass " . ucfirst($table) . "\n{\n\tpublic static function create()\n\t{\n\t\t";
		if (!empty($default))
			foreach ($default as $key => $value)
				$html .= "\$_POST['" . $key . "'] = isset(\$_POST['" . $key . "']) ? \$_POST['" . $key . "'] : " . $value . ";\n\t\t";
		$html .= "\n\t\t\$fields = array_keys(\$_POST);\n\t\t\$values = str_repeat('?,', count(\$fields));\n\t\t\$sql = 'INSERT INTO " . $table . " ('.implode(', ', \$fields).') VALUES ('.substr(\$values, 0, -1).')';\n\t\t\$stmt = Connection::prepare(\$sql);\n\t\tforeach (\$fields as \$key => \$value)\n\t\t\t\$stmt->bindParam(\$key+1, \$_POST[\$value]);\n\t\ttry {\n\t\t\t\$stmt->execute();\n\t\t} catch (PDOException \$e) {\n\t\t\tdie(Resources::response(500, \$e->getMessage()));\n\t\t}\n\t\treturn self::read(['id' => Connection::lastInsertId()]);\n\t}\n\n\t";

		// READ
		$html .= "public static function read(\$args = null)\n\t{\n\t\t\$sql = Resources::mountSql(\$args, '" . $table . "');\n\t\t\$stmt = Connection::prepare(\$sql);\n\t\ttry {\n\t\t\t\$stmt->execute();\n\t\t} catch (PDOException \$e) {\n\t\t\tdie(Resources::response(500, \$e->getMessage()));\n\t\t}\n\t\treturn \$stmt->fetchAll();\n\t}\n\n\t";

		// UPDATE
		$html .= "public static function update()\n\t{\n\t\t\$_PUT = Resources::parseRawHttpRequest();\n\t\t\$id = \$_PUT['id'];\n\t\tunset(\$_PUT['id']);\n\t\t\$fields = array_keys(\$_PUT);\n\t\tif (empty(\$fields))\n\t\t\tdie(Resources::response(500, 'No data to update.'));\n\t\t\$sql = 'UPDATE " . $table . " SET '.implode(' = ?, ', \$fields).' = ? WHERE id = '.\$id;\n\t\t\$stmt = Connection::prepare(\$sql);\n\t\tforeach (\$fields as \$key => \$value)\n\t\t\t\$stmt->bindParam(\$key+1, \$_PUT[\$value]);\n\t\ttry {\n\t\t\t\$stmt->execute();\n\t\t} catch (PDOException \$e) {\n\t\t\tdie(Resources::response(500, \$e->getMessage()));\n\t\t}\n\t\treturn self::read(['id' => \$id]);\n\t}\n\n\t";

		// DELETE
		$html .= "public static function delete(\$id)\n\t{\n\t\t\$sql = 'DELETE FROM posts WHERE id = '.\$id;\n\t\t\$stmt = Connection::prepare(\$sql);\n\t\ttry {\n\t\t\t\$stmt->execute();\n\t\t} catch (PDOException \$e) {\n\t\t\tdie(Resources::response(500, \$e->getMessage()));\n\t\t}\n\t\treturn [];\n\t}\n";

		$html .= "}\n";

		$path = 'v' . $_POST['version'] . DS;
		if (!is_dir($path))
			mkdir($path);

		file_put_contents($path . $table . '.php', $html);
		chmod($path . $table . '.php', 0777);
	}

	echo "<script>alert('Maker executed successfully!')</script>";
} 
