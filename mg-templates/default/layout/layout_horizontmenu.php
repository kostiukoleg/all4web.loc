<div class="mg-main-menu-holder">
  <a href="javascript:void(0);" class="mg-main-menu-toggle"><span class="toggle-icon"></span> Меню</a>
  <div class="centered">
    <ul class="mg-main-menu">
      <?php foreach($data['categories'] as $category): ?>
        <?php if($category['invisible']=="1"){
          continue;
        } ?>

        <?php
        if(SITE.URL::getClearUri()===$category['link']){
          $active = 'active';
        }
        else{
          $active = '';
        }
        ?>

        <?php if(isset($category['child'])): ?>
        <?php /** если все вложенные категории неактивны, то не создаем вложенный список UL */ $slider = 'slider';
        $noUl = 1;
        foreach($category['child'] as $categoryLevel1){
          $noUl *= $categoryLevel1['invisible'];
        } if($noUl){
          $slider = '';
        } ?>

            <li class="<?php echo $active ?>">
              <a href="<?php echo $category['link']; ?>">
                <?php echo MG::contextEditor('category', $category['title'], $category["id"], "category"); ?>
              </a>

                <?php if($noUl){
                  $slider = '';
                  continue;
                } ?>
              <ul class="submenu">

              <?php foreach($category['child'] as $categoryLevel1): ?>
                <?php if($categoryLevel1['invisible']=="1"){
                  continue;
                } ?>

                <?php
                if(SITE.URL::getClearUri()===$categoryLevel1['link']){
                  $active = 'active';
                }
                else{
                  $active = '';
                }
                ?>

              <?php if(isset($categoryLevel1['child'])): ?>
                <?php /** если все вложенные категории неактивны, то не создаем вложенный список UL */ $slider = 'slider';
                $noUl = 1;
                foreach($categoryLevel1['child'] as $categoryLevel2){
                  $noUl *= $categoryLevel2['invisible'];
                } if($noUl){
                  $slider = '';
                } ?>

                <li class="<?php echo $active ?>">
                   <?php if(!empty($categoryLevel1['image_url'])): ?>
                    <div class="mg-cat-img">
                      <img src="<?php echo SITE.$categoryLevel1['image_url']; ?>">
                    </div>
                      <?php endif; ?>
                      <div class="mg-cat-desc">
                        <a href="<?php echo $categoryLevel1['link']; ?>">
                        <?php echo MG::contextEditor('category', $categoryLevel1['title'], $categoryLevel1["id"], "category"); ?>
                        </a>
                      </div>

                        <?php if($noUl){
                          $slider = '';
                          continue;
                        } ?>
                      <ul>
                      <?php foreach($categoryLevel1['child'] as $categoryLevel2): ?>
                      <?php
                      if($categoryLevel2['invisible']=="1"){
                        continue;
                      }
                      ?>
                      <?php
                      if(SITE.URL::getClearUri()===$categoryLevel2['link']){
                        $active = 'active';
                      }
                      else{
                        $active = '';
                      }
                      ?>
                        <?php if(isset($categoryLevel2['child'])): ?>
                          <?php /** если все вложенные категории неактивны, то не создаем вложенный список UL */ $slider = 'slider';
                          $noUl = 1;
                          foreach($categoryLevel2['child'] as $categoryLevel3){
                            $noUl *= $categoryLevel3['invisible'];
                          } if($noUl){
                            $slider = '';
                          } ?>

                        <li class="<?php echo $active ?>">
                          <a href="<?php echo $categoryLevel2['link']; ?>">
                             <?php echo MG::contextEditor('category', $categoryLevel2['title'], $categoryLevel2["id"], "category"); ?>
                          </a>
                          <?php echo $categoryLevel2['insideProduct']?'('.$categoryLevel2['insideProduct'].')':''; ?>
                          <?php if($noUl){
                            $slider = '';
                            continue;
                          } ?>
                        </li>

                       <?php else: ?>
                        <li class="<?php echo $active ?>">
                          <a href="<?php echo $categoryLevel2['link']; ?>">
                             <?php echo MG::contextEditor('category', $categoryLevel2['title'], $categoryLevel2["id"], "category"); ?>
                          </a>
                              <?php echo $categoryLevel2['insideProduct']?'('.$categoryLevel2['insideProduct'].')':''; ?>
                        </li>
                        <?php endif; ?>

                      <?php endforeach; ?>
                      </ul>
                </li>

                <?php else: ?>
               <li class="<?php echo $active ?>">
                  <?php if(!empty($categoryLevel1['image_url'])): ?>
                   <div class="mg-cat-img">
                     <img src="<?php echo SITE.$categoryLevel1['image_url']; ?>">
                   </div>
                   <?php endif; ?>
                 <div class="mg-cat-desc">
                   <a href="<?php echo $categoryLevel1['link']; ?>">
                       <?php echo MG::contextEditor('category', $categoryLevel1['title'], $categoryLevel1["id"], "category"); ?>
                   </a>
                 </div>
               </li>
               <?php endif; ?>
             <?php endforeach; ?>
         </ul>
       </li>
       <?php else: ?>
       <li class="<?php echo $active ?>">
         <a href="<?php echo $category['link']; ?>">
            <?php echo MG::contextEditor('category', $category['title'], $category["id"], "category"); ?>
         </a>
       </li>
      <?php endif; ?>
    <?php endforeach; ?>
    </ul>
  </div>
</div>

