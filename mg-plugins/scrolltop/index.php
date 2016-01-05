<?php

/*
  Plugin Name: Кнопка для прокрутки страницы вверх
  Description: по шорткоду [scroll-top] на странице выводится кнопка “стрелка вверх”, после нажатия на нее, страница прокручивается в начало.
  Author: Чуркина Дарья
  Version: 1.0.2
 */

new ScrollTop();

class ScrollTop {

  private static $pluginName = ''; // название плагина (соответствует названию папки)
  private static $path = ''; //путь до файлов плагина 

  function __construct() {

    self::$pluginName = PM::getFolderPlugin(__FILE__);
    self::$path = PLUGIN_DIR.self::$pluginName;
    mgAddShortcode('scroll-top', array(__CLASS__, 'showScrollTop'));
    if (!URL::isSection('mg-admin')) { // подключаем CSS плагина для всех страниц, кроме админки
      mgAddMeta('<link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/style.css" type="text/css" />');
      mgAddMeta('<script type="text/javascript" src="'.SITE.'/'.self::$path.'/js/scrolltop.js"></script>');
    }
  }

  static function showScrollTop() {
    $html = '';
    $html .= '<a class="mg-scrollTop" href="#"></a>';
    return $html;
  }

}
