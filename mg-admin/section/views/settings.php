<div class="section-settings">
<div class="widget-table-wrapper">
<div class="widget-table-title">
    <h4 class="settings-table-icon"><?php echo $lang['TITLE_SETTINGS'];?></h4>
</div>

<!-- Тут начинается Верстка модального окна -->
<div class="b-modal hidden-form" id="add-list-cat-wrapper">
    <div class="properties-table-wrapper">
        <div class="widget-table-title">
            <h4 class="pages-table-icon" id="modalTitle"><?php echo $lang['STNG_LIST_CAT'];?></h4>
            <div class="b-modal_close tool-tip-bottom" title="<?php echo $lang['T_TIP_CLOSE_MODAL'];?>"></div>
        </div>
        <div class="widget-table-body">
            <div class="add-product-form-wrapper">
                <div id="select-category-form-wrapper" class="user-fields-wrapper">
                    <select  class ="tool-tip-right category-select" title="<?php echo $lang['T_TIP_SELECTED_U_CAT'];?>" name="listCat" multiple>';
                    </select>
                </div>
                <div class="user-fields-desc-wrapper">
                    <span><?php echo $lang['STNG_LISC_SELECT_CAT'];?></span> : "<span class="propertyName"></span>"
                    <p class="clear-text"><?php echo $lang['STNG_LISC_TIP'];?></p>
                    <a href="javascript:void(0);" class="cancelSelect"><?php echo $lang['STNG_LISC_CANCEL_SELECT'];?></a>
                </div>
                <div class="clear"></div>
                <button class="save-button tool-tip-bottom" title="<?php echo $lang['T_TIP_SAVE_U_CAT'];?>">
                    <span><?php echo $lang['SAVE'];?></span>
                </button>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>
<!-- Тут заканчивается Верстка модального окна -->


<div class="widget-table-body">
<div id="settings-tabs">
<ul class="tabs-list">
    <li class="ui-state-active" >
        <a href="javascript:void(0);" class="tool-tip-top" id="tab-shop" title="<?php echo $lang['T_TIP_TAB_SHOP'];?>"><span><?php echo $lang['STNG_TAB_SHOP'];?></span></a>
    </li>
    <li>
        <a href="javascript:void(0);" class="tool-tip-top" id="tab-system" title="<?php echo $lang['T_TIP_TAB_SYSTEM'];?>"><span ><?php echo $lang['STNG_TAB_SYSTEM'];?></span></a>
    </li>
    <li>
        <a href="javascript:void(0);" class="tool-tip-top" id="tab-template" title="<?php echo $lang['T_TIP_TAB_TEMPLATE'];?>"><span ><?php echo $lang['STNG_TAB_TEMPLATE'];?></span></a>
    </li>
    <li>
        <a href="javascript:void(0);" class="tool-tip-top" id="interface" title="<?php echo $lang['T_TIP_TAB_INTERFACE'];?>"><span ><?php echo $lang['STNG_TAB_INTERFACE'];?></span></a>
    </li>
    <li>
        <a href="javascript:void(0);" class="tool-tip-top" id="tab-userField" title="<?php echo $lang['T_TIP_TAB_USERFIELDS'];?>"><span ><?php echo $lang['STNG_USER_FIELD'];?></span></a>
    </li>
    <li>
        <a href="javascript:void(0);" class="tool-tip-top" id="tab-currency" title="<?php echo $lang['T_TIP_CURRENCY_SHOP'];?>"><span ><?php echo $lang['STNG_CURRENCY_SHOP'];?></span></a>
    </li>
    <li>
        <a href="javascript:void(0);" class="tool-tip-top" id="tab-deliveryMethod" title="<?php echo $lang['T_TIP_TAB_DELIVERY'];?>"><span ><?php echo $lang['STNG_TAB_DELIVERY'];?></span></a>
    </li>
    <li>
        <a href="javascript:void(0);" class="tool-tip-top" id="tab-paymentMethod" title="<?php echo $lang['T_TIP_TAB_PAYMENT'];?>"><span ><?php echo $lang['STNG_TAB_PAYMENT'];?></span></a>
    </li>
