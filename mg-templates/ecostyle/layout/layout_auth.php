<?php if($thisUser = $data['thisUser']): ?>
  <div class="auth">
    <span class="user-icon"></span>
    <a href="<?php echo SITE ?>/personal">
      <?php echo empty($thisUser->name)?$thisUser->email:$thisUser->name ?>
    </a>
    <span class="slash">/</span>
    <span class="logout-icon"></span>
    <a href="<?php echo SITE ?>/enter?logout=1">выход</a>
  </div>
<?php else: ?>
  <div class="auth">
    <div class="enter-on">
        <a href="javascript:void(0);" class="open-link"><span class="lock-icon"></span>Вход</a>
      <div class="enter-form">
        <form action="<?php echo SITE ?>/enter" method="POST">
          <ul class="form-list">
            <li>Email:<span class="red-star">*</span></li>
            <li><input type = "text" name = "email" value = "<?php echo!empty($_POST['email'])?$_POST['email']:'' ?>"></li>
            <li>Пароль:<span class="red-star">*</span></li>
            <li><input type="password" name="pass"></li>
            <?php echo!empty($data['checkCapcha'])?$data['checkCapcha']:'' ?>
          </ul>
          <a href="<?php echo SITE ?>/forgotpass" class="forgot-link">Забыли пароль?</a>
          <button type="submit" class="enter-btn default-btn">Войти</button>
        </form>
      </div>
    </div>
    <!--<span class="slash">/</span>-->
    <span class="key-icon"></span>
    <a href="<?php echo SITE ?>/registration">Регистрация</a>
  </div>
<?php endif; ?>	  
