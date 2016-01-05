
/**
 * Модуль для смены визуального оформления админки.
 * Сохранение выбраных параметров происходит не в
 * момент клика на цвет, а когда меню сворачивается, дабы
 * не перегружать запросами сервер.
 */
var changeTheme = (function () {
  return {

    color: $('#color-theme').text(),
    background: $('#bg-theme').text(),
    bufercolor: $('#color-theme').text(),
    buferbackground: $('#bg-theme').text(),
    /**
     * Инициализирует обработчики для кнопок и элементов раздела.
     */
    init: function() {

      changeTheme.applyTheme(changeTheme.color);
      $('.admin-wrapper .admin-top-menu').css('display','block');

      // смена цвета меню
      $('body').on('click', '.color-settings .color-list li', function(){
       changeTheme.color = changeTheme.applyTheme($(this).attr('class'));
       $('input[name="themeColor"]').val(changeTheme.color);
      });

       // смена фона
      $('body').on('click', '.background-settings .color-list li', function(){
       changeTheme.background = changeTheme.applybackground($(this).attr('class'));
       $('input[name="themeBackground"]').val(changeTheme.background);
      });


    },

    applyTheme: function(theme) {


      switch (theme) {
        case 'red-theme':{
          return changeTheme.redtheme();
          break;
        }
        case 'blue-theme':{
          return changeTheme.bluetheme();
          break;
        }
        case 'green-theme':{
          return changeTheme.greentheme();
          break;
        }
        case 'yellow-theme':{
          return changeTheme.yellowtheme();
          break;
        }
        default:{
          return changeTheme.redtheme();
          break;
        }
      }
    },

    applybackground: function(bg) {
      	$('body, html').css({
      'backgroundImage':'url('+admin.SITE+'/mg-admin/design/images/bg_textures/'+bg+'.png)'
      });
      return bg;
    },

    redtheme: function() {

    		$('.admin-wrapper .admin-top-menu').css({
      'backgroundColor':'#BA0A0A',
      'borderBottom':'2px solid #FC5858',
      'borderTop':'1px solid #FC5858',
      'boxShadow':'1px 1px 2px #BA0A0A'
      });
      $('.admin-top-menu-list > li > a, .double-border').css({
      'borderLeft':'1px solid #7D1616',
      'borderRight':'1px solid #DF4D4D'
      });
      $('.no-left-border a').css({
      'borderLeft':'none'
      });

      return 'red-theme';
    },

    bluetheme: function() {

      $('.admin-wrapper .admin-top-menu').css({
      'backgroundColor':'#1A86B2',
      'borderBottom':'2px solid #4EB4DE',
      'borderTop':'1px solid #4EB4DE',
      'boxShadow':'1px 1px 2px #00466C'
      });
      $('.admin-top-menu-list > li > a, .double-border').css({
      'borderLeft':'1px solid #004A70',
      'borderRight':'1px solid #4EB4DE'
      });
      $('.no-left-border a').css({
      'borderLeft':'none'
      });

      return "blue-theme";
   },

   greentheme: function() {
    $('.admin-wrapper .admin-top-menu').css({
		'backgroundColor':'#0D9803',
		'borderBottom':'2px solid #4FDA45',
		'borderTop':'1px solid #4FDA45',
		'boxShadow':'1px 1px 2px #139E09'
		});
		$('.admin-top-menu-list > li > a, .double-border').css({
		'borderRight':'1px solid #4FDA45',
		'borderLeft':'1px solid #002F00'
		});
		$('.no-left-border a').css({
		'borderLeft':'none'
		});
   return "green-theme";
  },

  yellowtheme: function() {
   $('.admin-wrapper .admin-top-menu').css({
		'backgroundColor':'#C1A700',
		'borderBottom':'2px solid #FBE13A',
		'borderTop':'1px solid #FBE13A',
		'boxShadow':'1px 1px 2px #7E6506'
		});
		$('.admin-top-menu-list > li > a, .double-border').css({
		'borderLeft':'1px solid #8A7000',
		'borderRight':'1px solid #DFC629'
		});
		$('.no-left-border a').css({
		'borderLeft':'none'
		});
   return "yellow-theme";
  }

  }
})();

// инициализациямодуля при подключении
changeTheme.init();