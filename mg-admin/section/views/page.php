<div class="section-page">
  <div class="widget-table-wrapper">
    <div class="widget-table-title">
      <h4 class="pages-table-icon"><?php echo $lang['TITLE_PAGES']; ?></h4>
        <p class="produc-count">Всего: <strong><?php echo $countPages?></strong> шт.</p>
    </div>

    <!-- Верстка модального окна -->

    <div class="b-modal hidden-form add-category-popup" id="add-page-wrapper">
      <div class="product-table-wrapper">
        <div class="widget-table-title">
          <h4 class="category-table-icon" id="modalTitle"><?php echo $lang['PAGE_MODAL_TITLE']; ?></h4>
          <div class="b-modal_close tool-tip-bottom" title="<?php echo $lang['T_TIP_CLOSE_MODAL']; ?>"></div>
        </div>
        <div class="widget-table-body">
          <div class="add-product-form-wrapper">
            <div class="add-category-form">
              <label><span class="custom-text"><?php echo $lang['PAGE_NAME']; ?>:</span><input type="text" name="title" class="product-name-input tool-tip-right" title="<?php echo $lang['T_TIP_PAGE_NAME']; ?>" ><div class="errorField"><?php echo $lang['ERROR_SPEC_SYMBOL']; ?></div></label>
              <label><span class="custom-text"><?php echo $lang['PAGE_URL']; ?>:</span><input type="text" name="url" class="product-name-input tool-tip-right" title="<?php echo $lang['T_TIP_PAGE_URL']; ?>"><div class="errorField"><?php echo $lang['ERROR_EMPTY']; ?></div></label>
             
              <label><span class="custom-text"><?php echo $lang['PAGE_LOCALE_2']?>:</span><input type="checkbox" name="invisible" class="tool-tip-bottom" title="<?php echo $lang['PAGE_LOCALE_2']?>"></label>

              <div class="category-desc-wrapper">
                <span class="custom-text" style="margin-bottom: 10px;"><?php echo $lang['PAGE_CONTENT']; ?>:</span>
                <div style="background:#FFF">
                  <textarea class="product-desc-field" name="html_content"></textarea>
                </div>
              </div>
              <div class="clear"></div>
              <span class="seo-title"><?php echo $lang['SEO_BLOCK'] ?></span>
              <div class="seo-wrapper">
                <label><span class="custom-text"><?php echo $lang['META_TITLE']; ?>:</span><input type="text" name="meta_title" class="product-name-input meta-data-category tool-tip-bottom" title="<?php echo $lang['T_TIP_META_TITLE']; ?>"></label>
                <label><span class="custom-text"><?php echo $lang['META_KEYWORDS']; ?>:</span><input type="text" name="meta_keywords" class="product-name-input meta-data-category tool-tip-bottom" title="<?php echo $lang['T_TIP_META_KEYWORDS']; ?>"></label>
                <label>
                  <ul class="meta-list">
                    <li><span class="custom-text"><?php echo $lang['META_DESC']; ?>:</span></li>
                    <li><span class="symbol-left"><?php echo $lang['LENGTH_META_DESC']; ?>: <span class="symbol-count"></span></li>
                  </ul>
                  <textarea class="product-meta-field meta-data-category tool-tip-bottom" name="meta_desc" title="<?php echo $lang['T_TIP_META_DESC']; ?>"></textarea>
                </label>
              </div>
              <div class="clear"></div>
              <form action="<?php echo SITE ?>/previewer" id="previewer" method="post" target="_blank" style="display:none">
                <input id="previewContent" type="hidden" name="content" value=""/>
              </form>
              <button class="previewPage custom-btn tool-tip-bottom" title="<?php echo $lang['T_TIP_PREVIEW_PAGE']; ?>"><span><?php echo $lang['PREVIEW']; ?></span></button>
              <button class="save-button tool-tip-bottom" title="<?php echo $lang['T_TIP_SAVE_CAT']; ?>"><span><?php echo $lang['SAVE']; ?></span></button>
              <div class="clear"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Верстка модального окна -->

    <div class="widget-table-body">
      <div class="widget-table-action">
        <a href="javascript:void(0);" class="add-new-button tool-tip-bottom" title="<?php echo $lang['T_TIP_ADD_PAGE']; ?>"><span><?php echo $lang['ADD_PAGE']; ?></span></a>
        <a href="javascript:void(0);" class="check-all-page tool-tip-bottom" title="Выбрать все страницы"></a>
        <a href="javascript:void(0);" class="uncheck-all-page tool-tip-bottom" title="Снять выбор страниц" style="display:none"></a>

      </div>

      <div class="category-tree-field">
        <ul class="edit-category-list" style="display:none">
          <li class="page-li"><span class="cat-title">Название категории</span> <span class="cat-id">[id=101010]</span></li>
          <li><a href="javascript:void(0);" class="edit-sub-cat"><?php echo $lang['EDIT']; ?></a></li>
          <li><a href="javascript:void(0);" class="add-sub-cat"><?php echo $lang['ADD_SUBPAGE']; ?></a></li>
          <li><a href="javascript:void(0);" class="delete-sub-cat"><?php echo $lang['DELETE']; ?></a></li>       
          <li><a href="javascript:void(0);" class="cancel-sub-cat"><?php echo $lang['CANCEL']; ?></a></li>
        </ul>
        <?php if (!empty($pages)): ?>
          <ul class="page-tree">
            <?php echo $pages ?>
          </ul>
        <?php else: ?>	
          <?php echo $lang['PAGE_NONE'] ?>
        <?php endif; ?>
        <div class="clear"></div>

      </div>
         <select name="operation" class="page-operation">
            <option value="invisible_0">Выводить в меню</option>
            <option value="invisible_1">Не выводить в меню</option>
            <option value="delete">Удалить выбранные страницы</option>
        </select>
        <a href="javascript:void(0);" class="run-operation custom-btn"><span><?php echo $lang['ACTION_RUN']?></span></a>
    </div>
  </div>
</div>