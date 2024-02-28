<?php
/*
Template Name: Edit Profile
*/


global $THEMEREX_GLOBALS;
$THEMEREX_GLOBALS['blog_streampage'] = true;
get_header(); 

?>
<div class="itemscope post_item post_item_single post_featured_default post_format_standard club_list" data-columns="3">
    <div class="woocommerce">
        <form name="" class="" method="post" action="">
            <?php 
            $user_id = $_GET['user_id'];
            
            $address = array(
                'billing_first_name' => get_user_meta($user_id,'billing_first_name',true),
                'billing_last_name'  => get_user_meta($user_id,'billing_last_name',true),
                'billing_company'    => get_user_meta($user_id,'billing_company',true),
                'billing_email'      => get_user_meta($user_id,'billing_email',true),
                'billing_phone'      => get_user_meta($user_id,'billing_phone',true),
                'billing_address_1'  => get_user_meta($user_id,'billing_address_1',true),
                'billing_address_2'  => get_user_meta($user_id,'billing_address_2',true),
                'billing_city'       => get_user_meta($user_id,'billing_city',true),
                'billing_state'      => get_user_meta($user_id,'billing_state',true),
                'billing_postcode'   => get_user_meta($user_id,'billing_postcode',true),
                'billing_country'    => get_user_meta($user_id,'billing_country',true)
            );
            wp_nonce_field( 'update_user_address', '_ship_address_update' );
            $checkout = WC_Checkout::instance();
            $fields = $checkout->get_checkout_fields('billing');
            $user_info = get_userdata($user_id);
            if($address['billing_country'] == '')
                $address['billing_country'] = 'US';
            
            if($address['billing_first_name'] == '')
                $address['billing_first_name'] = $user_info->first_name;

            if($address['billing_last_name'] == '')
                $address['billing_last_name'] = $user_info->last_name;

            if($address['billing_email'] == '')
                $address['billing_email'] = $user_info->user_email;

            foreach ( $fields as $key => $field ) {
                if($key == 'billing_email')
                    $field['custom_attributes'] = array('disabled'=>'disabled');
                woocommerce_form_field( $key, $field, $address[$key] );
            }
            ?>
            <input type="submit" name="save_address" value="Update">
        </form>
    </div>
</div>
<?php
get_footer();
?>