
<div class="section-catalog">
  <div class="widget-table-wrapper">
    <div class="widget-table-title">
      <h4 class="product-table-icon"><?php echo $lang['TITLE_PRODUCTS'];?></h4>
      <p class="produc-count"><?php echo $lang['ALL_COUNT_PRODUCT']; ?> : <strong><?php echo $productsCount ?></strong>  <?php echo $lang['UNIT'];?></p>
      <div class="clear"></div>
    </div>

    <!-- Тут начинается  Верстка таблицы товаров -->
    <div class="widget-table-body">
      <div class="widget-table-action">
        
        
        <div class="add-new-button tool-tip-top" title="<?php echo $lang['T_TIP_CREATE_PROD'];?>"><span><?php echo $lang['ADD_NEW_PRODUCT'];?></span></div>
        <a href="javascript:void(0);" class="show-filters tool-tip-top" title="<?php echo $lang['T_TIP_SHOW_FILTER'];?>"><span><?php echo $lang['FILTER'];?></span></a>
        <a href="<?php echo SITE ?>/mg-admin?csv=1" class="get-csv tool-tip-top" title="<?php echo $lang['T_TIP_PRODUCT_CSV'];?>"><span><?php echo $lang['IN_CSV'];?></span></a>
        <a href="javascript:void(0);" class="import-csv tool-tip-top custom-btn" title="<?php echo $lang['PROD_FROM_CSV'];?>"><span><?php echo $lang['PROD_FROM_CSV'];?></span></a>   
        <a href="javascript:void(0);" class="get-yml-market  tool-tip-top" title="<?php echo $lang['T_TIP_UPLOAD_YA'];?>"><span><?php echo $lang['PROD_UPLOAD_YA'];?></span></a>
        
        <div class="filter" >
          <span class="last-items"><?php echo $lang['SHOW_PRODUCT_COUNT'];?></span>
          <select class="last-items-dropdown countPrintRowsProduct">
            <?php
            foreach(array(10, 20, 50, 100, 300) as $value){
              $selected = '';
              if($value == $countPrintRowsProduct){
                $selected = 'selected="selected"';
              }
              echo '<option value="'.$value.'" '.$selected.' >'.$value.'</option>';
            }
            ?>
          </select>
        </div>

          <input type="text" name="search" value="<?php echo $lang['FIND'];?>..." onfocus="if (this.value == '<?php echo $lang['FIND'];?>...') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php echo $lang['FIND'];?>...';}" class="custom-input search-input"/> <a href="#" class="searchProd tool-tip-top" title="<?php echo $lang['FIND'];?>"></a>
         
          <div class="clear"></div>
      </div>

      <div class="filter-container" <?php if($displayFilter){echo "style='display:block'";} ?>>
     
      <div class="select">
        <span class="label-field"> <?php echo $lang['CATEGORIES'] ?> :</span>
          <select name="cat_id" class="last-items-dropdown">
          <?php    
           foreach ($listCategory  as $value => $text) {       
                $selected = ($_REQUEST['cat_id']."" === $value."") ? 'selected="selected"' : '';
                $html .= '<option value="'.$value.'" '.$selected.'>'.$text.'</option>';
              }
              $html .= '</select>';

              $checked = '';     

              if ($_REQUEST['insideCat']==="true"||empty($_REQUEST['insideCat'])) {
                  $checked = 'checked=checked';
              }
              $html .= '<div class="checkbox"><label>'.$lang['FILTR_PRICE7'].'<input type="checkbox"  name="insideCat" '.$checked.' /></label></div>';

              echo $html;
          ?>       
       </div>
        <?php  echo $filter ?>       
        <div class="clear"></div>
      </div>
      
      <div class="import-container">
        <div class="message-importing"></div>
        <div class="process">           
           
        </div>
      
        
        <div class="block-upload-сsv">
          <span><?php echo $lang['CSV_TYPE_UPLOAD'];?>:</span>
          
          <select name='importType'>
            <option value="MogutaCMS">MogutaCMS</option>    
            <option value="MogutaCMSUpdate">MogutaCMS [обновление цен и остатков]</option>            
          </select>
          
          <a href="<?php echo SITE?>/mg-admin?examplecsv=1" class="get-example-csv view-MogutaCMS example-csv" ><?php echo $lang['EXAMPLE_CATALOG_CSV'];?></a>
          <a href="<?php echo SITE?>/mg-admin?examplecsvupdate=1" class="get-example-csv-update view-MogutaCMSUpdate example-csv" style="display:none">Скачать пример файла для обновления цен и остатков</a>
          

          <form method="post" noengine="true" enctype="multipart/form-data" class="upload-csv-form">        
             <span><?php echo $lang['UPLOAD'];?></span>
             <input type="file" name="upload" class="" title="<?php echo $lang['CSV_UPLOAD_FILE'];?>"/>  

          </form>
        </div>
        
        <div class="block-importer">
          
          <div class="repeat-upload-file"><a href="javascript:void(0);" class="repeat-upload-csv" title="Отменить"></a></div>
          <div class="cancel-importing"><a href="javascript:void(0);" class="cancel-import custom-btn"><span><?php echo $lang['BREAK_UPLOAD_CSV'];?></span></a></div>    
          <div class="delete-all-products-btn"><input type="checkbox" name="no-merge" class="" title="<?php echo $lang['CLEAR_BEFORE_CSV'];?>"  value="false"><?php echo $lang['DEL_ALL_PROD'];?></div>
          <a href="javascript:void(0);" class="start-import custom-btn"><span><?php echo $lang['BEGIN_UPLOAD_CSV'];?></span></a>
         
        </div>
        <div class="clear"></div>
      </div>

      <div class="main-settings-container">
        <table class="widget-table product-table">
          <thead>
            <tr>
              <th class="checkbox-cell"><input type="checkbox" name="product-check"></th>
              <th class="id-product"><a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0]=="id") ? 'sort-dir-'.$sorterData[3]:'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0]=="id") ? $sorterData[1]*(-1) : 1 ?>" data-field="id">№</a></th>
              <th class="prod-cat"><a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0]=="cat_id") ? 'sort-dir-'.$sorterData[3]:'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0]=="cat_id") ? $sorterData[1]*(-1) : 1?>" data-field="cat_id"><?php echo $lang['CAT_PRODUCT'];?></a></th>
              <th class="product-picture"><?php echo $lang['IMAGE'];?></th>
              <th class="prod-name" class="product-name  "><a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0]=="title") ? 'sort-dir-'.$sorterData[3]:'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0]=="title")? $sorterData[1]*(-1) : 1?>" data-field="title"><?php echo $lang['NAME_PRODUCT'];?></a></th>
              <th class="prod-price"><a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0]=="price") ? 'sort-dir-'.$sorterData[3]:'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0]=="price") ?  $sorterData[1]*(-1) : 1 ?>"  data-field="price"><?php echo $lang['PRICE_PRODUCT'];?></a></th>
              <th class="rest"><a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0]=="count") ? 'sort-dir-'.$sorterData[3]:'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0]=="count") ?  $sorterData[1]*(-1) : 1 ?>" data-field="count"><?php echo $lang['REMAIN'];?></a></th>
              <th class="actions"><?php echo $lang['ACTIONS'];?></th>
            </tr>
          </thead>
          <tbody class="product-tbody">
          <?php
          if(!empty($catalog)){
         // viewData($catalog);
          $currencyShort = MG::getSetting('currencyShort'); 
          $currencyShopIso = MG::getSetting('currencyShopIso'); 
          $currency = MG::getSetting('currency'); 
          foreach($catalog as $data){ 
            $data['currency_iso'] = $data['currency_iso']?$data['currency_iso']:$currencyShopIso;
            ?>
            <tr id="<?php echo $data['id'] ?>" data-id="<?php echo $data['id'] ?>" class="product-row">
           
                <td class="check-align"><input type="checkbox" name="product-check"></td>
                <td class="id"><?php echo $data['id'] ?></td>
                <td id="<?php echo $data['cat_id'] ?>" class="cat_id">
                  <?php $path = (substr_count($data['category_url'], '/')>1) ? '<a class="parentCat tool-tip-bottom" title="" style="cursor:pointer;">../</a>' : '' ; ?>
                  <?php echo $listCategories[$data['cat_id']] ? $path.$listCategories[$data['cat_id']]:'Категория удалена'; ?></td>    
                <td class="product-picture image_url">
                  <?php
                  $imagesUrl =  explode("|", $data['image_url']);                
               
                  if(!empty($imagesUrl[0])){            
                    $src = mgImageProductPath($imagesUrl[0], $data["id"], 'small');           
                  }
                  ?>
                  <img class="uploads" src="<?php echo $src ?>"/>
                </td>                
                <td class="name" ><?php echo $data['title'] ?><a class="link-to-site tool-tip-bottom" title="<?php echo $lang['PRODUCT_VIEW_SITE'];?>" href="<?php echo SITE.'/'?><?php echo isset($data['category_url'])?$data['category_url']:'catalog/'?><?php echo $data['product_url']?>"  target="_blank" ><img src="<?php echo SITE?>/mg-admin/design/images/icons/link.png" alt="" /></a></td>
                <?php $printPrice = false;?>   
                <td class="price">
                  <table class="variant-row-table">
                    
                  <?php if($data['price']!=MG::numberFormat($data['real_price'])&&empty($data['variants'])):?>   
                    <?php $printPrice = true;?>   
                    <tr>
                      <td colspan="3">
                        <span class="view-price tool-tip-bottom"  data-productId="<?php echo $data['id']?>" style="color: <?php echo ($data['price']>$data['real_price'])?'#1C9221':'#B42020';?>;" title="с учетом скидки/наценки"><?php echo MG::priceCourse($data['price_course']).' '.$currency?></span>
                        <div class="clear"></div>  
                      </td>
                    </tr>  
                  <?php endif;?>                         
                  <?php if(!empty($data['variants'])){  ?>
                    <tbody>
                    <?php 
                    foreach ($data['variants'] as $count => $item) {
                      if($count > 2){?>
                        </tbody>
                        <tbody class="second-block-varians" style="display:none;">
                      <?php }
                    ?>
                      <?php if ($item['price']!=$item['price_course']): ?>
                          <tr>
                            <td colspan="3">
                              <span class="view-price tool-tip-bottom" data-productId="<?php echo $item['id']?>" style="color: <?php echo ($item['price']<$item['price_course'])?'#1C9221':'#B42020';?>;" title="с учетом скидки/наценки"><?php echo MG::priceCourse($item['price_course']).' '.$currency?></span>
                              <div class="clear"></div>
                            </td>
                          </tr>
                      <?php else:?>
                        <?php if ($count>0): ?>
                          <tr><td colspan="3" height="15"></td></tr>
                        <?php endif;?>
                      <?php endif;?>
                      <?php 
                      $printPrice = true;
                      echo '<tr class="variant-price-row"><td><span class="price-help">'.$item['title_variant'].'</span></td><td><input style="width: 45px;" class="variant-price fastsave" type="text" value="'.$item['price'].'"  data-packet="{variant:1,id:'.$item['id'].',field:\'price\'}"/></td><td>'.$currencyShort[$data['currency_iso']].'</td></tr>';
                    }
                    ?>
                    </tbody>
                    <?php if(count($data['variants']) > 3):?>
                    <tr><td colspan="3"><a href="javascript:void();" class="show-all-variants"><?php echo $lang['ACT_SHOW_ALL_VARIANTS']?></a></td></tr>
                    <?php endif;?>
                  <?php
                  }else{
                    echo ' <tr><td></td><td><input style="width: 45px;" type="text" value="'.$data['real_price'].'" class="fastsave"  data-packet="{variant:0,id:'.$data['id'].',field:\'price\'}"/></td><td> '. $currencyShort[$data['currency_iso']].'</td></tr>';
                  }?>
                  </table>    
                </td>
                <td class="count">
                <?php
                if ($printPrice) {
                  $margin = 'margin-top:15px;';
                }
                if (!empty($data['variants'])) {
                  echo '<div>';
                  foreach ($data['variants'] as $count => $item) {
                    if($count > 2){
                      echo '</div><div class="second-block-varians" style="display:none;">';
                    }
                    if ($count == 0) {
                      $margin = '';
                      if ($item['price'] != $item['price_course']) {
                        $margin = 'margin-top:15px;';
                      }
                    } else {
                      $margin = 'margin-top:15px;';
                    }
                    echo '<div style="' . $margin . '"><input style="width: 25px;" class="variant-count fastsave" type="text" value="' . ($item['count'] < 0 ? '&#8734;' : $item['count']) . '" data-packet="{variant:1,id:' . $item['id'] . ',field:\'count\'}"/> ' . $lang['UNIT'] . '</div>';
                  }
                  echo '</div>';
                } else {
                  echo '<div style="' . $margin . '"><input style="width: 25px;" type="text" value="' . ($data['count'] < 0 ? '&#8734;' : $data['count']) . '" class="fastsave"  data-packet="{variant:0,id:' . $data['id'] . ',field:\'count\'}"/> ' . $lang['UNIT'] . '</div>';
                }
                $margin = '';
                ?>
                    <?php if(count($data['variants']) > 3):?>
                        <div style="height: 18px;"></div>
                    <?php endif;?>
                </td>                                    
                <td class="actions">
                  <ul class="action-list">                
                    <li class="edit-row" id="<?php echo $data['id'] ?>"><a class="tool-tip-bottom" href="javascript:void(0);" title="<?php echo $lang['EDIT'];?>"></a></li>
                    <li class="new tool-tip-bottom  <?php echo ($data['new'])?'active':''?>" data-id="<?php echo $data['id'] ?>" title="<?php echo ($data['new'])? $lang['PRINT_IN_NEW']:$lang['PRINT_NOT_IN_NEW'];?>"><a href="javascript:void(0);"></a></li>
                    <li class="recommend tool-tip-bottom  <?php echo ($data['recommend'])?'active':''?>" data-id="<?php echo $data['id'] ?>" title="<?php echo ($data['recommend'])? $lang['PRINT_IN_RECOMEND']:$lang['PRINT_NOT_IN_RECOMEND'];?>"><a href="javascript:void(0);"></a></li>
                    <li class="clone-row" id="<?php echo $data['id'] ?>"><a class="tool-tip-bottom" href="javascript:void(0);" title="<?php echo $lang['CLONE'];?>"></a></li>
                    <li class="visible tool-tip-bottom  <?php echo ($data['activity'])?'active':''?>" data-id="<?php echo $data['id'] ?>" title="<?php echo ($data['activity'])? $lang['ACT_V_PROD']:$lang['ACT_UNV_PROD'];?>"><a href="javascript:void(0);"></a></li>
                    <li class="delete-order " id="<?php echo $data['id'] ?>"><a class="tool-tip-bottom" href="javascript:void(0);"  title="<?php echo $lang['DELETE'];?>"></a></li>
                  </ul> 
                </td>
              </tr>
          <?php }
          }else{
          ?>

           <tr class="no-results"><td colspan="10"><?php echo $lang['PROD_NONE']?></td></tr>

         <?php }?>
          </tbody>
        </table>       
      </div>
      
       
      <select name="operation" class="product-operation">       
        <option value="activity_0"><?php echo $lang['ACTION_PROD_1']?></option> 
        <option value="activity_1"><?php echo $lang['ACTION_PROD_2']?></option> 
        <option value="recommend_1"><?php echo $lang['ACTION_PROD_3']?></option> 
        <option value="recommend_0"><?php echo $lang['ACTION_PROD_4']?></option> 
        <option value="new_1"><?php echo $lang['ACTION_PROD_5']?></option> 
        <option value="new_0"><?php echo $lang['ACTION_PROD_6']?></option> 
        <option value="clone"><?php echo $lang['ACTION_PROD_7']?></option> 
        <option value="getcsv"><?php echo $lang['ACTION_PROD_8']?></option> 
        <option value="getyml"><?php echo $lang['ACTION_PROD_9']?></option> 
        <?php foreach (MG::getSetting('currencyShort') as $iso => $short):?>
          <option value="changecur_<?php echo $iso; ?>">Пересчитать валюту в <?php echo $iso; ?></option>
        <?php endforeach; ?> 
        <option value="delete"><?php echo $lang['DELL_SELECTED_PROD']?></option> 
      </select>
      <a href="javascript:void(0);" class="run-operation custom-btn"><span><?php echo $lang['ACTION_RUN']?></span></a>      
      <?php echo $pagination ?>
      <div class="clear"></div>
    </div>
    <!-- Тут заканчивается Верстка таблицы товаров -->

      <!-- Тут начинается Верстка модального окна -->

      <div class="b-modal hidden-form" id="add-product-wrapper">
        <div class="product-table-wrapper">
          <div class="widget-table-title">
            <h4 class="add-product-table-icon"><?php echo $lang['ADD_PRODUCT'];?></h4>
            <div class="b-modal_close tool-tip-bottom" title="<?php echo $lang['T_TIP_CLOSE_MODAL'];?>"></div>
          </div>
          <div class="widget-table-body">
            <div class="add-product-form-wrapper">

              <div class="add-img-form">
                  <div class="images-block">
                      <p class="add-img-text"><?php echo $lang['IMAGE_PRODUCT']?></p>
                      <div class="prod-gallery">
                        <div class="small-img-wrapper"></div>
                      </div>                  
                      <div class="controller-gallery">
                          <a href="javascript:void(0);" class="add-image"><span><?php echo $lang['ADD_IMG'];?></span></a>                          
                      </div>
                      <div class="clear"></div>
                  </div>
                <div class="product-text-inputs">
                  <label for="title"><span class="custom-text"><?php echo $lang['NAME_PRODUCT'];?>:</span><input style="width:248px;" type="text" name="title" class="product-name-input tool-tip-right" title="<?php echo $lang['T_TIP_NAME_PROD'];?>" ><div class="errorField"><?php echo $lang['ERROR_SPEC_SYMBOL'];?></div>
                  
                    <input type="hidden" name="link_electro" class="product-name-input">
                    <a href="javascript:void(0);" class="add-link-electro">Добавить ссылку на электронный товар</a>
                    <a href="javascript:void(0);" class="del-link-electro">Удалить</a>
                  </label>
                  <label><span class="custom-text"><?php echo $lang['URL_PRODUCT'];?>:</span><input style="width:248px;" type="text" name="url" class="product-name-input tool-tip-right" title="<?php echo $lang['T_TIP_URL_PRODUCT'];?>"><div class="errorField"><?php echo $lang['ERROR_EMPTY'];?></div></label>
                  <div class="category-filter">
                    <span class="custom-text"><?php echo $lang['CAT_PRODUCT'];?>:<a href="javascript:void(0);" class="add-category"><span>+</span></a></span>
                    <select  style="width:270px;" class="last-items-dropdown custom-dropdown tool-tip-right" title="<?php echo $lang['T_TIP_CAT_PROD'];?>" id="productCategorySelect" name="cat_id">
                      <option selected="selected" value="0"><?php echo $lang['ALL'];?></option>
                      <?php echo $categoriesOptions ?>
                    </select>
                  </div>
                  
                  <div class="inside-category" style="display:none">
                    <span class="custom-text"><?php echo $lang['PROD_VIEW_IN_CAT'];?>:</span>
                    <select class ="tool-tip-top" title="<?php echo $lang['T_TIP_SELECTED_U_CAT'];?>" name="inside_cat" multiple size="4">
                      <?php echo $categoriesOptions ?>
                    </select>
                    <div class="clear"></div>
                    <a href="javascript:void(0);" class="clear-select-cat"><span><?php echo $lang['PROD_CLEAR_CAT'];?></span></a>
                      <a href="javascript:void(0);" class="full-size-select-cat closed-select-cat"><span><?php echo $lang['PROD_OPEN_CAT'];?></span></a>
                  </div>                  
                 
                  <div class="select-currency-block" style="display:none">
                    <div class="currency-block">
                      <div class="add-product-field">
                        <span>Выберите валюту:</span>        
                        <select name="currency_iso" class="product-name-input">
                         <?php 
                           $currencyShort = MG::getSetting('currencyShort'); 
                           $currencyRate = MG::getSetting('currencyRate'); 
                           foreach ($currencyShort as $iso => $short):?>
                           <option value="<?php echo $iso; ?>" data-rate="<?php echo $currencyRate[$iso]; ?>"><?php echo $short; ?></option>
                         <?php endforeach; ?>                       
                        </select>                    
                      </div>                   
                      <a class="apply-currency fl-right custom-btn" href="javascript:void(0);"><span>Применить</span></a>
                      <div class="clear"></div>
                    </div>
                  </div>
                  
                  <div class="variant-table-wrapper">
                      <table class='variant-table'>
                      </table>
                      <a href="javascript:void(0);" class="add-position"><span><?php echo $lang['ADD_VARIANT'];?></span></a>
                  </div>  
					<div class="addPropertyField">
                    <a href="javascript:void(0);" class="add-property"><span><?php echo $lang['ADD_PROPERTY'];?></span></a>
                    <div class="clear"></div>
                    <div class="new-added-properties" style="display:none">
                      <ul data-id="">
                        <li class="name"><label>Название характеристики:</label>
                            <div class="errorField">Необходимо заполнить поле:</div><input type="text" name="name" ></li>
                        <li class="data"><label>Значение:</label><input type="text" name="value"></li>
                      </ul>
                      <a class="apply-new-prop fl-right custom-btn" href="javascript:void(0);"><span>Применить</span></a>
                      <a class="cancel-new-prop fl-left custom-btn" href="javascript:void(0);"><span>Отмена</span></a>
                    </div>
                </div>
                 <div class="addedProperty"></div>
                <div class="userField"></div>   				  
                </div>
                <!--Блок для редактирования галереии продуктов-->               
                             
                <div class="product-desc-wrapper">
                  <span class="custom-text" style="margin-bottom: 10px;"><?php echo $lang['DESCRIPTION_PRODUCT'];?>:</span>
                  <a href="javascript:void(0);" class="html-content-edit">Редактировать описание</a>
                  <div style="background:#FFF;display:none;" id="html-content-wrapper">
                    <textarea class="product-desc-field" name="html_content" style="width:821px;"></textarea>
                  </div>
                </div>
                
                
                
              <div class="add-related-product-block">
                  <div class="add-related-button-wrapper">
                      <a class="add-related-product tool-tip-bottom" href="javascript:void(0);" title="<?php echo $lang['RELATED_6'];?>"><?php echo $lang['RELATED_5'];?><span class="add-icon"></span></a>
                      <div class="select-product-block">
                          <div class="search-block">
                            <div class="add-product-field">

                            <span><?php echo $lang['PROD_ADD_RELATE'];?>: </span>               
                            <input type="text" autocomplete="off" name="searchcat" class="search-field" placeholder="<?php echo $lang['RELATED_7'];?>" >
                            <div class="errorField" style="display: none;"><?php echo $lang['RELATED_1'];?></div>

                            </div>
                            <div class="example-line"><?php echo $lang['RELATED_2'];?>: <a href="javascript:void(0)" class="example-find" ><?php echo $exampleName?></a></div>
                            <div class="fastResult"></div>     
                            <a class="cancel-add-related custom-btn" href="javascript:void(0);"><span><?php echo $lang['RELATED_3'];?></span></a>
                            <a class="random-add-related custom-btn" href="javascript:void(0);"><span>Случайный товар</span></a>
                          
                            <div class="clear"></div>
                          </div>
                      </div>
                         
                  </div>
                    <div class="related-wrapper">
                        <div class="added-related-product-block">    
                           <a class="add-related-product in-block-message" href="javascript:void(0);"><span><?php echo $lang['RELATED_4'];?></span></a>
                           <div class="clear"></div>
                        </div>
                    </div>
              </div>
                
                
                
                <span class="yml-title">Показать настройки YML</span>
                <div class="yml-wrapper" style="display:none">
                    <label>
                        <span class="custom-text">Содержание поля sales_notes для экспорта в Яндекс.Маркет:
                        <a href='javascript:void(0);' class='tool-tip-top desc-property' title="Используется для указания важной информации: необходимости предоплаты, условий комплектации, доставки для данного товара, минимальной суммы заказа, а также для описания акций, скидок, распродаж и пр." >?</a>
                        </span><input type="text" name="yml_sales_notes" title="Будет подставлено в sales_notes. Допустимая длина - 50 символов." class="product-name-input meta-data tool-tip-bottom"></label>
                  
                </div>      
                
                <div class="clear"></div>
                <span class="seo-title"><?php echo $lang['SEO_BLOCK']?></span>
                <div class="seo-wrapper">
                    <label><span class="custom-text"><?php echo $lang['META_TITLE'];?>:</span><input type="text" name="meta_title" title="<?php echo $lang['T_TIP_META_TITLE'];?>" class="product-name-input meta-data tool-tip-bottom"></label>
                    <label><span class="custom-text"><?php echo $lang['META_KEYWORDS'];?>:</span><input type="text" name="meta_keywords" class="product-name-input meta-data tool-tip-bottom" title="<?php echo $lang['T_TIP_META_KEYWORDS'];?>"></label>
                    <label>
                    <ul class="meta-list">
                            <li><span class="custom-text"><?php echo $lang['META_DESC'];?>:</span></li>
                            <li><span class="symbol-left"><?php echo $lang['LENGTH_META_DESC'];?></span>: <span class="symbol-count"></span></li>
                    </ul>
                    <textarea class="product-meta-field tool-tip-bottom" name="meta_desc" title="<?php echo $lang['T_TIP_META_DESC'];?>"></textarea>
                    </label>
                </div>                
             
                <div class="clear"></div>
               

                <button class="save-button tool-tip-bottom" title="<?php echo $lang['T_TIP_SAVE_PROD'];?>"><span><?php echo $lang['SAVE'];?></span></button>

                <div class="clear"></div>
              </div>
            </div>
          </div>
        </div>
<!-- Тут начинается Верстка модального окна для текстовой характеристики  -->
  <div class="textarea-overlay" style="display: none;"></div>
    <div id="textarea-property-value" style="display: none">
        <div class="product-table-wrapper">
            <div class="widget-table-title">
                <h4 class="add-product-table-icon">Редактирование характеристики</h4>
                <div class="proper-modal_close tool-tip-bottom" title="<?php echo $lang['T_TIP_CLOSE_MODAL']; ?>"></div>
            </div>
            <div class="widget-table-body">
                <div class="property-value custom-textarea-value">
                    <textarea name="html_content-textarea" ></textarea>
                </div>
                <div class="save">
                 <button class="save-button-value tool-tip-bottom custom-btn" title="Сохранить значение характеристики"><span><?php echo $lang['SAVE'];?></span></button>
                </div>
            </div>
        </div>
    </div>
    </div>
    <!-- Тут заканчивается Верстка модального окна -->
  </div>
</div>


     
<script>$(".added-related-product-block").sortable();</script>