</ul>
<div class="clear"></div>
<div class="tabs-content">
<!--Раздел настроек магазина-->
<div class="main-settings-container" id="tab-shop-settings">


    <h4><?php echo $lang['STNG_MAIN_SITE'];?></h4>

    <?php
    $propertyHtml = '';

    foreach($groups as $key=>$group){
        $propertyHtml .= "<div class='group-property'><h3>".$lang[$key]."</h3><ul class='group-property-list' style='display:none'>";

        if($key == 'STNG_GROUP_7'){
                  //$propertyHtml .=  '<li><div class="system-message">Режим кэширования находится в состоянии beta-тестирования.</div><a href = "javascript:void(0);" class="clear-cache custom-btn"><span>Очистить кэш</span></a></li>';
                  $propertyHtml .=  '<li><a href = "javascript:void(0);" class="clear-cache custom-btn"><span>Очистить кэш</span></a></li>';
                 
				          $propertyHtml .=  '<li><a href = "javascript:void(0);" class="memcache-conection custom-btn"><span>Проверить соединение для MEMCACHE</span></a></li>';
        }

        if($key == 'STNG_GROUP_5'){
          $propertyHtml .=  '<li><div class="system-message"> Для отправки через защищенное соединение, сервер нужно указывать с добавлением "ssl://" например так: "ssl://smtp.mail.ru"</div></li>';
        }
                
                
               
        foreach($group as $optionName){
            $input = '';
            $option = $data['setting-shop']['options'][$optionName];
            $alias = $option['name'];
            $numericProtection=""; if (in_array($alias,$data['numericFields'])){$numericProtection = "numericProtection";};


            if (in_array($option['option'],$data['checkFields'])){
                $checked = ('true' == $option['value'])?"checked='checked'":"";
                $checkUp = "";
                if($option['option']=="waterMark"){
                    if(in_array($option['option'], array("waterMark"))){
                        $checkUp = "check-up";
                    }
                }

                $input = '<input type="checkbox" class="option '.$checkUp.'" name="'.$option['option'].'" value="'.$option['value'].'" '.$checked.'/>';
                if($option['option'] == 'copyrightMoguta'){
                  $input = '<input disabled type="checkbox" class="option '.$checkUp.'" name="'.$option['option'].'" value="'.$option['value'].'" '.$checked.'/>';
               }
               if($option['option'] == 'waterMarkVariants' || $option['option'] == 'waterMark'){
                  $input = '<input type="checkbox" class="option " name="'.$option['option'].'" value="false" />  ';
               }
            }

            if (in_array($option['option'],$data['textFields'])){
                $input = '<textarea name="'.$option['option'].'" class="settings-input option">'.$option['value'].'</textarea>';
            }

            if($option['option'] == 'templateName'){
                    
                    $style='';
                    if($option['value']=="default"){
                      $style="display:block;";
                    }
                    
                   $input = '<div class="wrapp-templ"><div class="install-templ"><span>'.$lang['SETTING_BASE_1'].':</span><br/><span class="default-info" style="'.$style.'">Все изменения шаблона defaul будут отменены при обновлении версии системы,<br/>чтобы избежать этого, пожалуйста, используйте другоЙ шаблон!</span></div>
                      <select class="option last-items-dropdown" name="'.$option['option'].'" style="margin-top:5px; width:170px;" >';
                foreach($data['setting-shop']['templates'] as $template){
                         $input .=  '<option data-schemes=\''.json_encode($template['colorScheme']).'\' value="'.$template['foldername'].'" ';
                           if($template['foldername'] == $option['value']){  
                             $input .=  "selected";    
                             
                             // для выбранного строим перечень доступных схем
                             foreach($template['colorScheme']  as $scheme){
                               $active = '';
                               if($scheme==$template['colorSchemeActive']){
                                 $active = 'active';
                               }
                               $schemeHtml .= '<li class="color-scheme '.$active.'" data-scheme="'.$scheme.'" style="background:#'.$scheme.';"></li>';
                             }     
                             
                           } 
                         $input .=  '  > '. $template['foldername'].'
                         </option>';
                       }                    
                $input .= '</select>';

                      if(empty($schemeHtml)){
                        $style = 'style="display:none"';
                      }
                      
                      $input .= '<div class="template-schemes" '.$style.'><span>'.$lang['SETTING_BASE_14'].':</span><ul class="color-list">'.$schemeHtml.'</ul></div>';
                      
                      
                $input .= '<form id="newTemplateForm" method="post" noengine="true" enctype="multipart/form-data">
                          <div class="install-templ"><span>'.$lang['SETTING_BASE_2'].'</span></div>
                        <div class="type-file">
                            <a class="install-template custom-btn" href="javascript:void(0);"><span>'.$lang['SETTING_BASE_3'].'</span></a>
                            <input type="file" name="addTempl" id="addTempl" size="1">
                        </div>
                      </form></div>';
            }

            if($option['option'] == 'priceFormat'){
                $input = '
                      <select class="option last-items-dropdown" name="'.$option['option'].'" style="margin-top:5px; width:170px;" >';
                foreach(array(
                            '1234.56'=>'1234.56 - без форматирования',
                            '1 234,56'=>'1 234,56 - разделять тысячи пробелами, а копейки запятыми',
                            '1,234.56'=>'1,234.56 - разделять тысячи запятыми, а копейки точками',
                            '1234'=>'1234 - без копеек, без форматирования',
                            '1 234'=>'1 234 - без копеек, разделять тысячи пробелами',
                            '1,234'=> '1,234 - без копеек, разделять тысячи запятыми'
                        ) as $key =>$item){
                    $input .=  '<option value="'.$key.'" ';
                    if($key == $option['value']){
                        $input .=  "selected";
                    }
                    $input .=  '  > '. $item.'
                         </option>';
                }
                $input .= '</select>';

            }


            if($option['option'] == 'cacheMode'){
                $input = '
                      <select class="option last-items-dropdown" name="'.$option['option'].'" style="margin-top:5px; width:170px;" >';
                foreach(array('DB','MEMCACHE') as $item){
                    $input .=  '<option value="'.$item.'" ';
                    if($item == $option['value']){
                        $input .=  "selected";
                    }
                    $input .=  '  > '. $item.'
                         </option>';
                }
                $input .= '</select>';

            }

            
            
            if($option['option'] == 'cacheCssJs'){
              $cacheCssJs = MG::getSetting('cacheCssJs');
              $warning ='';
              if($cacheCssJs!="true"){
                $display = "display:none; ";                 
              } 
              if (!file_exists(PATH_TEMPLATE.'/cache/images')) {
                $warning = 'Для объединения файлов необходимо создать изображения.';
              }              
              $propertyHtml .=  '<li><a href = "javascript:void(0);" class="create-images-for-css-cache custom-btn" style="'.$display.'"><span>Создать images для CSS</span></a></li><span class="warning-create-images" style="'.$display.' color:#cc0000">'.$warning.'</span>';              
              $input = '<input type="checkbox" class="option minify-css-and-js" name="'.$option['option'].'" value="false"/>';
            }

            
            
            if($option['option'] == 'currencyShopIso'){
                $input = '
                      <select class="option last-items-dropdown" name="'.$option['option'].'" style="margin-top:5px; width:170px;" >';
                $currencyShopIso = MG::getSetting('currencyShopIso');
                foreach(MG::getSetting('currencyShort') as $iso => $short){
                    $input .=  '<option value="'.$iso.'" ';
                    if($currencyShopIso == $iso){
                        $input .=  "selected";
                    }
                    $input .=  '  > '. $short.'
                         </option>';
                }
                $input .= '</select>';


            }

            if($option['option']=="waterMark"){
                $input .= '
                     <div class="wrapp-watermark-img" >
                         <div class="watermark-img" >
                           <img style="max-width:200px;"  src="'.SITE.'/uploads/watermark/watermark.png">
                         </div>
                         <form class="watermarkform" method="post" noengine="true" enctype="multipart/form-data">
                           <a href="javascript:void(0);" class="add-watermark">
                           <span>'.$lang['SETTING_LOCALE_27'].'</span>
                             <input type="file" name="photoimg" class="add-img tool-tip-top" title="'.$lang['SETTING_LOCALE_27'].'">
                           </a>
                         </form>
                    </div>';
            }

            if($option['option']=="shopLogo"){
                if(empty($option['value'])){
                  $displaynone=" display:none";
                }
                $input .= '
                     <div class="wrapp-logo-img" >
                         <div class="logo-img" >
                           <img  style="max-width:200px; '.$displaynone.' "  src="'.SITE.$option['value'].' ">
                           <a class="remove-added-logo custom-btn" style="'.$displaynone.'" href="javascript:void(0);"><span></span></a>  
                         </div>
                       
                         <a href="javascript:void(0);" class="custom-btn add-logo browseImageLogo">
                           <span>'.$lang['SETTING_LOCALE_30'].'</span>                            
                         </a>
                         
                     <input type="hidden"  name="'.$option['option'].'" class="settings-input option" value="'.$option['value'].'">
                    </div>';
            }
            if($option['option']=="backgroundSite"){
              $displaynone ='';
                if(empty($option['value'])){
                  $displaynone=" display:none";
                }
                $input .= '
                     <div class="wrapp-background-img" >
                         <div class="background-img" >
                           <img  style="max-width:200px; '.$displaynone.' "  src="'.SITE.$option['value'].' ">
                             <a class="remove-added-background custom-btn" style="'.$displaynone.'" href="javascript:void(0);"><span></span></a>
                         </div>
                       
                         <a href="javascript:void(0);" class="custom-btn add-background browseBackgroundSite">
                           <span>'.$lang['SETTING_LOCALE_31'].'</span>                            
                         </a>
                         
                     <input type="hidden"  name="'.$option['option'].'" class="settings-input option" value="'.$option['value'].'">
                    </div>';
            }

            if(empty($input)){
                $type = "text";
                if($option['option']=="smtpPass"){
                    $type = "password";
                }

                $input = '<input type="'.$type.'"  name="'.$option['option'].'" class="settings-input option'.$numericProtection.'" value="'.str_replace('"','&quot;',$option['value']).'">';
            }

            $textUp = "";
            if(in_array($option['option'], array("waterMark","widgetCode","templateName"))){
                $textUp = "text-up";
                if($option['option']=="waterMark"){
                    $textUp = 'watermark-text';
                }
            }

            $propertyHtml .=  "<li><span class='property-name ".$textUp."'>".$lang[$alias]."</span><span class='property-fields'>".$input."<a href='javascript:void(0);' class='tool-tip-top desc-property' title='".$lang['DESC_'.$option['name']]."' >?</a></span></li>";
        }
        $propertyHtml .=  "</ul></div>";
    }
    ?>

    <table class="main-settings-list">
        <tr id="data">
            <td><?php echo $propertyHtml;?></td>
        </tr>
    </table>
    <button class="save-button save-settings"><span><?php echo $lang['SAVE'] ?></span></button>
    <div class="clear"></div>
