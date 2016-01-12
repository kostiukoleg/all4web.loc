/**
 * Модуль для  раздела "Страницы".
 */
var page = (function () {
  return {
    wysiwyg: null, // HTML редактор для  редактирования страниц
    supportCkeditor: null,
    openedPageAdmin: [], //массив открытых страниц
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init: function () {
      // восстанавливаем массив открытых страниц из куков
      page.openedPageAdmin = eval(cookie("openedPageAdmin"));
      if (!page.openedPageAdmin) {
        page.openedPageAdmin = [];
      }

      // Вызов модального окна при нажатии на кнопку добавления.      
      $('.admin-center').on('click', '.section-page .add-new-button', function () {
        page.openModalWindow('add');
      });

      // Обработка нажатия на кнопку  сделать видимыми все.      
      $('.admin-center').on('click', '.section-page .refresh-visible-cat', function () {
        page.refreshVisible();
      });

      // Вызов модального окна при нажатии на пункт изменения
      $('.admin-center').on('click', '.section-page .edit-sub-cat', function () {
        page.openModalWindow('edit', $(this).attr('id'));
      });

      // Вызов модального окна при нажатии на пункт добавления
      $('.admin-center').on('click', '.section-page .add-sub-cat', function () {
        page.openModalWindow('addSubCategory', $(this).attr('id'));
      });

      // Удаление страницы.
      $('.admin-center').on('click', '.section-page .delete-sub-cat', function () {
        page.deletePage($(this).attr('id'));
      });

      // Закрыть контекстное меню.
      $('.admin-center').on('click', '.section-page .cancel-sub-cat', function () {
        page.closeContextMenu();
      });

      // Сохранение в модальном окне.
      $('body').on('click', '#add-page-wrapper .save-button', function () {
        page.savePage($(this).attr('id'));
      });

      // Сохранение продукта при нажатии на кнопку сохранить в модальном окне.
      $('body').on('click', '.section-page .link-to-site', function () {
        var url = $(this).data('href');
        if (url == (mgBaseDir + '/index') || url == (mgBaseDir + '/index.html')) {
          url = mgBaseDir;
        }
        window.open(url);
      });

      // Сохранение продукта при на жатии на кнопку сохранить в модальном окне.
      $('body').on('click', ' .previewPage', function () {
        $('#previewContent').val($('textarea[name=html_content]').val());
        $('#previewer').submit();
      });




      // Разворачивание подпунктов по клику
      $('.admin-center').on('click', '.section-page .slider .slider_btn', function () {
        $(this).parent('.slider').children('.sub_menu').slideToggle(300);
        if (!$(this).hasClass('opened')) {
          page.addCategoryToOpenArr($(this).parent('.slider').find('a[rel=pageTree]').attr('id'));
          $(this).css({'background-position': '0 0'});
          $(this).addClass('opened');
          $(this).removeClass('closed');
        }
        else {
          page.delCategoryToOpenArr($(this).parent('.slider').find('a[rel=pageTree]').attr('id'));
          $(this).css({'background-position': '0 -18px'});
          $(this).addClass('closed');
          $(this).removeClass('opened');
        }
      });

      // клик на иконку лампочки, делает невидимой страницу в меню      
      $('.admin-center').on('click', '.section-page .visible', function () {
        $(this).toggleClass('active');
        var id = $(this).data('category-id');

        if ($(this).hasClass('active')) {
          page.invisiblePage(id, 0);
          $(this).attr('title', lang.ACT_V_CAT);
        }
        else {
          page.invisiblePage(id, 1);
          $(this).attr('title', lang.ACT_UNV_CAT);
        }
        admin.initToolTip();

      });

      // контекстное меню для работы со страницами
      $('.admin-center').on('click', '.page-tree li a', function () {
        $(".page-li .cat-title").text($(this).text());
        $(".page-li .cat-id").text('id = ' + $(this).attr('id') + ' ');
        page.openContextMenu($(this).attr('id'), $(this).offset());
      });

      // клик вне контестной менюхи
      $(document).mousedown(function (e) {
        var container = $(".edit-category-list");
        if (container.has(e.target).length === 0 && $(".edit-category-list").has(e.target).length === 0) {
          page.closeContextMenu();
        }
      });
      // Выполнение выбранной операции с отмеченными страницами
      $('.admin-center').on('click', '.section-page .run-operation', function () {
        page.runOperation($('.page-operation').val());
      });
      
      // Выделить все страницы
      $('.admin-center').on('click', '.section-page .check-all-page', function () {
        $('.page-tree input[type=checkbox]').prop('checked', 'checked');
        $('.page-tree input[type=checkbox]').val('true');
        $('.check-all-page').hide();
        $('.uncheck-all-page').show();
      });
      // Снять выделение со всех  страниц.
      $('.admin-center').on('click', '.section-page .uncheck-all-page', function () {

        $('.page-tree input[name=page-check]').prop('checked', false);
        $('.page-tree input[name=page-check]').val('false');
        $('.check-all-page').show();
        $('.uncheck-all-page').hide();
      });


    },
    /** 
     * Делает страницу видимой/невидимой в меню
     * oneId - идентификатор первой 
     * twoId - идентификатор второй 
     */
    invisiblePage: function (id, invisible) {
      admin.ajaxRequest({
        mguniqueurl: "action/invisiblePage",
        id: id,
        invisible: invisible
      },
      function (response) {
        admin.indication(response.status, response.msg)
      });
    },
    /**
     * открывает контекстное меню
     * id - идентификатор выбраной категории
     * offset - положение элемента на странице, для вычисления позиции контекстного меню
     */
    openContextMenu: function (id, offset) {

      $('.edit-category-list').css('position', 'absolute');
      $('.edit-category-list').css('display', 'block');
      $('.edit-category-list').css('z-index', '1');
      $('.edit-category-list').offset(offset);

      var top = $('.edit-category-list').css('top').slice(0, -2);
      var left = $('.edit-category-list').css('left').slice(0, -2);
      top = parseInt(top) - 2;
      left = parseInt(left) + 76;
      $('.edit-category-list').css({top: top + 'px', left: left + 'px', });
      $('.edit-sub-cat').attr('id', id);
      $('.add-sub-cat').attr('id', id);
      $('.delete-sub-cat').attr('id', id);
      $('.prod-sub-cat').data('id', id);

    },
    // закрывает контекстное меню для работы с категориями.
    closeContextMenu: function () {
      $('.edit-category-list').css('display', 'none');
    },
    // добавляет ID открытой категории в массив, записывает в куки для сохранения статуса дерева
    addCategoryToOpenArr: function (id) {

      var addId = true;
      page.openedPageAdmin.forEach(function (item) {
        if (item == id) {
          addId = false;
        }
      });

      if (addId) {
        page.openedPageAdmin.push(id);
      }

      cookie("openedPageAdmin", JSON.stringify(page.openedPageAdmin));
    },
    // удаляет ID закрытой категории из массива, записывает в куки для сохранения статуса дерева
    delCategoryToOpenArr: function (id) {

      var dell = false;
      var i = 0;
      var spliceIndex = 0;
      page.openedPageAdmin.forEach(function (item) {
        if (item == id) {
          dell = true;
          spliceIndex = i;
        }
        i++;
      });

      if (dell) {
        page.openedPageAdmin.splice(spliceIndex, 1);
      }

      cookie("openedPageAdmin", JSON.stringify(page.openedPageAdmin));
    },
    /**
     * Открывает модальное окно.
     * type - тип окна, либо для создания нового товара, либо для редактирования старого.
     * id - редактируемая категория, если это не создание новой
     */
    openModalWindow: function (type, id) {
      try {
        if (CKEDITOR.instances['html_content']) {
          CKEDITOR.instances['html_content'].destroy();
        }
      } catch (e) {
      }

      switch (type) {
        case 'edit':
        {
          page.clearFileds();
          $('#modalTitle').text(lang.PAGE_EDIT);
          page.editCategory(id);
          break;
        }
        case 'add':
        {
          $('#modalTitle').text(lang.PAGE_MODAL_TITLE);
          page.clearFileds();
          break;
        }
        case 'addSubCategory':
        {
          $('#modalTitle').text(lang.ADD_SUBPAGE);
          page.clearFileds();
          $('select[name=parent] option[value="' + id + '"]').prop("selected", "selected");
          break;
        }
        default:
        {
          page.clearFileds();
          break;
        }
      }

      // закрытие контекстного меню
      page.closeContextMenu();

      // Вызов модального окна.
      admin.openModal($('.b-modal'));

      $('textarea[name=html_content]').ckeditor(function () {
        this.setData(page.supportCkeditor);
      });

    },
    /**
     *  Проверка заполненности полей, для каждого поля прописывается свое правило.
     */
    checkRulesForm: function () {
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
      if (!$('input[name=url]').val()) {
        $('input[name=url]').parent("label").find('.errorField').css('display', 'block');
        $('input[name=url]').addClass('error-input');
        error = true;
      }

      if (error == true) {
        return false;
      }

      return true;
    },
    /**
     * Сохранение изменений в модальном окне страницы.
     * Используется и для сохранения редактированных данных и для сохраниеня новой страницы.
     * id - идентификатор страницы, может отсутсвовать если производится добавление  новой страницы.
     */
    savePage: function (id) {

      // Если поля неверно заполнены, то не отправляем запрос на сервер.
      if (!page.checkRulesForm()) {
        return false;
      }

      if ($('textarea[name=html_content]').val() == '') {
        if (!confirm(lang.ACCEPT_EMPTY_DESC + '?')) {
          return false;
        }
      }
      // Пакет характеристик категории.
      var packedProperty = {
        mguniqueurl: "action/savePage",
        id: id,
        title: $('input[name=title]').val(),
        url: $('input[name=url]').val(),
        parent: $('select[name=parent]').val(),
        html_content: $('textarea[name=html_content]').val(),
        meta_title: $('input[name=meta_title]').val(),
        meta_keywords: $('input[name=meta_keywords]').val(),
        meta_desc: $('textarea[name=meta_desc]').val(),
        invisible: $('input[name=invisible]').val() == 'true' ? 1 : 0
      }

      // Отправка данных на сервер для сохранеиня.
      admin.ajaxRequest(packedProperty,
        function (response) {
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
    editCategory: function (id) {
      admin.ajaxRequest({
        mguniqueurl: "action/getPageData",
        id: id
      },
      page.fillFileds(),
        $('.add-product-form-wrapper .add-category-form')
        );
    },
    /**
     * Удаляет страницу из БД сайта
     */
    deletePage: function (id) {
      if (confirm(lang.SUB_CATEGORY_DELETE + '?')) {
        admin.ajaxRequest({
          mguniqueurl: "action/deletePage",
          id: id
        },
        function (response) {
          admin.indication(response.status, response.msg)
          admin.refreshPanel();
        }
        );
      }

    },
    /**
     * Заполняет поля модального окна данными.
     */
    fillFileds: function () {
      return (function (response) {
        page.supportCkeditor = response.data.html_content;
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
        $('textarea[name=html_content]').val(response.data.html_content);
        $('input[name=meta_keywords]').val(response.data.meta_keywords);
        $('textarea[name=meta_desc]').val(response.data.meta_desc);
        $('.symbol-count').text($('textarea[name=meta_desc]').val().length);
        $('.save-button').attr('id', response.data.id);
      })
    },
    /**
     * Чистит все поля модального окна.
     */
    clearFileds: function () {

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
      $('.save-button').attr('id', '');

      // Стираем все ошибки предыдущего окна если они были.
      $('.errorField').css('display', 'none');
      $('.error-input').removeClass('error-input');
      page.supportCkeditor = "";
    },
    /**
     * устанавливает для каждой категории в списке возможность перемещения
     */
    draggableCat: function () {

      var listIdStart = [];
      var listIdEnd = [];

      $('.page-tree li').each(function () {

        $(this).addClass('ui-draggable');

        $(this).draggable({
          scroll: true,
          // axis: "y",
          cursor: "move",
          handle: "div[class=mover]",
          snapMode: 'outer',
          //containment: '.page-tree-field',
          snapTolerance: 0,
          start: function (event, ui) {

            $(this).css('width', '50%');
            $(this).parent('UL').addClass('editingCat');
            $(this).css('opacity', '0.5');
            $(this).css('height', '1px');
            var li = $(this).parent('UL').find('li');



            // составляем список ID категорий в текущем UL.
            listIdStart = [];
            var $thisId = $(this).find('a').attr('id');
            li.each(function (i) {
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
          stop: function (event, ui) {
            // var li = $(this).parent('UL').find('li');  
            // найдем выделенный объект поместим перед ним тот который перетаскивался
            $(this).attr('style', 'style=""');
            // $(this).css('top', 'inherit');     
            $('.afterCat').before($(this));


            var li = $(this).parent('UL').find('li');

            // составляем список ID категорий в текущем UL.
            listIdEnd = [];
            var $thisId = $(this).find('a').attr('id');
            li.each(function (i) {
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


            var sequence = page.getSequenceSort(listIdStart, listIdEnd, $(this).find('a').attr('id'));
            if (sequence.length > 0) {
              sequence = sequence.join();
              admin.ajaxRequest({
                mguniqueurl: "action/changeSortPage",
                switchId: $thisId,
                sequence: sequence
              },
              function (response) {
                admin.indication(response.status, response.msg)
                // admin.refreshPanel();
              }
              );
            }

          },
          drag: function (event, ui) {
            var dragElementTop = $(this).offset().top;
            var li = $(this).parent('UL').find('li');
            li.removeClass('afterCat');

            // проверяем, существуют ли LI ниже  перетаскиваемого.
            li.each(function (i) {
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


            //console.log(dragElementTop);              
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
    getSequenceSort: function (arr1, arr2, id) {
      var startPos = '';
      var endPos = '';

      // вычисляем стартовую позицию элемента
      arr1.forEach(function (element, index, array) {
        if (element == "start") {
          startPos = index;
          arr1[index] = id;
          return false;
        }
      });

      // вычисляем конечную позицию элемента      
      arr2.forEach(function (element, index, array) {
        if (element == "end") {
          endPos = index;
          arr2[index] = id;
          return false;
        }
      });

      // вычисляем индексы  с которыми надо поменяться местами     
      var result = [];

      // направление переноса, сверху вниз
      if (endPos > startPos) {
        arr1.forEach(function (element, index, array) {
          if (index > startPos && index <= endPos) {
            result.push(element);
          }
        });
      }

      // направление переноса, снизу вверх
      if (endPos < startPos) {
        arr2.forEach(function (element, index, array) {
          if (index > endPos && index <= startPos) {
            result.unshift(element);
          }
        });
      }

      return result;
    },
    /**
     * Обновляет статус видимости всех страниц  в меню
     */
    refreshVisible: function () {
      admin.ajaxRequest({
        mguniqueurl: "action/refreshVisiblePage"
      },
      function (response) {
        admin.indication(response.status, response.msg);
        admin.refreshPanel();
      });
    },
    /**
     * Выполняет выбранную операцию со всеми отмеченными страницами
     * operation - тип операции.
     */
    runOperation: function (operation) {

      var page_id = [];
      $('.page-tree input[name=page-check]').each(function () {
        if ($(this).prop('checked')) {
          page_id.push($(this).parent('li').find('.pageTree').attr('id'));
        }
      });
      if (confirm(lang.RUN_CONFIRM)) {
        admin.ajaxRequest({
          mguniqueurl: "action/operationPage",
          operation: operation,
          page_id: page_id,
        },
          function (response) {
            admin.refreshPanel();
          }
        );
      }


    },
  }
})();

// инициализациямодуля при подключении
page.init();