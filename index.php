<?php
/**
 * Файл index.php расположен в корне CMS, является единственной точкой инициализирующей работу системы.
 *
 * В этом файле:
 *  - настраивается вывод ошибок;
 *  - устанавлюиваются константы для работы движка;
 *  - массивом $includePath задаются пути для поиска библиотек при подключении файлов движка.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Files
 */

//Не выводить предупреждения и ошибки.
Error_Reporting(0);

// Установка кодировки для вывода контента.
header('Content-Type: text/html; charset=utf-8');
/**
 * Путь корневой директории сайта.
 */
define('SITE_DIR', $_SERVER['DOCUMENT_ROOT'].'/');

/**
 * Путь к директории ядра.
 */
define('CORE_DIR', 'mg-core/');

/**
 * Путь к директории с библиотеками движка.
 */
define('CORE_LIB', CORE_DIR.'lib/');

/**
 * Путь к директории с JS скриптам.
 */
define('CORE_JS', CORE_DIR.'script/');

/**
 * Путь к директории админки.
 */
define('ADMIN_DIR', 'mg-admin/');

/**
 * Путь к директории плагинов.
 */
define('PLUGIN_DIR', 'mg-plugins/'); 

/**
 * Путь к директории пользовательских php страниц.
 */
define('PAGE_DIR', 'mg-pages/');

/**
 *  Текущая версия.
 */
define('VER', 'v5.7.2');

// Установка путей, для поиска подключаемых библиотек.
$includePath = array(CORE_DIR,CORE_LIB);
set_include_path('.'.PATH_SEPARATOR.implode(PATH_SEPARATOR, $includePath));

/**
 * Автоматически подгружает запрошенные классы.
 * @param type $className наименование класса.
 * @return void
 */
function __autoload($className){
  $path = str_replace('_', '/', strtolower($className));
  return include_once $path.'.php';
}


/**
 * Подключает движок и запускает CMS.
 */
require_once ('mg-start.php');