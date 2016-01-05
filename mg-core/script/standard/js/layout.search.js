$(document).ready(function() {


  // Обработка ввода поисковой фразы в поле поиска
  $('body').on('keyup', 'input[name=search]', function() {

    var text = $(this).val();
    if (text.length >= 2) {
      $.ajax({
        type: "POST",
        url: "ajax",
        type: "POST",
          url:  mgBaseDir + "/catalog",
          data: {
            fastsearch: "true",
            text: text
          },
        dataType: "json",
        cache: false,
        success: function(data) {
          var html = '<ul class="fast-result-list">';
          var currency = data.currency;
          function buildElements(element, index, array) {

            text = $.trim(text);
            element.title = element.title.replace(eval("/" + text + "/gi"), "<b style='background:rgb(172, 207, 165)'>" + text + "</b>");

            html += '<li>' +
              '<a href="' + mgBaseDir + '/' + (element.category_url ? element.category_url : 'catalog') + '/' + element.product_url + '">' +
              '<div class="fast-result-img">' +
              '<img src="' + element.image_url + '" />' +
              '</div>' +
              '<div class="fast-result-info">' +
              element.title +
              '<span>' + element.price + ' ' + currency + '</span>' +
              '<span class="variant-text">' + (element.variant_exist ? 'есть варианты' : '') + '</span>' +
              '</div>' +
              '</a>' +
              '</li>';
          }
          ;

          if ('success' == data.status && data.item.items.catalogItems.length > 0) {
            data.item.items.catalogItems.forEach(buildElements);
            html += '</ul>';
            $('.fastResult').html(html);
            $('.fastResult').show();
            $('.wraper-fast-result').show();

          } else {
            $('.fastResult').hide();
          }
        }
      });
    } else {
      $('.fastResult').hide();
    }
  });

  // клик вне поиска
  $(document).mousedown(function(e) {
    var container = $(".wraper-fast-result");
    if (container.has(e.target).length === 0 && $(".search-block").has(e.target).length === 0) {
      container.hide();
    }
  });

});  