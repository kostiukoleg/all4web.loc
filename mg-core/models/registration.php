<?php

/**
 * Модель: Registration
 *
 * Класс Models_Registration реализует логику регистрации новых пользователей.
 * - Проверяет корректность введенных данных в форме регистрации;
 * - Регистрирует нового пользователя, заносит данные в базу сайта;
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 */
class Models_Registration {

  /**
   * Проверяет корректность введенных данных в форме регистрации.
   * 
   * @param array $userData массив данных пользователя.
   * @param string $mode режим проверки данных (full|pass) полный (по умолчанию) или только пароль.
   * @return string ошибка в случае не верного ввода данных в одном из полей.
   */
  public function validDataForm($userData, $mode = 'full') {
  

    // Проверка электронного адреса.
    if (USER::getUserInfoByEmail($userData['email']) && 'full' == $mode) {
      $error .= '<span class="email-in-use">Указанный email уже используется</span>';
    }

    // Пароль должен быть больше 5-ти символов.
    if (strlen($userData['pass']) < 5) {
      $error .= '<span class="passError">Пароль менее 5 символов</span>';
    }
    // Проверяем равенство введенных паролей.
    if (URL::getQueryParametr('pass2') != $userData['pass']) {
      $error .= '<span class="wrong-pass">Введенные пароли не совпадают</span>';
    }

    if ('full' == $mode) {

      // Проверка электронного адреса. 
      if (!preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]{0,61}+\.)+[a-zA-Z]{2,6}$/', $userData['email'])) {
        $error .= '<span class="errorEmail">Неверно заполнено email</span>';
      }
      
      if(MG::getSetting('useCaptcha')=="true"){ 
        if (strtolower(URL::getQueryParametr('capcha')) != strtolower($_SESSION['capcha'])) {
          $error .= "<span class='error-captcha-text'>Текст с картинки введен неверно!</span>";
        }
      }
    }
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $error, $args);
  }

}