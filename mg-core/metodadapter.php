<?php

/**
 * Файл metodadapter.php содержит набор функций, необходимых пользователям
 * для построения собственных скриптов. Все функции этого файла,
 * являются алиасами, для аналогичных функций из класса MG.
 * Целью использования данного файла является исключение из пользовательских
 * файлов сложного для понимания синтаксиса MG::
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Files
 */

/**
 * Метод addAction Добавляет обработчик для заданного хука.
 *
 * @param  $hookName имя хука, на который вешается обработчик.
 * @param  $userFunction пользовательская функци, которая сработает при объявлении хука.
 * @param  $countArg количество аргументов, которое ждет пользовательская функция.
 */
function mgAddAction($hookName, $userFunction, $countArg = 0, $priority = 10){
  MG::addAction($hookName, $userFunction, $countArg, $priority);
}

/*
 * Метод mgAddCustomPriceAction Добавляет обработчик для изменения цены товара, до того как он попал в корзину.
 * @param  $userFunction название функции обработчика, или массив вида (Класс, Имя функции обработчика в классе)
 * @param  $priority приоритет выполнения функции
 */
function mgAddCustomPriceAction($userFunction, $priority = 10){
  MG::addPriceCustomFunction($userFunction, $priority);
}

/**
 * Метод addAction Добавляет обработчик шорткода.
 * @param $shortcode название шорткода.
 * @param $userFunction пользовательская функци, которая сработает при встрече [названия шорткода].
 */
function mgAddShortcode($shortcode, $userFunction){
  MG::addShortcode($shortcode, $userFunction);
}

/**
 * Добавляет обработчик для страницы плагина.
 * Назначенная в качестве обработчика пользовательская функция
 * будет, отрисовывать страницу настроек плагина.
 *
 * @param  $plugin название папки, в которой лежит плагин.
 * @param  $userFunction пользовательская функция,
 *         которая сработает при открытии страницы настроек данного плагина.
 */
function mgPageThisPlugin($plugin, $userFunction){
  MG::addAction($plugin, $userFunction);
}

/**
 * Добавляет обработчик для активации плагина,
 * пользовательская функция будет срабатывать тогда когда
 * в панели администрирования будет активирован плагин.
 *
 * >Является необязательным атрибутом плагина, при отсутствии этого
 * обработчика плагин тоже будет работать.
 *
 * Функция обрабатывающя событие
 * не должна производить вывод (echo, print, print_r, var_dump), это нарушит
 * логику работы AJAX.
 *
 * @param  $dirPlugin директория, в которой хранится плагин.
 * @param  $userFunction пользовательская функци, которая сработает при объявлении хука.
 */
function mgActivateThisPlugin($dirPlugin, $userFunction){
  MG::activateThisPlugin($dirPlugin, $userFunction);
}

/**
 * Добавляет обработчик для деактивации плагина,
 * пользовательская функция будет срабатывать тогда когда
 * в панели администрирования будет выключен  плагин.
 *
 * >Является необязательным атрибутом плагина, при отсутствии этого
 * обработчика плагин тоже будет работать.
 *
 * Функция обрабатывающя событие
 * не должна производить вывод (echo, print, print_r, var_dump), это нарушит
 * логику работы AJAX.
 *
 * @param  $dirPlugin директория, в которой хранится плагин.
 * @param  $userFunction пользовательская функци, которая сработает при объявлении хука.
 */
function mgDeactivateThisPlugin($dirPlugin, $userFunction){
  MG::deactivateThisPlugin($dirPlugin, $userFunction);
}

/**
 * Создает hook -  крючок, для  пользовательских функций и плагинов.
 * может быть вызван несколькими спообами:
 * 1. createHook('userFunction'); - в любом месте программы выполнится пользовательская функция userFunction() из плагина;
 * 2. createHook('userFunction', $args); - в любом месте программы выполнится пользовательская функция userFunction($args) из плагина с параметрами;
 * 3. return createHook('thisFunctionInUserEnviroment', $result, $args); - хук прописывается перед.
 *  возвращением результата какой либо функции,
 *  в качестве параметров передается результат работы текущей функции,
 *  и начальные параметры, которые были переданы ей.
 *
 * @param array $arr параметры, которые надо защитить.
 * @return array $arr теже параметры, но уже безопасные.
 */
function mgCreateHook($hookName){
  MG::createHook($hookName);
}

/**
 * Добавляет переданную строку в секцию <head> </head>
 *
 * @param string $data - строковая переменная, с данными.
 * @param string $onlyController - подключать только для заданного контролера.
 * @return void.
 */
