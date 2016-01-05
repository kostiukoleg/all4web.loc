<?php

/**
 * Контроллер Forgotpass
 *
 * Класс Controllers_Forgotpass выполняет последовательность операций по восстановлению пароля пользователя.
 *
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Forgotpass extends BaseController {

  function __construct() {

    if (User::isAuth()) {
      MG::redirect('/');
    }

    // Шаг первый.
    $form = 1;
    $fPass = new Models_Forgotpass;

    // Ввторой шаг, производящий проверку введеного электронного адреса.
    if (URL::getQueryParametr('forgotpass')) {
      $email = URL::getQueryParametr('email');

      if ($userInfo = USER::getUserInfoByEmail($email)) {
        //Если введенных адрес совпадает с зарегистрированным в системе, то
        $form = 0;
        $message = 'Инструкция по восстановлению пароля была отправлена на <strong>'.$email.'</strong>';
        $hash = $fPass->getHash($email);
        //а) Случайный хэш заносится в БД.
        $fPass->sendHashToDB($email, $hash);
        $siteName = MG::getOption('sitename');
        
        
        $emailMessage = MG::layoutManager('email_forgot',
          array(          
            'siteName'=>$siteName,
            'email'=>$email,
            'hash'=> $hash,
            'userId'=> $userInfo->id,
            'link' => SITE.'/forgotpass?sec='.$hash.'&id='.$userInfo->id,
          )
        );   
        
        $emailData = array(
          'nameFrom' => $siteName,
          'emailFrom' => MG::getSetting('noReplyEmail'),
          'nameTo' => 'Пользователю сайта '.$siteName,
          'emailTo' => $email,
          'subject' => 'Восстановление пароля на сайте '.$siteName,
          'body' => $emailMessage,
          'html' => true
        );
        //б) На указанный электронный адрес отправляется письмо со сылкой на страницу восстановления пароля.
        $fPass->sendUrlToEmail($emailData);
      } else {
        $form = 0;
        $error = 'К сожалению, такой логин не найден<br>
          Если вы уверены, что данный логин существует, пожалуйста, свяжитесь с нами.';
      }
    }
    // Шаг 3. Обработка перехода по ссылки. Принимается id пользователя и сгенерированный хэш.
    if ($_GET) {
      $userInfo = USER::getUserById(URL::getQueryParametr('id'));
      $hash = URL::getQueryParametr('sec');
      // Если присланный хэш совпадает с хэшом из БД для соответствующего id.
      if ($userInfo->restore == $hash) {
        $form = 2;
        // Меняе в БД случайным образом хэш, делая невозможным повторный переход по ссылки.
        $fPass->sendHashToDB($userInfo->email, $fPass->getHash('0'));
        $_SESSION['id'] = URL::getQueryParametr('id');
      } else {
        $form = 0;
        $error = 'Некорректная ссылка. Повторите заново запрос восстановления пароля.';
      }
    }

    // Шаг 4. обрабатываем запрос на ввод нового пароля
    if (URL::getQueryParametr('chengePass')) {
      $form = 2;
      $person = new Models_Personal;
      $msg = $person->changePass(URL::getQueryParametr('newPass'), $_SESSION['id'], true);
      if ('Пароль изменен' == $msg) {
        $form = 0;
        $message = $msg.'! '.'Вы можете войти в личный кабинет по адресу <a href="'.SITE.'/enter" >'.SITE.'/enter</a>';
        $fPass->activateUser($_SESSION['id']);
        unset($_SESSION['id']);
      } else {
        $error = $msg;
      }
    }

    $this->data = array(
      'error' => $error, // Сообщение об ошибке.
      'message' => $message, // Информационное сообщение.
      'form' => $form, // Отображение формы.
      'meta_title' => 'Восстановление пароля',
      'meta_keywords' => $model->currentCategory['meta_keywords'] ? $model->currentCategory['meta_keywords'] : "забыли пароль, восстановить пароль, восстановление пароля",
      'meta_desc' => $model->currentCategory['meta_desc'] ? $model->currentCategory['meta_desc'] : "Если вы забыли пароль от личного кабинета, его модно восстановить с помощью формы восстановления паролей.",
    );
  }

}