/**
 * В этом файле собран весь JS скрипт необходимый для функционирования компонентов сайта.
 */

;(function(u){var I,e=typeof define=='function'&&typeof define.amd=='object'&&define.amd&&define,J=typeof exports=='object'&&exports,q=typeof module=='object'&&module,h=typeof require=='function'&&require,o=2147483647,p=36,i=1,H=26,B=38,b=700,m=72,G=128,C='-',E=/^xn--/,t=/[^ -~]/,l=/\x2E|\u3002|\uFF0E|\uFF61/g,s={overflow:'Overflow: input needs wider integers to process','not-basic':'Illegal input >= 0x80 (not a basic code point)','invalid-input':'Invalid input'},v=p-i,g=Math.floor,j=String.fromCharCode,n;function y(K){throw RangeError(s[K])}function z(M,K){var L=M.length;while(L--){M[L]=K(M[L])}return M}function f(K,L){return z(K.split(l),L).join('.')}function D(N){var M=[],L=0,O=N.length,P,K;while(L<O){P=N.charCodeAt(L++);if((P&63488)==55296&&L<O){K=N.charCodeAt(L++);if((K&64512)==56320){M.push(((P&1023)<<10)+(K&1023)+65536)}else{M.push(P,K)}}else{M.push(P)}}return M}function F(K){return z(K,function(M){var L='';if(M>65535){M-=65536;L+=j(M>>>10&1023|55296);M=56320|M&1023}L+=j(M);return L}).join('')}function c(K){return K-48<10?K-22:K-65<26?K-65:K-97<26?K-97:p}function A(L,K){return L+22+75*(L<26)-((K!=0)<<5)}function w(N,L,M){var K=0;N=M?g(N/b):N>>1;N+=g(N/L);for(;N>v*H>>1;K+=p){N=g(N/v)}return g(K+(v+1)*N/(N+B))}function k(L,K){L-=(L-97<26)<<5;return L+(!K&&L-65<26)<<5}function a(X){var N=[],Q=X.length,S,T=0,M=G,U=m,P,R,V,L,Y,O,W,aa,K,Z;P=X.lastIndexOf(C);if(P<0){P=0}for(R=0;R<P;++R){if(X.charCodeAt(R)>=128){y('not-basic')}N.push(X.charCodeAt(R))}for(V=P>0?P+1:0;V<Q;){for(L=T,Y=1,O=p;;O+=p){if(V>=Q){y('invalid-input')}W=c(X.charCodeAt(V++));if(W>=p||W>g((o-T)/Y)){y('overflow')}T+=W*Y;aa=O<=U?i:(O>=U+H?H:O-U);if(W<aa){break}Z=p-aa;if(Y>g(o/Z)){y('overflow')}Y*=Z}S=N.length+1;U=w(T-L,S,L==0);if(g(T/S)>o-M){y('overflow')}M+=g(T/S);T%=S;N.splice(T++,0,M)}return F(N)}function d(W){var N,Y,T,L,U,S,O,K,R,aa,X,M=[],Q,P,Z,V;W=D(W);Q=W.length;N=G;Y=0;U=m;for(S=0;S<Q;++S){X=W[S];if(X<128){M.push(j(X))}}T=L=M.length;if(L){M.push(C)}while(T<Q){for(O=o,S=0;S<Q;++S){X=W[S];if(X>=N&&X<O){O=X}}P=T+1;if(O-N>g((o-Y)/P)){y('overflow')}Y+=(O-N)*P;N=O;for(S=0;S<Q;++S){X=W[S];if(X<N&&++Y>o){y('overflow')}if(X==N){for(K=Y,R=p;;R+=p){aa=R<=U?i:(R>=U+H?H:R-U);if(K<aa){break}V=K-aa;Z=p-aa;M.push(j(A(aa+V%Z,0)));K=g(V/Z)}M.push(j(A(K,0)));U=w(Y,P,T==L);Y=0;++T}}++Y;++N}return M.join('')}function r(K){return f(K,function(L){return E.test(L)?a(L.slice(4).toLowerCase()):L})}function x(K){return f(K,function(L){return t.test(L)?'xn--'+d(L):L})}I={version:'1.2.0',ucs2:{decode:D,encode:F},decode:a,encode:d,toASCII:x,toUnicode:r};if(J){if(q&&q.exports==J){q.exports=I}else{for(n in I){I.hasOwnProperty(n)&&(J[n]=I[n])}}}else{if(e){define('punycode',I)}else{u.punycode=I}}}(this));

