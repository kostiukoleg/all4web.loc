<?php mgAddMeta('<link href="'.SCRIPT.'standard/css/layout.related.css" rel="stylesheet" type="text/css" />'); ?>
<?php var_dump($data);?>
<div class="mg-recent-products">
  <h2><span><?php echo $data['title'] ?></span></h2>
  <div class="m-p-products-slider">
    <div class="m-p-products-slider-start">
      <?php foreach($data['products'] as $item): ?>

        <div class="product-wrapper">
          <div class="product-stickers">
            <?php
            echo $item['recommend']?'<span class="sticker-recommend">Хит!</span>':'';
            echo $item['new']?'<span class="sticker-new">Новинка</span>':'';
            ?>
          </div>
          <div class="product-image">
            <a href="<?php echo SITE.'/' ?><?php echo isset($item["category_url"])?$item["category_url"]:'catalog/' ?><?php echo htmlspecialchars($item["product_url"]) ?>">
              <?php echo mgImageProduct($item); ?>
            </a>
          </div>
          <div class="product-code">Артикул: <?php echo $item["code"] ?></div>
          <div class="product-name">
            <a href="<?php echo SITE.'/' ?><?php echo isset($item["category_url"])?$item["category_url"]:'catalog/' ?><?php echo htmlspecialchars($item["product_url"]) ?>"><?php echo $item["title"] ?></a>
          </div>
          <div class="product-footer">
            <span class="product-price">
              <?php echo priceFormat($item["price"]) ?> <?php echo $data['currency']; ?>
            </span>
            <div class="product-buttons">
              <a class="default-btn buy-product" href="<?php echo SITE ?>/catalog?inCartProductId=<?php echo $item['id']; ?>" data-item-id="<?php echo $item['id']; ?>">Добавить</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>