<?php

/**
 * Модель: Catalog
 *
 * Класс Models_Catalog реализует логику работы с каталогом.
 * - Проверяет данные из формы авторизации;
 * - Получает параметры пользователя по его логину.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Model
 *
 * Last modified: 10.02.2015 - Osipov Ivan
 */
class Models_Catalog {

  /**
   * @var type @var mixed Массив с категориями продуктов.
   */
  public $categoryId = array();

  /**
   * @var type @var mixed Массив текущей категории.
   */
  public $currentCategory = array();

  /**
   * @var type @var mixed Фильтр пользователя..
   */
  public $userFilter = array();

  /**
   * Получает ссылку и название текущей категории.
   * @return bool
   */
  public function getCurrentCategory() {
    $result = false;

    $sql = '
      SELECT *
      FROM `' . PREFIX . 'category`
      WHERE id = %d
    ';

    if (end($this->categoryId)) {
      $res = DB::query($sql, end($this->categoryId));
      if ($this->currentCategory = DB::fetchAssoc($res)) {
        $result = true;
      }

    } else {
      $this->currentCategory['url'] = 'catalog';
      $this->currentCategory['title'] = 'Каталог';
      $result = true;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }

  /**
   * Возвращает список товаров и пейджер для постраничной навигации.
   * @param int $countRows - количество возвращаемых записей для одной страницы.
   * @param boolean $mgadmin - откуда вызван метод, из публичной части или панели управления.
   * @param boolean $onlyActive - учитывать только активные продукты.
   * @return type
   */
  public function getList($countRows = 20, $mgadmin = false, $onlyActive = false) {
    // Если не удалось получить текущую категорию.
    if (!$this->getCurrentCategory()) {
      echo 'Ошибка получения данных!';
      exit;
    }


    // только для публичной части строим html для фильтров, а ели уже пришел запрос с нее, то получаем результат
    if (!$mgadmin) {

      $onlyInCount = false; // ищем все товары
      if(MG::getSetting('printProdNullRem') == "true"){
        $onlyInCount = true; // ищем только среди тех которые есть в наличии
      }
      $filterProduct = $this->filterPublic(true, $onlyInCount);

      MG::set('catalogfilter',$filterProduct['filterBarHtml']);

      // return array('catalogItems'=>null, 'pager'=>null, 'filterBarHtml'=>$filter->getHtmlFilter(true), 'userFilter' => $userFilter);
      // если пришел запрос с фильтра со страницы каталога и не используется плагин фильтров
      if (isset($_REQUEST['applyFilter'])) {

        $result = array();
        if (!empty($filterProduct['userFilter'])) {
          // если при генерации фильтров был построен запрос
          // по входящим свойствам товара из  гет запроса
          // то получим все товары  именно по данному запросу, учитвая фильтрацию по характеристикам

          $result = $this->getListByUserFilter($countRows, $filterProduct['userFilter']);

          $result['filterBarHtml'] = $filterProduct['filterBarHtml'];
        }

        $args = func_get_args();
        return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
      }
    }

    // Страница.
    $page = URL::get("page");

    $sql .= 'SELECT
          DISTINCT p.id,
          CONCAT(c.parent_url,c.url) as category_url,
          p.url as product_url,
          p.*, pv.product_id as variant_exist,
          rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`
        FROM `' . PREFIX . 'product` p
        LEFT JOIN `' . PREFIX . 'category` c
          ON c.id = p.cat_id
        LEFT JOIN `' . PREFIX . 'product_variant` pv
          ON p.id = pv.product_id
        LEFT JOIN (
          SELECT pv.product_id, SUM( pv.count ) AS varcount
          FROM  `' . PREFIX . 'product_variant` AS pv
          GROUP BY pv.product_id
        ) AS temp ON p.id = temp.product_id';

      // FIND_IN_SET - учитывает товары, в настройках которых,
      // указано в каких категориях следует их покзывать.
      $this->currentCategory['id'] =  $this->currentCategory['id']?$this->currentCategory['id']:0;
      
        if (MG::getSetting('productInSubcat')=='true') {       
          $filter = '(p.cat_id IN (' .DB::quote( implode(',', $this->categoryId),1) . ') '
          . 'or FIND_IN_SET(' . DB::quote($this->currentCategory['id'],1) . ',p.`inside_cat`))';
        }else{
          $filter = '(c.id IN (' . DB::quote($this->currentCategory['id'],1) . ') '
          . 'or FIND_IN_SET(' .  DB::quote($this->currentCategory['id'],1)  . ',p.`inside_cat`))';
        }  
      
        if ($mgadmin) {           
          $filter = ' (p.cat_id IN (' .DB::quote( implode(',', $this->categoryId),1) . ') '
          . 'or FIND_IN_SET(' .  DB::quote($this->currentCategory['id'],1)  . ',p.`inside_cat`))';
          
          if($this->currentCategory['id'] == 0){
              $filter = ' 1=1 ';
          }
        }  
        
      // Запрос вернет общее кол-во продуктов в выбранной категории.
      $sql .=' WHERE  ' . $filter;
      if ($onlyActive) {
        $sql .= ' AND p.activity = 1';
      }
      if (MG::getSetting('printProdNullRem') == "true" && !$mgadmin) {
        $sql .=" AND (temp.`varcount` > 0 OR temp.`varcount` < 0"
          . " OR p.count>0 OR p.count<0)";
      }
   
    $orderBy = ' ORDER BY `sort` DESC ';
    if(FILTER_SORT!='FILTER_SORT' && !$mgadmin ){
      $parts = explode('|',FILTER_SORT);
      $orderBy = ' ORDER BY `'.DB::quote($parts[0],1).'` '.DB::quote($parts[1],1);      
    }
    $sql .= $orderBy;
    

    // в админке не используем кэш
    if (!$mgadmin) {
      $result = Storage::get(md5($sql.$page));
    }

    if ($result == null) {
      $navigator = new Navigator($sql, $page, $countRows); //определяем класс  
      $this->products = $navigator->getRowsSql();
      // добавим к полученым товарам их свойства
      $this->products = $this->addPropertyToProduct($this->products);

      if ($mgadmin) {
        $this->pager = $navigator->getPager('forAjax');
      } else {
        $this->pager = $navigator->getPager();
      }

      $result = array('catalogItems' => $this->products, 'pager' => $this->pager, 'totalCountItems' => $navigator->getNumRowsSql());
      Storage::save(md5($sql.$page), array('catalogItems' => $this->products, 'pager' => $this->pager, 'totalCountItems' => $navigator->getNumRowsSql()));
    }

    if (!empty($filterProduct['filterBarHtml'])) {
      $result['filterBarHtml'] = $filterProduct['filterBarHtml'];
    }

    $args = func_get_args();

    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }

  /**
   * Получает список продуктов в соответствии с выбранными параметрами фильтра.
   * @param type $countRows - количество записей;
   * @param type $userfilter - пользовательская составляющая для запроса;
   * @param type $mgadmin - админка;
   * @return array
   */
  public function getListByUserFilter($countRows = 20, $userfilter, $mgadmin = false) {
    // Вычисляет общее количество продуктов.
    $page = URL::get("page");

    // Запрос вернет общее кол-во продуктов в выбранной категории.
    $sql = '
      SELECT distinct p.id,
        CONCAT(c.parent_url,c.url) as category_url,
        p.url as product_url,
        p.*,pv.product_id as variant_exist,
        rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`,
        p.currency_iso
      FROM `' . PREFIX . 'product` p
      LEFT JOIN `' . PREFIX . 'category` c
        ON c.id = p.cat_id
      LEFT JOIN `' . PREFIX . 'product_variant` pv
        ON p.id = pv.product_id
     WHERE  ' . $userfilter;

    $navigator = new Navigator($sql, $page, $countRows); //определяем класс.
    $this->products =$navigator->getRowsSql();

    $model = new Models_Product();

    // добавим к полученым товарам их свойства
    $this->products = $this->addPropertyToProduct($this->products);

    if ($mgadmin) {
      $this->pager = $navigator->getPager('forAjax');
    } else {

      $this->pager = $navigator->getPager();
    }

    $result = array('catalogItems' => $this->products, 'pager' => $this->pager, 'totalCountItems' => $navigator->getNumRowsSql());
    
//    viewData($result);
    
    $args = func_get_args();
    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }

  /**
   * Возвращает список найденных продуктов соответствующих поисковой фразе.
   * @param string $keyword - поисковая фраза.
   * @param string $allRows - получить сразу все записи.
   * @param string $onlyActive - учитывать только активные продукты.
   * @param boolean $adminPanel - запрос из публичной части или админки.
   * @return array
   */
  public function getListProductByKeyWord($keyword, $allRows = false, $onlyActive = false, $adminPanel = false, $mode = false) {

    $result = array(
      'catalogItems' => array(),
      'pager' => null,
      'numRows' => null
    );

    $keyword = htmlspecialchars($keyword);
    $keywordUnTrim = $keyword;
    $keyword = trim($keyword);

    if (empty($keyword) || mb_strlen($keyword, 'UTF-8') <= 2) {
      return $result;
    }
    $currencyRate = MG::getSetting('currencyRate');
    $currencyShopIso = MG::getSetting('currencyShopIso');
    // Поиск по точному соответствию.
    // Пример $keyword = " 'красный',   зеленый "
    // Убираем начальные пробелы и конечные.
    $keyword = trim($keyword); //$keyword = "'красный',   зеленый"

    if (SEARCH_FULLTEXT) {
      // Вырезаем спец символы из поисковой фразы.
      $keyword = preg_replace('/[`~!#$%^*()=+\\\\|\\/\\[\\]{};:"\',<>?]+/', '', $keyword); //$keyword = "красный   зеленый"
      // Замена повторяющихся пробелов на на один.
      $keyword = preg_replace('/ +/', ' ', $keyword); //$keyword = "красный зеленый"
      // Обрамляем каждое слово в звездочки, для расширенного поиска.
      $keyword = str_replace(' ', '* +', $keyword); //$keyword = "красный* *зеленый"
      // Добавляем по краям звездочки.
      $keyword = '+' . $keyword . '*'; //$keyword = "*красный* *зеленый*"

      $sql = "
      SELECT distinct p.code, CONCAT(c.parent_url,c.url) AS category_url,
        p.url AS product_url, p.*, pv.product_id as variant_exist, pv.id as variant_id, rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`
      FROM  `" . PREFIX . "product` AS p
      LEFT JOIN  `" . PREFIX . "category` AS c ON c.id = p.cat_id
      LEFT JOIN  `" . PREFIX . "product_variant` AS pv ON p.id = pv.product_id";

      if (!$adminPanel) {
        $sql .=" LEFT JOIN (
        SELECT pv.product_id, SUM( pv.count ) AS varcount
        FROM  `" . PREFIX . "product_variant` AS pv
        GROUP BY pv.product_id
      ) AS temp ON p.id = temp.product_id";
      }

      $prod = new Models_Product();
      $fulltext = "";
      $sql .= " WHERE ";
      $match =
      " MATCH (
      p.`title` , p.`code`, p.`description` " . $fulltextInVar . " " . $fulltext . "
      )
      AGAINST (
      '" . $keyword . "'
      IN BOOLEAN
      MODE
      ) ";

      DB::query("SELECT id FROM `" . PREFIX . "product_variant` LIMIT 1");

      //Если есть варианты товаров то будеи искать и в них.
      if (DB::numRows(DB::query("SELECT id FROM `" . PREFIX . "product_variant` LIMIT 1"))) {
        $fulltextInVar = ', pv.`title_variant`, pv.`code` ';

      $match = "(".$match.
        " OR MATCH (pv.`title_variant`, pv.`code`)
        AGAINST (
        '" . $keyword . "'
        IN BOOLEAN
        MODE
        )) ";
      }

	  $sql .= $match;
      // Проверяем чтобы в вариантах была хотябы одна единица.
      if (!$adminPanel) {
	    if (MG::getSetting('printProdNullRem') == "true") {
          $sql .=" AND (temp.`varcount` > 0 OR temp.`varcount` < 0 OR p.count>0 OR p.count<0)";
		}
      }

      if ($onlyActive) {
        $sql .= ' AND p.`activity` = 1';
      }
    } else {

      $sql = "
       SELECT distinct p.id, CONCAT(c.parent_url,c.url) AS category_url,
         p.url AS product_url, p.*, pv.product_id as variant_exist, pv.id as variant_id, rate,(p.price_course + p.price_course * (IFNULL(rate,0))) as `price_course`, 
         p.currency_iso
       FROM  `" . PREFIX . "product` AS p
       LEFT JOIN  `" . PREFIX . "category` AS c ON c.id = p.cat_id
       LEFT JOIN  `" . PREFIX . "product_variant` AS pv ON p.id = pv.product_id";

      if (!$adminPanel) {
        $sql .=" LEFT JOIN (
         SELECT pv.product_id, SUM( pv.count ) AS varcount
         FROM  `" . PREFIX . "product_variant` AS pv
         GROUP BY pv.product_id
       ) AS temp ON p.id = temp.product_id";
      }

      $prod = new Models_Product();
      $fulltext = "";

      //Если есть варианты товаров то будеи искать и в них.
      if (DB::numRows(DB::query("SELECT id FROM `" . PREFIX . "product_variant` LIMIT 1"))) {

        $fulltextInVar = " OR
             pv.`title_variant` LIKE '%" . DB::quote($keyword, true) . "%'
           OR
             pv.`code` LIKE '%" . DB::quote($keyword, true) . "%'";
      }


      $sql .=
        " WHERE (
             p.`title` LIKE '%" . DB::quote($keyword, true) . "%'
           OR
             p.`code` LIKE '%" . DB::quote($keyword, true) . "%'
        " . $fulltextInVar .')';


      // Проверяем чтобы в вариантах была хотябы одна единица.
      if (!$adminPanel) {
	    if (MG::getSetting('printProdNullRem') == "true") {
        $sql .=" AND (temp.`varcount` > 0 OR temp.`varcount` < 0 OR p.count>0 OR p.count<0)";
		}
      }

      if ($onlyActive) {
        $sql .= ' AND p.`activity` = 1';
      }

    }


    $page = URL::get("page");
    $settings = MG::get('settings');

    //if ($mode=='groupBy') {
      $sql .= ' GROUP BY p.id' ;
    //}
    if ($allRows) {
      $sql .= ' LIMIT 15' ;
    }

    $navigator = new Navigator($sql, $page, $settings['countСatalogProduct'], $linkCount = 6, $allRows); // Определяем класс.

    $this->products = $navigator->getRowsSql();
    // добавим к полученым товарам их свойства
    $this->products = $this->addPropertyToProduct($this->products);
    $this->pager = $navigator->getPager();

    $result = array(
      'catalogItems' => $this->products,
      'pager' => $this->pager,
      'numRows' => $navigator->getNumRowsSql()
    );

    if (count($result['catalogItems']) > 0) {

      // упорядочивание списка найденых  продуктов
      // первыми в списке будут стоять те товары, у которых полностью совпала поисковая фраза
      // затем будут слова в начале которых встретилось совпадение
      // в конце слова в середине которых встретилось совпадение
      //
      //
      $keyword = str_replace('*', '', $keyword);
      $resultTemp = $result['catalogItems'];
      $prioritet0 = array();
      $prioritet1 = array();
      $prioritet2 = array();
      foreach ($resultTemp as $key => $item) {
        $title = mb_convert_case($item['title'], MB_CASE_LOWER, "UTF-8");
        $keyword = mb_convert_case($keyword, MB_CASE_LOWER, "UTF-8");
        $item['image_url'] = mgImageProductPath($item["image_url"], $item['id']);
        
        if (trim($title) == $keyword) {
        $prioritet0[] = $item;
          continue;
        }

        if (strpos($title, $keyword) === 0) {
            $prioritet1[] = $item;
          } else {
            $prioritet2[] = $item;
          }
        }

      $result['catalogItems'] = array_merge($prioritet0,  $prioritet1,$prioritet2);
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__ . "_" . __FUNCTION__, $result, $args);
  }

   /**
   * Выгружает содержание всего каталога в CSV файл.
   * $listProductId выгрузка выбранных товаров
   * @return array
   */
  public function exportToCsv($listProductId=array()) {
  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream;");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename=data.csv");
    header("Content-Transfer-Encoding: binary ");

    $csvText = '';
    $csvText .= '"Категория";"URL категории";"Товар";"Вариант";"Описание";"Цена";"URL";"Изображение";"Артикул";"Количество";"Активность";"Заголовок [SEO]";"Ключевые слова [SEO]";"Описание [SEO]";"Старая цена";"Рекомендуемый";"Новый";"Сортировка";"Вес";"Связанные артикулы";"Смежные категории";"Ссылка на товар";"Валюта";"Свойства";"id";'."\n";
    $import = new Import();
    $product = new Models_Product();
    $catalog = new Models_Catalog();

    Storage::$noCache = true;
    $page = 1;
    // получаем максимальное количество заказов, если выгрузка всего ассортимента
    if(empty($listProductId)){
      $maxCountPage = ceil($product->getProductsCount() / 500);    
    }else{
      $maxCountPage = ceil(count($listProductId) / 500);    
    }    
    $catalog->categoryId = MG::get('category')->getCategoryList(0);
    $catalog->categoryId[] = 0;
    $listId = implode(',', $listProductId);
    for ($page = 1; $page <= $maxCountPage; $page++) {
      
      URL::setQueryParametr("page", $page);
      
      if(empty($listProductId)){      
        $catalog->getList(500, true);
      }else{     
        $catalog->getListByUserFilter(500, ' p.id IN  ('.DB::quote($listId,1).')');        
      }
      foreach ($catalog->products as $row) {

        $parent = $row['category_url'];

        // Подставляем всесто URL названия разделов.
        $resultPath = '';
        $resultPathUrl = '';
        while ($parent) {
          $url = URL::parsePageUrl($parent);
          $parent = URL::parseParentUrl($parent);
          $parent = $parent != '/' ? $parent : '';
          $alreadyParentCat = MG::get('category')->getCategoryByUrl(
            $url, $parent
          );

          $resultPath = $alreadyParentCat['title'] . '/' . $resultPath;
          $resultPathUrl = $alreadyParentCat['url'] . '/' . $resultPathUrl;
        }


        $resultPath = trim($resultPath, '/');
        $resultPathUrl = trim($resultPathUrl, '/');

        $variants = $product->getVariants($row['id']);

        if (!empty($variants)) {
          foreach ($variants as $key => $variant) {
            foreach ($variant as $k => $v) {
              if( $k != 'sort' && $k != 'id'){
                $row[$k] = $v;
              }
            }
            $row['image'] = $variant['image'];
            $row['category_url'] = $resultPath;
            $row['category_full_url'] = $resultPathUrl;
            $row['real_price'] = $row['price'];
            $csvText .= $this->addToCsvLine($row, 1);
          }
        } else {
          $row['category_url'] = $resultPath;
          $row['category_full_url'] = $resultPathUrl;
          $csvText .= $this->addToCsvLine($row);
        }
      }
    }
    $csvText = substr($csvText, 0, -2); // удаляем последний символ '\n'
        
    $csvText = mb_convert_encoding($csvText, "WINDOWS-1251", "UTF-8");
    if(empty($listProductId)){
      echo $csvText;
      exit;
    } else{
      $date = date('m_d_Y_h_i_s');
      file_put_contents('data_csv_'.$date.'.csv', $csvText);
      $msg = 'data_csv_'.$date.'.csv';
    }
    return $msg;
  }

  /**
   * Добавляет продукт в CSV выгрузку.
   * @param type $row - продукт.
   * @param type $variant - есть ли варианты этого продукта.
   * @return string
   */
  public function addToCsvLine($row, $variant = false) {    
    
    $row['category_url'] = '"' . str_replace("\"", "\"\"", htmlspecialchars_decode($row['category_url'])) . '"';
    $row['category_full_url'] = '"' . str_replace("\"", "\"\"", $row['category_full_url']) . '"';
    $row['title'] = '"' . str_replace("\"", "\"\"", htmlspecialchars_decode($row['title'])) . '"';
    $row['description'] = '"' . str_replace("\"", "\"\"", $row['description']) . '"';
    $row['price'] = '"' . str_replace("\"", "\"\"", MG::numberDeFormat($row['real_price'])) . '"';
    $row['price'] = str_replace(".", "," ,$row['price']);
    $row['url'] = '"' . str_replace("\"", "\"\"", $row['url']) . '"';

    $row['image_url'] = '';
    if(!empty($row['images_product'])){
    foreach ($row['images_product'] as $key => $url ) {
      $param = '';
      if (!empty($row['images_alt'][$key])||!empty($row['images_title'][$key])) {
        $param = '[:param:][alt='.(!empty($row['images_alt'][$key]) ? $row['images_alt'][$key] : '').'][title='.(!empty($row['images_title'][$key]) ? $row['images_title'][$key] : '').']';
      }
      $row['image_url'] .= basename($url).$param.'|';
      }
      $row['image_url'] = substr($row['image_url'], 0, -1);
   //   $row['image_url'] = implode('|',$row['images_product']);
    }

    $row['code'] = '"' . str_replace("\"", "\"\"", $row['code']) . '"';
    $row['count'] = '"' . str_replace("\"", "\"\"", $row['count']) . '"';
    $row['activity'] = '"' . str_replace("\"", "\"\"", $row['activity']) . '"';
    $row['meta_title'] = '"' . str_replace("\"", "\"\"", htmlspecialchars_decode($row['meta_title'])) . '"';
    $row['meta_keywords'] = '"' . str_replace("\"", "\"\"", htmlspecialchars_decode($row['meta_keywords'])) . '"';
    $row['meta_desc'] = '"' . str_replace("\"", "\"\"", htmlspecialchars_decode($row['meta_desc'])) . '"';
    $row['old_price'] = '"' . str_replace("\"", "\"\"", MG::numberDeFormat($row['real_old_price'])) . '"';

    $row['old_price'] = ($row['real_old_price']!='"0"')?str_replace(".", "," ,$row['real_old_price']):'';
    $row['recommend'] = '"' . str_replace("\"", "\"\"", $row['recommend']) . '"';
    $row['new'] = '"' . str_replace("\"", "\"\"", $row['new']) . '"';
    $row['sort'] = '"' . str_replace("\"", "\"\"", $row['sort']) . '"';
    $row['description'] = str_replace("\r", "", $row['description']);
    $row['description'] = str_replace("\n", "", $row['description']);
    $row['meta_desc'] = str_replace("\r", "", $row['meta_desc']);
    $row['meta_desc'] = str_replace("\n", "", $row['meta_desc']);
    $row['related'] = '"' . str_replace("\"", "\"\"", $row['related']) . '"';
    $row['weight'] = str_replace(".", "," ,$row['weight']);
    $row['image_url'] = '"'.$row['image_url'].'"';
    //получаем строку со связанными продуктами
    // формируем строку с характеристиками
    $property = '';
    if (!empty($row['thisUserFields'])) {
      foreach ($row['thisUserFields'] as $item) {
        if ($item['type'] == 'string') {    
          $item['name'] = str_replace("&", "&amp;",  htmlspecialchars_decode($item['name']));
          $item['value'] = str_replace("&", "&amp;",  htmlspecialchars_decode($item['value']));
          $property .= '&' . $item['name'] . '=' . $item['value'];
        }
      }
    }
    $property = substr($property, 1);
    $row['property'] = $property;
    $row['property'] = str_replace("\r", "", $row['property']);
    $row['property'] = str_replace("\n", "", $row['property']);
    $row['property'] = '"' . str_replace("\"", "\"\"",$row['property']) . '"'; 


    $csvText = $row['category_url'] . ";" . $row['category_full_url'] . ";" .$row['title'] . ";";
    if ($variant) { 
      $var_image = '[:param:][src='.$row['image'].']';
      $row['title_variant'] .= $var_image;
      $csvText .= '"' . str_replace("\"", "\"\"", htmlspecialchars_decode($row['title_variant'])) . '";';
    } else {
      $csvText .= ";";
    }
    $csvText .= $row['description'] . ";" .
      $row['price'] . ";" .
      $row['url'] . ";" .
      $row['image_url'] . ";" .
      $row['code'] . ";" .
      $row['count'] . ";" .
      $row['activity'] . ";" .
      $row['meta_title'] . ";" .
      $row['meta_keywords'] . ";" .
      $row['meta_desc'] . ";" .
      $row['old_price'] . ";" .
      $row['recommend'] . ";" .
      $row['new'] . ";" .
      $row['sort'] . ";" .
      $row['weight'] . ";" .
      $row['related'] . ";" .
      $row['inside_cat'] . ";" .
      $row['link_electro'] . ";" .
      $row['currency_iso'] . ";" .
      $row['property'] . ";" .
      $row['id'] . ";\n";

    return $csvText;
  }

  /**
   * Получает массив категорий.
   * @return mixed - ассоциативный массив id => категория.
   */
  public function getCategoryArray() {
    $res = DB::query('
      SELECT *
      FROM `' . PREFIX . 'category`');
    while ($row = DB::fetchAssoc($res)) {
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Получает минимальную цену из всех сстоимостей продуктов.
   * @return float.
   */
  public function getMinPrice() {
    $res = DB::query('SELECT MIN(`price_course`) as price FROM `' . PREFIX . 'product`');
    if ($row = DB::fetchObject($res)) {
      $result = $row->price;
    }
    return $result;
  }

  /**
   * Получает максимальную цену из всех стоимостей продуктов.
   * @return float.
   */
  public function getMaxPrice() {
    $res = DB::query('SELECT MAX(`price_course`) as price FROM `' . PREFIX . 'product`');
    if ($row = DB::fetchObject($res)) {
      $result = $row->price;
    }
    return $result;
  }

  /**
   * Возвращает пример загружаемого каталога.
   * @return array
   */
  public function getExampleCSV() {

    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream;");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename=data.csv");
    header("Content-Transfer-Encoding: binary ");

   $csvText .='"Категория";"URL категории";"Товар";"Вариант";"Описание";"Цена";"URL";"Изображение";"Артикул";"Количество";"Активность";"Заголовок [SEO]";"Ключевые слова [SEO]";"Описание [SEO]";"Старая цена";"Рекомендуемый";"Новый";"Сортировка";"Вес";"Связанные артикулы";"Смежные категории";"Ссылка на товар";"Валюта";"Свойства";
"Компьютерная техника/Компьютеры и ноутбуки/Ноутбуки";"";"Ноутбук Dell Inspiron N411Z";;"<p>Компания Dell сделала из Inspiron N411Z поистине уникальный продукт. Он отличается малым весом и толщиной, а также впечатляющей автономностью, и при этом стоит совсем недорого. Он принадлежит к потребительской серии Inspiron, включающей в себя аппараты начального и среднего уровня с красочным дизайном.</p>";"19000";"noutbuk-dell-inspiron-n411z";"noutbuk-Dell-Inspiron-N411Z.png[:param:][alt=ноутбук dell][title=ноутбук dell]|noutbuk-Dell-Inspiron-N411Z-oneside.png[:param:][alt=Ноутбук Dell][title=Ноутбук Dell]|noutbuk-dell-verh.png[:param:][alt=ноутбук Dell черного цвета][title=ноутбук Dell черного цвета]";"1000A";"9";"1";"Ноутбук Dell Inspiron N411Z";"Ноутбук, легкий ноутбук, Ноутбук Dell Inspiron N411Z";"Компания Dell сделала из Inspiron N411Z поистине уникальный продукт. Он отличается малым весом и толщиной, а также впечатляющей автономностью, и при этом стоит совсем недорого. Он принадлежит к потребительской серии Inspiron, включающей в себя аппараты начального и среднего уровня с красочным дизайном.";;"1";"0";"16";2,27;"2009M,1086S,5609R,0390FW";;;RUR;Бренд=Dell&Размер экрана (дюйм)=14"&Операционная система=Windows 7&Память (Гб)=532&Разрешение (px)=1366x768&Процессор=Core i3 1400 МГц&Оперативная память (Гб)=4&Видео=Intel HD Graphics 3000&Объем видеопамяти (Гб)=1
"Компьютерная техника/Компьютеры и ноутбуки/Ноутбуки";"";"Ноутбук ASUS X555LD";;"<p>Ноутбук Asus X555LD – это высокопроизводительное универсальное устройство с эргономичным дизайном. Оно принесет немало пользы на рабочем месте, по желанию владельца может превратиться в мультимедийный центр, а благодаря дискретной видеокарте способно исполнить роль игрового ноутбука начального уровня.</p>";"27900";"noutbuk-asus-x555ld";"noutbuk-ASUS-X555LD.png[:param:][alt=ноутбук asus][title=ноутбук asus]|asus-X555LD-verh.png[:param:][alt=ноутбук asus][title=ноутбук asus]";"1001A";"5";"1";"Ноутбук ASUS X555LD";"Ноутбук, ASUS X555LD, ноутбук для работы, недорогой ноутбук";"Ноутбук Asus X555LD– это высокопроизводительное универсальное устройство с эргономичным дизайном. Оно принесет немало пользы на рабочем месте, по желанию владельца может превратиться в мультимедийный центр, а благодаря дискретной видеокарте способно исполнить роль игрового ноутбука начального уровня.";;"0";"0";"17";2;"2980M,1005A,1078M,10954M";;;RUR;Бренд=ASUS&Размер экрана (дюйм)=15.6"&Операционная система=Windows 8&Память (Гб)=750&Разрешение (px)=1366x768&Процессор=Core i5 1700 МГц&Оперативная память (Гб)=8&Видео=NVIDIA GeForce 820M&Объем видеопамяти (Гб)=2
"Компьютерная техника/Компьютеры и ноутбуки/Ноутбуки";"";"Ноутбук Lenovo G700";;"<p>Ноутбук Lenovo G700 представляет собой стильную 17-дюймовую модель. Устройство имеет высокую производительность, снабжено емким жестким диском и веб-камерой. Девайс позволит с легкостью подключаться к Интернету вне дома и офиса, а также осуществлять обмен данными. Портативный компьютер подойдет для решения повседневных задач, вечернего отдыха за просмотром фильма, общения с друзьями в социальных сетях или в видеочатах.</p>";"33500";"noutbuk-lenovo-g700";"noutbuk-Dell-Inspiron-5547.png[:param:][alt=ноутбук lenovo][title=ноутбук lenovo]";"1003A";"-1";"1";"Ноутбук Lenovo G700";"Ноутбук Lenovo G700, мощный ноутбук, купить ноутбук lenovo";"Ноутбук Lenovo G700 представляет собой стильную 17-дюймовую модель. Устройство имеет высокую производительность, снабжено емким жестким диском и веб-камерой. Девайс позволит с легкостью подключаться к Интернету вне дома и офиса, а также осуществлять обмен данными. Портативный компьютер подойдет для решения повседневных задач, вечернего отдыха за просмотром фильма, общения с друзьями в социальных сетях или в видеочатах.";;"0";"1";"18";2,9;"1001A,6790F,1006A,2009M";;;RUR;Бренд=Lenovo&Размер экрана (дюйм)=17.3"&Операционная система=Windows 8&Память (Гб)=1000 Гб&Разрешение (px)=&Процессор=Core i7 2200 МГц&Оперативная память (Гб)=4&Видео=&Объем видеопамяти (Гб)=
"Компьютерная техника/Компьютеры и ноутбуки/Планшеты";"";"Samsung SM-T531 Galaxy Tab 4";без чехла;"<p>Стильный и практичный дизайн: в планшете Samsung GALAXY Tab 4 10.1"" есть все, что вам необходимо.<br />Большой 10.1-дюймовый экран формата 16:10 с разрешением WXGA (1280x800) обеспечивает яркое и четкое изображение. Поддержка настоящей многозадачности — до 2 окон на одном экране — дает возможность одновременно просматривать видео, проверять почту, совершать покупки в онлайн-магазинах и чатиться с друзьями, не тратя лишнее время на переключение между приложениями. Четырехъядерный 1.2-ГГц процессор обеспечивает плавную работу всех приложений.</p><p>По специальной цене чехол-книжка G-Case Slim Premium для Samsung Galaxy Tab 4 10.1"" – стильный кожаный чехол на все случаи жизни. Прочный эргономичный корпус и гибкая обложка-трансформер не ограничивают свободу действий. Чехол-книжка G-Case Slim Premium для Samsung Galaxy Tab 4 10.1"" станет устойчивой подставкой и надежной защитой модели.</p>";"14990";"samsung-sm-t531-galaxy-tab-4";"Samsung-SM-T531-Galaxy-Tab-4.png[:param:][alt=планшет][title=планшет]|samsung-t531-galaxy-tab-back.jpg[:param:][alt=планшет черный][title=планшет черный]";"10034B";"-1";"1";" Samsung SM-T531 Galaxy Tab 4";" Samsung SM-T531 Galaxy Tab 4, планшет, планшетный компьютер, дешево планшет";"Планшет Samsung SM-T531 Galaxy Tab 4. Большой экран формата обеспечивает яркое и четкое изображение. Возможность просматривать видео, проверять почту, совершать покупки в онлайн-магазинах и чатиться с друзьями. П";"19000";"0";"0";"1";0;"0390FW,1089К,1004С";;;RUR;Бренд=Samsung&Размер экрана (дюйм)=10.1"&Время работы (ч)=10&Операционная система=Android&Цвет=Черный&Интернет=3G, Wi-Fi&Фотокамера (Мп)=1.3&Память (Гб)=16&Разрешение (px)=1280x800&Процессор=1200 МГц&Оперативная память (Гб)=1.5
"Компьютерная техника/Компьютеры и ноутбуки/Планшеты";"";"Samsung SM-T531 Galaxy Tab 4";с чехлом;"<p>Стильный и практичный дизайн: в планшете Samsung GALAXY Tab 4 10.1"" есть все, что вам необходимо.<br />Большой 10.1-дюймовый экран формата 16:10 с разрешением WXGA (1280x800) обеспечивает яркое и четкое изображение. Поддержка настоящей многозадачности — до 2 окон на одном экране — дает возможность одновременно просматривать видео, проверять почту, совершать покупки в онлайн-магазинах и чатиться с друзьями, не тратя лишнее время на переключение между приложениями. Четырехъядерный 1.2-ГГц процессор обеспечивает плавную работу всех приложений.</p><p>По специальной цене чехол-книжка G-Case Slim Premium для Samsung Galaxy Tab 4 10.1"" – стильный кожаный чехол на все случаи жизни. Прочный эргономичный корпус и гибкая обложка-трансформер не ограничивают свободу действий. Чехол-книжка G-Case Slim Premium для Samsung Galaxy Tab 4 10.1"" станет устойчивой подставкой и надежной защитой модели.</p>";"14990";"samsung-sm-t531-galaxy-tab-4";"Samsung-SM-T531-Galaxy-Tab-4.png[:param:][alt=планшет][title=планшет]|samsung-t531-galaxy-tab-back.jpg[alt=планшет черный][title=планшет черный]";"10034Bb";"-1";"1";" Samsung SM-T531 Galaxy Tab 4";" Samsung SM-T531 Galaxy Tab 4, планшет, планшетный компьютер, дешево планшет";"Планшет Samsung SM-T531 Galaxy Tab 4. Большой экран формата обеспечивает яркое и четкое изображение. Возможность просматривать видео, проверять почту, совершать покупки в онлайн-магазинах и чатиться с друзьями. П";"";"0";"0";"2";0;"0390FW,1089К,1004С";;;RUR;Бренд=Samsung&Размер экрана (дюйм)=10.1"&Время работы (ч)=10&Операционная система=Android&Цвет=Черный&Интернет=3G, Wi-Fi&Фотокамера (Мп)=1.3&Память (Гб)=16&Разрешение (px)=1280x800&Процессор=1200 МГц&Оперативная память (Гб)=1.5
"Компьютерная техника/Компьютеры и ноутбуки/Планшеты";"";"Планшет Samsung Galaxy Tab 3 T3110";Белый;"<p>Планшетный компьютер Samsung Galaxy Tab 3 T3110 16 Gb, работающий под управлением операционной системы Google Android 4.2, идеален для просмотра мультимедиа. Он сочетает в себе невероятную производительность и яркий восьмидюймовый сенсорный экран. </p><p></p>";"13000";"planshet-samsung-galaxy-tab-3-t3110";planshet-Samsung-Galaxy-Tab-3-T31101414880258.png|samsung-black-brown-back1413889894.png|samsung-black-brown14138896591414885757.png;"10024Bw";"-1";"1";"Планшет Samsung Galaxy Tab 3 T3110";"Планшет, планшет Samsung Galaxy Tab 3 T3110, планшет дешево";"Планшетный компьютер Samsung Galaxy Tab 3 T3110 16 Gb, работающий под управлением операционной системы Google Android 4.2, идеален для просмотра мультимедиа. Он сочетает в себе невероятную производительность и яркий восьмидюймовый сенсорный экран. Разные цвета.";"";"0";"0";"1";0,314;"390K,1078M,6790F,1004C";;;RUR;Бренд=Samsung&Размер экрана (дюйм)=8"&Время работы (ч)=11&Операционная система=Android&Цвет=Белый&Интернет=3G&Фотокамера (Мп)=5.0&Память (Гб)=16&Разрешение (px)=1280x800&Процессор=1500 МГц&Оперативная память (Гб)=2
"Компьютерная техника/Компьютеры и ноутбуки/Планшеты";"";"Планшет Samsung Galaxy Tab 3 T3110";Черный;"<p>Планшетный компьютер Samsung Galaxy Tab 3 T3110 16 Gb, работающий под управлением операционной системы Google Android 4.2, идеален для просмотра мультимедиа. Он сочетает в себе невероятную производительность и яркий восьмидюймовый сенсорный экран. </p><p></p>";"13000";"planshet-samsung-galaxy-tab-3-t3110";planshet-Samsung-Galaxy-Tab-3-T31101414880258.png|samsung-black-brown-back1413889894.png|samsung-black-brown14138896591414885757.png;"10024Bbl";"-1";"1";"Планшет Samsung Galaxy Tab 3 T3110";"Планшет, планшет Samsung Galaxy Tab 3 T3110, планшет дешево";"Планшетный компьютер Samsung Galaxy Tab 3 T3110 16 Gb, работающий под управлением операционной системы Google Android 4.2, идеален для просмотра мультимедиа. Он сочетает в себе невероятную производительность и яркий восьмидюймовый сенсорный экран. Разные цвета.";"";"0";"0";"2";0,314;"390K,1078M,6790F,1004C";;;RUR;Бренд=Samsung&Размер экрана (дюйм)=8"&Время работы (ч)=11&Операционная система=Android&Цвет=Белый&Интернет=3G&Фотокамера (Мп)=5.0&Память (Гб)=16&Разрешение (px)=1280x800&Процессор=1500 МГц&Оперативная память (Гб)=2
"Компьютерная техника/Компьютеры и ноутбуки/Планшеты";"";"Планшет Samsung Galaxy Tab 3 T3110";Коричневый;"<p>Планшетный компьютер Samsung Galaxy Tab 3 T3110 16 Gb, работающий под управлением операционной системы Google Android 4.2, идеален для просмотра мультимедиа. Он сочетает в себе невероятную производительность и яркий восьмидюймовый сенсорный экран. </p><p></p>";"13000";"planshet-samsung-galaxy-tab-3-t3110";planshet-Samsung-Galaxy-Tab-3-T31101414880258.png|samsung-black-brown-back1413889894.png|samsung-black-brown14138896591414885757.png;"10024Bbr";"-1";"1";"Планшет Samsung Galaxy Tab 3 T3110";"Планшет, планшет Samsung Galaxy Tab 3 T3110, планшет дешево";"Планшетный компьютер Samsung Galaxy Tab 3 T3110 16 Gb, работающий под управлением операционной системы Google Android 4.2, идеален для просмотра мультимедиа. Он сочетает в себе невероятную производительность и яркий восьмидюймовый сенсорный экран. Разные цвета.";"";"0";"0";"3";0,314;"390K,1078M,6790F,1004C";;;RUR;Бренд=Samsung&Размер экрана (дюйм)=8"&Время работы (ч)=11&Операционная система=Android&Цвет=Белый&Интернет=3G&Фотокамера (Мп)=5.0&Память (Гб)=16&Разрешение (px)=1280x800&Процессор=1500 МГц&Оперативная память (Гб)=2
"Компьютерная техника/Компьютеры и ноутбуки/Ноутбуки";"";"Ноутбук Samsung ATIV Book 8 880Z5E";;"<p>При первом знакомстве с ноутбуком Samsung 880Z5E складывается впечатление о нем, как об ультрабуке. Этот девайс получил сенсорный дисплей, тонкий (21 мм) корпус, несъемный аккумулятор и отсутствие привода. Остальные компоненты аппарата дают понять, что перед нами - производительный домашний ноутбук. Мощный процессор и большой объем памяти подойдет как для работы, учебы, так и для мощных игр.</p>";"44500";"samsung-ativ-book-8-880z5e";noutbuk-Samsung-ATIV-Book-8-880Z5E.png;"10105A";"1";"1";"Ноутбук Samsung ATIV Book 8 880Z5E";"Ноутбук, Samsung ATIV Book, ноутбук для работы";"При первом знакомстве с ноутбуком Samsung 880Z5E складывается впечатление о нем, как об ультрабуке. Этот девайс получил сенсорный дисплей, тонкий (21 мм) корпус, несъемный аккумулятор и отсутствие привода. Остальные компоненты аппарата дают понять, что перед нами - производительный домашний ноутбук. Мощный процессор и большой объем памяти подойдет как для работы, учебы, так и для мощных игр.";;"0";"0";"21";2,54;"2060B,2340v,1086S";;;RUR;Бренд=Samsung&Размер экрана (дюйм)=15.6"&Операционная система=Windows 8&Память (Гб)=1000&Разрешение (px)=&Процессор=Core i7 2400 МГц&Оперативная память (Гб)=8&Видео=AMD Radeon HD 8870M&Объем видеопамяти (Гб)=
"Компьютерная техника/Устройства ввода/Клавиатуры";"";"Клавиатура DEFENDER Warhead GK-1300L";;"<p>Общие: Соединение — Проводное,<br />Тип — Игровая,<br />Интерфейс — USB<br />Рабочая область: Подсветка клавиш — Есть,<br />Количество дополнительных клавиш — 8<br />Цвет: Черный</p>";"1390";"klaviatura-defender-warhead-gk-1300l";klaviatura-DEFENDER-Warhead-GK-1300L.png;"340К";"-1";"1";"Клавиатура DEFENDER Warhead GK-1300L";"Клавиатура DEFENDER Warhead, клавиатура";"Игровая удобная Клавиатура DEFENDER Warhead GK-1300L черного цвета.";;"1";"0";"24";0;"1001A,10954M,20008V";;;RUR;Бренд=DEFENDER&Страна производитель=Тайвань&Соединение=проводное
"Компьютерная техника/Компьютеры и ноутбуки/Планшеты";"";"Планшет Asus Transformer Book T100TA-DK003H + клавиатура";;"<p>Планшет Asus Transformer Book T100TA-DK003H + клавиатура предоставит своему владельцу новый уровень комфорта при просмотре интернет-страниц, текстовых документов, почты, фотографий и видеороликов, а также при загрузке игр и графического контента.</p>";"17990";"planshet-asus-transformer-book-t100ta-dk003h-klaviatura";planshet-Asus-Transformer-Book-T100TA-DK003H-+-klaviatura1414880701.png;"1004C";"-1";"1";"Планшет Asus Transformer Book T100TA-DK003H + клавиатура ";"Планшет Asus Transformer, Book T100TA-DK003H";"Планшет Asus Transformer Book T100TA-DK003H + клавиатура предоставит своему владельцу новый уровень комфорта при просмотре интернет-страниц, текстовых документов, почты, фотографий и видеороликов, а также при загрузке игр и графического контента.";;"1";"1";"25";0,55;"5609R,0390FW,20008V";;;RUR;Бренд=ASUS&Размер экрана (дюйм)=10.1"&Время работы (ч)=12&Операционная система=Windows 8&Цвет=Серый&Интернет=&Фотокамера (Мп)=5&Память (Гб)=64&Разрешение (px)=1366x768&Процессор=1330 МГц&Оперативная память (Гб)=2
"Компьютерная техника/Компьютеры и ноутбуки/Планшеты";"";"Планшет Qumo Sirius 1002W + Клавиатура-чехол";;"<p>Планшет Qumo Sirius 1002W – это мобильное устройство-трансформер, способное стать хорошей альтернативой ноутбуку. Четырехъядерный процессор Intel® Atom™ Z3735D с тактовой частотой 1,83 ГГц имеет высокую производительность и в то же время чрезвычайно экономно расходует электроэнергию, что положительно сказывается на показателях автономной работы планшета. Трансформация. Qumo Sirius 1002W снабжен чехлом-клавиатурой, который не только защищает планшет от повреждений, но и значительно расширяет его возможности. Если же понадобится, владелец может легко отсоединить клавиатуру и использовать устройство в режиме обычного планшета.</p>";"12550";"planshet-qumo-sirius-1002w-klaviatura";planshet-Qumo-Sirius-1002W-+-klaviatura-chehol.png;"1054C";"-1";"1";"Планшет Qumo Sirius 1002W + Клавиатура-чехол";"Планшет, Qumo Sirius 1002W, планшет и клавиатура-чехол";"Планшет Qumo Sirius 1002W – это мобильное устройство-трансформер, способное стать хорошей альтернативой ноутбуку. ";;"0";"1";"26";0,613;"2009M,290K,1003A,4560k,2980M";;;RUR;Бренд=Qumo&Размер экрана (дюйм)=10.1"&Время работы (ч)=9&Операционная система=Windows 8&Цвет=Черный&Интернет=3G&Фотокамера (Мп)=2.0&Память (Гб)=32&Разрешение (px)=1280x800 &Процессор=1830 Мгц, 4 ядра&Оперативная память (Гб)=2
"Компьютерная техника/Компьютеры и ноутбуки/Ноутбуки";"";"Ноутбук Lenovo IdeaPad Flex 2 14";;"<p>Ноутбук Lenovo IdeaPad Flex 2 14 — портативный компьютер-трансформер, с которым можно работать в двух режимах: консоль и лэптоп. С сенсорными приложениями или в поездках удобнее работать, отсоединив клавиатурный блок и используя режим консоли. Для ввода текста, работы с таблицами или графикой больше подойдет режим лептопа</p>";"27990";"noutbuk-lenovo-ideapad-flex-2-14";noutbuk-Lenovo-G700.png|lenovo-ideapad-2.png|lenovo-ideapad-back.png;"1005A";"-1";"1";"Ноутбук Lenovo IdeaPad Flex 2 14";"Ноутбук Lenovo IdeaPad Flex 2 14, ноутбук-трансформер";"Ноутбук Lenovo IdeaPad Flex 2 14 — портативный компьютер-трансформер, с которым можно работать в двух режимах: консоль и лэптоп. С сенсорными приложениями или в поездках удобнее работать, отсоединив клавиатурный блок и используя режим консоли. Для ввода текста, работы с таблицами или графикой больше подойдет режим лептопа. ";"30000";"0";"1";"27";0;"6790F,1078M,20008V,10954M";;;RUR;Бренд=Lenovo&Размер экрана (дюйм)=14"&Операционная система=Windows 8&Память (Гб)=508&Разрешение (px)=1366x768&Процессор=Intel Core i3 4030U 1900 МГц&Оперативная память (Гб)=4&Видео=nVidia GT 820M&Объем видеопамяти (Гб)=2
"Компьютерная техника/Компьютеры и ноутбуки/Ноутбуки";"";"Ноутбук Dell Inspiron 5547";;"<p>Ноутбук Dell Inspiron 5547  управляется процессором последнего поколения Core i5, который в сочетании с высокопроизводительной видеокартой может решать большинство поставленных пользователем задач. Этот ноутбук с равным успехом справится с рутинной офисной работой, оформлением документации, подготовкой к учебе, воспроизведением видео, обработкой изображений и видеофайлов.</p>";"21900";"noutbuk-dell-inspiron-5547";noutbuk-Dell-Inspiron-N411Z1414878901.png|noutbuk-dell-verh.jpg;"10002A";"2";"1";"Ноутбук Dell Inspiron 5547 ";"Ноутбук Dell Inspiron 5547 , ноутбук";"Ноутбук Dell Inspiron 5547  управляется процессором последнего поколения Core i5, который в сочетании с высокопроизводительной видеокартой может решать большинство поставленных пользователем задач. Этот ноутбук с равным успехом справится с рутинной офисной работой, оформлением документации, подготовкой к учебе, воспроизведением видео, обработкой изображений и видеофайлов.";"25000";"0";"0";"28";2,18;"1000A,1086S,2340v,0390FW";;;RUR;Бренд=Dell&Размер экрана (дюйм)=15.6"&Операционная система=Linux&Память (Гб)=500&Разрешение (px)=&Процессор=Core i5 1700 МГц&Оперативная память (Гб)=4&Видео=&Объем видеопамяти (Гб)=
"Компьютерная техника/Компьютеры и ноутбуки/Ноутбуки";"";"Ноутбук Sony VAIO Tap 11 SVT1122M2R";;"<p>Ноутбук Sony VAIO Tap 11 SVT1122M2R представляет собой планшет, изменяющий свой форм-фактор с помощью отсоединяемой магнитной клавиатуры. Деловые задачи требуют наличия клавиатуры – написание писем, рецензий, составление отчетов, актов и работа с офисными приложениями. А для творческих людей особое удобство обеспечит планшетный форм-фактор, когда достаточно взять электронное перо, сделать наброски и уделить особое внимание деталям.<br />Отличная мощность. Модель управляется современной операционной системой Microsoft Windows 8.1 с продуманным комфортным пользовательским интерфейсом. Благодаря мощности двухъядерного процессора Intel® Core™ i3 4020Y с тактовой частотой 1,5 ГГц и встроенной видеокарте Intel HD Graphics 4200 компьютер отлично обрабатывает практически любые по сложности задачи.</p>";"39990";"noutbuk-sony-vaio-tap-11-svt1122m2r";noutbuk-Sony-VAIO-Tap-11-SVT1122M2R.png;"1006A";"-1";"1";"Ноутбук Sony VAIO Tap 11 SVT1122M2R ";"Ноутбук Sony VAIO Tap 11 SVT1122M2R, ноутбук-трансформер, ";"Ноутбук Sony VAIO Tap 11 SVT1122M2R представляет собой планшет, изменяющий свой форм-фактор с помощью отсоединяемой магнитной клавиатуры. Деловые задачи требуют наличия клавиатуры – написание писем, рецензий, составление отчетов, актов и работа с офисными приложениями. А для творческих людей особое удобство обеспечит планшетный форм-фактор, когда достаточно взять электронное перо, сделать наброски и уделить особое внимание деталям.";;"1";"1";"29";0;"0390FW,20008V,1054C,1004C";;;RUR;Бренд=Sony&Размер экрана (дюйм)=11.6&Операционная система=Windows 8&Память (Гб)=128&Разрешение (px)=1920х1080&Процессор=Core i3 4020Y&Оперативная память (Гб)=4&Видео=Intel HD Graphics 4200&Объем видеопамяти (Гб)=
"Компьютерная техника/Компьютеры и ноутбуки/Планшеты";"";"Планшет Apple iPad Air";;"<p>Планшет Apple iPad Air 32Gb Wi-Fi + Cellular – невероятно тонкий и легкий. При этом он по всем параметрам обходит своих предшественников. 64-битный процессор A7 и сопроцессор движений M7, передовые технологии беспроводной связи, сотни тысяч приложений на каждый день – iPad Air предлагает больше возможностей, чем можно себе представить.</p><p></p>";"25000";"planshet-apple-ipad-air";planshet-Apple-iPad-Air.png|apple-ipad-top.jpg;"1007D";"-1";"1";"Планшет Apple iPad Air";"Планшет Apple iPad Air, ";"Планшет Apple iPad Air 32Gb Wi-Fi + Cellular – невероятно тонкий и легкий. При этом он по всем параметрам обходит своих предшественников. 64-битный процессор A7 и сопроцессор движений M7, передовые технологии беспроводной связи, сотни тысяч приложений на каждый день – iPad Air предлагает больше возможностей, чем можно себе представить.";"28990";"0";"0";"30";0,478;"460Kc,390K,2004F0b";;;RUR;Бренд=Apple&Размер экрана (дюйм)=9.7"&Время работы (ч)=10&Операционная система=iOS 7&Цвет=Белый&Интернет=3G, 4G&Фотокамера (Мп)=5.0&Память (Гб)=32&Разрешение (px)=2048x1536&Процессор=1400 МГц&Оперативная память (Гб)=1
"Компьютерная техника/Устройства ввода/Клавиатуры";"";"Клавиатура QUMO Dragon War Desert Eagle GK-001";;"<p>Общие: Соединение — Проводное,<br />Тип — Игровая,<br />Интерфейс — USB<br />Рабочая область: Количество клавиш — 104,<br />Количество дополнительных клавиш — 10<br />Цвет: черный</p>";"1090";"klaviatura-qumo-dragon-war-desert-eagle-gk-001";klaviatura-QUMO-Dragon-War-Desert-Eagle-GK-001.png;"390K";"-1";"1";"Клавиатура QUMO Dragon War Desert Eagle GK-001";"Клавиатура QUMO Dragon War Desert Eagle GK-001";"Игровая крутая Клавиатура QUMO Dragon War Desert Eagle GK-001 с дополнительными клавишами.";"1390";"1";"1";"32";0;"10954M,1054C,1078M";;;RUR;Бренд=Qumo&Страна производитель=США&Соединение=проводное
"Компьютерная техника/Компьютеры и ноутбуки/Планшеты";"";"Планшет Samsung Galaxy Tab S 8.4 SM-T700";;"<p>Планшет Samsung Galaxy Tab S 8.4 SM-T700 - мощная многозадачная модель с Super AMOLED-экраном, мощной восьмимегапиксельной камерой, GPS-навигатором и свежей операционной системой Google Android 4.4. Она базируется на восьмиядерном процессоре Samsung Exynos Octa 5420, который обеспечивает быструю работу в любых приложениях.</p><p></p>";"19990";"planshet-samsung-galaxy-tab-s-8-4-sm-t700";planshet-Samsung-Galaxy-Tab-S-8.4-SM-T700.png|samsung-t531-galaxy-tab-back1414885850.png;"2060B";"-1";"1";"Планшет Samsung Galaxy Tab S 8.4 SM-T700";"Планшет Samsung Galaxy Tab S 8.4 SM-T700";"Планшет Samsung Galaxy Tab S 8.4 SM-T700 - мощная многозадачная модель с Super AMOLED-экраном, мощной восьмимегапиксельной камерой, GPS-навигатором и свежей операционной системой Google Android 4.4. Она базируется на восьмиядерном процессоре Samsung Exynos Octa 5420, который обеспечивает быструю работу в любых приложениях.";;"0";"0";"33";0,3;"1905С,1089К,6790F,4560k";;;RUR;Бренд=Samsung&Размер экрана (дюйм)=8.4"&Время работы (ч)=7&Операционная система=Android&Цвет=&Интернет=&Фотокамера (Мп)=8.0&Память (Гб)=128&Разрешение (px)=2560x1600&Процессор=1900 МГц&Оперативная память (Гб)=3
"Компьютерная техника/Компьютеры и ноутбуки/Планшеты";"";"Чехол-книжка G-Case Slim Premium для Samsung Galaxy Tab 4";;"<p>Чехол-книжка G-Case Slim Premium для Samsung Galaxy Tab 4 10.1"" – стильный кожаный чехол на все случаи жизни. Прочный эргономичный корпус и гибкая обложка-трансформер не ограничивают свободу действий. Чехол-книжка G-Case Slim Premium для Samsung Galaxy Tab 4 10.1"" станет устойчивой подставкой и надежной защитой модели.</p>";"1090";"chehol-knijka-g-case-slim-premium-dlya-samsung-galaxy-tab-4";chehol-knijka-G-Case-Slim-Premium-dlya-Samsung-Galaxy-Tab.png;"1004С";"-1";"1";"Чехол-книжка G-Case Slim Premium для Samsung Galaxy Tab 4";"Чехол-книжка, G-Case Slim Premium, для Samsung Galaxy Tab 4,  чехол";"Чехол-книжка G-Case Slim Premium для Samsung Galaxy Tab 4 10.1"" – стильный кожаный чехол на все случаи жизни. ";"1190";"0";"0";"34";0;"1054C,290K,10034B";;;RUR;Бренд=G-Case&Размер экрана (дюйм)=10.1"&Время работы (ч)=&Операционная система=&Цвет=Черный&Интернет=&Фотокамера (Мп)=&Память (Гб)=&Разрешение (px)=&Процессор=&Оперативная память (Гб)=';

    echo iconv("UTF-8", "WINDOWS-1251", $csvText);
    exit;
  }


    /**
   * Возвращает пример сым файла для обновления цен товаров.
   * @return array
   */
  public function getExampleCsvUpdate() {

    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream;");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename=data.csv");
    header("Content-Transfer-Encoding: binary ");

    $csvText .='"Артикул";"Цена";"Старая цена";"Количество";"Активность"
1000A;39000;;9;1
1001A;27900;;0;1
1003A;33500;;-1;1
10034B;14990;19000;-1;1
10034Bb;14990;;0;1
10024Bw;13000;;-1;1
10024Bbl;13000;;-1;1
10024Bbr;13000;;-1;1
10105A;44500;;1;1
340К;1390;;0;1
1004C;17990;;-1;1
1054C;12550;;-1;1
1005A;27990;30000;6;1
10002A;21900;25000;2;1
1006A;39990;;-1;1
1007D;25000;28990;7;1
390K;1090;1390;-1;1
2060B;19990;;-1;1
1004С;1090;1190;-1;1';
    echo iconv("UTF-8", "WINDOWS-1251", $csvText);
    exit;
  }




  /**
   * Метод для обработки фильтрации товаров в каталоге
   * @return .
   */
  public function filterPublic($noneAjax = true, $onlyInCount = false, $onlyActive=true, $sortFields = array(
      'price_course|-1'=>'цене, сначала недорогие',
      'price_course|1'=>'цене, сначала дорогие',      
      'id|1'=>'новизне',
      'count_buy|1'=>'популярности',
      'recommend|1'=>'сначала рекомендуемые',
      'new|1'=>'сначала новинки',
      'old_price|1'=>'сначала распродажа',
      'sort|1'=>'порядку',
      'count|1'=>'наличию',
       ),$baseSort = 'sort|1') {
      
    
    $orderBy = strtolower(trim(FILTER_SORT));   
    
    $compareArray = array(
      "sort|desc" => 'sort|1',
      "sort|asc" => 'sort|-1',
      "price_course|asc" => 'price_course|-1',
      "price_course|desc" => 'price_course|1',
      "id|desc" => 'id|1',
      "count_buy|desc" => 'count_buy|1',
      "recommend|desc" => 'recommend|1',
      "new|desc" => 'new|1',
      "old_price|desc" => 'old_price|1',
      "count|desc" =>'count|1'
    );
    
    $baseSort = $compareArray[$orderBy]?$compareArray[$orderBy]:'sort|1';
    
    $newSortFields[$baseSort] = $sortFields[$baseSort];
    unset($sortFields[$baseSort]);      
    $sortFields = array_merge($newSortFields,$sortFields);
    
    $lang = MG::get('lang');
    $model = new Models_Catalog;
    $catalog = array();

    if(!empty($_REQUEST['insideCat']) && $_REQUEST['insideCat']==="false"){
      $this->categoryId = array(end($this->categoryId));
    }
    
    $currentCategoryId = $this->currentCategory['id'] ? $this->currentCategory['id'] : 0;
    $where = '';
    
    if(!URL::isSection('mg-admin')){
      $where .= ' p.activity = 1 ';   
    
      if(MG::getSetting('printProdNullRem') == "true"){
        $where .= ' AND count != 0 ';
      }
    }
    
    $catIds = implode(',', $this->categoryId);
        
    if (!empty($catIds)||$catIds === 0) {             
      $where1 = ' p.cat_id IN (' . $catIds . ') or FIND_IN_SET(' . $currentCategoryId . ',p.`inside_cat`)';
      $rule1 = ' cat_id IN (' . $catIds . ') or FIND_IN_SET(' . $currentCategoryId . ',p.`inside_cat`) ';
      if($currentCategoryId==0){
        $where1 = ' 1=1 or FIND_IN_SET(' . $currentCategoryId . ',p.`inside_cat`)';
        $rule1 = ' 1=1 or FIND_IN_SET(' . $currentCategoryId . ',p.`inside_cat`) ';
      } 
    } else{
      $catIds = 0;
    }
    
    if(!empty($where) || !empty($where1)){
      $where = 'WHERE '.$where;
      if(!empty($where1)){
        $where .= (URL::isSection('mg-admin')) ? $where1 : ' AND '.$where1;
      }
    }
    
    $prices = DB::fetchAssoc(
        DB::query('
         SELECT
          ROUND(MAX((p.price_course + p.price_course * (IFNULL(c.rate,0)))),0) as `max_price`,
          FLOOR(MIN((p.price_course + p.price_course * (IFNULL(c.rate,0))))) as min_price
        FROM `' . PREFIX . 'product` as p
          LEFT JOIN `' . PREFIX . 'category` as c ON
          c.id = p.cat_id ' . $where)
    );

    $maxPrice = $prices['max_price'];
    $minPrice = $prices['min_price'];

    $property = array(
      'cat_id' => array(
        'type' => 'hidden',
        'value' => $_REQUEST['cat_id'],
      ),

      'sorter' => array(
        'type' => 'select', //текстовый инпут
        'label' => 'Сортировать по',
	  	'option' => $sortFields,
        'selected' => !empty($_REQUEST['sorter']) ? $_REQUEST['sorter'] : 'null', // Выбранный пункт (сравнивается по значению)
        'value' => !empty($_REQUEST['sorter'])?$_REQUEST['sorter']:null,
      ),

      'price_course' => array(
        'type' => 'beetwen', //Два текстовых инпута
        'label1' => $lang['PRICE_FROM'],
        'label2' => $lang['PRICE_TO'],
        'min' => !empty($_REQUEST['price_course'][0]) ? $_REQUEST['price_course'][0] : $minPrice,
        'max' => !empty($_REQUEST['price_course'][1]) ? $_REQUEST['price_course'][1] : $maxPrice,
        'factMin' => $minPrice,
        'factMax' => $maxPrice,
        'class' => 'price numericProtection'
      ),



      'applyFilter' => array(
        'type' => 'hidden', //текстовый инпут
        'label' => 'флаг примения фильтров',
        'value' => 1,
      )
    );

    $filter = new Filter($property);

          
    $arr = array(
      '(p.price_course + p.price_course * (IFNULL(rate,0)))' => array(!empty($_REQUEST['price_course'][0]) ? $_REQUEST['price_course'][0] : $minPrice, !empty($_REQUEST['price_course'][1]) ? $_REQUEST['price_course'][1] : $maxPrice),
     // 'p.activity' => (isset($_REQUEST['activity'])) ? $_REQUEST['activity'] : 'null',
      'p.new' => (isset($_REQUEST['new'])) ? $_REQUEST['new'] : 'null',
      'p.recommend' => (isset($_REQUEST['recommend'])) ? $_REQUEST['recommend'] : 'null',
      'rule1' => $rule1,

    );


    $userFilter = $filter->getFilterSql($arr, array(), $_REQUEST['insideCat']);
   


    if(!empty($_REQUEST['prop'])){
     $arrayIdsProd = $filter->getProductIdByFilter($_REQUEST['prop']);

     $listIdsProd = implode(',',$arrayIdsProd);
     if($listIdsProd){
       if(strlen($userFilter) > 0){
         $userFilter .= ' AND ';
       }
       $userFilter .= ' p.id IN ('.$listIdsProd.') ';
     }else{
       // добавляем заведомо неверное  условие к запросу,
       // чтобы ничего не попало в выдачу, т.к. товаров отвечающих заданым характеристикам ненайдено
       $userFilter = ' 0 = 1 ';
     }
    }


    $keys = array_keys($sortFields);
    if(empty($_REQUEST['sorter'])){
      $_REQUEST['sorter'] = $keys[0];
    }elseif(!URL::isSection('mg-admin') && !in_array($_REQUEST['sorter'], $keys)){
      $_REQUEST['sorter'] = $keys[0];
    }

    if(!empty($_REQUEST['sorter']) && !empty($userFilter)){
      $sorterData = explode('|', $_REQUEST['sorter']);
      $field = $sorterData[0];
      if ($sorterData[1] > 0) {
        $dir = 'desc';
      } else {
        $dir = 'asc';
      }

      if ($onlyInCount) {
        $userFilter .= ' AND (p.count>0 OR p.count<0)';
      }

      if ($onlyActive) {
        $userFilter .= ' AND p.`activity` = 1';
      }

      if(!empty($userFilter)){
        $userFilter .= " ORDER BY `".DB::quote($field, true)."`  ".$dir;
      }
    }


    return array('filterBarHtml' => $filter->getHtmlFilter($noneAjax), 'userFilter' => $userFilter);
  }



  /**
   * Метод добавляет к массиву продуктов информацию о характеристиках
   * для каждого продукта
   * @param $arrayProducts - массив с продуктами
   * @return float.
   */
  public function addPropertyToProduct($arrayProducts) {

    $categoryIds = array();
    $whereCat = '';
    $idsProduct = array();
    $currency = MG::getSetting("currency");
    $currencyRate = MG::getSetting('currencyRate');
    $currencyShopIso = MG::getSetting('currencyShopIso');
    $prod = new Models_Product();
    $idsVariantProduct = array();
  
    foreach ($arrayProducts as $key => $product) {
      $arrayProducts[$key]['category_url'] = (SHORT_LINK == '1'&&(!URL::isSection('mg-admin')) ? '' : $arrayProducts[$key]['category_url'].'/');
      $product['category_url'] = (SHORT_LINK == '1' ? '' : $product['category_url'].'/');
      if($product['variant_exist']&&$product['variant_id']){

        $variants = $prod->getVariants($product['id']);
        $variantsKey = array_keys($variants);
        $product['variant_id'] = $variantsKey[0];
        $idsVariantProduct[$product['id']][] = $key;
        $variant = $variants[$product['variant_id']];

        $arrayProducts[$key]['price_course'] =  $variant['price_course'];
        $arrayProducts[$key]['price'] =  $variant['price'];
        $arrayProducts[$key]['image_url'] =  $variant['image']?$variant['image']:$arrayProducts[$key]['image_url'];

      }
      $idsProduct[$product['id']] = $key;
      $categoryIds[] = $product['cat_id'];
      // Назначаем для продукта позьзовательские
      // характеристики по умолчанию, заданные категорией.
   
      $arrayProducts[$key]['thisUserFields'] = MG::get('category')->getUserPropertyCategoryById($product['cat_id']);
      $arrayProducts[$key]['propertyIdsForCat'] =  MG::get('category')->getPropertyForCategoryById($product['cat_id']);
      $arrayProducts[$key]['currency'] = $currency;
      // Формируем ссылки подробнее и в корзину.
      $arrayProducts[$key]['actionBuy'] = '<a href="' . SITE . '/catalog?inCartProductId=' . $product["id"] . '" rel="nofollow" class="addToCart product-buy" data-item-id="' . $product["id"] . '"><span class="cart-ico">' . MG::getSetting('buttonBuyName') . '</span></a>';
      $arrayProducts[$key]['actionCompare'] = '<a href="' . SITE . '/compare?inCompareProductId=' . $product["id"] . '" rel="nofollow" class="addToCompare" data-item-id="' . $product["id"] . '">' . MG::getSetting('buttonCompareName') . '</a>';
      $arrayProducts[$key]['actionView'] = '<a href="' . SITE . '/' . (isset($product["category_url"]) ? $product["category_url"] : 'catalog') . '/' . $product["product_url"] . '" class="product-info"><span class="cart-ico">' . MG::getSetting('buttonMoreName') . '</span></a>';
      $arrayProducts[$key]['link'] = SITE.'/'.(isset($product["category_url"]) ? $product["category_url"] : 'catalog/').$product["product_url"];
      if (empty($arrayProducts[$key]['currency_iso'])) {
        $arrayProducts[$key]['currency_iso'] = $currencyShopIso;
      }
      $arrayProducts[$key]['real_old_price'] = $arrayProducts[$key]['old_price'];
      $arrayProducts[$key]['old_price'] *= $currencyRate[$arrayProducts[$key]['currency_iso']];
      $arrayProducts[$key]['real_price'] = $arrayProducts[$key]['price'];
      $arrayProducts[$key]['price'] = MG::priceCourse($arrayProducts[$key]['price_course']);

      
      $imagesConctructions = $prod->imagesConctruction($arrayProducts[$key]['image_url'],$arrayProducts[$key]['image_title'],$arrayProducts[$key]['image_alt'], $product['id']);
      $arrayProducts[$key]['images_product'] = $imagesConctructions['images_product'];
      $arrayProducts[$key]['images_title'] = $imagesConctructions['images_title'];
      $arrayProducts[$key]['images_alt'] = $imagesConctructions['images_alt'];
      $arrayProducts[$key]['image_url'] = $imagesConctructions['image_url'];
      $arrayProducts[$key]['image_title'] = $imagesConctructions['image_title'];
      $arrayProducts[$key]['image_alt'] = $imagesConctructions['image_alt'];

      $imagesUrl = explode("|", $arrayProducts[$key]['image_url']);
      $arrayProducts[$key]["image_url"] = "";
      if (!empty($imagesUrl[0])) {
        $arrayProducts[$key]["image_url"] = $imagesUrl[0];
      }

    }

    $model = new Models_Product();
    $arrayVariants = $model->getBlocksVariantsToCatalog(array_keys($idsProduct), true);

    foreach (array_keys($idsProduct) as $id) {
      $arrayProducts[$idsProduct[$id]]['variants'] = $arrayVariants[$id];

      
    }

    // Собираем все ID продуктов в один запрос.
    if ($prodSet = trim(DB::quote(implode(',', array_keys($idsProduct))), "'")) {
      // Формируем список id продуктов, к которым нужно найти пользовательские характеристики.
      $where = ' IN (' . $prodSet . ') ';
    } else {
      $where = ' IN (0) ';
    }

    //Определяем id категории, в которой находимся
    $catCode = URL::getLastSection();

    $sql = '
      SELECT pup.property_id, pup.value, pup.product_id, prop.*, pup.type_view, pup.product_margin
      FROM `'.PREFIX.'product_user_property` as pup
      LEFT JOIN `'.PREFIX.'property` as prop
        ON pup.property_id = prop.id ';
    
    if($catSet = trim(DB::quote(implode(',', $categoryIds)), "'")){
      $categoryIds = array_unique($categoryIds);
      $sql .= '
        LEFT JOIN  `'.PREFIX.'category_user_property` as cup
        ON cup.property_id = prop.id ';
      $whereCat = ' AND cup.category_id IN ('.$catSet.') ';
    }

    $sql .= 'WHERE pup.`product_id` '.$where.$whereCat;
    $sql .= 'ORDER BY `sort` DESC';

    $res = DB::query($sql);



    while ($userFields = DB::fetchAssoc($res)) {
   //   viewDAta($userFields['property_id']);
   //   viewDAta($arrayProducts[$key]['propertyIdsForCat']);

     // Обновляет данные позначениям характеристик, только для тех хар. которые  назначены для категории текущего товара.
     // Это не работает в фильтрах и сравнениях.
     // if(in_array($userFields['property_id'],$arrayProducts[$key]['propertyIdsForCat'])){

      // дописываем в массив пользовательских характеристик,
      // все переопределенные для каждого тоавара, оставляя при
      // этом не измененные характеристики по умолчанию
      $arrayProducts[$idsProduct[$userFields['product_id']]]['thisUserFields'][$userFields['property_id']] = $userFields;

      // добавляем польз характеристики ко всем вариантам продукта
        if(!empty($idsVariantProduct[$userFields['product_id']])){
          foreach ($idsVariantProduct[$userFields['product_id']]  as $keyPages ) {
             $arrayProducts[$keyPages]['thisUserFields'][$userFields['property_id']] = $userFields;
          }
       }

     // }
    }

    return $arrayProducts;
  }

}


