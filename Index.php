<?php

/*
* Refresh page 
*/

if ($_POST['done']) {
	header("Refresh:0");
}
if (isset($_POST['file'])) {
	header("Refresh:0");
}
if (isset($_POST['delete_file'])) {
	header("Refresh:0");
}

/*
* create url
*/

$base = './uploads';
$ajaxDirPath = $base . $_POST['path'];

if($_POST['is_ajax']) {
    if(sizeof(explode('uploads', $ajaxDirPath)) >= 3){
        $ajaxDirPath = str_replace('./uploads./', './', $ajaxDirPath);
    }
    $newScanDir = scandir($ajaxDirPath);

    $down_file_list = [];
    foreach ($newScanDir as $file) {
        $fileURL = preg_replace('#\/+#is', '/', $_SERVER["REQUEST_URI"] . '/' . $file);
        if ($file == '.' || $file == '..') continue;
        $newTable = [
            'name' => $file,
            'href' => $ajaxDirPath .'/' . $file,
            'date' => filemtime ($ajaxDirPath .'/' . $file),
            'size' => getFilesize($ajaxDirPath .'/' . $file),
            'mods' => (substr(decoct(fileperms($ajaxDirPath .'/' . $file)), 2)),
            'del' => 'Delete',
            'is_dir' => is_dir($ajaxDirPath .'/' . $file) ? true : false
        ];
        $down_file_list[] = $newTable;
    }

    $parseNewDir = json_encode($down_file_list);
    header("Content-type: application/json");
    print($parseNewDir);
    exit();
}

$base = './uploads';
$base_path = $base . $_SERVER['REQUEST_URI'];

$base_array = [];
if (is_dir($base_path)) {
	foreach (scandir($base_path) as $k => $va) {
		if ($k == '.' || $k == '..' && $va == '.' || $va == '..') continue;
		$file_url = preg_replace('#\/+#is', '/', $_SERVER["REQUEST_URI"]. '/'. $va);
		$aa = [];

		$aa['name'] = $va;
		$aa['mods'] = substr(decoct(fileperms($base . $file_url)),2);
		$aa['size'] = getFilesize($base . $file_url);
		$aa['date'] = filectime($base . $file_url);
		$aa['del'] = 'Удалить';

		array_push($base_array, $aa);
	}
}

?>



<?php

/*
*	Эту ф-цию я написал для себя, для того что бы мне было удобнее дебажить.
*/

function debug($arr)
{
	echo '<pre>'. print_r($arr, true) .'</pre>';
}

/*
*  create back url
*/

if ($base_path != '/') {
	$back_url = $_SERVER['REQUEST_URI'];
	$back_url = explode('/', $back_url);
	unset($back_url[sizeof($back_url) - 1]);
	$back_url = implode('/', $back_url);
	if ($back_url == '') $back_url = '/'; 
}

/*
*	start handling form 
*/

if (isset($_POST['save'])){
	$content = $_POST['content'];
	$path_to_save = $_POST['file_path'];

	$new_name = basename($_POST['new_name']);
	$old_name = basename($path_to_save);
	$dir_to_save = $base.$back_url.'/';

	if ($old_name != $new_name){
		rename($dir_to_save.$old_name, $dir_to_save.$new_name);
		file_put_contents($dir_to_save.$new_name, $content);
		header("Location: " . $back_url);
	} else {
		file_put_contents($path_to_save, $content);	
	}
}

if (isset($_POST['file'])) {
	$uploadfile = $base_path . '/' . basename($_FILES['userfile']['name']);
	move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile);
}

if (isset($_POST['delete_file'])) {
	$path_to_delet = 'uploads' . $_POST['delete_file'];
	rec($path_to_delet);
}

if (isset($_POST['change'])) {
	chmod($base . $_POST['name_file'], 0 .$_POST['access_change']);
}

/*
*	reqursion for delet file/dir
*/

