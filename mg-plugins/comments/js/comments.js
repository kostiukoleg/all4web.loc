var comments = (function(){
	return {
		init: function(){
			// Отправка комментария на сервер для обработки
			$('.comments .sendComment').on('click', function(e){
				comments.sendComment();   
         return false;
			})

			// Открытие окна изменение комментария
			$('.admin-center').on('click', '.section-comments .edit-row', function(e){				
				comments.openModalWindow($(this).attr('id'));
        return false;
			})

			// Обработка нажатия кнопки удаления комментария
			$('.admin-center').on('click', '.section-comments .delete-order', function(e){
				e.preventDefault();
				var res = confirm("Удалить комментарий?");
				if(res){
					comments.deleteComment($(this).attr('id'));
				}
			})

			// Обработка нажатия кнопки сохранения комментария
			$('.admin-center').on('click', '.section-comments .save-button', function(e){
		//		$('#overlay').remove();
				comments.editComment();
			})

			// Очищаем поля при закрытии модального окна
			$('.admin-center').on('click', 'section-comments .b-modal_close', function(){
				comments.clearFields();
			})

			// Устанавливает количиство выводимых записей в этом разделе.
      $('.admin-center').on('change', '.section-comments .countPrintRowsPage', function(){

        var count = $(this).val();
        admin.ajaxRequest({
          pluginHandler: 'comments', // имя папки в которой лежит данный плагин
          actionerClass: "Comments", // класс Comments в comments.php - в папке плагина
          action: "setCountPrintRowsComments", // название действия в пользовательском  классе Comments
          count: count
        },
        function(response) {
          admin.refreshPanel();
        }
        );

      });

		},

		// Открытие модального окна и заполнение полей из БД
		openModalWindow: function(id){
      comments.clearFields();
			admin.openModal($('.b-modal'));      
			comments.fillFields(id);
		},

		// Функция заполнения полей из БД
		fillFields: function(id){
			admin.ajaxRequest({
					pluginHandler: 'comments', // имя папки в которой лежит данный плагин
          actionerClass: "Comments", // класс Comments в comments.php - в папке плагина
          action: "getCommentById", // название действия в пользовательском  классе Comments
          id: id
        },
        function(response) {
        	$('.edit-comment-form input[name=name]').val(response.data.name);
        	$('.edit-comment-form input[name=email]').val(response.data.email);
        	$('.edit-comment-form select option[value=' + response.data.approved + ']').prop('selected', 'selected');
        	$('.edit-comment-form textarea').val(response.data.comment);
          
          var commentUrl=$('.edit-comment-form .commentUrl');
          var link = commentUrl.data('site')+response.data.uri;
          commentUrl.attr('href', link);
          commentUrl.text(link);
         
        	$('.edit-comment-form button.save-button').attr('id', id);
        },
        
        $('.add-product-form-wrapper')
        );
		},

		// Функция отправляет запрос для изменения комментария
		editComment: function(){
      
      var id = $('.edit-comment-form button.save-button').attr('id');
      var name = $('.edit-comment-form input[name=name]').val();
      var email = $('.edit-comment-form input[name=email]').val();
      var comment = $('.edit-comment-form textarea').val();
      var approved = $('.edit-comment-form select').val();

			var data = {
				pluginHandler: 'comments', // имя папки в которой лежит данный плагин
        actionerClass: "Comments", // класс Comments в comments.php - в папке плагина
        action: "saveComment", // название действия в пользовательском  классе Comments
				id: id,
				name: name,
				email: email,
				comment: comment,
				approved: approved
			}

			admin.ajaxRequest(
				data,
			function(response){  
        
        comments.indicatorCount(response.data.count);
        $('.comments-tbody tr[id='+id+']').replaceWith(        
            '<tr id="'+id+'">\
	          	<td class="c-name">'+name+'</td>\
	          	<td class="c-email">'+email+'</td>\
	          	<td class="c-approved"><span class="'+((approved==1)?'approved-comment':'n-approved-comment')+'">'+((approved==1)?'Одобрен':'Не одобрен')+'</span></td>\
	          	<td class="actions">\
	          		<ul class="action-list">\
	          			<li class="edit-row" id="'+id+'"><a class="tool-tip-bottom" href="#" title=""></a></li>\
                  <li class="delete-order" id="'+id+'"><a class="tool-tip-bottom" href="#" title=""></a></li>\
	          		</ul>\
	          	</td>\
          	</tr>');
    
        admin.indication(response.status, response.msg);
        admin.closeModal($('.b-modal'));
				//comments.clearFields();
			});
		},

    // меняеn индикатор количества новых комментариев
    indicatorCount: function(count) {       
        if(count==0){
          $('.button-list a[rel=comments]').parents('li').find('.comment-wrap').hide();
        } else {
          $('.button-list a[rel=comments]').parents('li').find('.comment-wrap').show();
           $('.button-list a[rel=comments]').parents('li').find('.comment-wrap').text(count);
        }
    },
            
		// Функция отправляет запрос на удаления комментария
		deleteComment: function(id){
			admin.ajaxRequest({
				pluginHandler: 'comments', // имя папки в которой лежит данный плагин
        actionerClass: "Comments", // класс Comments в comments.php - в папке плагина
        action: "deleteComment", // название действия в пользовательском  классе Comments
				id: id
			},
			function(response){
        admin.indication(response.status, response.msg);
        comments.indicatorCount(response.data.count);
			//	$('li.edit-row#'+id).parent().parent().parent().remove();
        $('.comments-tbody tr[id='+id+']').remove();          
    
			})
		},

		// Функция очищает поля формы правки комментария
		clearFields: function(){
			$('.edit-comment-form .input[type=text]').val('');
			$('.edit-comment-form textarea').val('');
			$('.edit-comment-form select option[value=0]').removeAttr('selected');
			$('.edit-comment-form select option[value=1]').removeAttr('selected');
      $('.edit-comment-form .commentUrl').text('');
		},

		// Функция отправляет комментарий из формы отправки на странице сайта
		sendComment: function(){
      $('.comments .comments-msg').html('Подождите, идет отправка комментария...');
      $.ajax({
			type: "POST",
			url: mgBaseDir+"/ajaxrequest",
			data: {
			    pluginHandler: 'comments', // имя папки в которой лежит данный плагин
          actionerClass: 'Comments', // класс Comments в Comments.php - в папке плагина
          action: "addComment", // название действия в пользовательском  классе News         
          name: $('.comments input[name=name]').val(),
				  email: $('.comments input[name=email]').val(),
			  	comment: $('.comments textarea').val()
			},
			dataType: "json",
			cache: false,
			success: function(response){     
        if(response.status!="error"){
            $('.comments input').val('');
            $('.comments textarea').val('');
          }
        $('.comments .comments-msg').html(response.msg);
      }});
    
		}		
	
	}
})();


$(document).ready(function(){
	comments.init();
});