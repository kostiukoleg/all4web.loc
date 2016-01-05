/**
 /* 
 * Модуль  sliderActionModule, подключается на странице настроек плагина.
 */

var sliderActionModule = (function() {
  
  return { 
    lang: [], // локаль плагина 
    init: function() {      
      // установка локали плагина 
      admin.ajaxRequest({
          mguniqueurl: "action/seLocalesToPlug",
          pluginName: 'slider-action'
        },
        function(response) {
          sliderActionModule.lang = response.data;        
        }
      );        
        
      // Выводит модальное окно для добавления
      $('.admin-center').on('click', '.section-slider-action .add-new-button', function() {    
        sliderActionModule.showModal('add');
        sliderActionModule.changeType('img');
      });
      
      // Выводит модальное окно для редактирования
      $('.admin-center').on('click', '.section-slider-action .edit-row', function() {       
        var id = $(this).data('id');
        sliderActionModule.showModal('edit', id);
        sliderActionModule.changeType($(this).data('type'));        
      });
      
       // Сохраняет изменения в модальном окне
      $('.admin-center').on('click', '.section-slider-action .b-modal .save-button', function() {
        var id = $(this).data('id');    
        sliderActionModule.saveField(id);        
      });
      
     // Нажатие на кнопку - активности
      $('.admin-center').on('click', '.section-slider-action .visible', function(){    
        $(this).toggleClass('active');  
        var id = $(this).data('id');
        if($(this).hasClass('active')) { 
          sliderActionModule.visibleEntity(id, 1); 
          $(this).attr('title', lang.ACT_V_ENTITY);
        }
        else {
          sliderActionModule.visibleEntity(id, 0);
          $(this).attr('title', lang.ACT_UNV_ENTITY);
        }
        $('#tiptip_holder').hide();
        admin.initToolTip();
      });
      
      // Удаляет запись
      $('.admin-center').on('click', '.section-slider-action .delete-row', function() {
        var id = $(this).data('id');
        sliderActionModule.deleteEntity(id);
      });
      
       // Сохраняет базовые настроки запись
      $('.admin-center').on('click', '.section-slider-action .base-setting-save', function() {
   
     
      var obj = '{';
      $('.list-option input, .list-option select').each(function() {     
        obj += '"' + $(this).attr('name') + '":"' + $(this).val() + '",';
      });
      obj += '}';    
    

      //преобразуем полученные данные в JS объект для передачи на сервер
      var data =  eval("(" + obj + ")");
      data.html_content_before = $('textarea[name=html_content_before]').val();
      data.html_content_after = $('textarea[name=html_content_after]').val();	 	 
	    data.nameaction = $(".base-settings input[name=nameaction]").val();
      
      admin.ajaxRequest({
        mguniqueurl: "action/saveBaseOption", // действия для выполнения на сервере
        pluginHandler: 'slider-action', // плагин для обработки запроса
        data: data // id записи
      },
      
      function(response) {
        admin.indication(response.status, response.msg);
        sliderActionModule.reloadSlider(response.data);        
      }
              
      );
        
      });
      
      
      // Выбор картинки слайдера
      $('.admin-center').on('click', '.section-slider-action .browseImage', function() {
        admin.openUploader('sliderActionModule.getFile');
      });     

      
      // Смена типа слайда
      $('.admin-center').on('change', '.section-slider-action .slide-editor select[name=type]', function() {
        sliderActionModule.changeType($(this).val());
      });
      
      
      // перезагрузка слайдера
      $('.admin-center').on('click', '.section-slider-action .reload-slider', function() {
         admin.ajaxRequest({
            mguniqueurl: "action/reloadSlider", // действия для выполнения на сервере
            pluginHandler: 'slider-action', // плагин для обработки запроса       
         },

         function(response) {
            admin.indication(response.status, response.msg);    
            sliderActionModule.reloadSlider(response.data);      
         }

         );
      });     
      
    },
    
    // открытие модального окна
    showModal: function(type, id) {
      switch (type) {
        case 'add':
          {
            sliderActionModule.clearField();           
            break;
          }
        case 'edit':
          {
            sliderActionModule.clearField();
            sliderActionModule.fillField(id);
            break;
          }
        default:
          {
            break;
          }
      }

      admin.openModal($('.b-modal'));      
      $('.section-slider-action .b-modal textarea').ckeditor();  
    },
                 
   /**
    * функция для приема файла из аплоадера
    */         
    getFile: function(file) {      
      $('.section-slider-action .b-modal  input[name="src"]').val(file.url);
    },      
            
   /**
    * Очистка модального окна
    */         
    clearField: function() {
      $('.section-slider-action .b-modal input').val('');
      $('.section-slider-action .b-modal textarea').text('');
      $('.section-slider-action .b-modal .id-entity').text('');
      $('.section-slider-action .b-modal .save-button').data('id','');
    },
            
    /**
     * Заполнение модального окна данными из БД
     * @param {type} id
     * @returns {undefined}
     */        
    fillField: function(id) {

      admin.ajaxRequest({
        mguniqueurl: "action/getEntity", // действия для выполнения на сервере
        pluginHandler: 'slider-action', // плагин для обработки запроса
        id: id // id записи
      },
      
      function(response) {
        var content = response.data.value;
        var src = $(content).attr('src');
        var alt = $(content).attr('alt');
        var title = $(content).attr('title');
     	   
	      $('.section-slider-action .b-modal  input[name="nameaction"]').val(response.data.nameaction);	   
        $('.section-slider-action .b-modal  input[name="src"]').val(src);
        $('.section-slider-action .b-modal  input[name="alt"]').val(alt);
        $('.section-slider-action .b-modal  input[name="title"]').val(title);
        $('.section-slider-action .b-modal  input[name="href"]').val(response.data.href);
        $('.section-slider-action .b-modal  textarea').val(content);          
         
        $('.section-slider-action .b-modal .save-button').data('id',response.data.id);
      },
              
      $('.b-modal .widget-table-body') // вывод лоадера в контейнер окна, пока идет загрузка данных
      
      );

    },
    
    /**
     * Сохранение данных из модального окна
     * @param {type} id
     * @returns {undefined}
     */        
    saveField: function(id) {
	    var nameaction = $('.section-slider-action .slide-editor input[name=nameaction]').val();
      var type = $('.section-slider-action .slide-editor select[name=type]').val();     
      var src = $('.section-slider-action .b-modal input[name="src"]').val();
      var alt = $('.section-slider-action .b-modal input[name="alt"]').val();
      var title = $('.section-slider-action .b-modal input[name="title"]').val();
      var href = $('.section-slider-action .b-modal input[name="href"]').val();
      var content = $('.section-slider-action .b-modal textarea').val();
            
      if(type=='img'){
        var value = "<img src='"+src+"' alt='"+alt+"' title='"+title+"'>";
      } else {
        var value = content;
      }   
 
      admin.ajaxRequest({
        mguniqueurl: "action/saveEntity", // действия для выполнения на сервере
        pluginHandler: 'slider-action', // плагин для обработки запроса
        id: id,
        value: value,
        type: type,
		    nameaction: nameaction,
        href: href,     
      },
      
      function(response) {
        admin.indication(response.status, response.msg);
        if(id){
          var replaceTr = $('.entity-table-tbody tr[data-id='+id+']');
          sliderActionModule.drawRow(response.data.row,replaceTr); // перерисовка строки новыми данными
        } else{
          sliderActionModule.drawRow(response.data.row); // добавление новой записи         
        }        
        sliderActionModule.reloadSlider(response.data.slider);       
        admin.closeModal($('.b-modal'));        
        sliderActionModule.clearField();
      },
              
      $('.b-modal .widget-table-body') // на месте кнопки
      
      );

    },
    
    
    /**    
     * Отрисовывает  строку сущности в главной таблице
     * @param {type} data - данные для вывода в строке таблицы
     */        
    drawRow: function(data, replaceTr) {
      var invisible = data.invisible==='1'?'active':'';        
      var titleInvisible = data.invisible?lang.ACT_V_ENTITY:lang.ACT_UNV_ENTITY;  
     
      if(data.type=="img"){ 
        var type = data.value;
      } else{                
        var type = data.type;  
      }
      
      var tr = '\
       <tr data-id="'+data.id+'">\
        <td>'+data.id+'</td>\
        <td class="type">'+type+'</td>\
         <td class="actions">\
           <ul class="action-list">\
             <li class="edit-row" data-id="'+data.id+'" data-type="'+data.type+'"><a class="tool-tip-bottom" href="javascript:void(0);" title="'+lang.EDIT+'"></a></li>\
             <li class="visible tool-tip-bottom '+invisible+'" data-id="'+data.id+'" title="'+titleInvisible+'"><a href="javascript:void(0);"></a></li>\
             <li class="delete-row" data-id="'+data.id+'"><a class="tool-tip-bottom" href="javascript:void(0);"  title="'+lang.DELETE+'"></a></li>\
           </ul>\
         </td>\
      </tr>';
 
      if(!replaceTr){
       
        if($('.entity-table-tbody tr').length>0){
          $('.entity-table-tbody tr:first').before(tr);
        } else{
          $('.entity-table-tbody').append(tr);
        }
        $('.entity-table-tbody .no-results').remove();
         
      }else{
        replaceTr.replaceWith(tr);
      }
    },
       
       
    /**    
     * Удаляет  строку сущности в главной таблице
     * @param {type} data - данные для вывода в строке таблицы
     */           
    deleteEntity: function(id) {
      if(!confirm(lang.DELETE+'?')){
        return false;
      }
      
      admin.ajaxRequest({
        mguniqueurl: "action/deleteEntity", // действия для выполнения на сервере
        pluginHandler: 'slider-action', // плагин для обработки запроса
        id: id               
      },
      
      function(response) {
        admin.indication(response.status, response.msg);
        $('.entity-table-tbody tr[data-id='+id+']').remove();
        if($(".entity-table-tbody tr").length==0){
          var html ='<tr class="no-results">\
            <td colspan="3" align="center">'+sliderActionModule.lang['ENTITY_NONE']+'</td>\
          </tr>';
          $(".entity-table-tbody").append(html);
        };
      }
      
      );
    },
    
    
    /**
    * Смена типа слайда
    */         
    changeType: function(type) {
       switch (type) {
        case 'img':
          {
            $('.type-img').show();
            $('.type-html').hide(); 
            $('.section-slider-action .slide-editor select[name=type] option[value=img]').prop('selected','selected');
            break;
          }
        case 'html':
          {
            $('.type-img').hide();
            $('.type-html').show(); 
            $('.section-slider-action .slide-editor select[name=type] option[value=html]').prop('selected','selected');
           
            break;
          }
        default:
          {
            break;
          }
      }
    },
   
    /*
     * Перезагрузка слайдера
     */        
    reloadSlider: function(newSlider) {
      $('.m-p-slider-wrapper').remove();     
      $('.before-slider-content').remove();
      $('.after-slider-content').remove();
      $('.reload-slider').parent().append(newSlider);
    },
    
    /*
     * Переключатель слайдера
     */
     visibleEntity:function(id, val) {
      admin.ajaxRequest({
        mguniqueurl:"action/visibleEntity",
        pluginHandler: 'slider-action', // плагин для обработки запроса
        id: id,
        invisible: val,
      },
      function(response) {
        admin.indication(response.status, response.msg);
      } 
      );
    },
    
  }
})();

sliderActionModule.init();