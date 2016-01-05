<?php

/*
  Plugin Name: Продающие триггеры
  Description: Триггеры - это маркетинговый метод, позволяющий с минимальными трудовыми затратами повысить эффективность сайта и поднять продажи. С помощью плагина Вы можете создать триггер из нескольких элементов, добавив иконки или изображения с ярким заголовком. Большой выбор настроек (цвет, фон, расположение и др.) позволит создать триггер идеально подходящий к Вашему сайту. Для вывода на сайт необходимо добавить шорт-код [trigger-guarantee id="номер триггера"]
  Author: Дарья Чуркина
  Version: 1.0.6
 */

new trigger;

class trigger {

  private static $lang = array(); // массив с переводом плагина 
  private static $pluginName = ''; // название плагина (соответствует названию папки)
  private static $path = ''; //путь до файлов плагина 

  public function __construct() {

    mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate')); //Инициализация  метода выполняющегося при активации  
    mgAddAction(__FILE__, array(__CLASS__, 'pageSettingsPlugin')); //Инициализация  метода выполняющегося при нажатии на кнопку настроект плагина  
    mgAddShortcode('trigger-guarantee', array(__CLASS__, 'viewTrigger')); // Инициализация шорткода [trigger-guarantee] - доступен в любом HTML коде движка.    

    self::$pluginName = PM::getFolderPlugin(__FILE__);
    self::$lang = PM::plugLocales(self::$pluginName);
    self::$path = PLUGIN_DIR.self::$pluginName;

    mgAddMeta('<link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/style.css" type="text/css" />');   
  }

  /**
   * Метод выполняющийся при активации палагина 
   */
  static function activate() {

    self::createDateBase();
  }

  /**
   * Создает таблицу плагина в БД
   */
  static function createDateBase() {
    // Запрос для проверки, был ли плагин установлен ранее.
    $exist = false;
    $result = DB::query('SHOW TABLES LIKE "'.PREFIX.self::$pluginName.'"');
    if (DB::numRows($result)) {
      $exist = true;
    }
    if (!$exist) {
      DB::query("
     CREATE TABLE IF NOT EXISTS `".PREFIX.self::$pluginName."` (
       `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер',      
       `title` text NOT NULL COMMENT 'Загаловок',
       `settings` text NOT NULL COMMENT 'Настройки',       
       PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

      DB::query("
     CREATE TABLE IF NOT EXISTS `".PREFIX.self::$pluginName."-elements` (
       `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер',      
       `parent` int(11) NOT NULL COMMENT 'id блока',
       `text` text  NOT NULL COMMENT 'Текст триггера',
       `icon` text  NOT NULL COMMENT 'Иконка или url картинки',
       `sort` int(11) NOT NULL COMMENT 'Сортировка',
       PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
      $settings = array(
        'form' => 'square',
        'place' => 'left',
        'color_icon' => '000',
        'background_icon' => 'fff',
        'background' => 'fff',
        'width' => '31',
        'height' => '90',
        'layout' => 'horfloat',
      );
      DB::query("INSERT INTO `".PREFIX.self::$pluginName."` SET `id`=1,
        `settings` = ".DB::quote(addslashes(serialize($settings)))." ");
      DB::query("INSERT INTO `".PREFIX.self::$pluginName."-elements` (`id`, `parent`, `text`, `icon`, `sort`) VALUES
(8, 1, '<div><span style=\"color: rgb(0, 0, 0); font-family: Tahoma, Verdana, sans-serif; line-height: 21px;\">Гарантия качества на все товары</span></div>\n', '<i class=\"fa fa-check-circle-o fa-5x\"></i>', 8),
(10, 1, '<div><span style=\"color: rgb(0, 0, 0); font-family: Tahoma, Verdana, sans-serif; line-height: 21px;\">Оплата Visa и MasterCard</span></div>\n', '<i class=\"fa fa-cc-visa fa-4x\"></i>', 10),
(9, 1, '<div><span style=\"color: rgb(0, 0, 0); font-family: Tahoma, Verdana, sans-serif; line-height: 21px;\">Бесплатная доставка от 3 тыс. руб.</span></div>\n', '<i class=\"fa fa-truck fa-4x\"></i>', 9)");
      // Если плагин впервые активирован, то задаются настройки по умолчанию 
      MG::setOption(array('option' => 'countPrintRowsTrigger', 'value' => 10));
    }
  }

  /**
   * Метод выполняющийся перед генерацией страницы настроек плагина
   */
  static function preparePageSettings() {
    echo '         
	    <link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/font-awesome.min.css" />
      <link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/style.css" type="text/css" />     
        <link rel="stylesheet" href="'.SITE.'/'.self::$path.'/colpick/css/colpick.css" type="text/css"/>
      <script type="text/javascript">
        includeJS("'.SITE.'/'.self::$path.'/colpick/js/colpick.js"); 
        includeJS("'.SITE.'/'.self::$path.'/js/script.js");     
      </script> 
    ';
  }

  /**
   * Выводит страницу настроек плагина в админке
   */
  static function pageSettingsPlugin() {
    $lang = self::$lang;
    $pluginName = self::$pluginName;
    $sql = 'SELECT * FROM `'.PREFIX.$pluginName.'`';
    $rez = DB::query($sql);
    $trigger = array();
    while ($row = DB::fetchArray($rez)) {
      $trigger[] = $row;
    }
    $rez = DB::query('SELECT max(`id`)+1 as nextid FROM `'.PREFIX.$pluginName.'`');
    $nextIdTrig = DB::fetchArray($rez);
    $countPrintRows = MG::getSetting('countPrintRowsTrigger');
    $res = DB::query("SELECT * FROM `".PREFIX.self::$pluginName."-elements` ORDER BY `parent`, `sort`");
    $entity = array();
    while ($row = DB::fetchArray($res)) {
      $entity[] = $row;
    }
    self::preparePageSettings();
    include('pageplugin.php');
  }

  // срабатывает на месте шорт-кода [trigger-guarantee]
  static function viewTrigger($arg) {
    if ($arg['id']) {
      $sql = 'SELECT * FROM `'.PREFIX.self::$pluginName.'` WHERE `id`='.DB::quote($arg['id']);
      $rez = DB::query($sql);
      if ($trigger = DB::fetchArray($rez)) {
        $options = unserialize(stripslashes($trigger['settings']));
        $res = DB::query("SELECT * FROM `".PREFIX.self::$pluginName."-elements` WHERE `parent`=".DB::quote($arg['id'])." ORDER BY `sort`");
        while ($elements = DB::fetchArray($res)) {
          $trigger['elements'][] = $elements;
        }
        $radius = $options['form'] == 'circle' ? '50%' : '0';
        $float = 'float:none';
        switch ($options['place']) {
          case 'left' : $float = 'float:none';
            break;
          case 'top' : $float = 'float:none; display: block';
        }
       
        $unit = $options['unit']==2 ? 'px' : '%';
        $style = ' background-color:#'.$options['background_icon'].'; color:#'.$options['color_icon'].'; border-radius: '.$radius.'; font-size: '.$options['fontSize'].'em;"';
               
        $layout = $options['layout'] == 'column' ? 'layout_column.php' : 'layout.php';
        if ($options['layout']=='vertleft'||$options['layout']=='vertright') {
          $styleTrigg = 'style="width: '.$options['width'].$unit.';"';
          $widthTrig = '';
        } else {
          $styleTrigg = '';
          $widthTrig = 'width: '.$options['width'].$unit.';';
        }
        ob_start();
        include ($layout);
        $html = ob_get_contents();
        ob_clean();
        return $html;
      }
    }
  }

}
