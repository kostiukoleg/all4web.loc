/**
 * Модуль для смены языка админки
 */
var changeLang = (function () {
  return {

    init: function() {
      //установка флага
      //alert($('#languageLocale').text());
      //$('.select-language').css('background', '/images/'+$('#languageLocale').text()+'.png');

      //Выпадающее меню выбора языков
      $('.language').hover(
      function () {
      $(".language-list-wrapper", this).show();
      },
      function(){
        $(".language-list-wrapper", this).hide();
      }
      );

      $('.language-list-wrapper a').click(function(){
        var locale = $(this).attr('class');
        changeLang.changeLanguage(locale);

      });


    },
    changeLanguage: function(language) {
      admin.ajaxRequest({
        mguniqueurl: "action/changeLanguage",
        language: language
      },
      (function(response) {
        window.location = admin.SITE+'/mg-admin/';
      })
     )
    }
  }
})();

// инициализация модуля при подключении
changeLang.init();