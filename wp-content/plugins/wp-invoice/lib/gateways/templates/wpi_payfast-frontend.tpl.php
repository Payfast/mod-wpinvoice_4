<?php 
/**
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

require_once( ud_get_wp_invoice()->path( "lib/class_template_functions.php", 'dir' ) );
if($invoice['billing']['wpi_payfast']['settings']['payfast_test_mode']['value'] == 'true')
{
  $url = 'sandbox.payfast';
  $merchant_id = '10000100';
  $merchant_key = '46f0cd694581a';
}
else
{
  $url = 'www.payfast';
  $merchant_id = $invoice['billing']['wpi_payfast']['settings']['payfast_merchantId']['value'];
  $merchant_key = $invoice['billing']['wpi_payfast']['settings']['payfast_merchantKey']['value'];
}
$formData = array(
    'merchant_id'=>$merchant_id,
    'merchant_key'=>$merchant_key,
    'return_url'=>get_invoice_permalink($invoice['invoice_id']),
    'cancel_url'=>get_invoice_permalink($invoice['invoice_id']),
    'notify_url'=>admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_payfast'),
    'm_payment_id'=>$invoice['invoice_id'],
    'amount'=>number_format( (float)$invoice['net'], 2, '.', '' ),
    'item_name'=>$invoice['post_title'],
    );

// Add subscription variables if subscription billing
$frequency = $invoice['recurring']['wpi_payfast']['interval'];
$cycles = (int)$invoice['recurring']['wpi_payfast']['cycles'];

if ( !empty( $frequency ) && !empty( $cycles ) )
{
 //   $formData['m_subscription_id'] = $invoice['invoice_id'];
    $formData['custom_str1'] = gmdate( 'Y-m-d' );
    $formData['subscription_type'] = 1;
    $formData['billing_date'] = gmdate( 'Y-m-d' );
    $formData['recurring_amount'] = number_format( (float)$invoice['net'], 2, '.', '' );
    $formData['frequency'] = $frequency;
    $formData['cycles'] = $cycles;
}

// Create output string
foreach( $formData as $key => $val )
{
    if (!empty( $val ) )
    {
        $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
    }
}
$passPhrase = $invoice['billing']['wpi_payfast']['settings']['payfast_passphrase']['value'];

if( empty( $passPhrase ) || $invoice['billing']['wpi_payfast']['settings']['payfast_test_mode']['value'] == 'true' )
{
    $pfOutput = substr( $pfOutput, 0, -1 );
}
else
{
    $pfOutput = $pfOutput."passphrase=".urlencode( $passPhrase );
}
$formData['signature'] = md5($pfOutput);
$formData['user_agent'] = 'WPInvoice 4.x';
?>
<form id="process_payment_form" class="wpi_checkout online_payment_form <?php print $this->type; ?> clearfix">
  <input type='hidden' value="<?php echo $invoice['invoice_id'];?>" name="invoice_id">
  <?php do_action('wpi_payment_fields_payfast', $invoice); ?>
</form>
<form id='payfast_payment' action="https://<?php echo $url; ?>.co.za/eng/process" method="post" class="wpi_checkout <?php print $this->type; ?> clearfix">
<?php 
foreach($formData as $k=>$v)
{
echo "    <input id='$k' type='hidden' name='$k' value='$v'>";
}
?>  
    <input id='process_payment' type="submit" value="<?php _e('Pay Now With PayFast ', WPI); ?>">          
</form>