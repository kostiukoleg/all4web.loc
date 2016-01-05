<?php

/**
 * Класс для экспорта товаров из каталога сайта на Yandex.Market.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
class YML {

  public function __construct() {
    
  }

  /**
   * Выгружает содержание всего каталога в CSV файл
   * @return array
   */
  public function exportToYml($listProductId=array(),$getYml=false) {
    
	if(!$getYml){
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream;");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename=data.xml");
    header("Content-Transfer-Encoding: binary ");
    }
    $currencyShopIso = MG::getSetting('currencyShopIso');
    $currencyRate = MG::getSetting('currencyRate');   
    $currencyRate  = $currencyRate[$currencyShopIso];


    $nXML = '<?xml version="1.0" encoding="windows-1251"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">';
    
    $nXML = mb_convert_encoding($nXML, "WINDOWS-1251", "UTF-8");
    if($getYml){
	 return $nXML;
	}
    if(empty($listProductId)){
      echo $nXML;
      exit;
    } else{
      $date = date('m_d_Y_h_i_s');
      file_put_contents('data_yml_'.$date.'.xml', $nXML);
      $msg = 'data_yml_'.$date.'.xml';
    }
    return $msg;  
  }
  
  /**
   * открывает запрошенный файл и отдает его в браузер с нужными заголовками
   * позволяет скачать указанный файл из панели управления в режиме аякс.
   * @return array
   */
  public function downloadYml($filename) {
    $documentroot = str_replace(DIRECTORY_SEPARATOR.'mg-core'.DIRECTORY_SEPARATOR.'lib','',dirname(__FILE__)).DIRECTORY_SEPARATOR; 
    if(is_file($documentroot.DIRECTORY_SEPARATOR.$filename)){
      header("Content-Type: application/force-download");
      header("Content-Type: application/octet-stream;");
      header("Content-Type: application/download");
      header("Content-Disposition: attachment;filename=".$filename);
      header("Content-Transfer-Encoding: binary ");   
      echo file_get_contents($filename);
      exit;      
    } 
    return false;    
  }

}

