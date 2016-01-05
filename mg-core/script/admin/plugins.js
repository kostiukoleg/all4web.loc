/**
 * Модуль для  раздела "Плагины".
 */
var plugin = (function () {
  return {

    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init:function() {
      // обрабатывает клик по кнопке настроек в таблице плагинов
      $('body').on('click', '.plugins-tables .plugSettings', function () {
        var pluginName = $(this).parents('tr').attr('id');
        var pluginTitle = $('tr[id='+pluginName+'] .p-name').text();
        plugin.openPagePlugin(pluginName, pluginTitle);
        $('#tiptip_holder').hide();
      });

      //Открывает и закрывает выпадающее меню плагинов
        $('.admin-top-menu-list li').hover(
            function () {
            // если в списке есть активные плагины выводим его
                if($('.plugins-dropdown-menu li a').length>1){
                    $(".plugins-menu-wrapper", this).show();
                }
            },
            function(){
                $(".plugins-menu-wrapper", this).hide();
            }
        );

      // Клик по активным плагинам из выпадающего меню, открывает страницу настроек
      $('body').on('click','.plugins-dropdown-menu li a', function () {
        var pluginName = $(this).attr('class');
        if(pluginName != 'all-plugins-settings'){
          var pluginTitle = $(this).text();
          plugin.openPagePlugin(pluginName, pluginTitle, true);
        }else{
          $('a[id=plugins]').click();
        }
        
        $('.admin-top-menu-list > li > a').removeClass('active-item');
        $('a[id=plugins]').addClass('active-item');
        $(".plugins-menu-wrapper").hide();

      });

      // Обработчик для загрузки нового плагина
      $('body').on('change', '#addPlugin', function(){
        plugin.addNewPlugin();
      });

      // Удаление плагина
      $('.admin-center').on('click', '.plugins-tables .delete-order', function(){
        admin.indication('error', "Обновление плагинов в демонстрационной версии недоступно");
        return false;
      });
      
      //Обновление плагина
      $('.admin-center').on('click', '#checkPluginsUpdate', function(){
        plugin.checkPluginsUpdate();
      });
      
      //Обновление плагина
      $('.admin-center').on('click', '.plugins-tables .update-plugin', function(){
        admin.indication('error', "Обновление плагинов в демонстрационной версии недоступно");
      });

    },

    // создает переключатели активности в строке с плагином
    createSwitch: function() {
      $('.plugins-active').each(function(){
        var pluginName = $(this).parent('tr').attr('id');
        var active = $(this).attr('active')=='1' ? true : false;
        $(this).html('<div class="switch-button" id="switchFor'+pluginName+'"></div>');

        $('#switchFor'+pluginName).toggles({
          on:active
        }
        );

        $('#switchFor'+pluginName+' .on').click(function () {
          plugin.deactivatePlugin(pluginName);
        });

        $('#switchFor'+pluginName+' .off').click(function () {
          plugin.activatePlugin(pluginName);
        });

      });
	  $('#addPlugin').hover(function(){
			$('.install-plugin').addClass('hover-btn');
		},
			function(){
			$('.install-plugin').removeClass('hover-btn');
			}
		);
    },

    // открывает страницу настроек плагина, если она существует
    openPagePlugin: function(pluginName, pluginTitle, havePage) {
      if(havePage || $('tr[id='+pluginName+']').attr('class')=='plugin-settings-on'){
        admin.show(pluginName, "plugin",'&pluginTitle='+pluginTitle,function(){
          admin.CURENT_PLUG_TITLE = pluginTitle;
         // $('.widget-table-title h4').text('Настройки плагина "'+pluginTitle+'"');
        });
      } else {
        alert(lang.PLUGIN_NOT_HAVE_SETTING);
      }
    },

    // активирует плагин
    activatePlugin: function(pluginName) {
      var pluginTitle = $('tr[id='+pluginName+'] .p-name').text();
      admin.ajaxRequest({
        mguniqueurl:"action/activatePlugin",
        pluginFolder: pluginName,
        pluginTitle: pluginTitle
      },
      (function(response) {
        admin.indication(response.status, response.msg);
        if(response.data.havePage){
          $('.go-to-plugins-settings').before('<li><a href="#" class="'+pluginName+'">'+pluginTitle+'</li>');
          $('tr[id='+pluginName+']').removeClass('plugin-settings-off');
          $('tr[id='+pluginName+']').addClass('plugin-settings-on');
          $('tr[id='+pluginName+'] .action-list .delete-order').before('<li class="plugin-settings-large"><a class="plugSettings" href="#" title="Настроить"></a></li>');
          $(".plugins-menu-wrapper").show();
          $('.plugins-icon').parents('li').find('.white-arrow-down').show();
      
          // обновляем панель  информеров
        }
        $('.info-panel .button-list').html('');
        if(response.data.newInformer){
          $('.info-panel .button-list').html(response.data.newInformer);
        }   
      })
      );
    },

    // деактивирует плагин
    deactivatePlugin: function(pluginName) {
       var pluginTitle = $('tr[id='+pluginName+'] .p-name').text();
      admin.ajaxRequest({
        mguniqueurl:"action/deactivatePlugin",
        pluginFolder: pluginName,
        pluginTitle: pluginTitle
      },
      (function(response) {
        admin.indication(response.status, response.msg)
        $('.plugins-dropdown-menu .'+pluginName).parent('li').remove();
        admin.hideWhiteArrowDown();
        $('tr[id='+pluginName+']').removeClass('plugin-settings-on');
        $('tr[id='+pluginName+']').addClass('plugin-settings-off');
        $('tr[id='+pluginName+'] .action-list .plugin-settings-large').remove();        
        $('.info-panel .button-list a[rel='+pluginName+']').parents('li').remove();           
      })
      );
    },

    addNewPlugin:function() {
     $('.img-loader').show();

      // установка плагина
      $("#newPluginForm").ajaxForm({
        type:"POST",
        url: "ajax",
        data: {
          mguniqueurl:"action/addNewPlugin"
        },
        cache: false,
        dataType: 'json',
        success: function(response){
            admin.indication(response.status, response.msg);
            admin.show("plugins.php","adminpage",'',plugin.createSwitch);
          $('.img-loader').hide();
        }
      }).submit();
    },

     /**
     * Удаляет плагин из системы
     */
    deletePlugin: function(id) {

      if(confirm(lang.DELETE+'?')){
        admin.ajaxRequest({
          mguniqueurl:"action/deletePlugin",
          id: id
        },
        (function(response) {
          admin.indication(response.status, response.msg);
          $('.plugins-tables tr[id='+id+']').remove();
         })
        );
      }


    },
    
    /*
     * Проверяет наличие обновления для плагинов
     */
    checkPluginsUpdate: function(){
      admin.ajaxRequest({
        mguniqueurl:"action/checkPluginsUpdate",
      },function(response){
        admin.indication(response.status, response.msg);
        $('a[id=plugins]').trigger('click');
      });
    },
    
    /*
     * Обновляет плагин
     */
    updatePlugin: function(id){
      admin.ajaxRequest({
        mguniqueurl:"action/updatePlugin",
        pluginName: id
      },function(response){
        if(!response.data['last_version'] && response.status != 'error'){
          plugin.updatePlugin(id);
        }else{
          admin.indication(response.status, response.msg);
          if(response.status != 'error'){
            $('a[id=plugins]').trigger('click');
          }
        }
      });
    }


  }
})();



plugin.init();