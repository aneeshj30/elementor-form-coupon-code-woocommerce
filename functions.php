//This code will go to your functions.php file in the parent theme/ child theme. Child theme is recommended. 



add_action( 'elementor_pro/forms/new_record', 'ele_form_new_record', 10, 10 );

function ele_form_new_record($record , $handler) {
    $form_name = $record->get_form_settings( 'form_name' );
	if ( 'Free Trial Form' == $form_name ) {


	//normalize the fields
	$raw_fields = $record->get( 'fields' );
	$fields = [];
	foreach ( $raw_fields as $id => $field ) {
		
		$fields[ $id ] = $field['value'];
	}
	//email address submitted in the form
	$email_free_trial_form = $fields['email'];
	
	
	global $woocommerce;
	//$characters = "ABCDEFGHJKMNPQRSTUVWXYZ23456789";
    //$char_length = "8";
    //$random_string = substr( str_shuffle( $characters ),  0, $char_length );
	$coupon_code = uniqid(); // Code created using the random string snippet.
    /**
 * Create a Coupon Programmatically
 *
 * @author Misha Rudrastyh
 * @url https://rudrastyh.com/woocommerce/create-coupon-programmatically.html
 */
$coupon = new WC_Coupon();

$coupon->set_code( $coupon_code );

$coupon->set_description( 'Some coupon description.' );

// General tab

// discount type can be 'fixed_cart', 'percent' or 'fixed_product', defaults to 'fixed_cart'
$coupon->set_discount_type( 'percent' );

// discount amount
$coupon->set_amount( 100 );

// allow free shipping
//$coupon->set_free_shipping( true );

// coupon expiry date
//$coupon->set_date_expires( '' );


// Usage Restriction

// minimum spend
//$coupon->set_minimum_amount();

// maximum spend
//$coupon->set_maximum_amount( 50000 );

// individual use only
$coupon->set_individual_use( false );

// exclude sale items
//$coupon->set_exclude_sale_items( true );

// products
$coupon->set_product_ids( array( 902, 926 ) );

// exclude products
//$coupon->set_excluded_product_ids( array( 15, 16 ) );

// categories
//$coupon->set_product_categories( array( 17 ) );

// exclude categories
//$coupon->set_excluded_product_categories( array( 19, 20 ) );

// allowed emails
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
//$ajax_handler->data['ftf_coupon_code'] = $coupon_code;
$handler->add_response_data( true, $output );
	}
	
}
add_action( 'elementor_pro/forms/validation/email', function( $field, $record, $ajax_handler ) {
	$form_name = $record->get_form_settings( 'form_name' );
	if ( 'Free Trial Form' == $form_name ) {

	//normalize the fields
	$raw_fields = $record->get( 'fields' );
	$fields = [];
	foreach ( $raw_fields as $id => $field ) {
		
		$fields[ $id ] = $field['value'];
	}
	//email address submitted in the form
	$email_free_trial_form = $fields['email'];
	 global $wpdb;
	
	//$email_free_trial_form ='sarath@white-space.studio';
	//print_r($email_free_trial_form);
	//$submission_ids = $wpdb->get_results("SELECT submission_id FROM wp_e_submissions_values WHERE `value` = '".$email_free_trial_form."'", ARRAY_A);
	//$cars = array("Volvo", "BMW", "Toyota");
	//foreach( $submission_ids as $submission_id ) {
    //$sub_id= $submission_id;
  //}
  //$submission_ids = $wpdb->get_results("SELECT ID, post_title FROM wp_posts");
	
	$ele_form_name = 'Free Trial Form';
	//$form_name = $wpdb->get_results("SELECT id FROM wp_e_submissions WHERE `form_name` = '".$ele_form_name."'", ARRAY_A);
	/*
	1. select submission ids from table using email
	2. Find count of submissions from main table using submission id = [ids] and forname = 'Free trial form'
	3. if count > 1 then reject else success
    */
	$submissions = $wpdb->get_results("SELECT submission_id FROM wp_e_submissions_values WHERE `value` = '".$email_free_trial_form."'");
	$_ids = array();
	foreach ($submissions as $submission){
		array_push($_ids, $submission->submission_id);
	}
	$ids = join("','",$_ids);
	// [1,2,3]
	// '1,2,3'
	$result = $wpdb->get_results("SELECT count(id) as count FROM wp_e_submissions WHERE `form_name` = '".$ele_form_name."' AND id IN ('$ids')");
	//var_dump($result[0]);
	
	if($result[0]->count > 0) {
		//$ajax_handler->data['output'] = 'You have already submitted a form';
		$ajax_handler->add_error_message( 'Seems like you already registered to get a free trial using this Email Address: '.$email_free_trial_form.'.' );

	} 
	
    }
}, 10, 3 );