function convertPunicode(val) {
		val = val.replace('http://', '');	
		ascii = punycode.toASCII(val),
		uni = punycode.toUnicode(val);
		if(ascii == val)
			res = uni;
		else if(uni == val)
			res = ascii;
		else
			res = val;
	return res;
}

var mgBaseDir = '';
var protocol = '';
$(document).ready(function() {
  $('script').each(function(){     
   if($(this).attr('src')){  
    $(this).attr('src').replace(/&amp;/g,'&');
    $(this).attr('src').replace(/(\w+)(?:=([^&]*))?/g, function(a, key, value) {
       if(key === 'protocol'){    
	     protocol = value;	
       }   
	   if(key === 'mgBaseDir'){    
		 if(protocol=='http'){ 
           mgBaseDir = 'http://'+convertPunicode(value);
		 }else{
		   mgBaseDir = convertPunicode(value);
		 }	
       }   
       if(key === 'currency'){           
           currency=value;         
       }    
     });    
   }  
  });
  if(!mgBaseDir){
    mgBaseDir = $('.mgBaseDir').text();
  }


  //Инициализация табов в личном кабинете
  $('.personal-tabs').tabs();

  //Показать форму закрытия заказов
  $('.close-order, .change-payment').click(function() {
    $('.reason-text').val('');
    $('strong[class=orderId]').text($(this).attr('id'));
    $('span[class=orderDate]').text($(this).attr('date'));
  });

  //Инициализация fancybox
  $(".change-payment,.close-order, a.fancy-modal").fancybox({
    'overlayShow': false
  });


  //при наведении на фото, появляется лупа для увеличения
  $('a.fancy-modal').hover(
    function() {
      $('.zoom').stop().fadeTo(200, 1.0);
    },
    function() {
      $('.zoom').stop().fadeTo(200, 0.0);
    }
  );

  //эмуляция радиокнопок в форме характеристик продукта
  $('body').on('change', '.property-form input[type=radio]', function(){
    $(this).parents('p').find('input[type=radio]').prop('checked',false);
    $(this).prop('checked',true);
  });    
           
  //пересчет цены товара аяксом
  $('body').on('change', '.property-form input, .property-form select , .product-wrapper .block-variants select', function(){
       
      var request = $('.buy-block form').formSerialize();  
      var priceBlock = '.product-status-list .price';
      
      if($(this).parents('.product-wrapper').length){// для вызова из каталога
        priceBlock = $(this).parents('.product-wrapper').find('.product-price');
        request = $(this).parents('.product-wrapper').find('.property-form').formSerialize(); 
      }
   
     // Пересчет цены            
      $.ajax({
        type: "POST",
        url: mgBaseDir+"/product",
        data: "calcPrice=1&"+request,
        dataType: "json",
        cache: false,
        success: function(response) {      
         
          $('.rem-info').hide();
          $('.buy-container.product .hidder-element').hide();
          if ($('.buy-block .count').length >1) {
            $('.buy-container .hidder-element').hide();
          }  
          if ('success' == response.status) {    
          
            $(priceBlock).text(response.data.price);   
            $('.product-status-list .code').text(response.data.code); 
            var message = 'Здравствуйте, меня интересует товар "'+response.data.title.replace("'",'"')+'" с артикулом "'+response.data.code+'", но его нет в наличии.\
              Сообщите, пожалуйста, о поступлении его этого товара на склад. ';
            $('.rem-info a').attr('href',mgBaseDir+'/feedback?message='+message);
            $('.code-msg').text(response.data.code);             
            var val = response.data.count;                   
            if(val!=0){ 
              $('.rem-info').hide();  
              if ($('.buy-block .count').length >0) {
                $('.buy-container .hidder-element').show();
              } 
            } else{             
              $('.rem-info').show();          
              if ($('.buy-block .count').length >0) {
                $('.buy-container .hidder-element').hide();
              } 
            }  
                  
            if((val=='\u221E'||val==''||parseFloat(val)<0)){val = '∞'; $('.rem-info').hide();}
            $('.product-status-list .count').text(val); 
            var val = response.data.old_price;          
            if(val!="0 "+currency && val!=' '+currency){         
               $('.product-status-list .old-price').parent('li').show();               
            } else{             
               $('.product-status-list .old-price').parent('li').hide();
            }          
            $('.product-status-list .old-price').text(response.data.old_price);                     
            $('.buy-block .amount_input').data('max-count',response.data.count);   
          
            $('.product-status-list .weight').text(response.data.weight); 
            
            if(parseFloat($('.buy-block .amount_input').val()) > parseFloat(response.data.count)) {   
              val = response.data.count;
              if((val=='\u221E'||val==''||parseFloat(val)<0)){
                val = $('.buy-block .amount_input').val();
              }             
              $('.buy-block .amount_input').val(val);      
            }       
          }
        }
      });

      return false;

  
    
  });    
  
  //Закрытие заказа из личного кабинета
  $('.close-order-btn').click(function() {   
    var id = $(this).parent('#openModal').find('strong[name=orderId]').text();
    var comm = $('.reason-text').val();
    $.ajax({
      type: "POST",
      url:  mgBaseDir+"/personal",
      data: {
        delOK: "OK",
        delID: id,
        comment: comm
      },
      cache: false,
      dataType: 'json',
      success: function(response) {
        if (response.status) {
          $('a[name=next]').click();
          $('.order-history#' + id + ' .order-number .order-status strong').text(response.orderStatus);
          $('p#order-comm').html(response.comment);
          $('.order-history#' + id + ' .order-settings').remove();
        } else {
          $('a[name=error]').click();
        }
      }
    });    
  });

  //Смена способа оплаты в ЛК
  $('.change-payment-btn').click(function() {  
    var paymetId = $(this).parent().find('.order-changer-pay').val();
    var paymetName = $(this).parent().find('.order-changer-pay option:selected').text();
    var id = $(this).parent('#changePayment').find('strong[name=orderId]').text();
    $('.order-history#'+id).find('input[name=paymentId]').val(paymetId);
    $('.order-history#'+id).find('.paymen-name-to-history').text(paymetName);    
    $.fancybox.close();
    
    $.ajax({
      type: "POST",
      url:  mgBaseDir+"/personal",
      data: {
        changePaymentId: paymetId,
        orderId: id,     
      },
      cache: false,
      dataType: 'json',
      success: function(response) {
       
      }
    });   
    
  });


  //Количество товаров
  $('.amount_change .up').unbind(); 
  $('.amount_change .up').click(function() {
    //bp-за вариантов товара делаем  бесконечное возможное количесво
   // 
    
    var obj = $(this).parents('.cart_form').find('.amount_input');   
    var val = obj.data('max-count');
    if((val=='\u221E'||val==''||parseFloat(val)<0)){ obj.data('max-count', 9999);} 
    var i = obj.val();
    i++;
    if (i > obj.data('max-count')) {       
      i=obj.data('max-count');
    }
    obj.val(i);
    return false;
  });
  
  $('.amount_change .down').unbind(); 
  $('.amount_change .down').click(function() {
    var obj = $(this).parents('.cart_form').find('.amount_input');
    var val = obj.val();
   // if((val=='\u221E'||val==''||parseFloat(val)<0)){val = 0;} 
    var i = val;
    i--;
    if (i <= 0) {
      i = 1;
    } 
    obj.val(i)
    return false;
  });

  //Показать суб меню при клике
  $("ul.cat-list li:has(ul)").addClass("active-menu");
  $("ul.sub-cat-list li:has(ul)").addClass("active-menu");
 

  // Обработка ввода поисковой фразы в поле поиска
  $('body').on('keyup', 'input[name=search]', function() {

    var text = $(this).val();
    if (text.length >= 2) {
      $.ajax({
        type: "POST",
        url: "ajax",
        data: {
          action: "getSearchData", // название действия в пользовательском класса Ajaxuser
          actionerClass: "Ajaxuser", // ajaxuser.php - в папке шаблона
          search: text
        },
        dataType: "json",
        cache: false,
        success: function(data) {
          var html = '<ul class="fast-result-list">';
          var currency = data.currency;
          function buildElements(element, index, array) {
            html += '<li><a href="'
                    + mgBaseDir + '/' 
                    + (element.category_url ? element.category_url : 'catalog') 
                    + '/' 
                    + element.product_url + '"> <div class="fast-result-img"><img src="' 
                    + mgBaseDir  + '/uploads/thumbs/30_' + element.image_url + '" alt="' 
                    + element.title + '"/></div><div>' + element.title + '</div><span>' 
                    + element.price + ' ' + currency + '</span></a></li>';
          }
          ;

          if ('success' == data.status && data.item.items.catalogItems.length > 0) {
            data.item.items.catalogItems.forEach(buildElements);
            html += '</ul>';
            $('.fastResult').html(html);
            $('.fastResult').show();
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
    var container = $(".fastResult");
    if (container.has(e.target).length === 0 && $(".search-block").has(e.target).length === 0) {
      container.hide();
    }
  });


  // Исключение ввода в поле выбора количесва недопустимых значений
  $('body').on('keyup', '.amount_input', function() {
    if ($(this).hasClass('zeroToo')) {
      if (isNaN($(this).val()) || $(this).val() < 0) {
        $(this).val('1');
      }
      
    } else {
      if (isNaN($(this).val()) || $(this).val() <= 0) {
        $(this).val('1');        
      }
      $(this).val($(this).val().replace(/\./g, ''));    
    }
    if (parseFloat($(this).val()) > parseFloat($(this).data('max-count')) && parseFloat($(this).data('max-count'))>0) { 
       $(this).val($(this).data('max-count'));
    }
  });

   //полет картинки в корзину
   $(".addToCart, .product-buy").click(function() {
      
      var $css = {
        'height': '100%',
        "opacity": 0.5,
        "position": "relative",
        "z-index": 100
      };
      
      var $transfer = {
         to: $(".small-cart-icon"),
         className: "transfer_class"
      }
      
      if($('.product-details-image').length){
        $('.product-details-image').each(function() { 
          if($(this).css('display')!='none'){
            $(this).find('img').effect("transfer", $transfer, 600);
            $('.transfer_class').html($(this).html());           
          }
        });  
      }else{
        $(this).closest('.product-wrapper, .product-details-block').find('.product-image a img, .product-details-image img:first').effect("transfer", $transfer, 600);
        $('.transfer_class').html($(this).closest('.product-wrapper, .product-details-block').find('.product-image, .product-details-image ').html());
      }
      
      $('.transfer_class').find('img').css($css);
  });

  // Исключение ввода в поле выбора количесва недопустимых значений
  $('body').on('.deleteFromCart', function() {
    if (isNaN($(this).val()) || $(this).val() <= 0) {
      $(this).val('1');
    }
  });
  
  // скрыть ошибки при переходе на другой таб в ЛК
  $('.personal-tabs li').click(function() {
    $('.personalInformer').hide();
  });


  if($('input[name=toOrder]').prop("disabled")){
    disabledToOrderSubmit(true);
  }

  if($('.delivery-details-list input[name=delivery]:checked').val()){
    disabledToOrderSubmit(false);
  }

  if($('.payment-details-list input[name=payment]:checked').val()){
    disabledToOrderSubmit(false);
  }
  
   // действия при оформлении заказа
  $('.delivery-details-list input').click(function() {
    $("p#auxiliary").html('');
    $('.delivery-details-list input[name=delivery]').parent().addClass('noneactive');
    $('.delivery-details-list input[name=delivery]').parent().removeClass('active');
    
    $('.delivery-details-list input[name=delivery]:checked').parent().removeClass('noneactive');
    $('.delivery-details-list input[name=delivery]:checked').parent().addClass('active');
    
    var deliveryId = $('.delivery-details-list input[name=delivery]:checked').val();
    $('.payment-details-list').before('<div class="loader"></div>');
    disabledToOrderSubmit(true);   

    $.ajax({
      type: "POST",
      url:  mgBaseDir+"/order",
      data: {
        action: "getPaymentByDeliveryId",
        deliveryId: deliveryId
      },
      dataType: "json",
      cache: false,
      success: function(response) {
        var paymentTable = response.paymentTable;
        if('' == paymentTable || null == paymentTable){ paymentTable = 'нет доступных способов оплаты'; disabledToOrderSubmit(false);}
        $('.payment-details-list').html(paymentTable);
        $('.loader').remove();
        $('.payment-details-list input[name=payment]').prop("checked", false);  
        if($('.payment-details-list input[name=payment]').length==1){        
          disabledToOrderSubmit(false);
          $('.payment-details-list input[name=payment]').prop("checked", true);  
        }
      }
    });

  });
  
  $('.form-list select[name="customer"]').change(function(){
    if ($(this).val() == 'fiz') {
      $('.form-list.yur-field').hide();
    }
    if ($(this).val() == 'yur') {
      $('.form-list.yur-field').show();
    }
  });
  
  
  $('body').on('click', '.payment-details-list input[name=payment]:checked', function(){
    $("p#auxiliary").html('');
    $('.payment-details-list input[name=payment]').parent().addClass('noneactive');
    $('.payment-details-list input[name=payment]').parent().removeClass('active');    
    $('.payment-details-list input[name=payment]:checked').parent().removeClass('noneactive');
    $('.payment-details-list input[name=payment]:checked').parent().addClass('active');    
    disabledToOrderSubmit(false);
  });
  
  function disabledToOrderSubmit(flag) {
    if(!flag){
      $('input[name=toOrder]').prop("disabled", false);
      $('input[name=toOrder]').removeClass('disabled-btn');
    }else{
      $('input[name=toOrder]').prop("disabled", true);
      $('input[name=toOrder]').addClass('disabled-btn');
    }
  }
    
});