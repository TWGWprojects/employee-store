<?php

require_once(__DIR__.'\soap_api.php');
require_once(__DIR__.'\usps.php');

// Custom css and js incldes
function wicky_child_custom_css() {
	if (!is_admin()) {
        
        // Load the css
		wp_enqueue_style( 'wicky-child-style-template', get_stylesheet_directory_uri().'/css/custom.css', array(), '1.0.0', true); 
        wp_enqueue_script( 'front-js', get_stylesheet_directory_uri() . '/front.js');
        wp_register_script( 'script-jquery-custom', get_stylesheet_directory_uri().'/custom.js', array(), '1.0.0', true );
        wp_enqueue_script('script-jquery-custom');
        
        // Load the script  
        wp_localize_script( 'front-js', 'readmelater_ajax', array( 'ajax_url' => admin_url('admin-ajax.php')) );
       
	}
}
add_action('wp_enqueue_scripts', 'wicky_child_custom_css' ); 


// Add custom css for admin panel side
function enqueue_admin_custom_css(){
    wp_enqueue_style( 'admin-custom', get_stylesheet_directory_uri() . '/admin/css/admin-custom.css' );
}
add_action( 'admin_enqueue_scripts', 'enqueue_admin_custom_css' );




global $invite_table, $roles;

 $roles = ['customer'=>'Customer','wholesalers' => 'Wholesaler', 'brokers'=>'Broker','retailers'=>'Retailer','employee'=>'TWG Employee','club'=>'Club Processing'];

 function ui_new_role() {  
    global $roles;
    //add the new user role
    foreach($roles as $k=>$r){
        add_role(
            $k,
            $r,
            array(
                'read'=> true
            )
        );
    } 
}
add_action('init', 'ui_new_role');

$invite_table  = $wpdb->prefix . 'user_invite_link';
add_action( 'init', 'hide_price_add_cart_not_logged_in' );
function hide_price_add_cart_not_logged_in() {   
    if ( ! is_user_logged_in() ) {      
        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
        //remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
        //remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );   
        //add_action( 'woocommerce_single_product_summary', 'bbloomer_print_login_to_see', 31 );
        //add_action( 'woocommerce_after_shop_loop_item', 'bbloomer_print_login_to_see', 11 );
    }
        remove_action('woocommerce_shop_loop_item_title','woocommerce_template_loop_product_title',10);
        add_action('woocommerce_shop_loop_item_title','woocommerce_loop_product_title',11);
        remove_action('woocommerce_widget_shopping_cart_total', 'woocommerce_widget_shopping_cart_subtotal', 10);
}

function woocommerce_loop_product_title(){
    global $post;
    $product = wc_get_product( $post->ID );
    echo '<h2 class="' . esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ) . '">' . get_custom_title($product) . '</h2>';
    //echo '<h2 class="' . esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ) . '">' . $post->post_excerpt . '</h2>';
}

add_action('admin_menu', 'add_send_invite_menu');

function add_send_invite_menu(){
    global $wpdb, $invite_table;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $invite_table (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		email varchar(55) NOT NULL,
		token varchar(100) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    maybe_create_table($invite_table, $sql);
    add_submenu_page( 'users.php', 'User Invite Send', 'User Invites',
    'manage_options', 'send-user-invite','send_invite_callback');
    add_submenu_page( 'woocommerce', 'Export Order', 'Export Orders',
    'manage_options', 'export-order','export_woo_order');
}

function export_woo_order(){
    ?>
        <h1><?php _e( 'Export Orders', 'wineshop' ); ?></h1>
        <form action="" method="post">        
            <?php wp_nonce_field( 'woo_orders', '_export_woo_order' ); ?>
             
                <label>From : <input type="date" name="expfrom" required></label> 
                <label>To : <input type="date" name="expto" required></label>
                <div class="api-setting-group"></div>
                <div><input type="submit" value="Export" class="button-primary" name="export_woo"></div>
        </form>
    <?php 
}

add_action('init', 'export_order_by_date');

function export_order_by_date(){
    if(isset($_POST['export_woo'])){
        if ( ! isset( $_POST['_export_woo_order'] ) 
        || ! wp_verify_nonce( $_POST['_export_woo_order'], 'woo_orders' ) 
        ) {
            print 'Sorry, your nonce did not verify.';
            exit;
        }else{
            $initial_date = $_POST['expfrom'];
            $final_date = $_POST['expto'];
            $orders = wc_get_orders(array(
                'limit'=>-1,
                'type'=> 'shop_order',
                'date_created'=> $initial_date .'...'. $final_date 
                )
            );
            
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="orders_'.$initial_date.'to'.$final_date.'.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
 
            $file = fopen('php://output', 'w');
 
            fputcsv($file, array('Ship to customer', 'Ship to name', 'Ship to company', 'Ship to Address 1', 'Ship to Address 2', 'Ship to City', 'Ship to State', 'Ship to Country', 'Ship to zip code', 'Request ship date', 'Customer PO', 'Ship From Location', 'Item Number', 'Quantity', 'UOM', 'Carrier'));
 
            //print_r($orders);
            //echo '<pre>';
            foreach ($orders as $post) {
                //print_r($post->data);die;
                $order = wc_get_order( $post->id ); 
                foreach ($order->get_items() as $item_id => $item_data) {
                    $product = $item_data->get_product();
                    fputcsv($file, array('Ship to customer', $post->data['billing']['first_name'].' '.$post->data['billing']['last_name'], $post->data['billing']['company'], $post->data['billing']['address_1'], $post->data['billing']['address_2'], $post->data['billing']['city'], $post->data['billing']['state'], $post->data['billing']['country'], $post->data['billing']['postcode'], $post->data['date_created'], 'Sample_'.$post->id, 'Ship From Location', $product->get_name(), $item_data->get_quantity(), $product->get_attribute('size'), 'Carrier'));
                }
            }
            exit();
        }
        
    } 
}


// Add a new interval of 180 seconds
// See http://codex.wordpress.org/Plugin_API/Filter_Reference/cron_schedules
add_filter( 'cron_schedules', 'add_every_five_minutes' );
function add_every_five_minutes( $schedules ) {
    $schedules['every_five_minutes'] = array(
            'interval'  => 300,
            'display'   => __( 'Every 5 Minutes', 'wineshop' )
    );
    return $schedules;
}

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'check_invite_every_five_minutes' ) ) {
    wp_schedule_event( time(), 'every_five_minutes', 'check_invite_every_five_minutes' );
}

// Hook into that action that'll fire every three minutes
add_action( 'check_invite_every_five_minutes', 'remove_invite_every_five_minutes' );

function remove_invite_every_five_minutes() {
    global $wpdb; 
    $invite_table = $wpdb->prefix . 'user_invite_link';	
    $time = date("Y-m-d H:i:s", strtotime('-48 hours'));
    $user = $wpdb->query( "DELETE FROM $invite_table where token != '' and created_at<'$time'" );

}


/**
 * Display callback for the submenu page.
 */
