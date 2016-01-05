<?php

$lang = MG::get('lang');
$arrayCategories = MG::get('category')->getHierarchyCategory(0);

$listCategory['null'] = $lang['NO_SELECT'];
$lc = MG::get('category')->getTitleCategory($arrayCategories, URL::get('category_id'), true);
if(!empty($lc)){
  foreach ($lc as $key => $value) {
    $listCategory[$key] = $value;
  }
}

$this->listCategory = $listCategory;

$model = new Models_Catalog;
if(empty($_REQUEST['cat_id'])||$_REQUEST['cat_id']=="null"){
  $_REQUEST['cat_id'] = 0;
}

$model->categoryId = MG::get('category')->getCategoryList($_REQUEST['cat_id']);

// В конец списка, добавляем корневую текущую категорию.

$model->categoryId[] = $_REQUEST['cat_id'];

if(!$model->getCurrentCategory()){
  $model->categoryId = array(0);
}

$_REQUEST['category_ids'] = $model->categoryId;

if(empty($_REQUEST['category_ids'])){
  $_REQUEST['category_ids'] = array(0);
}

$arrfilter = $model->filterPublic(false,false,false);

$this->filter = $arrfilter['filterBarHtml'];
if(isset($_REQUEST['applyFilter'])){
  $userFilter = $arrfilter['userFilter'];
}

$sorterData = explode('|', $_POST['sorter']);
if ($sorterData[1]>0) {
  $sorterData[3] = 'desc';
} else {
  $sorterData[3] = 'asc';
}

$countPrintRowsProduct = MG::getSetting('countPrintRowsProduct');


  if (!empty($userFilter)) {
    $catalog = $model->getListByUserFilter($countPrintRowsProduct, $userFilter, true);
  } else {
    $catalog = $model->getList($countPrintRowsProduct, true);  
  }

//категории:
$listCategories = MG::get('category')->getCategoryTitleList();
$arrayCategories = $model->categoryId = MG::get('category')->getHierarchyCategory(0);
$categoriesOptions = MG::get('category')->getTitleCategory($arrayCategories, URL::get('category_id'));

$product = new Models_Product;
$this->productsCount = $product->getProductsCount();
$this->catalog = $catalog['catalogItems'];
$this->listCategories = $listCategories;
$this->categoriesOptions = $categoriesOptions;
$this->countPrintRowsProduct = $countPrintRowsProduct;
$this->pagination = $catalog['pager'];
$this->displayFilter = ($_POST['cat_id']!="null"&&!empty($_POST['cat_id']))||isset($_POST['displayFilter']); // так проверяем произошол ли запрос по фильтрам или нет
$this->settings = MG::get('settings');
$this->sorterData = $sorterData;
$exampleName = $product->getProductByUserFilter(' 1=1 LIMIT 0,1');
$ids =  array_keys($exampleName); 
$this->exampleName=$exampleName[$ids[0]]['title'];
