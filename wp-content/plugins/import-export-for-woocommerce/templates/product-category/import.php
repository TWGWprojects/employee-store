<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

?>
<div class="sn-wie-import-page product-category">
    <div class="sn-wie-header">
        <h2><?php echo( __('Import product categories from a CSV file', SN_WIE_SLUG) ); ?></h2>
        <p><?php echo( __('This section allows you to import (or merge) product category data to your store from a CSV file', SN_WIE_SLUG) ); ?></p>
    </div>
    <div class="sn-wie-box-section">
        <?php wc_get_template( 'navigation.php', $args, '', SN_WIE_TEMPLATE_PATH .'/product-category/' );?>
        <div class="import-box">
            <?php wc_get_template( 'buy-now.php', $args, '', SN_WIE_TEMPLATE_PATH .'/' );?>
        </div>
    </div>
</div>