function wpdocs_set_html_mail_content_type() {
    return 'text/html';
}
function send_invite_callback() { 
    global $wpdb, $invite_table;
    if(isset($_POST['send_invite'])){
        if ( ! isset( $_POST['_register_invite'] ) 
        || ! wp_verify_nonce( $_POST['_register_invite'], 'send_register_invite' ) 
        ) {
        print 'Sorry, your nonce did not verify.';
        exit;
        } else {
            $emails = explode(',' ,$_POST['invite_email']);
            foreach($emails as $email){
                $token = md5($email.time());
                $url = get_permalink( get_page_by_path( 'registration' ) )."?token=".$token;
                if(isset($_POST['invite_email_id'])){
                    $wpdb->update( 
                        $invite_table, 
                        array( 
                            'created_at' => current_time( 'mysql' ), 
                            'token' => $token
                        ), array('id' => $_POST['invite_email_id'])
                    );

                }else{
                    $data = array( 
                        'created_at' => current_time( 'mysql' ), 
                        'email' => $email,
                        'token' => $token);

                    $wpdb->insert( 
                        $invite_table, 
                        $data 
                    );
                }
                add_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
        
                $to = $email;
                $subject = 'TWG Samples Store User Registration';
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $body = 'Click on the link to register on the Samples Store. <a href="'.$url.'">Register</a>';
                //echo $url; die;
                wp_mail( $to, $subject, $body, $headers );
            }
            // Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
            remove_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
            
        }
    }
    $user = $wpdb->get_results( "SELECT * FROM $invite_table where token=''" );
    ?>
    <div class="wrap">
        <h1><?php _e( 'User Invites', 'wineshop' ); ?></h1>
        <h3><?php _e( 'Send Invite', 'wineshop' ); ?></h3>
        <form action="" method="post">        
            <?php wp_nonce_field( 'send_register_invite', '_register_invite' ); ?>   
            <textarea name="invite_email" placeholder="Email" style="width:50%;"></textarea>
            <div><input type="submit" value="Send" name="send_invite"></div>
        </form>
        <h3>Register Request</h3>
        <?php if(count($user)){ ?>
        <table class="wp-list-table widefat fixed striped users">
            <tr>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php foreach($user as $row){
                ?>
                <tr>
                    <td><?=$row->email?></td>
                    <td>
                    <form action="" method="post">
                    <input type="hidden" name="invite_email_id" value="<?=$row->id?>">
                    <input type="hidden" name="invite_email" value="<?=$row->email?>">
                    <?php wp_nonce_field( 'send_register_invite', '_register_invite' ); ?>   
                    <input type="submit" value="Send" name="send_invite">
                    </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <?php }?>
    </div>
    <?php
}
add_shortcode('request_register', 'registration_request_form');
add_shortcode('user_registerform', 'registration_form'); 

function registration_request_form(){
    
    global $wpdb, $invite_table;
    if(isset($_POST['send_invite'])){
        if ( ! isset( $_POST['_register_invite'] ) 
        || ! wp_verify_nonce( $_POST['_register_invite'], 'send_register_invite' ) 
        ) {
        print 'Sorry, your nonce did not verify.';
        exit;
        } else {
            $data = array( 
                'created_at' => current_time( 'mysql' ), 
                'email' => $_POST['invite_email']);
            $wpdb->insert( 
                $invite_table, 
                $data 
            );
            
            add_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
            $regis_email = explode(',', get_option('_registration_request_email', get_bloginfo('admin_email')));
            $to = $regis_email;
            $subject = 'TWG Samples Store User Registration Request';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $body = 'New registration request form email - '.$_POST['invite_email'];
            //echo $url; die;
            if(count($to)>0){
                wp_mail( $to, $subject, $body, $headers );
            }
            // Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
            remove_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
        }
    }
    ob_start();
    ?>
    <h3>Send registration request</h3>
    <form action="" method="post">        
        <?php wp_nonce_field( 'send_register_invite', '_register_invite' ); ?>   
        <input type="email" name="invite_email" placeholder="Email" required="true">
        <input type="submit" value="Send" name="send_invite">
    </form>
<?php 
return ob_get_clean();
}

function registration_validation( $username, $password, $email, $first_name, $last_name, $gl_number )  {
    global $reg_errors;
    $reg_errors = new WP_Error;
    if ( empty( $username ) || empty( $password ) || empty( $email ) || empty( $gl_number ) ) {
        $reg_errors->add('field', 'Required form field is missing');
    }
    if ( 4 > strlen( $username ) ) {
        $reg_errors->add( 'username_length', 'Username too short. At least 4 characters is required' );
    }
    if ( username_exists( $username ) )
        $reg_errors->add('user_name', 'Sorry, that username already exists!');
    
    if ( ! validate_username( $username ) ) {
        $reg_errors->add( 'username_invalid', 'Sorry, the username you entered is not valid' );
    }

    if ( 5 > strlen( $password ) ) {
        $reg_errors->add( 'password', 'Password length must be greater than 5' );
    }

    if ( !is_email( $email ) ) {
        $reg_errors->add( 'email_invalid', 'Email is not valid' );
    }
    if ( email_exists( $email ) ) {
        $reg_errors->add( 'email', 'Email Already in use' );
    }
    if ( ! isset( $_POST['_register_form'] ) || ! wp_verify_nonce( $_POST['_register_form'], 'register_user' ))
        $reg_errors->add( 'nonce', 'Your nonce did not verify' );

    if ( is_wp_error( $reg_errors ) ) {
 	 echo '<div class="form-alert-msgs">';
        foreach ( $reg_errors->get_error_messages() as $error ) {
         
            echo '<div>';
            echo '<strong>ERROR</strong>:';
            echo $error . '<br/>';
            echo '</div>';
             
        }
     	 echo '</div>';
    }
}

function complete_registration() {
    global $reg_errors, $username, $password, $email, $first_name, $last_name, $role, $gl_number, $wpdb, $invite_table;
    if ( 1 > count( $reg_errors->get_error_messages() ) ) {
        $userdata = array(
        'user_login'    =>   $username,
        'user_email'    =>   $email,
        'user_pass'     =>   $password,
        'first_name'    =>   $first_name,
        'last_name'     =>   $last_name,
        'role'          =>   $role
        );
        $user_id = wp_insert_user( $userdata );
        update_user_meta($user_id, '_gl_number', $gl_number);
        $token = $_GET['token'];
        if(isset($_GET['token']) && $_GET['token'] != '' )
            $user = $wpdb->query( "DELETE FROM $invite_table where token='$token'" );
        echo 'Registration complete. Goto <a href="' . get_site_url() . '/wp-login.php">login page</a>.'; 
          exit();
    }
}
function registration_form(){
    
    global $wpdb, $invite_table, $username, $password, $email, $first_name, $last_name, $role, $gl_number;
    if ( isset($_POST['submit'] ) ) {
        registration_validation(
            $_POST['username'],
            $_POST['password'],
            $_POST['email'],
            $_POST['fname'],
            $_POST['lname'],
            $_POST['gl_number']
        );

        $username   =   sanitize_user( $_POST['username'] );
        $password   =   esc_attr( $_POST['password'] );
        $email      =   sanitize_email( $_POST['email'] );
        $first_name =   sanitize_text_field( $_POST['fname'] );
        $last_name  =   sanitize_text_field( $_POST['lname'] );
        $role       =   sanitize_text_field( $_POST['role'] );
        $gl_number       =   sanitize_text_field( $_POST['gl_number'] );

        complete_registration(
            $username,
            $password,
            $email,
            $first_name,
            $last_name,
            $role,
            $gl_number
            );
    }
    $token = $_GET['token'];
    if(isset($_GET['token']) && $_GET['token'] != '' )
        $user = $wpdb->get_results( "SELECT * FROM $invite_table where token='$token'" );
    ob_start();
    if($user){
        $roles = ['customer'=>'Customer','wholesalers' => 'Wholesaler', 'brokers'=>'Broker','retailers'=>'Retailer','employee'=>'TWG Employee','club'=>'Club Processing'];
    ?>
    <form action="" method="post">        
        <?php wp_nonce_field( 'register_user', '_register_form' ); ?> 
        
        <div>
        <label for="firstname">First Name</label>
        <input type="text" name="fname" value="<?php echo isset( $_POST['fname']) ? $first_name : null; ?>">
        </div>
        
        <div>
        <label for="website">Last Name</label>
        <input type="text" name="lname" value="<?php echo isset( $_POST['lname']) ? $last_name : null; ?>">
        </div>  

        <div>
        <label for="username">Username <strong>*</strong></label>
        <input type="text" name="username" value="<?php echo isset( $_POST['username'] ) ? $username : null; ?>">
        </div> 

        <div>
        <label for="gl_number">GL Number <strong>*</strong></label>
        <input type="text" name="gl_number" value="<?php echo isset( $_POST['gl_number'] ) ? $gl_number : null; ?>">
        </div>
        
        <div>
        <label for="firstname">User Type</label>
        <select name="role">
            <?php foreach($roles as $k=>$val){
                echo '<option value="'.$k.'">'.$val.'</option>';
            }?>
        </select> 
        </div>

        <div>
        <label for="password">Password <strong>*</strong></label>
        <input type="password" name="password" value="<?php echo isset( $_POST['password'] ) ? $password : null; ?>">
        </div>
        
        <div>
        <label for="email">Email <strong>*</strong></label>
        <input type="text" name="email" value="<?php echo isset( $_POST['email']) ? $email : null; ?>">
        </div>
        <input type="submit" name="submit" value="Register"/>
    </form>
<?php 
    }
    else{
        echo registration_request_form();
    }
    return ob_get_clean();
}

function custom_gl_number_fields($user){
    if(is_object($user))
        $company = esc_attr( get_the_author_meta( '_gl_number', $user->ID ) );
    else
        $company = null;
    ?>
    <table class="form-table">
        <tr>
            <th><label for="company">GL Number</label></th>
            <td>
                <input type="text" class="regular-text" name="gl_number" value="<?php echo $company; ?>" id="company" /><br />
            </td>
        </tr>
    </table>
<?php
}
add_action( 'show_user_profile', 'custom_gl_number_fields' );
add_action( 'edit_user_profile', 'custom_gl_number_fields' );
add_action( "user_new_form", "custom_gl_number_fields" );

function save_custom_gl_number_fields($user_id){
    # again do this only if you can
    if(!current_user_can('manage_options'))
        return false;

    # save my custom field
    if(isset($_POST['gl_number']) && !empty($_POST['gl_number']))
        update_user_meta($user_id, '_gl_number', $_POST['gl_number']);
}
add_action('user_register', 'save_custom_gl_number_fields');
add_action('profile_update', 'save_custom_gl_number_fields');

function wpdocs_theme_name_scripts() {
    wp_register_script( 'script-jquery-repeater', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js', array(), '1.0.0', true );
    wp_enqueue_script('script-jquery-repeater');
    wp_register_script( 'script-jquery-custom', get_stylesheet_directory_uri().'/custom.js', array(), '1.0.0', true );
    wp_enqueue_script('script-jquery-custom');
}

/**
 * Register a custom menu page.
 */
function register_questions_menu_page(){
   
    add_submenu_page( 'woocommerce', 'Inventory Update By SKU', 'Inventory Update By SKU',
    'manage_options', 'inventory-update-sku','inventory_update_sku');
add_action( 'admin_enqueue_scripts', 'wpdocs_theme_name_scripts' );
}
add_action( 'admin_menu', 'register_questions_menu_page' );
 


global $user_ques;

function get_user_ques(){
    global $user_ques;
    if(is_user_logged_in()){
        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;
        $user_role = $roles[0];
        $ques = get_option('products_qustions');
        if(isset($ques[$user_role])){
            $user_ques = $ques[$user_role];
        }else{
            $user_ques = false;
        }
    }
}

add_action('init', 'get_user_ques');


/**
 * Display input on single product page
 */
function kia_custom_option(){
    global $user_ques;
    if(is_user_logged_in() && $user_ques){
        echo '<div id="contactdiv">
        <form class="form" action="" method="post" id="contact">
        <div class="question_wrap" id="cancel"><span class="question_close">x</span></div>';
        wp_nonce_field( 'users_question', '_questions_form_front' ); 
        if($user_ques){
            foreach($user_ques as $k=>$q){
                if($q['status'] == 'yes'){
                    printf( '<p><label>%s<input name="%s" value="" /></label></p>', __( $q['ques'], 'wineshop' ), 'ques_'.$k );
                }
            }
        }
        echo '<input type="submit" id="send" value="Submit"/>
        </form>
        </div>';
    }
}
//add_action( 'wp_footer', 'kia_custom_option', 9 );

function kia_save_custom_option(){
    global $user_ques;
    if(is_user_logged_in()){
        if($user_ques && wp_verify_nonce( $_POST['_questions_form_front'], 'users_question' )){
            $ans = array();
            foreach($user_ques as $k=>$q){
                if($q['status'] == 'yes'){
                    $name = 'ques_'.$k;
                    if( isset( $_POST[$name] ) && sanitize_text_field( $_POST[$name] ) != '' ){
                        $ans[$name] = $_POST[$name];
                    }
                }
            }
            setcookie('users_answers', serialize($ans), time()+31556926, COOKIEPATH, COOKIE_DOMAIN);
        }
    }
}

add_action('init','kia_save_custom_option');

/**

* Add custom field to the checkout page

*/

//add_action('woocommerce_after_order_notes', 'custom_checkout_field');

function custom_checkout_field($checkout){
    global $user_ques, $roles;
    if(isset($_COOKIE['users_answers'])) {
        $ans = unserialize(stripslashes($_COOKIE['users_answers']));
    }
    $user = wp_get_current_user();
    $user_roles = ( array ) $user->roles;
    $user_role = $user_roles[0];
    echo '<div id="custom_checkout_field">';
    if($user_role == 'club'){
        $clubs = get_posts(
            array(
                'post_type' => 'clubs',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'status',
                        'value' => 'active',
                        'compare' => '='
                    )
                )
            )
        );
        $options = ['' => 'Select club'];      
        if($clubs){
            foreach($clubs as $club){                
                $users = get_field('club_managed_by',$club->ID);
                if(!empty($users) && in_array($user->ID, $users)){
                    $key = "club_".$club->ID;
                    $mem_count = count(get_field('users',$club->ID));
                    $options[$key] = 'Club - '.$club->post_title.'('.$mem_count.' members)';
                }
            }
        }
        echo '<h3>' . __('Please Select') . '</h3>';
        woocommerce_form_field('customer_type', array(  
            'type' => 'select',
            'class' => array('my-field-class form-row-wide') ,
            'id' => 'user_type_select',
            'label' => __('Customer Type') ,
            'placeholder' => __('Select Club') ,
            'required' => false,
            'options' =>  $options  
        ));
    }

    echo '<div id="checkout_ques">';
    if(is_user_logged_in() && $user_ques){
        foreach($user_ques as $k=>$q){
            if($q['status'] == 'yes'){
                $name = 'ques_'.$k;
                $val = isset($ans[$name]) ? $ans[$name] :'';
                woocommerce_form_field($name, array(  
                    'type' => 'text',
                    'class' => array('my-field-class form-row-wide') ,
                    'label' => __($q['ques']) ,
                    'required' => false,
                    'placeholder' => __('') ,
                ) ,$val);
            }
        }
    }

    echo '</div></div>';

}

/**

* Checkout Process

*/

//add_action('woocommerce_checkout_process', 'customised_checkout_field_process');

function customised_checkout_field_process(){
    global $user_ques;
    $user_role = $_POST['customer_type'];
    if($user_role != 'club'){
        $ques = get_option('products_qustions');
        if(isset($ques[$user_role]))
            $user_ques = $ques[$user_role]; 
    }
    // Show an error message if the field is not set.
    if($user_ques){
        foreach($user_ques as $k=>$q){
            if($q['status'] == 'yes'){
                $name = 'ques_'.$k;
                if (!$_POST[$name]) wc_add_notice(__('<strong>'.$q['ques'].'</strong> Please enter value!') , 'error');
            }
        }
    }

}

/**

* Update the value given in custom field

*/

//add_action('woocommerce_checkout_update_order_meta', 'custom_checkout_field_update_order_meta');

function custom_checkout_field_update_order_meta($order_id){
    $_POST['customer_type'];
    $ques = get_option('products_qustions');
    $user_role = $_POST['customer_type'];
    if(isset($ques[$user_role])){
        $user_ques = $ques[$user_role]; 
        $ques_ans = array();
        foreach($user_ques as $k=>$q){
            if($q['status'] == 'yes'){
                $name = 'ques_'.$k;
                if (!empty($_POST[$name])) {
                    $ques_ans[$k] = array('ques' => $q['ques'], 'ans'=>$_POST[$name]) ;
                }
            }
        }
        update_post_meta($order_id, 'user_ques_ans',serialize($ques_ans));
        unset($_COOKIE['users_answers']); 
        setcookie('users_answers', '', time() - ( 15 * 60 ), COOKIEPATH, COOKIE_DOMAIN);
    }

}
/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
    $ques_ans = unserialize( get_post_meta( $order->get_id(), 'user_ques_ans', true ));
    if(!empty($ques_ans)){
        foreach($ques_ans as $k => $val){
            echo '<p><strong>'.__($val['ques']).':</strong> <br/>' . $val['ans'] . '</p>';
        }
    }
}

add_action( 'init', 'custom_taxonomy_Item' );

// Register Custom Taxonomy
function custom_taxonomy_Item()  {
$taxo = array('Brand'=>'Brands', 'Master Code'=>'Master Codes', 'Pack Description' => 'Pack Description','Varietal Flavor Description' => 'Varietal Flavor Description', 'Tier Description' => 'Tier Description', 'Size Description' => 'Size Description');
foreach($taxo as $k=>$v){
    $labels = array(
    'name'                       => $v,
    'singular_name'              => $k,
    'menu_name'                  => $k,
    'all_items'                  => 'All '.$v,
    'parent_item'                => 'Parent '.$k,
    'parent_item_colon'          => 'Parent '.$k.':',
    'new_item_name'              => 'New '.$k.' Name',
    'add_new_item'               => 'Add New '.$k,
    'edit_item'                  => 'Edit '.$k,
    'update_item'                => 'Update '.$k,
    'separate_items_with_commas' => 'Separate '.$k.' with commas',
    'search_items'               => 'Search '.$v,
    'add_or_remove_items'        => 'Add or remove '.$v,
    'choose_from_most_used'      => 'Choose from the most used '.$v,
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        //'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
    );
    $slug = strtolower(str_replace(' ','_',$k));
    register_taxonomy( $slug, 'product', $args );
    register_taxonomy_for_object_type( $slug, 'product', $args );
    }
}

add_action( 'wp_ajax_get_questions', 'get_users_role_ques' );

function get_users_role_ques(){
    global $user_ques;
    $ques = get_option('products_qustions');
    $user_role = $_POST['role'];
    $con = strpos($_POST['role'], 'club');
    if(isset($ques[$user_role])){
        $user_ques = $ques[$user_role]; 
        foreach($ques[$user_role] as $k=>$q){
            if($q['status'] == 'yes'){
                $name = 'ques_'.$k;
                woocommerce_form_field($name, array(  
                    'type' => 'text',
                    'class' => array('my-field-class form-row-wide') ,
                    'label' => __($q['ques']) ,
                    'required' => false,
                    'placeholder' => __('') ,
                ));
            }
        }
    }elseif($con !== false){
        $club = explode('_',$_POST['role']);
        $club_id = $club[1];
        $users = get_field('users',$club_id);
        $status = false;
        $noaddress = array();
        foreach ($users as $user) {
            if(get_user_meta($user['ID'],'billing_address_1',true)==''){
                $status = true;
                $noaddress[] = '<span class="badge"><a target="_blank" href="'.get_permalink(get_page_by_path('edit-profile')).'?user_id='.$user['ID'].'">'.$user['user_firstname']. ' '. $user['user_lastname'].'</a></span>';
            }
        }
        if($status){
            echo '<h6>Few members don\'t have address order for them will not be created.</h6><a href="javascript:void(0)" id="noaddress">View Details</a> <span title="Recheck" style="cursor: pointer;" class="contact_icon icon-spin1" id="recheck_club"></span>';
            echo '<div id="noaddressdiv">
                <div class="form" id="addressdiv">
                <div class="question_wrap" id="canceladd"><span class="question_close">x</span></div>
		<div class="no-address__title"><strong>Please add shipping address for below Club members:</strong></div>';
            echo implode('', $noaddress);
            echo '</div></div>';
            die;
        }
    }
    die;
}

