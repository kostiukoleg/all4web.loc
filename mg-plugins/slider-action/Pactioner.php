<?php

/**
 * Класс Pactioner наследник стандарного Actioner
 * Предназначен для выполнения действий,  AJAX запросов плагина 
 *
 * @author Avdeev Mark <mark-avdeev@mail.ru>
 */
class Pactioner extends Actioner {

  private $pluginName = 'slider-action';
  
  public function addEntity($array) {    
    unset($array['id']);
    $result = array();
    DB::buildQuery('INSERT INTO `'.PREFIX.$this->pluginName.'` SET ', $array);
    return $result;
  }

  public function updateEntity($array) {
    $id = $array['id'];
    $result = false;
    if (!empty($id)) {
      if (DB::query('
        UPDATE `'.PREFIX.$this->pluginName.'`
        SET '.DB::buildPartQuery($array).'
        WHERE id = '.DB::quote($id))) {
        $result = true;
      }
    } else {
      $result = $this->addEntity($array);
    }
    return $result;
  }

  public function deleteEntity() {
    $this->messageSucces = $this->lang['ENTITY_DEL'];
    $this->messageError = $this->lang['ENTITY_DEL_NOT'];
    if (DB::query('DELETE FROM `'.PREFIX.$this->pluginName.'` WHERE `id`= '.DB::quote($_POST['id']))) {
      return true;
    }
    return false;
  }

  public function getEntity() {   
    $res = DB::query('
      SELECT * 
      FROM `'.PREFIX.$this->pluginName.'`
      WHERE `id` = '.DB::quote($_POST['id']).' ORDER BY sort ASC');

    if ($row = DB::fetchAssoc($res)) {
      $this->data = $row;
      return true;
    } else {
      return false;
    }

    return false;
  }

  /**
   * Сохраняет и обновляет параметры записи.
   * @return type
   */
  public function saveEntity() {

    $this->messageSucces = $this->lang['ENTITY_SAVE'];
    $this->messageError = $this->lang['ENTITY_SAVE_NOT'];

    unset($_POST['pluginHandler']);

    if (!empty($_POST['id'])) {  // если передан ID, то обновляем
      if (DB::query('
        UPDATE `'.PREFIX.$this->pluginName.'`
        SET '.DB::buildPartQuery($_POST).'
        WHERE id = '.DB::quote($_POST['id']))) {
        $this->data['row'] = $_POST;
        $this->data['slider'] = SliderAction::sliderAction();
      } else {
        return false;
      }
    } else {
      // если  не передан ID, то создаем
      if (DB::buildQuery('INSERT INTO `'.PREFIX.$this->pluginName.'` SET ', $_POST)) {
        $_POST['id'] = DB::insertId();
        
        DB::query('
          UPDATE `'.PREFIX.$this->pluginName.'`        
          SET `sort` = `id`
          WHERE `id` = '.DB::quote($_POST['id'])
        );
        
        $this->data['row'] = $_POST;
        $this->data['slider'] = SliderAction::sliderAction();
      } else {
        return false;
      }
    }
    return true;
  }

  /**
   * Устанавливает количество отображаемых записей в разделе новостей
   * @return boolean
   */
  public function setCountPrintRowsNews() {

    $count = 20;
    if (is_numeric($_POST['count'])&&!empty($_POST['count'])) {
      $count = $_POST['count'];
    }

    MG::setOption(array('option' => 'countPrintRowsNews ', 'value' => $count));
    return true;
  }

  /**
   * Устанавливает флаг  активности  
   * @return type
   */
  public function visibleEntity() {
    $this->messageSucces = $this->lang['ACT_V_ENTITY'];
    $this->messageError = $this->lang['ACT_UNV_ENTITY'];

    //обновление
    if (!empty($_POST['id'])) {
      unset($_POST['pluginHandler']);
      $this->updateEntity($_POST);
    }

    if ($_POST['invisible']) {
      return true;
    }

    return false;
  }

  /**
   * Устанавливает количество отображаемых записей в разделе новостей
   * @return boolean
   */
  public function saveBaseOption() {
    $this->messageSucces = $this->lang['SAVE_BASE'];
    $this->messageError = $this->lang['NOT_SAVE_BASE'];
    if (!empty($_POST['data'])) {
      MG::setOption(array('option' => 'sliderActionOption', 'value' => addslashes(serialize($_POST['data']))));
    }
    $this->data = SliderAction::sliderAction();
    return true;
  }

  /**
   * Получает верстку обновленного слайдера, нужна для админки
   * @return boolean
   */
  public function reloadSlider() {
    $this->data = SliderAction::sliderAction();
    return true;
  }

}

