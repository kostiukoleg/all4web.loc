<?php

/**
 * Модель: Personal
 *
 * Класс Models_Personal реализует логику взаимодействия с личным кабинетом пользователя.
 *
 * @package moguta.cms
 * @subpackage Model
 */
class Models_Personal {

  /**
   * Функция смены пароля пользователя
   *
   * После проверки корректности введеных данных производит хэширование и внесения в БД пароля пользователя
   *
   * @param string $newPass - новый пароль пользователя
   * @param int $id - id пользователя
   * @param bool $forgotPass - флаг для функции восстановления пароля, когда не происходит изменения данных пользователя находящихся в системе
   * @return string - сообщение о результате операции
   */
  public function changePass($newPass, $id, $forgotPass = false) {
    $userData = array(
      'pass' => $newPass,
    );
    $registration = new Models_Registration;

    if ($err = $registration->validDataForm($userData, 'pass')) {
      $msg = $err;
    } else {
      $userData['pass'] = crypt($userData['pass']);
      USER::update($id, $userData, $forgotPass);
      $msg = "Пароль изменен";
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $msg, $args);
  }

}