</div>


<!--Раздел настроек системы-->
<div class="main-settings-container" id="tab-system-settings" style="display:none">
    <h4><?php echo $lang['STNG_SYSTEM']?></h4>

    
    <div class="tab-inner">
        <?php          
          $downtime = $data['setting-system']['options']['downtime']['value'];
          $checked = '';
          $value = 'value="false"';

          if($downtime=="true"){
              $checked = 'checked="checked"';
              $value = 'value="'.$downtime.'"';
        }?>
        <ul class="form-list">
            <li>
                <label>
                    <span class="key-text"><?php echo $lang['DOWNTIME_SITE']?>:</span>
                    <input class="option downtime-check" type="checkbox" <?php echo $value ?> <?php echo $checked ?> name="downtime">
                </label>
            </li>
        </ul>

        <?php if($newFirstVersiov):?>
            <div class="step-info link-success">Доступна более новая версия движка - <?php echo $newFirstVersiov?>  <span class='start-update'>[<a href="javascript:void(0);" onclick="$('#go').click();">Начать обновление</a>]</span></div>
        <?php endif; ?>

       

        <?php if($newFirstVersiov):?>

            <ul class="step-form">

                <li class="step-update-li-1" >
                    <span class="corner"></span>
                    <h2>Шаг 1</h2>
                    <strong>Загрузка обновлений</strong>
                    <img style="display: none" class="loading-update-step-1 loader" src="<?php echo SITE ?>/mg-admin/design/images/small-loader.gif" class="loader" width="16" height="16" alt=""/>
                </li>
                <li class="step-update-li-2 current">
                    <span class="corner"></span>
                    <h2>Шаг 2</h2>
                    <strong>Применение обновлений</strong>
                    <img style="display:none" class="loading-update-step-2 loader" src="<?php echo SITE ?>/mg-admin/design/images/small-loader.gif" class="loader" width="16" height="16" alt=""/>
                </li>
                <li class="step-update-li-3 current">
                    <span class="corner"></span>
                    <h2>Шаг 3</h2>
                    <strong>Система обновлена!</strong>
                </li>
            </ul>


           
            
            <div class="step-block">
                <div class="step1">                   
                    <div style="display:none" class="step-process-info link-result"></div>
                    <div class="step-1-info link-result">
                        <ul class="system-version-list">
                            <li>
                                <strong>Описание изменений: </strong>
                                <?php
                                if($newVersionMsg){
                                    echo $newVersionMsg;
                                }
                                ?>
                            </li>
                        </ul>
						<div style="display:none" class="step-eror-info link-fail" style="margin-bottom:5px;"></div>
                        <button rel="preDownload" class="update-now tool-tip-bottom <?php echo $updataOpacity ?>" title="<?php $lang['SETTING_BASE_6']?>" <?php echo $updataDisabled ?> >
                            <span id="go">Скачать <?php echo strip_tags( $newFirstVersiov)?></span>
                        </button>
                    </div>
                </div>
                <div class="step2" style="display:none">                   
                    <div style="display:none" class="step-process-info link-result"></div>
                    <div class="step-2-info link-result">
                        <ul class="system-version-list">
                             <li>
							     Вы подтверждаете, что резервная копия сайта и базы данных создана?<br/>
                                 Вы принимаете риск несовместимости установленных плагинов и шаблонов с новой версией?<br/>
								 <div style="display:none" class="step-eror-info link-fail" style="margin-bottom:5px;"></div>
                                 <button style="display:none" rel="preDownload" class="update-archive button">
                                     <span id="go"><?php echo $lang['APPLY_UPDATE']?></span>
                                 </button>
                             </li>
                        </ul>
                    </div>
                </div>
            </div>
            

        <?php else: ?>         
            <ul class="step-form">

                <li class="step-update-li-1 current completed" >
                    <span class="corner"></span>
                    <h2>Шаг 1</h2>
                    <strong>Загрузка обновлений</strong>
                </li>
                <li class="step-update-li-2 current completed">
                    <span class="corner"></span>
                    <h2>Шаг 2</h2>
                    <strong>Применение обновлений</strong>
                </li>
               <li class="step-update-li-3">                  
                    <h2>Шаг 3</h2>
                    <strong>Система обновлена до актуальной версии <?php echo VER?>!</strong>
                </li>
            </ul>
        <?php endif; ?>
          <div class="enter-key-block">              
            <ul class="form-list">
                <li>
                    <span class="key-text"><?php echo $lang['LICENSE_KEY'] ?>:</span>
                    <?php 
                    $displayKey = "display:block";
                    if($data['setting-system']['options']['licenceKey']['value']){ $displayKey = "display:none"; }?>
                    <input style="<?php echo $displayKey; ?>" type="text"  name="licenceKey" class="settings-input option licenceKey" value="<?php echo $data['setting-system']['options']['licenceKey']['value']?>">
                    <button style="<?php echo $displayKey; ?>" class="save-button save-settings save-settings-system"><span>Сохранить ключ</span></button>
                    <?php if($displayKey == "display:none"):?>
