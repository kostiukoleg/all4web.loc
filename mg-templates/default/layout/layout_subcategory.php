
<?php if(!empty($data)): ?>
  <ul class="sub-categories">
    <?php foreach($data as $category): ?>          
      <li>
        <?php if(!empty($category['image_url'])): ?>
          <a href="<?php echo SITE.'/'.$category['parent_url'].$category['url']; ?>" class="cat-image">
            <img src="<?php echo SITE.'/'.$category['image_url']; ?>" alt="<?php echo $category['title']; ?>" title="<?php echo $category['title']; ?>">
          </a>
        <?php else: ?>
          <a href="<?php echo SITE.'/'.$category['parent_url'].$category['url']; ?>" class="cat-image">
            <img src="<?php echo SITE.'/uploads/thumbs/70_no-img.jpg' ?>" alt="<?php echo $category['title']; ?>" title="<?php echo $category['title']; ?>">
          </a>
        <?php endif; ?>

        <a href="<?php echo SITE.'/'.$category['parent_url'].$category['url']; ?>" class="sub-cat-name"><?php echo $category['title']; ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
