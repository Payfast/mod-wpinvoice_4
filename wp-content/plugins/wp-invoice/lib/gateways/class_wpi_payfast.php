<?php
/**
Name: PayFast
Class: wpi_payfast
Internal Slug: wpi_payfast
JS Slug: wpi_payfast
Version: 1.0.0
Description: Provides the PayFast for payment options

 * PayFast WP-Invoice Payments Plug in
 * 
 * Copyright (c) 2009-2013 PayFast (Pty) Ltd
 * 
 * LICENSE:
 * 
 * This payment module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or (at
 * your option) any later version.
 * 
 * This payment module is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
 * License for more details.
 * 
 * @author     Ron Darby
 * @copyright  2009-2013 PayFast (Pty) Ltd
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
*/


class wpi_payfast extends wpi_gateway_base 
{
  /**
   * Constructor
   */
  
  function __construct() 
  {
    parent::__construct();    

    /**
   * Payment settings
   *
   * @var array
   */
  $this->options = array(
    'name' => 'PayFast',
    'allow' => '',
    'default_option' => '',
    'settings' => array(
      'payfast_merchantId' => array(
        'label' => "PayFast Merchant ID",
        'value' => ''
      ),
      'payfast_merchantKey' => array(
        'label' => "PayFast Merchant Key",
        'value' => ''
      ),
      'payfast_passphrase' => array(
      'label' => "PayFast Passphrase",
      'value' => ''
      ),
      'payfast_test_mode' => array(
        'label' => "Use in Test Mode",
        'description' => "Use PayFast SandBox for test mode",
        'type' => 'select',
        'value' => 'true',
        'data' => array(
          'false' => "No",
          'true' => "Yes"
        )
      ),
      'payfast_debug_mode' => array(
        'label' => "Use Debug Mode",
        'description' => "Create a debug file with ITN callbacks",
        'type' => 'select',
        'value' => 'true',
        'data' => array(
          'false' => "No",
          'true' => "Yes"
        )
      )
    )
  );

  /**
   * Fields list for frontend
   */
  $this->front_end_fields = array(

    'customer_information' => array(

      'first_name'  => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'first_name',
        'label' => 'First Name'
      ),

      'last_name'   => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'last_name',
        'label' => 'Last Name'
      ),

      'user_email'  => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'email_address',
        'label' => 'Email Address'
      ),

      'phonenumber' => array(
        array(
          'type'  => 'text',
          'class' => 'text-input small',
          'name'  => 'night_phone_a'
        ),
        array(
          'type'  => 'text',
          'class' => 'text-input small',
          'name'  => 'night_phone_b'
        ),
        array(
          'type'  => 'text',
          'class' => 'text-input small',
          'name'  => 'night_phone_c'
        )
      ),

