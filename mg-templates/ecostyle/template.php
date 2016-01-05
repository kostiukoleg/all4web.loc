<?php
/**
 * Файл template.php является каркасом шаблона, содержит основную верстку шаблона.
 *
 *
 *   Получить подробную информацию о доступных данных в массиве $data, можно вставив следующую строку кода в верстку файла.
 *   <code>
 *    <?php viewData($data); ?>
 *   </code>
 *
 *   Также доступны вставки, для вывода верстки из папки layout
 *   <code>
 *      <?php layout('cart'); ?>      // корзина
 *      <?php layout('auth'); ?>      // личный кабинет
 *      <?php layout('widget'); ?>    // виджиеы и коды счетчиков
 *      <?php layout('compare'); ?>   // информер товаров для сравнения
 *      <?php layout('content'); ?>   // содержание открытой страницы
 *      <?php layout('leftmenu'); ?>  // левое меню с категориями
 *      <?php layout('topmenu'); ?>   // верхнее горизонтаьное меню
 *      <?php layout('contacts'); ?>  // контакты в шапке
 *      <?php layout('search'); ?>    // форма для поиска
 *      <?php layout('content'); ?>   // вывод контента сгенерированного движком
 *   </code>
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Views
 */
?>

<!DOCTYPE html>
<html>
<head>
    <?php mgMeta(); ?>
    <meta name="viewport" content="width=device-width">

    <?php mgAddMeta('<link href="' . PATH_SITE_TEMPLATE . '/css/owl.carousel.css" rel="stylesheet" type="text/css" />'); ?>
    <?php mgAddMeta('<link href="' . PATH_SITE_TEMPLATE . '/css/mobile.css" rel="stylesheet" type="text/css" />'); ?>
    <?php mgAddMeta('<script type="text/javascript" src="' . PATH_SITE_TEMPLATE . '/js/owl.carousel.js"></script>'); ?>
    <?php mgAddMeta('<script type="text/javascript" src="' . PATH_SITE_TEMPLATE . '/js/script.js"></script>'); ?>
	<?php mgAddMeta('<link rel="shortcut icon" type="image/x-icon" href="'. PATH_SITE_TEMPLATE .'/favicon.ico">'); ?>

</head>
<body <?php backgroundSite(); ?>>

<div class="wrapper <?php echo isIndex() ? 'main-page' : '';
echo isCatalog() && !isSearch() ? 'catalog-page' : ''; ?>">
    <!--Шапка сайта-->
    <div class="header">
        <div class="top-bar">
            <span class="menu-toggle"></span>

            <div class="centered">
                <div class="top-menu-block">
                    <!--Вывод верхнего меню-->
                    <?php layout('topmenu'); ?>
                    <!--/Вывод верхнего меню-->

					<!--Вывод корзины-->
					<?php layout('cart'); ?>
					<!--/Вывод корзины-->
					
					<!--Вывод авторизации-->
					<div class="top-auth-block">
                    <?php layout('auth'); ?>
					</div>
					<!--/Вывод авторизации-->
                    
					<!--Вывод реквизитов сайта для мобильной версии-->
                    <?php layout('contacts_mobile'); ?>
                    <!--/Вывод реквизитов сайта для мобильной версии-->
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
        </div>

        <div class="bottom-bar">
            <div class="centered">
                <div class="header-left">
				<div class="logo">
					<!--Вывод логотипа сайта-->
                    <div class="logo-block">
                        <a href="<?php echo SITE ?>">
                            <?php echo mgLogo(); ?>
                        </a>
                    </div>
					<h2>ИНТЕРНЕТ МАГАЗИН</h2>
					<h1><a href="#">ДВЕРЕЙ</a></h1>
					<h3 class="telephone">Тел.: +38 <span>096 938 96 28</span><br>
			   +38 <span>063 313 49 82</span></h3>
					<h2>ДВЕРИ МЕЖКОМНАТНЫЕ<br>
		И ВХОДНЫЕ, ОПТ/РОЗНИЦА</h2>
				</div>
                <div class="slider-box">
                	<div id="slider-wrap">
                		<div id="slider">
							<?php if (class_exists('SliderAction')): ?>
                            [slider-action]
							<?php endif; ?>
						</div>
                	</div>
                </div>    

                <div class="clear"></div>
                </div>



                <div class="clear"></div>
            </div>
        </div>
    </div>
    <!--/Шапка сайта-->

    <!--Вывод горизонтального меню, если оно подключено в настройках-->
    <?php horizontMenu(); ?>
    <!--/Вывод горизонтального меню, если оно подключено в настройках-->

    <div class="container">
        <!--Центральная часть сайта-->
        <div class="center show-menu">

            <!--/Если горизонтальное меню не выводится и это не каталог, то вывести левое меню-->
            <?php if (horizontMenuDisable() && !isCatalog() || isSearch()): ?>
                <div class="left-block">
                    <div class="menu-block">
                        <span class="mobile-toggle"></span>

                        <h2 class="cat-title">
                            <a href="<?php echo SITE ?>/catalog">Каталог товаров</a>
                        </h2>
                        <!-- Вывод левого меню-->
                        <?php layout('leftmenu'); ?>
                        <!--/Вывод левого меню-->
                        
                        <!-- Блок новостей на главной-->
                        <?php if (isIndex()): ?>
                           <?php layout('mockup_news'); ?>
                        <?php endif; ?>
                        <!--/Блок новостей-->
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!isCatalog() || isSearch()) : ?>
                <div class="right-block <?php if (isIndex()): ?>index-page<?php endif; ?>">
                    <!--Вывод аякс поиска-->
                    <?php layout('search'); ?>
                    <!--/Вывод аякс поиска-->

                    <?php if (isIndex()): ?>
                        <?php if (class_exists('trigger')): ?>
                            [trigger-guarantee id="1"]
                        <?php endif; ?>
                        <div class="main-block">
                            <?php layout('content'); ?>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

            <div class="center-inner <?php echo (!isIndex() || catalogToIndex()) ? 'inner-page' : '';
            echo isSearch() ? 'no-filters' : ''; ?>">
                <?php if (isCatalog() && !isSearch()) : ?>
                    <div class="side-menu">
                        <?php if (horizontMenuDisable()) : ?>
                            <div class="menu-block">
                                <span class="mobile-toggle"></span>

                                <h2 class="cat-title"><a href="<?php echo SITE ?>/catalog">Каталог товаров</a></h2>
                                <!-- Вывод левого меню-->
                                <?php layout('leftmenu'); ?>
                                <!--/Вывод левого меню-->
                            </div>
                        <?php endif; ?>
                        <div class="filter-block ">
                            <a class="show-hide-filters" href="javascript:void(0);">Показать/скрыть фильтры</a>
                            <?php filterCatalog(); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!isIndex()): ?>
                    <div class="main-block">
                        <?php if (isCatalog() && !isSearch()) : ?>
                            <!--Вывод аякс поиска-->
                            <?php layout('search'); ?>
                            <!--/Вывод аякс поиска-->
                        <?php endif; ?>
                        <?php layout('content'); ?>
                    </div>
                <?php endif; ?>

                <?php if (class_exists('ScrollTop')): ?>
                    [scroll-top]
                <?php endif; ?>

                <div class="clear"></div>
            </div>
        </div>
        <!--/Центральная часть сайта-->
        <div class="clear"></div>
    </div>

    <!--Индикатор сравнения товаров-->
    <?php layout('compare'); ?>
    <!--/Индикатор сравнения товаров-->
