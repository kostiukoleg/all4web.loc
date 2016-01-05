/**
 * Модуль для  раздела "Заказы".
 */
$(".ui-autocomplete").css('z-index', '1000');
$.datepicker.regional['ru'] = {
  closeText: 'Закрыть',
  prevText: '&#x3c;Пред',
  nextText: 'След&#x3e;',
  currentText: 'Сегодня',
  monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
    'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
  monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
    'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
  dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
  dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
  dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
  dateFormat: 'dd.mm.yy',
  firstDay: 1,
  isRTL: false
};
$.datepicker.setDefaults($.datepicker.regional['ru']);

var order = (function () {
  return {
    comment: null,
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init: function () {

      // Вызов модального окна при нажатии на кнопку добавления заказа.
      $('.admin-center').on('click', '.section-order .add-new-button', function () {
        order.openModalWindow('add');
      });

      // Вызов модального окна при нажатии на кнопку изменения товаров.
      $('.admin-center').on('click', '.section-order .see-order', function () {
        order.openModalWindow('edit',$(this).attr('id'), $(this).attr('data-number'));
      });

      // Клонирование заказа
      $('.admin-center').on('click', '.section-order .clone-row', function () {
        order.cloneOrder($(this).attr('id'));
      });

      // Удаление товара.
      $('.admin-center').on('click', '.section-order .delete-order', function () {
        order.deleteOrder($(this).attr('id'));
      });

      // Показывает панель с фильтрами.
      $('.admin-center').on('click', '.section-order .show-filters', function () {
        $('.filter-container').slideToggle(function () {
          $('.property-order-container').slideUp();
          $('.widget-table-action').toggleClass('no-radius');
        });
      });

      // Показывает панель с фильтрами.
      $('.admin-center').on('click', '.section-order .show-property-order', function () {
        $('.property-order-container').slideToggle(function () {
          $('.filter-container').slideUp();
          $('.widget-table-action').toggleClass('no-radius');
        });
      });


      // Сброс фильтров.
      $('.admin-center').on('click', '.section-order .refreshFilter', function () {
        admin.clearGetParam();
        admin.show("orders.php", "adminpage", "refreshFilter=1", admin.sliderPrice);
        return false;
      });

      // Применение выбраных фильтров
      $('.admin-center').on('click', '.section-order .filter-now', function () {
        order.getProductByFilter();
        return false;
      });

      // Открывает панель настроек заказа
      $('.admin-center').on('click', '.section-order .property-order-container .save-property-order', function () {
        order.savePropertyOrder();
        return false;
      });

      // Выбор картинки
      $('.admin-center').on('click', '.section-order .property-order-container .upload-sign', function () {
        admin.openUploader('order.getSignFile');
      });

      // Выбор картинки
      $('.admin-center').on('click', '.section-order .property-order-container .upload-stamp', function () {
        admin.openUploader('order.getStampFile');
      });

      // Сохранение  при нажатии на кнопку сохранить в модальном окне.
      $('body').on('click', '#add-order-wrapper .save-button', function () {
        order.saveOrder($(this).attr('id'), $(this).parents('.orders-table-wrapper'), $(this).attr('data-number'));
      });

      // Распечатка заказа  
      $('body').on('click', '#add-order-wrapper .order-preview .print-button, .order-to-print a', function () {
        order.printOrder($(this).data('id'));
      });

      // Сохранить в PDF   
      $('body').on('click', '#add-order-wrapper .order-preview .get-pdf-button, .order-to-pdf a', function () {
        window.location.href = mgBaseDir + '/mg-admin?getOrderPdf=' + $(this).data('id');
      });

      // Получить выгрузку счета в CSV   
      $('body').on('click', '#add-order-wrapper .order-preview .csv-button, .order-to-csv a', function () {
        window.location.href = mgBaseDir + '/mg-admin?getExportCSV=' + $(this).data('id');
      });

      // Разблокировать поля для редактирования заказа.
      $('body').on('click', '#add-order-wrapper .order-preview .editor-order', function () {
        order.enableEditor();
      });

      $('body').find('#add-order-wrapper .delivery-date input[name=date_delivery]').datepicker({dateFormat: "dd.mm.yy", minDate: 0});
      $('body').on('mousedown', '#add-order-wrapper .delivery-date input[name=date_delivery]', function () {
        $(this).datepicker({dateFormat: "dd.mm.yy", minDate: 0});

      });

      // Удаляет выбраный продукт из поля для добавления в заказ.
      $('body').on('click', '#add-order-wrapper .clear-product', function () {
        $(".product-block").html('');
      });

      // Применить купон в редактировании заказа.
      $('body').on('change', '#add-order-wrapper select[name=promocode]', function () {
        order.calculateOrder();
      });
      // Применить скидку в редактировании заказа.
      $('body').on('change', '#add-order-wrapper .discount-system input', function () {
        if ($(this).val()=='true') {
          $(this).val('false');
        } else {
          $(this).val('true');
        }
        order.calculateOrder();            
      });
      // при изменении email покупателя - пересчет накопительной скидки
      $('body').on('blur', '#order-data input[name=user_email]', function () {
        if ($('.order-payment-sum .discount-system input[name=cumulative]').val()=='true') {
          order.calculateOrder();          
        }        
      });

      // Подстановка значения стоимости при выборе способа доставки в добавлении заказа.
      $('body').on('change', '#delivery', function () {
        $('#delivery').parent().find('.errorField').css('display', 'none');
        $('#delivery').removeClass('error-input');
        order.calculateOrder();
      });

      // Смена плательщика.
      $('body').on('change', '#customer', function () {
        $(this).val() == 'fiz' ? $('.yur-list-editor').hide() : $('.yur-list-editor').show();
      });

      // Действия при выборе способа оплаты.
      $('body').on('change', 'select#payment', function () {
        $('.main-settings-list select#payment').parent().find('.errorField').css('display', 'none');
        $('.main-settings-list select#payment').removeClass('error-input');
      });

      // Устанавливает количиство выводимых записей в этом разделе.
      $('.admin-center').on('change', '.section-order .countPrintRowsOrder', function () {
        var count = $(this).val();
        admin.ajaxRequest({
          mguniqueurl: "action/setCountPrintRowsOrder",
          count: count
        },
        function (response) {
          admin.refreshPanel();
        }
        );
      });

      // Поиск товара при создании нового заказа.
      // Обработка ввода поисковой фразы в поле поиска.
      $('.admin-center').on('keyup', '#order-data input[name=searchcat]', function () {
        admin.searchProduct($(this).val(), '#order-data .fastResult');
      });

      // Подстановка товара из примера в строку поиска.
      $('.admin-center').on('click', '#order-data .example-find', function () {
        $('#order-data input[name=searchcat]').val($(this).text());
        admin.searchProduct($(this).text(), '#order-data .fastResult');
      });

      
      // Клик вне поиска.
      $(document).mousedown(function (e) {
        var container = $(".fastResult");
        if (container.has(e.target).length === 0 &&
          $(".search-block").has(e.target).length === 0) {
          container.hide();
        }
      });

      // Пересчет цены товара аяксом в форме добавления заказа.
      $('.admin-center').on('change', '.orders-table-wrapper .property-form input, .orders-table-wrapper .property-form select',
        function () {
          if ($(this).parents('p').find('input[type=radio]').length) {
            $(this).parents('p').find('input[type=radio]').prop('checked', false);
            $(this).prop('checked', true);
          }
          order.refreshPriceProduct();
          return false;
        });

      // Клик по найденым товарам поиске в форме добавления заказа
      $('.admin-center').on('click', '.section-order .fast-result-list a', function () {
        order.viewProduct($(this).data('element-index'));
      });

      // Вставка продукта из списка поиска в строку заказа.
      $('.admin-center').on('click', '.orders-table-wrapper .property-form .addToCart', function () {
        order.addToOrder($(this));
        return false;
      });

      // Удаление позиции из заказа.
      $('body').on('click', '.order-history a[rel=delItem]', function () {
        $(this).parents('tr').remove();
        order.calculateOrder();
      });

      // Обработка выбора  способа доставки при добавлении нового заказа.
      $('body').on('change', 'select #delivery', function () {
        $('select #delivery option[name=null]').remove();
      });

      // Обработка выбора  способа оплаты при добавлении нового заказа.
      $('body').on('change', 'select#payment', function () {
        $('select#payment option[name=null]').remove();
      });

      // Перерасчет стоимости при смене количества товара.
      $('body').on('keyup', '#orderContent input', function () {
        if (1 > $(this).val() || !$.isNumeric($(this).val())) {
          $(this).val('1');
        }
        if ($(this).hasClass('count') && ($(this).data('max') >= 0)) {
          var max = parseInt($(this).data('max')) + parseInt($(this).attr('count-old'));
          if ($(this).val() > max) {
             $(this).val(max);
          } 
        }
        order.calculateOrder();
      });
      
      $('body').on('focus', '#orderContent input.count', function () {
        if (!$(this).attr('count-old')) {
          $(this).attr('count-old', $(this).val());
        }
      });

      // Обработка ввода адреса доставки 
      $('body').on('keyup', '#order-data input[name=address]', function () {
        $('.map-btn').attr('href', 'http://maps.yandex.ru/?text=' + encodeURIComponent($(this).val()));
      });

      // Выделить все заказы.
      $('.admin-center').on('click', '.section-order .checkbox-cell input[name=order-check]', function () {
        if ($(this).val() != 'true') {
          $('.order-tbody input[name=order-check]').prop('checked', 'checked');
          $('.order-tbody input[name=order-check]').val('true');
        } else {
          $('.order-tbody input[name=order-check]').prop('checked', false);
          $('.order-tbody input[name=order-check]').val('false');
        }
      });

      // Выполнение выбранной операции с заказами
      $('.admin-center').on('click', '.section-order .run-operation', function () {
        order.runOperation($('.order-operation').val());
      });

      $('.admin-center').on('click', '.order-preview input[name=inform-user]', function () {
        if ($(this).val() != 'true') {
          $('input[name=inform-user]').attr('checked', true);
          $('input[name=inform-user]').val('true');
        } else {
          $('input[name=inform-user]').removeAttr('checked');
          $('input[name=inform-user]').val('false');
        }

      });

      $('.admin-center').find('#delivery').attr('selected');

    },
    /**
     * Создает строку в таблице заказов
     * @param {type} position - параметры позиции
     * @param {type} type - тип формирования, для имеющегося состава или новой позиции
     * @returns {String}
     */
    createPositionRow: function (position, type) {

      var row = '\
          <tr data-id=' + position.id + ' data-variant=' + (position.variant ? position.variant : 0) + '>\
          <td class="image"><img src="' + position.image_url + '" style="width:50px;"></td>\
          <td class="title" style="width:240px">' + position.title + '</td>\
          <td class="code" data-code="' + position.code + '">' + position.code + '</td>\
          <td class="weight" data-weight="' + position.weight + '">' + ((position.weight == "undefined" || !position.weight) ? 0 : position.weight) + '</td>\
          <td class="price" data-price="' + position.price + '"  data-fulprice="' + position.fulPrice + '">'+
           ((type == "view") ? '<span class="value order-edit-visible">' + admin.numberFormat(position.price) + '</span>' : 
                               '<span class="value" style="display:none;">' + admin.numberFormat(position.price) + '</span>')
          +'<input class="price-val '+((type == "view") ? 'order-edit-display' : '')+'" type="text" value="' + position.price + '"> ' + admin.CURRENCY + '</td>\
          <td class="count">' +
        ((type == "view") ? '<span class="value order-edit-visible">' + position.count + '</span>' : '') +
        (position.notSet ? 
        '<input order_id="' + position.order_id + '"  type="text" data-max="' + position.maxCount + '" count-old =' + position.count + ' value="' + position.count + '" class="count ' +
        ((type == "view") ? 'order-edit-display' : '')
        + '"> ':
        '<input disabled order_id="' + position.order_id + '"  type="text" data-max="' + position.maxCount + '" count-old =' + position.count + ' value="' + position.count + '" class=" tool-tip-bottom count ' +
        ((type == "view") ? 'order-edit-display' : '')
        + '" title="Редактирование количества комплектов в оформленном заказе запрещено плагином \'Комплект товаров\'"> '
        ) + lang.UNIT + '</td>\
          <td class="summ" data-summ="' + position.summ + '"><span class="value">' + admin.numberFormat(position.summ) + '</span> ' + admin.CURRENCY + '</td>\
          <td class="prod-remove"><span class="' + ((type == "view") ? 'order-edit-display' : '') + '"><a class="tool-tip-bottom dell-btn ' +
        ((type == "view") ? 'order-edit-display' : '')
        + '" order_id="' + position.order_id + '" href="javascript:void(0);" rel="delItem"></a></span></td>\
        </tr>';
      return row;
    },
    /*
     * Получает все выбранные свойства товара перед добавлением в строку заказа  
     * @returns {String}
     */
    getPropPosition: function () {
      var prop = '';
      $('.property-form select, .property-form input[type=checkbox],.property-form input[type=radio]').each(function () {
        if ($(this).attr('name') != 'variant') {
          var val = "";
          var val = $(this).find('option:selected').text();

          if ($(this).val() == "true") {
            val = $(this).next("span").text();
          }

          if ($(this).prop('checked') === true) {
            val = $(this).next("span").text();
          }

          if (val) {
            var propertyTitle = $(this).parents('p').find('.property-title').text() + ': ';
           
            var marg = admin.trim(val.replace(eval('/(.*)([-+]\\s[0-9]+' + $('#order-data .currency-sp').text() + ')/gi'), '$2'));                
            var val = admin.trim(val.replace(eval('/(.*)([-+]\\s[0-9]+' + $('#order-data .currency-sp').text() + ')/gi'), '$1'));
            if (marg == val) {
              marg = '';
            }
            
            var wrap = '<div class="prop-position"> <span class="prop-name">' + propertyTitle + val + '</span> <span class="prop-val"> ' + marg + '</span></div>';
            prop += wrap;
          }

        }
      });

      return prop;
    },
    /**
     * Открывает модальное окно.
     * type - тип окна, либо для создания нового товара, либо для редактирования старого.
     */
    openModalWindow: function (type, id, number) {
      $('.product-block').html('');
      switch (type) {
        case 'add':
        {
          admin.indication('error','Доступно только в полной версии Moguta.CMS');  
          return false;
          break;
        }
        case 'edit':
        {
          $('.add-order-table-icon').text(lang.TITLE_ORDER_VIEW + ' №' + number + ' от ' + $('tr[order_id=' + id + '] .add_date').text());
          order.editOrder(id);
          break;
        }
      }
      // Вызов модального окна.
      admin.openModal($('.b-modal'));
      admin.initToolTip();
    },
    /**
     * Выполняет выбранную операцию со всеми отмеченными заказами
     * operation - тип операции.
     */
    runOperation: function (operation) {

      var orders_id = [];
      $('.order-tbody tr').each(function () {
        if ($(this).find('input[name=order-check]').prop('checked')) {
          orders_id.push($(this).attr('order_id'));
        }
      });

      if (confirm(lang.RUN_CONFIRM)) {
        admin.ajaxRequest({
          mguniqueurl: "action/operationOrder",
          operation: operation,
          orders_id: orders_id
        },
        function (response) {
          response.data.count = response.data.count ? response.data.count : '';
          $('.button-list a[rel=orders]').parent().find('span').eq(0).text(response.data.count);
          admin.refreshPanel();
        }
        );
      }


    },
    /**
     *  Проверка заполненности полей, для каждого поля прописывается свое правило.
     */
    checkRulesForm: function () {
      $('.errorField').css('display', 'none');
      $('#order-data input, select').removeClass('error-input');
      var error = false;

      // покупатель обязательно надо заполнить.
	  /*
      if (!$('#order-data input[name=name_buyer]').val()) {
        $('#order-data input[name=name_buyer]').parent().find('.errorField').css('display', 'block');
        $('#order-data input[name=name_buyer]').addClass('error-input');
        error = true;
      }

      // email обязательно надо заполнить.
      if (!/^[-._a-zA-Z0-9]+@(?:[a-zA-Z0-9][-a-zA-Z0-9]{0,61}\.)+[a-zA-Z]{2,6}$/.test($('#order-data input[name=user_email]').val()) || !$('#order-data input[name=user_email]').val()) {
        $('#order-data input[name=user_email]').parent().find('.errorField').css('display', 'block');
        $('#order-data input[name=user_email]').addClass('error-input');
        error = true;
      }*/
      // товар обязательно надо добавить
      if ($("#totalPrice").text() == "0") {
        $('.search-block .errorField').css('display', 'block');
        $('.search-block input.search-field').addClass('error-input');
        error = true;
      }

      // проверка реквизитов юр. лица
      if ($('#customer').val() == 'yur') {
        //var filds = ['nameyur', 'adress', 'inn', 'kpp', 'bank', 'bik', 'ks', 'rs'];
        var filds = ['inn'];
        filds.forEach(function (element, index, array) {
          if (!$('#order-data input[name=' + element + ']').val()) {
            $('#order-data input[name=' + element + ']').parent().find('.errorField').css('display', 'block');
            $('#order-data input[name=' + element + ']').addClass('error-input');
            error = true;
          }
        });
      }

      if (error == true) {
        return false;

      }
      return true;
    },
    /**
     * Cобираем состав заказа из таблицы   
     * @returns {string}
     */
    getOrderContent: function () {
      var discountCum = $('.discount-system input[name=cumulative]').val()=='true' ? "true" : "false" ;
      var discountVol = $('.discount-system input[name=volume]').val()=='true' ? "true" : "false" ;
      var obj = '[';
      $('#order-data .order-history tbody#orderContent tr').each(function () {
        if ($(this).data('id')) {
          obj += '{'
          obj += '"id":"' + +encodeURIComponent($(this).data('id')) + '",';
          obj += '"variant":"' + +encodeURIComponent($(this).data('variant')) + '",';
          obj += '"title":"' + encodeURIComponent($(this).find('.titleProd').text()) + '",';
          obj += '"name":"' + encodeURIComponent($(this).find('.titleProd').text()) + '",';
          obj += '"property":"' + encodeURIComponent($(this).find('.property').html()) + '",';
          obj += '"price":"' + encodeURIComponent($(this).find('.price').data('price')) + '",';
          obj += '"fulPrice":"' + encodeURIComponent($(this).find('.price').data('fulprice')) + '",';
          obj += '"code":"' + encodeURIComponent($(this).find('.code').text()) + '",';
          obj += '"weight":"' + encodeURIComponent($(this).find('.weight').text()) + '",';
          obj += '"count":"' + encodeURIComponent($(this).find('input.count').val()) + '",';
          obj += '"coupon":"' + encodeURIComponent($("select[name=promocode]").val()) + '",';
          obj += '"info":"' + encodeURIComponent($(".user-info-order").text()) + '",';
          obj += '"url":"' + encodeURIComponent($(this).find(".href-to-prod").data('url')) + '",';
          obj += '"discount":"' + encodeURIComponent($('.promocode-percent span').text()) + '",';
          obj += '"discSyst":"' + encodeURIComponent(discountCum+'/'+discountVol) + '",';
          obj += '},';
        }

      });
      obj += ']';

      return eval("(" + obj + ")");
    },
    /**
     * Сохранение изменений в модальном окне заказа.
     * Используется и для сохранения редактированных данных и для сохраниеня нового заказа.
     * id - идентификатор продукта, может отсутсвовать если производится добавление нового заказа.
     */
    saveOrder: function (id, container, number) {
      var orderContent = order.getOrderContent();

      if (!order.checkRulesForm()) {
        return false;
      }

      var yur = $('#customer').val() == 'yur' ? true : false;
      // Пакет характеристик заказа.
      var packedProperty = {
        mguniqueurl: "action/saveOrder",
        orderPositionCount: orderContent.length,
        address: $('input[name=address]').val(),
        date_delivery: $('input[name=date_delivery]').val(),
        comment: $('textarea[name=comment]').val(),
        delivery_cost: $('#deliveryCost').text(),
        delivery_id: $('select#delivery :selected').attr('name'),
        id: id,
        number: number,
        name_buyer: $('input[name=name_buyer]').val(),
        payment_id: $('select#payment :selected').val(),
        phone: $('input[name=phone]').val(),
        status_id: $('select[name=status_id] :selected').val(),
        inform_user: $('input[name=inform-user]').val(),
        summ: $('#totalPrice').text(),
        user_email: $('input[name=user_email]').val(),
        nameyur: (yur ? container.find('.yur-list-editor input[name=nameyur]').val() : ''),
        adress: (yur ? container.find('.yur-list-editor input[name=adress]').val() : ''),
        inn: (yur ? container.find('.yur-list-editor input[name=inn]').val() : ''),
        kpp: (yur ? container.find('.yur-list-editor input[name=kpp]').val() : ''),
        ogrn: (yur ? container.find('.yur-list-editor input[name=ogrn]').val() : ''),
        bank: (yur ? container.find('.yur-list-editor input[name=bank]').val() : ''),
        bik: (yur ? container.find('.yur-list-editor input[name=bik]').val() : ''),
        ks: (yur ? container.find('.yur-list-editor input[name=ks]').val() : ''),
        rs: (yur ? container.find('.yur-list-editor input[name=rs]').val() : ''),
        order_content: orderContent
      }
      
      // отправка данных на сервер для сохранеиня
      admin.ajaxRequest(packedProperty,
        function (response) {
          admin.clearGetParam();
          admin.indication(response.status, response.msg);
          order.indicatorCount(response.data.count);
          var assocStatus = ['get-paid', 'get-paid', 'paid', 'get-paid', 'dont-paid', 'paid', 'get-paid'];

          if (response.data.newId) {

            var row = order.drawRowOrder(response.data, assocStatus);
            var newCount = $('.widget-table-title .produc-count strong').text() - 0 + 1;

            if ($('.order-tbody tr').length == 1 && newCount == 1) {
              $('.order-tbody tr').remove();
            }

            // Если id не было значит добавляем новую строку в начало таблицы.
            if ($('.order-tbody tr:first').length > 0) {
              $('.order-tbody tr:first').before(row);
            } else {
              $('.order-tbody ').append(row);
            }

            $('.button-list a[rel=orders]').parent().find('span').show();

            $('.button-list a[rel=orders]').parent().find('span :first').text(newCount);
            $('.produc-count strong').text(newCount);
            if (!newCount) {
              $('.button-list a[rel=orders]').parent().find('span').hide();
            }

          } else {
            response.data.date = $('tr[order_id=' + response.data.id + '] td[class="add_date"]').text();
            var row = order.drawRowOrder(response.data, assocStatus);
            $('tr[order_id=' + response.data.id + ']').replaceWith(row);
          }


          admin.closeModal($('.b-modal'));
        }
      );
    },
    // меняет индикатор количества новых заказов
    indicatorCount: function (count) {
      if (count == 0) {
        $('.button-list a[rel=orders]').parents('li').find('.message-wrap').hide();
      } else {
        $('.button-list a[rel=orders]').parents('li').find('.message-wrap').show();
        $('.button-list a[rel=orders]').parents('li').find('.message-wrap').text(count);
      }
    },
    /**
     * Удаляет запись из БД сайта и таблицы в текущем разделе
     */
    deleteOrder: function (id) {
      if (confirm(lang.DELETE + '?')) {
        admin.ajaxRequest({
          mguniqueurl: "action/deleteOrder",
          id: id
        },
        function (response) {
          admin.indication(response.status, response.msg);
          order.indicatorCount(response.data.count - 1);
          $('tr[order_id=' + id + ']').remove();
          var newCount = ($('.widget-table-title .produc-count strong').text() - 1);
          if (newCount >= 0) {
            $('.widget-table-title .produc-count strong').text(newCount);
          }

          if ($('.product-table tr').length == 1) {
            var row = "<tr><td colspan=" + $('.product-table th').length + " class='noneOrders'>" + lang.ORDER_NONE + "</td></tr>"
            $('.order-tbody').append(row);
          }

        }
        );
      }
    },
    /**
     * Редактирует заказ
     * @param {type} id
     * @returns {undefined}
     */
    editOrder: function (id) {
      admin.ajaxRequest({
        mguniqueurl: "action/getOrderData",
        id: id,
      },
      order.fillFileds(),
        $('.order-preview')
        );
    },
    newOrder: function (id) {
      admin.ajaxRequest({
        mguniqueurl: "action/getOrderData",
        id: null
      },
      order.fillFileds('newOrder'),
        $('.order-preview')
        );
    },
    /**
     * Заполняет поля модального окна данными.
     */
    fillFileds: function (type) {
      return function (response) {
        $('.order-edit-display').hide();
        $('.order-edit-visible').show();
        $("#orderStatus").removeClass('edit-layout');
        /* заполнение выпадающих списков */
        $('.order-preview .save-button').attr('id', response.data.order.id);
        $('.order-preview .save-button').attr('data-number', response.data.order.number);
        $('.order-preview .print-button').data('id', response.data.order.id);
        $('.order-preview .get-pdf-button').data('id', response.data.order.id);
        $('.order-preview .csv-button').data('id', response.data.order.id);
        $('#orderStatus').val(response.data.order.status_id ? response.data.order.status_id : '0');
        $('input[name=inform-user]').val(false);
        $('input[name=inform-user]').removeAttr('checked');
        var deliveryCurrentName = '';
        var deliveryDatePossible;
        //список способов доставки
        var deliveryList = '<select id="delivery">';
        var selected = '';
        $.each(response.data.deliveryArray, function (i, delivery) {
          selected = '';

          if (delivery.activity == 1) {
            if (delivery.id == response.data.order.delivery_id) {
              deliveryCurrentName = delivery.name;
              deliveryDatePossible = delivery.date;
              selected = 'selected';
            }
            deliveryList += '<option value="' + delivery.cost + '" data-free="' + delivery.free + '" data-date="' + delivery.date + '" name="' + delivery.id + '" ' + selected + '>' + delivery.name + '</option>';
          }
        });
        deliveryList += '</select>';


        var paymentCurrentName = '';
        //список способов оплаты
        var paymentList = '<select id="payment">';
        $.each(response.data.paymentArray, function (i, payment) {
          selected = '';
          if (payment.id == response.data.order.payment_id) {
            paymentCurrentName = payment.name;
            selected = 'selected';
          }
          paymentList += '<option value="' + payment.id + '" ' + selected + '>' + payment.name + '</option>';

        });
        paymentList += '</select>';
        var coupon = '';
        var info = '';
        var orderContentTable = '';
        var discounts = '';
        if (response.data.order.order_content) {
          $.each(response.data.order.order_content, function (i, element) {
            coupon = element.coupon ? element.coupon : '';
            info = element.info ? element.info : '';
            discounts = element.discSyst ? element.discSyst : '';
            // если товар находится в корне каталога, то приписываем категорию catalog           
            if (element.url) {

              var sections = admin.trim(element.url, '/').split('/');

              if (sections.length == 1) {
                element.url = 'catalog' + element.url;
              }
            }

            var position = {
              order_id: response.data.order.id,
              id: element.id,
              title: '<a href="' + mgBaseDir + '/' + element.url + '" data-url="' + element.url + '" class="href-to-prod"><span class="titleProd">' + element.name + '</span></a>' + '<span class="property">' + element.property + '</span>',
              prop: element.property,
              code: element.code,
              weight: element.weight,
              price: element.price,
              count: element.count,
              summ: (element.count * (element.price * 100)) / 100,
              image_url: element.image_url,
              fulPrice: element.fulPrice,
              maxCount: element.maxCount,
              variant: element.variant,
              notSet: element.notSet
            };


            orderContentTable += order.createPositionRow(position, 'view');

          });
        }
        
        if(info == ''){
          info = response.data.order.user_comment;
        }

        var data = {
          paymentList: paymentList,
          deliveryList: deliveryList,
          coupon: coupon,
          info: info,
          discounts: discounts,
          orderContentTable: orderContentTable,
          paymentCurrentName: paymentCurrentName,
          deliveryCurrentName: deliveryCurrentName,
          deliveryDatePossible: deliveryDatePossible
        }

        $('.order-history').html(order.drawOrder(response, data));

        // Если открыта модалка добавления нового заказа.
        if (type == 'newOrder') {
          $('.order-history input').val('');
          $('.order-history #orderContent').html('');
          order.enableEditor();
          $('#delivery option[value=0]').prop('selected', 'selected');
          order.calculateOrder();
          $('.order-preview .save-button').attr('id', "");
          $('.order-preview .save-button').attr('data-number', "");
          $('.delivery-date').hide();

        }
      }
    },
    /**
     * Создает верстку для модального окна, редактирования и добавления заказа
     * @param {type} id
     * @returns {undefined}
     */
    drawOrder: function (response, data) {
      var dateDelivery = '';

      /* заполнение состава заказа  */
      var editorBlock = '\
          <div class="order-edit-display fl-left editor-block">\
            <p>\
              <span>' + lang.ORDER_BUYER + ':</span>\
              <input type="text" name="name_buyer" value="' + admin.htmlspecialchars(response.data.order.name_buyer) + '" >\
            </p>\
            <p>\
              <span>' + lang.ORDER_ADDRESS + ':</span>\
               <input type="text" name="address" value="' + admin.htmlspecialchars(response.data.order.address) + '" ><a target="_blank" class="map-btn" title="Посмотреть на карте" href="http://maps.yandex.ru/?text=' + encodeURIComponent(response.data.order.address) + '" ></a></strong>\
            </p>\
            <p>\
                <div class="delivery-date" style="display:none"><span >' + lang.DELIVERY_DATE + ':</span>\
                        <input type="text" name="date_delivery" value="' + (response.data.order.date_delivery ? response.data.order.date_delivery : '') + '" >\
             </div></p>\
            <p>\
              <span>' + lang.ORDER_PAYMENT + ':</span>\
              ' + data.paymentList + '\
            </p>\
            <p>\
              <span>' + lang.ORDER_EMAIL + '</span>\
              <input type="text" name="user_email" value="' + admin.htmlspecialchars(response.data.order.user_email) + '">\
            </p>\
            <p>\
              <span>' + lang.ORDER_PHONE + '</span>\
              <input type="text" name="phone" value="' + admin.htmlspecialchars(response.data.order.phone) + '">\
            </p>\
            <p>\
              <span>' + lang.EDIT_ORDER_1 + ':</span>\
              <select id="customer" name="customer">\
                <option value="fiz">' + lang.EDIT_ORDER_2 + '</option>\
                <option value="yur" ' + (response.data.order.yur_info.inn ? 'selected' : '') + '>' + lang.EDIT_ORDER_3 + '</option>\
              </select>\
            </p>\
            ';

      editorBlock += '\
            <ul class="yur-list-editor">\
              <li><span>' + lang.OREDER_LOCALE_9 + ':</span><input type="text" name="nameyur" value="' + admin.htmlspecialchars((response.data.order.yur_info.nameyur ? response.data.order.yur_info.nameyur : '')) + '"></li>\
              <li><span>' + lang.OREDER_LOCALE_15 + ':</span><input type="text" name="adress" value="' + admin.htmlspecialchars((response.data.order.yur_info.adress ? response.data.order.yur_info.adress : '')) + '"></li>\
              <li><span>' + lang.OREDER_LOCALE_16 + ':</span><input type="text" name="inn" value="' + admin.htmlspecialchars((response.data.order.yur_info.inn ? response.data.order.yur_info.inn : '')) + '"></li>\
              <li><span>' + lang.OREDER_LOCALE_17 + ':</span><input type="text" name="kpp" value="' + admin.htmlspecialchars((response.data.order.yur_info.kpp ? response.data.order.yur_info.kpp : '')) + '"></li>\
              <li><span>' + lang.OREDER_LOCALE_18 + ':</span><input type="text" name="bank" value="' + admin.htmlspecialchars((response.data.order.yur_info.bank ? response.data.order.yur_info.bank : '')) + '"></li>\
              <li><span>' + lang.OREDER_LOCALE_19 + ':</span><input type="text" name="bik" value="' + admin.htmlspecialchars((response.data.order.yur_info.bik ? response.data.order.yur_info.bik : '')) + '"></li>\
              <li><span>' + lang.OREDER_LOCALE_20 + ':</span><input type="text" name="ks" value="' + admin.htmlspecialchars((response.data.order.yur_info.ks ? response.data.order.yur_info.ks : '')) + '"></li>\
              <li><span>' + lang.OREDER_LOCALE_21 + ':</span><input type="text" name="rs" value="' + admin.htmlspecialchars((response.data.order.yur_info.rs ? response.data.order.yur_info.rs : '')) + '"></li>\
          </ul>'

      editorBlock += '</div>';
      var disabled = '';
      if (data.orderContentTable !='') {
        disabled = 'disabled title="'+lang.T_TIP_EDIT_ORDER_11+'"';
      }
      var selectPromocode = '<select class="tool-tip-bottom" data-discount=0 name="promocode" '+disabled+'>';
      selectPromocode += '<option value=0>' + lang.EDIT_ORDER_4 + '</option>';
      $.each(response.data.order.promoCodes, function (i, element) {
        selectPromocode += '<option ' + (element == data.coupon ? 'selected' : '') + '>' + element + '</option>';
      });
      selectPromocode += '</select>';
      var discounts = '';
      if (response.data.order.discountsSystem ||data.discounts != '') {  
        var cumulative = false; 
        var volume = false;        
        if (data.discounts != '') {
          var discount = data.discounts.split('/');
          cumulative = discount[0]; 
          volume = discount[1];
          disabled = 'disabled title="'+lang.T_TIP_EDIT_ORDER_11+'"';
        }        
        discounts = '<span>Накопительная скидка </span>\
        <input class="tool-tip-bottom" type="checkbox" name="cumulative" value='+cumulative+' '+(cumulative == 'true' ? 'checked' : '')+ ' '+ disabled+ '><br>\
               <span>Объемная скидка </span>\
        <input class="tool-tip-bottom" type="checkbox" name="volume" value='+volume+' '+(volume == 'true' ? 'checked' : '')+' '+ disabled+ '>';
      }
      // deliveryDatePossible - 1 или 0 - возможность добавления даты доставки в заказ, значение выбранного метода доставки
      if (data.deliveryDatePossible == 1) {
        dateDelivery = '<p>\
                        <span>' + lang.DELIVERY_DATE + ':</span>\
                        <strong>' + (response.data.order.date_delivery ? response.data.order.date_delivery : 'Дата не указана') + '</strong>\
                        </p>\ ';
      }
      var orderHtml = '\
                   <p class="change-pass-title th-' + changeTheme.color + '">' + lang.ORDER_CONТENT + ':</p>\
                     <table class="status-table">\
                       <thead>\
                        <tr>\
                          <th></th>\
                          <th class="prod-name">' + lang.ORDER_PROD + '</th>\
                          <th>' + lang.ORDER_CODE + '</th>\
                          <th>' + lang.WEIGHT + '</th>\
                          <th class="prod-price">' + lang.ORDER_PRICE + '</th>\
                          <th>' + lang.ORDER_COUNT + '</th>\
                          <th>' + lang.ORDER_SUMM + '</th>\
                          <th class="prod-remove"></th>\
                        </tr>\
                      </thead>\
                      <tbody id="orderContent">' + data.orderContentTable + '</tbody>\
                     </table>\
                       <div class="order-payment-sum">\
                          <p>\
                              <span>' + lang.ORDER_TOTAL_PRICE + ':</span>\
                              <span>' + '<span id="totalPrice">' + response.data.order.summ * 1 + '</span>' + " " + admin.CURRENCY + '</span>\
                          </p>\
                           <p class="promocode-order">\
                            <span>' + lang.EDIT_ORDER_11 + ': </span>\
                            <span class="order-edit-visible"><strong>' + (data.coupon !='0'  ? data.coupon : '' )  + '</strong></span>\
                            <span class="order-edit-display code-block">' + selectPromocode + '</span>\
                          </p>\
                          <p class="discount-system">\
                              '+ discounts +
                          '</p>\
                          <p>\
                              <span>' + lang.EDIT_ORDER_5 + ':</span>\
                              <span class="promocode-percent"><span>' + (response.data.discount ? response.data.discount : '0') + '</span>%</span>\
                          </p>\
                          <p>\
                              <span>' + lang.ORDER_DELIVERY + ':</span>\
                              <span class="order-edit-visible"><strong>' + data.deliveryCurrentName + '</strong></span>\
                              <span class="order-edit-display">' + data.deliveryList + '</span>\
                          </p>\
                           <p>\
                              <span>' + lang.EDIT_ORDER_6 + ':</span>\
                              <span>' + '<span id="deliveryCost">' + admin.numberFormat(response.data.order.delivery_cost) + '</span>' + " " + admin.CURRENCY + '</span>\
                          </p>\
                          <p>\
                              <span>' + lang.ORDER_SUMM + ':</span>\
                              <span class="total-price">' + '<span id="fullCost">' + admin.numberFormat((response.data.order.summ * 1 + response.data.order.delivery_cost * 1)) + '</span>' + " " + admin.CURRENCY + '</span>\
                        </p>\
                     </div>\
                        </div>'
        + editorBlock +
        '<div class="order-other-info order-edit-visible">\
                        <p>\
                            <span>' + lang.ORDER_BUYER + ':</span>\
                            <strong>' + response.data.order.name_buyer + '</strong>\
                        </p>\
                        <p>\
                            <span>' + lang.ORDER_ADDRESS + ':</span>\
                            <strong><a target="_blank" href="http://maps.yandex.ru/?text=' + encodeURIComponent(response.data.order.address) + '">' + response.data.order.address + '</a></strong>\
                        </p>\ '
        + dateDelivery +
        '<p>\
                            <span>' + lang.ORDER_PAYMENT + ':</span>\
                            <strong><span class="icon-payment-' + response.data.order.payment_id + '"></span>' + data.paymentCurrentName + '</strong>\
                        </p>\
                        <p>\
                            <span>' + lang.ORDER_EMAIL + ':</span>\
                            <strong>' + response.data.order.user_email + '</strong>\
                        </p>\
                        <p>\
                            <span>' + lang.ORDER_PHONE + ':</span>\
                            <strong>' + response.data.order.phone + '</strong>\
                        </p>\
                        <p>\
                            <span>' + lang.ORDER_IP + ':</span>\
                            <strong>' + response.data.order.ip+ '</strong>\
                        </p>';
      
      if (response.data.order.yur_info.inn) {
        orderHtml += '\
            <ul class="order-edit-visible">\
              <li><span>Юр. лицо:</span> <strong>' + (response.data.order.yur_info.nameyur ? response.data.order.yur_info.nameyur : '') + '</strong></li>\
              <li><span>Юр. адрес:</span> <strong>' + (response.data.order.yur_info.adress ? response.data.order.yur_info.adress : '') + '</strong></li>\
              <li><span>ИНН:</span> <strong>' + (response.data.order.yur_info.inn ? response.data.order.yur_info.inn : '') + '</strong></li>\
              <li><span>КПП:</span> <strong>' + (response.data.order.yur_info.kpp ? response.data.order.yur_info.kpp : '') + '</strong></li>\
              <li><span>Банк:</span> <strong>' + (response.data.order.yur_info.bank ? response.data.order.yur_info.bank : '') + '</strong></li>\
              <li><span>БИК:</span> <strong>' + (response.data.order.yur_info.bik ? response.data.order.yur_info.bik : '') + '</strong></li>\
              <li><span>К/Сч:</span> <strong>' + (response.data.order.yur_info.ks ? response.data.order.yur_info.ks : '') + '</strong></li>\
              <li><span>Р/Сч:</span> <strong>' + (response.data.order.yur_info.rs ? response.data.order.yur_info.rs : '') + '</strong></li>\
          </ul>'
      }
      orderHtml += '</div><div class="clear"></div>';
      if (data.info) {
        orderHtml += '<div class="order-comment-block added-comment" >\
                  <span>' + lang.EDIT_ORDER_7 + ':</span>\
                  <div class="user-info-order">' + data.info + '</div></div>';
      }
      orderHtml += '<div class="order-comment-block ' + (response.data.order.comment ? 'added-comment' : 'order-edit-display') + '" >\
                  <span>' + lang.EDIT_ORDER_8 + ':</span>\
                  <div class="order-edit-visible">' + (response.data.order.comment ? response.data.order.comment : ' ') + '</div>\
                  <textarea name="comment" class="cancel-order-reason order-edit-display">' + (response.data.order.comment ? response.data.order.comment : '') + '</textarea>\
                </div>\
               ';


      return orderHtml;
    },
    /**
     * Сохраняет настройки к заказам.
     */
    savePropertyOrder: function () {
      var request = "mguniqueurl=action/savePropertyOrder&" + $("form[name=requisites]").formSerialize();

      admin.ajaxRequest(
        request,
        function (response) {
          admin.indication(response.status, response.msg);
          $('.property-order-container').slideToggle(function () {
            $('.widget-table-action').toggleClass('no-radius');
          });
        }
      );

      return false;
    },
    /**
     * Просчитывает стоимость заказа, обновляет поля.
     */
    calculateOrder: function () {
      var totalFullSumm = 0;
      $('tbody#orderContent tr').each(function (i, element) { 
        var fullPrice = $(this).find('td.price').data('fulprice');
        var count = $(this).find('td.count input').val();
        var fullSumm = count * (Math.round(fullPrice * 100));
        fullSumm = fullSumm / 100;
        totalFullSumm += Math.round(fullSumm * 100);
      });
      totalFullSumm = totalFullSumm / 100;
      $('#totalPrice').attr('data-fullsum', totalFullSumm);
      $.ajax({
        type: "POST",
        url: mgBaseDir + "/ajax",
        data: {
          mguniqueurl: "action/getDiscount",
          summ: totalFullSumm,
          email: $('#order-data input[name="user_email"]').val(),
          promocode: $("#order-data select[name=promocode]").val(),
          cumulative: $('#order-data .discount-system input[name=cumulative]').val(),
          volume: $('#order-data .discount-system input[name=volume]').val(),
        },
        dataType: "json",
        cache: false,
        success: function (response) {
          $(".promocode-percent span").text(response.data.percent);          
      var totalSumm = 0;
      $('tbody#orderContent tr').each(function (i, element) {
        var price = $(this).find('td.price').data('fulprice');        
            var fullPrice = price;
            var percent = parseFloat(response.data.percent);
            var priceDiscount = price - (price * percent / 100);
        if(price==priceDiscount){
            $(this).find('td.price .price-val').show();  
            $(this).find('td.price .value').hide(); 
            //если задана произвольная цена для товара
            var newPrice = $(this).find('td.price .price-val').val();
            if(newPrice!=price){
              price=newPrice;               
            }
        }else{
          $(this).find('td.price .price-val').val(price);  
          $(this).find('td.price .price-val').hide();  
          $(this).find('td.price .value').text(price).show();         
          price=priceDiscount; 
       
        }
        
        var count = $(this).find('td.count input').val();
        var summ = count * (Math.round(price * 100));
        summ = summ / 100;

        $(this).find('td.price').data('price', price);
        $(this).find('td.price span').text(price);

        $(this).find('td.summ').data('summ', summ);
        $(this).find('td.summ span').text(summ);
        totalSumm += Math.round(summ * 100);

      });
      totalSumm = totalSumm / 100;
      var deliveryCost = $('#delivery option:selected').val();

      if (totalSumm >= $('#delivery option:selected').data('free') && $('#delivery option:selected').data('free') > 0 || deliveryCost == undefined) {
        deliveryCost = 0;
      }
      var fullCost = totalSumm * 100 + deliveryCost * 100;
      fullCost = fullCost / 100;
      $('#deliveryCost').text(admin.numberFormat(deliveryCost));
      $('#totalPrice').text(totalSumm);
      $('#fullCost').text(admin.numberFormat(fullCost));
        }});

      return false;
    },
    /**
     * Получает данные из формы фильтров и перезагружает страницу
     */
    getProductByFilter: function () {
      admin.indication('error','Доступно только в полной версии Moguta.CMS');
      return false;
    },

    /**
     * изменяет строки в таблице товаров при редактировании изменении.                    
     */
    drawRowOrder: function (element, assocStatus) {

      var deliveryText = $('.order-preview #delivery option[name=' + element.delivery_id + ']').text();
      var paymentText = $('.order-preview #payment option[value=' + element.payment_id + ']').text();
      var statusName = $('.order-preview #orderStatus option:selected').text();
      var orderSumm = parseFloat(element.summ) + parseFloat(element.delivery_cost);
       // html верстка для  записи в таблице раздела  

      var row = '\
       <tr class="" order_id="' + element.id + '">\
       <td class="check-align"><input type="checkbox" name="order-check"></td>\
       <td > ' + element.id + '</td>\
       <td > ' + element.number + '</td>\
       <td class="add_date"> ' + element.date + '</td>\
       <td > ' + element.name_buyer + '</td>\
       <td > ' + element.user_email + '</td>\
       <td > ' + deliveryText + '</td>\
       <td > <span class="icon-payment-' + element.payment_id + '"></span>' + paymentText + '</td>\
       <td > ' + admin.numberFormat(orderSumm) + ' ' + admin.CURRENCY + '</td>\
       <td class="statusId id_' + element.status_id + '">\
       <span class="' + assocStatus[element.status_id] + '">' + statusName + '</span>\
       </td>\
       <td class="actions">\
       <ul class="action-list">\
       <li class="see-order" id="' + element.id + '"  data-number="'+ element.number +'">\
       <a class="tool-tip-bottom" href="javascript:void(0);" title="' + lang.SEE + '"></a>\
       </li>\
       <li class="order-to-csv"><a  data-id="' + element.id + '" class="tool-tip-bottom" href="javascript:void(0);" title="Сохранить в CSV"></a></li>\
       <li class="order-to-pdf"><a data-id="' + element.id + '"   class="tool-tip-bottom" href="javascript:void(0);" title="Сохранить в PDF"></a></li>\
       <li class="order-to-print"><a  data-id="' + element.id + '"   class="tool-tip-bottom" href="javascript:void(0);" title="Печать заказа"></a></li>\
       <li class="clone-row" id="' + element.id + '"><a title="Клонировать заказ"  class="tool-tip-bottom" href="javascript:void(0);"></a></li>\
       <li class="delete-order" id="' + element.id + '">\
       <a class="tool-tip-bottom" href="javascript:void(0);" title="' + lang.DELETE + '"></a>\
       </li>\
       </ul>\
       </tr>';

      return row;

    },
    /**
     * функция для приема подписи из аплоадера
     */
    getSignFile: function (file) {
      var src = file.url;
      src = 'uploads' + src.replace(/(.*)uploads/g, '');
      $('.section-order .property-order-container input[name="sing"]').val(src);
      $('.section-order .property-order-container .singPreview').attr("src", file.url);
    },
    /**
     * функция для приема печати из аплоадера
     */
    getStampFile: function (file) {
      var src = file.url;
      src = 'uploads' + src.replace(/(.*)uploads/g, '');
      $('.section-order .property-order-container input[name="stamp"]').val(src);
      $('.section-order .property-order-container .stampPreview').attr("src", file.url);
    },
    /**
     * Печать заказа
     */
    printOrder: function (id) {
      admin.ajaxRequest({
        mguniqueurl: "action/printOrder",
        id: id
      },
      function (response) {
        //admin.indication(response.status, response.msg);     
        $('.block-print').html(response.data.html);
        $('#tiptip_holder').hide();
        window.print();
      }
      );
    },
    /**
     * Включает режим редактирования заказа
     */
    enableEditor: function () {
      var id = $(".save-button").attr('id');
      var number = $(".save-button").attr('data-number');
      if (id) {
        $('.add-order-table-icon').text(lang.EDIT_ORDER_9 + ' №' + number + ' от ' + $('tr[order_id=' + id + '] .add_date').text());
      } else {
        $('.add-order-table-icon').text(lang.EDIT_ORDER_10);
      }
      $(".order-edit-display").show();
      $(".order-edit-visible").hide();
      $("#orderStatus").addClass('edit-layout');
      var date = $("#delivery :selected").data('date');
      if (date == 1) {
        $('.delivery-date').show();
      }
      else {
        $('.delivery-date').hide();
      }
      $("#customer").change();
      includeJS(mgBaseDir + '/mg-core/script/jquery.maskedinput.min.js');
      //$("input[name=phone]").mask("+7 (999) 999-99-99");
      //$("input[name=phone]").mask("+38 (999) 999-99-99");
      $('#delivery').on('change', function () {
        $('.delivery-date').hide();
        if ($("#delivery :selected").data('date') == 1) {
          $('.delivery-date').show();
        }
        ;
      })
    },
    /**
     * Пересчет цены товара аяксом в форме добавления заказа.
     */
    refreshPriceProduct: function () {
      var request = $('.property-form').formSerialize();
      $('.orders-table-wrapper .property-form .addToCart').css('visibility', 'hidden');
      // Пересчет цены.        
      $.ajax({
        type: "POST",
        url: mgBaseDir + "/product",
        data: "calcPrice=1&" + request,
        dataType: "json",
        cache: false,
        success: function (response) {
          if ('success' == response.status) {
            //$('#order-data .product-block .price-sp').text(response.data.price_wc);
            $('#order-data .product-block .price-sp').text(Math.round(response.data.real_price*100)/100);            
            $('#order-data .product-block .code-sp').text(response.data.code);
            $('#order-data .product-block .weight-sp').text(response.data.weight);
            $('#order-data .product-block .count-sp').text(response.data.count=='-1' ? '∞' : response.data.count);
            $('.orders-table-wrapper .property-form .addToCart').css('visibility', 'visible');
          }
        }
      });
    },
    /**
     * Клик по найденым товарам поиске в форме добавления заказа
     */
    viewProduct: function (elementIndex) {
      $('.search-block .errorField').css('display', 'none');
      $('.search-block input.search-field').removeClass('error-input');
      var product = admin.searcharray[elementIndex];
  
      if (!product.category_url) {
        product.category_url = 'catalog';
      }

  
      var html = '<a href="javascript:void(0)" class="custom-btn clear-product"><span>' + lang.EDIT_ORDER_15 + '</span></a><div class="clear"></div>\
          <div class="image-sp"><img src="' + product.image_url + '"></div>';
      html +=
        '<div class="product-info"><div class="title-sp">' +
        '<a href="' + mgBaseDir + '/' + product.category_url + "/" + product.url +
        '" data-url="' + product.category_url +
        "/" + product.url + '" class="url-sp" target="_blank">' +
        product.title + '</a>' +
        '</div>';
      html += '<div class="id-sp" style="display:none" data-set='+product.notSet+'>' + product.id + '</div>';
      html += '<div class="price-line">' + lang.PRICE_PRODUCT + ' <span class="price-sp">' + Math.round(product.price_course*100)/100 + '</span>';
      html += '<span class="currency-sp"> ' + product.currency + '</span></div>';
      html += '<div class="code-line">' + lang.CODE_PRODUCT + ' <span class="code-sp">' + product.code + '</span></div>';
      html += '<div class="weight-line">' + lang.REMAIN + ' <span class="count-sp">' + (product.count=='-1' ? '∞' :product.count) + '</span></div>';
      html += '<div class="weight-line">' + lang.WEIGHT + ' <span class="weight-sp">' + product.weight + '</span></div>';
      html += '<div class="form-sp">' + product.propertyForm + '</div>';
      html += '</div>';
      html += '<div class="desc-sp">' + product.description + '</div>';
      html += '<div class="clear"></div>';
      $('#order-data .product-block').html(html);
      $('input[name=searchcat]').val('');
      $('.fastResult').hide();
    },
    /**
     * Добавляет товар в заказ
     */

    addToOrder: function (obj) {
      if ($('#add-order-wrapper .save-button').attr('id')&& !$('#order-data .id-sp').data('set')) {
        admin.indication('error', 'Невозможно добавить комплекты товаров при редактировании, создайте новый заказ для добавления в него комплектов');
        return false;
      }
      $('.search-block .errorField').css('display', 'none');
      $('.search-block input.search-field').removeClass('error-input');

      var max_count_in_order = $('#max-count-cart').text();
      var count_in_order = $('#orderContent tr').length + 1;
      if (count_in_order > max_count_in_order) {
        admin.indication('error', 'Превышено максимальное количество позиций в заказе' + ' [max =' + max_count_in_order + ']');
        return false;
      }
      var count = $('#order-data .count-sp').text();
      if (count == '0') {
        admin.indication('error', 'Данного товара нет в наличии');
        return false;
      }
      var maxCount = (count == '-1'|| count == '∞') ? -1 : parseInt(count) - 1;
      
      
      // Собираем все выбранные характеристики для записи в заказ.
      var prop = order.getPropPosition(obj);
    
      var variant = $('.block-variants tr td input:checked').val();
      variant  = variant ? variant : 0; 
      var position = {
        order_id: $('#order-data .id-sp').text(),
        id: $('#order-data .id-sp').text(),
        title: '<a href="' + mgBaseDir + '/' + $('#order-data .url-sp').data('url') + '" data-url="' + $('#order-data .url-sp').data('url') + '" class="href-to-prod"><span class="titleProd">' + $('#order-data .title-sp').text() + ' ' + admin.trim($('.property-form input[name=variant]:checked').parents('tr').find("label").text()) + '</span></a>' + '<span class="property">' + prop + '</span>',
        prop: prop,
        code: $('#order-data .code-sp').text(),
        weight: $('#order-data .weight-sp').text(),
        price: $('#order-data .price-sp').text(),
        count: 1,
        summ: $('#order-data .price-sp').text().replace(/,/, '.').replace(/\s/, ''),
        url: $('#order-data .url-sp').data('url'),
        image_url: $('#order-data .image-sp img').attr('src'),
        fulPrice: $('#order-data .price-sp').text().replace(/,/, '.').replace(/\s/, ''),
        variant: variant,
        maxCount: maxCount, 
        notSet: $('#add-order-wrapper .save-button').attr('id') ? $('#order-data .id-sp').data('set') : true,
      };

      var row = order.createPositionRow(position);
      var update = false;

      // сравним добавляемую строку с уже имеющимися, возможно нужно только увеличить количество
      $('.status-table tbody#orderContent tr').each(function (i, element) {
        var title1 = $(this).find('.title').html().replace("<br>", "").replace(/\s/gi, "");
        var title2 = position.title.replace("<br>", "").replace(/\s/gi, "");

        if ($(this).data('id') == position.id && title1 == title2) {
          var count = $(this).find('.count input').val();
          $(this).find('.count input').val(count * 1 + 1);
          var max = parseInt ($(this).find('.count input').data('max'));
          if ((count * 1 + 1) > max + 1 && (max > 0)) {
            $(this).find('.count input').val(max + 1);
          }
          update = true;
        }
      });

      // если не обновляем, то добавляем новую строку
      if (!update) {
        $('.status-table tbody#orderContent').append(row);
      }

      order.calculateOrder();
      $('.fastResult').hide();
      $('input[name=searchcat]').val('');
    },
    //Клонирование заказа
    cloneOrder: function (id) {
      // получаем с сервера все доступные пользовательские параметры
      admin.ajaxRequest({
        mguniqueurl: "action/cloneOrder",
        id: id
      },
      function (response) {
        admin.indication(response.status, response.msg);
        admin.refreshPanel();
      }
      );
    },
  }
})();

// инициализациямодуля при подключении
order.init();
