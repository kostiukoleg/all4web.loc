<?php

/**
 * Контроллер Feedback
 *
 * Класс Controllers_Feedback обрабатывает действия пользователей на странице обратной связи.
 * - Проверяет корректность ввода данных с формы обратной связи;
 * - При успешной валидации данных, отправляет сообщение админам интернет магазина, и выводит сообщение об успешной отправке.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Feedback extends BaseController{

  function __construct(){

    $html = MG::get('pages')->getPageByUrl('feedback');    
    $html['html_content'] = MG::inlineEditor(PREFIX.'page',"html_content", $html['id'], $html['html_content']);
    
    $data = array(
      'dislpayForm' => true,
      'meta_title' => $html['meta_title']?$html['meta_title']:$html['title'],
      'meta_keywords' => $html['meta_keywords'],
      'meta_desc' => $html['meta_desc'],
      'html_content' => $html['html_content'], 
      'title' => $html['title'],
    );
    
    
    // Если пришли данные с формы.
    if(isset($_POST['send'])){

      // Создает модель отправки сообщения.
      $feedBack = new Models_Feedback;

      // Проверяет на корректность вода.
      $error = $feedBack->isValidData($_POST);
      $data['error'] = $error;

      // Если есть ошибки заносит их в переменную.
      if(!$error){
        $_POST['message'] = nl2br($_POST['message']);     
        //Отправляем админам.
        $sitename = MG::getSetting('sitename');       
        //$message = str_replace('№', '#', $feedBack->getMessage());        
          $body = MG::layoutManager('email_feedback', array('msg'=>$_POST['message'], 'email'=>$feedBack->getEmail(), 'name'=>$feedBack->getFio()));
     
        $mails = explode(',', MG::getSetting('adminEmail'));    
        foreach($mails as $mail){
          if(preg_match('/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]+\.)+[a-zA-Z]{2,6}$/', $mail)){
            Mailer::addHeaders(array("Reply-to" => $feedBack->getEmail()));
            Mailer::sendMimeMail(array(
              'nameFrom' => $feedBack->getFio(),
              'emailFrom' => $feedBack->getEmail(),
              'nameTo' => $sitename,
              'emailTo' => $mail,
              'subject' => 'Сообщение с формы обратной связи',
              'body' => $body,
              'html' => true
            ));
          }
        }

        MG::redirect('/feedback?thanks=1');
      }
    }

    // Формирует сообщение.
    if(isset($_REQUEST['thanks'])){
      $data = array(
        'message' => 'Ваше сообщение отправлено!',
        'dislpayForm' => false,
        'meta_title' => 'Обратная связь',
        'meta_keywords' => $model->currentCategory['meta_keywords'] ? $model->currentCategory['meta_keywords'] : "Обратная сввязь, быстрое сообщение, вопрос в поддержку",
        'meta_desc' => $model->currentCategory['meta_desc'] ? $model->currentCategory['meta_desc'] : "Задайте свой вопрос по средствам формы обратной связи.",
    );
    }

    $this->data = $data;
  }

}