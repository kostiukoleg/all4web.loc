<?php

/**
 * Контроллер: Ajaxrequest
 *
 * Класс Controllers_Ajaxrequest обрабатывает все AJAX запросы как из админки так и с публичной части сайта.
 * - Отключает вывод шаблона;
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Ajaxrequest extends BaseController{

  function __construct(){

    // Не существует обработки для прямого обращения.
    if(empty($_REQUEST)){
      header('HTTP/1.0 404 Not Found');
      exit;
    }

    // Отключаем вывод темы.
    MG::disableTemplate();

    $actioner = URL::getQueryParametr('actionerClass');
    if('Ajaxuser'==$actioner){
      $this->routeUserAction(URL::getQueryParametr('action'));
      // Если в пользовательском классе не найдено обрабатывающего метода,
      // то продолжаем поиск в админском классе, но туда имеют доступ только админы.
    }

    if(URL::getQueryParametr('delInstal')){
      $this->delInstal();
    }

    // Если передана переменная $pluginFolder, то вся обработка
    // перекладывается на плечи стороннего плагина из этой папки.
    $pluginHandler = URL::getQueryParametr('pluginHandler');
    $url = URL::getQueryParametr('mguniqueurl');
    $type = URL::getQueryParametr('mguniquetype');


    if(!empty($pluginHandler)){
      // Обработкой действия займется плагин, папка которого передана в $pluginHandler.
      $actioner = URL::getQueryParametr('actionerClass');
      if(empty($actioner)){
        // Если обработчик задан в параметре mguniqueurl , 
        // то назначаем стандартный  класс обработки,
        // который должен быть в каждом плагине.
        $actioner = 'Pactioner';
        $this->routeAction($url, $pluginHandler, $actioner);
      }
      else{
        //если задан уникальный обработчик.
        // запускаем маршрутизатор действий.
        $this->routeAction($url, $pluginHandler, $actioner);
      }
    }
  }

  /**
   * Если действие запрошенно стандартными файлами движка, то
   * маршрутизирует действие в класс Actioner для дальнейшего выполнения.
   *
   * Если действие запрошено из страницы плагина, то передает действие в
   * пользовательский класс плагина. Класс плагина передается
   * в переменной  URL::getQueryParametr('action')
   *
   * @param string $url ссылка на действие
   * @param string $plugin папка с плагином
   * @param string $actioner - обработчик аякс запросов
   * 
   */
  public function routeAction($url, $plugin = null, $actioner = false){
    // Если не плагин.
    if(!$plugin){
      //Защита контролера от несанкционированного доступа вне админки.
      if(!$this->checkAccess(User::getThis()->role)){
        echo "Для доступа к методу необходимо иметь права администратора!";
        exit;
        return false;
      };

      $parts = explode('/', $url);
      if($parts[0]=='action'){
        $act = new Actioner();
        $act->runAction($parts[1]);
        return true;
      }
    }
    else{

      // Подключам пользовательский класс для обаботки.
      $action = URL::getQueryParametr('action');

      if(empty($action)){
        $parts = explode('/', $url);
        if($parts[0]=='action'){
          $action = $parts[1];
        }
      }

      // Формируем путь до класса плагина, который обработает действие.
      $pluginClassPath = PLUGIN_DIR.$plugin."/".strtolower($actioner).'.php';
      if(file_exists($pluginClassPath)){
        $pathPluginActioner = $pluginClassPath;
      }
      else{
        $pathPluginActioner = PLUGIN_DIR.$plugin."/".$actioner.'.php';
      }

      // Подключаем класс плагина.
      include $pathPluginActioner;

      // Создаем экземпляр класа обработчика.
      // (он обязательно должен наследоваться от стандартноко класса Actioner)    

      $lang = PLUGIN_DIR.$plugin."/locales/".MG::getSetting('languageLocale').'.php';
      if(file_exists($lang)){
        include $lang;
        $act = new $actioner($lang);
      }
      else{
        $act = new $actioner();
      }

      // выполняем стандартный метод класса Actioner
      $act->runAction($action);
      return true;
    }

    return false;
  }

  /**
   * Маршрутизатор для AJAX запроса. Передает запрос на обработку в специальный класс.
   * @param type $action
   * @return boolean
   */
  public function routeUserAction($action){
    include PATH_TEMPLATE.'/ajaxuser.php';
    // создаем экземпля класа обработчика
    // (он обязательно должен наследоваться от стандартноко класса Actioner)
    $act = new Ajaxuser();
    if(method_exists($act, $action)){
      // выполняем стандартный метод класса Actioner
      $act->runAction($action);
      return true;
    }
    return false;
  }

  /**
   * Проверяет наличие прав администратора, на доступ к этому контролеру.
   * Защищает его от прямых ссылок таких как ajax?url=action/editProduct
   *
   * @param boolean $role флаг прав администратора
   * @return boolean
   */
  public function checkAccess($role){
    if($role=='2'){
      header('HTTP/1.0 404 Not Found');
      URL::setQueryParametr('view', PATH_TEMPLATE.'/404.php');
      return false;
    }
    return true;
  }

  /**
   * Удаляет инсталятор.
   * @return void
   */
  public function delInstal(){
    $installDir = SITE_DIR.URL::getCutPath().'/install/';
    $this->removeDir($installDir);
    MG::redirect('');
  }

  /**
   * Удаляет папку со всем ее содержимым.
   * @param string $path путь к удаляемой папке.
   * @return void
   */
  public function removeDir($path){
    if(file_exists($path)&&is_dir($path)){
      $dirHandle = opendir($path);

      while(false!==($file = readdir($dirHandle))){

        if($file!='.'&&$file!='..'){
          // Исключаем папки с назварием '.' и '..'
          $tmpPath = $path.'/'.$file;
          chmod($tmpPath, 0777);

          if(is_dir($tmpPath)){  // Если папка.
            $this->removeDir($tmpPath);
          }
          else{
            if(file_exists($tmpPath)){
              // Удаляем файл.
              unlink($tmpPath);
            }
          }
        }
      }
      closedir($dirHandle);

      // Удаляем текущую папку.
      if(file_exists($path)){
        rmdir($path);
        return true;
      }
    }
  }

}