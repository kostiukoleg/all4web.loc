<?php if(!empty($data['blockVariants'])){?>
<div class="clear"></div><div class="block-variants">
<table class="variants-table">
  <?php foreach ($data['blockVariants'] as $variant) :?>

      <tr>
        <td>
            <input type="radio" id="variant-<?php echo $variant['id']; ?>" data-count="<?php echo $variant['count']; ?>" name="variant" value = "<?php echo $variant['id']; ?>" <?php echo !$i++ ? 'checked=checked' : ''?>>
        </td>
          <td>
              <?php
              $src = mgImageProductPath($variant['image'], $variant['product_id'], 'small');
              echo !empty($variant['image'])?'<img src="'.$src.'" width="30" height="20">':'' ?>
          </td>
          <td>
              <label for="variant-<?php echo $variant['id']; ?>"><?php echo $variant['title_variant'] ?></label>
          </td>
          <td style="padding-left:5px;" class="nowrap">
              <span>
                  <?php echo $variant['price'] ?> <?php echo MG::getSetting('currency')?>
              </span>
          </td>
      </tr>

      <?php if ($variant['activity'] === "0" || $variant['count'] == 0): ?> 
        <tr>
            <td colspan="4">
                <span class='reminfo'>Нет в наличии</span>
            </td>
        </tr>
      <?php endif; ?>

   <?php endforeach; ?>
    </table>
</div>
<?php }?>