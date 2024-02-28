<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

?>
<div class="sn-wie-import-page product">
    <div class="sn-wie-header">
        <h2><?php echo( __('Import products from a CSV file', SN_WIE_SLUG) ); ?></h2>
        <p><?php echo( __('This section allows you to import (or merge) product data to your store from a CSV file', SN_WIE_SLUG) ); ?></p>
    </div>
    <div class="sn-wie-box-section">
        <?php wc_get_template( 'navigation.php', $args, '', SN_WIE_TEMPLATE_PATH .'/product/' );?>
        <div class="import-box">
            <form id="form_product_import" action="<?php echo(admin_url('admin-ajax.php')) ?>" method="post" class="sn-wie-form  import-form product">
                <input type="hidden" id="action" name="action" value="sn_wie_upload_product_csv_file" />
                <div class="input-row">
                    <div class="input-group left">
                        <label class="control-label"><?php echo( __('Upload type', SN_WIE_SLUG) ); ?></label>
                        <select id="upload_type" name="upload_type" class="form-control custom-select">
                            <option value="file_upload">File Upload</option>
                            <option value="from_url">From URL</option>
                        </select>
                        <div class="input-hint"><?php echo( __('What is your import file source', SN_WIE_SLUG) ); ?></div>
                    </div>
                    <div class="input-group upload-file right">
                        <label class="control-label"><?php echo( __('Upload file', SN_WIE_SLUG) ); ?></label>
                        <input type="file" id="upload_file" name="upload_file" class="form-control required" />
                        <div class="input-hint"><?php echo( __('Maximum size', SN_WIE_SLUG) ); ?>: <?php echo(ini_get('upload_max_filesize')) ?></div>
                    </div>
                    <div class="input-group import-url right" style="display:none;">
                        <label class="control-label"><?php echo( __('Import URL', SN_WIE_SLUG) ); ?></label>
                        <input type="url" id="upload_url" name="upload_url" class="form-control required url" />
                        <div class="input-hint"><?php echo( __('URL from where the CSV file will be downloaded', SN_WIE_SLUG) ); ?></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="input-group">
                    <label class="control-label"><?php echo( __('Column mapping profile', SN_WIE_SLUG) ); ?></label>
                    <select id="import_columns" name="import_columns" class="form-control custom-select">
                        <option value="all">Default</option>
                        <?php if($mapping_profile_list) {
                            foreach($mapping_profile_list as $profile_id => $profile_detail) {
                                ?>
                                <option value="use_mapping_<?php echo($profile_id) ?>"><?php echo($profile_detail['profile_name']) ?></option>
                                <?php
                            }
                        } ?>
                    </select>
                    <div class="input-hint"><?php echo( __('Default will import all system columns', SN_WIE_SLUG) ); ?></div>
                </div>
                <div class="input-row">
                    <div class="input-group left">
                        <label class="control-label"><?php echo( __('Import operation', SN_WIE_SLUG) ); ?></label>
                        <select id="product_operation" name="product_operation" class="form-control custom-select">
                            <option value="create_new_update_existing"><?php echo( __('Create new product & update existing product', SN_WIE_SLUG) ); ?></option>
                            <option value="create_new_skip_existing"><?php echo( __('Create new product & skip existing product', SN_WIE_SLUG) ); ?></option>
                            <option value="skip_new_update_existing"><?php echo( __('Skip new product & update existing product', SN_WIE_SLUG) ); ?></option>
                        </select>
                    </div>
                    <div class="input-group right">
                        <label class="control-label"><?php echo( __('Search products based on?', SN_WIE_SLUG) ); ?></label>
                        <select id="product_search_type" name="product_search_type" class="form-control custom-select">
                            <option value="SKU">SKU</option>
                            <option value="ID">ID</option>
                        </select>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="input-row">
                    <div class="input-group left">
                        <label class="control-label"><?php echo( __('CSV field separator', SN_WIE_SLUG) ); ?></label>
                        <input type="text" id="field_separator" name="field_separator" class="form-control" />
                        <div class="input-hint"><?php echo( __('Default is', SN_WIE_SLUG) ); ?> (<?php echo( $field_separator ); ?>)</div>
                    </div>
                    <div class="clear"></div>
                </div>
            </form>
            <div class="footer">
                <button type="button" class="button button-primary btn-import"><?php echo( __('Import', SN_WIE_SLUG) ); ?></button>
            </div>
            <div class="import-modal" style="display:none;">
                <div class="import-dialog">
                    <div class="progress-bar-section">
                        <div class="import-export-stats">
                            <span class="percentage">0%</span>
                            <span class="timer">00:00</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <div class="import-msg"><?php echo( __('Importing products, please do not close or refresh the browser!', SN_WIE_SLUG) ); ?></div>
                    </div>
                    <div class="import-status-section" style="display:none;">
                        <div class="status-box">
                            <div class="stat percentage"><div class="caption"><?php echo( __('Status', SN_WIE_SLUG) ); ?>: </div><div class="value"></div></div>
                            <div class="stat time"><div class="caption"><?php echo( __('Export Time', SN_WIE_SLUG) ); ?>: </div><div class="value"></div></div>
                            <div class="stat update-count"><div class="caption"><?php echo( __('Products Updated', SN_WIE_SLUG) ); ?>: </div><div class="value">0</div></div>
                            <div class="stat insert-count"><div class="caption"><?php echo( __('Products Inserted', SN_WIE_SLUG) ); ?>: </div><div class="value">0</div></div>
                            <div class="stat skip-count"><div class="caption"><?php echo( __('Products Skipped', SN_WIE_SLUG) ); ?>: </div><div class="value">0</div></div>
                            <div class="stat fail-count"><div class="caption"><?php echo( __('Products Failed', SN_WIE_SLUG) ); ?>: </div><div class="value">0</div></div>
                        </div>
                        <div class="response-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="id"><?php echo( __('ID', SN_WIE_SLUG) ); ?></th>
                                        <th class="sku"><?php echo( __('SKU', SN_WIE_SLUG) ); ?></th>
                                        <th class="message"><?php echo( __('Message', SN_WIE_SLUG) ); ?></th>
                                        <th class="status"><?php echo( __('Status', SN_WIE_SLUG) ); ?></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="button btn-pause-resume" style="display:none;">Pause</button>
                        <button type="button" class="button btn-close" style="display:none;">Close</button>
                        <a href="edit.php?post_type=product" class="button btn-view-products" target="_blank" style="display:none;"><?php echo( __('View Products', SN_WIE_SLUG) ); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
