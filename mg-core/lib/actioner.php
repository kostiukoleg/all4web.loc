<?php

/**
 * Класс Actioner - предназначен для обработки административных действий, 
 * совершаемых из панели управления сайтом, таких как добавление и удалени товаров, 
 * категорий, и др. сущностей.
 * 
 * Методы класса являются контролерами между AJAX запросами и логикой моделей движка, возвращают в конечном результате строку в JSON формате.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Actioner {

  /**
   * @var string сообщение об успешнон результате выполнения операции. 
   */
  public $messageSucces;

  /**
   * @var string сообщение о неудачном результате выполнения операции. 
   */
  public $messageError;

  /**
   * @var mixed массив с данными возвращаемый в ответ на AJAX запрос. 
   */
  public $data = array();

  /**
   * @var mixed язык локали движка. 
   */
  public $lang = array();

  /**
   * @var string префикс таблиц в базе сайта. 
   */
  public $prefix;

  /**
   * Конструктор инициализирует поля клааса.
   * @param boolean $lang - массив дополняющий локаль движка. Используется для работы плагинов.
   */
  public function __construct($lang = false) {
    $this->messageSucces = 'Succes';
    $this->messageError = 'Error';

    $langMerge = array();
    if (!empty($lang)) {
      $langMerge = $lang;
    }// если $lang не пустой, значит он передан для работы в наследнике данного класса, например для обработки аяксовых запросов плагина
    include('mg-admin/locales/'.MG::getSetting('languageLocale').'.php');

    $lang = array_merge($lang, $langMerge);

    $this->lang = $lang;
    $this->prefix = PREFIX;
  }

  /**
   * Запускает один из методов данного класса.
   * @param type $action - название метода который нужно вызвать.
   */
  public function runAction($action) {
    unset($_POST['mguniqueurl']);
    unset($_POST['mguniquetype']);
    //отсекаем все что после  знака ?
    $action = preg_replace("/\?.*/s", "", $action);

    $this->jsonResponse($this->$action());
    exit;
  }

  /**
   * Добавляет продукт в базу.
   * @return boolean
   */
  public function addProduct() {
    $model = new Models_Product;
    $this->data = $model->addProduct($_POST);
    $this->messageSucces = $this->lang['ACT_CREAT_PROD'].' "'.$_POST['name'].'"';
    $this->messageError = $this->lang['ACT_NOT_CREAT_PROD'];
    return true;
  }
  
 
  /**
   * Клонирует  продукт.
   * @return boolean
   */
  public function cloneProduct() {
    $model = new Models_Product;
    $this->data = $model->cloneProduct($_POST['id']);
    $this->data['image_url'] = mgImageProductPath($this->data['image_url'], $this->data['id']);
    $this->messageSucces = $this->lang['ACT_CLONE_PROD'];
    $this->messageError = $this->lang['ACT_NOT_CLONE_PROD'];
    return true;
  }

  /**
   * Клонирует  заказ.
   * @return boolean
   */
  public function cloneOrder() {
    $model = new Models_Order;
    $this->messageSucces = $this->lang['ACT_CLONE_ORDER'];
    $this->messageError = $this->lang['ACT_NOT_CLONE_ORDER'];
    $this->data = $model->cloneOrder($_POST['id']);
    return $this->data;
  }

  /**
   * Активирует плагин.
   * @return boolean
   */
  public function activatePlugin() {
    $this->messageSucces = $this->lang['ACTIVE_PLUG'].' "'.$_POST['pluginTitle'].'"';
    $pluginFolder = $_POST['pluginFolder'];
    $res = DB::query("
      SELECT *
      FROM  `".PREFIX."plugins`
      WHERE folderName = '%s'
      ", $pluginFolder);

    if (!DB::numRows($res)) {
      $result = DB::query("
        INSERT INTO `".PREFIX."plugins`
        VALUES ('%s', '1')"
          , $pluginFolder);

      MG::createActivationHook($pluginFolder);
      $this->data['havePage'] = PM::isHookInReg($pluginFolder);
      return true;
    }

    if ($result = DB::query("
      UPDATE `".PREFIX."plugins`
      SET active = '1'
      WHERE `folderName` = '%s'
      ", $pluginFolder
      )) {
      MG::createActivationHook($pluginFolder);
      $this->data['havePage'] = PM::isHookInReg($pluginFolder);
      $this->data['newInformer'] = MG::createInformerPanel();
      return true;
    }

    return false;
  }

  /**
   * Деактивирует плагин.
   * @return boolean
   */
  public function deactivatePlugin() {
    $this->messageSucces = $this->lang['ACT_NOT_ACTIVE_PLUG'].' "'.$_POST['pluginTitle'].'"';
    $pluginFolder = $_POST['pluginFolder'];
    $res = DB::query("
      SELECT *
      FROM  `".PREFIX."plugins`
      WHERE folderName = '%s'
      ", $pluginFolder);

    if (DB::numRows($res)) {
      DB::query("
        UPDATE `".PREFIX."plugins`
        SET active = '0'
        WHERE `folderName` = '%s'
      ", $pluginFolder
      );

      MG::createDeactivationHook($pluginFolder);
      return true;
    }

    return false;
  }

  /**
   * Удаляет инсталятор.
   * @return void
   */
  public function delInstal() {
    $installDir = SITE_DIR.URL::getCutPath().'/install/';
    $this->removeDir($installDir);
    MG::redirect('');
  }

  /**
   * Удаляет папку со всем ее содержимым.
   * @param string $path путь к удаляемой папке.
   * @return void
   */
  public function removeDir($path) {
    if (file_exists($path) && is_dir($path)) {
      $dirHandle = opendir($path);

      while (false !== ($file = readdir($dirHandle))) {

        if ($file != '.' && $file != '..') {// Исключаем папки с назварием '.' и '..'
          $tmpPath = $path.'/'.$file;
          chmod($tmpPath, 0777);

          if (is_dir($tmpPath)) {  // Если папка.
            $this->removeDir($tmpPath);
          } else {

            if (file_exists($tmpPath)) {
              // Удаляем файл.
              unlink($tmpPath);
            }
          }
        }
      }
      closedir($dirHandle);

      // Удаляем текущую папку.
      if (file_exists($path)) {
        rmdir($path);
        return true;
      }
    }
  }

  /**
   * Добавляет картинку для использования в визуальном редакторе.
   * @return boolean
   */
  public function upload() {
    new Upload(true, $_REQUEST['upload_dir']);
  }

  /**
   * Подключает elfinder.
   * @return boolean
   */
  public function elfinder() {

    include('mg-core/script/elfinder/php/connector.php');
  }

  /**
   * Добавляет водяной знак.
   * @return boolean
   */
  public function updateWaterMark() {

    $uploader = new Upload(false);
   
    $tempData = $uploader->addImage(false, true);
    $this->data = array('img' => $tempData['actualImageName']);

    if ($tempData['status'] == 'success') {
      $this->messageSucces = $tempData['msg'];
      return true;
    } else {
      $this->messageError = $tempData['msg'];
      return false;
    }
  }

  /**
   * Обрабатывает запрос на установку плагина.
   * @return boolean
   */
  public function addNewPlugin() {

    if (isset($_POST) && 'POST' == $_SERVER['REQUEST_METHOD']) {
      $file_array = $_FILES['addPlugin'];
      $downloadResult = PM::downloadPlugin($file_array);

      if ($downloadResult['data']) {
        $this->messageSucces = $downloadResult['msg'];
        PM::extractPluginZip($downloadResult['data']);
        return true;
      } else {
        $this->messageError = $downloadResult['msg'];
      }
    }
    return false;
  }

  /**
   * Обрабатывает запрос на установку шаблона.
   * @return boolean
   */
  public function addNewTemplate() {

    if (isset($_POST) && 'POST' == $_SERVER['REQUEST_METHOD']) {
      $file_array = $_FILES['addTempl'];
      //имя шаблона
      $name = $file_array['name'];
      //его размер
      $size = $file_array['size'];
      //временная папка архива плагина
      $path = 'mg-templates/';
      //поддерживаемые форматы
      $validFormats = array('zip');

      $lang = MG::get('lang');

      if (strlen($name)) {
        $fullName = explode('.', $name);
        $ext = array_pop($fullName);
        $name = implode('.', $fullName);
        if (in_array($ext, $validFormats)) {
          if ($size < (1024 * 1024 * 10)) {
            $actualName = $name.'.'.$ext;
            $tmp = $file_array['tmp_name'];
            if (move_uploaded_file($tmp, $path.$actualName)) {
              $data = $path.$actualName;
              $msg = $this->lang['TEMPL_UPLOAD'];
            } else {
              $msg = $this->lang['TEMPL_UPLOAD_ERR'];
            }
          } else {
            $msg = $this->lang['TEMPL_UPLOAD_ERR2'];
          }
        } else {
          $msg = $this->lang['TEMPL_UPLOAD_ERR3'];
        }
      } else {
        $msg = $this->lang['TEMPL_UPLOAD_ERR4'];
      }

      if ($data) {
        $this->messageSucces = $msg;

        if (file_exists($data)) {
          $zip = new ZipArchive;
          $res = $zip->open($data, ZIPARCHIVE::CREATE);
          if ($res === TRUE) {
            $zip->extractTo($path);
            $zip->close();
            unlink($data);
            return true;
          }
        }
        $this->messageError = 'Не удалось распаковать шаблон!';
        return false;
      } else {
        $this->messageError = $msg;
      }
    }
    return false;
  }
  
  /*
   * Проверяет наличие обновлени плагинов
   * @return boolean
   */
  public function checkPluginsUpdate(){
    $this->messageSucces = $this->lang['ACT_PLUGIN_CHECK_UPD_SUCCESS'];
    $this->messageError = $this->lang['ACT_PLUGIN_CHECK_UPD_ERR'];
    
    if(!MG::libExists()){
      return PM::checkPluginsUpdate();
    }else{
      $this->messageError = $this->lang['ACT_PLUGIN_CURL_NOT_INCLUDE'];
      return false;
    }
  }
  
  /*
   * Выполняет обновление плагина
   * @return boolean
   */
  public function updatePlugin(){
    $this->messageError = "Доступно только в полной версии Moguta.CMS";
    return false;
  }

  /**
   * Обрабатывает запрос на удаление плагина.
   * @return boolean
   */
  public function deletePlugin() {
    $this->messageSucces = $this->lang['ACT_PLUGIN_DEL'].$_POST['id'];
    $this->messageError = $this->lang['ACT_PLUGIN_DEL_ERR'];

    // удаление плагина из папки.
    $documentroot = str_replace('mg-core'.DIRECTORY_SEPARATOR.'lib', '', dirname(__FILE__));
    if (PM::deletePlagin($_POST['id']) && $this->removeDir($documentroot.'mg-plugins'.DIRECTORY_SEPARATOR.$_POST['id'])) {
      return true;
    }
    return false;
  }

  /**
   * Добавляет картинку товара.
   * @return boolean
   */
  public function addImage() {
    $uploader = new Upload(false);
    //$uploader->deleteImageProduct($_POST['currentImg']);
    $tempData = $uploader->addImage(true);
    $this->data = array('img' => $tempData['actualImageName']);
    if ($tempData['status'] == 'success') {
      $this->messageSucces = $tempData['msg'];
      return true;
    } else {
      $this->messageError = $tempData['msg'];
      return false;
    }
  }
  
   /**
   * Удаляет картинку товара.
   * @return boolean
   */
  public function deleteImageProduct() {
    $uploader = new Upload(false);
    $uploader->deleteImageProduct($_POST['imgFile'], $_POST['id']);
    $this->messageSucces = 'Файл изображения успешно удален с сервера';
    return true; 
  }
  
  /**
   * Удаляет изображения из временной папки, если товар не был сохранен
   */
  public function deleteTmpImages(){
    $arImages = explode('|', trim($_POST['images'], '|'));
    $product = new Models_Product();
    $product->deleteImagesProduct($arImages);
    return false;
  }
  
  /**
   * Добавляет картинку без водяного знака.
   * @return boolean
   */
  public function addImageNoWaterMark() {
    $uploader = new Upload(false);
    if (MG::getOption('waterMarkVariants')=='false') {
      $_POST['noWaterMark'] = true;
    }    
    $tempData = $uploader->addImage(true);
    $this->data = array('img' => $tempData['actualImageName']);
    $documentroot = str_replace('mg-core'.DIRECTORY_SEPARATOR.'lib', '', dirname(__FILE__));
    if ($tempData['status'] == 'success') {
      $this->messageSucces = $tempData['msg'];

      if ($_GET['oldimage'] != 'undefined') {
        if (file_exists($documentroot.'uploads'.DIRECTORY_SEPARATOR.$_GET['oldimage'])) {
          // если старая картинка используется только в одном варианте, то она будет удалена         
          $res = DB::query('SELECT image FROM `'.PREFIX.'product_variant` WHERE image = '.DB::quote($_GET['oldimage']));
          if (DB::numRows($res) === 1) {
            unlink($documentroot.'uploads'.DIRECTORY_SEPARATOR.$_GET['oldimage']);
          }
        }
      }
      return true;
    } else {
      $this->messageError = $tempData['msg'];
      return false;
    }
  }

  /**
   * Удаляет категорию.
   * @return type
   */
  public function deleteCategory() {
    $this->messageSucces = $this->lang['ACT_DEL_CAT'];
    $this->messageError = $this->lang['ACT_NOT_DEL_CAT'];
    return MG::get('category')->delCategory($_POST['id']);
  }

  /**
   * Удаляет страницу.
   * @return type
   */
  public function deletePage() {
    $this->messageSucces = $this->lang['ACT_DEL_PAGE'];
    $this->messageError = $this->lang['ACT_NOT_DEL_PAGE'];
    return MG::get('pages')->delPage($_POST['id']);
  }

  /**
   * Удаляет пользователя.
   * @return type
   */
  public function deleteUser() {
    $this->messageSucces = $this->lang['ACT_DEL_USER'];
    $this->messageError = $this->lang['ACT_NOT_DEL_USER'];
    return USER::delete($_POST['id']);
  }

  /**
   * Удаляет товар.
   * @return type
   */
  public function deleteProduct() {
    $this->messageSucces = $this->lang['ACT_DEL_PROD'];
    $this->messageError = $this->lang['ACT_NOT_DEL_PROD'];
    $model = new Models_Product;
    return $model->deleteProduct($_POST['id']);
  }

  /**
   * Удаляет заказ.
   * @return type
   */
  public function deleteOrder() {

    $this->messageSucces = $this->lang['ACT_DEL_ORDER'];
    $this->messageError = $this->lang['ACT_NOT_DEL_ORDER'];
    $model = new Models_Order;
    $model->refreshCountProducts($_POST['id'], 4);
    $this->data = array('count' => $model->getNewOrdersCount());
    return $model->deleteOrder($_POST['id']);
  }

  /**
   * Удаляет пользовательскую характеристику товара.
   * @return type
   */
  public function deleteUserProperty() {
    $res = DB::query('SELECT `plugin` FROM `'.PREFIX.'property` WHERE `id`='.DB::quote($_POST['id']));
    if ($row = DB::fetchArray($res)) {
      $pluginDirectory = PLUGIN_DIR.$row['plugin'].'/index.php';
      if ($row['plugin']&&  file_exists($pluginDirectory)) {
        $this->messageError = $this->lang['ACT_NOT_DEL_PROP_PLUGIN'];
        $result = false;
        return $result;
      }
    }
    $this->messageSucces = $this->lang['ACT_DEL_PROP'];
    $this->messageError = $this->lang['ACT_NOT_DEL_PROP'];
    $result = false;
    if (DB::query('
      DELETE
      FROM `'.PREFIX.'property`
      WHERE id = '.DB::quote($_POST['id'], true)) &&
      DB::query('
      DELETE
      FROM `'.PREFIX.'product_user_property`
      WHERE property_id = '.DB::quote($_POST['id'], true)) &&
      DB::query('
      DELETE
      FROM `'.PREFIX.'category_user_property`
      WHERE property_id = '.DB::quote($_POST['id'], true))
    ) {
      $result = true;
    }
    return $result;
  }

  /**
   * Удаляет категорию.
   * @return boolean
   */
  public function editCategory() {
    $this->messageSucces = $this->lang['ACT_EDIT_CAT'].' "'.$_POST['title'].'"';
    $this->messageError = $this->lang['ACT_NOT_EDIT_CAT'];

    $id = $_POST['id'];
    unset($_POST['id']);
    // Если назначаемая категория, является тойже.
    if ($_POST['parent'] == $id) {
      $this->messageError = $this->lang['ACT_ERR_EDIT_CAT'];
      return false;
    }

    $childsCaterory = MG::get('category')->getCategoryList($id);
    // Если есть вложенные, и одна из них назначена родительской.
    if (!empty($childsCaterory)) {
      foreach ($childsCaterory as $cateroryId) {
        if ($_POST['parent'] == $cateroryId) {
          $this->messageError = $this->lang['ACT_ERR_EDIT_CAT'];
          return false;
        }
      }
    }

    if ($_POST['parent'] == $id) {
      $this->messageError = $this->lang['ACT_ERR_EDIT_CAT'];
      return false;
    }
    return MG::get('category')->editCategory($id, $_POST);
  }

  /**
   * Сохраняет курс валют
   * @return boolean
   */
  public function saveCurrency() {
    $this->messageSucces = $this->lang['ACT_SAVE_CURR'];
    $this->messageError = $this->lang['ACT_NOT_SAVE_CURR'];

   
    foreach ($_POST['data'] as $currency) {
      $currency['iso'] =  htmlspecialchars($currency['iso']);
      $currency['short'] =  htmlspecialchars($currency['short']);
      $currency['rate'] =  (float)($currency['rate']);
      $currencyShopRate[$currency['iso']] = $currency['rate'];
      $currencyShopShort[$currency['iso']] = $currency['short'];
    }

    MG::setOption(array('option' => 'currencyRate', 'value' => addslashes(serialize($currencyShopRate))));
    MG::setOption(array('option' => 'currencyShort', 'value' => addslashes(serialize($currencyShopShort))));

    
    $settings = MG::get('settings');  
    $settings['currencyRate'] = $currencyShopRate;
    $settings['currencyShort'] = $currencyShopShort;
    MG::set('settings', $settings );

    
    $product = new Models_Product();
    $product->updatePriceCourse(MG::getSetting('currencyShopIso'));

    return true;
  }

  /** Применяет скидку/наценку ко всем вложенным подкатегориям
   */
  public function applyRateToSubCategory() {
    MG::get('category')->applyRateToSubCategory($_POST['id']);
    return true;
  }

  /**
   * Отменяет скидку и наценку для выбраной категории
   * @return boolean
   */
  public function clearCategoryRate() {
    $this->messageSucces = $this->lang['ACT_CLEAR_CAT_RATE'];
    MG::get('category')->clearCategoryRate($_POST['id']);
    return true;
  }

  /**
   * Сохраняет и обновляет параметры товара.
   * @return type
   */
  public function saveProduct() {

    $this->messageSucces = $this->lang['ACT_SAVE_PROD'];
    $this->messageError = $this->lang['ACT_NOT_SAVE_PROD'];   
    $model = new Models_Product;
    $itemId = 0;
    //Перед сохранением удалим все помеченные  картинки продукта физически с диска.        
    $_POST = $model->prepareImageName($_POST);
    
    $images = explode("|", $_POST['image_url']);
    
    foreach($_POST['variants'] as $cell=>$variant){     
      $images[] = $variant['image'];
    }
    
    
    if (!is_numeric($_POST['count'])) {
      $_POST['count'] = "-1";
    }

    // исключаем дублированные артикулы в строке связаных товаров
    if (!empty($_POST['related'])) {
      $_POST['related'] = implode(',', array_unique(explode(',', $_POST['related'])));
    }

    if (!empty($_POST['userProperty'])) {
      foreach ($_POST['userProperty'] as $k => $v) {
        $_POST['userProperty'][$k] = htmlspecialchars_decode($v);
      }
    }

    //Обновление
    if (!empty($_POST['id'])) {
      $itemId = $_POST['id'];
      $_POST['updateFromModal'] = true; // флаг, чтобы отличить откуда было обновление  товара
      $model->updateProduct($_POST);
      $_POST['image_url'] = $images[0];
      $_POST['currency'] = MG::getSetting('currency');
      $_POST['recommend'] = $_POST['recommend'];
      $tempProd = $model->getProduct($_POST['id']);     
      $arrVar = $model->getVariants($_POST['id']);
      foreach ($arrVar as $key => $variant) {
        $variant['image'] = basename($variant['image']); 
        $tempProd['variants'][] = $variant;
      }
     // $tempProd['variants'] = array($arrVar);
      $tempProd['real_price']=$tempProd['price'];     
      $this->data = $tempProd;
    } else {  // добавление
      unset($_POST['delete_image']);
      $newProd = $model->addProduct($_POST);
      $itemId = $newProd['id'];
      $this->data['image_url'] = $images[0];
      $this->data['currency'] = MG::getSetting('currency');
      $this->data['recommend'] = $_POST['recommend'];
      $tempProd = $model->getProduct($newProd['id']);     
      $arrVar = $model->getVariants($newProd['id']);
      foreach ($arrVar as $key => $variant) {
        $tempProd['variants'][] = $variant;
      }
     // $tempProd['variants'] = array($arrVar);
      $tempProd['real_price']=$tempProd['price']; 
      $this->data = $tempProd;
    }
    
    if($arImages = explode('|', $_POST['delete_image'])){
      $model->deleteImagesProduct($arImages, $itemId);
    }
        
    $model->movingProductImage($images, $itemId, 'uploads/prodtmpimg');
    $image = (empty($images[0])) ? 0 : $images[0];
    $this->data['image_url'] = mgImageProductPath($image, $itemId);
    
//    viewData($this->data['image_url']);
//    exit();
    
    return true;
  }

  /**
   * Обновляет параметры товара (быстрый вариант).
   * @return type
   */
  public function fastSaveProduct() {
    $this->messageSucces = $this->lang['ACT_SAVE_PROD'];
    $this->messageError = $this->lang['ACT_NOT_SAVE_PROD'];

    $model = new Models_Product;
    $variant = $_POST['variant'];
    
    unset($_POST['variant']);

    $arr = array(
      $_POST['field'] => $_POST['value']
    );
        // Обновление.
    if ($variant) {
      $model->fastUpdateProductVariant($_POST['id'], $arr, $_POST['product_id']);
      $arrVar = $model->getVariants($_POST['product_id']);
      foreach ($arrVar as $key => $variant) {
        if ($variant['id'] == $_POST['id']) {
          $this->data = MG::priceCourse($variant['price_course']);
        }
      }
      } else {
        $model->fastUpdateProduct($_POST['id'], $arr);
        $tempProd = $model->getProduct($_POST['id']);
        $this->data = MG::priceCourse($tempProd['price_course']);
      }
    return true;
  }

  /**
   * Перезаписывает новым значением, любое поле в любой таблице, в зависимости от входящих параметров.
   */
  public function fastSaveContent() {
    if (!DB::query('
       UPDATE `'.DB::quote($_POST['table'], true).'`
       SET `'.DB::quote($_POST['field'], true).'` = '.DB::quote($_POST['content']).'
       WHERE id = '.DB::quote($_POST['id'], true))) {
      return false;
    }
    return true;
  }

  /**
   * Устанавливает флаг для вывода продукта в блоке рекомендуемых товаров.
   * @return type
   */
  public function recomendProduct() {
	$this->messageSucces = $this->lang['ACT_PRINT_RECOMEND'];
    $this->messageError = $this->lang['ACT_NOT_PRINT_RECOMEND'];

    $model = new Models_Product;
    // Обновление.
    if (!empty($_POST['id'])) {
      $model->updateProduct($_POST);
    }

    if ($_POST['recommend']) {
      return true;
    }

    return false;
  }

  /**
   * Устанавливает флаг  активности продукта 
   * @return type
   */
  public function visibleProduct() {
    $this->messageSucces = $this->lang['ACT_V_PROD'];
    $this->messageError = $this->lang['ACT_UNV_PROD'];

    $model = new Models_Product;
    // Обновление.
    if (!empty($_POST['id'])) {
      $model->updateProduct($_POST);
    }

    if ($_POST['activity']) {
      return true;
    }

    return false;
  }

  /**
   * Устанавливает флаг  активности пользовательской характеристики 
   * @return type
   */
  public function visibleProperty() {
    $this->messageSucces = $this->lang['ACT_V_PROP'];
    $this->messageError = $this->lang['ACT_UNV_PROP'];

    // Обновление.
    if (!empty($_POST['id'])) {
      DB::query("
        UPDATE `".PREFIX."property`
        SET `activity`= ".DB::quote($_POST['activity'])." 
        WHERE `id` = ".DB::quote($_POST['id'], true)
      );
    }

    if ($_POST['activity']) {
      return true;
    }

    return false;
  }

  /**
   * Устанавливает флаг  использования в фильтрах указанных характеристик
   * @return type
   */
  public function filterProperty() {
    $this->messageSucces = 'Указанные характеристики будут выводиться в фильтрах';
    $this->messageError = '';

    // Обновление.
    if (!empty($_POST['id'])) {
      DB::query("
        UPDATE `".PREFIX."property`
        SET `filter`= ".DB::quote($_POST['filter'])." 
        WHERE `id` = ".DB::quote($_POST['id'], true)
      );
    }

    if ($_POST['filter']) {
      return true;
    }

    return false;
  }

  /**
   * Устанавливает флаг для использования характеристики в товарах
   * @return type
   */
  public function filterVisibleProperty() {
    $this->messageSucces = $this->lang['ACT_FILTER_PROP'];
    $this->messageError = $this->lang['ACT_UNFILTER_PROP'];

    // Обновление.
    if (!empty($_POST['id'])) {
      DB::query("
        UPDATE `".PREFIX."property`
        SET `filter`= ".DB::quote($_POST['filter'])." 
        WHERE `id` = ".DB::quote($_POST['id'], true)
      );
    }

    if ($_POST['filter']) {
      return true;
    }

    return false;
  }

  /**
   * Устанавливает флаг для вывода продукта в блоке новых товаров.
   * @return type
   */
  public function newProduct() {
    $this->messageSucces = $this->lang['ACT_PRINT_NEW'];
    $this->messageError = $this->lang['ACT_NOT_PRINT_NEW'];

    $model = new Models_Product;
    // Обновление.
    if (!empty($_POST['id'])) {
      $model->updateProduct($_POST);
    }

    if ($_POST['new']) {
      return true;
    }

    return false;
  }

  /**
   * Устанавливает флаг для выбранной страницы, чтобы выводить ее в главном меню.
   * @return type
   */
  public function printMainMenu() {
    $this->messageSucces = $this->lang['ADD_IN_MENU'];
    $this->messageError = $this->lang['NOT_ADD_IN_MENU'];


    // Обновление.
    if (!empty($_POST['id'])) {
      MG::get('pages')->updatePage($_POST);
    }

    if ($_POST['print_in_menu']) {
      return true;
    }

    return false;
  }

  /**
   * Печать заказа.
   */
  public function printOrder() {
    $this->messageSucces = $this->lang['ACT_PRINT_ORD'];
    $model = new Models_Order;
    $this->data = array('html' => $model->printOrder($_POST['id']));
    return true;
  }

  /**
   * Получает данные по промокоду.
   */
  public function getPromoCode() {
    $this->messageSucces = 'Скидка применена';
    // Заменить на получение скидки.
    $codes = array();
    // Запрос для проверки , существуют ли промокоды.  
    $result = DB::query('SHOW TABLES');
    while ($row = DB::fetchArray($result)) {
      if (PREFIX.'promo-code' == $row[0]) {
        $res = DB::query('SELECT code, percent FROM `'.PREFIX.'promo-code` WHERE invisible = 1');
        while ($code = DB::fetchAssoc($res)) {
          $codes[$code['code']] = $code['percent'];
        }
      };
    }
    $percent = $codes[$_POST['promocode']] ? $codes[$_POST['promocode']] : 0;
    $this->data = array('percent' => $percent, 'codes' => array('DEFAULT-DISCONT', 'DEFAULT-DISCONT2'));
    return true;
  }
  /**
   * Получает данные по промокоду.
   */
  public function getDiscount() {
    //viewData($_POST);
    // Заменить на получение скидки.
    $percent = 0;
    // Запрос для проверки , существуют ли промокоды.  
    if ($_POST['promocode']) {
      $result = DB::query('SHOW TABLES LIKE "'.PREFIX.'promo-code"');
      if (DB::numRows($result)) {
        $sql = 'SELECT * FROM `'.PREFIX.'promo-code` 
          WHERE `code` ='.DB::quote($_POST['promocode']).'
           AND `invisible` = 1 
           AND now() >= `from_datetime`
           AND now() <= `to_datetime`';
        $res = DB::query($sql);

          if ($code = DB::fetchAssoc($res)) {
            $percent = $code['percent'] ? $code['percent'] : 0;
            
        };
      }        
    }
    $percent = (float) $percent;
    if ($_POST['cumulative']=='true' || $_POST['volume']=='true')  {
      $result = DB::query('SHOW TABLES LIKE "'.PREFIX.'discount-system%"');
      if (DB::numRows($result)) {
        if ($_POST['cumulative'] == 'true' && $_POST['email']) {
          $sql = "SELECT SUM(`summ`) as summ FROM  `".PREFIX."order`       
            WHERE  `user_email` =  ".DB::quote($_POST['email'])." 
            AND ( `status_id` =  '2'
            OR  `status_id` =  '5')";
        $res = DB::query($sql);
        if ($count = DB::fetchAssoc($res)) {
          $sql = "SELECT * FROM `".PREFIX."discount-system-cumulative` 
            WHERE `summ` <= ".DB::quote($count['summ'])." ORDER BY `summ` DESC LIMIT 1";
          $res = DB::query($sql);
          if ($discount = DB::fetchAssoc($res)) {
            $percent += (float) $discount['percent'];
          }
        }
      } 
      if ($_POST['volume'] == 'true' && ($_POST['summ'] > 0)) {
        $sql = 'SELECT * FROM `'.PREFIX.'discount-system-volume` 
          WHERE `summ` <= '.DB::quote($_POST['summ']).' ORDER BY `summ` DESC LIMIT 1';
        $rez = DB::query($sql);
        if ($discount = DB::fetchArray($rez)) {
          $percent += (float) $discount['percent'];
        }
      }
    }
    }
    $this->data['percent'] = $percent;
    return true;
  }

  /**
   * Получает данные по вводимому email в форме заказа.
   * @return boolean
   */
  public function getUserEmail() {
    $emails = array('mark-avdeev@mail.ru', 'mark-avdeev2@mail.ru');
    $this->data = $emails;
    return true;
  }

  /**
   * Сохраняет и обновляет параметры заказа.
   * @return type
   */
  public function saveOrder() {
    $this->messageSucces = $this->lang['ACT_SAVE_ORD'];
    $this->messageError = $this->lang['ACT_SAVE_ORDER'];

    if (count($_POST['order_content']) != $_POST['orderPositionCount']) {
      $this->messageError = 'Невозможно передать столь большой заказ на ваш сервер. Необходимо изменить настройки web-сервера!';
      return false;
    }
    unset($_POST['orderPositionCount']);

    // Cобираем воедино все параметры от юр. лица если они были переданы, для записи в информацию о заказе.
    $_POST['yur_info'] = '';
    $informUser = $_POST['inform_user'];
    unset($_POST['inform_user']);
    
    if (!empty($_POST['inn'])) {
      $_POST['yur_info'] = array(
        'email' => htmlspecialchars($_POST['orderEmail']),
        'name' => htmlspecialchars($_POST['orderBuyer']),
        'address' => htmlspecialchars($_POST['orderAddress']),
        'phone' => htmlspecialchars($_POST['orderPhone']),
        'inn' => htmlspecialchars($_POST['inn']),
        'kpp' => htmlspecialchars($_POST['kpp']),
        'nameyur' => htmlspecialchars($_POST['nameyur']),
        'adress' => htmlspecialchars($_POST['adress']),
        'bank' => htmlspecialchars($_POST['bank']),
        'bik' => htmlspecialchars($_POST['bik']),
        'ks' => htmlspecialchars($_POST['ks']),
        'rs' => htmlspecialchars($_POST['rs']),
      );
    }

    $model = new Models_Order;

    // Обновление.
    if (!empty($_POST['id'])) {
      unset($_POST['inn']);
      unset($_POST['kpp']);
      unset($_POST['nameyur']);
      unset($_POST['adress']);
      unset($_POST['bank']);
      unset($_POST['bik']);
      unset($_POST['ks']);
      unset($_POST['rs']);
      unset($_POST['ogrn']);

      if (!empty($_POST['yur_info'])) {
        $_POST['yur_info'] = addslashes(serialize($_POST['yur_info']));
      }

      foreach ($_POST['order_content'] as &$item) {
        foreach ($item as &$v) {
          $v = rawurldecode($v);
        }
      }
      $_POST['delivery_cost'] = MG::numberDeFormat($_POST['delivery_cost']);
      $_POST['order_content'] = addslashes(serialize($_POST['order_content']));
      $model->refreshCountAfterEdit($_POST['id'], $_POST['order_content']);
      
      $model->updateOrder($_POST, $informUser);
    } else {
      return false;
    }

    $_POST['count'] = $model->getNewOrdersCount();
    $_POST['date'] = MG::dateConvert(date('d.m.Y H:i'));
    $this->data = $_POST;
    return true;
  }

  /**
   * Сохраняет и обновляет параметры категории.
   * @return type
   */
  public function saveCategory() {
    $this->messageSucces = $this->lang['ACT_SAVE_CAT'];
    $this->messageError = $this->lang['ACT_NOT_SAVE_CAT'];
    $_POST['image_url'] = $_POST['image_url'] ? str_replace(SITE, '', $_POST['image_url']) : '';
    $_POST['parent_url'] = MG::get('category')->getParentUrl($_POST['parent']);
    // Обновление.
    if (!empty($_POST['id'])) {
      if (MG::get('category')->updateCategory($_POST)) {
        $this->data = $_POST;
      } else {
        return false;
      }
    } else {  // добавление
      $this->data = MG::get('category')->addCategory($_POST);
    }
    return true;
  }

  /**
   * Сохраняет и обновляет параметры страницы.
   * @return type
   */
  public function savePage() {
    $this->messageSucces = $this->lang['ACT_SAVE_PAGE'];
    $this->messageError = $this->lang['ACT_NOT_SAVE_PAGE'];

    $_POST['parent_url'] = MG::get('pages')->getParentUrl($_POST['parent']);
    // Обновление.
    if (!empty($_POST['id'])) {
      if (MG::get('pages')->updatePage($_POST)) {
        $this->data = $_POST;
      } else {
        return false;
      }
    } else {  // добавление
      $this->data = MG::get('pages')->addPage($_POST);
    }
    return true;
  }

  /**
   * Делает страницу невидимой в меню.
   * @return type
   */
  public function invisiblePage() {

    $this->messageError = $this->lang['ACT_NOT_SAVE_PAGE'];
    if ($_POST['invisible'] === "1") {
      $this->messageSucces = $this->lang['ACT_UNV_PAGE'];
    } else {
      $this->messageSucces = $this->lang['ACT_V_PAGE'];
    }
    // Обновление.
    if (!empty($_POST['id']) && isset($_POST['invisible'])) {
      MG::get('pages')->updatePage($_POST);
    } else {
      return false;
    }
    return true;
  }

  /**
   * Делает категорию невидимой в меню.
   * @return type
   */
  public function invisibleCat() {

    $this->messageError = $this->lang['ACT_NOT_SAVE_CAT'];
    if ($_POST['invisible'] === "1") {
      $this->messageSucces = $this->lang['ACT_UNV_CAT'];
    } else {
      $this->messageSucces = $this->lang['ACT_V_CAT'];
    }
    // Обновление.
    if (!empty($_POST['id']) && isset($_POST['invisible'])) {
      MG::get('category')->updateCategory($_POST);
    } else {
      return false;
    }
    return true;
  }
  
  /**
   * Устанавливает флаг экпорта категории
   * @return type
   */
  public function exportCatStatus() {

    $this->messageError = $this->lang['ACT_NOT_SAVE_CAT'];
    if ($_POST['export'] === "1") {
      $this->messageSucces = $this->lang['ACT_EXPORT_CAT'];
    } else {
      $this->messageSucces = $this->lang['ACT_NOT_EXPORT_CAT'];
    }
    // Обновление.
    if (!empty($_POST['id']) && isset($_POST['export'])) {
      MG::get('category')->updateCategory($_POST);
      $childIds = MG::get('category')->getCategoryList($_POST['id']);
      foreach($childIds as $id){
        $_POST['id'] = $id;
        MG::get('category')->updateCategory($_POST);
      }
    } else {
      return false;
    }
    return true;
  }

  /**
   * Делает все страницы видимыми в меню.
   * @return type
   */
  public function refreshVisiblePage() {
    MG::get('pages')->refreshVisiblePage();
    $this->messageSucces = $this->lang['ACT_PINT_IN_MENU'];
    return true;
  }

  /**
   * Сохраняет и обновляет параметры пользователя.
   * @return type
   */
  public function saveUser() {
    $this->messageSucces = $this->lang['ACT_SAVE_USER'];
    $this->messageError = $this->lang['ACT_NOT_SAVE_USER'];
    if ($_POST['role'] == '3'||$_POST['role'] == '4'){
     return false;
    }
    // Обновление.
    if (!empty($_POST['id'])) {

      // если пароль не передан значит не обновляем его
      if (empty($_POST['pass'])) {
        unset($_POST['pass']);
      } else {
        $_POST['pass'] = crypt($_POST['pass']);
      }

      //вычисляем надо ли перезаписать данные текущего пользователя после обновления
      //(только в том случае если из админки меняется запись текущего пользователя)
      $authRewrite = $_POST['id'] != User::getThis()->id ? true : false;

      // если происходит попытка создания нового администратора от лица модератора, то вывести ошибку
      if ($_POST['role'] == '1') {
        if (!USER::AccessOnly('1')) {
          return false;
        }
      }
      if ($_POST['birthday']) {
        $_POST['birthday'] = date('Y-m-d', strtotime($_POST['birthday']));  
      }
      if (User::update($_POST['id'], $_POST, $authRewrite)) {
        $this->data = $_POST;
      } else {
        return false;
      }
    } else {  // добавление	
      if ($_POST['role'] == '1') {
        if (!USER::AccessOnly('1')) {
          return false;
        }
      }

      try {
        $_POST['id'] = User::add($_POST);
      } catch (Exception $exc) {
        $this->messageError = $this->lang['ACT_ERR_SAVE_USER'];
        return false;
      }

      //отправка письма с информацией о регистрации
      $siteName = MG::getSetting('sitename');
      $userEmail = $_POST['email'];
      $message = '
        Здравствуйте!<br>
          Вы получили данное письмо так как на сайте '.$siteName.' зарегистрирован новый пользователь с логином '.$userEmail.'.<br>
          Отвечать на данное сообщение не нужно.';
      $emailData = array(
        'nameFrom' => $siteName,
        'emailFrom' => MG::getSetting('noReplyEmail'),
        'nameTo' => 'Пользователю сайта '.$siteName,
        'emailTo' => $userEmail,
        'subject' => 'Активация пользователя на сайте '.$siteName,
        'body' => $message,
        'html' => true
      );
      Mailer::sendMimeMail($emailData);

      $_POST['date_add'] = date('d.m.Y H:i');
      $this->data = $_POST;
    }
    return true;
  }

  /**
   * Изменяет настройки.
   * @return boolean
   */
  public function editSettings() {
    $this->messageSucces = $this->lang['ACT_SAVE_SETNG'];
    Storage::clear();
    if (!empty($_POST['options'])) {

      // если произошла смена валюты магазина, то пересчитываем курсы
      $currencyShopIso = MG::getSetting('currencyShopIso');
      if ($_POST['options']['currencyShopIso'] != MG::getSetting('currencyShopIso')) {
        $currencyRate = MG::getSetting('currencyRate');
        $currencyShort = MG::getSetting('currencyShort');

        $product = new Models_Product();
        $product->updatePriceCourse($_POST['options']['currencyShopIso']);

        //  $currencyRate[$currencyShopIso] = 1/$currencyRate[$_POST['options']['currencyShopIso']];
        $rate = $currencyRate[$_POST['options']['currencyShopIso']];
        $currencyRate[$_POST['options']['currencyShopIso']] = 1;
        foreach ($currencyRate as $iso => $value) {
          if ($iso != $_POST['options']['currencyShopIso']) {
            if (!empty($rate)) {
              $currencyRate[$iso] = $value / $rate;
            }
          }
        }
       DB::query("UPDATE `".PREFIX."delivery` SET cost = cost * ".$currencyRate[$currencyShopIso]." , free = free * ".$currencyRate[$currencyShopIso]);
        
        MG::setOption(array('option' => 'currencyRate', 'value' => addslashes(serialize($currencyRate))));

        // echo $_POST['options']['currencyShopIso'];      
      }

      $errorMemcache = false;
      foreach ($_POST['options'] as $option => $value) {

        if ($value == 'MEMCACHE' && !class_exists('Memcache')) {
          $value = 'DB';
          $this->messageError = 'Невозможно использовать кэширование MEMCACHE';
          $errorMemcache = true;
        }
        if (!DB::query("UPDATE `".PREFIX."setting` SET `value`=".DB::quote($value)." WHERE `option`=".DB::quote($option)."")) {
          return false;
        }
      }
      if ($errorMemcache) {
        return false;
      }
      return true;
    }
  }

  /**
   * Получает параметры редактируемого продукта.
   */
  public function getProductData() {
    $this->messageError = $this->lang['ACT_NOT_GET_POD'];

    $model = new Models_Product;
    $product = $model->getProduct($_POST['id']);
    
    $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');
    foreach ($product as $k => $v) {
       if(in_array($k, $maskField)){
        $product[$k] = htmlspecialchars_decode($v);  
       }
    }
    
    if (empty($product)) {
      return false;
    }
    $this->data = $product;

    foreach($this->data['images_product'] as $cell => $image){
      $this->data['images_product'][$cell] = mgImageProductPath($image, $product['id']);
    }
    
    // Получаем весь набор пользовательских характеристик.
    $res = DB::query("SELECT * FROM `".PREFIX."property`");
    while ($userFields = DB::fetchAssoc($res)) {
      $this->data['allProperty'][] = $userFields;
    }

    $variants = $model->getVariants($_POST['id']);
    foreach ($variants as $variant) {
      $variant['image'] = mgImageProductPath($variant['image'], $product['id'], 'small');
      $this->data['variants'][] = $variant;
    }

    $stringRelated = ' null';
    $sortRelated = array();
    foreach (explode(',', $product['related']) as $item) {
      $stringRelated .= ','.DB::quote($item);
      if (!empty($item)) {
        $sortRelated[$item] = $item;
      }
    }
    $stringRelated = substr($stringRelated, 1);


    //$productsRelated = $model->getProductByUserFilter(' id IN ('.($product['related']?$product['related']:'0').')');
    $res = DB::query('
      SELECT  CONCAT(c.parent_url,c.url) as category_url,
        p.url as product_url, p.id, p.image_url,p.price_course as price,p.title,p.code
      FROM `'.PREFIX.'product` p
        LEFT JOIN `'.PREFIX.'category` c
        ON c.id = p.cat_id
      WHERE p.code IN ('.$stringRelated.')');

    while ($row = DB::fetchAssoc($res)) {
      $img = explode('|', $row['image_url']);
      $row['image_url'] = $img[0];
      $sortRelated[$row['code']] = $row;
    }
    $productsRelated = array();
    //сортируем связанные товары в том порядке, в котором они идут в строке артикулов

    if (!empty($sortRelated)) {
      foreach ($sortRelated as $item) {
        if (is_array($item)) {
          $item['image_url'] = mgImageProductPath($item['image_url'], $item['id'], 'small');
          $productsRelated[] = $item;
        }
      }
    }
    $this->data['relatedArr'] = $productsRelated;

    $_POST['produtcId'] = $_POST['id'];
    $_POST['categoryId'] = $product['cat_id'];
    $tempDataResult = $this->data;
    $this->data = null;
    $this->getProdDataWithCat();
    $tempDataResult['prodData'] = $this->data;
    $this->data = $tempDataResult;
    //$this->data['prodData'] = $this->getProdDataWithCat();

    return true;
  }

  /**
   * Получает параметры для категории продуктов.
   */
  public function getProdDataWithCat() {
    $this->data['allProperty'] = array();
    $this->data['thisUserFields'] = array();

    // Получаем заданные ранее пользовательские характеристики для редактируемого товара.
    $res = DB::query("
        SELECT pup.property_id, pup.value, pup.product_margin, pup.type_view, prop.*
        FROM `".PREFIX."product_user_property` as pup
        LEFT JOIN `".PREFIX."property` as prop ON pup.property_id = prop.id
        WHERE pup.`product_id` = ".DB::quote($_POST['produtcId']));

    while ($userFields = DB::fetchAssoc($res)) {
      $this->data['thisUserFields'][] = $userFields;
    }

    // Получаем набор пользовательских характеристик предназначенных для выбраной категории.
    $res = DB::query("
        SELECT *
        FROM `".PREFIX."category_user_property` as сup
        LEFT JOIN `".PREFIX."property` as prop ON сup.property_id = prop.id
        WHERE сup.`category_id` = ".DB::quote($_POST['categoryId']).' ORDER BY sort DESC');
    $alreadyProp = array();
    while ($userFields = DB::fetchAssoc($res)) {
      $this->data['allProperty'][] = $userFields;
      $alreadyProp[$userFields['property_id']] = true;
    }

    // Получаем набор пользовательских характеристик.
    // Предназначенных для всех категорий и приплюсовываем его к уже имеющимя характеристикам выбраной категории.
    /* $res = DB::query("SELECT * FROM `".PREFIX."property` WHERE all_category = 1");
      while ($userFields = DB::fetchAssoc($res)) {
      if (empty($alreadyProp[$userFields['id']])) {
      $this->data['allProperty'][] = $userFields;
      $alreadyProp[$userFields['id']];
      }
      } */

    return true;
  }

  /**
   * Получает пользовательские поля для добавления нового продукта.
   */
  public function getUserProperty() {
    if ($_POST['cat_id']) {
      $res = DB::query("
        SELECT distinct prop.id, prop.* FROM `".PREFIX."category_user_property` AS cup
        LEFT JOIN `".PREFIX."property` as prop ON cup.property_id = prop.id
        WHERE cup.category_id = ".DB::quote($_POST['cat_id'])."
        ORDER BY sort DESC
        ");
   
     while ($userFields = DB::fetchAssoc($res)) {
      if($userFields['id']){
        $this->data['allProperty'][] = $userFields;      
      } 
     }   
    } else {
      $res = DB::query("SELECT * FROM `".PREFIX."property` ORDER BY sort DESC");
      while ($userFields = DB::fetchAssoc($res)) {
        $this->data['allProperty'][] = $userFields;
      }
    }
    return true;
  }

  /**
   * Получает привязку пользовательского свойства к набору категорий.
   */
  public function getConnectionCat() {
    $id = $_POST['id'];
    $categoryIds = array();
    // Получчаем список выбраных категорий дл данной характеристики.
    $res = DB::query("
        SELECT category_id
        FROM `".PREFIX."category_user_property` as сup
        WHERE сup.`property_id` = %s", $id);

    while ($row = DB::fetchAssoc($res)) {
      $categoryIds[] = $row['category_id'];
    }

    $this->data['selectedCatIds'] = implode(',', $categoryIds);
    $listCategories = MG::get('category')->getCategoryTitleList(0);
    $arrayCategories = MG::get('category')->getHierarchyCategory(0);
    $html = MG::get('category')->getTitleCategory($arrayCategories, 0);
    $this->data['optionHtml'] = $html;

    return true;
  }

  /**
   * Добавляет новую характеристику.
   */
  public function addUserProperty() {
    $this->messageSucces = $this->lang['ACT_ADD_POP'];
    $res = DB::query("
       INSERT INTO `".PREFIX."property`
       VALUES ('','-','string','','','1','1','','1','','checkbox','','')"
    );
    if ($id = DB::insertId()) {
      DB::query("
       UPDATE `".PREFIX."property`
       SET `sort`=`id` WHERE `id` = ".DB::quote($id)
      );
      $this->data['allProperty'] = array(
        'id' => $id,
        'name' => '-',
        'type' => 'string',
        'data' => '-',
        'default' => '',
        'activity' => '1',
        'description' => '',
        'type_filter' => 'checkbox',       
        'sort' => $id,
      );
    }
    return true;
  }

  /**
   * Сохраняет пользовательские настройки для товаров.
   */
  public function saveUserProperty() {
    $result = false;
    $this->messageSucces = $this->lang['ACT_EDIT_POP'];
    $id = $_POST['id'];
    $array = $_POST;
    if (!empty($id)) {
      unset($array['id']);
      $res = DB::query('SELECT `plugin`, `type` FROM `'.PREFIX.'property` WHERE `id`='.DB::quote($_POST['id']));
      if ($row = DB::fetchArray($res)) {
        $pluginDirectory = PLUGIN_DIR.$row['plugin'].'/index.php';
        if ($row['plugin'] && file_exists($pluginDirectory)) {
          $this->messageSucces = $this->lang['ACT_EDIT_POP_PLUGIN'];
          $this->data['type'] = $row['type'];
          unset($array['type']);
          $result = true;
        }
      }

      if ($array['type'] == 'string') {
        $array['data'] = htmlspecialchars_decode($array['data']);
        $array['default'] = htmlspecialchars_decode($array['default']);
        $array['default'] = '';
      }
      if ($array['type'] == 'textarea') {
        $array['data'] = htmlspecialchars_decode($array['data']);
        $array['default'] = '';
        $array['filter'] = 0;
      }

      // обновление значений характеристики
      if (DB::query('
        UPDATE `'.PREFIX.'property`
        SET '.DB::buildPartQuery($array).'
        WHERE id ='.DB::quote($id))) {
        $result = true;
      }

      // обновление  списков допустимых значний в имеющихся продуктах
      if ($array['type'] == 'select' || $array['type'] == 'assortment') {
        if (DB::query('
          UPDATE `'.PREFIX.'product_user_property`
          SET `product_margin` = '.DB::quote($array['data']).'
          WHERE property_id ='.DB::quote($id))) {
          $result = true;
        }
      }
    }
    return $result;
  }

  /**
   * Сохраняет привязку выбранных категорий для характеристики.
   */
  public function saveUserPropWithCat() {
    $this->messageSucces = $this->lang['ACT_EDIT_POP']; 
    $category = array();
    if (!empty($_POST['category'])) {
      $category = explode("|", $_POST['category']);
    }
    $propId = $_POST['id'];

    $catAlreadyThisProp = array();
    $res = DB::query('
        SELECT `category_id`
        FROM `'.PREFIX.'category_user_property`
        WHERE `property_id` ='.$propId
    );

    while ($row = DB::fetchAssoc($res)) {
      $catAlreadyThisProp[] = $row['category_id'];
    }
   
    // удалаляем все привязки характеристики к категориям сделанные ранее
    DB::query('
        DELETE FROM `'.PREFIX.'category_user_property`
        WHERE property_id = '.DB::quote($propId));

    $poductIdForCreate = array();
    $propertyDefault = null;
    $catAlreadyThisProp = array_intersect($catAlreadyThisProp, $category);
    $catAlreadyThisProp = array_unique($catAlreadyThisProp);

    if (!empty($category)) {
      foreach ($category as $cat_id) {
        DB::query("
            INSERT IGNORE INTO `".PREFIX."category_user_property`
            VALUES ('%s', '%s')"
          , $cat_id, $propId);

        $propertyDefault = '';
        $res = DB::query('
             SELECT id
             FROM `'.PREFIX.'product`
             WHERE cat_id ='.$cat_id
        );

        while ($row2 = DB::fetchAssoc($res)) {
          $poductIdForCreate[] = $row2['id'];
        }
      }
    }

    $poductIdForCreate = array_unique($poductIdForCreate);

   /*
    $catAlreadyThisProp = implode(',', $catAlreadyThisProp);
    if (!empty($catAlreadyThisProp)) {
      $where = 'cat_id NOT IN ('.DB::quote($catAlreadyThisProp, true).') and';
    }


    DB::query('
        DELETE pup.* FROM `'.PREFIX.'product_user_property` as pup
        LEFT JOIN `'.PREFIX.'product` as p ON pup.product_id = p.id
        WHERE '.$where.'
          pup.property_id ='.$propId.'
          '
    );
    
   */
    $allCategory = empty($_POST['category']) ? 1 : 0;

    // Обновлем флаг , использовать во всех категориях.
    DB::query('
        UPDATE `'.PREFIX.'property`
        SET all_category = '.$allCategory.'
        WHERE id = '.DB::quote($propId));

    return true;
  }

  /**
   * Получает параметры редактируемого пользователя.
   */
  public function getUserData() {
    $this->messageError = $this->lang['ACT_GET_USER'];
    $response = USER::getUserById($_POST['id']);
    foreach ($response as $k => $v) {
        if($k!='pass'){
          $response->$k = htmlspecialchars_decode($v);  
        }
    }
    $this->data = $response;
    return false;
  }

  /**
   * Получает параметры категории.
   */
  public function getCategoryData() {
    $this->messageError = $this->lang['ACT_NOT_GET_CAT'];
    $result = DB::query("
      SELECT * FROM `".PREFIX."category`
      WHERE `id` =".DB::quote($_POST['id'])
    );
    if ($response = DB::fetchAssoc($result)) {
      $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');
      foreach ($response as $k => $v) {
         if(in_array($k, $maskField)){
          $response[$k] = htmlspecialchars_decode($v);  
         }
      }
      $this->data = $response;
      return true;
    } else {
      return false;
    }

    return false;
  }

  /**
   * Получает параметры редактируемой страницы.
   */
  public function getPageData() {
    $this->messageError = $this->lang['ACT_SAVE_SETNG'];
    $result = DB::query("
      SELECT * FROM `".PREFIX."page`
      WHERE `id` =".DB::quote($_POST['id'])
    );
    if ($response = DB::fetchAssoc($result)) {
      $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');
      foreach ($response as $k => $v) {
         if(in_array($k, $maskField)){
          $response[$k] = htmlspecialchars_decode($v);  
         }
      }
      $this->data = $response;
      return true;
    } else {
      return false;
    }

    return false;
  }

  /**
   * Устанавливает порядок сортировки. Меняет местами две категории.
   */
  public function changeSortCat() {
    $switchId = $_POST['switchId'];
    $sequence = explode(',', $_POST['sequence']);
    if (!empty($sequence)) {
      foreach ($sequence as $item) {
        MG::get('category')->changeSortCat($switchId, $item);
      }
    } else {
      $this->messageError = $this->lang['ACT_NOT_GET_CAT'];
      return false;
    }

    $this->messageSucces = $this->lang['ACT_SWITH_CAT'];
    return true;
  }

  /**
   * Устанавливает порядок сортировки. Меняет местами две страницы.
   */
  public function changeSortPage() {
    $switchId = $_POST['switchId'];
    $sequence = explode(',', $_POST['sequence']);
    if (!empty($sequence)) {
      foreach ($sequence as $item) {
        //MG::get('category')->changeSortCat($switchId, $item);
        MG::get('pages')->changeSortPage($switchId, $item);
      }
    } else {
      $this->messageError = $this->lang['ACT_NOT_GET_PAGE'];
      return false;
    }

    $this->messageSucces = $this->lang['ACT_SWITH_PAGE'];
    return true;
  }

  /**
   * Устанавливает порядок сортировки. Меняет местами две записи
   */
  public function changeSortRow() {
    $switchId = $_POST['switchId'];
    $tablename = $_POST['tablename'];
    $sequence = explode(',', $_POST['sequence']);
    if (!empty($sequence)) {
      foreach ($sequence as $item) {
        MG::changeRowsTable($tablename, $switchId, $item);
      }
    } else {
      return false;
    }

    $this->messageSucces = $this->lang['ACT_SWITH'];
    return true;
  }

  /**
   * Возвращает ответ в формате JSON.
   * @param boolean $flag - если отработаный метод что-то вернул, то ответ считается успешным ждущей его фунции.
   * @return boolean
   */
  public function jsonResponse($flag) {
    if ($flag === null) {
      return false;
    }
    if ($flag) {
      $this->jsonResponseSucces($this->messageSucces);
    } else {
      $this->jsonResponseError($this->messageError);
    }
  }

  /**
   * Возвращает положительный ответ с сервера.
   * @param string $message
   */
  public function jsonResponseSucces($message) {
    $result = array(
      'data' => $this->data,
      'msg' => $message,
      'status' => 'success');
    echo json_encode($result);
  }

  /**
   * Возвращает отрицательный ответ с сервера.
   * @param string $message
   */
  public function jsonResponseError($message) {
    $result = array(
      'data' => $this->data,
      'msg' => $message,
      'status' => 'error');
    echo json_encode($result);
  }

  /**
   * Проверяет актуальность текущей версии системы.
   * @return void возвращает в AJAX сообщение о результате операции.
   */
  public function checkUpdata() {
    $msg = Updata::checkUpdata();

    if ($this->lang['ACT_THIS_LAST_VER'] == $msg['msg']) {
      $status = 'alert';
    } else {
      $status = 'success';
    }
    $response = array(
      'msg' => $msg['msg'],
      'status' => $status,
    );

    echo json_encode($response);
    exit;
  }

  /**
   * Обновленяет верcию CMS.
   *
   * @return void возвращает в AJAX сообщение о результате операции.
   */
  public function updata() {
    $version = $_POST['version'];

    if (Updata::updataSystem($version)) {
      $msg = $this->lang['ACT_UPDATE_VER'];
      $status = 'success';
    } else {
      $msg = $this->lang['ACT_ERR_UPDATE_VER'];
      $status = 'error';
    }

    $response = array(
      'msg' => $msg,
      'status' => $status,
    );

    echo json_encode($response);
  }

  /**
   * Отключает публичную часть сайта. Обычно требуется для внесения изменений администратором.
   * @return bool
   */
  public function downTime() {
    $downtime = MG::getOption('downtime');

    if ('Y' == $downtime) {
      $activ = 'N';
    } else {
      $activ = 'Y';
    }

    $res = DB::query('
      UPDATE `'.PREFIX.'setting`
      SET `value` = "'.$activ.'"
      WHERE `option` = "downtime"
    ');

    if ($res) {
      return true;
    };
  }

  /**
   * Функцию отправляет на сервер обновления информацию о системе и в случае одобрения скачивает архив с обновлением.
   * @return void возвращает в AJAX сообщение загруженную в систему версию.
   */
  public function preDownload() {
    $this->messageSucces = $this->lang['ACT_UPLOAD_ZIP']." ".$_POST['version'];
    $this->messageError = $this->lang['ACT_NOT_UPLOAD_ZIP'];
    $result = Updata::preDownload($_POST['version']);

    if (!empty($result['status'])) {
      if ($result['status'] == 'error') {
        $this->messageError = $result['msg'];
        return false;
      }
      return true;
    }


    return false;
  }

  /**
   * Установливает загруженный ранее архив с обновлением.
   * @return void возвращает в AJAX сообщение о результате операции.
   */
  public function postDownload() {
    $this->messageSucces = $this->lang['ACT_UPDATE_TRUE'].$_POST['version'];
    $this->messageError = $this->lang['ACT_NOT_UPDATE_TRUE'];

    $version = $_POST['version'];

    if (Updata::extractZip($version.'-m.zip')) {
      $this->messageSucces = $this->lang['ACT_UPDATE_VER'];
      return true;
    } else {
      $this->messageError = $this->lang['ACT_ERR_UPDATE_VER'];
      return false;
    }
    return false;
  }

  /**
   * Устанавливает цветовую тему для меню в административном разделе
   * @return boolean
   */
  public function setTheme() {

    if ($_POST['color']) {
      MG::setOption(array('option' => 'themeColor', 'value' => $_POST['color']));
      MG::setOption(array('option' => 'themeBackground', 'value' => $_POST['background']));
    }
    return true;
  }

  /**
   * Устанавливает язык в административном разделе.
   * @return boolean
   */
  public function changeLanguage() {

    if ($_POST['language']) {
      MG::setOption(array('option' => 'languageLocale', 'value' => $_POST['language']));
    }
    
    // создает js массив локали из php файла. Требуется использовать когда вносятсяизменения в локали.
    $createJsLocale = 0;
    if($createJsLocale){
      $documentRoot = URL::getDocumentRoot();
      require ($documentRoot.ADMIN_DIR.'locales/'.$_POST['language'].'.php');
      $locale = 'var lang = '.json_encode($lang,JSON_UNESCAPED_UNICODE);
      file_put_contents($documentRoot.ADMIN_DIR.'locales/'.$_POST['language'].'.js', $locale);
    }
    
    return true;
  }

  /**
   * Устанавливает количество отображаемых записей в разделе товаров
   * @return boolean
   */
  public function setCountPrintRowsProduct() {

    $count = 20;
    if (is_numeric($_POST['count']) && !empty($_POST['count'])) {
      $count = $_POST['count'];
    }


    MG::setOption(array('option' => 'countPrintRowsProduct', 'value' => $count));
    return true;
  }

  /**
   * Устанавливает количество отображаемых записей в разделе страницы
   * @return boolean
   */
  public function setCountPrintRowsPage() {

    $count = 20;
    if (is_numeric($_POST['count']) && !empty($_POST['count'])) {
      $count = $_POST['count'];
    }


    MG::setOption(array('option' => 'countPrintRowsPage', 'value' => $count));
    return true;
  }

  /**
   * Устанавливает количество отображаемых записей в разделе пользователей
   * @return boolean
   */
  public function setCountPrintRowsOrder() {

    $count = 20;
    if (is_numeric($_POST['count']) && !empty($_POST['count'])) {
      $count = $_POST['count'];
    }

    MG::setOption(array('option' => 'countPrintRowsOrder', 'value' => $count));
    return true;
  }

  /**
   * Устанавливает количество отображаемых записей в разделе заказов
   * @return boolean
   */
  public function setCountPrintRowsUser() {

    $count = 20;
    if (is_numeric($_POST['count']) && !empty($_POST['count'])) {
      $count = $_POST['count'];
    }

    MG::setOption(array('option' => 'countPrintRowsUser', 'value' => $count));
    return true;
  }

  /**
   * Возвращает список найденых продуктов по ключевому слову
   * @return boolean
   */
  public function searchProduct() {
    $this->messageSucces = $this->lang['SEACRH_PRODUCT'];
    $model = new Models_Catalog;

    $_POST['mode']=$_POST['mode']?$_POST['mode']:false;
    $arr = $model->getListProductByKeyWord($_POST['keyword'], true, false, true, $_POST['mode']);
  
    if (empty($arr)) {  
      $arr['catalogItems'] = array();
    }

    $this->data = $arr['catalogItems'];
    return true;
  }

  /**
   * Устанавливает локаль для плагина, используется в JS плагинов
   * @return boolean
   */
  public function seLocalesToPlug() {
    $this->data = PM::plugLocales($_POST['pluginName']);
    return true;
  }

  /**
   * Сохранение способа доставки
   */
  public function saveDeliveryMethod() {
    $this->messageSucces = $this->lang['ACT_SUCCESS'];
    $this->messageError = $this->lang['ACT_ERROR'];
    $status = $_POST['status'];
    $deliveryName = htmlspecialchars($_POST['deliveryName']);
    $deliveryCost = (float)$_POST['deliveryCost'];
    $deliveryId = (int)$_POST['deliveryId'];    
    $free = 0;

    $paymentMethod = $_POST['paymentMethod'];
    $paymentArray = json_decode($paymentMethod, true);

    $deliveryDescription = htmlspecialchars($_POST['deliveryDescription']);
    $deliveryActivity = $_POST['deliveryActivity'];
    $deliveryDate = $_POST['deliveryDate'];
    $deliveryYmarket= $_POST['deliveryYmarket'];
    switch ($status) {
      case 'createDelivery':
        $sql = "
          INSERT INTO `".PREFIX."delivery` (`name`,`cost`, `description`, `activity`,`free`, `date`, `ymarket`  )
          VALUES (
            ".DB::quote($deliveryName).", ".DB::quote($deliveryCost).", ".DB::quote($deliveryDescription).", ".DB::quote($deliveryActivity).", ".DB::quote($free).", ".DB::quote($deliveryDate).", ".DB::quote($deliveryYmarket)." 
          );
        ";

        $result = DB::query($sql);

        if ($deliveryId = DB::insertId()) {
          DB::query(" UPDATE `".PREFIX."delivery` SET `sort`=`id` WHERE `id` = ".DB::quote($deliveryId));
          $status = 'success';
          $msg = $this->lang['ACT_SUCCESS'];
        } else {
          $status = 'error';
          $msg = $this->lang['ACT_ERROR'];
        }

        foreach ($paymentArray as $paymentId => $compare) {
          $sql = "
            INSERT INTO `".PREFIX."delivery_payment_compare`
              (`compare`,`payment_id`, `delivery_id`)
            VALUES (
              ".DB::quote($compare).", ".DB::quote($paymentId).", ".DB::quote($deliveryId)."
            );
          ";
          $result = DB::query($sql);
        }

        break;
      case 'editDelivery':
        $sql = "
          UPDATE `".PREFIX."delivery`
          SET `name` = ".DB::quote($deliveryName).",
              `cost` = ".DB::quote($deliveryCost).",
              `description` = ".DB::quote($deliveryDescription).",
              `activity` = ".DB::quote($deliveryActivity).",
              `free` = ".DB::quote($free).",
              `date` = ".DB::quote($deliveryDate).",
              `ymarket` = ".DB::quote($deliveryYmarket)."
          WHERE id = ".DB::quote($deliveryId);
        $result = DB::query($sql);

        foreach ($paymentArray as $paymentId => $compare) {
          $result = DB::query("
            SELECT * 
            FROM `".PREFIX."delivery_payment_compare`         
            WHERE `payment_id` = ".DB::quote($paymentId)."
              AND `delivery_id` = ".DB::quote($deliveryId));
          if (!DB::numRows($object)) {
            $sql = "
                INSERT INTO `".PREFIX."delivery_payment_compare`
                  (`compare`,`payment_id`, `delivery_id`)
                VALUES (
                  ".DB::quote($compare).", ".DB::quote($paymentId).", ".DB::quote($deliveryId)."
                );
              ";
            $result = DB::query($sql);
          } else {
            $sql = "
              UPDATE `".PREFIX."delivery_payment_compare`
              SET `compare` = ".DB::quote($compare)."
              WHERE `payment_id` = ".DB::quote($paymentId)."
                AND `delivery_id` = ".DB::quote($deliveryId);
            $result = DB::query($sql);
          }
        }
       

        if ($result) {
          $status = 'success';
          $msg = $this->lang['ACT_SUCCESS'];
        } else {
          $status = 'error';
          $msg = $this->lang['ACT_ERROR'];
        }
    }
     if ($deliveryYmarket == 1) {
          DB::query(" UPDATE `".PREFIX."delivery` SET `ymarket`=0 WHERE `id` != ".DB::quote($deliveryId));
        }
        
    $response = array(
      'data' => array(
        'id' => $deliveryId,
      ),
      'status' => $status,
      'msg' => $msg,
    );
    echo json_encode($response);
  }

  /**
   * Удаляет способ доставки.
   * @return boolean
   */
  public function deleteDeliveryMethod() {
    $this->messageSucces = $this->lang['ACT_SUCCESS'];
    $this->messageError = $this->lang['ACT_ERROR'];
    $res1 = DB::query('DELETE FROM `'.PREFIX.'delivery` WHERE `id`= '.DB::quote($_POST['id']));
    $res2 = DB::query('DELETE FROM `'.PREFIX.'delivery_payment_compare` WHERE `delivery_id`= '.DB::quote($_POST['id']));

    if ($res1 && $res2) {
      return true;
    }
    return false;
  }

  /**
   * Сохраняет способ оплаты.
   */
  public function savePaymentMethod() {
    $paymentParam = str_replace("'", "\'", $_POST['paymentParam']);

    $deliveryMethod = $_POST['deliveryMethod'];
    $deliveryArray = json_decode($deliveryMethod, true);
    $paymentActivity = $_POST['paymentActivity'];
    $paymentId = $_POST['paymentId'];

    if (is_array($deliveryArray)) {
      foreach ($deliveryArray as $deliveryId => $compare) {
        $sql = "
          UPDATE `".PREFIX."delivery_payment_compare`
          SET `compare` = ".DB::quote($compare)."
          WHERE `payment_id` = ".DB::quote($paymentId)."
            AND `delivery_id` = ".DB::quote($deliveryId);
        $result = DB::query($sql);
      }
    }
    $newparam = array();
    $param = json_decode($paymentParam);
    foreach ($param as $key=>$value) {
      if ($value != '') {
        $value = CRYPT::mgCrypt($value);
      }
      $newparam[$key] = $value;
      }
    $paymentParamEncoded = CRYPT::json_encode_cyr($newparam);
    
    $sql = "
      UPDATE `".PREFIX."payment`
      SET `name` = ".DB::quote($_POST['name']).",     
          `paramArray` = ".DB::quote($paymentParamEncoded).",
          `activity` = ".DB::quote($paymentActivity)."
      WHERE id = ".$paymentId;
    $result = DB::query($sql);

    if ($result) {
      $status = 'success';
      $msg = $this->lang['ACT_SUCCESS'];
    } else {
      $status = 'error';
      $msg = $this->lang['ACT_ERROR'];
    }

    $sql = "
      SELECT *
      FROM `".PREFIX."payment`     
      WHERE id = ".$paymentId;
    $result = DB::query($sql);
    if ($row = DB::fetchAssoc($result)) {
      $newparam = array();
      $param = json_decode($row['paramArray']);
      foreach ($param as $key=>$value) {
        if ($value != '') {
          $value = CRYPT::mgDecrypt($value);
        }
        $newparam[$key] = $value;
        }
      $paymentParam = CRYPT::json_encode_cyr($newparam);
    }

    $response = array(
      'status' => $status,
      'msg' => $msg,
      'data' => array('paymentParam' => $paymentParam)
    );
    echo json_encode($response);
  }

  /**
   * Обновляет способов оплаты и доставки при переходе по вкладкам в админке.
   */
  public function getMethodArray() {
    $mOrder = new Models_Order;
    $deliveryArray = $mOrder->getDeliveryMethod();
    $response['data']['deliveryArray'] = $deliveryArray;

    $paymentArray = array();
    $i = 1;
    while ($payment = $mOrder->getPaymentMethod($i)) {
      $paymentArray[$i] = $payment;
      $i++;
    }
    $response['data']['paymentArray'] = $paymentArray;
    echo json_encode($response);
  }

  /**
   * Проверяет наличие подключенного модуля xmlwriter и библиотеки libxml.
   */
  public function existXmlwriter() {
    $this->messageSucces = 'Начата генерация файла';
    $this->messageError = 'Отсутствует необходимое PHP расширение xmlwriter или модуль libxml';
    if (LIBXML_VERSION && extension_loaded('xmlwriter')) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Осуществляет импорт данных в таблицы продуктов и категорий.
   */
  public function importFromCsv() {
    $this->messageSucces = 'Импорт выполнен';
    $this->messageError = 'Ошибка';
    $importer = new Import();
    $importer->ImportFromCSV();
    return true;
  }

  /**
   * Получает файл шаблона.
   */
  public function getTemplateFile() {
    $this->messageError = $this->lang['NOT_FILE_TPL'];
    $pathTemplate  = 'mg-templates'.DIRECTORY_SEPARATOR.MG::getSetting('templateName');
    if (file_exists($pathTemplate.$_POST['path']) && is_writable($pathTemplate.$_POST['path'])) {
      $this->data['filecontent'] = file_get_contents($pathTemplate.$_POST['path']);
      return true;
    } else {
      $this->data['filecontent'] = "CHMOD = ".substr(sprintf('%o', fileperms($pathTemplate.$_POST['path'])), -4);
      return true;
    }
    return false;
  }

  /**
   * Сохраняет файл шаблона.
   */
  public function saveTemplateFile() {
    $this->messageSucces = $this->lang['SAVE_FILE_TPL'];
    $pathTemplate = 'mg-templates'.DIRECTORY_SEPARATOR.MG::getSetting('templateName');
    if (file_exists($pathTemplate.$_POST['filename']) && is_writable($pathTemplate.$_POST['filename'])) {
      file_put_contents($pathTemplate.$_POST['filename'], $_POST['content']);
    } else {
      return false;
    }
    return true;
  }

  /**
   * Очищает кеш проверки версий и проверяет наличие новой.
   */
  public function clearLastUpdate() {
    if (!$checkLibs = MG::libExists()) {
      MG::setOption('timeLastUpdata', '');
      $newVer = Updata::checkUpdata(true);
      if (!$newVer) {
        $this->messageError = "Пока нет новых версий";
        return false;
      }
      $this->messageSucces = "Доступна новая версия ".$newVer['lastVersion'];
      return true;
    } else {
      $this->messageError = "Невозможно проверить наличие версий. Библиотека CURL отключена";
      return false;
    }
  }

  /**
   * Получает список продуктов при вводе в поле поиска товара при создании заказа через админку.
   */
  public function getSearchData() {
    $keyword = URL::getQueryParametr('search');
    if (!empty($keyword)) {
      $catalog = new Models_Catalog;
      $product = new Models_Product;
      $order = new Models_Order;
      $currencyRate = MG::getSetting('currencyRate');
      $currencyShort = MG::getSetting('currencyShort');
      $currencyShopIso = MG::getSetting('currencyShopIso');
      $items = $catalog->getListProductByKeyWord($keyword, true, false, true);//добавление к заказу из админки товара, который не выводится в каталог.
      
      $blockedProp = $product->noPrintProperty();

      foreach ($items['catalogItems'] as $key => $item) {
        $items['catalogItems'][$key]['image_url'] = mgImageProductPath($item["image_url"], $item['id'], 'small');

        $propertyFormData = $product->createPropertyForm($param = array(
          'id' => $item['id'],
          'maxCount' => 999,
          'productUserFields' => $item['thisUserFields'],
          'action' => "/catalog",
          'method' => "POST",
          'ajax' => true,
          'blockedProp' => $blockedProp,
          'noneAmount' => true,
          'titleBtn' => "<span>".$this->lang['EDIT_ORDER_14']."</span>",
          'blockVariants' => $product->getBlockVariants($item['id']),
          'classForButton' => 'addToCart buy-product buy custom-btn',
          'printCompareButton' => false,
          'currency_iso' => $item['currency_iso'],
          
        ));
       
        $items['catalogItems'][$key]['price'] = $items['catalogItems'][$key]['price']; 
        $items['catalogItems'][$key]['propertyForm'] = $propertyFormData['html'];
        $items['catalogItems'][$key]['notSet'] = $order->notSetGoods($item['id']);
      }
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

    echo json_encode($searchData);
    exit;
  }

  /**
   * Возвращает случайный продукт из ассортимента.
   * @return boolean
   */
  public function getRandomProd() {
    $res = DB::query('
      SELECT id 
      FROM `'.PREFIX.'product` 
        WHERE 1=1 
      ORDER BY RAND() LIMIT 1');
    if ($row = DB::fetchAssoc($res)) {
      $product = new Models_Product();
      $prod = $product->getProduct($row['id']);
      $prod['image_url'] = mgImageProductPath($prod['image_url'], $prod['id']);      
    } else {
      return false;
    }
    $this->data['product'] = $prod;
    return true;
  }

  /**
   * Возвращает список заказов для вывода статистики по заданному периоду.
   * @return boolean
   */
  public function getOrderPeriodStat() {
    $model = new Models_Order;
    $this->data = $model->getStatisticPeriod($_POST['from_date_stat'], $_POST['to_date_stat']);
    return true;
  }

  /**
   * Возвращает список заказов для вывода статистики.
   * @return boolean
   */
  public function getOrderStat() {
    $model = new Models_Order;
    $this->data = $model->getOrderStat();
    return true;
  }

  /**
   * Выполняет операцию над отмеченными заказами в админке.
   * @return boolean
   */
  public function operationOrder() {
    $model = new Models_Order;
    $operation = $_POST['operation'];
    if (empty($_POST['orders_id'])) {
      $this->messageError = 'Необходимо отметить заказы!';
      return false;
    }
    if ($operation == 'delete') {
      foreach ($_POST['orders_id'] as $orderId) {
      $model->refreshCountProducts($orderId, 4);
      }
      $result = $model->deleteOrder(true, $_POST['orders_id']);
    } elseif (strpos($operation, 'status_id') === 0 && !empty($_POST['orders_id'])) {
      foreach ($_POST['orders_id'] as $orderId) {
        $result = $model->updateOrder(array('id' => $orderId, 'status_id' => substr($operation, -1, 1)));
      }
    }

    $this->data = array('count' => $model->getNewOrdersCount());
    return $result;
  }

  /**
   * Выполняет операцию над отмеченными характеристиками в админке.
   * @return boolean
   */
  public function operationProperty() {

    $operation = $_POST['operation'];
    if (empty($_POST['property_id'])) {
      $this->messageError = 'Необходимо отметить характеристики!';
      return false;
    }
    if ($operation == 'delete') {
      foreach ($_POST['property_id'] as $propertyId) {
        $_POST['id'] = $propertyId;
        $this->deleteUserProperty();
      }
    } elseif (strpos($operation, 'activity') === 0 && !empty($_POST['property_id'])) {
      foreach ($_POST['property_id'] as $propertyId) {
        $_POST['id'] = $propertyId;
        $_POST['activity'] = substr($operation, -1, 1);
        $this->visibleProperty();
      }
    } elseif (strpos($operation, 'filter') === 0 && !empty($_POST['property_id'])) {
      foreach ($_POST['property_id'] as $propertyId) {
        $_POST['id'] = $propertyId;
        $_POST['filter'] = substr($operation, -1, 1);
        $this->filterProperty();
      }
    }
    return true;
  }

  /**
   * Выполняет операцию над отмеченными товарами в админке.
   * @return boolean
   */
  public function operationProduct() {
    $productModel = new Models_Product();
    $operation = $_POST['operation'];
    if (empty($_POST['products_id'])) {
      $this->messageError = 'Необходимо отметить товары!';
      return false;
    }
    if ($operation == 'delete') {
      foreach ($_POST['products_id'] as $productId) {
        $productModel->deleteProduct($productId);
      }
    } elseif (strpos($operation, 'activity') === 0 && !empty($_POST['products_id'])) {
      foreach ($_POST['products_id'] as $product) {
        $productModel->updateProduct(array('id' => $product, 'activity' => substr($operation, -1, 1)));
      }
    } elseif (strpos($operation, 'recommend') === 0 && !empty($_POST['products_id'])) {
      foreach ($_POST['products_id'] as $product) {
        $productModel->updateProduct(array('id' => $product, 'recommend' => substr($operation, -1, 1)));
      }
    } elseif (strpos($operation, 'new') === 0 && !empty($_POST['products_id'])) {
      foreach ($_POST['products_id'] as $product) {
        $productModel->updateProduct(array('id' => $product, 'new' => substr($operation, -1, 1)));
      }
    } elseif (strpos($operation, 'clone') === 0 && !empty($_POST['products_id'])) {
      foreach ($_POST['products_id'] as $product) {
        $productModel->cloneProduct($product);
      }
    } elseif (strpos($operation, 'delete') === 0 && !empty($_POST['products_id'])) {
      foreach ($_POST['products_id'] as $product) {
        $productModel->deleteProduct($product);
      }
    } elseif (strpos($operation, 'changecur') === 0 && !empty($_POST['products_id'])) {
      foreach ($_POST['products_id'] as $product) {
        $part = explode('_', $operation);   
        $iso = str_replace($part[0].'_','',$operation);

        $productModel->convertToIso($iso, $_POST['products_id']);
        $this->data['clearfilter'] = true;
        //$result = $model->updateOrder(array('id' => $orderId, 'status_id' => substr($operation, -1, 1)));
      }
    }elseif (strpos($operation, 'getcsv') === 0 && !empty($_POST['products_id'])) {     
        $catalogModel = new Models_Catalog();
        $filename = $catalogModel->exportToCsv($_POST['products_id']); 
        $this->data['filecsv'] = $filename;          
        $this->messageSucces = 'Товары импортированы успешно в файл '.$filename;
    }elseif (strpos($operation, 'getyml') === 0 && !empty($_POST['products_id'])) { 
        if (LIBXML_VERSION && extension_loaded('xmlwriter')) {
          $ymlLib = new YML();
          $filename = $ymlLib->exportToYml($_POST['products_id']);      
          $this->data['fileyml'] = $filename;
          $this->messageSucces = 'Товары импортированы успешно в файл '.$filename;
        } else {
          $this->messageError = 'Отсутствует необходимое PHP расширение: xmlwriter';         
        }       
    }

    return true;
  }

  /**
   * Выполняет операцию над отмеченными категориями в админке.
   * @return boolean
   */
  public function operationCategory() {

    $operation = $_POST['operation'];

    if (empty($_POST['category_id'])) {
      $this->messageError = 'Необходимо отметить категории!';
      return false;
    }
    if ($operation == 'delete') {
      foreach ($_POST['category_id'] as $catId) {
        MG::get('category')->delCategory($catId);
      }
    } elseif (strpos($operation, 'invisible') === 0 && !empty($_POST['category_id'])) {
      foreach ($_POST['category_id'] as $catId) {
        MG::get('category')->updateCategory(array('id' => $catId, 'invisible' => substr($operation, -1, 1)));
      }
    }
    Storage::clear(md5('category'));
    return true;
  }
 /**
   * Выполняет операцию над отмеченными страницами в админке
   * @return boolean
   */
  public function operationPage() {

    $operation = $_POST['operation'];

    if (empty($_POST['page_id'])) {
      $this->messageError = 'Необходимо отметить страницы!';
      return false;
    }
    if ($operation == 'delete') {
      foreach ($_POST['page_id'] as $pageId) {
        MG::get('pages')->delPage($pageId);
      }
    } elseif (strpos($operation, 'invisible') === 0 && !empty($_POST['page_id'])) {
      foreach ($_POST['page_id'] as $pageId) {
        MG::get('pages')->updatePage(array('id' => $pageId, 'invisible' => substr($operation, -1, 1)));
      }
    }
    return true;
  }

  /**
   * Получает параметры заказа
   */
  public function getOrderData() {

    $model = new Models_Order();
    $orderData = $model->getOrder(" id = ".DB::quote($_POST['id']));
    $orderData = $orderData[$_POST['id']];
    
    if ($orderData['number']=='') {
      $orderData['number'] = $orderData['id'];
      DB::query("UPDATE `".PREFIX."order` SET `number`= ".DB::quote($orderData['number'])." WHERE `id`=".DB::quote($orderData['id'])."");
    } 
      
    $orderData['yur_info'] = unserialize(stripslashes($orderData['yur_info']));
    $orderData['order_content'] = unserialize(stripslashes($orderData['order_content']));
    // Запрос для проверки, существует ли система скидок
    $percent = false;
    $discountSyst = false;
    $res = DB::query('SELECT * FROM `'.PREFIX.'plugins` WHERE `folderName` = "discount-system"');
    $act = DB::fetchArray($res);
    $result = DB::query('SHOW TABLES LIKE "'.PREFIX.'discount-system%"');
    if ((DB::numRows($result) == 2)&&($act['active'])){        
      $percent = 0; 
      $discountSyst = true;
    }     
    if (!empty($orderData['order_content'])) {
      $product = new Models_Product();

      foreach ($orderData['order_content'] as &$item) {
        foreach ($item as &$v) {
          $v = rawurldecode($v);
        }
      }

      foreach ($orderData['order_content'] as &$items) {
        $res = $product->getProduct($items['id']);
        $items['image_url'] = mgImageProductPath($res['image_url'], $items['id'], 'small');
        $items['property'] = htmlspecialchars_decode(str_replace('&amp;', '&', $items['property']));
        $response['discount'] = $items['discount'];
        $percent = $items['discount'];
        $items['maxCount'] = $res['count'];
        $variants = DB::query("SELECT `id`, `count` FROM `".PREFIX."product_variant`
                  WHERE `product_id`=".DB::quote($items['id'])." AND `code`=".DB::quote($items['code']));
        if ($variant = DB::fetchAssoc($variants)) {
          $items['variant'] = $variant['id'];
          $items['maxCount'] = $variant['count'];
        }
        $items['notSet'] = $model->notSetGoods($items['id']);
      }
    }

    //заменить на получение скидки
    $codes = array();
  
    
    // Запрос для проверки , существуют ли промокоды.  
    $result = DB::query('SHOW TABLES LIKE "'.PREFIX.'promo-code"');
    if (DB::numRows($result)) {
      $res = DB::query('SELECT * FROM `'.PREFIX.'plugins` WHERE `folderName` = "promo-code"');
      $act = DB::fetchArray($res);
      if ($act['active']) {
        $res = DB::query('SELECT code, percent FROM `'.PREFIX.'promo-code` 
          WHERE invisible = 1 
          AND now() >= `from_datetime`
          AND now() <= `to_datetime`');
        while ($code = DB::fetchAssoc($res)) {
          $codes[] = $code['code'];
          if ($code['code'] == $orderData['order_content'][0]['coupon']) {
            $percent = $percent== 0 ? $code['percent'] : $percent;
          }
        }
      };
    }

    $response['order'] = $orderData;
    $response['order']['discountsSystem'] = $discountSyst;
    $response['order']['discontPercent'] = $percent;
    $response['order']['promoCodes'] = $codes;
    $response['order']['date_delivery'] = $orderData['date_delivery'] ? date('d.m.Y', strtotime($orderData['date_delivery'])) : '';
    $deliveryArray = $model->getDeliveryMethod();
    $response['deliveryArray'] = $deliveryArray;
    $paymentArray = array();
    $i = 1;
    while ($payment = $model->getPaymentMethod($i)) {
      $paymentArray[$i] = $payment;
      $i++;
    }
    $response['paymentArray'] = $paymentArray;    
    $this->data = $response;
    return true;
  }

  /**
   * Устанавливает флаг редактирования сайта.
   * @return boolean
   */
  public function setSiteEdit() {
    Storage::clear();
    MG::setOption(array('option' => 'enabledSiteEditor', 'value' => $_POST['enabled']));
    return true;
  }

  /**
   * Очишает таблицу с кэшем объектов
   * @return boolean
   */
  public function clearСache() {
    Storage::clear();
    return true;
  }
  
  
  /**
   * Удаляет папку с собранными картинками для минифицированного css
   * @return boolean
   */
  public function clearImageCssСache() {
    MG::clearMergeStaticFile(PATH_TEMPLATE.'/cache/');
	  MG::createImagesForStaticFile(); 
    return true;
  }

  /**
   * Возвращает список найденых продуктов по ключевому слову.
   * @return boolean
   */
  public function uploadCsvToImport() {

    $uploader = new Upload(false);
    $tempData = $uploader->addImportCatalogCSV();

    $this->data = array('img' => $tempData['actualImageName']);

    if ($tempData['status'] == 'success') {
      $this->messageSucces = $tempData['msg'];
      return true;
    } else {
      $this->messageError = $tempData['msg'];
      return false;
    }
  }

  /**
   * Импортирует данные из файла importCatalog.csv.
   * @return boolean
   */
  public function startImport() {
    $this->messageSucces = "Процесс запущен";
    $this->messageError = "Неудалось начать импорт";

    $import = new Import($_POST['typeCatalog']);

    if (empty($_POST['rowId'])) {
      unset($_SESSION['stopProcessImportCsv']);
    }

    if ($_POST['delCatalog'] !== null) {
      if ($_POST['delCatalog'] === "true") {
        DB::query('TRUNCATE TABLE `'.PREFIX.'cache`');
        if ($_POST['rowId'] == 0) {
          DB::query('TRUNCATE TABLE `'.PREFIX.'product_variant`');
          DB::query('TRUNCATE TABLE `'.PREFIX.'product`');
          DB::query('TRUNCATE TABLE `'.PREFIX.'product_user_property`');
          DB::query('TRUNCATE TABLE `'.PREFIX.'category`');
          DB::query('TRUNCATE TABLE `'.PREFIX.'category_user_property`');
          /* 
           * Характеристики не удаляются потому что их id могут использоваться 
           * для кастомизированного вывода, и могут быть характеристики созданные из плагинов.
           */
        }
      }
    }

    $this->data = $import->startUpload($_POST['rowId']);
    if($this->data['status']=='error'){
      $this->messageError = $this->data['msg'].'';
      return false;
    }
    return true;
  }

  /**
   * Останавливает процесс импорта каталога из файла importCatalog.csv.
   * @return boolean
   */
  public function canselImport() {
    $this->messageSucces = "Процесс прерван пользователем";
    $this->messageError = "Неудалось отменить импорт";

    $import = new Import();
    $import->stopProcess();

    return true;
  }

  /**
   * Сохраняет реквизиты в настройках заказа.
   * @return boolean
   */
  public function savePropertyOrder() {
    $this->messageSucces = "Настройки сохранены";
    $this->messageError = "Неудалось сохранить настройки";

    $propertyOrder = serialize($_POST);
    $propertyOrder = addslashes($propertyOrder);
    MG::setOption(array('option' => 'propertyOrder', 'value' => $propertyOrder));

    return true;
  }

  /**
   * Получает данные об ошибке произошедшей в админке и отправляет на support@moguta.ru.
   * @return boolean
   */
  public function sendBugReport() {
    $this->messageSucces = "Отчет отправлен";
    $this->messageError = "Неудалось отправить отчет";

    $body .= 'Непредвиденная ошибка на сайте '.$_SERVER['SERVER_NAME'];
    $body .= '<br/><br/><br/><strong>Информация о системе</strong>';
    $body .= '<br/>Версия Moguta.CMS: '.VER;
    $body .= '<br/>Версия php: '.phpversion();
    $body .= '<br/>USER_AGENT: '.$_SERVER['HTTP_USER_AGENT'];
    $body .= '<br/>IP: '.$_SERVER['SERVER_ADDR'];

    $body .= '<br/><strong>Информация о магазине</strong>';
    $product = new Models_Product;
    $body .= '<br/>Количество товаров: '.$product->getProductsCount();
    $body .= '<br/>Количество категорий: '.MG::get('category')->getCategoryCount();
    $body .= '<br/>Шаблон: '.MG::getSetting('templateName');
    $body .= '<br/>E-mail администратора: '.MG::getSetting('adminEmail');

    $body .= '<br/><strong>Баг-репорт</strong>';
    $body .= '<br/>'.$_POST['text'];
    $body .= '<br/><br/><img alt="Embedded Image" src="data:'.$_POST['screen'].'" />';
    Mailer::addHeaders(array("Reply-to" => MG::getSetting('adminEmail')));
    Mailer::sendMimeMail(array(
      'nameFrom' => MG::getSetting('adminEmail'),
      'emailFrom' => MG::getSetting('adminEmail'),
      'nameTo' => "support@moguta.ru",
      'emailTo' => "support@moguta.ru",
      'subject' => "Отчет об ошибке с сайта ".$_SERVER['SERVER_NAME'],
      'body' => $body,
      'html' => true
    ));

    return true;
  }

  /**
   * Устанавливает тестовое соединение с сервером Memcache
   * @return boolean
   */
  public function testMemcacheConection() {
    $this->messageSucces = "Настройки корректны, соединение с Memcache установлено успешно.";
    $this->messageError = "Не удалось установить соединение с сервером Memcache по адресу ".$_POST['host'].":".$_POST['port'];



    if (class_exists('Memcache')) {
      $memcacheObj = new Memcache();
      $memcacheObj->connect($_POST['host'], $_POST['port']);
      $this->messageSucces.= " Версия: ".$memcacheObj->getVersion();
      $ver = $memcacheObj->getVersion();
      if (!empty($ver)) {
        return true;
      }

      $this->messageError = 'Не установлен PHP модуль для работы с Memcache';
      return false;
    }
    return false;
  }

  /**
   * Упорядочивает всё дерево категорий по алфавиту 
   * @return boolean
   */
  public function sortToAlphabet(){
    MG::get('category')->sortToAlphabet();
    return true;
  }
  
  /**
   * Выполняет операцию над отмеченными пользователями в админке.
   * @return boolean
   */
  public function operationUser() {
    $operation = $_POST['operation'];
    if (empty($_POST['users_id'])) {
      $this->messageError = 'Необходимо отметить пользователей!';
      return false;
    }
    if ($operation == 'delete') {
      foreach ($_POST['users_id'] as $userId) {
        $del = USER::delete($userId);
        if (!$del) {
          $this->messageSucces = 'Удалены отмеченные пользователи, кроме администратора!';
        }
      }
    } 
    $this->messageSucces = 'Выделенные пользователи удалены!';
    return true;
  }
  /**
   * Получает следующий id для таблицы продуктов
   * @return boolean
   */
  public function nextIdProduct() {
    $result['id'] = 0;
    USER::AccessOnly('1,4','exit()');
    $res = DB::query('SHOW TABLE STATUS WHERE Name =  "'.PREFIX.'product" ');
    if ($row = DB::fetchArray($res)) {
      $result['id'] = $row['Auto_increment'];
    }
    $this->data = $result;
    return true;
  }
 
  
}
