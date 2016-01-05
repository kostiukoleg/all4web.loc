<?php

/**
 * Класс Page - совершает все возможные операции со страницами сайта.
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Page {

  // Массив страниц.
  private $page;
  private $listCategoryId;

  public function __construct() {
    // получаем список страниц   
    $result = DB::query('SELECT id, title, url, parent, parent_url, sort, meta_title, meta_keywords, meta_desc,invisible FROM `'.PREFIX.'page` ORDER BY sort');
    $listId = "";
    while ($page = DB::fetchAssoc($result)) {   
      if(strpos($page['url'],'http') === 0){     
        $link = $page['url'];
      }else{
        $link = SITE.'/'.$page['parent_url'].$page['url'];        
      }
      if($link == SITE.'/index'){
        $link = SITE;
      }
      $page['link'] = $link;
      $this->page[$page['id']] = $page;
    }

   /* if (!empty($this->page)) {
      usort($this->page, array(__CLASS__, "sort"));
    }*/
  }

  /**
   * Возвращает url родительской страницы по ее id.
   * @param $parentId - id страницы для которой нужно найти UR родителя.
   * @return string
   */
  public function getParentUrl($parentId) {
    $cat = $this->getPageById($parentId, true);
    $res = !empty($cat) ? $cat['parent_url'].$cat['url'] : '';
    return $res ? $res.'/' : '';
  }

  /**
   *  Создает новую страницу.
   *
   * @param array $array массив с данными о страницах.
   * @return bool|int в случае успеха возвращает id добавленной страницы.
   */
  public function addPage($array) {

    unset($array['id']);
    $result = array();

    if (!empty($array['url'])) {
      $array['url'] = URL::prepareUrl($array['url']);
    }
      
    $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');

    foreach ($array as $k => $v) {
       if(in_array($k, $maskField)){
        $v = htmlspecialchars_decode($v);
        $array[$k] = htmlspecialchars($v);       
       }
    }
    // Исключает дублирование.
    $dublicatUrl = false;

    $tempArray = $this->getPageByUrl($array['url'], $array['parent_url']);
    if (!empty($tempArray)) {
      $dublicatUrl = true;
    }
    $array['sort'] = $array['id'];
    if (DB::buildQuery('INSERT INTO `'.PREFIX.'page` SET ', $array)) {
      $id = DB::insertId();
      // Если url дублируется, то дописываем к нему id продукта.
      if ($dublicatUrl) {
        $arr = array('id' => $id, 'sort' => $id, 'url' => $array['url'].'_'.$id);
      } else {
        $arr = array('id' => $id, 'sort' => $id, 'url' => $array['url']);
      }
      $this->updatePage($arr);
      $array['id'] = $id;
      $result = $array;
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Изменяет данные о странице
   *
   * @param array $array массив с данными о категории.
   * @param int $id  id изменяемой категории.
   * @return bool
   */
  public function updatePage($array) {
    $id = $array['id'];
    $result = false;

    if (!empty($array['url']) && strpos($array['url'], 'http:') !== 0) {
      $array['url'] = URL::prepareUrl($array['url']);
    }

    $maskField = array('title','meta_title','meta_keywords','meta_desc','image_title','image_alt');

    foreach ($array as $k => $v) {
       if(in_array($k, $maskField)){
        $array[$k] = htmlspecialchars($v);       
       }
    }
    
    // Если назначаемая категория, является тойже.
    if ($array['parent'] == $id) {
      $this->messageError = 'Нельзя назначить выбраную страницу родительской!';
      return false;
    }

    $childsPage = $this->getPagesInside($id);
    // Если есть вложенные, и одна из них назначена родительской.
    if (!empty($childsPage)) {
      foreach ($childsPage as $cateroryId) {
        if ($array['parent'] == $cateroryId) {
          $this->messageError = 'Нельзя назначить выбраную страницу родительской!';
          return false;
        }
      }
    }

    if ($_POST['parent'] == $id) {
      $this->messageError = 'Нельзя назначить выбраную страницу родительской!';
      return false;
    }

    if (!empty($id)) {
      
      // обновляем выбраную страницу
      if (DB::query('
        UPDATE `'.PREFIX.'page`
        SET '.DB::buildPartQuery($array).'
        WHERE id =  '.DB::quote($id, true)
        )) {
        $result = true;
      }

      // находим список всех вложенных в нее страниц
      $arrayChildCat = $this->getPagesInside($array['parent']);
      
      if (!empty($arrayChildCat)) {
        
        // обновляем parent_url у всех вложенных категорий, т.к. корень поменялся
        foreach ($arrayChildCat as $childCat) {
          $childCat = $this->getPageById($childCat, true);
          
          $upParentUrl = $this->getParentUrl($childCat['parent']);
          
          if (DB::query('
            UPDATE `'.PREFIX.'page`
            SET parent_url='.DB::quote($upParentUrl).'
            WHERE id = '.DB::quote($childCat['id'], true)
            ))
            ;
        }
      }
    } else {
      $result = $this->addPage($array);
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Удаляет Страницу.
   *
   * @param int $id id удаляемой категории.
   * @return bool
   */
  public function delPage($id) {
    $categories = $this->getPagesInside($id);
    $categories[] = $id;

    foreach ($categories as $pageID) {
      DB::query('
        DELETE FROM `'.PREFIX.'page`
        WHERE id = %d
      ', $pageID);
    }

    $args = func_get_args();
    $result = true;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает древовидный список страниц, пригодный для использования в меню.
   *
   * @param int $parent id категории, для которой надо вернуть список.
   * @param int $type тип списка (для публичной части, либо для админки).
   * @return string
   */
  public function getPagesUl($parent = 0, $type = 'public') {
    // получаем данные об открытых страницах из куков  
    if (empty($this->openedPage)) {
      if ('admin' == $type) {
        $this->openedPage = json_decode($_COOKIE['openedPageAdmin']);
      } else {
        $this->openedPage = json_decode($_COOKIE['openedPage']);
      }
      if (empty($this->openedPage)) {
        $this->openedPage = array();
      }
    }

    $print = '';
    if (empty($this->page)) {
      $print = '';
    } else {
      $lang = MG::get('lang');
      $gategoryArr = $this->page;
      //для публичной части убираем из меню закрытые страницы
      if ('public' == $type) {
        foreach ($gategoryArr as $key => $val) {
          if ($val['invisible'] == 1) {
            unset($gategoryArr[$key]);
          }
        }
      }

      foreach ($gategoryArr as $page) {

        if ($parent == $page['parent']) {

          $flag = false;

          $mover = '';

          if ('admin' == $type) {
            $class = 'active';
            $title = $lang['ACT_V_CAT'];
            if ($page['invisible'] == 1) {
              $class = '';
              $title = $lang['ACT_UNV_CAT'];
            }

            if (strpos($page['url'], 'http:') !== 0) {
              $url = SITE.'/'.$page['parent_url'].$page['url'];
            } else {
              $url = $page['url'];
            }
            $checkbox = '<input type="checkbox" name="page-check"> ';
            $mover .= $checkbox.'<div class="visible tool-tip-bottom '.$class.'" title="'.$title.'" data-category-id="'.$page['id'].'"></div><div class="mover"></div><div class="link-to-site tool-tip-bottom" title="'.$lang['MOVED_TO_PAGE'].'" data-href="'.$url.'"></div>';
          }

          $slider = '>'.$mover;

          foreach ($this->page as $sub_category) {
            if ($page['id'] == $sub_category['parent']) {
              $slider = ' class="slider">'.$mover.'<div class="slider_btn"></div>';
              $style = "";
			  $opened = " opened ";
              if (in_array($page['id'], $this->openedPage)) {
                $opened = " opened ";
                $style = ' style="background-position: 0 0"';
              }

              $slider = ' class="slider">'.$mover.'<div class="slider_btn '.$opened.'" '.$style.'></div>';
              $flag = true;
              break;
            }
          }

          if ('admin' == $type) {
            $print.= '<li'.$slider.'<a href="javascript:void(0);" onclick="return false;" rel="pageTree" class="pageTree" id="'.$page['id'].'" parent_id="'.$page["parent"].'">'.$page['title'].'</a>
              <span style="display:none"> [id='.$page['id'].'] </span>';
          } else {
            $hotFix1 = false;
            if ($page['parent_url'] == "" && ($page['url'] == 'index' || $page['url'] == 'index.html')) {
              $hotFix1 = true;
            }
            if ($page['invisible'] != 1) {
              $active = '';
              if (URL::isSection($page['parent_url'].$page['url'])) {
                $active = 'class="active"';
              }
              $page['title'] = MG::contextEditor('page', $page['title'], $page["id"], "page");

              if (strpos($page['url'], 'http://') === false) {
                $url = SITE.'/'.$page['parent_url'].$page['url'];
              } else {
                $url = $page['url'];
              }

              if ($hotFix1) {
                $print.= '<li'.$slider.'<a href="'.SITE.'"><span '.$active.'>'.$page['title'].'</span></a>';
              } else {
                $print.= '<li'.$slider.'<a href="'.$url.'"><span '.$active.'>'.$page['title'].'</span></a>';
              }
            }
          }

          if ($flag) {
            $display = "display:none";
            if (in_array($page['id'], $this->openedPage)) {
              $display = "display:block";
            }

            $sub_menu = '
              <ul class="sub_menu" style="'.$display.'">
                [li]
              </ul>';

            //Если страница  скрыта, то не идем вглубь.                
            $li = $this->getPagesUl($page['id'], $type);
            $print .= strlen($li) > 0 ? str_replace('[li]', $li, $sub_menu) : "";

            $print .= '</li>';
          } else {
            $print .= '</li>';
          }
        }
      }
    }

    $args = func_get_args();
    $result = $print;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает древовидный список страниц, пригодный для использования в футере.
   * Вернет заданное количество списков.
   * @param int $parent id категории, для которой надо вернуть список.
   * @param int $type тип списка (для публичной части, либо для админки).
   * @return string
   */
  public function getFooterPagesUl($type = 'public', $column = 3) {

    $print = '';
    if (empty($this->page)) {
      $print = '';
    } else {
      $lang = MG::get('lang');
      $gategoryArr = $this->page;
      //для публичной части убираем из меню закрытые страницы

      foreach ($gategoryArr as $key => $val) {
        if ($val['invisible'] == 1) {
          unset($gategoryArr[$key]);
        }
      }

      $countPage = 0;
      foreach ($gategoryArr as $page) {
        if ($page['parent'] == 0) {
          $countPage++;
        }
      }

      if ($countPage > 1) {
        $inColumn = floor($countPage / $column);
      }

      $newColumn = true;
      $i = 0;
      foreach ($gategoryArr as $page) {

        if ($page['parent'] == 0) {

          if ($newColumn == true) {

            if ($i > 0) {
              $i = 0;
              $print.= "</ul><ul class='footer-column'>";
            } else {
              $i = 0;
              $print.= "<ul class='footer-column'>";
            }
          }

          if ($i < $inColumn) {
            $newColumn = false;
            $i++;
          } else {
            $newColumn = true;
          }

     


          $hotFix1 = false;
          if ($page['parent_url'] == "" && ($page['url'] == 'index' || $page['url'] == 'index.html')) {
            $hotFix1 = true;
          }

          if ($page['invisible'] != 1) {
            $active = '';
            if (URL::isSection($page['parent_url'].$page['url'])) {
              $active = 'class="active"';
            }
            $page['title'] = MG::contextEditor('page', $page['title'], $page["id"], "page");

            if (strpos($page['url'], 'http://') === false) {
              $url = SITE.'/'.$page['parent_url'].$page['url'];
            } else {
              $url = $page['url'];
            }

            if ($hotFix1) {
              $print.= '<li><a href="'.SITE.'"><span '.$active.'>'.$page['title'].'</span></a>';
            } else {
              $print.= '<li><a href="'.$url.'"><span '.$active.'>'.$page['title'].'</span></a>';
            }
          }
        }
      }
      $print.= "</ul>";
    }

    $args = func_get_args();
    $result = $print;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает массив вложенных страниц на заданном уровне.
   *
   * @param int $parent  id родительской категории.
   * @return string.
   */
  public function getChildPageIds($parentId = 0) {
    $result = array();

    $res = DB::query('
      SELECT id
      FROM `'.PREFIX.'page`
      WHERE parent = %d
      ORDER BY id
    ', $parentId);

    while ($row = DB::fetchArray($res)) {
      $result[] = $row['id'];
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает список только id всех вложенных страниц.
   *
   * @param int $parent id родительской категории.
   * @return array
   */
  public function getPagesInside($parent = 0) {
    if (!empty($this->page))
      foreach ($this->page as $page) {
        if ($parent == $page['parent']) {
          $this->listCategoryId[] = $page['id'];
          $this->getPagesInside($page['id']);
        }
      }
    $args = func_get_args();
    if (!empty($this->listCategoryId)) {
      $this->listCategoryId = array_flip(array_flip($this->listCategoryId)); //удаление дублей
    }
    $result = $this->listCategoryId;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает массив id страниц и ее заголовок.
   *
   * @return array
   */
  public function getCategoryTitleList() {
    $titleList[0] = 'Корень каталога';
    if (!empty($this->page))
      foreach ($this->page as $page) {
        $titleList[$page['id']] = $page['title'];
      }

    $args = func_get_args();
    $result = $titleList;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает иерархический массив страниц.
   *
   * @param int $parent id родительской категории.
   * @return array
   */
  public function getHierarchyPage($parent = 0) {
    $catArray = array();
    if (!empty($this->page))
      foreach ($this->page as $page) {
        if ($parent == $page['parent']) {
          $child = $this->getHierarchyPage($page['id']);

          if (!empty($child)) {
            $array = $page;
            usort($child, array(__CLASS__, "sort"));
            $array['child'] = $child;
          } else {
            $array = $page;
          }

          $catArray[] = $array;
        }
      }

    $args = func_get_args();
    $result = $catArray;
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает массив дочерних страниц для заданой страницы.
   *
   * @param int $pageUrl заданая страница
   * @return array
   */
  public function getSubPages($pageUrl = false) {
    $result = array();
    if (!$pageUrl) {
      $pageUrl = URL::getClearUri();
    }
    $pageUrl = trim($pageUrl, '/').'/';

    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'page`
      WHERE parent_url = '.DB::quote($pageUrl).'
      ORDER BY id
    ');

    while ($row = DB::fetchAssoc($res)) {
      $result[] = array(
        'title' => $row['title'],
        'url' => $row['parent_url'].$row['url']);
    }


    $args = func_get_args();

    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает массив  страниц на томже уровне что  и заданная страница.
   *
   * @param int $pageUrl  заданая страница
   * @return array
   */
  public function getParallelslPage($pageUrl = false) {
    $result = array();
    if (!$pageUrl) {
      $pageUrl = URL::getClearUri();
    }

    $pageUrl = URL::parseParentUrl($pageUrl);
    $result = $this->getSubPages($pageUrl);
    $args = func_get_args();

    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает li список дочерних старниц.
   * @param string $pattern - шаблон вывода подстраниц.
   * @param int $pageUrl  заданая страница.
   * @return array
   */
  public function getListSubPage($pageUrl = false, $pattern = '<span class="#INDEX#">#TITLE#</span>') {
    $result = '';
    $pages = $this->getSubPages($pageUrl);
    $i = 1;
    foreach ($pages as $page) {
      $inside = str_replace('#TITLE#', $page['title'], $pattern);
      $inside = str_replace('#INDEX#', 'lp'.$i++, $inside);
      $result .= '
        <li><a href="'.SITE.'/'.$page['url'].'">'.$inside.'</a></li>';
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает li список страниц этого же уровня старниц.
   * @param string $pattern - шаблон вывода подстраниц.
   * @param int $pageUrl  заданая страница.
   * @return array
   */
  public function getListParallelslPage($pageUrl = false, $pattern = '<span class="#INDEX#">#TITLE#</span>') {
    $result = '';
    $pages = $this->getParallelslPage($pageUrl);
    $i = 1;
    $thisUrl = URL::getClearUri();

    foreach ($pages as $page) {
      $inside = str_replace('#TITLE#', $page['title'], $pattern);
      $inside = str_replace('#INDEX#', 'lp'.$i++, $inside);
      $active = '';
      if ('/'.$page['url'] == $thisUrl) {
        $active = 'class="active"';
      }
      $result .= '
        <li><a href="'.SITE.'/'.$page['url'].'" '.$active.'>'.$inside.'</a></li>';
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Возвращает отдельные пункты списка заголовков страниц.
   *
   * @param array $arrayCategories массив с категориями.
   * @param array $modeArray - если установлен этот флаг, то  результат вернет массив а не HTML список.
   * 
   * @return string
   */
  public function getTitlePage($arrayCategories, $selectCaegory = 0, $modeArray = false) {
    if ($modeArray) {
      global $catArr;
    }
    global $lvl;
    $option = '';
    foreach ($arrayCategories as $page) {
      $select = '';
      if ($selectCaegory == $page['id']) {
        $select = 'selected = "selected"';
      }
      $option .= '<option value='.$page['id'].' '.$select.' >';
      $option .= str_repeat('-', $lvl);
      $option .= $page['title'];
      $option .= '</option>';
      $catArr[$page['id']] = str_repeat('-', $lvl).$page['title'];
      if (isset($page['child'])) {
        $lvl++;
        $option .= $this->getTitlePage($page['child'], $selectCaegory, $modeArray);
        $lvl--;
      }
    }
    $args = func_get_args();

    $result = $option;
    if ($modeArray) {
      $result = $catArr;
    }

    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Получает параметры страницы по его URL.
   *
   * @param string $url запрашиваемой  категории.
   * @param string $parentUrl родительской категории.
   * @return array массив с данными о категории.
   *
   */
  public function getPageByUrl($url, $parentUrl = "") {
    $result = array();
    $res = DB::query('
      SELECT *
      FROM `'.PREFIX.'page`
      WHERE url = "%s" AND parent_url = "%s"
    ', $url, $parentUrl);

    if (!empty($res)) {
      if ($cat = DB::fetchAssoc($res)) {
        $result = $cat;
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   * Получает параметры Страницы по её Id.
   *
   * @param string $id запрашиваемой  страницы.
   * @return array массив с данными о странице.
   *
   */
  public function getPageById($id, $fromDb = false) {
    $result = array();
    // получаем данные из памяти
    if(!$fromDb){
      if(!empty($this->page[$id])){
        $result = $this->page[$id];
      }
    }else{
      // получаем данные из базы , необходимо при сортировке
      $res = DB::query('
       SELECT *
       FROM `'.PREFIX.'page`
       WHERE id = '.DB::quote($id)
      );

      if (!empty($res)) {
        if ($cat = DB::fetchArray($res)) {
          $result = $cat;
        }
      }
    } 
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }


  /**
   * Получает содержание страницы.
   * @param type $id - номер страницы
   * @return type
   */
  public function getDesctiption($id) {
    $result = null;
    $res = DB::query('
      SELECT html_content
      FROM `'.PREFIX.'page`
      WHERE id = "%d"
    ', $id);

    if (!empty($res)) {
      if ($cat = DB::fetchArray($res)) {
        $result = $cat['html_content'];
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }

  /**
   *  Упорядочивает страниыц по сортировке.
   */
  public function sort($a, $b) {
    return $a['sort'] - $b['sort'];
  }

  /**
   * Меняем местами параметры сортировки двух страниц
   * @param type $oneId - первый ID 
   * @param type $twoId - второй ID 
   * @return boolean
   * 
   */
  public function changeSortPage($oneId, $twoId) {
    $cat1 = $this->getPageById($oneId, true);
    $cat2 = $this->getPageById($twoId, true);
    if (!empty($cat1) && !empty($cat2)) { 
      
      $res = DB::query('
       UPDATE `'.PREFIX.'page`
       SET  `sort` = '.DB::quote($cat1['sort']).'  
       WHERE  `id` ='.DB::quote($cat2['id']).'
     ');      
   
      $res = DB::query('
       UPDATE `'.PREFIX.'page`
       SET  `sort` = '.DB::quote($cat2['sort']).'  
       WHERE  `id` ='.DB::quote($cat1['id']).'
     ');
      return true;
    }
    return false;
  
    
  }

  /**
   *  Делает все страницы видимыми в меню.
   */
  public function refreshVisiblePage() {
    $res = DB::query('
       UPDATE `'.PREFIX.'page`
       SET  `invisible` = 0  
       WHERE  1 = 1
     ');
    return true;
  }

  /**
   * Возвращает страницы, которые должны быть выведены в меню.
   * @return array массив страниц.
   */
  public function getPageInMenu() {
    $result = array();
    $res = DB::query('
      SELECT id, title, url, sort
      FROM `'.PREFIX.'page`
      WHERE print_in_menu = 1
      ORDER BY `sort` ASC
    ');

    if (!empty($res)) {
      while ($page = DB::fetchAssoc($res)) {
        $result[] = $page;
      }
    }

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $result, $args);
  }
  
  /**
   * Возвращает общее количество страниц
   */
  public function getCountPages() {
    $count = DB::query('SELECT COUNT(`id`) as count FROM `'.PREFIX.'page`');
    $count = DB::fetchAssoc($count);
    $countPages = $count['count'];
    return $countPages;
  }
}