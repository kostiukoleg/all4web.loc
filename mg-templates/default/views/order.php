<?php
/**
 *  Файл представления Index - выводит сгенерированную движком информацию на главной странице магазина.
 *  В этом файле доступны следующие данные:
 *   <code>     
 *    $data['active'] => состояние активации пользователя.
 *    $data['msg'] => сообщение.
 *    $data['step'] => стадия оформления заказа.
 *    $data['delivery'] => массив способов доставки.
 *    $data['paymentArray'] => массив способов оплаты.
 *    $data['paramArray'] => массив способов оплаты.
 *    $data['id'] => id заказа.
 *    $data['orderNumber'] => номер заказа.
 *    $data['summ'] => сумма заказа.
 *    $data['pay'] => оплата. 
 *    $data['payMentView'] => файл представления дляспособа оплаты.
 *    $data['currency'] => валюта магазина
 *    $data['userInfo'] => информация о пользователе,
 *    $data['orderInfo'] => информация о заказе,
 *    $data['meta_title'] => 'Значение meta тега для страницы order'
 *    $data['meta_keywords'] => 'Значение meta_keywords тега для страницы order'
 *    $data['meta_desc'] => 'Значение meta_desc тега для страницы order'
 *   </code>
 *   
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php viewData($data['saleProducts']); ?>  
 *   </code>
 * 
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php echo $data['saleProducts']; ?>  
 *   </code>
 * 
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложую программную логику логику.
 *   @author Авдеев Марк <mark-avdeev@mail.ru>
 *   @package moguta.cms
 *   @subpackage Views
 */
