<?php 
	if (!isset($_REQUEST)) { 
	  return; 
	} 
	else{
	//Строка для подтверждения адреса сервера из настроек Callback API 
	$confirmation_token = '1264f359'; 

	//Ключ доступа сообщества 
	$token = 'ca96155dcb1d01b1ded896d965e9209b73924b00e4e61170360d1f18902a57206f1f96363820a58c72573'; 
	$secret="ssl1237895";
	//Получаем и декодируем уведомление 
	$data = json_decode(file_get_contents('php://input')); 
	include ('config.php');
	include('inser.php');
	include('functions.php');
	//Проверяем, что находится в поле "type" 
	if($data->secret==$secret)
		{if ($data->type=="confirmation") {
		  //Если это уведомление для подтверждения адреса... 
		    //...отправляем строку для подтверждения 
		    echo $confirmation_token; 
		}
		  else{ if($data->type=="group_join"){
	        //...получаем id нового участника
	        //...получаем id его автора 
		    $user_id = $data->object->user_id; 
		    //затем с помощью users.get получаем данные об авторе 
		    $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&fields=bdate,sex&v=5.8")); 

			//и извлекаем из ответа его имя 
		    $user_name = $user_info->response[0]->first_name; 

	        //С помощью messages.send и токена сообщества отправляем ответное сообщение
	        $request_params = array(
	            'message' => "Добро пожаловать в наше сообщество Знакомства, {$user_name}!<br>" .
	                            "Если у Вас возникнут вопросы, то вы всегда можете обратиться к администраторам сообщества.<br>" .
	                            "Успехов в поиске знакомств!<br><br>Для начала напиши 'Привет'",
	            'user_id' => $userId,
	            'access_token' => $token,
	            'v' => '5.46'
	        );
	        $get_params = http_build_query($request_params); 

			file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);  
			//Возвращаем "ok" серверу Callback API 

			echo('ok');
	      }
		  else{ if($data->type=="message_new") {
		  //Если это уведомление о новом сообщении... 
		    //...получаем id его автора 
		  	$userId = $data->object->user_id;
		    //затем с помощью users.get получаем данные об авторе 
		  	$userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&fields=bdate,sex&v=5.8"));
			//и извлекаем из ответа его имя 
		    $user_name =$userInfo->response[0]->first_name; 
		    $r=0;
			//С помощью messages.send отправляем ответное сообщение 
			$message = $data->object->body;
			$messages_array =array(
				'Привет' => "Привет, "."{$userInfo->response[0]->first_name}"."! Я могу помочь найти тебе компанию на вечер, хочешь?",
				'привет' => "Привет, "."{$userInfo->response[0]->first_name}"."! Я могу помочь найти тебе компанию на вечер, хочешь?",
				'Как дела?' => "Нормально",
				'Да'=>'Для начнала заполни анкету. Для этого напиши "заполнить"',
				'да'=>'Для начнала заполни анкету. Для этого напиши "заполнить"',
				'хочу'=>'Для начнала заполни анкету. Для этого напиши "заполнить"',
				'Хочу'=>'Для начнала заполни анкету. Для этого напиши "заполнить"',
				'помощь'=>"Выберите что вы хотите сделать:<br>1. Посмотреть мою анктеу.<br>2. Заполнить мою анкету заново.<br>3.",
				'Помощь'=>"Выберите что вы хотите сделать:<br>1. Посмотреть мою анктеу.<br>2. Заполнить мою анкету заново.<br>3.",
				'Заполнить'=>'Напиши предпочтения в кино.',
				'заполнить'=>'Напиши предпочтения в кино.',
				'2'=>'Напиши предпочтения в кино.',
			);
			foreach($messages_array as $k => $v){
				if($message == $k){
					$otwet = $v;
					$r=1;
				}
			}

			if ($message=="1") {
				$r=1;
				$otwet="Вот твоя анкета:<br>";
				$anketa=Get_data_all($userId);
				for ($i=0; $i < count($anketa); $i++) {
					if($anketa[$i]!=NULL){
						switch ($i) {
							case '0': $otwet=$otwet."Имя и фамилия:   ".$anketa[$i]; break;
							case '1': 
								$let=explode(".", $anketa[$i]);
								$l1=date("j")-$let[0];
								$l2=date("n")-$let[1];
								$l3=date("Y")-$let[2];
								if($l2<=0){
									if ($l1<=0) {
										$l3--;
									}
								}
								$otwet=$otwet."<br>Полных лет:   ".$l3; break;
							case '2': $otwet=$otwet."<br>Предпочтения в кино:   ".$anketa[$i]; break;
							case '3': $otwet=$otwet."<br>Интересы, хобби, увлечения:   ".$anketa[$i]; break;
							case '5': $otwet=$otwet."<br>Нужны от возроста:   ".$anketa[$i]; break;
							case '6': $otwet=$otwet."<br>Нужны до возроста:   ".$anketa[$i]; break;
						}
					}
				}
			}
			
			if(($message=="Заполнить")||($message=="заполнить")){
				$a="{$userInfo->response[0]->first_name}"." "."{$userInfo->response[0]->last_name}";
				$m=Get_data($userId,'id_str');
				if ($m==0) {
					vstavka("$userId","id_str");
					update("$userId","$a","name");
					if ($userInfo->response[0]->sex==2) {
						update("$userId","ж","sex_hochy");
					}
					else{
						update("$userId","м","sex_hochy");
					}
				}
				else{
					$otwet="У вас уже есть анкета. Напиши '2' для заполнения анкеты заново?";
				}
			}
			else{
				if($r==0) {
					$request_params = array( 
				      'offset' => "1", 
				      'count' => "1",
				      'peer_id' => "$userId",
				      'access_token' => $token,
				      'v' => '5.67'
				    );

					$get_params = http_build_query($request_params); 
					
					$d=json_decode(file_get_contents('https://api.vk.com/method/messages.getHistory?'.$get_params));
					if($d->response->items[0]->body=="Напиши предпочтения в кино."){
						update("$userId","$message","kino");
						if (isset($userInfo->response[0]->bdate)){
							update("$userId","{$userInfo->response[0]->bdate}","years_old");
							$otwet="Напиши свои интересы, хобби, увлечения";
						}
						else{
							$otwet="Теперь напиши дату своего рождения<br>*пиши реальную дату, так как от этого зависит то, с кем ты познакомишься";
						}
					}
					else{
						if ($d->response->items[0]->body=="Теперь напиши дату своего рождения в формате день.месяц.год<br>*пишите реальную дату, так как от этого зависит то, с кем вы познакомитесь") {
							update("$userId","$message","years_old");
							$otwet="Напиши свои интересы, хобби, увлечения";
						}
						else{
							if($d->response->items[0]->body=="Напиши свои интересы, хобби, увлечения"){
								update("$userId","$message","yvlech");
								$otwet="От какого возроста ты хочешь найти собеседника?";
							}
							else{
								if ($d->response->items[0]->body=="От какого возроста ты хочешь найти собеседника?") {
									update("$userId","$message","vizrast_ot");
									$otwet="До какого возроста ты хочешь найти собеседника?";
								}
								else{
									if ($d->response->items[0]->body=="До какого возроста ты хочешь найти собеседника?") {
										update("$userId","$message","vizrast_do");
										$otwet="Поздравляю, теперь анкета заполнена!<br><br>Теперь выберите что вы хотите сделать:<br>1. Посмотреть мою анктеу.<br>2. Заполнить мою анкету заново.<br>3.";
									}
									else{
										$otwet="Ты написал неизвесную команду. Напишите 'Помощь' для справки.";
									}
								}
							}
						}
					}
				}
			}
		    $request_params = array( 
		      'message' => "$otwet", 
		      'user_id' => $userId,
		      'access_token' => $token,
		      'read_state' => 1,
		      'v' => '5.46'
		    );

			$get_params = http_build_query($request_params); 

			file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);  
			//Возвращаем "ok" серверу Callback API 

			echo('ok');
	}}}
	   }
	   else{ echo "ok";}}
?>