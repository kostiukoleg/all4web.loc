<?php

/**
 * Класс PM - плагин-менеджер, управляет плагинами и регистрирует их. Устанавливает взаимодействие пользовательских функций с системой.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class PM implements PluginManager {

  static private $_instance = null;
  // Заристрированные обработчики хуков.
  static private $_eventHook;
  // Список зарегистрированных шорткодов.
  static public $listShortCode = array();
  static private $_pluginsPath = 'mg-plugins/';
  // Данные о плагинах
  static private $plugins = array();
  static private $pluginsInfo = array();
  static private $pluginsFolder = array();
  
  private static $_updateServer = 'http://moguta.ru';

  public function __construct() {
    self::$_eventHook = array();
  }

  /**
   * Добавляет локаль плагина, если она есть в папке /locales текущего плагина $pluginName.
   * @param $pluginName - название плагина
   */
  static public function plugLocales($pluginName) {
    $return = array();

    $filename = self::$_pluginsPath.$pluginName.'/locales/'.MG::getSetting('languageLocale').'.php';
    if (file_exists($filename)) {
      include($filename);
      $return = $lang;
    }

    return $return;
  }

  private function __clone() {
    
  }

  private function __wakeup() {
    
  }

  /**
   * Возвращет единственный экземпляр данного класса.
   * @return object - объект класса PM.
   */
  public static function getInstance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * Возвращает массив названий шорткодов.
   * Все хуки для шорткодов начинаются с префикса "shortcode_"
   * @return array - массив названий шорткодов.
   */
  public static function getListShortCode() {
   
    $result = array();
      if (sizeof(self::$_eventHook)) {
        foreach (self::$_eventHook as $eventHook) {      
          $nameHook = $eventHook->getHookName();
          if (strpos($nameHook, 'shortcode_') === 0) {
            $result[] = str_replace('shortcode_', '', $nameHook);
          }
        }
      }
      self::$listShortCode = $result;
 
    
    return self::$listShortCode;
  }

  /**
   * Возвращает массив названий зарегистрированных хуков.
   * @return array -  массив названий зарегистрированных хуков.
   */
  public static function getListNameHooks() {
    $result = array();
    if (sizeof(self::$_eventHook)) {
      foreach (self::$_eventHook as $eventHook) {
        $result[] = strtolower($eventHook->getHookName());
      }
    }
    return $result;
  }

  /**
   * Проверяет зарегистрирован ли хук.
   * @param $hookname - имя хука, который надо проверить на регистрацию.
   * @return bool.
   */
  public static function isHookInReg($hookname) {
    return in_array(strtolower($hookname), self::getListNameHooks());
  }

  /**
   * Инициализирует объект данного класса.
   */
  public static function init() {
    self::getInstance();
  }

  /**
   * Регистрирует обработчик для действия, занося его в реестр обработчиков.
   * @param Hook $eventHook - объект содержащий информацию об обработчике и событии.
   */
  public static function registration(Hook $eventHook) {
    self::$_eventHook[] = $eventHook;
  }

  /**
   * Удаляет из рееестра данные об обработчике.
   * @param Hook $eventHook  - объект содержащий информацию об обработчике и событии.
   */
  public static function delete(Hook $eventHook) {
    if ($id = array_search($eventHook, self::$_eventHook, TRUE)) {
      unset(self::$_eventHook[$id]);
    }
  }

  /**
   * Вычисляет приоритетность пользовательских функций, назначеных на обработку одного и тогоже события.
   * Используется для сравнения приоритетов в функции.
   *
   * @param $a - приоритет текущей функции.
   * @param $a - приоритет предыдущей функции usort  в методе 'PM::createHook'.
   */
  public static function prioritet($a, $b) {
    return $a['priority'] - $b['priority'];
  }

  /**
   * Инициализирует то или иное событие в коде программы,
   * сообщая об этом всем зарегистрированных обработчикам.
   * Если существуют обработчики назначенные на данное событие,
   * то запускает их пользовательские функции, в порядке очереди
   * определенной приоритетами.
   *
   * @param string $hookName - название  события.
   * @param type $arg - массив арументов.
   * @param type $result - флаг, определяющий, должна ли пользовательская
   *   функция вернуть результат для дальнейшей работы в месте инициализации события.
   * @return array
   */
  public static function createHook($hookName, $arg, $result = false) {
    $hookName = strtolower($hookName);
    if ($result) {
      if (sizeof(self::$_eventHook)) {
        foreach (self::$_eventHook as $eventHook) {

          // Если нашлись пользовательские функции которые хотя обработать событие.
          if ($eventHook->getHookName() == $hookName && $eventHook->getCountArg() == 1) {

            // В массив найденых обработчиков записываем все обработчики и их порядок выполнения.
            $handleEventHooks[] = array(
              'eventHook' => $eventHook,
              'priority' => $eventHook->getPriority()
            );
          }
        }

        // Запускает функции всех подходящих обработчиков.
        if (!empty($handleEventHooks)) {

          // Сортировка в порядке приоритетов.
          usort($handleEventHooks, array(__CLASS__, "prioritet"));

          foreach ($handleEventHooks as $handle) {
            $arg['result'] = $handle['eventHook']->run($arg);
          }
          return $arg['result'];
        }
      }
      return $arg['result'];
    } else {

      $countArg = count($arg);
      if (sizeof(self::$_eventHook)) {
        foreach (self::$_eventHook as $eventHook) {

          if ($eventHook->getHookName() == $hookName && $eventHook->getCountArg() == $countArg) {

            $eventHook->run($arg);
          }
        }
      }
    }
  }

  /**
   *  Подключает все плагины соответствующии требованиям.
   *  - Все плагины должны содержаться в каталоге mg-plugins/;
   *  - Название папки содержащей плагин, может быть любым;
   *  - Если в папке плагина есть файл index.php и в первом блоковом комментариии
   *    он содержит хотябы один из доступных параметров PluginName ,
   *    то плагин будет подключен.
   *    Пример мета информации в index.php
   *    <code>
   *     Plugin Name: Hello World
   *     Plugin URI: http://moguta.ru/plugins/HelloWorld/
   *     Description: Плагин для демонстрации функционала
   *     Author: mogutaTeam
   *     Version: 1.0
   *    </code>
   *  @return void
   */
  public static function includePlugins() {
    $pluginsInfo = self::getPluginsInfo();
    
    foreach ($pluginsInfo as $plugin) {    
      
      //никогда не подключать плагин с названием catalog-filter
      if($plugin['folderName'] == 'catalog-filter'){
        continue;
      }
      // Подключает только активные плагины.
      if ("1" == $plugin['Active']&&!empty($plugin['folderName'])) {        
        require_once PLUGIN_DIR.$plugin['folderName'].'/index.php';
      }
    }
  }

  /**
   *  Подключает один конкретный плагин хранящийся в выбраной директории.
   *  @param string $folderName - наименование папки плагина.
   *  @return void
   */
  public static function includePluginInFolder($folderName) {
    require_once PLUGIN_DIR.$folderName.'/index.php';
  }

  /**
   *  Считывает информацию обо всех плагинах в дирректории PLUGIN_DIR.
   *  @return void
   */
  public static function getPluginsInfo() {
    //self::checkPluginsUpdate();
    if(empty(self::$pluginsInfo)){
      $result = array();
      $plugins = scandir(PLUGIN_DIR);
      unset($plugins[1]);
      foreach ($plugins as $folderName) {
        
        if (!is_dir($folderName)) {        
          $plug = self::readInfo($folderName);
          
          if($plug){
            $result[] = $plug;
          }
        }
      }
      
      $pluginsUpdateInfo = unserialize(stripslashes(MG::getSetting('pluginsVersionInfo')));
      $pluginsList = array_keys($pluginsUpdateInfo);
      //viewData($pluginsUpdateInfo);
      // Считываем активность плагинов из БД.
      $res = DB::query("SELECT *  FROM `".PREFIX."plugins`");
      while ($row = DB::fetchArray($res)) {
        $pluginsActivity[$row['folderName']] = $row['active'];
      }

      // Сортировка в порядке по алфавиту.
      usort($result, array(__CLASS__, "sortByPluginName"));
      
      // Дополняем массив найденых плагинов информацие их активности.
      foreach ($result as $id => $plugin) {    
        $result[$id]['Active'] = isset($pluginsActivity[$plugin['folderName']]) ? $pluginsActivity[$plugin['folderName']] : 0;

        if(in_array($plugin['folderName'], $pluginsList)){
          self::$pluginsFolder[$plugin['folderName']] = $id;
          $result[$id]['update'] = $pluginsUpdateInfo[$plugin['folderName']];
          $result[$id]['update']['description'] = $result[$id]['update']['description'];
        }
      }

      self::$pluginsInfo = $result;
      
    }else{
      $result = self::$pluginsInfo;
    }
    
    return $result;
  }
  
  /**
   * Отправляет запрос на сервер, с целью получить данные о последней версии.
   *
   * @param string $url адрес сервера.
   * @param string $post  параметры для POST запроса.
   * @return string ответ сервера.
   */
  private static function sendCurl($url, $post) {


    // Иницализация библиотеки curl.
    $ch = curl_init();

    // Устанавливает URL запроса.
    curl_setopt($ch, CURLOPT_URL, $url);

    // При значении true CURL включает в вывод заголовки.
    curl_setopt($ch, CURLOPT_HEADER, false);

    // Куда помещать результат выполнения запроса:
    //  false – в стандартный поток вывода,
    //  true – в виде возвращаемого значения функции curl_exec.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Нужно явно указать, что будет POST запрос.
    curl_setopt($ch, CURLOPT_POST, true);

    // Здесь передаются значения переменных.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

    // Максимальное время ожидания в секундах.
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

    // Выполнение запроса.
    $res = curl_exec($ch);

    //  return array();
    // Освобождение ресурса.
    curl_close($ch);
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $res, $args);
  }

  /**
   * Функция для сортировки плагинов по активности.
   *
   * @param $a - активность текущего плагина.
   * @param $b - активность предыдущего плагина (usort  в методе 'PM::getPluginsInfo').
   */
  public static function sortByActivity($a, $b) {
    return $b['Active'] - $a['Active'];
  }
  
   /**
   * Функция для сортировки плагинов по алфавиту.
   *
   * @param $a - название первог оплагина.
   * @param $b - название второго плагина.
   */
  public static function sortByPluginName($a, $b) {
    if ($a['PluginName']==$b['PluginName']) return 0; 
    return ($a['PluginName']>$b['PluginName']) ? 1 : -1; 
  }  


  /**
   * Считывает информацию о конкретном плагине.
   * @param string $folderName - путь к файлу index.php плагина
   * @return если соответствует стандартам то array иначе null.
   */
  public static function readInfo($folderName) {
    $pluginDirectory = PLUGIN_DIR.$folderName.'/index.php';

    // Считываем содержание index.php.
    if (file_exists($pluginDirectory)) {
      $contentIndex = file_get_contents($pluginDirectory);

      // Удаляем все переносы строк.
      $contentIndex = str_replace(array("\r\n", "\n", "\r"), 'infoParam', $contentIndex);

      // Ищем все блочные комментарии.
      preg_match_all('~/\*(.*?)\*/~i', $contentIndex, $pluginInfo);
      $result = array();

      // Определяем доступные информационные параметры.
      $parametr = array(
        'PluginName' => 'Plugin Name',
        'PluginURI' => 'Plugin URI',
        'Description' => 'Description',
        'Author' => 'Author',
        'Version' => 'Version'
      );

      // Ищем в первом блочном комментарии информацию, по доступным параметрам $parametr.
      foreach ($parametr as $key => $value) {
        preg_match('~'.$value.'\s?:\s?(.*?)infoParam~i', $pluginInfo[1][0], $pluginData);

        $tmp = '';
        if (!empty($pluginData[1])) {
          //замена фигурной скобки на мнемонику, чтобы не производить обработку шорткода 
          $tmp = str_replace('[', '&#091;', $pluginData[1]);
        }
        $result[$key] = $tmp;
      }

      // Если не существует параметра PluginName, то файл не корректно задает плагин.
      if (!empty($result['PluginName'])) {  
        $result['folderName'] = $folderName;
        return $result;
      }
    }
    return null;
  }

  /**
   * Получает название папки в которой хранится плагин.
   * @param type $dir - директория в коотрой хранится плагин.
   */
  public static function getFolderPlugin($dir) {
    $section = explode(DIRECTORY_SEPARATOR, dirname($dir));
    $folderName = count($section) > 1 ? end($section) : $dir;
    return strtolower($folderName);
  }

  /**
   * Ищет в контенте шорткоды и запускает их обработчики.
   * Если шотркод не определен или его плагин отключен, он будет возвращен без обработки.
   *
   * @param string $content - строка для поиска в ней шорткодов.
   * @return string исходную строку с результатами выполнения хуков для шорткодов.
   */
  public static function doShortcode($content) {
    
    $shortCodes = self::getListShortCode();
    if (empty($shortCodes) || URL::isSection('mg-admin')) {
      return $content;
    } elseif(substr_count($content, '[') > 400) {
      echo 'Превышено допустимое количество шорткодов на странице. <br />Пожалуйста, примите меры по уменьшению числа шорткодов';
      exit();
    }

    // Получает шаблон для поиска шорткодов.
    $pattern = self::getShortcodeRegex();

    return preg_replace_callback("/$pattern/s", array(__CLASS__, 'doShortcodeTag'), $content);
  }

  /**
   * Возвращает шаблон для поиска по регулярному выражению.
   * Регулярное выражение содержит 6 различных частей,
   *  для обеспечения разбора контента, которые ищут:
   * 1 - Открывающую скобку [ исключая вложения их друг в друга [[]];
   * 2 - Название шорткода;
   * 3 - Список аргументов;
   * 4 - Закрывающий слешь /;
   * 5 - Содержимое шорткода, между тегами;
   * 6 - Закрывающую скобку [ исключая вложения их друг в друга [[]];
   *
   * @return string регулярное выражение для поиска шорткода.
   */
  public static function getShortcodeRegex() {

    $tagnames = self::getListShortCode();
    $tagregexp = join('|', array_map('preg_quote', $tagnames));

    // ВНИМАНИЕ! Не используйте это
    // выражение без методов do_shortcode_tag() и strip_shortcode_tag()
    return
      '\\['                              // Открывающая скобка
      .'(\\[?)'                           // 1: Дополнительная проверка на вложенность: [[tag]]
      ."($tagregexp)"                     // 2: Имя тега
      .'\\b'                              // Слово - граница
      .'('                                // 3: Проверка внутри открыторого тега
      .'[^\\]\\/]*'                   //    - не закрывающий слеш или скобка
      .'(?:'
      .'\\/(?!\\])'               // нет последовательности - /]
      .'[^\\]\\/]*'               // нет закрывающей скобки либо слеша
      .')*?'
      .')'
      .'(?:'
      .'(\\/)'                        // 4: Текущий тег - закрывающий ...
      .'\\]'                          // ... и закрывающая скобка
      .'|'
      .'\\]'                          // Закрывающая скобка
      .'(?:'
      .'('                        // 5: Содержимое между тегами [shotcode]$content[shotcode/]
      .'[^\\[]*+'             // Нет открывающей скобки
      .'(?:'
      .'\\[(?!\\/\\2\\])' // нет последовательности  закрывающий слеш со скобкой
      .'[^\\[]*+'         // нет открывающейся скобки
      .')*+'
      .')'
      .'\\[\\/\\2\\]'             // Закрывающий тег кода
      .')?'
      .')'
      .'(\\]?)';                          // 6: исключает вложение[[tag]]
  }

  /**
   * Проверка разобраных частей для передачи в обработчик хука
   * @param array $m массив полученный регулярным выражением
   */
  public static function doShortcodeTag($m) {

    if ($m[1] == '[' && $m[6] == ']') {
      return substr($m[0], 1, -1);
    }

    $tag = $m[2];
    $attr = self::shortcodeParseAtts($m[3]);


    // если между тегами есть содержимое, до записываем его в аргументы с ключем content
    if (!empty($m[5])) {
      $attr['content'] = $m[5];
    }

    return self::createHook('shortcode_'.$tag, $attr, true);
  }

  /**
   * Возвращает список атрибутов внутри шорткода.
   * в виде массива пар - {ключ:значение}
   * @param string строка
   * @return array массив атрибутов со значениями.
   */
  public static function shortcodeParseAtts($text) {
    $atts = array();
    $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
    if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
      foreach ($match as $m) {
        if (!empty($m[1]))
          $atts[strtolower($m[1])] = stripcslashes($m[2]);
        elseif (!empty($m[3]))
          $atts[strtolower($m[3])] = stripcslashes($m[4]);
        elseif (!empty($m[5]))
          $atts[strtolower($m[5])] = stripcslashes($m[6]);
        elseif (isset($m[7]) and strlen($m[7]))
          $atts[] = stripcslashes($m[7]);
        elseif (isset($m[8]))
          $atts[] = stripcslashes($m[8]);
      }
    } else {
      $atts = ltrim($text);
    }
    return $atts;
  }

  /**
   * Очищает контент от всех шорткодов.
   * @param string $content строка с шорткодами.
   * @return string исходная строка уже без шорткодов.
   */
  public static function stripShortcodes($content) {      
    // Получает шаблон для поиска шорткодов.
    $pattern = self::getShortcodeRegex(); 
  
    return $content;
  }

  public static function stripShortcodeTag($m) {
    if ($m[1] == '[' && $m[6] == ']') {
      return substr($m[0], 1, -1);
    }

    return $m[1].$m[6];
  }

  /**
   * Метод валидации загружаемого плагина.
   *
   * @param array $file_array - массив данных загружаемого плагина
   * @return array данные о загрузке,
   *     'data' => если имеется имя загруженного плагина
    'msg' => статус(сообщение) загрузки
   */
  public static function downloadPlugin($file_array) {
    //имя плагина
    $name = $file_array['name'];
    //его размер
    $size = $file_array['size'];
    //временная папка архива плагина
    $path = self::$_pluginsPath;
    //поддерживаемые форматы
    $validFormats = array('zip');

    $_lang = MG::get('lang');

    if (strlen($name)) {
      $fullName = explode('.', $name);
      $ext = array_pop($fullName);
      $name = implode('.', $fullName);
      if (in_array($ext, $validFormats)) {
        if ($size < (1024 * 1024 )) {
          $actualName = $name.'.'.$ext;
          $tmp = $file_array['tmp_name'];

          if (move_uploaded_file($tmp, $path.$actualName)) {
            $data = $path.$actualName;
            $msg = $_lang['PLUG_UPLOAD'];
          } else {
            $msg = $_lang['PLUG_UPLOAD_ERR'];
          }
        } else {
          $msg = $_lang['PLUG_UPLOAD_ERR2'];
        }
      } else {
        $msg = $_lang['PLUG_UPLOAD_ERR3'];
      }
    } else {
      $msg = $_lang['PLUG_UPLOAD_ERR4'];
    }


    return array(
      'data' => $data,
      'msg' => $msg
    );
  }

  /**
   * Распаковка плагина при установке через панель администрирования
   * @param string $archiveFile - путь до файла с плагином
   * @param string $pluginName - название папки плагина
   * @return boolean
   */
  public static function extractPluginZip($archiveFile, $pluginName = '') {

    if (file_exists($archiveFile)) {
      $zip = new ZipArchive;
      $res = $zip->open($archiveFile, ZIPARCHIVE::CREATE);

      if ($res === TRUE) {
        $zip->extractTo(self::$_pluginsPath.$pluginName);
        $zip->close();
        unlink($archiveFile);
        return true;
      }
    }
    return false;
  }
  
  /*
   * Преобразует объект stdClass в обычный массив - array
   * @param stdClass $std - объект для преобразования
   * @return array
   */
  private static function stdToArray($std){
    $array = (array)$std;
    
    foreach($array as $key=>&$field){
      if(is_object($field)){
        $field = self::stdToArray($field);
      }
    }
    
    return $array;
  }
  
  public static function getPluginDir($pluginName){
    $data['last_version'] = false;
    
    $pluginsUpdateInfo = unserialize(stripslashes(MG::getSetting('pluginsVersionInfo')));
    $pluginInfo = $pluginsUpdateInfo[$pluginName];
    $post = 'update=y&plugin='.$pluginName;
    $curlRes = self::sendCurl(self::$_updateServer.'/updateplugin', $post);
    
    $data = (array)json_decode($curlRes);
    
    $data['version'] = array_pop($pluginInfo['versions']);
    
    if(empty($pluginInfo['versions'])){
      $data['last_version'] = true;
      unset($pluginsUpdateInfo[$pluginName]);
    }else{
      $pluginsUpdateInfo[$pluginName] = $pluginInfo;
    }
    
    MG::setOption('pluginsVersionInfo', addslashes(serialize($pluginsUpdateInfo)));
    
    return $data;
  }
  
  public static function failtureUpdate($plugin ,$version){
    $pluginsUpdateInfo = unserialize(stripslashes(MG::getSetting('pluginsVersionInfo')));
    array_push($pluginsUpdateInfo[$plugin]['versions'], $version);
    MG::setOption('pluginsVersionInfo', addslashes(serialize($pluginsUpdateInfo)));
  }

  /*
   * Проверяет наличие обновлений для плагинов и записывает результат в базу данных
   * @param array $plugins - массив вида: плагин=>версия
   * @return boolean
   */
  public static function checkPluginsUpdate($plugins = array()){
    
    if(empty($plugins)){
      foreach(self::$pluginsInfo as $plugin){
        if(preg_match('/\d(.\d(.\d)?)?/', $plugin['Version'], $version)){
          $plugins[$plugin['folderName']] = $version[0];
        }
      }
    }
    
    if(!empty($plugins)){
      $post = 'check=y&plugins='.serialize($plugins);
      $curlRes = self::sendCurl(self::$_updateServer.'/updateplugin', $post);
      
      if($curlRes){
        $curlData = self::stdToArray(json_decode($curlRes));
        MG::setOption('pluginsVersionInfo', addslashes(serialize($curlData)));
      }else{
        return false;
      }
    }
    
    return true;
  }
  
  /**
   * Удаление информации о плагине из БД.
   * @param string $pluginFolder имя плагина
   * @return boolean
   */
  public static function deletePlagin($pluginFolder) {
    if (DB::query('
        DELETE FROM `'.PREFIX.'plugins`
        WHERE folderName = '.DB::quote($pluginFolder))
      ) {
      return true;
    }
  }
  
  private static function updatePluginVersion($pluginName){
    $fileVersion = PLUGIN_DIR.$pluginName.'/version.php';
    $fileIndex = PLUGIN_DIR.$pluginName.'/index.php';

    $contentIndex = file_get_contents($fileIndex);
    $contentVersion = file_get_contents($fileVersion);
    
    preg_match('~/\*(.*?)\*/~is', $contentIndex, $pluginInfo);
    preg_match('~/\*(.*?)\*/~is', $contentVersion, $versionInfo);

    $contentIndex = str_replace($pluginInfo[1], $versionInfo[1], $contentIndex);
    file_put_contents($fileIndex, $contentIndex);
    
    unlink($fileVersion);
  }
  
  /*
   * Обновление плагина.
   * @param string $pluginFolder имя плагина
   * @return boolean
   */
  public static function updatePlugin($pluginName, $folder, $version){
    $pluginDirectory = PLUGIN_DIR.$pluginName.'/';
    $file = $pluginDirectory.$version.'.zip';
    
    if(!file_exists(SITE_DIR.$file)){
      $ch = curl_init(self::$_updateServer.'/updata/plugins/'.$pluginName.'/'.$folder.'/'.$version.'.zip');
      $fp = fopen($file, "w");

      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);

      curl_exec($ch);
      curl_close($ch);
      fclose($fp);
    }
    
    if(file_exists($file) && self::extractPluginZip($file, $pluginName)){
      
      if(file_exists($pluginDirectory.'/version.php')){
        self::updatePluginVersion($pluginName);
      }
      
      $fileUpdate = $pluginDirectory.'update.php';
      if(file_exists($fileUpdate)){
        require_once($fileUpdate);
        unlink($fileUpdate);
      }
    }else{
      return false;
    }
    
    return true;
  }
  
}