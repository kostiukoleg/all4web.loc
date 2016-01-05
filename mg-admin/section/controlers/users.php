<?php

/**
 *
 * Раздел управления пользователями.
 * Позволяет управлять учетными записями пользователей.
 *
 * @autor Авдеев Марк <mark-avdeev@mail.ru>
 */
$page = !empty($_POST["page"]) ? $_POST["page"] : 0; //если был произведен запрос другой страницы, то присваиваем переменной новый индекс
$lang = MG::get('lang');
$maxDate = USER::getMaxDate();
$minDate = USER::getMinDate();
$listRoles = array(
  'null' => 'Не выбрано',
  '1' => 'Администратор',
  '2' => 'Пользователь',
  '3' => 'Менеджер',
  '4' => 'Модератор'
);

$property = array(
  'email' => array(
    'type' => 'text',
    'label' => 'email',
    'value' => !empty($_POST['email']) ? $_POST['email'] : null,
  ),
  'date_add' => array(
    'type' => 'beetwen', //Два текстовых инпута
    'label1' => 'Регистрация '.$lang['FILTR_PRICE5'],
    'label2' => $lang['FILTR_PRICE6'],
    'min' => !empty($_POST['date_add'][0]) ? $_POST['date_add'][0] : $minDate,
    'max' => !empty($_POST['date_add'][1]) ? $_POST['date_add'][1] : $maxDate,
    'special' => 'date',
    'class' => 'date'
  ),
  'sorter' => array(
    'type' => 'hidden', //текстовый инпут
    'label' => 'сортировка по полю',
    'value' => !empty($_POST['sorter']) ? $_POST['sorter'] : null,
  ),
  'role' => array(
    'type' => 'select',
    'option' => $listRoles,
    'selected' => (!empty($_POST['role'])) ? $_POST['role'] : 'null', // Выбранный пункт (сравнивается по значению)
    'label' => $lang['USER_GROUP']
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
  'date_add' => array(!empty($_POST['date_add'][0]) ? $_POST['date_add'][0] : $minDate, !empty($_POST['date_add'][1]) ? $_POST['date_add'][1] : $maxDate, 'date'),
  'email' => !empty($_POST['email']) ? $_POST['email'] : null,
  'role' => !empty($_POST['role']) ? $_POST['role'] : 'null',
);

$userFilter = $filter->getFilterSql($arr, explode('|', $_POST['sorter']));

$sorterData = explode('|', $_POST['sorter']);

if ($sorterData[1] > 0) {
  $sorterData[3] = 'desc';
} else {
  $sorterData[3] = 'asc';
}


$countPrintRowsUser = MG::getSetting('countPrintRowsUser');

if (empty($_POST['sorter'])) {
  if (empty($userFilter)) {
    $userFilter .= ' 1=1 ';
  }
  $userFilter .= "ORDER BY `date_add` DESC";
}


$navigator = new Navigator("SELECT  *  FROM `".PREFIX."user` WHERE ".$userFilter."", $page, $countPrintRowsUser); //определяем класс
$users = $navigator->getRowsSql();

$this->accessStatus = USER::$accessStatus;
$this->groupName = USER::$groupName;

$this->users = $navigator->getRowsSql();
$this->pagination = $navigator->getPager('forAjax');
$this->countPrintRowsUser = $countPrintRowsUser;
$res = DB::query("SELECT id  FROM `".PREFIX."user`");
$count = DB::numRows($res);
$this->usersCount = $count;
$this->displayFilter = ($_POST['role'] != "null" && !empty($_POST['role'])) || isset($_POST['applyFilter']); // так проверяем произошол ли запрос по фильтрам или нет
$this->filter = $filter->getHtmlFilter();
$this->sorterData = $sorterData;