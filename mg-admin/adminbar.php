<?php
/**
 * Панель администрирования, подключается в публичной части сайта,
 * если пользователь является администратором
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Files
 */
?>


<?php
mgAddMeta('<link rel="stylesheet" href="'.SITE.'/mg-admin/design/css/adminbar.css" type="text/css" />');
$lang = MG::get('lang');
?>

<div class="admin-top-menu">
	<div class="left-side">
		<ul class="admin-list">
			<li><a href="<?php echo SITE?>/mg-admin/"><span class="admin-site-icon"></span><?php echo $lang['PUBLIC_BAR_0']?></a></li>
		</ul>
	</div>
	<div class="right-side">
		<ul class="exit-list">
			<!--<li><a href="#"><span class="user-icon"></span><?php echo User::getThis()->name ?></a></li>-->
      <?php if ('1' == User::getThis()->role || '4' == User::getThis()->role) {?>
     
      <li>
          <a href="javascript:void(0);" class="site-edit <?php echo MG::getSetting("enabledSiteEditor")=="true"?"enabled":"" ?>"><?php echo $lang['PUBLIC_BAR_1']?>
          <div class="switch"></div>
          </a>
      </li>
      
      <li>
          <a href="javascript:void(0);" class="clear-cache"><?php echo $lang['PUBLIC_BAR_2']?></a>
      </li>
      <?php }?>     
			<li><a href="<?php echo SITE?>/enter?logout=1"><span class="exit-icon"></span><?php echo $lang['PUBLIC_BAR_3']?></a></li>
		</ul>
	</div>
</div>

