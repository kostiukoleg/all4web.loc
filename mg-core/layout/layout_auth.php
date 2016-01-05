<?php if($thisUser = $data['thisUser']): ?>
<p class="auth">
  Добро пожаловать 
  <a href="<?php echo SITE?>/personal">
    <?php echo empty($thisUser->name)?$thisUser->email:$thisUser->name ?>
  </a>,
  <a href="<?php echo SITE?>/enter?logout=1">выход</a>
</p>
<?php else: ?>
<p class="auth">
  <a href="<?php echo SITE?>/enter">Войти</a> или 
  <a href="<?php echo SITE?>/registration">зарегистрироваться</a>
</p>
<?php endif; ?>	  
