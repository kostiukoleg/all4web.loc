<p style="font-size:12px;line-height:16px;margin:0;">
    Здравствуйте,  <b><?php echo $data['orderInfo']['name_buyer'] ?></b>!
    <br/> Статус Вашего заказа <b>№<?php echo $data['orderInfo']['number'] != '' ? $data['orderInfo']['number'] : $data['orderInfo']['id']; ?></b> был изменен c "<b><?php echo $data['statusOldName'] ?></b>" на "<b><?php echo $data['statusName'] ?></b>".
    <br/> 
    <?php if ($data['orderInfo']['hash'] == '') { ?>
      Следить за состоянием заказа Вы можете в <a href="<?echo SITE.'/personal'?>">личном кабинете</a>.
    <?php } else { ?>
      <span> Следить за состоянием заказа Вы можете по ссылке:
          <br/> 
          <a href="<?php echo SITE.'/order?hash='.$data['orderInfo']['hash']?>"><?echo SITE.'/order?hash='.$data['orderInfo']['hash']?></a>. </span>
    <?php }; ?>
</p>
  