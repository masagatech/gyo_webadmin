<?php 
include('includes/configuration.php');
$gnrl->check_login();
$gnrl->isPageAccess(BASE_FILE);

	$page_title = "SMS Settings";
	$page = "sms_settings";
	$table = 'tbl_sitesetting';
	
	extract( $_POST );
	
	if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Check' ){
		$email_subject = 'Testing Email';
		$email_message = 'Hi <br/><br/>This is just testing email.!!!';
		$email_status = $gnrl->custom_email( $_POST['v_test_email'], $email_from = "", $reply_to = "", $email_cc = "", $email_bcc = "", $email_subject, $email_message, $email_format = "" );
		//echo $email_status; exit;
		if( $email_status == 1 ){
			$gnrl->redirectTo( $page.'.php?succ=1&msg=test_email_sent');
		} 
		else {
			$gnrl->redirectTo( $page.'.php?succ=0&msg=test_email_not_sent');
		}
	}
	
	if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ){
		unset($_POST['submit_btn']);
		if( $gnrl->checkAction('edit') == '1' ){
			foreach( $_POST as $key => $val ) {
				$gnrl->save_site_setting( $key, addslashes( stripslashes( $val ) ) );
			}
			$gnrl->redirectTo( $page.'.php?succ=1&msg=edit');
		}else{
			$gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
		}
		
	}
	else {
		$sql = "SELECT * FROM ".$table;
		$res = $dclass->query( $sql );
		$data = array();
		$row_Data = $dclass->fetchResults($res);
		foreach( $row_Data as $row){
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
        
        <div class="cl-mcont">
            <?php include('all_alert_msg.php'); ?>
            
            <div class="row">
                <div class="col-sm-12 col-md-12">
				
					<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
					
						<!-- Email Settings -->
						<div class="block-flat">
							<div class="header"><h3>SMS Settings</h3></div>
							<div class="content">
								<div class="row" >
									<div class="col-md-4" >
										<div class="form-group">
											<label>SMS Gateway Username</label>
											<?php $key = 'SMS_USERNAME';?>
											<input type="text" class="form-control"  name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
									</div>
									<div class="col-md-4" >
										<div class="form-group">
											<label>SMS Gateway Password</label>
											<?php $key = 'SMS_PASSWORD';?>
											<input type="text" class="form-control"  name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
									</div>
									<div class="col-md-4" >
										<div class="form-group">
											<label>SMS Sender Name</label>
											<?php $key = 'SMS_SENDERNAME';?>
											<input type="text" class="form-control"  name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
									</div>
									
								</div>
								<div class="row" >
									<div class="col-md-12" >
										<div class="form-group">
											<button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
										</div>
									</div>
								</div>
							</div>                            
						</div>
						
						
					</form>
					
					
					
					
					
                </div>
            </div>
        </div>
	</div>
</div>

	<?php include('_email_keywords.php');?>

	

<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>

</body>
</html>
