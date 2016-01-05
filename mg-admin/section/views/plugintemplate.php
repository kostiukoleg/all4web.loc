<div class="widget-table-wrapper">
<div class="widget-table-title">

<h4 class="settings-table-icon"><?php  $lang=MG::get('lang'); echo $lang['SETTINGS_PLUGIN'];?> "<?php echo URL::getQueryParametr('pluginTitle')?>"</h4>
</div>
<div class="widget-table-body">

                <a href="javascript:void(0);" onclick="$('a[id=plugins]').click();" class="go-back-plugins"><span>&larr; <?php echo $lang['BACK_PLUGIN'];?></span></a>

        <?php  MG::createHook(URL::getQueryParametr('mguniqueurl')); ?>
</div>

<div class="clear"></div>
</div>