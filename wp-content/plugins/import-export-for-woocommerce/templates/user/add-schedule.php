<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

?>
<div class="sn-wie-schedule-page user">
    <div class="sn-wie-header">
        <h2><?php echo( __('Add new user import export schedule profile', SN_WIE_SLUG) ); ?></h2>
        <p><?php echo( __('This section allows you to schedule Import Export of CSV file automatically', SN_WIE_SLUG) ); ?></p>
    </div>
    <div class="sn-wie-box-section">
        <?php wc_get_template( 'navigation.php', $args, '', SN_WIE_TEMPLATE_PATH .'/user/' );?>
        <div class="schedule-box">
            <?php wc_get_template( 'buy-now.php', $args, '', SN_WIE_TEMPLATE_PATH .'/' );?>
        </div>
    </div>
</div>
