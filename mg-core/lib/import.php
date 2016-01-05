<?php

/**
 * Класс Import - предназначен для импорта товаров в каталог магазина. Подерживает две структуры файлов  в формате CSV. Упрощенная - с артикулами и ценаи, а также полная со всей информацией о каждом товаре.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Import{
  private $typeCatalog = 'MogutaCMS';
  private $currentRowId = null;
  private $validError = null;   

  public function __construct($typeCatalog = "MogutaCMS") {
    $this->typeCatalog = $typeCatalog;
  }

  /**
   * Запускает загрузку товаров с заданной строки
   * @param type $rowId - id строки для старта
   * @return type
   */
  public function startUpload($rowId = false) {

    if(!$rowId){
      $rowId = 1;
    }
    if(empty($_SESSION['stopProcessImportCsv'])){

      $data = $this->importFromCsv($rowId);

      if($data===false){
        $msg = 'Ошибка в CSV файле! '.$this->validError.' line:'.((int)$this->currentRowId+1);
        return
        array(
          'status' => 'error',
          'msg' => $msg
        );
      }
      
      return
        array(
          'percent' => $data['percent'],
          'status' => 'run',
          'rowId' => $data['rowId']       
      );
    } else{
      unset($_SESSION['stopProcessImportCsv']);
      return
        array(
          'percent' => 0,
          'status' => 'canseled',
          'rowId' => $rowId
      );
    }
  }

  /**
   * Останавливает процесс импорта.
   * @return type
   */
  public function stopProcess() {
    $_SESSION['stopProcessImportCsv'] = true;
  }

  /**
   * Вычисляет разделитель в CSV файле.
   * @return type
   */
  public function getDelimetr() {
    $delimert = ';';
    return $delimert;
  }

  public function importFromCsv($rowId) {
   
    $this->maxExecTime = min(30, @ini_get("max_execution_time"));
    if(empty($this->maxExecTime)){
      $this->maxExecTime = 30;
    }
    $startTimeSql = microtime(true);
    $delimert = $this->getDelimetr();
    $infile = false;

    $file = new SplFileObject("uploads/importCatalog.csv");
    if($rowId === 1 || empty($rowId)){
      $rowId = 0;
    }
    $this->currentRowId = $rowId;
    $file->seek($rowId);
    $validFormat = true;
  
    while(!$file->eof()){
      
      $infile = true;
      $data = $file->fgetcsv(";");

      
      if($rowId === 0){
        if($this->typeCatalog == "MogutaCMS"){
          $validFormat = $this->validateFormate(
            $data,
            $maskArray = array(
              'Категория',
              'URL категории',
              'Товар',
              'Вариант',
              'Описание',
              'Цена',
              'URL',
              'Изображение',
              'Артикул',
              'Количество',
              'Активность',
              'Заголовок [SEO]',
              'Ключевые слова [SEO]',
              'Описание [SEO]',
              'Старая цена',
              'Рекомендуемый',
              'Новый',
              'Сортировка',
              'Вес',
              'Связанные артикулы',
              'Смежные категории',
              'Ссылка на товар',
              'Валюта',
              'Свойства'
              )
          );
          if(!$validFormat){
            break;
          }
        }  
        
        if($this->typeCatalog == "MogutaCMSUpdate"){ 
          $validFormat = $this->validateFormate(
            $data,
            $maskArray = array(
              'Артикул',
              'Цена',
              'Старая цена',
              'Количество',
              'Активность',              
              )
          );
          if(!$validFormat){
            break;
          }
        }
        $rowId = 1;
        continue;
      }
      
      if((microtime(true) - $startTimeSql) > $this->maxExecTime - 5){
        break;
      }
      
      foreach($data as $k => $v){
        $v = trim($v);
        if(!empty($v)){
          // Функция str_replace(' ',' ',...); Заменяет невидимый символ alt+255 на пробелы
          $data[$k] = str_replace(' ',' ',iconv("WINDOWS-1251", "UTF-8", $v));          
        }
      }            
      
      $this->currentRowId = $rowId;
      switch($this->typeCatalog){
        case "MogutaCMS":
          if(!$this->formateMogutaCMS($data)){
            return false;
          }         
          break;
        case "MogutaCMSUpdate":         
          if(!$this->formateMogutaCMSUpdate($data)){
            return false;
          } 
          break;
 
        default:
          if(!$this->formateMogutaCMS($data)){
            return false;
          }      
      } 
      
      $rowId++;
      
    }
    
    if(!$validFormat){
      $this->validError = 'Нарушен порядок столбцов или кодировка!';
      return false;
    }
    
    $file = null;

    $percent100 = count(file("uploads/importCatalog.csv"));
    $percent = $rowId;
    $percent = ($percent * 100) / $percent100;

    if(!$infile){
      $percent = 100;
    }

    $data = array(
      'rowId' => $rowId,
      'percent' => floor($percent),
    );

    return $data;
  }
  
  /**
   * Проверка валидности файла
   */
  public function validateFormate($data,$maskArray) {
  
    $result = true;
    // проверим на соответствие заголовки столбцов
    foreach($data as $k => $v){
      $v = str_replace(' ',' ',iconv("WINDOWS-1251", "UTF-8", $v));
      if(isset($maskArray[$k])){
        if($maskArray[$k]!=$v) {
          $result = false;        
          $this->validError = 'Столбец "'.$maskArray[$k].'" не обнаружен!';
          
          break;
        }      
      }
    }
    
    return $result;    
  }
  
  /**
   * Полная выгрузка по формату Moguta.CMS
   */    
  public function formateMogutaCMS($data) {    
    
    $itemsIn = array(      
      'cat_id' => trim($data[0]),
      'cat_url' => trim($data[1]),
      'title' => trim($data[2]),
      'variant' => '',      
      'description' => trim($data[4]),
      'price' => trim(MG::numberDeFormat($data[5])),
      'url' => trim($data[6]),
      'image_url' => trim($data[7]),
      'code' => $data[8],
      'count' => trim($data[9]),
      'activity' => trim($data[10]),
      'meta_title' => $data[11],
      'meta_keywords' => $data[12],
      'meta_desc' => $data[13],
      'old_price' => trim(MG::numberDeFormat($data[14])),
      'recommend' => trim($data[15]),
      'new' => trim($data[16]),
      'sort' => trim($data[17]),
      'weight' => trim(MG::numberDeFormat($data[18])),
      'related' => trim($data[19]),
      'inside_cat'  => trim($data[20]),
      'link_electro' => trim($data[21]),
      'currency_iso' => trim($data[22]),
      'property' => trim($data[23]),
      'id' => trim($data[24]),
    );  
    
    if(!empty($data[3])){
      if(strpos($data[3], '[:param:]')!==false){
        $variant = explode('[:param:]', $data[3]);
        $itemsIn['variant'] = $variant[0];
        $itemsIn['image'] = str_replace(array('[src=', ']'),'', $variant[1]);
      }else{
        $itemsIn['variant'] = $data[3];
      }     
    }

    $this->prepareLineCsv($itemsIn);
    return true;
  }
    
  
  /**
   * выгрузка для обновления цен имеющихся товаров по их артикулам
   */
  public function formateMogutaCMSUpdate($data) {
    $itemsIn = array(
      'price' => trim(MG::numberDeFormat($data[1])),    
      'old_price' => trim(MG::numberDeFormat($data[2])),
      'count' => trim($data[3]),
      'activity' => trim($data[4]),  
    );   
 
    if((count($data) < count($itemsIn)+1) && count($data)>1){
      $this->validError = 'Нарушена целостность строки!';
      return false;
    }
     
    DB::query('
      UPDATE `'.PREFIX.'product`
      SET '.DB::buildPartQuery($itemsIn).'
      WHERE code = '.DB::quote($data[0])
    );
    
    DB::query('
      UPDATE `'.PREFIX.'product_variant`
      SET '.DB::buildPartQuery($itemsIn).'
      WHERE code = '.DB::quote($data[0])
    );
    
    $model = new Models_Product();
    $currencyShopIso = MG::getSetting('currencyShopIso');    
    
    $res = DB::query('
      SELECT id
      FROM `'.PREFIX.'product`
      WHERE code = '.DB::quote($data[0])
    );
    
    if($row = DB::fetchAssoc($res)){     
      $model->updatePriceCourse($currencyShopIso, array($row['id']));    
    }else{
      $res = DB::query('
        SELECT product_id
        FROM `'.PREFIX.'product_variant`
        WHERE code = '.DB::quote($data[0])
      );
      
      if($row = DB::fetchAssoc($res)){     
        $model->updatePriceCourse($currencyShopIso, array($row['product_id']));    
      }
    }
    
    return true;    
  }
    
  
 
  
  /**
   * Парсит категории, создает их и продукт.
   * @param type $itemsIn - массив собранный из строки файла.
   */
  public function prepareLineCsv($itemsIn) {
    $categories = $this->parseCategoryPath($itemsIn['cat_id']);
    $catId = null;
    //первая проверка на корректность URL категории. 
    //Необходимо чтобы  количество разделителей-слешей в первой колонке, соответствовало количеству 
    //слешей во второй колонке с URL.
    // Если это услови не выполняется, значит не будем учитывать заданый URL категории, и будет создан правильный URL    
    if(substr_count($itemsIn['cat_id'], '/')==substr_count($itemsIn['cat_url'], '/')&& !empty($itemsIn['cat_url'])){     
     
      // проверим на этом этапе, существует ли категория с url = $itemsIn['cat_url'];
      // если существует, то не будем создавать новую и в последствии будем использовать ее id;
      $url = URL::parsePageUrl($itemsIn['cat_url']);
      $parentUrl = URL::parseParentUrl($itemsIn['cat_url']);
      if($parentUrl=="/"){
        $parentUrl="";
      }
	
      $category = MG::get('category')->getCategoryByUrl($url,$parentUrl);
	 
      if(!empty($category)){
        $catId = $category['id'];   
      }else{
        $this->createCategory($categories);
      }
    }else{ 
      $this->createCategory($categories);   
    }
    
    $this->createProduct($itemsIn, $catId);
    // вычисляем  ID категории если она есть
  }

  /**
   * Создает продукт в БД если его не было.
   * @param type $product - массив с данными о продукте.
   * @param type $catId - категория к которой относится продукт.
   */
  public function createProduct($product, $catId = null) {
    $model = new Models_Product();
           
    $variant = $product['variant'];
    $img_var = $product['image'];
    $property = $product['property'];
    unset($product['cat_url']);
    unset($product['variant']);
    unset($product['image']);
    unset($product['property']);

    if($catId === null ){
      // 1 находим ID категории по заданному пути   
      $product['cat_id'] = MG::translitIt($product['cat_id'], 1);
      $product['cat_id'] = URL::prepareUrl($product['cat_id']);

      if($product['cat_id']){
        $url = URL::parsePageUrl($product['cat_id']);
        $parentUrl = URL::parseParentUrl($product['cat_id']);
        $parentUrl = $parentUrl != '/'?$parentUrl:'';
        $cat = MG::get('category')->getCategoryByUrl(
        $url, $parentUrl
        );     
        $product['cat_id'] = $cat['id'];
      }
    }else{
      $product['cat_id'] = $catId;
    }

    $product['cat_id'] = !empty($product['cat_id'])?$product['cat_id']:0;
  
    if(!empty($product['id'])){
      $dbRes = DB::query('SELECT `id`, `url`, `title` FROM `'.PREFIX.'product` WHERE `id` = '.DB::quote($product['id'], 1));
      if($res = DB::fetchArray($dbRes)){
        if($res['title'] == $product['title']){
          $product['url'] = $res['url'];
        }       
        unset($product['id']);
      }else{
        $arrProd = $model->addProduct($product);               
      }             
    }    
    
    if(empty($arrProd)){
      // 2 если URL не задан в файле, то транслитирируем его из названия товара
      $product['url'] = !empty($product['url'])?$product['url']:preg_replace('~-+~','-',MG::translitIt($product['title'], 0));
      $product['url'] = URL::prepareUrl($product['url'], true);    

      if($product['cat_id'] == 0){
        $alreadyProduct = $model->getProductByUrl($product['url']);
      } else{
        $alreadyProduct = $model->getProductByUrl($product['url'], $product['cat_id']);
      }

      // если в базе найден этот продукт, то при обновлении будет сохранен ID и URL 
      if(!empty($alreadyProduct['id'])){
        $product['id'] = $alreadyProduct['id'];
        $product['url'] = $alreadyProduct['url'];
      }
      // парсим изображение, его alt и title
      if (strpos($product['image_url'], '[:param:]')!==false) {
        $images = $this->parseImgSeo($product['image_url']);
        $product['image_url'] = $images[0];  
        $product['image_alt'] = $images[1];
        $product['image_title'] = $images[2];         
      }
      // обновляем товар, если его не было то метод вернет массив с параметрами вновь созданного товара, в том числе и ID. Иначе  вернет true 
      $arrProd = $model->updateProduct($product);
    }
        
    $product_id = $product['id']?$product['id']:$arrProd['id'];   
    $categoryId = $product['cat_id'];
    $productId = $product_id;
    $listProperty = $property;
    $arrProperty = $this->parseListProperty($listProperty);

    foreach($arrProperty as $key => $value){
      $this->createProperty($key, $value, $categoryId, $productId);
    }

    if(!$variant){
      return true;
    }
    
    $var = $model->getVariants($product['id'], $variant);

    $varUpdate = null;
    if(!empty($var)){
      foreach($var as $k => $v){
        if($v['title_variant'] == $variant && $v['product_id'] == $product_id){
          $varUpdate = $v['id'];
        }
      }
    }

    // Иначе обновляем существующую запись в таблице вариантов.
    $newVariant = array(
      'product_id' => $product_id,
      'title_variant' => $variant,
      'sort' => $product['sort'],
      'price' => $product['price'],
      'old_price' => $product['old_price'],
      'count' => $product['count'],
      'code' => $product['code'],
      'image' => $img_var,
      'activity' => $product['activity'],
      'currency_iso' => $product['currency_iso']
    );
    $model->importUpdateProductVariant($varUpdate, $newVariant, $product_id);


    // Обновляем продукт по первому варианту.
    $res = DB::query('
      SELECT  pv.*
      FROM `'.PREFIX.'product_variant` pv    
      WHERE pv.product_id = '.DB::quote($product_id).'
      ORDER BY sort
    ');
    if($row = DB::fetchAssoc($res)){

      if(!empty($row)){
        $row['title'] = $product['title'];
        $row['id'] = $row['product_id'];
        unset($row['image']);
        unset($row['title_variant']);
        unset($row['product_id']);
        $model->updateProduct($row);
      }
    }
  }

  /**
   * Создает категории в БД если их небыло.
   * @param type $categories - массив категорий полученый из записи вида категория/субкатегория/субкатегория2
   */
  public function createCategory($categories) {

    foreach($categories as $category){

      $category['parent_url'] = $category['parent_url'] != '/'?$category['parent_url']:'';

      if($category['parent_url']){
        $pUrl = URL::parsePageUrl($category['parent_url']);
        $parentUrl = URL::parseParentUrl($category['parent_url']);
        $parentUrl = $parentUrl != '/'?$parentUrl:'';
      } else{
        $pUrl = $category['url'];
        $parentUrl = $category['parent_url'];
      }

      // вычисляем  ID родительской категории если она есть
      $alreadyParentCat = MG::get('category')->getCategoryByUrl(
        $pUrl, $parentUrl
      );

      // если нашлась  ID родительская категория назначаем parentID для новой
      if(!empty($alreadyParentCat)){
        $category['parent'] = $alreadyParentCat['id'];
      }

      // проверяем, вдруг такая категория уже существует
      $alreadyExist = MG::get('category')->getCategoryByUrl(
        $category['url'], $category['parent_url']
      );      
     

      if(!empty($alreadyExist)){
        $category = $alreadyExist;
      }

      MG::get('category')->updateCategory($category);
    }
  }

  /**
   * Восстанавливает привязки характеристик для новых категорий из таблицы import_cat_prop.
   */
  public function recoveryTableCatProp() {

    DB::query("
      INSERT INTO ".PREFIX."category_user_property( category_id, property_id ) 
      SELECT c.id as 'category_id', ip.property_id
      FROM ".PREFIX."import_cat_prop AS ip
      RIGHT JOIN ".PREFIX."category AS c ON  ip.url = c.url AND ip.parent_url = c.parent_url   
    ");
  }

  /**
   * Парсит путь категории возвращает набор категорий.
   * @param type $path - список категорий через /
   */
  public function parseCategoryPath($path) {

    $i = 1;

    $categories = array();
    if(!$path){
      return $categories;
    }

    $parent = $path;
    $parentForUrl = str_replace(array('«', '»'), '', $parent);    
    $parentTranslit = MG::translitIt($parentForUrl, 1);
    $parentTranslit = URL::prepareUrl($parentTranslit);

    $categories[$parent]['title'] = URL::parsePageUrl($parent);
    $categories[$parent]['url'] = URL::parsePageUrl($parentTranslit);
    $categories[$parent]['parent_url'] = URL::parseParentUrl($parentTranslit);
    $categories[$parent]['parent'] = 0;

    while($parent != '/'){
      $parent = URL::parseParentUrl($parent);
      $parentForUrl = str_replace(array('«', '»'), '', $parent);
      $parentTranslit = MG::translitIt($parentForUrl, 1);
      $parentTranslit = URL::prepareUrl($parentTranslit);
      if($parent != '/'){
        $categories[$parent]['title'] = URL::parsePageUrl($parent);
        $categories[$parent]['url'] = URL::parsePageUrl($parentTranslit);
        $categories[$parent]['parent_url'] = URL::parseParentUrl($parentTranslit);
        $categories[$parent]['parent_url'] = $categories[$parent]['parent_url'] != '/'?$categories[$parent]['parent_url']:'';
        $categories[$parent]['parent'] = 0;
      }
    }

    $categories = array_reverse($categories);

    return $categories;
  }

  /**
   * Сравнивает создаваемую категорию, с имеющимися ранее.
   * Если обнаруживает, что аналогичная категория раньше существовала, 
   * то возвращает ее старый ID.   
   * @param type $title - название товара.
   * @param type $path - путь.
   */
  public function getCategoryId($title, $path) {
    $path = trim($path, '/');

    $sql = '
      SELECT cat_id
      FROM `'.PREFIX.'import_cat`
      WHERE `title` ='.DB::quote($title)." AND `parent` = ".DB::quote($path);

    $res = DB::query($sql);
    if($row = DB::fetchAssoc($res)){
      return $row['cat_id'];
    }
    return null;
  }

  /**
   * Возвращает старый ID для товара.
   * то возвращает ее старый ID.
   * @param type $title - название товара.
   * @param type $cat_id - id категории.
   */
  public function getProductId($title, $cat_id) {
    $path = trim($path, '/');

    $sql = '
      SELECT product_id
      FROM `'.PREFIX.'import_prod`
      WHERE `title` ='.DB::quote($title)." AND `category_id` = ".DB::quote($cat_id);

    $res = DB::query($sql);
    if($row = DB::fetchAssoc($res)){
      return $row['product_id'];
    }
    return null;
  }

  /**
   * Создает временную таблицу import_cat_prop, для сохранения связей характеристик и категорий
   */
  public function greateTempTableImport() {
    DB::query("DROP TABLE IF EXISTS ".PREFIX."import_prod");
    DB::query("DROP TABLE IF EXISTS ".PREFIX."import_cat");
    DB::query("
     CREATE TABLE IF NOT EXISTS ".PREFIX."import_cat (
      `cat_id` int(11) NOT NULL,  
      `title` varchar(2048) NOT NULL,
      `parent` varchar(2048) NOT NULL 
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    DB::query("
     CREATE TABLE IF NOT EXISTS ".PREFIX."import_prod (
      `product_id` int(11) NOT NULL,  
      `title` varchar(2048) NOT NULL,    
      `url_cpu_cat` varchar(2048) NOT NULL,
      `category_id` int(11) NOT NULL,
      `variant` int(1) NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $sql = '
      SELECT    
        c.id as category_id,
        c.title as category_title,
        CONCAT(c.parent_url,c.url) as category_url,
        p.url as product_url,
        p.*
      FROM `'.PREFIX.'product` p
      LEFT JOIN `'.PREFIX.'category` c
        ON c.id = p.cat_id';
    $res = DB::query($sql);


    $product = new Models_Product();

    while($row = DB::fetchAssoc($res)){

      $parent = $row['category_url'];

      // Подставляем всесто URL названия разделов.
      $resultPath = '';      
      while($parent){     
        $url = URL::parsePageUrl($parent);
        $parent = URL::parseParentUrl($parent);
        $parent = $parent != '/'?$parent:'';
        $alreadyParentCat = MG::get('category')->getCategoryByUrl(
          $url, $parent
        );
        $resultPath = $alreadyParentCat['title'].'/'.$resultPath;
      }

      $resultPath = trim($resultPath, '/');
      $variants = $product->getVariants($row['id']);
      $variant = 0;

      DB::query("
       INSERT INTO `".PREFIX."import_prod` 
         (`product_id`, `title`, `url_cpu_cat`, `category_id`, `variant`) 
       VALUES (".DB::quote($row['id']).", ".DB::quote($row['title']).", ".DB::quote($row['category_url']).", ".DB::quote($row['category_id']).", ".$variant.")");
    }

    //---------------------наполняем таблицу для категорий-----------------------------
    $sql = '
      SELECT `id`, `title`, `parent_url`, url,
       CONCAT(parent_url,url) as category_url
      FROM `'.PREFIX.'category` c';
    $res = DB::query($sql);

    while($row = DB::fetchAssoc($res)){

      $parent = $row['parent_url'];

      // Подставляем вместо URL названия разделов.
      $resultPath = ''; 
      while($parent){    
        $url = URL::parsePageUrl($parent);
        $parent = URL::parseParentUrl($parent);
        $parent = $parent != '/'?$parent:'';
        $alreadyParentCat = MG::get('category')->getCategoryByUrl(
          $url, $parent
        );

        $resultPath = $alreadyParentCat['title'].'/'.$resultPath;
      }

      $resultPath = trim($resultPath, '/');

      DB::query("
       INSERT INTO `".PREFIX."import_cat` 
         (`cat_id`, `title`, `parent`) 
       VALUES (".DB::quote($row['id']).", ".DB::quote($row['title']).", ".DB::quote($resultPath).")");
    }
  }

  /**
   * Возвращает массив из входящей строки с характеристиками
   * @param type $listProperty пример $listProperty = 'Производитель=Индия&Цвет=красный&высота=1024';
   * @return type
   */
  function parseListProperty($listProperty) {
    $listProperty = str_replace('&amp;', '[[amp]]', $listProperty);

    $params = explode('&', $listProperty);
    $paramsarr = array();
    foreach($params as $value){
      $value = str_replace('[[amp]]', '&', $value);
      $tmp = explode('=', $value);
      $paramsarr[$tmp[0]] = $tmp[1];
    }

    return $paramsarr;
  }

  /**
   * Создает свойства продукта
   * @param type $key = Название характеристики
   * @param type $value = Значание
   * @param type $categoryId = Категория
   * @param type $productId = Продукт
   * @return type
   */
  function createProperty($key, $value, $categoryId, $productId) {
    if(empty($key)){
      return false;
    }
    // 0. Очистим продукт от всех ранее имеющихся свойств

    $propertyId = '';
    // 1. Проверяем, существует такая характеристика у данной категории?
    $res = DB::query(  
      'SELECT * 
        FROM `'.PREFIX.'property`
        LEFT JOIN `'.PREFIX.'category_user_property` as `cup`
           ON `cup`.`property_id`=`'.PREFIX.'property`.`id` 
        WHERE `name` = '.DB::quote($key)    
    );  
    
    $row = DB::fetchAssoc($res);
    if(empty($row)){
  
      // если нет характеристики до создадим ее
      DB::query('
       INSERT INTO `'.PREFIX.'property`
         (`name`, `type`,  `activity`)
       VALUES ('.DB::quote($key).',"string",1)'
      );      
      $propertyId = DB::insertId();
      // установка  сортировки
      DB::query(
        'UPDATE `'.PREFIX.'property`
        SET `sort` = '.DB::quote($propertyId).'
        WHERE `id` = '.DB::quote($propertyId)
      );
    } else{
      
      // если найдена уже характеристика, получаем ее id
      $propertyId = $row['id'];
     
      // добавляем привязку, если ее небыло раньше, для действующей категории
      $res = DB::query('
       SELECT * 
       FROM `'.PREFIX.'category_user_property` 
       WHERE `property_id` = '.DB::quote($propertyId).' 
         AND `category_id` = '.DB::quote($categoryId)
      );
      $rowCup = DB::fetchAssoc($res);
      if(empty($rowCup)){
       DB::query('
         INSERT INTO `'.PREFIX.'category_user_property`
          (`category_id`, `property_id`)
         VALUES ('.DB::quote($categoryId).','.DB::quote($propertyId).')'
       );
      }
     
    }


    // 2. Привязываем к продукту
    $res = DB::query('
     SELECT * 
     FROM `'.PREFIX.'product_user_property` 
     WHERE `property_id` = '.DB::quote($propertyId).'
       AND `product_id` = '.DB::quote($productId)
    );
    $row = DB::fetchAssoc($res);
    if(empty($row)){
      DB::query('
        INSERT INTO `'.PREFIX.'product_user_property`
         (`product_id`, `property_id`, `value`)
        VALUES ('.DB::quote($productId).','.DB::quote($propertyId).','.DB::quote($value).')'
      );
    } else{

      DB::query('
        UPDATE `'.PREFIX.'product_user_property`
        SET `value` = '.DB::quote($value).'
        WHERE `product_id` = '.DB::quote($productId).'
          AND `property_id` = '.DB::quote($propertyId)
      );
    }
    // 3. Привязываем к категории
    $res = DB::query('
     SELECT * 
     FROM `'.PREFIX.'category_user_property` 
     WHERE `property_id` = '.DB::quote($propertyId)
    );
    $row = DB::fetchAssoc($res);
    if(empty($row)){
      // если нет характеристики до создадим ее
      DB::query('
     INSERT INTO `'.PREFIX.'category_user_property`
      (`category_id`, `property_id`)
     VALUES ('.DB::quote($categoryId).','.DB::quote($propertyId).')'
      );
    }
  }
  /**
   * Возвращает массив из изображений и seo-настройки к ним - alt и title
   * @param type $listImg пример $listImg = 'noutbuk.png[:param:][alt=ноутбук][title=ноутбук]|noutbuk-Dell-Inspiron-N411Z-oneside.png[:param:][alt=ноутбук черного цвета][title=ноутбук черного цвета]';
   * @return type
   */
  function parseImgSeo($listImg) {
    $images_alt = '';
    $images_title = '';
    $images = explode('|', $listImg);
    foreach ($images as $value) {
      $item = explode('[:param:]', $value);
      $images_url .= $item[0].'|';
      $seo = explode(']', $item[1]);
      $images_alt .= str_replace('[alt=','', $seo[0]).'|';
      $images_title .= str_replace('[title=','', $seo[1]).'|';  
    }
    $result= array (substr($images_url, 0, -1), substr($images_alt, 0, -1), substr($images_title, 0, -1));
    return $result;
  }

}