<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

?>
<div class="sn-wie-mapping-page user">
    <div class="sn-wie-header">
        <h2><?php echo( __('Add new user import export mapping profile', SN_WIE_SLUG) ); ?></h2>
        <p><?php echo( __('This section allows you to map CSV columns with WooCommerce columns', SN_WIE_SLUG) ); ?></p>
    </div>
    <div class="sn-wie-box-section">
        <?php wc_get_template( 'navigation.php', $args, '', SN_WIE_TEMPLATE_PATH .'/user/' );?>
        <div class="mapping-box">
            <?php wc_get_template( 'buy-now.php', $args, '', SN_WIE_TEMPLATE_PATH .'/' );?>
        </div>
    </div>
</div>
