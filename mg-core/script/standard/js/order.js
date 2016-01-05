$(document).ready(function() {

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
  $('.delivery-date input[name=date_delivery]').datepicker({dateFormat: "dd.mm.yy", minDate: 0});



  if ($('input[name=toOrder]').prop("disabled")) {
    disabledToOrderSubmit(true);
  }

  if ($('.delivery-details-list input[name=delivery]:checked').val()) {
    disabledToOrderSubmit(false);

  }

  if ($('.payment-details-list input[name=payment]:checked').val()) {
    disabledToOrderSubmit(false);
  }
  var dataDelivery = $('.delivery-details-list input[name=delivery]:checked').parent().attr('data-delivery-date');
  if (dataDelivery == '1') {
    $('.delivery-date').show();
  }

  // действия при оформлении заказа
  $('body').on('click', '.delivery-details-list input', function() {
    $("p#auxiliary").html('');
    $('.delivery-details-list input[name=delivery]').parent().addClass('noneactive');
    $('.delivery-details-list input[name=delivery]').parent().removeClass('active');

    $('.delivery-details-list input[name=delivery]:checked').parent().removeClass('noneactive');
    $('.delivery-details-list input[name=delivery]:checked').parent().addClass('active');
    if ($('.delivery-details-list li .active').data('delivery-date') == '1') {
      $('.delivery-date').show();
    }
    else {
      $('.delivery-date').hide();
    }
    var deliveryId = $('.delivery-details-list input[name=delivery]:checked').val();

    $('.payment-details-list').before('<div class="loader"></div>');
    disabledToOrderSubmit(true);
    $('.summ-info .delivery-summ').html('');
    $.ajax({
      type: "POST",
      url: mgBaseDir + "/order",
      data: {
        action: "getPaymentByDeliveryId",
        deliveryId: deliveryId,
        customer: $('.form-list select[name="customer"]').val()
      },
      dataType: "json",
      cache: false,
      success: function(response) {
        var paymentTable = response.paymentTable;
        if ('' == paymentTable || null == paymentTable) {
          paymentTable = 'нет доступных способов оплаты';
          disabledToOrderSubmit(false);
        }
        $('.payment-details-list').html(paymentTable);
        $('.loader').remove();
        $('.payment-details-list input[name=payment]').prop("checked", false);
        if ($('.payment-details-list input[name=payment]').length == 1) {
          disabledToOrderSubmit(false);
          $('.payment-details-list input[name=payment]').prop("checked", true);
        }
        if (response.summDelivery) {
          $('.summ-info .delivery-summ').html('+ доставка: <span class="order-summ">' + response.summDelivery + ' </span> ');
        }
      }
    });

  });

  $('.form-list select[name="customer"]').change(function() {
    if ($(this).val() == 'fiz') {
      $('.form-list.yur-field').hide();
      $('.payment-details-list input[name=payment]').parents('li').show();
      $('.payment-details-list input[name=payment][value=7]').parents('li').hide();
    }
    if ($(this).val() == 'yur') {
      $('.form-list.yur-field').show();
      $('.payment-details-list input[name=payment]').parents('li').hide();
      $('.payment-details-list input[name=payment][value=7]').parents('li').show();
    }

    $('.delivery-details-list input[name=delivery]:checked').click();

  });


  $('body').on('click', '.payment-details-list input[name=payment]:checked', function() {
    $("p#auxiliary").html('');
    $('.payment-details-list input[name=payment]').parent().addClass('noneactive');
    $('.payment-details-list input[name=payment]').parent().removeClass('active');
    $('.payment-details-list input[name=payment]:checked').parent().removeClass('noneactive');
    $('.payment-details-list input[name=payment]:checked').parent().addClass('active');
    disabledToOrderSubmit(false);
  });

  function disabledToOrderSubmit(flag) {
    if (!flag) {
      $('input[name=toOrder]').prop("disabled", false);
      $('input[name=toOrder]').removeClass('disabled-btn');
    } else {
      $('input[name=toOrder]').prop("disabled", true);
      $('input[name=toOrder]').addClass('disabled-btn');
    }
  }
}); 