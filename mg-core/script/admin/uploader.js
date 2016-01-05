/**
 * Модуль для  раздела "работы с загружаемыми файлами".
 */
var uploader = (function () {
  return {   
    CALLBACK: null, // отложенная функция которая будет вызвана после выбора файла из высплывающего окна менеджера
    PARAM1: null, // параметр для передачи в отложенную функцию
    
    
    /**
     * Инициализирует экземляр файлового менеджера
     */
    init: function() {    
      var elf = $('#elfinder').elfinder({
              url : mgBaseDir+'/ajax?mguniqueurl=action/elfinder&dir=uploads',
              lang: 'ru',
              getFileCallback : function(file) { // после выбора файла передаем его в отложенную функцию                
                 eval(uploader.CALLBACK).call(null,file);
                 admin.closeModal( $('#modal-elfinder'));     //закрываем окно        
                 $('.cke_dialog_background_cover').css('z-index', '96');  
              },      
              closeOnEditorCallback: function() { 
                 admin.closeModal( $('#modal-elfinder'));     //закрываем окно        
                 $('.cke_dialog_background_cover').css('z-index', '96');  
              },        
              resizable: false
       }).elfinder('instance');
       
       $('#elfinderTemplate').elfinder({
              url : mgBaseDir+'/ajax?mguniqueurl=action/elfinder&dir=template',
              lang: 'ru',              
              closeOnEditorCallback: function() { 
                 admin.closeModal( $('#modal-elfinder'));     //закрываем окно        
                 $('.cke_dialog_background_cover').css('z-index', '96');  
              },        
              resizable: false
       });
    },
    
   
       
    /*
     * этот метод отрабатывает при вызове файлового менеджера из CKEditor
     */
    getFileCallbackCKEDITOR: function(file) {        
       CKEDITOR.tools.callFunction(uploader.PARAM1, file.url);     
    },   
    
    /**
     * открывает окно менеджера файлов, сохраняет  параметры для вызова отложенной функции 
     * @param {type} callback
     * @param {type} param1
     * @returns {undefined}
     */        
    open: function(callback,param1) {  
      
      uploader.PARAM1 = param1;
      uploader.CALLBACK = callback;
      
      if($('#modal-elfinder').length==0){        
        $('body').append('\
          <link href="'+mgBaseDir+'/mg-admin/design/css/jquery-ui.css" rel="stylesheet" type="text/css">\
          <link rel="stylesheet" type="text/css" media="screen" href="'+mgBaseDir+'/mg-core/script/elfinder/css/elfinder.min.css">\
          <link rel="stylesheet" type="text/css" media="screen" href="'+mgBaseDir+'/mg-core/script/elfinder/css/theme.css">\
          <div class="uploader-modal hidden-form add-category-popup" id="modal-elfinder">\
            <div class="product-table-wrapper">\
                <div class="widget-table-title">\
                    <h4 class="category-table-icon" id="modalTitle">Файловый менеджер</h4>\
                    <div class="uploader-modal_close tool-tip-bottom" title=""></div>\
                </div>\
                   <div id="elfinder"></div>\<div id="elfinderTemplate"></div>\
            </div>\
          </div>');
        uploader.init();
        $( "#modal-elfinder").draggable({ handle: ".widget-table-title" });
        $('body').on('click', '.uploader-modal_close', function() {  
          $('.cke_dialog_background_cover').css('z-index', '96');  
        });
      }
      

      if(admin.DIR_FILEMANAGER=='template'){
        $('#elfinderTemplate').show();
        $('#elfinder').hide();  
      }
      if(admin.DIR_FILEMANAGER=='uploads'){
        $('#elfinderTemplate').hide();
        $('#elfinder').show();
      }
     
      admin.openModal($('#modal-elfinder'));
      $('.cke_dialog ').css('z-index', '100'); 
      $('.cke_dialog_background_cover').css('z-index', '150');  
      $('#modal-elfinder').css('z-index', '200');   
    },            
    
            
    }
  
})();

// инициализациямодуля при подключении
uploader.init();