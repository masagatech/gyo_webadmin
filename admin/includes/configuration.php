<?php 
session_start();
date_default_timezone_set('Asia/Kolkata');
ini_set('display_errors',1);
ob_start("ob_gzhandler");
@ob_gzhandler();
error_reporting( E_ERROR );

	## Define BASE FILE
	global $basefile;
	$basefile = basename( $_SERVER['PHP_SELF'] );
	define( "BASE_FILE", $basefile );
	define( "_LIVE", strstr( $_SERVER["HTTP_HOST"], '192.168.0.' ) ? 0 : 1 );
	define( "ADMIN_BASE", "admin" );
	define( "PATH_CLASSES", "classes/" );
	
	define( "INC","inc/" );
	
	require_once('dbconfig.php');
	require_once('message.php');

	global $config_frontend, $glob_cls_path, $uploads;
	// 
	## Admin
	if( strstr( $_SERVER['PHP_SELF'], ADMIN_BASE ) ){
		$config_frontend 	= 0;
		$glob_cls_path 		= "./".PATH_CLASSES;
		if( _LIVE ){
			$uploads 			= "../apis/public/uploads/";
		}
		else{
			$uploads 			= "../apis/public/uploads/";
		}
		require_once( $glob_cls_path.'paging.class.php' );
	}
	else{
		$config_frontend 	= 1;
		$glob_cls_path	 	= "../".ADMIN_BASE."/".PATH_CLASSES;
		$uploads 			= "uploads/";
	}
	
	## Add Classes
	require_once( $glob_cls_path."database.class.php" );
	require_once( $glob_cls_path."general.class.php" );
	
	$dclass = new database();
	$gnrl 	= new general();
	
	## Include SITE_SETTINGS VARIABLES
	global $global_site_settings;
	include("site_variables.php");
	
	## Use in ckeditor ( js/ckeditor/plugins/ckfinder/config.php in $baseUrl )
	$_SESSION['site_url'] = SITE_URL.ADMIN_BASE.'/';
	
	## Admin Paths
	define( "ADMIN_URL", SITE_URL.ADMIN_BASE."/" );
	define( "ADMIN_IMG", ADMIN_URL."images/" );
	define( "ADMIN_AJAX_URL", ADMIN_URL."ajax_operations.php" );
	
	define( "UPLOADS", $uploads );
	define( "UPLOAD_PATH", $uploads );
	
	## IMG Paths
	define("IMG_PATH", SITE_URL."uploads/" );
	
	## Some Common Functions
	function _p( $str ){
		echo '<pre>'; print_r( $str ); echo '</pre>';
	}

	function lang( $data, $lang ){
		return ( isset( $data[ $lang ] ) && $data[ $lang ] ) ? $data[ $lang ] : $data[ DEFAULT_LANGUAGE ];
	}
	
	function _is_file( $folder = '', $file = '' ){
		// _p( $folder );
		// _p( $file );
		 // _p( UPLOAD_PATH.$folder.'/'.$file );
		if( $folder && $file && file_exists( UPLOAD_PATH.$folder.'/'.$file ) ){
			// echo "string";
			// exit;
			return UPLOAD_PATH.$folder.'/'.$file;
		}
		return 0;
	}
	
	 
	function chk_all( $type = '' ){
		if( $type == 'drop' ){
			echo '<div class="row" style="margin-top:0;margin-bottom:10px;" >
					<div class="col-sm-12">
						<div class="pull-left">
							<label>Choose Action</label>
							<select id="mult_action" onChange="mult_action_fun();" >
								<option value="" >- Select -</option>
								<option value="active" >Active</option>
								<option value="inactive" >Inactive</option>
								<option value="delete" >Delete</option>
							</select>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>';
		}
		else if( is_numeric( $type ) && $type > 0 ){
			echo '<td><input type="checkbox" name="name_chk_all[]" class="cls_chk_all" value="'.$type.'" /></td>';
		}
		else{
			echo '<th width="5%" ><input id="id_chk_all" type="checkbox" value="" onClick="chk_all_fun()" /></th>';
		}
			
	}
	
	## Set Admin Session Data
	define( "AID", ( isset( $_SESSION['adminid'] ) && $_SESSION['adminid'] ) ? $_SESSION['adminid'] : 0 );
	define( "ALVL",  ( isset( $_SESSION['adminlevel'] ) && $_SESSION['adminlevel'] ) ? $_SESSION['adminlevel'] : 0 );
	define( "AUNAME", ( isset( $_SESSION['adminname'] ) && $_SESSION['adminname'] ) ? $_SESSION['adminname'] : "" );
	
	if( $_SESSION['adminid'] ){
		$get_admin_login = $dclass->select( "*", 'tbl_admin', " AND id='".$_SESSION['adminid']."' ");
		$_SESSION['page_access'] = json_decode( $get_admin_login[0]['l_data'], true );
	}
	
	## Checking for Permissions
	if( !$config_frontend ){
		$gnrl->isAccess();
	}
	
	
	global $globalCharges, $globalTrip, $globEmailTypes,$globSmsTypes,$globalRideStatus,$globalShowEstimateCharge,$globalDriverSearchQuery,$globalUserAction,$globalAdminRole,$globNotificationTypes,$globalParentChild,$globalPromotionType,$globalBankInfoArr;

	// {"day_km_end": "", "day_km_start": "", "night_km_end": "", "": "", "day_km_charges": "", "": "", "night_km_start": "", "": "", "": "", "night_km_charges": "", "": "", "": "", "day_km_after_charge": "", "night_km_after_charge": ""}
	$globalPromotionType=array(
		'ride' =>'Ride', 
		'add_wallet' =>'Add Wallet',
	);
	$globalParentChild=array(
		'0' => 'Child',
		'1' => 'Parent',
		'2' => 'Single (page)',
	);
	$globalAdminRole = array(
		'admin' => 'Admin',
		'superadmin' => 'Super Admin',
	);
	$globalUserAction = array(
		'add' => 'Add',
		'update' => 'Update',
		'delete' => 'Delete',
		'view' => 'View',
		'list' => 'List',
	);
	$globalShowEstimateCharge =array(
		'1' => 'Yes',
		'0' => 'No',
	);
	$globalDriverSearchQuery =array(
		'simple'=>'Simple',
		'complex'=>'Complex'
	);
	$globalCharges = array(
		'min_charge' => 'Minimum Charge',
		'base_fare' => 'Base Fare',
		'upto_km' => 'Upto X Km',
		'upto_km_charge' => 'Upto X Km Charge (Per Kilometer)',
		'after_km_charge' => 'After X Km Charges',
		'ride_time_charge' => 'Ride Time Charge (Per Minite)',
		'ride_time_pick_charge' => 'Ride Time Pick Charge (Per Minite)',
		'service_tax' => 'Service Tax (In %)',
		'surcharge' => 'Surcharge (In %)',
		'cancel_charge_driver' => 'Ride Cancellation Charge (Driver)',
		'cancel_charge_user' => 'Ride Cancellation Charge (Customer)',
		'max_dry_run_km' => 'Max Dry Run (In Km)',
		'max_dry_run_charge' => 'Max Dry Run Price (Per Km)',
		
		'company_commission' => 'Company Commission [ Flat (5) OR In Percentage (5%) ]',
	);
	
	$globalVehicleOtherSettings = array(
		'vehicle_list_radious' => 'Show Vehicles (Radious in Km)',
		// 'send_buzz_count' => 'Send Buzz Count',
	);
	
	$globEmailTypes = array(
		
		'driver_registration' 			=> 'Driver : Registration',
		'driver_otp_verified' 			=> 'Driver : OTP Verified',
		'driver_forgot_password'		=> 'Driver : Forgot Password',
		'driver_reset_password' 		=> 'Driver : Reset Password',
		
		'user_registration' 			=> 'User : Registration',
		'user_otp_verified' 			=> 'User : OTP Verified',
		'user_forgot_password' 			=> 'User : Forgot Password',
		'user_reset_password' 			=> 'User : Reset Password',
		'user_manual_update' 			=> 'User : Manual Update',
		'user_add_money' 				=> 'User : Add Money To Wallet',
		
		'driver_ride_complete' 	=> 'Driver : Ride Complete',
		'user_ride_complete' => 'User Complete Ride',
		'ride_alert_sos' => 'Ride Alert SOS',
		
		
		'driver_ride_cancel_charge' => 'Driver : Ride Cancellation Charge',
		
		'user_ride_cancel_charge' 	=> 'User : Ride Cancellation Charge',
		
		'user_submit_support_inquiry' 	=> 'User : Submit Support Inquiry',
		
		'ticket_resolved' 				=> 'Ticket Resolved',

	);
	$globSmsTypes = array(
		'driver_registration' 			=> 'Driver : Registration',
		'driver_otp_verified' 			=> 'Driver : OTP Verified',
		'driver_forgot_password'		=> 'Driver : Forgot Password',
		'driver_reset_password' 		=> 'Driver : Reset Password',
		
		'user_registration' 			=> 'User : Registration',
		'user_otp_verified' 			=> 'User : OTP Verified',
		'user_forgot_password' 			=> 'User : Forgot Password',
		'user_reset_password' 			=> 'User : Reset Password',
		'user_manual_update' 			=> 'User : Manual Update',
		'user_add_money' 				=> 'User : Add Money To Wallet',
		
		'user_ride_complete' => 'User Complete Ride',
		'ride_alert_sos' => 'Ride Alert SOS',
		
		'ride_track_sms' => 'Ride Track SMS',
		
		'driver_ride_cancel_charge' => 'Driver : Ride Cancellation Charge',
		'user_ride_cancel_charge' 	=> 'User : Ride Cancellation Charge',
		'user_submit_support_inquiry' 	=> 'User : Submit Support Inquiry',
		
		'resend_otp' 	=> 'Resend OTP',
		
		'ticket_resolved' 				=> 'Ticket Resolved',
		
	); 
	
	

	$globNotificationTypes = array(
		
		'user_add_money' 			=> 'User : Add Money To Wallet',
		
		
		'driver_ride_buzz' 			=> 'Driver : Ride New Buzz',
		'driver_ride_assign' 		=> 'Driver : Ride Assign',
		'driver_ride_other_assign' 	=> 'Driver : Ride Assign To Other',
		'driver_ride_cancel' 		=> 'Driver : Ride Cancel',
		'driver_ride_complete' 		=> 'Driver : Ride Complete',
		'driver_ride_get_dry_run' 	=> 'Driver : Ride Get Dry Run',
		'driver_ride_get_payment' 	=> 'Driver : Ride Get Payment',
		'driver_ride_cancel_charge' => 'Driver : Ride Cancellation Charge',
		'user_manual_update' 	    => 'User : Manual Update',
		'user_ride_start' 			=> 'User : Ride Start',
		'user_ride_cancel' 			=> 'User : Ride Cancel',
		'user_ride_complete' 		=> 'User : Ride Complete',
		'user_ride_wallet_payment' 	=> 'User : Ride Wallet Payment',
		'user_ride_cancel_charge' 	=> 'User : Ride Cancellation Charge',
		
		'user_driver_arrived' 		=> 'User : Driver Arrived at Location',
		
		
		// Pending
		
	); 
	
	$globalRideStatus = array(
		'pending' => 'Pending',
		'scheduled' => 'Scheduled',
		'confirm' => 'Confirm',
		'start' => 'Start',
		'complete' => 'Complete',
		'cancel' => 'Cancel'
	);

	
	global $globLangArr;
	$temp = $dclass->select( '*', 'tbl_language', " ORDER BY v_name" );
	foreach( $temp as $rowTemp ){
		$globLangArr[$rowTemp['v_key']] = $rowTemp['v_name'];
	}
	
	
	//$_SESSION['DETECT_IP'] = $gnrl->getRealIpAddr(); '49.213.55.201'; 
	//$_SESSION['DETECT_LOCATION'] = $gnrl->getLocationInfoByIp( DETECT_IP );
	
	
	
	
?>