<h1 style="margin: 0 0 10px 0; font-size: 16px;padding: 0;">Подбор паролей на сайте <?php echo $data['siteName']?> предотвращен!</h1>
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
  Система защиты от перебора паролей для авторизации зафиксировала активность.
  с IP адреса <?php echo $_SERVER['REMOTE_ADDR'];?> было введено более 5 неверных паролей.
  Пользователь вновь сможет ввести пароль через 15 минут.
</p>
<p style="padding: 0;margin: 10px 0;font-size: 12px;">
  Если 5 неправильных попыток авторизации были инициированы администратором,
 то для снятия блокировки перейдите по ссылке
</p>
<div style="margin: 0;padding: 10px;background: #FFF5B5; font-weight: bold; text-align: center;">
    <?php echo $data['link']?>
</div>

<p style="padding: 0;margin: 10px 0;font-size: 10px; color: #555; font-weight: bold;">
    Отвечать на данное сообщение не нужно.
</p>