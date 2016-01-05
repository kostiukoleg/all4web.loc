<?php
/**
 *  Файл представления Cart - выводит сгенерированную движком информацию на странице сайта с корзиной товаров.
 *  В этом  файле доступны следующие данные:
 *   <code>     
 *    $data['isEmpty'] => 'Флаг наполненности корзины'
 *    $data['productPositions'] => 'Набор продуктов в корзине'
 *    $data['totalSumm'] => 'Общая стоимость товаров в корзине'
 *    $data['meta_title'] => 'Значение meta тега для страницы '
 *    $data['meta_keywords'] => 'Значение meta_keywords тега для страницы '
 *    $data['meta_desc'] => 'Значение meta_desc тега для страницы '
 *    $data['currency'] => 'Текущая валюта магазина'
 *   </code>
 *   
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php viewData($data['productPositions']); ?>  
 *   </code>
 * 
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php echo $data['productPositions']; ?>  
 *   </code>
 * 
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложую программную логику логику.
 *   @author Авдеев Марк <mark-avdeev@mail.ru>
 *   @package moguta.cms
 *   @subpackage Views
 */
// Установка значений в метатеги title, keywords, description.
mgSEO($data);
?>
<?php mgTitle('Корзина'); ?>

<h1 class="new-products-title"><span>Корзина</span> товаров</h1>

<div class="product-cart" style="display:<?php echo!$data['isEmpty']?'none':'block'; ?>">
  <div class="cart-wrapper">
    <form method="post" action="<?php echo SITE ?>/cart">
      <table class="cart-table">
        <?php $i = 1;
        foreach($data['productPositions'] as $product): ?>
          <tr>
            <td class="img-cell">
              <a href="<?php echo $product["link"] ?>" target="_blank" class="cart-img">
                <img src="<?php echo mgImageProductPath($product["image_url"], $product['id'], 'small')?>" alt="">
              </a>
            </td>
            <td>
              <a href="<?php echo SITE.'/' ?><?php echo isset($product["category_url"])?$product["category_url"]:'catalog/' ?><?php echo $product["product_url"] ?>" target="_blank">
                <?php echo $product['title'] ?>
              </a>
              <br/><?php echo $product['property_html'] ?>
            </td>
            <td class="code-cell">
              Артикул:
              <span class="code"><?php echo $product['code'] ?></span>
            </td>
            <td class="count-cell">
              <input type="text" class="amount_input zeroToo"  name="item_<?php echo $product['id'] ?>[]" value = "<?php echo $product['countInCart'] ?>"/>
              <input type="hidden"  name="property_<?php echo $product['id'] ?>[]" value = "<?php echo $product['property'] ?>"/>
              <button type="submit" name="refresh" class="refresh" title="Пересчитать" value="Пересчитать">Пересчитать</button>
            </td>
            <td class="price-cell">
              <?php echo MG::numberFormat($product['countInCart']*$product['price']) ?>  <?php echo $data['currency']; ?>
              <a class="deleteItemFromCart delete-btn" href="<?php echo SITE ?>/cart" data-delete-item-id="<?php echo $product['id'] ?>" data-property="<?php echo $product['property'] ?>" data-variant="<?php echo $product['variantId'] ?>" title="Удалить товар"></a>
            </td>
          </tr>
        <?php endforeach; ?>
        <!--                <tr>-->
        <!--                    <td colspan="4" style="text-align:right;"></td>-->
        <!--                    <td class="total-sum-cell">-->
        <!---->
        <!--                    </td>-->
        <!--                </tr>-->
      </table>
    </form>
    <div class="total-sum">
      <span>Стоимость всех товаров:</span>
      <strong> <?php echo priceFormat($data['totalSumm']) ?> <?php echo $data['currency']; ?></strong>
    </div>
  </div>


  <?php if(class_exists('PromoCode')): ?>
    [promo-code] 
  <?php endif; ?>


  <form action="<?php echo SITE ?>/order" method="post" class="checkout-form">
    <button type="submit" class="checkout-btn" name="order" value="Оформить заказ">Оформить заказ</button>
  </form>
  <div class="clear">&nbsp;</div>


  <?php echo $data['related'] ?> 
</div>

<div class="empty-cart-block alert-info" style="display:<?php echo!$data['isEmpty']?'block':'none'; ?>">
  Ваша корзина пуста
</div>