<!DOCTYPE html>
<html class="mg-admin-html">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="<?php echo SITE?>/mg-admin/design/css/reset.css" rel="stylesheet" type="text/css">
<link href="<?php echo SITE?>/mg-admin/design/css/tipTip.css" rel="stylesheet" type="text/css">
<link href="<?php echo SITE?>/mg-admin/design/css/datepicker.css" rel="stylesheet" type="text/css">
<link href="<?php echo SITE?>/mg-admin/design/css/toggles.css" rel="stylesheet" type="text/css">
<link href="<?php echo SITE?>/mg-admin/design/css/style.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" href="<?php echo SITE?>/mg-core/script/codemirror/lib/codemirror.css">
<!--[if lte IE 9]>
    <link href="<?php echo SITE?>/mg-admin/design/css/ie.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="<?php echo SITE?>/mg-core/script/css3-mediaqueries.js"></script>
<![endif]-->
<title>Панель управления | Moguta.CMS</title>


<?php 
if(USER::isAuth() && ('1' == USER::getThis()->role || '3' == USER::getThis()->role || '4' == USER::getThis()->role)): ?>


 <?php MG::titlePage($lang['ADMIN_BAR']);?>

    <script>var phoneMask =  "<?php echo MG::getSetting('phoneMask');?>" </script>
    <script>var SITE = "<?php echo SITE; ?>";</script>
    <script type="text/javascript" src="<?php echo SITE?>/mg-core/script/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="<?php echo SITE?>/mg-core/script/jquery-ui-1.10.3.custom.min.js"></script>


    <script type="text/javascript" src="<?php echo SITE?>/mg-core/script/admin/admin.js?protocol=<?php echo PROTOCOL; ?>&amp;mgBaseDir=<?php echo SITE; ?>&amp;currency=<?php echo MG::getSetting('currency'); ?>&amp;lang=<?php echo MG::getSetting('languageLocale');?>"></script>
     
    
</head>
 <?php
   $oldIe = false;
   if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')||strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')||strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')){
     $oldIe = true;
   };
 ?>