function rec($dir) {
if (is_dir($dir)) {
	$files = array_diff(scandir($dir), array('.','..'));
foreach ($files as $file) {
	if(is_dir("$dir/$file")) { 
		rec("$dir/$file");
	} 
	else
		unlink("$dir/$file");
}
return rmdir($dir);
}
else
	unlink("$dir");
}

/*
* function for output pretty size file/dir
*/

function sum_sum($size) {
	if($size > 1024){
		$size = ($size/1024);
    	if($size > 1024){
    		$size = ($size/1024);
        	if($size > 1024) {
        		$size = ($size/1024);
        		$size = round($size, 1);
        		return $size." ГБ";       
        	} 
        	else {
        		$size = round($size, 1);
        		return $size." MБ";   
        	}       
    	} 
    	else {
   			$size = round($size, 1);
    		return $size." Кб";   
    	}  
    } 
    else {
    	$size = round($size, 1);
    	return $size." байт";   
    }	
    return $size;
}

/*
* function for output size (in bytes) not pretty
*/

function getFilesize($file) {
	if(!file_exists($file)) return "Файл  не найден";
	if (is_dir($file)) {
		$fileSize = 0;
	    $dir = array_diff(scandir($file), array('.','..'));   
	    foreach($dir as $files)
	    {
		    if(is_dir($file . '/' . $files))
		        $fileSize += getFilesize($file.'/'.$files);
		    else
            	$fileSize += filesize($file . '/' . $files);

	    }
	    return $fileSize;
	}
	elseif(is_file($file)) {
		$filesize = filesize($file);
		return $filesize;
	}
	else {
		echo "not cool!";
	}

}
/*
* function for sort/usort name/date/size file
*/

function sort_rsort($name, $key, $arr){
	foreach ($_POST as $key => $value) {
		$poz = $key;
	}
	$sort_field = $name;
	if ($_POST[$poz] == 'asc') {
		$_POST[$poz] = "desc";

	}
	else {
		$_POST[$poz] = "asc";
	}
	$k = $_POST[$poz];
	usort($arr, function($a, $b) use ($sort_field, $k){
		$retval = strnatcmp($a[$sort_field], $b[$sort_field]);
		if ($k == 'desc') { 
			return $retval;
		}
		else {
			return -$retval;
		}
	});
	return $arr;
}
if(isset($_POST['sort_field'])){
    $sort_field = $_POST['sort_field'];
}else {
    $sort_field = 'Name';
}

if(isset($_POST['sort_direction'])){
    $sort_direction = $_POST['sort_direction'];
} else {
    $sort_direction = "ASC";
}

$sort_direction == 'DESC' ? $sort_direction = 'ASC' : $sort_direction = 'DESC';

/*
* here cheking touch at button for sort/usort
*/ 
?>
<html>
<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
<?php
$base = './uploads';

