<div class="section-<?php echo $pluginName?>">

<!-- Тут начинается Верстка модального окна -->
<div class="b-modal hidden-form slide-editor">
    <div class="custom-table-wrapper"><!-- блок для контента модального окна -->

    <div class="widget-table-title"><!-- Заголовок модального окна -->
        <h4 class="pages-table-icon" id="modalTitle"><?php echo $lang['HEADER_MODAL_ADD'];?></h4><!-- Иконка + Заголовок модального окна -->
        <div class="b-modal_close tool-tip-bottom" title="<?php echo $lang['CLOSE_MODAL'];?>"></div><!-- Кнопка для закрытия окнаа -->
    </div>

        <div class="widget-table-body"><!-- Содержимое окна, управляющие элементы -->
           <ul class="text-list">
               <li>
                   <span class="custom-text">Название:</span>
                   <input type="text" name="nameaction" value=""/>
               </li>
               <li>
                   <span class="custom-text">Тип слайда:</span>
                   <select name="type">
                       <option value="img" selected='selected'>Изображение</option>
                       <option value="html" >HTML код</option>
                   </select>
               </li>
           </ul>
            <div class="block-for-form" >
                <ul class="custom-form-wrapper type-img">
                    <li><span>src =</span> <input type="text" name="src" value=""/> <a href="javascript:void(0);" class='browseImage'>выбрать изображение</a> </li>
                    <li><span>alt =</span> <input type="text" name="alt" value=""/></li>
                    <li><span>title =</span> <input type="text" name="title" value=""/></li>
                    <li><span>href =</span> <input type="text" name="href" value=""/></li>
                </ul>
                <form class="type-html" >
                    <ul class="custom-form-wrapper">
                        <li><span>Контент слайда</span><textarea class="slide-html"> </textarea></li>
                        <li><span>href = </span> <input type="text" name="href" value=""/></li>
                    </ul>
                </form>
            </div>
            <button class="save-button tool-tip-bottom" data-id="" title="<?php echo $lang['SAVE_MODAL'] ?>"><!-- Кннопка действия -->
                <span><?php echo $lang['SAVE_MODAL'] ?></span>
            </button>
            <div class="clear"></div>
        </div>
    </div>
</div>
<!-- Тут заканчивается Верстка модального окна -->
<!-- Тут начинается  Верстка таблицы -->
<div class="widget-table-body">
    <div class="wrapper-slider-setting">
        <div class="widget-table-action base-settings">
            <h3>Настройки вывода слайдера</h3>
            <ul class="list-option">
                <li><span>Ширина:</span> <input type="text" name="width" value="<?php echo $options['width']?>" placeholder="Если пусто, то растянется">px</li>
                <li><span>Высота:</span> <input type="text" name="height" value="<?php echo $options['height']?>" placeholder="Если пусто, то растянется">px</li>
                <li><span>Скорость смены слайдов:</span> <input type="text" name="speed" value="<?php echo $options['speed']?>">ms</li>
                <li><span>Пауза:</span> <input type="text" name="pause" value="<?php echo $options['pause']?>">ms</li>
                <li><span>Способ смены слайдов:</span>
                    <select name="mode">
                        <option value="fade" <?php echo ($options['mode']=="fade")?'selected=selected':'';?> >Исчезновение</option>
                        <option value="horizontal" <?php echo ($options['mode']=="horizontal")?'selected=selected':'';?>>Перелистывание по горизонтали</option>
                        <option value="vertical" <?php echo ($options['mode']=="vertical")?'selected=selected':'';?>>Перелистывание по вертикали</option>
                    </select>
                </li>
                <li><span>Позиционирование :</span>
                    <select name="position">
                        <option value="left" <?php echo ($options['position']=="left")?'selected=selected':'';?> >По левому краю</option>
                        <option value="center" <?php echo ($options['position']=="center")?'selected=selected':'';?>>По центру</option>
                        <option value="right" <?php echo ($options['position']=="right")?'selected=selected':'';?>>По правому краю</option>
                    </select>
                </li>
				<li><span>Заголовок слайдера:</span> <input type="text" name="titleslider" value="<?php echo $options['titleslider']?>"></li>
              
            </ul>		

          
			<?php if($options['nameaction']=="true"){ $checkbox = " value='true' checked=checked ";} else{ $checkbox = " value='false' "; }?>
            <span class="custom-text">Выводить название:</span> <input type="checkbox" class="option-slide" name="nameaction" <?php echo $checkbox?>>
          
			<button class="tool-tip-bottom base-setting-save save-button custom-btn" data-id="" title="<?php echo $lang['SAVE_MODAL'] ?>"><!-- Кнопка действия -->
                <span><?php echo $lang['SAVE_MODAL'] ?></span>
            </button>
            <div class="clear"></div>
        </div>

        <div class="widget-table-action slide-settings">
            <div class="add-new-button tool-tip-bottom" title="<?php echo $lang['ADD_MODAL'];?>">
                <span><?php echo $lang['ADD_MODAL'];?></span>
            </div>
            <div class="clear"></div>
            <div class="slide-settings-table-wrapper">
                <table class="widget-table">
                    <thead>
                    <tr>
                        <th style="width:40px">Слайд  №</th>
                        <th style="width:100px; text-align: center;">Тип</th>
                        <th style="width:100px">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="entity-table-tbody"> 
                    <?php if(empty($entity)): ?>
                        <tr class="no-results">
                            <td colspan="4" align="center"><?php echo $lang['ENTITY_NONE'];?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($entity as $row): ?>
                            <tr data-id="<?php echo $row['id']; ?>">
                                <td><?php echo $row['id']; ?></td>
                                <td class="type">                                  
                                  <?php
                                    if($row['type']=="img"){ 
                                      echo $row['value'];                                  
                                    }else{
                                      echo "<span class='activity-product-true'>".$row['type']."</span>";                                     
                                    }
                                  ?>
                                </td>
                                <td class="actions">
                                    <ul class="action-list"><!-- Действия над записями плагина -->
                                      <li class="edit-row" data-id="<?php echo $row['id'] ?>" data-type="<?php echo $row['type']; ?>"><a class="tool-tip-bottom" href="javascript:void(0);" title="<?php echo $lang['EDIT'];?>"></a></li>
                                      <li class="visible tool-tip-bottom  <?php echo ($row['invisible'])?'active':''?>" data-id="<?php echo $row['id'] ?>" title="<?php echo ($row['invisible'])? $lang['ACT_V_CAT']:$lang['ACT_UNV_CAT'];?>"><a href="javascript:void(0);"></a></li>
                                      <li class="delete-row" data-id="<?php echo $row['id'] ?>"><a class="tool-tip-bottom" href="javascript:void(0);"  title="<?php echo $lang['DELETE'];?>"></a></li>
                                    </ul>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="clear"></div>
        
        
        <div class="widget-table-action slider-admin-preview">
            <h2>Внешний вид слайдера</h2>
            <a href="javascript:void(0);" class='reload-slider custom-btn'><span>Обновить слайдер</span></a>
            <div class="clear"></div>
            [slider-action]
        </div>
        <div class="clear"></div>
        <!-- Блок действий -->
        <?php echo $pagination ?>
            <div class="clear"></div>
        </div>
</div>

<script>
admin.sortable('.entity-table-tbody','slider-action');
</script>




<!-- Element where elFinder will be created (REQUIRED) -->


