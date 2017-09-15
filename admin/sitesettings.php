<?php 
include('includes/configuration.php');
$gnrl->check_login();

$page_title = "General Settings";
$page = "sitesettings";
$table = 'tbl_sitesetting';
extract( $_POST );


	if( $_REQUEST['getHash'] ){
		
		function checkNull( $value ){
			if( $value == null ){
				return '';
			}
			else{
				return $value;
			}
		}
		
		function getHashes( $txnid, $amount, $productinfo, $firstname, $email, $user_credentials, $udf1, $udf2, $udf3, $udf4, $udf5, $offerKey, $cardBin ){
			// $firstname, $email can be "", i.e empty string if needed. Same should be sent to PayU server (in request params) also.
			$key = '9Dz64u';
			$salt = 'SybIl3mO';
			
			$payhash_str = $key . '|' . checkNull($txnid) . '|' .checkNull($amount)  . '|' .checkNull($productinfo)  . '|' . checkNull($firstname) . '|' . checkNull($email) . '|' . checkNull($udf1) . '|' . checkNull($udf2) . '|' . checkNull($udf3) . '|' . checkNull($udf4) . '|' . checkNull($udf5) . '||||||' . $salt;
			$paymentHash = strtolower(hash('sha512', $payhash_str));
			$arr['payment_hash'] = $paymentHash;
			
			$cmnNameMerchantCodes = 'get_merchant_ibibo_codes';
			$merchantCodesHash_str = $key . '|' . $cmnNameMerchantCodes . '|default|' . $salt ;
			$merchantCodesHash = strtolower(hash('sha512', $merchantCodesHash_str));
			$arr['get_merchant_ibibo_codes_hash'] = $merchantCodesHash;
			
			$cmnMobileSdk = 'vas_for_mobile_sdk';
			$mobileSdk_str = $key . '|' . $cmnMobileSdk . '|default|' . $salt;
			$mobileSdk = strtolower(hash('sha512', $mobileSdk_str));
			$arr['vas_for_mobile_sdk_hash'] = $mobileSdk;
			
			$cmnPaymentRelatedDetailsForMobileSdk1 = 'payment_related_details_for_mobile_sdk';
			$detailsForMobileSdk_str1 = $key  . '|' . $cmnPaymentRelatedDetailsForMobileSdk1 . '|default|' . $salt ;
			$detailsForMobileSdk1 = strtolower(hash('sha512', $detailsForMobileSdk_str1));
			$arr['payment_related_details_for_mobile_sdk_hash'] = $detailsForMobileSdk1;
			
			//used for verifying payment(optional)  
			$cmnVerifyPayment = 'verify_payment';
			$verifyPayment_str = $key . '|' . $cmnVerifyPayment . '|'.$txnid .'|' . $salt;
			$verifyPayment = strtolower(hash('sha512', $verifyPayment_str));
			$arr['verify_payment_hash'] = $verifyPayment;
			
			
			if($user_credentials != NULL && $user_credentials != ''){
				$cmnNameDeleteCard = 'delete_user_card';
				$deleteHash_str = $key  . '|' . $cmnNameDeleteCard . '|' . $user_credentials . '|' . $salt ;
				$deleteHash = strtolower(hash('sha512', $deleteHash_str));
				$arr['delete_user_card_hash'] = $deleteHash;
			
				$cmnNameGetUserCard = 'get_user_cards';
				$getUserCardHash_str = $key  . '|' . $cmnNameGetUserCard . '|' . $user_credentials . '|' . $salt ;
				$getUserCardHash = strtolower(hash('sha512', $getUserCardHash_str));
				$arr['get_user_cards_hash'] = $getUserCardHash;
			
				$cmnNameEditUserCard = 'edit_user_card';
				$editUserCardHash_str = $key  . '|' . $cmnNameEditUserCard . '|' . $user_credentials . '|' . $salt ;
				$editUserCardHash = strtolower(hash('sha512', $editUserCardHash_str));
				$arr['edit_user_card_hash'] = $editUserCardHash;
			
				$cmnNameSaveUserCard = 'save_user_card';
				$saveUserCardHash_str = $key  . '|' . $cmnNameSaveUserCard . '|' . $user_credentials . '|' . $salt ;
				$saveUserCardHash = strtolower(hash('sha512', $saveUserCardHash_str));
				$arr['save_user_card_hash'] = $saveUserCardHash;
			
				$cmnPaymentRelatedDetailsForMobileSdk = 'payment_related_details_for_mobile_sdk';
				$detailsForMobileSdk_str = $key  . '|' . $cmnPaymentRelatedDetailsForMobileSdk . '|' . $user_credentials . '|' . $salt ;
				$detailsForMobileSdk = strtolower(hash('sha512', $detailsForMobileSdk_str));
				$arr['payment_related_details_for_mobile_sdk_hash'] = $detailsForMobileSdk;
			}
			
			// if($udf3!=NULL &amp;&amp; !empty($udf3)){
				$cmnSend_Sms='send_sms';
				$sendsms_str=$key . '|' . $cmnSend_Sms . '|' . $udf3 . '|' . $salt;
				$send_sms = strtolower(hash('sha512',$sendsms_str));
				$arr['send_sms_hash']=$send_sms;
			// }
			
			if( $offerKey != NULL && !empty( $offerKey ) ){
				$cmnCheckOfferStatus = 'check_offer_status';
				$checkOfferStatus_str = $key  . '|' . $cmnCheckOfferStatus . '|' . $offerKey . '|' . $salt ;
				$checkOfferStatus = strtolower(hash('sha512', $checkOfferStatus_str));
				$arr['check_offer_status_hash']=$checkOfferStatus;
			}
			if( $cardBin!=NULL && !empty($cardBin)){
				$cmnCheckIsDomestic = 'check_isDomestic';
				$checkIsDomestic_str = $key  . '|' . $cmnCheckIsDomestic . '|' . $cardBin . '|' . $salt ;
				$checkIsDomestic = strtolower(hash('sha512', $checkIsDomestic_str));
				$arr['check_isDomestic_hash']=$checkIsDomestic;
			}
			
			return $arr;
		}
		 
		
		
		$_POST["txnid"] = 'fd3e847h2';
		$_POST["amount"] = '10.00';
		$_POST["productinfo"] = 'tshirt100';
		$_POST["firstname"] = 'Ankit';
		$_POST["email"] = 'test@gmail.com';
		$_POST["user_credentials"] = '9999999999';
		$_POST["udf1"] = '';
		$_POST["udf2"] = '';
		$_POST["udf3"] = '';
		$_POST["udf4"] = '';
		$_POST["udf5"] = '';
		$_POST["offerKey"] = '';
		$_POST["cardBin"] = '';
		 
		$output = getHashes(
			$_POST["txnid"], 
			$_POST["amount"], 
			$_POST["productinfo"], 
			$_POST["firstname"], 
			$_POST["email"], 
			$_POST["user_credentials"], 
			$_POST["udf1"], 
			$_POST["udf2"], 
			$_POST["udf3"], 
			$_POST["udf4"], 
			$_POST["udf5"],
			$_POST["offerKey"],
			$_POST["cardBin"]
		);
		_p($output);
		echo json_encode($output);
		
		_p( $_REQUEST ); exit;
	}
	
