<?php

	function Get_data_all($id){//получение данных пользователя по id страницы
			addslashes(strip_tags(trim($name)));
			$sel = "SELECT * FROM `user` WHERE `id_str`='$id'";
				include ('config.php');// добавляем информацию для подключения БД

			$link = mysqli_connect($HOST,$USERNAME,$PASSWORD,$DATABASE)
							or die("Не могу подключитьтся к БД:".mysqli_connect_errno().":".mysqli_connect_error());//подключение к БД
			$result=mysqli_query($link,"SET CHARACTER SET UTF8");//установка кодировки UTF8
			mysql_query($link,"SET NAMES UTF8");//установка кодировки UTF8
			$query = mysqli_query($link,$sel);
			if(!$query){
					//echo "Не правильный запрос";
			}
			else{
					if(mysqli_num_rows($query)>0){//если есть результат при SELECT
						$res = mysqli_fetch_array($query,3);
						$name = $res['name'];
						$date_born = $res['date_born'];
						$years_old = $res['years_old'];
						$kino = $res['kino'];//любимые жанры кино
						$yvlech = $res['yvlech'];//увлечения
						$sex = $res['sex_hochy'];//пол нужного кондидата
						$vizrast_ot = $res['vozrast_ot'];//от какого возраста нужен партнёр
						$vizrast_do = $res['vozrast_do'];//до какого возраста нужен партнёр
						$id_sex = $res['id_sex'];//id к оторому последний раз подал заявку на знакомство пользователь
						$zapoln_ank = $res['zapoln_ank'];//заполнена или не заполнена анкета
						$id_posled_see = $res['id_posled_see'];//id последних людей, которые смотрели профель пользователя
						$id_black_list = $res['id_black_list'];//id в чёрном списке, к оторым нельзя обращаться
						$city = $res['city'];//город прживания
						$data_arr = array($name,$years_old, $kino, $yvlech, $sex, $vizrast_ot, $vizrast_do,$id_sex,$date_born,$zapoln_ank,$id_posled_see,$id_black_list,$city);
					}
					else{
						$data_arr = 0;
					}
			}
			return $data_arr;
	}

	function Get_data_id($id){// получение id_str по id  пользователя
			addslashes(strip_tags(trim($name)));// экранируем и убирааем пробелы в имени и фамилии
			$sel = "SELECT * FROM `user` WHERE `id`='$id'";// добавляем информацию для подключения БД
				include ('config.php');

			$link = mysqli_connect($HOST,$USERNAME,$PASSWORD,$DATABASE)
							or die("Не могу подключитьтся к БД:".mysqli_connect_errno().":".mysqli_connect_error());
			$result=mysqli_query($link,"SET CHARACTER SET UTF8");
			mysql_query($link,"SET NAMES UTF8");
			$query = mysqli_query($link,$sel);
			if(!$query){
					//echo "Не правильный запрос";
			}
			else{
					if(mysqli_num_rows($query)>0){
						$res = mysqli_fetch_array($query,3);
						$id_str = $res['id_str'];
						$data_arr = $id_str;
					}
					else{
						$data_arr = 0;
					}
			}
			return $data_arr;
	}


	function Get_data_sex($sex,$ot,$do,$id_sex,$id_black_list){//получение информации о кондидате знакомства
			$ot=addslashes(strip_tags(trim($ot)));// экранируем и убирааем пробелы в имени и фамилии
			$do=addslashes(strip_tags(trim($do)));// экранируем и убирааем пробелы в имени и фамилии

			include ('config.php');// добавляем информацию для подключения БД
			if ($id_black_list!="") {// есть ли пользователи в списке заблокированных пользователей
				$id=explode(".", $id_black_list);
				for ($i=1; $i < count($id); $i++) {
					$a=$a." AND `id`!=".$id[$i];
				}
			}
			if ($id_sex==0) {// Если пользователь первый раз хочет познакомиться
				$sel = "SELECT * FROM `user` WHERE `sex_hochy`!='$sex' AND `years_old`>=$ot AND `years_old`<=$do AND `id_posled_see`='0' $a";
			}
			else{
				$sel = "SELECT * FROM `user` WHERE `sex_hochy`!='$sex' AND `years_old`>=$ot AND `years_old`<=$do AND `id`>$id_sex AND `id_posled_see`='0' $a";
			}

			$link = mysqli_connect($HOST,$USERNAME,$PASSWORD,$DATABASE)
							or die("Не могу подключитьтся к БД:".mysqli_connect_errno().":".mysqli_connect_error());
			$result=mysqli_query($link,"SET CHARACTER SET UTF8");
			mysql_query($link,"SET NAMES UTF8");
			$query = mysqli_query($link,$sel);

			if(!$query){
					//echo "Не правильный запрос";
			}
			else{
					if(mysqli_num_rows($query)>0){//если в результате выборки хотя бы одна строка
						$res = mysqli_fetch_array($query,3);
						$id = $res['id'];
						$id_str = $res['id_str'];//id страницы вконтакте
						$name = $res['name'];
						$years_old = $res['years_old'];//количество полных лет
						$kino = $res['kino'];//любимые жанры кино
						$yvlech = $res['yvlech'];//увлечения
						$sex = $res['sex_hochy'];//пол нужного кондидата
						$vizrast_ot = $res['vozrast_ot'];//от какого возраста нужен партнёр
						$vizrast_do = $res['vozrast_do'];//до какого возраста нужен партнёр
						$date_born = $res['date_born'];//дата рождения
						$city = $res['city'];//город прживания
						$data_arr = array($id,$id_str,$name,$years_old, $kino, $yvlech, $sex, $vizrast_ot, $vizrast_do,$date_born,$city);
					}
					else{
						$data_arr =0;
					}
			}
			return $data_arr;
	}

	function up_yeras_old($userId,$date){//обновление полных количества лет
				$let=explode(".", $date);
				$l1=date("j")-$let[0];
				$l2=date("n")-$let[1];
				$l3=date("Y")-$let[2];
				if($l2<0){
					$l3--;
				}
				else{
					if ($l2==0) {
						if ($l1<0) {
							$l3--;	
						}
					}
				}
				update($userId,$l3,"years_old");
	}
?>