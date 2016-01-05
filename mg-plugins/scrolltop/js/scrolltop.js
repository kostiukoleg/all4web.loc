// плагин для кнопки вверх
(function ($) {
  /* использование: <a class='mg-scrollTop' href='#' style='display:none;'></a>
   ------------------------------------------------- */
  $(function () {
    var e = $(".mg-scrollTop");
    var speed = 700;
    
    e.click(function () {
      $("html:not(:animated), body:not(:animated)").animate({scrollTop: 0}, speed);
      return false; //важно!
    });
    //появление
    function show_scrollTop() {
      ($(window).scrollTop() > 200) ? e.fadeIn(600) : e.hide();
    }
    $(window).scroll(function () {
      show_scrollTop();
    });
    show_scrollTop();
  });

})(jQuery)