add_filter( 'bulk_actions-edit-shop_order', 'order_export_bulk_actions' );
 
function order_export_bulk_actions( $bulk_array ) {
 
	$bulk_array['order_export'] = 'Export order';
	return $bulk_array;
 
}

add_filter( 'handle_bulk_actions-edit-shop_order', 'order_export_bulk_action_handler', 10, 3 );
 
function order_export_bulk_action_handler( $redirect, $doaction, $object_ids ) {
    if ( $doaction == 'order_export' ) {
 
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="orders.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
 
            $file = fopen('php://output', 'w');
 
            fputcsv($file, array('Ship to customer', 'Ship to name', 'Ship to company', 'Ship to Address 1', 'Ship to Address 2', 'Ship to City', 'Ship to State', 'Ship to Country', 'Ship to zip code', 'Request ship date', 'Customer PO', 'Ship From Location', 'Item Number', 'Quantity', 'UOM', 'Carrier'));
 
            //print_r($orders);
            //echo '<pre>';
		    foreach ( $object_ids as $post_id ) {
                //print_r($post->data);die;
                $order = wc_get_order( $post_id ); 
                foreach ($order->get_items() as $item_id => $item_data) {
                    $product = $item_data->get_product();
                    fputcsv($file, array('Ship to customer', $order->data['billing']['first_name'].' '.$order->data['billing']['last_name'], $order->data['billing']['company'], $order->data['billing']['address_1'], $order->data['billing']['address_2'], $order->data['billing']['city'], $order->data['billing']['state'], $order->data['billing']['country'], $order->data['billing']['postcode'], $order->data['date_created'], 'Sample_'.$post_id, 'Ship From Location', $product->get_name(), $item_data->get_quantity(), $product->get_attribute('size'), 'Carrier'));
                }
                update_post_meta($post_id, '_order_data_exported','Exported');
            }
            exit();
        }
    }

add_filter( 'manage_edit-shop_order_columns', 'exportfunction' );
function exportfunction( $columns ){
    $reordered_columns = array();
    unset($columns['shipping_address']);
    foreach( $columns as $key => $column){
        $reordered_columns[$key] = $column;
        if( $key ==  'order_status' ){
            $reordered_columns['ship_address'] = 'Ship To';
        }
    }
	$reordered_columns['order_country'] = 'Country';
	// $reordered_columns['track_no'] = 'Track No';
	$reordered_columns['order_export'] = 'Exported';
	$reordered_columns['order_sync'] = 'JDE Sync';
	return $reordered_columns;
}

add_action( 'manage_shop_order_posts_custom_column', 'export_populate_columns', 10, 2);
 
function export_populate_columns( $column_name, $id ) {
    // $id is the User ID or taxonomy Term ID
	if( $column_name == 'order_export' ) { // you can use switch()
		$output = get_post_meta($id, '_order_data_exported', true);
    }
    if( $column_name == 'order_sync' ) { // you can use switch()
        $output = get_post_meta($id, '_order_documentnumber', true);
        if($output == 'Error'){
            
            $nonce = wp_create_nonce("resync_order_nonce");
	        $link = admin_url('admin-ajax.php?action=resync_order&post_id='.$id.'&nonce='.$nonce);
            $output = '<a href="'.$link.'">Re-Sync</a>';
        }
    }
	if( $column_name == 'track_no' ) { // you can use switch()
		$track_order = get_post_meta( $id, '_ship_track_numbers', true );
        if($track_order){            
            $track_order = array_unique(unserialize($track_order));
           $output = implode(', ', ($track_order));
        }
    }
	if( $column_name == 'ship_address' ) { // you can use switch()
		$order = $order = wc_get_order( $id );
        $raw_address = $order->get_address( 'billing' );
        $address = WC()->countries->get_formatted_address( $raw_address );
		unset( $raw_address['first_name'], $raw_address['last_name'], $raw_address['company'] );
        $output = '<a target="_blank" href="https://maps.google.com/maps?&q=' . rawurlencode( implode( ', ', $raw_address ) ). '&z=16">' . esc_html( preg_replace( '#<br\s*/?>#i', ', ', $address ) ) . '</a>';
    }
	if( $column_name == 'order_country' ) { // you can use switch()
		$order = $order = wc_get_order( $id );
        $raw_address = $order->get_address( 'billing' );
        $output = ($raw_address['country'] == 'US') ? 'D' : 'I';
    }
	echo $output;
}

