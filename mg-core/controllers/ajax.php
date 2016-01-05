<?php

/**
 * Контроллер: Ajax
 *
 * Класс Controllers_Ajax обрабатывает все AJAX запросы присылаемые из админки.
 * - Отключает вывод шаблона;
 * - Передает запрос в библиотеку Actioner.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Ajax extends BaseController {

  function __construct() {

    // Не существует обработки для прямого обращения.
    if (empty($_REQUEST)) {
      header('HTTP/1.0 404 Not Found');
      exit;
    }

    // Отключаем вывод темы.
    MG::disableTemplate();

    $actioner = URL::getQueryParametr('actionerClass');
    if ('Ajaxuser' == $actioner) {
      $this->routeUserAction(URL::getQueryParametr('action'));
    }

    // Если этот аякс запрос направлен на выполнение
    // действия с БД, то пытаемся их выполнить.
    // Иначе подключается контролер из админки.

    $url = URL::getQueryParametr('mguniqueurl');
    $type = URL::getQueryParametr('mguniquetype');

    // Менеджерам запрещено работать с разделами.
    if (USER::getThis()->role == 3) {
      $accessDenied = array(
        'category.php',
        'page.php',
        'catalog.php',
        'users.php',
        'settings.php',
        'statistics.php');
      if (in_array($url, $accessDenied)) {
        exit();
      }
    }

    // Модераторам запрещено работать с разделами.
    if (USER::getThis()->role == 4) {
      $accessDenied = array('settings.php', 'statistics.php');
      if (in_array($url, $accessDenied)) {
        exit();
      }
    }

    // Незарегистрированным пользователям и клиентам запрещено работать с разделами.
    if (USER::getThis()->role == 2 || empty(USER::getThis()->role)) {
      exit();
    }

    // Если передана переменная $pluginFolder, то вся обработка
    // происходит в плагине из этой папки.
    $pluginHandler = URL::getQueryParametr('pluginHandler');

    if (empty($pluginHandler)) {
      if (!$this->routeAction($url)) {
        if ('plugin' == $type) {
          if (!empty($_POST['request'])) {
            $_POST = $_POST['request'];
          }
          URL::setQueryParametr('view', ADMIN_DIR . 'section/views/plugintemplate.php');
        } else {
          require_once ADMIN_DIR . 'section/controlers/' . $url;
          $this->lang = MG::get('lang');
          URL::setQueryParametr('view', ADMIN_DIR . 'section/views/' . $url);
        }
      }
    } else {
      // Обработкой действия займется плагин, папка которого передана в $pluginHandler.
      $actioner = URL::getQueryParametr('actionerClass');
      if (empty($actioner)) {
        // Если обработчик задан в параметре mguniqueurl , 
        // то назначаем стандартный  класс обработки,
        // который должен быть в каждом плагине.
        $actioner = 'Pactioner';
        $this->routeAction($url, $pluginHandler, $actioner);
      } else {
        // Если задан уникальный обработчик, то
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
   * @param string $url - ссылка на действие.
   * @param string $plugin - папка с плагином.
   * @param string $actioner - обработчик аякс запросов.
   * 
   */
  public function routeAction($url, $plugin = null, $actioner = false) {
    // Если не плагин.
    if (!$plugin) {
      //Защита контролера от несанкционированного доступа вне админки.
      if (!$this->checkAccess(User::getThis()->role)) {
        echo "Для доступа к методу необходимо иметь права администратора!";
        exit;
        return false;
      };

      $parts = explode('/', $url);
      if ($parts[0] == 'action') {
        $act = new Actioner();
        $act->runAction($parts[1]);
        return true;
      }
    } else {

      // Подключам пользовательский класс для обаботки.
      $action = URL::getQueryParametr('action');

      if (empty($action)) {
        $parts = explode('/', $url);
        if ($parts[0] == 'action') {
          $action = $parts[1];
        }
      }

      // Формируем путь до класса плагина, который обработает действие.
      $pluginClassPath = PLUGIN_DIR . $plugin . "/" . strtolower($actioner) . '.php';
      if (file_exists($pluginClassPath)) {
        $pathPluginActioner = $pluginClassPath;
      } else {
        $pathPluginActioner = PLUGIN_DIR . $plugin . "/" . $actioner . '.php';
      }

      // Подключаем класс плагина.
      include $pathPluginActioner;

      // Создаем экземпляр класа обработчика.
      // (он обязательно должен наследоваться от стандартноко класса Actioner)
      $lang = PLUGIN_DIR . $plugin . "/locales/" . MG::getSetting('languageLocale') . '.php';
      if (file_exists($lang)) {
        include $lang;
        $act = new $actioner($lang);
      } else {
        $act = new $actioner();
      }

      // Выполняем стандартный метод класса Actioner.
      $act->runAction($action);
      return true;
    }

    return false;
  }

  /**
   * Маршрутизатор для AJAX запроса. Передает запрос на 
   * обработку в  файл шаблона ajaxuser.php.
   * @param string $action - запрошенное действие.
   * @return boolean
   */
  public function routeUserAction($action) {
    include PATH_TEMPLATE . '/ajaxuser.php';
    // Создаем экземпля класа обработчика.
    // (он обязательно должен наследоваться от стандартноко класса Actioner)
    $act = new Ajaxuser();
    if (method_exists($act, $action)) {
      // Выполняем стандартный метод класса Actioner.
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
  public function checkAccess($role) {
    if ($role == '2') {
      header('HTTP/1.0 404 Not Found');
      URL::setQueryParametr('view', PATH_TEMPLATE . '/404.php');
      return false;
    }
    return true;
  }

}