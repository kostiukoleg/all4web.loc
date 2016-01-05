<?php

/**
 * Класс Pactioner наследник стандарного Actioner
 * Предназначен для выполнения действий,  AJAX запросов плагина 
 *
 * @author Avdeev Mark <mark-avdeev@mail.ru>
 */
class Pactioner extends Actioner {

  private $pluginName = 'trigger-guarantee';

  /**
   * получает список всех иконок шрифта font-awesome
   * @return boolean
   */
  public function getIcons() {
    USER::AccessOnly('1,4', 'exit()');
    ob_start();
    include 'font-awesome.php';
    $content = ob_get_contents();
    ob_end_clean();
    $this->data = $content;
    return true;
  }

  /**
   * Получает сущность 
   * @return boolean
   */
  public function getEntity() {
    USER::AccessOnly('1,4', 'exit()');
    $res = DB::query('
      SELECT * 
      FROM `'.PREFIX.$this->pluginName.'-elements`
      WHERE `id` = '.DB::quote($_POST['id']));
    if ($row = DB::fetchAssoc($res)) {
      $this->data = $row;
      return true;
    }
    return false;
  }

  /**
   * Сохраняет и обновляет параметры записи.
   * @return type
   */
  public function saveEntity() {
    //доступно только модераторам и админам.
    USER::AccessOnly('1,4', 'exit()');

    $this->messageSucces = $this->lang['ENTITY_SAVE'];
    $this->messageError = $this->lang['ENTITY_SAVE_NOT'];
    unset($_POST['mguniqueurl']);
    unset($_POST['pluginHandler']);

    if (!empty($_POST['id'])) {  // если передан ID, то обновляем
      if (DB::query('
        UPDATE `'.PREFIX.$this->pluginName.'-elements`
        SET '.DB::buildPartQuery($_POST).'
        WHERE id ='.DB::quote($_POST['id']))) {
        $this->data['row'] = $_POST;
      } else {
        return false;
      }
    } else {
      // если  не передан ID, то создаем
      if (DB::buildQuery('INSERT INTO `'.PREFIX.$this->pluginName.'-elements` SET ', $_POST)) {
        $_POST['id'] = DB::insertId();
        DB::query('
        UPDATE `'.PREFIX.$this->pluginName.'-elements`
        SET `sort`='.DB::quote($_POST['id']).'
        WHERE id ='.DB::quote($_POST['id']));
        $this->data['row'] = $_POST;
      } else {
        return false;
      }
    }
    return true;
  }

  /**
   * Сохранение триггера 
   * @param type $array - массив полей и значений
   * @return array возвращает входящий массив
   */
  public function saveTrigger() {
    //доступно только модераторам и админам.
    USER::AccessOnly('1,4', 'exit()');
    $this->messageSucces = $this->lang['ENTITY_SAVE'];
    $this->messageError = $this->lang['ENTITY_SAVE_NOT'];
    if (!empty($_POST['id'])) {  // если передан ID, то обновляем
      if (DB::query('
        UPDATE `'.PREFIX.$this->pluginName.'`
        SET `settings` = '.DB::quote(addslashes(serialize($_POST['settings']))).', `title` = '.DB::quote($_POST['title']).'
        WHERE id = '.DB::quote($_POST['id']))) {
        $parent = $_POST['id'];
        foreach ($_POST['elements'] as $key => $id) {
          DB::query('UPDATE `'.PREFIX.$this->pluginName.'-elements` SET `parent` = '.DB::quote($parent).' WHERE id = '.DB::quote($id));
        }
        $this->data['row'] = $_POST;
      } else {
        return false;
      }
    } else {
      // если  не передан ID, то создаем
      $_POST['id'] = $_POST['new_id'];
      if (DB::query('INSERT INTO `'.PREFIX.$this->pluginName.'` SET `id`='.DB::quote($_POST['id']).',`settings` = '.DB::quote(addslashes(serialize($_POST['settings']))).', 
         `title` = '.DB::quote($_POST['title']).' ')) {
        $_POST['id'] = DB::insertId();
        $parent = $_POST['id'];
        foreach ($_POST['elements'] as $key => $id) {
          DB::query('UPDATE `'.PREFIX.$this->pluginName.'-elements` SET `parent` = '.DB::quote($parent).' WHERE id = '.DB::quote($id));
        }
        $this->data['row'] = $_POST;
      } else {
        return false;
      }
    }
    return true;
  }

  // получает настройки триггера и его составляющие
  public function getTrigger() {
    //доступно только модераторам и админам.
    USER::AccessOnly('1,4', 'exit()');
    $res = DB::query('SELECT * FROM `'.PREFIX.$this->pluginName.'` WHERE `id`='.DB::quote($_POST['id']));
    if ($row = DB::fetchAssoc($res)) {
      $elements = DB::query('
        SELECT * 
        FROM `'.PREFIX.$this->pluginName.'-elements`
        WHERE `parent` = '.DB::quote($_POST['id']).' ORDER BY `sort`');
      while ($el = DB::fetchAssoc($elements)) {
        $row['elements'][] = $el;
      }
      $options = unserialize(stripslashes($row['settings']));
      $row['settings'] = $options;

      $this->data = $row;
      return true;
    } else {
      return false;
    }
    return false;
  }

  /**
   * Удаление элемент триггера
   * @return boolean
   */
  public function deleteElement() {
    //доступно только модераторам и админам.
    USER::AccessOnly('1,4', 'exit()');
    $this->messageSucces = $this->lang['ENTITY_DEL'];
    $this->messageError = $this->lang['ENTITY_DEL_NOT'];
    if (DB::query('DELETE FROM `'.PREFIX.$this->pluginName.'-elements` WHERE `id`= '.DB::quote($_POST['id']))) {
      return true;
    }
    return false;
  }

  /**
   * Удаление триггера и его элементов
   * @return boolean
   */
  public function deleteTrigger() {
    //доступно только модераторам и админам.
    USER::AccessOnly('1,4', 'exit()');
    $this->messageSucces = $this->lang['ENTITY_DEL'];
    $this->messageError = $this->lang['ENTITY_DEL_NOT'];
    if (DB::query('DELETE FROM `'.PREFIX.$this->pluginName.'` WHERE `id`= '.DB::quote($_POST['id']))) {
      DB::query('DELETE FROM `'.PREFIX.$this->pluginName.'-elements` WHERE `parent`= '.DB::quote($_POST['id']));
      return true;
    }

    return false;
  }

}
