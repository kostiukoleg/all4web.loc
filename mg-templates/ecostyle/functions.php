<?php

/**
 * Файл может содержать ряд пользовательских фунций влияющих на работу движка. 
 * В данном файле можно использовать собственные обработчики 
 * перехватывая функции движка, аналогично работе плагинов.
 * 
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage File
 */
function seoMeta($args) {
  $settings = MG::get('settings');
  $args[0]['title'] = !empty($args[0]['title']) ? $args[0]['title'] : '';
  $title = !empty($args[0]['meta_title']) ? $args[0]['meta_title'] : $args[0]['title'];
  MG::set('metaTitle', $title.' | '.$settings['sitename']);
}

mgAddAction('mg_seometa', 'seoMeta', 1);
