<?php

/**
 * Контроллер: Enter
 * 
 * Класс Controllers_Enter обрабатывает действия пользователей на странице авторизации.
 * - Аутентифицирует пользовательские данные;
 * - Проверяет корректность ввода данных с формы авторизации;
 * - При успешной авторизации перенаправляет пользователя в личный кабинет;
 * - При необходимых настройках включает защиту от подбора паролей;
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Enter extends BaseController {

  function __construct() {

    // Разлогиниваем пользователя.
    if (URL::getQueryParametr('logout')) {
      User::logout();
    }

    // Пользователь уже авторизован, отправляем его в личный кабинет.
    if (User::isAuth()) {
      MG::redirect('/personal');
    }

    $data = array(
      'meta_title' => 'Авторизация',
      'meta_keywords' => !empty($model->currentCategory['meta_keywords'])?$model->currentCategory['meta_keywords']:"Авторизация,вход, войти в личный кабинет",
      'meta_desc' => !empty($model->currentCategory['meta_desc'])?$model->currentCategory['meta_desc']:"Авторизуйтесь на сайте и вы получите дополнительные возможности, недоступные для обычных пользователей.",
    );

    // Если загрузка произведена по ссылке для отмены блокировки авторизации.
    if (URL::getQueryParametr('unlock')) {
      if (URL::getQueryParametr('unlock') == $_SESSION['unlockCode']) {
        unset($_SESSION['loginAttempt']);
        unset($_SESSION['blockTimeStart']);
        unset($_SESSION['unlockCode']);
      }
    }

    // Если пользователь не авторизован, проверяется  правильность ввода данных и количество неудачных попыток.
    if (!User::isAuth() && (isset($_POST['email']) || isset($_POST['pass']))) {

      $loginAttempt = (int) LOGIN_ATTEMPT?LOGIN_ATTEMPT:5;

      $capcha = (isset($_POST['capcha'])?$_POST['capcha']:false);
      unset($_POST['capcha']);

      if (!User::auth(URL::get('email'), URL::get('pass'), $capcha)) {
        if ($_SESSION['loginAttempt'] < 2) {
          $data['msgError'] = '<span class="msgError">'.
            'Неправильная пара email-пароль! Авторизоваться не удалось.'.'</span>';
        } elseif ($_SESSION['loginAttempt'] < $loginAttempt) {
          $data['msgError'] = '<span class="msgError">'.
            'Неправильно введен код с картинки! Авторизоваться не удалось.'.
            ' Количество оставшихся попыток - '.
            ($loginAttempt - $_SESSION['loginAttempt']).'</span>';
          $data['checkCapcha'] = '<div class="checkCapcha">
            <img style="margin-top: 5px; border: 1px solid gray; background: url("'.
            PATH_TEMPLATE.'/images/cap.png")" src = "captcha.html" width="140" height="36">
            <div>Введите текст с картинки:<span class="red-star">*</span> </div>
            <input type="text" name="capcha" class="captcha">';
        } else {
          if (!isset($_SESSION['blockTimeStart'])) {  
            // Начало отсчета  времени блокировки на 15 мин.
            $_SESSION['blockTimeStart'] = time();
            $_SESSION['unlockCode'] = md5('mg'.time());
            $this->sendUnlockMail($_SESSION['unlockCode']);
          }
          $data['msgError'] = '<span class="msgError">'.
            'В целях безопасности возможность авторизации '.
            'заблокирована на 15 мин. Отсчет времени от '.
            date("H:i:s", $_SESSION['blockTimeStart']).'</span>';
        }
      } else {
        $this->successfulLogon();
      }
    }

    $this->data = $data;
  }

  /**
   * Перенаправляет пользователя на страницу в личном кабинете.
   * @return void
   */
  public function successfulLogon() {

    // Если указан параметр для редиректа после успешной авторизации.
    if ($location = URL::getQueryParametr('location')) {
      MG::redirect($location);
    } else {
      
      // Иначе  перенаправляем в личный кабинет.
      MG::redirect('/personal');
    }
  }

  /**
   * Проверяет корректность ввода данных с формы авторизации.
   * @return void
   */
  public function validForm() {
    $email = URL::getQueryParametr('email');
    $pass = URL::getQueryParametr('pass');

    if (!$email || !$pass) {
      // При первом показе, не выводить ошибку.
      if (strpos($_SERVER['HTTP_REFERER'], '/enter')) {
        $this->data = array(
          'msgError' => '<span class="msgError">'.'Одно из обязательных полей не заполнено!'.'</span>',
          'meta_title' => 'Авторизация',
          'meta_keywords' => !empty($model->currentCategory['meta_keywords'])?$model->currentCategory['meta_keywords']:"Авторизация,вход, войти в личный кабинет",
          'meta_desc' => !empty($model->currentCategory['meta_desc'])?$model->currentCategory['meta_desc']:"Авторизуйтесь на сайте и вы получите дополнительные возможности, недоступные для обычных пользователей.",
        );
      }
      return false;
    }
    return true;
  }

  /**
   * Метод отправки письма администратору с сcылкой для отмены блокировки авторизации .
   * @param type $unlockCode
   * @return void 
   */
  private function sendUnlockMail($unlockCode) {
    $link = '<a href="'.SITE.'/enter?unlock='.$unlockCode.'" target="blank">'.SITE.'/enter?unlock='.$unlockCode.'</a>';
    $siteName = MG::getOption('sitename');
    
    $paramToMail = array(
      'siteName' => $siteName,
      'link' => $link,
    );
    
    $message = MG::layoutManager('email_unclockauth', $paramToMail);
    $emailData = array(
      'nameFrom' => $siteName,
      'emailFrom' => MG::getSetting('noReplyEmail'),
      'nameTo' => 'Администратору сайтй '.$siteName,
      'emailTo' => MG::getSetting('adminEmail'),
      'subject' => 'Подбор паролей на сайте '.$siteName.' предотвращен!',
      'body' => $message,
      'html' => true
    );
    
    if (Mailer::sendMimeMail($emailData)) {
      return true;
    }
    
    return false;
  }

}