function mgAddMeta($data, $onlyController = 'all'){

  $register = MG::get('register')?MG::get('register'):array();

  // Если заголовок нужно подключить только в определенном контролере, 
  // то записываем его в  отдельный ключ массива.
  if($onlyController!='all'){
    $onlyController = 'controllers_'.$onlyController;
  }
  if(!empty($register[$onlyController])){
    if(!in_array($data, $register[$onlyController])){
      $register[$onlyController][] = $data;
    }
  }
  else{
    $register[$onlyController][] = $data;
  }

  MG::set('register', $register);
  MG::set('userMeta', MG::get('userMeta')."\n".$data);
}

/**
 * Устанавливает значение для опции (настройки).
 * @param array $data -  может содержать значения для полей таблицы.
 * <code>
 * $data = array(
 *   option => 'идентификатор опции например: sitename'
 *   value  => 'значение опции например: moguta.ru'
 *   active => 'в будущем будет отвечать за автоподгрузку опций в кеш Y/N'
 *   name => 'Метка для опции например: Имя сайта'
 *   desc => 'Описание опции: Настройа задает имя для сайта'
 * )
 * </code>
 * @return void
 */
function setOption($data){
  // Если функция вызвана вот так: setOption('option', 'value');
  if(func_num_args()==2){
    $arg = func_get_args();
    $data = array();
    $data['option'] = $arg[0];
    $data['value'] = $arg[1];
  }
  MG::setOption($data);
}

/**
 * Возвращает значение для запрошенной опции (настройки).
 * Имеет два режима:
 * 1. getOption('optionName') - вернет только значение;
 * 2. getOption('optionName' , true) - вернет всю информацию об опции в
 * виде массива.
 * <code>
 * $data = array(
 *   option => 'идентификатор опции например: sitename'
 *   value  => 'значение опции например: moguta.ru'
 *   active => 'в будущем будет отвечать за автоподгрузку опций в кеш Y/N'
 *   name => 'Метка для опции например: Имя сайта'
 *   desc => 'Описание опции: Настройа задает имя для сайта'
 * )
 * </code>
 * @return void
 */
function getOption($option, $data = false){
  return MG::getOption($option, $data);
}

/**
 * Получить меню в HTML виде.
 * @return object - объект класса Menu.
 */
function mgMenu(){
  echo MG::getMenu();
}

/**
 * Получить полное меню в HTML виде.
 * @return object - объект класса Menu.
 */
function mgMenuFull($type = 'top'){
  echo MG::getMenu($type);
}

/**
 * Получить параметры маленькой корзины.
 * @return object - объект класса SmalCart.
 */
function mgGetCart(){
  return MG::getSmalCart();
}

/**
 * Устанавливает шорткод для вывода meta данных к сгенерированной странице.
 * @param string|bool $title заголовок страницы.
 * @return void.
 */
function mgMeta(){
  echo '[mg-meta]';
  mgAddShortcode('mg-meta', 'mgMetaInsert');
}

function mgMetaInsert(){
  return MG::meta();
}

/**
 * Устанавливает meta данные страницы title, description, keywords.
 * @param string|bool $title заголовок страницы.
 * @return void.
 */
function mgSEO($data){
  MG::seoMeta($data);
}

/**
 * Задает заголовок страницы.
 * @return void
 */
function mgTitle($title){
  MG::titlePage($title);
}

/**
 * Выводит содержимое массива на страницу
 * @return void
 */
function viewData($data){
  echo "<pre>";
  echo htmlspecialchars(print_r($data, true));
  echo "</pre>";
}

/**
 * Склонение числительных.
 * Пример:
 * echo 'Найдено '.mgDeclensionNum($data['searchData']['count'], array('товар', 'товара', 'товаров'))
 * @param int $number - количество
 * @param array $titles - массив для склонения, например: array('товар', 'товара', 'товаров')
 * @return string
 */
function mgDeclensionNum($number, $titles){
  return MG::declensionNum($number, $titles);
}

/**
 * Проверяет является ли страница статичной, созданной из панели администрирования
 * @return void
 */
function isStaticPage(){
  return MG::get('isStaticPage');
}

/**
 * Блок стандартной маленькой корзины в HTML виде.
 * @param type $data
 * @return string.
 */
function mgSmallCartBlock($data){
  echo MG::layoutManager('layout_cart', $data);
}

/**
 * Блок стандартной маленькой корзины в HTML виде.
 * @param type $data
 * @return string.
 */
function mgSearchBlock(){
  echo MG::layoutManager('layout_search', null);
}

/**
 * Блок с контактами в HTML виде.
 * @param type $data
 * @return string.
 */
