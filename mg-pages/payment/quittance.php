<div class="payment-form-block">
При оформлении заказа был выбран способ оплаты по реквизитам.


<?php


foreach($data['paramArray'] as $k=>$field){
  $data['paramArray'][$k]['value'] = htmlentities($data['paramArray'][$k]['value'], ENT_QUOTES, "UTF-8");
}

$yurInfo = unserialize(stripslashes($data['orderInfo'][$data['id']]['yur_info']));
if(empty($yurInfo['inn'])){
?>
<form action="<?php echo SITE?>/order" method="post">  
  <input type="hidden" id="name" name="name" value="<?php echo $data['paramArray'][0]['value']?>">
  <input type="hidden" id="inn" name="inn" value="<?php echo $data['paramArray'][1]['value']?>">
  <input type="hidden" id="nsp" name="nsp" value="<?php echo $data['paramArray'][6]['value']?>">
  <input type="hidden" id="bank" name="bank" value="<?php echo $data['paramArray'][4]['value']?>">
  <input type="hidden" id="bik" name="bik" value="<?php echo $data['paramArray'][5]['value']?>">
  <input type="hidden" id="ncsp" name="ncsp" value="<?php echo $data['paramArray'][7]['value']?>">
  <input type="hidden" id="appointment" name="appointment"  value="Оплата по счету № <?php echo $data['orderInfo'][$data['id']]['number']!=''?$data['orderInfo'][$data['id']]['number']:$data['id'];?>">
  <input type="hidden" id="payer" name="payer" value="<?php echo $data['userInfo']->sname?> <?php echo $data['userInfo']->name?>">
  <input type="hidden" id="addrPayer" name="addrPayer" value="<?php echo $data['orderInfo'][$data['id']]['address']?>">
  <input type="hidden" id="nls" name="nls">
  <input type="hidden" id="sRub" name="sRub" value="<?php echo $data['orderInfo'][$data['id']]['summ']+$data['orderInfo'][$data['id']]['delivery_cost']?>">
  <input type="hidden" id="sKop" name="sKop" maxlength="2">
  <input type="hidden" id="uRub" name="uRub">
  <input type="hidden" id="uKop" name="uKop" maxlength="2">
  <input type="hidden" id="day" name="day" value="<?php echo date('d');?>">
  <input type="hidden" id="day" name="month" value="<?php echo date('m');?>">
  <input type="hidden" name="printQittance">
  <input type="submit" name="submit" value="Получить заполненный бланк для оплаты заказа">
</form>

<?php }else{
	if(USER::getThis()->email == $data['userInfo']->email){
	?>
	  <br/>
	  <a href="<?php echo SITE?>/order?getOrderPdf=<?php echo $data['id']?>" >Скачать счет в PDF</a>

  <?php }else{?>
      <br/>Скачать счет из личного кабинета, можно будет после подтверждения заказа, перейдя по ссылке в высланом вам письме.
 <?php }
} ?>
</div>