<!--                    <span class="key-text">--><?php //echo $data['setting-system']['options']['licenceKey']['value'] ?><!--</span>-->
                    <a href="javascript:void(0);" class ="edit-key edit-row" ><?php echo $data['setting-system']['options']['licenceKey']['value'] ?></a>
                    <?php endif;?>
                </li>
                <li>
                    <span class="error-key link-fail" style="display: <?php echo (($updataDisabled!="disabled")?'none':'block'); ?>"><?php echo $lang['SETTING_LOCALE_1']?></span>
                    <?php
                    $dateActivate = MG::getOption('dateActivateKey');
                    if($dateActivate!='0000-00-00 00:00:00'){
                        $now_date = strtotime($dateActivate);
                        $future_date = strtotime(date("Y-m-d"));
                        $dayActivate = (365-(floor(($future_date - $now_date) / 86400 )));
                        if($dayActivate<=0){$dayActivate=0; $extend=" [<a href='http://moguta.ru/extendcenter'>Продлить</a>]";}
                        $activeDate =   " ".$lang['SETTING_BASE_4']." <span class='key-days-number'>".$dayActivate." ".$lang['SETTING_BASE_5']."</span>".$extend;

                    } else{
                        $activeDate = " <span class='link-result'>".$lang['SETTING_LOCALE_2']."</span>";
                    }
                    ?>
                    <?php echo $activeDate ?>
                </li>
                <li>
                    <a href="javascript:void(0);" class="clearLastUpdate custom-btn">
                        <span><?php echo $lang['SETTING_LOCALE_7']?></span>
                    </a>
                    <?php if($newFirstVersiov):?>
                     
                        
                    <?php endif; ?>
                </li>
            </ul>
        </div> 

    </div>




    <table style="display:none" class="main-settings-list">

        <tr>
            <td>
                <dl>
                    <dt><?php echo $lang['STNG_CUR_VER']?><span><?php echo VER?></span></dt>
                    <dd id="updataMsg">
                        <?php if(!$errorUpdata):
                            if($newVersionMsg):
                                echo $newVersionMsg;?>
                                <span class="custom-text" style="color:red"><?php echo $lang['SETTING_LOCALE_3']?></span>
                                <br/><button rel="preDownload" class="update-now tool-tip-bottom <?php echo $updataOpacity ?>" title="<?php $lang['SETTING_BASE_6']?>" <?php echo $updataDisabled ?> >
                                <span id="go"><?php echo $lang['SETTING_LOCALE_5']?></span>
                            </button>
                            <?php else:?> <!--if($newVersionMsg)-->
                                <strong><span style="color:green;"><?php echo $lang['SETTING_LOCALE_6']?></span></strong>
                                (<a href="javascript:void(0);" class="clearLastUpdate"><?php echo $lang['SETTING_LOCALE_7']?></a> )
                            <?php endif?><!--if($newVersionMsg)-->
                        <?php  else:?>  <!--if(!$errorUpdata)-->
                            <span style="color:red">
                                <?php echo $errorUpdata; ?> <?php echo $lang['SETTING_LOCALE_8']?>
                              </span>
                        <?php endif?> <!--if(!$errorUpdata)-->
                    </dd>
                </dl>
            </td>
        </tr>
    </table>

    <div class="clear"></div>
