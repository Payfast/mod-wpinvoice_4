/* Our Rules for this type of form */
var wpi_payfast_rules = { 
  "name_first": {
    required: true
  },
  "name_last": {
    required: true
  }
};

/* Our Messages for this type of form */
var wpi_payfast_messages = { 
  "name_first": {
    required: "First name is required."
  },
  "name_last": {
    required: "Last name is required."
  }
};

/* This function adds to form validation, and returns true or false */
var wpi_payfast_validate_form = function(){
  /* Just return, no extra validation needed */
  return true;
};


jQuery(document).ready(function(){
  jQuery( "#payfast_payment" ).submit(function(e){
    e.preventDefault();
    var paymentForm = jQuery(this);
    var url = wpi_ajax.url+"?action=wpi_gateway_process_payment&type=wpi_payfast";
    var userInfo = jQuery('#process_payment_form').serialize();
    jQuery.ajax({
            url: url,
            type: 'post',
            data: userInfo,
            success: function(msg){
              if ( msg == 1 && wpi_payfast_validate_form) {
                paymentForm.unbind('submit').submit();
                }
              }
            })
  });
})