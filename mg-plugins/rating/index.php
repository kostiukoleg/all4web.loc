<?php

/*
  Plugin Name: Рейтинг товаров
  Description: Плагин вывода "звездочек" для оценки товара от 1 до 5. После подключения плагина необходимо вставить в ваш шаблон, в верстку файла view/product.php код: [rating id = "&lt;?php echo $data['id']  ?>"]  , а в верстку файла view/catalog шорткод: [rating id = "&lt;?php  echo ($item ['id']) ?>"]
  Author: Дарья Чуркина
  Version: 1.1.4
 */

new Rating;

class Rating {

  private static $lang = array(); // массив с переводом плагина 
  private static $pluginName = ''; // название плагина (соответствует названию папки)
  private static $path = ''; //путь до файлов плагина 

  public function __construct() {

    mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate')); //Инициализация  метода выполняющегося при активации  
    mgAddShortcode('rating', array(__CLASS__, 'showRating')); // Инициализация шорткода [rating] - доступен в любом HTML коде движка.    

    self::$pluginName = PM::getFolderPlugin(__FILE__);
    self::$lang = PM::plugLocales(self::$pluginName);
    self::$path = PLUGIN_DIR.self::$pluginName;

    if (!URL::isSection('mg-admin')) { // подключаем CSS плагина для всех страниц, кроме админки
      mgAddMeta('<link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/rateit.css" type="text/css" />');
    }

    mgAddMeta('<script type="text/javascript" src="'.SITE.'/'.self::$path.'/js/rating.js"></script>');
    // подключаем плагин для работы с отображение звезд системы рейтинга
    mgAddMeta('<script type="text/javascript" src="'.SITE.'/'.self::$path.'/js/jquery.rateit.min.js"></script>');
    
  }

  /**
   * Метод выполняющийся при активации палагина 
   */
  static function activate() {
    self::createDateBase();
  }

  /**
   * Создает таблицу плагина в БД
   */
  static function createDateBase() {
    DB::query("
     CREATE TABLE IF NOT EXISTS `".PREFIX."product_".self::$pluginName."` (     
      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер записи',     
      `id_product` int(11) NOT NULL COMMENT 'ID товара',
      `rating` double NOT NULL COMMENT 'Оценка',      
      `count` int(11) NOT NULL COMMENT 'Количество голосов',
       PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
  }

  /**
   * Получает из БД запись рейтинга по id товара
   */
  static function getEntity($id) {
    $result = array();
    $sql = "SELECT * FROM `".PREFIX."product_".self::$pluginName."` WHERE `id_product` = ".$id.";";
    $result = DB::query($sql);
    while ($row = DB::fetchAssoc($result)) {
      $array[] = $row;
    }
    return $array;
  }

  /**
   * Функция вывода рейтинга на месте шорткода 
   * [rating id = "<?php echo $data['id'] ?>"] и [rating id = "<?php  echo ($item ['id']) ?>"]
   *  @param type $vote - массив с данными о рейтнге (полностью запись из БД)
   */
  static function showRating($vote) {
    if ($vote['id']) {
    $check = 0;
    if (isset($_COOKIE['rating_product'])) {
      $array_id = json_decode($_COOKIE['rating_product']);
      if (in_array($vote['id'], $array_id)){
        $check = 1;
      }
    }
    $entity = self::getEntity($vote['id']);
    $core = "";
    // если запись об рейтинге товара нет в БД плагина
    foreach ($entity as $rows) {
      $rating = round($rows['rating'] / $rows['count'], 1);
      $count = $rows['count'];
      $id = $rows['id_product'];
      $core = "<span class='info' data-id = '".$id."'><span class='mg-rating-count' data-count='".$id."'>(<span itemprop='ratingValue'>".$rating."</span>/<span itemprop='ratingCount'>".$count."</span>)</span></span></div>
				";
    }
    if ($core == "") {
      $rating = $count = 0;
      $core = "<span class='info' data-id = '".$vote['id']."'><span class='mg-rating-count' data-count='".$vote['id']."'>(<span itemprop='ratingValue'>".$rating."</span>/<span itemprop='ratingCount'>".$count."</span>)</span></span></div>";
    }
    $core = "<div itemprop='aggregateRating' itemscope itemtype='http://schema.org/AggregateRating'> 
            <div class='rating-action' data-rating-id = ".$vote['id'].">
            <div class='rateit' data-plugin='stars' data-rateit-value =".$rating."
                data-productid=".$vote['id']." data-rateit-readonly=".$check.">
            </div>    
            </div>
            ".$core;

    return $core;
   }
  }
}
