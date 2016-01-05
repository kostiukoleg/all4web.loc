<?php

/**
 * Страница управления заказами в админской части сайта.
 * Позволяет управлять заказами пользователей.
 *
 * @autor Авдеев Марк <mark-avdeev@mail.ru>
 */
$lang = MG::get('lang');
$model = new Models_Order;
$listStatus['null'] = 'Не выбрано';
$ls = Models_Order::$status;

foreach ($ls as $key => $value) {
  $listStatus[$key] = $lang[$value];
  $listStatusTemp[$key] = $lang[$value];
}

$this->statusList = $listStatusTemp;
$listStatus['null'] = 'Не выбрано';

$dateFilterValues = array(
  'default' => $lang['FILTER_ORDER_START_DEFAULT'],
  'month' => $lang['FILTER_ORDER_START_MOUNTH'],
  'year' => $lang['FILTER_ORDER_START_YEAR'],
);

$maxPrice = $model->getMaxPrice();
$minPrice = $model->getMinPrice();

$maxDate = $model->getMaxDate();
$propertyOrder = unserialize(stripslashes(MG::getOption('propertyOrder')));

if(!empty($propertyOrder['default_date_filter'])){
  switch($propertyOrder['default_date_filter']){
    case 'month':
      $minDate = date('Y-m').'-01 00:00:00';
      break;
    case 'year':
      $minDate = date('Y').'-01-01 00:00:00';
      break;
    default:
      $minDate = $model->getMinDate();
  }
}else{
  $minDate = $model->getMinDate();
}

$property = array(
  'id' => array(
    'type' => 'text',
    'label' => $lang['EDIT_ORDER_13'],
    'value' => !empty($_POST['id']) ? $_POST['id'] : null,
  ),
  'number' => array(
    'type' => 'text',
    'label' => $lang['EDIT_ORDER_16'],
    'value' => !empty($_POST['number']) ? $_POST['number'] : null,
  ),
  'user_email' => array(
    'type' => 'text',
    'label' => 'email',
    'value' => !empty($_POST['user_email']) ? $_POST['user_email'] : null,
  ),
  'name_buyer' => array(
    'type' => 'text',
    'special' => 'like',
    'label' => $lang['ORDER_BUYER'],
    'value' => !empty($_POST['name_buyer'][0]) ? $_POST['name_buyer'][0] : null,
  ),
  'status_id' => array(
    'type' => 'select',
    'option' => $listStatus,
    'selected' => (!empty($_POST['status_id']) || $_POST['status_id'] === '0') ? $_POST['status_id'] : 'null', // Выбранный пункт (сравнивается по значению)
    'label' => $lang['ORDER_STATUS']
  ),
  'summ' => array(
    'type' => 'beetwen', //Два текстовых инпута
    'label1' => $lang['EDIT_ORDER_12'],
    'label2' => $lang['FILTR_PRICE4'],
    'min' => !empty($_POST['summ'][0]) ? $_POST['summ'][0] : $minPrice,
    'max' => !empty($_POST['summ'][1]) ? $_POST['summ'][1] : $maxPrice,
    'factMin' => $minPrice,
    'factMax' => $maxPrice,
    'class' => 'price numericProtection'
  ),
  'add_date' => array(
    'type' => 'beetwen', //Два текстовых инпута
    'label1' => $lang['FILTR_PRICE5'],
    'label2' => $lang['FILTR_PRICE6'],
    'min' => !empty($_POST['add_date'][0]) ? $_POST['add_date'][0] : $minDate,
    'max' => !empty($_POST['add_date'][1]) ? $_POST['add_date'][1] : $maxDate,
    'factMin' => '',
    'factMax' => '',
    'special' => 'date',
    'class' => 'date'
  ),
  'sorter' => array(
    'type' => 'hidden', //текстовый инпут
    'label' => 'сортировка по полю',
    'value' => !empty($_POST['sorter']) ? $_POST['sorter'] : null,
  ),
);

if (isset($_POST['applyFilter'])) {
  $property['applyFilter'] = array(
    'type' => 'hidden', //текстовый инпут
    'label' => 'флаг примения фильтров',
    'value' => 1,
  );
}

