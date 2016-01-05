<?php

/**
 * Класс Menu - задает пункты меню сайта.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class Menu {

  private function __construct() {
    
  }

  /**
   * Возвращает меню в HTML виде.
   * @param string type = top или footer. Footer - вернет  три списка с равным количеством пунктов
   */
  public static function getMenuFull($type = 'top') {   
  
    if($type == 'top'){
      $pages = MG::get('pages')->getHierarchyPage();
      $print =  MG::layoutManager('layout_topmenu', array('pages'=>$pages));
    }
    
    if($type == 'footer'){      
      $print .= MG::get('pages')->getFooterPagesUl(0);     
    }
   
    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $print, $args);
  }

  /**
   * Возвращает меню в HTML виде.
   * @return type
   */
  public static function getMenu() {
    $menuItem = self::getArrayMenu();

    $print = '<ul class="top-menu-list">';

    foreach ($menuItem as $name => $item) {

      if ('Вход' == $item['title'] && '' != $_SESSION['User']) {
        $print .= '<li><a href='.SITE.'"/enter">'.$_SESSION['User'].'</a><a class="logOut" href="enter?out=1"><span style="font-size:10px">[ выйти ]</span></a></li>';
      } else {
        $item['title'] = MG::contextEditor('page', $item['title'], $item["id"], 'page');
        $print .= '<li><a href="'.$item['url'].'">'.$item['title'].'</a></li>';
      }
    }

    $print .= '</ul>';

    $args = func_get_args();
    return MG::createHook(__CLASS__."_".__FUNCTION__, $print, $args);
  }

  /**
   * Возвращает массив пунктов меню.
   * @return type
   */
  public static function getArrayMenu() {

   
    $arrPages = MG::get('pages')->getPageInMenu();   
    
    $menuItem = array();
    foreach ($arrPages as $item) {
      
      if ($item['url'] == "index" || $item['url'] == "index.html") {
        $item['url'] = '';
      }
      
      if(strpos($item['url'],'http://')===false){     
        $url = SITE.'/'.$item['url'];
      }else{
        $url = $item['url'];
      }
 
      $menuItem[] = array('title' => $item['title'], 'id' => $item['id'], 'url' => $url);      
      
    }  

    return $menuItem;
  }

}