
$(document).ready(function(){
  $(".mg-close-popup").on("click", function(){
    $(this).parents(".mg-fake-cart").fadeOut("fast");
    $(this).parents("body").find(".mg-layer").hide();
    $("body").removeClass("mg-lock");
  });

  $("body").on("click", ".mg-layer", function(){
    $(".mg-layer").fadeOut("fast");
    $(".mg-fake-cart").fadeOut("fast");
    $("body").removeClass("mg-lock");
  });


  //Показать маленькую корзину
  $('.mg-desktop-cart').hover(function(event){
    event.stopPropagation();
    if($('.small-cart-table tbody tr').length > 0){
      $('.small-cart').addClass("show");
    }
  },
    function(){
      $('.small-cart').removeClass("show");
    }
  );


  // Заполнение корзины аяксом
  $('body').on('click', '.addToCart', function(){

    var productId = $(this).data('item-id');
    transferEffect(productId, $(this), '.product-wrapper');

    if($(this).parents('.property-form').length){
      var request = $(this).parents('.property-form').formSerialize();
    } else{
      var request = 'inCartProductId=' + $(this).data('item-id') + "&amount_input=1";
    }


    $.ajax({
      type: "POST",
      url: mgBaseDir + "/cart",
      data: "updateCart=1&" + request,
      dataType: "json",
      cache: false,
      success: function(response){
        $(".mg-layer").show();
        $(".mg-fake-cart").fadeIn("fast");
        $("body").addClass("mg-lock");

        if('success' == response.status){
          dataSmalCart = '';
          response.data.dataCart.forEach(printSmalCartData);
          $('.small-cart-table').html(dataSmalCart);
          $('.total .total-sum span').text(response.data.cart_price_wc);
          $('.pricesht').text(response.data.cart_price);
          $('.countsht').text(response.data.cart_count);
        }
      }
    });

    return false;
  });

  // Удаление вещи из корзины аяксом
  $('body').on('click', '.deleteItemFromCart', function(){

    var $this = $(this);
    var itemId = $this.data('delete-item-id');
    var property = $this.data('property');
    var $vari = $this.data('variant');
    $.ajax({
      type: "POST",
      url: mgBaseDir + "/cart",
      data: {
        action: "cart", // название действия в пользовательском класса Ajaxuser
        delFromCart: 1,
        itemId: itemId,
        property: property,
        variantId: $vari
      },
      dataType: "json",
      cache: false,
      success: function(response){
        if('success' == response.status){
          var table = $('.deleteItemFromCart[data-property="' + property + '"][data-delete-item-id="' + itemId + '"]').parents('table');
          $('.deleteItemFromCart[data-property="' + property + '"][data-delete-item-id="' + itemId + '"]').parents('tr').remove();
          var i = 1;
          table.find('.index').each(function(){
            $(this).text(i++);
          });

          $('.total .total-sum span').text(response.data.cart_price_wc);
          response.data.cart_price = response.data.cart_price?response.data.cart_price:0;
          response.data.cart_count = response.data.cart_count?response.data.cart_count:0;
          $('.pricesht').text(response.data.cart_price);
          $('.countsht').text(response.data.cart_count);
          $('.cart-table .total-sum-cell strong').text(response.data.cart_price_wc);

          if($('.small-cart-table tbody tr').length == 0){
            $(".mg-layer").hide();
            $(".mg-fake-cart").hide();
            $("body").removeClass("mg-lock");
            $('.small-cart').hide();
            $('.mg-desktop-cart .cart').css({border: '1px solid transparent'});
            $('.mg-desktop-cart .cart-inner').css({background: 'none'});
            $('.empty-cart-block').show();
            $('.product-cart').hide();
            $(".cart-list .show-btn").hide();
          }
          ;

        }
      }
    });

    return false;
  });

  // строит содержимое маленькой корзины в  выпадащем блоке
  function printSmalCartData(element, index, array){

    dataSmalCart += '<tr>\
				<td class="small-cart-img">\
					<a href="' + mgBaseDir + '/' + ((element.category_url||element.category_url=='')?element.category_url:'catalog/') 
      + element.product_url + '"><img src="' + element.image_url_new + '" alt="'
      + element.title + '" alt="" /></a>\
				</td>\
				<td class="small-cart-name">\
					<ul class="small-cart-list">\
						<li><a href="' + mgBaseDir + '/' + ((element.category_url||element.category_url=='')?element.category_url:'catalog/') 
      + element.product_url + '">' + element.title + '</a><span class="property">'
      + element.property_html + '</span></li>\
						<li class="qty">x' + element.countInCart + ' <span>'
      + element.priceInCart + '</span></li>\
					</ul>\
				</td>\
				<td class="small-cart-remove"><a href="#" class="deleteItemFromCart" title="Удалить" data-delete-item-id="' + element.id
      + '" data-property="' + element.property
      + '">&#215;</a></td>\
			</tr>';
  }

});