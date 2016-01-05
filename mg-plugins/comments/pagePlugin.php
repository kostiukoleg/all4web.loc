<script type="text/javascript">
includeJS('../mg-plugins/comments/js/comments.js');
</script>
<link rel="stylesheet" href="../mg-plugins/comments/css/style.css" type="text/css" />

<div class="section-comments">
	<!-- Верстка модального окна -->

		<div class="b-modal hidden-form" id="edit-comments-wrapper">
			<div class="product-table-wrapper edit-comment-form">
				<div class="widget-table-title">
          <h4 class="pages-table-icon" id="modalTitle"><?php echo $lang['COMMENTS_MODAL_TITLE'];?></h4>
          <div class="b-modal_close tool-tip-bottom" title="<?php echo $lang['T_TIP_CLOSE_MODAL'];?>"></div>
        </div>
        <div class="widget-table-body">
          <div class="add-product-form-wrapper">
     
              <label><span class="custom-text">Страница:</span>
                <a style="margin-left:17px;" class='commentUrl' href="<?php echo SITE?>/" data-site="<?php echo SITE?>" title="Страница на которой оставлен комментарий"><?php echo SITE?>/</a></label>
          		<label><span class="custom-text">Имя:</span>
          		<input type="text" name="name" value="" class="product-name-input tool-tip-right" title="<?php echo $lang['T_TIP_COMMENTS_NAME'];?>"></label>
          		<label><span class="custom-text">Email:</span>
          		<input type="text" name="email" value="" class="product-name-input tool-tip-right" title="<?php echo $lang['T_TIP_COMMENTS_EMAIL'];?>"></label>
          		<label><span class="custom-text">Статус:</span>
          		<select class="last-items-dropdown tool-tip-right" title="<?php echo $lang['T_TIP_COMMENTS_STATUS'];?>">
          			<option value="0">Не одобрен</option>
          			<option value="1">Одобрен</option>
          		</select></label>
          		<label><span class="custom-text">Комментарий:</span>
          		<textarea name="comment" class="product-desc-field" title="<?php echo $lang['T_TIP_COMMENTS_COMMENT'];?>"></textarea></label>
          		<button class="save-button tool-tip-bottom" title="<?php echo $lang['T_TIP_SAVE_COMMENT'];?>"><span><?php echo $lang['SAVE'];?></span></button>
          		<div class="clear"></div>
          	
          </div>
        </div>
			</div>
		</div>

		<!-- Тут заканчивается Верстка модального окна -->


    <!-- Тут начинается  Верстка таблицы товаров -->

    <div class="widget-table-body">
      <div class="widget-table-action">
      	<div class="filter">
          <span class="last-items"><?php echo $lang['COMMENTS_COUNT'];?></span>
          <select class="last-items-dropdown countPrintRowsPage">
            <?php
            foreach(array(5, 10, 15, 20, 25, 30, 100, 150) as $value){
              $selected = '';
              if($value == $countPrintRowsComments){
                $selected = 'selected="selected"';
              }
              echo '<option value="'.$value.'" '.$selected.' >'.$value.'</option>';
            }
            ?>
          </select>
        </div>
        <div class="clear"></div>
      </div>

      <div class="main-settings-container">
        <table class="widget-table product-table">
          <thead>
            <tr>
              <th class="c-name"><?php echo $lang['COMMENTS_NAME'];?></th>
              <th class="c-email"><?php echo $lang['COMMENTS_EMAIL'];?></th>              
              <th class="c-approved"><?php echo $lang['COMMENTS_APPROVE'];?></th>
              <th class="actions"><?php echo $lang['COMMENTS_ACTIONS'];?></th>
            </tr>
          </thead>
          <tbody class="comments-tbody">
          	<?php if(!empty($comments)){ ?>
          	<?php foreach($comments as $comment): ?>
          	<tr id = "<?php echo $comment['id']; ?>">
	          	<td class="c-name"><?php echo $comment['name']; ?></td>
	          	<td class="c-email"><?php echo $comment['email']; ?></td>	          	
	          	<td class="c-approved"><?php echo $comment['approved'] ? '<span class="approved-comment">Одобрен</span>' : '<span class="n-approved-comment">Не одобрен</span>'; ?></td>
	          	<td class="actions">
	          		<ul class="action-list">
	          			<li class="edit-row" id="<?php echo $comment['id'] ?>"><a class="tool-tip-bottom" href="#" title="<?php echo $lang['EDIT'];?>"></a></li>
                  <li class="delete-order" id="<?php echo $comment['id'] ?>"><a class="tool-tip-bottom" href="#"  title="<?php echo $lang['DELETE'];?>"></a></li>
	          		</ul>
	          	</td>
          	</tr>
          	<?php endforeach; ?>
          	<?php } else{ ?>
						<tr>
							<td colspan="5"><?php echo $lang['NEWS_NONE']; ?></td>
						</tr>
          	<?php } ?>
          </tbody>
        </table>
    	</div>

    	<?php echo $pagination ?>
      <div class="clear"></div>
		</div>
	</div>