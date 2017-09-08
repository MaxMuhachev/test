<?php 
	function insert($value,$name_pole){//вставка в базу данных  по названию поля
			$ins = "INSERT INTO `user` (`$name_pole`) VALUES ('$value')";
    		include ('config.php');

			$link = mysqli_connect($HOST,$USERNAME,$PASSWORD,$DATABASE)
			        or die("Не могу подключитьтся к БД:".mysqli_connect_errno().":".mysqli_connect_error());
			$result=mysqli_query($link,"SET CHARACTER SET UTF8");
			mysql_query($link,"SET NAMES 'UTF8'");
			$query = mysqli_query($link,$ins);
	}

	function update($id,$value,$name_pole){//обновление информации
			$value = addslashes(strip_tags(trim($value)));
			$up = "UPDATE `user` SET `$name_pole`='$value' WHERE `id_str`='$id'";
    		include ('config.php');

			$link = mysqli_connect($HOST,$USERNAME,$PASSWORD,$DATABASE)
			        or die("Не могу подключитьтся к БД:".mysqli_connect_errno().":".mysqli_connect_error());
			$result=mysqli_query($link,"SET CHARACTER SET UTF8");
			mysql_query($link,"SET NAMES 'UTF8'");
			$charset = mysqli_set_charset ($link, "utf8_unicode_ci");
			$query = mysqli_query($link,$up);
	}

	function delete($id){//удаление пользоваетля
		$del="DELETE FROM `user` WHERE `id_str` = $id";
		include ('config.php');

		$link = mysqli_connect($HOST,$USERNAME,$PASSWORD,$DATABASE)
		        or die("Не могу подключитьтся к БД:".mysqli_connect_errno().":".mysqli_connect_error());
		$result=mysqli_query($link,"SET CHARACTER SET UTF8");
		mysql_query($link,"SET NAMES 'UTF8'");
		$charset = mysqli_set_charset ($link, "utf8_unicode_ci");
		$query = mysqli_query($link,$del);
	}
?>