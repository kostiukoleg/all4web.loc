<?php
/**
 *  Файл представления Index - выводит сгенерированную движком информацию на главной странице магазина.
 *  В этом файле доступны следующие данные:
 *   <code>     
 *    $data['recommendProducts'] => Массив рекомендуемых товаров 
 *    $data['newProducts'] => Массив товаров новинок 
 *    $data['saleProducts'] => Массив товаров распродажи
 *    $data['titeCategory'] => Название категории
 *    $data['cat_desc'] => Описание категории
 *    $data['meta_title'] => Значение meta тега для страницы 
 *    $data['meta_keywords'] => Значение meta_keywords тега для страницы
 *    $data['meta_desc'] => Значение meta_desc тега для страницы 
 *    $data['currency'] => Текущая валюта магазина
 *    $data['actionButton'] => тип кнопки в миникарточке товара
 *   </code>
 *   
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php viewData($data['saleProducts']); ?>  
 *   </code>
 * 
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php echo $data['saleProducts']; ?>  
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
<div class="banner">
	<h3>ДВЕРИ ОТ МИРОВЫХ<br>ПРОИЗВОДИТЕЛЕЙ</h3>
	<a href="#"><img src="<?php echo PATH_SITE_TEMPLATE; ?>/images/banner.png"></a>
</div>
<?php if(!empty($data['newProducts'])): ?>

  <div class="m-p-products latest">
    <h2><a href="<?php echo SITE; ?>/group?type=latest">Новинки продукции</a><!--<a class="catalog" href="<?php echo SITE; ?>/catalog">Перейти в каталог</a>--></h2>
    <div class="m-p-products-slider">
      <div class="<?php echo count($data['newProducts'])>3?"m-p-products-slider-start":"" ?>">
            <?php foreach($data['newProducts'] as $item): ?>
          <div class="product-wrapper">
		<div class="product-stickers">
		<?php if($item['new']): ?>
				<span class="sticker-new">Новинка</span>            
		<?php endif; ?>
		<?php if($item['recommend']): ?>
				<span class="sticker-recommend">Хит!</span>            
		<?php endif; ?>
		</div>
            <div class="product-image">
              <a href="<?php echo SITE.'/' ?><?php echo isset($item["category_url"])?$item["category_url"]:'catalog/' ?><?php echo htmlspecialchars($item["product_url"]) ?>">
                 <?php echo mgImageProduct($item); ?>
              </a>
            </div>
            <div class="product-code">Артикул: <?php echo $item["code"] ?></div>
            <div class="product-name">
              <a href="<?php echo SITE.'/' ?><?php echo isset($item["category_url"])?$item["category_url"]:'catalog/' ?><?php echo htmlspecialchars($item["product_url"]) ?>"><h3><?php echo $item["title"] ?></h3></a>
            </div>
            <div class="product-footer">
              <p class="product-price"><?php echo priceFormat($item["price"]) ?> <span><?php echo $data['currency']; ?></span></p>
                <!--Кнопка, кототорая меняет свое значение с "В корзину" на "Подробнее"-->
                 <?php echo $item[$data['actionButton']] ?>
                <?php //echo $item['actionCompare'] ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="clear"></div>
  </div>
<?php endif; ?> 

<?php if(!empty($data['recommendProducts'])): ?>
  <div class="m-p-products recommend">
    <h2> <a href="<?php echo SITE; ?>/group?type=recommend">Хит продаж</a></h2>
    <div class="m-p-products-slider">
      <div class="<?php echo count($data['recommendProducts'])>3?"m-p-products-slider-start":"" ?>">
          <?php foreach($data['recommendProducts'] as $item): ?>
          <div class="product-wrapper">
		<div class="product-stickers">
		<?php if($item['new']): ?>
				<span class="sticker-new">Новинка</span>            
		<?php endif; ?>
		<?php if($item['recommend']): ?>
				<span class="sticker-recommend">Хит!</span>            
		<?php endif; ?>
		</div>
            <div class="product-image">
              <a href="<?php echo SITE.'/' ?><?php echo isset($item["category_url"])?$item["category_url"]:'catalog/' ?><?php echo htmlspecialchars($item["product_url"]) ?>">
                 <?php echo mgImageProduct($item); ?>
              </a>
            </div>
            <div class="product-code">Артикул: <?php echo $item["code"] ?></div>
            <div class="product-name">
              <a href="<?php echo SITE.'/' ?><?php echo isset($item["category_url"])?$item["category_url"]:'catalog/' ?><?php echo htmlspecialchars($item["product_url"]) ?>"><h3><?php echo $item["title"] ?></h3></a>
            </div>
            <div class="product-footer">
              <p class="product-price"><?php echo priceFormat($item["price"]) ?> <span><?php echo $data['currency']; ?></span></p>
                <!--Кнопка, кототорая меняет свое значение с "В корзину" на "Подробнее"-->
                <?php echo $item[$data['actionButton']] ?>
                <?php //echo $item['actionCompare'] ?>
            </div>
          </div>
        <?php endforeach; ?> 
      </div>
    </div>
    <div class="clear"></div>
  </div>
<?php endif; ?> 

<?php if(!empty($data['saleProducts'])): ?>
  <div class="m-p-products sale">
    <h2><a href="<?php echo SITE; ?>">Распродажа</a></h2>
    <div class="m-p-products-slider">
    <div class="<?php echo count($data['saleProducts'])>3?"m-p-products-slider-start":"" ?>">
        <?php foreach($data['saleProducts'] as $item): ?>
        <div class="product-wrapper">
		<div class="product-stickers">
		<?php if($item['new']): ?>
				<span class="sticker-new">Новинка</span>            
		<?php endif; ?>
		<?php if($item['recommend']): ?>
				<span class="sticker-recommend">Хит!</span>            
		<?php endif; ?>
		</div>
          <div class="product-image">
            <a href="<?php echo SITE.'/' ?><?php echo isset($item["category_url"])?$item["category_url"]:'catalog/' ?><?php echo htmlspecialchars($item["product_url"]) ?>">
               <?php echo mgImageProduct($item); ?>
            </a>
          </div>
          <div class="product-code">Артикул: <?php echo $item["code"] ?></div>
          <div class="product-name">
            <a href="<?php echo SITE.'/' ?><?php echo isset($item["category_url"])?$item["category_url"]:'catalog/' ?><?php echo htmlspecialchars($item["product_url"]) ?>"><h3><?php echo $item["title"] ?></h3></a>
          </div>
          <div class="product-footer">
            <p class="product-price">
              <span class="product-old-price"><?php echo $item["old_price"] ?> <?php echo $data['currency']; ?></span>
              <?php echo priceFormat($item["price"]) ?> <span><?php echo $data['currency']; ?></span>
            </p>
              <!--Кнопка, кототорая меняет свое значение с "В корзину" на "Подробнее"-->
              <?php echo $item[$data['actionButton']] ?>
              <?php //echo $item['actionCompare'] ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="clear"></div>
  </div>
<?php endif; ?> 
	<?php if (class_exists('trigger')): ?>
		[trigger-guarantee id="1"]
	<?php endif; ?>
<div class="cat-desc">
	<?php echo $data['cat_desc'] ?>
</div>