<body class="mg-admin-body <?php if($oldIe): ?>old-ie<?php endif;?>" style="zoom: 1; background-image: url(<?php echo SITE?>/mg-admin/design/images/bg_textures/<?php echo $data['themeBackground']; ?>.png);">
    <?php 
    if($oldIe): ?>
        <div class="old-browser">
            <h1>ВНИМАНИЕ! Вы используете устаревший браузер Internet Explorer</h1>
            <p>Панель управления <b>MOGUTA.CMS</b> построена на передовых, современных технологиях и не поддерживает устаревшие браузеры Internet Explorer!.

                Настоятельно Вам рекомендуем выбрать и установить любой из современных браузеров. Это бесплатно и займет всего несколько минут.</p>
            <table class="brows">
                <tbody>
                    <tr>
                      <td width='120'></td>
                      <td><a href="http://www.google.com/chrome"><img src="<?php echo SITE?>/mg-admin/design/images/browsers/gc.jpg" alt="Google Chrome"></a></td>
                        <td><a href="http://www.mozilla.com/firefox/"><img src="<?php echo SITE?>/mg-admin/design/images/browsers/mf.jpg" alt="Mozilla Firefox"></a></td>
                        <td><a href="http://www.opera.com/download/"><img src="<?php echo SITE?>/mg-admin/design/images/browsers/op.jpg" alt="Opera Browser"></a></td>
                        <td><a href="http://www.apple.com/safari/download/"><img src="<?php echo SITE?>/mg-admin/design/images/browsers/as.jpg" alt="Apple Safari"></a></td>
                    </tr>
                    <tr class="brows_name">
                        <td></td>
                        <td><a href="http://www.google.com/chrome">Google Chrome</a></td>
                        <td><a href="http://www.opera.com/download/">Opera Browser</a></td>
                        <td><a href="http://www.mozilla.com/firefox/">Mozilla Firefox</a></td>                    
                        <td><a href="http://www.apple.com/safari/download/">Apple Safari</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </body>
    <?php 
    exit();
    endif;?>
    
    <div class="admin-wrapper no-print">
      
        
        <div class="notice-block top-position" style="height:30px;">     
            <div  class="message_information inform" style="background:#D8D8D8; margin: 0;border-bottom: 2px solid rgb(165, 161, 161);">Увеличьте продажи с помощью полного функционала Moguta.CMS. <b><a style="color:#28BB1D" href="http://moguta.ru/keys">Перейти на полную версию!</a></b></div>
        </div>
      
        <div class="header">
            <div class="info-panel">
                <div class="admin-logo-block">
                    <a href="#" class="logo"><img src="<?php echo SITE?>/mg-admin/design/images/logo.png" alt="" /></a>
                    <span class="current-version"><?php echo VER ?></span>
                </div>
                <ul class="button-list">
                    <?php echo $data['informerPanel']; ?>
                </ul>
                <!--Правая часть верхней панели-->
                <div class="fl-right">
                    <ul class="settings-list">
                        <li class="language"><a href="#" title="<?php echo($lang['T_TIP_LANG_PACK']);?>" class="tool-tip-left"><span class="language-ru select-language-<?php echo $data['languageLocale']; ?>"></span></a>
                            <div class="language-list-wrapper">
                                <ul class="language-list dropdown-language-menu">
                                    <li class="russian"><a href="#" class="ru_RU"><span class="ru-icon"></span>Русский</a></li>
                                    <li><a href="#" class="en_EN"><span class="usa-icon"></span>English</a></li>
                                    <li class="ua"><a href="#" class="ua_UA"><span class="ua-icon"></span>Украинский</a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="site-view"><a href="<?php echo SITE?>/" title="<?php echo($lang['BACK_TO_SITE']);?>" class="tool-tip-bottom"><span class="site-view-icon"></span><?php echo($lang['BACK_TO_SITE']);?></a></li>
                        <li class="exit"><a href="#" title="<?php echo($lang['QUIT']);?>" class="tool-tip-bottom logout-button"><span class="exit-icon"></span></a></li>
                    </ul>
                </div>
                <!--Правая часть верхней панели-->
            </div>

            <div class="admin-top-menu">
                <ul class="admin-top-menu-list">
                   <?php /*<li class="no-left-border"><a id="statistics" href="#" title="<?php echo($lang['T_TIP_STAT']);?>" class="tool-tip-bottom"><span class="stat-icon"></span><?php echo($lang['STATISTICS']);?></a></li>*/?>
                   <?php if ('1' == User::getThis()->role || '4' == USER::getThis()->role) {?>  <li class="no-left-border"><a id="catalog" href="javascript:void(0);" title="<?php echo($lang['T_TIP_PROD']);?>" class="tool-tip-bottom"><span class="product-icon"></span><?php echo($lang['PRODUCTS']);?></a></li><?php }?>
                   <?php if ('1' == User::getThis()->role || '4' == USER::getThis()->role) {?> <li><a id="statistics" style="display:none" href="javascript:void(0);"><span class="category-icon"></span>1111</a></li><?php }?>
                   <?php if ('1' == User::getThis()->role || '4' == USER::getThis()->role) {?>  <li><a id="category" href="javascript:void(0);" title="<?php echo($lang['T_TIP_CAT']);?>" class="tool-tip-bottom"><span class="category-icon"></span><?php echo($lang['CATEGORIES']);?></a></li><?php }?>
                   <?php if ('1' == User::getThis()->role || '4' == USER::getThis()->role) {?> <li><a id="page" href="javascript:void(0);" title="<?php echo($lang['T_TIP_PAGE']);?>" class="tool-tip-bottom"><span class="pages-icon"></span><?php echo($lang['PAGES']);?></a></li><?php }?>
                    <li><a id="orders" href="javascript:void(0);" title="<?php echo($lang['T_TIP_ORDR']);?>" class="tool-tip-bottom"><span class="orders-icon"></span><?php echo($lang['ORDERS']);?></a></li>
                   <?php if ('1' == User::getThis()->role || '4' == USER::getThis()->role) {?> <li class="no-right-border"><a id="users" href="javascript:void(0);" title="<?php echo($lang['T_TIP_USER']);?>" class="tool-tip-bottom"><span class="users-icon"></span><?php echo($lang['USERS']);?></a></li><?php }?>
                    <li><a id="plugins" href="javascript:void(0);" title="<?php echo($lang['T_TIP_PLUG']);?>" class="tool-tip-top"><span class="plugins-icon"></span><?php echo($lang['PLUGINS']);?><p class="white-arrow-down"></p></a>
                        <div class="plugins-menu-wrapper">
                            <ul class="plugins-dropdown-menu">
                                <?php foreach ($pluginsList as $item):?>
                                <?php
                                if(PM::isHookInReg($item['folderName'])&& $item['Active']){ ?>
                                <li><a href="#" class="<?php echo $item['folderName']?>"><?php echo $item['PluginName']?></a></li>
                                <?php } ?>
                                <?php endforeach;?>
                                <li class="go-to-plugins-settings"><a href="javascript:void(0);" class="all-plugins-settings"><?php echo($lang['SETTINGS']);?></a></li>
                            </ul>
                        </div>
                    </li>
                   <?php if ('1' == User::getThis()->role) {?> <li><a id="settings" href="javascript:void(0);" title="<?php echo($lang['T_TIP_SETT']);?>" class="tool-tip-bottom"><span class="settings-icon"></span><?php echo($lang['SETTINGS']);?></a><span class="double-border"></span></li> <?php }?>
                </ul>
            </div>
        </div>
        <div class="notice-block">
            <?php if($newVersion){ ?>
            <div id ="newVersion" class="message_information inform">
            <?php echo($lang['NEW_VER'].' - '.$newVersion);?>
            </div>
            <?php }?>
            <div class="mailLoader" style=""></div>
            <?php if($fakeKey){ ?>
                <div id ="fakeKey" class="message_information inform">
                    <?php echo($fakeKey);?>
                </div>
            <?php }?>
        </div>

        <div id="thisHostName" style="display:none"><?php echo SITE; ?></div>
        <div id="currency" style="display:none"><?php echo MG::getSetting('currency'); ?></div>
        <div id="color-theme" style="display:none"><?php echo $data['themeColor']; ?></div>
        <div id="bg-theme" style="display:none"><?php echo $data['themeBackground']; ?></div>
        <div id="staticMenu" style="display:none"><?php echo $data['staticMenu']; ?></div>
        <div id="protocol" style="display:none"><?php echo PROTOCOL; ?></div>
        <div id="currency-iso" style="display:none"><?php echo MG::getSetting('currencyShopIso'); ?></div>
        <div id="max-count-cart" style="display:none"><?php echo MAX_COUNT_CART; ?></div>
        
        
        
        <div class="admin-center">
            <div class="data">
                <!-- Контент раздела -->
            </div>
        </div>
        <div class="admin-h-height"></div>
    </div>
        <div class="admin-footer-block no-print">
            <div class="admin-copyright">
               <p>&copy; Copyright by Moguta.CMS</p>
            </div>
        </div>
        <div class="block-print">
           <!-- В этот блок будет вставляться контент для печати -->
        </div>
    </body>

    <?php else:?>
    </head>
    <body style="zoom: 1; background-image: url(<?php echo SITE?>/mg-admin/design/images/bg_textures/<?php echo $data['themeBackground']; ?>.png);">
    <div class="enter-wrapper">
        <div class="all-login-wrapper">
            <!--<div class="enter-logo"><a href="#"><img src="<?php echo SITE?>/mg-admin/design/images/enter-logo.png" alt="" /></a></div>-->
            <div class="enter-logo"><span style="font-size:15px; margin-left:12px;">Вход в панель управления</span></div>
            <div class="enter-form">
                <form action="<?php echo SITE?>/enter" method="POST" class="login">
                    <ul class="login-list">
                        <li class="login-text">Email:</li>
                        <li><input type="text" name="email" value="" class="login-input"></li>
                        <li class="login-text">Пароль:</li>
                        <li><input type="password" name="pass" value="" class="pass-input"></li>
                    </ul>
                    <input type="hidden" name="location" value="/mg-admin" />
                    <button type="submit" class="enter-button" style="margin: 10px 0px 0px 0px; padding:5px;">Войти</button>
                </form>
            </div>
        </div>
    </div>
</body>
<?php endif;?>
</html>
<!-- VER <?php echo VER;?><?php echo (class_exists('Controllers_Compare') ? '-full' : '-free');?> -->