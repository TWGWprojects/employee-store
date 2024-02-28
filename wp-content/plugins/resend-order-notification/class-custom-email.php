<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

class Custom_Email extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'resend_email';
        $this->customer_email = true;
        $this->title          = __( 'Resend order details to customer', 'woocommerce' );
        $this->description    = __( 'Customer Order emails can be sent to customers containing their order information.', 'woocommerce' );
        $this->template_html  = 'emails/customer-invoice.php';
        $this->template_plain = 'emails/plain/customer-invoice.php';
        $this->placeholders   = array(
            '{order_date}'   => '',
            '{order_number}' => '',
        );
        $this->template_base  = CUSTOM_TEMPLATE_PATH;
        add_action( 'woocommerce_order_action_resend_email', array( $this, 'trigger' ) );
        // Call parent constructor.
        parent::__construct();

        $this->manual = true;
    }

    /**
     * Get email subject.
     *
     * @param bool $paid Whether the order has been paid or not.
     * @since  3.1.0
     * @return string
     */
    public function get_default_subject( $paid = false ) {
        if ( $paid ) {
            return __( 'Details for order #{order_number} on {site_title}', 'woocommerce' );
        } else {
            return __( 'Your {site_title} order has been received!', 'woocommerce' );
        }
    }

    /**
     * Get email heading.
     *
     * @param bool $paid Whether the order has been paid or not.
     * @since  3.1.0
     * @return string
     */
    public function get_default_heading( $paid = false ) {
        if ( $paid ) {
            return __( 'Details for order #{order_number}', 'woocommerce' );
        } else {
            return __( 'Your details for order #{order_number}', 'woocommerce' );
        }
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_subject() {
        $subject = $this->get_option( 'subject', $this->get_default_subject() );
        return apply_filters( 'woocommerce_email_subject_customer_resend_email', $this->format_string( $subject ), $this->object, $this );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_heading() {
        $heading = $this->get_option( 'heading', $this->get_default_heading() );
        return apply_filters( 'woocommerce_email_heading_customer_resend_email', $this->format_string( $heading ), $this->object, $this );
    }

    /**
     * Default content to show below main email content.
     *
     * @since 3.7.0
     * @return string
     */
    public function get_default_additional_content() {
        return __( 'Thanks for using {site_address}!', 'woocommerce' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int      $order_id The order ID.
     * @param WC_Order $order Order object.
     */
    public function trigger( $order_id, $order = false ) {
        $this->setup_locale();

        if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
            $order = wc_get_order( $order_id );
        }

        if ( is_a( $order, 'WC_Order' ) ) {
            $this->object                         = $order;
            $this->recipient                      = $this->object->get_billing_email();
            $this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
            $this->placeholders['{order_number}'] = $this->object->get_order_number();
        }
        
        if ( $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
            do_action( 'custom_pending_email_notification', $order_id );
        }

        $this->restore_locale();
    }

    /**
     * Get content html.
     *
     * @return string
     */
    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'order'              => $this->object,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => false,
                'email'              => $this,
            )
        );
    }

    /**
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'order'              => $this->object,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => true,
                'email'              => $this,
            )
        );
    }

    /**
     * Initialise settings form fields.
     */
    public function init_form_fields() {
        /* translators: %s: list of placeholders */
        $placeholder_text  = sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );
        $this->form_fields = array(
            'subject'      => array(
                'title'       => __( 'Subject', 'woocommerce' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'      => array(
                'title'       => __( 'Email heading', 'woocommerce' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'subject_paid' => array(
                'title'       => __( 'Subject (paid)', 'woocommerce' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject( true ),
                'default'     => '',
            ),
            'heading_paid' => array(
                'title'       => __( 'Email heading (paid)', 'woocommerce' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading( true ),
                'default'     => '',
            ),
            'additional_content' => array(
                'title'       => __( 'Additional content', 'woocommerce' ),
                'description' => __( 'Text to appear below the main email content.', 'woocommerce' ) . ' ' . $placeholder_text,
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'woocommerce' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ),
            'email_type'   => array(
                'title'       => __( 'Email type', 'woocommerce' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }
}
return new Custom_Email();
?>