</div>









<!--Раздел настроек пользовательсктх полей-->
<div class="main-settings-container" id="tab-userField-settings" style="display:none">
    <h4><?php echo $lang['STNG_USER_FIELD'];?></h4>
    <button class="save-button addProperty"><span><?php echo $lang['SETTING_LOCALE_9']?></span></button>
            
            <label class="property-options">
                Показать характеристики привязанные к категории:
                <select name="cat_id">
                  <?php foreach ($listCategories as $key => $value):?>
                    <option value="<?php echo $key?>"><?php echo $value?></option>
                  <?php endforeach;?>
                </select>
            </label>
    <div class="clear"></div>
    <div style="margin-left: 15px; margin-bottom: 15px; color: red; ">В бесплатной версии доступен только один тип способ редактирования: "Строка". </div>       
   
    <table class="userField-settings-list main-settings-list"></table>

    <div class="clear"></div>
    <select name="operation" class="property-operation" >
        <option value="activity_0"><?php echo $lang['SETTING_BASE_7']?></option>
        <option value="activity_1"><?php echo $lang['SETTING_BASE_8']?></option>
              <option value="filter_1">Использовать в фильтрах</option> 
              <option value="filter_0">Не использовать в фильтрах</option> 
        <option value="delete"><?php echo $lang['SETTING_BASE_9']?></option>
    </select>
    <a href="javascript:void(0);" class="run-operation custom-btn"><span><?php echo $lang['SETTING_BASE_10']?></span></a>
   
    
</div>
<!--Содержимое, показываемое при удачном загрузке архива с обновлением-->
<div id="hiddenMsg" style="display:none">
    <?php echo $lang['SETTING_LOCALE_10']?> <b><span id="lVer"></span></b> <?php echo $lang['SETTING_LOCALE_11']?><br>
    <a href="javascript:void(0);" rel="postDownload" class="button"><span><?php echo $lang['SETTING_LOCALE_12']?></span></a>
</div>

<!--Раздел настроек шаблона -->
<div class="main-settings-container" id="tab-template-settings" style="display:none;">
    <h4><?php echo $lang['STNG_TEMPLATE'];?></h4>
    <div class="template-edit-links-wrapper">
        <ul class="template-tabs">
            <li><a href="javascript:void(0);" class="active open-email-views">Шаблоны страниц</a></li>
            <li><a href="javascript:void(0);" class="open-block-layout" >Шаблон блоков</a></li>
            <li><a href="javascript:void(0);" class="open-email-layout" >Шаблоны писем</a></li>
            <li><a href="javascript:void(0);" class="open-print-layout"  >Шаблон печати</a></li>
            <li><a hhref="javascript:void(0);"  class='browseImage'><?php echo $lang['SETTING_BASE_11']?></a></li>
        </ul>

        <?php foreach($data['setting-template']['files'] as $filename=>$path):?>
            <?php if(file_exists($pathTemplate.'/'.$path[0])):?>
                <a href="javascript:void(0);" class="file-template tab-email-views tool-tip-bottom" title="<?php echo $path[1];?>" data-path="<?php echo $path[0]?>"><?php echo $filename?></a>
            <?php endif;?>
        <?php endforeach;?>

        <?php foreach($data['setting-template']['email_layout'] as $filename=>$path):?>
            <?php if(file_exists($pathTemplate.'/'.$path[0])):?>
                <a href="javascript:void(0);" class="file-template tab-email-layout tool-tip-bottom" title="<?php echo $path[1];?>" data-path="<?php echo $path[0]?>"  style="display:none;"><?php echo $filename?></a>
            <?php endif;?>
        <?php endforeach;?>

        <?php foreach($data['setting-template']['layout'] as $filename=>$path):?>
            <?php if(file_exists($pathTemplate.'/'.$path[0])):?>
                <a href="javascript:void(0);" class="file-template tab-block-layout tool-tip-bottom" title="<?php echo $path[1];?>" data-path="<?php echo $path[0]?>"  style="display:none;"><?php echo $filename?></a>
            <?php endif;?>
        <?php endforeach;?>

        <?php foreach($data['setting-template']['print_layout'] as $filename=>$path):?>
            <?php if(file_exists($pathTemplate.'/'.$path[0])):?>
                <a href="javascript:void(0);" class="file-template tab-print-layout tool-tip-bottom" title="<?php echo $path[1];?>" data-path="<?php echo $path[0]?>"  style="display:none;"><?php echo $filename?></a>
            <?php endif;?>
        <?php endforeach;?>
    </div>


    <textarea id="codefile" style="width:100%; height:500px;"></textarea>
    <div class="error-not-tpl" style="display:none"><?php echo $lang['NOT_FILE_TPL'] ?></div>

    <button class="save-button save-file-template"><span><?php echo $lang['SAVE'] ?></span></button>
    <div class="clear"></div>
</div>



