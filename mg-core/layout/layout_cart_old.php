<?php mgAddMeta('<link type="text/css" href="'.SCRIPT.'standard/css/layout.cart.css" rel="stylesheet"/>'); ?>
<?php mgAddMeta('<script type="text/javascript" src="'.SCRIPT.'standard/js/layout.cart.js"></script>'); ?>

<?php if (MG::getSetting('popupCart')=='true') { ?>
<?php mgAddMeta('<link type="text/css" href="'.SCRIPT.'standard/css/layout.fake.cart.css" rel="stylesheet"/>'); ?>
<div class="mg-fake-cart" style="display: none;">
    <a class="mg-close-fake-cart mg-close-popup" href="javascript:void(0);"></a>
    <div class="popup-header">
        <h2>Корзина товаров</h2>
    </div>
    <div class="popup-body">
        <table class="small-cart-table">

            <?php if(!empty($data['cartData']['dataCart'])){?>

                <?php foreach($data['cartData']['dataCart'] as $item):?>
                    <tr>
                        <td class="small-cart-img">
                            <a href="<?php echo SITE."/".(isset($item['category_url'])?$item['category_url']:'catalog/').$item['product_url'] ?>">
                                <img src="<?php echo SITE."/uploads/thumbs/30_".($item['image_url']?$item['image_url']:'no-img.jpg') ?>" alt="<?php echo $item['title'] ?>" />
                            </a>
                        </td>
                        <td class="small-cart-name">
                            <ul class="small-cart-list">
                                <li>
                                    <a href="<?php echo SITE."/".(isset($item['category_url'])?$item['category_url']:'catalog/').$item['product_url'] ?>"><?php echo $item['title'] ?></a>
                                    <span class="property"><?php echo $item['property_html'] ?> </span>
                                </li>
                                <li class="qty">
                                    x<?php echo $item['countInCart'] ?>
                                    <span><?php echo $item['priceInCart'] ?></span>
                                </li>
                            </ul>
                        </td>
                        <td class="small-cart-remove">
                            <a href="#" class="deleteItemFromCart" title="Удалить" data-delete-item-id="<?php echo $item['id'] ?>"  data-property="<?php echo $item['property'] ?>"  data-variant="<?php echo $item['variantId'] ?>">&#215;</a>
                        </td>
                    </tr>
                <?php endforeach;?>

            <?php } else { ?>

            <?php }?>
        </table>
    </div>
    <ul class="total sum-list">
        <li class="total-sum">Общая сумма:
            <span><?php echo $data['cartData']['cart_price_wc'] ?></span>
        </li>
    </ul>
    <div class="popup-footer">
        <ul class="total">
            <li class="checkout-buttons">
                <a href="javascript:void();" class="mg-close-popup">Продолжить покупки</a>
                <a href="<?php echo SITE ?>/order" class="default-btn">Оформить</a>
            </li>
        </ul>
    </div>
</div>
<?php }; ?>


<div class="mg-desktop-cart">
    <div class="cart">
        <div class="cart-inner">
            <ul class="cart-list">
                <li>
                    <h3 class="cart-title">Корзина товаров:</h3>
                </li>
                <li class="cart-qty">
                    <span class="countsht"><?php echo $data['cartCount']?$data['cartCount']:0 ?></span> шт. -
                    <span class="pricesht"><?php echo $data['cartPrice']?$data['cartPrice']:0 ?></span>  <?php echo $data['currency']; ?>
                </li>
            </ul>
            <a href="<?php echo SITE ?>/cart" class="small-cart-icon"></a>
        </div>
        <div class="small-cart">
            <h2>Товары в корзине</h2>
            <table class="small-cart-table">

                <?php if(!empty($data['cartData']['dataCart'])){?>

                    <?php foreach($data['cartData']['dataCart'] as $item):?>
                        <tr>
                            <td class="small-cart-img">
                                <a href="<?php echo SITE."/".(isset($item['category_url'])?$item['category_url']:'catalog/').$item['product_url'] ?>">
                                    <img src="<?php echo SITE."/uploads/thumbs/30_".($item['image_url']?$item['image_url']:'no-img.jpg') ?>" alt="<?php echo $item['title'] ?>" />
                                </a>
                            </td>
                            <td class="small-cart-name">
                                <ul class="small-cart-list">
                                    <li>
                                        <a href="<?php echo SITE."/".(isset($item['category_url'])?$item['category_url']:'catalog/').$item['product_url'] ?>"><?php echo $item['title'] ?></a>
                                        <span class="property"><?php echo $item['property_html'] ?> </span>
                                    </li>
                                    <li class="qty">
                                        x<?php echo $item['countInCart'] ?>
                                        <span><?php echo $item['priceInCart'] ?></span>
                                    </li>
                                </ul>
                            </td>
                            <td class="small-cart-remove">
                                <a href="#" class="deleteItemFromCart" title="Удалить" data-delete-item-id="<?php echo $item['id'] ?>"  data-property="<?php echo $item['property'] ?>"  data-variant="<?php echo $item['variantId'] ?>">&#215;</a>
                            </td>
                        </tr>
                    <?php endforeach;?>

                <?php } else { ?>

                <?php }?>
            </table>
            <ul class="total">
                <li class="total-sum">Общая сумма:
                    <span><?php echo $data['cartData']['cart_price_wc'] ?></span>
                </li>
                <li class="checkout-buttons">
                    <a href="<?php echo SITE ?>/cart">Корзина</a>&nbsp;&nbsp;|
                    <a href="<?php echo SITE ?>/order" class="default-btn">Оформить</a>
                </li>
            </ul>
        </div>
    </div>
</div>	
	