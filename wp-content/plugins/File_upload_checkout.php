<?php
/**
 * @package File upload checkout
 * @version 1.7.2
 */
/*
Plugin Name: File Upload Checkout
Plugin URI: 
Description: Used to add file upload to checkout and send in email as attachment
Author: Shishir
Version: 1.0.0
Author URI: 
*/

add_action('woocommerce_after_order_notes', 'add_file_upload_checkout');

 function add_file_upload_checkout(){ 
     ?>
        <p class="form-row form-row-wide validate-required">
            <?php wp_nonce_field( 'file_upload_checkout', '_add_file_upload_checkout' ); ?>  
            <label for="file_upload_checkout" class="">Upload File to go with Shipment such as Tasting Notes&nbsp;</label>
            <span class="woocommerce-input-wrapper">
                <input type="hidden" name="add_file_upload_name" id="add_file_upload_name">
                <input type="file" class="input-text " name="file_upload_checkout" id="file_upload_checkout" placeholder="Upload File" value="" >
            </span>
        </p>
        </br>
        </br>
        <script>
        
        jQuery(document).ready(function ($) {            
            $('#file_upload_checkout').on('change', function(){
                
                //on change event  
                formdata = new FormData();
                var url = $(this).val()
                var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
            
                if($(this).prop('files').length > 0 )
                { 
                    if(ext == "pdf" || ext == 'jpg' || ext == 'jpeg' ||  ext == 'doc' ||  ext == 'docx' ||  ext == 'png'){
                        file =$(this).prop('files')[0];
                        _nonce = $('#_add_file_upload_checkout').val();
                        formdata.append("file_upload_checkout", file);
                        formdata.append("_nonce", _nonce);    
                        formdata.append("action", 'file_upload_checkout'); 
                        $.ajax({
                            url : readmelater_ajax.ajax_url,
                            type : 'post',
                            data : formdata,
                            processData: false,
                            contentType: false,
                            success : function( response ) {
                                $('#add_file_upload_name').val(response);
                            }
                        });
                    }else{
                        alert('Please upload PDF,JPEG, JPG, PNG, DOC, DOCX files.');
                    }
                }
            });
        });
        </script>
     <?php
 }

 add_action( 'wp_ajax_file_upload_checkout', 'add_file_upload_checkout_name' );

 function add_file_upload_checkout_name(){
    $_filter = true; // For the anonymous filter callback below.
    add_filter( 'upload_dir', function( $arr ) use( &$_filter ){
        if ( $_filter ) {
            $folder = '/checkout_upload'; // No trailing slash at the end.
            $arr['path'] = $arr['basedir'] .$folder;
            $arr['url'] = $arr['basedir'] .$folder;
        }
    
        return $arr;
    } );
    $ext = pathinfo($_FILES["file_upload_checkout"]["name"], PATHINFO_EXTENSION);
    $filename = time() . '.'. $ext;
    $upload = wp_upload_bits($filename, null, file_get_contents($_FILES["file_upload_checkout"]["tmp_name"]));
    $_filter = false;
    echo $filename; 
    die;
}

add_filter( 'woocommerce_email_attachments', 'bbloomer_attach_pdf_to_emails', 10, 4 );
 
function bbloomer_attach_pdf_to_emails( $attachments, $email_id, $order, $email ) {
    if(isset($_POST['add_file_upload_name']) && !empty($_POST['add_file_upload_name'])
    && !in_array($email_id, ['low_stock', 'no_stock', 'backorder'])){
        $filename = $_POST['add_file_upload_name'];
        $order_id = $order->get_id();
        update_post_meta($order_id, '_file_upload_checkout', $filename);
        $email_ids = array( 'new_order', 'customer_processing_order' );
        if ( in_array ( $email_id, $email_ids ) ) {
            $upload_dir = wp_upload_dir();
            $attachments[] = $upload_dir['basedir'] ."/checkout_upload"."/". $filename;
        }
    }
    return $attachments;
}


add_action('woocommerce_before_order_object_save', 'add_attachment_note',10,2);

function add_attachment_note($order, $data){
    $note = 'THERE IS AN ATTACHMENT FOR THIS ORDER. PLEASE CHECK THE SAMPLES EMAIL.';
    $order_note = $order->get_customer_note();
    if(isset($_POST['add_file_upload_name']) && !empty($_POST['add_file_upload_name']) && !strpos($order_note, $note)){
        $order_note = $order_note.' '.$note;
        $order->set_customer_note($order_note);
    }
}

// "Order notes" field validation
add_action('woocommerce_checkout_process', 'order_comments_field_validation');
function order_comments_field_validation() {
    if(isset($_POST['order_comments']) && !empty($_POST['order_comments'])){
        $strlen = strlen($_POST['order_comments']);
        if ( !empty($_POST['add_file_upload_name']) && $strlen > 179){
            wc_add_notice( __( 'Maximum 180 character allowed in Order notes.' ), 'error' );
        }
        if ( empty($_POST['add_file_upload_name']) && $strlen > 250){
            wc_add_notice( __( 'Maximum 250 character allowed in Order notes.' ), 'error' );
        }
    }
}