// Register Custom Post Type
function club_post_type() {

	$labels = array(
		'name'                  => _x( 'Clubs', 'Post Type General Name', 'wineshop' ),
		'singular_name'         => _x( 'Club', 'Post Type Singular Name', 'wineshop' ),
		'menu_name'             => __( 'Clubs', 'wineshop' ),
		'name_admin_bar'        => __( 'Clubs', 'wineshop' ),
		'archives'              => __( 'Item Archives', 'wineshop' ),
		'attributes'            => __( 'Item Attributes', 'wineshop' ),
		'parent_item_colon'     => __( 'Parent Item:', 'wineshop' ),
		'all_items'             => __( 'All Items', 'wineshop' ),
		'add_new_item'          => __( 'Add New Item', 'wineshop' ),
		'add_new'               => __( 'Add New', 'wineshop' ),
		'new_item'              => __( 'New Item', 'wineshop' ),
		'edit_item'             => __( 'Edit Item', 'wineshop' ),
		'update_item'           => __( 'Update Item', 'wineshop' ),
		'view_item'             => __( 'View Item', 'wineshop' ),
		'view_items'            => __( 'View Items', 'wineshop' ),
		'search_items'          => __( 'Search Item', 'wineshop' ),
		'not_found'             => __( 'Not found', 'wineshop' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'wineshop' ),
		'featured_image'        => __( 'Featured Image', 'wineshop' ),
		'set_featured_image'    => __( 'Set featured image', 'wineshop' ),
		'remove_featured_image' => __( 'Remove featured image', 'wineshop' ),
		'use_featured_image'    => __( 'Use as featured image', 'wineshop' ),
		'insert_into_item'      => __( 'Insert into item', 'wineshop' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'wineshop' ),
		'items_list'            => __( 'Items list', 'wineshop' ),
		'items_list_navigation' => __( 'Items list navigation', 'wineshop' ),
		'filter_items_list'     => __( 'Filter items list', 'wineshop' ),
	);
	$args = array(
		'label'                 => __( 'Club', 'wineshop' ),
		'description'           => __( 'User Clubs', 'wineshop' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail' ),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 15,
		'menu_icon'             => 'dashicons-groups',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'capability_type'       => 'page',
	);
	register_post_type( 'clubs', $args );

}
add_action( 'init', 'club_post_type', 0 );

function wpse_product_thumbnail_size( $size ) {
    
    $size = 'full';
    return $size;
}
add_filter( 'single_product_archive_thumbnail_size', 'wpse_product_thumbnail_size' );

function getCartItemThumbnail( $img, $cart_item ) {

    if ( isset( $cart_item['product_id'] ) ) {
        $product = wc_get_product($cart_item['product_id']);

        if ( $product ) {
            // Return variable product thumbnail instead variation.
            return $product->get_image('full');
        }
    }

    return $img;
}

add_filter( 'woocommerce_cart_item_thumbnail', 'getCartItemThumbnail', 111, 2 );

/*add_filter('woocommerce_create_order','create_custom_orders',10,2);

function create_custom_orders($order_id, $order_obj){
    /*$con = strpos($_POST['customer_type'], 'club');
    if($con !== false){
        $club = explode('_',$_POST['customer_type']);
        $club_id = $club[1];
        $users = get_field('users',$club_id);
        foreach ($users as $user) {
            if(get_user_meta($user['ID'],'billing_address_1',true)!=''){
                $address = array(
                    'first_name' => get_user_meta($user['ID'],'billing_first_name',true),
                    'last_name'  => get_user_meta($user['ID'],'billing_last_name',true),
                    'company'    => get_user_meta($user['ID'],'billing_company',true),
                    'email'      => get_user_meta($user['ID'],'billing_email',true),
                    'phone'      => get_user_meta($user['ID'],'billing_phone',true),
                    'address_1'  => get_user_meta($user['ID'],'billing_address_1',true),
                    'address_2'  => get_user_meta($user['ID'],'billing_address_2',true),
                    'city'       => get_user_meta($user['ID'],'billing_city',true),
                    'state'      => get_user_meta($user['ID'],'billing_state',true),
                    'postcode'   => get_user_meta($user['ID'],'billing_postcode',true),
                    'country'    => get_user_meta($user['ID'],'billing_country',true)
                );
                $cart_hash          = WC()->cart->get_cart_hash();
                $order = new WC_Order();
                $order->set_created_via( 'checkout' );
                $order->set_cart_hash( $cart_hash );
                $order->set_customer_id($user['ID']);
                $order->set_customer_ip_address( WC_Geolocation::get_ip_address() );
                $order->set_customer_user_agent( wc_get_user_agent() );
                $order->set_customer_note( isset( $data['order_comments'] ) ? $data['order_comments'] : '' );
                $order->set_address($address, 'billing');
                $order_obj->create_order_line_items( $order, WC()->cart );
                $order_id = $order->save();
                $order->update_status('processing');
                update_post_meta($order_id, '_wpgdprc', time());
            }
        }
        order_update_cron();
    }*/
    //file_put_contents('logs.txt', json_encode ($_POST).PHP_EOL , FILE_APPEND | LOCK_EX);
    /*if($_POST['add_address_csv_name'] && !empty($_POST['add_address_csv_name']) ){
        $user_id = get_current_user_id();
        $upload_dir = wp_upload_dir();
        $csv_file = $upload_dir['basedir']."/address_csv"."/".$_POST['add_address_csv_name'];
        $file = fopen($csv_file, 'r');
        $i = 0;
        $email=[];
        while (($csv_address = fgetcsv($file)) !== FALSE) {
            
            if($i > 0){
                $duplicate = false; 
                $addr = strtolower($csv_address[5].$csv_address[6].$csv_address[7].$csv_address[8].$csv_address[9].$csv_address[10]) ;     
                if( in_array($addr, $email)){
                    $duplicate = true;
                }else{
                    $email[] = $addr;
                }
                if(!$duplicate){
                    $address = array(
                        'first_name' => $csv_address[0],
                        'last_name'  => $csv_address[1],
                        'company'    => $csv_address[2],
                        'email'      => $csv_address[3],
                        'phone'      => $csv_address[4],
                        'address_1'  => $csv_address[5],
                        'address_2'  => $csv_address[6],
                        'city'       => $csv_address[7],
                        'state'      => $csv_address[8],
                        'postcode'   => $csv_address[9],
                        'country'    => $csv_address[10]
                    );
                    $cart_hash          = WC()->cart->get_cart_hash();
                    $order = new WC_Order();
                    $order->set_created_via( 'checkout' );
                    $order->set_cart_hash( $cart_hash );
                    $order->set_customer_id($user_id);
                    $order->set_customer_ip_address( WC_Geolocation::get_ip_address() );
                    $order->set_customer_user_agent( wc_get_user_agent() );
                    $order->set_customer_note( isset( $data['order_comments'] ) ? $data['order_comments'] : '' );
                    $order->set_address($address, 'billing');
                    $order->set_address( $address, 'shipping' );
                    $order->set_shipping_total( WC()->cart->get_shipping_total() );
                    $order->set_discount_total( WC()->cart->get_discount_total() );
                    $order->set_discount_tax( WC()->cart->get_discount_tax() );
                    $order->set_cart_tax( WC()->cart->get_cart_contents_tax() + WC()->cart->get_fee_tax() );
                    $order->set_shipping_tax( WC()->cart->get_shipping_tax() );
                    $order->set_total( WC()->cart->get_total( 'edit' ) );
                    $order_obj->create_order_line_items( $order, WC()->cart );
                    $order_id = $order->save();
                    do_action('update_custom_order_meta', $order_id);
                    $order->update_status('processing');
                    update_post_meta($order_id, '_wpgdprc', time());
                }
            }
            $i++;
         }
         fclose($file); 
    }    
    order_update_cron();
    return $order_id;
}*/

add_filter('woocommerce_billing_fields','wpb_custom_billing_fields');
// remove some fields from billing form
// ref - https://docs.woothemes.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
function wpb_custom_billing_fields( $fields = array() ) {

    // $con = strpos($_POST['customer_type'], 'club');
    if($_POST['add_address_csv_name'] && !empty($_POST['add_address_csv_name'])){
        unset($fields['billing_first_name']);
        unset($fields['billing_last_name']);
        unset($fields['billing_company']);
        unset($fields['billing_address_1']);
        unset($fields['billing_address_2']);
        unset($fields['billing_state']);
        unset($fields['billing_city']);
        unset($fields['billing_phone']);
        unset($fields['billing_postcode']);
        unset($fields['billing_email']);
        unset($fields['billing_country']);
    }

	return $fields;
}

add_filter( 'cron_schedules', 'add_cron_interval' );
 
function add_cron_interval( $schedules ) {
    $schedules['ten_min'] = array(
        'interval' => 600,
        'display'  => esc_html__( 'Every Ten Minute' ),
    );
 
    return $schedules;
}

add_action('stock_update_events', 'stock_update_cron');

// The action will trigger when someone visits your WordPress site
function my_activation() {
   

    if ( !wp_next_scheduled( 'stock_update_events' ) ) {
        wp_schedule_event( current_time( 'timestamp' ), 'every_five_minutes', 'stock_update_events');
    }
    if ( !wp_next_scheduled( 'order_update_events' ) ) {
        wp_schedule_event( current_time( 'timestamp' ), 'every_five_minutes', 'order_update_events');
    }
    if ( !wp_next_scheduled( 'product_add_update_events' ) ) {
        wp_schedule_event( current_time( 'timestamp' ), 'daily', 'product_add_update_events');
    }
    if ( !wp_next_scheduled( 'order_shipped' ) ) {
        wp_schedule_event( strtotime('16:55:00'), 'daily', 'order_shipped_update_events');
    }
}
add_action('init', 'my_activation');

add_action('wp_ajax_stock_update_cron', 'stock_update_cron');


// add_action('order_update_events', 'order_update_cron');
add_action('woocommerce_checkout_update_order_meta', 'order_update_cron');
function order_update_cron(){    
    global $wpdb;
    // $orders = wc_get_orders( array(
    //     'limit'        => 20, // Query all orders
    //     'orderby'      => 'date',
    //     'order'        => 'DESC',
    //     'return'       => 'ids',
    //     'meta_key'     => '_order_documentnumber', // The postmeta key field	        'meta_query '  => array(
    //     'meta_compare' => 'NOT EXISTS',
    // ));
    $query = "SELECT DISTINCT(".$wpdb->prefix."posts.ID) FROM ".$wpdb->prefix."posts Where ".$wpdb->prefix."posts.post_type = 'shop_order' AND NOT EXISTS ( SELECT * FROM `".$wpdb->prefix."postmeta` WHERE `".$wpdb->prefix."postmeta`.`meta_key` = '_order_documentnumber' AND `".$wpdb->prefix."postmeta`.`post_id`=".$wpdb->prefix."posts.ID ) ORDER BY ".$wpdb->prefix."posts.post_date DESC LIMIT 0, 20";
    $orders = $wpdb->get_results($query);
    if(!empty($orders)){
        foreach($orders as $post){
            $order = wc_get_order($post->ID);
            $products=[];
            $address=[];
            $address['order_id'] = $order->get_id();
            $address['user_gl_number'] = esc_attr( get_the_author_meta( '_gl_number', $order->get_customer_id() ) );
            $address['user_cu_no'] = get_gl_number($order->get_customer_id(), $order->data['billing']['state']);
            $address['user_id'] = $order->get_customer_id();        
            $address['action_type'] = ($order->data['billing']['country'] != 'US') ? 'SJ' :'A';
            $address['order_note'] =  $order->get_customer_note();
            $address['order_delivery_date'] = date("Y-m-d", strtotime( get_post_meta( $order->get_id(), get_option( 'orddd_lite_delivery_date_field_label' ), true )))."T00:00:00";
            $address['billing_first_name'] = $order->data['billing']['first_name'].' '.$order->data['billing']['last_name'];
            $address['billing_last_name'] = $order->data['billing']['last_name'];
            $address['billing_address_1'] = $order->data['billing']['address_1'];
            $address['billing_address_2'] = $order->data['billing']['address_2'];
            $address['billing_city'] =  $order->data['billing']['city'];
            $address['billing_phone'] =  $order->data['billing']['phone'];
            $address['billing_state'] =  $order->data['billing']['state'];
            $address['billing_country'] =  $order->data['billing']['country'];
            $address['billing_postcode'] =  $order->data['billing']['postcode'];
            $address['billing_email'] =  $order->data['billing']['email'];
            $address['billing_date'] =  $order->get_date_created();
            $address['order_total'] = $order->get_total();
            foreach ($order->get_items() as $item_id => $item_data) {
                $product = $item_data->get_product();
                if($product){
                    $item['sku'] = $product->get_sku();
                    $item['qty'] = $item_data->get_quantity();
                    $item['price'] = $item_data->get_total();
                    $products[]=$item;
                }
            }

            $jde_no = get_post_meta($address['order_id'], '_order_documentnumber', true);        
            if(!is_numeric($jde_no)){
                $result = order_api_curl($products, $address);
                if($result['status']){
                    if($address['billing_country'] == 'US'){
                        _ship_compliance_commit($address, $products, $result['document_no']);
                    }
                    update_post_meta($address['order_id'], '_order_documentnumber', $result['document_no']);
                    $logmsg = 'Automatic JDE number created '.$result['document_no'];
                    jde_log($address['order_id'], $logmsg);
                    $order->add_order_note( $logmsg );
                    $order->save();
                }else{
                    update_post_meta($address['order_id'], '_order_documentnumber', 'Error');
                    update_post_meta($address['order_id'], '_order_error_msg', $result['error_msg']);
                }
            }
        }
    }
}

add_action( 'wp_ajax_resync_order', 'resync_order_jde' );

function resync_order_jde(){

    
    if ( !wp_verify_nonce( $_REQUEST['nonce'], "resync_order_nonce")) {
        exit("Woof Woof Woof");
    }
    
    $order = wc_get_order($_REQUEST["post_id"]);
    
    $products=[];
    $address=[];
    $d_date = get_post_meta( $order->get_id(), get_option( 'orddd_lite_delivery_date_field_label' ), true );

    
    if(!$d_date){
        $order_date= $order->get_date_created();
        $d_date = date("Y-m-d", strtotime("$order_date + 7 days"));
        do_action('update_custom_order_meta', $order->get_id());
    }
    $address['order_id'] = $order->get_id();
    $address['user_gl_number'] = esc_attr( get_the_author_meta( '_gl_number', $order->get_customer_id() ) );
    $address['user_cu_no'] = get_gl_number($order->get_customer_id(), $order->data['billing']['state']);
    $address['user_id'] = $order->get_customer_id();
    $address['action_type'] = ($order->data['billing']['country'] != 'US') ? 'SJ' :'A';
    $address['order_note'] =  $order->get_customer_note();
    $address['order_delivery_date'] = date("Y-m-d", strtotime($d_date))."T00:00:00";
    $address['billing_first_name'] = $order->data['billing']['first_name'].' '.$order->data['billing']['last_name'];
    $address['billing_last_name'] = $order->data['billing']['last_name'];
    $address['billing_address_1'] = $order->data['billing']['address_1'];
    $address['billing_address_2'] = $order->data['billing']['address_2'];
    $address['billing_city'] =  $order->data['billing']['city'];
    $address['billing_phone'] =  $order->data['billing']['phone'];
    $address['billing_state'] =  $order->data['billing']['state'];
    $address['billing_country'] =  $order->data['billing']['country'];
    $address['billing_postcode'] =  $order->data['billing']['postcode'];
    $address['billing_email'] =  $order->data['billing']['email'];
    $address['billing_date'] =  $order->get_date_created();
    $address['order_total'] = $order->get_total();
    foreach ($order->get_items() as $item_id => $item_data) {
        $product = $item_data->get_product();
        if($product){
            $item['sku'] = $product->get_sku();
            $item['qty'] = $item_data->get_quantity();
            $item['price'] = $item_data->get_total();
            $products[]=$item;
        }
    }

  
    
    $jde_no = get_post_meta($address['order_id'], '_order_documentnumber', true);   
   
    if(!is_numeric($jde_no)){
       
        $result = _order_jde_resync_check($address['order_id'], $address['user_gl_number']);
        
        if($result == 'Error'){
            $result = order_api_curl($products, $address);
            if($result['status']){
                if($address['billing_country'] == 'US'){
                    _ship_compliance_commit($address, $products, $result['document_no']);
                }
                update_post_meta($address['order_id'], '_order_documentnumber', $result['document_no']);
                $logmsg = 'Automatic JDE number created via resync '.$result['document_no'];
                jde_log($address['order_id'], $logmsg);                
                $order->add_order_note( $logmsg );
                $order->save();
            }else{
                update_post_meta($address['order_id'], '_order_documentnumber', 'Error');
                update_post_meta($address['order_id'], '_order_error_msg', $result['error_msg']);
            }
        }elseif(is_numeric($result)){
            update_post_meta($address['order_id'], '_order_documentnumber', $result);
            $logmsg = 'Automatic JDE number updated via resync '.$result;
            jde_log($address['order_id'], $logmsg);
        }
    }
    header("Location: ".$_SERVER["HTTP_REFERER"]);
    die;
}

function get_gl_number($user_id, $state){
    $cus_no = get_option('_ship_customer_no');
    $user_gl = ($state == 'CA') ? $cus_no['ca']: $cus_no['nonca'];
    return $user_gl;
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'order_sync_field_display_admin_order_meta', 20, 1 );

function order_sync_field_display_admin_order_meta($order){

    $jde_order = get_post_meta( $order->get_id(), '_order_documentnumber', true );

    if($jde_order == 'Error'){
        $jde_order_msg = get_post_meta( $order->get_id(), '_order_error_msg', true );
        echo 'JDE Error : '.(!empty($jde_order_msg) ? $jde_order_msg : 'No response from the server.');
    }
    else{
        echo 'JDE Order No : '. $jde_order;
    }
}


add_action('wp_footer', 'add_loader');

function add_loader(){
    
    echo '<div id="loader" style="display: none;"><img src="'.get_stylesheet_directory_uri(). '/loading-png-gif.gif" width="32px" height="32px"></div>'; 
}

add_action('wp_enqueue_scripts', 'load_template_scripts');
function load_template_scripts(){
    global $post;
    if( is_page() && $post->post_name == 'edit-profile'){
        wp_enqueue_script('selectWoo' );
        wp_enqueue_script('select2');
        wp_enqueue_script('wc-checkout');
        wp_enqueue_script('wc-country-select');
        wp_enqueue_script('wc-address-i18n');
        wp_enqueue_style('select2');
        
        if(wp_verify_nonce( $_POST['_ship_address_update'], 'update_user_address' )){
            $user_id = $_GET['user_id'];
            update_user_meta($user_id,'billing_first_name',$_POST['billing_first_name']);
            update_user_meta($user_id,'billing_last_name',$_POST['billing_last_name']);
            update_user_meta($user_id,'billing_company',$_POST['billing_company']);
            update_user_meta($user_id,'billing_email',$_POST['billing_email']);
            update_user_meta($user_id,'billing_phone',$_POST['billing_phone']);
            update_user_meta($user_id,'billing_address_1',$_POST['billing_address_1']);
            update_user_meta($user_id,'billing_address_2',$_POST['billing_address_2']);
            update_user_meta($user_id,'billing_city',$_POST['billing_city']);
            update_user_meta($user_id,'billing_state',$_POST['billing_state']);
            update_user_meta($user_id,'billing_postcode',$_POST['billing_postcode']);
            update_user_meta($user_id,'billing_country',$_POST['billing_country']);
        }
    }
}

/** Remove payment method from email */
add_filter( 'woocommerce_get_order_item_totals', 'remove_payment_method', 10, 3 );
function remove_payment_method($total_rows, $order, $tax_display ){
    unset($total_rows['payment_method']);
    return $total_rows;
}

/** Add size in cart item */
add_action('woocommerce_after_cart_item_name', 'add_item_custom_data',10,2);

function add_item_custom_data($cart_item, $cart_item_key){
    $item_data = $cart_item['data'];
    echo "<br><span>".$item_data->get_attribute( 'pa_size' )."</span>";
}

/** Add order notice */
add_action('woocommerce_review_order_before_payment','checkout_page_notice');

function checkout_page_notice(){   
    echo '<div class="checkout-note-msg"><strong>*Note :</strong> <span>Normal processing time is 3 business days</span></div>';
    echo '<div style="color:red"><strong><span>All Orders are placed per bottle in the Sample Store.</span></strong> </div>';
}

add_filter('woocommerce_product_tabs', function($tabs){
    unset($tabs['description']);
    return $tabs;
});

/*add_filter('woocommerce_display_product_attributes', 'add_desc_to_info',10,2);
function add_desc_to_info($attr, $product){
    $attr['prod_desc'] = ['label' => 'Description', 'value' => $product->get_description()];
    return $attr;
}*/

add_action('admin_menu', 'compliance_email');
function compliance_email(){
    add_submenu_page( 'options-general.php', 'API Setting', 'API Setting',
    'manage_options', 'ship-compliance','save_ship_compliance_email');

    add_submenu_page( 'options-general.php', 'Update GL Code', 'Update GL Code',
    'manage_options', 'update-gl-code','save_update_gl_code');
}

function save_ship_compliance_email(){
    if(isset($_POST['submit'])){
        if ( ! isset( $_POST['_ship_compliance_email'] ) 
            || ! wp_verify_nonce( $_POST['_ship_compliance_email'], 'ship_compliance' ) 
            ) {
            print 'Sorry, your nonce did not verify.';
            exit;
            }
            else{
                $post = $_POST['ship_emails'];
                $inventory_emails = $_POST['inventory_emails'];
                $ca_no['ca'] = $_POST['ship_ca_customer_no'];
                $ca_no['nonca'] = $_POST['ship_non_ca_customer_no'];
                $api_base_url = $_POST['api_base_url'];
                $ship_compliance_url = $_POST['ship_compliance_url'];
                $api_username = $_POST['api_username'];
                $api_password = $_POST['api_password'];
                $api_username2 = $_POST['api_username2'];
                $api_password2 = $_POST['api_password2'];
                $regis_emails = $_POST['registration_emails'];
                update_option('_ship_compliance_email', $post);
                update_option('_product_inventory_email', $inventory_emails);
                update_option('_api_base_url', $api_base_url);
                update_option('_ship_compliance_url', $ship_compliance_url);
                update_option('_ship_customer_no', $ca_no);
                update_option('_api_base_username', $api_username);
                update_option('_api_base_password', $api_password);
                update_option('_api_base_username2', $api_username2);
                update_option('_api_base_password2', $api_password2);
                update_option('_registration_request_email', $regis_emails);
            }
    }
    $email = get_option('_ship_compliance_email','');
    $inv_email = get_option('_product_inventory_email','');
    $api_base_url = get_option('_api_base_url','');
    $ship_compliance_url = get_option('_ship_compliance_url',''); 
    $cus_no = get_option('_ship_customer_no',['ca'=>'','nonca'=>'']);
    $api_username = get_option('_api_base_username','');
    $api_password = get_option('_api_base_password','');
    $api_username2 = get_option('_api_base_username2','');
    $api_password2 = get_option('_api_base_password2','');
    $regis_email = get_option('_registration_request_email','');
    
        echo '<h2>Shipping Setting</h2>

        <form action="" method="post">

        <div class="api-setting-group"> <label>Registration Request Email : </label><input type="text" style="width:50%" name="registration_emails" value="'.$regis_email.'" placeholder="test@example.com,test2@example.com"><br></div>
        
        <div class="api-setting-group"> <label>Ship Compliance Email : </label><input type="text" style="width:50%" name="ship_emails" value="'.$email.'" placeholder="test@example.com,test2@example.com"><br></div>
        
        <div class="api-setting-group"> <label>Product Inventory Email : </label><input type="text" style="width:50%" name="inventory_emails" value="'.$inv_email.'" placeholder="test@example.com,test2@example.com"><br></div>
        
        <div class="api-setting-group"> <label>Customer No For CA : </label><input type="text" style="width:50%" name="ship_ca_customer_no" value="'.$cus_no['ca'].'" placeholder="12345678"><br></div>
        
        <div class="api-setting-group"> <label>Customer No For Non CA : </label><input type="text" style="width:50%" name="ship_non_ca_customer_no" value="'.$cus_no['nonca'].'" placeholder="12345678"><br></div>
        
        <div class="api-setting-group"> <label>API base url : </label><input type="text" style="width:50%" name="api_base_url" value="'.$api_base_url.'" placeholder=""><br></div>

        <div class="api-setting-group"> <label>Ship Compliance url : </label><input type="text" style="width:50%" name="ship_compliance_url" value="'.$ship_compliance_url.'" placeholder=""><br></div>
        
        <div class="api-setting-group"> <label>API username : </label><input type="text" style="width:50%" name="api_username" value="'.$api_username.'" placeholder=""><br></div>
        
        <div class="api-setting-group"> <label>API password : </label><input type="tecontactdivxt" style="width:50%" name="api_password" value="'.$api_password.'" placeholder=""><br></div>
        
        <div class="api-setting-group"> <label>API username 2 : </label><input type="text" style="width:50%" name="api_username2" value="'.$api_username2.'" placeholder=""><br></div>
        
        <div class="api-setting-group"> <label>API password 2 : </label><input type="text" style="width:50%" name="api_password2" value="'.$api_password2.'" placeholder=""></div>'; //Open the container
        
        wp_nonce_field( 'ship_compliance', '_ship_compliance_email' ); 
        echo '<br><div class="api-setting-group"><input type="submit" name="submit" class="button-primary" value="Save"><div class="api-setting-group"></form> ';

}

function shipcompliance_check_popup(){
    if(is_user_logged_in() && (is_product() || is_product() || is_front_page() || is_home())){
        $countries_obj   = new WC_Countries();
        $states = $countries_obj->get_states( 'US' );
        echo '<div id="contactdiv">
        <form class="form" action="" method="post" id="contact">';
        wp_nonce_field( 'users_ship_compliance', '_ship_form_front' ); 
       echo '<select id="user_type" style="width:100%; line-height:35px;">
                <option value="">Select shipping user type</option>
                <option value="1">Wholesaler/Broker</option>
                <option value="2">Retailer/Retailer employees</option>
                <option value="3">Media/Writers/Influencers</option>
                <option value="4">TWG Employees</option>
            </select>
            <select id="user_state" name="user_state" style="width:100%; line-height:35px;">
             <option value="">Select State</option>';
        foreach($states as $key => $state){
            echo '<option value="'.$key.'">'.$state.'</option>';
        }
        echo '</select>
        <div id="ship_responce"></div>
        </form>
        </div>';
    }
}
add_action( 'wp_footer', 'shipcompliance_check_popup', 9 );

add_action( 'wp_ajax_check_ship_popup', 'get_users_state_compliance' );

function get_users_state_compliance(){
    $type = $_POST['type'];
    $state = $_POST['state'];
    $ship_popup_array = array(
        '1' => ['state' => ['UT','MS' ],
                'msg' => '<p>State laws do not allow us to ship wine samples to our brokers in Mississippi and
                Utah. Wine samples must be sent to the control state authorities. Please contact
                Jeannie Bremer, Vice President of Compliance & Public Policy if you need more
                information.</p>'],
        '2' => ['state'=>['UT' ,'NV', 'NH', 'ND'],
                'msg' => '<p>State laws do not allow us to ship wine samples to retailers and retailer employees
                in certain states. Solutions can be found below. Please contact Jeannie Bremer, Vice
                President of Compliance & Public Policy if you need more information.</p>
                
                <p>Nevada, New Hampshire & North Dakota: Ship to the sample our wholesaler who
                can get it to the retailer​</p>
                
                <p>Mississippi & Utah: Ship the sample to the control state authority</p>'],
        '3' => ['state' => ['AL', 'AR', 'DE', 'IN', 'NJ', 'ND', 'RI', 'SD', 'UT'],
                'msg' => '<p>State laws do not allow us to ship wine samples to individuals in certain states. Solutions
                can be found below. Please contact Jeannie Bremer, Vice President of Compliance & Public
                Policy if you need more information.</p>
                
                <p>Please have the sample shipped to our wholesaler/broker who can arrange to get it to the
                individual in question.​</p>
                
                <p>Mississippi & Utah: Ship the sample to the control state authority</p>'],
        '4' => ['state' => ['AL', 'AR', 'DE', 'IN', 'NJ', 'ND', 'RI', 'SD', 'UT'],
                'msg' => '<p>State laws do not allow us to ship wine samples to individuals in certain states. Solutions
                can be found below. Please contact Jeannie Bremer, Vice President of Compliance & Public
                Policy if you need more information.</p>
                
                <p>Please have the sample shipped to our wholesaler/broker who can arrange to get it to the
                individual in question.​</p>
                
                <p>Mississippi & Utah: Ship the sample to the control state authority</p>']
    );
    if(in_array($state, $ship_popup_array[$type]['state'])){
        echo $ship_popup_array[$type]['msg'];
        echo '<p style="color:red;">Please change state to continue</p><a href="'.wp_logout_url().'">Logout</a>';
    }else{
        echo '<input type="submit" id="procced_now" value="Proceed Now">';
    }
    die;
}

function save_users_state_compliance(){
   
    if(is_user_logged_in()){
        if(isset($_POST['_ship_form_front']) && wp_verify_nonce( $_POST['_ship_form_front'], 'users_ship_compliance' )){
            $ans = $_POST['user_state'];
            setcookie('users_compliance_state', $ans, time()+7200, COOKIEPATH, COOKIE_DOMAIN);
        }
    }/*else{
        do_action('stock_update_events');
    }*/
}

add_action('init','save_users_state_compliance');

add_action('product_add_update_events', 'product_add_update_cron');
add_action('wp_ajax_add_update_product', 'product_add_update_cron');
function product_add_update_cron(){
    $products = _add_update_product();    
   
    $taxo = array('Brand'=>'Brands', 'Master Code'=>'Master Codes', 'Pack Description' => 'Pack Description','Varietal Flavor Description' => 'Varietal Flavor Description', 'Tier Description' => 'Tier Description', 'Size Description' => 'Size Description');
    $attributes = [
        'pa_brand'=>'brand',
        'pa_master-code'=>'master_code',
        'pa_pack-description' => 'pack_description',
        'pa_package-format' => 'package_format',
        'pa_size' => 'size_description',
        'pa_tier-description' => 'tier_description',
        'pa_varietal-flavor' => 'varietal_flavor_description'
    ];
    $raw_attributes = array();

   
    if(!empty($products)){
        foreach($products as $product){
            if($product['stockingtype'] == 'M' || $product['stockingtype'] == 'P'){
                
                
                $post_id = wc_get_product_id_by_sku($product['SKU']);
                if($post_id==0){
                    $post_id = wp_insert_post(
                        array(
                            'post_title' => $product['name'],
                            'post_type' => 'product',
                            'post_status' => 'publish'
                        )
                    );
                    $pro = new WC_Product( $post_id );
                    $pro->set_image_id(369);
                    $pro->set_sku($product['SKU']);
                }else{
                    $pro = new WC_Product( $post_id );
                }
                $pro->set_description($product['description']);
                $pro->set_short_description($product['description']);
                $pro->add_meta_data('appellation', $product['appellation'], true);
                $pro->add_meta_data('stocking_type', $product['stockingtype'], true);
                $pro->add_meta_data('vintage_description', $product['vintage'],  true);
                $pro->add_meta_data('unit_of_measure', $product['unit_of_measure'],  true);
                $pro->add_meta_data('export', $product['export'],  true);
                $pro->set_image_id(369);
                $size = $product['size_description'];
                if (stripos($size, 'ML') !== false) {
                    $size_int = (int) str_replace('ML','',$size);
                    $price = number_format( $size_int*(1.12/750) ,2);
                }else{
                    $size_int = (int) str_replace('L','',$size);
                    $price = number_format($size_int*1000*(1.12/750),2);
                }
                //file_put_contents('logs.txt', $size.$size_int.'/'.$price.PHP_EOL , FILE_APPEND | LOCK_EX);
                $pro->set_regular_price($price);
                $pro->set_sale_price($price);
                foreach($taxo as $k => $v){
                    $slug = strtolower(str_replace(' ','_',$k));
                    $term = term_exists($product[$slug], $slug);
                    if ( $term !== 0 && $term !== null ) {
                        $term_id = (int)$term['term_id'];
                    }else{
                        $term = wp_insert_term($product[$slug], $slug);
                        $term_id = $term['term_id'];
                    }
                    wp_set_object_terms($post_id, $term_id, $slug);
                }
                foreach($attributes as $k => $v){
                    $option = $product[$v];
                    /*$term = term_exists([$product[$v]], $k);
                    if ( $term !== 0 && $term !== null ) {
                        $option = $product[$v];
                    }else{
                        $term = wp_insert_term($product[$v], $k);
                        $option = $product[$v];
                    }*/
                    $attribute = new WC_Product_Attribute();
                    $attribute->set_id(wc_attribute_taxonomy_id_by_name($k)); //if passing the attribute name to get the ID
                    $attribute->set_name($k); //attribute name
                    $attribute->set_options([$option]); // attribute value
                    //$attribute->set_position(1); //attribute display order
                    $attribute->set_visible(1); //attribute visiblity
                    //$attribute->set_variation(0);//to use this attribute as varint or not

                    $raw_attributes[] = $attribute; //<--- storing the attribute in an array
                }
                $pro->set_attributes($raw_attributes);
                $pro->save();
            }
        }
    }
    die;
}

function remove_cookie(){    
    setcookie('users_compliance_state', $ans, time()-3600, COOKIEPATH, COOKIE_DOMAIN);
}
add_action('wp_login', 'remove_cookie');
add_action('woocommerce_thankyou', 'remove_cookie');

add_filter('woocommerce_widget_cart_is_hidden', 'show_cart', 10, 1);
function show_cart($status){
    if(is_checkout()){
        return $status = false;
    }
    return $status;
}

add_filter('woocommerce_my_account_my_orders_columns', 'myorder_heading', 10, 1);
function myorder_heading($head){
    unset($head['order-total']);
    return $head;
}

add_filter( 'woocommerce_states', 'custom_woocommerce_states' );
 
function custom_woocommerce_states( $states ) {
  $states['US']['International'] = 'International';
 
  return $states;
}

function redirect_to_home() {
    if(is_user_logged_in() && !isset($_COOKIE['users_compliance_state']) && !is_home() && !is_front_page() && !wp_verify_nonce( $_POST['_ship_form_front'], 'users_ship_compliance' )) {
      wp_redirect(home_url());
      exit();
    }
  }
add_action('template_redirect', 'redirect_to_home');

add_filter('woocommerce_widget_cart_item_quantity', 'remove_minicart_price', 10, 3);

function remove_minicart_price($html, $cart_item, $cart_item_key){
    return '<span class="quantity">' . $cart_item['quantity']. '</span>';
}

  add_shortcode( 'ship_message', 'ship_compliance_message' );
    function ship_compliance_message( ) {
        $html ="";
        $html .= '<div class="ship-message">
                        
                    <div class="blog-box">
                        <div class="user-type">Samples to Wholesalers or Brokers</div>
                        <ul>
                            <li>TWG is allowed to ship wine samples to wholesalers and brokers in all states except:
                                <ul>
                                    <li>Mississippi</li>
                                    <li>Utah</li>
                                </ul>
                            </li>
                            <li>State laws do not allow us to ship wine samples to our brokers in Mississippi and
                            Utah. Wine samples must be sent to the control state authorities. Please contact
                            Jeannie Bremer, Vice President of Compliance & Public Policy if you need more
                            information.</li>
                        </ul>
                    </div>
                    <div class="blog-box">
                        <div class="user-type">Samples to Retailers/Retailer Employees</div>
                        <ul>
                            <li>TWG is allowed to ship wine samples to retailers/retailer employees in all states except:
                                <ul>
                                    <li>Nevada​, New Hampshire​, North Dakota, Mississippi​, and Utah</li>
                                </ul>
                            </li>
                            <li>State laws do not allow us to ship wine samples to retailers and retailer employees
                            in certain states. Solutions can be found below. Please contact Jeannie Bremer, Vice
                            President of Compliance & Public Policy if you need more information.</li>
                            <li>Nevada, New Hampshire & North Dakota: Ship to the sample our wholesaler who
                            can get it to the retailer​</li>
                            <li>Mississippi & Utah: Ship the sample to the control state authority</li>
                        </ul>
                    </div>
                    <div class="blog-box">
                        <div class="user-type">Samples to Media, Writers, Influencers, etc.</div>
                        <ul>
                            <li>TWG is allowed to ship wine samples to members of the media, writers, influencers employees in all states except:
                                <ul>
                                    <li>Alabama​; Arkansas​; Delaware​; Indiana​; Mississippi​; New Jersey​; North Dakota​;
                                     Rhode Island​; South Dakota​; Utah​</li>
                                </ul>
                            </li>
                            <li>State laws do not allow us to ship wine samples to individuals in certain states. Solutions
                            can be found below. Please contact Jeannie Bremer, Vice President of Compliance & Public
                            Policy if you need more information.</li>
                            <li>Please have the sample shipped to our wholesaler/broker who can arrange to get it to the
                            individual in question.​</li>
                            <li>Mississippi & Utah: Ship the sample to the control state authority</li>
                        </ul>
                    </div>
                    <div class="blog-box">
                        <div class="user-type">Samples to TWG Employees</div>
                        <ul>
                            <li>TWG is allowed to ship wine samples to members our own employees in all states except:
                                <ul>
                                    <li>Alabama​; Arkansas​; Delaware​; Indiana​; Mississippi​; New Jersey​; North Dakota​;
                                    Rhode Island​; South Dakota​; Utah​</li>
                                </ul>
                            </li>
                            <li>State laws do not allow us to ship wine samples to individuals in certain states. Solutions
                            can be found below. Please contact Jeannie Bremer, Vice President of Compliance & Public
                            Policy if you need more information.</li>
                            <li>Please have the sample shipped to our wholesaler/broker who can arrange to get it to the
                            individual in question.​</li>
                            <li>Mississippi & Utah: Ship the sample to the control state authority</li>
                        </ul>
                    </div>
                </div>';
        return $html;
    }

add_action('woocommerce_checkout_process', 're_validate_stock');

function re_validate_stock(){
    $cart = WC()->cart->get_cart();
    foreach($cart as $cart_item){
        $sku = $cart_item['data']->get_sku();
        $product = new WC_Product( $cart_item['product_id'] );
            if(check_obsolete_api($sku)){
                $product->set_catalog_visibility('hidden');
            }else{
                $unit_of_measure = get_post_meta($product->get_id(), 'unit_of_measure', true);
                $unit_of_measure = empty($unit_of_measure) ? 'CA' : $unit_of_measure;
                $stock =  stock_api_curl($sku, $unit_of_measure);
                $product->set_manage_stock(true);
                $product->set_stock_quantity($stock);
                $status = ($stock == 0) ? 'outofstock' : 'instock';
                $product->set_stock_status($status);
                $product->save();
            }
        }
    }

add_action('woocommerce_save_account_details_errors', 'password_validate');

function password_validate(){
    $pass_cur             = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	$pass1                = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
    if(! empty( $pass_cur ) && $pass_cur == $pass1)
        wc_add_notice( __( 'Current password and old password can not be same.', 'woocommerce' ), 'error' );		
}

/**
 * Ensure cart contents update when products are added to the cart via AJAX
 */
function my_header_add_to_cart_fragment( $fragments ) {
 
    ob_start();
    $count = WC()->cart->cart_contents_count;
    ?>
        
        <span class="cart_total">
            <span><?php echo esc_html($count) . ' ' . ($count == 1 ? esc_html__('item - ', 'wineshop') : esc_html__('items - ', 'wineshop')); ?></span>
            <?php echo WC()->cart->get_cart_subtotal(); ?>
        </span>
        <?php
 
    $fragments['span.cart_total'] = ob_get_clean();
     
    return $fragments;
}

add_filter( 'woocommerce_add_to_cart_fragments', 'my_header_add_to_cart_fragment' );

add_action( 'woocommerce_order_details_after_order_table', 'order_display_jde_number', 20, 1 );

function order_display_jde_number($order){

    $jde_order = get_post_meta( $order->get_id(), '_order_documentnumber', true );
    
    if($jde_order == 'Error'){
        //$jde_order_msg = get_post_meta( $order->get_id(), '_order_error_msg', true );
        //echo 'JDE Error : '.$jde_order_msg;
    }
    else{
        echo '<p><strong>JDE Order No : </strong>'. $jde_order.'</p>';
    }
}

add_action( 'woocommerce_order_item_meta_start', 'customizing_cart_item_data', 10, 4);
function customizing_cart_item_data( $item_id , $item, $order, $status ) {
    $product = $item->get_product();
    if(!empty($product)){
        $out = '';
        $out .= '<strong>'.wc_attribute_label('pa_package-format').' : </strong>';
        $out .= '<span>'.$product->get_attribute('pa_package-format').'</span>';
        echo '<br>'.$out;
    }
}

add_filter('woocommerce_cart_item_name', 'customizing_checkout_item_data', 10, 3 );
function customizing_checkout_item_data($item_name, $cart_item, $cart_item_key){
    $out = '';
    $out .= '<strong>'.wc_attribute_label('pa_package-format').' : </strong>';
    $out .= '<span>'.$cart_item['data']->get_attribute('pa_package-format').'</span>';
    return $item_name.'<br>'.$out;
}

add_action('woocommerce_after_order_notes', 'add_address_csv');

 function add_address_csv(){     
    global $roles;
    $user = wp_get_current_user();
    $user_roles = ( array ) $user->roles;
    $user_role = $user_roles[0];
    if($user_role == 'club'){
     ?>
        <p class="form-row form-row-wide validate-required">
            <?php wp_nonce_field( 'address_csv', '_add_address_csv' ); ?>  
            <label for="address_csv" class="">Upload CSV to ship to multiple addresses. CSV file must be in the format as given in the <a href="<?php echo admin_url('admin-ajax.php?action=download_dummy_csv') ?>" target="_blank">Download Sample CSV</a>&nbsp;</label>
            
            <span class="woocommerce-input-wrapper">
                <input type="hidden" name="add_address_csv_name" id="add_address_csv_name">
                <input type="file" class="input-text " name="address_csv" id="address_csv" placeholder="Address Csv" value="" >
            </span>
        </p>
     <?php
    }
 }

 add_action('woocommerce_checkout_before_order_review_heading','add_address_view');

 function add_address_view(){
     echo '<div id="add_address_view" style="overflow: auto; width: 100%; max-height: 400px; display:none;"> <h3>Uploaded Address</h3> </div>';
 }
add_action( 'wp_ajax_add_address_csv', 'add_address_csv_name' );

 function add_address_csv_name(){
    $_filter = true; // For the anonymous filter callback below.
    add_filter( 'upload_dir', function( $arr ) use( &$_filter ){
        if ( $_filter ) {
            $folder = '/address_csv'; // No trailing slash at the end.
            $arr['path'] = $arr['basedir'] .$folder;
            $arr['url'] = $arr['basedir'] .$folder;
        }
    
        return $arr;
    } );
    
    $filename = time() . '.csv';
    $upload = wp_upload_bits($filename, null, file_get_contents($_FILES["address_csv"]["tmp_name"]));
    $_filter = false;
    /* display address */
    $upload_dir = wp_upload_dir();
    $csv_file = $upload_dir['basedir']."/address_csv"."/".$filename;
    $file = fopen($csv_file, 'r');
    $html = '<table>{error}';
    $address1 = [];
    $msg = [];
    while (($csv_address = fgetcsv($file)) !== FALSE) {
        $data = '';
        $duplicate = false;     
        $addr = strtolower($csv_address[5].$csv_address[6].$csv_address[7].$csv_address[8].$csv_address[9].$csv_address[10]);   
        if( in_array($addr, $address1)){
            $duplicate = true;
        }else{
            $address1[] = $addr;
        }
        for($i=0; $i<=10; $i++){
            $data .= '<td>'.$csv_address[$i].'</td>';
        }
        if(!$duplicate){
            if($csv_address[0] != 'first_name'){
                $result = _get_usps_address($csv_address[5],$csv_address[7],$csv_address[8], $csv_address[9], $csv_address[6]);
                if(isset($result['Error'])){
                    $msg[] = $result['Error']['Description'];
                }
            }
            $html .= '<tr>'.$data;
            $html .= '</tr>';
        }
    }
    $html .= '</table>';
    $error = empty($msg) ? '' : '<tr><td colspan="11">'.implode(',', $msg).'</td></tr>';
    $html = str_replace('{error}', $error, $html);
    echo json_encode(['file_name' => $filename, "html" => $html]);
    die;
 }

 
add_action( 'wp_ajax_download_dummy_csv', 'download_dummy_csv' );

function download_dummy_csv(){
    
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="Samples-Store-Download-Address.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $file1 = fopen('php://output', 'w');
    $upload_dir = get_stylesheet_directory();
    $csv_file = $upload_dir."/dummy_address_csv.csv";
    if(isset($_GET['file']) && $_GET['file'] = 'gl_code'){
        $csv_file = $upload_dir."/Samples-GL-number-csv.csv";
        header('Content-Disposition: attachment; filename="Samples-Store-Download-GLnumber.csv"');
    }
    $file = fopen($csv_file, 'r');
    while (($csv_address = fgetcsv($file)) !== FALSE) {        
        fputcsv($file1, $csv_address);
    }
    exit();
}

function get_custom_title($product){
       
    $title = '';
    $export = (get_post_meta($product->get_id(), 'export', true ) != 'BLANK') ? 'EXPORT' : 'DOMESTIC';
    return $title = $product->get_attribute( 'brand' ).' '.
    $product->get_attribute( 'varietal-flavor' ).' '.
    $product->get_attribute( 'tier-description' ).' '.
    $product->get_attribute( 'size' ).' '.
    get_post_meta($product->get_id(), 'appellation', true ).' '.
    get_post_meta($product->get_id(), 'vintage_description', true ).' '. $export;
}
remove_action('woocommerce_single_product_summary','woocommerce_template_single_title',5);
add_action('woocommerce_single_product_summary', 'woocommerce_my_single_title',5);

if ( ! function_exists( 'woocommerce_my_single_title' ) ) {
   function woocommerce_my_single_title() {
    global $product;
?>
            <h1 itemprop="name" class="product_title entry-title"> 
            <?=get_custom_title($product)?>
            </h1>
<?php
    }
}

function save_update_gl_code(){
    ?>
    <h1> Update GL Codes </h1>
    <table style="text-align:left;">
        <tr>
            <th>
                <label>Sample CSV file</label>
            </th>
            <td>
                <a href="<?php echo admin_url('admin-ajax.php?action=download_dummy_csv&file=gl_code') ?>" target="_blank">Download Sample CSV</a>
            </td>
        </tr>    
        <tr>
            <th>
                <label>Upload CSV</label>
            </th>
            <td>
                <input type="file" name="gl_code" id="gl_code">
            </td>
        </tr>    
        <tr>
            <td>
                <input type="button" id="upload_csv" class="button-primary" value="Upload">
                
            </td>
            <td>
                <div id="loader" style="display: none;"><img src="<?=get_stylesheet_directory_uri()?>/loading-png-gif.gif" width="32px" height="32px"></div>
                <span id="success_msg" style="display: none;color: green;">GL Code update Successfuly</span>
            </td>
        </tr>
    </table>
    <?php
}


add_action( 'wp_ajax_add_gl_code', 'add_gl_code' );

 function add_gl_code(){
    $_filter = true; // For the anonymous filter callback below.
    add_filter( 'upload_dir', function( $arr ) use( &$_filter ){
        if ( $_filter ) {
            $folder = '/gl_code'; // No trailing slash at the end.
            $arr['path'] = $arr['basedir'] .$folder;
            $arr['url'] = $arr['basedir'] .$folder;
        }
        return $arr;
    } );
    
    $filename = time() . '.csv';
    $upload = wp_upload_bits($filename, null, file_get_contents($_FILES["gl_code"]["tmp_name"]));
    $_filter = false;
    /* display address */
    $upload_dir = wp_upload_dir();
    $csv_file = $upload_dir['basedir']."/gl_code"."/".$filename;
    $file = fopen($csv_file, 'r');
    $i = 0;
    while (($csv_address = fgetcsv($file)) !== FALSE) {
        if($i > 0){
            $user = get_user_by('email', $csv_address[0]);
            if(isset($csv_address[1]) && !empty($csv_address[1]))
                update_user_meta($user->ID, '_gl_number', $csv_address[1]);
        }
        $i++;
    }
    echo json_encode(['file_name' => $filename]);
    die;
 }

 // To change add to cart text on single product page
/*add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_single_add_to_cart_text', 10,2 ); 
function woocommerce_custom_single_add_to_cart_text($var, $instance) {
    return __( 'Add One Unit to Cart', 'woocommerce' ); 
}*/

// To change add to cart text on product archives(Collection) page
/*add_filter( 'woocommerce_product_add_to_cart_text', 'woocommerce_custom_product_add_to_cart_text', 10,2 );  
function woocommerce_custom_product_add_to_cart_text($var, $instance) {
   $text = $instance->is_purchasable() && $instance->is_in_stock() ? 'Add One Unit to Cart' : $var;
    return __( $text, 'woocommerce' );
}*/

add_filter('woocommerce_cart_item_quantity', 'add_unit_of_measure', 10, 3);
add_filter('woocommerce_checkout_cart_item_quantity', 'add_unit_of_measure_checkout', 10, 3);
function add_unit_of_measure($product_quantity, $cart_item_key, $cart_item){
    return $product_quantity.'<span>'.get_post_meta($cart_item['data']->get_id(), 'unit_of_measure', true ).'</span>';
}
function add_unit_of_measure_checkout($product_quantity, $cart_item, $cart_item_key){
    return $product_quantity.' <span>'.get_post_meta($cart_item['data']->get_id(), 'unit_of_measure', true ).'</span>';
}

add_filter( 'woocommerce_product_query_meta_query', 'show_only_products_with_specific_metakey', 10, 2 );
function show_only_products_with_specific_metakey( $meta_query, $query ) {
    if(isset($_GET['export']) && $_GET['export']=='international'){

        $meta_query[] = array(
            'key'     => 'export',
            'value' => "BLANK",
            'compare' => '!='
        );
        $meta_query[] = array(
            'key'     => 'export',
            'value' => "",
            'compare' => '!='
        );

    }else{
        $meta_query[] = array(
            'key'     => 'export',
            'value' => "BLANK"
        );
    }
    $meta_query[] = array(
        'key'  => 'stocking_type',
        'value' => ['U', 'O'],
        'compare' => 'NOT IN'
    );
    return $meta_query;
}

function custom_pre_get_posts_query( $q ) {

    $tax_query = (array) $q->get( 'tax_query' );

    $tax_query[] = array(
           'taxonomy' => 'brand',
           'field' => 'slug',
           'terms' => array( 'yard-sale', 'bota-box' ), // Don't display products in the clothing category on the shop page.
           'operator' => 'NOT IN'
    );

    $q->set( 'tax_query', $tax_query );

}
add_action( 'woocommerce_product_query', 'custom_pre_get_posts_query' ); 

add_filter('ajax_search_query', 'filter_search_bar',10, 1);

function filter_search_bar($arg){
    $arg['tax_query'] = array(
        'taxonomy' => 'brand',
        'field' => 'slug',
        'terms' => array( 'yard-sale', 'bota-box' ),
        'operator' => 'NOT IN'
    );
    $arg['meta_query'] = array(
        'relation' => 'AND',
        array(
            'relation' => 'OR',
            array(
                'key'     => 'export',
                'value' => 'BLANK'
            ),
            array(
                'key'     => 'export',
                'value' => ''
            ),
        ),
        array(
            'key'  => 'stocking_type',
            'value' => ['U', 'O'],
            'compare' => 'NOT IN'
        )
    );
    return $arg;
}
add_action( 'update_custom_order_meta', 'orddd_lite_checkout_field_update_order_meta' );

function orddd_lite_checkout_field_update_order_meta($order_id){
    
    $order = wc_get_order($order_id);
    $order_date= $order->get_date_created();
    $delivery_date = date("Y-m-d", strtotime("$order_date +7 days"));
    $date_format   = 'yy-mm-dd';
    update_post_meta( $order_id, get_option( 'orddd_lite_delivery_date_field_label' ), sanitize_text_field( wp_unslash( $_POST['e_deliverydate'] ) ) );

    $timestamp = orddd_lite_common::orddd_lite_get_timestamp( $delivery_date, $date_format );
    update_post_meta( $order_id, '_orddd_lite_timestamp', $timestamp );
    Orddd_Lite_Process::orddd_lite_update_lockout_days( $delivery_date );
}

add_action( 'wp_ajax_inventory_log', 'inventory_log' );

function inventory_log(){
    $last_id = (get_option('_last_stock_update_prod'))? get_option('_last_stock_update_prod') : 0;
    if($last_id > 0){
        $product = new WC_Product( $last_id );
        echo 'Last Product Updated : '. $product->get_sku().'<br>';
        echo "Last Run : ".get_the_modified_date('d/m/Y H:i:s', $last_id);
        die;
    }
}

function update_stock_common($prod_id){
    
    $result = [];

    $product = new WC_Product( $prod_id );
    
    $result['sku'] = $product->get_sku();

    $product->set_image_id(10769);

    if(check_obsolete_api($product->get_sku())){
        $product->set_catalog_visibility('hidden');
        $stock = 0;
    }else{  
    
        $unit_of_measure = get_post_meta($prod_id, 'unit_of_measure', true);
        $unit_of_measure = empty($unit_of_measure) ? 'CA' : $unit_of_measure;
        $stock =  stock_api_curl($product->get_sku(), $unit_of_measure);
       
        $product->set_manage_stock(true);

        if($stock != ''){
            $product->set_stock_quantity($stock);
            $status = ($stock == 0) ? 'outofstock' : 'instock';
            $product->set_stock_status($status);
        }

        $product->set_catalog_visibility('visible');
        
        if(!get_option('_last_price_update_prod_date')){
            $txt ="price";
            //file_put_contents('logs.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
            $product->set_weight('0');
            $price = 0;
            $size = $product->get_attribute( 'pa_size' );
            if (stripos($size, 'ML') !== false) {
                $size_int = (int) str_replace('ML','',$size);
                $price = number_format( $size_int*(1.12/750) ,2);
            }else{
                $size_int = (int) str_replace('L','',$size);
                $price = number_format($size_int*1000*(1.12/750),2);
            }
            //file_put_contents('logs.txt', $size.$size_int.'/'.$price.PHP_EOL , FILE_APPEND | LOCK_EX);
            $product->set_regular_price($price);
            $product->set_sale_price($price);
        }
    }
    $product->save();    
    $result['stock'] = $stock;
    do_action('insert_stock_log', $result);
    return $result;
}


function stock_update_cron() {
    
    global $wpdb;
    $time = serialize(['start' => time(), 'end' => '']);        
    if(isset($_GET['reset'])){
        
        $recent_posts = wp_get_recent_posts(array(
            'numberposts' => 1, 
            'post_type'   => 'product',// Number of recent posts thumbnails to display
            'post_status' => 'publish' // Show only the published posts
        ));        
        $post_id_recent = $recent_posts[0]['ID'];
        update_option('_last_stock_update_prod', $post_id_recent);
        update_option('_inventory_api_time',$time);
    }
        
    if(get_option('_last_stock_update_prod_date')){
        if(get_option('_last_stock_update_prod_date') < date('Y-m-d')){
            
            $recent_posts = wp_get_recent_posts(array(
                'numberposts' => 1, 
                'post_type'   => 'product',// Number of recent posts thumbnails to display
                'post_status' => 'publish' // Show only the published posts
            ));
            $post_id_recent = $recent_posts[0]['ID'];
            update_option('_last_stock_update_prod', $post_id_recent);            
            update_option('_inventory_email_status','');
            update_option('_last_stock_update_prod_date', date('Y-m-d')); 
            update_option('_inventory_api_time',$time);
        }   
    }else{
        update_option('_last_stock_update_prod_date', date('Y-m-d'));
    }
    $last_id = (get_option('_last_stock_update_prod'))? get_option('_last_stock_update_prod') : 0;
    $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE ID <= %d AND post_status = 'publish' AND post_type = 'product' order by ID DESC LIMIT 70", $last_id );
    $results = $wpdb->get_results( $query );
    
    // file_put_contents('logs.txt', $last_id.json_encode ($results).PHP_EOL , FILE_APPEND | LOCK_EX);
    if(count($results)>0){
        foreach($results as $prod){
            
        // wc_update_product_stock($prod, $stock);set_catalog_visibility
            update_stock_common($prod->ID);
            $last_id = $prod->ID;
            // file_put_contents('logs.txt', ($last_id).PHP_EOL , FILE_APPEND | LOCK_EX);
        }
    }else{
        $get_time = (get_option('_inventory_api_time')) ? unserialize( get_option('_inventory_api_time') ) : '';
        if(!empty($get_time) && $get_time['end'] != ''){
            $get_time['end'] = time(); 
            update_option('_inventory_api_time',serialize( $get_time ));
        }
        update_option('_last_price_update_prod_date',date("Y-m-d"));
    }
    update_option('_last_stock_update_prod', $last_id);
}


add_action( 'wp_ajax_update_stock_by_sku', 'update_stock_by_sku' );

function update_stock_by_sku(){
    if(isset($_GET['sku']) && $_GET['sku'] != ''){
        $post_id = wc_get_product_id_by_sku($_GET['sku']);
        if($post_id > 0){
            $result = update_stock_common($post_id);
            echo "SKU = ".$_GET['sku'];
            echo "Quantity = ".$result['stock'];
        }else{
            echo "SKU not found";
        }
    }else{
        echo "Please enter SKU";
    }
    die;
}


add_action( 'wp_ajax_update_stock_by_day', 'update_stock_by_day' );

function update_stock_by_day(){
    if(isset($_GET['days']) && $_GET['days'] != ''){
        global $wpdb;
        $day_90 = date('Y-m-d', strtotime('-'.$_GET['days'].' days', time()));       
        $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_date >= %s AND post_status = 'publish' AND post_type = 'product' order by ID DESC ", $day_90 );
        $results = $wpdb->get_results( $query );
        
        // file_put_contents('logs.txt', $last_id.json_encode ($results).PHP_EOL , FILE_APPEND | LOCK_EX);
        if(count($results)>0){
            foreach($results as $prod){
                
            // wc_update_product_stock($prod, $stock);set_catalog_visibility
                $result = update_stock_common($prod->ID);
                echo "SKU = ".$result['sku'];
                echo "Quantity = ".$result['stock'];
                echo '<br>';
                // file_put_contents('logs.txt', ($last_id).PHP_EOL , FILE_APPEND | LOCK_EX);
            }
        }
    }
    die;
}

add_action('woocommerce_before_checkout_billing_form', 'abbreviations_notification', 10, 1);

function abbreviations_notification($checkout){
    echo '<p class="abb_notify">Please use the following abbreviations in the street address:<br>Dr (not “Drive”)<br>Ln (not “Lane”)<br>St (not “Street’)<br>Please sure to include apartment or unit number.</p>';
}

add_shortcode("inventory_update_form", 'inventory_update_sku'); 
function inventory_update_sku(){
    echo '<h2>Inventory Update By SKU</h2>
    <label>SKU : </label><input type="text" style="width:50%" name="inventory_sku" value="" placeholder="SKU" id="inventory_sku"><br>';
    echo '<br><input type="button" name="submit" id="update_inventory" value="Update"><br><div id="responce"></div>';
}
add_filter('yit_get_terms_args', 'exclude_terms', 10, 1);
function exclude_terms($args){
    if($args['taxonomy'] == 'pa_brand'){
        $args = array( 
            'exclude' => ['3608', '2410']
        );
    }
    return $args;
}

add_action('order_shipped_update_events', 'order_shipped_update');
add_action('wp_ajax_order_ship', 'order_shipped_update');

function order_shipped_update(){
    if(isset($_GET['order_id']) && $_GET['order_id'] != ''){
        $jde_no = get_post_meta( $_GET['order_id'], '_order_documentnumber', true );
        $track_numbers = _order_shipped($_GET['order_id'], $jde_no);
        echo serialize($track_numbers);
    }else{
        global $wpdb;
        $day_10 = date('Y-m-d', strtotime('-5 days', time()));       
        $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_date >= %s AND post_status = 'wc-processing' AND post_type = 'shop_order' order by ID DESC ", $day_10 );
        $results = $wpdb->get_results( $query );
        
        file_put_contents('logs.txt', $query.json_encode ($results).PHP_EOL , FILE_APPEND | LOCK_EX);
        if(count($results)>0){
            foreach($results as $prod){
                $jde_no = get_post_meta( $prod->ID, '_order_documentnumber', true );
                _order_shipped($prod->ID, $jde_no);
            }
        }
    }
    die;
}

// add_action( 'woocommerce_order_details_after_order_table', 'order_display_track_number', 20, 1 );

// add_action( 'woocommerce_admin_order_data_after_billing_address', 'order_display_track_number', 20, 1 );
function order_display_track_number($order){
    
    $track_order = get_post_meta( $order->get_id(), '_ship_track_numbers', true );
    if($track_order){
        $track_order = array_unique(unserialize($track_order));
        echo '<p><strong>Order Track No : </strong>'. implode(', ', ($track_order)).'</p>';
    }
}
//add_action('woocommerce_admin_order_item_headers', 'order_product_item_track_header');
function order_product_item_track_header(){
    echo '<th>Track No</th>';
}
//add_action('woocommerce_admin_order_item_values', 'order_product_item_track_id',10,3);
function order_product_item_track_id($product, $item, $item_id ){
    $track_order = get_post_meta( $_GET['post'], '_ship_track_numbers', true );
    if($track_order){
        $track_order = unserialize($track_order);
        echo '<td><strong>'. $track_order[$product->get_sku()].'</strong></td>';
    }
}

// add_action('wp_ajax_usps_address_verify', 'usps_address_verify');

// function usps_address_verify($address){
//     $address = $_GET['address'];
//     $address2 = $_GET['address2'];
//     $city = $_GET['city'];
//     $state = $_GET['state'];
//     $zip = $_GET['zip'];
//     if(!empty($address)){
//         $result = getUspsAddress($address,$city,$state, $zip);
//     }else{
//         $result = getUspsAddress($address2,$city,$state, $zip, $address);
//     }
//    if(isset($result['Error'])){
//       echo $msg = $result['Error']['Description'];
//    }
//    die;

// return $address;
// }

add_action('woocommerce_before_shop_loop_item_title', 'show_vintage_tag',11);

function show_vintage_tag(){
    global $product;
    $vintage = get_post_meta($product->get_id(), 'vintage_description', true );
    if($vintage != ''){
        $data = explode(' ', $vintage);
        if($data[0] == 'VINTAGE'){
            echo '<span class="product-vintage-flag">'.$vintage.'</span>';
        }
    }
}

add_action( 'add_meta_boxes', 'add_jde_meta_box' );

function add_jde_meta_box()
{
    add_meta_box( 
        'woocommerce-order-my-custom', 
        __( 'JDE Number' ), 
        'order_jde_number', 
        'shop_order', 
        'side', 
        'default' 
    );
}
function order_jde_number($post, $metabox)
{
    $no = get_post_meta( $post->ID, '_order_documentnumber', true );
    if($no == 'Error'){
        $no = '';
    }
    echo '<h1>JDE Number</h1>';
    echo '<input type="text" name="_order_jde_number" value="'.$no.'" >';
    $jdelogs = get_post_meta( $post->ID, '_order_jde_log', true );
    if($jdelogs){
        $jdelogs = unserialize($jdelogs);
        echo '<h3>JDE Log</h3>';
        foreach($jdelogs as $log){
            echo $log.'<br>';
        }
    }
}

add_action('save_post', 'save_order_item_jde_field_value', 10, 2 );

function save_order_item_jde_field_value($post_id, $post){
    if(isset($_POST['_order_jde_number']) && $_POST['_order_jde_number'] != ''){
        $no = (isset($_POST['_order_jde_number']) && $_POST['_order_jde_number'] != '') ? $_POST['_order_jde_number'] : 'Error';
        update_post_meta($post_id, '_order_documentnumber', $no);
        $current_user = wp_get_current_user();
        $logmsg = 'JDE number updated by '.$current_user->user_login.'('.$current_user->user_firstname.' '.$current_user->user_lastname.') to '.$no;
        jde_log($post_id, $logmsg);
    }
}

add_filter('woocommerce_email_enabled_customer_processing_order', 'woocommerce_email_on_jde_sync',10, 3);
function woocommerce_email_on_jde_sync($status, $order, $obj){  
    if(!empty($order)){
        $jde_order = get_post_meta( $order->get_id(), '_order_documentnumber', true );
        if($jde_order == 'Error')
            $status = false;
    }
    return $status;
}

function jde_log($order_id, $msg){
    $jdelogs = get_post_meta( $order_id, '_order_jde_log', true );
    if($jdelogs){
        $jdelogs = unserialize($jdelogs);
        $jdelogs[] = $msg;
    }else{        
        $jdelogs = [$msg];
    }
    update_post_meta($order_id, '_order_jde_log', serialize($jdelogs));
}

function sv_wc_add_order_ship_track_action( $actions ) {
    $actions['wc_track_ship_action'] = __( 'Update ship tracking number', 'woocommerce' );
    return $actions;
}
add_filter( 'woocommerce_order_actions', 'sv_wc_add_order_ship_track_action' );
function sv_wc_process_order_ship_track_action( $order ) {
    $jde_no = get_post_meta( $order->id, '_order_documentnumber', true );
    $track_numbers = _order_shipped($order->id, $jde_no);
}
add_action( 'woocommerce_order_action_wc_track_ship_action', 'sv_wc_process_order_ship_track_action' );

add_action('pre_post_update', 'update_old_data_of_product', 10,2);
function update_old_data_of_product($post_id, $post_data){
    $order = wc_get_order($post_id);
    if($post_id != '' && $order){
        $data = get_order_data($post_id);
        update_post_meta($post_id, '_order_old_data_log', serialize($data));
    }
}

function get_order_data($post_id){
    
    $order = wc_get_order($post_id);
    $data = [];
    $address=[];
    $products =[];
    
    $address['billing_first_name'] = $order->data['billing']['first_name'];
    $address['billing_last_name'] = $order->data['billing']['last_name'];
    $address['billing_address_1'] = $order->data['billing']['address_1'];
    $address['billing_address_2'] = $order->data['billing']['address_2'];
    $address['billing_city'] =  $order->data['billing']['city'];
    $address['billing_phone'] =  $order->data['billing']['phone'];
    $address['billing_state'] =  $order->data['billing']['state'];
    $address['billing_country'] =  $order->data['billing']['country'];
    $address['billing_postcode'] =  $order->data['billing']['postcode'];
    $address['billing_email'] =  $order->data['billing']['email'];

    $address['shipping_first_name'] = $order->data['shipping']['first_name'];    
    $address['shipping_last_name'] = $order->data['shipping']['last_name'];
    $address['shipping_address_1'] = $order->data['shipping']['address_1'];
    $address['shipping_address_2'] = $order->data['shipping']['address_2'];
    $address['shipping_city'] =  $order->data['shipping']['city'];
    $address['shipping_state'] =  $order->data['shipping']['state'];
    $address['shipping_country'] =  $order->data['shipping']['country'];
    $address['shipping_postcode'] =  $order->data['shipping']['postcode'];
    foreach ($order->get_items() as $item_id => $item_data) {
        $product = $item_data->get_product();
        if($product){
            $item['sku'] = $product->get_sku();
            $item['qty'] = $item_data->get_quantity();
            $item['price'] = $item_data->get_total();
            $products[$item['sku']]=$item;
        }
    }
    $data['user_id'] = $order->get_customer_id();
    $data['address'] = $address;
    $data['products'] = $products;
    return $data;
}

add_action('save_post', 'verify_order_change_log', 10, 2 );

function verify_order_change_log($post_id, $post){
    
    $order = wc_get_order($post->ID);
    if($post_id != '' && $order){
        $data = get_order_data($post->ID);
        $old_data = unserialize(get_post_meta($post->ID, '_order_old_data_log', true)); 
        // print_r($data);
        // print_r($old_data); 
        // print_r(array_diff_assoc($data['address'], $old_data['address']));
        // print_r(array_diff_assoc($old_data['address'], $data['address'])); die;
        $current_user = wp_get_current_user();
        if($data['user_id'] != $old_data['user_id']){
            $message = 'Customer changed by '.$current_user->user_login.'('.$current_user->user_firstname.' '.$current_user->user_lastname.')';
            $order->add_order_note( $message );
        }
        if(!empty(array_diff_assoc($data['address'], $old_data['address'])) || !empty(array_diff_assoc($old_data['address'], $data['address']))){
            $message = 'Address changed by '.$current_user->user_login.'('.$current_user->user_firstname.' '.$current_user->user_lastname.')';
            $order->add_order_note( $message );
        }
        if(count($data['products']) != count($old_data['products'])){
            $message = 'Order Items changed by '.$current_user->user_login.'('.$current_user->user_firstname.' '.$current_user->user_lastname.')';
            $order->add_order_note( $message );
        }else{
            foreach($data['products'] as $sku => $prod){
                if(isset($old_data['products'][$sku])){
                    if(!empty(array_diff_assoc($data['products'][$sku], $old_data['products'][$sku]))){
                        $message = 'Order Items changed by '.$current_user->user_login.'('.$current_user->user_firstname.' '.$current_user->user_lastname.')';
                        $order->add_order_note( $message );
                    }
                }else{
                    $message = 'Order Items changed by '.$current_user->user_login.'('.$current_user->user_firstname.' '.$current_user->user_lastname.')';
                    $order->add_order_note( $message );
                }
            }
        }
        $order->save();
    }
}

add_action('custom_pending_email_notification', 'add_notification_email_log',10,1);

function add_notification_email_log($order_id){
    $order = wc_get_order($order_id);
    $current_user = wp_get_current_user();
    $message = 'Order emial notification send by '.$current_user->user_login.'('.$current_user->user_firstname.' '.$current_user->user_lastname.')';
    $order->add_order_note( $message );
    $order->save();
}


add_action( 'add_meta_boxes', 'tcg_tracking_box' );
function tcg_tracking_box() {
    add_meta_box(
        'tcg-tracking-modal',
        'Tracking number',
        'tcg_meta_box_callback',
        'shop_order',
        'normal',
        'core'
    );
}

// Callback
function tcg_meta_box_callback( $post )
{
    $track_order = get_post_meta( $post->ID, '_ship_track_numbers', true );
    if($track_order){
        echo '<div class="woocommerce_order_items_wrapper wc-order-items-editable">
        <p><strong>Order Shipping Details:</strong></p>';
        echo '<table border="1" cellspacing="0" class="woocommerce_order_items" width="100%">
            <tr style="text-align: left;">
                <th width="30%">SKU</th>
                <th width="20%">Ship Qty</th>
                <th width="20%">Cancel Qty</th>
                <th width="30%">Track number</th>
            </tr>';
        $tracks = unserialize($track_order);
        foreach($tracks as $key => $track){
            echo "<tr>
                    <td>".$key."</td>
                    <td>".(int)$track['s_qty']."</td>
                    <td>".(int)$track['c_qty']."</td>
                    <td>".$track['track_no']."</td>
                </tr>";
        }
        echo "</table></div>";
    }
}

add_action('woocommerce_order_details_after_order_table', 'tcg_meta_box_callback', 10, 1);

add_filter('alg_orders_custom_statuses_email_content', 'ship_detail_email', 10,2);
function ship_detail_email($replaced_values, $order){
    ob_start();
    tcg_meta_box_callback($order);
   $replaced_values['{ship_details}'] = ob_get_clean();
   return $replaced_values;
}
add_filter('alg_orders_custom_statuses_emails_address', 'ship_detail_email_address', 10,2);
function ship_detail_email_address($replaced_values, $order){
    $replaced_values['{employee_email}'] = $order->get_user()->user_email;
    return $replaced_values;
}

function wc_cancelled_order_add_customer_email( $recipient, $order ){
    if(!empty($order))
        return $recipient = $recipient . ',' . $order->billing_email.','.$order->get_user()->user_email;
    return $recipient;
}
add_filter( 'woocommerce_email_recipient_cancelled_order', 'wc_cancelled_order_add_customer_email', 10, 2 );
add_filter( 'woocommerce_email_recipient_failed_order', 'wc_cancelled_order_add_customer_email', 10, 2 );

// Address Validation Integration on Checkout 

add_filter('woocommerce_checkout_fields', 'bbloomer_checkout_fields_custom_attributes' , 9999);

function bbloomer_checkout_fields_custom_attributes($fields){
    $fields['billing']['billing_company']['maxlength'] = 40;
    return $fields;
}

add_filter('woocommerce_checkout_fields', 'bbloomer_checkout_fields_custom_attributes_billing_city' , 9999);

function bbloomer_checkout_fields_custom_attributes_billing_city($fields){
    $fields['billing']['billing_city']['maxlength'] = 25;
    return $fields;
}

add_filter('woocommerce_checkout_fields', 'bbloomer_checkout_fields_custom_attributes_billing_state' , 9999);

function bbloomer_checkout_fields_custom_attributes_billing_state($fields){
    $fields['billing']['billing_state']['maxlength'] = 3;
    return $fields;
}

add_filter('woocommerce_checkout_fields', 'bbloomer_checkout_fields_custom_attributes_billing_address' , 9999);

function bbloomer_checkout_fields_custom_attributes_billing_address($fields){
    $fields['billing']['billing_address_1']['maxlength'] = 40;
    return $fields;
}

add_filter('woocommerce_checkout_fields', 'bbloomer_checkout_fields_custom_attributes_billing_address_2' , 9999);

function bbloomer_checkout_fields_custom_attributes_billing_address_2($fields){
    $fields['billing']['billing_address_2']['maxlength'] = 40;
    return $fields;
}

// After Checkout Validation
add_action('woocommerce_after_checkout_validation' , 'wck_validate_billing_address_1' , 10, 2 );
function wck_validate_billing_address_1( $fields, $errors ){
     if(!preg_match('/^.{1,40}$/', $fields['billing_address_1'])){
         $errors->add('validation', ' Street Address 1 field can not be more than 40 characters.');
     }
}

add_action('woocommerce_after_checkout_validation' , 'wck_validate_billing_address_2' , 10, 2 );
function wck_validate_billing_address_2( $fields, $errors ){
    if ( !empty($_POST['billing_address_2'])) {
     if(!preg_match('/^.{1,40}$/', $fields['billing_address_2'])){
         $errors->add('validation', ' Street Address field 2 can not be more than 40 characters.');
     }
    }
}

add_action('woocommerce_after_checkout_validation' , 'wck_validate_billing_city' , 10, 2 );
function wck_validate_billing_city( $fields, $errors ){
     if(!preg_match('/^.{1,25}$/', $fields['billing_city'])){
         $errors->add('validation', 'City field can not be more than 25 characters.');
     }
}


/*add_action('woocommerce_after_checkout_validation' , 'wck_validate_billing_state' , 10, 2 );
function wck_validate_billing_state( $fields, $errors ){
    if($fields['billing_state']){
     if(!preg_match('/^.{1,3}$/', $fields['billing_state'])){
         $errors->add('validation', 'State/County field can not be more than 3 characters.');
     }
}
} */

// Customize internal server error message
function custom_internal_server_error_message($error_message) {
    return "We Received your Order, but an issue occurred. We will investigate and there is no further action on your part. Thank you";
}
add_filter('wp_fatal_error_handler_message', 'custom_internal_server_error_message');

// Remove Category in Breadcrumb
add_filter( 'avia_breadcrumbs_trail', 'remove_woocommerce_category_breadcrumb_trail', 20, 2 );
function remove_woocommerce_category_breadcrumb_trail( $trail, $args ){
    if( is_product() ) {
        unset( $trail[count($trail)-2] );
    }
    return $trail;
}