if( isset( $_REQUEST['act'] ) && $_REQUEST['act'] == 'delimg' && trim( $_REQUEST['img'] ) && trim( $_REQUEST['path'] ) ){
	@unlink( trim( $_REQUEST['path'] ).trim( $_REQUEST['img'] ) );
}

## Update Site Setting Table
if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ){
	unset($_POST['submit_btn']);
	
	foreach( $_POST as $key => $val ){
		$gnrl->save_site_setting( $key, addslashes( stripslashes( $val ) ) );
	}
	$gnrl->redirectTo( $page.'.php?succ=1&msg=edit');
	
}
else {
	$sql = "SELECT * FROM ".$table;
	$res = $dclass->query( $sql );
	$data = array();
	$row_Data = $dclass->fetchResults($res);
	foreach($row_Data as $row){
		$data[ $row['v_key'] ] = $row['l_value'];
	}
	extract( $data );
	
	
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

        <form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
		
            <div class="cl-mcont">
			
                <?php include('all_alert_msg.php'); ?>
                
                <!-- General Settings -->
                <div class="row">
                	<div class="col-sm-12 col-md-12">
                        <div class="block-flat">
                            <div class="header"><h3>General Settings</h3></div>
							<div class="row">
								<div class="col-sm-6">
									<div class="content">
										<div class="form-group">
											<?php $key = 'SITE_NAME'; ?>
											<label>Site Name</label>
											<input type="text" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
										<div class="form-group ">
											<?php $key = 'SITE_URL';?>
											<label>Site Url</label>
											<input type="text" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required parsley-type="url" >
										</div>
										<div class="form-group ">
											<?php $key = 'UPLOADS_URL';?>
											<label>Uploads Url</label>
											<input type="text" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required parsley-type="url" >
										</div>
										<div class="form-group ">
											<?php $key = 'API_URL';?>
											<label>API Url</label>
											<input type="text" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required parsley-type="url" >
										</div>
										<div class="form-group ">
											<button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
										</div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="content">
										<div class="form-group">
											<?php $key = 'RECORD_PER_PAGE'; ?>
											<label>Record Per Page</label>
											<input type="text" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
										<div class="form-group">
											<?php $key = 'DEFAULT_LANGUAGE'; ?>
											<label>Default Language</label>
											<select class="select2" name="<?php echo $key;?>" id="<?php echo $key;?>" >
												<?php $gnrl->getLanguageDropdownList($$key); ?>
											</select>
										</div>
										<div class="form-group ">
											<?php $key = 'RIDE_TRACK_URL';?>
											<label>Ride Track Url</label>
											<input type="text" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required parsley-type="url" >
										</div>
										
									</div>
								</div>
							</div>
                        </div>
						
						<div class="block-flat">
                            <div class="header"><h3>Some Configuration</h3></div>
							<div class="content">
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<?php $key = 'DRIVER_SEARCH_QUERY'; ?>
											<label>Driver Search Query</label>
											<select class="select2" name="<?php echo $key;?>" id="<?php echo $key;?>" >
												<?php echo $gnrl->get_keyval_drop($globalDriverSearchQuery,$$key); ?>
											</select>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<?php $key = 'SHOW_PICKUP_BUTTON'; ?>
											<label>Show Button For (Have you arrived at pickup location?)</label>
											<select class="select2" name="<?php echo $key;?>" id="<?php echo $key;?>" >
												<?php echo $gnrl->get_keyval_drop( array( '1' => 'Yes', '0' => 'No' ) , $$key ); ?>
											</select>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<div class="form-group">
											<button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
										</div>
									</div>
								</div>	
							</div>
                        </div>
                        
						
						<div class="block-flat">
                            <div class="header"><h3>Push Notification Settings</h3></div>
							<div class="row">
								<div class="col-sm-6">
									<div class="content">
										<div class="form-group ">
											<?php $key = 'USER_NOTIFICATION_KEY'; ?>
											<label>User Notification Key</label>
											<input type="text" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required  >
										</div>
										<div class="form-group ">
											<button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
										</div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="content">
										<div class="form-group">
											<?php $key = 'DRIVER_NOTIFICATION_KEY'; ?>
											<label>Driver Notification Key</label>
											<input type="text" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required  >
										</div>
									</div>
								</div>
							</div>
                        </div>
						
						
						<div class="block-flat">
                            <div class="header"><h3>API Keys</h3></div>
							<div class="row">
								<div class="col-sm-6">
									<div class="content">
										<div class="form-group ">
											<?php $key = 'GOOGLE_TRACK_RIDE_API_KEY'; ?>
											<label>Google Track Ride API Key</label>
											<input type="text" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required  >
										</div>
										<div class="form-group ">
											<button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
										</div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="content">
										<div class="form-group">
											<?php $key = 'GOOGLE_DISTANCE_MATRIX_API_KEY'; ?>
											<label>Google Distance Matrix API Key</label>
											<input type="text" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required  >
										</div>
									</div>
								</div>
							</div>
                        </div>
						
						
                    </div>
                    
                </div>
            </div>
            
        </form>
		
	</div>
</div>

<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>

</body>
</html>
