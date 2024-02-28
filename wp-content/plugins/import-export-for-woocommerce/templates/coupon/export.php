<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

?>
<div class="sn-wie-export-page coupon">
    <div class="sn-wie-header">
        <h2><?php echo( __('Export coupons to a CSV file', SN_WIE_SLUG) ); ?></h2>
        <p><?php echo( __('This section allows you to generate and download a CSV file containing a list of all coupons', SN_WIE_SLUG) ); ?></p>
    </div>
    <div class="sn-wie-box-section">
        <?php wc_get_template( 'navigation.php', $args, '', SN_WIE_TEMPLATE_PATH .'/coupon/' );?>
        <div class="export-box">
            <form action="<?php echo(admin_url('admin-ajax.php')) ?>" method="post" class="sn-wie-form  export-form coupon">
                <input type="hidden" id="action" name="action" value="sn_wie_create_coupon_csv_file" />
                <div class="input-group">
                    <label class="control-label"><?php echo( __('Column mapping profile', SN_WIE_SLUG) ); ?></label>
                    <select id="export_columns" name="export_columns" class="form-control custom-select">
                        <option value="all">Default</option>
                        <?php if($mapping_profile_list) {
                            foreach($mapping_profile_list as $profile_id => $profile_detail) {
                                ?>
                                <option value="use_mapping_<?php echo($profile_id) ?>"><?php echo($profile_detail['profile_name']) ?></option>
                                <?php
                            }
                        } ?>
                    </select>
                    <div class="input-hint"><?php echo( __('Default will export all system columns', SN_WIE_SLUG) ); ?></div>
                </div>
                <div class="input-group meta-row">
                    <label class="control-label"><?php echo( __('Export custom meta?', SN_WIE_SLUG) ); ?></label>
                    <label class="switch"><input type="checkbox" id="export_meta" name="export_meta" class="form-control" value="1" checked="checked" /><span class="switch-slider"></span></label><?php echo( __('Yes, export all custom meta', SN_WIE_SLUG) ); ?>
                </div>
                <div class="input-row">
                    <div class="input-group left">
                        <label class="control-label"><?php echo( __('CSV field separator', SN_WIE_SLUG) ); ?></label>
                        <input type="text" id="field_separator" name="field_separator" class="form-control" />
                        <div class="input-hint"><?php echo( __('Default is', SN_WIE_SLUG) ); ?> (<?php echo( $field_separator ); ?>)</div>
                    </div>
                    <div class="input-group right">
                        <label class="control-label"><?php echo( __('Download filename', SN_WIE_SLUG) ); ?></label>
                        <input type="text" id="download_filename" name="download_filename" class="form-control" />
                        <div class="input-hint"><?php echo( __('Default will be system generated', SN_WIE_SLUG) ); ?></div>
                    </div>
                    <div class="clear"></div>
                </div>
            </form>

            <div class="footer">
                <button type="button" class="button button-primary btn-export"><?php echo( __('Export', SN_WIE_SLUG) ); ?></button>
            </div>

            <div class="export-modal" style="display:none;">
                <div class="export-dialog">
                    <div class="progress-bar-section">
                        <div class="import-export-stats">
                            <span class="percentage">0%</span>
                            <span class="timer">00:00</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <div class="import-msg"><?php echo( __('Exporting coupons, please do not close or refresh the browser!', SN_WIE_SLUG) ); ?></div>
                    </div>
                    <div class="export-status-section" style="display:none">
                        <div class="status-box">
                            <div class="stat percentage"><div class="caption"><?php echo( __('Status', SN_WIE_SLUG) ); ?>: </div><div class="value"></div></div>
                            <div class="stat time"><div class="caption"><?php echo( __('Export Time', SN_WIE_SLUG) ); ?>: </div><div class="value"></div></div>
                            <div class="stat export-count"><div class="caption"><?php echo( __('Coupons Exported', SN_WIE_SLUG) ); ?>: </div><div class="value">0</div></div>
                            <div class="stat file-name"><div class="caption"><?php echo( __('File Name', SN_WIE_SLUG) ); ?>: </div><div class="value"></div></div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="button btn-pause-resume" style="display:none;">Pause</button>
                        <button type="button" class="button btn-close" style="display:none;">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
