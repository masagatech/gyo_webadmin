<?php 
include('includes/configuration.php');
$gnrl->check_login();

$page_title = "Manage Translations";
$page = "translations";

$file = str_replace( 'uploads/', '', UPLOAD_PATH ).'translation.json';
	
	
	$keyArr = array(
		"err_acc_inactive" => "Error! Acc Inactive",
		"err_already_verified" => "Error! Already Verified",
		"err_drivers_not_found_try_again" => "Error! Drivers Not Found Try Again",
		"err_invalid_amount" => "Error! Invalid Amount",
		"err_invalid_auth_token" => "Error! Invalid Auth Token",
		"err_invalid_card_no" => "Error! Invalid Card No",
		"err_invalid_charge_type" => "Error! Invalid Charge Type",
		"err_invalid_email" => "Error! Invalid Email",
		"err_invalid_email_or_phone" => "Error! Invalid Email Or Phone",
		"err_invalid_key" => "Error! Invalid Key",
		"err_invalid_lang" => "Error! Invalid Lang",
		"err_invalid_old_password" => "Error! Invalid Old Password",
		"err_invalid_otp" => "Error! Invalid Otp",
		"err_invalid_password" => "Error! Invalid Password",
		"err_invalid_phone" => "Error! Invalid Phone",
		"err_invalid_pin" => "Error! Invalid Pin",
		"err_invalid_promotion_code" => "Error! Invalid Promotion Code",
		"err_invalid_referral_code" => "Error! Invalid Referral Code",
		"err_invalid_role" => "Error! Invalid Role",
		"err_invalid_status" => "Error! Invalid Status",
		"err_invalid_payment_method" => "Error! Invalid Payment Method",
		"err_msg_already_login" => "Error! Already Login",
		"err_msg_device_not_recognized" => "Error! Device Not Recognized",
		"err_msg_email_not_sent" => "Error! Email Not Sent",
		"err_msg_exists_email" => "Error! Exists Email",
		"err_msg_exists_phone" => "Error! Exists Phone",
		"err_msg_no_account" => "Error! No Account",
		"err_msg_no_device_tokens" => "Error! No Device Tokens",
		"err_msg_no_email_template" => "Error! No Email Template",
		"err_msg_no_notification_template" => "Error! No Notification Template",
		"err_msg_no_sms_template" => "Error! No Sms Template",
		"err_msg_not_logged_in" => "Error! Not Logged In",
		"err_msg_ride_alreay_accepted" => "Error! Ride Alreay Accepted",
		"err_msg_ride_alreay_cancelled" => "Error! Ride Alreay Cancelled",
		"err_msg_ride_alreay_completed" => "Error! Ride Alreay Completed",
		"err_msg_ride_alreay_confirmed" => "Error! Ride Alreay Confirmed",
		"err_msg_sms_not_sent" => "Error! Sms Not Sent",
		"err_no_records" => "Error! No Records",
		"err_no_ride" => "Error! No Ride",
		"err_no_vehicles" => "Error! No Vehicles",
		"err_not_verified" => "Error! Not Verified",
		"err_promotion_code_closed" => "Error! Promotion Code Closed",
		"err_promotion_code_expired" => "Error! Promotion Code Expired",
		"err_promotion_code_not_in_city" => "Error! Promotion Code Not In City",
		"err_promotion_code_redeemed" => "Error! Promotion Code Redeemed",
		"err_req_action" => "Error! Required Action",
		"err_req_address" => "Error! Required Address",
		"err_req_amount" => "Error! Required Amount",
		"err_req_auth_token" => "Error! Required Auth Token",
		"err_req_buzz_id" => "Error! Required Buzz Id",
		"err_req_cancel_reason" => "Error! Required Cancel Reason",
		"err_req_card_no" => "Error! Required Card No",
		"err_req_charge_info" => "Error! Required Charge Info",
		"err_req_charge_type" => "Error! Required Charge Type",
		"err_req_charges" => "Error! Required Charges",
		"err_req_city" => "Error! Required City",
		"err_req_comment" => "Error! Required Comment",
		"err_req_cvv" => "Error! Required Cvv",
		"err_req_destination_address" => "Error! Required Destination Address",
		"err_req_destination_latitude" => "Error! Required Destination Latitude",
		"err_req_destination_longitude" => "Error! Required Destination Longitude",
		"err_req_device_token" => "Error! Required Device Token",
		"err_req_driver_id" => "Error! Required Driver Id",
		"err_req_email" => "Error! Required Email",
		"err_req_email_or_phone" => "Error! Required Email Or Phone",
		"err_req_estimate_km" => "Error! Required Estimate Km",
		"err_req_estimate_time" => "Error! Required Estimate Time",
		"err_req_expiry_date" => "Error! Required Expiry Date",
		"err_req_fields" => "Error! Required Fields",
		"err_req_from_date" => "Error! Required From Date",
		"err_req_id" => "Error! Required Id",
		"err_req_image_adhar_card" => "Error! Required Image Adhar Card",
		"err_req_image_license" => "Error! Required Image License",
		"err_req_image_permit_copy" => "Error! Required Image Permit Copy",
		"err_req_image_police_copy" => "Error! Required Image Police Copy",
		"err_req_imei_number" => "Error! Required Imei Number",
		"err_req_insurance_image" => "Error! Required Insurance Image",
		"err_req_key" => "Error! Required Key",
		"err_req_lang" => "Error! Required Lang",
		"err_req_latitude" => "Error! Required Latitude",
		"err_req_login_id" => "Error! Required Login Id",
		"err_req_longitude" => "Error! Required Longitude",
		"err_req_name" => "Error! Required Name",
		"err_req_name_on_card" => "Error! Required Name On Card",
		"err_req_notification_id" => "Error! Required Notification Id",
		"err_req_old_password" => "Error! Required Old Password",
		"err_req_otp" => "Error! Required Otp",
		"err_req_password" => "Error! Required Password",
		"err_req_payment_mode" => "Error! Required Payment Mode",
		"err_req_phone" => "Error! Required Phone",
		"err_req_pickup_address" => "Error! Required Pickup Address",
		"err_req_pickup_latitude" => "Error! Required Pickup Latitude",
		"err_req_pickup_longitude" => "Error! Required Pickup Longitude",
		"err_req_pin" => "Error! Required Pin",
		"err_req_productinfo" => "Error! Required Product Info",
		"err_req_profile_image" => "Error! Required Profile Image",
		"err_req_promo_code" => "Error! Required Promo Code",
		"err_req_puc_image" => "Error! Required Puc Image",
		"err_req_rc_book_image" => "Error! Required Rc Book Image",
		"err_req_ride_cancel_reason" => "Error! Required Ride Cancel Reason",
		"err_req_ride_id" => "Error! Required Ride Id",
		"err_req_ride_time" => "Error! Required Ride Time",
		"err_req_round_id" => "Error! Required Round Id",
		"err_req_sms_body" => "Error! Required Sms Body",
		"err_req_sort_by" => "Error! Required Sort By",
		"err_req_status" => "Error! Required Status",
		"err_req_support_text" => "Error! Required Support Text",
		"err_req_support_type_id" => "Error! Required Support Type Id",
		"err_req_to_date" => "Error! Required To Date",
		"err_req_track_code" => "Error! Required Track Code",
		"err_req_transaction_id" => "Error! Required Transaction Id",
		"err_req_type" => "Error! Required Type",
		"err_req_type_id" => "Error! Required Type Id",
		"err_req_user_credentials" => "Error! Required User Credentials",
		"err_req_user_id" => "Error! Required User Id",
		"err_req_vehicle_id" => "Error! Required Vehicle Id",
		"err_req_vehicle_number" => "Error! Required Vehicle Number",
		"err_req_vehicle_type" => "Error! Required Vehicle Type",
		"err_validation_password" => "Error! Validation Password",
		"err_validation_phone" => "Error! Validation Phone",
		"error" => "Error",
		"error_file_upload" => "Error File Upload",
		"lbl_accept" => "Label! Accept",
		"lbl_add_amount" => "Label! Add Amount",
		"lbl_add_extra_amount" => "Label! Add Extra Amount",
		"lbl_add_money" => "Label! Add Money",
		"lbl_add_money_value" => "Label! Add Money Value",
		"lbl_applicable_surcharge" => "Label! Applicable Surcharge",
		"lbl_apply_code" => "Label! Apply Code",
		"lbl_apply_pin" => "Label! Apply Pin",
		"lbl_authentication_code" => "Label! Authentication Code",
		"lbl_available" => "Label! Available",
		"lbl_base_fare" => "Label! Base Fare",
		"lbl_best_sarathi" => "Label! Best Sarathi",
		"lbl_book_a_ride" => "Label! Book A Ride",
		"lbl_book_your_ride" => "Label! Book Your Ride",
		"lbl_booking_cancel" => "Label! Booking Cancel",
		"lbl_booking_sucessful" => "Label! Booking Sucessful",
		"lbl_by_wallet" => "Label! By Wallet",
		"lbl_cab_is_not_moving_in_my_direction" => "Label! Cab Is Not Moving In My Direction",
		"lbl_cancel" => "Label! Cancel",
		"lbl_cancel_booking" => "Label! Cancel Booking",
		"lbl_cancel_ride" => "Label! Cancel Ride",
		"lbl_car" => "Label! Car",
		"lbl_car_parking" => "Label! Car Parking",
		"lbl_card_number" => "Label! Card Number",
		"lbl_cash" => "Label! Cash",
		"lbl_change_password" => "Label! Change Password",
		"lbl_changed_my_mind" => "Label! Changed My Mind",
		"lbl_choose_category" => "Label! Choose Category",
		"lbl_choose_city" => "Label! Choose City",
		"lbl_comment_about_trip" => "Label! Comment About Trip",
		"lbl_complete" => "Label! Complete",
		"lbl_complete_ride" => "Label! Complete Ride",
		"lbl_confirm_booking" => "Label! Confirm Booking",
		"lbl_confirm_new_password" => "Label! Confirm New Password",
		"lbl_confirm_password" => "Label! Confirm Password",
		"lbl_confirm_payment" => "Label! Confirm Payment",
		"lbl_confirmation_pin" => "Label! Confirmation Pin",
		"lbl_copy_code" => "Label! Copy Code",
		"lbl_copy_text" => "Label! Copy Text",
		"lbl_customer_location" => "Label! Customer Location",
		"lbl_customer_number" => "Label! Customer Number",
		"lbl_customer_pickup_address" => "Label! Customer Pickup Address",
		"lbl_cvv" => "Label! Cvv",
		"lbl_dashboard" => "Label! Dashboard",
		"lbl_denied" => "Label! Denied",
		"lbl_done" => "Label! Done",
		"lbl_driver_denied_duty" => "Label! Driver Denied Duty",
		"lbl_drop_duration" => "Label! Drop Duration",
		"lbl_drop_point" => "Label! Drop Point",
		"lbl_due_amount" => "Label! Due Amount",
		"lbl_edit_profile" => "Label! Edit Profile",
		"lbl_email" => "Label! Email",
		"lbl_email_address" => "Label! Email Address",
		"lbl_end_time" => "Label! End Time",
		"lbl_enter_amount" => "Label! Enter Amount",
		"lbl_enter_code_here" => "Label! Enter Code Here",
		"lbl_enter_confirmation_pin" => "Label! Enter Confirmation Pin",
		"lbl_enter_destination_location" => "Label! Enter Destination Location",
		"lbl_enter_new_password" => "Label! Enter New Password",
		"lbl_enter_reason" => "Label! Enter Reason",
		"lbl_expiry_date" => "Label! Expiry Date",
		"lbl_facebook" => "Label! Facebook",
		"lbl_feedback" => "Label! Feedback",
		"lbl_first_name" => "Label! First Name",
		"lbl_flat_rate" => "Label! Flat Rate",
		"lbl_for_ride_with_us" => "Label! For Ride With Us",
		"lbl_forgot_password" => "Label! Forgot Password",
		"lbl_full_name" => "Label! Full Name",
		"lbl_give_feedback_about_app" => "Label! Give Feedback About App",
		"lbl_have_you_promotion_code" => "Label! Have You Promotion Code",
		"lbl_invite_friends" => "Label! Invite Friends",
		"lbl_login" => "Label! Login",
		"lbl_logout" => "Label! Logout",
		"lbl_make_payment" => "Label! Make Payment",
		"lbl_message" => "Label! Message",
		"lbl_mobile" => "Label! Mobile",
		"lbl_mobile_number" => "Label! Mobile Number",
		"lbl_mobile_number_or_email" => "Label! Mobile Number Or Email",
		"lbl_mobile_or_email" => "Label! Mobile Or Email",
		"lbl_monthly" => "Label! Monthly",
		"lbl_my_dry_run" => "Label! My Dry Run",
		"lbl_my_earning" => "Label! My Earning",
		"lbl_my_rides" => "Label! My Rides",
		"lbl_my_wallet" => "Label! My Wallet",
		"lbl_new_password" => "Label! New Password",
		"lbl_no" => "Label! No",
		"lbl_not_available" => "Label! Not Available",
		"lbl_notifications" => "Label! Notifications",
		"lbl_old_password" => "Label! Old Password",
		"lbl_or" => "Label! Or",
		"lbl_other" => "Label! Other",
		"lbl_parking_charges" => "Label! Parking Charges",
		"lbl_password" => "Label! Password",
		"lbl_password_recovery" => "Label! Password Recovery",
		"lbl_pay_any_extra_amount" => "Label! Pay Any Extra Amount",
		"lbl_payable_amount" => "Label! Payable Amount",
		"lbl_payment_complete" => "Label! Payment Complete",
		"lbl_payment_detail" => "Label! Payment Detail",
		"lbl_payment_done_by_wallet" => "Label! Payment Done By Wallet",
		"lbl_payment_received" => "Label! Payment Received",
		"lbl_pickup_arriving" => "Label! Pickup Arriving",
		"lbl_promocode" => "Label! Promocode",
		"lbl_promocode_offer" => "Label! Promocode Offer",
		"lbl_promotion_code" => "Label! Promotion Code",
		"lbl_rate_app" => "Label! Rate App",
		"lbl_rate_now" => "Label! Rate Now",
		"lbl_rate_this_ride" => "Label! Rate This Ride",
		"lbl_rated_trip" => "Label! Rated Trip",
		"lbl_reason_for_cancellation" => "Label! Reason For Cancellation",
		"lbl_reason_to_cancel" => "Label! Reason To Cancel",
		"lbl_referral_code" => "Label! Referral Code",
		"lbl_reset_password" => "Label! Reset Password",
		"lbl_retry_new_password" => "Label! Retry New Password",
		"lbl_ride" => "Label! Ride",
		"lbl_ride_cancel" => "Label! Ride Cancel",
		"lbl_ride_date" => "Label! Ride Date",
		"lbl_ride_details" => "Label! Ride Details",
		"lbl_ride_dry_run" => "Label! Ride Dry Run",
		"lbl_ride_later" => "Label! Ride Later",
		"lbl_ride_now" => "Label! Ride Now",
		"lbl_ride_request" => "Label! Ride Request",
		"lbl_ride_successful" => "Label! Ride Successful",
		"lbl_ride_time" => "Label! Ride Time",
		"lbl_save" => "Label! Save",
		"lbl_schedule_new_ride" => "Label! Schedule New Ride",
		"lbl_schedule_now" => "Label! Schedule Now",
		"lbl_schedule_ride_details" => "Label! Schedule Ride Details",
		"lbl_scheduled" => "Label! Scheduled",
		"lbl_select_method" => "Label! Select Method",
		"lbl_select_payment_mode" => "Label! Select Payment Mode",
		"lbl_select_payment_type" => "Label! Select Payment Type",
		"lbl_service_tax" => "Label! Service Tax",
		"lbl_sign_up" => "Label! Sign Up",
		"lbl_standard_fare" => "Label! Standard Fare",
		"lbl_start_point" => "Label! Start Point",
		"lbl_start_ride" => "Label! Start Ride",
		"lbl_start_riding" => "Label! Start Riding",
		"lbl_start_time" => "Label! Start Time",
		"lbl_status" => "Label! Status",
		"lbl_submit" => "Label! Submit",
		"lbl_success" => "Label! Success",
		"lbl_tariff_card" => "Label! Tariff Card",
		"lbl_teriff_card" => "Label! Teriff Card",
		"lbl_this_weeks_trip" => "Label! This Weeks Trip",
		"lbl_today" => "Label! Today",
		"lbl_toll" => "Label! Toll",
		"lbl_tolls" => "Label! Tolls",
		"lbl_total" => "Label! Total",
		"lbl_total_distance" => "Label! Total Distance",
		"lbl_total_fare" => "Label! Total Fare",
		"lbl_trip_duration" => "Label! Trip Duration",
		"lbl_trip_fare" => "Label! Trip Fare",
		"lbl_twitter" => "Label! Twitter",
		"lbl_unable_to_contact_driver" => "Label! Unable To Contact Driver",
		"lbl_upload_puc_photo" => "Label! Upload Puc Photo",
		"lbl_upload_rc_book_photo" => "Label! Upload Rc Book Photo",
		"lbl_upload_vehicle_insrance_photo" => "Label! Upload Vehicle Insrance Photo",
		"lbl_vehicle_number" => "Label! Vehicle Number",
		"lbl_vehicle_type" => "Label! Vehicle Type",
		"lbl_wallet" => "Label! Wallet",
		"lbl_weekly" => "Label! Weekly",
		"lbl_write_your_comment" => "Label! Write Your Comment",
		"lbl_yes" => "Label! Yes",
		"lbl_your_trip_confirmatin_pin" => "Label! Your Trip Confirmatin Pin",
		"msg_driver_ride_payment_str" => "Message! Driver Ride Payment Str",
		"msg_refer_code" => "Message! Refer Code",
		"msg_refer_code_string_off" => "Message! Refer Code String Off",
		"msg_refer_code_string_on" => "Message! Refer Code String On",
		"msg_wallet_credit" => "Message! Wallet Credit",
		"msg_wallet_debit" => "Message! Wallet Debit",
		"msg_wallet_payment_method" => "Message! Wallet Payment Method",
		
		"msg_wallet_driver_payu" => "Message! Wallet Driver Payu",
		"msg_wallet_driver_ride" => "Message! Wallet Driver Ride",
		"msg_wallet_driver_ride_cancel" => "Message! Wallet Driver Ride Cancel",
		"msg_wallet_driver_ride_dry_run" => "Message! Wallet Driver Ride Dry Run",
		"msg_wallet_user_payu" => "Message! Wallet User Payu",
		"msg_wallet_user_referral" => "Message! Wallet User Referral",
		"msg_wallet_user_ride" => "Message! Wallet User Ride",
		"msg_wallet_user_ride_cancel" => "Message! Wallet User Ride Cancel",
		"msg_wallet_user_ride_dry_run" => "Message! Wallet User Ride Dry Run",
		
		"msg_referral_link" => "Message! Referral Link",
		
		"succ_account_verified" => "Success Message! Account Verified",
		"succ_feedback_successfully" => "Success Message! Feedback Successfully",
		"succ_location_updated" => "Success Message! Location Updated",
		"succ_login_successfully" => "Success Message! Login Successfully",
		"succ_logout_successfully" => "Success Message! Logout Successfully",
		"succ_money_added" => "Success Message! Money Added",
		"succ_msg_ride_cancelled" => "Success Message! Msg Ride Cancelled",
		"succ_msg_ride_started" => "Success Message! Msg Ride Started",
		"succ_otp_sent" => "Success Message! Otp Sent",
		"succ_password_updated" => "Success Message! Password Updated",
		"succ_profile_updated" => "Success Message! Profile Updated",
		"succ_promotion_code_avail" => "Success Message! Promotion Code Avail",
		"succ_promotion_code_removed" => "Success Message! Promotion Code Removed",
		"succ_record_found" => "Success Message! Record Found",
		"succ_register_successfully" => "Success Message! Register Successfully",
		"succ_ride_accepted" => "Success Message! Ride Accepted",
		"succ_ride_charge_added" => "Success Message! Ride Charge Added",
		"succ_ride_completed" => "Success Message! Ride Completed",
		"succ_ride_confirmed" => "Success Message! Ride Confirmed",
		"succ_ride_payment" => "Success Message! Ride Payment",
		"succ_ride_rate_successfully" => "Success Message! Ride Rate Successfully",
		"succ_sos_send" => "Success Message! Sos Send",
		"succ_status_updated" => "Success Message! Status Updated",
		"succ_support_inquiry_submitted" => "Success Message! Support Inquiry Submitted",
		
	);
	
	ksort( $keyArr );
	
	$finalData = array();
	foreach( $keyArr as $key => $lable ){
		$finalData[$key] = array(
			'lable' => $lable,
			'data' => array(),
		);
		foreach( $globLangArr as $l_key => $l_value ){
			$finalData[$key]['data'][$l_key] = '';
		}
	}
	
	
	$translationData = array();
	$fileData = file_get_contents( $file );
	if( $fileData ){
		$fileData = json_decode( $fileData, true );
		if( is_array( $fileData ) && count( $fileData ) ){
			foreach( $fileData as $lang => $data ){
				foreach( $data as $key => $val ){
					$finalData[$key]['data'][$lang] = $val ? $val : $data[DEFAULT_LANGUAGE];
				}
			}
		}
	}
	
	/*$fileData['gu'] = $fileData['en'];
	$fileData['hi'] = $fileData['en'];
	$fp = fopen( $file, "w" );
		fwrite( $fp, json_encode( $fileData ) );
		fclose( $fp );
		exit;*/
	
	if( isset( $_POST['submit_btn'] ) && $_POST['submit_btn'] == 'Update' ){
		// _p( $_POST ); 
		foreach( $_POST['l_data'] as $key => $langData ){
			foreach( $langData as $lang => $val ){
				$fileData[$lang][$key] = $val;
			}
		}
		$fp = fopen( $file, "w" );
		fwrite( $fp, json_encode( $fileData ) );
		fclose( $fp );
		$gnrl->redirectTo( $page.'.php?succ=1&msg=edit' );
	}
	
	
	
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include('_css.php');?>
</head>
<body>

