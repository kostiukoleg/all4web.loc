<style type="text/css">
  .form-wrapper table{border-collapse: collapse;width:100%;color:#000;}
  .form-wrapper small-table{border-collapse: separate;}
  .form-wrapper table tr th{padding: 10px;border: 1px solid #000;background:#FFFFE0;}
  .form-wrapper .who-pay tr td{padding: 5px;}
  .form-wrapper .who-pay tr td.name{width: 110px;}
  .form-wrapper .who-pay{margin: 10px 0 0 0;}
  .form-wrapper table tr td{padding: 5px;border: 1px solid #000;}
  .form-wrapper table tr td.bottom{border: none;text-align: right;}
  .form-wrapper .order-total{margin: 10px 0 0 0;color:#000;}
  .form-wrapper .title{text-align:center;font-size:24px;color:#000;}
  .form-wrapper .total-list{list-style:none;}
  .form-wrapper .no-border, .form-wrapper .who-pay tr td, .form-wrapper .small-table tr td{border:none;}
  .form-wrapper .colspan4{border:none;text-align:right;}
  .form-wrapper .rowspan2{vertical-align:bottom;}
  .form-wrapper .nowrap{white-space:nowrap;}
  .yur-table td {height:30px;}
  .form-table td {height:30px; vertical-align: baseline;}
  .p {height:30px; vertical-align: baseline;}
</style>
<div class="form-wrapper">
  <strong><?php echo $data['propertyOrder']['nameyur'] ?></strong><br>
  <?php echo $data['propertyOrder']['adress'] ?>
  <br/>
  <br/>
  <table class="yur-table">
    <tr>
      <td>
        ИНН  <?php echo $data['propertyOrder']['inn'] ?>
      </td>
      <td>КПП  <?php echo $data['propertyOrder']['kpp'] ?></td>
      <td rowspan="2" class="rowspan2 nowrap" valign="middle">Сч. №</td>
      <td rowspan="2" class="rowspan2" valign="bottom"> <?php echo $data['propertyOrder']['rs'] ?></td>
    </tr>
    <tr>
      <td colspan="2">Получатель <br> <?php echo $data['propertyOrder']['nameyur'] ?></td>
    </tr>
    <tr>
      <td colspan="2" rowspan="2">Банк получателя <br><?php echo $data['propertyOrder']['bank'] ?></td>
      <td>БИК</td>
      <td><?php echo $data['propertyOrder']['bik'] ?></td>
    </tr>
    <tr>
      <td class="nowrap">Сч. №</td>
      <td><?php echo $data['propertyOrder']['ks'] ?></td>
    </tr>
  </table>
  <h1 class="title">
    Счет <strong>№ <?php echo $data['propertyOrder']['prefix'].$data['order']['number']!='' ? $data['order']['number']:$data['order']['id']; ?></strong>
    от <?php echo MG::dateConvert($data['order']['add_date'],true) ?>	г.					
  </h1>
  <table class="who-pay">
    <tr>
      <td class="name" width="100">Плательщик:</td>
      <td width="760"><?php echo  trim(trim($data['customerInfo'],' '), ',') ?></td>
    </tr>
  </table>
  <br />
  <br />
  <table class="form-table">
    <tr>
      <th bgcolor="#FFFFE0" width="40">№</th>
      <th bgcolor="#FFFFE0" width="327">Товар</th>
      <th bgcolor="#FFFFE0" >Артикул</th>
      <th bgcolor="#FFFFE0" >Цена</th>
      <th bgcolor="#FFFFE0" width="70">Кол-во</th>
      <th bgcolor="#FFFFE0" width="50">НДС</th>
      <th bgcolor="#FFFFE0" >Сумма</th>
    </tr>

    <?php
    $i = 1;
    $ndsPercent = is_numeric($data['propertyOrder']['nds']) ? $data['propertyOrder']['nds'] : 0;
    $totalNds = 0;
	$totalWithoutNds = 0;
    if ($ndsPercent === 0) {
      $totalNds = '-';
    }
	
    if (!empty($data['perOrders']))
      
	  if($data['order']['delivery_cost']>0){
          $data['perOrders'][] = array(
            'price' => $data['order']['delivery_cost'],
            'count' => 1,
            'name' => 'Доставка',
            'code' => '-'
          ); 
      }
      
      foreach ($data['perOrders'] as $perOrder) {    
      
        if ($totalNds !== '-') {
          $marginNds = $perOrder['price'] * $ndsPercent / (100 + $ndsPercent);
          $perOrder['price'] -= $marginNds;
          $totalNds+=$perOrder['count']*$marginNds;
        }
        ?>
        <tr>
        <td style="padding: 5px;"><?php echo $i++ ?></td>
          <td cellpadding="5"><?php echo $perOrder['name'] ?>
          <?php echo htmlspecialchars_decode(str_replace('&amp;', '&', $perOrder['property'])) ?>
          </td>
          <td ><?php echo $perOrder['code'] ?></td>
          <td ><?php echo MG::numberFormat($perOrder['price']).'  '.$data['currency'] ?></td>
          <td ><?php echo $perOrder['count'] ?> шт.</td>
          <td ><?php echo (($data['propertyOrder']['nds'] >= 0 && is_numeric($data['propertyOrder']['nds'])) ? $data['propertyOrder']['nds'].'%' : '---') ?></td>
          <td ><?php echo MG::numberFormat($perOrder['price'] * $perOrder['count']).'  '.$data['currency']; ?></td>
        </tr>
        
    <?php 
    $totalWithoutNds+=$perOrder['price'] * $perOrder['count'];
    } ?>


    <tr>
      <td colspan="6" class="colspan4">
        <strong>Итого без НДС:</strong>
      </td>
      <td><?php echo MG::numberFormat($totalWithoutNds).'  '.$data['currency'] ?></td>
    </tr>
    <tr>
      <td colspan="6" class="colspan4">
        <strong>Итого НДС:</strong>
      </td>

      <?php if ($totalNds !== '-') { ?>  
        <td><?php echo MG::numberFormat($totalNds).'  '.$data['currency'] ?></td>
      <?php } else { ?>
        <td>---</td>
<?php } ?>

    </tr>
    <tr>
  

     
<?php $totalsumm = $data['order']['summ'] + $data['order']['delivery_cost']; ?>

    </tr>
    <tr>
      <td colspan="6" class="colspan4">
        <strong>Всего к оплате:</strong>
      </td>
      <td><strong><?php echo MG::numberFormat($totalsumm).'  '.$data['currency'] ?></strong></td>
    </tr>
  </table>
  <p>Всего наименований <?php echo $i-1 ?> шт., на сумму <?php echo MG::numberFormat($totalsumm).'  '.$data['currency'] ?></p>

  <?php
  include('int2str.php');
  $sumToWord = new int2str($totalsumm, true);
  $sumToWord->ucfirst($sumToWord->rub);
  ?>

  <p><strong style="font-size: 18px;"><?php echo $sumToWord->ucfirst($sumToWord->rub); ?></strong></p>    
  <div class="clear">&nbsp;</div>
</div>

<?php
$imgSing = '';
$documentRoot = URL::getDocumentRoot();
if (file_exists($documentRoot.str_replace(array('/','\\'), DIRECTORY_SEPARATOR, $data['propertyOrder']['sing']))) {
  $imgSing = '<img src="'.SITE.'/'.$data['propertyOrder']['sing'].'">';
} else {
  if (file_exists($documentRoot.str_replace(array('/','\\'), DIRECTORY_SEPARATOR, 'uploads/sing.jpg'))) { 
    $imgSing = '<img src="'.SITE.'/uploads/sing.jpg">';
  }
}

if (file_exists($documentRoot.str_replace(array('/','\\'), DIRECTORY_SEPARATOR, $data['propertyOrder']['stamp']))) {
  $imgStamp = '<img src="'.SITE.'/'.$data['propertyOrder']['stamp'].'">';
} else {
  if (file_exists($documentRoot.str_replace(array('/','\\'), DIRECTORY_SEPARATOR, 'uploads/stamp.jpg'))) {
    $imgStamp = '<img src="'.SITE.'\\uploads\\stamp.jpg">';
  }
}

if (empty($data['propertyOrder']['usedsing'])) {
  $imgSing = '';
  $imgStamp = '';
}
?>

<br /> 
<br />           
<table>

  <tr>
    <td width="240"></td>
    <td width="10"></td> 
    <td width="140" align="center"><?php echo $imgSing ?></td>
    <td width="30"></td> 
    <td width="240"></td>
  </tr>

  <tr>
    <td width="240">Генеральный директор</td>
    <td width="10"></td>        
    <td width="140"></td>
    <td width="30"></td> 
    <td width="240" align="center">/<?php echo $data['propertyOrder']['general'] ?>/</td>
  </tr>

  <tr>
    <td width="240"></td>
    <td width="10"></td>
    <td width="140" style="border-top: black solid 1px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;подпись</td>        
    <td width="30"></td> 
    <td width="240" style="border-top: black solid 1px;"><strong style="font-size: 10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;расшифровка подписи</strong></td>
  </tr>

  <tr>
    <td width="240"></td>
    <td width="10"></td>
    <td width="140"></td>   
    <td width="30"></td> 
    <td width="240"></td>
  </tr>

  <tr>
    <td width="240"></td>
    <td width="10"></td>
    <td width="140"></td> 
    <td width="30"></td> 
    <td width="240">М.П.<?php echo $imgStamp ?></td>
  </tr>

  <tr>
  </tr>

</table>