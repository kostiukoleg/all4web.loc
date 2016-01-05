<div class="mg-contacts-block desktop" itemscope itemtype="http://schema.org/Organization">
  <div class="address" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
      <span>Адрес:</span>
      <h2 itemprop="streetAddress"><?php echo MG::getSetting('shopAddress') ?></h2>
  </div>
  <div class="phone">
      <span>Телефон:</span>
      <h2 itemprop="telephone"><?php echo MG::getSetting('shopPhone') ?></h2>     
      <?php if (class_exists('BackRing')): ?>
        [back-ring]
      <?php else: ?>
        <div style="height:17px;"> </div>
      <?php endif; ?>
  </div>
</div>
