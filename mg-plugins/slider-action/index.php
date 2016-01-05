<?php

/*
  Plugin Name: Слайдер акций
  Description: Плагин определяет шорткод [slider-action], имеет страницу настроек, создает в БД таблицу для дальнейшей работы, использует собственный файл локали, свой  CSS и JS скрипы.
  Author: Avdeev Mark
  Version: 1.0.2
 */

new SliderAction;
mgAddMeta('<script type="text/javascript" src="'.SCRIPT.'jquery.bxslider.min.js"></script>');
class SliderAction {

  private static $lang = array(); // массив с переводом плагина 
  private static $pluginName = ''; // название плагина (соответствует названию папки)
  private static $path = ''; //путь до файлов плагина 

  public function __construct() {

    mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate')); //Инициализация  метода выполняющегося при активации  
    mgAddAction(__FILE__, array(__CLASS__, 'pageSettingsPlugin')); //Инициализация  метода выполняющегося при нажатии на кнопку настроект плагина  
    mgAddShortcode('slider-action', array(__CLASS__, 'sliderAction')); // Инициализация шорткода [slider-action] - доступен в любом HTML коде движка.    

    self::$pluginName = PM::getFolderPlugin(__FILE__);
    self::$lang = PM::plugLocales(self::$pluginName);
    self::$path = PLUGIN_DIR.self::$pluginName;

    if (!URL::isSection('mg-admin')) { // подключаем CSS плагина для всех страниц, кроме админки
      mgAddMeta('<link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/style.css" type="text/css" />');
    }
  }

  
  /**
   * Метод выполняющийся при активации палагина 
   */
  static function activate() {
    self::createDateBase();
  }

  
  /**
   * Метод выполняющийся перед генераццией страницы настроек плагина
   */
  static function preparePageSettings() {
    echo '   
      <link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/style.css" type="text/css" />
      <script type="text/javascript" src="'.SITE.'/'.self::$path.'/js/jquery.bxslider.min.js"></script>
      <script type="text/javascript">
        includeJS("'.SITE.'/'.self::$path.'/js/script.js");  
      </script> 
    ';
  }

  
  /**
   * Создает таблицу плагина в БД
   */
  static function createDateBase() {
    DB::query("
     CREATE TABLE IF NOT EXISTS `".PREFIX.self::$pluginName."` (
      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер записи',
      `type` varchar(255) NOT NULL COMMENT 'Тип слайда картинка или HTML',
	    `nameaction` text NOT NULL COMMENT 'Название слайда',
      `href` text NOT NULL COMMENT 'ссылка', 
      `value` text NOT NULL COMMENT 'значение',      
      `sort` int(11) NOT NULL COMMENT 'Порядок слайдов',
      `invisible` int(1) NOT NULL COMMENT 'видимость',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    // запрос для проверки, был ли плагин установлен ранее.
    $res = DB::query("
      SELECT id
      FROM `".PREFIX.self::$pluginName."`
      WHERE id in (1,2,3) 
    ");
    
    // если плагин впервые активирован то задаются настройки по умолчанию 
    if (!DB::numRows($res)) {
      DB::query("
      INSERT INTO `".PREFIX.self::$pluginName."` (`id`, `type`,`nameaction`, `href`, `value`, `sort`, `invisible`) VALUES
        (1, 'img', 'Акция 1','".SITE."/catalog', ".DB::quote("<img src='".SITE.'/mg-plugins/'.self::$pluginName."/images/pic/slide1.jpg' alt=''>").", 1,1),
        (2, 'img', 'Акция 2','".SITE."/feedback', ".DB::quote("<img src='".SITE.'/mg-plugins/'.self::$pluginName."/images/pic/slide2.jpg' alt=''>").", 2,1),
        (3, 'html', 'Акция 3','".SITE."/contacts', '<div style=\"background-color: blue; width: 1000px; height: 300px; text-align: center; background:rgb(214, 214, 214);\"><br />\n<span style=\"color:#006400;\"><span style=\"font-size:24px;\"><strong><font face=\"georgia, serif\">Название акциии<br />\n<br />\n<br />\n<br />\n<br />\nЛюбой HTML контент</font></strong></span></span><br />\n<br />\n<br />\n&nbsp;</div>\n', 3,1);
      ");
      $array = Array(
        'width' => '',
        'height' => '',
        'speed' => '2000',
        'pause' => '1500',
        'mode' => 'horizontal',
        'position' => 'left'
      );
      MG::setOption(array('option' => 'sliderActionOption', 'value' => addslashes(serialize($array))));
    }
  }

  /**
   * Выводит страницу настроек плагина в админке
   */
  static function pageSettingsPlugin() {
    $lang = self::$lang;
    $pluginName = self::$pluginName;
    $entity = self::getEntity();

    self::preparePageSettings();

    //получаем опцию sliderActionOption в переменную option
    $option = MG::getSetting('sliderActionOption');
    $option = stripslashes($option);
    $options = unserialize($option);

    include('pageplugin.php');
  }

  
  /**
   * Получает из БД записи
   */
  static function getEntity() {
    $entity = array();
    $res = DB::query("
      SELECT * 
      FROM `".PREFIX.self::$pluginName."` 
        ORDER BY sort ASC
    ");
    while ($row = DB::fetchAssoc($res)) {
      $entity[] = $row;
    }

    return $entity;
  }

  
  /**
   * Обработчик шотркода вида [slider-action] 
   * выполняется когда при генерации страницы встречается [slider-action] 
   */
  static function sliderAction() {

    if (!URL::isSection('mg-admin')) {
      $option = MG::getSetting('sliderActionOption');
    } else {
      $option = MG::getOption('sliderActionOption');
    }

    $option = stripslashes($option);
    $options = unserialize($option);

    $options["width"] = $options["width"] ? $options["width"].'px' : '100%';
    $options["height"] = $options["height"] ? $options["height"].'px' : 'auto';

    if ($options["position"]=='right') {
      $options["position"] = "float:right;";
    };

    if ($options["position"]=='left') {
      $options["position"] = "float:left;";
    };

    if ($options["position"]=='center') {
      $options["position"] = "margin: 0 auto;";
    };

    $slides = self::getEntity();

    $html = '<div class="m-p-slider-wrapper" style="width:'.$options["width"].'; height:'.$options["height"].'; '.$options["position"].'">';
    if ($options["titleslider"]!="") {
      $html .= '<h2>'.$options["titleslider"].'</h2>';
    };
    $html .= '<div class="m-p-slider-contain">';

    foreach ($slides as $slide) {
      if (!$slide['invisible']) {
        continue;
      }
      if ($slide["type"]=="img"&&!empty($slide["href"])) {
        $slide["value"] = '<a href="'.$slide["href"].'">'.$slide["value"].'</a>';
      }

      $html .= "<div class='m-p-slide-unit'>".$slide["value"];

      if ($options["nameaction"]=='true') {
        $html .= "<div class='nameaction'>".$slide["nameaction"]."</div>";
      }

      $html .= "</div>";
    }

    $html .= "</div>
			</div>
      <div class='clear fix-slider-block' style='display:inline-block'></div>
    ";


    $options["pause"] = $options["pause"] ? $options["pause"] : '1500';
    $options["mode"] = $options["mode"] ? $options["mode"] : 'horizontal';
    $options["speed"] = $options["speed"] ? $options["speed"] : '3000';

    $html .= '
      <script type="text/javascript">       
        $(document).ready(function() {
        $(".m-p-slider-contain").bxSlider({
          minSlides: 1,
          maxSlides: 1,
          pager:true,
          adaptiveHeight: false,
          auto:true,
          pause: '.$options["pause"].',
          useCSS: false,
          speed:'.$options["speed"].',
          mode: "'.$options["mode"].'",				  
        });
       });
      </script>
    ';

    $html .= '<div class="after-slider-content"></div>';

    return $html;
  }

}