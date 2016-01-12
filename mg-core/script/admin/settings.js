/**
 * Модуль для  раздела "Настройки".
 */

var settings = (function () {
  return {
    codeEditor: null,
         
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
    */
    init: function() {

      // переход во вкладку "Магазин"
      $('.admin-center').on('click', '.section-settings #tab-shop', function(){    
        settings.closeAllTab();
        settings.openTab($(this).attr('id'));
      });

      // переход во вкладку "Система"
      $('.admin-center').on('click', '.section-settings #tab-system', function(){        
        settings.closeAllTab();
        settings.openTab($(this).attr('id'));
      });
      
      // переход во вкладку "шаблон"
      $('.admin-center').on('click', '.section-settings #tab-template', function(){        

        includeJS(mgBaseDir+'/mg-core/script/codemirror/lib/codemirror.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/javascript/javascript.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/xml/xml.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/php/php.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/css/css.js');
        includeJS(mgBaseDir+'/mg-core/script/codemirror/mode/clike/clike.js');

        settings.closeAllTab();
        // выбираем первый в наборе файл 
        $('.file-template').eq(0).click();    
        settings.openTab($(this).attr('id'));        
      });
      
    
      
       // Выбор картинки
      $('.admin-center').on('click', '.section-settings .browseImage', function() {
        admin.openUploader(null,null,'template');
      }); 
      
      
      // сворачиваем все вкладки с настройками     
     
      // клик по заголовкам настроек в первой вкладке
      $('.admin-center').on('click', '.section-settings .group-property h3', function(){    
      
         if($(this).parent().hasClass("open")){
             $(this).parent().removeClass("open");
             $(this).next().slideUp("fast");
         }
         else{
             $('.group-property .group-property-list').slideUp("fast");
             $('.group-property .group-property-list').parent().removeClass("open");
             $(this).next().slideDown("fast");
             $(this).parent().addClass("open");
         }
       });

      // клик по кнопкам файлов шаблона, загружает  содержание файла с сервера
      $('.admin-center').on('click', '.section-settings .file-template', function(){
        $('.save-file-template').data('editfilename', $(this).data('path'));
        $('.file-template').removeClass('editing-file');
        $(this).addClass('editing-file');
        var path = $(this).data('path');
        admin.ajaxRequest({
          mguniqueurl: "action/getTemplateFile",   
          path: path,       
        },
        
        function(response) {         
          $('.CodeMirror').remove();
          $('.save-file-template').hide();     
          // каждому файлу свою схему
          if(response.status!="error"){ 
            var mode = "application/x-httpd-php";
            if(path=="/css/style.css"){     
              mode = "text/css";
            }
             if(path=="/js/script.js"){     
              mode = "text/javascript";
            }
        
            $('#codefile').val(response.data.filecontent);            
            codeEditor = CodeMirror.fromTextArea(document.getElementById("codefile"), {
              lineNumbers: true,           
              mode: mode,
            });       
            $('.error-not-tpl').hide();
            $('.save-file-template').show();
          }else{
            $('.error-not-tpl').show();
          }
        }
        
        );
      });      
      
      $('.admin-center').on('click', '.section-settings #tab-currency', function(){
        includeJS(mgBaseDir+'/mg-core/script/admin/currency.js');   
        settings.closeAllTab();
        settings.openTab($(this).attr('id'));    
      });
      
      $('.admin-center').on('click', '.section-settings #interface', function(){
        settings.closeAllTab();
        settings.openTab($(this).attr('id'));
      });

      $('.admin-center').on('click', '.section-settings #tab-userField', function(){
        settings.closeAllTab();
        settings.openTab($(this).attr('id'));     
        userProperty.print();
      });
      
      // при выборе категории переформировать талицу характеристик
      $('.admin-center').on('change', '.section-settings #tab-userField-settings select[name=cat_id]', function(){      
        var cat_id = $(this).val();             
        userProperty.print(cat_id,true);
      });
      
      $('.admin-center').on('click', '.section-settings #tab-deliveryMethod', function(){
        settings.closeAllTab();
        settings.openTab($(this).attr('id'));
        //settings.getDeliveryArray();
        settings.updataTabs();
        admin.sortable('.deliveryMethod-tbody','delivery');
        
      });

      $('.admin-center').on('click', '.section-settings #tab-paymentMethod', function(){
        settings.closeAllTab();
        settings.openTab($(this).attr('id'));
        //settings.getPaymentTable();
        settings.updataTabs();
        admin.sortable('.paymentMethod-tbody','payment');
      });

      $('.admin-center').on('change', '.section-settings  input[name="staticMenu"]', function(){
        admin.fixedMenu($(this).val()=='false'?'true':'false');
      });
      
       $('.admin-center').on('click', '.section-settings .edit-key', function(){
        $('.section-settings input[name="licenceKey"]').fadeIn();
        $('.section-settings .save-settings-system').fadeIn();        
        $(this).hide();       
      });
      
      
      // для клика по чекбоксу - закрытия сайта от посетителей, особый обрабочик
      $('.admin-center').on('change', '.section-settings  .downtime-check', function(){        
        var tabName = $(this).parents('.main-settings-container').attr('id');      
        
        var obj ={downtime: "false"};
        if($(this).prop('checked')){
          obj ={downtime: "true"};
        }        
           
        admin.ajaxRequest({
          mguniqueurl: "action/editSettings",
          options: obj
        },
        function(response) {
          admin.indication(response.status, response.msg);        
          $('.tabs-content').animate({opacity: "hide"}, 1000);
          $('.tabs-content').animate({opacity: "show"}, "slow");
          admin.refreshPanel();
        }
       );
      });
      
      
      
      $('.admin-center').on('change', '.section-settings  select[name="cacheMode"]', function(){
        $('.memcache-conection').hide();	
        $('input[name="cacheHost"]').parent('li').hide();
        $('input[name="cachePort"]').parent('li').hide();

        if($(this).val()=="MEMCACHE"){
          $('.memcache-conection').show();	
          $('input[name="cacheHost"]').parent('li').show();	
          $('input[name="cachePort"]').parent('li').show();		 
        };	
      });
      
       $('.admin-center').on('change', '.section-settings .minify-css-and-js', function(){        
        if($(this).prop('checked')){
          $('.create-images-for-css-cache').show(); 
          $('.warning-create-images').show();
        }else{
          $('.create-images-for-css-cache').hide();	
          $('.warning-create-images').hide();
        }
      });
      
      $('.admin-center').on('change', '.section-settings  select[name="templateName"]', function(){
        settings.drawColorShemes($(this).find('option:selected').data('schemes'));
        $('.default-info').hide();   
        if($(this).val()=='default'){
          $('.default-info').show();            
        }        
      });
      
	 
      $('.admin-center').on('click', '.section-settings .save-settings', function(){
        var tabName = $(this).parents('.main-settings-container').attr('id');       
        admin.ajaxRequest({
          mguniqueurl: "action/editSettings",
          options: settings.getAllSetting(tabName)
        },
        function(response) {
          admin.indication(response.status, response.msg);
          settings.checkValidKey();
          $('.tabs-content').animate({opacity: "hide"}, 1000);
          $('.tabs-content').animate({opacity: "show"}, "slow");
          admin.refreshPanel();
        }
       );
      });
      
     
        // Выбор цветовой схемы шаблона
      $('.admin-center').on('click', '.section-settings .color-scheme', function(){ 
        $(this).parents('ul').find('.color-scheme').removeClass('active');
        $(this).addClass('active');
      });
      
      // установка сединения с memcache
      $('.admin-center').on('click', '.section-settings .memcache-conection', function(){           
	     admin.ajaxRequest({
          mguniqueurl: "action/testMemcacheConection", 
          host: $('input[name=cacheHost]').val(),
          port: $('input[name=cachePort]').val()
        },
        function(response) {
          admin.indication(response.status, response.msg);        
        });
      });
      
      // Выбор картинки для логотипа сайта
      $('.admin-center').on('click', '.section-settings  .browseImageLogo', function() {
        admin.openUploader('settings.getFile');       
      });     
      // Выбор картинки для фона сайта
      $('.admin-center').on('click', '.section-settings  .browseBackgroundSite', function() {
        admin.openUploader('settings.getBackground');       
      });
     
       // Открытие шаблонов писем 
      $('.admin-center').on('click', '.section-settings  .open-email-views', function() {      
        $('.template-tabs .active').removeClass('active');
        $(this).addClass('active');
        $('.file-template').hide();
        $('.tab-email-views').show();
      });    
      
       // Открытие шаблонов писем 
      $('.admin-center').on('click', '.section-settings  .open-email-layout', function() {
        $('.template-tabs .active').removeClass('active');
        $(this).addClass('active');
        $('.file-template').hide();
        $('.tab-email-layout').show();
      });    
      
        // Открытие шаблонов блоков 
      $('.admin-center').on('click', '.section-settings  .open-block-layout', function() {
        $('.template-tabs .active').removeClass('active');
        $(this).addClass('active');
        $('.file-template').hide();
        $('.tab-block-layout').show();
      }); 
           
       // Открытие шаблонов печати 
      $('.admin-center').on('click', '.section-settings  .open-print-layout', function() {
        $('.template-tabs .active').removeClass('active');
        $(this).addClass('active');
        $('.file-template').hide();
        $('.tab-print-layout').show();
      }); 
      
      // Обработчик для загрузки нового шаблона
      $('body').on('change', '#addTempl', function(){
        settings.addNewTemplate();
      });

      // сохранение файла шаблона
      $('.admin-center').on('click', '.section-settings .save-file-template', function(){
        var filename = $(this).data('editfilename');
        content = codeEditor.getValue();
        
        admin.ajaxRequest({
          mguniqueurl: "action/saveTemplateFile", 
          content: content,
          filename: filename
        },
        function(response) {
          admin.indication(response.status, response.msg);        
        });
      });
      
      
      //Обработка  нажатия кнопки проверить версию
      $('.admin-center').on('click', '.clearLastUpdate', function(){         
        admin.ajaxRequest({
          mguniqueurl: "action/clearLastUpdate",         
        },
        function(response) {
          //admin.indication(response.status, response.msg);
		  window.location = mgBaseDir+"/mg-admin/";
        }
        );
      });
      
    //Обработка  нажатия кнопки Приступить к обновлению
      $('.admin-center').on('click', '.update-now', function(){
       $(".loading-update-step-1").show();       
       $(".step-1-info").hide();
   
       var buttonDownload = $(this);
       buttonDownload.hide();
        
       $('.start-update').hide();
       $(".step-process-info").show();
       $(".step-process-info").text(lang.WHAITING_UPDATE);
       $('.step-eror-info').hide();
        var version = $("#lVer").text();
        
        admin.ajaxRequest({
          mguniqueurl: "action/preDownload",
          version: version
        },
        
        function(response) {
   
         // response.status = "success";
          $(".loading-update-step-1").hide();
          $(".step-process-info").hide();     
          $(".step-1-info").show();
          
          if('error'==response.status){
            admin.indication(response.status, response.msg);            
    
            $('.step-eror-info').show();
            $('.step-eror-info').text(response.msg);
            buttonDownload.show();  
            $('.start-update').show();
            
          } else{
              
            admin.indication(response.status, response.msg);
            
            $('.step-update-li-1').addClass('current');
            $('.step-update-li-1').addClass('completed');
            $('.step-update-li-2').removeClass('current');              
            $('.update-archive').show();           
            $("#lVer").html(version);
            $(".step-block .step1").hide();
            $(".step-block .step2").show();
            
          }  
        admin.initToolTip();
        }
        );
          
      });

      //Обработка  нажатия кнопки Установить обновление
      $('.admin-center').on('click', '.update-archive', function(){
 
       var version = $("#lVer").text();
       $(".loading-update-step-2").show();
       $(".step-2-info").hide();
       var buttonArchive = $(this);
       buttonArchive.hide();  
       $(".step-process-info").show();       
       $(".step-process-info").text('Идет процесс применение изменений...');
    
        admin.ajaxRequest({
          mguniqueurl: "action/postDownload",
          version: version
        },
        function(response) {
          admin.indication(response.status, response.msg);   
          $(".loading-update-step-2").hide();
           if('error'==response.status){
            admin.indication(response.status, response.msg);            
            $('.error-update').remove();           
            $('.step-eror-info').html(response.msg+ '  <a href="'+admin.SITE+'/mg-admin'+'"> Начать процесс обновления с первого шага! </a>');     
            
          } else{              
            admin.indication(response.status, response.msg);            
            $('.step-update-li-2').addClass('current');
            // $('.step-update-li-2').addClass('completed');
            $('.step-update-li-3').removeClass('current');  
            $(".step-info").hide();     
            $(".step-process-info").text('Завешения процесса обновления, страница перезагрузится через 3 секунды ...');
            $(".loading-update-step-2").show();
            setTimeout(function() { window.location = mgBaseDir+"/mg-admin/"; }, 3000)

          }  
          
          
        }
        );
        return false;
      });
      
      
      // Вызов модального окна при нажатии на кнопку добавления способа доставки.
      $('.admin-center').on('click', '#tab-deliveryMethod-settings .add-new-button', function(){
        settings.openDeliveryModalWindow('add');
      });
      
          
      // Вызов модального окна при нажатии на кнопку изменения способа доставки.
      $('.admin-center').on('click', '#tab-deliveryMethod-settings .edit-row', function(){
        settings.openDeliveryModalWindow('edit', $(this).attr('id'));
      });
      
      // Сохранение при нажатии на кнопку сохранить в модальном окне способа доставки.
      $('body').on('click', '#tab-deliveryMethod-settings .save-button', function(){
        settings.saveDeliveryMethod($(this).attr('id'));
      });
      
      // Удаление способа доставки.
      $('.admin-center').on('click', '#tab-deliveryMethod-settings .delete-row', function(){
        settings.deleteDelivery($(this).attr('id'));
      });
            
      // Вызов модального окна при нажатии на кнопку изменения способа оплаты.
      $('.admin-center').on('click', '#tab-paymentMethod-settings .edit-row', function(){
        settings.openPaymentModalWindow($(this).attr('id'));
      });
      
      // Сохранение при нажатии на кнопку сохранить в модальном окне способа оплаты
      $('body').on('change', '#tab-shop-settings .watermarkform', function(){        
        settings.addWatermark();
      });     
      
        // Сохранение при нажатии на кнопку сохранить в модальном окне способа оплаты
      $('body').on('click', '#tab-paymentMethod-settings .save-button', function(){
        settings.savePaymentMethod($(this).attr('id'));
      });
      // удаление фонового рисунка сайта
      $('.admin-center').on('click', '.section-settings .wrapp-background-img .remove-added-background', function(){
        $(this).hide();
        $('.section-settings input[name="backgroundSite"]').val('');    
        $('.section-settings .background-img img').removeAttr().hide();  
      });
       // удаление логотипа сайта
      $('.admin-center').on('click', '.section-settings .wrapp-logo-img .remove-added-logo', function(){
        $(this).hide();
        $('.section-settings input[name="shopLogo"]').val('');    
        $('.section-settings .logo-img img').removeAttr().hide();  
      })
      
    },

    /**
     * Закрывает все табы
    */
    closeAllTab: function() {
     $('.tabs-list li').removeClass('ui-state-active');
     $('.main-settings-container').css('display', 'none');
    },

    /**
     * Открывает все табы
     */
    openTab: function(tab) {
     cookie('setting-active-tab','#'+tab);
     $('#'+tab).parent('li').addClass('ui-state-active');    
     $('#'+tab+'-settings').css('display', 'block');
    },
            
     /**
     * отложенное открытие таба, применяется при перезагрузке
     */
    calbackOpenTab: function() {   
     $(cookie('setting-active-tab')).click();    
    },

    /**
     * Получает значение всех настроек в выбраном табе
     */
    getAllSetting: function(tab) {
      //собираем из таблицы все инпуты с данными, записываим их в виде нативного кода
      var obj ='{';
      $('#'+tab+' .option').each(function(){
        var val = $(this).val();
       // исключение для кодов счетчиков, т.к. в них можгут встретиться запрещенные символы
       if($(this).attr('name')!='widgetCode' && $(this).attr('name')!='shopName'){ 
         obj+='"'+$(this).attr('name')+'":"'+admin.htmlspecialchars(val)+'",';
       }else{
         obj+='"'+$(this).attr('name')+'":"",';
       }
      });
      obj+='}';
      
   
      obj=eval("(" + obj + ")");
      
      if(tab == "tab-shop-settings" ){
        //теперь присваиваем текстовое значение объекту
        obj.widgetCode=$('textarea[name=widgetCode]').val();
        obj.shopName = $('input[name=shopName]').val();
        obj.colorScheme = $('.color-scheme.active').data('scheme');
      }      
     
      return obj;
    },
    
    checkValidKey:function() {
      if(32 == $('.licenceKey').val().length){
        $('.update-now').removeClass('opacity');
        $('.update-now').prop('disabled', false);
        $('.error-key').hide();
      }else{
        $('.update-now').addClass('opacity');
        $('.update-now').prop('disabled', true);
        $('.error-key').show();
      }
    },

    /**
     * Открывает модальное окно способа доставки.
     * type - тип окна, либо для создания нового, либо для редактирования старого.
     */
    openDeliveryModalWindow: function(type, id) {
    settings.clearFileds();   
      switch (type) {
        case 'edit':{          
          $('.deliveryMethod-table-wrapper .delivery-table-icon').text(lang.TITLE_EDIT_DELIVERY);
          $('#add-deliveryMethod-wrapper .save-button').attr("id", id);
          var paymentMethod = $.parseJSON($('tr[id=delivery_'+id+'] td#paymentHideMethod').text());
          $('input[name=deliveryName]').val($('tr[id=delivery_'+id+'] td#deliveryName').text());
          $('input[name=deliveryCost]').val($('tr[id=delivery_'+id+'] td#deliveryCost span.costValue').text());
          $('input[name=deliveryDescription]').val($('tr[id=delivery_'+id+'] td#deliveryDescription').text());
          $('input[name=free]').val($('tr[id=delivery_'+id+'] td.free .costFree').text());
          
          if(1 == $('tr[id=delivery_'+id+'] td#activity').attr('status')){
            $('input[name=deliveryActivity]').prop('checked', true);
          }
          if(1 == $('tr[id=delivery_'+id+'] td#activity').data('delivery-date')){
            $('input[name=deliveryDate]').prop('checked', true);
          }
          if($('tr[id=delivery_'+id+'] td#activity').attr('data-delivery-ymarket') === '1'){
            $('input[name=deliveryYmarket]').prop('checked', true);
          }
                   
          //выбор способов оплаты применительно к данному способу доставки
          $.each(paymentMethod, function(paymentId, active) {
            if(1 == active){
              $('#add-deliveryMethod-wrapper #paymentCheckbox input[name='+paymentId+']').prop('checked', true);
            }else{
              $('#add-deliveryMethod-wrapper #paymentCheckbox input[name='+paymentId+']').prop('checked', false);
            }
          });
          
          
          break;
        }
        case 'add':{
          $('.deliveryMethod-table-wrapper .delivery-table-icon').text(lang.TITLE_NEW_DELIVERY);
          break;
        }
        default:{
          user.clearFileds();
          break;
        }
      }

      // Вызов модального окна.
      admin.openModal($('#add-deliveryMethod-wrapper'));


    },

    /**
     * Открывает модальное окно способа оплаты.
     */
    openPaymentModalWindow: function(id) {
  
     var paramArray = JSON.parse($('tr[id=payment_'+id+'] td#paramHideArray').html());
  
      //проверка ниличия сопособов доставки для данного метода
      
      if('' != $('tr[id=payment_'+id+'] td#deliveryHideMethod').text()){
        
        var deliveryMethod = $.parseJSON($('tr[id=payment_'+id+'] td#deliveryHideMethod').text());
      }

      settings.clearFileds();
      $('.paymentMethod-table-wrapper .payment-table-icon').text(lang.TITLE_EDIT_PAYMENT);
      $('#add-paymentMethod-wrapper .save-button').attr("id", id);
      //подстановка классов иконок
      switch (id){
        case "1":
          var iconClass = 'wm_icon';
          break;
        case "2":
          var iconClass = 'ym_icon';
          break;
        case "5":
          var iconClass = 'robo_icon';
          break;
        case "6":
          var iconClass = 'qiwi_icon';
          break;
        case "8":
          var iconClass = 'sci_icon';
          break;
        case "9":
          var iconClass = 'payanyway_icon';
		  break;
		case "10":
          var iconClass = 'paymenmaster_icon';
		  break;
		case "11":
          var iconClass = 'alfabank_icon';
		  break;      
        default:
          var iconClass = 'default_icon';
      }
      $('#add-paymentMethod-wrapper span#paymentName').html('<span class="'+iconClass+'">'+'<input class="name-payment" name="name" type="text" value="'+admin.htmlspecialchars($('tr[id=payment_'+id+'] td#paymentName').text())+'">'+'</span>');
      
      if('' != $('tr[id=payment_'+id+'] td#urlArray').text()){
        var urlArray = $.parseJSON($('tr[id=payment_'+id+'] td#urlArray').text());
        var urlParam = '<span class="custom-text links-text"><strong>Ссылки для указания в сервисе '+$('tr[id=payment_'+id+'] td#paymentName').text()+':</strong></span>';
        var k=1;
        $.each(urlArray, function(name, val) {
          if(k==1){urlParam += '<p class="link-result">'}
          if(k==2){urlParam += '<p class="link-success">'}
          if(k==3){urlParam += '<p class="link-fail">'}
          urlParam += '<span>'+name+'</span>\
                      '+admin.SITE+val+'\
                    </p>';
          k++;
        });
        $('#add-paymentMethod-wrapper #urlParam').html(urlParam);
      }
      //создание списка изменения параметров для данного способа оплаты
      var input = '';
      var algorithm = new Array('md5', 'sha256', 'sha1');
       $('#add-paymentMethod-wrapper #paymentParam').html('');
     
      $.each(paramArray, function(name, val) {  
        var inpType = "text";
        if(name.indexOf('ароль') + 1){
          inpType = "password";
        }
        if(name.indexOf('екретн') + 1){
          inpType = "password";
        }
        if(name.indexOf('од проверки ') + 1){
          inpType = "password";
        }
        if(name.indexOf('естовый') + 1){
           inpType = "checkbox";
         }
         if(name.indexOf('етод шифрования') + 1){
          var options = '<option value="0">Выбрать:</option>';
          algorithm.forEach(function(arr, i, e){
            options += '<option value="'+arr+'">'+arr+'</option>';
          });
          $('#add-paymentMethod-wrapper #paymentParam').append(
            '<label>\n\
            <span class="custom-text">'+name+'</span>\n\
              <select name="'+name+'">'+options+'</select>\
          </label>');
          val = admin.htmlspecialchars_decode(val);
          $('#add-paymentMethod-wrapper #paymentParam select[name="'+name+'"]').val(val);
          return; 
        }
         
         $('#add-paymentMethod-wrapper #paymentParam').append(
          '<label>\n\
            <span class="custom-text">'+name+'</span>\n\
              <input type="'+inpType+'" name="'+name+'" class="product-name-input" value="">\n\
          </label>'
         );
        val = admin.htmlspecialchars_decode(val);
        $('#add-paymentMethod-wrapper #paymentParam input[name="'+name+'"]').val(val);
        if (inpType=='checkbox'&&val=='true') {
          $(this).attr('checked', 'checked');
          $('#add-paymentMethod-wrapper #paymentParam input[name="'+name+'"]').attr('checked', 'checked');
          }
      });

      
      // вешаем текстовый редактор на поле в реквизитах
      $('textarea[class=product-name-input]').ckeditor();  
      //ниличие сопобов доставки для данного метода
      if(!$.isEmptyObject(deliveryMethod)){
        //выбор способов доставки применительно к данному способу оплаты
        $.each(deliveryMethod, function(deliveryId, active) {
          if(1 == active){
            $('#add-paymentMethod-wrapper #deliveryCheckbox input[name='+deliveryId+']').prop('checked', true);
          }else{
            $('#add-paymentMethod-wrapper #deliveryCheckbox input[name='+deliveryId+']').prop('checked', false);
          }
        });
      }else{
        $('#add-paymentMethod-wrapper #deliveryArray').html(lang.NONE_DELIVERY);
      }
      //выбор активности данного способа оплаты
      if(1 == $('tr[id=payment_'+id+'] td#activity').attr('status')){
        $('input[name=paymentActivity]').prop('checked', true);
      }

      // Вызов модального окна.
      admin.openModal($('.b-modal#add-paymentMethod-wrapper'));

    },

   /**
    * Чистит все поля модального окна
    */
    clearFileds:function() {
      $('input').removeClass('error-input');
      $('input[name=deliveryName]').val('');
      $('input[name=deliveryCost]').val('');
      $('input[name=deliveryDescription]').val('');
      $('input[name=deliveryActivity]').prop('checked', false);
      $('input[name=deliveryDate]').prop('checked', false);
      $('input[name=deliveryYmarket]').prop('checked', false);
      $('input[name=paymentActivity]').prop('checked', false);
      $('.deliveryMethod').prop('checked', false);
      $('#add-paymentMethod-wrapper #urlParam').html('');
      $('.paymentMethod').prop('checked', false);
      $('.save-button').attr('id','');
      // Стираем все ошибки предыдущего окна если они были.
      $('.errorField').css('display','none');
    },
   /**
    * сохранение способа доставки
    */
    saveDeliveryMethod:function(id) {
      
      // Если поля не верно заполнены, то не отправляем запрос на сервер.
      if(!settings.validForm()){
        return false;
      }
      
      $('.img-loader').show();
      var status="createDelivery";
      //обрабатываем доступные методы оплаты для данного метода доставки
      var paymentMethod='{';
      
      $('#paymentCheckbox input').each(function(){
        
        if($(this).prop('checked')){
          paymentMethod += '"'+$(this).attr('name')+'":1,';
        }else{
          paymentMethod += '"'+$(this).attr('name')+'":0,';
        }
      });
      
      paymentMethod = paymentMethod.substr(0, paymentMethod.length-1); //удаляем последнюю запятую в конце списка
      paymentMethod +='}';
      
      
      if(id){
        status="editDelivery";
      }
      
      
      var deliveryName = $('input[name=deliveryName]').val();
      var deliveryCost = $('input[name=deliveryCost]').val();
      var deliveryDescription = $('input[name=deliveryDescription]').val();
      var free = $('input[name=free]').val();
      var deliveryActivity = 0;
      var deliveryDate = 0;
      var deliveryYmarket = 0;
      if($('input[name=deliveryActivity]').prop('checked')){
        deliveryActivity = 1;
      }      
      if($('input[name=deliveryDate]').prop('checked')){
        deliveryDate = 1;
      } 
      if($('input[name=deliveryYmarket]').prop('checked')){
        deliveryYmarket= 1;
      } 
      admin.ajaxRequest({
        mguniqueurl: "action/saveDeliveryMethod",
        status: status,
        deliveryName: deliveryName,
        deliveryCost: deliveryCost,
        deliveryDescription: deliveryDescription,
        deliveryActivity: deliveryActivity,
        deliveryDate: deliveryDate,
        deliveryYmarket: deliveryYmarket,
        paymentMethod: paymentMethod,
        deliveryId: id,
        free:free
      },
      function(response) {
        $('.img-loader').hide();
  
        admin.indication(response.status, response.msg);
        if('success' == response.status){
          
          var activity = '<span class="activity-product-false">'+lang.ACTYVITY_FALSE+'</span>';
          if(deliveryActivity){
            activity = '<span class="activity-product-true">'+lang.ACTYVITY_TRUE+'</span>';
          }
          
          if(id){
            response.data.id = id;
          }
          
          // обнуляем data для всех способов доставки, т.к. выбрать можно только 1 доставку для выгрузки на яндекс маркет
          if (deliveryYmarket) {
            $('.deliveryMethod-tbody tr td#activity').attr('data-delivery-ymarket', 0);
          }
          // html верстка для записи в таблицу
          var row='\
            <tr id="delivery_'+response.data.id+'">\
                <td class="deliveryId">'+response.data.id+'</td>\
                <td id="deliveryName">'+admin.htmlspecialchars(deliveryName)+'</td>\
                <td id="deliveryCost">\
                  <span class="costValue">'+deliveryCost+'</span> \
                  <span class="currency">'+admin.CURRENCY+'</span>\
                </td>\
                <td id="deliveryDescription">'+admin.htmlspecialchars(deliveryDescription)+'</td>\
                <td class="free"><span class="costFree">'+free+'</span> <span class="currency">'+admin.CURRENCY+'</span></td>\
                <td id="activity" status="'+deliveryActivity+'" data-delivery-date ="'+deliveryDate+'" data-delivery-ymarket ="'+deliveryYmarket+'">'+activity+'</td>\
                <td class="actions">\
                  <ul class="action-list">\
                    <li class="edit-row" id="'+response.data.id+'"><a href="javascript:void(0);" title="'+lang.EDIT+'"></a></li>\
                    <li class="delete-row" id="'+response.data.id+'"><a href="javascript:void(0);" title="'+lang.DELETE+'"></a></li>\
                  </ul>\
                </td>\
                <td id="paymentHideMethod" style="display: none">'+paymentMethod+'</td>\
             </tr>';
          
          if($('tr#none_delivery').length){
            $('tr#none_delivery').remove();
          }
          if(id){
            $('.deliveryMethod-tbody tr[id=delivery_'+id+']').replaceWith(row);
          }else{
            $('.deliveryMethod-tbody').append(row);
          }
          

          admin.closeModal($('.b-modal'));
        }
        
      }
      );
    },
    
   /**
    * сохранение способа оплаты
    */
    savePaymentMethod:function(id) {      
      $('.img-loader').show();
     
      //обрабатываем параметры методов оплаты
      var name = admin.htmlspecialchars($('.name-payment').val());   
      
      
      //обрабатываем параметры методов оплаты
      var paymentParam ='{';
      $('#paymentParam input,#paymentParam select').each(function(){
          if(!$(this).hasClass('name-payment')){
           // paymentParam+='"'+$(this).attr('name')+'":"'+$(this).val().replace(/\\/g, '\\\\\\\\').replace(/"/g, '\\\\$&')+'",';
           paymentParam+='"'+$(this).attr('name')+'":"'+admin.htmlspecialchars($(this).val())+'",';
          }
      });   
      
      paymentParam = paymentParam.substr(0, paymentParam.length-1); //удаляем последнюю запятую в конце списка
      paymentParam+='}';
      
      var deliveryMethod='';
      if(0 != $('#deliveryCheckbox #deliveryArray').find('input').length){
        //обрабатываем доступные методы доставки для данного метода оплаты

        deliveryMethod='{';
        $('#deliveryCheckbox input').each(function(){

          if($(this).prop('checked')){
            deliveryMethod += '"'+admin.htmlspecialchars($(this).attr('name'))+'":1,';
          }else{
            deliveryMethod += '"'+admin.htmlspecialchars($(this).attr('name'))+'":0,';
          }
        });

        deliveryMethod = deliveryMethod.substr(0, deliveryMethod.length-1); //удаляем последнюю запятую в конце списка
        deliveryMethod +='}';
      }
      //активность метода оплаты
      var paymentActivity = 0;
      if($('input[name=paymentActivity]').prop('checked')){
        paymentActivity = 1;
      }

      
      
      admin.ajaxRequest({
        mguniqueurl: "action/savePaymentMethod",
        paymentParam: paymentParam,
        deliveryMethod: deliveryMethod,
        paymentActivity: paymentActivity,
        name: name,
        paymentId: id
      },
      function(response) {
        $('.img-loader').hide();
        admin.indication(response.status, response.msg);
        if('success' == response.status){
          
          var activity = '<span class="activity-product-false">'+lang.ACTYVITY_FALSE+'</span>';
          if(paymentActivity){
            activity = '<span class="activity-product-true">'+lang.ACTYVITY_TRUE+'</span>';
          }
          
          $('tr[id=payment_'+id+'] td#paymentName').html(name);
          $('tr[id=payment_'+id+'] td#activity').attr('status', paymentActivity);
          $('tr[id=payment_'+id+'] td#activity').html(activity);         
          $('tr[id=payment_'+id+'] td#deliveryHideMethod').text(deliveryMethod);  
          $('tr[id=payment_'+id+'] td#paramHideArray').text(response.data.paymentParam);
          admin.closeModal($('.b-modal'));
        }
        
      }
      );
    },
    
    
    
    /**
     * Удаляет способ доставки из БД сайта и таблицы в текущем разделе
     */
    deleteDelivery: function(id) {
      if(confirm(lang.DELETE+'?')){
        admin.ajaxRequest({
          mguniqueurl:"action/deleteDeliveryMethod",
          id: id
        },
        (function(response) {
          admin.indication(response.status, response.msg);
          $('.deliveryMethod-tbody tr[id=delivery_'+id+']').remove();
          
          if($('#tab-deliveryMethod-settings table.main-settings-list tr').length==1){
            var row = '<tr id="none_delivery"><td class="no-delivery" colspan="6">'+lang.NONE_DELIVERY+'</td></tr>';
            $('.deliveryMethod-tbody').append(row);
          }
          
         })
        );
      }
    },
    
    /**
     * Обновляет необходимые поля при переходе по табам
     */
    updataTabs: function() {
      $('.add-new-button').hide();
      admin.ajaxRequest({
        mguniqueurl:"action/getMethodArray"
      },
      function(response) {
        var deliveryArray ='';
        //массив способов доставки
         
        $('.add-new-button').show();
        $.each(response.data.deliveryArray, function(i, delivery) {
          var paymentMethod = delivery.paymentMethod ? delivery.paymentMethod : '{"0":0}';
          //к каждому способу доставки добавляем "привязаные" способы оплаты
          $('tr#delivery_'+delivery.id+' td#paymentHideMethod').text(paymentMethod);
          //формируем список чекбоксов для вставки в модальное окно способов оплаты
          deliveryArray +='\
            <label>\
              <span class="custom-text">'+delivery.name+'</span>\
              <input type="checkbox" name="'+delivery.id+'" class="deliveryMethod">\
            </label>\
          ';
        });
        
        $('#add-paymentMethod-wrapper #deliveryArray').html(deliveryArray);
        
        var paymentArray ='';
        //массив способов оплаты
    
        $.each(response.data.paymentArray, function(i, payment) {
          
          var deliveryMethod = payment.deliveryMethod ? payment.deliveryMethod : '';
          $('tr#payment_'+payment.id+' td#deliveryHideMethod').text(deliveryMethod);
          
          paymentArray +='\
            <label>\
              <span class="custom-text">'+payment.name+'</span>\
              <input type="checkbox" name="'+payment.id+'" class="paymentMethod">\
            </label>\
          ';
        });
        
        $('#add-deliveryMethod-wrapper #paymentArray').html(paymentArray);

       
      },
      $('.main-settings-list')
      );
    },
    
    validForm : function() {
      $('.errorField').css('display','none');
      $('input').removeClass('error-input');
      var error;
      var cost = $('input[name=deliveryCost]').val();
      cost = admin.numberDeFormat(cost); //отменяем форматирование цены
      $('input[name=deliveryCost]').val(cost); //помещаем новое значение в поле ввода перед отправкой
      
      if('' == $('input[name=deliveryName]').val()){
        $('input[name=deliveryName]').addClass('error-input');
        $('input[name=deliveryName]').parent("label").find('.errorField').css('display','block');
        error = true;
      }
      
      if('' == cost || 0 != cost*0){
        $('input[name=deliveryCost]').addClass('error-input');
        $('input[name=deliveryCost]').parent("label").find('.errorField').css('display','block');
        error = true;
      }
      
      if('' == $('input[name=deliveryDescription]').val()){
        $('input[name=deliveryDescription]').addClass('error-input');
        $('input[name=deliveryDescription]').parent("label").find('.errorField').css('display','block');
        error = true;
      }
           
      // Проверка поля для бесплатной доставки, является ли текст в него введенный числом.    
      if(isNaN(parseFloat($('input[name=free]').val()))){
        $('input[name=free]').addClass('error-input');
        $('input[name=free]').parent("label").find('.errorField').css('display','block');
        error = true;
      }
      
      if(error == true){
        return false;
      }

      return true;
    },
    
    /**
     * Загружает водяной знак
     */
    addWatermark: function() {
      $('.watermarkform').ajaxForm({
        type:"POST",
        url: "ajax",
        data: {
          mguniqueurl:"action/updateWaterMark"
        },
        cache: false,
        dataType: 'json',
        success: function(response){
          admin.indication(response.status, response.msg);         
          $('.watermark-img').html("");   
          $('.watermark-img').html("<img style=\"max-width:200px;\" src='"+admin.SITE+'/uploads/watermark/watermark.png?='+parseInt(new Date().getTime()/1000)+"'/>"); 
        }
      }).submit();
    },  
  
    addNewTemplate:function() {      
       // установка плагина
       $("#newTemplateForm").ajaxForm({
         type:"POST",
         url: "ajax",
         data: {
           mguniqueurl:"action/addNewTemplate"
         },
         cache: false,
         dataType: 'json',
         success: function(response){
         
           if(response.status == 'error'){
             admin.indication(response.status, response.msg);  
           }else{
             admin.indication(response.status, response.msg);  
             admin.refreshPanel();
           }
        
         }
       }).submit();
     },
     
    /**
    * функция для приема файла из аплоадера, для сохранения в путь логотипа сайта
    */         
    getFile: function(file) {               
      var dir = file.url;    
      dir= dir.replace(mgBaseDir, '');      
      $('.section-settings input[name="shopLogo"]').val(dir);    
      $('.section-settings .logo-img img').attr('src',mgBaseDir+dir).show(); 
       $('.section-settings .logo-img .remove-added-logo').show();
    },     
    /**
    * функция для приема фонового изобрадения для сайта
    */         
    getBackground: function(file) {               
      var dir = file.url;    
      dir= dir.replace(mgBaseDir, '');      
      $('.section-settings input[name="backgroundSite"]').val(dir);    
      $('.section-settings .background-img img').attr('src',mgBaseDir+dir).show();  
      $('.section-settings .background-img .remove-added-background').show();
    },   
     
          
    /**
    * функция строит список цветовых схем
    */         
    drawColorShemes: function(arrayColor) { 
    
      if(arrayColor.length == 0){
        $('.template-schemes').hide();
        $('.template-schemes .color-scheme').remove();
        return false;
      }
     
      var html = '<ul class="color-list">';
      var active = 'active';
      arrayColor.forEach(function(element) {          
         html +='<li class="color-scheme '+active+'" data-scheme="'+element+'" style="background:#'+element+';"></li>';
         active = '';
      }); 
      html += '</ul>';
      
      $('.template-schemes .color-list').replaceWith(html);
      $('.template-schemes').show();
      
      return html;
    },      
     
     
  }
})();


settings.init();
