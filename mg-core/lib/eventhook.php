<?php

/**
 * Класс EventHook - вешает обработчик для заданного хука.
 * Доступен из любой точки программы.
 * Реализован в виде синглтона, что исключает его дублирование.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class EventHook implements Hook {

  /**
   * @var string наименование хука.
   */
  private $_hookName;

  /**
   * @var string пользовательская функция, которая сработает при хуке.
   */
  private $_functionName;

  /**
   * @var string количество агрументов которое ждет пользовательская функция.
   */
  private $_countArg;

  /**
   * @var string приоритет выполнения.
   */
  private $_priority;

  /**
   * @var string класс в котором находится пользовательская функция
   */
  private $_class;

  public function __construct($hookName, $functionName, $countArg = 0, $priority = 10) {
    
    //Если имя хука является путем, то названием считается последняя директория в пути.
    //Необходимо для валидной работы страницы настроек плагина.
    $section = explode(DIRECTORY_SEPARATOR, dirname($hookName));

    $hookName = count($section) > 1 ? end($section) : $hookName;

    $this->_hookName = strtolower($hookName);

    // если функция передана в массиве, вместе с указанием ее класса
    if (is_array($functionName)) {
      $this->_class = $functionName[0];
      $functionName = $functionName[1];
    }

    $this->_functionName = $functionName;
    $this->_countArg = $countArg;
    $this->_priority = $priority;
  }

  /**
   * Запускает обработчик для хука.
   * @param type $arg массив параметров.
   * @return type результат работы пользовательской функции.
   */
  public function run($arg) {

    if (function_exists($this->_functionName) && empty($this->_class)) {
      // Если  хук передал параметры, то передать их в пользовательскую функцию.
      if (empty($arg)) {
        return call_user_func($this->_functionName);
      } else {
        $args[0] = $arg;
        return call_user_func_array($this->_functionName, $args);
      }
    } else {
      // если пользовательская функция задана в классе
      if ($this->_class && class_exists($this->_class)) {
        if (empty($arg)) {
          return call_user_func(array($this->_class, $this->_functionName));
        } else {
          $args[0] = $arg;
          return call_user_func_array(array($this->_class, $this->_functionName), $args);
        }
      }
    }
  }

  /**
   * Возвращает название хука.
   */
  public function getHookName() {
    return $this->_hookName;
  }

  /**
   * Возвращает количество агрументов которое ожидает пользовательская функция.
   */
  public function getCountArg() {
    return $this->_countArg;
  }

  /**
   * Возвращает приоритет пользовательской функций.
   */
  public function getPriority() {
    return $this->_priority;
  }

}
