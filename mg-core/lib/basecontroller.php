<?php
/**
 * Класс BaseController - любой контролер будет наследоваться от этого
 * базового класса,с целью переноса области видимости переменных из
 * класса контролера в представление.
 * Переменные заданные в контролере такие как $this->data, будут доступны в 
 * представлении как $data.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class BaseController{

  private $variables;
  /**
   * Магический метод устанавливает ключ и значение передаваемых параметров
   * в массив данных контроллера.
   *
   * @param string $name ключ.
   * @param string $val значение.
   * @return void
   */
  function __set($name, $val){
    $this->variables[$name] = $val;
  }

  /**
   * Магический метод возвращает массив параметров контроллера.
   *
   * @param string $name ключ.
   * @return array
   */
  function __get($name){
    return $this->variables;
  }

}