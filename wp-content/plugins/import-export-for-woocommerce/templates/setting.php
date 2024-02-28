<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

?>
<div class="sn-wie-setting-page">
    <div class="sn-wie-header">
        <h2><?php echo( __('Import Export Settings', SN_WIE_SLUG) ); ?></h2>
        <p><?php echo( __('This section allows you to manage the settings for the plugin', SN_WIE_SLUG) ); ?></p>
    </div>
    <div class="sn-wie-box-section">
        <?php SN_WIE_INIT::show_message(); ?>
        <div class="sn-wie-navigation-box">
            <a href="admin.php?page=sn-wie-products" class="nav-link icon-nav"><span class="icon"><img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/product-icon.png" /></span><?php echo( __('Products', SN_WIE_SLUG) ); ?><span class="badge"><?php echo($products['total_count']); ?></span></a>
            <a href="admin.php?page=sn-wie-product-categories" class="nav-link icon-nav"><span class="icon"><img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/category-icon.png" /></span><?php echo( __('Product Categories', SN_WIE_SLUG) ); ?><span class="badge"><?php echo($product_categories['total_count']); ?></span></a>
            <a href="admin.php?page=sn-wie-product-reviews" class="nav-link icon-nav"><span class="icon"><img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/review-icon.png" /></span><?php echo( __('Product Reviews', SN_WIE_SLUG) ); ?><span class="badge"><?php echo($product_reviews['total_count']); ?></span></a>
            <a href="admin.php?page=sn-wie-orders" class="nav-link icon-nav"><span class="icon"><img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/order-icon.png" /></span><?php echo( __('Orders', SN_WIE_SLUG) ); ?><span class="badge"><?php echo($orders['total_count']); ?></span></a>
            <a href="admin.php?page=sn-wie-users" class="nav-link icon-nav"><span class="icon"><img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/user-icon.png" /></span><?php echo( __('Users', SN_WIE_SLUG) ); ?><span class="badge"><?php echo($users['total_count']); ?></span></a>
            <a href="admin.php?page=sn-wie-coupons" class="nav-link icon-nav"><span class="icon"><img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/coupon-icon.png" /></span><?php echo( __('Coupons', SN_WIE_SLUG) ); ?><span class="badge"><?php echo($coupons['total_count']); ?></span></a>
            <a href="admin.php?page=sn-wie-settings" class="nav-link icon-nav active"><span class="icon"><img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/product-icon.png" /></span><?php echo( __('Setting', SN_WIE_SLUG) ); ?></a>
            <div class="support-links">
                <a href="<?php echo( SN_WIE_PLUGIN_URL ); ?>documentation/woocommerce-import-export/setting" target="_blank"><?php echo( __('Documentation', SN_WIE_SLUG) ); ?></a> | <a href="<?php echo( SN_WIE_PLUGIN_URL ); ?>contact-us" target="_blank"><?php echo( __('Support', SN_WIE_SLUG) ); ?></a>
            </div>
        </div>
        <div class="setting-box">
            <form id="form_setting" action="<?php echo(admin_url('admin-post.php')) ?>" method="post" class="sn-wie-form setting-form">
                <input type="hidden" id="action" name="action" value="sn_wie_update_setting" />
                <div class="input-group">
                    <label class="control-label"><?php echo( __('Import records per request', SN_WIE_SLUG) ); ?></label>
                    <input type="number" id="import_record_per_request" name="import_record_per_request" class="form-control" value="<?php echo(get_option('sn_wie_import_record_per_request')) ?>" />
                    <div class="input-hint"><?php echo( __('Default Value', SN_WIE_SLUG) ); ?>: <?php echo( $options['import_record_per_request'] ) ?></div>
                </div>
                <div class="input-group">
                    <label class="control-label"><?php echo( __('Export Records Per Request', SN_WIE_SLUG) ); ?></label>
                    <input type="number" id="export_record_per_request" name="export_record_per_request" class="form-control" value="<?php echo(get_option('sn_wie_export_record_per_request')) ?>" />
                    <div class="input-hint"><?php echo( __('Default Value', SN_WIE_SLUG) ); ?>: <?php echo( $options['export_record_per_request'] ) ?></div>
                </div>
                <div class="input-group">
                    <label class="control-label"><?php echo( __('CSV field separator', SN_WIE_SLUG) ); ?></label>
                    <input type="text" id="field_separator" name="field_separator" class="form-control" value="<?php echo(get_option('sn_wie_field_separator')) ?>" />
                    <div class="input-hint"><?php echo( __('Default Value', SN_WIE_SLUG) ); ?>: (<?php echo( $options['field_separator'] ) ?>)</div>
                </div>
            </form>
            <div class="footer">
                <button type="submit" class="button button-primary btn-submit" data-form="form_setting"><?php echo( __('Save', SN_WIE_SLUG) ); ?></button>
            </div>
        </div>
    </div>
</div>
