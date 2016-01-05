<?php

/*
  Plugin Name: Хлебные крошки
  Description: Выводит навигационную цепочку в каталоге товаров. Для вывода в файлах темы views/catalog.php и views/product.php необходимо вставить шорт код [brcr]
  Author: Дмитрий Гринчевский, Авдеев Марк
  Version: 2.1.5
 */
new BreadCrumbs;

class BreadCrumbs {

  public function __construct() {
    mgAddShortcode('brcr', array(__CLASS__, 'breadcrumbs'));
  }

  static function breadcrumbs() {
    $breadcrumbs = Storage::get(md5('breadcrumbs'.URL::getUrl()));
    if ($breadcrumbs == null) {
      $sections = URL::getSections();
      array_splice($sections, 0, 1);
      if (SHORT_LINK == 1 && MG::get('controller')=='controllers_product') {
        $product_url = URL::getLastSection();
        $res = DB::query('SELECT CONCAT(c.`parent_url`, c.`url`) as fullurl
          FROM `'.PREFIX.'product` p LEFT JOIN `'.PREFIX.'category` c 
          ON p.cat_id  = c.id WHERE p.url = '.DB::quote($product_url));
        $cat = DB::fetchArray($res);
        $sections = explode('/', $cat['fullurl']);
        $sections[] = $product_url;
      }
      $breadcrumbs = '<a href="'.SITE.'/catalog">Каталог</a>';
      $max = count($sections);
      $i = 0;
      $par = '';
      foreach ($sections as $section) {
        $url = $section;
        $cat = 'title';
        if ($url != 'catalog') {
        $data = self::checkURLname('*', 'category', $section, 'url', $par);

        $url = $data[0]['parent_url'].$section;
        $res = $data[0]['title'];
        $par = $data[0]['id'];

        if (!$data[0]['title']) {
          $cat = 'name';
          $n = '';
          $result = self::checkURLname('*', 'product', $section, 'url', $n);
          $url = $data[0]['parent_url'].$sections[1].'/'.$sections[2];
          $categoryRes = self::checkURLname('url, parent_url', 'category', $result[0]['cat_id'], 'id');
          $url = $categoryRes[0]['parent_url'].$categoryRes[0]['url'].'/'.$result[0]['url'];
          $res = $result[0]['title'];
        }
        }
        $i++;
        if ($max == $i) {
          $breadcrumbs .= ' » <span class="last-crumb">'.$res.'</span>';
        } else {
          $breadcrumbs .= ' » <a href="'.SITE.'/'.$url.'">'.$res.'</a>';
        }
      }
      $breadcrumbs = "<div class='bread-crumbs'>".$breadcrumbs."</div>";
      //сохраняем объект в кэш
      Storage::save(md5('breadcrumbs'.URL::getUrl()), $breadcrumbs);
    }
    return $breadcrumbs;
  }

  /**
   * Метод работает с БД, получая значение по передаваемым параметрам.
   *
   * @param string $col что.
   * @param string $table от куда.
   * @param string $name условие соответствие.
   * @return array массив с результатом.
   */
  static function checkURLname($col, $table, $name, $where1, $parent_id = '') {
    if ($parent_id || $table=='category') {
      
      if(empty($parent_id)){
        $parent_id = 0;
      }
      
      $where2 = 'parent';
      $sql = 'SELECT '.DB::quote($col, true).' FROM '.PREFIX.DB::quote($table, true).
          ' WHERE '.DB::quote($where1, true).'='.DB::quote($name).'  AND '.DB::quote($where2, true).'='.DB::quote($parent_id).'';
      $result = DB::query($sql);
    } else {
      $sql = 'SELECT '.DB::quote($col, true).' FROM '.PREFIX.DB::quote($table, true).'  WHERE '.DB::quote($where1, true).'='.DB::quote($name).'  ';
      $result = DB::query($sql);
    }
    while ($row = DB::fetchArray($result)) {
      $categories[] = $row;
    }
    if ($result) {
      return $categories;
    }
  }

}