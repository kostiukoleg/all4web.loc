<?php

/**
 * Модель: Cart
 *
 * Класс Models_Cart реализует логику взаимодействия с корзиной товаров.
 * - Добавляет товар в корзину;
 * - Получает список id продуктов из корзины;
 * - Расчитывает суммарную стоимость всех товаров в корзине;
 * - Очищает содержимое корзины.
 * - Обновляет содержимое корзины.
 * - Проверяет корзину на заполненность.
 * - Получает данные о всех продуктах в корзине.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 */
class Models_Cart {

  /**
   * Добавляет товар в корзину.
   *
   * @param int $id id товара.
   * @param int $count количество.
   * @return bool
   */
  public function addToCart($id, $count = 1, $property = array('property' => '', 'propertyReal' => ''), $variantId = null) {

    $propertyReal = $property['propertyReal'];
    $property = $property['property'];
    if (empty($count) || !is_numeric($count)) {
      $count = 1;
    }
    $property = str_replace('%', '&#37;', $property);
    $property = str_replace('&', '&amp;', htmlspecialchars($property));

    // Если есть в корзине такой товар с этими характеристиками.
    $key = $this->alreadyInCart($id, $property, $variantId);

    if ($key !== null) {
      $product = new Models_Product();
      $tempProduct = $product->getProduct($id);
      $countMax = $tempProduct['count'];

      if ($variantId) {
        $tempProdVar = $product->getVariants($id);
        $countMax = $tempProdVar[$variantId]['count'];
      }

      if (($count + $_SESSION['cart'][$key]['count']) > $countMax && $countMax > 0) {
        $_SESSION['cart'][$key]['count'] = $countMax;
      } else {
        // Увеличиваем счетчик.
        $_SESSION['cart'][$key]['count'] += $count;
      }
    } else {
      $_SESSION['propertySetArray'][] = $property;
      $lastKey = array_keys($_SESSION['propertySetArray']);
      $lastKey = end($lastKey);
      if ($variant) {
        $id = $variant;
      }
      if(count($_SESSION['cart']) < MAX_COUNT_CART){

        $_SESSION['cart'][] = array(
          'id' => $id, 
          'count' => $count, 
          'property' => $property, 
          'propertyReal' => $propertyReal, 
          'propertySetId' => $lastKey, 
          'variantId' => $variantId
        );
      
      }
    }

    $args = func_get_args();
    $result = true;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Создает информацию для последующего сохранения свойства для товара положенного в корзину из входящего массива.
   * @param mixed $arr
   */
  public function createProperty($arr) {
        
    $productId = $arr['inCartProductId'];
    $product = new Models_Product;
    unset($arr['inCartProductId']);
    unset($arr['buyWithProp']);
    unset($arr['amount_input']);
    unset($arr['ajax']);
    unset($arr['updateCart']);
    unset($arr['delFromCart']);
    unset($arr['propertySetId']);
    


    $property = ''; // Фиктивная информация о характеристиках, выводимая в публичной части, в понятном пользователям виде.
    $propertyReal = ''; // Реальная защищенная информация о характеристиках, не выводимая в публичной части, хранящаяся в сессии в корзине.
    foreach ($arr as $key => $value) {

      // Разбор зашифрованых ключей (номер характеристики#номер пункта), для множественной характеристики (чекбоксы).
      $keyParse = array();
      $pattern = "/^(.*)#(.*)$/";
      preg_match($pattern, $key, $matches);
      if (isset($matches[1]) && isset($matches[2])) {
        // Получили данные из ключа, теперь по ним можно достать реальную информацию о добавочной стоимости пункта.
        $keyParse = array('property_id' => $matches[1], 'numberElement' => $matches[2]);
      }

      // В значении тоже может передаваться дополнительная стоимость, это если доступен только один пункт (select и radiobutton).
      $valueParse = array();
      preg_match($pattern, $value, $matches);
      if (isset($matches[1]) && isset($matches[2])) {
        // Получили данные из ключа, теперь по ним можно достать реальную информацию о добавочной стоимости пункта.
        $valueParse = array('property_id' => $matches[1], 'numberElement' => $matches[2]);
      }

      $parseData = null;
      // Если и ключ и значение удалось распарсить, приоритет ключу.
      if (!empty($keyParse)) {
        $parseData = $keyParse;
      } elseif (!empty($valueParse)) {
        $parseData = $valueParse;
      }

      // Если ключ расшифрован найден, надо дописывать добавочные стоимости.
      if (!empty($parseData)) {
       
        $item = $product->getProduct($productId);
        // воспроизводим набор реальных характеристик, чтобы избежать бага с перезаписью сессиии из второй вкладки
        $propertyFormData = $product->createPropertyForm($param = array(
           'id' => $productId,
           'productUserFields' => $item['thisUserFields'],
           'maxCount' => $item['count'],
           'blockedProp' =>  $product->noPrintProperty(),
           'currency_iso' => $item['currency_iso']
         ));
        $currencyRate = MG::getSetting('currencyRate');
        $currencyShort = MG::getSetting('currencyShort');
        $currencyRate = $currencyRate[$item['currency_iso']];
        $currencyShort = $currencyShort[$item['currency_iso']];
        
        $realVal = $propertyFormData['propertyNodummy'][$parseData['property_id']][$parseData['numberElement']]['value'];
        $realName = $propertyFormData['propertyNodummy'][$parseData['property_id']][$parseData['numberElement']]['name'];
        $data = $product->parseMarginToProp($realVal);
        
        if (empty($data) && !empty($realVal)) {
          $data['name'] = $realVal;
          $data['margin'] = 0;
        }
        if (!empty($data)) {
          $plus = $product->addMarginToProp($data['margin'], $currencyRate );
          $plus = OUTPUT_MARGIN=='0' ? '' : $plus;
          $property .= '<div class="prop-position"> <span class="prop-name">'.$realName.': '.str_replace('_', ' ', $data['name']).'</span> <span class="prop-val">'.$plus.'</span></div>';
          $propertyReal.= '<div class="prop-position"> <span class="prop-name">'.$realName.': '.str_replace('_', ' ', $data['name']).'</span> <span class="prop-val"> '.$realVal.'</span></div>';
        }
      } else {
        // Иначе, выбрана обычная характеристика без стоимости.
        $property .= '<div class="prop-position"> <span class="prop-name">'.str_replace('_', ' ', $key).'</span>: <span class="prop-val">'.$value.'</span></div>';
      }
    }

    return array('property' => $property, 'propertyReal' => $propertyReal);
  }

  /**
   * Сравнивает добавляемый товар с товарами в корзине, если в корзине 
   * есть такой же товар с id и его свойства совпадают с 
   * текущим, то увеличиваем счетчик иначе просто добавляем новую
   * позицию продукта с выбраными параметрами.
   *
   * @param int $id id товара.
   * @param int $count количество.
   * @param int $variant id варианта товара.
   * @return id - элемента в корзине
   */
  public function alreadyInCart($id, $property, $variant = null) {
    $result = null;

    if (!empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $key => $item) {
        if (empty($item['variantId'])) {
          if ($id == $item['id'] && $property == $item['property']) {
            $result = $key;
            break;
          }
        } else {
          if ($variant == $item['variantId'] && $property == $item['property']) {
            $result = $key;
            break;
          }
        }
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Удаляет товар из корзины.
   * @param int $id id товара.
   * @return bool
   */
  public function delFromCart($id, $property, $variantId) {
    if (!empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $key => $item) {
        if ($variantId > 0) {
          if ($property == $item['property'] && $variantId == $item['variantId']) {
            $propertySetId = $_SESSION['cart'][$key]['propertySetId'];
            if (!empty($_SESSION['propertySetArray'][$propertySetId])) {
              unset($_SESSION['propertySetArray'][$propertySetId]);
            }
            unset($_SESSION['cart'][$key]);
            break;
          }
        } else {
          if ($id == $item['id'] && $property == $item['property']) {
            $propertySetId = $_SESSION['cart'][$key]['propertySetId'];
            if (!empty($_SESSION['propertySetArray'][$propertySetId])) {
              unset($_SESSION['propertySetArray'][$propertySetId]);
            }
            unset($_SESSION['cart'][$key]);
            break;
          }
        }
      }
    }
  }

  /**
   * Возвращает список id продуктов из корзины.
   * @return array список id.
   */
  protected function getListItemId() {
    $args = func_get_args();
    $result = null;

    if (!empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $key => $item) {
        $result[] = $item['id'];
      }
    }

    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает суммарную стоимость всех товаров в корзине
   * @deprecated
   * @return float
   */
  public function getTotalSumm() {

    // Создает модель для работы с продуктами.
    $itemPosition = new Models_Product();

    $currencyRate = MG::getSetting('currencyRate');   
    $currencyShopIso = MG::getSetting('currencyShopIso');
    
    if (!empty($_SESSION['cart'])) {
      $variants = array();
      $products = array();
      foreach ($_SESSION['cart'] as $key => $item) {  
        if($item['variantId']){          
          $variants[$key] = $item['variantId'];                 
        }else{  
          $products[$key] = $item['id'];            
        }
      }
        if (!empty($variants)) {
          $ids = implode(',', $variants);
          $res_var = DB::query('
            SELECT  pv.id as id, pv.price_course as `price`, c.rate as rate,
            pv.currency_iso
            FROM `'.PREFIX.'product_variant` pv   
            LEFT JOIN `'.PREFIX.'product` as p ON 
            p.id = pv.product_id
            LEFT JOIN `'.PREFIX.'category` as c ON 
            c.id = p.cat_id       
            WHERE pv.id IN ('.DB::quote($ids, true).')
          ');  
         while($prod = DB::fetchArray($res_var)) {
            $rate = $prod['rate'] ? $prod['rate'] : 0;
            $prod['price_course'] = $prod['price']+$prod['price']*$rate;
            $prices_variant[$prod['id']] = $prod;
        }
        }
        if (!empty($products)) {
          $ids = implode(',', $products);
          $res_pr = DB::query('
            SELECT p.id as id, p.price_course as `price`, c.rate,
            p.`currency_iso` 
            FROM `'.PREFIX.'product` p
            LEFT JOIN `'.PREFIX.'category` c
            ON c.id = p.cat_id
            WHERE p.id IN  ('.DB::quote($ids, true).')');        
          while($prod = DB::fetchArray($res_pr)) {
            $rate = $prod['rate'] ? $prod['rate'] : 0;
            $prod['price_course'] = $prod['price']+$prod['price']*$rate;
            $prices_prod[$prod['id']] = $prod;
          }
        }      
      
      foreach ($_SESSION['cart'] as $key => $item) {
       
        if($item['variantId']){
          $prod = $prices_variant[$variants[$key]];  
        }else{
          $prod = $prices_prod[$item['id']]; 
        }
     
          
        $prod['currency_iso'] = $prod['currency_iso']?$prod['currency_iso']:$currencyShopIso;
        //$prod['price'] *= $currencyRate[$prod['currency_iso']];    
   
        $prod['price'] =  $prod['price_course']; 
        $prod['price'] = SmalCart::plusPropertyMargin($prod['price'], $item['propertyReal'], $currencyRate[$prod['currency_iso']]);
        $prod['property'] = $item['property'];
        $prod['keyInCart'] = $key;
        $productPositions[] = $prod;
      }
    }

    $totalSumm = 0;
    // Расчитывает сумму.
    if (!empty($productPositions)) {
      foreach ($productPositions as $key => $product) {
        
        // применение скидки по купону          
        $priceWithCoupon = $this->applyCoupon($_SESSION['couponCode'], $product['price'], $product);
        $priceWithDiscount = $this->applyDiscountSystem($product['price']); 
        //$product['price'] = $priceWithCoupon < $priceWithDiscount['price'] ? $priceWithCoupon : $priceWithDiscount['price'];
        $product['price'] = $this->customPrice(array(
          'product' => $product,
          'priceWithCoupon' => $priceWithCoupon, 
          'priceWithDiscount' => $priceWithDiscount['price'],
        ));
        
        $totalSumm += $_SESSION['cart'][$product['keyInCart']]['count'] * $product['price'];
      }
    }
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $totalSumm, $args);
  }

  /**
   * Очищает содержимое корзины.  
   * @return void
   */
  public function clearCart() {
    unset($_SESSION['cart']);
    MG::createHook(__CLASS__."_".__FUNCTION__);
  }

  /**
   * Обновляет содержимое корзины. 
   *
   * @param array $arr  массив продуктов в корзине.
   * @return void
   */
  public function refreshCart($arr) {
    $_SESSION['cart'] = $arr;   
    $result = true;
    $args = func_get_args();
    MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Проверяет корзину на заполненность.
   *
   * @return bool
   */
  public function isEmptyCart() {
    $result = false;
    unset($_SESSION['cart']['']);
    if (!empty($_SESSION['cart'])) {
      $result = true;
    }
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает данные о всех продуктах в корзине.
   * @return array
   */
  public function getItemsCart() {

    $productPositions = array();

    // Создает модель для работы с продуктами.
    $itemPosition = new Models_Product();
    $totalSumm = 0;
    if (!empty($_SESSION['cart'])) {

      $currencyRate = MG::getSetting('currencyRate');   
      $currencyShopIso = MG::getSetting('currencyShopIso');
      $variantsId = array();
      $productsId = array();
      foreach ($_SESSION['cart'] as $key => $item) {
        if (!empty($item['variantId'])) {
          $variantsId[] = $item['variantId'];
        }
        $productsId[] = $item['id'];        
      }
      $products_all = array();
      $variants_all = array();
      if (!empty($variantsId)) {
          $ids = implode(',', $variantsId);
          $variants_res = DB::query('SELECT  pv.*, c.rate,(pv.price_course + pv.price_course *(IFNULL(c.rate,0))) as `price_course`,
          p.currency_iso
          FROM `'.PREFIX.'product_variant` pv   
          LEFT JOIN `'.PREFIX.'product` as p ON 
            p.id = pv.product_id
          LEFT JOIN `'.PREFIX.'category` as c ON 
            c.id = p.cat_id       
          WHERE pv.id IN ('.trim(DB::quote($ids, true)).')');
          while ($variant_row = DB::fetchAssoc($variants_res)) {
            $variants_all[$variant_row['id']] = $variant_row;
           }
        }
        if (!empty($productsId)) {
          $ids = implode(',', array_unique($productsId));
          $product_res = DB::query('
            SELECT  CONCAT(c.parent_url,c.url) as category_url,
            p.url as product_url, p.*, rate, (p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`, 
            p.`currency_iso` 
            FROM `'.PREFIX.'product` p
            LEFT JOIN `'.PREFIX.'category` c
            ON c.id = p.cat_id
            WHERE p.id IN ('.DB::quote($ids, true).')');
   
          if (!empty($product_res)) {
            while ($result = DB::fetchAssoc($product_res)) {
              $products_all[$result['id']] = $result;
            }          
          }
        }
      foreach ($_SESSION['cart'] as $key => $item) {
        $variant = '';
        if (!empty($item['variantId'])) {
        //  $variants = $itemPosition->getVariants($item['id']);
          $variant = $variants_all[$item['variantId']];
        }
        // Заполняет массив информацией о каждом продукте по id из куков.
        // Если куки не актуальны, пропускает товар.
        $product = $products_all[$item['id']];
        if (!empty($product)) {
          $product['property'] = $_SESSION['cart'][$key]['propertySetId'];
          $product['property_html'] = htmlspecialchars_decode(str_replace('&amp;', '&', $_SESSION['cart'][$key]['property']));
          $product['propertySetId'] = $_SESSION['cart'][$key]['propertySetId'];

          if (!empty($variant)) {           
            $product['price'] = $variant['price'];
            $product['code'] = $variant['code'];
            $product['count'] = $variant['count'];
            $product['weight'] = $variant['weight'];        
            $product['image_url'] = $variant['image']?$variant['image']:$product['image_url'];
            $product['title'] .= " ".$variant['title_variant'];
            $product['variantId'] = $variant['id'];
          	$product['price_course']  = $variant['price_course'];
          }

          // если установлен формат без копеек то округлим стоимость.
          $priceFormat = MG::getSetting('priceFormat');
          
          if(in_array($priceFormat, array('1234.56','1234','1 234','1,234',''))){               
            $price = round($product['price_course']);          
          }else{
            $price = $product['price_course'];     
          }
       
          
          if ($item['id'] == $product['id']) {
            $count = $item['count'];
            $price = SmalCart::plusPropertyMargin($price, $item['propertyReal'], $currencyRate[$product['currency_iso']]);
            
           // $product['currency_iso'] = $product['currency_iso']?$product['currency_iso']:$currencyShopIso;
           // $price *= $currencyRate[$product['currency_iso']];    
            
            $product['price'] = $price;

            // применение скидки по купону 

            $priceWithCoupon = $this->applyCoupon($_SESSION['couponCode'], $product['price'], $product);     
            $priceWithDiscount = $this->applyDiscountSystem($product['price']); 
            //$product['price'] = $priceWithCoupon < $priceWithDiscount['price'] ? $priceWithCoupon : $priceWithDiscount['price'];
            $product['price'] = $this->customPrice(array(
              'product' => $product,
              'priceWithCoupon' => $priceWithCoupon, 
              'priceWithDiscount' => $priceWithDiscount['price'],
            ));
            
            $product['priceInCart'] = MG::priceCourse($product['price'] * $count)." ".MG::getSetting('currency');
          
            $arrayImages = explode("|", $product['image_url']);
            
            if (!empty($arrayImages)) {
              $product['image_url'] = $arrayImages[0];
            }
          }
          $product['category_url'] = (SHORT_LINK == '1' ? '' : $product['category_url'].'/');
          $product['link'] = SITE.'/'.(isset($product["category_url"]) ? $product["category_url"] : 'catalog'.'/').$product["product_url"];
          $product['countInCart'] = $item['count'];

          if ($product['countInCart'] > 0) {
            $productPositions[] = $product;
          }
          $totalSumm += $product['price'] * $item['count'];          
         
        }
      }
    }
   
    $totalSumm = MG::priceCourse($totalSumm);
    $result = array('items' => $productPositions, 'totalSumm' => $totalSumm);
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
  /**
   * Проверяет целостность корзины.
   * Необходимо, когда был удален один из продуктов из БД, но у пользователя в куках остался ID продукта
   * @return void
   */
  public function repairCart() {
    foreach ($_SESSION['cart'] as $id => $count) {
      if ($id == '') {
        unset($_SESSION['cart']['']);
      }
    }
  }

  /**
   * Функция возвращает минимальную цену с учетом скидки по купону, объемной и накопительно скидок
   * или если находит пользовательскую функцию обработки цены, возвращает её результат.
   * 
   * @param $arFields  массив данных, которые можно использовать для формирования своей цены:
   *    product - информация о продукте, включающая оригинальную цену
   *    priceWithCoupon - цена с примененной скидкой по купону
   *    priceWithDiscount - цена с примененной накопительной или объемной скидкой
   */
  
  public function customPrice($arFields){
    $result = $arFields['priceWithCoupon'] < $arFields['priceWithDiscount'] ? $arFields['priceWithCoupon'] : $arFields['priceWithDiscount'];
    
    $product = $arFields['product'];
    $priceArgs = array(
      'priceWithCoupon' => $arFields['priceWithCoupon'], 
      'priceWithDiscount' => $arFields['priceWithDiscount'],
    );
    
    $priceCustomFunctionsList = MG::getInstance()->getPriceCustomFunctions();
    usort($priceCustomFunctionsList, 'sortFunctionByPriority');

    foreach($priceCustomFunctionsList as $function){
      $priceArgs['product'] = $product;
      $args[0] = $priceArgs;
      
      if (function_exists($function['function_name']) && empty($function['class'])) {                
        $product['price'] = call_user_func_array($function['function_name'], $args);
      } elseif ($function['class'] && class_exists($function['class'])) {
        $product['price'] = call_user_func_array(array($function['class'], $function['function_name']), $args);
      }
      
      $result = $product['price'];
    }
    
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
  /*
   * Сортирует записи по полю "priority"
   * @param $a, $b  массивы с полями:
   *    class
   *    function_name
   *    priority
   */
  private function sortFunctionByPriority($a, $b){
    if($a['priority'] == $b['priority']){
      return 0;
    }
    
    return $a['priority'] < $b['priority'] ? -1 : 1;
  }
  
  /**
   * Применяет скидку по купону
   * @param string $code - код купона товара.
   * @param string $price - входящая стоимость.
   * @param string $product - информация о продукте.
   * @return double - возвращает новую стоимость товара
   */
  public function applyCoupon($code, $price, $product = null) {
    $result = $price;
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
    /**
   * Применяет скидку по ситеме скидок - накопительная или объемная
   * @param string $price - входящая стоимость.
   * @return double - возвращает новую стоимость товара, информация о том какая система скидок применена и скидочный купон
   */
  public function applyDiscountSystem($price) {
    $result = array (
      'price' => $price, 
      'discounts' => '',
      'promo' => ''
      );
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

}