<!-- Fixed navbar -->
<?php include('inc/header.php');?>
<div id="cl-wrapper" class="fixed-menu">
	<?php include('inc/sidebar.php');?>
	<div class="container-fluid" id="pcont">
		<?php include('all_page_head.php'); ?>

            <div class="cl-mcont">
				<div class="row">
					<div class="col-sm-12 col-md-12">
					
						<?php include('all_alert_msg.php'); ?>
					
						<?php
						foreach( $finalData as $key => $data ){ ?>
							<div class="block-flat">
								<div class="header">							
									<h3><?php echo $data['lable'].' ('.$key.')';?></h3>
								</div>
								<div class="content">
									<form role="form" class="form-horizontal group-border-dashed" action="" method="post" parsley-validate novalidate enctype="multipart/form-data" >
										<?php
										foreach( $data['data'] as $lang => $val ){ 
											?>
											<div class="form-group">
												<label class="col-sm-2 control-label"><?php echo $globLangArr[$lang];?></label>
												<div class="col-sm-10">
													<textarea class="form-control" name="l_data[<?php echo $key;?>][<?php echo $lang;?>]" style="height:55px;max-width:100%;" required ><?php echo trim( $val );?></textarea>
												</div>
											</div> <?php
										} ?>
										<div class="form-group ">
											<button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
										</div>
									</form>
								</div>
							</div> <?php
						}
						?>
					</div>
				</div>
			</div>
						
					 
	</div>
</div>

<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>

</body>
</html>
