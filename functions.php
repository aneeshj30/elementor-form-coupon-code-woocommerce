<?php
/**
 * This code will go to your functions.php file in the parent theme/child theme. Child theme is recommended. 
 * This snippet will help you to automatically generate coupon code after successful submission of elementor form. 
 * This snippet also validates if a user already used the same email address to submit the form if so it will return an error.
 * You can also show the coupon code by just adding the code success-message.html in this reposity. 
 * 
*/
/*
	|--------------------------------------------------------------------------
	| Important Notes
	|--------------------------------------------------------------------------
	|
	| Please replace 'MY_FORM_NAME' in the below code with the form names that you provided to your elementor form.
	| If you are adding this in your theme file please don't use PHP opening <?php and Closing ?> tags. 
	|
*/

//This is the place to add custom form handlers.
add_action( 'elementor_pro/forms/new_record', 'ele_form_new_record', 10, 10 );

function ele_form_new_record($record , $handler) {
    //Get the form names from Elementor.
    $form_name = $record->get_form_settings( 'form_name' );
    //Replace MY_FORM_NAME with the name you gave your form
    if ( 'MY_FORM_NAME' == $form_name ) {
	    //normalize the fields
	    $raw_fields = $record->get( 'fields' );
		$fields = [];
		foreach ( $raw_fields as $id => $field ) {
			
			$fields[ $id ] = $field['value'];
		}
		//email address submitted in the form
		$email_free_trial_form = $fields['email'];
		global $woocommerce;
		//Create code created using the random string snippet.
		$coupon_code = uniqid(); // 
		$coupon = new WC_Coupon();
		$coupon->set_code( $coupon_code );
		$coupon->set_description( 'Some coupon description.' );
		// Set discount type here. You can choose these values: 'fixed_cart', 'percent' or 'fixed_product', default is 'fixed_cart'
		$coupon->set_discount_type( 'percent' );
		// Discount amount. Only enter numbers.
		$coupon->set_amount( 10 );
		// Allow free shipping. Set the value to true or false
		//$coupon->set_free_shipping( true );
		// Coupon expiry date. 
		//$coupon->set_date_expires( '' );
		// Below are the usage restrictions. This can be used to control where  the coupon code will be applied.
		// Minimum spend by a user. This should be in numbers. Do not put currency like $,CAD.
		//$coupon->set_minimum_amount(1);
		// Maximum spend by a user
		//$coupon->set_maximum_amount( 50000 );
		// Individual use only
		$coupon->set_individual_use( false );
		// Exclude sale items
		//$coupon->set_exclude_sale_items( true );
		// Enter individual product id's of the products you want to include this coupon to.
		$coupon->set_product_ids( array( 902, 926 ) );
		// Enter individual product id's of the products you want to exclude this coupon to.
		//$coupon->set_excluded_product_ids( array( 15, 16 ) );
		// Enter category id's you want to include this coupon to.
		//$coupon->set_product_categories( array( 17,25 ) );
		// Enter category id's you want to exclude this coupon to.
		//$coupon->set_excluded_product_categories( array( 19, 20 ) );
		// Adds email address submitted in the elementor form into email restriction field in the backend.
		$coupon->set_email_restrictions( 
			array( $email_free_trial_form )
		);
		// Usage limit tab
		// usage limit per coupon
		$coupon->set_usage_limit( 2 );
		// limit usage to X items
		$coupon->set_limit_usage_to_x_items( 1 );
		// usage limit per user
		$coupon->set_usage_limit_per_user( 2 );
		$coupon->save();
		$output['result'] = $coupon_code;
		$handler->add_response_data( true, $output );
	}
	
}

/**
 * This action validates if a user already submitted a particular form in elementor. Basically this rule will be applicable only for MY_FORM_NAME
*/

add_action( 'elementor_pro/forms/validation/email', function( $field, $record, $ajax_handler ) {
	
	// Get the form names from Elementor.
	$form_name = $record->get_form_settings( 'form_name' );
	// Replace MY_FORM_NAME with the name you gave your form
	if ( 'MY_FORM_NAME' == $form_name ) {
		//normalize the fields
		$raw_fields = $record->get( 'fields' );
		$fields = [];
		foreach ( $raw_fields as $id => $field ) {
			$fields[ $id ] = $field['value'];
	    }
		//email address submitted in the form
		$email_custom_validate = $fields['email'];
		// Get Wordpress Database
		global $wpdb;
		//Set the Elementor form name
		$ele_form_name = 'MY_FORM_NAME';
		// Get all the submissions by the submitted user from the Elementor table.
		$submissions = $wpdb->get_results("SELECT submission_id FROM wp_e_submissions_values WHERE `value` = '".$email_custom_validate."'");
		$_ids = array();
		foreach ($submissions as $submission){
		    array_push($_ids, $submission->submission_id);
		}
		$ids = join("','",$_ids);
                // Count the number of submissions by the user
		$result = $wpdb->get_results("SELECT count(id) as count FROM wp_e_submissions WHERE `form_name` = '".$ele_form_name."' AND id IN ('$ids')");
		//var_dump($result[0]);
		if($result[0]->count > 0) {
			//Error Message. Add your custom message here. This error message will return the email address as well.
		    $ajax_handler->add_error_message( 'Seems like you have already registered: '.$email_custom_validate.'.' );
		} 
		
    }
}, 10, 3 );

?>
