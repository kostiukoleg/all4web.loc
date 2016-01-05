/**
 * В этом файле собран весь JS скрипт необходимый для функционирования компонентов сайта.
 */
;(function(u){var I,e=typeof define=='function'&&typeof define.amd=='object'&&define.amd&&define,J=typeof exports=='object'&&exports,q=typeof module=='object'&&module,h=typeof require=='function'&&require,o=2147483647,p=36,i=1,H=26,B=38,b=700,m=72,G=128,C='-',E=/^xn--/,t=/[^ -~]/,l=/\x2E|\u3002|\uFF0E|\uFF61/g,s={overflow:'Overflow: input needs wider integers to process','not-basic':'Illegal input >= 0x80 (not a basic code point)','invalid-input':'Invalid input'},v=p-i,g=Math.floor,j=String.fromCharCode,n;function y(K){throw RangeError(s[K])}function z(M,K){var L=M.length;while(L--){M[L]=K(M[L])}return M}function f(K,L){return z(K.split(l),L).join('.')}function D(N){var M=[],L=0,O=N.length,P,K;while(L<O){P=N.charCodeAt(L++);if((P&63488)==55296&&L<O){K=N.charCodeAt(L++);if((K&64512)==56320){M.push(((P&1023)<<10)+(K&1023)+65536)}else{M.push(P,K)}}else{M.push(P)}}return M}function F(K){return z(K,function(M){var L='';if(M>65535){M-=65536;L+=j(M>>>10&1023|55296);M=56320|M&1023}L+=j(M);return L}).join('')}function c(K){return K-48<10?K-22:K-65<26?K-65:K-97<26?K-97:p}function A(L,K){return L+22+75*(L<26)-((K!=0)<<5)}function w(N,L,M){var K=0;N=M?g(N/b):N>>1;N+=g(N/L);for(;N>v*H>>1;K+=p){N=g(N/v)}return g(K+(v+1)*N/(N+B))}function k(L,K){L-=(L-97<26)<<5;return L+(!K&&L-65<26)<<5}function a(X){var N=[],Q=X.length,S,T=0,M=G,U=m,P,R,V,L,Y,O,W,aa,K,Z;P=X.lastIndexOf(C);if(P<0){P=0}for(R=0;R<P;++R){if(X.charCodeAt(R)>=128){y('not-basic')}N.push(X.charCodeAt(R))}for(V=P>0?P+1:0;V<Q;){for(L=T,Y=1,O=p;;O+=p){if(V>=Q){y('invalid-input')}W=c(X.charCodeAt(V++));if(W>=p||W>g((o-T)/Y)){y('overflow')}T+=W*Y;aa=O<=U?i:(O>=U+H?H:O-U);if(W<aa){break}Z=p-aa;if(Y>g(o/Z)){y('overflow')}Y*=Z}S=N.length+1;U=w(T-L,S,L==0);if(g(T/S)>o-M){y('overflow')}M+=g(T/S);T%=S;N.splice(T++,0,M)}return F(N)}function d(W){var N,Y,T,L,U,S,O,K,R,aa,X,M=[],Q,P,Z,V;W=D(W);Q=W.length;N=G;Y=0;U=m;for(S=0;S<Q;++S){X=W[S];if(X<128){M.push(j(X))}}T=L=M.length;if(L){M.push(C)}while(T<Q){for(O=o,S=0;S<Q;++S){X=W[S];if(X>=N&&X<O){O=X}}P=T+1;if(O-N>g((o-Y)/P)){y('overflow')}Y+=(O-N)*P;N=O;for(S=0;S<Q;++S){X=W[S];if(X<N&&++Y>o){y('overflow')}if(X==N){for(K=Y,R=p;;R+=p){aa=R<=U?i:(R>=U+H?H:R-U);if(K<aa){break}V=K-aa;Z=p-aa;M.push(j(A(aa+V%Z,0)));K=g(V/Z)}M.push(j(A(K,0)));U=w(Y,P,T==L);Y=0;++T}}++Y;++N}return M.join('')}function r(K){return f(K,function(L){return E.test(L)?a(L.slice(4).toLowerCase()):L})}function x(K){return f(K,function(L){return t.test(L)?'xn--'+d(L):L})}I={version:'1.2.0',ucs2:{decode:D,encode:F},decode:a,encode:d,toASCII:x,toUnicode:r};if(J){if(q&&q.exports==J){q.exports=I}else{for(n in I){I.hasOwnProperty(n)&&(J[n]=I[n])}}}else{if(e){define('punycode',I)}else{u.punycode=I}}}(this));

