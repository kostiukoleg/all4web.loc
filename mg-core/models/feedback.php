<?php

/**
 * Модель: Feedback
 *
 * Класс Models_Feedback реализует логику взаимодействия с формой оратной связи.
 * - Проверяет корректность ввода данных;
 * - Отправляет сообщения на электронные адреса пользователя и администраторов.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 */
class Models_Feedback {

  // Электронный адрес пользователя.
  private $email;
  // Фамилия имя пользователя.
  private $fio;
  // Сообщение пользователя.
  private $message;

  /**
   * Проверяет корректность ввода данных.
   *
   * @param array $arrayData массив с данными введенными пользователем.
   * @return bool|string $error сообщение с ошибкой в случае некорректных данных.
   */
  public function isValidData($arrayData) {
   
    $result = false;
    if (!preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]{0,61}+\.)+[a-zA-Z]{2,6}$/', $arrayData['email'])) {
      $error = '<span class="error-email">E-mail не существует!</span>';
    } elseif (!trim($arrayData['message'])) {
      $error = 'Введите текст сообщения!';
    }

    if(MG::getSetting('useCaptcha')=="true"){
      if (strtolower($arrayData['capcha']) != strtolower($_SESSION['capcha'])) {
        $error .= "<span class='error-captcha-text'>Текст с картинки введен неверно!</span>";
      }    
    }

    // Если нет ощибок, то заносит информацию в поля класса.
    if ($error) {
      $result = $error;
    } else {

      $this->fio = trim($arrayData['fio']);
      $this->email = trim($arrayData['email']);
      $this->message = trim($arrayData['message']);
      $result = false;
    }

    $args = func_get_args();

    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
  /**
   * Получает сообщение из закрытых полей класса.
   * @return type
   */
  public function getMessage() {
    return $this->fio.": ".$this->message;
  }

   /**
   * Получает email из закрытых полей класса.
   * @return type
   */
  public function getEmail() {
    return $this->email;
  }

  /**
   * Получает имя отправителя из закрытых полей класса.
   * @return type
   */
  public function getFio() {
    return $this->fio;
  }

}