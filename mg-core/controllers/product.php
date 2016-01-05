<?php

/**
 * Контроллер Product
 *
 * Класс Controllers_Product обрабатывает действия пользователей на странице товара.
 * - Пересчитывает стоимость товара.
 * - Подготавливает форму для вариантов товара.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Product extends BaseController {

  function __construct() {
   
    $model = new Models_Product;
    
    $id = URL::getQueryParametr('id');
    
    if(empty($id) && empty($_REQUEST['calcPrice'])){
      MG::redirect('/404');
      exit;
    }

    // Требуется только пересчет цены товара.
    if (!empty($_REQUEST['calcPrice'])) {
      $model->calcPrice();
      exit;
    }

    $product = Storage::get(md5('ControllersProduct'.URL::getUrl()));
    
    if ($product == null) {
      $settings = MG::get('settings');
      $product = $model->getProduct($id);
      
      if (empty($product)){
        MG::redirect('/404');
        exit;
      } 
      
      $product['meta_title'] = $product['meta_title'] ? $product['meta_title'] : $product['title'];
      $product['currency'] = $settings['currency'];
      $blockVariants = $model->getBlockVariants($product['id']);
      $blockedProp = $model->noPrintProperty();
      $propertyFormData = $model->createPropertyForm($param = array(
        'id' => $product['id'],
        'maxCount' => $product['count'],
        'productUserFields' => $product['thisUserFields'],
        'action' => "/catalog",
        'method' => "POST",
        'ajax' => true,
        'blockedProp' => $blockedProp,
        'noneAmount' => false,
        'noneButton' => $product['count']?false:true,
        'titleBtn' => MG::getSetting('buttonBuyName'),
        'blockVariants' => $blockVariants,
        'currency_iso' => $product['currency_iso'],
      ));
      

      // Легкая форма без характеристик.   
      $liteFormData = $model->createPropertyForm($param = array(
        'id' => $product['id'],
        'maxCount' => $product['count'],
        'productUserFields' => null,
        'action' => "/catalog",
        'method' => "POST",
        'ajax' => true,
        'blockedProp' => $blockedProp,
        'noneAmount' => false,
        'noneButton' => $product['count']?false:true,
        'titleBtn' => MG::getSetting('buttonBuyName'),
        'blockVariants' => $blockVariants,
      ));

      //echo viewData($propertyFormData['defaultSet']);
  
      $product['price_course']+=$propertyFormData['marginPrice'];       
      
      $currencyRate = MG::getSetting('currencyRate');      
      $currencyShopIso = MG::getSetting('currencyShopIso');      
      $product['currency_iso'] = $product['currency_iso']?$product['currency_iso']:$currencyShopIso;
      $product['old_price'] = $product['old_price']*$currencyRate[$product['currency_iso']];
      $product['old_price'] = $product['old_price']? $product['old_price']:0;
      $product['price'] = MG::priceCourse($product['price_course']); 
      
      $product['propertyForm'] = $propertyFormData['html'];
      $product['propertyNodummy'] = $propertyFormData['propertyNodummy'];
      $product['stringsProperties'] = $propertyFormData['stringsProperties'];   
      $product['liteFormData'] = $liteFormData['html'];
      $product['description'] = MG::inlineEditor(PREFIX.'product', "description", $product['id'], $product['description']);
      $product['title'] = MG::modalEditor('catalog', $product['title'], 'edit', $product["id"]);
      // Информация об отсутствии товара на складе.
      if (MG::getSetting('printRemInfo') == "true") {
        $message = 'Здравствуйте, меня интересует товар "'.str_replace("'", "&quot;", $product['title']).'" с артикулом "'.$product['code'].'", но его нет в наличии.
        Сообщите, пожалуйста, о поступлении этого товара на склад. ';
        if($product['count']!=0){
          $style = 'style="display:none;"';        
        }
        $product['remInfo'] = "<noindex><span class='rem-info' ".$style.">Товара временно нет на складе!<br/><a rel='nofollow' href='".SITE."/feedback?message=".$message."'>Сообщить когда будет в наличии.</a></span></noindex>";
      }
      if ($product['count'] < 0) {
        $product['count'] = "много";
      };
      $product['related'] = $model->createRelatedForm($product['related']);
      Storage::save(md5('ControllersProduct'.URL::getUrl()), $product);
    }
    
   // MG::set('propertyNodummy',$product['propertyNodummy']);
   // $_SESSION['propertyNodummy'] = $product['propertyNodummy'];
    $this->data = $product;
    
  }

}