// делаем путь через УРЛ к обьекту
$URI = $_SERVER["REQUEST_URI"];
$pregUri = preg_split("/([\/\?]{2}|[\&]+)level[0-9]+\=/", $URI);
$implodedUri = implode('/', $pregUri);
$path = $base . $implodedUri; // это истинный путь к обьекту

	?><a href="<?= $back_url; ?>"> наверх </a><?php

	if (is_dir($base_path)){
		 $filelist = scandir($base_path);
		if (isset($_POST['access'])) {	
			?>
				<form method="POST">
					<input type="text" name="access_change" value="<?= $_POST['access'];?>" >
					<input type="text" hidden name="name_file" value="<?= $_POST['name_access']?>">
					<input type="submit" name="change">
				</form>
			<?php
		}
		else {
				?>
				<table border="1" class="main">
					<thead>
						<tr>
							<th>
								<form method="POST" >
									<input type="submit" hidden  name="sort_direction" value=". <?= $sort_direction?> .">
									<input type="submit" data-field="name" name="sort_field" value="name">
								</form>
							</th>
							<th>Удалить</th>
							<th>Права</th>
							<th>
								<form method="POST">
									<input type="submit" hidden  name="$sort_direction" value=". <?= $sort_direction?> .">
									<input type="submit" data-field="size" name="sort_field" value="size">
								</form>
							</th>
							<th>
								<form method="POST">
									<input type="submit" hidden  name="$sort_direction" value=". <?= $sort_direction?> .">
									<input type="submit" data-field="date" name="sort_field" value="date">
								</form>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$base_array = [];
							if (is_dir($base_path)) {
								foreach (scandir($base_path) as $k => $va) {
									if ($k == '.' || $k == '..' && $va == '.' || $va == '..') continue;
									$file_url = preg_replace('#\/+#is', '/', $_SERVER["REQUEST_URI"]. '/'. $va);
									$aa = [];

									$aa['name'] = $va;
									$aa['del'] = 'Удалить';
									$aa['href'] = $file_url;
									$aa['mods'] = substr(decoct(fileperms($base . $file_url)),2);
									$aa['size'] = getFilesize($base . $file_url);
									$aa['date'] = filectime($base . $file_url);							
									$aa['is_dir'] = is_dir($base_path.'/'.$va) ? true : false;

									array_push($base_array, $aa);
								}
							}
							// debug($base_array);
							$parseToJS = json_encode($base_array);

							$sort_field = $_POST['sort_field'];
				            $sort_direction = $_POST['sort_direction'];
				            if (!is_null($sort_field)) {
				                usort($base_array, function($a, $b) use ($sort_field, $sort_direction){
				                    $retval = strnatcmp($a[$sort_field], $b[$sort_field]);
				                    return $sort_direction == 'DESC' ? $retval : -$retval;
				                });
				            }
							foreach ($base_array as $key => $value) {
							if ($f == '.' || $f == '..') continue;
							$file_url = preg_replace('#\/+#is', '/', $_SERVER["REQUEST_URI"]. '/'. $value['name']);
							?>
							<tr>
								<td><a href="<?= $file_url; ?>"><?= $value['name']; ?></a></td>
								<td>
									<form method="POST">
										<input type="submit" value="Удалить" name="delet" />
										<input type="text" hidden name="delete_file" value="<?= $file_url; ?>">
									</form>
								</td>
								<td>
									<form method="POST">
										<a href="" name="access"></a>
										<input type="text" hidden name="name_access" value="<?= $file_url; ?>">
										<input type="submit" name="access" value="<?= $value['mods'] ?>">
									</form>
								</td>
								<td><?= sum_sum($value['size']) ?></td>
								<td><?= date("Y-m-d", $value['date']) ?></td>
							</tr>		
						<?php
					}
					?>
					</tbody>

				</table>

				<script type="text/javascript">
					var enc = '<?= $parseToJS ?>';
        			var dec = $.parseJSON(enc); //получили кучу объектов с 3 ключами и значениями
        			var sort_direction = 1;
        			console.log(dec);

        			function trAp(dir ,tr, v, ko, lev=1) {
        				if (dir) {
        					if (ko == "ajax") {
        						tr.append([$('<td/>').text(lev + v.name),]);
	        				}
	        				else {
	        					tr.append([$('<td/>').text(v.name),]);
	        				}
        				}
        				else {
        					if (ko == "ajax") {
        					tr.append([$('<td/>').append($("<a/>").attr("href", v.href.replace('./uploads/', '/')).text(lev + v.name)),]);
	        				}
	        				else {
	        					tr.append([$('<td/>').append($("<a/>").attr("href", v.href.replace('./uploads/', '/')).text(v.name)),]);
	        				}
        				}
        				
        				tr.append([
		                    $('<td/>').append($("<form/>").attr("method", 'POST').append($("<input/>").attr("type", 'submit').attr("value","Удалить").attr("name","delet")).append($("<input/>").attr("type", 'hidden').attr("name", "delete_file").attr("value", v.href))),
		                    $('<td/>').append($("<form/>").attr("method", "POST").append($('<a/>').attr("href", v.href).attr("name", "access")).append($("<input/>").attr("type", "submit").attr("value",v.mods).attr("name","access")).append($("<input/>").attr("type", 'hidden').attr("name", "name_access").attr("value", v.href))),
		                    $('<td/>').text(v.size),
		                    $('<td/>').text(v.date),   
		                ]);
		                if(v.is_dir == true){
	                        tr.addClass('ajax');
	                        tr.attr('path', v.href);
		                }
        			}

        			function draw_table(obj){
			            var t = $('table.main tbody');

			            t.empty();
			            $(obj).each(function(k, v){

			            	var base_length = v.href.split('/').length;
	                        var fix_url = document.location.pathname.split('/').length + 2;
	                        if (document.location.pathname == '/')
	                            var fix_url = 3;

				            var level = base_length - fix_url ;
			                var tr = $('<tr/>').attr('level', level + 2);
			                trAp(v.is_dir, tr, v, "a");

			                t.append(tr);
			            });
			        }
			        draw_table(dec);

			        $('input[data-field]').on("click", function(e){
			            var sort_field = $(this).data("field");
			            dec.sort(function(a, b){
			                if ($.isNumeric(a[sort_field])){
			                    return (a[sort_field]- b[sort_field])*sort_direction;
			                } else {
			                    return (a[sort_field].localeCompare(b[sort_field]))*sort_direction;
			                }

			            });
			            sort_direction = sort_direction == 1 ? -1 : 1;
			            draw_table(dec);
			            return false;
			        });

			        $("body").on("click", ".ajax, .-ajax", function(){
				        if ($(this).hasClass("-ajax")){
				            var lev = $(this).attr('level');
				            var atrl = '[level='+ lev +']';
				            $(this).nextUntil(atrl).each(function(){
				            	var levv = $(this).attr('level');

				            	if (levv >= lev ) {
				            		$(this).remove();
				            	}
				            });
				            $(this).removeClass("-ajax").addClass('ajax');
				        }
				        else {
				            var path = $(this).attr('path');
				            var that = this;
				            $.ajax({
				                url: '',
				                type: 'post',
				                data: {
				                    path: path,
				                    is_ajax: 1
				                },
				                success: function (data) {
				                    $(data).each(function(k, v){
				                        var base_length = v.href.split('/').length;
				                        var fix_url = document.location.pathname.split('/').length + 2;
				                        if (document.location.pathname == '/')
				                            var fix_url = 3;

				                        var level = base_length - fix_url ;

				                        var level_sign = "..";
				                        for (var i = 1; i < level; i++){
				                            level_sign += ".";
				                        }
				                        var tr = $('<tr/>').attr('level', level + 1);
				                      	trAp(v.is_dir, tr, v, "ajax", level_sign);
				                        $(tr).insertAfter(that);
				                    });
				                    $(that).removeClass("ajax").addClass('-ajax');
				                }
				            });
				        }
				    });
				</script>
				<form enctype="multipart/form-data"  method="POST">
			    	<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
			    	Отправить этот файл: <input name="userfile" type="file" />
			    	<input type="submit" name="file" value="Send File" />
				</form>
			<?php
		}
} 
elseif (is_file($base_path)){
	if (isset($_POST['access'])) {
		?>
		<form method="POST">
			<input type="text" name="access_change" >
			<input type="submit" name="change	">
		</form>
		<?php

	}
	else {

		$content = htmlspecialchars(file_get_contents($base_path));
		$old_name = $_SERVER["REQUEST_URI"];
		$old_name = explode('/', $old_name);
		$old_name = $old_name[sizeof($old_name) - 1];
		?>
			<form method="post">
				<textarea name="content"><?= $content; ?></textarea><br />
				<input name="new_name" value="<?= $old_name; ?>"><br />
				<input hidden type="text" name="file_path" value="<?= $base_path; ?>">
				<a href="<?= $back_url; ?>">Назад</a>
				<button name="save" type="submit">Сохранить</button>
			</form>
		<?php
	}
} 
else {
	echo "not cool!";
}
?>
</body>
</html>