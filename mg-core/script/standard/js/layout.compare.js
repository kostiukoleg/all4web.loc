$(document).ready(function() {
  // Заполнение списка аяксом
  $('body').on('click', '.addToCompare', function() {

    var request = 'inCompareProductId=' + $(this).data('item-id');

    $.ajax({
      type: "GET",
      url: mgBaseDir + "/compare",
      data: "updateCompare=1&" + request,
      dataType: "json",
      cache: false,
      success: function(response) {
        $('.mg-compare-count').html(response.count);
        $('.mg-product-to-compare').fadeOut('normal');
        $('.mg-product-to-compare').fadeIn('normal');
      }
    });

    return false;
  });

  //double scroll compare products
  function DoubleScroll(element) {
    var scrollbar = document.createElement('div');
    scrollbar.setAttribute("class", "mg-top-scroll");
    scrollbar.appendChild(document.createElement('div'));
    scrollbar.style.overflow = 'auto';
    scrollbar.style.overflowY = 'hidden';
    scrollbar.firstChild.style.width = element.scrollWidth + 'px';
    scrollbar.firstChild.style.paddingTop = '1px';
    scrollbar.firstChild.appendChild(document.createTextNode('\xA0'));
    scrollbar.onscroll = function() {
      element.scrollLeft = scrollbar.scrollLeft;
    };
    element.onscroll = function() {
      scrollbar.scrollLeft = element.scrollLeft;
    };
    element.parentNode.insertBefore(scrollbar, element);
  }
  if (document.getElementById('doublescroll')) {
    DoubleScroll(document.getElementById('doublescroll'));
  }

});