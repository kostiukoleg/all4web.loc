<?php

new pluginAjaxComments();

class pluginAjaxComments{

	public function __construct(){
		if($_POST['showComments']){
			$this->getComments();
		} else{
			$this->addComment();
		}
	}
	

	private function getComments(){
		$result = array();

		// Если запрос был со стороны сайта выполняется первая ветка условия. Иначе - вторая.
		if(isset($_POST['showComments']) && isset($_POST['uri'])){
			$uri = explode('/', $_POST['uri']);

			if(!empty($uri[1])){
				unset($uri[0]);
				$uri = implode('/', $uri);
			} else{
				$uri = $uri[0];
			}

			// Запрос для генерации блока пагинации 
			$sql = "
				SELECT id, name, comment, UNIX_TIMESTAMP(date) as date
				FROM `comments` 
				WHERE uri = ".DB::quote($uri)." AND approved = '1'
				ORDER BY `id` DESC";
				$res = DB::query($sql);

			//Получаем блок пагинации
	    if ($_POST["page"])
	      $page = $_POST["page"]; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс

	    $navigator = new Navigator($sql, $page, MG::getOption('countPrintRowsComments')); //определяем класс
	    $pagination = $navigator->getPager('forAjax');

	    /* Получаем непосредственно комментарии.
	    	 Если была запрошена не первая страница комментариев, выполняем вторую ветку условия.
	    */
	    if(empty($_POST['page'])){
	    $sql = "
				SELECT id, name, comment, UNIX_TIMESTAMP(date) as date
				FROM `comments` 
				WHERE uri = ".DB::quote($uri)." AND approved = '1'
				ORDER BY `id` DESC
				LIMIT 0, ".MG::getOption('countPrintRowsComments');
				$res = DB::query($sql);
			} else{
				$start = ($_POST['page'] - 1) * MG::getOption('countPrintRowsComments');
				$amount= MG::getOption('countPrintRowsComments');

				$sql = "
				SELECT id, name, comment, UNIX_TIMESTAMP(date) as date
				FROM `comments` 
				WHERE uri = ".DB::quote($uri)." AND approved = '1'
				ORDER BY `id` DESC
				LIMIT ".$start.", ".$amount;
				$res = DB::query($sql);
			}

			while($row = DB::fetchAssoc($res)){
				$row['date'] = date('d.m.Y H:i', $row['date']);
				$result['comments'][] = $row;
			}
			$result['pagination'] = $pagination;

			echo json_encode($result);
			exit;
		} else{
			$sql = "
				SELECT id, name, comment, date
				FROM `comments`
				ORDER BY date DESC";

			$res = DB::query($sql);

			while($row = DB::fetchAssoc($res)){
				$row['date'] = date('d.m.Y H:i', $row['date']);
				$result[$row['id']] = $row;
			}

			return $result;
		}
	}


	private function addComment(){

		// Проверяем полученные данные
		$data = $this->dataCheck($_POST);

		// Если есть ошибки, то выводим сообщение и прекращаем работу скрипта
		if($data['error']){
			unset($data['error']);
			echo json_encode($data);
			exit;
		}

		$sql = "
			INSERT INTO `comments` (
				name,
				email,
				comment,
				date,
				uri
				) 
			VALUES (
				".DB::quote($data['name']).",
				".DB::quote($data['email']).",
				".DB::quote($data['comment']).",
				NOW(),
				".DB::quote($data['uri'])."
				)";
		
		// Выполняем запрос на добавление коммента
		DB::query($sql);

		echo json_encode(array('msg' => '<span class="c-msg-green">Комментарий был успешно отправлен!</span><br ><span class="c-msg-red">Он будет опубликован после проверки модератором!</span>'));
		exit;
	}


	private function dataCheck($POST){
		$data = $POST['data'];
		parse_str($data);
		unset($data);

		// Убираем хост и URI
		$uri = explode('/', $uri);
		if(!empty($uri[1])){
			unset($uri[0]);
			$uri = implode('/', $uri);
		} else{
			$uri = $uri[0];
		}

		// Подготовка данных для вноса в БД
		$data['name'] = $name;
		$data['email'] = $email;
		$data['comment'] = $comment;
		$data['uri'] = $uri;

		// Проверяем данные, генерируем ошибки
		if(empty($data['name'])){
			$error['name'] = "Введите свое имя!";
		}
		if(empty($data['email'])){
			$error['email'] = "Введите корректный email!";
		}
		if(empty($data['comment'])){
			$error['comment'] = "Введите текст комментария!";
		}

		// В случае наличия ошибок, возвращаем их вызывающей функции
		if(!empty($error['name']) || !empty($error['email']) || !empty($error['comment'])){
			$error['error'] = true;
			return $error;
		}

		return $data;
	}
}