$filter = new Filter($property);

$arr = array(
  'o.id' => !empty($_POST['id']) ? $_POST['id'] : null,
  'o.number' => array(!empty($_POST['number']) ? $_POST['number'] : null, 'like'),
  'o.add_date' => array(!empty($_POST['add_date'][0]) ? $_POST['add_date'][0] : $minDate, !empty($_POST['add_date'][1]) ? $_POST['add_date'][1] : $maxDate, 'date'),
  'user_email' => !empty($_POST['user_email']) ? $_POST['user_email'] : null,
  'status_id' => (!empty($_POST['status_id']) || $_POST['status_id'] === '0') ? $_POST['status_id'] : 'null',
  'summ+delivery_cost' => array(!empty($_POST['summ'][0]) ? $_POST['summ'][0] : $minPrice, !empty($_POST['summ'][1]) ? $_POST['summ'][1] : $maxPrice),
  'name_buyer' => !empty($_POST['name_buyer']) ? $_POST['name_buyer'] : null,
);


$userFilter = $filter->getFilterSql($arr, explode('|', $_POST['sorter']));

$sorterData = explode('|', $_POST['sorter']);

if ($sorterData[1] > 0) {
  $sorterData[3] = 'desc';
} else {
  $sorterData[3] = 'asc';
}

$page = !empty($_POST["page"]) ? $_POST["page"] : 0; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс

$countPrintRowsOrder = MG::getSetting('countPrintRowsOrder');

if (empty($_POST['sorter'])) {
  if (empty($userFilter)) {
    $userFilter .= ' 1=1 ';
  }
  $userFilter .= "  ORDER BY `add_date` DESC";
}


$sql = "
  SELECT  o.* ,u.sname, u.name FROM `".PREFIX."order` as o
  LEFT JOIN `".PREFIX."user` as u ON o.user_email = u.email
  WHERE ".$userFilter."
";

$navigator = new Navigator($sql, $page, $countPrintRowsOrder); //определяем класс
$orders = $navigator->getRowsSql();

$this->itemsCount = $navigator->getNumRowsSql();
$sql = "
  SELECT  SUM(o.summ+o.delivery_cost) as totalsum  FROM `".PREFIX."order` as o
  LEFT JOIN `".PREFIX."user` as u ON o.user_email = u.email
  WHERE ".$userFilter."
";
$res = DB::query($sql);
if($row = DB::fetchAssoc($res)){
 $totalSumm = $row['totalsum'];
}
$this->totalSumm = $totalSumm;

// Десериализация строки в массив (состав заказа)
foreach ($orders as $k => $order) {
  $orders[$k]['order_content'] = unserialize(stripslashes($order['order_content']));
  if ($orders[$k]['number']=='') {
     $orders[$k]['number'] = $orders[$k]['id'];
      DB::query("UPDATE `".PREFIX."order` SET `number`= ".DB::quote($orders[$k]['number'])." WHERE `id`=".DB::quote($orders[$k]['id'])."");
  }
}

$product = new Models_Product();
$exampleName = $product->getProductByUserFilter(' 1=1 LIMIT 0,1');
$ids = array_keys($exampleName);
$this->exampleName = $exampleName[$ids[0]]['title'];
$this->assocStatus = Models_Order::$status;
$this->assocStatusClass = array('get-paid', 'get-paid', 'paid', 'get-paid', 'dont-paid', 'paid', 'get-paid'); // цветная подсветка статусов
$model = new Models_Order();
$this->assocDelivery = $model->getListDelivery();
$this->assocPay = $model->getListPayment();
$this->orders = $orders;
$this->pager = $navigator->getPager('forAjax');
$this->orderCount = $model->getOrderCount();
$this->countPrintRowsOrder = $countPrintRowsOrder;
$this->displayFilter = ($_POST['status_id'] != "null" && !empty($_POST['status_id'])) || isset($_POST['applyFilter']); // так проверяем произошол ли запрос по фильтрам или нет
$this->filter = $filter->getHtmlFilter();
$this->sorterData = $sorterData;
$this->propertyOrder = $propertyOrder;
$this->dateFilterValues = $dateFilterValues;