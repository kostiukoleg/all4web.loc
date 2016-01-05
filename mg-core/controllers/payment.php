<?php

class Controllers_Payment extends BaseController {

  public $msg = "";

  function __construct() {
    $this->msg = "";
    $paymentID = URL::getQueryParametr('id');
    $paymentStatus = URL::getQueryParametr('pay');
    $_POST['url'] = URL::getUrl();
    $modelOrder = new Models_Order();

    switch ($paymentID) {
      case 1: //webmoney
        $msg = $this->webmoney($paymentID, $paymentStatus);
        break;
      case 5: //robokassa
        $msg = $this->robokassa($paymentID, $paymentStatus);
        break;
      case 6: //qiwi
        $msg = $this->qiwi($paymentID, $paymentStatus);
        break;
      case 8: //interkassa
        $msg = $this->interkassa($paymentID, $paymentStatus);
        break;
      case 2: //ЯндексДеньги	  
        $msg = $this->yandex($paymentID, $paymentStatus);
        break;
      case 9: //PayAnyWay
        $msg = $this->payanyway($paymentID, $paymentStatus);

      case 10: //PayMastert
        $msg = $this->paymaster($paymentID, $paymentStatus);
        break;

      case 11: //alfabank
        $msg = $this->alfabank($paymentID, $paymentStatus);
        break;
      case 14: //Яндекс.Касса
        $msg = $this->yandexKassa($paymentID, $paymentStatus);
        break;
      case 15: //privat24
        $msg = $this->privat24($paymentID, $paymentStatus);
        break;
    }

    $this->data = array(
      'payment' => $paymentID, //id способа оплаты
      'status' => $paymentStatus, //статус ответа платежной системы (result, success, fail)
      'message' => $msg, //статус ответа платежной системы (result, success, fail)
    );
  }

  /**
   * Действие при оплате заказа
   * Обновляет статус заказа на Оплачен, отправляет письма оповещения, генерирует хук.
   */
  public function actionWhenPayment($args) {
    $result = true;
    ob_start();

    $order = new Models_Order();
    if (method_exists($order, 'updateOrder')) {
      $order->updateOrder(array('id' => $args['paymentOrderId'], 'status_id' => 2));
    }
    if (method_exists($order, 'sendMailOfPayed')) {
      $order->sendMailOfPayed($args['paymentOrderId'], $args['paymentAmount'], $args['paymentID']);
    }
    if (method_exists($order, 'sendLinkForElectro')) {
      $order->sendLinkForElectro($args['paymentOrderId']);
    }

    $content = ob_get_contents();
    ob_end_clean();

    // если в ходе работы метода допущен вывод контента, то записать в лог ошибку.
    if (!empty($content)) {
      MG::loger('ERROR PAYMENT: ' . $content);
    }

    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }

  /**
   * проверка платежа через WebMoney
   */
  public function webmoney($paymentID, $paymentStatus) {  
    $msg = 'Оплата не удалась. Ограничение версии.';
    return $msg;
  }

  /**
   * проверка платежа через paymaster
   */
  public function paymaster($paymentID, $paymentStatus) {
    $msg = 'Оплата не удалась. Ограничение версии.';
    return $msg;
  }

  /**
   * проверка платежа через ROBOKASSA
   */
  public function robokassa($paymentID, $paymentStatus) {
    $msg = 'Оплата не удалась. Ограничение версии.';
    return $msg;
  }

  /**
   * проверка платежа через QIWI
   */
  public function qiwi($paymentID, $paymentStatus) {
    $msg = 'Оплата не удалась. Ограничение версии.';
    return $msg;
  }

  /**
   * проверка платежа через Interkassa
   */
  public function interkassa($paymentID, $paymentStatus) {
    $msg = 'Оплата не удалась. Ограничение версии.';
    return $msg;
  }

  /**
   * проверка платежа через Interkassa
   */
  public function payanyway($paymentID, $paymentStatus) {
    $msg = 'Оплата не удалась. Ограничение версии.';
    return $msg;
  }

  /**
   * проверка платежа через Yandex
   */
  public function yandex($paymentID, $paymentStatus) {
    $order = new Models_Order();
    if ('success' == $paymentStatus) {      
      $orderInfo = $order->getOrder(" id = " . DB::quote($_POST['label'], 1));
      $msg = 'Вы успешно оплатили заказ №' . $orderInfo[$_POST['label']]['number'];
      $msg .= $this->msg;
    } elseif ('result' == $paymentStatus && isset($_POST)) {     
      $paymentAmount = trim($_POST['withdraw_amount']);
      $paymentOrderId = trim($_POST['label']);
      if (!empty($paymentAmount) && !empty($paymentOrderId)) {
        $orderInfo = $order->getOrder(" id = " . DB::quote($paymentOrderId, 1) . " and summ+delivery_cost = " . DB::quote($paymentAmount, 1));
        $paymentInfo = $order->getParamArray($paymentID, $paymentOrderId, $orderInfo[$paymentOrderId]['summ']);
      }
      // предварительная проверка платежа
      if (empty($orderInfo)) {
        echo "ERR: НЕКОРРЕКТНЫЕ ДАННЫЕ ЗАКАЗА";
        exit;
      }

      $secret = $paymentInfo[1]['value'];
      $alg = $paymentInfo[3]['value'];
      $pre_sha = $_POST['notification_type'] . '&' .
        $_POST['operation_id'] . '&' .
        $_POST['amount'] . '&' .
        $_POST['currency'] . '&' .
        $_POST['datetime'] . '&' .
        $_POST['sender'] . '&' .
        $_POST['codepro'] . '&' .
        $secret . '&' .
        $_POST['label'];

      $sha = hash($alg,$pre_sha);
      if ($sha == $_POST['sha1_hash']) {
        $this->actionWhenPayment(
          array(
            'paymentOrderId' => $paymentOrderId,
            'paymentAmount' => $orderInfo[$paymentOrderId]['summ'],
            'paymentID' => $paymentID
          )
        );
        echo "0";
        exit;
      } else {
        echo "1";
        exit;
      }
    }
    return $msg;
  }
  
  /*
   * проверка платежа через Яндекс.Кассу
   */
  public function yandexKassa($paymentID, $paymentStatus){
    $msg = 'Оплата не удалась. Ограничение версии.'; 
    return $msg;
  }
  
  /**
   * проверка платежа через AlfaBank
   */
  public function alfabank($paymentID, $paymentStatus) {
    $msg = 'Оплата не удалась. Ограничение версии.';
    return $msg;
  }
  
  /*
   * Проверка платежа через privat24
   */
  public function privat24($paymentID, $paymentStatus){
    $msg = 'Оплата не удалась. Ограничение версии.';
    return $msg;
  }

}
