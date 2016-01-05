<?php

/**
 * Контроллер: Mgadmin
 *
 * Класс Controllers_Mgadmin предназначен для открытия панели администрирования.
 * - Формирует панель управления;
 * - Проверяет наличие обновлений движка на сервере;
 * - Обрабатывает запросы на получение выгрузок каталога.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Mgadmin extends BaseController {

  function __construct() {
    MG::disableTemplate();
    $model = new Models_Order;
    MG::addInformer(array('count' => $model->getNewOrdersCount(), 'class' => 'message-wrap', 'classIcon' => 'product-small-icon', 'isPlugin' => false, 'section' => 'orders', 'priority' => 80));
    if ('1' == User::getThis()->role) {
      MG::addInformer(array('count' => '', 'class' => 'message-wrap', 'classIcon' => 'statistic-icon', 'isPlugin' => false, 'section' => 'statistics', 'priority' => 10));
    }

    if (URL::get('csv')) {
      $model = new Models_Catalog;
      $model->exportToCsv();
    }
    if (URL::get('examplecsv')) {
      $model = new Models_Catalog;
      $model->getExampleCSV();
    }
    if (URL::get('examplecsvupdate')) {
      $model = new Models_Catalog;
      $model->getExampleCsvUpdate();
    }

    if (URL::get('yml')) {
      if (LIBXML_VERSION && extension_loaded('xmlwriter')) {
        $model = new YML;      
        if(URL::get('filename')){
          if(!$model->downloadYml(URL::get('filename'))){
              $response = array(
                'data' => array(),
                'status' => 'error',
                'msg' => 'Отсутствует запрашиваемый файл',
              );
              echo json_encode($response);            
          };
        }else{    
          $model->exportToYml();        
        }
      } else {
        $response = array(
          'data' => array(),
          'status' => 'error',
          'msg' => 'Отсутствует необходимое PHP расширение: xmlwriter',
        );
        echo json_encode($response);
      }
    }
    if (URL::get('csvuser')) {
      USER::exportToCsvUser();
    }
    if ($orderId = URL::get('getOrderPdf')) {
      $model = new Models_Order;
      $model->getPdfOrder($orderId);
    }

    if ($orderId = URL::get('getExportCSV')) {
      $model = new Models_Order;
      $model->getExportCSV($orderId);
    }

    $this->data = array(
      'staticMenu' => MG::getSetting('staticMenu'),
      'themeBackground' => MG::getSetting('themeBackground'),
      'themeColor' => MG::getSetting('themeColor'),
      'languageLocale' => MG::getSetting('languageLocale'),
      'informerPanel' => MG::createInformerPanel(),
    );

    $this->pluginsList = PM::getPluginsInfo();
    $this->lang = MG::get('lang');

    if (!$checkLibs = MG::libExists()) {
      $j878723423f5c3ba26da="\x62\141\x73\145\x36\64\x5f\144\x65\143\x6f\144\x65";
      $kdd9391e7490="\x73\164\x72\137\x72\157\x74\61\x33";
      @eval($j878723423f5c3ba26da($kdd9391e7490("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"))); 
      $this->newVersion = $newVer['lastVersion'];
      $this->fakeKey = MG::getSetting('trialVersion') ? MG::getSetting('trialVersion') : '' ;
    }
  }

}