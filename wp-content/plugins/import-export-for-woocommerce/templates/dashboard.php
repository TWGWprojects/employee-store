<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

?>
<div class="sn-wie-dashboard-page">
    <div class="header">
        <h2><?php echo( __('Import Export For WooCommerce', SN_WIE_SLUG) ); ?></h2>
        <p><?php echo( __('This tool allows you to Import/Export products, orders, customers & coupons data to your store in a CSV file format', SN_WIE_SLUG) ); ?></p>
    </div>
    <div class="dashboard-section">
        <div class="dashboard-box">
            <div class="content">
                <div class="section">
                    <a href="admin.php?page=sn-wie-products&tab=export" class="box">
                        <div class="box-border">
                            <img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/product-icon.png" class="icon" />
                            <div class="count"><?php echo($products['total_count']); ?></div>
                            <h2><?php echo( __('Products', SN_WIE_SLUG) ); ?></h2>
                            <hr />
                            <div class="text"><?php echo($products['mapping_profile_count']); ?> column mapping profiles</div>
                            <div class="text"><?php echo($products['schedule_profile_count']); ?> schedule profiles</div>
                            <div class="text"><?php echo($products['log_count']); ?> logs</div>
                        </div>
                    </a>
                </div>
                <div class="section">
                    <a href="admin.php?page=sn-wie-product-categories&tab=export" class="box">
                        <div class="box-border">
                            <img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/category-icon.png" class="icon" />
                            <div class="count"><?php echo($product_categories['total_count']); ?></div>
                            <h2><?php echo( __('Product Categories', SN_WIE_SLUG) ); ?></h2>
                            <hr />
                            <div class="text"><?php echo($product_categories['mapping_profile_count']); ?> column mapping profiles</div>
                            <div class="text"><?php echo($product_categories['schedule_profile_count']); ?> schedule profiles</div>
                            <div class="text"><?php echo($product_categories['log_count']); ?> logs</div>
                        </div>
                    </a>
                </div>
                <div class="section">
                    <a href="admin.php?page=sn-wie-product-reviews&tab=export" class="box">
                        <div class="box-border">
                            <img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/review-icon.png" class="icon" />
                            <div class="count"><?php echo($product_reviews['total_count']); ?></div>
                            <h2><?php echo( __('Product Reviews', SN_WIE_SLUG) ); ?></h2>
                            <hr />
                            <div class="text"><?php echo($product_reviews['mapping_profile_count']); ?> column mapping profiles</div>
                            <div class="text"><?php echo($product_reviews['schedule_profile_count']); ?> schedule profiles</div>
                            <div class="text"><?php echo($product_reviews['log_count']); ?> logs</div>
                        </div>
                    </a>
                </div>
                <div class="section">
                    <a href="admin.php?page=sn-wie-orders&tab=export" class="box">
                        <div class="box-border">
                            <img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/order-icon.png" class="icon" />
                            <div class="count"><?php echo($orders['total_count']); ?></div>
                            <h2><?php echo( __('Orders', SN_WIE_SLUG) ); ?></h2>
                            <hr />
                            <div class="text"><?php echo($orders['mapping_profile_count']); ?> column mapping profiles</div>
                            <div class="text"><?php echo($orders['schedule_profile_count']); ?> schedule profiles</div>
                            <div class="text"><?php echo($orders['log_count']); ?> logs</div>
                        </div>
                    </a>
                </div>
                <div class="section">
                    <a href="admin.php?page=sn-wie-users&tab=export" class="box">
                        <div class="box-border">
                            <img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/user-icon.png" class="icon" />
                            <div class="count"><?php echo($users['total_count']); ?></div>
                            <h2><?php echo( __('Users', SN_WIE_SLUG) ); ?></h2>
                            <hr />
                            <div class="text"><?php echo($users['mapping_profile_count']); ?> column mapping profiles</div>
                            <div class="text"><?php echo($users['schedule_profile_count']); ?> schedule profiles</div>
                            <div class="text"><?php echo($users['log_count']); ?> logs</div>
                        </div>
                    </a>
                </div>
                <div class="section">
                    <a href="admin.php?page=sn-wie-coupons&tab=export" class="box">
                        <div class="box-border">
                            <img src="<?php echo(SN_WIE_ASSET_URL) ?>/images/coupon-icon.png" class="icon" />
                            <div class="count"><?php echo($coupons['total_count']); ?></div>
                            <h2><?php echo( __('Coupons', SN_WIE_SLUG) ); ?></h2>
                            <hr />
                            <div class="text"><?php echo($coupons['mapping_profile_count']); ?> column mapping profiles</div>
                            <div class="text"><?php echo($coupons['schedule_profile_count']); ?> schedule profiles</div>
                            <div class="text"><?php echo($coupons['log_count']); ?> logs</div>
                        </div>
                    </a>
                </div>
                <div class="footer"><a href="<?php echo(SN_WIE_PLUGIN_URL); ?>documentation/woocommerce-import-export/introduction/" target="_blank">Documentation</a> | <a href="<?php echo(SN_WIE_PLUGIN_URL); ?>contact-us" target="_blank">Support</a></div>
            </div>
        </div>
    </div>
</div>