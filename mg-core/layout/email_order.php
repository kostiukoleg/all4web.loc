<table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" width="675">
  <tbody>
  <tr>
      <td valign="top">
      <h1 style="margin: 0 0 10px 0; font-size: 16px;padding: 0;">
          Здравствуйте, <?php echo $data['fio'] ?>!
        </h1>


        <div style="font-size:12px;line-height:16px;margin:0;">
          Ваш заказ <b>№<?php echo $data['orderNumber'] ?></b>
          принят к выполнению в магазине <?php echo $data['shopName'] ?>.
		  <p class="confirm-info" style="font-size:12px;margin:0 0 10px 0">
		  <br>
		  Подтвердите заказ, перейдя <br> по <?php echo $data['confirmLink'] ?>
		  <br>
         <?php if($data['toKnowStatus'] == '' &&(!USER::getUserInfoByEmail($data['email'])->activity)) { ?>
		  <span style="color:red">Автоматически будет создана учетная запись для доступа в Ваш личный кабинет.</span>
          <?php } ?>
		  </p>
      
		  
          <br>
          Мы будем оповещать Вас о ходе выполнения заказа по почте.
          <br>
          <?php if ($data['toKnowStatus'] == '') {?>
          После подтверждения заказа, Вы можете в любой момент самостоятельно узнать статус заказа в Вашем
          <a href="<?php echo SITE ?>/personal" style="color:#1E7EC8;" target="_blank">личном кабинете</a>.
          <?php } else {?>
          <span>После подтверждения заказа, Вы можете в любой момент самостоятельно узнать статус заказа 
              по ссылке:<br> <?php echo $data['toKnowStatus'] ?> </span>
          <?php }; ?>
          <br>
          Перед доставкой мы Вам позвоним, чтобы согласовать время доставки.
          <br>
          Если у Вас возникнут вопросы — их можно задать по почте

          <a href="mailto:<?php echo $data['supportEmail'] ?>" style="color:#1E7EC8;" target="_blank"><?php echo $data['supportEmail'] ?></a>
          или по телефону
		  <br>
          <span>
            <span class="js-phone-number highlight-phone"><?php echo $data['shopPhone'] ?>,</span>
          </span>.
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <h2 style="font-size:18px;font-weight:normal;margin:0;">Ваш заказ №<?php echo $data['orderNumber'] ?>
          <small>(создан <?php echo date('d.m.Y H:i', strtotime($data['formatedDate'])) ?>)</small></h2>
      </td>
    </tr>
    <tr>
      <td>
        <table cellspacing="0" cellpadding="0" border="0" width="675">

          <tbody><tr>
              <th align="left" width="325" bgcolor="#EAEAEA" style="font-size:13px;padding:5px 9px 6px 9px;line-height:1em;">Плательщик:</th>
              <th width="10"></th>
              <th align="left" width="325" bgcolor="#EAEAEA" style="font-size:13px;padding:5px 9px 6px 9px;line-height:1em;">Способ оплаты:</th>
            </tr>

          </tbody><tbody>
            <tr>
              <td valign="top" style="font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;">
                <?php echo $data['fio'] ?><br>       
                <br>
				Тел: <span class="js-phone-number highlight-phone"><?php echo $data['phone'] ?></span>               


              </td>
              <td>&nbsp;</td>
              <td valign="top" style="font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;">
                <p><strong><?php echo $data['payment'] ?></strong></p>



              </td>
            </tr>
          </tbody>
        </table>
        <br>

        <table cellspacing="0" cellpadding="0" border="0" width="675">

          <tbody><tr>
              <th align="left" width="325" bgcolor="#EAEAEA" style="font-size:13px;padding:5px 9px 6px 9px;line-height:1em;">Адрес доставки:</th>
              <th width="10"></th>
              <th align="left" width="325" bgcolor="#EAEAEA" style="font-size:13px;padding:5px 9px 6px 9px;line-height:1em;">Способ доставки:</th>
            </tr>

          </tbody><tbody>
            <tr>
              <td valign="top" style="font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;">            

                <?php echo $data['address'] ?><br>

                &nbsp;
              </td>
              <td>&nbsp;</td>
              <td valign="top" style="font-size:12px;padding:7px 9px 9px 9px;border-left:1px solid #EAEAEA;border-bottom:1px solid #EAEAEA;border-right:1px solid #EAEAEA;">
                <?php echo $data['delivery'] ?>
                &nbsp;
                <?php echo ($data['date_delivery'] ? '<br> Дата: '.$data['date_delivery'] : '') ?>
                &nbsp;
              </td>
            </tr>
          </tbody>
        </table>
        <br>

        <table cellspacing="0" cellpadding="0" border="0" width="675" style="border:1px solid #EAEAEA;">

          <thead>
            <tr>
              <th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Товар</th>
              <th align="left" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Артикул</th>
              <th align="center" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">Количество</th>
              <th align="right" bgcolor="#EAEAEA" style="font-size:13px;padding:3px 9px">стоимость товаров</th>
            </tr>
          </thead>
          <tbody bgcolor="#F6F6F6">
            <?php if (!empty($data['productPositions']) || $data['adminOrder']) : ?>                  
              <?php foreach ($data['productPositions'] as $product) : ?>
                <?php $product['property'] = htmlspecialchars_decode(str_replace('&amp;', '&', $product['property'])); ?>
                <tr>
                  <td style="font-size:13px;padding:5px 9px;"><?php echo $product['name'].$product['property'] ?></td>
                  <td style="font-size:13px;padding:5px 9px;"><?php echo $product['code'] ?></td>             
                  <td style="font-size:13px;padding:5px 9px;" align="center"><?php echo $product['count'] ?> шт.</td>
                  <td style="font-size:13px;padding:5px 9px;" align="right"><?php echo MG::numberFormat($product['price']).' '.$data['currency'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody> 

          <tr>
            <td colspan="3" align="right" style="padding:5px 9px 0 9px;font-size: 13px;">
              стоимость товаров                    </td>
            <td align="right" style="padding:5px 9px 0 9px;font-size: 13px;font-weight:bold;">
              <span><?php echo  MG::numberFormat($data['result']).' '.$data['currency'] ?></span>                    </td>
          </tr>
          <tr>
            <td colspan="3" align="right" style="padding:2px 9px;font-size: 13px;">
              доставка                    </td>
            <td align="right" style="padding:2px 9px;font-size: 13px;font-weight:bold;">
              <span><?php echo  MG::numberFormat($data['deliveryCost']).' '.$data['currency'] ?></span>                    </td>
          </tr>
          <tr>
            <td colspan="3" align="right" style="padding:2px 9px 5px 9px; font-size: 13px;font-weight:bold;">
              <strong>полная стоимость</strong>
            </td>
            <td align="right" style="padding:2px 9px 5px 9px; color: #BA0A0A;font-size: 13px;font-weight:bold;">
              <strong><span><?php echo  MG::numberFormat($data['total']).' '.$data['currency'] ?></span></strong>
            </td>
          </tr>

        </table>

        <p style="font-size:12px;margin:0 0 10px 0">

        </p>
      </td>
    </tr>
    <tr>
      <td bgcolor="#EAEAEA" align="center" style="background:#EAEAEA;text-align:center;">
  <center>
    <p style="font-size:12px;margin:0;">
      Спасибо за покупку!
      <br>
      Магазин «<strong><?php echo $data['shopName'] ?></strong>».
    </p>
  </center>
</td>
</tr>

<?php if(!empty($data['adminMail'])):?>
  <tr>
   <td bgcolor="" align="left" style="background:#F5F3C6;">
      <p style="font-size:11px;margin:0;">
        ip пользователя: <b><?php echo $data['ip']?></b><br/>
        Покупатель сделал этот заказ после перехода из:  <b><?php echo $data['lastvisit']?> </b><br/>
        Покупатель впервые пришел к нам на сайт из:  <b><?php echo $data['firstvisit']?> </b><br/>
        Покупатель использовал купон: <b><?php echo $data['couponCode']?> </b><br/>
      </p>
  </td>
  </tr>
<?php endif;?>




</tbody></table>

