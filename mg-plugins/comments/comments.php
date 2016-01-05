<?php

/**
 * Класс Comments наследник стандарного Actioner
 * Предназначен для выполнения действий, запрошеных AJAX функциями
 *
 * @author Mark Avdeev
 */
class Comments extends Actioner {

  // Функция добавляет комментарий в БД
  public function addComment() {

    $infoUser = User::getThis();
    // если пользователь не авторизован и не заполнил все поля, то возвращаем ошибку
    if (!$infoUser&&(empty($_POST['name'])||empty($_POST['email'])||empty($_POST['comment']))) {
      $this->messageError = "Необходимо заполнить все поля!";
      return false;
    }

    // если пользователь авторизован но не заполнил поле с комментаринем
    if (empty($_POST['comment'])) {
      $this->messageError = "Необходимо указать комментарий!".$_POST['comment'];
      return false;
    }
	
    $name = $infoUser->name;
    $email = $infoUser->email;
	
		// если пользователь не авторизован но заполнил все поля
    if (!empty($_POST['name'])&&!empty($_POST['email'])&&!empty($_POST['comment'])) {
      $name = $_POST['name'];
  	  $email = $_POST['email'];
      // Проверка электронного адреса.
	  if (!preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]{0,61}+\.)+[a-zA-Z]{2,6}$/', $email)) {      
        $this->messageError = "Неверно заполнено поле e-mail!";
        return false;
      }
    }

	
    $sql = "
     INSERT INTO `".PREFIX."comments` (name,email,comment,date,uri) 
     VALUES (
       ".DB::quote($name).",
       ".DB::quote($email).",
       ".DB::quote($_POST['comment']).",
       now(),
       ".DB::quote(URL::clearingUrl($_SERVER['HTTP_REFERER']))."
       )";

    // Выполняем запрос на добавление коммента
    DB::query($sql);
    $this->messageSucces = '
      <span class="c-msg-green">Комментарий был успешно отправлен!</span>
        <br >
      <span class="c-msg-red">Он будет опубликован после проверки модератором!</span>';
    return true;
  }

  // Получаем все записи комментариев к этой странице
  public function getComments() {

    $result = array();

    // Если запрос был со стороны сайта выполняется первая ветка условия. Иначе - вторая.
    if (isset($_POST['showComments'])&&isset($_POST['uri'])) {
      $uri = explode('/', $_POST['uri']);

      if (!empty($uri[1])) {
        unset($uri[0]);
        $uri = implode('/', $uri);
      } else {
        $uri = $uri[0];
      }

      // Запрос для генерации блока пагинации 
      $sql = "
				SELECT id, name, comment, date
				FROM `".PREFIX."comments` 
				WHERE (uri = ".DB::quote($uri)." OR uri = ".DB::quote($uri.".html").") AND approved = '1'
				ORDER BY `id` DESC";
      $res = DB::query($sql);

      //Получаем блок пагинации
      if ($_POST["page"])
        $page = $_POST["page"]; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс

      $navigator = new Navigator($sql, $page, MG::getSetting('countPrintRowsComments')); //определяем класс
      $comments = $navigator->getRowsSql();
      $pagination = $navigator->getPager('forAjax');

      /*
       * Получаем непосредственно комментарии.
        Если была запрошена не первая страница комментариев, выполняем вторую ветку условия.
       */
      foreach ($comments as $key => $value) {
        $comments[$key]['date'] = date('d.m.Y H:i', $result['comments']['date']);
      }
      $result['pagination'] = $pagination;
      $this->data = $result;
      return true;
    } else {
      $sql = "
				SELECT id, name, comment, date
				FROM `".PREFIX."comments`
				ORDER BY date DESC";

      $res = DB::query($sql);

      while ($row = DB::fetchAssoc($res)) {
        $result[$row['id']] = $row;
      }

      return $result;
    }
  }

  // Удаляет комментарий из БД
  public function deleteComment() {
    //удаление доступно только модераторам и админам.
    USER::AccessOnly('1,4','exit()');
    $this->messageSucces = 'Комментарий удален';
    $res = DB::query('DELETE FROM `'.PREFIX.'comments` WHERE id = '.DB::quote($_POST['id']));
    if (!$res) {
      return false;
    }

    $sql = "
     SELECT `id`
     FROM `".PREFIX."comments`
     WHERE `approved`=0";
     $res = DB::query($sql);
     $count = DB::numRows($res);
     $count = $count ? $count : 0;
     $this->data = array(
       'count' => $count
     );
      
    return true;
  }

  // Получает комментрий по ID
  public function getCommentById() {
    $res = DB::query("SELECT * FROM `".PREFIX."comments` WHERE id = ".DB::quote($_POST['id']));
    $this->data = DB::fetchAssoc($res);
    return true;
  }

  // Сохраняет измененный комментарий
  public function saveComment() {
    USER::AccessOnly('1,4','exit()'); 
    $this->messageSucces = 'Комментарий отредактирован';
    $res = DB::query(
        "UPDATE `".PREFIX."comments` SET 
				name = '%s',
				email = '%s',
				comment = '%s',
				approved = '%s'
			WHERE id = '%d'", $_POST['name'], $_POST['email'], $_POST['comment'], $_POST['approved'], $_POST['id']
    );
    if ($res) {

      $sql = "
  		SELECT `id`
      FROM `".PREFIX."comments`
      WHERE `approved`=0";
      $res = DB::query($sql);
      $count = DB::numRows($res);
      $count = $count ? $count : 0;
      $this->data = array(
        'count' => $count
      );
      return true;
    } else {
      return false;
    }
  }

  /**
   * Устанавливает количество отображаемых записей в разделе новостей
   * @return boolean
   */
  public function setCountPrintRowsComments() {
    USER::AccessOnly('1,4','exit()'); 
    $count = 20;
    if (is_numeric($_POST['count'])&&!empty($_POST['count'])) {
      $count = $_POST['count'];
    }
    MG::setOption(array('option' => 'countPrintRowsComments', 'value' => $count));
    return true;
  }

}

