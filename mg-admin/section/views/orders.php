<div class="section-order">
    <div class="widget-table-wrapper">
        <div class="widget-table-title">
            <h4 class="product-order-icon"><?php echo $lang['TITLE_ORDERS']; ?></h4>
            <p class="produc-count"><?php echo $lang['ALL_COUNT_ORDER']; ?>: <strong><?php echo $orderCount ?></strong> <?php echo $lang['UNIT']; ?></p>
            <div class="clear"></div>
        </div>
        <!--Модальное окно заказов-->
        <div class="b-modal hidden-form" id="add-order-wrapper">
            <div class="orders-table-wrapper">
                <div class="widget-table-title">
                    <h4 class="add-order-table-icon"></h4>
                    <div class="b-modal_close tool-tip-bottom" title="<?php echo $lang['T_TIP_CLOSE_MODAL']; ?>"></div>
                </div>
                <div class="widget-table-body">
                    <div class="order-preview">                  

                        <div class="category-filter">
                            <button class="editor-order tool-tip-bottom custom-btn order-edit-visible" title="<?php echo $lang['MOD_ORDER_1']; ?>" data-id=""><span><?php echo $lang['MOD_ORDER_1']; ?></span></button>
                            <button class="print-button tool-tip-bottom custom-btn order-edit-visible" title="<?php echo $lang['T_TIP_PRINT_ORDER']; ?>" data-id=""><span><?php echo $lang['PRINT_ORDER']; ?></span></button>
                            <button class="csv-button tool-tip-bottom custom-btn order-edit-visible" title="<?php echo $lang['T_TIP_PRINT_ORDER']; ?>" data-id=""><span><?php echo $lang['MOD_ORDER_2']; ?></span></button>
                            <button class="get-pdf-button tool-tip-bottom custom-btn order-edit-visible" title="<?php echo $lang['T_TIP_PRINT_ORDER_PDF']; ?>"  data-id=""><span><?php echo $lang['PRINT_ORDER_PDF']; ?></span></button>
                            <span class="custom-text"><?php echo $lang['MOD_ORDER_3']; ?>:</span>
                            <select id="orderStatus" class="last-items-dropdown custom-dropdown tool-tip-right" title="<?php echo $lang['SELECT_ORDER_STATUS']; ?>"  name="status_id">
                                <?php foreach ($assocStatus as $k => $v): ?>
                                  <option value="<?php echo $k ?>"> <?php echo $lang[$assocStatus[$k]] ?> </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="user-inform"><label><input type="checkbox" name="inform-user" value = "false">Информировать покупателя о смене статуса.</label></div>
                        </div>
                        <div class="loading-block"></div>
                        <div class="clear"></div>

                        <div id="order-data">
                            <div class="search-block order-edit-display">
                                <div class="add-product-field">

                                    <span><?php echo $lang['ORDER_BS_1']; ?>: </span>               
                                    <input type="text" autocomplete="off" name="searchcat" class="search-field" placeholder="<?php echo $lang['RELATED_7']; ?>" >
                                    <div class="errorField" style="display: none;"><?php echo $lang['ORDER_BS_2']; ?></div>

                                </div>
                                <div class="example-line"><?php echo $lang['ORDER_BS_3']; ?>: <a href="javascript:void(0)" class="example-find" ><?php echo $exampleName ?></a></div>
                                <div class="fastResult"></div>               
                            </div>

                            <div class="product-block">
                                <!-- Здесь будет отображена карточка товара -->
                            </div>

                            <form name="orderContent">   
                                <div class="order-history">   
                                </div>
                            </form>

                        </div>
                        <button class="save-button tool-tip-bottom" title="<?php echo $lang['APPLY']; ?>"><span><?php echo $lang['APPLY']; ?></span></button>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Тут начинается  Верстка таблицы заказов -->
        <div class="widget-table-body">

            <div class="widget-table-action">
                <a href="javascript:void(0);" class="add-new-button tool-tip-top" title="<?php echo $lang['T_TIP_ADD_NEW_ORDER']; ?>"><span><?php echo $lang['ADD_NEW_ORDER']; ?></span></a>
                <a href="javascript:void(0);" class="show-filters tool-tip-top" title="<?php echo $lang['T_TIP_SHOW_FILTER']; ?>"><span><?php echo $lang['FILTER']; ?></span></a>
                <a href="javascript:void(0);" class="show-property-order tool-tip-top" title="<?php echo $lang['T_TIP_SHOW_PROPERTY_ORDER']; ?>"><span><?php echo $lang['SHOW_PROPERTY_ORDER']; ?></span></a>

                <div class="filter">
                    <span class="last-items"><?php echo $lang['SHOW_COUNT_ORDER']; ?></span>
                    <select class="last-items-dropdown countPrintRowsOrder">
                        <?php
                        foreach (array(10, 20, 30, 50, 100) as $value) {
                          $selected = '';
                          if ($value == $countPrintRowsOrder) {
                            $selected = 'selected="selected"';
                          }
                          echo '<option value="'.$value.'" '.$selected.' >'.$value.'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>

            <div class="filter-container" <?php
            if ($displayFilter) {
              echo "style='display:block'";
            }
            ?>>
                     <?php echo $filter ?>
                <div class="block-stat-info-order">
                    <span>Найдено заказов: <strong><?php echo  $itemsCount; ?> шт.</strong></span>
                    <span>Общая сумма заказов: <strong><?php echo  MG::priceCourse($totalSumm).' '.MG::getSetting('currency'); ?></strong></span>
                </div>
                <div class="clear"></div>
            </div>

            <div class="property-order-container">    
                <h2><?php echo $lang['OREDER_LOCALE_24'] ?>:</h2>
                <form name="requisites" method="POST">
                    <ul class="requisites-list">
                        <li><span><?php echo $lang['OREDER_LOCALE_9'] ?>:</span><input type="text" name="nameyur" value="<?php echo htmlspecialchars($propertyOrder["nameyur"]) ?>"></li>
                        <li><span><?php echo $lang['OREDER_LOCALE_15'] ?>:</span><input type="text" name="adress" value="<?php echo htmlspecialchars($propertyOrder["adress"]) ?>"></li>
                        <li><span><?php echo $lang['OREDER_LOCALE_16'] ?>:</span><input type="text" name="inn" value="<?php echo htmlspecialchars($propertyOrder["inn"]) ?>"></li>
                        <li><span><?php echo $lang['OREDER_LOCALE_17'] ?>:</span><input type="text" name="kpp" value="<?php echo htmlspecialchars($propertyOrder["kpp"]) ?>"></li>
                        <li><span><?php echo $lang['OREDER_LOCALE_32'] ?>:</span><input type="text" name="ogrn" value="<?php echo htmlspecialchars($propertyOrder["ogrn"]) ?>"></li>               
                        <li><span><?php echo $lang['OREDER_LOCALE_18'] ?>:</span><input type="text" name="bank" value="<?php echo htmlspecialchars($propertyOrder["bank"]) ?>"></li>
                        <li><span><?php echo $lang['OREDER_LOCALE_19'] ?>:</span><input type="text" name="bik" value="<?php echo htmlspecialchars($propertyOrder["bik"]) ?>"></li>
                        <li><span><?php echo $lang['OREDER_LOCALE_20'] ?>:</span><input type="text" name="ks" value="<?php echo htmlspecialchars($propertyOrder["ks"]) ?>"></li>
                        <li><span><?php echo $lang['OREDER_LOCALE_21'] ?>:</span><input type="text" name="rs" value="<?php echo htmlspecialchars($propertyOrder["rs"]) ?>"></li>
                        <li><span><?php echo $lang['OREDER_LOCALE_25'] ?>:</span><input type="text" name="general" value="<?php echo htmlspecialchars($propertyOrder["general"]) ?>"></li>

                    </ul>

                    <ul class="order-form-img-list">
                        <li><span><?php echo $lang['OREDER_LOCALE_26'] ?>: </span><input type="hidden" name="sing" value="<?php echo $propertyOrder["sing"] ?>">
                            <img class="singPreview" src="<?php echo file_exists($propertyOrder["sing"]) ? SITE.'/'.$propertyOrder["sing"] : SITE.'/uploads/sing.jpg'; ?>"><br/>             
                            <a href="javascript:void(0);" class="upload-sign custom-btn"><span><?php echo $lang["UPLOAD"] ?></span></a>
                        </li>
                        <li><span><?php echo $lang['OREDER_LOCALE_27'] ?>:</span><input type="hidden" name="stamp" value="<?php echo $propertyOrder["stamp"] ?>">                  
                            <img class="stampPreview" src="<?php echo file_exists($propertyOrder["stamp"]) ? SITE.'/'.$propertyOrder["stamp"] : SITE.'/uploads/stamp.jpg'; ?>"><br/>
                            <a href="javascript:void(0);" class="upload-stamp custom-btn"><span><?php echo $lang["UPLOAD"] ?></span></a>
                        </li>
                    </ul>
                    <ul class="nds-list">
                        <li><?php echo $lang['OREDER_LOCALE_28'] ?>: <input  type="text" name="nds" size="2" value="<?php echo $propertyOrder["nds"] ?>"> %</li>
                        <li><?php echo $lang['OREDER_LOCALE_29'] ?>: <input type="checkbox" name="usedsing" value="<?php echo $propertyOrder["usedsing"] ?>" <?php echo $propertyOrder["usedsing"] ? 'checked=cheked' : '' ?>></li>
                        <li><?php echo $lang['OREDER_LOCALE_30'] ?>: <input  type="text" name="prefix" value="<?php echo $propertyOrder["prefix"] ?>"></li>      
                        <li><?php echo $lang['OREDER_LOCALE_31'] ?>: <input  type="text" name="currency" placeholder="рубль,рубля,рублей" value="<?php echo $propertyOrder["currency"] ?>"></li>  
                        <li>
                          <?php echo $lang['DEFAULT_ORDER_STATUS'] ?>:
                          <select name="order_status">
                            <?php foreach($statusList as $id=>$status){?>
                            <option value="<?php echo $id?>" <?php echo($propertyOrder['order_status']==$id)?'selected=selected':''?>><?php echo $status?></option>
                            <?php }?>
                          </select>
                        </li> 
                        <li>
                          <?php echo $lang['DEFAULT_DATE_FILTER'] ?>:
                          <select name="default_date_filter">
                            <?php foreach($dateFilterValues as $value=>$label){?>
                            <option value="<?php echo $value?>" <?php echo($propertyOrder['default_date_filter']==$value)?'selected=selected':''?>><?php echo $label?></option>
                            <?php }?>
                          </select>
                        </li>
                    </ul>
                    <div class="clear"></div>
                </form>
                <div class="clear"></div>
                <a href="javascript:void(0);" class="save-property-order custom-btn"><span><?php echo $lang['SAVE']; ?></span></a>
                <div class="clear"></div>
            </div>

            <div class="main-settings-container">
                <table class="widget-table product-table">
                    <thead>
                        <tr>
                            <th class="checkbox-cell"><input type="checkbox" name="order-check"></th>
                            <th class="id-width">№</th>
                            <th class="order-number">Номер заказа</th>
                            <th>
                                <a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0] == "add_date") ? 'sort-dir-'.$sorterData[3] : 'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0] == "add_date") ? $sorterData[1] * (-1) : 1 ?>" data-field="add_date"><?php echo $lang['ORDER_ADD_DATE']; ?></a>
                            </th>
                            <th>
                                <a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0] == "name_buyer") ? 'sort-dir-'.$sorterData[3] : 'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0] == "name_buyer") ? $sorterData[1] * (-1) : 1 ?>" data-field="name_buyer"><?php echo $lang['ORDER_BUYER']; ?></a>
                            </th>
                            <th>
                                <a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0] == "user_email") ? 'sort-dir-'.$sorterData[3] : 'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0] == "user_email") ? $sorterData[1] * (-1) : 1 ?>" data-field="user_email"><?php echo $lang['ORDER_EMAIL']; ?></a>
                            </th>
                            <th>
                                <a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0] == "delivery_id") ? 'sort-dir-'.$sorterData[3] : 'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0] == "delivery_id") ? $sorterData[1] * (-1) : 1 ?>" data-field="delivery_id"><?php echo $lang['ORDER_DELIVERY']; ?></a>
                            </th>
                            <th>
                                <a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0] == "payment_id") ? 'sort-dir-'.$sorterData[3] : 'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0] == "payment_id") ? $sorterData[1] * (-1) : 1 ?>" data-field="payment_id"><?php echo $lang['ORDER_PAYMENT']; ?></a>
                            </th>
                            <th>
                                <a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0] == "summ") ? 'sort-dir-'.$sorterData[3] : 'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0] == "summ") ? $sorterData[1] * (-1) : 1 ?>" data-field="summ"><?php echo $lang['ORDER_SUMM']; ?></a>
                            </th>              
                            <th>
                                <a href="javascript:void(0);" class="field-sorter <?php echo ($sorterData[0] == "status_id") ? 'sort-dir-'.$sorterData[3] : 'sort-dir-asc' ?>" data-sort="<?php echo ($sorterData[0] == "status_id") ? $sorterData[1] * (-1) : 1 ?>" data-field="status_id"><?php echo $lang['ORDER_STATUS']; ?></a>
                            </th>
                            <th class="actions"><?php echo $lang['ACTIONS']; ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="order-tbody">
                        <?php if ($orders) { ?>            
                          <?php foreach ($orders as $order) { ?>

                            <tr class="" order_id="<?php echo $order['id'] ?>" >
                                <td class="check-align"><input type="checkbox" name="order-check"></td>
                                <td > <?php echo $order['id'] ?></td>
                                <td > <?php echo $order['number']!='' ? $order['number'] : $order['id']; ?></td>
                                <td class="add_date"> <?php echo MG::dateConvert(date('d.m.Y H:i', strtotime($order['add_date']))).' г. в '.date('H:i', strtotime($order['add_date'])); ?></td>
                                <td > <?php echo $order['name_buyer'] ?></td>
                                <td > <?php echo $order['user_email'] ?></td>
                                <td > <?php echo $assocDelivery[$order['delivery_id']] ?></td>
                                <td ><span class="icon-payment-<?php echo $order['payment_id'] ?>"></span> <?php echo $assocPay[$order['payment_id']] ?></td>
                                <td > <?php echo MG::numberFormat(($order['summ']*1 + $order['delivery_cost']*1)) ?> <?php echo MG::getSetting('currency'); ?></td>
                                <td class="statusId id_<?php echo $order['status_id'] ?>">
                                    <span class="<?php echo $assocStatusClass[$order['status_id']] ?>">
                                        <?php echo $lang[$assocStatus[$order['status_id']]] ?>
                                    </span>
                                </td>

                                <td class="actions">
                                    <ul class="action-list">
                                        <li class="see-order" id="<?php echo $order['id'] ?>" data-number="<?php echo $order['number'] != '' ? $order['number'] : $order['id']; ?>"><a class="tool-tip-bottom" href="javascript:void(0);" title="<?php echo $lang['SEE']; ?>"></a></li>
                                        <li class="order-to-csv"><a  data-id="<?php echo $order['id'] ?> " class="tool-tip-bottom" href="javascript:void(0);" title="Сохранить в CSV"></a></li>
                                        <?php
                                        if (empty($order['yur_info'])) {
                                          $textBtnFdf = "квитанцию";
                                        } else
                                          $textBtnFdf = "счет";
                                        ?>
                                        <li class="order-to-pdf"><a data-id="<?php echo $order['id'] ?>" class="tool-tip-bottom" href="javascript:void(0);" title="Сохранить <?php echo $textBtnFdf; ?> в PDF"></a></li>

                                        <li class="order-to-print"><a  data-id="<?php echo $order['id'] ?>" class="tool-tip-bottom" href="javascript:void(0);" title="Печать <?php echo $textBtnFdf; ?>"></a></li>
                                        <li class="clone-row" id="<?php echo $order['id'] ?>"><a title="Клонировать заказ"  class="tool-tip-bottom" href="javascript:void(0);"></a></li>
                                        <li class="delete-order " id="<?php echo $order['id'] ?>" ><a class="tool-tip-bottom" href="javascript:void(0);"  title="<?php echo $lang['DELETE']; ?>"></a></li>
                                    </ul>
                                </td>
                            </tr>

                            <?php
                          }
                        }else {
                          ?>

                          <tr><td colspan="11" class="noneOrders"><?php echo $lang['ORDER_NONE'] ?></td></tr>

                        <?php } ?>

                    </tbody>
                </table>
            </div>      
            <select name="operation" class="order-operation">       
                <option value="status_id_5"><?php echo $lang['ACTION_ORDER_1'] ?></option> 
                <option value="status_id_4"><?php echo $lang['ACTION_ORDER_2'] ?></option> 
                <option value="status_id_3"><?php echo $lang['ACTION_ORDER_3'] ?></option> 
                <option value="status_id_2"><?php echo $lang['ACTION_ORDER_4'] ?></option> 
                <option value="status_id_1"><?php echo $lang['ACTION_ORDER_5'] ?></option> 
                <option value="status_id_0"><?php echo $lang['ACTION_ORDER_6'] ?></option> 
                <option value="delete"><?php echo $lang['ACTION_ORDER_7'] ?></option> 
            </select>
            <a href="javascript:void(0);" class="run-operation custom-btn"><span><?php echo $lang['ACTION_RUN'] ?></span></a>
            <?php echo $pager ?>
            <div class="clear"></div>
        </div>
    </div>
</div>
<script>
  $('.section-order .to-date').datepicker({dateFormat: "dd.mm.yy"});
  $('.section-order .from-date').datepicker({dateFormat: "dd.mm.yy"});
</script>