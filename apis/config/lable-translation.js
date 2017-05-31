var express = require('express');

var lables = {
	'en' : {
		
		// App Lables
		
		'lbl_login' 					: 'Login',
		'lbl_mobile_or_email' 			: 'Mobile Number OR Email',
		'lbl_password' 					: 'Password',
		'lbl_forgot_password' 			: 'Forgot Password?',
		'lbl_or' 						: 'OR',
		'lbl_sign_up' 					: 'Sign Up',
		
		'lbl_full_name' 				: 'Full Name',
		'lbl_email_address' 			: 'email address',
		'lbl_mobile_number' 			: 'mobile number',	
		'lbl_confirm_password' 			: 'confirm password',
		'lbl_submit' 				    : 'submit',
		'lbl_password_recovery' 		: 'password recovery',
		'lbl_mobile_number_or_email' 	: 'mobile number or email',
		'lbl_reset_password' 			: 'reser password',
		'lbl_authentication_code' 		: 'authentication code',
		'lbl_new_password' 				: 'new password',
		'lbl_retry_new_password' 		: 'retry new password',
		'lbl_edit_profile' 				: 'edit profile',
		'lbl_first_name' 				: 'fist name',
		'lbl_email' 			    	: 'email',
		'lbl_mobile' 				    : 'mobile',
		'lbl_save' 				        : 'save',
		'lbl_change_password' 			: 'change password',
		'lbl_old_password' 				: 'old password',
		'lbl_enter_new_password' 		: 'enter new password',
		'lbl_confirm_new_password' 		: 'confirm new password',

		'lbl_book_a_ride' 				: 'book a ride',
		'lbl_enter_destination_location': 'enter destination location',
		'lbl_ride_later' 				: 'ride later',
		'lbl_ride_now' 				    : 'ride now',

		'lbl_logout' 				    : 'logout',
		'lbl_book_your_ride' 			: 'book your ride',
		'lbl_my_rides' 			    	: 'my rides',
		'lbl_new_password' 				: 'new password',
		'lbl_tariff_card' 				: 'tariff card',
		'lbl_promotion_code' 			: 'promotion code',
		'lbl_my_wallet' 				: 'my wallet',
		'lbl_notifications' 			: 'notofications',
		'lbl_feedback' 	     			: 'feedback',

		'lbl_confirm_booking' 			: 'confirm booking',
		'lbl_vehicle_type' 				: 'vehicle type',
		'lbl_have_you_promotion_code' 	: 'have you promotion code?',
		'lbl_applicable_surcharge' 		: 'applicable surcharge',

		'lbl_booking_sucessful' 		: 'booking scessful',
		'lbl_cancel' 				    : 'cancel',
		'lbl_done' 				        : 'done',

		'lbl_pickup_arriving' 		    : 'pickup arriving',
		'lbl_your_trip_confirmatin_pin' : 'your trip confirmation pin',
		'lbl_cancel_booking' 		    : 'cancel booking',

		'lbl_booking_cancel' 	        : 'booking_cancel',
		'lbl_no' 				        : 'no',
		'lbl_yes' 				        : 'yes',

		'lbl_reason_for_cancellation'   : 'reason for cancellation',
		'lbl_unable_to_contact_driver'  : 'unable to contact driver',
		'lbl_driver_denied_duty' 	    : 'driver denied duty',
		'lbl_cab_is_not_moving_in_my_direction' : 'cab is not moving in my direction',
		'lbl_other' 				    : 'other',
		'lbl_enter_reason' 			    : 'enter reason',

		'lbl_promocode' 			    : 'promocode',
		'lbl_enter_code_here' 		    : 'enter code here',
		'lbl_apply_code' 			    : 'apply code',

		'lbl_select_payment_type' 	    : 'select payment type',
		'lbl_cash' 				        : 'cash',
		'lbl_wallet' 				    : 'wallet',

		'lbl_start_riding' 		        : 'start riding',
		'lbl_status' 				    : 'status',

		'lbl_message' 				    : 'message',
		'lbl_copy_text' 			    : 'copy_text',

		'lbl_complete_ride' 	        : 'complete ride',
		'lbl_for_ride_with_us' 	        : 'for ride with us',
		'lbl_start_point' 			    : 'start point',
		'lbl_drop_point' 		        : 'drop point',
		'lbl_payable_amount'            : 'payable amount',
		'lbl_make_payment' 			    : 'make payment',

		'lbl_payment_detail' 	        : 'payment detail',
		'lbl_promocode_offer' 	        : 'promocode offer',
		'lbl_payment_done_by_wallet'    : 'payment done by wallet',
		'lbl_due_amount' 		        : 'due amount',

		'lbl_ride_successful' 	        : 'ride successful',
		'lbl_rate_this_ride' 	        : 'rate this ride',

		'lbl_write_your_comment'        : 'write your comment',
		'lbl_rate_now' 				    : 'rate now',

		'lbl_schedule_new_ride'         : 'schedule new ride',
		'lbl_schedule_now' 		        : 'schedule now',

		'lbl_schedule_ride_details'     : 'schedule ride details',
		'lbl_ride_date' 		        : 'ride date',
		'lbl_ride_time' 		        : 'ride time',
		'lbl_car' 				        : 'car',

		'lbl_changed_my_mind' 		    : 'changed my mind',


		'lbl_complete' 			        : 'complete',
		'lbl_scheduled' 	            : 'scheduled',

		'lbl_total_fare' 			    : 'total fare',
		'lbl_total_distance' 	        : 'total distance',
		'lbl_start_time' 		        : 'start time',
		'lbl_end_time' 			        : 'end time',
		'lbl_trip_duration'             : 'trip duration',
		'lbl_rated_trip' 			    : 'rated trip',
		'lbl_comment_about_trip'        : 'comment about trip',

		'lbl_cancel_ride' 		        : 'cancel ride',
		'lbl_reason_to_cancel'          : 'reason to cancel',


		'lbl_teriff_card' 	            : 'tariff card',
		'lbl_choose_city' 		        : 'choose city',
		'lbl_choose_category' 	        : 'choose category',
		'lbl_flat_rate' 	            : 'flat rate',
		'lbl_standard_fare' 	        : 'standare fare',
		'lbl_base_fare' 			    : 'base fare',


		'lbl_copy_code' 	            : 'copy code',

		'lbl_referral_code' 	        : 'referral code',
		'lbl_invite_friends' 		    : 'invite friends',

		'lbl_facebook' 		            : 'facebook',
		'lbl_twitter' 				    : 'twitter',

		'lbl_rate_app' 			        : 'rate app',
		'lbl_give_feedback_about_app'   : 'give feedback about app',

		'lbl_add_money' 	            : 'add money',

		'lbl_add_money_value'           : 'add money value',

		'lbl_select_payment_mode' 		: 'select payment mode',
		'lbl_card_number' 	            : 'card number',
		'lbl_expiry_date' 		        : 'expiry date',
		'lbl_cvv' 				        : 'cvv',



		


		'lbl_vehicle_number' 	        : 'vehicle number',
		'lbl_upload_rc_book_photo' 	    : 'upload rc book photo',
		'lbl_upload_puc_photo' 		    : 'upload puc photo',
		'lbl_upload_vehicle_insrance_photo' : 'upload vehicle insurance photo',

		'lbl_ride_request' 	            : 'ride request',
		'lbl_customer_pickup_address'   : 'customer pickup address',
		'lbl_denied' 				    : 'denied',	
		'lbl_accept' 				    : 'accept',

		'lbl_my_dry_run' 		        : 'my dry run',
		'lbl_customer_location' 	    : 'customer location',
		'lbl_customer_number' 	        : 'customer number',
		'lbl_start_ride' 			    : 'start ride',		          

		'lbl_confirmation_pin' 	        : 'confirmation pin',
		'lbl_enter_confirmation_pin'    : 'enter confirmation pin',
		'lbl_apply_pin' 		        : 'apply pin',

		'lbl_pay_any_extra_amount' 	    : 'pay any extra amount?',
		'lbl_add_amount' 	            : 'add amount',

		'lbl_add_extra_amount'          : 'add extra amount',
		'lbl_select_method'             : 'select method',
		'lbl_enter_amount' 		        : 'enter amount',
		'lbl_toll' 				        : 'toll',
		'lbl_car_parking' 	            : 'car parking',
		'lbl_total' 				    : 'total',

		'lbl_trip_fare' 	            : 'trip fare',
		'lbl_tolls' 				    : 'tolls',
		'lbl_parking_charges'           : 'parking charges',
		'lbl_service_tax' 	            : 'service tax',
		'lbl_confirm_payment' 			: 'confirm payment',

		'lbl_payment_complete' 		    : 'payment complete',
		'lbl_payment_received' 		    : 'payment received',
		'lbl_by_wallet' 	            : 'by wallet',

		'lbl_dashboard' 			    : 'dashboard',
		'lbl_my_earning' 	            : 'my earning',
		'lbl_best_sarathi' 		        : 'best sarathi',

		'lbl_today' 		            : 'today',
		'lbl_weekly' 				    : 'weekly',
		'lbl_monthly' 				    : 'monthly',
		'lbl_available' 	            : 'available',

		'lbl_not_available' 			: 'not available',

		'lbl_success' 				    : 'success',


		'lbl_this_weeks_trip' 	        : 'this weeks trip',

		'lbl_ride_details' 				: 'ride details',
		'lbl_drop_duration' 	        : 'drop duration',
		
		'lbl_ride_cancel' 	        	: 'Ride Cancel',
		'lbl_ride_dry_run' 	        	: 'Ride Dry Run',
		'lbl_ride' 	        			: 'Ride',


		
		// App Messages
		'error' 						: 'Error!!! Please try again',
		'error_file_upload' 			: 'Error!!! Files not uploaded',
		
		// App Required
		'err_req_name' 					: 'Name is required.',
		'err_req_email' 				: 'Email is required.',
		'err_req_phone' 				: 'Phone is required.',
		'err_req_email_or_phone' 		: 'Email or Phone is required.',
		'err_req_password' 				: 'Password is required.',
		'err_req_otp' 					: 'OTP is required.',
		'err_req_old_password' 			: 'Old Password is required.',
		'err_req_device_token' 			: 'Device Token is required.',
		'err_req_user_id' 				: 'User ID is required.',
		'err_req_id' 					: 'ID is required.',
		'err_req_login_id' 				: 'Login ID is required.',
		'err_req_auth_token' 			: 'Auth Token is required.',
		'err_req_vehicle_type' 			: 'Vehicle type is required.',
		'err_req_vehicle_number' 		: 'Vehicle number is required.',
		'err_req_profile_image' 		: 'Profile Image is required.',
		'err_req_rc_book_image' 		: 'RC book image is required.',
		'err_req_puc_image' 			: 'PUC image is required.',
		'err_req_insurance_image' 		: 'Insurance image is required.',
		'err_req_image_license' 		: 'License copy is required.',
		'err_req_image_adhar_card' 		: 'Adhar Card copy is required.',
		'err_req_image_permit_copy' 	: 'Permit copy is required.',
		'err_req_image_police_copy' 	: 'Police copy is required.',
		'err_req_lang' 					: 'Language is required.',
		'err_req_type' 					: 'Type is required.',
		'err_req_driver_id' 			: 'Driver ID required.',
		'err_req_buzz_id' 				: 'Buzz ID required.',
		'err_req_ride_id' 				: 'Ride ID required.',
		'err_req_action' 				: 'Action is required.',
		'err_req_comment' 				: 'Comment is required.',
		'err_req_ride_cancel_reason' 	: 'Ride cancellation is required.',
		'err_req_vehicle_id' 			: 'Vehicle ID is required.',
		'err_req_pin' 					: 'PIN is required.',
		'err_req_address' 		        : 'Address is required.',
		'err_req_latitude' 				: 'Latitude is required.',
		'err_req_longitude' 			: 'Longitude is required.',
		'err_req_pickup_address' 		: 'Pickup address is required.',
		'err_req_pickup_latitude' 		: 'Pickup latitude is required.',
		'err_req_pickup_longitude' 		: 'Pickup longitude is required.',
		'err_req_destination_address' 	: 'Destination address is required.',
		'err_req_destination_latitude' 	: 'Destination latitude is required.',
		'err_req_destination_longitude' : 'Destination longitude is required.',
		'err_req_estimate_km' 		    : 'Estimate KM is required.',
		'err_req_estimate_time' 		: 'Estimate Time is required.',
		'err_req_sort_by' 		        : 'Sort by is required.',
		'err_req_status' 		        : 'Status is required.',
		'err_req_city' 		        	: 'City is required.',
		'err_req_ride_time' 		    : 'Ride time is required.',
		'err_req_payment_mode' 		    : 'Payment mode is required.',
		'err_req_round_id' 		    	: 'Round ID is required.',
		'err_req_cancel_reason' 		: 'Cancel reason is required.',
		'err_req_charge_type' 			: 'Charge Type is required.',
		'err_req_charge_info' 			: 'Charge Info is required.',
		'err_req_amount' 				: 'Amount is required.',
		'err_req_sms_body' 				: 'SMS body is required.',
		'err_req_charges' 				: 'Charges is required.',
		'err_req_notification_id' 		: 'Notification ID is required.',
		'err_req_from_date' 		    : 'From date is required.',
		'err_req_to_date'   		    : 'To date is required.',
		'err_req_promo_code'   		    : 'Promotion code is required.',
		'err_req_payment_mode'   		: 'Payment moed is required.',
		'err_req_card_no'        		: 'Card no. is required.',
		'err_req_expiry_date'      		: 'Expiry date is required.',
		'err_req_cvv'           		: 'CVV no. is required.',
		'err_req_name_on_card'          : 'Name on card is required.',
		'err_req_track_code'          	: 'Track code is required.',
		'err_req_key'          			: 'Key is required.',
		'err_req_transaction_id'        : 'Transaction ID is required.',
		'err_req_imei_number'        	: 'IMEI number is required.',
		
		
		'err_not_verified'              : 'Account is not verified.',
		
		
		// App Validations
		'err_validation_password' 		: 'Password must be 6 to 10 characters.',
		'err_validation_phone' 		    : 'Phone no. must be 10 characters.',
		
		// App Invalid
		'err_invalid_old_password' 		: 'Invalid Old password.',
		'err_invalid_email' 			: 'Invalid Email.',
		'err_invalid_phone' 			: 'Invalid Mobile number.',
		'err_invalid_email_or_phone' 	: 'Invalid Email or Phone.',
		'err_invalid_auth_token' 		: 'Invalid Auth Token.',
		'err_invalid_password' 			: 'Incorrect Password.',
		'err_invalid_otp' 				: 'Incorrect OTP.',
		'err_invalid_status' 			: 'Incorrect Status.',
		'err_invalid_pin' 				: 'Incorrect PIN.',
		'err_invalid_charge_type' 		: 'Incorrect Charge Type.',
		'err_invalid_amount' 			: 'Incorrect Amount.',
		'err_invalid_card_no' 			: 'Incorrect Card No.',
		'err_invalid_role' 				: 'Incorrect Role.',
		'err_invalid_key' 				: 'Incorrect Key.',
		'err_invalid_lang' 				: 'Incorrect Language.',
		'err_invalid_referral_code' 	: 'Incorrect Referral Code.',

		'err_invalid_promotion_code'    : 'Incorrect promotion code.',
		'err_promotion_code_redeemed' 	: 'Promotion code is already used.',
		'err_promotion_code_closed'     : 'Promotion code is closed.',
		'err_promotion_code_not_in_city': 'Promotion code is not available in your city.',
		'err_promotion_code_expired'    : 'Promotion code is expired.',
		
		
		// App Error Message
		'err_no_records' 				: 'No records found.',
		'err_no_ride' 					: 'No ride found.',
		'err_no_vehicles' 				: 'No vehicles found.',
		'err_msg_no_account' 			: 'No account found.',
		
		'err_msg_not_logged_in' 		: 'Not logged in.',
		'err_msg_exists_email' 			: 'Email already exists.',
		'err_msg_exists_phone' 			: 'Phone already exists.',
		'err_msg_ride_alreay_accepted' 	: 'Ride already accepted.',
		'err_msg_ride_alreay_completed' : 'Ride already completed.',
		'err_msg_ride_alreay_confirmed' : 'Ride already confirmed.',
		
		'err_msg_email_not_sent' 		: 'Email can not sent.',
		'err_msg_sms_not_sent' 			: 'SMS can not sent.',
		
		'err_msg_no_email_template' 	: 'No email template found.',
		'err_msg_no_sms_template' 		: 'No sms template found.',
		
		'err_msg_already_login' 		: 'User already login in another device.',
		
		
		'err_msg_no_notification_template' 	: 'No notification template found.',
		'err_msg_no_device_tokens' 			: 'No device tokens found.',
		'err_already_verified'             	: 'Account is already verified.',
		'err_acc_inactive'               	: 'Account is inactive, Please contact GOYO Help.',
		
		
		'err_msg_no_notification_template' 	: 'No notification template found.',
		
		
		
		'err_drivers_not_found_try_again' 	: 'No driver found.',
		
		// App Success Message
		'succ_record_found' 			: 'Records found.',
		'succ_login_successfully' 		: 'Login successfully.',
		'succ_logout_successfully' 		: 'Logout successfully.',
		'succ_register_successfully' 	: 'Register successfully.',
		'succ_password_updated' 		: 'Password updated successfully.',
		'succ_status_updated' 			: 'Status updated successfully.',
		'succ_location_updated' 		: 'Location updated successfully.',
		'succ_profile_updated' 			: 'Profile updated successfully.',
		'succ_otp_sent' 				: 'OTP sent successfully.',
		'succ_msg_ride_started' 		: 'Your ride is started.',
		'succ_msg_ride_cancelled' 		: 'Ride cancelled successfully.',
		'succ_ride_rate_successfully' 	: 'Ride rated successfully.',
		'succ_feedback_successfully' 	: 'Feedback added successfully.',
		'succ_ride_confirmed' 			: 'Ride confirmed successfully.',
		'succ_ride_accepted' 			: 'Ride accepted successfully.',
		'succ_ride_charge_added' 		: 'Ride charged added successfully.',
		'succ_ride_completed' 			: 'Ride completed successfully.',
		'succ_promotion_code_avail'  	: 'Promotion code is applied successfully.',
		'succ_promotion_code_removed'  	: 'Promotion code is removed successfully.',
		'succ_money_added'  	        : 'Money added successfully.',		
		'succ_ride_payment'             : 'Payment received successfully.',		
		'succ_sos_send'                 : 'SOS send successfully.',
		'succ_account_verified'         : 'Account is verified successfully.',
		
		'msg_wallet_credit'         	: '₹[amount], credited to your wallet.',
		'msg_wallet_debit'         		: '₹[amount], debited from your wallet.',
		
		'msg_wallet_user_payu'         	: '₹[amount], creadit to your wallet from PayUmoney.',
		'msg_wallet_user_ride_cancel'   : '₹[amount], debited from your wallet becaues of cancel ride ([ride_code]).',
		'msg_wallet_user_ride_dry_run'  : '',
		'msg_wallet_user_ride'         	: '₹[amount], debited from your wallet for ride ([ride_code]).',
		'msg_wallet_user_referral'      : '₹[amount], credit to your wallet for Referral.',
		
		'msg_wallet_driver_payu'        	: '',
		'msg_wallet_driver_ride_cancel'     : '₹[amount], debited from your wallet becaues of cancel ride ([ride_code]).',
		'msg_wallet_driver_ride_dry_run'    : '₹[amount] (Dry Run), credited to your wallet for ride ([ride_code]).',
		'msg_wallet_driver_ride'         	: 'For Ride ([ride_code]), \nYour ride receivable amount ₹[receivable_amount], \nYou need to pay ₹[payable_amount] to Company. \nYou received ₹[received_amount].',
		
		
		'msg_driver_ride_payment_str'   : '',
		
		'msg_refer_code' : 'No code available',
		'msg_refer_code_string_on' : 'Share your referral code and get ₹[amount]. So share your code and get money.',
		'msg_refer_code_string_off' : 'Currently no referral program available.',
		
		
	},
	
	'gu' : {
		
		// App Lables
		
		// App Messages
		'error' : 'Error!!! Please try again',
	},
	
	'hi' : {
		
		// App Lables
		
		// App Messages
		'error' : 'Error!!! Please try again',
	}
};

module.exports = lables;