<!--Интерфейс-->
<div class="main-settings-container" id="interface-settings" style="display:none;">
    <h4><?php echo $lang['STNG_INTERFACE'];?></h4>
    <table class="main-settings-list">
        <tr>
            <td>
                <p><?php echo $lang['SETTING_LOCALE_13']?></p>
            </td>
            <td>
                <div class="color-settings">
                    <ul class="color-list">
                        <li class="red-theme"></li>
                        <li class="blue-theme"></li>
                        <li class="yellow-theme"></li>
                        <li class="green-theme"></li>
                    </ul>
                </div>
                <input type="hidden" name="themeColor" class="option" value="<?php echo $data['interface-settings']['options']['themeColor']['value'] ?>">
            </td>
            <td>
                <p><?php echo $lang['SETTING_LOCALE_14']?></p>
            </td>
        </tr>
        <tr>
            <td>
                    <span>
                      <?php echo $lang['SETTING_LOCALE_15']?>
                    </span>
            </td>
            <td>
                <div class="background-settings">
                    <ul class="color-list">
                        <li class="bg_1"></li>
                        <li class="bg_2"></li>
                        <li class="bg_3"></li>
                        <li class="bg_4"></li>
                        <li class="bg_5"></li>
                        <li class="bg_6"></li>
                        <li class="bg_7"></li>
                        <li class="bg_8"></li>
                        <li class="bg_9"></li>
                        <li class="bg_10"></li>
                        <li class="bg_11"></li>
                        <li class="bg_12"></li>
                        <li class="bg_13"></li>
                    </ul>
                    <div class="clear"></div>
                </div>
                <input type="hidden" name="themeBackground" class="option" value="<?php echo $data['interface-settings']['options']['themeBackground']['value'] ?>">
            </td>
            <td>
                <p><?php echo $lang['SETTING_LOCALE_16']?></p>
            </td>
        </tr>
        <tr>
            <td>
                    <span>
                      <?php echo $lang['SETTING_LOCALE_17']?>
                    </span>
            </td>
            <td>
                <?php $staticMenu = $data['interface-settings']['options']['staticMenu']['value'];
                $checked = '';
                $value = 'value="false"';
                if($staticMenu=="true"){
                    $checked = 'checked="checked"';
                    $value = 'value="'.$staticMenu.'"';
                }
                ?>
                <input type="checkbox" <?php echo $value ?> <?php echo $checked ?> name="staticMenu" class="option the-fixed-menu">
            </td>
            <td>
                <p><?php echo $lang['SETTING_LOCALE_18']?></p>
            </td>
        </tr>
    </table>
    <button id="temp" class="save-button save-settings tool-tip-bottom" title="<?php echo $lang['T_TIP_SAVE_U_CAT'];?>">
                <span><?php echo $lang['SAVE'];?>
                </span>
    </button>
    <div class="clear"></div>
</div>
<!--Вкладка - Валюта-->
<div class="main-settings-container" id="tab-currency-settings" style="display:none;">
    <h4>Курсы валюты</h4>
    <div class="currency-buttons">
        <a href="javascript:void(0)" class="add-new-currency custom-btn tool-tip-bottom" title="Добавить валюту" ><span>Добавить валюту</span></a>
        <a href="javascript:void(0)" class="edit-currency custom-btn tool-tip-bottom" title="Редактировать" ><span>Редактировать</span></a>
        <a href="javascript:void(0)" class="save-currency custom-btn tool-tip-bottom" title="Сохранить" ><span>Сохранить</span></a>
    </div>
    <?php $currencyShort = MG::getSetting('currencyShort');?>
    <table class="main-settings-list">
        <thead class="yellow-bg">
        <th>ISO</th>
        <th>Стоимость по отношению к валюте магазина ( <span class="view-value-curr"><?php echo MG::getSetting('currencyShopIso') ?> )</span></th>
        <th>Сокращение</th>
        <th>Действия</th>
        </thead>
        <tbody class="currency-tbody">
        <?php
        if(0 < count($data['currency-settings'])):
            foreach($data['currency-settings'] as $iso => $currency):?>
                <?php
                if($iso == MG::getSetting('currencyShopIso') ){
                    $class = 'class="none-edit"';
                }else{
                    $class = '';
                }
                ?>
                <tr data-iso="<?php echo $iso ?>" <?php echo $class ?>>
                    <td data-iso="<?php $iso ?>">
                        <span class="view-value-curr"><?php echo $iso ?></span>
                        <input type="text" name="currency_iso" value="<?php echo $iso ?>" class="currency-field" style="display:none"/>
                    </td>
                    <td class="currency-rate">
                        <span class="view-value-curr"> = </span>
                        <span class="view-value-curr"><?php echo number_format($currency['rate'], 2, ',', ' ' ); ?></span>
                        <span class="view-value-curr"><?php echo $currencyShort[MG::getSetting('currencyShopIso')]?></span>
                        <input type="text" name="currency_rate" value="<?php echo $currency['rate'] ?>" class="currency-field" style="display:none"/>
                    </td>
                    <td class="currency-short">
                        <span class="view-value-curr"><?php echo $currency['short'] ?></span>
                        <input type="text" name="currency_short" value="<?php echo $currency['short'] ?>" class="currency-field" style="display:none"/>
                    </td>
                    <td class="actions">
                        <?php if($iso != MG::getSetting('currencyShopIso') ){ ?>
                            <ul class="action-list">
                                <li class="delete-row" id="<?php echo $iso ?>"><a class="tool-tip-bottom" href="javascript:void(0);" title="<?php echo $lang['DELETE'];?>"></a></li>
                            </ul>
                        <?php } ?>
                    </td>
                </tr>
            <?php endforeach;
        else:?>
            <tr id="none_delivery"><td class="no-delivery" colspan="4">Отсутствуют валюты</td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>
<!-- Верстка модального окна  валют-->
</div>

