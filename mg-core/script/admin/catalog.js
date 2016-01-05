
/**
 * Модуль для  раздела "Товары".
 */
var catalog = (function () {
  return {
    errorVariantField: false,
    memoryVal: null, // HTML редактор для   редактирования страниц
    supportCkeditor: null, 
    deleteImage: '', // список картинок помеченых на удаление, при сохранении товара, данный список передается на сервер и картинки удаляются физически
    tmpImage2Del: '',
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init: function() {
      includeJS(admin.SITE+'/mg-core/script/jquery.bxslider.min.js');
             
      // Вызов модального окна при нажатии на кнопку добавления товаров.
      $('.admin-center').on('click', '.section-catalog .add-new-button', function(){
        catalog.openModalWindow('add');        
      });

      // Показывает панель с фильтрами.
      $('.admin-center').on('click', '.section-catalog .show-filters', function(){
        $('.import-container').slideUp();
        $('.filter-container').slideToggle(function(){
          $('.widget-table-action').toggleClass('no-radius');
        });  
      });


      // Применение выбраных фильтров
      $('.admin-center').on('click', '.section-catalog .filter-now', function(){
        catalog.getProductByFilter();
        return false;
      });
      
      // показывает все фильтры в заданной характеристике
      $('.admin-center').on('click', '.section-catalog .mg-filter-item .mg-viewfilter', function(){
        $(this).parents('ul').find('li').fadeIn();
        $(this).hide();
      });
            
       // показывает все группы фильтров
      $('.admin-center').on('click', '.section-catalog .mg-viewfilter-all', function(){
        $(this).hide();
        $('.mg-filter-item').fadeIn();
      });
      
      // Вызов модального окна при нажатии на кнопку изменения товаров.
      $('.admin-center').on('click', '.section-catalog .clone-row', function(){
        catalog.cloneProd($(this).attr('id'));

      });

      // Вызов модального окна при нажатии на кнопку изменения товаров.
      $('.admin-center').on('click', '.section-catalog .import-csv', function(){
        $('.filter-container').slideUp();
        $('.import-container').slideToggle(function(){
          $('.widget-table-action').toggleClass('no-radius');
        });       
      });
      
      // Обработчик для загрузки файла импорта из CSV 
      $('body').on('change', '.section-catalog input[name="upload"]', function(){     
        catalog.uploadCsvToImport();           
        if($(".block-upload-сsv select[name=importType]").val()=="MogutaCMS"){
          $(".block-importer .delete-all-products-btn").show();
        }
        if($(".block-upload-сsv select[name=importType]").val()=="MogutaCMSUpdate"){
          $(".block-importer .delete-all-products-btn").hide();
        }
      });
         
      // Обработчик для смены категории
      $('body').on('change', '.section-catalog .filter-container select[name="cat_id"]', function() {       
        var cat_id= $('.section-catalog .filter-container select[name="cat_id"]').val();
        if(cat_id=="null"){
            cat_id = 0;
        }
        admin.show("catalog.php", cookie("type"), "page=0&cat_id=" + cat_id, catalog.callbackProduct);   
      });      
      // Обработчик для  переключения вывода товаров подкатегорий
      $('body').on('change', '.section-catalog .filter-container input[name="insideCat"]', function() {
        var cat_id= $('.section-catalog .filter-container select[name="cat_id"]').val();
        if(cat_id=="null"){
            cat_id = 0;
        }
        var request = $("form[name=filter]").formSerialize();
        var insideCat = $(this).prop('checked');  
        admin.show("catalog.php", cookie("type"), request+"&page=0&insideCat="+insideCat+"&cat_id=" +cat_id, catalog.callbackProduct);   
      });
      
      
      // Обработчик для загрузки файла импорта из CSV 
      $('body').on('click', '.section-catalog .repeat-upload-csv', function(){
        $('.import-container input[name="upload"]').val('');
        $('.block-upload-сsv').show();
        $('.block-importer').hide();        
        $('.repat-upload-file').show();
        $('.cancel-importing').hide();        
        $('.message-importing').text('');
        catalog.STOP_IMPORT=false;;
       
      });
         
      
      // Обработчик для загрузки изображения на сервер, сразу после выбора.
      $('body').on('click', '.section-catalog .start-import', function(){
         if(!confirm('Перед началом импорта, категорически, рекомендуем проверить параметры импорта и создать копию базы данных! Копия создана?')){
            return false;
         }
         $('.repat-upload-file').hide();        
         $('.block-importer').hide();
         $('.cancel-importing').show();     
         catalog.startImport($('.block-importer .uploading-percent').text());        
      });
      
      // Останавливает процесс загрузки товаров.
      $('body').on('click', '.section-catalog .cancel-import', function(){      
         catalog.canselImport();        
      });
      
      
       // Открывает список  дополнительных категорий
      $('body').on('click', '#add-product-wrapper .add-category', function(){ 
         $(this).toggleClass('opened-list'); 
         if($(this).hasClass('opened-list')){
           $('.inside-category').show();
         }else{
           $('.inside-category').hide();
         }
      });
      
      // снимает выделение со всех дополнительных категорий
      $('body').on('click', '#add-product-wrapper .clear-select-cat', function(){  
         $('select[name=inside_cat] option').prop('selected', false);         
      });
      
      // разворачивает список всех дополнительных категорий
      $('body').on('click', '#add-product-wrapper .full-size-select-cat.closed-select-cat', function(){  
          $('select[name=inside_cat]').attr('size',$('select[name=inside_cat] option').length);         
          $(this).removeClass('closed-select-cat').addClass('opened-select-cat');
          $(this).text(lang.PROD_CLOSE_CAT);
      });
      
      $('body').on('click', '.yml-title', function(){
        $(this).toggleClass('opened').toggleClass('closed');
        $('.yml-wrapper').slideToggle(300);
        if($(this).hasClass('opened')) {
          $(this).html('Спрятать настройки YML');
        }
        else {
          $(this).html('Показать настройки YML');
        }        
      });

      // сворачивает список всех дополнительных категорий
      $('body').on('click', '#add-product-wrapper .full-size-select-cat.opened-select-cat', function(){  
          $('select[name=inside_cat]').attr('size',4);         
          $(this).removeClass('opened-select-cat').addClass('closed-select-cat');
          $(this).text(lang.PROD_OPEN_CAT);
      });


      // Вызов формы для выбора валют.
      $('body').on('click', '#add-product-wrapper .btn-selected-currency', function(){
        var position = $(this).position();
        $('.add-product-form-wrapper .select-currency-block').css('position', 'absolute');
        $('.add-product-form-wrapper .select-currency-block').css('top', position.top - 15 + 'px');
        $('.add-product-form-wrapper .select-currency-block').css('left', position.left + 25 + 'px');
        $('.add-product-form-wrapper .select-currency-block').show();
      });
      
      // применение выбраной валюты
      $('body').on('click', '#add-product-wrapper .apply-currency', function(){           
         catalog.changeIso();
      });


      // Вызов модального окна при нажатии на кнопку изменения товаров.
      $('.admin-center').on('click', '.section-catalog .edit-row', function(){
        catalog.openModalWindow('edit', $(this).attr('id'));
      });

      // Удаление товара.
      $('.admin-center').on('click', '.section-catalog .delete-order', function(){
        catalog.deleteProduct(
          $(this).attr('id'),
          $('tr[id='+$(this).attr('id')+'] .uploads').attr('src'),
          false          
        );
      });
      
            
      // Нажатие на кнопку - рекомендуемый товар
      $('.admin-center').on('click', '.section-catalog .recommend', function(){    
        $(this).toggleClass('active');  
        var id = $(this).data('id');

        if($(this).hasClass('active')) {       
          catalog.recomendProduct(id, 1);                 
          $(this).attr('title', lang.PRINT_IN_RECOMEND);
        }
        else {       
          catalog.recomendProduct(id, 0);
          $(this).attr('title', lang.PRINT_NOT_IN_RECOMEND);
        }
        $('#tiptip_holder').hide();
        admin.initToolTip();
      });
      
      // Нажатие на кнопку - активный товар
      $('.admin-center').on('click', '.section-catalog .visible', function(){    
        $(this).toggleClass('active');  
        var id = $(this).data('id');

        if($(this).hasClass('active')) {       
          catalog.visibleProduct(id, 1); 
          $(this).attr('title', lang.ACT_V_PROD);
        }
        else {       
          catalog.visibleProduct(id, 0);
          $(this).attr('title', lang.ACT_UNV_PROD);
        }
        $('#tiptip_holder').hide();
        admin.initToolTip();
      });
      
       // Нажатие на кнопку - новый товар
      $('.admin-center').on('click', '.section-catalog .new', function(){    
        $(this).toggleClass('active');  
        var id = $(this).data('id');

        if($(this).hasClass('active')) {       
          catalog.newProduct(id, 1);                 
          $(this).attr('title', lang.PRINT_IN_NEW);
        }
        else {       
          catalog.newProduct(id, 0);
          $(this).attr('title', lang.PRINT_NOT_IN_NEW);
        }
        $('#tiptip_holder').hide();
        admin.initToolTip();
      });
      
      
      // Выделить все товары.
      $('.admin-center').on('click', '.section-catalog .checkbox-cell input[name=product-check]', function(){

        if($(this).val()!='true'){
          $('.product-tbody input[name=product-check]').prop('checked','checked');
          $('.product-tbody input[name=product-check]').val('true');
        }else{
          $('.product-tbody input[name=product-check]').prop('checked', false);
          $('.product-tbody input[name=product-check]').val('false');
        }
      });
      

      // Сброс фильтров.
      $('.admin-center').on('click', '.section-catalog .refreshFilter', function(){       
        admin.clearGetParam();
        admin.show("catalog.php","adminpage","refreshFilter=1",admin.sliderPrice);
        return false;
      });

     // Обработка выбраной категории (перестраивает пользовательские характеристики).
      $('body').on('change', '#productCategorySelect', function(){
        //достаем id редактируемого продукта из кнопки "Сохранить"
        var product_id=$(this).parents('.add-product-form-wrapper').find('.save-button').attr('id');
        var category_id=$(this).val();
        catalog.generateUserProreprty(product_id, category_id);
     
      });

      // Обработчик для загрузки изображения на сервер, сразу после выбора.
      $('body').on('change', 'input[name="photoimg"]', function(){
        var currentImg = '';
        var img_container = $(this).parents('.product-upload-img');
        
        if(!img_container.attr('class')){
          img_container = $(this).parents('.variant-row');
        }
        
        if(img_container.find('.prev-img img').length > 0){
          currentImg = img_container.find('.prev-img img').attr('alt');
        }else{
          currentImg = img_container.find('img').attr('filename');
        }

        //Пишем в поле deleteImage имена изображений, которые необходимо будет удалить при сохранении
        if(catalog.deleteImage){
          catalog.deleteImage += '|'+currentImg;
        }else{
          catalog.deleteImage = currentImg;
        }
        
        if($(this).val()){          
          catalog.addImageToProduct(img_container);
        }
      });

      // Добавляет ссылку на электронный товар
      $('body').on('click', '.add-link-electro', function(){ 
         admin.openUploader('catalog.getFileElectro'); 
      });
      
      // Удаляет ссылку на электронный товар
      $('body').on('click', '.del-link-electro', function(){ 
         $('.section-catalog input[name="link_electro"]').val('');   
         $('.del-link-electro').hide();
         $('.add-link-electro').show();
      });


      // Удаляение изображения товара, как из БД таи физически с сервера.
      $('body').on('click', '.cancel-img-upload', function(){
        var img_container = $(this).parents('.product-upload-img');
        catalog.delImageProduct($(this).attr('id'),img_container);
        
      });

      // Сохранение продукта при на жатии на кнопку сохранить в модальном окне.
      $('body').on('click', '#add-product-wrapper .save-button', function(){
        catalog.saveProduct($(this).attr('id'));       
      });

       // Нажатие ентера при вводе в строку поиска товара
      $('body').on('keypress', '.widget-table-action input[name=search]', function(e){
        if(e.keyCode==13){  
          catalog.getSearch($(this).val());       
          $(this).blur();
        }
      });


       // Добавить вариант товара
      $('body').on('click', '.product-table-wrapper .add-position', function(){             
        catalog.addVariant($('.variant-table'));
      });
      
       // Удалить вариант товара
      $('body').on('click', '.product-table-wrapper .del-variant', function(){       
        if($('.variant-table tr').length==2){        
          $('.variant-table .hide-content').hide();        
          $('.variant-table-wrapper').css('width','415px');
          $('.variant-table tr td').css('padding','5px 18px 5px 0');
          $('.variant-table').data('have-variant','0');         
        }else{        
          $(this).parents('tr').remove();
        }
      
        var imgFile = $(this).parents('tr').find('.img-this-variant img').attr('src');
        return false;
        admin.ajaxRequest({
          mguniqueurl:"action/deleteImageProduct",
          imgFile: imgFile,         
        },
        
        function(response) {
          admin.indication(response.status, response.msg);      
        });
      
      });
      
       // при ховере на иконку картинки варианта  показывать  имеющееся изображение
       $('body').on('mouseover mouseout', '.product-table-wrapper .img-variant',  function(event) {
        if (event.type == 'mouseover') {
          $(this).parents('td').find('.img-this-variant').show();
        } else {
          $(this).parents('td').find('.img-this-variant').hide();
        }
      });
      
      // При получении фокуса в поля для изменения значений, запоминаем каким было  исходное значение
      $('.admin-center').on('focus', '.section-catalog .fastsave', function(){       
        catalog.memoryVal = $(this).val();   
      });
      
      // сохранение параметров товара прямо из общей таблицы товаров при потере фокуса
      $('.admin-center').on('blur', '.section-catalog .fastsave', function(){       
        //если введенное отличается от  исходного, то сохраняем.    
        if(catalog.memoryVal!=$(this).val()){          
          catalog.fastSave($(this).data('packet'), $(this).val(),$(this));                 
        }
        catalog.memoryVal = null; 
      });
      
      // сохранение параметров товара прямо из общей таблицы товаров при нажатии ентера
      $('.admin-center').on('keypress', '.section-catalog .fastsave', function(e){
        if(e.keyCode==13){
          $(this).blur();
        }
      });     
      
      // показывает сроку поиска для связанных товаров
      $('body').on('click', '#add-product-wrapper .add-related-product', function() {
        $('.select-product-block').show();
      });
            
      // Удаляет связанный товар из списка связанных
      $('body').on('click', '#add-product-wrapper .add-related-product-block .remove-added-product', function() {        
        $(this).parents('.product-unit').remove();
        catalog.msgRelated();
      });
      
      // Закрывает выпадающий блок выбора связанных товаров
      $('body').on('click', '#add-product-wrapper .add-related-product-block .cancel-add-related', function() {
        $('.select-product-block').hide();
      });
      
      // Поиск товара при создании связанного товара.
      // Обработка ввода поисковой фразы в поле поиска.
      $('body').on('keyup', '#add-product-wrapper .search-block input[name=searchcat]', function() {
        admin.searchProduct($(this).val(),'#add-product-wrapper .search-block .fastResult');
      });
      
      // подбор случайного товара
      $('body').on('click', '#add-product-wrapper .random-add-related', function() {        
          admin.ajaxRequest({
            mguniqueurl:"action/getRandomProd"   
          },
          function(response) {           
            admin.indication(response.status, response.msg);
            if(response.status!='error'){
              catalog.addrelatedProduct(0, response.data.product);
            }
          },
          false, 
          false, 
          true  
         );      
      });      
      
      // Подстановка товара из примера в строку поиска связанного товара.
      $('body').on('click', '#add-product-wrapper .search-block  .example-find', function() {
        $('.section-catalog .search-block input[name=searchcat]').val($(this).text());
        admin.searchProduct($(this).text(),'#add-product-wrapper .search-block .fastResult');
      });
    
     // Клик по найденым товарам поиска в форме добавления связанного товара.
      $('body').on('click', '#add-product-wrapper .fast-result-list a', function() {
        catalog.addrelatedProduct($(this).data('element-index'));
      });
            
      // Выполнение выбранной операции с товарами
      $('.admin-center').on('click', '.section-catalog .run-operation', function(){
        catalog.runOperation($('.product-operation').val());
      });
      
      // Изменение типа каталога для импорта из CSV
      $('.admin-center').on('change', ".block-upload-сsv select[name=importType]", function(){
        $('.block-upload-сsv .example-csv').hide();
        $('.block-upload-сsv .view-'+$(this).val()).show();    
      });
     
               
     // Обработчик для загрузки изображения на сервер, сразу после выбора.
      $('body').on('change', 'input[name="photoimg"]', function(){      
        // отправка картинки на сервер
        var imgContainer = $(this).parents('td');     
        var mguniqueurl = "action/addImage";
        var oldimage = null;
        var nowatermark = $(this).hasClass('img-variant')?1:0;
        if(nowatermark) {          
          oldimage = $(this).parents('td').find('img').attr('filename');      
          if(!oldimage){
             oldimage = $(this).parents('td').find('img').data('filename');          
          }
          mguniqueurl = "action/addImageNoWaterMark";          
        }     
        
        $(this).parent('form').ajaxForm({
          type:"POST",
          url: "ajax?oldimage="+oldimage,
          data: {
            mguniqueurl: mguniqueurl,   
            oldimage: oldimage,
          },
          cache: false,
          dataType: 'json',
          success: function(response){
         
            admin.indication(response.status, response.msg);
            if(response.status != 'error'){
              var src=admin.SITE+'/uploads/'+response.data.img;
              imgContainer.find('img').attr('src',src).attr('filename', response.data.img);
            }else{
              var src=admin.SITE+'/mg-admin/design/images/no-img.png';
              imgContainer.find('img').attr('src',src).attr('filename', 'no-img.png');
            }
          }
        }).submit();
      });

      // Устанавливает количиство выводимых записей в этом разделе.
      $('.admin-center').on('change', '.section-catalog .countPrintRowsProduct', function(){
        var count = $(this).val();
        admin.ajaxRequest({
          mguniqueurl: "action/setCountPrintRowsProduct",
          count: count
        },
        function(response) {         
          admin.refreshPanel();
        }
        );

      });


      // Подобрать продукты по поиску
      $('.admin-center').on('click', '.section-catalog .searchProd', function(){
        var keyword =  $('input[name="search"]').val();
        catalog.getSearch(keyword);
      });
      
      
       //Добавить изображение для продукта
       $('body').on('click', '.add-product-form-wrapper .add-image', function(){        
         var src=admin.SITE+'/mg-admin/design/images/no-img.png';
         var row = catalog.drawControlImage(src, true,'','','');
         $('.small-img-wrapper').prepend(row).addClass("added-img");
         admin.initToolTip();
       });
       
       // для главной картинки меняем классы сохраняем в буфер и удаляем
        
       //Сделать основной картинку продукта
       $('body').on('click', '.main-image', function(){     
          var obj = $(this).parents('.product-upload-img');         
          catalog.upMainImg(obj);
       });
       
       //Показать окно с настройками title и alt для картинки
       $('body').on('click', '.add-product-form-wrapper .seo-image', function(e){
          var obj = $(this).parents('.product-upload-img');         
          var offset = $(this).parents('.small-img-wrapper').offset();  
          var relativeY = 0;
          if(offset!=null){
            relativeY = (e.pageY - offset.top);      
          }
          obj.find('.seo-image-block').show().css('top',relativeY+'px');
          
       });
       
       //Спрятать  окно с настройками title и alt для картинки
       $('body').on('click', '.add-product-form-wrapper .apply-seo-image', function(){
          $('.seo-image-block').hide();
       });
      
     
       
       //Клик по кнопке Яндекс Маркет
       $('body').on('click', '.get-yml-market', function(){        
     
          admin.ajaxRequest({
             mguniqueurl:"action/existXmlwriter"   
           },
           function(response) {
             admin.indication(response.status, response.msg);
             if(response.status!='error'){
               window.location=admin.SITE+'/mg-admin?yml=1';
             }
           },
           $('.userField')
          );
       }); 
      
      // выводит путь родительских категорий при наведении мышкой 
      $('.admin-center').on('mouseover', '.section-catalog tbody tr.product-row .cat_id', function(){
          if (!$(this).find('.parentCat').hasClass('categoryPath') && $(this).attr('id')!=0) {
            
            $(this).find('.parentCat').addClass('categoryPath');
            var cat_id = $(this).attr('id'); 
            var path = '';
            var parent = $('.section-catalog .category-filter select[name=cat_id] option[value='+cat_id+']').data('parent');
            if (parent) {
              while (parent != 0) {
                path = $('.section-catalog .category-filter select[name=cat_id] option[value='+parent+']').text()+ '/' + path ;
                parent = $('.section-catalog .category-filter select[name=cat_id] option[value='+parent+']').data('parent');
              }
              path = path.replace(/-/g,'');
              $(this).find('.parentCat').attr('title', path);
              $('#tiptip_holder').hide();
              admin.initToolTip();
            }
          } 
      });
       // открытие текстового редактора для ввода значения текстовой характеристики, замена вхождения <br> на пернос строки /n
      $('body').on('click', '.property.custom-textarea', function(){
        var id = $(this).data('name');
        var html = $('.user-field-table .custom-textarea[data-name='+id+']').parent().find('.value').text();
        html = html.replace(/&lt;br\s*\/*&gt;/g, '\n');
        $('#textarea-property-value textarea[name=html_content-textarea]').val(admin.htmlspecialchars_decode(html));  
        var offset = (window.pageYOffset);
        $("#textarea-property-value").show();
        $('#textarea-property-value textarea[name=html_content-textarea]').ckeditor();  
        $('.textarea-overlay').show();
        $('#textarea-property-value .save-button-value').data('id', id);
      })
      // если поле изменено и не сохранено - перед закрытием выводит сообщение 
      $('body').on('click', '#textarea-property-value .proper-modal_close', function() {
        if ($(this).hasClass('edited')) {
          if (!confirm('Изменения не сохранены. Закрыть окно характеристики?')) {
            return false;
          }
        } 
        $(this).removeClass('edited')
        $('#textarea-property-value').hide();
        $('.textarea-overlay').hide();
        $('#textarea-property-value textarea').val('');
        $('#textarea-property-value .save-button-value').data('id', '');
      });
       // добавление класса на кнопку закрытия при изменении 
      $('body').on('click', '#textarea-property-value .custom-textarea-value', function(){
        $('#textarea-property-value .proper-modal_close').addClass('edited');
      })
       // сохранение значения текстовой характеристики
      $('body').on('click', '#textarea-property-value .save-button-value', function(){
        var id = $(this).data('id');
        var value = $('#textarea-property-value textarea').val();
        $('.user-field-table .custom-textarea[data-name='+id+']').parent().find('.value').text(admin.htmlspecialchars(value));
        admin.indication('success', 'Значение характеристики сохранено');
        $('#textarea-property-value').hide();
          $('.textarea-overlay').hide();
        $('#textarea-property-value textarea').val('');
        $('#textarea-property-value .save-button-value').data('id', '');
        $('#textarea-property-value .proper-modal_close').removeClass('edited');
      })
      // добавление "своего" артикула 
      $('.admin-center').on('keyup', '.variant-table .default-code', function(){
        $(this).removeClass('default-code');
      });
      // формирование meta title по введенному названию
      $('.admin-center').on('blur', '.product-text-inputs input[name=title]', function(){
        var title = $(this).val();
        if (!$('.add-product-form-wrapper .seo-wrapper input[name=meta_title]').val()){
          $('.add-product-form-wrapper .seo-wrapper input[name=meta_title]').val(title);
        }
        if (!$('.add-product-form-wrapper .seo-wrapper input[name=meta_keywords]').val()) {	 
		  var code = $('input[name=code]').val();
		  if(code){
		   code = ', '+code;
		  }
          var keywords = title.replace(/\s/g, ', ')+ ', '+ title +' '+ lang.META_BUY + code;
          $('.add-product-form-wrapper .seo-wrapper input[name=meta_keywords]').val(keywords);
        }        
      });
      // при заполнении поля описание товара - первые 160 символов копируются в блок SEO - description
      CKEDITOR.on('instanceCreated', function(e) {
        if (e.editor.name === 'html_content') {
          e.editor.on('blur', function (event) {      
          var description = $('.add-product-form-wrapper .seo-wrapper textarea[name=meta_desc]').val();
          if (!$.trim(description)) {
            description = $('textarea[name=html_content]').val();
            var short_desc = description.replace(/<\/?[^>]+>/g, '');
            short_desc = short_desc.substr(0, 160);            
            short_desc = admin.htmlspecialchars_decode(short_desc.replace(/\n/g, ' ').replace(/&nbsp;/g, '').replace(/  /g, ' '));               
            $('.add-product-form-wrapper .seo-wrapper textarea[name=meta_desc]').val($.trim(short_desc));
          } 
          });
        }
      }); 
      
      /*Инициализирует CKEditior и раскрывает поле для заполнения описания товара*/
      $('.admin-center').on('click', '.product-desc-wrapper .html-content-edit', function(){
        var link = $(this);
        $('textarea[name=html_content]').ckeditor(function() {  
          $('#html-content-wrapper').show();
          link.hide();
        });
      });
      
      /*Разворачивает все варианты товара на странице*/
      $('.admin-center').on('click', '.section-catalog .show-all-variants', function(){  
        if($('.second-block-varians').is(':visible')){
          $('.second-block-varians').hide();
          $(this).text(lang.ACT_SHOW_ALL_VARIANTS);
        }else{
          $('.second-block-varians').show();
          $(this).text(lang.ACT_HIDE_VARIANTS);
        }
        return false;
      });
      
      /**
       * Дополнительный обработчик закрытия модального окна, 
       * для удаления загруженных изображений.
       */
      $('body').on('click', '.b-modal_close', function () {
        var imagesList = '';
        if($(this).attr('item-id')){
          imagesList = catalog.tmpImage2Del;
          catalog.tmpImage2Del = '';
        }else{
          imagesList = catalog.createFieldImgUrl();
        
          $('.variant-table .variant-row').each(function(){
            var filename = $(this).find('img[filename]').attr('filename');

            if(!filename){
              filename = $(this).find('img').data('filename');
            }

            if(filename){
              imagesList += '|'+filename;
            }
          });
                    
          imagesList += '|'+catalog.deleteImage;     
          catalog.deleteImage = '';
        }
        
        admin.ajaxRequest({
          mguniqueurl:"action/deleteTmpImages",
          images: imagesList
        });
        // удаляем добавленные характеристики, если товар не был сохранен
        catalog.closeAddedProperty('close');
      });
      /* Добавляет новую характеристику для товара */
      $('.admin-center').on('click', '.product-table-wrapper .add-property', function(){
        $('.product-table-wrapper .new-added-properties').show();
      });
      /* Добавляет новую характеристику для товара */
      $('.admin-center').on('click', '.product-table-wrapper .apply-new-prop', function () {
        var name = $(this).parent().find('input[name=name]').val();
        var value = $(this).parent().find('input[name=value]').val();
        if (name == '') {
          $(this).parent().find('input[name=name]').addClass('error-input');
          $('.product-table-wrapper .new-added-properties .errorField').show();
          return false;
        } else {
          catalog.addNewProperty(admin.htmlspecialchars(name), admin.htmlspecialchars(value));
        }
      });
      /* Отменяет создание новой характеристики */
      $('.admin-center').on('click', '.product-table-wrapper .cancel-new-prop', function(){        
        catalog.closeAddedProperty();
      });
      /* Удаляет вновь созданную характеристику */
      $('.admin-center').on('click', '.product-table-wrapper .remove-added-property', function(){        
        var id = $(this).parent().data('id');
        $(this).parent().remove();
        admin.ajaxRequest({
          mguniqueurl: "action/deleteUserProperty",
          id: id
        })
      });
    },

             
    /**
     * Открывает модальное окно.
     * type - тип окна, либо для создания нового товара, либо для редактирования старого.
     */
    openModalWindow: function(type, id) {
    
      try{        
        if(CKEDITOR.instances['html_content']){         
          CKEDITOR.instances['html_content'].destroy();
        }   
        if(CKEDITOR.instances['html_content-textarea']){         
          CKEDITOR.instances['html_content-textarea'].destroy();
        }
      } catch(e) { }     
     
      switch (type) {
        case 'edit':{
        
          catalog.clearFileds();   
          $('.html-content-edit').show();
          $('.product-desc-wrapper #html-content-wrapper').hide();
          $('.add-product-table-icon').text('Редактирование товара');
          catalog.editProduct(id);
        
          break;
        }
        case 'add':{

          $('.add-product-table-icon').text('Добавление нового товара');
          catalog.clearFileds();
          $('.html-content-edit').hide();
          $('.product-desc-wrapper #html-content-wrapper').show();
          $('textarea[name=html_content]').ckeditor();
          
          // получаем с сервера все доступные пользовательские параметры
          admin.ajaxRequest({
             mguniqueurl:"action/getUserProperty"
           },
           function(response) {
              // выводим поля для редактирования пользовательских характеристик
              userProperty.createUserFields(null,response.data.allProperty);   
            },
           $('.error-input').removeClass('error-input')        
           );

         
          catalog.msgRelated();          
          var src=admin.SITE+'/mg-admin/design/images/no-img.png';
          var row = catalog.drawControlImage(src, false,'','','');                  
          $('.small-img-wrapper').before(row);
          $('.main-img-prod .main-image').hide();
          
          var catId = $('.filter-container select[name=cat_id]').val();       
          if(catId == 'null'){
            catId = 0;
          }
          // получаем набор общих характеристик и выводим их
          catalog.generateUserProreprty(0, catId);
          

          break;
        }
        default:{
          catalog.clearFileds();
          break;
        }
      }
     
      // Вызов модального окна.
      admin.openModal($('.b-modal'));      
   
      
    },

    /**
     *  Изменяет список пользовательских свойств для выбранной категории в редактировании товара
     */
     generateUserProreprty: function(produtcId,categoryId) {
   
       admin.ajaxRequest({
          mguniqueurl:"action/getProdDataWithCat",
          produtcId: produtcId,
          categoryId: categoryId
        },
        function(response) {   
          userProperty.createUserFields($('.userField'), response.data.thisUserFields, response.data.allProperty);
          admin.initToolTip();        
        },
        $('.userField')
       );

     },
    /**
     *  Проверка заполненности полей, для каждого поля прописывается свое правило.
     */
    checkRulesForm: function() {
      $('.errorField').css('display','none');
      $('.product-text-inputs input').removeClass('error-input');
      var error = false;

      // наименование не должно иметь специальных символов.
      if(!$('.product-text-inputs input[name=title]').val()){
        $('.product-text-inputs input[name=title]').parent("label").find('.errorField').css('display','block');
        $('.product-text-inputs input[name=title]').addClass('error-input');
        error = true;
      }

      // наименование не должно иметь специальных символов.
      if(!admin.regTest(2, $('.product-text-inputs input[name=url]').val()) || !$('.product-text-inputs input[name=url]').val()){
        $('.product-text-inputs input[name=url]').parent("label").find('.errorField').css('display','block');
        $('.product-text-inputs input[name=url]').addClass('error-input');
        error = true;
      }

      // артикул обязательно надо заполнить.
      if(!$('.product-text-inputs input[name=code]').val()){
        $('.product-text-inputs input[name=code]').parent("label").find('.errorField').css('display','block');
        $('.product-text-inputs input[name=code]').addClass('error-input');
        error = true;
      }

      // Проверка поля для стоимости, является ли текст в него введенный числом.
      if(isNaN(parseFloat($('.product-text-inputs input[name=price]').val()))){
        $('.product-text-inputs input[name=price]').parent("label").find('.errorField').css('display','block');
        $('.product-text-inputs input[name=price]').addClass('error-input');
        error = true;
      }
      
      // Проверка поля для старой стоимости, является ли текст в него введенный числом.
      $('.product-text-inputs input[name=old_price]').each(function(){
        var val = $(this).val();
        if(isNaN(parseFloat(val))&&val!=""){
          $(this).parent("label").find('.errorField').css('display','block');
          $(this).addClass('error-input');
          error = true;
        }
      });

      // Проверка поля количество, является ли текст в него введенный числом.
      $('.product-text-inputs input[name=count]').each(function(){
        var val = $(this).val();
        if(val=='\u221E'||val==''||parseFloat(val)<0){val = "-1"; $(this).val('∞'); }
        if(isNaN(parseFloat(val))){
          $(this).parent("label").find('.errorField').css('display','block');
          $(this).addClass('error-input');
          error = true;
        }
      });
      if(error == true){
        return false;
      }

      return true;
    },


    /**
     * Сохранение изменений в модальном окне продукта.
     * Используется и для сохранения редактированных данных и для сохраниеня нового продукта.
     * id - идентификатор продукта, может отсутсвовать если производится добавление нового товара.
     */
    saveProduct: function(id) {
      
      // Если поля неверно заполнены, то не отправляем запрос на сервер.
      if(!catalog.checkRulesForm()){
        return false;
      }
      
      var recommend = $('.save-button').data('recommend');
      var activity =  $('.save-button').data('activity');
      var newprod =  $('.save-button').data('new');
      //определяем имеются ли варианты товара 
      var variants=catalog.getVariant();      
      
      if(catalog.errorVariantField){    
        admin.indication('error', lang.ERROR_VARIANT); 
        return false;
      }
     
      if($('textarea[name=html_content]').val()==''){
        if(!confirm(lang.ACCEPT_EMPTY_DESC+'?')){
          return false;
        }
      }   
      if ($('.addedProperty .new-added-prop').length > 0) {
        catalog.saveAddedProperties();
      }
      if(!variants){     
        
        // Пакет характеристик товара.
        var packedProperty = {
          mguniqueurl:"action/saveProduct",
          id: id,
          title: $('.product-text-inputs input[name=title]').val(),
          link_electro: $('.product-text-inputs input[name=link_electro]').val(),
          url: $('.product-text-inputs input[name=url]').val(),
          code: $('.product-text-inputs input[name=code]').val(),
          price: $('.product-text-inputs input[name=price]').val(),
          old_price: $('.product-text-inputs input[name=old_price]').val(),
          image_url: catalog.createFieldImgUrl(),
          image_title: catalog.createFieldImgTitle(),
          image_alt: catalog.createFieldImgAlt(),  
          delete_image: catalog.deleteImage,
          count: $('.product-text-inputs input[name=count]').val(),      
          weight: $('.product-text-inputs input[name=weight]').val(),
          cat_id: $('.product-text-inputs select[name=cat_id]').val(),
          inside_cat: catalog.createInsideCat(),
          description: $('textarea[name=html_content]').val(),          
          meta_title: $('.seo-wrapper input[name=meta_title]').val(),
          meta_keywords: $('.seo-wrapper input[name=meta_keywords]').val(),
          meta_desc: $('.seo-wrapper textarea[name=meta_desc]').val(),       
          currency_iso: $('.add-product-form-wrapper select[name=currency_iso]').val(),  
          recommend: recommend,
          activity: activity,
          new:newprod,
          userProperty: userProperty.getUserFields(),
          variants:null,
          related: catalog.getRelatedProducts(),
          yml_sales_notes: $('.yml-wrapper input[name=yml_sales_notes]').val(),       
        }
      } else {  
     
        var packedProperty = {
          mguniqueurl:"action/saveProduct",
          id: id,
          title: $('.product-text-inputs input[name=title]').val(),
          link_electro: $('.product-text-inputs input[name=link_electro]').val(),
          code: $('.variant-table tr').eq(1).find('input[name=code]').val(),
          price: $('.variant-table tr').eq(1).find('input[name=price]').val(),
          old_price: $('.variant-table tr').eq(1).find('input[name=old_price]').val(),
          count: $('.variant-table tr').eq(1).find('input[name=count]').val(),
          weight: $('.variant-table tr').eq(1).find('input[name=weight]').val(),        
          url: $('.product-text-inputs input[name=url]').val(),         
          image_url: catalog.createFieldImgUrl(),
          image_title: catalog.createFieldImgTitle(),
          image_alt: catalog.createFieldImgAlt(),          
          delete_image: catalog.deleteImage,
          cat_id: $('.product-text-inputs select[name=cat_id]').val(),
          inside_cat: catalog.createInsideCat(),
          description: $('textarea[name=html_content]').val(),
          meta_title: $('.seo-wrapper input[name=meta_title]').val(),
          meta_keywords: $('.seo-wrapper input[name=meta_keywords]').val(),
          meta_desc: $('.seo-wrapper textarea[name=meta_desc]').val(),
          currency_iso: $('.add-product-form-wrapper select[name=currency_iso]').val(),  
          recommend: recommend,
          activity: activity,
          new:newprod,
          userProperty: userProperty.getUserFields(),
          variants:variants,
          related: catalog.getRelatedProducts(),
          yml_sales_notes: $('.yml-wrapper input[name=yml_sales_notes]').val(),   
        }
         
      }
      
      catalog.deleteImage = '';
      
      // отправка данных на сервер для сохранеиня
      admin.ajaxRequest(packedProperty,
        function(response) {
          admin.clearGetParam();          
          admin.indication(response.status, response.msg);
          
          var row = catalog.drawRowProduct(response.data);

          // Вычисляем, по наличию характеристики 'id',
          // какая операция производится с продуктом, добавление или изменение.
          // Если id есть значит надо обновить запись в таблице.
          if(packedProperty.id){
            $('.product-tbody tr[id='+packedProperty.id+']').replaceWith(row);
          }else{
            // Если id небыло значит добавляем новую строку в начало таблицы.
            if($('.product-tbody tr:first').length>0){
              $('.product-tbody tr:first').before(row);
            } else{
              $('.product-tbody ').append(row);
            }
            
            var newCount = $('.widget-table-title .produc-count strong').text()-0+1;
            if(response.status=='success'){
              $('.widget-table-title .produc-count strong').text(newCount);
            }
          }


           $('.no-results').remove();

          // Закрываем окно
          admin.closeModal($('.b-modal'));
          admin.initToolTip();
        }
      );
    },

    cloneProd: function(id) {
     // получаем с сервера все доступные пользовательские параметры
      admin.ajaxRequest({
         mguniqueurl:"action/cloneProduct",
         id:id
       },
       function(response) {
            admin.indication(response.status, response.msg);  
              var row = catalog.drawRowProduct(response.data);

                // Если id небыло значит добавляем новую строку в начало таблицы.
                if($('.product-tbody tr:first').length>0){
                  $('.product-tbody tr:first').before(row);
                } else{
                  $('.product-tbody ').append(row);
                }
                
              var newCount = $('.widget-table-title .produc-count strong').text()-0+1;
              if(response.status=='success'){
                $('.widget-table-title .produc-count strong').text(newCount);
              }

        }
       );
    },
    /**
     * изменяет строки в таблице товаров при редактировании изменении.
     */
    drawRowProduct: function(element) {      
        if(!element.real_price){
          element.real_price = element.price;
        }
      // получаем название категории из списка в форме, чтобы внести в строку в таблице
          var cat_name = $('.product-text-inputs select[name=cat_id] option[value='+element.cat_id+']').text();
          if (cat_name.indexOf(' -- ') != -1) {
            cat_name = cat_name.replace(/ -- /g, '');
            cat_name = '<a class="parentCat tool-tip-bottom" title="" style="cursor:pointer;">../</a>' + cat_name;
          }              
          // получаем URL имеющейся картинки товара, если она была
          var src=$('tr[id='+element.id+'] .image_url .uploads').attr('src');
          
          if(element.image_url){
            // если идет процесс обновления и картинка новая то обновляем путь к ней
            src=element.image_url;
          }else {
            src=admin.SITE+'/mg-admin/design/images/no-img.png'
          }

          if(element.image_url=='no-img.png') {
            src=admin.SITE+'/mg-admin/design/images/no-img.png'
          }

          // переменная для хранения класса для подсветки активности товара
          var classForTagActivity='activity-product-true';
     
          var recommend = element.recommend==='1'?'active':'';        
          var titleRecommend = element.recommend?lang.PRINT_IN_RECOMEND:lang.PRINT_NOT_IN_RECOMEND;  
          
          var $new = element.new==='1'?'active':'';        
          var titleNew = element.new?lang.PRINT_IN_NEW:lang.PRINT_NOT_IN_NEW;  
          
          var activity = element.activity==='1'?'active':'';        
          var titleActivity = element.activity?lang.ACT_V_CAT:lang.ACT_UNV_CAT;  
          
          var printPrice = false;    
          
          // построение  ячейки с ценами
          var tdPrice ='<td  class="price">';     
          tdPrice += '<table class="variant-row-table">';
          if(element.price_course && !element.variants){
           if(element.price_course!=element.real_price){
           printPrice = true;
            tdPrice +='<tr><td colspan="3">';           
            tdPrice +='<span class="view-price tool-tip-bottom" style="color: '+((element.price_course>element.real_price)?"#1C9221":"#B42020")+'" title="с учетом скидки/наценки">'+admin.numberFormat(element.price_course)+' '+admin.CURRENCY+'</span><div class="clear"></div>';
            tdPrice += '</td>';
            tdPrice += '</tr>';
           }
          }   
           if(element.variants){
             element.variants.forEach(function (variant, index, array) {
              if(variant.price_course){
                if(variant.price_course!=variant.real_price){
                  printPrice = true;		
                    		  
                    if (variant.price != variant.price_course) {
					   
                        tdPrice +='<tr><td colspan="3">';                        
                                tdPrice +='<span class="view-price tool-tip-bottom" style="color: '+((variant.price_course>variant.price)?"#1C9221":"#B42020")+'" title="с учетом скидки/наценки">'+admin.numberFormat(variant.price_course)+' '+admin.CURRENCY+'</span><div class="clear"></div>';
                            tdPrice += '</td>';
                        tdPrice += '</tr>';
                    }else{
					  if(index>0){
					    tdPrice +='<tr><td colspan="3" height="15"></td></tr>';
				      }	
					}
                }   
              }  
			  tdPrice +='<tr class="variant-price-row"><td>';  
              tdPrice +='<span class="price-help">'+variant.title_variant+'</span></td><td><input style="width: 45px;" class="variant-price fastsave" type="text" value="'+variant.price+'" data-packet="{variant:1,id:'+variant.id+',field:\'price\'}"/></td><td>'+ catalog.getShortIso(element.currency_iso) +'<div class="clear"></div></td></tr>';
             }
           );                    
          }else{
		    tdPrice +='<tr class="variant-price-row"><td>';  
            tdPrice += '</td><td><input style="width: 45px;" type="text" value="'+element.real_price+'" class="fastsave" data-packet="{variant:0,id:'+element.id+',field:\'price\'}"/></td><td> '+catalog.getShortIso(element.currency_iso)+'<div class="clear"></div></td></tr>';
          }
          
          tdPrice += '</table>';
          tdPrice += '</td>';
         
         
         // построение  ячейки с остатками вариантов товара
          var tdCount ='<td class="count">';     
          var margin =''; 
          if(printPrice){
            margin ='';
          }
           if(element.variants){
             element.variants.forEach(function (variant, index, array) {
             			  			 
               if(variant.count<0){variant.count='∞'}
			   if(index>0 || variant.price_course!=variant.price){
			     margin ='margin-top:15px;';
			   }
               tdCount +='<div style=" '+margin+'"><input style="width: 25px;" class="variant-count fastsave" type="text" value="'+variant.count+'" data-packet="{variant:1,id:'+variant.id+',field:\'count\'}"/> '+lang.UNIT+'</div>';
             }
           );                                
          }else{
            if(element.count<0){element.count='∞'}
			if(element.price_course!=element.price){
			     margin ='margin-top:15px;';
			}
            tdCount += '<div style=" '+margin+'"><input style="width: 25px;" type="text" value="'+element.count+'" class="fastsave" data-packet="{variant:0,id:'+element.id+',field:\'count\'}"/> '+lang.UNIT+'</div>';
          }
          tdCount += '</td>'
         
          // html верстка для  записи в таблице раздела
          var row='\
            <tr id="'+element.id+'" data-id="'+element.id+'" class="product-row">\
             <td class="check-align"><input type="checkbox" name="product-check"></td>\
              <td class="id">'+element.id+'</td>\
              <td id="'+element.cat_id+'" class="cat_id">'+cat_name+'</td>\
              <td class="product-picture image_url">\
                <img class="uploads" src="'+src+'"/>\
              </td>\
              <td class="name">'+element.title+'<a class="link-to-site tool-tip-bottom" title="'+lang.PRODUCT_VIEW_SITE+'" href="'+mgBaseDir+'/'+(element.category_url?element.category_url:"catalog")+'/'+element.product_url+'"  target="_blank" ><img src="'+mgBaseDir+'/mg-admin/design/images/icons/link.png" alt="" /></a></td>\
              '+tdPrice+'\
              '+tdCount+'\
              <td class="actions">\
                <ul class="action-list">\
                  <li class="edit-row" id="'+element.id+'"><a href="javascript:void(0);" title="'+lang.EDIT+'"></a></li>\
                  <li class="tool-tip-bottom new '+$new+'" data-id="'+element.id+'" title="'+titleNew+'" ><a href="javascript:void(0);"></a></li>\
                  <li class="tool-tip-bottom recommend '+recommend+'" data-id="'+element.id+'" title="'+titleRecommend+'" ><a href="javascript:void(0);"></a></li>\
                  <li class="clone-row" id="'+element.id+'"><a href="javascript:void(0);" title="'+lang.CLONE+'"></a></li>\
                  <li class="visible tool-tip-bottom '+activity+'" data-id="'+element.id+'" title="'+titleActivity+'" ><a href="javascript:void(0);"></a></li>\
                  <li class="delete-order" id="'+element.id+'"><a href="javascript:void(0);"  title="'+lang.DELETE+'"></a></li>\
                </ul>\
              </td>\
           </tr>';          
     
        return row;
    },

    /**
     * Получает данные о продукте с сервера и заполняет ими поля в окне.
     */
    editProduct: function(id) {
      admin.ajaxRequest({
        mguniqueurl:"action/getProductData",
        id: id
      },
      catalog.fillFileds(),
      $('.add-product-form-wrapper .add-img-form')      
      );
    },


    /**
     * Удаляет продукт из БД сайта и таблицы в текущем разделе
     */
    deleteProduct: function(id,imgFile,massDel) {
      var confirmed = false;
      if(!massDel){
        if(confirm(lang.DELETE+'?')){
          confirmed = true;
        }
      } else {
        confirmed = true;
      }
      if(confirmed){
        admin.ajaxRequest({
          mguniqueurl:"action/deleteProduct",
          id: id,
          imgFile: imgFile,
          msgImg: true
        },
        function(response) {
          if(!massDel){admin.indication(response.status, response.msg);}
          $('.product-table tr[id='+id+']').remove();
          var newCount = ($('.widget-table-title .produc-count strong').text()-1);
          if(newCount>=0){
            $('.widget-table-title .produc-count strong').text(($('.widget-table-title .produc-count strong').text()-1));
          }
            if($(".product-table tr").length==1){
                var html ='<tr class="no-results">\
                <td colspan="10" align="center">'+lang.PROD_NONE+'</td>\
               </tr>';
              $(".product-table").append(html);
            };
          }
        );
      }

    },


    /**
     * Выполняет выбранную операцию со всеми отмеченными товарами
     * operation - тип операции.
     */
    runOperation: function(operation) { 
      
      var products_id = [];
      $('.product-tbody tr').each(function(){              
        if($(this).find('input[name=product-check]').prop('checked')){  
          products_id.push($(this).attr('id'));
        }
      });  
      var notice = (operation.indexOf('changecur') != -1) ? lang.RUN_NOTICE : ''
     
      if (confirm(lang.RUN_CONFIRM + notice)) {        
        admin.ajaxRequest({
          mguniqueurl: "action/operationProduct",
          operation: operation,
          products_id: products_id,
        },
        function(response) {
          if(response.data.clearfilter){
            admin.show("catalog.php","adminpage","refreshFilter=1",admin.sliderPrice);
          }else{
           if(response.data.filecsv) {
            admin.indication(response.status, response.msg);
            setTimeout(function() {
              if (confirm('Файл с выгрузкой создан в корне сайта под именем: '+response.data.filecsv+' Желаете скачать сейчас?')){
              location.href = mgBaseDir+'/'+response.data.filecsv;
            }}, 2000);            
           }
           if(response.data.fileyml) {
            admin.indication(response.status, response.msg);
            setTimeout(function() {
              if (confirm('Файл с выгрузкой создан в корне сайта под именем: '+response.data.fileyml+' Желаете скачать сейчас?')){
              location.href = mgBaseDir+'/mg-admin?yml=1&filename='+response.data.fileyml;
            }}, 2000);            
           }
           admin.refreshPanel();  
         }
        }
        );
      }
       

    },     
    
    /**
    * Формирует HTML для добавления и удалени картинки
    */
    drawControlImage:function(url,main,filename,title,alt) {
      var mainclass="main-img-prod";
      if(main==true){
        mainclass='small-img';
      } 
      
      return '\
        <div class="product-upload-img '+mainclass+'" data-filename="'+filename+'">\
            <div class="seo-image-block" style="display:none">\
            <div class="alt-title-block">\
              <div class="add-image-field">\
                <span>title:</span>\
                <input type="text" name="image_title" value="'+title+'">\
                <span>alt:</span>\
                <input type="text" name="image_alt" value="'+alt+'">\
              </div>\
              <a class="apply-seo-image fl-right custom-btn" href="javascript:void(0);"><span>Применить</span></a>\
              <div class="clear"></div>\
            </div>\
          </div>\
             <div class="product-img-prev">\
             <div class="seo-img-btn">\
                <a href="javascript:void(0);" class="seo-image tool-tip-bottom" title="SEO настройка">\
                    <span></span>\
                </a>\
            </div>\
             <a href="javascript:void(0);" class="main-image tool-tip-bottom" title="По умолчанию"><span></span></a>\
              <div class="img-loader" style="display:none"></div>\
              <div class="prev-img"><img src="'+url+'" alt="'+filename+'" /></div>\
             <form class="imageform" method="post" noengine="true" enctype="multipart/form-data">\
                <a href="javascript:void(0);" class="add-img-wrapper">\
                <span>'+lang['UPLOAD']+'</span>\
                  <input type="file" name="photoimg" class="add-img tool-tip-top" title="'+lang['UPLOAD_IMG']+'">\
                </a>\
              </form>\
              <a href="javascript:void(0);" class="cancel-img-upload tool-tip-top" title="'+lang['T_TIP_DEL_IMG_PROD']+'"><span>'+lang['DELETE']+'</span></a>\
              <div class="clear"></div>\
            </div>\
      </div>';
      
    },

   /**
    * Заполняет поля модального окна данными
    */
    fillFileds:function() {
  
      return function(response) {            
        var imageDir = Math.floor(response.data.id/100)+'00/'+response.data.id+'/';
        
        catalog.supportCkeditor = response.data.description;
        $('.product-desc-wrapper textarea[name=html_content]').text(response.data.description);
        $('.product-text-inputs input').removeClass('error-input');
        $('.product-text-inputs input[name=title]').val(response.data.title);
        $('.product-text-inputs input[name=link_electro]').val(response.data.link_electro),
        $('.section-catalog .del-link-electro').text(response.data.link_electro.substr(0,50));   
        $('.section-catalog .del-link-electro').attr('title',response.data.link_electro);
        if(response.data.link_electro){
          $('.section-catalog .del-link-electro').show(); 
          $('.section-catalog .add-link-electro').hide(); 
        }
        $('.product-text-inputs select[name=cat_id]').val(response.data.cat_id);
        $('.product-text-inputs input[name=url]').val(response.data.url);
           
        catalog.selectCategoryInside(response.data.inside_cat);
        catalog.cteateTableVariant(response.data.variants, imageDir);
     
        if(!response.data.variants){          
          
          $('.product-text-inputs input[name=code]').val(response.data.code);
          $('.product-text-inputs input[name=price]').val(response.data.price);
          $('.product-text-inputs input[name=old_price]').val(response.data.old_price);   
          $('.product-text-inputs input[name=weight]').val(response.data.weight);
          //превращаем минусовое значение в знак бесконечности
          var val = response.data.count;
          if((val=='\u221E'||val==''||parseFloat(val)<0)){val = '∞';}
          $('.product-text-inputs input[name=count]').val(val);          
        }
        
        var rowMain = '';
        var rows = '';
   
        response.data.images_product.forEach(        
          function (element, index, array) {   
            var title=response.data.images_title[index]?response.data.images_title[index]:'';
            var alt=response.data.images_alt[index]?response.data.images_alt[index]:'';
            
            var src=admin.SITE+'/mg-admin/design/images/no-img.png';
            if(element){
              var src=element;
            }
            
            if(index!=0){
              rows += catalog.drawControlImage(src, true, element, title, alt);        
            } else {
              rowMain = catalog.drawControlImage(src, false, element, title, alt);  
            }
           
          }
        );
          
        $('.small-img-wrapper').before(rowMain);
        $('.small-img-wrapper').prepend(rows);
        $('.main-img-prod .main-image').hide();   
        $('textarea[name=html_content]').val(response.data.description);        
        $('.seo-wrapper input[name=meta_title]').val(response.data.meta_title);
        $('.seo-wrapper input[name=meta_keywords]').val(response.data.meta_keywords);
        $('.seo-wrapper textarea[name=meta_desc]').val(response.data.meta_desc); 
        $('.yml-wrapper input[name=yml_sales_notes]').val(response.data.yml_sales_notes);
        catalog.drawRelatedProduct(response.data.relatedArr);
        $('.save-button').attr('id',response.data.id);    
        $('.save-button').data('recommend',response.data.recommend);
        $('.save-button').data('activity',response.data.activity);
        $('.save-button').data('new',response.data.new); 
        $('.b-modal_close').attr('item-id', response.data.id);
        $('.cancel-img-upload').attr('id',response.data.id);
        $('.userField').html('');

        try{
          $('.symbol-count').text($('.seo-wrapper textarea[name=meta_desc]').val().length);
        }catch(e){
          $('.symbol-count').text('0');
        }
         
        var iso = response.data.currency_iso?response.data.currency_iso:admin.CURRENCY_ISO;       
        $('#add-product-wrapper .btn-selected-currency').text(catalog.getShortIso(iso));                    
        $('.add-product-form-wrapper select[name=currency_iso] option[value='+JSON.stringify(iso)+']').prop('selected','selected')
      
        /*
         admin.ajaxRequest({
          mguniqueurl:"action/getProdDataWithCat",
          produtcId: produtcId,
          categoryId: categoryId
        },
        function(response) {     
          userProperty.createUserFields($('.userField'), response.data.thisUserFields, response.data.allProperty);
          admin.initToolTip();        
        },
           */     
    
        userProperty.createUserFields($('.userField'), response.data.prodData.thisUserFields, response.data.prodData.allProperty);
        $('.user-field-table tr td .value').each(function(){
          var value = $(this).text();
          if (value) {
            $(this).text(admin.htmlspecialchars(value));
          }
        })
        //admin.initToolTip();          
        //catalog.generateUserProreprty(response.data.id, response.data.cat_id);
        
        // Проверка на наличии поля в возвращаемом результате, для вывода предупреждения,
        // если этот товар является комплектом товаров, созданным в плагине "Комплект товаров"
        
       if (response.data.plugin_message) {
         $('#add-product-wrapper .add-product-table-icon').append(response.data.plugin_message);
       }
      }
    },


   /**
    * Чистит все поля модального окна
    */
    clearFileds:function() {
      
      $('.product-text-inputs input[name=title]').val('');
      $('.product-text-inputs input[name=link_electro]').val(''),
      $('.product-text-inputs input[name=url]').val('');
      $('.product-text-inputs input[name=code]').val('');
      $('.product-text-inputs input[name=price]').val('');
      $('.product-text-inputs input[name=old_price]').val('');   
      $('.product-text-inputs input[name=count]').val('');    
      
      catalog.selectCategoryInside('');
     
      var catId = $('.filter-container select[name=cat_id]').val();       
      if(catId == 'null'){
        catId = 0;
      }
      
      $('select[name=inside_cat]').attr('size',4);         
      $('.full-size-select-cat').removeClass('opened-select-cat').addClass('closed-select-cat');
      $('.full-size-select-cat').text(lang.PROD_OPEN_CAT);
      
      
      $('.product-text-inputs select[name=cat_id]').val(catId);      
      
      $('.prod-gallery').html('<div class="small-img-wrapper"></div>');
      $('textarea[name=html_content]').val(''); 
      $('.seo-wrapper input[name=meta_title]').val('');
      $('.seo-wrapper input[name=meta_keywords]').val('');
      $('.seo-wrapper textarea[name=meta_desc]').val('');
      $('.yml-wrapper input[name=yml_sales_notes]').val(''),   
      $('.product-text-inputs .variant-table').html('');     
      $('.added-related-product-block').html('');
      $('.added-related-product-block').css('width',"800px"); 
      $('.userField').html('');
      $('.symbol-count').text('0');
      $('.save-button').attr('id','');
      $('.save-button').data('recommend','0');
      $('.save-button').data('activity','1');
      $('.save-button').data('new','0');     
      $('.select-product-block').hide();
      catalog.cteateTableVariant(null);
      catalog.deleteImage ='';

      $('.del-link-electro').hide(); 
      $('.add-link-electro').show();
      // Стираем все ошибки предыдущего окна если они были.
      $('.errorField').css('display','none');
      
      $('.add-product-form-wrapper .select-currency-block').hide();
         
      var short = catalog.getShortIso(admin.CURRENCY_ISO);    
      $('#add-product-wrapper .btn-selected-currency').text(short);
      $('.add-product-form-wrapper select[name=currency_iso] option[value='+admin.CURRENCY_ISO+']').prop('selected','selected');
      $('.error-input').removeClass('error-input');
      
      catalog.supportCkeditor = '';
      $('.addedProperty').html('');
    },


   /**
    * Добавляет изображение продукта
    */
    addImageToProduct:function(img_container) {
      var currentImg = '';
      img_container.find('.img-loader').show();      
      
      if(img_container.find('.prev-img img').length > 0){
        currentImg = img_container.find('.prev-img img').attr('alt');
      }else{
        currentImg = img_container.find('img').attr('data-filename');
      }
      
      //Пишем в поле deleteImage имена изображений, которые необходимо будет удалить при сохранении
      if(catalog.deleteImage){
        catalog.deleteImage += '|'+currentImg;
      }else{
        catalog.deleteImage = currentImg;
      }
      
      // отправка картинки на сервер
      img_container.find('.imageform').ajaxForm({
        type:"POST",
        url: "ajax",
        data: {
          mguniqueurl:"action/addImage"
        },
        cache: false,
        dataType: 'json',
        success: function(response){
          admin.indication(response.status, response.msg);
          if(response.status != 'error'){
            var src=admin.SITE+'/uploads/'+response.data.img;
            catalog.tmpImage2Del += '|'+response.data.img;
            img_container.find('.prev-img').html('<img src="'+src+'" alt="'+response.data.img+'" />');
          }else{
            var src=admin.SITE+'/mg-admin/design/images/no-img.png';
            img_container.find('.prev-img').html('<img src="'+src+'" alt="'+response.data.img+'" />');
          }
         img_container.find('.img-loader').hide();
        }
      }).submit();
    },
    
    /**
     *  собирает названия файлов всех картинок чтобы сохранить их в БД в поле image_url
     */  
    createFieldImgUrl: function() {         
       var image_url = "";   
       $('.prod-gallery .prev-img img').each(function(){    
         if($(this).attr('alt') && $(this).attr('alt')!='undefined'){
           image_url+=$(this).attr('alt')+'|';
         }
       });   

       if(image_url){
         image_url = image_url.slice(0,-1);
       }
       
       return image_url;
    }, 

    /**
     *  собирает все заголовки для картинок, чтобы сохранить их в БД в поле image_title
     */  
    createFieldImgTitle: function() {         
       var image_title = "";   
       $('.prod-gallery .prev-img img').each(function(){    
         if($(this).attr('alt') && $(this).attr('alt')!='undefined'){
           var title = $(this).parents('.product-upload-img').find('input[name=image_title]').val();
           title = title.replace('|','');
           image_title+=title+'|';
         }
       });   

       if(image_title){
         image_title = image_title.slice(0,-1);
       }
  
       return image_title;
    }, 
            
     /**
     *  собирает все описания для картинок, чтобы сохранить их в БД в поле image_alt
     */  
    createFieldImgAlt: function() {         
       var image_alt = "";   
       $('.prod-gallery .prev-img img').each(function(){    
         if($(this).attr('alt') && $(this).attr('alt')!='undefined'){
           var title = $(this).parents('.product-upload-img').find('input[name=image_alt]').val();
           title = title.replace('|','');
           image_alt+=title+'|';
         }
       });   

       if(image_alt){
         image_alt = image_alt.slice(0,-1);
       }
  
       return image_alt;
    }, 

   /**
     * Помещает  выбранную основной картинку в начало ленты  
     * removemain = true - была удалена главная и требуется поднять из лены первую на место главной
     */
    upMainImg: function(obj, removemain) {
      var oldMain = ''; 
      if(!removemain){
        // для главной картинки меняем классы сохраняем в буфер и удаляем      
        oldMain = $('.main-img-prod'); 
        oldMain.find('.main-image').show();
        oldMain.removeClass('main-img-prod').addClass('small-img');         
      }   
      $('.main-img-prod').remove();

      
      // выбранную картинку удаляем из ленты, добавляем классы как для главной и помещаем на место главной
      var bufer = obj;  
      obj.remove();
      bufer.removeClass('small-img').addClass('main-img-prod');
      bufer.find('.main-image').hide();
      
      $('.small-img-wrapper').before(bufer);
      $('.small-img-wrapper').prepend(oldMain);
   
    },

   /**
    * Удаляет изображение продукта
    */
    delImageProduct: function(id,img_container) {      
      var imgFile = img_container.find('.prev-img img').attr('src');            
      
      if(confirm(lang.DELETE_IMAGE+'?')){      
        catalog.deleteImage += "|"+imgFile;
        // удаляем текущий блок управления картинкой        
        if($('.prod-gallery .prev-img img').length>1){ 
          if(img_container.hasClass('main-img-prod')){
              catalog.upMainImg($('.small-img').eq(0), true);
          }else{
            img_container.remove();
          }
        } else{
          // если блок единственный, то просто заменяем в нем картнку на заглушку
          var src=admin.SITE+'/mg-admin/design/images/no-img.png';
          img_container.find('.prev-img img').attr('src',src).attr('alt',''); 
          img_container.data('filename','');           
        }            
      $('#tiptip_holder').hide();
      admin.ajaxRequest({
          mguniqueurl:"action/deleteImageProduct",
          imgFile: imgFile,   
          id: id,
      },
      function(response) {
        admin.indication(response.status, response.msg);      
      });
     }
    },

   /**
    * Поиск товаров
    */
    getSearch: function(keyword) { 
      keyword = $.trim(keyword);
      if(keyword == lang.FIND+"..."){
        keyword = '';
      }
      if(!keyword){
        admin.indication('error', 'Введите поисковую фразу');    
        return false
      };
      
      admin.ajaxRequest({
          mguniqueurl:"action/searchProduct",
          keyword:keyword,
          mode: 'groupBy',
      },
      function(response) {
        admin.indication(response.status, response.msg);     
        $('.product-tbody tr').remove();
        response.data.forEach(
          function (element, index, array) {
             var row = catalog.drawRowProduct(element);
             $('.product-tbody').append(row);
          });
          // Если в результате поиска ничего не найдено
          if(response.data.length==0){    
            var row = "<tr><td class='no-results' colspan='"+$('.product-table th').length+"'>"+lang.SEARCH_PROD_NONE+"</td></tr>"
            $('.product-tbody').append(row);
          }
          $('.mg-pager').hide();
        }
      );
    },


    //  Получает данные из формы фильтров и перезагружает страницу
    getProductByFilter: function(){
       var request = $("form[name=filter]").formSerialize();
       var insideCat = $('input[name="insideCat"]').prop('checked');     
       admin.show("catalog.php","adminpage",request+'&insideCat='+insideCat+'&applyFilter=1',catalog.callbackProduct);    
       return false;
    },
    
    // Устанавливает статус продукта - рекомендуемый
     recomendProduct:function(id, val) {
      admin.ajaxRequest({
        mguniqueurl:"action/recomendProduct",
        id: id,
        recommend: val,
      },
      function(response) {
        admin.indication(response.status, response.msg);
      } 
      );
    },
    
    // Устанавливает статус - видимый
     visibleProduct:function(id, val) {
      admin.ajaxRequest({
        mguniqueurl:"action/visibleProduct",
        id: id,
        activity: val,
      },
      function(response) {
        admin.indication(response.status, response.msg);
      } 
      );
    },
    
    // вывод в новинках
    newProduct:function(id, val) {
      admin.ajaxRequest({
        mguniqueurl:"action/newProduct",
        id: id,
        new: val,
      },
      function(response) {
        admin.indication(response.status, response.msg);
      } 
      );
    },
    
     // Добавляет строку в таблицу вариантов
    cteateTableVariant:function(variants, imageDir) { 
      
      admin.ajaxRequest({
        mguniqueurl:"action/nextIdProduct",
      },
      function(response) {
        if (!$('.product-text-inputs .variant-table .default-code').val()){
          var id = response.data.id;
          $('.product-text-inputs .variant-table .default-code').val("CN"+id);
        }       
      } 
      );
      // строим первую строку заголовков        
      $('.product-text-inputs .variant-table').html('');
      if(variants){
        var position ='\
        <tr>\
          <th class="hide-content">'+lang.NAME_VARIANT+'</th>\
          <th>'+lang.CODE_PRODUCT+'</th>\
          <th>'+lang.PRICE_PRODUCT+'/<a href="javascript:void(0);" class="btn-selected-currency">'+admin.CURRENCY+'</a></th>\
          <th>'+lang.OLD_PRICE_PRODUCT+'</th>\
          <th>'+lang.WEIGHT+'</th>\
          <th>'+lang.UNIT +'</th>\
          <th class="hide-content"></th>\
        </tr>\ ';
        $('.variant-table').append(position);  
        // заполняем вариантами продукта
        variants.forEach(function(variant, index, array) {
          var src = admin.SITE+"/mg-admin/design/images/no-img.png";
          if(variant.image){
            src = variant.image;
          }
          
          if(variant.count<0){variant.count='∞'};
          var position ='\
          <tr data-id="'+variant.id+'"  class="variant-row">\
             <td class="hide-content">\
              <label for="title_variant"><input style="width: 120px;" type="text" name="title_variant" value="'+variant.title_variant+'" class="product-name-input tool-tip-right" title="'+lang.NAME_PRODUCT+'" ><div class="errorField">'+lang.NAME_PRODUCT+'</div></label>\
            </td>\
            <td>\
              <label for="code"><input style="width: 50px;" type="text" name="code" value="'+variant.code+'" class="product-name-input tool-tip-right" title="'+lang.T_TIP_CODE_PROD+'" ><div class="errorField">'+lang.ERROR_EMPTY+'</div></label>\
            </td>\
            <td>\
              <label for="price"><input style="width:60px;" type="text" name="price" value="'+variant.price+'" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_PRICE_PROD+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="old_price"><input style="width:60px;" type="text" name="old_price" value="'+variant.old_price+'" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_OLD_PRICE+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="weight"><input style="width:30px;" type="text" name="weight" value="'+variant.weight+'" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_WEIGHT_PROD+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="count"><input style="width:30px;" type="text" name="count" value="'+variant.count+'" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_COUNT_PROD+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td class="hide-content actions">\
            <div class="variant-dnd"></div>\
            <div class="img-this-variant" style="display:none;">\
            <img src="'+src+'" style="width:50px; min-height:100%" data-filename="'+variant.image+'">\
            </div>\
              <form method="post" noengine="true" enctype="multipart/form-data" class="img-button">\
              <span class="add-img-clone"></span>\
                <input type="file" name="photoimg" class="add-img-var img-variant">\
              </form>\
            <a href="javascript:void(0);" class="del-variant">Удалить</a>\
            </td>\
          </tr>\ ';
          $('.variant-table').append(position);
          $('.variant-table-wrapper').css('width','620px');
          $('.variant-table tr td').css('padding','5px 10px 5px 0');
        });      
        $('.variant-table').data('have-variant','1');
      }else{
        
        var position ='\
        <tr>\
          <th style="display:none" class="hide-content">'+lang.NAME_VARIANT+'</th>\
          <th>'+lang.CODE_PRODUCT+'</th>\
          <th>'+lang.PRICE_PRODUCT+'/<a href="javascript:void(0);" class="btn-selected-currency">'+admin.CURRENCY+'</a></th>\
          <th>'+lang.OLD_PRICE_PRODUCT+'</th>\
          <th>'+lang.WEIGHT+'</th>\
          <th>'+lang.UNIT+'</th>\
          <th style="display:none" class="hide-content"></th>\
        </tr>\ ';
        $('.variant-table').append(position);  
          var position ='\
          <tr class="variant-row">\
            <td style="display:none" class="hide-content">\
              <label for="title_variant"><input style="width: 120px;" type="text" name="title_variant" value="" class="product-name-input tool-tip-right" title="'+lang.NAME_PRODUCT+'" ><div class="errorField">'+lang.ERROR_EMPTY+'</div></label>\
            </td>\
            <td>\
              <label for="code"><input style="width: 50px;" type="text" name="code" value="" class="product-name-input tool-tip-right default-code" title="'+lang.T_TIP_CODE_PROD+'" ><div class="errorField">'+lang.ERROR_EMPTY+'</div></label>\
            </td>\
            <td>\
              <label for="price"><input style="width:60px;" type="text" name="price" value="" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_PRICE_PROD+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="old_price"><input style="width:60px;" type="text" name="old_price" value="" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_OLD_PRICE+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="weight"><input style="width:30px;" type="text" name="weight" value="" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_WEIGHT_PROD+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td>\
              <label for="count"><input style="width:30px;" type="text" name="count" value="∞" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_COUNT_PROD+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
            </td>\
            <td style="display:none" class="hide-content actions">\
            <div class="variant-dnd"></div>\
            <div class="img-this-variant" style="display:none;">\
            <img src="'+admin.SITE+'/mg-admin/design/images/no-img.png" data-filename="" style="width:50px; min-height:100%">\
            </div>\
              <form method="post" noengine="true" enctype="multipart/form-data" class="img-button">\
                <span class="add-img-clone"></span>\
                <input type="file" name="photoimg" class="add-img-var img-variant">\
              </form>\
            <a href="javascript:void(0);" class="del-variant">Удалить</a>\
            </td>\
          </tr>\ ';        
          $('.variant-table').append(position);
          $('.variant-table-wrapper').css('width','415px');
          $('.variant-table').data('have-variant','0');
          $('.variant-table').sortable({        
            opacity: 0.6,
            axis: 'y',
            handle: '.variant-dnd',   
            items: "tr+tr"
            }
          );


      }
    },
    
    
    // Добавляет строку в таблицу вариантов
    addVariant:function(table) {      
      if($('.variant-table').data('have-variant')=="0"){
        $('.variant-table .hide-content').show();
        $('.variant-table').data('have-variant','1');
      }
      var code = $('.variant-table input[name="code"]:first').val();
      
      var position ='\
      <tr class="variant-row">\
         <td class="hide-content">\
          <label for="title_variant"><input style="width: 120px;"  type="text" name="title_variant" class="product-name-input tool-tip-right" title="'+lang.NAME_PRODUCT+'" ><div class="errorField">'+lang.ERROR_EMPTY+'</div></label>\
        </td>\
        <td>\
          <label for="code"><input style="width: 50px;"  type="text" name="code" class="product-name-input tool-tip-right default-code" title="'+lang.T_TIP_CODE_PROD+'" value='+code+'-'+$('.variant-table input[name="code"]').length+'><div class="errorField">'+lang.ERROR_EMPTY+'</div></label>\
        </td>\
        <td>\
          <label for="price"><input style="width:60px;" type="text" name="price" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_PRICE_PROD+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
        </td>\
        <td>\
          <label for="old_price"><input style="width:60px;" type="text" name="old_price" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_OLD_PRICE+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div> </label>\
        </td>\
        <td>\
          <label for="weight"><input style="width:30px;" type="text" name="weight" value="" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_WEIGHT_PROD+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
        </td>\
        <td>\
          <label for="count"><input style="width:30px;" type="text" name="count" value="∞" class="product-name-input qty tool-tip-right" title="'+lang.T_TIP_COUNT_PROD+'" ><div class="errorField">'+lang.ERROR_NUMERIC+'</div></label>\
        </td>\
        <td class="hide-content actions">\
          <div class="variant-dnd"></div>\
          <div class="img-this-variant" style="display:none">\
          <img src="'+admin.SITE+'/mg-admin/design/images/no-img.png"  data-filename=""  style="width:50px; min-height:100%">\
          </div>\
          <form method="post" noengine="true" enctype="multipart/form-data" class="img-button">\
            <span class="add-img-clone"></span>\
            <input type="file" name="photoimg" class="add-img-var img-variant">\
           </form>\
          <a href="javascript:void(0);" class="del-variant">Удалить</a>\
        </td>\
      </tr>\ ';
      table.append(position);
      $('.variant-table-wrapper').css('width','620px');
      admin.initToolTip();
    
    },
    
 
    // возвращает пакет  вариантов собранный из таблицы вариантов
    getVariant: function(){
      catalog.errorVariantField = false;
     
      if($('.variant-table').data('have-variant')=="1"){      
        var result = [];        
        $('.variant-table .variant-row').each(function(){
           
          //собираем  все значения полей варианта для сохранения в БД
          
          var id =$(this).data('id');
          var currency_iso = $('.add-product-form-wrapper select[name=currency_iso] option:selected').val();
          var obj = '{';
          $(this).find('input').removeClass('error-input');        
          $(this).find('input').each(function() {   
            
            if($(this).attr('name')!='photoimg'){
              
              var val = $(this).val();
              if((val=='\u221E'||val==''||parseFloat(val)<0)&&$(this).attr('name')=="count"){val = "-1";}
              
              if(val==""&&$(this).attr('name')!='old_price'){               
                $(this).addClass('error-input');
                catalog.errorVariantField = true;
              }
              obj += '"' + admin.htmlspecialchars($(this).attr('name')) + '":"' + admin.htmlspecialchars(val) + '",';
            }
          });
          obj += '"activity":"1",';
          obj += '"id":"'+id+'",';
          obj += '"currency_iso":"'+currency_iso+'",';
       
          var filename = $(this).find('img[filename]').attr('filename');
          if(!filename){filename = $(this).find('img').data('filename')}
          obj += '"image":"'+filename+'",';         
          
          obj += '}';          
          //преобразуем полученные данные в JS объект для передачи на сервер
          result.push(eval("(" + obj + ")"));
        });
        
        return result;
      }
      return null;
    },
    
    // возвращает список id связанных товаров с редактируемым
    getRelatedProducts: function(){
      var result = '';       
      $('.add-related-product-block .product-unit').each(function(){    
        result += $(this).data('code') + ',';
      });       
      result = result.slice(0, -1);
     
      
      return result;     
    },            
        
    // сохраняет параметры товара прямо со страницы каталога в админке
    fastSave:function(data, val, input){
      var obj = eval("(" + data + ")");
      // Проверка поля для стоимости, является ли текст в него введенный числом.
     
      // знак бесконечности 
      if((val=='\u221E'||val==''||parseFloat(val)<0)&&obj.field=="count"){val = "-1"; input.val('∞'); }
 

      if(isNaN(parseFloat(val))){ 
        admin.indication('error', lang.ENTER_NUM);   
        input.addClass('error-input');        
        return false;   
      } else {
        input.removeClass('error-input');
      }
      var id = input.parents('.product-row').attr('id');
      // получаем с сервера все доступные пользовательские параметры
      admin.ajaxRequest({
        mguniqueurl:"action/fastSaveProduct",
        variant:obj.variant,
        id:obj.id,
        field:obj.field,
        value:val,
        product_id: id
      },
      function(response) {
        if (response.data) {
          $(".product-tbody tr#"+id+" .price").find(".view-price[data-productId="+obj.id+"]").text(response.data+' '+admin.CURRENCY);
        }
        admin.clearGetParam(); 
        admin.indication(response.status, response.msg);       
      });
      
    },
    
    
    importFromCsv:function(){
      admin.ajaxRequest({
        mguniqueurl:"action/importFromCsv",  
      },
      function(response) {
        admin.indication(response.status, response.msg);       
      });
    },
    
    /**
     * Загружает CSV файл на сервер для последующего импорта
     */
    uploadCsvToImport:function() {         
      // отправка файла CSV на сервер
      $('.message-importing').text('Идет передача файла на сервер. Подождите, пожалуйста...'); 
      $('.upload-csv-form').ajaxForm({
        type:"POST",
        url: "ajax",
        data: {
          mguniqueurl:"action/uploadCsvToImport"
        },
        cache: false,
        dataType: 'json',
        error: function() {alert("Загружаемый вами файл превысил максимальный объем и не может быть передан на сервер из-за ограничения в настройках файла php.ini\n\n{Внимание! Поддерживается загрузка zip архива с CSV файлом}");},
        success: function(response){
          admin.indication(response.status, response.msg);      
          if(response.status=='success'){
            $('.block-upload-сsv').hide();
            $('.block-importer').show();
            $('.message-importing').text('Файл готов к импорту товаров в каталог'); 
          }else{
            $('.message-importing').text('');
            $('.import-container input[name="upload"]').val('');
          }
        },
        
      }).submit();
    },
    
    /**
     * Контролирует процесс импорта, выводит индикатор в процентах обработки каталога.
     */
    startImport:function(rowId, percent) {
     var typeCatalog = $(".block-upload-сsv select[name=importType]").val();
     
     var delCatalog=null;
      if(!rowId){        
        if(!$('.loading-line').length) {     
          $('.process').append('<div class="loading-line"></div>');   
        }
        rowId = 0;
        delCatalog = $('input[name=no-merge]').val();
      }
      if(!percent){       
        percent = 0;
      }
      
           
      if(!catalog.STOP_IMPORT){
        $('.message-importing').html('Идет процесс импорта товаров. Загружено:'+percent+'%<div class="progress-bar"><div class="progress-bar-inner" style="width:'+percent+'%;"></div></div>');
      }else{
        $('.loading-line').remove();
      }     
      
      // отправка файла CSV на сервер      
      admin.ajaxRequest({
        mguniqueurl:"action/startImport",
        rowId:rowId,
        delCatalog:delCatalog,
        typeCatalog: typeCatalog
      },
      function(response){
        if(response.status=='error'){ 
          admin.indication(response.status, response.msg);  
        }
       
        if(response.data.percent<100){        
          if(response.data.status=='canseled'){
            $('.message-importing').html('Процесс импорта остановлен пользователем! Загружено: '+response.data.rowId+' товаров  [<a href="javascript:void(0);" class="repeat-upload-csv">Загрузить другой файл</a>]' );
            $('.block-importer').hide(); 
            $('.loading-line').remove();
          }else{            
            catalog.startImport(response.data.rowId,response.data.percent);
          }
        } else{
           $('.message-importing').html('Импорт товаров успешно завершен! <a class="refresh-page custom-btn" href="'+mgBaseDir+'/mg-admin/"><span>Обновите страницу</span></a>');
           $('.block-importer').hide();
           $('.loading-line').remove();
        } 
    
      });
    },
    
     /**
     * Клик по найденым товарам поиске в форме добавления связанного товара
     */
    addrelatedProduct: function(elementIndex, product) {      
      $('.search-block .errorField').css('display', 'none');
      $('.search-block input.search-field').removeClass('error-input');
      if(!product){
        var product = admin.searcharray[elementIndex];    
      }
      var html = catalog.rowRelatedProduct(product);       
      $('.added-related-product-block .product-unit[data-id='+product.id+']').remove();
      $('.related-wrapper .added-related-product-block').prepend(html);    
      catalog.widthRelatedUpdate();  
      catalog.msgRelated();
      $('input[name=searchcat]').val('');      
      $('.select-product-block').hide();
      $('.fastResult').hide();
    },
            
     /**
     * формирует верстку связанного продукта
     */        
    rowRelatedProduct: function(product) {  
      var price = (product.real_price) ? product.real_price : product.price;
      
      var html = '\
      <div class="product-unit" data-id='+ product.id +' data-code="'+ product.code +'">\
          <div class="product-img">\
              <a href="javascript:void(0);"><img src="' + product.image_url + '"></a>\
          </div>\
          <a href="' + mgBaseDir + '/' + product.category_url + "/" + product.product_url +
              '" data-url="' + product.category_url +
              "/" + product.product_url + '" class="product-name" target="_blank" title="' +
              product.title + '">' +
              product.title + '</a>\
          <span>' + admin.numberFormat(price) +' '+ admin.CURRENCY+'</span>\
          <a class="remove-added-product custom-btn" href="javascript:void(0);"><span></span></a>\
      </div>\
      ';
      return html;
    },
            
    //выводит связанные товары 
    //relatedProducts - массив с товарами
    drawRelatedProduct: function(relatedArr) {  
      relatedArr.forEach(function (product, index, array) {
        var html = catalog.rowRelatedProduct(product);      
        $('.related-wrapper .added-related-product-block').append(html);  
        catalog.widthRelatedUpdate();
      });
      catalog.msgRelated();
    }, 
    
    //выводит ссылку в пустом блоке для добавления связаного товара
    msgRelated: function() {    
      if($('.added-related-product-block .product-unit').length==0){
        $('.related-wrapper .added-related-product-block').append('\
         <a class="add-related-product in-block-message" href="javascript:void(0);"><span>'+lang.RELATED_PROD+'</span></a>\
       '); 
        $('.added-related-product-block').width('800px');
      }else{
        $('.added-related-product-block .add-related-product').remove();
      };  
    }, 
            
    //пересчитывает ширину блока с связанными товарами, для работы скрола.
    widthRelatedUpdate: function() {    
      var widthCanvas = $('.added-related-product-block').width();
      var widthUnit = $('.product-unit').width();
      if(!widthUnit){widthUnit = 105};   
      $('.added-related-product-block').css('width',(widthCanvas+widthUnit)+"px");          
    }, 
            
    /**
     * Останавливает процесс импорта в каталог товаров
     */
    canselImport:function() { 
      $('.message-importing').text('Происходит остановка импорта!');
      catalog.STOP_IMPORT=true;    
      admin.ajaxRequest({
        mguniqueurl:"action/canselImport"
      },
      function(response){
        admin.indication(response.status, response.msg);          
      });
    },
    
    /**
     *Пакет выполняемых действий после загрузки раздела товаров
     */
    callbackProduct:function() {   
    admin.sliderPrice();
    admin.AJAXCALLBACK = [      
      {callback:'admin.sortable', param:['.product-table > tbody','product']},
    ]; 
    },
            
    /**
     * Выделяет все категории в списке, в которых будет отображаться товар
     */
    selectCategoryInside:function(selectedCatIds) { 
      if(!selectedCatIds){
        $('.add-category').removeClass('opened-list'); 
        $('.inside-category').hide();
      }else{
        $('.add-category').addClass('opened-list'); 
        $('.inside-category').show();
      }
      var htmlOptionsSelected = selectedCatIds.split(',');  
      $('select[name=inside_cat] option').prop('selected', false);   
      function buildOption(element, index, array) {
        $('.inside-category select[name="inside_cat"] [value="' + element + '"]').prop('selected', 'selected');
      }
      ;
      htmlOptionsSelected.forEach(buildOption);
    },   
            
    /**
     * Возвращает список выбранных категорий для товара
     */
    createInsideCat: function() { 
      var category = '';
      $('select[name=inside_cat] option').each(function() {
        if ($(this).prop('selected')) {
          category += $(this).val() + ',';
        }
      });

      category = category.slice(0, -1);
      
      return category;
    },    
            
    /**
     * Возвращает список выбранных категорий для товара
     */
    getFileElectro: function(file) {        
      var dir = file.url;    
      dir= dir.replace(mgBaseDir, '');      
      $('.section-catalog input[name="link_electro"]').val(dir);    
      $('.section-catalog .del-link-electro').text(dir.substr(0,50));   
      $('.section-catalog .del-link-electro').attr('title',dir);
      $('.section-catalog .del-link-electro').show();   
      $('.section-catalog .add-link-electro').hide();   
    },    
    
    /**
     * Смена валюты  
     */
    changeIso: function() {          
     // var short_cur = $('.section-catalog .btn-selected-currency').text();
      var short = $('.add-product-form-wrapper select[name=currency_iso] option:selected').text();
      var rate = $('.add-product-form-wrapper select[name=currency_iso] option:selected').data('rate');
      $('#add-product-wrapper .btn-selected-currency').text(short);
      $('.add-product-form-wrapper .select-currency-block').hide();
      /*
      if(short_cur!=short){
        $('.add-product-form-wrapper input[name=price]').each(function() {   
          $(this).val($(this).val()*rate);
        });
      }   */
    }, 
            
            
    /** 
     * Возвращает сокращение, из списка допустимых валют  
     * @param {type} iso
     * @returns {undefined}
     */    
    getShortIso: function(iso){
      iso = JSON.stringify(iso);
      var short = $('.add-product-form-wrapper select[name=currency_iso] option[value='+iso+']').text();
      return short;
    },
    closeAddedProperty: function(type){
      if (type == 'close') {
        $('.addedProperty .new-added-prop').each(function() {
          var id = $(this).data('id');      
          admin.ajaxRequest({
            mguniqueurl: "action/deleteUserProperty",
            id: id
          })
        });
      }
      $('.product-table-wrapper .new-added-properties').hide();
      $('.product-table-wrapper .new-added-properties input').val('');
      $('.product-table-wrapper .new-added-properties input').removeClass('error-input');
      $('.new-added-properties .errorField').hide();
    },
    // добавляет новую характеристику
    addNewProperty: function (name, value) {
      admin.ajaxRequest({
        mguniqueurl: "action/addUserProperty",
      },
        function (response) {
          var id = response.data.allProperty.id;
          var html = '<div class="new-added-prop" data-id="' + id + '"> <span class="name-property">' + name + ':</span>\
             <input class="property custom-input" type="text" value="' + value + '"><a href="javascript:void(0);" class="remove-added-property"></a></div>';
          $('.product-table-wrapper .addedProperty').prepend(html);
          admin.ajaxRequest({
            mguniqueurl: "action/saveUserProperty",
            id: id,
            name: name,
          })
          var category = $('.product-text-inputs select[name=cat_id]').val();
          admin.ajaxRequest({
            mguniqueurl: "action/saveUserPropWithCat",
            id: id,
            category: category
          })
          })
      catalog.closeAddedProperty();
    },
     //Добавляет новую характеристику 
    saveAddedProperties: function () {
      $('.addedProperty .new-added-prop ').each(function () {
        var id = $(this).data('id');
        var category = $('.product-text-inputs select[name=cat_id]').val();
        admin.ajaxRequest({
          mguniqueurl: "action/saveUserPropWithCat",
          id: id,
          category: category
        })
      }) 
    },
  
  }
})();

// инициализациямодуля при подключении
catalog.init();
