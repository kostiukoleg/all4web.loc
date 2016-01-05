/**
 * Модуль для  раздела "Статистика".
 */

var statistics = (function () {
  return {   

    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init: function() {       
       
      $('.admin-center').on('click', '.section-statistics .apply-period-stat', function(){
        $('.section-statistics input[name="to-date-stat"]').val(); 
        $('.section-statistics input[name="from-date-stat"]').val();   
        admin.ajaxRequest({
          mguniqueurl: "action/getOrderPeriodStat",           
          from_date_stat: $('.section-statistics input[name="from-date-stat"]').val(),
          to_date_stat: $('.section-statistics input[name="to-date-stat"]').val()
        },
        function(response) {          
          var html ='\
            <li class="all-orders-noclosed">Незакрытые заказы: <span>'+response.data.noclosed+'<span> шт.</li>\
            <li class="all-orders">Закрытые заказы: <span>'+response.data.orders+'<span> шт.</li>\
            <li class="all-summ">Заработано: <span>'+response.data.summ+' '+admin.CURRENCY+'<span></li>\
            <li class="all-users">Общее количество покупателей: <span>'+response.data.users+'<span> шт.</li>\
            <li class="all-products">Общее количество товаров в каталоге: <span>'+response.data.products+'<span> шт.</li>\
          ';
          $('.indicators').html(html);
        }
       );
      });       
      
      includeJS(admin.SITE+'/mg-core/script/highstock.js');                 
    },
    
    
    /**
     * Получает данные с сервера о статистике заказов
     */
    getOrderStat: function() {  
       admin.ajaxRequest({
          mguniqueurl: "action/getOrderStat",  
        },
        function(response) {
          statistics.updateGraph(response.data);
        }
       );
    },
            
    updateGraph: function(data) {   
      // Create the chart
      $('#container').highcharts('StockChart', {
      
        rangeSelector : {
          selected : 1
        },

        title : {
          text : 'График заказов за каждый день начиная с открытия магазина'
        },

        series : [{
          type: 'column',
          name : 'Заказов',
          data : data,
          tooltip: {
            valueDecimals: 2
          }
        }]
      });
    },    
    
     /**
     * Получает данные с сервера о статистике заказов
     */
    startFunction: function() {  
       $('.section-statistics input[name="to-date-stat"]').datepicker({ dateFormat: "dd.mm.yy" }); 
       $('.section-statistics input[name="from-date-stat"]').datepicker({ dateFormat: "dd.mm.yy" });    
       statistics.getOrderStat();
    },
    
    }

})();

// инициализациямодуля при подключении
statistics.init();