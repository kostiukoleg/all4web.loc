<style>
    .mg-main-menu {
        position:relative;
        height: 60px;
        margin: 10px auto;
        background: rgb(250, 250, 250);
        background: -moz-linear-gradient(90deg, rgb(250, 250, 250) 20%, rgb(240, 240, 240) 70%);
        background: -webkit-linear-gradient(90deg, rgb(250, 250, 250) 20%, rgb(240, 240, 240) 70%);
        background: -o-linear-gradient(90deg, rgb(250, 250, 250) 20%, rgb(240, 240, 240) 70%);
        background: -ms-linear-gradient(90deg, rgb(250, 250, 250) 20%, rgb(240, 240, 240) 70%);
        background: linear-gradient(180deg, rgb(250, 250, 250) 20%, rgb(240, 240, 240) 70%);
         box-shadow:         0px 1px 3px rgba(50, 50, 50, 0.5);
        border-radius: 2px;
        padding:0;
        z-index: 60;
    }

    .mg-main-menu li {
        float: left;
        list-style: none;
        height:60px;
        z-index: 60;
    }

    .mg-main-menu li + li{
        border-left: 1px solid #fff;
    }

    .mg-main-menu li + li a{
        border-left: 1px solid #e9e9e8;
    }

    .mg-main-menu li a {
        color: #303030;
        display: block;
        float:left;
        text-align:center;
        font-size: 12px;
        width: 128px;
        font-weight: bold;
        padding: 15px 6px 12px 6px;
        text-decoration: none;
    }

    .mg-main-menu li a:hover {
        color: #000;
    }

    .mg-main-menu li ul{
        display:none;
    }

    .mg-main-menu li:hover ul {
        float:left;
        display:block;
    }

    .mg-main-menu .submenu {
        position: absolute;
        top: 60px;
        background-color: #fff;
        z-index: 50;
        max-width: 100%;
        padding:10px 0 15px 0;
        border: 2px solid #F1F1F1;
        border-radius: 0 0 5px 5px;
        box-shadow: 0 2px 5px rgba(50, 50, 50, 0.5);
        right:0;
        left:0;
    }

    .mg-main-menu li:hover ul li {
        list-style:none;
        width: 245px;
        padding:0;
        height:auto;
        margin:15px 0 0 0;
    }

    .mg-main-menu li:hover li a {
        padding: 6px 10px 6px 14px;
        text-align:left;
        font-size: 13px;
        font-weight:bold;
        width: 148px;
        color:#303030;
        border:none;
    }

    .mg-main-menu li:hover li ul {
        margin: 0;
        height: auto;
    }

    .mg-main-menu li:hover li ul li {
        margin: 0;
        width: 180px;
        height: auto;
    }

    .mg-main-menu li:hover li ul li a {
        padding: 2px 0 6px 39px;
        text-align:left;
        font-size: 13px;
        font-weight: normal;
        color:#0095e2;
        border:none;
    }

    .mg-main-menu .mg-cat-name{
        overflow: hidden;
    }

    .mg-main-menu .mg-cat-img{
        width: 40px;
        float: left;
        margin: 0 2px 0 20px;
        text-align: center;
    }

    .mg-main-menu .mg-cat-img img{
        max-width: 100%;
        height: auto;
    }

    .mg-main-menu li:hover li a:hover{
        text-decoration:underline;
    }

</style>



<ul class="mg-main-menu">
  <?php foreach ($data['categories'] as $category): ?>
  <?php if ($category['invisible'] == "1") { continue;} ?>

  <?php if (SITE.URL::getClearUri() === $category['link']) {
    $active = 'active';
  } else {
    $active = '';
  } ?>
  
  <?php if (isset($category['child'])): ?>
  <?php /** если все вложенные категории неактивны, то не создаем вложенный список UL */ $slider = 'slider'; $noUl = 1; foreach($category['child'] as $categoryLevel1){$noUl *= $categoryLevel1['invisible']; } if($noUl){$slider='';}?>
	 
  <li class="<?php echo $active ?>">      
    <a href="<?php echo $category['link']; ?>">     
      <?php echo MG::contextEditor('category', $category['title'], $category["id"], "category"); ?>
    </a>
    
    <?php  if($noUl){$slider=''; continue;} ?>         
    <ul class="submenu">
	  
      <?php foreach ($category['child'] as $categoryLevel1): ?>	 
      <?php if ($categoryLevel1['invisible'] == "1") { continue; } ?>
      
      <?php if (SITE.URL::getClearUri() === $categoryLevel1['link']) {
          $active = 'active';
        } else {
          $active = '';
      } ?>
      
      <?php if (isset($categoryLevel1['child'])): ?>
      <?php /** если все вложенные категории неактивны, то не создаем вложенный список UL */ $slider = 'slider'; $noUl = 1; foreach($categoryLevel1['child'] as $categoryLevel2){$noUl *= $categoryLevel2['invisible']; } if($noUl){$slider='';}?>

        <li class="<?php echo $active ?>">			
          <div class="mg-cat-img">
		     <?php if(!empty($categoryLevel1['image_url'])): ?>
               <img src="<?php echo SITE.$categoryLevel1['image_url'];?>">
			 <?php endif; ?>	
          </div>
          <div class="mg-cat-name">
              <a href="<?php echo $categoryLevel1['link']; ?>">
                  <?php echo MG::contextEditor('category', $categoryLevel1['title'], $categoryLevel1["id"], "category"); ?>
              </a>
          </div>
          
          <?php  if($noUl){$slider=''; continue;} ?>       
          <ul>              
            <?php foreach ($categoryLevel1['child'] as $categoryLevel2): ?>	      
            <?php if ($categoryLevel2['invisible'] == "1") {
              continue;
            } ?>
              <?php if (SITE.URL::getClearUri() === $categoryLevel2['link']) {
                $active = 'active';
              } else {
                $active = '';
              } ?>
            <?php if (isset($categoryLevel2['child'])): ?>
            <?php /** если все вложенные категории неактивны, то не создаем вложенный список UL */ $slider = 'slider'; $noUl = 1; foreach($categoryLevel2['child'] as $categoryLevel3){$noUl *= $categoryLevel3['invisible']; } if($noUl){$slider='';}?>

            <li class="<?php echo $active ?>">
              <a href="<?php echo $categoryLevel2['link']; ?>">               
                  <?php echo MG::contextEditor('category', $categoryLevel2['title'], $categoryLevel2["id"], "category"); ?>
              </a>  
                  <?php  if($noUl){$slider=''; continue;} ?>       
            </li>
          
          <?php else: ?>
          
          <li class="<?php echo $active ?>">		    
            <a href="<?php echo $categoryLevel2['link']; ?>">         
               <?php echo MG::contextEditor('category', $categoryLevel2['title'], $categoryLevel2["id"], "category"); ?>
            </a>
          </li>
          <?php endif; ?>          
          
          <?php endforeach; ?>
          </ul>
        </li>
        
        <?php else: ?>
        <li class="<?php echo $active ?>">		
		  <div class="mg-cat-img">              
			 <?php if(!empty($categoryLevel1['image_url'])): ?>
               <img src="<?php echo SITE.$categoryLevel1['image_url'];?>">
			 <?php endif; ?>	
		  </div>
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