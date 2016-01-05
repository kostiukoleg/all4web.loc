<?php

/**
 *
 * Раздел статистика магазина.
 * Позволяет отследить динамику развития сайта.
 *
 * @autor Авдеев Марк <mark-avdeev@mail.ru>
 */

$model = new Models_Order;


$model = new Models_Order;

$_POST['from_date_stat'] = date("1.m.Y");
$_POST['to_date_stat'] = date("31.m.Y");

$this->data = $model->getStatisticPeriod($_POST['from_date_stat'],$_POST['to_date_stat']);