<!--Методы доставки-->
<div class="main-settings-container" id="tab-deliveryMethod-settings" style="display:none;">
    <h4><?php echo $lang['STNG_DELIVERY'];?></h4>
    <a href="#" class="add-new-button tool-tip-bottom" title="<?php echo $lang['T_TIP_KEY_ADD_DELIVERY'];?>" ><span><?php echo $lang['STNG_KEY_ADD_DELIVERY'];?></span></a>
    <table class="main-settings-list">
        <thead class="yellow-bg">
        <th>id</th>
        <th><?php echo $lang['SETTING_LOCALE_19']?></th>
        <th><?php echo $lang['SETTING_LOCALE_20']?></th>
        <th><?php echo $lang['SETTING_LOCALE_21']?></th>
        <th><?php echo $lang['SETTING_LOCALE_29']?></th>
        <th><?php echo $lang['SETTING_LOCALE_22']?></th>
        <th><?php echo $lang['SETTING_LOCALE_23']?></th>
        </thead>
        <tbody class="deliveryMethod-tbody">
        <?php if(0 < count($data['deliveryMethod-settings']['deliveryArray'])):
            foreach($data['deliveryMethod-settings']['deliveryArray'] as $delivery):?>
                <tr id="delivery_<?php echo $delivery['id'] ?>"  data-id="<?php echo $delivery['id'] ?>" style="cursor:move">
                    <td class="deliveryId"><?php echo $delivery['id'] ?></td>
                    <td id="deliveryName" ><?php echo $delivery['name'] ?></td>
                    <td id="deliveryCost"><span class="costValue"><?php echo MG::numberFormat($delivery['cost'])?></span> <span class="currency"><span class="currency"><?php echo MG::getSetting('currency')?></span></span> </td>
                    <td id="deliveryDescription"><?php echo $delivery['description'] ?></td>
                    <td class="free"><span class="costFree"><?php echo MG::numberFormat($delivery['free']) ?></span> <span class="currency"><?php echo MG::getSetting('currency')?></span></td>
                  <td id="activity"  data-delivery-date ="<?php echo $delivery['date'] ?>" data-delivery-ymarket ="<?php echo $delivery['ymarket'] ?>" status="<?php echo $delivery['activity'] ?>">
                        <?php if($delivery['activity']):?>
                            <span class="activity-product-true"><?php echo $lang['ACTYVITY_TRUE'];?></span>
                        <?php else:?>
                            <span class="activity-product-false"><?php echo $lang['ACTYVITY_FALSE'];?></span>
                        <?php endif?>
                    </td>
                    <td class="actions">
                        <ul class="action-list">
                            <li class="edit-row" id="<?php echo $delivery['id'] ?>"><a class="tool-tip-bottom" href="javascript:void(0);" title="<?php echo $lang['EDIT'];?>"></a></li>
                            <li class="delete-row " id="<?php echo $delivery['id'] ?>"><a class="tool-tip-bottom" href="javascript:void(0);" title="<?php echo $lang['DELETE'];?>"></a></li>
                        </ul>
                    </td>
                    <td id="paymentHideMethod" style="display: none"></td>
                </tr>
            <?php endforeach;
        else:?>
            <tr id="none_delivery"><td class="no-delivery" colspan="6"><?php echo $lang['NONE_DELIVERY'];?></td></tr>
        <?php endif;?>
        </tbody>
    </table>
    <!-- Верстка модального окна способов доставки-->
    <div class="b-modal hidden-form add-category-popup" id="add-deliveryMethod-wrapper">
        <div class="product-table-wrapper deliveryMethod-table-wrapper">
            <div class="widget-table-title">
                <h4 class="delivery-table-icon"></h4>
                <div class="b-modal_close tool-tip-bottom" title="<?php echo $lang['T_TIP_CLOSE_WITHOUT_SAVE'];?>"></div>
            </div>
            <div class="widget-table-body">
                <div class="add-user-form-wrapper">
                    <div class="add-user-form">
                        <label>
                            <span class="custom-text"><?php echo $lang['SETTING_LOCALE_19']?>:</span>
                            <input type="text" name="deliveryName" class="product-name-input" title="<?php echo $lang['T_TIP_USER_EMAIL'];?>">
                            <div class="errorField"><?php echo $lang['ERROR_EMPTY'];?></div>
                        </label>
                        <label>
                            <span class="custom-text"><?php echo $lang['SETTING_LOCALE_20']?>:</span>
                            <input type="text" name="deliveryCost" class="product-name-input">
                            <span class="currency"><?php echo MG::getSetting('currency')?></span>
                            <div class="errorField"><?php echo $lang['ERROR_NUMERIC'];?></div>
                        </label>
                        <label>
                            <span class="custom-text"><?php echo $lang['SETTING_LOCALE_21']?>:</span>
                            <input type="text" name="deliveryDescription" class="product-name-input">
                            <div class="errorField"><?php echo $lang['ERROR_EMPTY'];?></div>
                        </label>
                        <label>
                            <span class="custom-text"><?php echo $lang['SETTING_BASE_12'];?>:</span>
                            <input type="text" name="free" class="product-name-input tool-tip-bottom" title="<?php echo $lang['SETTING_BASE_13'];?>">
                            <span class="currency"><?php echo MG::getSetting('currency')?></span>
                            <div class="errorField"><?php echo $lang['ERROR_NUMERIC'];?></div>
                        </label>
                        <label>
                            <span class="custom-text"><?php echo $lang['SETTING_LOCALE_22']?>:</span>
                            <input type="checkbox" name="deliveryActivity" class="delivery-active">
                        </label>
                         <label>
                            <span class="custom-text"><?php echo $lang['SETTING_LOCALE_YMARKET']?>:</span>
                            <input type="checkbox" name="deliveryYmarket" class="delivery-date">
                        </label>
                        <label>
                            <span class="custom-text"><?php echo $lang['SETTING_LOCALE_DATE']?>:</span>
                            <input type="checkbox" name="deliveryDate" class="delivery-date">
                        </label>
                        <div id="paymentCheckbox">
                            <span class="custom-text bold-text"><?php echo $lang['SETTING_LOCALE_24']?>:</span>
                            <div id="paymentArray">
                                <?php foreach($data['paymentMethod-settings']['paymentArray'] as $payment):?>
                                    <label>
                                        <span class="custom-text"><?php echo $payment['name']?></span>
                                        <input type="checkbox" name="<?php echo $payment['id']?>" class="paymentMethod">
                                    </label>
                                <?php endforeach;?>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <button class="save-button tool-tip-bottom" title="<?php echo $lang['T_TIP_USER_SAVE'];?>"><span><?php echo $lang['SAVE'];?></span></button>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Верстка модального окна  способов доставки-->
