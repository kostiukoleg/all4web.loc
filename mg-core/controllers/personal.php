<?php

/**
 * Контроллер: Personal
 *
 * Класс Controllers_Personal обрабатывает действия пользователей на странице личного кабинета.
 * - подготавливает данных пользователя для их отоббражения;
 * - обрабатывает запрос на изменения пароля;
 * - обрабатывает запрос на изменения способа оплаты;
 * - обрабатывает запрос на изменение данных пользователя.
 * 
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Personal extends BaseController {

  function __construct() {
    $lang = MG::get('lang');
    $settings = MG::get('settings');
    $this->lang = $lang;
    $status = 0;
    if (User::isAuth()) {
      $order = new Models_Order;
      $status = 3;

      //обработка запроса на изменение данных пользователя
      if (URL::getQueryParametr('userData')) {
        $customer = URL::getQueryParametr('customer');
        $birthday = URL::getQueryParametr('birthday');
        if ($birthday) {
          $birthday = date('Y-m-d', strtotime(URL::getQueryParametr('birthday')));  
        }
        $userData = array(
          'name' => URL::getQueryParametr('name'),
          'sname' => URL::getQueryParametr('sname'),
          'birthday' => $birthday,
          'address' => URL::getQueryParametr('address'),
          'phone' => URL::getQueryParametr('phone'),
          'nameyur' => $customer == 'yur' ? URL::getQueryParametr('nameyur') : '',
          'adress' => $customer == 'yur' ? URL::getQueryParametr('adress') : '',
          'inn' => $customer == 'yur' ? URL::getQueryParametr('inn') : '',
          'kpp' => $customer == 'yur' ? URL::getQueryParametr('kpp') : '',
          'bank' => $customer == 'yur' ? URL::getQueryParametr('bank') : '',
          'bik' => $customer == 'yur' ? URL::getQueryParametr('bik') : '',
          'ks' => $customer == 'yur' ? URL::getQueryParametr('ks') : '',
          'rs' => $customer == 'yur' ? URL::getQueryParametr('rs') : '',
        );
        
       
        if (USER::update(User::getThis()->id, $userData)) {
          $message = 'Данные успешно сохранены';
        } else {
          $error = 'Не удалось сохранить данные '.$this->_newUserData['sname'];
        }
      }

      // Обработка запроса на изменения пароля.
      if (URL::getQueryParametr('chengePass')) {
        if (USER::auth(User::getThis()->email, URL::getQueryParametr('pass'))) {
          $person = new Models_Personal;
          $message = $person->changePass(URL::getQueryParametr('newPass'), User::getThis()->id);
        } else {
          $error = 'Неверный пароль';
        }
      }

      // Обработка запроса на изменения способа оплаты.
      if (URL::getQueryParametr('changePaymentId')) {
        $status = $order->updateOrder(array('payment_id' => $_POST['changePaymentId'], 'id' => $_POST['orderId']));
        $result = array(
          'status' => $status,
          'comment' => 2,
          'orderStatus' => 3
        );

        echo json_encode($result);
        MG::disableTemplate();
        exit;
      }

      // Обработка AJAX запроса на закрытие заказа.
      if (URL::getQueryParametr('delOK')) {
        $comment = 'Отменено покупателем '.date('d.m.Y H:i').', по причине <br>"'.URL::getQueryParametr('comment').'"' ;
        // Пересчитываем остатки продуктов из заказа.
        $order->refreshCountProducts(URL::getQueryParametr('delID'), 4);

        $res = DB::query('
          UPDATE `'.PREFIX.'order`
          SET close_date = now(), status_id = 4, comment = '.DB::quote($comment).'
          WHERE id = '.DB::quote(URL::getQueryParametr('delID')).' AND user_email ='.DB::quote(User::getThis()->email));

        if ($res) {
          $status = true;
        }

        if ($comment) {
          $comm = "<b>Комментарий: ".$comment."</b>";
        }

        $result = array(
          'status' => $status,
          'comment' => $comm,
          'orderStatus' => $lang[$order->getOrderStatus(array('status_id' => 4))]
        );

        $order->sendMailOfUpdateOrder(URL::getQueryParametr('delID'));

        echo json_encode($result);
        MG::disableTemplate();
        exit;
      }
      
      // Отображение данных пользователя.
      $orderArray = $order->getOrder('user_email = "'.User::getThis()->email.'"');
      if (is_array($orderArray)) {

        foreach ($orderArray as $orderId => $orderItems) {
          $orderArray[$orderId]['string_status_id'] = $order->getOrderStatus($orderItems);
          $paymentArray = $order->getPaymentMethod($orderItems['payment_id']);
          $orderArray[$orderId]['name'] = $paymentArray['name'];
          $orderArray[$orderId]['paided'] = $order->getPaidedStatus($orderItems);
        }
      }

      if (!User::getThis()->activity) {
        $status = 2;
        unset($_SESSION['user']);
      }

      if (User::getThis()->blocked) {
        $status = 1;
        unset($_SESSION['user']);
      }
      $paymentListTemp = $order->getPaymentBlocksMethod();
      $paymentList[] = array();
      foreach ($paymentListTemp as $item) {
        if ($item['activity'] != '0') {
          $paymentList[$item['id']] = $item;
        }
      }
    }

    $this->data = array(
      'error' => !empty($error) ? $error : '', // Сообщение об ошибке.
      'message' => !empty($message) ? $message : '', // Информационное сообщение.
      'status' => !empty($status) ? $status : '', // Статус пользователя.
      'userInfo' => User::getThis(), // Информация о пользователе.
      'orderInfo' => !empty($orderArray) ? $orderArray : '', // Информация о заказе.
      'currency' => $settings['currency'],
      'paymentList' => $paymentList,
      'meta_title' => 'Личный кабинет',
      'meta_keywords' => !empty($model->currentCategory['meta_keywords']) ? $model->currentCategory['meta_keywords'] : "заказы,личные данные, личный кабинет",
      'meta_desc' => !empty($model->currentCategory['meta_desc']) ? $model->currentCategory['meta_desc'] : "В личном кабинете нашего сайта вы сможете отслеживать состояние заказов и менять свои данные",
    );
  }

}
