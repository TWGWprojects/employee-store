<?php SN_WIE_INIT::show_message(); ?>
<div class="sn-wie-navigation-box">
    <a href="admin.php?page=sn-wie-orders&tab=export" class="nav-link <?php echo($page_name=='export'?'active':'') ?>"><?php echo( __('Export', SN_WIE_SLUG) ); ?></a>
    <a href="admin.php?page=sn-wie-orders&tab=import" class="nav-link <?php echo($page_name=='import'?'active':'') ?>"><?php echo( __('Import', SN_WIE_SLUG) ); ?></a>
    <a href="admin.php?page=sn-wie-orders&tab=mapping" class="nav-link <?php echo($page_name=='mapping'?'active':'') ?>"><?php echo( __('Mapping Profiles' , SN_WIE_SLUG) ); ?></a>
    <a href="admin.php?page=sn-wie-orders&tab=schedule" class="nav-link <?php echo($page_name=='schedule'?'active':'') ?>"><?php echo( __('Schedule Profiles', SN_WIE_SLUG) ); ?></a>
    <a href="admin.php?page=sn-wie-orders&tab=log" class="nav-link <?php echo($page_name=='log'?'active':'') ?>"><?php echo( __('Log', SN_WIE_SLUG) ); ?><span class="badge"><?php echo( $log_count ); ?></span></a>
    <a href="admin.php?page=sn-wie-orders&tab=setting" class="nav-link <?php echo($page_name=='setting'?'active':'') ?>"><?php echo( __('Setting', SN_WIE_SLUG) ); ?></a>
    <div class="support-links">
        <a href="<?php echo( SN_WIE_PLUGIN_URL ); ?>documentation/woocommerce-import-export/order-export" target="_blank"><?php echo( __('Documentation', SN_WIE_SLUG) ); ?></a> | <a href="<?php echo( SN_WIE_PLUGIN_URL ); ?>contact-us" target="_blank"><?php echo( __('Support', SN_WIE_SLUG) ); ?></a>
    </div>
</div>
