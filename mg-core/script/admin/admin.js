var protocol = '';
var currency = '';
// массив подключенных скриптов, для избежания дублей
var javascripts = [];

// главный модуль админки, управляет всем остальным, включает в себя ряд полезных функций используемы повсеместно.
var admin = (function () {

  return {
    SITE: "null", // домен сайта
    SECTION: "null", // страница по умолчанию
    WAIT_PROCESS: false, // процесс загрузки
    WAIT_DELAY: 300, // количество милисекунд, которое должно пройти между запросом и получением ответа чтобы показать лоадер
    CURENT_PLUG_TITLE: null, // название плагина, страница настроек которого открыта
    CURRENCY: "", // валюта
    CURRENCY_ISO: "", // валюта магазина
    PROTOCOL: "", // домен сайта
    AJAXCALLBACK: null, // стек функций отложенного вызова посе аяксовой подгрузки данных
    PULIC_MODE: false, // становится true когда включен режим редактирования на сайте 
    searcharray: [], //массив найденых товаров в строке поиска
    DIR_FILEMANAGER: 'uploads',
    /**
     * Инициализация компонентов админки
     */
    init: function () {

      // Домен сайта
      this.SITE = mgBaseDir;
      // Домен сайта
      //if(!currency){currency = $.trim($("#currency").html());}
      this.CURRENCY = $.trim($("#currency").html());
      this.CURRENCY_ISO = $.trim($("#currency-iso").html());
      if (!this.CURRENCY) {
        this.CURRENCY = currency
      }
      ;

      this.PROTOCOL = $.trim($("#protocol").html());
      if (!this.PROTOCOL) {
        this.PROTOCOL = protocol
      }
      ;
      // блокируем стандарные горячие клавиши ctrl+s для сохранения модалок в админке
      document.onkeydown = this.ctrlKeyUpgrade;
      // обработчик нажатия на пункт "Товары"
      $('a[id=catalog]').click(function () {
        admin.SECTION = 'catalog';
        // admin.show("catalog.php","adminpage");
        includeJS(admin.SITE + '/mg-core/script/admin/catalog.js');

        admin.show("catalog.php", "adminpage", cookie(admin.SECTION + "_getparam"), catalog.callbackProduct);
      });

      // обработчик нажатия на пункт "Категории"
      $('a[id=category]').click(function () {
        admin.SECTION = 'category';
        //admin.show("category.php","adminpage");
        includeJS(admin.SITE + '/mg-core/script/admin/category.js');
        admin.show("category.php", "adminpage", cookie(admin.SECTION + "_getparam"), category.draggableCat);
        ;
      });

      // обработчик нажатия на пункт "Заказы"
      $('a[id=orders]').click(function () {
        admin.SECTION = 'orders';
        includeJS(admin.SITE + '/mg-core/script/admin/orders.js');
        admin.show("orders.php", "adminpage", cookie(admin.SECTION + "_getparam"), admin.sliderPrice);
      });

      // обработчик нажатия на пункт "Страницы"
      $('a[id=page]').click(function () {
        admin.SECTION = 'page';
        includeJS(admin.SITE + '/mg-core/script/admin/page.js');
        admin.show("page.php", "adminpage", cookie(admin.SECTION + "_getparam"), page.draggableCat);
      });

      // обработчик нажатия на пункт "Плагины"
      $('a[id=plugins]').click(function () {
        admin.SECTION = 'plugins';
        includeJS(admin.SITE + '/mg-core/script/admin/plugins.js');
        admin.show("plugins.php", "adminpage", cookie(admin.SECTION + "_getparam"), plugin.createSwitch);
        // инициализациямодуля при подключении

      });

      // обработчик нажатия на пункт "Настройки"
      $('a[id=settings]').click(function () {
        admin.SECTION = 'settings';
        admin.show("settings.php", "adminpage", cookie(admin.SECTION + "_getparam"), settings.calbackOpenTab);
        includeJS(admin.SITE + '/mg-core/script/admin/settings.js');
      });

      // обработчик нажатия на пункт "Статистика"
      $('a[id=statistics]').click(function () {
        admin.SECTION = 'statistics';
        includeJS(admin.SITE + '/mg-core/script/admin/statistics.js');
        admin.show("statistics.php", "adminpage", cookie(admin.SECTION + "_getparam"), statistics.startFunction);
      });

      // обработчик нажатия на пункт "Пользователи"
      $('a[id=users]').click(function () {
        admin.SECTION = 'users';
        admin.show("users.php", "adminpage");
        includeJS(admin.SITE + '/mg-core/script/admin/users.js');
      });

      // обработчик для нажатий на навигацию страниц
      // вешается на тело страницы, и отрабатывает с любого раздела
      $('.admin-center').on('click', '.linkPage', function () {
        var pageId = admin.getIdByPrefixClass($(this), 'page');
        // открываем выбранную страницу, текущего раздела
        //alert(admin.SECTION+" "+cookie("type"));

        // если пагинация нажата внутри плагина, то указываем тип и не прибавляем расширение .php к названию плагина
        if (cookie("type") == 'plugin') {
          var tab = '';
           // если на странице присутствуют фильтры учитываем их в пагинации
          if (cookie("section")) {
            var pluginFilter = cookie("section").split('&');         
            if (pluginFilter.length > 1 && pluginFilter[0] == admin.SECTION) {          
              tab = pluginFilter[1];          
            }
          }
          var request = $(tab+" form[name=filter]").formSerialize();
          var getparam = "page=" + pageId + "&" + request;
          var displayFilter = '';
          if ($(".filter-container").css('display') == 'block') {
            displayFilter = '&displayFilter=1';
          };
          cookie(admin.SECTION + "_getparam", getparam);
          admin.show(admin.SECTION, cookie("type"), "&pluginTitle=" + admin.CURENT_PLUG_TITLE + "&" + getparam + displayFilter, null, tab);
        }
        else {
          // если на странице присутствуют фильтры учитываем их в пагинации
          var request = $("form[name=filter]").formSerialize();
          var getparam = "page=" + pageId + "&" + request;
          var displayFilter = '';
          if ($(".filter-container").css('display') == 'block') {
            displayFilter = '&displayFilter=1';
          }
          ;
          cookie(admin.SECTION + "_getparam", getparam);
          admin.show(admin.SECTION + ".php", cookie("type"), getparam + displayFilter, catalog.callbackProduct);
        }
      });

      $('body').on('click', '.logout-button', function () {
        window.location = admin.SITE + "/enter?logout=1";
      });
      // Восстанавливаем  последний открытый раздел
      this.refreshPanel();


      // при смене значения чекбокса записываем в value, чтобы потом получать данные (актуально для всех чекбоксов на странице в админке) 
      $('body').on('click', 'input[type="checkbox"]', function () {
        if ($(this).hasClass('mg-filter-prop-checkbox')) {
          return true;
        }
        // если чекбокс находится в админкt то применяем к нему наше правило
        if (!admin.PULIC_MODE) {
          $(this).val($(this).prop('checked'));
        }

        // если чекбокс находится в паблике и в сплвающем окне админки (быстрое редактирование) 
        if (admin.PULIC_MODE && $(this).parents('.b-modal').length) {
          $(this).val($(this).prop('checked'));
        }

        // если чекбокс находится просто в паблике то не применяем к нему данного действия
      });

      // клик по панеле информера, и переход на страницу плагина или раздела
      $('body').on('click', '.info-panel .button-list a', function () {

        // если указан раздел то переходим в раздел
        if ($(this).attr('class') == 'notPlugin') {
          $('a[id=' + $(this).attr('rel') + ']').click();
          return false;
        }

        // если указан плагин то переходим в плагин
        if ($('.plugins-dropdown-menu a[class=' + $(this).attr('rel') + ']').length == 0) {
          $('a[id=plugins]').click();
        }
        $('.plugins-dropdown-menu a[class=' + $(this).attr('rel') + ']').click();
      });

      // клик по сообщению о доступной новой версии
      $('body').on('click', '#newVersion, #fakeKey', function () {
        $('a[id=settings]').click();
        // после перехода выполняем два отложенных метода
        admin.AJAXCALLBACK = [
          {callback: 'settings.closeAllTab', param: null},
          {callback: 'settings.openTab', param: ['tab-system']},
        ];
      });

      // автотранслит заголовка в URL. При клике, или табе, на поле URL, если оно пустое то будет автозаполнено транслитироированным заголовком
      $('body').on('click, focus', 'input[name=url]', function () {
        if ($('input[name=url]').val() == '') {
          var text = $('input[name=title]').val();
          if (text) {
            text.replace('%', '-');
            text = admin.urlLit(text, 1);
            $(this).val(text);
          }
        }
      });

      // автоподстчет количества символов
      $('body').on('blur keyup', 'textarea[name=meta_desc]', function () {
        $('.symbol-count').text($(this).val().length);
      });

      // защита для ввода в числовое поле символов
      $('body').on('keyup', '.numericProtection', function () {
        if (isNaN($(this).val()) || $(this).val() <= 0) {
          $(this).val('1');
        }
      });

      // для количественного поля в админке будем прописывать знак бесконечности при пустом значении или минусовом
      $('body').on('blur', '.product-text-inputs input[name=count]', function () {
        if ($(this).val() < 0 || $(this).val() == "") {
          $(this).val('∞');
        }
      });

      // обработчик закрытия окна
      $('body').on('click', '.b-modal_close', function () {
        admin.closeModal($(this).closest('.b-modal'));
      });

      // обработчик закрытия окна uploader
      $('body').on('click', '.uploader-modal_close', function () {
        admin.closeModal($(this).closest('.uploader-modal'));
      });




      // при смене значения чекбокса записываем в value, чтобы потом получать данные

      $('body').on('click', '.seo-title', function () {
        $(this).toggleClass('opened').toggleClass('closed');
        $('.seo-wrapper').slideToggle(300);
        if ($(this).hasClass('opened')) {
          $(this).html(lang.HIDE_SEO_BLOCK);
        }
        else {
          $(this).html(lang.SEO_BLOCK);
        }
      });


      // обработка клика по переключателю режима редактирования
      $('body').on('click', '.site-edit', function () {

        $(this).toggleClass('enabled');
        var enabled = false;

        if ($(this).hasClass('enabled')) {
          enabled = true;
        }

        admin.ajaxRequest({
          mguniqueurl: "action/setSiteEdit",
          enabled: enabled
        },
        function (response) {
          location.reload();
        }
        );

      });

      // обработка клика по кнопки - сбросить кэш
      $('body').on('click', '.clear-cache', function () {
        admin.ajaxRequest({
          mguniqueurl: "action/clearСache",
        },
          function (response) {
            location.reload();
          }
        );

      });
      
      // обработка клика по кнопки - создать images для css 
      $('body').on('click', '.create-images-for-css-cache', function () {
        admin.ajaxRequest({
          mguniqueurl: "action/clearImageCssСache",
        },
          function (response) {
            admin.indication('success', 'Изображения успешно созданы!');
            $('.group-property .warning-create-images').hide();
          }
        );

      });
      
      

      // Применение сортировки таблицы (обязательно должна присутствовать форма фильтров)
      $('.admin-center').on('click', '.field-sorter', function () {
        if ($('.filter-container input[name=sorter]').length) {
          $('.filter-container input[name=sorter]').val($(this).data('field') + '|' + $(this).data('sort'));
        } else {
          var val = $(this).data('field') + '|' + $(this).data('sort');
          $('.filter-container select[name=sorter]').append('<option value="' + val + '">' + val + '</option>').val(val);
        }
        var request = $("form[name=filter]").formSerialize();
        var type = cookie("type");
        if (type == "adminpage") {
          admin.show(admin.SECTION + ".php", "adminpage", request, admin.sliderPrice);
        } else {
          admin.show(admin.SECTION, type, request, admin.sliderPrice);
        }
        return false;
      });

      admin.hideWhiteArrowDown();

      //Кастомный скрипт для фиксации меню при скроле
      var $menu = $(".admin-top-menu");

      // обработчик скрола страницы и перемещения меню
      $(window).scroll(function () {
        if ($(this).scrollTop() > 70 && $menu.hasClass("default")) {
          $('.info-panel').css('height', '118px');
          $menu.removeClass("default").addClass("fixed");
        } else if ($(this).scrollTop() <= 70 && $menu.hasClass("fixed")) {
          $('.info-panel').css('height', '70px');
          $menu.removeClass("fixed").addClass("default");
        }
      });

      admin.fixedMenu($('#staticMenu').text());

      // вешаем класс на выбранный пункт
      $('.admin-top-menu-list > li > a').click(function () {
        $('.admin-top-menu-list > li > a').removeClass('active-item');
        $(this).addClass('active-item');
      });

    },
    /**
     * фиксация меню при скроле
     */
    fixedMenu: function (staticMenu) {
      if (staticMenu != 'false') {
        $('.admin-top-menu').addClass('default');
      } else {
        $('.admin-top-menu').removeClass("default").removeClass("fixed");
      }
    },
    /**
     * При обновлении страницы, восстанавливает открытый раздел
     */
    refreshPanel: function () {

      // открыть последний активный раздел, считывается с куков
      if (cookie("section")) {
        var sections = cookie("section") ? cookie("section").split('&') : cookie("section") ;
        this.SECTION = sections[0];        
      }             
      this.SECTION =  cookie("section") ;

      // если куки пусты то открывается первый раздел - статистика
      if (!this.SECTION) {
        this.SECTION = "orders";
      }

      // особый выбор, когда необходимо в разделе открыть еще подраздел, как в плагинах и настройках
      var paramChoose = false;
      if (cookie("type") == 'plugin') {
        $('.plugins-dropdown-menu a[class=' + this.SECTION + ']').click();
        paramChoose = true;
      }

      //приоткрытии вкладки настроек необходимо сразу подключить скрипт, чтобы открыть таб
      if (this.SECTION == 'settings') {
        includeJS(admin.SITE + '/mg-core/script/admin/settings.js');
      }

      if (!paramChoose) {
        //debugger;
        $('a[id=' + this.SECTION + ']').click();
      }

      // делаем пункт выбранным
      $('a[id=' + this.SECTION + ']').addClass('active-item');

      admin.initToolTip();
    },
    /**
     * Индикатор сообщений
     * Функция выводит информацию об успешности или ошибки 
     * различных действия администратора в админке.
     */
    indication: function (status, text) {

      $('.message-error').remove();
      $('.message-succes').remove();
      var object = "";
      switch (status) {
        case 'success':
        {
          $('body').append('<div class="message-succes"></div>');
          object = $('.message-succes');
          break;
        }
        case 'error':
        {
          $('body').append('<div class="message-error"></div>');
          object = $('.message-error');
          break;
        }
        default:
        {
          $('body').append('<div class="message-error"></div>');
          object = $('.message-error');
          break;
        }
      }

      object.slideDown("fast");
      object.html(text);
      setTimeout(function () {
        object.remove();
      }, 3000);
    },
    /**
     * Обертка для всех аякс запросов админки
     * необходимо для оптимизации вывода процесса загрузки
     * и унификации всех аякс вызовов
     */
    ajaxRequest: function (data, callBack, loader, dataType, noAlign) {

      if (!dataType)
        dataType = 'json';
      if (!loader)
        loader = $('.mailLoader');

      $.ajax({
        type: "POST",
        url: "ajax",
        data: data,
        cache: false,
        dataType: dataType,
        success: callBack,
        beforeSend: function () {
          // флаг, говорит о том что начался процесс загрузки с сервера
          admin.WAIT_PROCESS = true;
          loader.hide();
          loader.before('<div class="view-action" style="display:none; margin-top:-2px;">' + lang.LOADING + '</div>');
          // через 300 msec отобразится лоадер.
          // Задержка нужна для того чтобы не мерцать лоадером на быстрых серверах.
          setTimeout(function () {
            if (admin.WAIT_PROCESS) {
              admin.waiting(true);
            }
          }, admin.WAIT_DELAY);
        },
        complete: function () {
          
          // завершился процесс
          admin.WAIT_PROCESS = false;
          //прячим лоадер если он успел появиться
          admin.waiting(false);
          loader.show();
          $('.view-action').remove();

          if ($('.b-modal').length > 0 && !noAlign) {
            admin.centerPosition($('.b-modal'));
          }

          // выполнение стека отложенных функций после AJAX вызова    
          if (admin.AJAXCALLBACK) {
            //debugger;
            admin.AJAXCALLBACK.forEach(function (element, index, arr) {
              eval(element.callback).apply(this, element.param);
            });
            admin.AJAXCALLBACK = null;
          }
        },
        error: function (request, status, error) {
          var errorText = request.responseText;     
          var noneReport = '';
          if(errorText==''){
            errorText = 'Причиной данного сообщения могло послужить завершение сеанса работы пользователя.\n\
              Попробуйте авторизоваться заново. Если ошибка продолжает происходить, проверьте содержание файла с логом ошибок сервера, для выявления причины. Или включите показ ошибок в файле index.php заменив строку Error_Reporting(0); на Error_Reporting(1);';
            noneReport = 'style="display:none;"';
            lang.SORRY_ERROR = "Произошла непредвиденная ошибка. Пожалуйста, убедитесь что ваш web-сервер соответствует необходимым требованиям и все PHP модули подключены. Все файлы сайта и шаблона должны иметь кодировку UTF-8 без BOM. <br> Необходимые для корректной работы движка модули:  mysqli, json, curl, php_zip, gd, xmlwriter, xmlreader";
          }
          
          var errorBox = "<div class='error-box'><a href='javascript:void(0)' class='close-notification' onclick='$(\".error-box\").remove()'></a><div class='sorry-error'>" + lang.SORRY_ERROR + " <br/> Support email: <a href='support@moguta.ru'>support@moguta.ru</a>  </div><div class='text-error'></div><a href='javascript:void(0);' "+noneReport+" class='custom-btn send-report-btn' onClick='admin.downimg()' ><span>Отправить отчет об ошибке</span></a><div class='clear'></div></div>";
          $('.error-box').remove();
          $('body').append(errorBox);
          admin.centerPosition($('.error-box'));
          $('.error-box .text-error').html(errorText);
        }
      });

    },
    downimg: function () {
      includeJS(mgBaseDir + '/mg-core/script/html2canvas.js');
      html2canvas($('body'), {
        onrendered: function (canvas) {
          // var img = canvas.toDataURL('image/png').replace("image/png", "image/octet-stream");
          var img = canvas.toDataURL('image/png');
          admin.ajaxRequest({
            mguniqueurl: "action/sendBugReport",
            screen: img,
            text: $('.error-box .text-error').text()
          },
          function (response) {
            $('.error-box').remove();
            alert('Отчет отправлен. Спасибо за помощь в развитии проекта!');
            window.location.reload();

          });

        }
      });
    },
    /**
     * Открывает выбранный раздел админки
     * url - задает контролер, который будет обрабатывать запрос
     * type - тип (adminpage|plugin) разделяющий запросы по логике обработки движком. Запросы со страниц плагинов обрабатываются иначе.
     * request - сериализованные данные с форм использующихся в плагинах
     * tab - класс открытой вкладки, если вызов из плагина в котором две таблицы с фильтрами
     */
    show: function (url, type, request, callback, tab) {

      // Устанавливаем в куки название открываемого раздела
      var sect = url.split('.');
      if (tab) {
        sect[0] += '&'+tab; 
      }
      cookie("section", sect[0]);
      cookie("type", type);
      cookie(admin.SECTION + "_getparam", request);
      // подготвка параметров для отправки
      var data = "mguniqueurl=" + url + "&mguniquetype=" + type + "&" + request;

      admin.ajaxRequest(
        data,
        function (data) {
          //debugger;
          //вывод полученной верстки страниы 
          $(".admin-center .data").html(data);

          //выполнение отложенной функции после открытия страницы
          if (callback) {
            callback.call();
          }

          admin.initCustom();

          if ('plugin' == type) {
            admin.SECTION = url;

            //Добавляем для пользовательских форм (они же формы плагинов) отличительные атрибуты
            $("form").each(function () {

              // если у формы плагина стоит атрибут noengine = true,
              // то такая форма не будет обработана движком, 
              // а произведет обычную отправку данных (необходимо для плагинов)
              if (!$(this).attr('noengine')) {
                $(this).attr("plugin", url);
                $(this).attr("ajaxForm", 'true');
              }

            });

            // сбор данных с формы и сериализация их в строку
            $("form[ajaxForm=true]").submit(function () {
              var request = $(this).formSerialize();
              admin.show(url, type, request, null, tab);
              return false;
            });
          }
        },
        false,
        "html"
        );

    },
    /**
     * Чистит куки хранящие в себе гет параметры для раздела
     */
    clearGetParam: function (run) {
      cookie(admin.SECTION + "_getparam", null);
    },
    /**
     * Если спустя секунду данные не были получены, то выводим лоадер.
     */
    waiting: function (run) {
      var cont = $(".view-action");
      run ? cont.show() : cont.hide();
    },
    // Включение подсказок и добавление перетягивания  всплывающих окон
    initCustom: function () {
      admin.initToolTip();
      admin.initDraggable();
    },
    /**
     * инициализация всплывающих подсказок, для всех  дом элементов,
     * у которых есть класс tool-tip-bottom, tool-tip-top, tool-tip-right, tool-tip-left 
     */
    initToolTip: function () {
      $('#tiptip_holder').hide();
      $(".tool-tip-bottom").unbind('tipTip');
      $(".tool-tip-top").unbind('tipTip');
      $(".tool-tip-right").unbind('tipTip');
      $(".tool-tip-left").unbind('tipTip');

      //Вызов всплывающих подсказок
      $(".tool-tip-bottom").tipTip({maxWidth: "auto", defaultPosition: "bottom", edgeOffset: 6});
      $(".tool-tip-top").tipTip({maxWidth: "auto", defaultPosition: "top", edgeOffset: 6});
      $(".tool-tip-right").tipTip({maxWidth: "auto", defaultPosition: "right", edgeOffset: 6});
      $(".tool-tip-left").tipTip({maxWidth: "auto", defaultPosition: "left", edgeOffset: 6});
    },
    /**
     * Инициализация тягабельности   
     */
    initDraggable: function () {
      $(".b-modal, .uploader-modal").draggable({handle: ".widget-table-title"});
    },
    /**
     *  @deprecated
     *  Получает id по префиксу класса.
     *  Наприемр есть такой элемент:
     *  <a class='linkPage navButton page_501 link_2' href='#' >
     *  Задача: получить число 501 (идентификатор страницы)
     *  Вызова данной функции getIdByPrefixClass('.linkPage', 'page')
     *  вернет число 501  
     */
    getIdByPrefixClass: function (obj, prefix) {
      var result = null;
      var classList = obj.attr('class').split(/\s+/);
      var reg = new RegExp(prefix + '_(.*)');
      $.each(classList, function (index, item) {
        var id = item.match(reg);
        if (id !== null)
          result = id[1];
      });
      return result;
    },
    /**
     * Скрывает белую стрелку в пункте плагинов, если нет активных плагинов с настройками
     */
    hideWhiteArrowDown: function (obj, prefix) {
      if ($('.plugins-dropdown-menu li').length == 1) {
        $('.plugins-icon').parents('li').find('.white-arrow-down').hide();
        $(".plugins-menu-wrapper").hide();
      }
      ;
    },
    /**
     * Транслитирирует строку
     */
    urlLit: function (string, lower) {
      var dictionary = {'А': 'a', 'Б': 'b', 'В': 'v', 'Г': 'g', 'Д': 'd', 'Е': 'e', 'Ё': 'yo', 'Ж': 'j', 'З': 'z', 'И': 'i', 'Й': 'y', 'К': 'k', 'Л': 'l', 'М': 'm', 'Н': 'n', 'О': 'o', 'П': 'p', 'Р': 'r', 'С': 's', 'Т': 't', 'У': 'u', 'Ф': 'f', 'Х': 'h', 'Ц': 'ts', 'Ч': 'ch', 'Ш': 'sh', 'Щ': 'sch', 'Ъ': '', 'Ы': 'y', 'Ь': '', 'Э': 'e', 'Ю': 'yu', 'Я': 'ya', 'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'j', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch', 'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya', '1': '1', '2': '2', '3': '3', '4': '4', '5': '5', '6': '6', '7': '7', '8': '8', '9': '9', '0': '0', 'І': 'i', 'Ї': 'i', 'Є': 'e', 'Ґ': 'g', 'і': 'i', 'ї': 'i', 'є': 'e', 'ґ': 'g'};
      // старый вариант
      //var dictionary = {'а':'a', 'б':'b', 'в':'v', 'г':'g', 'д':'d', 'е':'e', 'ж':'g', 'з':'z', 'и':'i', 'й':'y', 'к':'k', 'л':'l', 'м':'m', 'н':'n', 'о':'o', 'п':'p', 'р':'r', 'с':'s', 'т':'t', 'у':'u', 'ф':'f', 'ы':'i', 'э':'e', 'А':'A', 'Б':'B', 'В':'V', 'Г':'G', 'Д':'D', 'Е':'E', 'Ж':'G', 'З':'Z', 'И':'I', 'Й':'Y', 'К':'K', 'Л':'L', 'М':'M', 'Н':'N', 'О':'O', 'П':'P', 'Р':'R', 'С':'S', 'Т':'T', 'У':'U', 'Ф':'F', 'Ы':'I', 'Э':'E', 'ё':'yo', 'х':'h', 'ц':'ts', 'ч':'ch', 'ш':'sh', 'щ':'shch', 'ъ':'', 'ь':'', 'ю':'yu', 'я':'ya', 'Ё':'YO', 'Х':'H', 'Ц':'TS', 'Ч':'CH', 'Ш':'SH', 'Щ':'SHCH', 'Ъ':'', 'Ь':'',	'Ю':'YU', 'Я':'YA','і':'i', 'ї':'i', 'є':'e', 'ґ':'g', 'І':'i', 'Ї':'i', 'Є':'e', 'Ґ':'g' };
      var result = string.replace(/[\s\S]/g, function (x) {
        if (dictionary.hasOwnProperty(x))
          return dictionary[ x ];
        return x;
      });
      result = result.replace(/\W/g, '-').replace(/[-]{2,}/gim, '-').replace(/^\-+/g, '').replace(/\-+$/g, '');
      if (lower) {
        result = result.toLowerCase();
      }
      return  result;
    },
    /*
     * альтернатива htmlspecialchars
     */
    htmlspecialchars: function (text) {
      if (text) {
        return text
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;");
      }
      return text;
    },
    /**
     * альтернатива htmlspecialchars_decode
     */
    htmlspecialchars_decode: function (text) {
      if (text) {
        return text
          .replace(/&amp;/g, "&")
          .replace(/&lt;/g, "<")
          .replace(/&gt;/g, ">")
          .replace(/&quot;/g, "\"")
          .replace(/&#039;/g, "\'");
      }
      return text;
    },
    /**
     * Позиционированирует элемент по центру окна браузера
     */
    centerPosition: function (object) {
      object.css('position', 'absolute');
      var top = ($(window).height() - object.height()) / 2;
      if (top < 0) {
        top = 20;
      }
      object.css('left', ($(document).width() - object.width()) / 2 + 'px');
      object.css('top', top + (document.body.scrollTop || document.documentElement.scrollTop) + 'px');
      // object.css('top', '100px');
      // alert($(window).height());
      // object.find('.widget-table-body').css('max-height',($(window).height()-200)+'px');
      //  object.find('.widget-table-body').css('overflow','auto');   // добавляет в позиционируемый лемент скрол, который появится если элемент будет не помещаться на экран.

    },
    /**
     * Открывает модальное окно
     */
    openModal: function (object) {
      admin.overlay();
      object.fadeIn(300);
      //object.css('z-index', 2);
      object.css({'position': 'fixed'});
      admin.centerPosition(object);
      $('.admin-footer-block').css({'position': 'fixed', 'width': '100%', 'bottom': '0'});
    },
    /**
     * Закрывает модальное окно
     */
    closeModal: function (object) {
      object.fadeOut(300);
      $("#overlay").eq($("#overlay").length - 1).remove();
      $('.admin-footer-block').css({'position': 'static', 'width': 'auto', 'bottom': '0'});
    },
    /**
     * Фон для заднего плана при открытии всплывающего окна
     */
    overlay: function () {
      var docHeight = $(document).height();
      $("body").append("<div id='overlay' class='no-print'></div>");
      $("#overlay").height(docHeight);
    },
    /**
     * Шаблоны регулярных выражений для проверки ввода в поля
     * admin.regTest(4,'текст')
     */
    regTest: function (regId, text) {
      switch (regId) {
        case 1:
        {
          return /^[-0-9a-zA-Zа-яА-ЯёЁїЇєЄґҐ&`'іІ«»()$%\s_\"\.,!?:]+$/.test(text);
          break;
        }
        case 2:
        {
          return /^[-0-9a-zA-Zа-яА-ЯёЁїЇєЄґҐ&`'іІ«»()$%\s_]+$/.test(text);
          break;
        }
        case 3:
        {
          return /^[,\s]+$/.test(text);
          break;
        }
        case 4:
        {
          return /["']/.test(text);
          break;
        }
      }
    },
    /**
     * отсечение символа по краям строки
     */
    trim: function (s, simb) {
      if (!simb) {
        s = s.replace(/\s+$/g, '');
        s = s.replace(/^\s+/g, '');
      } else {
        s = s.replace(eval("/^\\" + simb + "+/g"), '');
        s = s.replace(eval("/\\" + simb + "+$/g"), '');
      }
      return s;
   },
    /**
     * выводит ползунок цены для фильтров цены в "заказах" и "товарах"
     */
    sliderPrice: function () {
      $("#price-slider").slider({
        min: $("input#minCost").data("fact-min"),
        max: $("input#maxCost").data("fact-max"),
        values: [$("input#minCost").val(), $("input#maxCost").val()],
        step: 100,
        range: true,
        stop: function (event, ui) {
          $("input#minCost").val($("#price-slider").slider("values", 0));
          $("input#maxCost").val($("#price-slider").slider("values", 1));
        },
        slide: function (event, ui) {
          $("input#minCost").val($("#price-slider").slider("values", 0));
          $("input#maxCost").val($("#price-slider").slider("values", 1));
        }
      });

      $("input#minCost").change(function () {
        var value1 = $("input#minCost").val();
        var value2 = $("input#maxCost").val();

        if (parseInt(value1) > parseInt(value2)) {
          value1 = value2;
          $("input#minCost").val(value1);
        }
        $("#price-slider").slider("values", 0, value1);
      });

      $("input#maxCost").change(function () {
        var value1 = $("input#minCost").val();
        var value2 = $("input#maxCost").val();

        if (parseInt(value1) > parseInt(value2)) {
          value2 = value1;
          $("input#maxCost").val(value2);
        }
        $("#price-slider").slider("values", 1, value2);
      });
    },
    /**
     * разрешает менять местами строки таблицы, для сортировки элементов
     * tableSelector - селектор объекта , таблица в которой  доступна сортировка
     * tablename - название таблицы в базе данных (обязательно должна иметь поля id и sort)
     * у строк таблицы обязательно должен быть атрибут data-id
     * @returns {undefined}
     */
    sortable: function (tableSelector, tablename) {

      // исправляет баг с ломающейся строкой таблицы
      var fixHelper = function (e, ui) {
        ui.children().each(function () {
          $(this).width($(this).width());
        });
        return ui;
      };

      // создает массив позиций с маркером
      function createArray(ui, marker) {
        var strItems = [];
        $(tableSelector).children().each(function (i) {
          var tr = $(this);
          if (tr.data("id") == ui.item.data("id")) {
            strItems.push(marker);
          } else {
            if (tr.data("id") != undefined) {
              strItems.push(tr.data("id"));
            }
          }

        });
        return strItems;
      }

      var listIdStart = [];
      var listIdEnd = [];


      if ($(tableSelector).hasClass('ui-sortable')) {
        $(tableSelector).sortable('destroy');
        $(tableSelector).unbind();
      }

      $(tableSelector).sortable({
        helper: fixHelper,
        start: function (event, ui) {
          listIdStart = createArray(ui, 'start');
        },
        update: function (event, ui) {
          listIdEnd = createArray(ui, 'end');

          var $thisId = ui.item.data("id");
          var sequence = getSequenceSort(listIdStart, listIdEnd, $thisId);

          if (sequence.length > 0) {
            sequence = sequence.join();
            admin.ajaxRequest({
              mguniqueurl: "action/changeSortRow",
              switchId: $thisId,
              sequence: sequence,
              tablename: tablename,
            },
              function (response) {
                admin.indication(response.status, response.msg)
              }
            );
          }
        }
      });



      /**
       * Вычисляет последовательность замены порядковых индексов 
       * Получает  дла массива
       * ["1", "start", "9", "2", "10"]
       * ["1", "9", "2", "end", "10"]
       * и ID перемещенной категории
       */
      function getSequenceSort(arr1, arr2, id) {
        var startPos = '';
        var endPos = '';

        // вычисляем стартовую позицию элемента
        arr1.forEach(function (element, index, array) {
          if (element == "start") {
            startPos = index;
            arr1[index] = id;
            return false;
          }
        });

        // вычисляем конечную позицию элемента      
        arr2.forEach(function (element, index, array) {
          if (element == "end") {
            endPos = index;
            arr2[index] = id;
            return false;
          }
        });

        // вычисляем индексы категорий с которым и надо поменяться пместами     
        var result = [];

        // направление переноса, сверху вниз
        if (endPos > startPos) {
          arr1.forEach(function (element, index, array) {
            if (index > startPos && index <= endPos) {
              result.push(element);
            }
          });
        }

        // направление переноса, снизу вверх
        if (endPos < startPos) {
          arr2.forEach(function (element, index, array) {
            if (index > endPos && index <= startPos) {
              result.unshift(element);
            }
          });
        }

        return result;
      }
      ;
    },
    /**
     * Сохраняет html контент из inline редактора в разделе товаров
     * @param string table - название таблицы в которую пойдет запись
     * @param string field - название поля в таблице для перезаписи
     * @param int id - идентификатор записи для обновления
     * @param string content
     * @returns {undefined}
     */
    fastSaveField: function (table, field, id, content) {
      // отправка данных на сервер для сохранеиня
      admin.ajaxRequest({
        mguniqueurl: "action/fastSaveContent",
        table: table,
        id: id,
        field: field,
        content: content,
      },
        function (response) {
          admin.indication('success', lang.ACT_SAVE_PAGE);
        }
      );
    },
    /**
     * Для открытия модалки в публичной части вытаскивает 
     * только модалку с необходимого раздела админки   
     * @returns {undefined}
     */
    cloneModal: function () {
      $('body').append($('.b-modal'));
      $('.admin-center').remove();
    },
    /**
     * Показывает модальное окно файлового менеджера для загрузки файлов
     * @returns {undefined}
     */
    openUploader: function (callback, param, dir) {
      includeJS(mgBaseDir + '/mg-core/script/elfinder/js/elfinder.min.js');
      includeJS(mgBaseDir + '/mg-core/script/elfinder/js/i18n/elfinder.ru.js');
      includeJS(mgBaseDir + '/mg-core/script/admin/uploader.js');
      if (dir) {
        admin.DIR_FILEMANAGER = dir;
      }
      if (admin.DIR_FILEMANAGER == 'template') {
        uploader.open();
      }
      if (admin.DIR_FILEMANAGER == 'uploads') {
        uploader.open(callback, param);
      }
      admin.DIR_FILEMANAGER = 'uploads';
    },
    numberFormat: function (str) {
      return admin.number_format(str, 2, ',', ' ');
    },
    // форматирует строку в соответствии с форматом
    number_format: function (number, decimals, dec_point, thousands_sep) {	// Format a number with grouped thousands

      var i, j, kw, kd, km;

      if (isNaN(decimals = Math.abs(decimals))) {
        decimals = 2;
      }
      if (dec_point == undefined) {
        dec_point = ",";
      }
      if (thousands_sep == undefined) {
        thousands_sep = ".";
      }

      i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

      if ((j = i.length) > 3) {
        j = j % 3;
      } else {
        j = 0;
      }

      km = (j ? i.substr(0, j) + thousands_sep : "");
      kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);

      kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");


      return km + kw + kd;
    },
    
    numberDeFormat: function (str) {
      return admin.number_de_format(str);
    },
    // Отменяет форматирование цены, и приводит к числу
    number_de_format: function (str) {	// Format a number with grouped thousands

      result = str;

      cent = false;
      thousand = false;

      existpoint = str.lastIndexOf('.');
      existcomma = str.lastIndexOf(',');

      // 1,320.50
      if (existpoint > 0 && existcomma > 0) {
        result = str.replace(/,/g, '.'); 
        firstpoint = result.indexOf('.');
        lastpoint = result.lastIndexOf('.');

        if (firstpoint != lastpoint) {
          str1 = result.substr(0, lastpoint);
          str2 = result.substr(lastpoint);
          str1 = str1.replace(/\./g, '');
          result = str1+str2;
        }

        return result;
      }

      // 1,234 или 1 234,56
      if (existpoint < 0 && existcomma > 0) {
        //определяем, что отделяется запятой, тысячи или копейки 
        str2 = str.substr(existcomma);
        if (str2.length - 1 == 2) {
          cent = true;
        } else {
          thousand = true;
        }
      }

      if (thousand) {
        result = str.replace(/,/g, '');
      }

      if (cent) {
        result = str.replace(/,/g, '.');
        firstpoint = result.indexOf('.');
        lastpoint = result.lastIndexOf('.');
        if (firstpoint != lastpoint) {
          str1 = result.substr(0, lastpoint);
          str2 = result.substr(lastpoint);
          str1 = str1.replace('.', '');
          result = str1+str2;
        }
      }

      result = result.replace(/ /g, '');

      return result;
    },
    // Выводит выпадающий список продуктов по заданному запросу
    searchProduct: function (text, fastResult) {
      if (text.length >= 2) {
        admin.ajaxRequest({
          mguniqueurl: "action/getSearchData",
          search: text
        },
        function (response) {
          admin.searcharray = [];
          var html = '<ul class="fast-result-list">';
          var currency = response.currency;
          var mgBaseDir = $('#thisHostName').text();

          function buildElements(element, index, array) {
            admin.searcharray.push(element);
            html +=
              '<li><a href="javascript:void(0)" data-element-index="' +
              index + '" data-id="' + element.id + '" data-code="' +
              element.code + '" data-price="' + element.price + '"> \n\
                <div class="fast-result-img">' +
              '<img src="' + element.image_url
              + '" ' + 'alt="' + element.title + '"/>' +
              '</div><div class="fast-result-info"><div class="search-prod-name">'
              + element.title +
              '</div> <span class="product-code">' + element.code +
              '</span><span class="product-price">' + element.price + ' ' + currency +
              '</span></div></a></li>';
          }

          if ('success' == response.status && response.item.items.catalogItems.length > 0) {
            response.item.items.catalogItems.forEach(buildElements);
            html += '</ul>';
            $(fastResult).html(html);
            $(fastResult).show();
          } else {
            $(fastResult).hide();
          }
        },
          false,
          "json",
          true
          );
      } else {
        $('.fastResult').hide();
      }
    },
    // быстрое сохранение всплывающих окон
    ctrlKeyUpgrade: function(e) {
      var forbiddenKeys = new Array('s');
      //var forbiddenKeys = new Array('s','x','c'); пример запрета на использование клавишей в сочетании с ctrl
      var key;
      var isCtrl;
      if (window.event) {
        key = window.event.keyCode;
        if (window.event.ctrlKey)
          isCtrl = true;
        else
          isCtrl = false;
      } else {
        key = e.which;
        if (e.ctrlKey)
          isCtrl = true;
        else
          isCtrl = false;
      }
      if (isCtrl) {
        for (i = 0; i < forbiddenKeys.length; i++) { 
          if (forbiddenKeys[i].toLowerCase() == String.fromCharCode(key).toLowerCase()) {
            if ($('.b-modal').length > 0) {
              $('.b-modal').find('.save-button').click();
            }
            return false;
          }
        }
      }
      return true;
    },

    /**
     * Метод для редактирования контента в публичной части для администратора
     */
    publicAdmin: function () {
      
     
      
      admin.PULIC_MODE = true;

      if ($(".admin-top-menu").length > 0) {
        $("body").addClass("admin-on-site");
      }
      else {
        $("body").removeClass("admin-on-site");
      }

      // клик по элементу открывающему модалку
      $('body').on('click', '.modalOpen', function (e) {
        e.preventDefault();
        $('.b-modal').remove();

        $('body').append('<div class="admin-center" ><div class="data"></div></div>');
        includeJS(admin.SITE + '/mg-core/script/admin/' + $(this).data('section') + '.js');

        // перечень функций выполняемых после  получения ответа от сервера 
        // (вырезаем только модалку из полученного контента, и открываем ее с нужными параметрами)
        admin.AJAXCALLBACK = [
          {callback: 'admin.cloneModal', param: null},
          {callback: $(this).data('section') + '.openModalWindow', param: eval($(this).data('param'))}
        ];

        // открываем раздел из которого вызовем модалку 
        admin.show($(this).data('section') + ".php", "adminpage");
      });


      // контекстное меню при наведении на элемент в публичной части
      $('.exist-admin-context').hover(
        function () {

          $(this).find('.admin-context').show();
        },
        function () {
          $(this).find('.admin-context').hide();
        }
      );
      $(".exist-admin-context").parent().css({display: "block"});

 },
  };
})();

//функция для работы с куками
function cookie(name, value, options) {
  if (typeof value != 'undefined') {
    options = options || {};
    if (value === null) {
      value = '';
      options.expires = -1;
    }
    var expires = '';
    if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
      var date;
      if (typeof options.expires == 'number') {
        date = new Date();
        date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
      } else {
        date = options.expires;
      }
      expires = '; expires=' + date.toUTCString();
    }

    var path = options.path ? '; path=' + (options.path) : '';
    var domain = options.domain ? '; domain=' + (options.domain) : '';
    var secure = options.secure ? '; secure' : '';
    document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
  } else {
    var cookieValue = null;
    if (document.cookie && document.cookie != '') {
      var cookies = document.cookie.split(';');
      for (var i = 0; i < cookies.length; i++) {
        var cookie = jQuery.trim(cookies[i]);
        if (cookie.substring(0, name.length + 1) == (name + '=')) {
          cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
          break;
        }
      }
    }
    return cookieValue;
  }
}
;

/**
 * подключает javascript файл и выполняет его
 * заносит название файла в реестр подключенных,
 * дабы не дублировать
 */
function includeJS(path) {
  //alert('пробуем подключить'+path);
  for (var i = 0; i < javascripts.length; i++) {
    if (path == javascripts[i]) {
      // alert('JavaScript: ['+path+'] уже был подключен ранее!');
      return false;
    }
  }
  javascripts.push(path);
  $.ajax({
    url: path,
    dataType: "script", // при типе script JS сам инклюдится и воспроизводится без eval
    async: false
  });
}

// для конвертации кирилического домена, чтобы IE понимал ссылки
;
(function (u) {
  var I, e = typeof define == 'function' && typeof define.amd == 'object' && define.amd && define, J = typeof exports == 'object' && exports, q = typeof module == 'object' && module, h = typeof require == 'function' && require, o = 2147483647, p = 36, i = 1, H = 26, B = 38, b = 700, m = 72, G = 128, C = '-', E = /^xn--/, t = /[^ -~]/, l = /\x2E|\u3002|\uFF0E|\uFF61/g, s = {overflow: 'Overflow: input needs wider integers to process', 'not-basic': 'Illegal input >= 0x80 (not a basic code point)', 'invalid-input': 'Invalid input'}, v = p - i, g = Math.floor, j = String.fromCharCode, n;
  function y(K) {
    throw RangeError(s[K])
  }
  function z(M, K) {
    var L = M.length;
    while (L--) {
      M[L] = K(M[L])
    }
    return M
  }
  function f(K, L) {
    return z(K.split(l), L).join('.')
  }
  function D(N) {
    var M = [], L = 0, O = N.length, P, K;
    while (L < O) {
      P = N.charCodeAt(L++);
      if ((P & 63488) == 55296 && L < O) {
        K = N.charCodeAt(L++);
        if ((K & 64512) == 56320) {
          M.push(((P & 1023) << 10) + (K & 1023) + 65536)
        } else {
          M.push(P, K)
        }
      } else {
        M.push(P)
      }
    }
    return M
  }
  function F(K) {
    return z(K, function (M) {
      var L = '';
      if (M > 65535) {
        M -= 65536;
        L += j(M >>> 10 & 1023 | 55296);
        M = 56320 | M & 1023
      }
      L += j(M);
      return L
    }).join('')
  }
  function c(K) {
    return K - 48 < 10 ? K - 22 : K - 65 < 26 ? K - 65 : K - 97 < 26 ? K - 97 : p
  }
  function A(L, K) {
    return L + 22 + 75 * (L < 26) - ((K != 0) << 5)
  }
  function w(N, L, M) {
    var K = 0;
    N = M ? g(N / b) : N >> 1;
    N += g(N / L);
    for (; N > v * H >> 1; K += p) {
      N = g(N / v)
    }
    return g(K + (v + 1) * N / (N + B))
  }
  function k(L, K) {
    L -= (L - 97 < 26) << 5;
    return L + (!K && L - 65 < 26) << 5
  }
  function a(X) {
    var N = [], Q = X.length, S, T = 0, M = G, U = m, P, R, V, L, Y, O, W, aa, K, Z;
    P = X.lastIndexOf(C);
    if (P < 0) {
      P = 0
    }
    for (R = 0; R < P; ++R) {
      if (X.charCodeAt(R) >= 128) {
        y('not-basic')
      }
      N.push(X.charCodeAt(R))
    }
    for (V = P > 0 ? P + 1 : 0; V < Q; ) {
      for (L = T, Y = 1, O = p; ; O += p) {
        if (V >= Q) {
          y('invalid-input')
        }
        W = c(X.charCodeAt(V++));
        if (W >= p || W > g((o - T) / Y)) {
          y('overflow')
        }
        T += W * Y;
        aa = O <= U ? i : (O >= U + H ? H : O - U);
        if (W < aa) {
          break
        }
        Z = p - aa;
        if (Y > g(o / Z)) {
          y('overflow')
        }
        Y *= Z
      }
      S = N.length + 1;
      U = w(T - L, S, L == 0);
      if (g(T / S) > o - M) {
        y('overflow')
      }
      M += g(T / S);
      T %= S;
      N.splice(T++, 0, M)
    }
    return F(N)
  }
  function d(W) {
    var N, Y, T, L, U, S, O, K, R, aa, X, M = [], Q, P, Z, V;
    W = D(W);
    Q = W.length;
    N = G;
    Y = 0;
    U = m;
    for (S = 0; S < Q; ++S) {
      X = W[S];
      if (X < 128) {
        M.push(j(X))
      }
    }
    T = L = M.length;
    if (L) {
      M.push(C)
    }
    while (T < Q) {
      for (O = o, S = 0; S < Q; ++S) {
        X = W[S];
        if (X >= N && X < O) {
          O = X
        }
      }
      P = T + 1;
      if (O - N > g((o - Y) / P)) {
        y('overflow')
      }
      Y += (O - N) * P;
      N = O;
      for (S = 0; S < Q; ++S) {
        X = W[S];
        if (X < N && ++Y > o) {
          y('overflow')
        }
        if (X == N) {
          for (K = Y, R = p; ; R += p) {
            aa = R <= U ? i : (R >= U + H ? H : R - U);
            if (K < aa) {
              break
            }
            V = K - aa;
            Z = p - aa;
            M.push(j(A(aa + V % Z, 0)));
            K = g(V / Z)
          }
          M.push(j(A(K, 0)));
          U = w(Y, P, T == L);
          Y = 0;
          ++T
        }
      }
      ++Y;
      ++N
    }
    return M.join('')
  }
  function r(K) {
    return f(K, function (L) {
      return E.test(L) ? a(L.slice(4).toLowerCase()) : L
    })
  }
  function x(K) {
    return f(K, function (L) {
      return t.test(L) ? 'xn--' + d(L) : L
    })
  }
  I = {version: '1.2.0', ucs2: {decode: D, encode: F}, decode: a, encode: d, toASCII: x, toUnicode: r};
  if (J) {
    if (q && q.exports == J) {
      q.exports = I
    } else {
      for (n in I) {
        I.hasOwnProperty(n) && (J[n] = I[n])
      }
    }
  } else {
    if (e) {
      define('punycode', I)
    } else {
      u.punycode = I
    }
  }
}(this));






$(document).ready(function () {
  
  protocol = $.trim($("#protocol").html());
  protocol = protocol ? protocol : 'http';
  //поиск basedir и currency в параметрах скриптов
  $('script').each(function () {
    if ($(this).attr('src')) {
      $(this).attr('src').replace(/&amp;/g, '&');
      $(this).attr('src').replace(/(\w+)(?:=([^&]*))?/g, function (a, key, value) {
        if (key === 'protocol') {
          protocol = value;
        }
        if (key === 'mgBaseDir') {

          var val = value;
          val = val.replace(protocol + '://', '');
          var ascii = punycode.toASCII(val);
          var uni = punycode.toUnicode(val);
          mgBaseDir = protocol + '://' + uni;
          if (
            /chrome/.test(navigator.userAgent.toLowerCase()) ||
            /safari/.test(navigator.userAgent.toLowerCase())
            ) {
            mgBaseDir = protocol + '://' + ascii;
          }

        }

        if (key === 'currency') {
          currency = value;
        }
        
        if (key === 'lang') {
          lang = value;
        }


      });
    }
  });

  includeJS(mgBaseDir + '/mg-admin/locales/'+lang+'.js');
  // все скрипты в админке нужно подключать через функцию includeJS,  
  includeJS(mgBaseDir + '/mg-core/script/jquery.tipTip.js');
  includeJS(mgBaseDir + '/mg-core/script/toggles.js');
  includeJS(mgBaseDir + '/mg-core/script/admin/change-theme.js');
  includeJS(mgBaseDir + '/mg-core/script/admin/change-language.js');
  includeJS(mgBaseDir + '/mg-core/script/admin/userProperty.js');
  includeJS(mgBaseDir + '/mg-core/script/jquery.form.js');
  includeJS(mgBaseDir + '/mg-core/script/ckeditor/ckeditor.js');
  includeJS(mgBaseDir + '/mg-core/script/ckeditor/adapters/jquery.js');
  includeJS(mgBaseDir + '/mg-core/script/admin/plugins.js');

  admin.init();
  
});