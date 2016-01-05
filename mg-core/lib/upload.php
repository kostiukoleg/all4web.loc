<?php

/**
 * Класс для загрузки изображений на сервер, в том числе и через ckeditor.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Upload {

  public $lang = array();

  public function __construct($ckeditMode = true, $uploadDir = '') {

    include('mg-admin/locales/'.MG::getOption('languageLocale').'.php');
    $this->lang = $lang;
    if ($ckeditMode) {
      $uploaddir = 'uploads';
      $arrData = $this->addImage(false, false, $uploadDir);
      $msg = $arrData['msg'];
      if ($arrData['status'] == "error") {
        echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.$_REQUEST['CKEditorFuncNum'].',  "'.$full_path.'","'.$arrData['msg'].'" );</script>';
      } else {
        $full_path = SITE.'/uploads/'.$arrData['actualImageName'];
        echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'.$_REQUEST['CKEditorFuncNum'].'",  "'.$full_path.'","'.$arrData['msg'].'" );</script>';
      }
    }
  }

  /**
   * Загружает картинку из формы на сервер
   * @return boolean
   */
  public function addImage($productImg = false, $watermark = false, $addPath = '') {

    $path = 'uploads/';
    
    if($_COOKIE['type'] == 'plugin'){
      //Если из плагина не задан параметр для обработки изображений как для товаров
      if(empty($_SESSION[$_COOKIE['section'].'-upload-to-product'])){
        $addPath = $_COOKIE['section'];
      }
    }

    $validFormats = array('jpeg', 'jpg', 'png', 'gif', 'JPG');
    if ($watermark) {
      $path.="watermark/";
      if (!file_exists('uploads/watermark/')) {
        if (is_writable('uploads/')) {
          chdir('uploads/'); //путь где создавать папку		
          mkdir('watermark', 0755); //имя папки и атрибуты на папку	
          return array('msg' => "Папка для знака была восстановлена. Теперь можно загрузить картинку.", 'status' => 'success');
        }
      }
      $validFormats = array('png');
    }

    if (isset($_POST) && 'POST' == $_SERVER['REQUEST_METHOD']) {

      if (!empty($_FILES['upload'])) {
        $file_array = $_FILES['upload'];
      } elseif (!empty($_FILES['photoimg'])) {
        $file_array = $_FILES['photoimg'];
      } else {
        $file_array = $_FILES['edit_photoimg'];
      }

      $name = $file_array['name'];
      $size = $file_array['size'];

      if (strlen($name)) {
        //list($txt, $ext) = explode('.', $name);
        $fullName = explode('.', $name);
        $ext = array_pop($fullName);
        $name = implode('.', $fullName);
        if (in_array(strtolower($ext), $validFormats)) {
          if ($size < (1024 * 5 * 1024) && !empty($file_array['tmp_name'])) { //$file_array['tmp_name'] будет пустым если размер загруженного файла превышает размер установленный параметром upload_max_filesize в php.ini
            $name = rawurldecode($name);
            $name = str_replace(array(" ", "%"), array("-", ""), $name);    
            $name = MG::translitIt($name);
            $actualImageName = $this->prepareName($name, $ext);
            if ($watermark) {
              $actualImageName = 'watermark.png';
            }
            $tmp = $file_array['tmp_name'];
            
            if($productImg && !$watermark){
              $addPath = 'prodtmpimg';
            }
            
            if(!empty($addPath)){  //Если задана дополнительная директория для изображения
              if(!file_exists('uploads/'.$addPath.'/')){ //Проверяем наличие папки
                $curDir = getcwd();
                chdir('uploads/'); 
                mkdir($addPath, 0755);  //Создаем папку для изображений
                chdir($curDir);
              }
              $addPath .= '/';
              $path .= $addPath;
            }
            
            if (move_uploaded_file($tmp, $path.$actualImageName)) {
              
              if (MG::getSetting("waterMark") == "true" && !$watermark) {
                if (empty($_POST['noWaterMark'])) {
                  $this->addWatterMark($path.$actualImageName);
                }
              }

              //если картинка заливаются для продукта, то делаем две миниатюры
              if ($productImg && !$watermark) {
                
                if(!file_exists('uploads/'.$addPath.'thumbs/')){
                  $curDir = getcwd();
                  chdir('uploads/'.$addPath); 
                  mkdir('thumbs', 0755);  //Создаем папку для изображений
                  chdir($curDir);
                }
                //подготовка миниатюр с заданными в настройках размерами
                // preview по заданным в настройках размерам
                $widthPreview = MG::getSetting('widthPreview') ? MG::getSetting('widthPreview') : 200;
                $widthSmallPreview = MG::getSetting('widthSmallPreview') ? MG::getSetting('widthSmallPreview') : 50;
                $heightPreview = MG::getSetting('heightPreview') ? MG::getSetting('heightPreview') : 100;
                $heightSmallPreview = MG::getSetting('heightSmallPreview') ? MG::getSetting('heightSmallPreview') : 50;
                $this->_reSizeImage('70_'.$actualImageName, $path.$actualImageName, $widthPreview, $heightPreview, 'PROPORTIONAL', 'uploads/'.$addPath.'thumbs/');
                // миниатюра по размерам из БД (150*100)
                $this->_reSizeImage('30_'.$actualImageName, $path.$actualImageName, $widthSmallPreview, $heightSmallPreview, 'PROPORTIONAL', 'uploads/'.$addPath.'thumbs/');
              }

              return array('msg' => $this->lang['ACT_IMG_UPLOAD'], 'actualImageName' => $addPath.$actualImageName, 'status' => 'success');
            } else {
              return array('msg' => $this->lang['ACT_IMG_NOT_UPLOAD'], 'status' => 'error');
            }
          } else {
            return array('msg' => $this->lang['ACT_IMG_NOT_UPLOAD1'], 'status' => 'error');
          }
        } else {
          return array('msg' => $this->lang['ACT_IMG_NOT_UPLOAD2'], 'status' => 'error');
        }
      } else {
        return array('msg' => $this->lang['ACT_IMG_NOT_UPLOAD3'], 'status' => 'error');
      }
    }
    return false;
  }

  /**
   * Проверяет существует ли уже в папке uploads файл с таким же именем.
   * Чтобы не перезатереть его  имя текущего файла будет дополненно индексом.
   * @return boolean
   */
  public function prepareName($name, $ext) {
    if (file_exists('uploads/'.$name.".".$ext)) {
      return $name.time().".".$ext;
    }
    return $name.".".$ext;
  }

  /**
   * Функция для масштабирования изображения - новая версия 07.04.2015
   * @param string $name имя файла без расширения
   * @param string $tmp исходный временный файл
   * @param int $widthSet заданная ширина изображения
   * @param int $heightSet заданная высота изображения
   * @param string $resizeType тип сжатия: PROPORTIONAL|EXACT
   * @param string $dirUpload папка для загрузки изображения
   * @return void
   */
  public function _reSizeImage($name, $tmp, $widthSet, $heightSet, $resizeType="PROPORTIONAL", $dirUpload = 'uploads/thumbs/'){
    $fullName = explode('.', $name);
    $ext = array_pop($fullName);
    $name = implode('.', $fullName);
    
    list($width_orig, $height_orig) = getimagesize($tmp);
    $start_x = 0;
    $start_y = 0;
    $sWidth = $width_orig;
    $sHeight = $height_orig;
    
    if($width_orig <= $widthSet && $height_orig <= $heightSet){
      copy($tmp, $dirUpload.$name.'.'.$ext);
    }
    
    if($resizeType == "EXACT"){ //масштабируем в прямоугольник $widthSet*$heightSet c сохранением пропорций, обрезая лишнее
      $width = $widthSet;
      $height = $heightSet;
      
      $scale = ($width_orig / $height_orig > $widthSet / $heightSet) ? 
        $heightSet / $height_orig : $widthSet / $width_orig;
      
      $start_x = max(0, round($width_orig / 2 - ($widthSet / 2) / $scale));
      $start_y = max(0, round($height_orig / 2 - ($heightSet / 2) / $scale));
      
      $sWidth = round($widthSet / $scale, 0);
      $sHeight = round($heightSet / $scale, 0);
    }else{  //масштабируем с сохранением пропорций, размер ограничивается заданными параметрами $widthSet и $heightSet
      $widthCoef = $widthSet / $width_orig;
      $heightCoef = $heightSet / $height_orig;
      
      $resizeCoef = min($widthCoef, $heightCoef);
      $resizeCoef = ((0 < $resizeCoef) && ($resizeCoef < 1) ? $resizeCoef : 1);
      
      $width = max(1, intval($resizeCoef * $width_orig));
      $height = max(1, intval($resizeCoef * $height_orig));
    }
    
    $image_p = imagecreatetruecolor($width, $height);
    imageAlphaBlending($image_p, false);
    imageSaveAlpha($image_p, true);

    // вывод
    switch ($ext) {
      case 'png':
        $image = imagecreatefrompng($tmp);
        
        //делаем фон изображения белым, иначе в png при прозрачных рисунках фон черный
        $black = imagecolorallocate($image, 0, 0, 0);
        // Сделаем фон прозрачным
        imagecolortransparent($image, $black);

        imagealphablending($image_p, false);
        $col = imagecolorallocate($image_p, 0, 0, 0);
        imagefilledrectangle($image_p, 0, 0, $width, $height, $col);

        imagecopyresampled($image_p, $image, 0, 0, $start_x, $start_y, $width, $height, $sWidth, $sHeight);
        imagepng($image_p, $dirUpload.$name.'.'.$ext);
        break;

      case 'gif':
        $image = imagecreatefromgif($tmp);
        imagecopyresampled($image_p, $image, 0, 0, $start_x, $start_y, $width, $height, $sWidth, $sHeight);
        imagegif($image_p, $dirUpload.$name.'.'.$ext, 100);
        break;

      default:

        $image = imagecreatefromjpeg($tmp);
        imagecopyresampled($image_p, $image, 0, 0, $start_x, $start_y, $width, $height, $sWidth, $sHeight);
        imagejpeg($image_p, $dirUpload.$name.'.'.$ext, 100);
      // создаём новое изображение
    }
    imagedestroy($image_p);
    imagedestroy($image);
  }

  /**
   * Добавляет водяной знак к картинке
   * @param type $image - путь до картинки на сервере
   * @return boolean
   */
  public function addWatterMark($image) {
    $filename = $image;
    if (!file_exists('uploads/watermark/watermark.png')) {
      return false;
    }
    $size_format = getimagesize($image);
    $format = strtolower(substr($size_format['mime'], strpos($size_format['mime'], '/') + 1));

    // создаём водяной знак
    $watermark = imagecreatefrompng('uploads/watermark/watermark.png');
    imagealphablending($watermark, false);
    imageSaveAlpha($watermark, true);
    // получаем значения высоты и ширины водяного знака
    $watermark_width = imagesx($watermark);
    $watermark_height = imagesy($watermark);

    // создаём jpg из оригинального изображения
    $image_path = $image;



    switch ($format) {
      case 'png':
        $image = imagecreatefrompng($image_path);
        $w = imagesx($image);
        $h = imagesy($image);
        $imageTrans = imagecreatetruecolor($w, $h);
        imagealphablending($imageTrans, false);
        imageSaveAlpha($imageTrans, true);


        $col = imagecolorallocate($imageTrans, 0, 0, 0);
        imagefilledrectangle($imageTrans, 0, 0, $w, $h, $col);
        imagealphablending($imageTrans, true);


        break;
      case 'gif':
        $image = imagecreatefromgif($image_path);
        break;
      default:
        $image = imagecreatefromjpeg($image_path);
    }

    //если что-то пойдёт не так
    if ($image === false) {
      return false;
    }
    $size = getimagesize($image_path);
    // помещаем водяной знак на изображение
    $dest_x = (($size[0]) / 2) - (($watermark_width) / 2);
    $dest_y = (($size[1]) / 2) - (($watermark_height) / 2);

    imagealphablending($image, true);
    imagealphablending($watermark, true);

    imageSaveAlpha($image, true);
    // создаём новое изображение
    imagecopy($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);

    $imageformat = 'image'.$format;
    if ($format = 'png') {
      $imageformat($image, $filename);
    } else {
      $imageformat($image, $filename, 100);
    }

    // освобождаем память
    imagedestroy($image);
    imagedestroy($watermark);
    return true;
  }

  /**
   * Загружает CSV файл для импорта каталога
   * @return boolean
   */
  public function addImportCatalogCSV() {

    $path = 'uploads/';
    $validFormats = array('csv', 'zip');

    if (isset($_POST) && 'POST' == $_SERVER['REQUEST_METHOD']) {

      if (!empty($_FILES['upload'])) {
        $file_array = $_FILES['upload'];
      }

      $name = $file_array['name'];
      $size = $file_array['size'];

      if (strlen($name)) {
        $fullName = explode('.', $name);
        $ext = array_pop($fullName);
        $name = implode('.', $fullName);
        if (in_array(strtolower($ext), $validFormats)) {
          if ($size < (1024 * 10 * 1024) && !empty($file_array['tmp_name'])) { //$file_array['tmp_name'] будет пустым если размер загруженного файла превышает размер установленный параметром upload_max_filesize в php.ini
            if (strtolower($ext) == 'csv') {
              $name = 'importCatalog.csv';
            }
            if (strtolower($ext) == 'zip') {
              $name = 'importCatalog.zip';
            }

            $tmp = $file_array['tmp_name'];

            if (move_uploaded_file($tmp, $path.$name)) {

              if (strtolower($ext) == 'zip') {
                if (file_exists($path.$name)) {
                  $zip = new ZipArchive;
                  $res = $zip->open($path.$name, ZIPARCHIVE::CREATE);
                  
                  if ($res === TRUE) {
                    //$realDocumentRoot = str_replace(DIRECTORY_SEPARATOR.'mg-core'.DIRECTORY_SEPARATOR.'lib', '', dirname(__FILE__));
                    for($i = 0; $i < $zip->numFiles; $i++) {
                      $filename = $zip->getNameIndex($i);
                      $fullName = explode('.', $zip->getNameIndex($i));
                      $ext = array_pop($fullName);
                      if($ext=='csv'){
                        $zip->extractTo('uploads/', array($filename));
                        rename('uploads/'.$filename, "uploads/importCatalog.csv");
                      }                      
                    }                
                    $zip->close();
                    unlink($path.$name);
                  }
                }
              }
              return array('msg' => $this->lang['ACT_FILE_UPLOAD'], 'actualImageName' => 'importCatalog.csv', 'status' => 'success');
            } else {
              return array('msg' => $this->lang['ACT_FILE_NOT_UPLOAD'], 'status' => 'error');
            }
          } else {
            return array('msg' => $this->lang['ACT_FILE_NOT_UPLOAD1'], 'status' => 'error');
          }
        } else {
          return array('msg' => $this->lang['ACT_FILE_NOT_UPLOAD2'], 'status' => 'error');
        }
      } else {
        return array('msg' => $this->lang['ACT_FILE_NOT_UPLOAD3'], 'status' => 'error');
      }
    }
    return false;
  }
  
   /**
   * Удаляет сущесвующую картинку вместе с ее миниатюрами, если таковые имеются
   */
  public function deleteImageProduct($filename, $id = false) {
    $ds = DIRECTORY_SEPARATOR;
    $filename = basename($filename); 
    
    if($id){
      $addPath = 'product'.$ds.floor($id/100).'00'.$ds.$id;
    }else{
      $addPath = 'prodtmpimg';
    }
    
    $documentroot = str_replace($ds.'mg-core'.$ds.'lib','',dirname(__FILE__)).$ds; 
    
    if(is_file($documentroot."uploads".$ds.$addPath.$ds.$filename)){    
      unlink($documentroot."uploads".$ds.$addPath.$ds.$filename);
      
      if(is_file($documentroot."uploads".$ds.$addPath.$ds."thumbs".$ds."30_".$filename))
        unlink($documentroot."uploads".$ds.$addPath.$ds."thumbs".$ds."30_".$filename);
      
      if(is_file($documentroot."uploads".$ds.$addPath.$ds."thumbs".$ds."70_".$filename))
        unlink($documentroot."uploads".$ds.$addPath.$ds."thumbs".$ds."70_".$filename);
    }elseif(is_file($documentroot."uploads".$ds.$filename)){
      unlink($documentroot."uploads".$ds.$filename);
      
      if(is_file($documentroot."uploads".$ds."thumbs".$ds."30_".$filename))
        unlink($documentroot."uploads".$ds."thumbs".$ds."30_".$filename);
      
      if(is_file($documentroot."uploads".$ds."thumbs".$ds."70_".$filename))
        unlink($documentroot."uploads".$ds."thumbs".$ds."70_".$filename);
    }
    
    return true;
  }
    /**
   * Загружает картинку от пользователей с публичной части сайта на сервер 
   * @param string $subDir - имя каталога куда будет загружено изображение 
   * @return boolean
   */
  
  public function uploadImage($subDir = '') {
    $validFormats = array('jpeg', 'jpg', 'png', 'gif');
  
    if (isset($_POST) && 'POST' == $_SERVER['REQUEST_METHOD']) {

      if (!empty($_FILES['upload'])) {
        $imageinfo = getimagesize($_FILES['upload']['tmp_name']);
        $file_array = $_FILES['upload'];
      } elseif (!empty($_FILES['logo'])) {
        $imageinfo = getimagesize($_FILES['logo']['tmp_name']);
        $file_array = $_FILES['logo'];
      }
      $name = $file_array['name'];
      $size = $file_array['size'];
      $type = $file_array['type'];
      
      if (strlen($name)) {
        $fullName = explode('.', $name);
        $ext = array_pop($fullName);
        $name = implode('.', $fullName);
        // проверка соответствмя расширения с разрешенными,
        if (in_array(strtolower($ext), $validFormats)) {  
          // проверка типа файла и  на количество типов 
          if(strpos($type,'image') !== false) {
            if($imageinfo['mime'] == 'image/gif' || $imageinfo['mime'] == 'image/jpeg' || 
              $imageinfo['mime'] == 'image/jpg' || $imageinfo['mime'] == 'image/png') {
              if(substr_count($type, '/') <= 1){
                // проверка на установелнный размер файла и переименование латинским написанием
                if ($size < (1024 * 2 * 1024) && !empty($file_array['tmp_name'])) { //$file_array['tmp_name'] будет пустым если размер загруженного файла превышает размер установленный параметром upload_max_filesize в php.ini
                  $name = str_replace(" ", "-", $name);
                  $name = MG::translitIt($name);
                  $actualImageName =  $name.".".$ext;
                   if (file_exists('uploads/'.$subDir.$name.".".$ext)) {
                    $actualImageName = $name.time().".".$ext;
                   }
                  
                  $tmp = $file_array['tmp_name'];
                  // пересохранение с помощью GD
                   if ($this -> resavingImageFromPublic($actualImageName, $tmp, $dirUpload = 'uploads/'.$subDir)) {
                    return SITE.'/'.$dirUpload.$actualImageName;   
                   }         
                } 
              } 
            }
          }
        }
      }
    }
    return false;
  }
   /**
   * Функция для пересохранения картинки, загруженной из публичной части
   * @param string $name имя файла 
   * @param string $tmp исходный временный файл
   * @param string $dirUpload имя каталога 
   * @return bool
   */
  public function resavingImageFromPublic($name, $tmp, $dirUpload = 'uploads/') {
    $result = false;
    $fullName = explode('.', $name);
    $ext = array_pop($fullName);
    $name = implode('.', $fullName);
    // сохранение изображения
    switch ($ext) {
      case 'png':
        $image = imagecreatefrompng($tmp);
        imagealphablending($image, true);
        imageSaveAlpha($image, true);
        imagepng($image, $dirUpload.$name.'.'.$ext);
        if (imagepng($image, $dirUpload.$name.'.'.$ext)) {
          $result = true;
        }
        break;
      case 'gif':
        $image = imagecreatefromgif($tmp);
        if (imagegif($image, $dirUpload.$name.'.'.$ext)) {
           $result = true;          
        }
        break;
      default:
        $image = imagecreatefromjpeg($tmp);
        if (imagejpeg($image, $dirUpload.$name.'.'.$ext)) {
           $result = true;          
        }
    }
    imagedestroy($image);
    return $result;
  }

}