</div>
<!--Методы оплаты-->
<div class="main-settings-container" id="tab-paymentMethod-settings" style="display:none;">
    <h4><?php echo $lang['STNG_PAYMENT'];?>  ( <a style="color:#73CBF7" href="http://wiki.moguta.ru/kak-nastroit-sposobi-oplati-v-moguta-cms">Видеоинструкция</a> )</h4>
    <?php //viewData($data['paymentMethod-settings']['paymentArray'])?>
    <table class="main-settings-list">
        <thead class="yellow-bg">
        <th class="id-way" style="display:none">id способа</th>
        <th><?php echo $lang['SETTING_LOCALE_19']?></th>
        <th><?php echo $lang['SETTING_LOCALE_22']?></th>
        <th><?php echo $lang['SETTING_LOCALE_23']?></th>
        </thead>
        <tbody class="paymentMethod-tbody">
        <?php foreach($data['paymentMethod-settings']['paymentArray'] as $payment):?>
            <tr id="payment_<?php echo $payment['id'] ?>" data-id="<?php echo $payment['id'] ?>" style="cursor:move">
                <td class="paymentId" style="display:none"><?php echo $payment['id'] ?></td>
                <td id="paymentName"><?php echo $payment['name'] ?></td>
                <td id="activity" status="<?php echo $payment['activity'] ?>">
                    <?php if($payment['activity']):?>
                        <span class="activity-product-true"><?php echo $lang['ACTYVITY_TRUE'];?></span>
                    <?php else:?>
                        <span class="activity-product-false"><?php echo $lang['ACTYVITY_FALSE'];?></span>
                    <?php endif?>
                </td>
                <td class="actions">
                    <ul class="action-list">
                        <li class="edit-row" id="<?php echo $payment['id'] ?>"><a class="tool-tip-bottom" href="javascript:void(0);" title="<?php echo $lang['EDIT'];?>"></a></li>
                    </ul>
                </td>
                <td id="paramHideArray" style="display: none"><?php echo $payment['paramArray'] ? htmlspecialchars($payment['paramArray']): '{"0":0}' ?></td>
                <td id="deliveryHideMethod" style="display: none"><?php echo $payment['deliveryMethod'] ? $payment['deliveryMethod']: '{"0":0}' ?></td>
                <td id="urlArray" style="display: none"><?php echo $payment['urlArray'] ?></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <!-- Верстка модального окна способов оплаты-->
    <div class="b-modal hidden-form add-category-popup" id="add-paymentMethod-wrapper">
        <div class="product-table-wrapper paymentMethod-table-wrapper">
            <div class="widget-table-title">
                <h4 class="payment-table-icon"></h4>
                <div class="b-modal_close tool-tip-bottom" title="<?php echo $lang['T_TIP_CLOSE_WITHOUT_SAVE'];?>"></div>
            </div>
            <div class="widget-table-body">
                <div class="add-user-form-wrapper">
                    <div class="add-user-form">
                        <span class="custom-text"><strong><?php echo $lang['SETTING_LOCALE_19']?>:</strong></span>
                        <span id="paymentName"><?php echo $lang['SETTING_LOCALE_28']?></span><br>
                        <span class="custom-text bold-text"><?php echo $lang['SETTING_LOCALE_25']?>:</span>
                        <div id="paymentParam"></div>
                        <label>
                            <span class="custom-text"><?php echo $lang['SETTING_LOCALE_22']?>:</span>
                            <input type="checkbox" name="paymentActivity" class="payment-active">
                        </label>
                        <div id="deliveryCheckbox">
                            <span class="custom-text bold-text"><?php echo $lang['SETTING_LOCALE_26']?>:</span>
                            <div id="deliveryArray">
                                <?php foreach($data['deliveryMethod-settings']['deliveryArray'] as $delivery):?>
                                    <label>
                                        <span class="custom-text"><?php echo $delivery['name']?></span>
                                        <input type="checkbox" name="<?php echo $delivery['id']?>" class="deliveryMethod">
                                    </label>
                                <?php endforeach;?>
                            </div>
                        </div>

                        <div id="urlParam"></div>
                        <div class="clear"></div>
                        <button class="save-button tool-tip-bottom" title="<?php echo $lang['T_TIP_USER_SAVE'];?>"><span><?php echo $lang['SAVE'];?></span></button>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Верстка модального окна  способов оплаты-->
    <div class="clear"></div>
</div>
</div>
</div>
</div>
</div>
</div>
<script>
$('.memcache-conection').hide();	
$('input[name="cacheHost"]').parent('li').hide();
$('input[name="cachePort"]').parent('li').hide();

if($('.section-settings  select[name="cacheMode"]').val()=="MEMCACHE"){
  $('.memcache-conection').show();	
  $('input[name="cacheHost"]').parent('li').show();	
  $('input[name="cachePort"]').parent('li').show();		 
};
</script>