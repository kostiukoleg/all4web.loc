
<?php //viewData($pluginsList);?>
<div class="widget-table-wrapper">
  <div class="widget-table-title">
    <h4 class="plugins-table-icon"><?php echo $lang['TITLE_PLUGINS']; ?></h4>
  </div>
  <div class="widget-table-body">
    <div class="widget-table-action">
      <form id="newPluginForm" method="post" noengine="true" enctype="multipart/form-data">    
        <div class="type-file">
          <div class="install-plugin"><span><?php echo $lang['PLUG_UPLOAD']; ?></span></div>
          <input type="file" name="addPlugin" id="addPlugin" size="1">
        </div>
        <button type="button" name="checkPluginsUpdate" id="checkPluginsUpdate" class="custom-btn"><span>Проверить обновления</span></button>
      </form>
    </div>
    <div class="main-settings-container">
      <table class="widget-table plugins-tables">
        <thead>
          <tr>
            <th class="plugins-activety"><?php echo $lang['PLUG_ATIVITY']; ?></th>
            <th class="plugins-name"><?php echo $lang['PLUG_NAME']; ?></th>
            <th><?php echo $lang['PLUG_NAME']; ?></th>
            <th class="actions"><?php echo $lang['ACTIONS']; ?></th>
          </tr>
        </thead>

        <tbody>
          <?php if (!empty($pluginsList)) {
            foreach ($pluginsList as $item):
              ?>
              <?php
              $class = 'plugin-settings-off';
              if (PM::isHookInReg($item['folderName'])) {
                $class = 'plugin-settings-on';
              }
              ?>
              <tr id="<?php echo $item['folderName'] ?>" class="<?php echo $class ?>">

                <td class="plugins-active" active="<?php echo $item['Active'] ?>">

                </td>
                <td>
                  <div class="plugins-name-wrapper">
                    <ul class="plugins-author-list">
                      <li class="p-name"><?php echo $item['PluginName'] ?></li>
                      <li>Версия <span class="plugin-version"><?php echo $item['Version'] ? $item['Version'] : '-'; ?></span> </li>
                      <li><?php echo $item['Author'] ? $item['Author'] : ''; ?></li>
                      <li><?php if (!empty($item['PluginURI'])): ?><a href="<?php echo $item['PluginURI'] ?>"><?php echo $lang['PLUG_PAGE']; ?></a><?php endif; ?></li>
                      <?php if (!empty($item['update'])): ?>
                        <li class="new-plugin-version">
                          <?php 
                          echo $lang['PLUGIN_NEW_VERSION']; ?>: <?php echo $item['update']['last_version']; 
                          $desc = '<ul style=\'max-width:600px;\'>';
                          foreach($item['update']['description'] as $version=>$description){
                            $desc .= '<li><b>'.$version.'</b><br />'.$description.'</li>';
                          }
                          $desc .= '</ul>';
                          ?>
                          
                          <div class="about-plugin-update">
                              <a class="tool-tip-right desc-property" href="javascript:void(0);" title="<?php echo $desc;?>">?</a>
                          </div>
                        </li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </td>
                <td class="plugin-desc"><?php echo $item['Description'] ?></td>
                <td class="actions">
                  <ul class="action-list">
                    <?php if ($class !== 'plugin-settings-off') { ?>
                      <li class="plugin-settings-large"><a class="plugSettings tool-tip-bottom" href="javascript:void(0);" title="<?php echo $lang['T_TIP_GOTO_PLUG']; ?>"></a></li>
                    <?php } ?>
                    <?php if (!empty($item['update'])){ ?>
                      <li class="update-plugin"><br><a class="tool-tip-bottom update-plugin-icon" href="javascript:void(0);"  title="<?php echo $lang['PLUGIN_UPDATE_START']; ?>"></a></li>
                    <?php } ?>
                    <li class="delete-order"><a class="tool-tip-bottom" href="javascript:void(0);"  title="<?php echo $lang['DELETE']; ?>"></a></li>
                  </ul>
                </td>
              </tr>

            <?php
            endforeach;
          }else {
            ?>

            <tr class="no-results"><td colspan="4"><?php echo $lang['PLUG_NONE'] ?></td></tr>

<?php } ?>

        </tbody>
      </table>
    </div>
    <div class="clear"></div>
  </div>
</div>
