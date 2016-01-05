<?php
/**
 * Класс Navigator - генерирует пейджер для постраничной навигации.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Navigator{

  //Блок переменных достуных только внутри класса .

  private $countRecord; //Количество выводимых записей на странице.
  private $sql; // Исходный sql запрос.
  private $maxAcceptedCount; //Количество записей вернувшихся по запросу
  private $numberPage; //Номер текущей страницы.
  private $viewAll; //Флаг - показать все страницы.
  private $paramName; //Имя параметра в GET запросе например "page".
  private $linkCount; //количество выводимых ссылок на страницы в пайджере.
  private $returnData = array();
  public  $allPages = true;
  public $poinBetwenIntervar = false;

  /**
   * Производит  sql запрос и устанавливает параметры.
   * @param type $sql - запрос к базе
   * @param type $numberPage - номер запрашиваемой страницы
   * @param type $countRecord - количество выводимых записаей на одной странице
   * @param type $linkCount - количесво выводимых ссылок в педжере
   * @param type $viewAll - вывести все страницы
   * @param type $paramName - наименование гет переменной указывающей текущую страницу
   */
  public function __construct($sql, $numberPage, $countRecord = 20, $linkCount = 6, $viewAll = false, $paramName = "page"){
   
    // Инициализируем переменные класса
    $this->sql = $sql;
    $this->countRecord = $countRecord;
    $this->numberPage = $numberPage;
    $this->viewAll = $viewAll;
    $this->paramName = $paramName;

    //количество ссылок не может быть меньше двух
    $this->linkCount = $linkCount==1?2:$linkCount;

   //$this->returnData = Storage::get(md5($sql.$numberPage));    
   //if($this->returnData == null){
      // если не запроcе вывод всего списка записей
      if(!$this->viewAll){
        //вычисляем данные для педжера
        $this->calcDataPage();
      }

      // выполняем запрос
      $res = DB::query($this->sql);
     
      // сохраняем все полученные записи
      while($row = DB::fetchAssoc($res)){    
        $this->returnData[] = $row;
      }

   //  Storage::save(md5($sql.$numberPage),$this->returnData); 
   //}
  }

  /**
   * Возвращает результат выполнения  SQL запроса
   * return int
   */
  public function getNumRowsSql(){
    return $this->maxAcceptedCount;
  }

  /**
   * Возвращает количество записей
   * @return array - массив полученных записей
   */
  public function getRowsSql(){ 
    if(empty($this->returnData)){
      return array();    
    }
    return $this->returnData;  
  }

  /**
   * Возвращает результат выполнения  SQL запроса
   * @return string - пейджер в HTML виде
   */
  public function getPager($type = "getQuery"){
    return $this->createNavigator($type);
  }

  /**
   * Вычисляет все параметры для составления педжера:
   *  - общее количесво записей.
   *  - часть запроса указывающую на нужную страницу.
   *  - максимально доступное количество страниц
   */
  private function calcDataPage(){   
    $result = DB::query($this->sql);

    //узнаем общее количество возвращенных записей
    $count = DB::numrows($result);
   
    $this->maxAcceptedCount = $count;
    //Вычисляем максимально доступное количество страниц


    // общее количество страниц
	if(empty($this->countRecord)||!is_numeric($this->countRecord)){$this->countRecord=1;}
    $maxCountRecOnPage = ceil($count / $this->countRecord);

    $this->maxCountRecOnPage = $maxCountRecOnPage;
    // если общее количество страниц меньше чем должно выводиться впейджере
    if($maxCountRecOnPage <= $this->linkCount){
      $this->linkCount=$maxCountRecOnPage-1;
    }

    // если максимальное количество страниц меньше чем номер запрашиваемой
    if($maxCountRecOnPage <= ($this->numberPage - 1)){
      
      // если запрашивается  страница пагинации, которая не существует,
      // то в публичной части произойдет редирект на страницу с ошибкой.
      // в админке будет выведена последняя из доступных.
      /*  if(MG::get('controller')!='controllers_ajax'){
        header( "HTTP/1.1 404 Not Found" );
        MG::redirect('/404');
        exit();      
      }*/
      // то запросим у MySql последнюю доступную страницу
      $pos = $maxCountRecOnPage - 1;
      $pos = $pos>0?$pos:0;
      //если номер запрашиваемой страницы, меньше либо равен 0
    }elseif(0 >= ($this->numberPage - 1)){

      //то запросим у MySql первую доступную страницу
      $pos = 0;

      // если запрашиваемая страница попадает в диапазон существующих
    }else{
      //то запросим у MySql нужную страницу
      $pos = $this->numberPage - 1;
    }

    
    // к запросу дописываем параметр вывода записей с нужной позиции, и их количесвто
    $this->sql = $this->sql." LIMIT ".$pos * $this->countRecord.", ".$this->countRecord;
  }

  private function createNavigator($type){

    // если все записи помещаются на одной странице не формируем навигатор
    if($this->maxAcceptedCount < $this->countRecord){
      return false;
    }
    //формирование  навигатора
    if(URL::isSection("mg-admin")){ //В административной части
      //если текущая страница, выходит за рамки допустимых, то показываем первую доступную с нужной стороны.
      if($this->numberPage <= 0){
        $this->numberPage = 1; // если текущая страница меньше первой то показываем всегда перву.
      }
      if($this->numberPage > $this->maxCountRecOnPage){
        $this->numberPage = $this->maxCountRecOnPage; // если текущая страница больше последней то показываем всегда последнюю.
      }
    }
    else{                                                                       //В публичной части
      if(MG::get('controller')=="controllers_catalog" && (intval($this->numberPage) < 0 || intval($this->numberPage) > $this->maxCountRecOnPage)){ //если текущая страница, выходит за рамки допустимых, то показываем страницу 404.
        header( "HTTP/1.1 404 Not Found" );
        MG::redirect('/404');
        exit();
      }else{
        if($this->numberPage == 0){
          $this->numberPage = 1; // если текущая страница меньше первой то показываем всегда перву.
        }
      }
    }


    $first = '';
    $prev = '';
    $next = '';
    $last = '';
    //создаем кнопки для навигатора
    if($this->numberPage > 1){ // если не первая страница
      $prev = $this->gelLink('linkPage navButton', ($this->numberPage - 1), $type, '&laquo;');
      $first = $this->gelLink('linkPage navButton', (1), $type, '&laquo;&laquo;');
    }
    if($this->numberPage < $this->maxCountRecOnPage){ // если не последняя страница
      $next = $this->gelLink('linkPage navButton', ($this->numberPage + 1), $type, '&raquo;');
      $last = $this->gelLink('linkPage navButton', ($this->maxCountRecOnPage), $type, '&raquo;&raquo;');
    }

    // если количесво доступных страниц меньше требуемого, то вывести лишь возможные
    if($this->linkCount > $this->maxCountRecOnPage){
      $this->linkCount = $this->maxCountRecOnPage;
    }



    $half = floor($this->linkCount / 2);

    $pager = '';
    if($this->allPages){
      $allPages='<div class="allPages">Всего '.$this->maxCountRecOnPage.' страниц</div>';
    };
    // если все записи помещаются на двух страницах выводим только две ссылки
    if($this->linkCount==1){
       for($i = 1; $i <= 2; $i++){
      $class = "linkPage";
      if($i == $this->numberPage){
        $class = "active";
      }
      $pager.=$this->gelLink($class, $i, $type);

      // возвращаем полученный список страниц
      }
      $navigator = '<div class="mg-pager">'.$allPages.'<ul>'.$pager."</ul></div>";

      return $navigator;
    }


    //средняя часть навигатора , вывод ссылок по половине от общего числа выводимых
    //по обе стороны текущей страницы
    for($i = ($this->numberPage - $half);
        $i <= ($this->numberPage + $half);
        $i++){

      //всем ссылкам назначается класс 'linkPage'
      $class = "linkPage";

      //если ссылка идет на текущую страницу то ей присваивается особый класс active
      if($i == $this->numberPage){
        $class = "active";
      }

      $noneftpoint = false;
      $norightpoint = false;
      $leftpoint = '';
      $lastpages = ''; 
     
      // формирование ссылок на добавочные с конца страницы
      if($i <= 0){

        $numberPage = (abs($i) + $this->numberPage + $half + 1);
        $lastpages = $this->gelLink($class, $numberPage, $type).$lastpages;
        // если начали добавлять страницы, убираем точки с другого конца
        $leftpoint = "";
        // флаг о том что точки слева убраны
        $noneftpoint = true;
      }

      $firstpages ='';
      $rightpoint ='';
      // формирование ссылок на добавочные с начала страницы
      if($i > $this->maxCountRecOnPage){
        $numberPage = (abs($i - $this->maxCountRecOnPage - $this->numberPage + $half));
        $firstpages = $this->gelLink($class, $numberPage, $type).$firstpages;
        $norightpoint = true; //если начали добавлять  страницы, убираем точки с другого конца
        $rightpoint = ""; // флаг о том что точки справа убраны
      }


        if($i > 0 && $i <= $this->maxCountRecOnPage){ //если формируемая ссылка попадает в интервал допустимых страниц
          if(!$noneftpoint && $this->poinBetwenIntervar)
            $leftpoint = "<span class='point'>...</span>"; //добавляем точки слева от списка
          $pager.=$this->gelLink($class, $i, $type); //создаем ссылку
          if(!$norightpoint && $this->poinBetwenIntervar)
            $rightpoint = "<span class='point'>...</span>"; //добавляем точки справа от списка
        }

    }

    //склеиваем все сгенерированные части навигатора
    $navigator = '<div class="mg-pager">'.$allPages.'<ul>'.$first.$prev.$leftpoint.$firstpages.$pager.$lastpages.$rightpoint.$next.$last."</ul></div>";
    // возвращаем полученный список страниц
    return $navigator;
  }

  private function gelLink($class, $numberPage, $type = "getQuery", $ancor = null){
    $href = "href='javascript:void(0);'";
    if($type == "forAjax"){
      $href = "href='javascript:void(0);'";
    }
    if($type == "getQuery"){
      $uri = $_SERVER['REQUEST_URI'];
      if(MG::get('controller')=="controllers_catalog" && (MG::getSetting('catalogIndex')=='true') &&(URL::isSection(null)||(URL::isSection('index')))) {
        $uri = substr_count($uri, 'index') ? str_replace('/index', '/catalog', $uri) : str_replace('/', '/catalog', $uri);
      }

      $url = str_replace(array('[', ']'), array('&#91;', '&#93;'), URL::add_get($uri, $this->paramName, $numberPage));
      $href =  "href='".$url."'";
    }
    $ancor = $ancor ? $ancor : $numberPage;
    return "<li><a class='".$class." page_".$numberPage."' ".$href." >".$ancor."</a></li>";
  }

}