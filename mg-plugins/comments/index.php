<?php

/*
  Plugin Name: Отзывы покупателей
  Description: Плагин позволяет оставлять отзывы о товарах и статьях сайта. Имеет панель адмнистрирования. Добавить форму отзывов можно вставив шорткод [comments] в любое место страницы.
  Author: HollowJ и Avdeev Mark
  Version: 2.1.4
 */
$coments = new CommentsToMoguta;
if (URL::isSection('mg-admin')) {
  MG::addInformer(array('count'=>$coments->getNewCommentsCount(),'class'=>'comment-wrap','classIcon'=>'comment-small-icon', 'isPlugin'=>true, 'section'=>'comments', 'priority'=>80));
 }
class CommentsToMoguta {

  public function __construct() {
    mgActivateThisPlugin(__FILE__, array(__CLASS__, 'createDataComments'));
    mgAddAction(__FILE__, array(__CLASS__, 'pagePluginComments'));
    mgAddShortcode('comments', array(__CLASS__, 'showComments'));
    mgAddShortcode('wall-comments', array(__CLASS__, 'wallComments'));
    mgAddMeta('<script src="'.SITE.'/mg-plugins/comments/js/comments.js"></script>');
	mgAddMeta('<link href="'.SITE.'/mg-plugins/comments/css/style.css" rel="stylesheet" type="text/css">');
  }

// При активации создает таблицу в БД и регистрирует новую опцию
  static function createDataComments() {
    $sql = "
  		 CREATE TABLE IF NOT EXISTS `".PREFIX."comments` (
  			`id` INT AUTO_INCREMENT NOT NULL,
        `name` VARCHAR(45) NOT NULL,
        `email` VARCHAR(45) NOT NULL,
        `comment` TEXT NoT NULL,
        `date` TIMESTAMP NOT NULL,
        `uri` VARCHAR(255) NOT NULL,
        `approved` TINYINT NOT NULL DEFAULT 0, 
        PRIMARY KEY(`id`)
  			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


    DB::query($sql);
    setOption('countPrintRowsComments', 10);
  }

  /**
   * Создает каркас для вывода плагина. Обрабатывается шорткодом
   * @return type 
   */
  static function showComments() {

    $html = "
      <div class='comments'>
      <h3>Отзывы покупателей</h3>
      <div class='comments-msg'></div>
        <form>";

    if (!User::getThis()) {
      $html .= "
        <label>Введите имя:</label>
        <input type='text' name='name' value='' />
        <span class='error'></span>
        <label>Введите email:</label>
        <input type='text' name='email' value='' />
        <span class='error'></span>
      ";
    }

    $html .= "
      <label>Ваш отзыв:</label>
        <textarea name='comment'></textarea>
        <button class='sendComment'>Оставить отзыв</button>     
		<div class='clear'></div>
      </form>
      <div class='comment-list'>";
  
    $comments = self::getComments();
    if (!empty($comments['comments'])) {
      foreach ($comments['comments'] as $item) {
        $html .= "
         <div class='comment-post'>
          <span class='post-author'>".$item['name']."</span>
          <span class='post-date'>".$item['date']."</span>
          <p>".$item['comment']."</p>
        </div>";
      }
      $html .= $comments['pagination'];
    } else {
      $html .= "<div class='comment-post'>Еще никто не оставил отзыв. Вы можете быть первым!</div>";
    }    
 
    return $html.'</div></div>';
  }

  /**
   * Получаем все записи комментариев к этой странице
   * @return type 
   */
  static function getComments() {
    $result = array();
    $where = '';
    if (MG::get('controller')=='controllers_product') {
      $where = " OR uri LIKE '%/".DB::quote(URL::getLastSection(), true)."' OR uri = ".DB::quote("/".URL::getLastSection());
    }
    // Запрос для генерации блока пагинации 
    $sql = "
      SELECT id, name, comment, date
      FROM `".PREFIX."comments` 
      WHERE (uri = ".DB::quote(URL::getClearUri()).$where.") AND approved = '1'
      ORDER BY `date` DESC";

    //Получаем блок пагинации
    if ($_GET["comm_page"]) {
      $page = $_GET["comm_page"]; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс
    }


    $navigator = new Navigator($sql, $page, MG::getSetting('countPrintRowsComments'), 6, false, "comm_page"); //определяем класс
    $comments = $navigator->getRowsSql();
    $pagination = $navigator->getPager();

	
    /*
     * Получаем  комментарии.	
     */
    foreach ($comments as $key => $value) {
      $comments[$key]['date'] = date('d.m.Y H:i', strtotime($comments[$key]['date']));
    }

    $result['comments'] = $comments;
    $result['pagination'] = $pagination;

    return $result;
  }

  /**
   * Получаем количество новых комментариев 
   */
  static function getNewCommentsCount() {   
    $exist=false;
    $result = DB::query('SHOW TABLES');
      while($row = DB::fetchArray($result)){
        if( PREFIX."comments"==$row[0]){
          $exist=true;
        };
      }
      

    if ($exist){
      $sql = "
        SELECT `id`
        FROM `".PREFIX."comments`
        WHERE `approved`=0";

      $res = DB::query($sql);
      $count = DB::numRows($res); 

    }
    return $count?$count:0;
  }
  
  /**
   * Вывод страницы плагина в админке
   */
  static function pagePluginComments() {
    $lang = PM::plugLocales('comments');
    if ($_POST["page"])
      $page = $_POST["page"]; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс

    $countPrintRowsComments = MG::getOption('countPrintRowsComments');
    $navigator = new Navigator("SELECT  *  FROM `".PREFIX."comments` ORDER BY `id` DESC", $page, $countPrintRowsComments); //определяем класс
    $comments = $navigator->getRowsSql();
    $pagination = $navigator->getPager('forAjax');

    // подключаем view для страницы плагина
    include 'pagePlugin.php';
  }
  
   /**
   * Вывод всех комментариев
   */
  static function wallComments() {
    $comments = self::getComments();
    $html .= '<div class="reviews-big">';
    if (!empty($comments['comments'])) {
      foreach ($comments['comments'] as $item) {
        $html .= '
         <div class="reviews-info">
          <span class="user-name">'.$item['name'].'</span>
          <span class="add-date">'.$item['date'].'</span>
         </div>   
          <p>'.$item['comment'].'</p>
        ';
      }
      $html .= '</div>'.$comments['pagination'];
    }
   return $html;
  }

}