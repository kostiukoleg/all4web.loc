<?php

/**
 * Контроллер: Registration
 *
 * Класс Controllers_Registration обрабатывает действия пользователей на странице регистрации нового пользователя.
 * - Проверяет корректность данных;
 * - Регистрирует учетную запись пользователя.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Registration extends BaseController {

  private $error;
  private $userData;
  private $fPass;

  function __construct() {
    $this->fPass = new Models_Forgotpass;
    $form = true; // Отображение формы.
    //
    // Оброботка действий пользователя при регистрации.
    if (isset($_POST['registration'])) {

      // Если данные введены верно.
      if (!$this->unValidForm()) {
        USER::add($this->userData);
        $message = '<span class="succes-reg">Вы успешно зарегистрировались! Для активации пользователя Вам необходимо перейти по ссылке высланной на Ваш электронный адрес <strong>'.$this->userData['email'].'</strong></span>';
        $form = false;

        // Рассылаем письма со ссылкой для подтверждения регистрации.
        $this->_sendActivationMail($this->userData['email']);
      } else {
        $error = $this->error;
        $form = true;
      }
    }

    // Обработка действий перехода по ссылки.
    if (URL::getQueryParametr('id')) {
      $userInfo = USER::getUserById(URL::getQueryParametr('id'));
      $hash = URL::getQueryParametr('sec');

      // Если присланный хэш совпадает с хэшом из БД для соответствующего id.
      if ($userInfo->restore == $hash) {

        // Меняет в БД случейным образом хэш, делая невозможным повторный переход по ссылки.
        $this->fPass->sendHashToDB($userInfo->email, $this->fPass->getHash('0'));
        $message = 'Ваша учетная запись активирована. Теперь Вы можете <a href="'.SITE.'/enter">войти в личный кабинет</a> используя логин и пароль заданный при регистрации.';
        $form = false;
        $this->fPass->activateUser(URL::getQueryParametr('id'));
      } else {
        $error = 'Некорректная ссылка. Повторите активацию!';
        $form = false;
      }
    }

    // Обработка действий при запросе на повторную активацию.
    if ($_POST['reActivate']) {
      $email = URL::getQueryParametr('activateEmail');
      if (USER::getUserInfoByEmail($email)) {
        $this->_sendActivationMail($email);
        $message = 'Для активации пользователя Вам необходимо перейти по ссылке высланной на Ваш электронный адрес '.$this->userData['email'];
        $form = false;
      } else {
        $error = 'К сожалению, такой логин не найден. Если вы уверены, что данный логин существует, свяжитесь, пожалуйста, с нами.';
        $form = false;
      }
    }

    $this->data = array(
      'error' => $error, // Сообщение об ошибке.
      'message' => $message, // Информационное сообщение
      'form' => $form, // Отображение формы.
      'meta_title' => 'Регистрация',
      'meta_keywords' => !empty($model->currentCategory['meta_keywords']) ? $model->currentCategory['meta_keywords'] : "регистрация, зарегистрироваться",
      'meta_desc' => !empty($model->currentCategory['meta_desc']) ? $model->currentCategory['meta_desc'] : "Зарегистрируйтесь в системе, чтобы получить дополнительные возможности, такие как просмотр состояния заказа",
    );
  }

  /**
   * Метод проверяет корректность данных введенных в форму регистрации.
   * @return boolean
   */
  public function unValidForm() {

    if (!URL::getQueryParametr('name')) {
      $name = 'Пользователь';
    } else {
      $name = URL::getQueryParametr('name');
    }

    $this->userData = array(
      'pass' => URL::getQueryParametr('pass'),
      'email' => URL::getQueryParametr('email'),
      'role' => 2,
      'name' => $name,
      'sname' => URL::getQueryParametr('sname'),
      'address' => URL::getQueryParametr('address'),
      'phone' => URL::getQueryParametr('phone'),
      'ip' => URL::getQueryParametr('ip'),
    );

    $registration = new Models_Registration;

    if ($err = $registration->validDataForm($this->userData)) {
      $this->error = $err;
      return true;
    }

    return false;
  }

  /**
   * Метод отправки письма для активации пользователя.
   * @param type $userEmail
   * @return void 
   */
  private function _sendActivationMail($userEmail) {
    $userId = USER::getUserInfoByEmail($userEmail)->id;
    $hash = $this->fPass->getHash($userEmail);
    $this->fPass->sendHashToDB($userEmail, $hash);
    $siteName = MG::getOption('sitename');
    $link = '<a href="'.SITE.'/registration?sec='.$hash.'&id='.$userId.'" target="blank">'.SITE.'/registration?sec='.$hash.'&id='.$userId.'</a>';

    $paramToMail = array(
      'siteName' => $siteName,
      'userEmail' => $userEmail,
      'link' => $link,
    );

    $message = MG::layoutManager('email_registry', $paramToMail);
    $emailData = array(
      'nameFrom' => $siteName,
      'emailFrom' => MG::getSetting('noReplyEmail'),
      'nameTo' => 'Пользователю сайта '.$siteName,
      'emailTo' => $userEmail,
      'subject' => 'Активация пользователя на сайте '.$siteName,
      'body' => $message,
      'html' => true
    );

    $this->fPass->sendUrlToEmail($emailData);
  }

}
