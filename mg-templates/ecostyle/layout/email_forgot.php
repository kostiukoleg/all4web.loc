<h1 style="margin: 0 0 10px 0; font-size: 16px;padding: 0;">Здравствуйте!</h1>
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
      Вы зарегистрированы на сайте <strong><?php echo $data['siteName']?></strong> с логином <strong><?php echo $data['email']?></strong>
  </p>
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
    Для восстановления пароля пройдите по ссылке
</p>
<div style="margin: 0;padding: 10px;background: #FFF5B5; font-weight: bold; text-align: center;">
    <a href="<?php echo $data['link']?>" target="blank"> <?php echo $data['link']?> </a>
</div>
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
    Если Вы не делали запрос на восстановление пароля, то проигнорируйте это письмо.
</p>
<p style="padding: 0;margin: 10px 0;font-size: 10px; color: #555; font-weight: bold;">
    Отвечать на данное сообщение не нужно.
</p>
