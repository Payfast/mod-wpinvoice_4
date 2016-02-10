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

/* This function handles the submit event */
var wpi_payfast_submit = function(){

    jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
    jQuery( ".loader-img" ).show();
    var success = false;
    var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
    jQuery.ajaxSetup({
        async: false
    });
    jQuery.post(
        url,
        jQuery("#online_payment_form-wpi_payfast").serialize(),
        function(msg){
            jQuery.ajaxSetup({
                async: true
            });
            if ( msg.success == 1 ) {
                success = true;
            } else if ( msg.error == 1 ) {
              var message = '';
              jQuery.each( msg.data.messages, function(k, v){
                message += v +'\n\n';
              });
              alert( message );
              location.reload(true);
            }
        }, 'json');
    return success;

};

function wpi_payfast_init_form() {
    jQuery("#online_payment_form_wrapper").trigger('formLoaded');
}