function convertPunicode(val) {
  val = val.replace('http://', '');
  ascii = punycode.toASCII(val),
    uni = punycode.toUnicode(val);
  if (ascii == val)
    res = uni;
  else if (uni == val)
    res = ascii;
  else
    res = val;
  return res;
}

var mgBaseDir = '';
var protocol = '';
var phoneMask='';
$(document).ready(function() {
  $('script').each(function() {
    if ($(this).attr('src')) {
      $(this).attr('src').replace(/&amp;/g, '&');
      $(this).attr('src').replace(/(\w+)(?:=([^&]*))?/g, function(a, key, value) {
        if (key === 'protocol') {
          protocol = value;
        }
        if (key === 'mgBaseDir') {
          if (protocol == 'http') {
            mgBaseDir = 'http://' + convertPunicode(value);
          } else {
            mgBaseDir = convertPunicode(value);
          }
        }
        if (key === 'currency') {
          currency = value;
        }
        if (key === 'phoneMask') {
          phoneMask = value;         
        }
      });
    }
  });
  if (!mgBaseDir) {
    mgBaseDir = $('.mgBaseDir').text();
  }

  //эмуляция радиокнопок в форме характеристик продукта
  $('body').on('change', '.property-form input[type=radio]', function() {
    $(this).parents('p').find('input[type=radio]').prop('checked', false);
    $(this).prop('checked', true);
  });

  //пересчет цены товара аяксом
  $('body').on('change', '.property-form input, .property-form select , .product-wrapper .block-variants select', function() {
    var request = $('.buy-block form').formSerialize();
    var priceBlock = '.product-status-list .price';
    var productList = $('.product-status');

    if ($(this).parents('.product-wrapper').length) {// для вызова из каталога
      priceBlock = $(this).parents('.product-wrapper').find('.product-price');
      request = $(this).parents('.product-wrapper').find('.property-form').formSerialize();
      productList = $(this).parents('.product-wrapper');
    }

    if ($(this).parents('.mg-compare-product').length) {// для вызова из сравнений    
      priceBlock = $(this).parents('.mg-compare-product').find('.price');
      request = $(this).parents('.mg-compare-product').find('.property-form').formSerialize();
      productList = $(this).parents('.mg-compare-product').find('.product-status-list');
    }

    // Пересчет цены            
    $.ajax({
      type: "POST",
      url: mgBaseDir + "/product",
      data: "calcPrice=1&" + request,
      dataType: "json",
      cache: false,
      success: function(response) {

        productList.find('.rem-info').hide();
        
        productList.find('.buy-container.product .hidder-element').hide();
        if ($('.buy-block .count').length > 0) {
            $('.buy-container .hidder-element').hide();
        }  
        if ('success' == response.status) {

          $(priceBlock).text(response.data.price);
          productList.find('.code').text(response.data.code);

          var message = 'Здравствуйте, меня интересует товар "' + response.data.title.replace("'", '"') + '" с артикулом "' + response.data.code + '", но его нет в наличии.\
              Сообщите, пожалуйста, о поступлении этого товара на склад. ';
          productList.find('.rem-info a').attr('href', mgBaseDir + '/feedback?message=' + message);
          productList.find('.code-msg').text(response.data.code);
          var val = response.data.count;
          if (val != 0) {
            productList.find('.rem-info').hide();
            productList.find('.buy-container .hidder-element').show();
            if ($('.buy-block .count').length >0) {
              $('.buy-container .hidder-element').show();
            }  
            productList.find('.buy-container.product').show();
          } else {
            productList.find('.rem-info').show();
            if ($('.buy-block .count').length >0) {
              $('.buy-container .hidder-element').hide();
            }  
            productList.find('.buy-container.product').hide();
          }

          if ((val == '\u221E' || val == '' || parseFloat(val) < 0)) {
            val = 'много';
            productList.find('.rem-info').hide();
          }
          productList.find('.count').text(val);
          var val = response.data.old_price;
          if (val != "0 " + currency && val != ' ' + currency) {
            productList.find('.old-price').parent('li').show();
          } else {
            productList.find('.old-price').parent('li').hide();
          }

          if (val != "0 " + currency && val != ' ' + currency) {
            productList.find('.old-price').text(response.data.old_price);
          }

          productList.find('.amount_input').data('max-count', response.data.count);

          productList.find('.weight').text(response.data.weight);

          if (parseFloat(productList.find('.amount_input').val()) > parseFloat(response.data.count)) {
            val = response.data.count;
            if ((val == '\u221E' || val == '' || parseFloat(val) < 0)) {
              val = productList.find('.amount_input').val();
            }
            if (val == 0) {
              val = 1
            }
            ;
            productList.find('.amount_input').val(val);
          }
        }
      }
    });

    return false;
  });

  // ссылка на главную картинку продукта
  var linkDefaultPreview = "";
  var variantId = "";
  //подстановка картинки варианта вместо картинки товара  
  $('body').on('change', '.block-variants input[type=radio]', function() {
 
    // обработчик подстановки картинки варианта для страницы с карточкой товара
    if ($('.mg-product-slides').length) {
      // текущая ссылка на главную картинку продукта  
      var linkInPreview = $('.mg-product-slides .main-product-slide li a').eq(0).attr('href');
      if (linkDefaultPreview == "") {
        // запоминаем стоящую поумолчанию ссылку на картинку товара
        linkDefaultPreview = linkInPreview;
      }
      // получаем новую ссылку на продукт из картинки варианта
      var src = $(this).parents('tr').find('img').attr('src');
      // если она оличается от той что уже установлена в качестве главной
      if (src != linkInPreview) {
        // проверяем есть ли в варианте ссылка на картинку, еси нет то показываем картинку продукта поумолчанию  
        if (!src) {
          src = linkDefaultPreview;
        }
        // меняем ссылку на картинку в модалке, для увеличенного просмотра  
        $('.mg-product-slides .main-product-slide li a').eq(0).attr('href', src.replace('thumbs/30_', ''));
        // меняем главную картинку товара в просмотрщике
        $('.mg-product-slides .main-product-slide li').eq(0).find('img').attr('src', src.replace('thumbs/30_', 'thumbs/70_')).attr('data-large',src.replace('thumbs/30_', ''));
        // меняем первую картинку товара в ленте просмотрщика
        $('.slides-inner a[data-slide-index=0]').find('img').attr('src', src.replace('thumbs/70_', ''));
        // кликаем по первому элементу, чтобы показать картинку в просмотрщике.
        $('.mg-product-slides a[data-slide-index="0"]').click();
      }
    } else {
      var obj = $(this).parents('.product-wrapper');
      var count = $(this).data('count');
      if (!obj.length) {
        obj = $(this).parents('.mg-compare-product');
      }
      if (obj.length) {// для вызова из каталога

        //Обнуление дефолтной картинки, если перешли к вариантам другого товара 
        if(!variantId){
          variantId = $(this).attr('id');
        }else{
          var newVariantId = $(this).attr('id');
          if(newVariantId != variantId){
            linkDefaultPreview = "";
            variantId = newVariantId;
          }
        }
        
        // текущая ссылка на главную картинку продукта  
        var linkInPreview = obj.find('img[data-transfer="true"]').eq(0).attr('src');
        
        if (linkDefaultPreview == "") {
          // запоминаем стоящую поумолчанию ссылку на картинку товара
          linkDefaultPreview = linkInPreview;
        }
        // получаем новую ссылку на продукт из картинки варианта
        var src = $(this).parents('tr').find('img').attr('src');
        // если она оличается от той что уже установлена в качестве главной
        if (src != linkInPreview) {
          // проверяем есть ли в варианте ссылка на картинку, еси нет то показываем картинку продукта поумолчанию  
          if (!src) {
            src = linkDefaultPreview;
          }
          // меняем ссылку на картинку в модалке, для увеличенного просмотра  
          // $('.mg-product-slides .main-product-slide li a').eq(0).attr('href',src.replace('thumbs/30_', ''));
          // меняем главную картинку товара в просмотрщике    
          obj.find('img[data-transfer="true"]').eq(0).attr('src', src.replace('thumbs/30_', 'thumbs/70_'));
          // меняем первую картинку товара в ленте просмотрщика
          //$('.slides-inner a[data-slide-index=0]').find('img').attr('src',src.replace('thumbs/70_', ''));      
          // кликаем по первому элементу, чтобы показать картинку в просмотрщике.
          //$('.mg-product-slides a[data-slide-index="0"]').click();
        }
      }   
      
      var form = $(this).parents('form');
    
      if(form.hasClass('actionView')){
        return false;
      }
      
      var buttonbuy = $(obj).find('.buy-container .hidder-element a').hasClass('addToCart');
      if (count != '0' && !buttonbuy) {
        var namebutton = $('.addToCart:first').text();
        $(obj).find('.buy-container .hidder-element .product-info').hide();
        var id = $(obj).find('.buy-container .hidder-element input').val();
        var buttonbuy = '<a href="http://'+mgBaseDir+'/catalog?inCartProductId='+id+'" class="addToCart product-buy" data-item-id="'+id+'">'+namebutton+'</a>';
        $(obj).find('.buy-container .hidder-element ').append(buttonbuy);
      } else if (count == '0' && buttonbuy == true){
        $(obj).find('.buy-container .hidder-element .addToCart').remove();
        var id = $(obj).find('.buy-container .hidder-element input').val();
        $(obj).find('.buy-container .hidder-element .product-info').show();
      }
    }
  });

  //Количество товаров
  $('body').on('click','.amount_change .up', function() {
    //bp-за вариантов товара делаем  бесконечное возможное количесво
    // 

    var obj = $(this).parents('.cart_form').find('.amount_input');
    var val = obj.data('max-count');
    if ((val == '\u221E' || val == '' || parseFloat(val) < 0)) {
      obj.data('max-count', 9999);
    }
    var i = obj.val();
    i++;
    if (i > obj.data('max-count')) {
      i = obj.data('max-count');
    }
    obj.val(i);
    return false;
  });

  $('body').on('click','.amount_change .down', function() { 
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
    if (parseFloat($(this).val()) > parseFloat($(this).data('max-count')) && parseFloat($(this).data('max-count')) > 0) {
      $(this).val($(this).data('max-count'));
    }
  });





  // Исключение ввода в поле выбора количесва недопустимых значений
  $('body').on('.deleteFromCart', function() {
    if (isNaN($(this).val()) || $(this).val() <= 0) {
      $(this).val('1');
    }
  });
  
  $('.product-wrapper .variants-table').each(function() {
    var form = $(this).parents('form');
    
    if(form.hasClass('actionView')){
      return;
    }
    
    if ($(this).find('td input:checked').data('count') != 0 && $(form).find('.buy-container a.addToCart').length==0) {
      var namebutton = $('.addToCart:first').text();
      $(form).find('.buy-container .hidder-element .product-info').hide();
      var id = $(form).find('.buy-container .hidder-element input').val();
      var buttonbuy = '<a href="http://'+mgBaseDir+'/catalog?inCartProductId='+id+'" class="addToCart product-buy" data-item-id="'+id+'">'+namebutton+'</a>';
      $(form).find('.buy-container .hidder-element ').append(buttonbuy);
    }
  });
});

