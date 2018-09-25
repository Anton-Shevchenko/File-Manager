<!DOCTYPE html>
<html>
<head>
	<title>редактирования файла | </title>
	<meta charset="utf-8">
</head>
<body>

	<?php
		$text = file_get_contents("uploads/" . $_GET['id']);

		function debug($arr)
		{
			echo '<pre>'. print_r($arr, true) .'</pre>';
		}

		// debug($_SERVER['SCRIPT_NAME']);
		// debug($_SERVER['REQUEST_URI']);
		// debug($_SERVER['REQUEST_URL']);
	?>

	<form method="POST" action="index.php" name="saveFile">
		<input type="text" name="file_<?= $_GET['id']?>" value="<?= $_GET['id']?>" /><br />
		<textarea name="<?= $_GET['id']?>" cols="30" rows="5"><?= $text?></textarea><br />
		<button><a href="index.php">Назад</a></button>
		<input type="submit" id="<?= $_GET['id'] ?>" name="done" value="сохранить">
	</form>
</body>
</html>