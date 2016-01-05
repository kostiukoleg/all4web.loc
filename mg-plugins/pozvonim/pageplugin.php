<!--
Доступны переменные:
  $pluginName - название плагина
  $lang - массив фраз для выбранной локали движка
  $locale - локаль
  $options - набор данного плагина хранимый в записи таблиц mg_setting
  $options - набор данного плагина хранимый в записи таблиц mg_setting
-->
<?php
$disabled = isset($options['id']);
?>
<div id="pz-wrap" class="section-<?php echo $pluginName ?>">

    <div class="widget-table-body">
        <div class="wrapper-entity-setting">
            <div class="widget-table-action base-settings">
                <?php
                if (!$disabled) {
                    ?>
                    <div>
                        <div class="list-option">
                            <div class="pz-block">
                                <h3><?php echo $lang['HEADER_REGISTER'] ?></h3>
                                <ul>
                                    <li>
                                        <span>Email:</span>
                                        <input type="text" name="email" id="email"
                                               value="<?php echo $options['email'] ? $options['email'] : $settings['adminEmail']; ?>">
                                    </li>
                                    <li>
                                        <span><?php echo $lang['FIELD_PHONE'] ?>:</span>
                                        <input type="text" name="phone" value="<?php echo $options['phone']? $options['phone'] : $settings['shopPhone']; ?>">
                                    </li>
                                    <li>
                                        <span><?php echo $lang['FIELD_HOST'] ?>:</span>
                                        <input type="text" name="host" value="<?php echo $options['host']? $options['host']: $_SERVER['HTTP_HOST']; ?>">
                                    </li>
                                    <li>
                                        <h5><?php echo $lang['FIELD_TOKEN_FORMAT'] ?> &nbsp;</h5>
                                        <span><?php echo $lang['FIELD_TOKEN'] ?>:</span>
                                        <input type="text" name="token" value="<?php echo $options['token']; ?>"><br/>
                                        <button data-emsg="<?php echo $lang['RESET_TOKEN_REQUEST'] ?>"
                                                data-rmsg="<?php echo $lang['BAD_EMAIL'] ?>"
                                                class="tool-tip-bottom custom-btn" id="restoreToken"><?php echo $lang['RESTORE_TOKEN'] ?></button>
                                    </li>
                                </ul>
                            </div>
                            <div class=" pz-block">
                                <h3><?php echo $lang['HEADER_USE_CODE'] ?></h3>
                                <textarea cols="38" rows="4" name="code"><?php echo $options['code']; ?></textarea><br/>
                                <a style="padding:2px 10px;margin:2px;" href="http://my.pozvonim.com"
                                   target="_blank"><?php echo $lang['CABINET_LINK_TEXT'] ?></a>

                            </div>
                        </div>
                        <span style="clear:both"></span>
                    </div>
                    <button class="tool-tip-bottom base-setting-save save-button custom-btn" data-id="" title="<?php echo $lang['SAVE_MODAL'] ?>">
                        <span><?php echo $lang['SAVE_MODAL'] ?></span>
                    </button>

                    <?php
                } else {
                    echo '<h3>' . $lang['INSTALLED'] . '</h3>';
                    $url = '#';
                    if (isset($options['id'], $options['token'])) {
                        $url = 'http://appspozvonim.com/moguta/login?' . http_build_query(array(
                                    'id'    => $options['id'],
                                    'token' => md5($options['id'] . $options['token']),
                                )
                            );
                    }

                    ?>
                    <span class="list-option">
                        <input type="hidden" style="display:none" name="reset" value="1"/>
                    </span>
                    <button onclick="confirm('<?php echo $lang['RESET_REQUEST'] ?>')" class="tool-tip-bottom base-setting-save save-button custom-btn"
                            style="float:none" title="<?php echo $lang['RESET_INSTALL'] ?>">
                        <span><?php echo $lang['RESET_INSTALL'] ?></span>
                    </button>
                    <a target="_blank" href="<?php echo $url ?>" title="<?php echo $lang['CONROL_PANEL_LOGIN'] ?>">
                        <span><?php echo $lang['CONROL_PANEL_LOGIN'] ?></span>
                    </a>
                    <?php
                }
                ?>


                <div class="clear"></div>
            </div>
        </div>
    </div>