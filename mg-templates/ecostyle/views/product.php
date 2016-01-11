<?php
/**
 *  Файл представления Product - выводит сгенерированную движком информацию на странице личного кабинета.
 *  В этом файле доступны следующие данные:
 *   <code>
 *   $data['category_url'] => URL категории в которой находится продукт
 *   $data['product_url'] => Полный URL продукта
 *   $data['id'] => id продукта
 *   $data['sort'] => порядок сортировки в каталоге
 *   $data['cat_id'] => id категории
 *   $data['title'] => Наименование товара
 *   $data['description'] => Описание товара
 *   $data['price'] => Стоимость
 *   $data['url'] => URL продукта
 *   $data['image_url'] => Главная картинка товара
 *   $data['code'] => Артикул товара
 *   $data['count'] => Количество товара на складе
 *   $data['activity'] => Флаг активности товара
 *   $data['old_price'] => Старая цена товара
 *   $data['recommend'] => Флаг рекомендуемого товара
 *   $data['new'] => Флаг новинок
 *   $data['thisUserFields'] => Пользовательские характеристики товара     
 *   $data['images_product'] => Все изображения товара 
 *   $data['currency'] => Валюта магазина.
 *   $data['propertyForm'] => Форма для карточки товара
 * 	 $data['liteFormData'] => Упрощенная форма для карточки товара
 *   $data['meta_title'] => Значение meta тега для страницы,
 *   $data['meta_keywords'] => Значение meta_keywords тега для страницы,
 *   $data['meta_desc'] => Значение meta_desc тега для страницы
 *   </code>
 *   
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php viewData($data['thisUserFields']); ?>  
 *   </code>
 * 
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php echo $data['thisUserFields']; ?>  
 *   </code>
 * 
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложую программную логику логику.
 *   @author Авдеев Марк <mark-avdeev@mail.ru>
 *   @package moguta.cms
 *   @subpackage Views
 */
// Установка значений в метатеги title, keywords, description.
mgSEO($data);
mgAddMeta('<link href="'.SCRIPT.'standard/css/layout.related.css" rel="stylesheet" type="text/css" />');
mgAddMeta('<script type="text/javascript" src="'.SCRIPT.'standard/js/layout.related.js"></script>');
?>
<div class="product-details-block" itemscope itemtype="http://schema.org/Product">
  
  <?php if(class_exists('BreadCrumbs')): ?>
    [brcr]
  <?php endif; ?>
    
  <h1 class="product-title" itemprop="name"><?php echo $data['title'] ?></h1>

  <?php mgGalleryProduct($data); ?>
  <div class="product-status">
    <div class="buy-block">
      <div class="buy-block-inner">
        <div class="product-code">
          Артикул: <span class="label-article code" itemprop="productID"><?php echo $data['code'] ?></span>
        </div>
        <div class="product-price" itemprop="offers">
          <ul class="product-status-list">
            <li>
              <div class="old" <?php echo (!$data['old_price'])?'style="display:none"':'style="display:block"' ?>>
                <s><span class="old-price"><?php echo MG::numberFormat($data['old_price'], '1 234,56')." ".$data['currency']; ?></span></s>
              </div>
            </li>
            <li>
              <div class="normal-price" itemprop="price">
                <p class="price"><?php echo $data['price'] ?> <span><?php echo $data['currency']; ?></span></p>
              </div>
            </li>
          </ul>
        </div>

        <ul class="product-status-list">
          <!--если не установлен параметр - старая цена, то не выводим его-->
          <li>Остаток: <span class="label-black count"><?php echo $data['count'] ?></span> шт. <?php echo $data['remInfo'] ?></li>
          <li <?php echo (!$data['weight'])?'style="display:none"':'style="display:block"' ?>>Вес: <span class="label-black weight"><?php echo $data['weight'] ?></span> кг. </li>
        </ul>
        <!--Кнопка, кототорая меняет свое значение с "В корзину" на "Подробнее"-->
        <?php echo $data['propertyForm'] ?>
        <div class="product-price mobile">
          <ul class="product-status-list">
            <li>
              <div class="old-price" <?php echo (!$data['old_price'])?'style="display:none"':'style="display:block"' ?>>
                <span class="old"><?php echo MG::numberFormat($data['old_price'], '1 234,56')." ".$data['currency']; ?></span>
              </div>
            </li>
            <li>
              <div class="normal-price">
                <span class="price"><?php echo $data['price'] ?> <?php echo $data['currency']; ?></span>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div><!-- End product-status-->

  <?php if(class_exists('Pluso')): ?>
    [pluso]
  <?php endif; ?>

  <div class="clear"></div>

  <div class="product-details-wrapper">
    <ul class="product-tabs">
      <li><a href="#tab1">Описание</a></li>
      <li><a href="#tab2">Комментарии</a></li>
      <?php foreach ($data['thisUserFields'] as $key => $value) {
        if ($value['type']=='textarea'&&$value['value']) {?>
      <li><a href="#tab<?php echo $key?>"><?php echo $value['name']?></a></li>        
      <?php   }
        
      }?>
    </ul>
    <div class="product-tabs-container">
      <div id="tab1" itemprop="description"><?php echo $data['description'] ?></div>
      <div id="tab2" itemscope itemtype="http://schema.org/Review">
        
        <?php if(class_exists('CommentsToMoguta')): ?>
          [comments]
        <?php endif; ?>    
          
      </div>
      <?php foreach ($data['thisUserFields'] as $key => $value) {
        if ($value['type']=='textarea') {?>
      <div id="tab<?php echo $key?>" itemscope><?php echo preg_replace('/\<br(\s*)?\/?\>/i', "\n", $value['value'])?></div>        
      <?php  }
        
      }?>
    </div>
  </div>

  <?php
  /* Следующая строка для вывода свойств в таблицу характеристик */
  /* $data['stringsProperties'] */
  ?>  
  <?php echo $data['related'] ?> 

</div><!-- End product-details-block-->