</div>
<!--Подвал сайта-->
<div class="footer">
    <div class="footer-top">
        <div class="centered">
			<div class="col copyright">
				<h3>МАГАЗИН ДВЕРЕЙ</h3>
				<p> <?php echo date('Y') ?> год. Все права защищены.</p>
			</div>
			<div>
				<ul>
					<li class="right-line">
						<h4>НАШ АДРЕС</h4>
						<p style="width: 100px;"><?php echo MG::getSetting('shopAddress') ?></p>
					</li>
					<li class="right-line">
						<h4>ТЕЛЕФОНЫ</h4>
						<p><?php echo MG::getSetting('shopPhone') ?></p>
						<p>+38 (063) 313-49-82</p>
					</li>
					<li id="map"><a href="https://www.google.com.ua/maps/place/%D0%AD%D0%BA%D0%BE%D1%81%D1%82%D0%B8%D0%BB%D1%8C/@49.2392329,28.477799,17z/data=!3m1!4b1!4m2!3m1!1s0x472d5b66327df683:0xb70313290b197357?hl=ru" target="_blank">Карта<br>проезда</a></li>
					<li id="pay" class="left-line">
					<?php echo MG::get('pages')->getFooterPagesUl(1,1); ?>
					</li>
				</ul>
			</div>
			<!--/*
            <div class="col">
                <h2>Сайт</h2>
                <?php echo MG::get('pages')->getFooterPagesUl(); ?>
            </div>
            <div class="col">
                <h2>Продукция</h2>
                <ul>
                    <?php echo MG::get('category')->getCategoryListUl(0, 'public', false); ?>
                </ul>
            </div>
            <div class="col">
                <h2>Мы в соцсетях</h2>
                <ul class="social-media">
                    <li><a href="javascript:void(0);" class="vk-icon" title="Vkontakte"><span></span></a></li>
                    <li><a href="javascript:void(0);" class="gplus-icon" title="Google+"><span></span></a></li>
                    <li><a href="javascript:void(0);" class="fb-icon" title="Facebook"><span></span></a></li>
                </ul>
            </div>*/-->
            <div class="clear"></div>
        </div>
    </div>
</div>
<!--/Подвал сайта-->

<!--Коды счетчиков-->
<?php layout('widget'); ?>
<!--/Коды счетчиков-->

<!--[pozvonim]-->
</body>
</html>