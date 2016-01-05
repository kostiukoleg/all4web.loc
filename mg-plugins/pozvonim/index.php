<?php

/*
  Plugin Name: pozvonim
  Description: Callback service Pozvonim.com plugin
  Author: Mazx
  Version: 1.0.0
 */

new BlankEntity;

class BlankEntity
{

    private static $lang = array(); // массив с переводом плагина
    private static $locale = 'ru';
    private static $pluginName = ''; // название плагина (соответствует названию папки)
    private static $path = ''; //путь до файлов плагина

    public function __construct()
    {

        mgActivateThisPlugin(__FILE__, array(__CLASS__, 'activate')); //Инициализация  метода выполняющегося при активации
        mgAddAction(__FILE__, array(__CLASS__, 'pageSettingsPlugin')); //Инициализация  метода выполняющегося при нажатии на кнопку настроект плагина
        mgAddShortcode('pozvonim', array(__CLASS__, 'handleShortCode')); // Инициализация шорткода [pozvonim] - доступен в любом HTML коде движка.

        self::$pluginName = PM::getFolderPlugin(__FILE__);
        self::$lang = PM::plugLocales(self::$pluginName);
        self::$locale = substr(MG::getSetting('languageLocale'), 0, 2);
        self::$path = PLUGIN_DIR . self::$pluginName;

        if (!URL::isSection('mg-admin')) { // подключаем CSS плагина для всех страниц, кроме админки
            mgAddMeta('<link rel="stylesheet" href="' . SITE . '/' . self::$path . '/css/style.css" type="text/css" />');
        }
    }

    /**
     * Метод выполняющийся при активации палагина
     */
    static function activate()
    {
        self::createDateBase();
    }

    /**
     * Метод выполняющийся перед генераццией страницы настроек плагина
     */
    static function preparePageSettings()
    {
        echo '
      <link rel="stylesheet" href="' . SITE . '/' . self::$path . '/css/style.css" type="text/css" />
      <script type="text/javascript">
        includeJS("' . SITE . '/' . self::$path . '/js/script.js");
      </script> 
    ';
    }

    /**
     * Создает таблицу плагина в БД
     */
    static function createDateBase()
    {

        // Если плагин впервые активирован, то задаются настройки по умолчанию
        if (!MG::getOption('pozvonimOption')) {

            $array = array(
                'email' => '',
                'phone' => '',
                'host'  => '',
            );

            MG::setOption(array('option' => 'pozvonimOption', 'value' => addslashes(serialize($array))));

        }
    }

    /**
     * Выводит страницу настроек плагина в админке
     */
    static function pageSettingsPlugin()
    {
        $lang = self::$lang;
        $locale = self::$locale;
        $pluginName = self::$pluginName;

        //получаем опцию pozvonimOption в переменную option
        $option = MG::getSetting('pozvonimOption');
        if (!$option) {
            $option = addslashes(serialize(array()));
        }
        $option = stripslashes($option);
        $options = unserialize($option);

        $result = DB::query("SELECT `option`, `value`  FROM `" . PREFIX . "setting`  ");
        $settings = array();

        while ($row = DB::fetchAssoc($result)) {
            $settings[$row['option']] = $row['value'];
        }
        if (!isset($settings['shopPhone'])) {
            $settings['shopPhone'] = '';
        } else {
            $settings['shopPhone'] = '+' . preg_replace('/[^0-9]+/', '', trim($settings['shopPhone']));
        }

        self::preparePageSettings();
        include('pageplugin.php');
    }

    /**
     * Обработчик шотркода вида [pozvonim]
     * выполняется когда при генерации страницы встречается [pozvonim]
     */
    static function handleShortCode()
    {

        if (!URL::isSection('mg-admin')) {
            $option = MG::getSetting('pozvonimOption');
        } else {
            $option = MG::getOption('pozvonimOption');
        }

        // преобразование строки опций в массив
        $option = stripslashes($option);
        $options = unserialize($option);
        if (isset($options['key']) && !empty($options['key'])) {
            return '<script crossorigin="anonymous" async type="text/javascript" src="//api.pozvonim.com/widget/callback/v3/' .
                   $options['key']
                   . '/connect" id="check-code-pozvonim" charset="UTF-8"></script>';
        }

        return '';
    }

}