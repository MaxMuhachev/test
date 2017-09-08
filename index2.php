<?php 
	if (!isset($_REQUEST)) { 
	  return; 
	} 

	//Строка для подтверждения адреса сервера из настроек Callback API 
	$confirmation_token = '1264f359'; 

	//Ключ доступа сообщества 
	$token = '01bebbcafbfc3ed8cc517331f2e36566909e52ed6cae9e048c511cd235c3840d50a22c83004a9107baa3b'; 
	$secret="ssl1237895";
	//Получаем и декодируем уведомление 
	$data = json_decode(file_get_contents('php://input')); 
	include ('config.php');
	include('inser.php');
	include('functions.php');
	
	
	if($data->secret==$secret){//если секретный код в группе равен коду здесь
		switch ($data->type) {
		  //Если это уведомление для подтверждения адреса... 
		  case 'confirmation': 
		    //...отправляем строку для подтверждения 
		    echo $confirmation_token; 
		    break; 

		  case 'group_join':
	        //...получаем id нового участника
	        //...получаем id его автора 
		    $userId = $data->object->user_id; 
		    //затем с помощью users.get получаем данные об авторе 
		    $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&fields=bdate,sex&v=5.8"));
			//и извлекаем из ответа его имя 
		    $user_name =$userInfo->response[0]->first_name; 

			$request_params = array(
		        'message' => "Добро пожаловать в наше сообщество Знакомства, ".$user_name."!<br>"."Если у Вас возникнут вопросы, то вы всегда можете обратиться к администраторам сообщества.<br>"."Успехов в поиске знакомств!<br><br>Для начала напиши 'Привет'",
		        'user_id' => $userId,
		        'access_token' => $token,
		        'v' => '5.46'
		    );
		    $get_params = http_build_query($request_params); 

			file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);
			//Возвращаем "ok" серверу Callback API 

			echo('ok');
	        break;

		  //Если это уведомление о новом сообщении... 
		  case 'message_new'://если новое сообщение
		    //...получаем id его автора 
		  	class user {
				// данные (свойства):
				var $ID = $data->object->user_id;
				var $name;
				var $last_name;

				// методы:
				 function Name() {
				 echo "<h3>John</h3>";
				 }
			}

		  	$userId = $data->object->user_id;
		    //затем с помощью users.get получаем данные об авторе 
		  	$userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&fields=bdate,sex,city&v=5.8"));
			//и извлекаем из ответа его имя, пол, город
		    $user_name =$userInfo->response[0]->first_name; 
		    $r=0;//для того, чтобы понять, что ответ уже сформирован и не нужно дополнительных действий
			
			$message = mb_strtolower("{$data->object->body}", 'UTF-8');
			$messages_array =array(
				'привет' => "Привет, "."{$userInfo->response[0]->first_name}"."! Я могу помочь найти тебе компанию на вечер, хочешь?",
				'заполнить'=>'Напиши свои любимые жанры фильмов'
			);
			foreach($messages_array as $k => $v){//просмотр есть ли сообщение в массиве
				if($message == $k){
					$otwet = $v;
					$r=1;
				}
			}

			if ($message=='2') {
				update($userId,0,'zapoln_ank');//обновление что пользователь не заполнил анкету
				$otwet='Напиши свои любимые жанры фильмов';
				$r=1;
			}

			if (($message=='помощь')||($message=='да')||($message=='хочу')) {
				$user=Get_data_all($userId);//полцчение информации о пользователе
					$r=1;
				if ($user[9]!=0) {
						$otwet="Выбери что вы хочешь сделать:<br>1. Посмотреть мою анктеу.<br>2. Заполнить мою анкету заново.<br>3. Поиск нового собеседника.<br>4. Не хочу больше никого искать";
				}
				else{
					if ($message=='помощь') {
						$otwet="Напиши 'привет'";
					}
					else{
						$otwet='Для начнала заполни анкету. Для этого напиши "заполнить"';
					}
				}
			}

			
			if ($message=='4') {
					$r=1;
					delete($userId);
					$otwet="Хорошо. Я удалил твою анктеу";
			}

			if ($message=="1") {//если пользователь хочет посмотреть анкету
				$user=Get_data_all($userId);//получение информации о пользователе
				if ($user[9]!=0) {//если анкета заполнена
					$r=1;
					$otwet="Вот твоя анкета:<br><br>";
					up_yeras_old($userId,$user[8]);//обновлнеие пколичество полных лет
					$anketa=Get_data_all($userId);//получение анкеты с обновленным количством лет
					$user_sex = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&fields=photo_max_orig&v=5.8"));//получение информации о пользователе с параметром photo_max_orig
					for ($i=0; $i < count($anketa); $i++) {
						if($anketa[$i]!=NULL){
							switch ($i) {
								case '0': $otwet=$otwet.'Имя и фамилия:   '.$anketa[$i]."."; break;
								case '1': $otwet=$otwet.'<br>Полных лет:   '.$anketa[$i]."."; break;
								case '2': $otwet=$otwet."<br>Любимые жанры фильмов:  ".$anketa[$i]."."; break;
								case '3': $otwet=$otwet."<br>Интересы, хобби, увлечения:  ".$anketa[$i]."."; break;
								case '4': 
										if ($anketa[$i]=="м") {
											$aa="мужчиной, которому";	
										}
										else{
											if ($anketa[$i]=="ж") {
												$aa="девушкой, которой";
											}
										} break;
								case '5': $otwet=$otwet."<br>Хочу познакомиться с ".$aa." от  ".$anketa[$i]; break;
								case '6': $otwet=$otwet." до ".$anketa[$i]."."; break;
								case '12':
									$w = explode("<br>Любимые жанры", $otwet);//разбиваем строку по параметру "<br>Любимые жанры"
									$otwet=$w[0]."<br>Проживает в городе:  ".$anketa[$i].".<br>Любимые жанры".$w[1];
								break;
							}
						}
					}
					$otwet=$otwet.'<br>Вот фотография: '.$user_sex->response[0]->photo_max_orig;
				}
			}

			if ($message=='3') {
				$user=Get_data_all($userId);//полцчение информации о пользователе
				if ($user[9]!=0) {//если анкета заполнена
					$r=1;
					update($userId,0,'id_posled_see');// id пользователя, который отправил запрос на знакомство=0
					$sex=Get_data_sex($user[4],$user[5],$user[6],$user[7],$user[11]);//получение информации о партнёре с параметрами(пол,нужен_возрост_от,нужен_возрост_от,id к оторому последний раз подал заявку на знакомство пользователь,id к котормы уже был запрос на знакомство) 

					if ($sex==0) {//если нет кондидатов на знакоство
						$sex=Get_data_sex($user[4],$user[5],$user[6],0,$user[11]);
					}
					if ($sex!=0) {//если пользователь найден
						$otwet="Вот анкета того, с кем ты можешь познакомиться:<br><br>";
						update($userId,$sex[0],'id_sex'); //ВПИСАТЬ В БАЗУ ID страницы выведенного пользователя
						$user_sex = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$sex[1]}&fields=photo_max_orig&v=5.8"));//получение информации о пользователе с параметром photo_max_orig
						for ($i=0; $i < count($sex); $i++) {
							if($sex[$i]!=NULL){
								switch ($i) {
									case '2': $otwet=$otwet."Имя и фамилия:   ".$sex[$i]."."; break;
									case '3': 
										up_yeras_old($sex[1],$sex[9]); //обновляем полное количество лет
										$s=Get_data_all($sex[1]);//получаем количество полных лет
										$otwet=$otwet."<br>Полных лет:   ".$s[1]."."; break;
									case '4': $otwet=$otwet."<br>Любимые жанры фильмов:  ".$sex[$i]."."; break;
									case '5': $otwet=$otwet."<br>Интересы, хобби, увлечения:  ".$sex[$i]."."; break;
									case '6': 
											if ($sex[$i]=="м") {
												$aa="мужчиной, которму";	
											}
											else{
												if ($sex[$i]=="ж") {
													$aa="девушкой, которой";
												}
											} break;
									case '7': $otwet=$otwet."<br>Хочет познакомиться с ".$aa." от  ".$sex[$i]; break;
									case '8': $otwet=$otwet." до ".$sex[$i]."."; break;
									case '10':$w = explode("<br>Любимые жанры", $otwet);$otwet=$w[0]."<br>Проживает в городе:  ".$anketa[$i].".<br>Любимые жанры".$w[1]; break;
								}
							}
						}
						$otwet=$otwet.'<br>Вот фотография: '.$user_sex->response[0]->photo_max_orig.'<br><br> Хочешь познакомиться? (11)да (3) искать дальше';
					}
					else{
						$otwet="На данный момент нет никого в соответсвии с твоими требованиями, заходи попозже 😉";
					}
				}
				else{
					$otwet="У тебя ещё не заполнена анкета. Заполни её, тогда всё получится!";
				}
			}

			if ($message=="11") {//если пользователь хочет познакомиться
				$user=Get_data_all($userId);//полцчение информации о пользователе
				if ($user[9]!=0) {//если анкета заполнена
					$r=1;
					$sex=Get_data_id($user[7]);//получение ID кондидата знакомства
					if ($sex!=0) {//если пользователь найден
						$otwet="Привет! С тобой хочет познакомиться "."{$userInfo->response[0]->first_name}".'.<br>Вот анкета:<br><br>';

						update($sex,$userId,'id_posled_see');//запоминаем id пользователя, который отправил запрос на знакомство, чтобы другие не могли подать запрос
						//С помощью messages.send отправляем ответное сообщение 
						up_yeras_old($userId,$user[8]);
						$request_params = array( 
					      'message' => "Сейчас я спрошу хочет ли другой пользователь с тобой пообщаться, подожди немного...", 
					      'user_id' => $userId,
					      'access_token' => $token,
					      'read_state' => 1,
					      'v' => '5.46'
					    );

						$get_params = http_build_query($request_params); 

						file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);
						$user_sex = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&fields=photo_max_orig&v=5.8"));//получение информации о пользователе с параметром photo_max_orig
						$anketa=$user;
						$userId=$sex;
						for ($i=0; $i < count($anketa); $i++) {
							if($anketa[$i]!=NULL){
								switch ($i) {
									case '0': $otwet=$otwet."Имя и фамилия:   ".$anketa[$i]; break;
									case '1': $otwet=$otwet."<br>Полных лет:   ".$anketa[$i]; break;
									case '2': $otwet=$otwet."<br>Любимые жанры фильмов:  ".$anketa[$i]; break;
									case '3': $otwet=$otwet."<br>Интересы, хобби, увлечения:  ".$anketa[$i]; break;
									case '4': 
											if ($anketa[$i]=="м") {
												$aa="мужчиной, которму";	
											}
											else{
												if ($anketa[$i]=="ж") {
													$aa="девушкой, которой";
												}
											} break;
									case '5': $otwet=$otwet."<br>Хочу познакомиться с ".$aa." от  ".$anketa[$i]; break;
									case '6': $otwet=$otwet." до ".$anketa[$i]; break;
									case '12':$w = explode("<br>Любимые жанры", $otwet);$otwet=$w[0]."<br>Проживает в городе:  ".$anketa[$i].".<br>Любимые жанры".$w[1]; break;
									
								}
							}
						}
						$otwet=$otwet.'<br>Вот фотография: '.$user_sex->response[0]->photo_max_orig.'<br><br> Хочешь познакомиться? (!1)да (!0) нет. Или заблокируй пользователя, написав "!чс"?';
					}
				}
			}

			if ($message=="!1") {//если второй пользователь хочет познакомиться
				$user=Get_data_all($userId);//получение информации о пользователе
				if ($user[9]!=0) {//если анкета заполнена
					$r=1;
					$sex=$user[10];//получение ID кондидата знакомства
					if ($sex!=0) {//если пользователь найден
						$anketa=Get_data_all($sex);
						//С помощью messages.send отправляем ответное сообщение 
						$otwet="Этот пользователь хочет с тобой познакомиться: http://vk.com/id".$userId;
						$request_params = array( 
					      'message' => "$otwet", 
					      'user_id' => $sex,
					      'access_token' => $token,
					      'read_state' => 1,
					      'v' => '5.46'
					    );

						$get_params = http_build_query($request_params); 

						file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);
						$otwet="Этот пользователь хочет с тобой познакомиться: http://vk.com/id".$sex;
						update($userId,0,'id_posled_see');
					}
					else{$otwet=$user[10]." ".$sex;}
				}
			}

			if ($message=="!0") {//если второй пользователь не хочет познакомиться
				$user=Get_data_all($userId);//получение информации о пользователе
				if ($user[9]!=0) {//если анкета заполнена
					//С помощью messages.send отправляем ответное сообщение 
					$request_params = array( 
				      'message' => "Пользователь не хочет с тобой общаться, попробуй посмотреть других и напиши мне '3'.", 
				      'user_id' => $user[10],
				      'access_token' => $token,
				      'read_state' => 1,
				      'v' => '5.46'
				    );

					$get_params = http_build_query($request_params); 

					file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);
					update($userId,0,'id_posled_see');//обновление id_posled_see на 0
					$otwet="Хорошо, напиши '3' для просмотра других вариантов";
					$r=1;//запоминаем ответ
				}
			}

			if ($message=="!чс") {//если пользователь хочет заблокировать другого
				$user=Get_data_all($userId);//получение информации о пользователе
				if ($user[9]!=0) {//если анкета заполнена
					$black_list=Get_data_all($user[10]);//получение информации о пользователе
					update($user[10],$black_list[11].".".$black_list[7],'id_black_list');//добавляем id в чёрный список

					//С помощью messages.send отправляем ответное сообщение 
					$request_params = array( 
				      'message' => "Пользователь не хочет с тобой общаться, попробуй посмотреть других и напиши мне '3'.", 
				      'user_id' => $user[10],
				      'access_token' => $token,
				      'read_state' => 1,
				      'v' => '5.46'
				    );

					$get_params = http_build_query($request_params); 

					file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);

					update($userId,0,'id_posled_see');//обновление id_posled_see на 0
					$otwet="Хорошо, я заблокировал пользователя и теперь он больше не будет тебе писать";
					$r=1;//запоминаем ответ
				}
			}
			
			/*ПРЕДУПРЕЖДАЮ, что был испробован метод switch, но ничего не вышло, была проблема с датай рождения, если её нет, иначе можно реализовать switch, но тогда кода будет больше, так как повторяется действия при разных сообщениях, если был некорректный ввод сообщения пользователем.
			*/

			if(($message=="Заполнить")||($message=="заполнить")){//если пользователь хочет заполнить анкету
				$a="{$userInfo->response[0]->first_name}"." "."{$userInfo->response[0]->last_name}";//полное имя
				$m=Get_data_all($userId,'id_str');
				if (($m==0)||((isset($m[9]))&&($m[9]==0))) {//если пользователь не найден или анкета полностью не заполнена
					insert("$userId","id_str");//добавить пользователя
					update("$userId","$a","name");//добавить имя и фамилию в базу
					if ($userInfo->response[0]->sex==2) {//установка пола пользователя
						update("$userId","ж","sex_hochy");
					}
					else{
						update("$userId","м","sex_hochy");
					}
				}
				else{
					$otwet="У тебя уже есть анкета. Напиши '2' для заполнения анкеты заново".$m[9];
				}
			}
			else{
				if($r==0) {//если ответ, который нужен уже найден и все необходимые действия выполнены
					$request_params = array( 
				      'offset' => "1", 
				      'count' => "1",
				      'peer_id' => "$userId",
				      'access_token' => $token,
				      'v' => '5.67'
				    );

					$get_params = http_build_query($request_params); 
					
					$d=json_decode(file_get_contents('https://api.vk.com/method/messages.getHistory?'.$get_params));//получение истории сообщений и выбор предпоследнего
					if($d->response->items[0]->body=="Напиши свои любимые жанры фильмов"){//если предыдущее сообщение "Напиши свои любимые жанры фильмов."
						update("$userId","$message","kino");
						if ((isset($userInfo->response[0]->bdate))&&(strlen($userInfo->response[0]->bdate)>=8)) {//если в профиле вконтакте установлена дата рождения
							update("$userId","{$userInfo->response[0]->bdate}","date_born");
							up_yeras_old($userId,"{$userInfo->response[0]->bdate}");
							if (isset($userInfo->response[0]->city->title)) {
								update($userId,$userInfo->response[0]->city->title,'city');//добавление города в базу
								$otwet="Напиши свои интересы, хобби, увлечения, чем ты любишь заниматься?";
							}
							else{
								$otwet="Напиши название города, в котором ты живешь";
							}
							update($userId,0,'zapoln_ank');//обновление что пользователь не заполнил анкету
						}
						else{
							$otwet="Теперь напиши дату своего рождения в формате ДД.ММ.ГГГГ(день.месяц.год)   *пиши реальную дату, так как от этого зависит то, с кем ты познакомишься";
							update($userId,0,'zapoln_ank');//обновление что пользователь не заполнил анкету
						}
					}
					else{
						if (($d->response->items[0]->body=="Теперь напиши дату своего рождения в формате ДД.ММ.ГГГГ(день.месяц.год)   *пиши реальную дату, так как от этого зависит то, с кем ты познакомишься")||($d->response->items[0]->body=="Такой даты рождения не может быть (она должна быть в формате ДД.ММ.ГГГГ(день.месяц.год) напиши дату своего рождения")) {
							if (!preg_match_all("/[0-9.]+/",$message)) {//если ползователь написал только цифры и точки
								$otwet="Такой даты рождения не может быть (она должна быть в формате ДД.ММ.ГГГГ(день.месяц.год) напиши дату своего рождения";
							}
							else{
								$let=explode(".", $message);
								if (((int)$let[0]<=31)&&((int)$let[0]>0)) {//ограничения по количеству дней в месяце от 1 до 31
									if (((int)$let[1]<=12)&&((int)$let[1]>0)) {//ограничения по количеству месяцев от 1 до 12
										if (((int)$let[2]<=(date("Y")-12))&&((int)$let[2]>=1947)){//ограничения по году от 1950 до 12 лет
											up_yeras_old($userId,$message);//получение полных лет
											if (isset($userInfo->response[0]->city->title)) {//если в профиле указан город
												update($userId,$userInfo->response[0]->city,'city');//добавление города в базу
												$otwet="Напиши свои интересы, хобби, увлечения, чем ты любишь заниматься?";
											}
											else{
												$otwet="Напиши название города, в котором ты живешь";
											}
										}
										else {$otwet="Такой даты рождения не может быть (она должна быть в формате ДД.ММ.ГГГГ(день.месяц.год) напиши дату своего рождения";}
									}
									else {$otwet="Такой даты рождения не может быть (она должна быть в формате ДД.ММ.ГГГГ(день.месяц.год) напиши дату своего рождения";}
								}
								else {$otwet="Такой даты рождения не может быть (она должна быть в формате ДД.ММ.ГГГГ(день.месяц.год) напиши дату своего рождения";}
							}
						}	
						else{
							if($d->response->items[0]->body=="Напиши название города, в котором ты живешь"){
								update($userId,$message,'city');//добавление города в базу
								$otwet="Напиши свои интересы, хобби, увлечения, чем ты любишь заниматься?";
							}
							else{
								if($d->response->items[0]->body=="Напиши свои интересы, хобби, увлечения, чем ты любишь заниматься?"){
									update("$userId","$message","yvlech");//добавление увлечений в базу
									$otwet="От какого возраста ты хочешь найти собеседника?";
								}
								else{
									if (($d->response->items[0]->body=="От какого возраста ты хочешь найти собеседника?")||($d->response->items[0]->body=="Такого возраста может быть в сообществе. Введи другое.")) {
										if (!preg_match_all("/[0-9]+/",$message)||((int)$message<13)||((int)$message>79)) {//возраст от 13 до 80 и ввести можно только числа
											$otwet="Такого возраста может быть в сообществе. Введи другое.";
										}
										else{
												update("$userId","$message","vozrast_ot");//добавление от какого возроста пользователь хочет найти пару в базу
												$otwet="До какого возраста ты хочешь найти собеседника?";
										}
									}
									else{
										if (($d->response->items[0]->body=="До какого возраста ты хочешь найти собеседника?")||($d->response->items[0]->body=="Такого возраста может быть в сообществе. Введи другой возраст.")) {
											if (!preg_match_all("/[0-9]+/",$message)||((int)$message<14)||((int)$message>80)) {//возраст от которого может быть партнёр от 14 до 81 и ввести можно только числа
												$otwet="Такого возраста может быть в сообществе. Введи другой возраст.";
											}
											else{
												update("$userId","$message","vozrast_do");//добавление до какого возроста пользователь хочет найти пару в базу
												$otwet="Поздравляю, теперь анкета заполнена!<br><br>Теперь выберите что вы хотите сделать:<br>1. Посмотреть мою анктеу.<br>2. Заполнить мою анкету заново.<br>3.  Поиск нового собеседника.<br>4. Не хочу больше никого искать";
												update($userId,1,'zapoln_ank');//пользователь заполнил анкету
											}
										}
										else{
											$otwet="Ты написал неизвесную команду. Напишите 'Помощь' для справки.";//если не нашлось ответа
										}
									}
								}
							}
						}
					}
				}
			}
			//С помощью messages.send отправляем ответное сообщение 

		    $request_params = array( 
		      'message' => "$otwet", 
		      'user_id' => $userId,
		      'random_id'=>mt_rand(20, 99999999),
		      'access_token' => $token,
		      'v' => '5.46'
		    );

			$get_params = http_build_query($request_params); 

			file_get_contents('https://api.vk.com/method/messages.send?'. $get_params); 
			//Возвращаем "ok" серверу Callback API 

			echo('ok');
		    break;
		    return false;
		}
	   }
?>