/**
 * Модуль работы с валютами
 */
var currency = (function() {

  var savedDataRow = {}; // данные редактируемой строки
  var cansel = false; // использовать возврат значений при отмене

  return {
    init: function() {

      // редактирование
      $('body').on('click', '#tab-currency-settings .edit-currency ', function() {
         currency.editRow();
  
      });

      // сохранение
      $('body').on('click', '#tab-currency-settings .save-currency ', function() {
        $('.currency-field').hide();      
        currency.save();
      });
     
      // добавление новой валюты.
      $('.admin-center').on('click', '#tab-currency-settings .add-new-currency', function(){
        currency.addRow();
      });
      
       // удаление 
      $('body').on('click', '#tab-currency-settings .delete-row', function() {
        $(this).parents('tr').remove();
      });



    },
            
    editRow: function() {
       $('.currency-field').show();
       $('.view-value-curr').hide();  
       $('.currency-tbody .none-edit .currency-field').hide();   
       $('.currency-tbody .none-edit .view-value-curr').show();   
       $('.currency-tbody .none-edit input[name=currency_short]').show();   
       $('.currency-tbody .none-edit input[name=currency_short]').parent('td').find('.view-value-curr').hide();   
    },
            
    addRow: function() {
      var row = '<tr data-iso="NEW">\
                  <td data-iso="">\
                    <input type="text" name="currency_iso" value="" class="currency-field" style="display:none">\
                  </td>\
                  <td class="currency-rate">\
                    <input type="text" name="currency_rate" value="" class="currency-field" style="display:none">\
                  </td>\
                  <td class="currency-short">\
                    <input type="text" name="currency_short" value="" class="currency-field" style="display:none">\
                  </td>\
                  <td class="actions">\
                    <ul class="action-list">\
                      <li class="delete-row" id=""><a class="tool-tip-bottom" href="javascript:void(0);"></a></li>\
                    </ul>\
                  </td>\
                </tr>';
      $('.currency-tbody').prepend(row);
      currency.editRow();     
    },
     
    // сохраняет все валюты и их соотношения
    save: function() {
      var data = [];
      $('.currency-tbody tr').each(function(index, row) {
        var pack = {
          iso: $(row).find('input[name=currency_iso]').val(),
          rate: $(row).find('input[name=currency_rate]').val().replace(/,/, '.').replace(/[^\.0-9]+/, ''),
          short: $(row).find('input[name=currency_short]').val()
        };
        data.push(pack);
      });

      // получаем с сервера все доступные пользовательские параметры
      admin.ajaxRequest({
        mguniqueurl: "action/saveCurrency",
        data: data
      },
      function(response) {    
        admin.indication(response.status, response.msg);
        admin.refreshPanel()
      }
      );
    
    }

  }
})();

// инициализация модуля при подключении
currency.init();