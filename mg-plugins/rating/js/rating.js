/** Функция для отправки на сервер оценки и получение нового значения рейтинга и 
 * количества голосов, одновление звезд рейтинга.
 * Голосовать второй раз возможно лишь при удалении куки
 */

var mgrate = (function() {
  return {
    init: function() {
      mgrate.reloadCount();
      mgrate.initStar();
    },
    initStar: function() {
      $('div.rateit, span.rateit').rateit();
    },
    /**
     * Функция для навешивания звездочек
     */
    reloadCount: function() {
      $('.rating-action .rateit').bind('rated', function(e) {
        var ri = $(this);
        var product = ri.data('productid'); //id товара
        var value = ri.rateit('value'); // оценка от 1 до 5

        $.ajax({
          type: "POST",
          url: mgBaseDir + "/ajaxrequest",
          dataType: 'json',
          data: {
            mguniqueurl: "action/getEntity", // действия для выполнения на сервере
            pluginHandler: 'rating',
            rating: value,
            id_product: product,
            count: 1,
          },
          success: function(response) {
            if (response.status != 'error') {
              var newValue = mgrate.showResult(response.data.row);
              ri.rateit('readonly', true);
              ri.rateit('value', newValue);
            } else {
              $('.info').text("Ошибка!");
            }
          }
        });

      });

      var tooltipvalues = ['плохо', 'нормально', 'хорошо', 'очень хорошо', 'отлично'];
      $('.rating-action .rateit[data-plugin=stars]').bind('over', function(event, value) {
        $(this).attr('title', tooltipvalues[value - 1]);
      });
    },
    showResult: function(data) {
      var id = data.id_product;
      var grade = data.rating;
      var count = data.count;
      var rating = (grade / count).toFixed(1);
      $('.info [data-rating=' + id + ']').html(rating);
      $('.info [data-count=' + id + ']').html('('+count+')');
      return rating;
    }
  }
})();

$(document).ready(function() {
  mgrate.init();
  if (typeof (AJAX_CALLBACK_FILTER) != 'undefined') {
  AJAX_CALLBACK_FILTER.push({callback: 'mgrate.reloadCount', param: null});
  AJAX_CALLBACK_FILTER.push({callback: 'mgrate.initStar', param: null});
}
});