?>
<?php mgAddMeta('<script type="text/javascript" src="'.SCRIPT.'jquery.maskedinput.min.js"></script>'); ?>
<?php mgAddMeta('<script type="text/javascript" src="'.SCRIPT.'standard/js/order.js"></script>'); ?>
<?php mgAddMeta('<link href="'.SCRIPT.'standard/css/datepicker.css" rel="stylesheet" type="text/css">'); ?>
<?php if(!empty($data['fileToOrder'])){ ?>
  <h1 class="new-products-title"><?php echo $data['fileToOrder']['infoMsg'] ?></h1>
  <?php if(!empty($data['fileToOrder']['electroInfo'])){ ?>
    <ul>
    <?php foreach($data['fileToOrder']['electroInfo'] as $item){ ?>
        <li>Скачать: <a href="<?php echo $item['link'] ?>"><?php echo $item['title'] ?></a></li>
      <?php } ?>
    </ul>
      <?php
    }
  }
  else{
    switch($data['step']){
      case 1:
        mgSEO($data);
        ?>
      <h1 class="new-products-title">Оформление заказа</h1>

      <div class="checkout-form-wrapper">
        <?php if($data['msg']): ?>
          <div class="mg-error">
        <?php echo $data['msg'] ?>
          </div>   
        <?php endif; ?>
        <div class="payment-option">
          <p class="custom-text">Для того чтобы совершить покупку, корректно заполните форму ниже.</p>
          <form action="<?php echo SITE ?>/order?creation=1" method="post">
            <ul class="form-list">
              <li>Email:<span class="red-star">*</span></li>
              <li><input type="text" name="email" value="<?php echo $_POST['email'] ?>"></li>
              <li>Телефон:<span class="red-star">*</span>
              </li>
              <li>
                <input type="text" name="phone" value="<?php echo $_POST['phone'] ?>">
              </li>
              <li>Ф.И.О.:</li>
              <li><input type="text" name="fio" value="<?php echo $_POST['fio'] ?>"></li>
              <li>Адрес доставки:</li>
              <li><textarea class="address-area" name="address"><?php echo $_POST['address'] ?></textarea></li>
              <li>Комментарий к заказу:</li>
              <li><textarea class="address-area" name="info"><?php echo $_POST['info'] ?></textarea></li>
              <li>Плательщик:</li>
              <li>
                <select name="customer">
                  <?php $selected = $_POST['customer']=="yur"?'selected':''; ?>
                  <option value="fiz">Физическое лицо</option>
                  <option value="yur" <?php echo $selected ?>>Юридическое лицо</option>
                </select>
              </li>
            </ul>
            <?php if($_POST['customer']!="yur"){
              $style = 'style="display:none"';
            } ?>
            <ul class="form-list yur-field" <?php echo $style ?>>
              <li>Юр. лицо:</li>
              <li><input type="text" name="yur_info[nameyur]" value="<?php echo $_POST['yur_info']['nameyur'] ?>"></li>
              <li>Юр. адрес:</li>
              <li><input type="text" name="yur_info[adress]" value="<?php echo $_POST['yur_info']['adress'] ?>"></li>
              <li>ИНН:</li>
              <li><input type="text" name="yur_info[inn]" value="<?php echo $_POST['yur_info']['inn'] ?>"></li>
              <li>КПП:</li>
              <li><input type="text" name="yur_info[kpp]" value="<?php echo $_POST['yur_info']['kpp'] ?>"></li>
              <li>Банк:</li>
              <li><input type="text" name="yur_info[bank]" value="<?php echo $_POST['yur_info']['bank'] ?>"></li>
              <li>БИК:</li>
              <li><input type="text" name="yur_info[bik]" value="<?php echo $_POST['yur_info']['bik'] ?>"></li>
              <li>К/Сч:</li>
              <li><input type="text" name="yur_info[ks]" value="<?php echo $_POST['yur_info']['ks'] ?>"></li>
              <li>Р/Сч:</li>
              <li><input type="text" name="yur_info[rs]" value="<?php echo $_POST['yur_info']['rs'] ?>"></li>
            </ul>

            <div class="delivery-vs-payment">
              <?php if(''!=$data['delivery']): ?>
                <p class="delivery-text">Доставка</p>
                <ul class="delivery-details-list">
                    <?php foreach($data['delivery'] as $delivery): ?>
                    <li <?php echo ($delivery['checked'])?'class = "active"':'class = "noneactive"' ?>>
                      <label  data-delivery-date = "<?php echo $delivery['date']; ?>">
                        <input type="radio" name="delivery" <?php if($delivery['checked']) echo 'checked' ?>  value="<?php echo $delivery['id'] ?>">
                        <?php echo $delivery['description'] ?> <?php echo MG::numberFormat($delivery['cost']); ?> <?php echo '&nbsp;'.$data['currency']; ?>
                      </label>
                    </li>
                <?php endforeach; ?>
                </ul>
                <?php endif; ?>
              <div class="delivery-date" style="display:none;">
                <p class="delivery-text">Дата доставки:</p>
                <input type='text' name='date_delivery' placeholder='Дата доставки' value="<?php echo $_POST['date_delivery'] ?>" >
              </div>

              <p class="delivery-text">Способ оплаты</p>
              <ul class="payment-details-list">
                <?php if(count($data['delivery'])>1&&!$_POST['payment']): ?>
                  <li>Укажите способ доставки, чтобы выбрать способ оплаты</li>
                <?php elseif(''!=$data['paymentArray']): ?>
                  <?php echo $data['paymentArray'] ?>
                  <?php else:
                  ?>
                  <li>Нет доступных способов оплаты</li>
                <?php endif; ?>
              </ul>
              <div class="summ-info">
                <span class="delivery-text">Сумма заказа:</span>
                <span class="order-summ"><?php echo $data['summOrder'] ?></span>
                <span class="delivery-summ"></span>
              </div>
                <?php if($data['captcha']){ ?>
                <div class="checkCapcha" style="display:inline-block">
                  <img src="captcha.html" width="140" height="36">
                  <div class="capcha-text">Введите текст с картинки:<span class="red-star">*</span> </div>
                  <input type="text" name="capcha" class="captcha">
                </div>
               <?php }; ?>
            </div>

            <div class="clear"></div>
            <input type="submit" name="toOrder" class="checkout-btn" value="Оформить заказ" disabled>
          </form>
          <div class="clear">&nbsp;</div>
        </div>
      </div>
      <?php
      break;
    case 2:
      $data['meta_title'] = 'Оплата заказа';
      mgSEO($data);
      if($data['msg']):
        ?>
        <div class="errorSend">
        <?php echo $data['msg'] ?>
        </div>   
        <?php endif; ?>

      <h1 class="new-products-title">Оплата заказа</h1>
      <?php if(!$data['pay']&&$data['payment']=='fail'): ?>
        <div class="payment-form-block"><span style="color:red"><?php echo $data['message']; ?></span></div>
      <?php else: ?>
        <div class="payment-form-block">
          <div class="text-success">Ваша заявка <strong>№ <?php echo $data['orderNumber'] ?></strong> принята!</div>
          <br>На Ваш электронный адрес выслано письмо для подтверждения заказа.
          <hr>
          <p>Оплатить заказ <b>№ <?php echo $data['orderNumber'] ?> </b> на сумму <b><?php echo MG::numberFormat($data['summ']) ?></b>  <?php echo $data['currency']; ?> </p></div>
      <?php
      endif;

      if($data['payMentView']){
        include($data['payMentView']);
      }
      break;
    case 3:
      $data['meta_title'] = 'Подтверждение заказа';
      mgSEO($data);
      if($data['msg']):
        ?>
        <div class="text-success">
        <?php echo $data['msg'] ?>
        </div>   
        <?php
        endif;

        if($data['id']):
          ?>
        <h1 class="new-products-title">Подтверждение заказа</h1>
        <p class="auth-text">Заказ №<?php echo $data['orderNumber'] ?> подтвержден</p>
        <?php
      endif;
      //если пользователь не активизирован, то показываем форму задания пароля
      if($data['active']):
        ?>
        <div class="text-success">Вы успешно зарегистрировались на сайте <strong><?php echo SITE ?></strong> и можете отслеживать заказ в личном кабинете.</div>
        <div class="get-login text-center">
            Ваш логин: <strong><?php echo $data['active'] ?></strong>.
        </div>
          <div class="user-login">
              <p class="custom-text">Задайте пароль для доступа в личный кабинет.</p>
              <form action = "<?php echo SITE ?>/forgotpass" method = "POST">
                  <ul class="form-list">
                      <li>Новый пароль (не менее 5 символов)</li>
                      <li>
                          <input type="password" name="newPass">
                      </li>
                      <li>Подтвердите новый пароль</li>
                      <li>
                          <input type="password" name="pass2">
                      </li>
                  </ul>
                  <input type = "submit" class="default-btn fl-right" name="chengePass" value = "Сохранить" />
              </form>
          </div>


        <?php
      endif;
      break;

    case 4:
      ?>

      <h1 class="new-products-title">Оплатите заказ № <?php echo $data['orderNumber'] ?> на сумму <?php echo MG::numberFormat($data['summ']) ?> <?php echo $data['currency'] ?></h1>

      <?php
      if($data['payMentView']){
        include($data['payMentView']);
      }
      else{
        ?>
        <span>  Ваш способ не предусматривает оплату электронными деньгами</span><br><span> Вы должны оплатить заказ в соответствии с указанным способом оплаты! </span>
        <?php       
      }
       break;
      case 5:
      ?>
          <h1 class="new-products-title">Статус заказа</h1>
      <?php if ($data['msg']) { ?>
              <div class="errorSend">
        <?php echo $data['msg']; ?>
                  </div>
      <?php
      } else {
        $order = $data['orderInfo'][$data['id']];
        ?>
        <div class="order-history" id="<?php echo $data['id'] ?>">
          <div class="order-number">
          Заказ <strong>№<?php echo $order['number'] != '' ? $order['number'] : $order['id'] ?></strong>
          от <?php echo date('d.m.Y', strtotime($order['add_date'])) ?>
          <span class="order-status"> Cтатус заказа: <strong><?php echo $order['string_status_id'] ?></strong></span>
        </div>
           <table class="status-table">
            <tr>
            <th>Товар</th>
            <th>Артикул</th>
            <th>Цена</th>
            <th>Количество</th>
            <th>Сумма</th>
            </tr>                         
        <?php
        $perCurrencyShort = MG::getSetting('currency');
        $perOrders = unserialize(stripslashes($order['order_content']));
        ?>
        <?php if (!empty($perOrders)) foreach ($perOrders as $perOrder): ?>
            <?php
            $currencyShort = MG::getSetting('currencyShort');
            $currencyShopIso = MG::getSetting('currencyShopIso');
            $perCurrencyShort = $currencyShort[$perOrder['currency_iso']] ? $currencyShort[$perOrder['currency_iso']] : MG::getSetting('currency');
            $coupon = $perOrder['coupon'];
            ?>            
            <tr>
                <td>
                  <a href="<?php echo $perOrder['url'] ?>" target="_blank"><?php echo $perOrder['name'] ?></a>
                  <br/>
                  <?php echo htmlspecialchars_decode(str_replace('&amp;', '&', $perOrder['property'])) ?>
                </td>
                <td><?php echo $perOrder['code'] ?></td>
                <td><?php echo MG::numberFormat(($perOrder['price'])).'  '.$perCurrencyShort; ?></td>
                <td><?php echo $perOrder['count'] ?> шт.</td>
                <td><?php echo MG::numberFormat(($perOrder['price'] * $perOrder['count'])).'  '.$perCurrencyShort; ?></td>
                </tr>
         <?php endforeach; ?>
                   </table>
                   <?php if ($order['status_id'] == 2 || $order['status_id'] == 5): ?>
                     <a href="<?php echo SITE.'/order?getFileToOrder='.$order['id'] ?>" class="download-link">
                         Скачать электронные товары
                     </a>
                   <?php endif; ?>
                   <?php
                   $yurInfo = unserialize(stripslashes($order['yur_info']));
                   if (!empty($yurInfo['inn'])):
                     ?>
                     <a href="<?php echo SITE.'/order?getOrderPdf='.$order['id'] ?>" class="download-link">
                         Скачать счет в PDF
                     </a>
                   <?php endif; ?>

                   <div class="clear"></div>
                   <?php if (2 > $order['status_id']): ?>
                     <div class="order-settings">
                         <form  method="POST" action="<?php echo SITE ?>/order">
                             <input type="hidden" name="orderID" value="<?php echo $order['id'] ?>">
                             <input type="hidden" name="orderSumm" value="<?php echo $order['summ'] ?>">
                             <input type="hidden" name="paymentId" value="<?php echo $order['payment_id'] ?>">
                             <?php if ($order['payment_id'] != 3): ?>
                               <button type="submit" name="pay" value="go" class="default-btn">Оплатить заказ</button>
                             <?php endif; ?>
                         </form>
                     </div>
                   <?php endif; ?>
                   <?php if ($order['status_id'] < 2): ?>
                     <div class="order-settings">
                         Изменить способ оплаты или отменить заказ можно в личном кабинет после 
                         <a href="<?php echo SITE.'/registration' ?>">регистрации</a>. 
                         При регистрации используйте email, на который оформлен заказ.         
                     </div>
                   <?php endif; ?>

                   <div class="order-total">
                       <ul class="total-list">
                           <?php if ($coupon): ?>
                             <li>Купон: <span title="<?php echo $coupon ?>"><?php echo MG::textMore($coupon, 20) ?></span></li>
                           <?php endif; ?>
                           <li>Итого: <span><?php echo MG::numberFormat($order['summ']).'  '.$perCurrencyShort ?></span></li>
                           <?php if ($order['description']): ?>
                             <li>Доставка: <span><?php echo $order['description'] ?></span></li>
                             <?php if ($order['date_delivery']): ?>
                             <li>Дата доставки: <span><?php echo date('d.m.Y', strtotime($order['date_delivery'])) ?></span></li>
                                       <?php endif; ?>
                                     <?php endif; ?>
                                     <li>Оплата: <span class="paymen-name-to-history"><?php echo $order['paymentName'] ?></span></li>
                                     <?php $totSumm = $order['summ'] + $order['delivery_cost']; ?>
                                         <?php if ($order['delivery_cost']): ?><li>Стоимость доставки : <span><?php echo MG::numberFormat($order['delivery_cost']).'  '.$perCurrencyShort; ?></span></li>
                                         <?php endif; ?>
                                     <li>Всего к оплате: <span><?php echo MG::numberFormat($totSumm).'  '.$perCurrencyShort; ?></span></li>
                               </ul>
                           </div>
                           <div class="clear"></div>
                           <?php if (!empty($order['comment'])): ?>
                             <div class="manager-information">
                                 <div class="manager-information-head">Комментарий менеджера</div>
                                 <div class="manager-information-comm"><?php echo $order['comment']; ?> </div>
                             </div>
                           <?php endif; ?>
                           <div class="clear">&nbsp;</div>
                </div>
          <?php
      }
  }
}
?>