function transferEffect(productId, buttonClick, wrapperClass) {

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

  //если кнопка на которую нажали находится внутри нужного контейнера 
  if (buttonClick.parents(wrapperClass).find('img[data-transfer=true][data-product-id=' + productId + ']').length) {

    // даем способность летать для картинок из слайдера новинок и прочих.
    var tempObj = buttonClick.parents(wrapperClass).find('img[data-transfer=true][data-product-id=' + productId + ']');
    tempObj.effect("transfer", $transfer, 600);
    $('.transfer_class').html(tempObj.clone().css($css));

  } else {
    //Если кнопка находится не в контейнере, проверяем находится ли она на странице карточки товара  
    if ($('.product-details-image').length) {
      // даем способность летать для картинок из галереи в карточке товара.
      $('.product-details-image').each(function() {
        if ($(this).css('display') != 'none') {
          $(this).find('img').effect("transfer", $transfer, 600);
          $('.transfer_class').html($(this).find('img').clone().css($css));
        }
      });

    } else {
      // даем способность летать для всех картинок.
      var tempObj = $('img[data-transfer=true][data-product-id=' + productId + ']');
      tempObj.effect("transfer", $transfer, 600);
    }
  }

  if (tempObj) {
    $('.transfer_class').html(tempObj.clone().css($css));
  }

}

function getInternetExplorerVersion(){
	var rv = -1;
	if (navigator.appName == 'Microsoft Internet Explorer')	{
		var ua = navigator.userAgent;
		var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null)
			rv = parseFloat( RegExp.$1 );
	}
	else if (navigator.appName == 'Netscape')	{
		var ua = navigator.userAgent;
		var re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null)
			rv = parseFloat( RegExp.$1 );
	}
	return rv;
}