function mgContactBlock(){
  echo MG::layoutManager('layout_contacts', null);
}

/**
 * Возвращает правильно сформированную картинку для продукта в HTML.
 * Со всеми параметрами, для эфекта перелета в корщину.
 * @param type $data - параметры продукта
 * @return string.
 */
function mgImageProduct($data){
  $product = new Models_Product();
  $data["image_url"] = basename($data["image_url"]);
  $imagesData = $product->imagesConctruction($data["image_url"], $data["image_title"], $data["image_alt"], $data['id']);  
  $src = SITE."/uploads/no-img.jpg";
  $dir = floor($data["id"]/100).'00';
  $imagesData["image_url"] = basename($imagesData["image_url"]);
	$srcLarge = mgImageProductPath($data["image_url"], $data["id"]);
  if(file_exists(URL::$documentRoot.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'product'.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.$data["id"].DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.'70_'.$imagesData["image_url"])){
    $src = SITE.'/uploads/product/'.$dir.'/'.$data['id'].'/thumbs/70_'.$imagesData["image_url"];
  }elseif(file_exists(URL::$documentRoot.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.'70_'.$imagesData["image_url"])){
    $src = SITE.'/uploads/thumbs/70_'.$imagesData["image_url"];
  }
  
  return '<img data-transfer="true" data-product-id="'.$data["id"].'" src="'.$src.'" alt="'.$imagesData["image_alt"].'" title="'.$imagesData["image_title"].'" data-large="'.$srcLarge.'">';
}

/**
 * Функция возвращает сформированную ссылку на картинку
 * 
 * @param type $image - имя изображения
 * @param type $productId - id товара
 * @param type $size - размер: small/big/orig - не обязательный
 * @return string
 */
function mgImageProductPath($image, $productId, $size = 'orig'){
  $src = SITE.'/uploads/no-img.jpg';
  
  if(empty($image)){
    return $src;
  }
  
  $image = str_replace(array('30_', '70_'), '', basename($image));
  $dir = floor($productId/100).'00';
  $ds = DIRECTORY_SEPARATOR;
  $prefix = '';
  
  if($size == 'small'){
    $prefix = '30_';
  }elseif($size == 'big'){
    $prefix = '70_';
  }
  
  if(empty($size) || $size == 'orig'){
    if(file_exists(URL::$documentRoot.$ds.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.$image)){
      $src = SITE.'/uploads/product/'.$dir.'/'.$productId.'/'.$image;
    }elseif(file_exists(URL::$documentRoot.$ds.'uploads'.$ds.$image)){
      $src = SITE.'/uploads/'.$image;
    }
  }else{
    //Добалено в 5.7.0. Через некоторое время можно удалить проверку наличия якартинки по старому образцу пути.
    if(file_exists(URL::$documentRoot.$ds.'uploads'.$ds.'product'.$ds.$dir.$ds.$productId.$ds.'thumbs'.$ds.$prefix.$image)){
      $src = SITE.'/uploads/product/'.$dir.'/'.$productId.'/thumbs/'.$prefix.$image;
    }elseif(file_exists(URL::$documentRoot.$ds.'uploads'.$ds.'thumbs'.$ds.$prefix.$image)){
      $src = SITE.'/uploads/thumbs/'.$prefix.$image;
    }
  }
  
  return $src;
}

/**
 * Возвращает список вложенных категорий.
 */
function mgSubCategory($catId){
  $data = MG::get('category')->getHierarchyCategory($catId, true);
  echo MG::layoutManager('layout_subcategory', $data);
}

/**
 * Возвращает правильно сформированную картинку для продукта в HTML.
 * Со всеми параметрами, для эфекта перелета в корзину.
 * @param type $data - параметры продукта
 * @return string.
 */
function mgGalleryProduct($data){
  echo MG::layoutManager('layout_images', $data);
}

/**
 * Возвращает картинку логотипа магазина, установленую в настройках.
 * @param string $alt - параметр alt;
 * @param string $title -  параметр title;
 * @param string $style - дополнительные стили;
 */
function mgLogo($alt = '', $title = '', $style = ''){
  if(!$title&&!$alt){
    $title = MG::getSetting('shopName');
    $alt = $title;
  }
  $logo = (MG::getSetting('shopLogo')!='')?MG::getSetting('shopLogo'):"/mg-templates/".MG::getSetting('templateName')."/images/logo.png";

  return '<img src='.SITE.$logo.' alt="'.htmlspecialchars($alt).
    '" title="'.htmlspecialchars($title).'" '.$style.'>';
}

