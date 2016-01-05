<div class="section-statistics">
    <div class="widget-table-wrapper">
        <div class="widget-table-title">
            <h4 class="statistics-table-icon"><?php echo $lang['STAT_LOCALE_1']?></h4>      
        </div>     

        <div class="widget-table-body">
            <div class="widget-table-action">   
                <ul class="data-statistics">
                 <li class="period-stat"><?php echo $lang['STAT_LOCALE_2']?> <input type="text" name="from-date-stat" value="<?php echo $data['from_date_stat'] ?>"/> <?php echo $lang['STAT_LOCALE_3']?> <input type="text" name="to-date-stat" value="<?php echo $data['to_date_stat'] ?>"/>
                     <a href="javascript:void(0);" class="custom-btn apply-period-stat"><span><?php echo $lang['STAT_LOCALE_4']?></span></a></li>
                </ul>
                <ul class="data-statistics indicators">    
                 <li class="all-orders"><?php echo $lang['STAT_LOCALE_5']?>: <span><?php echo $data['noclosed'] ?><span> <?php echo $lang['UNIT']?></li>  
                 <li class="all-orders-noclosed">  <?php echo $lang['STAT_LOCALE_6']?>: <span><?php echo $data['orders'] ?><span> <?php echo $lang['UNIT']?></li>      
                 <li class="all-summ"><?php echo $lang['STAT_LOCALE_7']?>: <span><?php echo $data['summ'] ?><span> <?php echo MG::getSetting('currency'); ?></li>       
                 <li class="all-users"><?php echo $lang['STAT_LOCALE_8']?>: <span><?php echo $data['users'] ?><span> <?php echo $lang['UNIT']?></li>
                 <li class="all-products"><?php echo $lang['STAT_LOCALE_9']?>: <span><?php echo $data['products'] ?><span> <?php echo $lang['UNIT']?></li>                
               </ul>               
               <div class="clear"></div>
            </div>
            <div class="main-settings-container"> 
                  <div id="container" style="height: 500px; min-width: 500px"></div>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</div>