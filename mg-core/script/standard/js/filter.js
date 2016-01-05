// список отложенных функций, выполняемых после фильтрации аяксом.
// Пример использования в сторонних JS:
// AJAX_CALLBACK_FILTER = [
//        {callback: 'settings.closeAllTab', param: null},
//        {callback: 'settings.openTab', param: ['tab-system']},
// ];
var AJAX_CALLBACK_FILTER = [];
var VIEW_ALL_FILTER = -1;

$(document).ready(function() {

  function mgInitFilter() {
    $("#price-slider").slider({
      min: $("input#minCost").data("fact-min"),
      max: $("input#maxCost").data("fact-max"),
      values: [$("input#minCost").val(), $("input#maxCost").val()],
      step: 10,
      range: true,
      stop: function(event, ui) {
        $("input#minCost").val($("#price-slider").slider("values", 0));
        $("input#maxCost").val($("#price-slider").slider("values", 1));
        getFilteredItems($('.filter-form #maxCost'));
      },
      slide: function(event, ui) {
        $("input#minCost").val($("#price-slider").slider("values", 0));
        $("input#maxCost").val($("#price-slider").slider("values", 1));
      }
    });

    $("input#minCost").change(function() {
      var value1 = $("input#minCost").val();
      var value2 = $("input#maxCost").val();

      if (parseInt(value1) > parseInt(value2)) {
        value1 = value2;
        $("input#minCost").val(value1);
      }
      $("#price-slider").slider("values", 0, value1);
    });

    $("input#maxCost").change(function() {
      var value1 = $("input#minCost").val();
      var value2 = $("input#maxCost").val();

      if (parseInt(value1) > parseInt(value2)) {
        value2 = value1;
        $("input#maxCost").val(value2);
      }
      $("#price-slider").slider("values", 1, value2);
    });


    // Собираем слайдер с ползунками для всех характеристик #ДОБАВЛЕНО
    $(".mg-filter-item .mg-filter-prop-slider").each(function(i) {

      var min = parseInt($(this).data("min"));
      var max = parseInt($(this).data("max"));

      var fMin = (parseInt($(this).data("factmin"))) ? parseInt($(this).data("factmin")) : min;
      var fMax = (parseInt($(this).data("factmax"))) ? parseInt($(this).data("factmax")) : max;

      var sliderEl = $(this);
      var minInput = $("input#Prop" + $(this).data("id") + "-min");
      var maxInput = $("input#Prop" + $(this).data("id") + "-max");
      var step = max / 10;

      // Создаем ползунок
      $(this).slider({
        min: min,
        max: max,
        values: [fMin, fMax],
        step: 1,
        range: true,
        stop: function(event, ui) {
          minInput.val(sliderEl.slider("values", 0));
          maxInput.val(sliderEl.slider("values", 1));
          getFilteredItems(maxInput);
        },
        slide: function(event, ui) {
          minInput.val(sliderEl.slider("values", 0));
          maxInput.val(sliderEl.slider("values", 1));
        }
      });

      // Создаем крючок для ввода из полей
      minInput.change(function() {
        var value1 = minInput.val();
        var value2 = maxInput.val();

        // Если значение ускакало за пределы
        if (parseInt(value1) > parseInt(value2)) {
          value1 = value2;
          minInput.val(value1);
        }
        sliderEl.slider("values", 0, value1);
        getFilteredItems(maxInput);
      });

      maxInput.change(function() {
        var value1 = minInput.val();
        var value2 = maxInput.val();

        if (parseInt(value1) > parseInt(value2)) {
          value2 = value1;
          maxInput.val(value2);
        }
        sliderEl.slider("values", 1, value2);
        getFilteredItems(maxInput);
      });
    });

  }

  mgInitFilter();

  $('body').on('click', '.mg-filter-item .mg-viewfilter', function() {
    $(this).parents('ul').find('li').fadeIn();
    $(this).hide();
  });

  $('body').on('click', '.mg-viewfilter-all', function() {
    $(this).hide();
    $('.mg-filter-item').fadeIn();
    VIEW_ALL_FILTER = -1 * VIEW_ALL_FILTER;
  });



  $('body').on('click', '.mg-filter-item input[type=checkbox]', function() {

    getFilteredItems($(this));
  });

  $('body').on('change', '.mg-filter-item select', function() {
    getFilteredItems($(this));
  });


  $('body').on('change', '.filter-form #maxCost', function() {
    getFilteredItems($(this));
  });

  $('body').on('change', '.filter-form #minCost', function() {
    getFilteredItems($(this));
  });

  $('body').on('change', '.filter-form select[name=sorter]', function() {
    $('.filter-form').submit();
  });

  /**
   * 
   * @param {type} object - объект который иницировал новый поиск, нужен для расчета офсета
   * @param {type} page - страница
   * @returns {undefined}
   */
  function getFilteredItems(object, page, sort) {
    if (location.origin) {
      var uri = location.origin + location.pathname;
    } else {
      var uri = location.protocol + '//' + location.hostname + location.pathname;
    }

    var printToLeft = true; // установить в false если нужно выводить внутри блока

    var offset = object.offset();
    $('.mg-filter-head .filter-preview span').hide();
    $('.mg-filter-head .filter-preview .loader-search').fadeIn();
    $('.mg-filter-head .filter-preview').show();
    $('.mg-filter-head .filter-preview').css('top', offset.top + 'px');
    var leftMargin = $('.mg-filter-head').css('width').slice(0, -2);
    var blockLeft = $('.mg-filter-head').offset().left;
    leftMargin = blockLeft + leftMargin * 1;

    if (!printToLeft)
      leftMargin = leftMargin - $('.mg-filter-head').css('width').slice(0, -2);

    $('.mg-filter-head .filter-preview').css('left', leftMargin + 'px');
    var packedData = $('.filter-form').serialize();
    var autoUpdate = $('.filter-form').data('print-res');
    if (!autoUpdate) {
      $.ajax({
        type: "GET",
        url: uri,
        data: packedData + '&filter=1',
        dataType: 'html',
        success: function(response) {
          $('.mg-filter-head .filter-preview').fadeOut();
          var productContainer = $(response).find('.products-wrapper').html();
          $('.products-wrapper').fadeOut();
          if ($(response).find('.product-wrapper').length == 0) {
            $('.products-wrapper').html('<div class="mg-filter-empty"><span>Не нашлось подходящих товаров!</span></div>').fadeIn();
          } else {
            $('.products-wrapper').html(productContainer).fadeIn();
          }

          var filterForm = $(response).find('.filter-form').html();
          $('.filter-form').fadeOut();
          $('.filter-form').html(filterForm).fadeIn();
          mgInitFilter();
          if (VIEW_ALL_FILTER == 1) {
            $('.mg-viewfilter-all').hide();
            $('.mg-filter-item').fadeIn();
          }

        },
        complete: function() {
          // выполнение стека отложенных функций после AJAX вызова       
          if (AJAX_CALLBACK_FILTER) {
            //debugger;
            AJAX_CALLBACK_FILTER.forEach(function(element, index, arr) {
              eval(element.callback).apply(this, element.param);
            });

          }
        }
      });
    } else {
      $.ajax({
        type: "GET",
        url: uri,
        data: packedData + '&filter=1&getcount=1',
        dataType: 'html',
        success: function(response) {
          var html = 'Выбрано товаров: ' + response + ' шт. <a href="' + uri + '?' + packedData + '&filter=1">Показать</a>';
          $('.mg-filter-head .filter-preview .loader-search').fadeOut();
          $('.mg-filter-head .filter-preview span').html(html).fadeIn();
        }
      });
    }
  }

  // клик вне блока с количеством найденных товаров
  $(document).mousedown(function(e) {
    var container = $('.mg-filter-head .filter-preview');
    if (container.has(e.target).length === 0) {
      container.hide();
    }
  });


  $(".price-slider-list input[type=text]").change(function() {
    if (isNaN(parseFloat($(this).val()))) {
      $(this).val('1');
    }
  });


}); 