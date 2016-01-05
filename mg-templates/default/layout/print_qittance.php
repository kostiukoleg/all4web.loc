<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Квитанция Сбербанка</title>
    <style type="text/css">
      * {
        padding: 0;
        margin: 0;
      }

      body {
        font-size: 16px;
      }
      .clear { clear: both;}
      #blank{ width: 792px; border: 4px solid #000; margin: 0 auto; }
      .blanks-wrapper { width: 800px;  margin: 0 auto; padding: 20px 0; }
      #control-panel{height:40px;}
      #control-panel a span{display:inline-block;}
      #control-panel a.btn-personal span{padding:4px 10px 4px 27px;background:url(<?php echo SITE ?>/mg-admin/design/images/icons/go-back-icon.png) 6px 4px no-repeat;}
      #control-panel a.btn-print span{padding:4px 10px 4px 27px;background:url(<?php echo SITE ?>/mg-admin/design/images/icons/print-icon.png) 6px 4px no-repeat;}
      #control-panel a{display:block;
                       background: #FCFCFC; /* Old browsers */
                       background: -moz-linear-gradient(top, #FCFCFC 0%, #E8E8E8 100%); /* FF3.6+ */
                       background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#FCFCFC), color-stop(100%,#E8E8E8)); /* Chrome,Safari4+ */
                       background: -webkit-linear-gradient(top, #FCFCFC 0%,#E8E8E8 100%); /* Chrome10+,Safari5.1+ */
                       background: -o-linear-gradient(top, #FCFCFC 0%,#E8E8E8 100%); /* Opera11.10+ */
                       background: -ms-linear-gradient(top, #FCFCFC 0%,#E8E8E8 100%); /* IE10+ */
                       filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#FCFCFC', endColorstr='#E8E8E8',GradientType=0 ); /* IE6-9 */
                       background: linear-gradient(top, #FCFCFC 0%,#E8E8E8 100%); /* W3C */
                       border: 1px solid #D3D3D3;
                       font-family: Tahoma, Verdana, sans-serif;
                       font-size:14px;
                       border-radius: 5px;
                       -moz-border-radius: 5px;
                       -webkit-border-radius: 5px;
                       color:#333;
                       text-decoration:none;
      }
      #control-panel a:hover{
        background: #eeeeee; /* Old browsers */
        background: -moz-linear-gradient(top, #eeeeee 0%, #eeeeee 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#eeeeee), color-stop(100%,#eeeeee)); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top, #eeeeee 0%,#eeeeee 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top, #eeeeee 0%,#eeeeee 100%); /* Opera11.10+ */
        background: -ms-linear-gradient(top, #eeeeee 0%,#eeeeee 100%); /* IE10+ */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#eeeeee', endColorstr='#eeeeee',GradientType=0 ); /* IE6-9 */
        background: linear-gradient(top, #eeeeee 0%,#eeeeee 100%);
      }
      #control-panel a:active{
        -moz-box-shadow: 0 0 4px 2px rgba(0,0,0,.3) inset;
        -webkit-box-shadow: 0 0 4px 2px rgba(0,0,0,.3) inset;
        box-shadow: 0 0 4px 2px #CFCFCF inset;
        outline:none;
      }
      #control-panel .btn-print { float: left; }
      #control-panel .btn-personal { float: right; }
      #top { border-bottom: 4px solid #000; }
      #top, #bottom { overflow: hidden; }
      #left01, #left02 { float: left; width: 185px; }
      #right01, #right02 { width: 600px; float: right; border-left: 4px solid #000; }
      #right01 dt, #right02 dt { text-align: center; }
      #right01 dd, #right02 dd { text-align: center; font-size: 13px; }
      .line { border-bottom: 1px solid #000;  padding: 25px 0 0 0; }
      .line2 { border-bottom: 1px solid #000; padding: 15px 0 0 0; }
      .inn { float: left; margin: 0 30px 0 0; width: 250px; }
      .nsp { float: right; width: 320px; }
      .bank { float: left; margin: 0 25px 0 0; width: 325px; }
      .bik dt, .ncsp dt, .payer dt, .addrPayer dt { float: left; padding: 10px 0 0 5px; }
      .bik dd, .ncsp dd, .payer dd, .addrPayer dd { float: right; width: 200px; font-size: 16px !important; padding: 10px 0 0 0; text-align: left !important; }
      .ncsp dd { width: 250px; margin: 0 0 7px 0; }
      .payer dd { width: 430px; }
      .addrPayer dd { width: 430px; }
      .appointment { float: left; margin: 0 30px 0 0; width: 320px; }
      .nls { float: right; width: 250px; }
      .sRub { float: left; width: 290px; }
      .uRub {float: right; width: 310px; }
      .result { float: left; width: 290px; margin: 10px 0 0 0;}
      .date { float: left; margin: 10px 0 0 0;}
      .terms { margin: 10px 0 40px 5px; }

      @media print {
        .no-print {
          display:none;
        }
      }
    </style>
  <head>
  <body>
    <div class="blanks-wrapper">
      <div id="control-panel" class="no-print">
        <a href="javascript:vodi(0);" onclick="window.print();" class="no-print btn-print"><span>Распечатать</span></a>
        <a href="<?php echo SITE ?>/personal" class="no-print btn-personal"><span>Вернуться в личный кабинет</span></a>
      </div>  
      <div id="blank">
        <div id="top">
          <div id="left01">
            <p style="text-align: center;">
              <strong>Извещание</strong>
              <strong style="display: block; margin-top: 150%;">Кассир</strong>
            </p>
          </div>
          <div id="right01">
            <dl>
              <dt><?php echo $data['name'] ?></dt>
              <dd>(наименование получателя)</dd>
            </dl>

            <dl class="inn">
              <dt><?php echo $data['inn']; ?></dt>
              <dd>(ИНН получателя платежа)</dd>
            </dl>

            <dl class="nsp">
              <dt><?php echo $data['nsp']; ?></dt>
              <dd>(номер счета получателя платежа)</dd>
            </dl>

            <dl class="bank">
              <dt><?php echo $data['bank']; ?></dt>
              <dd>(наименование банка получателя)</dd>
            </dl>

            <dl class="bik">
              <dt>БИК</dt>
              <dd><?php echo $data['bik']; ?></dd>
            </dl>
            <div class="clear"></div>

            <dl class="ncsp">
              <dt>Номер кор./сч банка получателя платежа</dt>
              <dd><?php echo $data['ncsp']; ?></dd>
            </dl>
            <div class="clear"></div>

            <dl class="appointment">
              <dt><?php echo $data['appointment']; ?></dt>
              <dd>(наименование платежа)</dd>
            </dl>

            <dl class="nls">
              <dt><?php echo $data['nls']; ?></dt>
              <dd>(номер лицевого счета (код) плательщика)</dd>
            </dl>

            <dl class="payer">
              <dt>Ф.И.О. плательщика:</dt>
              <dd><?php echo $data['payer']; ?></dd>
            </dl>

            <div class="clear"></div>
            <dl class="addrPayer">
              <dt>Адрес плательщика:</dt>
              <dd><?php echo $data['addrPayer']; ?></dd>
            </dl>

            <div class="clear"></div>
            <?php echo $currency = MG::getSetting('currency'); ?>
            <div class="sRub">
              <p>Сумма платежа: <?php echo $data['sRub']; ?> <?php echo $currency; ?> <?php echo $data['sKop']; ?> коп.</p>
            </div>
            <div class="sKop">
              <p>Сумма платы за услуги <?php echo $data['uRub']; ?> <?php echo $currency; ?> <?php echo $data['uKop'] ?> коп.</p>
            </div>
            <div class="result">
              <p>Итого: <?php echo $data['rub']; ?> <?php echo $currency; ?> <?php echo $data['kop']; ?> коп.</p>
            </div>

            <div class="date"><?php echo $data['day']; ?>.<?php echo $data['month']; ?>.<?php echo date('Y'); ?> г.</div>
            <div class="clear"></div>
            <p class="terms">
              С условиями приема указанной в платежном документе суммы, в т.ч. с суммой взимаемой платы
              банка ознакомлен и согласен.
            </p>
          </div>
          <div class="clear"></div>
          <div id="bottom" style="border-top: 4px solid #000">
            <div id="left02">
              <p style="text-align: center; margin-top: 150%;">
                <strong>Квитанция</strong><br>
                <strong>Кассир</strong>
              </p>
            </div>
            <div id="right02">
              <dl>
                <dt><?php echo $data['name'] ?></dt>
                <dd>(наименование получателя)</dd>
              </dl>

              <dl class="inn">
                <dt><?php echo $data['inn']; ?></dt>
                <dd>(ИНН получателя платежа)</dd>
              </dl>

              <dl class="nsp">
                <dt><?php echo $data['nsp']; ?></dt>
                <dd>(номер счета получателя платежа)</dd>
              </dl>

              <dl class="bank">
                <dt><?php echo $data['bank']; ?></dt>
                <dd>(наименование банка получателя)</dd>
              </dl>

              <dl class="bik">
                <dt>БИК</dt>
                <dd><?php echo $data['bik']; ?></dd>
              </dl>
              <div class="clear"></div>

              <dl class="ncsp">
                <dt>Номер кор./сч банка получателя платежа</dt>
                <dd><?php echo $data['ncsp']; ?></dd>
              </dl>
              <div class="clear"></div>

              <dl class="appointment">
                <dt><?php echo $data['appointment']; ?></dt>
                <dd>(наименование платежа)</dd>
              </dl>

              <dl class="nls">
                <dt><?php echo $data['nls']; ?></dt>
                <dd>(номер лицевого счета (код) плательщика)</dd>
              </dl>

              <dl class="payer">
                <dt>Ф.И.О. плательщика:</dt>
                <dd><?php echo $data['payer']; ?></dd>
              </dl>

              <div class="clear"></div>
              <dl class="addrPayer">
                <dt>Адрес плательщика:</dt>
                <dd><?php echo $data['addrPayer']; ?></dd>
              </dl>

              <div class="clear"></div>
              <div class="sRub">
                <p>Сумма платежа: <?php echo $data['sRub']; ?> <?php echo $currency; ?> <?php echo $data['sKop']; ?> коп.</p>
              </div>
              <div class="sKop">
                <p>Сумма платы за услуги <?php echo $data['uRub']; ?> <?php echo $currency; ?> <?php echo $data['uKop'] ?> коп.</p>
              </div>
              <div class="result">
                <p>Итого: <?php echo $data['rub']; ?> <?php echo $currency; ?> <?php echo $data['kop']; ?> коп.</p>
              </div>

              <div class="date"><?php echo $data['day']; ?>.<?php echo $data['month']; ?>.<?php echo date('Y'); ?> г.</div>
              <div class="clear"></div>
              <p class="terms">
                С условиями приема указанной в платежном документе суммы, в т.ч. с суммой взимаемой платы
                банка ознакомлен и согласен.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>