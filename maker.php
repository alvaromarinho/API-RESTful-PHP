<?php 

error_reporting(E_ALL);
ini_set("display_errors", 1);

define('DS', DIRECTORY_SEPARATOR);

require_once "Connection.php";

$sql   = "SELECT table_name AS 'name', table_comment AS 'relationship' FROM information_schema.tables WHERE table_schema = SCHEMA()";
$stmt  = Connection::prepare($sql);
$stmt->execute();
$tables = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<title>Maker API</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" href="<?= 'img/icon.png' ?>" />
		<!--[if IE]><link rel="shortcut icon" href="<?= 'img/icon.ico' ?>"><![endif]-->
		<link rel="stylesheet" href="https://getbootstrap.com/dist/css/bootstrap.min.css">

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

				<div class="form-group row">
					<div class="col-12">
						<div class="card border-dark">
							<div class="card-header bg-dark">Tables</div>
							<div class="card-body">
								<div class="btn-checkbox" data-toggle="buttons">
									<div class="row">
										<?php foreach ($tables as $table) { 
											echo "<div class='col-3 form-group'><label class='btn btn-outline-secondary btn-block'><input type='checkbox' name='tables[]' id='".$table->name."' value='".$table->name."'>".ucwords($table->name)."</label></div>"; 
										} ?>
									</div>
								</div>
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

		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
		<script src="https://getbootstrap.com/dist/js/bootstrap.min.js"></script>
	</body>
</html>

<?php 

if(!empty($_POST)){
	foreach ($_POST['tables'] as $table) {
		
		$html 	 = 	null;
		$count 	 = 	null;
		$default =  array();
		$fields  =  array();

		$sql     =  "SELECT 
						column_name     AS 'field', 
						column_default  AS 'default'
					FROM information_schema.columns 
					WHERE table_name = '".$table."' AND table_schema = SCHEMA()";
		$stmt  	=   Connection::prepare($sql);
		$stmt->execute();
		$describe = $stmt->fetchAll();

		foreach ($describe as $d) {
			$fields[] = $d->field;
			if(!empty($d->default))
				$default[$d->field] = ($d->default == "CURRENT_TIMESTAMP") ? "date('Y-m-d H:i:s')" : $d->default;
		}

		// CREATE
		$html = "<?php\n\nrequire_once 'Connection.php';\nrequire_once 'Resources.php';\n\nclass ".ucfirst($table)." extends Resources\n{\n\tpublic static function create()\n\t{\n\t\t";
		if(!empty($default))
			foreach ($default as $key => $value) 
				$html .= "\$_POST['".$key."'] = \$_POST['".$key."'] ?: ".$value.";\n\t\t";
		$html .= "\n\t\t\$fields = array_keys(\$_POST);\n\t\t\$values = str_repeat('?,', count(\$fields));\n\t\t\$sql  	= 'INSERT INTO ".$table." ('.implode(', ', \$fields).') VALUES ('.substr(\$values, 0, -1).')';\n\t\t\$stmt 	= Connection::prepare(\$sql);\n\t\tforeach (\$fields as \$key => \$value)\n\t\t\t\$stmt->bindParam(\$key+1, \$_POST[\$value]);\n\n\t\ttry {\n\t\t\t\$stmt->execute();\n\t\t} catch (PDOException \$e) {\n\t\t\treturn end(\$e->errorInfo);\n\t\t}\n\n\t\treturn 'Created successfully!';\n\t}\n\n\t";

		// READ
		$html .= "public static function read(\$id = null)\n\t{\n\t\t\$where = \$id ? ' WHERE id = '.\$id : '';\n\t\t\$sql   = 'SELECT * FROM ".$table."'.\$where;\n\t\t\$stmt  = Connection::prepare(\$sql);\n\t\ttry {\n\t\t\t\$stmt->execute();\n\t\t} catch (PDOException \$e) {\n\t\t\treturn end(\$e->errorInfo);\n\t\t}\n\t\treturn \$stmt->fetchAll();\n\t}\n\n\t";

		// UPDATE
		$html .= "public static function update(\$id)\n\t{\n\t\t\$_PUT 	= parent::parse_raw_http_request();\n\t\t\$fields = array_keys(\$_PUT);\n\t\t\$sql  	= 'UPDATE ".$table." SET '.implode(' = ?, ', \$fields).' = ? WHERE id = '.\$id;\n\t\t\$stmt 	= Connection::prepare(\$sql);\n\t\tforeach (\$fields as \$key => \$value)\n\t\t\t\$stmt->bindParam(\$key+1, \$_PUT[\$value]);\n\n\t\ttry {\n\t\t\t\$stmt->execute();\n\t\t} catch (PDOException \$e) {\n\t\t\treturn end(\$e->errorInfo);\n\t\t}\n\n\t\treturn 'Updated successfully!';\n\t}\n\n\t";

		// DELETE
		$html .= "public static function delete(\$id)\n\t{\n\t\t\$sql   = 'DELETE FROM posts WHERE id = '.\$id;\n\t\t\$stmt  = Connection::prepare(\$sql);\n\n\t\ttry {\n\t\t\t\$stmt->execute();\n\t\t} catch (PDOException \$e) {\n\t\t\treturn end(\$e->errorInfo);\n\t\t}\n\n\t\treturn 'Deleted successfully!';\n\t}\n";
		
		$html .= "}\n";
		file_put_contents('resource'.DS.$table.'.php', $html);
		chmod('resource'.DS.$table.'.php', 0777);
		
		/* DELETING EMPTY FILES */
		if(file_exists('resource/empty'))
			unlink('resource/empty');
	}

	echo "<script>alert('Maker executed successfully!')</script>";
} 
