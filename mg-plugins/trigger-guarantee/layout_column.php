<?php echo ('<link rel="stylesheet" href="'.SITE.'/'.self::$path.'/css/font-awesome.min.css"  type="text/css"/>')?>
<div class="mg-trigger-guarantee <?php echo $options['layout'] ? $options['layout'] : 'column' ?>">
    <?php echo $trigger['title'] ? "<h2>".$trigger['title']."</h2>" : "" ?>

    <div class="mg-trigger-column one">
        <?php $count = ceil(count($trigger['elements']) / 2); ?>
        <?php for ($i = 0; $i < $count; $i++) : ?>
          <div class="mg-trigger"
               style="background-color: #<?php echo $options['background'] ?>; height:<?php echo $options['height'] ?>px;">
              <span class="mg-trigger-icon" style="<?php echo $float ?>;">
                  <?php if (stristr($trigger['elements'][$i]['icon'], 'style') === FALSE) : 
                $trigger['elements'][$i]['icon'] = str_replace('>', ' style="'.$style.'>', $trigger['elements'][$i]['icon']);
              else :
                 $trigger['elements'][$i]['icon'] = str_replace('">', $style.'>', $trigger['elements'][$i]['icon']);
              endif; ?>
                  <?php echo $trigger['elements'][$i]['icon'] ?>
              </span>
              <span class="mg-trigger-text">
                  <?php echo $trigger['elements'][$i]['text'] ?>
              </span>
          </div>
        <?php endfor; ?>
    </div>
    <div class="mg-trigger-column last" style="margin-left:51%">
        <?php for ($i = $count; $i < count($trigger['elements']); $i++) : ?>
          <div class="mg-trigger"
               style="background-color: #<?php echo $options['background'] ?>; height:<?php echo $options['height'] ?>px;">
              <span class="mg-trigger-icon" style="<?php echo $float ?>;">
                  <?php if (stristr($trigger['elements'][$i]['icon'], 'style') === FALSE) : 
                $trigger['elements'][$i]['icon'] = str_replace('>', ' style="'.$style.'>', $trigger['elements'][$i]['icon']);
              else :
                 $trigger['elements'][$i]['icon'] = str_replace('">', $style.'>', $trigger['elements'][$i]['icon']);
              endif; ?>
                  <?php echo $trigger['elements'][$i]['icon'] ?>
              </span>
              <span class="mg-trigger-text">
  <?php echo $trigger['elements'][$i]['text'] ?>
              </span>
          </div>
<?php endfor; ?>
    </div>    
</div>
<div class="clear"></div>
