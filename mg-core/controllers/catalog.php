<?php

/**
 * Контроллер: Catalog
 *
 * Класс Controllers_Catalog обрабатывает действия пользователей в каталоге интернет-магазина.
 * - Формирует список товаров для конкретной страницы;
 * - Добавляет товар в корзину.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Catalog extends BaseController {

  function __construct() {
    $settings = MG::get('settings');
    // Если нажата кнопка купить.
    $_REQUEST['category_id'] = URL::getQueryParametr('category_id');

    if (!empty($_REQUEST['inCartProductId'])) {
      $cart = new Models_Cart;
      // Если параметров  товара не передано     
      // возможно была нажата кнопка купить из мини карточки, 
      // в этом случае самостоятельно вычисляем набор
      // параметров, которые были бы указаны при открытии карточки товара.
      if (empty($_POST) || (isset($_POST['updateCart']) && isset($_POST['inCartProductId']) && count($_POST) == 2 )) {

        $modelProduct = new Models_Product;
        $product = $modelProduct->getProduct($_REQUEST['inCartProductId']);

        if (empty($product)) {
          MG::redirect('/404');
          exit;
        }

        $blockVariants = $modelProduct->getBlockVariants($product['id']);
        $blockedProp = $modelProduct->noPrintProperty();

        $propertyFormData = $modelProduct->createPropertyForm($param = array(
          'id' => $product['id'],
          'maxCount' => $product['count'],
          'productUserFields' => $product['thisUserFields'],
          'action' => "/catalog",
          'method' => "POST",
          'ajax' => true,
          'blockedProp' => $blockedProp,
          'noneAmount' => false,
          'titleBtn' => MG::getSetting('buttonBuyName'),
          'blockVariants' => $blockVariants,
          'currency_iso' => $product['currency_iso'],
        ));

        $_POST = $propertyFormData['defaultSet'];
        $_POST['inCartProductId'] = $product['id'];
      }

      $property = $cart->createProperty($_POST);

      $cart->addToCart(
        $_REQUEST['inCartProductId'], $_REQUEST['amount_input'], $property
      );

      SmalCart::setCartData();
      MG::redirect('/cart');
    }

    if (!empty($_REQUEST['fastsearch'])) {
      $this->getSearchData();
    }

    $countСatalogProduct = $settings['countСatalogProduct'];
    // Показать первую страницу выбранного раздела.
    $page = 1;

    // Запрашиваемая страница.
    if (isset($_REQUEST['p'])) {
      $page = $_REQUEST['p'];
    }

    $model = new Models_Catalog;

    // Если происходит поиск по ключевым словам.
    $keyword = MG::defenderXss_decode(urldecode(URL::getQueryParametr('search')));

    if (!empty($keyword)) {
      $keyword = $this->convertLang($keyword);
      $items = $model->getListProductByKeyWord($keyword, false, true, false, 'groupBy');
      $searchData = array('keyword' => $keyword, 'count' => $items['numRows']);
    } else {

      // Получаем список вложенных категорий, 
      // для вывода всех продуктов, на страницах текущей категории.           
      if (empty($_REQUEST['category_id'])) {
        $_REQUEST['category_id'] = 0;
      }

      $model->categoryId = MG::get('category')->getCategoryList($_REQUEST['category_id']);

      // В конец списка, добавляем корневую текущую категорию.
      $model->categoryId[] = $_REQUEST['category_id'];

      // Записываем в глобальную переменную список всех вложенных категорий, 
      // чтобы использовать в других местах кода, например в фильтре по характеристикам
      $_REQUEST['category_ids'] = $model->categoryId;
      // Передаем номер требуемой страницы, и количество выводимых объектов.
      $countСatalogProduct = $settings['countСatalogProduct'];
      $items = $model->getList($countСatalogProduct, false, true);
    }
    // Если с фильтра пришел запрос только на количество позиций.
    if (!empty($_REQUEST['getcount']) && !empty($_REQUEST['filter'])) {
      echo $items['totalCountItems'] ? $items['totalCountItems'] : 0;
      exit();
    }

    $settings = MG::get('settings');
    if (empty($items['catalogItems'])) {
      $items['catalogItems'] = array();
    } else {
      foreach ($items['catalogItems'] as $item) {
        if ($item['id']) {
          $productIds[] = $item['id'];
        }
      }

      $product = new Models_Product;
      $blocksVariants = empty($productIds) ? null : $product->getBlocksVariantsToCatalog($productIds);

      $blockedProp = $product->noPrintProperty();
      $actionButton = MG::getSetting('actionInCatalog') === "true" ? 'actionBuy' : 'actionView';
      
      foreach ($items['catalogItems'] as $k => $item) {
        $imagesUrl = explode("|", $item['image_url']);
        $items['catalogItems'][$k]["image_url"] = "";

        if (!empty($imagesUrl[0])) {
          $items['catalogItems'][$k]["image_url"] = $imagesUrl[0];
        }

        $items['catalogItems'][$k]['title'] = MG::modalEditor('catalog', $item['title'], 'edit', $item["id"]);
        if ($items['catalogItems'][$k]['count'] == 0) {
          $buyButton = $items['catalogItems'][$k]['actionView'];
        } else {
          $buyButton = $items['catalogItems'][$k][$actionButton];
          if (!empty($items['catalogItems'][$k]['variants'])) {
            foreach ($items['catalogItems'][$k]['variants'] as $variant) {
              if ($variant['count'] == 0) {
                $buyButton = $items['catalogItems'][$k]['actionView'];             
              }
            }
          } 
        }

        // Легкая форма без характеристик.
        $liteFormData = $product->createPropertyForm($param = array(
          'id' => $item['id'],
          'maxCount' => $item['count'],
          'productUserFields' => null,
          'action' => "/catalog",
          'method' => "POST",
          'ajax' => true,
          'blockedProp' => $blockedProp,
          'noneAmount' => true,
          'titleBtn' => "В корзину",
          'blockVariants' => $blocksVariants[$item['id']],
          'buyButton' => $buyButton
        ));

        $items['catalogItems'][$k]['liteFormData'] = $liteFormData['html'];
        $buyButton = $items['catalogItems'][$k]['liteFormData'];
        $items['catalogItems'][$k]['buyButton'] = $buyButton;
      }
    }

    $categoryDesc = MG::get('category')->getDesctiption($_REQUEST['category_id']);

    if ($_REQUEST['category_id']) {
      $categoryDesc = MG::inlineEditor(PREFIX.'category', "html_content", $_REQUEST['category_id'], $categoryDesc);
    }

    $catImg = MG::get('category')->getImageCategory($_REQUEST['category_id']);

    //$model->currentCategory['title'] = $_REQUEST['category_id'] ? $model->currentCategory['title'] : 0,
	//var_dump($model->currentCategory['title']); 
    $data = array(
      'items' => $items['catalogItems'],
      'titeCategory' => $model->currentCategory['title'],
      'cat_desc' => $categoryDesc,
      'cat_img' => $catImg,
      'cat_id' => $_REQUEST['category_id'] ? $_REQUEST['category_id'] : 0,
      'filterBar' => $items['filterBarHtml'],
      'totalCountItems' => $items['totalCountItems'],
      'pager' => $items['pager'],
      'searchData' => empty($searchData) ? '' : $searchData,
      'meta_title' => !empty($model->currentCategory['meta_title']) ? $model->currentCategory['meta_title'] : $model->currentCategory['title'],
      'meta_keywords' => !empty($model->currentCategory['meta_keywords']) ? $model->currentCategory['meta_keywords'] : "товары,продукты,изделия",
      'meta_desc' => !empty($model->currentCategory['meta_desc']) ? $model->currentCategory['meta_desc'] : "В каталоге нашего магазина есть все.",
      'currency' => $settings['currency'],
      'actionButton' => $actionButton
    ); 
    if (URL::isSection('catalog')||(((MG::getSetting('catalogIndex')=='true') && (URL::isSection('index') || URL::isSection(''))))) {
      $html = MG::get('pages')->getPageByUrl('catalog');
      $html['html_content'] = MG::inlineEditor(PREFIX.'page', "html_content", $html['id'], $html['html_content']);
      $data['meta_title'] = $html['meta_title'] ? $html['meta_title'] : $html['title'];
      $data['meta_title'] = $data['meta_title'] ? $data['meta_title'] : $model->currentCategory['title'];
      $data['meta_keywords'] = $html['meta_keywords'];
      $data['meta_desc'] = $html['meta_desc'];
      $data['cat_desc'] = $html['html_content'];
      $data['titeCategory'] = $html['title'];      
    }
    if ($keyword) {
      $data['meta_title'] = 'Поиск по фразе: '.$keyword;
    }

    $this->data = $data;
  }

  /**
   * Конвертирует текст в поиске в правильную раскладку.
   * @param string $text - текст который необходимо конвертировать.
   * @return string
   */
  public function convertLang($text) {
    return $text;
  }

  /**
   * Получает список продуктов при вводе в поле поиска.
   */
  public function getSearchData() {
    $keyword = MG::defenderXss_decode(URL::getQueryParametr('text'));
    if (!empty($keyword)) {
      $keyword = $this->convertLang($keyword);

      $catalog = new Models_Catalog;
      $items = $catalog->getListProductByKeyWord($keyword, true, true, false, 'groupBy');

      foreach ($items['catalogItems'] as $key => $value) {
        $items['catalogItems'][$key]['image_url'] = mgImageProductPath($value["image_url"], $value['id'], 'small');
      }

      $searchData = array(
        'status' => 'success',
        'item' => array(
          'keyword' => $keyword,
          'count' => $items['numRows'],
          'items' => $items,
        ),
        'currency' => MG::getSetting('currency')
      );
    }

    echo json_encode($searchData);
    exit;
  }

}