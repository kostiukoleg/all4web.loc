<?php

/**
 * Интерфейс PluginManager - для класса PM.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Libraries
 */
interface PluginManager {

  /**
   * Регистрирует пользовательскую функцию в качетсве обработчика для события.
   * @param Hook $hook - объект содержащий информаццию о привязке 
   * пользовательской функции к событию, которое может произойти. 
   */
  static function registration(Hook $hook);

  /**
   * Удаляет обработчика.
   * @param Hook $hook - объект содержащий информаццию о привязке 
   * пользовательской функции к событию, которое может произойти. 
   */
  static function delete(Hook $hook);

  /**
   * Создает хук.
   * @param Hook $hook - объект содержащий информаццию о привязке 
   * пользовательской функции к событию, которое может произойти. 
   */
  static function createHook($hookName, $arg);
}
