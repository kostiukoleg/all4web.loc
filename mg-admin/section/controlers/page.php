<?php
/**
 *
 * Раздел управления страницами сайта.
 * Позволяет управлять заказами пользователей.
 *
 * @autor Авдеев Марк <mark-avdeev@mail.ru>
 */


$array = MG::get('pages')->getHierarchyPage(0);
$this->selectPages = MG::get('pages')->getTitlePage($array);
$this->pages = MG::get('pages')->getPagesUl(0, 'admin');

$this->countPages =  MG::get('pages')->getCountPages();



