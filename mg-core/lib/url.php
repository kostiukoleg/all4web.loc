<?php

/**
 * Класс URL - предназначен для работы со ссылками, а также с адресной строкой.
 * Доступен из любой точки программы.
 *
 * Реализован в виде синглтона, что исключает его дублирование.
 * Имеет в себе реестр queryParams для хранения любых объектов.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class URL {

  static private $_instance = null;
  static private $cutPath = '';
  static private $route = 'index';
  static public $documentRoot = '';

  /**
   * Исключает XSS уязвимости для вех пользовательских данных.
   * Сохраняет все переданные параметры в реестр queryParams,
   * в дальнейшем доступный из любой точки программы.
   * Выявляет часть пути в ссылках, по $_SERVER['SCRIPT_NAME'],
   * которая не должна учитываться при выборе контролера.
   * Актуально когда файлы движка лежат не в корне сайта.
   */
  private function __construct() {
    self::$documentRoot = str_replace(DIRECTORY_SEPARATOR.'mg-core'.DIRECTORY_SEPARATOR.'lib', '', dirname(__FILE__));
   
    self::$cutPath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    $route = self::getLastSection();
    $route = $route ? $route : 'index';
    if ((MG::getSetting('catalogIndex')=='true') && $route == 'index') {
      $route = 'catalog';
    }
    if ($route == 'mg-admin') {
      $route = 'mgadmin';
    }

    // Заполняем QUERY_STRING переменной route.
    $_SERVER['QUERY_STRING'] = 'route='.$route;
    $route = str_replace('.html', '', $route);

    // Конвертируем обращение к контролеру админки в подобающий вид.
    self::$route = $route;

    if (get_magic_quotes_gpc()) {
      $_REQUEST = MG::stripslashesArray($_REQUEST);
      $_POST = MG::stripslashesArray($_POST);
      $_GET = MG::stripslashesArray($_GET);
    }
    // Если данные пришли не из админки и не из плагинов а от пользователей то проверяим их на XSS.
    // Также исключение действует на просмотрщик страниц,
    // он защищен от стороннего использования в контролере, поэтому исключает опасность.
    if ((strpos($route, 'mgadmin') === false && strpos($route, 'ajax') === false && strpos($route, 'previewer') === false) || strpos($route, 'ajaxrequest') !== false) {
      $emulMgOff = false;
      if (get_magic_quotes_gpc()) {

        $emulMgOff = true;
      }

      $_REQUEST = MG::defenderXss($_REQUEST, $emulMgOff);
      $_POST = MG::defenderXss($_POST, $emulMgOff);
      $_GET = MG::defenderXss($_GET, $emulMgOff);
    }



    $this->queryParams = $_REQUEST;
  }

  private function __clone() {
    
  }

  private function __wakeup() {
    
  }

  /**
   * Конвертирует рускоязычны URL в транслит.
   * @param string $str рускоязычный url.
   * @return string|bool
   */
  public static function createUrl($urlstr) {
    $result = false;
    if (preg_match('/[^A-Za-z0-9_\-]/', $urlstr)) {
      $urlstr = translitIt($urlstr);
      $urlstr = preg_replace('/[^A-Za-z0-9_\-]/', '', $urlstr);
      $result = $urlstr;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращет защищенный параметр из массива $_GET.
   * @return object
   */
  public static function get($param) {
    return self::getQueryParametr($param);
  }

  /**
   * Вовзращает чистый URI, без строки с get параметрами.
   * @return type
   */
  public static function getClearUri() {
    $data = self::getDataUrl();
    //отрезаем только первую встретившуюся часть
    if (self::$cutPath) {
      $pos = strpos($data['path'], self::$cutPath);
      if ($pos !== false) {
        $res = substr_replace($data['path'], '', $pos, strlen(self::$cutPath));
      }
    } else {
      $res = $data['path'];
    }

    return $res;
  }

  /**
   * Чистит входящий URL и возвращает URI , аналогично методу  getClearUri() только для  заданного URL.
   * @return $url - входящая ссылка
   */
  public static function clearingUrl($url) {
    $data = parse_url($url);
    //отрезаем только первую встретившуюся часть
    if (self::$cutPath) {
      $pos = strpos($data['path'], self::$cutPath);
      if ($pos !== false) {
        $res = substr_replace($data['path'], '', $pos, strlen(self::$cutPath));
      }
    } else {
      $res = $data['path'];
    }

    return $res;
  }

  /**
   * Вовзращает часть пути, до папки с CMS.
   * Например если движок расположен по этому пути http://sitname.ru/shop/index.php,
   * то  метод вернет строку "/shop"
   * @return string
   */
  public static function getCutPath() {
    return self::$cutPath;
  }

  /**
   * Вовзращает количество секций.
   * @return type
   */
  public static function getCountSections() {
    $sections = self::getSections();
    return count($sections) - 1;
  }

  /**
   * Вовзращает массив составных частей ссылки.
   * @return type
   */
  public static function getDataUrl($url = false) {
    if (!$url) {
      $url = URL::getUrl();
    }
    return parse_url($url);
  }

  /**
   * Возвращет единственный экземпляр данного класса.
   * @return object - объект класса URL.
   */
  static public function getInstance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * Вовзращает последнюю часть uri.
   * @return type
   */
  public static function getLastSection() {
    $sections = self::getSections();
    $lastSections = end($sections);
    
    if(OLDSCOOL_LINK!='OLDSCOOL_LINK' && OLDSCOOL_LINK!='0'){
      $lastSections = str_replace('.html','',$lastSections);
    }

    return $lastSections;
  }

  /**
   * Вовзращает часть пути, до папки с CMS.
   * Например если движок расположен по этому пути http://sitname.ru/shop/index.php,
   * то  метод вернет строку "/shop"
   * @return string
   */
  public static function getCutSection() {
    return str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
  }

  /**
   * Вовзращает запрошенный request параметр.
   * @return type
   */
  public static function getQueryParametr($param) {
    $params = self::getInstance()->queryParams;
    $res = !empty($params[$param]) ? $params[$param] : null;
    return $res;
  }

  /**
   * Вовзращает запрошенную строку параметров.
   * @return string
   */
  public static function getQueryString() {
    return $_SERVER['QUERY_STRING'];
  }

  /**
   * Вовзращает массив секций URI.
   * @return string
   */
  public static function getSections($path = false) {
    
    if (!$path) {
      $uri = self::getClearUri();
    } else {
      $uri = $path;
    }
    
    if(OLDSCOOL_LINK!='OLDSCOOL_LINK' && OLDSCOOL_LINK!='0'){
      $uri = str_replace('.html','',$uri);
    }
    
    $sections = explode('/', rtrim($uri, '/'));
    return $sections;
  }

  /**
   * Вовзращает часть url являющуюся parent_url.
   * @return string
   */
  public static function parseParentUrl($path = false) {
    if (!$path) {
      $uri = self::getSections();
    } else {
      $uri = self::getSections($path);
    }
    unset($uri[count($uri) - 1]);
    $parentUrl = trim(implode('/', $uri), '/').'/';
    return $parentUrl;
  }

  /**
   * Вовзращает последнюю секцию URL.
   * @return string
   */
  public static function parsePageUrl($path = false) {
    if (!$path) {
      $uri = self::getSections();
    } else {
      $uri = self::getSections($path);
    }
    $pageUrl = $uri[count($uri) - 1];
    return $pageUrl;
  }

  /**
   * Вовзращает  URI, с get параметров.
   * @return type
   */
  public static function getUri() {
    return $_SERVER['REQUEST_URI'];
  }

  /**
   * Вовзращает ссылку с хостом и протоколом.
   * @return string
   */
  public static function getUrl() {
    return PROTOCOL.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
  }

  /**
   * Вовзращает имя для роутера.
   * @return string
   */
  public static function getRoute() {
    return self::$route;
  }

  /**
   * Инициализирует данный класс URL.
   * @return void
   */
  public static function init() {
    self::getInstance();
  }

  /**
   * Проверяет является ли полученное значение  - именем текущего раздела.
   * Пример:  isSection('catalog') вернет true если открыта страница каталога.
   * @param string $section название секции.
   * @return bool
   */
  public static function isSection($section) {
    $sections = self::getSections();
    return ($sections[1] == $section) ? true : false;
  }

  /**
   * Возвращает защищенный параметр из $_POST массива.
   * @return string
   * @param string $param запрошеный параметр.
   */
  public static function post($param) {
    return self::getQueryParametr($param);
  }

  /**
   * Устанавливает параметр в реестр URL. Можно использовать как реестр переменных.
   * @param string $param наименование параметра.
   * $value string $param значение параметра.
   */
  public static function setQueryParametr($param, $value) {
    self::getInstance()->queryParams[$param] = $value;
  }

  /**
   * Добавляет, либо заменяет гет параметр в строке URL. Обычно нужен для пейджера навигации.
   * @param type $url
   * @param type $param
   * @param type $pvalue
   * @return string
   */
  public static function add_get($url, $param, $pvalue = '') {
    $res = $url;
    if (($p = strpos($res, '?')) !== false) {
      $paramsstr = substr($res, $p + 1);
      $params = explode('&', $paramsstr);
      $paramsarr = array();
      foreach ($params as $value) {
        $tmp = explode('=', $value);
        if (isset($paramsarr[$tmp[0]])) {

          if (is_array($paramsarr[$tmp[0]])) {
            $paramsarr[$tmp[0]][] = (string) $tmp[1];
          } else {
            $temp = $paramsarr[$tmp[0]];
            unset($paramsarr[$tmp[0]]);
            $paramsarr[$tmp[0]][] = $temp;
            $paramsarr[$tmp[0]][] = (string) $tmp[1];
          }
        } else {
          $paramsarr[$tmp[0]] = (string) $tmp[1];
        }
      }
      $paramsarr[$param] = $pvalue;
      $res = substr($res, 0, $p + 1);

      foreach ($paramsarr as $key => $value) {
        if (is_array($value)) {
          foreach ($value as $item) {
            $str = $key;
            if ($item !== '') {
              $str .= '='.$item;
            }
            $res .= $str.'&';
          }
        } else {
          $str = $key;
          if ($value !== '') {
            $str .= '='.$value;
          }
          $res .= $str.'&';
        }
      }
      $res = substr($res, 0, -1);
    } else {
      $str = $param;
      if ($pvalue) {
        $str .= '='.$pvalue;
      }
      $res .= '?'.$str;
    }
    return $res;
  }

  /**
   * Удаляет из URL все запрещенные спецсимволы.
   * $str - строка для операции
   */
  public static function prepareUrl($str, $product = false) {
    $str = strtolower($str);
    $str = preg_replace('%\s%i', '-', $str);
    $str = str_replace('`', '', $str);
    $str = str_replace(array("\\","<",">"),"",$str);
    if($product){
      $pattern = '%[^/-a-zа-я\d]%i';
    }else{
      $pattern = '%[^/-a-zа-я\.\d]%i';
    }
    $str = preg_replace($pattern, '', $str);
    $str = substr($str, 0, 255);
    return $str;
  }
  
    
  /**
   * Вычисляет настоящее местоположение до файла на сервере
   */
  public function getDocumentRoot($lastSep=true) {     
    $documentroot = str_replace(DIRECTORY_SEPARATOR.'mg-core'.DIRECTORY_SEPARATOR.'lib','',dirname(__FILE__)); 
    $documentroot = $lastSep?$documentroot.DIRECTORY_SEPARATOR:$documentroot;
    
    return $documentroot;
  }

}