      'streetaddress'     => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'address1',
        'label' => 'Address'
      ),

      'city'        => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'city',
        'label' => 'City'
      ),

      'state'       => array(
        'type'   => 'text',
        'class'  => 'text-input',
        'name'   => 'state',
        'label'  => 'State/Province'
      ),

      'zip'         => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'zip',
        'label' => 'Zip/Postal Code'
      ),

      'country'     => array(
        'type'   => 'text',
        'class'  => 'text-input',
        'name'   => 'country',
        'label'  => 'Country'
      )

    )

  );
    $this->options['settings']['itn']['value'] = admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_payfast');
  }

  /**
     *
     * @param type $this_invoice
     */
    function recurring_settings( $this_invoice ) 
    {
      ?>
      <h4><?php _e( 'PayFast Payments', WPI ); ?></h4>
      <p><?php _e( 'Currently PayFast gateway does not support Recurring Billing', WPI ); ?></p>
      <?php
    }

  /**
   * Overrided payment process for payfast
   *
   * @global type $invoice
   * @global type $wpi_settings
   */
  static function process_payment() 
  {
     global $invoice;

    $crm_data = $_REQUEST['crm_data'];
    $wp_users_id = $invoice['user_data']['ID'];

    $invoice = new WPI_Invoice();
    $invoice->load_invoice("id={$_POST['invoice_id']}"); 

    $crm_data    = $_REQUEST['crm_data'];
    $invoice_id  = $invoice->data['invoice_id'];
    $wp_users_id = $invoice->data['user_data']['ID'];
    $post_id     = wpi_invoice_id_to_post_id($invoice_id);
    
    // update user data
    update_user_meta($wp_users_id, 'last_name', $_REQUEST['last_name']);
    update_user_meta($wp_users_id, 'first_name', $_REQUEST['first_name']);
    update_user_meta($wp_users_id, 'city', $_REQUEST['city']);
    update_user_meta($wp_users_id, 'state', $_REQUEST['state']);
    update_user_meta($wp_users_id, 'zip', $_REQUEST['zip']);
    update_user_meta($wp_users_id, 'streetaddress', $_REQUEST['address1']);
    update_user_meta($wp_users_id, 'phonenumber', $_REQUEST['night_phone_a'].'-'.$_REQUEST['night_phone_b'].'-'.$_REQUEST['night_phone_c']);
    update_user_meta($wp_users_id, 'country', $_REQUEST['country']);

    if ( !empty( $crm_data ) ) self::user_meta_updated( $crm_data );

    echo 1 ;

  }

  
  /**
   * wpi_payment_fields
   *
   * Render fields
   *
   * @date    2013-08-02
   * @version 1.0.0
   * @access  public
   *
   * @author  Ron    Darby    ron.darby@payfast.co.za
   * @since   1.0.0 
   *
   * @param array $invoice
   */
  function wpi_payment_fields( $invoice ) 
  {

    $this->front_end_fields = apply_filters( 'wpi_crm_custom_fields', $this->front_end_fields, 'crm_data' );

    if ( !empty( $this->front_end_fields ) ) {
      // For each section
      foreach( $this->front_end_fields as $key => $value ) {
        // If section is not empty
        if ( !empty( $this->front_end_fields[ $key ] ) ) {
          $html = '';
          ob_start();

          ?>
          <ul class="wpi_checkout_block">
            <li class="section_title"><?php _e( ucwords( str_replace('_', ' ', $key) ), WPI); ?></li>
          <?php
          $html = ob_get_contents();
          ob_end_clean();
          echo $html;
          // For each field
          foreach( $value as $field_slug => $field_data ) {

            // If field is set of 3 fields for payfast phone number
            if ( $field_slug == 'phonenumber' ) {

              echo '<li class="wpi_checkout_row"><div class="control-group"><label class="control-label">'.__('Phone Number', WPI).'</label><div class="controls">';

              $phonenumber = !empty($invoice['user_data']['phonenumber']) ? $invoice['user_data']['phonenumber'] : "---";
              $phone_array = split('[/.-]', $phonenumber);

              foreach( $field_data as $field ) {
                //** Change field properties if we need */
                $field = apply_filters('wpi_payment_form_styles', $field, $field_slug, 'wpi_paypal');
                ob_start();
                ?>
                  <input type="<?php echo esc_attr( $field['type'] ); ?>" class="<?php echo esc_attr( $field['class'] ); ?>"  name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $phone_array[key($phone_array)] ); next($phone_array); ?>" />
                <?php
                $html = ob_get_contents();
                ob_end_clean();
                echo $html;
              }

              echo '</div></div></li>';

            }
            //** Change field properties if we need */
            $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_paypal');

            $html = '';
            switch ( $field_data['type'] ) {
              case self::TEXT_INPUT_TYPE:

                ob_start();

                ?>

                <li class="wpi_checkout_row">
                  <div class="control-group">
                    <label class="control-label" for="<?php echo esc_attr( $field_slug ); ?>"><?php _e($field_data['label'], WPI); ?></label>
                    <div class="controls">
                      <input type="<?php echo esc_attr( $field_data['type'] ); ?>" class="<?php echo esc_attr( $field_data['class'] ); ?>"  name="<?php echo esc_attr( $field_data['name'] ); ?>" value="<?php echo !empty($invoice['user_data'][$field_slug])?$invoice['user_data'][$field_slug]:'';?>" />
                    </div>
                  </div>
                </li>

                <?php

                $html = ob_get_contents();
                ob_end_clean();

                break;

              case self::SELECT_INPUT_TYPE:

                ob_start();

                ?>

                <li class="wpi_checkout_row">
                  <label for="<?php echo esc_attr( $field_slug ); ?>"><?php _e($field_data['label'], WPI); ?></label>
                  <?php echo WPI_UI::select("name={$field_data['name']}&values={$field_data['values']}&id={$field_slug}&class={$field_data['class']}"); ?>
                </li>

                <?php

                $html = ob_get_contents();
                ob_clean();

                break;

              default:
                break;
            }

            echo $html;

          }
          echo '</ul>';
        }
      }

    }

  }


  /**
   * server_callback
   *
   * Handler for PayFast ITN queries
   *
   * @date    2013-08-02
   * @version 1.0.1
   * @access  public
   *
   * @author  Ron    Darby    ron.darby@payfast.co.za
   * @since   1.0.0  
   * @author  Ron    Darby    ron.darby@payfast.co.za
   * @since   1.0.1 
   *
   */
  static function server_callback()
  {
    
    if ( empty( $_POST ) ) die(__('Direct access not allowed', WPI));    

    $invoice = new WPI_Invoice();
    $invoice->load_invoice("id={$_POST['m_payment_id']}");   

    $pfError = false;
    $pfErrMsg = '';
    $pfDone = false;
    $pfData = array();
    $pfHost = ( ( $invoice->data['billing']['wpi_payfast']['settings']['test_mode']['value'] == 'true' ) ? 'www' : 'sandbox' ) . '.payfast.co.za';
    $pfOrderId = '';
    $pfParamString = '';

    include('payfast/payfast_common.inc');

    pflog( 'PayFast ITN call received' );

    header( 'HTTP/1.0 200 OK' );
    flush();

    if( !$pfError && !$pfDone )
    {
        pflog( 'Get posted data' );
    
        // Posted variables from ITN
        $pfData = pfGetData();
    
        pflog( 'PayFast Data: '. print_r( $pfData, true ) );
    
        if( $pfData === false )
        {
            $pfError = true;
            $pfErrMsg = PF_ERR_BAD_ACCESS;
        }
    }


    if( !$pfError && !$pfDone )
    {
        pflog( 'Verify security signature' );
    
        // If signature different, log for debugging
        if( !pfValidSignature( $pfData, $pfParamString ) )
        {
            $pfError = true;
            $pfErrMsg = PF_ERR_INVALID_SIGNATURE;
        }
    }

    //// Verify source IP (If not in debug mode)
    if( !$pfError && !$pfDone && !PF_DEBUG )
    {
        pflog( 'Verify source IP' );
    
        if( !pfValidIP( $_SERVER['REMOTE_ADDR'] ) )
        {
            $pfError = true;
            $pfErrMsg = PF_ERR_BAD_SOURCE_IP;
        }
    }

    //// Get internal cart
    if( !$pfError && !$pfDone )
    {
        pflog( "Purchase:\n". print_r( $invoice, true )  );
    }

    //// Verify data received
    if( !$pfError )
    {
        pflog( 'Verify data received' );
    
        $pfValid = pfValidData( $pfHost, $pfParamString );
    
        if( !$pfValid )
        {
            $pfError = true;
            $pfErrMsg = PF_ERR_BAD_ACCESS;
        }
    }
        
    //// Check data against internal order
    if( !$pfError && !$pfDone )
    {
        pflog( 'Check data against internal order' );

        // Check order amount
        if( !pfAmountsEqual( $pfData['amount_gross'], $invoice->data['net'] ) )
        {
            $pfError = true;
            $pfErrMsg = PF_ERR_AMOUNT_MISMATCH;
        }       
    }

    /** Verify callback request */
    if ( !$pfError && !$pfDone ) 
    {

          pflog( 'Check status and update order' );
      
          /** PayFast Cart. Used for SPC */
          switch( $pfData['payment_status'] ) 
          {
            case 'PENDING':
              /** Mark invoice as Pending */
              wp_invoice_mark_as_pending( $pfData['m_payment_id'] );
              do_action( 'wpi_payfast_pending_ipn', $_POST );
              break;
            case 'COMPLETE':
              /** Add payment amount */
              $event_note = sprintf(__('%s paid via PayFast', WPI), WPI_Functions::currency_format(abs($pfData['amount_gross']), $pfData['m_payment_id']));
              
              $event_amount = (float)$pfData['amount_gross'];
              $event_type   = 'add_payment';
              /** Log balance changes */
              $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
              /** Log payer email */
              $payer_email = sprintf(__("PayFast Payer email: %s", WPI), $pfData['email_address']);
              $invoice->add_entry("attribute=invoice&note=$payer_email&type=update");
              $invoice->save_invoice();
              /** ... and mark invoice as paid */
              wp_invoice_mark_as_paid( $pfData['m_payment_id'], $check = true );
              send_notification( $invoice->data );
              do_action( 'wpi_payfast_complete_ipn', $_POST );
              break;

            default: break;

          }
      }
  }
}