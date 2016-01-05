<?php

/**
 * Класс Pactioner наследник стандарного Actioner
 * Предназначен для выполнения действий,  AJAX запросов плагина 
 *
 * @author Avdeev Mark <mark-avdeev@mail.ru>
 */
class Pactioner extends Actioner {

  private $pluginName = 'rating';

  /**
   * Добавление сущности в таблицу БД
   * @param type $array - массив полей и значений
   * @return array возвращает входящий массив
   */
  public function addEntity($array) {
    unset($array['id']);
    if (DB::buildQuery('INSERT INTO `'.PREFIX."product_".$this->pluginName.'` SET ', $array)){
      return true;
    }
    return false;
  }

  /**
   * Обновление сущности в таблице БД
   * @param type $array - массив полей и значений
   * @return array возвращает входящий массив
   */
  public function updateEntity($array) {
    $id = $array['id'];
    $result = false;
    if (!empty($id)) {
      if (DB::query('
        UPDATE `'.PREFIX."product_".$this->pluginName.'`
        SET '.DB::buildPartQuery($array).'
        WHERE id = '.DB::quote($id))) {
        $result = true;
      }
    } 
    return $result;
  }

  /**
   * Получаем запись из БД плагина, если существует запись к данному товару
   * @return массив, если существует, если нет то вызываем функцию добавления.
   */
  public function getEntity() {
    $this->addCookie($_POST['id_product']);
    $res = DB::query('
      SELECT * 
      FROM `'.PREFIX."product_".$this->pluginName.'`
      WHERE `id_product` = '.DB::quote($_POST['id_product']));

    if ($row = DB::fetchAssoc($res)) {
      $this->updateEntity(array('id' =>  $row['id'], 'id_product' => $row['id_product'], 'rating' => $row['rating']+ $_POST['rating'],'count' =>$row['count']+$_POST['count']));
      } 
    else {
      $this->addEntity(array('id_product' => $_POST['id_product'], 'rating' => $_POST['rating'], 'count'=> $_POST['count']));
    }
    $result = DB::query('
      SELECT * 
      FROM `'.PREFIX."product_".$this->pluginName.'`
      WHERE `id_product` = '.DB::quote($_POST['id_product']));
    if ($row = DB::fetchAssoc($result)) {
      $this->data['row']=$row;
      return true;
    }
    return false;
  }
  
  public function addCookie($id){
    if (isset($_COOKIE['rating_product'])) {
      $array = json_decode($_COOKIE['rating_product'], true);
    }
    $array[] = $id;
    $json = json_encode($array);
    setcookie ("rating_product", $json);
    return true;
  }
  
}
