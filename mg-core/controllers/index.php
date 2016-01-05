<?php

/**
 * Контроллер: Catalog
 *
 * Класс Controllers_Catalog обрабатывает действия пользователей в каталоге интернет магазина.
 * - Формирует список товаров для конкретной страницы;
 * - Добавляет товар в корзину.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Index extends BaseController {

  function __construct() {
    $settings = MG::get('settings');
    // Если нажата кнопка купить.
    $_REQUEST['category_id'] = URL::getQueryParametr('category_id');

    if (!empty($_REQUEST['inCartProductId'])) {
      $cart = new Models_Cart;
      $property = $cart->createProperty($_POST);
      $cart->addToCart($_REQUEST['inCartProductId'], $_REQUEST['amount_input'], $property);
      SmalCart::setCartData();
      MG::redirect('/cart');
    }

    $countСatalogProduct = $settings['countСatalogProduct'];
    // Показать первую страницу выбранного раздела.
    $page = 1;

    // Запрашиваемая страница.
    if (isset($_REQUEST['p'])) {
      $page = $_REQUEST['p'];
    }
    
    $model = new Models_Catalog;

    // Получаем список вложенных категорий, для вывода всех продуктов, на страницах текущей категории.
    $model->categoryId = MG::get('category')->getCategoryList($_REQUEST['category_id']);

    // В конец списка, добавляем корневую текущую категорию.
    $model->categoryId[] = $_REQUEST['category_id'];

    // Передаем номер требуемой страницы, и количество выводимых объектов.
    $countСatalogProduct = 100;

    $actionButton = MG::getSetting('actionInCatalog') === "true" ? 'actionBuy' : 'actionView';
    $dataGroupProducts = Storage::get(md5('dataGroupProductsIndexConroller'));
    
    $currencyRate = MG::getSetting('currencyRate');      
    $currencyShopIso = MG::getSetting('currencyShopIso'); 
    $randomProdBlock = MG::getSetting('randomProdBlock')=="true"? true: false;  
    
    if ($dataGroupProducts == null) {
      
      // Формируем список товаров со старой ценой.
      $sort = $randomProdBlock ? "RAND()" : "sort";
      $saleProducts = $model->getListByUserFilter(MG::getSetting('countSaleProduct'), ' (p.old_price>0 || pv.old_price>0) and p.activity=1 ORDER BY '.$sort.' ASC');
	  
	  $recommendProducts = $model->getListByUserFilter(MG::getSetting('countRecomProduct'), ' p.recommend=1 ORDER BY '.$sort.' ASC');
	  
	  $newProducts = $model->getListByUserFilter(MG::getSetting('countNewProduct'), ' p.new=1 ORDER BY '.$sort.' ASC');
	  
      foreach ($saleProducts['catalogItems'] as &$item) {
        $imagesUrl = explode("|", $item['image_url']);
        $item["image_url"] = "";
        if (!empty($imagesUrl[0])) {
          $item["image_url"] = $imagesUrl[0];
        }
        $item['currency_iso'] = $item['currency_iso']?$item['currency_iso']:$currencyShopIso;
        //$item['price'] *= $currencyRate[$item['currency_iso']];   
        $item['old_price'] = $item['old_price']? MG::priceCourse($item['old_price']):0;
        $item['price'] =  MG::priceCourse($item['price_course']); 
        if($printCompareButton!='true'){
          $item['actionCompare'] = '';         
        }    
        if($actionButton=='actionBuy' && $item['count']==0){
          $item['actionBuy'] = $item['actionView'];         
        }
       
      }

      $dataGroupProducts['recommendProducts'] = $recommendProducts;
      $dataGroupProducts['newProducts'] = $newProducts;
      $dataGroupProducts['saleProducts'] = $saleProducts;
      Storage::save(md5('dataGroupProductsIndexConroller'), $dataGroupProducts);
    }
    
    $recommendProducts = $dataGroupProducts['recommendProducts'];
    $newProducts = $dataGroupProducts['newProducts'];
    $saleProducts = $dataGroupProducts['saleProducts'];
    
    $html = MG::get('pages')->getPageByUrl('index');
    
    if(!empty($html)){
      $html['html_content'] = MG::inlineEditor(PREFIX.'page', "html_content", $html['id'], $html['html_content']);
    }else{
      $html['html_content'] = '';    
    }
    
    $this->data = array(
      'newProducts' => !empty($newProducts['catalogItems']) ? $newProducts['catalogItems'] : array(),
      'recommendProducts' => !empty($recommendProducts['catalogItems']) ? $recommendProducts['catalogItems'] : array(),
      'saleProducts' => !empty($saleProducts['catalogItems']) ? $saleProducts['catalogItems'] : array(),
      'titeCategory' => $html['meta_title'],
      'cat_desc' => $html['html_content'],
      'meta_title' => $html['meta_title'],
      'meta_keywords' => $html['meta_keywords'],
      'meta_desc' => $html['meta_desc'],
      'currency' => $settings['currency'],
      'actionButton' => $actionButton
    );
    

  }

}