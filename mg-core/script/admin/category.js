
/**
 * Модуль для  раздела "Категории".
 */
var category = (function() {
  return {
    wysiwyg: null, // HTML редактор для   редактирования страниц
    supportCkeditor: null, 
    openedCategoryAdmin: [], //массив открытых категорий
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init: function() {
      // восстанавливаем массив открытых значенией из куков
      category.openedCategoryAdmin = eval(cookie("openedCategoryAdmin"));
      if (!category.openedCategoryAdmin) {
        category.openedCategoryAdmin = [];
      }

      // Вызов модального окна при нажатии на кнопку добавления категории.      
      $('.admin-center').on('click', '.section-category .add-new-button', function() {
        category.openModalWindow('add');
      });

   

      // Вызов модального окна при нажатии на пункт изменения категории.
      $('.admin-center').on('click', '.section-category .edit-sub-cat', function() {
        category.openModalWindow('edit', $(this).attr('id'));
      });

      // Вызов модального окна при нажатии на пункт добавления подкатегории.
      $('.admin-center').on('click', '.section-category .add-sub-cat', function() {
        category.openModalWindow('addSubCategory', $(this).attr('id'));
      });

      // Удаление категории.
      $('.admin-center').on('click', '.section-category .delete-sub-cat', function() {
        category.deleteCategory($(this).attr('id'));
      });

      // Закрыть контекстное меню.
      $('.admin-center').on('click', '.section-category .cancel-sub-cat', function() {
        category.closeContextMenu();
      });

      // Сохранение продукта при на жатии на кнопку сохранить в модальном окне.
      $('body').on('click', '#add-category-wrapper .save-button', function() {
        category.saveCategory($(this).attr('id'));
      });      
      
       // Выбор картинки категории
      $('body').on('click', '#add-category-wrapper  .add-image-to-category', function() {
        admin.openUploader('category.getFile');
      });  
      
       // Удаление картинки категории
      $('body').on('click', '#add-category-wrapper  .del-image-to-category', function() {         
        $('#add-category-wrapper input[name=image_url]').val('');
        $('#add-category-wrapper .category-img-block').hide();
        $('#add-category-wrapper .add-image-to-category').show();
        $('#add-category-wrapper .del-image-to-category').hide();       
      });  

     // Выделить все категории.
      $('.admin-center').on('click', '.section-category .check-all-cat', function(){
        
          $('.category-tree input[type=checkbox]').prop('checked','checked');
          $('.category-tree input[type=checkbox]').val('true');      
        
          $('.check-all-cat').hide();  
          $('.uncheck-all-cat').show();    
      });
      
      
       // Сортировать все категории по алфавиту
      $('.admin-center').on('click', '.section-category .sort-all-cat', function(){
        category.sortToAlphabet();
      });
      
      // Снять выделение со всех  категорий.
      $('.admin-center').on('click', '.section-category .uncheck-all-cat', function(){        

          $('.category-tree input[name=category-check]').prop('checked', false);
          $('.category-tree input[name=category-check]').val('false');
          $('.check-all-cat').show();  
          $('.uncheck-all-cat').hide();    
     
      });
      
      // Выполнение выбранной операции с категориями
      $('.admin-center').on('click', '.section-category .run-operation', function(){
       
          category.runOperation($('.category-operation').val());
          
      });


      // Сохранение категории при нажатии на кнопку сохранить в модальном окне.
      $('body').on('click', '.section-category .prod-sub-cat', function() {
        includeJS(admin.SITE + '/mg-core/script/admin/catalog.js');
        admin.SECTION = 'catalog';
        admin.show("catalog.php", cookie("type"), "page=0&insideCat=true&cat_id=" + $(this).data('id'), catalog.callbackProduct);
        $('#category').removeClass('active-item');
        $('#catalog').addClass('active-item');
      });

      // Сохранение категории при на жатии на кнопку сохранить в модальном окне.
      $('body').on('click', '.section-category .link-to-site', function() {
        window.open($(this).data('href'));
      });

      // Разворачивание подпунктов по клику
      $('.admin-center').on('click', '.section-category .slider .slider_btn', function() {

        $(this).parent('.slider').children('.sub_menu').slideToggle(300);
        if (!$(this).hasClass('opened')) {
          category.addCategoryToOpenArr($(this).parent('.slider').find('a[rel=CategoryTree]').attr('id'));
          $(this).css({'background-position': '0 0'});
          $(this).addClass('opened');
          $(this).removeClass('closed');
        }
        else {
          category.delCategoryToOpenArr($(this).parent('.slider').find('a[rel=CategoryTree]').attr('id'));
          $(this).css({'background-position': '0 -18px'});
          $(this).addClass('closed');
          $(this).removeClass('opened');
        }
      });

      // Клик на иконку лампочки, делает невидимой категорию в меню      
      $('.admin-center').on('click', '.section-category .visible', function() {
        $(this).toggleClass('active');
        var id = $(this).data('category-id');

        if ($(this).hasClass('active')) {
          category.invisibleCat(id, 0);
          $(this).attr('title', lang.ACT_V_CAT);
        }
        else {
          category.invisibleCat(id, 1);
          $(this).attr('title', lang.ACT_UNV_CAT);
        }
        admin.initToolTip();

      });
      
      // Клик на иконку экспорта, включает или исключает категорию из выгрузки      
      $('.admin-center').on('click', '.section-category .export', function() {
        $(this).toggleClass('active');
        var id = $(this).data('category-id');

        if ($(this).hasClass('active')) {
          $(this).parent('li').find('ul div.export').addClass('active').attr('title', lang.ACT_EXPORT_CAT);
          category.exportCatStatus(id, 1);
          $(this).attr('title', lang.ACT_EXPORT_CAT);
        } else {
          $(this).parent('li').find('ul div.export').removeClass('active').attr('title', lang.ACT_NOT_EXPORT_CAT);
          category.exportCatStatus(id, 0);
          $(this).attr('title', lang.ACT_NOT_EXPORT_CAT);
        }
        admin.initToolTip();

      });

      // контекстное меню для работы с категориями.
      $('.admin-center').on('click', '.category-tree li a[class=CategoryTree]', function() {
        $(".cat-li .cat-title").text($(this).text());
        $(".cat-li .cat-id").text('id = ' + $(this).attr('id') + ' ');
        category.openContextMenu($(this).attr('id'), $(this).offset());
      });
      
      // Клик по кнопке для редактирования скидки
      $('.admin-center').on('click', '#add-category-wrapper  .discount-edit', function() {
        $('.discount-rate-control .discount-state').hide();
        $('.discount-rate-control .discount-rate-edit').show();
      });  
      
      //Клик по стикеру в меню для  отмены скидки/наценки 
      $('.admin-center').on('click', '.sticker-menu .discount-cansel', function() {
        var obj = $(this).parents('.sticker-menu');
        obj.hide();       
        admin.ajaxRequest({
          mguniqueurl: "action/clearCategoryRate",
          id: obj.data('cat-id')   
        },
        function(response) {
          admin.indication(response.status, response.msg)
        });
      });   
      
      //Клик по стикеру в меню для применения скидки/наценки к вложенным подкатегориям
      $('.admin-center').on('click', '.sticker-menu .discount-apply-follow', function() {
        var obj = $(this).parents('.sticker-menu');            
        admin.ajaxRequest({
          mguniqueurl: "action/applyRateToSubCategory",
          id: obj.data('cat-id')   
        },
        function(response) {      
          admin.refreshPanel();
        });
      });   
      
      
       //Клик по ссылке для установки скидки/наценки
      $('body').on('click', '#add-category-wrapper .discount-setup-rate', function() {
        $(this).hide();
        $('.discount-rate-control').show();
      }); 
      
       //Клик по отмене скидки/наценки 
      $('body').on('click', '#add-category-wrapper .cancel-rate', function() {
        $('.discount-setup-rate').show();
        $('.discount-rate-control').hide();
        $('.discount-rate-control input[name=rate]').val(0);        
      });     
      
       // Клик по кнопке для смены скидки/наценки
      $('body').on('click', '#add-category-wrapper .discount-change-rate', function() {
        $('.select-rate-block').show();
      }); 
      // Клик по кнопке для  отмены модалки смены скидки/наценки
      $('body').on('click', '#add-category-wrapper .cancel-rate-dir', function() {
        $('.select-rate-block').hide();  
        if($('.rate-dir').text()=="+"){
          $('.select-rate-block select[name=change_rate_dir] option[value=up]').prop('selected','selected');
        }
        if($('.rate-dir').text()=="-"){
          $('.select-rate-block select[name=change_rate_dir] option[value=down]').prop('selected','selected');
        }        
      });  
      
      // Клик по кнопке для применения скидки/наценки
      $('body').on('click', '#add-category-wrapper .apply-rate-dir', function() {
        $('.select-rate-block').hide();        
        if($('.select-rate-block select[name=change_rate_dir]').val()=='up'){
          category.setupDirRate(1);    
        }else{
          category.setupDirRate(-1);    
        }
      }); 
      

      // клик вне поиска
      $(document).mousedown(function(e) {
        var container = $(".edit-category-list");
        if (container.has(e.target).length === 0 && $(".edit-category-list").has(e.target).length === 0) {
          category.closeContextMenu();
        }
      });

    },
    /** 
     * меняет местами две категории oneId и twoId
     * oneId - идентификатор первой категории
     * twoId - идентификатор второй категории
     */
    changeSortCat: function(oneId, twoId) {
      admin.ajaxRequest({
        mguniqueurl: "action/changeSortCat",
        oneId: oneId,
        twoId: twoId
      },
      function(response) {
        admin.indication(response.status, response.msg)
      });
    },
    /** 
     * Делает категорию  видимой/невидимой в меню
     * oneId - идентификатор первой категории
     * twoId - идентификатор второй категории
     */
    invisibleCat: function(id, invisible) {
      admin.ajaxRequest({
        mguniqueurl: "action/invisibleCat",
        id: id,
        invisible: invisible
      },
      function(response) {
        admin.indication(response.status, response.msg)
      });
    },
    /** 
     * Устанавливает флаг выгрузки для категории
     * oneId - идентификатор первой категории
     * exportCat - значение флага выгружать/не выгружать(1/0)
     */
    exportCatStatus: function(id, exportCat) {
      admin.ajaxRequest({
        mguniqueurl: "action/exportCatStatus",
        id: id,
        export: exportCat
      },
      function(response) {
        admin.indication(response.status, response.msg)
      });
    },
    /**
     * открывает контекстное меню
     * id - идентификатор выбраной категории
     * offset - положение элемента на странице, для вычисления позиции контекстного меню
     */
    openContextMenu: function(id, offset) {

      $('.edit-category-list').css('position', 'absolute');
      $('.edit-category-list').css('display', 'block');
      $('.edit-category-list').css('z-index', '1');
      $('.edit-category-list').offset(offset);
      var top = $('.edit-category-list').css('top').slice(0, -2);
      var left = $('.edit-category-list').css('left').slice(0, -2);
      top = parseInt(top) - 2;
      left = parseInt(left) + 92;
      $('.edit-category-list').css({top: top + 'px', left: left + 'px', });

      $('.edit-sub-cat').attr('id', id);
      $('.add-sub-cat').attr('id', id);
      $('.delete-sub-cat').attr('id', id);
      $('.prod-sub-cat').data('id', id);

    },
    // закрывает контекстное меню для работы с категориями.
    closeContextMenu: function() {
      $('.edit-category-list').css('display', 'none');
    },
    // добавляет ID открытой категории в массив, записывает в куки для сохранения статуса дерева
    addCategoryToOpenArr: function(id) {

      var addId = true;
      category.openedCategoryAdmin.forEach(function(item) {
        if (item == id) {
          addId = false;
        }
      });

      if (addId) {
        category.openedCategoryAdmin.push(id);
      }

      cookie("openedCategoryAdmin", JSON.stringify(category.openedCategoryAdmin));
    },
    // удаляет ID закрытой категории из массива, записывает в куки для сохранения статуса дерева
    delCategoryToOpenArr: function(id) {

      var dell = false;
      var i = 0;
      var spliceIndex = 0;
      category.openedCategoryAdmin.forEach(function(item) {
        if (item == id) {
          dell = true;
          spliceIndex = i;
        }
        i++;
      });

      if (dell) {
        category.openedCategoryAdmin.splice(spliceIndex, 1);
      }

      cookie("openedCategoryAdmin", JSON.stringify(category.openedCategoryAdmin));
    },
    /**
     * Открывает модальное окно.
     * type - тип окна, либо для создания нового товара, либо для редактирования старого.
     * id - редактируемая категория, если это не создание новой
     */
    openModalWindow: function(type, id) {
     try{        
        if(CKEDITOR.instances['html_content']){
          CKEDITOR.instances['html_content'].destroy();
        }      
      } catch(e) { }   
      
      switch (type) {
        case 'edit':
          {
            category.clearFileds();
            $('#modalTitle').text(lang.EDIT_CAT);
            category.editCategory(id);
            break;
          }
        case 'add':
          {
            $('#modalTitle').text(lang.ADD_CATEGORY);
            category.clearFileds();
            break;
          }
        case 'addSubCategory':
          {
            $('#modalTitle').text(lang.ADD_SUBCATEGORY);
            category.clearFileds();
            $('select[name=parent] option[value="' + id + '"]').prop("selected", "selected");
            break;
          }
        default:
          {
            category.clearFileds();
            break;
          }
      }

      // закрытие контекстного меню
      category.closeContextMenu();

      // Вызов модального окна.
      admin.openModal($('.b-modal'));
      $('textarea[name=html_content]').ckeditor(function() {
         this.setData(category.supportCkeditor);   
      });

    },
    /**
     *  Проверка заполненности полей, для каждого поля прописывается свое правило.
     */
    checkRulesForm: function() {
      $('.errorField').css('display', 'none');
      $('input').removeClass('error-input');

      var error = false;
      // наименование не должно иметь специальных символов.
      if (!$('input[name=title]').val()) {
        $('input[name=title]').parent("label").find('.errorField').css('display', 'block');
        $('input[name=title]').addClass('error-input');
        error = true;
      }

      // артикул обязательно надо заполнить.
      if (!admin.regTest(1, $('input[name=url]').val()) || !$('input[name=url]').val()) {
        $('input[name=url]').parent("label").find('.errorField').css('display', 'block');
        $('input[name=url]').addClass('error-input');
        error = true;
      }


     if(isNaN(parseFloat($('.discount-rate-control input[name=rate]').val()))){
        $('.discount-error.errorField').css('display','block');
        $('.product-text-inputs input[name=price]').addClass('error-input');
        error = true;
      }

      if (error == true) {
        return false;
      }

      return true;
    },
    /**
     * Сохранение изменений в модальном окне категории.
     * Используется и для сохранения редактированных данных и для сохраниеня нового продукта.
     * id - идентификатор продукта, может отсутсвовать если производится добавление нового товара.
     */
    saveCategory: function(id) {

      // Если поля неверно заполнены, то не отправляем запрос на сервер.
      if (!category.checkRulesForm()) {
        return false;
      }

      if($('textarea[name=html_content]').val()==''){
        if(!confirm(lang.ACCEPT_EMPTY_DESC+'?')){
          return false;
        }
      }
      var rate = $('.discount-rate-control input[name=rate]').val();
      if(rate!=0){
        rate = rate/100;
      }
      if($('.rate-dir').text()!='+'){
        rate = -1*rate;
      }
      // Пакет характеристик категории.
      var packedProperty = {
        mguniqueurl: "action/saveCategory",
        id: id,
        title: $('input[name=title]').val(),
        url: $('input[name=url]').val(),
        parent: $('select[name=parent]').val(),
        html_content: $('textarea[name=html_content]').val(),
        meta_title: $('input[name=meta_title]').val(),
        meta_keywords: $('input[name=meta_keywords]').val(),
        meta_desc: $('textarea[name=meta_desc]').val(),
        image_url: $('#add-category-wrapper  input[name="image_url"]').val(),
        invisible: $('input[name=invisible]').val() == 'true' ? 1 : 0,
        rate: rate
      }

      // Отправка данных на сервер для сохранеиня.
      admin.ajaxRequest(packedProperty,
              function(response) {
                admin.indication(response.status, response.msg);
                // Закрываем окно.
                admin.closeModal($('.b-modal'));
                admin.refreshPanel();
              }
      );
    },
    /**
     * Получает данные о категории с сервера и заполняет ими поля в окне.
     */
    editCategory: function(id) {
      admin.ajaxRequest({
        mguniqueurl: "action/getCategoryData",
        id: id
      },
      category.fillFileds(),
              $('.add-product-form-wrapper .add-category-form')
              );
    },
    /**
     * Удаляет каттегорию из БД сайта
     */
    deleteCategory: function(id) {
      if (confirm(lang.SUB_CATEGORY_DELETE + '?')) {
        admin.ajaxRequest({
          mguniqueurl: "action/deleteCategory",
          id: id
        },
        function(response) {
          admin.indication(response.status, response.msg)
          admin.refreshPanel();
        }
        );
      }
    },
    /**
     * Заполняет поля модального окна данными.
     */
    fillFileds: function() {
      return (function(response) {
        $('input').removeClass('error-input');
        $('input[name=title]').val(response.data.title);
        $('input[name=url]').val(response.data.url);
        $('select[name=parent]').val(response.data.parent);
        $('select[name=parent]').val(response.data.parent);
        $('input[name=invisible]').prop('checked', false);
        $('input[name=invisible]').val('false');
        if (response.data.invisible == 1) {
          $('input[name=invisible]').prop('checked', true);
          $('input[name=invisible]').val('true');
        }
        $('input[name=meta_title]').val(response.data.meta_title);  
        category.supportCkeditor = response.data.html_content;  
        $('textarea[name=html_content]').val(response.data.html_content);
        $('input[name=meta_keywords]').val(response.data.meta_keywords);
        $('textarea[name=meta_desc]').val(response.data.meta_desc);
        $('.symbol-count').text($('textarea[name=meta_desc]').val().length);
                
        if(response.data.image_url){
          $('#add-category-wrapper .category-image').attr('src', admin.SITE+response.data.image_url);
          $('#add-category-wrapper .category-image').show();
          $('#add-category-wrapper .category-img-block').show();          
          $('#add-category-wrapper .del-image-to-category').show();  
          $('#add-category-wrapper .add-image-to-category').hide();
          $('#add-category-wrapper  input[name="image_url"]').val(admin.SITE+response.data.image_url);
        } else{
           $('#add-category-wrapper  input[name="image_url"]').val('');
           $('#add-category-wrapper .category-image').hide();
           $('#add-category-wrapper .category-img-block').hide();     
           $('#add-category-wrapper .del-image-to-category').hide();  
           $('#add-category-wrapper .add-image-to-category').show();
        }   
        
  
        $('.discount-rate-control input[name=rate]').val(response.data.rate*100);
        if(response.data.rate!=0){
          $('.discount-setup-rate').hide();
          $('.discount-rate-control').show();
          category.setupDirRate(response.data.rate);  
        }
        
        $('.save-button').attr('id', response.data.id);
      })
    },
    /**
     * Чистит все поля модального окна.
     */
    clearFileds: function() {

      $('input[name=title]').val('');
      $('input[name=url]').val('');
      $('select[name=parent]').val('0');
      $('input[name=invisible]').prop('checked', false);
      $('input[name=invisible]').val('false');
      $('textarea[name=html_content]').val('');
      $('input[name=meta_title]').val('');
      $('input[name=meta_keywords]').val('');
      $('textarea[name=meta_desc]').val('');
      $('.symbol-count').text('0');
      $('#add-category-wrapper  input[name="image_url"]').val('');
      $('#add-category-wrapper .category-image').hide();
      $('#add-category-wrapper .category-img-block').hide();     
      $('#add-category-wrapper .del-image-to-category').hide();  
      $('#add-category-wrapper .add-image-to-category').show();
      $('.save-button').attr('id', '');
      // Стираем все ошибки предыдущего окна если они были.
      $('.errorField').css('display', 'none');
      $('.error-input').removeClass('error-input');
      $('.discount-setup-rate').show();
      $('.discount-rate-control input[name=rate]').val(0);
      $('.discount-rate-control').hide();
      $('#add-category-wrapper .discount-rate').removeClass('color-down').addClass('color-up');
      category.setupDirRate(0);
      
      
      category.supportCkeditor = "";
    },
            
     setupDirRate: function(rate) {         
      if(rate>=0){
        $('#add-category-wrapper select[name=change_rate_dir] option[value=up]').prop('selected','selected');
        $('#add-category-wrapper .discount-rate').removeClass('color-down').addClass('color-up');
        $('.rate-dir').text('+');
        $('.rate-dir-name').text(lang.DISCOUNT_UP);
        $('.discount-rate-control input[name=rate]').val(Math.abs($('.discount-rate-control input[name=rate]').val()));
      }else{
        $('#add-category-wrapper select[name=change_rate_dir] option[value=down]').prop('selected','selected');  
        $('.rate-dir-name').text(lang.DISCOUNT_DOWN);
        $('#add-category-wrapper .discount-rate').removeClass('color-up').addClass('color-down');
        $('.rate-dir').text('-');
        $('.discount-rate-control input[name=rate]').val(Math.abs($('.discount-rate-control input[name=rate]').val()));
      }
     },        
    /**
     * устанавливает для каждой категории в списке возможность перемещения
     */
    draggableCat: function() {

      var listIdStart = [];
      var listIdEnd = [];

      $('.category-tree li').each(function() {

        $(this).addClass('ui-draggable');

        $(this).draggable({
          scroll: true,
          cursor: "move",
          handle: "div[class=mover]",
          snapMode: 'outer',
          snapTolerance: 0,
          start: function(event, ui) {
            $(this).css('width', '50%');
            $(this).parent('UL').addClass('editingCat');
            $(this).css('opacity', '0.5');
            $(this).css('height', '1px');
            var li = $(this).parent('UL').find('li');

            // составляем список ID категорий в текущем UL.
            listIdStart = [];
            var $thisId = $(this).find('a').attr('id');
            li.each(function(i) {
              if ($(this).parent('ul').hasClass('editingCat')) {
                var id = $(this).find('a').attr('id');
                if ($thisId == id) {
                  listIdStart.push('start');
                } else {
                  listIdStart.push($(this).find('a').attr('id'));
                }
              }
            });

            $(this).before('<li class="pos-element" style="display:none;"></li>'); // чтобы можно было вернуть на тоже место           
            $(this).parent('UL').append('<li class="end-pos-element"></li>'); // чтобы можно было вставить в конец списка  

          },
          stop: function(event, ui) {

            // найдем выделенный объект поместим перед ним тот который перетаскивался
            $(this).attr('style', 'style=""');
            $('.afterCat').before($(this));


            var li = $(this).parent('UL').find('li');

            // составляем список ID категорий в текущем UL.
            listIdEnd = [];
            var $thisId = $(this).find('a').attr('id');
            li.each(function(i) {
              if ($(this).parent('ul').hasClass('editingCat')) {
                var id = $(this).find('a').attr('id');
                if (id) {
                  if ($thisId == id) {
                    listIdEnd.push('end');
                  } else {
                    listIdEnd.push($(this).find('a').attr('id'));
                  }
                }
              }
            });


            $(this).parent('UL').removeClass('editingCat');
            $(this).parent('UL').find('li').removeClass('afterCat');
            $('.pos-element').remove();
            $('.end-pos-element').remove();


            var sequence = category.getSequenceSort(listIdStart, listIdEnd, $(this).find('a').attr('id'));
            if (sequence.length > 0) {
              sequence = sequence.join();
              admin.ajaxRequest({
                mguniqueurl: "action/changeSortCat",
                switchId: $thisId,
                sequence: sequence
              },
              function(response) {
                admin.indication(response.status, response.msg)
              }
              );
            }

          },
          drag: function(event, ui) {
            var dragElementTop = $(this).offset().top;
            var li = $(this).parent('UL').find('li');
            li.removeClass('afterCat');

            // проверяем, существуют ли LI ниже  перетаскиваемого.
            li.each(function(i) {
              $('.end-pos-element').removeClass('afterCat');
              if ($(this).offset().top > dragElementTop
                      && !$(this).hasClass('pos-element')
                      && $(this).parent('ul').hasClass('editingCat')
                      ) {
                $(this).addClass('afterCat');
                return false;
              } else {
                $('.end-pos-element').addClass('afterCat');
              }
            });
          }

        });
      });
    },
    /**
     * Вычисляет последовательность замены порядковых индексов 
     * Получает  дла массива
     * ["1", "start", "9", "2", "10"]
     * ["1", "9", "2", "end", "10"]
     * и ID перемещенной категории
     */
    getSequenceSort: function(arr1, arr2, id) {
      var startPos = '';
      var endPos = '';

      // вычисляем стартовую позицию элемента
      arr1.forEach(function(element, index, array) {
        if (element == "start") {
          startPos = index;
          arr1[index] = id;
          return false;
        }
      });

      // вычисляем конечную позицию элемента      
      arr2.forEach(function(element, index, array) {
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
        arr1.forEach(function(element, index, array) {
          if (index > startPos && index <= endPos) {
            result.push(element);
          }
        });
      }

      // направление переноса, снизу вверх
      if (endPos < startPos) {
        arr2.forEach(function(element, index, array) {
          if (index > endPos && index <= startPos) {
            result.unshift(element);
          }
        });
      }

      return result;
    },
  
    /**
    * функция для приема файла из аплоадера
    */         
    getFile: function(file) {      
      $('#add-category-wrapper  input[name="image_url"]').val(file.url);
      $('#add-category-wrapper .category-image').attr('src',file.url);
      $('#add-category-wrapper .category-img-block').show();
	  $('#add-category-wrapper .category-image').show();
      $('#add-category-wrapper .add-image-to-category').hide();
      $('#add-category-wrapper .del-image-to-category').show();  
    }, 
    
    /**
     * Выполняет выбранную операцию со всеми отмеченными категориями
     * operation - тип операции.
     */
    runOperation: function(operation) { 
        
      var category_id = [];
       $('.category-tree input[name=category-check]').each(function(){              
        if($(this).prop('checked')){  
          category_id.push($(this).parent('li').find('.CategoryTree').attr('id'));
        }
      });  
      
      if (confirm(lang.RUN_CONFIRM)) {        
        admin.ajaxRequest({
          mguniqueurl: "action/operationCategory",
          operation: operation,
          category_id: category_id,
        },
        function(response) { 
          admin.refreshPanel();  
        }
        );
      }
       

    }, 
    
    /**
     * 
     * Упорядочивает всё дерево категорий по алфавиту 
     */
    sortToAlphabet: function() { 
          
      if (confirm('Упорядочить всё дерево категорий по алфавиту?')) {        
        admin.ajaxRequest({
          mguniqueurl: "action/sortToAlphabet",      
        },
        function(response) {         
          admin.refreshPanel();  
        }
        );
      }
       

    }, 
  }
})();

// инициализациямодуля при подключении
category.init();