/**
 * Выводит верстку содержащуюся в заданном layout. 
 * @param  $layout - название верстки  в папке шаблона layout, без префикса 'layout_';
 * @param  $data - массив данных переданых в layout';
 */
function layout($layout, $data = null){
  if(in_array($layout, array('cart', 'auth', 'contacts', 'search'))){
    $data = MG::get('templateData');
  }

  if($layout=='topmenu'){
    echo Menu::getMenuFull('top');
    return true;
  }

  if($layout=='leftmenu'){
    echo MG::get('category')->getCategoriesHTML();
    return true;
  }

  if($layout=='horizontmenu'){
    echo MG::get('category')->getCategoriesHorHTML();
    return true;
  }

  if($layout=='content'){
    $data = MG::get('templateData');
    echo $data['content'];
    return true;
  }

  if($layout=='widget'){
    echo MG::getSetting('widgetCode');
    return true;
  }

  if($layout=='logo'){
    $logo = (MG::getSetting('shopLogo')!='')?MG::getSetting('shopLogo'):"/mg-templates/".MG::getSetting('templateName')."/images/logo.png";
    echo '<img src="'.SITE.$logo.'" alt="">';
    return true;
  }

  echo MG::layoutManager('layout_'.$layout, $data);
  return true;
}

/**
 * Возвращает цену в отформатированном виде.
 */
function priceFormat($number){
  return $number;
}

/**
 * Возвращает html код фильтров магазина.
 * Работает только для разделов каталога.
 * @param  $userStyle - отключает стандартные стили, позволяете задать пользовательские;
 */
function filterCatalog($userStyle = false){
  if(!$userStyle){
    if(MG::get('controller')=='controllers_catalog'){
      mgAddMeta('<link type="text/css" href="'.SCRIPT.'standard/css/jquery.ui.slider.css" rel="stylesheet"/>');
      mgAddMeta('<link type="text/css" href="'.SCRIPT.'standard/css/filter.css" rel="stylesheet"/>');
      mgAddMeta('<script type="text/javascript" src="'.SCRIPT.'standard/js/filter.js"></script>');
    }
  }
  echo MG::get('catalogfilter');
}

/**
 * Возвращает html код копирайта Moguta.CMS в футере сайта
 */
function copyrightMoguta(){
  $html = '';
  if (MG::getSetting('copyrightMoguta')=='true') { 
    $html = '<div class="powered"> Сайт работает на движке: 
      <a href="http://moguta.ru" target="_blank">
      <img src="'.PATH_SITE_TEMPLATE.'/images/footer-logo.png" 
      alt="Moguta.CMS - Выбирай лучшее!" title="Moguta - простая CMS для интернет-магазина!"></a></div>';
  }
  echo $html;
}
/**
 * Добавляет фоновое изображение, если выбрано в настройках
 */
function backgroundSite() {
  
  $backgr = (MG::getSetting('backgroundSite')!='')? SITE.MG::getSetting('backgroundSite'): '';
  if ($backgr) {
    $html = 'style="background: url('.SITE.(MG::getSetting('backgroundSite')).') no-repeat fixed center center /100% auto #fff;" ';
  }
  echo $html;
}

/**
 * Возвращает true, если открыта главная страница
 */
function isIndex() {
  return (MG::get('controller') == 'controllers_index') ? true: false;
}

/**
 * Возвращает true, если открыта страница каталога
 */
function isCatalog() {
  return (MG::get('controller') == 'controllers_catalog') ? true: false;
}

/**
 * Возвращает true, если открыта страница корзины
 */
function isCart() {
  return (MG::get('controller') == 'controllers_cart') ? true: false;
}

/**
 * Возвращает true, если открыта страница заказа
 */
function isOrder() {
  return (MG::get('controller') == 'controllers_order') ? true: false;
}

/**
 * Возвращает true, если открыта страница заказа
 */
function isSearch() {
  return !empty($_GET['search']) ? true: false;
}


/**
 * Возвращает верстку горизонтального меню, если оно, подключено в настройках
 */
function horizontMenu() {
  if (MG::getSetting('horizontMenu') == "true"){
    return layout('horizontmenu');
  }
  return false;
}
/**
 * Возвращает верстку горизонтального меню, если оно, подключено в настройках
 */
function horizontMenuDisable() {
  if (MG::getSetting('horizontMenu') == "false"){
    return true;
  }
  return false;
}

/**
 * Возвращает значение флага, для опции вывода каталога на главной
 */
function catalogToIndex() {
  if (MG::getSetting('catalogIndex') == 'true'){
    return true;
  }
  return false;
}
