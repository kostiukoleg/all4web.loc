
<ul class="cat-list">
  
  <?php foreach ($data['categories'] as $category): ?>


  <?php if ($category['invisible'] == "1") { continue;} ?>
  
  <?php if (isset($category['child'])): ?>
  <?php /** если все вложенные категории неактивны, то не создаем вложенный список UL */ $slider = 'slider'; $noUl = 1; foreach($category['child'] as $categoryLevel1){$noUl *= $categoryLevel1['invisible']; } if($noUl){$slider='';}?>
	 
  <li class="<?php echo $slider?>">
    <div class="slider_btn "></div>
    <a href="<?php echo $category['link']; ?>">      
      <span>
      <?php echo MG::contextEditor('category', $category['title'], $category["id"], "category"); ?>
      </span>
      <span><?php echo $category['insideProduct']?'('.$category['insideProduct'].')':''; ?></span>
    </a>
    
    <?php  if($noUl){$slider=''; continue;} ?>         
    <ul class="sub_menu" style="display:none">

      <?php foreach ($category['child'] as $categoryLevel1): ?>	 
      <?php if ($categoryLevel1['invisible'] == "1") { continue; } ?>
      
      <?php if (isset($categoryLevel1['child'])): ?>
      <?php /** если все вложенные категории неактивны, то не создаем вложенный список UL */ $slider = 'slider'; $noUl = 1; foreach($categoryLevel1['child'] as $categoryLevel2){$noUl *= $categoryLevel2['invisible']; } if($noUl){$slider='';}?>

        <li class="<?php echo $slider?>">
          <div class="slider_btn "></div>
          <a href="<?php echo $categoryLevel1['link']; ?>">
            <span>
                  <?php echo MG::contextEditor('category', $categoryLevel1['title'], $categoryLevel1["id"], "category"); ?>
            </span>
			<span><?php echo $categoryLevel1['insideProduct']?'('.$categoryLevel1['insideProduct'].')':''; ?></span>         
          </a>
          
          <?php  if($noUl){$slider=''; continue;} ?>       
          <ul class="sub_menu" style="display:none">              
            <?php foreach ($categoryLevel1['child'] as $categoryLevel2): ?>	      
            <?php if ($categoryLevel2['invisible'] == "1") {
              continue;
            } ?>
             
            <?php if (isset($categoryLevel2['child'])): ?>
            <?php /** если все вложенные категории неактивны, то не создаем вложенный список UL */ $slider = 'slider'; $noUl = 1; foreach($categoryLevel2['child'] as $categoryLevel3){$noUl *= $categoryLevel3['invisible']; } if($noUl){$slider='';}?>

            <li>
              <a href="<?php echo $categoryLevel2['link']; ?>">
                <span>
                  <?php echo MG::contextEditor('category', $categoryLevel2['title'], $categoryLevel2["id"], "category"); ?>
                </span>
				<span><?php echo $categoryLevel2['insideProduct']?'('.$categoryLevel2['insideProduct'].')':''; ?></span>
              </a>  
                  <?php  if($noUl){$slider=''; continue;} ?>          
              <ul class="sub_menu" style="display:none">
                
                  <?php foreach ($categoryLevel2['child'] as $categoryLevel3): ?>	  
                  <?php if ($categoryLevel3['invisible'] == "1") { continue; } ?>
                                
                  <?php if (isset($categoryLevel3['child'])): ?>                           
                    <li class="slider">
                      <div class="slider_btn "></div>
                      <a href="<?php echo $categoryLevel3['link']; ?>">
                        <span>
                            <?php echo MG::contextEditor('category', $categoryLevel3['title'], $categoryLevel3["id"], "category"); ?>
                        </span>
						<span><?php echo $categoryLevel3['insideProduct']?'('.$categoryLevel3['insideProduct'].')':''; ?></span>
                      </a>                 
                    </li>
                  <?php else: ?>
                    <li class="<?php echo $active ?>">
                      <a href="<?php echo $categoryLevel3['link']; ?>">
                        <span>
                            <?php echo MG::contextEditor('category', $categoryLevel3['title'], $categoryLevel3["id"], "category"); ?>
                        </span>
						<span><?php echo $categoryLevel3['insideProduct']?'('.$categoryLevel3['insideProduct'].')':''; ?></span>
                    </a>
                    </li>
                  <?php endif; ?>		
                  <?php endforeach; ?>
              </ul>
          </li>
          
          <?php else: ?>
          
          <li class="<?php echo $active ?>">
            <a href="<?php echo $categoryLevel2['link']; ?>">
              <span>
                  <?php echo MG::contextEditor('category', $categoryLevel2['title'], $categoryLevel2["id"], "category"); ?>
              </span>
			  <span><?php echo $categoryLevel2['insideProduct']?'('.$categoryLevel2['insideProduct'].')':''; ?></span>
            </a>
          </li>
          <?php endif; ?>          
          
          <?php endforeach; ?>
          </ul>
        </li>
        
        <?php else: ?>
        <li class="<?php echo $active ?>">
          <a href="<?php echo $categoryLevel1['link']; ?>">
            <span>
               <?php echo MG::contextEditor('category', $categoryLevel1['title'], $categoryLevel1["id"], "category"); ?>
            </span>
			<span><?php echo $categoryLevel1['insideProduct']?'('.$categoryLevel1['insideProduct'].')':''; ?></span>
            </a>
        </li>
        <?php endif; ?>		
        <?php endforeach; ?>
        </ul>
      </li>
      <?php else: ?>
      <li class="<?php echo $active ?>">
        <a href="<?php echo $category['link']; ?>">         
          <span>
            <?php echo MG::contextEditor('category', $category['title'], $category["id"], "category"); ?>
          </span>
		   <span><?php echo $category['insideProduct']?'('.$category['insideProduct'].')':''; ?></span>
        </a>
      </li>
<?php endif; ?>
<?php endforeach; ?>
</ul>