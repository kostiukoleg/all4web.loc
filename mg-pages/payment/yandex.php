<div class="payment-form-block">

<form method="POST" action="https://money.yandex.ru/quickpay/confirm.xml">
   <input type="hidden" name="receiver" value="<?php echo $data['paramArray'][0]['value']?>">
   <input type="hidden" name="formcomment" value="Оплата заказа № <?php echo $data['orderNumber']?>">
   <input type="hidden" name="short-dest" value="Оплата заказа № <?php echo $data['orderNumber']?>">
   <input type="hidden" name="writable-targets" value="">
   <input type="hidden" name="comment-needed" value="false">
   <input type="hidden" name="label" value="<?php echo $data['id']?>">
   <input type="hidden" name="quickpay-form" value="shop">
   <input type="hidden" name="targets" value="Оплата заказа № <?php echo $data['orderNumber']?>">
   <input type="hidden" name="sum" value="<?php echo $data['summ']?>" data-type="number" >
   <?php if ($data['paramArray'][2]['value']=='true') :?>
    <input type="hidden" name="test_payment" value="true"> 
   <?php endif;?>
   <input type="submit" name="submit-button" value="Оплатить" class="btn" style="padding: 10px 20px;">
</form>
 <p>
 <em>
 Вы можете изменить способ оплаты данного заказа из Вашего личного кабинета в разделе "<a href="<?php echo SITE?>/personal">История заказов</a>".
 <br/>
 Или перечислить деньги на наш <b><span style="color:red" >Я</span>ндекс</b> кошелек <b><?php echo $data['paramArray'][0]['value']?></b> указав номер заказа.
 <br/>

 </em>
 </p>
<br/>
<img alt="" src="data:image/png;base64,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" />
</div>