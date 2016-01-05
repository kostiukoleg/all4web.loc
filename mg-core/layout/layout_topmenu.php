<ul class="top-menu-list">
<?php foreach($data['pages'] as $page):?> 
  <?php if($page['invisible']=="1"){continue;}?>
  <?php if(URL::getUrl()==$page['link']||URL::getUrl()==$page['link'].'/'){$active = 'active';} else {$active = '';}?>
	<?php if(isset($page['child'])):?>
    <?php /** если все вложенные страницы неактивны, то не создаем вложенный список UL */ $slider = 'slider'; $noUl = 1; foreach($page['child'] as $pageLevel1){$noUl *= $pageLevel1['invisible']; } if($noUl){$slider='';}?>
	  <li class="<?php echo $slider?> <?php echo $active?>">
          <div class="slider_btn "></div>
	    <a href="<?php echo $page['link']; ?>">
        <span>
          <?php echo MG::contextEditor('page', $page['title'], $page["id"], "page"); ?>
        </span>
      </a>      
     	<?php  if($noUl){$slider=''; continue;} ?>     
      
      <ul class="sub_menu" style="display:none">
		  <?php foreach($page['child'] as $pageLevel1):?>	 
        <?php if($pageLevel1['invisible']=="1"){continue;}?>
		   	<?php if(isset($pageLevel1['child'])):?>
         <?php /** если все вложенные страницы неактивны, то не создаем вложенный список UL */ $slider = 'slider'; $noUl = 1; foreach($pageLevel1['child'] as $pageLevel2){$noUl *= $pageLevel2['invisible']; } if($noUl){$slider='';}?>
	 
				  <li class="<?php echo $slider?> <?php echo $active?>">
                      <div class="slider_btn "></div>
					<a href="<?php echo $pageLevel1['link']; ?>">
            <span>
              <?php echo MG::contextEditor('page', $pageLevel1['title'], $pageLevel1["id"], "page"); ?>
            </span>
          </a>
            <?php  if($noUl){$slider=''; continue;} ?>  
            
					  <ul class="sub_menu" style="display:none">
					  <?php foreach($pageLevel1['child'] as $pageLevel2):?>	 
            <?php if($pageLevel2['invisible']=="1"){continue;}?>
						<li class="<?php echo $active?>">
						  <a href="<?php echo $pageLevel2['link']; ?>">
                <span>
                  <?php echo MG::contextEditor('page', $pageLevel2['title'], $pageLevel2["id"], "page");?>
                </span>
              </a>
						</li>
					  <?php endforeach;?>
					  </ul>
            
				  </li>
				<?php else:?>
				<li class="<?php echo $active?>">
				  <a href="<?php echo $pageLevel1['link']; ?>">
            <span>
              <?php echo MG::contextEditor('page', $pageLevel1['title'], $pageLevel1["id"], "page");  ?>
            </span>
          </a>
				</li>
				<?php endif;?>		
	      <?php endforeach;?>
		  </ul>
	  </li>
	<?php else:?>
	<li class="<?php echo $active?>">
	  <a href="<?php echo $page['link']; ?>">
      <span><?php echo MG::contextEditor('page', $page['title'], $page["id"], "page"); ?></span>
    </a>
	</li>
	<?php endif;?>
<?php endforeach;?>
</ul>