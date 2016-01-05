<?php

/**
 * Класс DB - предназначен для работы с базой данных.
 * Доступен из любой точки программы.
 * Реализован в виде синглтона, что исключает его дублирование.
 * Все запросы выполняемые в коде движка должны обязательно проходить через метод DB::query() данного класса, а параметры запроса  экранироваться методом DB::quote();
 * - Создает соединение с БД средствами mysqli;
 * - Защищает базу от SQL инъекций;
 * - Ведет логирование запросов если установленна данная опция;
 * 
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class DB {

  static private $_instance = null;
  static private $_debugMode = DEBUG_SQL;
  static private $log = null;
  static private $lastQuery = null;
  static public $connection = null;

  private function __construct() {
  
	$hostAndPort = explode(':',HOST);
	$port =  null;
	$host = HOST;
	if(!empty($hostAndPort[1])){
	  $port = $hostAndPort[1];
	  $host = $hostAndPort[0];
	}
		
    self::$connection = new mysqli($host, USER, PASSWORD, NAME_BD, $port);
		
    if (self::$connection->connect_error) {
      die('Ошибка подключения ('.self::$connection->connect_errno.') '
        .self::$connection->connect_error);
    }
  }

  private function __clone() {
    
  }

  private function __wakeup() {
    
  }

  /**
   * Строит часть запроса, из полученного ассоциаливного массива.
   * Обычно используется для оператора SET.
   * Пример:
   * <code>   
   * $array = (
   *   'login' => 'admin',
   *   'pass' => '1',
   * );
   * // преобразует массив в строку: "'login' = 'admin', 'pass' = '1'"
   * DB::buildPartQuery($array); 
   * </code>  
   * @param array $array ассоциативный массив полей с данными.
   * @param string $devide разделитель.
   *
   * @return string
   */
  public static function buildPartQuery($array, $devide = ',') {
    $partQuery = '';

    if (is_array($array)) {
      $partQuery = '';
      foreach ($array as $index => $value) {     
        $partQuery .= ' `'.self::quote($index,true).'` = "'.self::quote($value,true).'"'.$devide;
      }
      $partQuery = trim($partQuery, $devide);     
    }
    return $partQuery;
  }

  /**
   * Аналогичен методу buildPartQuery, но используется для целого запроса.
   * Как правило для WHERE.
   *
   * @param string SQL запрос.
   * @param array $array ассоциативный массив.
   * @param string $devide разделитель
   * @return obj|bool
   */
  public static function buildQuery($query, $array, $devide = ',') {

    if (is_array($array)) {
      $partQuery = '';

      foreach ($array as $index => $value) {        
        $partQuery .= ' `'.self::quote($index,true).'` = "'.self::quote($value,true).'"'.$devide;
      }

      $partQuery = trim($partQuery, $devide);
      $query .= $partQuery;

      return self::query($query);
    }
    return false;
  }

  /**
   * Возвращает запись в виде массива.
   * @param obj $object
   * @return array
   */
  public static function fetchArray($object) {
    return @mysqli_fetch_array($object);
  }

  /**
   * Возвращает ряд результата запроса в качестве ассоциативного массива.
   * @param obj $object
   * @return array
   */
  public static function fetchAssoc($object) {
    return @mysqli_fetch_assoc($object);
  }

  /**
   * Возвращает запись в виде объекта.
   * @param obj $object
   * @return obj
   */
  public static function fetchObject($object) {
    return @mysqli_fetch_object($object);
  }

  /**
   * Возвращет единственный экземпляр данного класса.
   * @return object - объект класса DB
   */
  static public function getInstance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * Инициализирует единственный объект данного класса, устанавливает кодировку БД utf8.
   * @return object - объект класса DB
   */
  public static function init() {
    self::getInstance();
    DB::query('SET names utf8');
    if (SQL_BIG_SELECTS) {
      DB::query('SET SQL_BIG_SELECTS = 1');
    }
  }

  /**
   * Возвращает сгенерированный колонкой с AUTO_INCREMENT
   * последним запросом INSERT к серверу.
   * @return int
   */
  public static function insertId() {
    return @mysqli_insert_id(self::$connection);
  }

  /**
   * Возвращает количество рядов результата запроса.
   * @param obj $object
   * @return int
   */
  public static function numRows($object) {
    return @mysqli_num_rows($object);
  }

  /**
   * Выполняет запрос к БД.
   *
   * @param srting $sql запрос.( Может содержать дополнительные аргументы.)
   * @return obj|bool
   */
  public static function query($sql) {

    if (($num_args = func_num_args()) > 1) {
      $arg = func_get_args();
      unset($arg[0]);

      // Экранируем кавычки для всех входных параметров.
      foreach ($arg as $argument => $value) {
        $arg[$argument] = mysqli_real_escape_string(self::$connection, $value);
      }
      $sql = vsprintf($sql, $arg);
    }
    $obj = self::$_instance;

    if (isset(self::$connection)) {
      $obj->count_sql++;

      $startTimeSql = microtime(true);
      $result = mysqli_query(self::$connection, $sql) 
        or die(self::console('<br/><span style="color:red">Ошибка в SQL запросе: '
          . '</span><span style="color:blue">'.$sql.'</span> <br/> '
          . '<span style="color:red">'.mysqli_error(self::$connection).'</span>'));

      $timeSql = microtime(true) - $startTimeSql;
      $obj->timeout += $timeSql;
      self::$lastQuery = $sql;
      if (self::$_debugMode) {    
        self::$log .= "<p style='margin:5px; font-size:10px;'><span style='color:blue'> <span style='color:green'># Запрос номер ".$obj->count_sql.": </span>".$sql."</span> <span style='color:green'>(".round($timeSql, 4)." msec )</span>";
        $stack = debug_backtrace();
        self::$log .= " <span style='color:#c71585'>".$stack[0]['file'].' (line '.$stack[0]['line'].")</span></p>";
      }

      return $result;
    }
    return false;
  }

  /**
   * Экранирует кавычки для части запроса.
   *
   * @param srting $noQuote - если true, то не будет выводить кавычки вокруг строки.
   * @param srting $string часть запроса.
   */
  public static function quote($string, $noQuote = false) {
    return (!$noQuote) ? "'".mysqli_real_escape_string(self::$connection, $string)."'" : mysqli_real_escape_string(self::$connection, $string);
  }

  /**
   * Выводит консоль запросов и ошибок.
   * @param srting $text - данные лога.
   */
  public static function console($text = '') {

    $stack = debug_backtrace();
  
    unset($stack[0]);
    $obj = self::$_instance;
    $html = '<script>var consoleCount = $(".wrap-mg-console").length; if(consoleCount>1){$(".wrap-mg-console").hide();}</script>
      <div class="wrap-mg-console '.time().'" style="height: 200px; width:100%; position:fixed;z-index:66;bottom:0;left:0;right:0;background:#fff;">
      <div class="mg-bar-console" style="background:#dfdfdf; height: 30px; line-height: 30px; padding: 0 0 0 10px; width:100%; border-top: 2px solid #a3a3a3; border-bottom: 2px solid #a3a3a3;">
      Всего выполнено запросов: '.$obj->count_sql.' шт. за '.round($obj->timeout, 4).' сек.
      <a style="float:right; margin-right:30px;" href="javascript:void(0);" onclick=\'$(".wrap-mg-console").hide()\'>Закрыть</a>
      </div>
      <div class="mg-console" style="background:#f4f4f4; height: 200px; overflow:auto;">
      <script>$(".'.time().'").show();</script>     
      ';
    $logStack = '';
    foreach ($stack as $item) {
      $logStack .= '<p style="margin:5px; font-size:10px;"><br/><span style="color:#c71585">'.$item['file'].' (line '.$item['line'].")</span></p>";
    }
    $html.= self::$log.'<br/>'.$text.$logStack;
    $html.='</div>
    </div>';
    return $html;
  }

  /*
   * Выводит последний выполненный запрос.
   */
  public static function lastQuery() {
    return self::$lastQuery;
  }

}