<?php 
include('includes/configuration.php');
$gnrl->check_login();
$gnrl->isPageAccess(BASE_FILE);

	$page_title = "Email Settings";
	$page = "email_settings";
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
							<div class="header"><h3>Email Settings</h3></div>
							<div class="content">
								<div class="row" >
									<div class="col-md-12" >
										<div class="form-group">
											<label>Mail Via</label>
											<?php $key = 'MAIL_VIA';?>
											<select class="select2" name="<?php echo $key;?>" id="<?php echo $key;?>" >
												<?php echo $gnrl->get_keyval_drop( array( 'smtp' => 'SMTP' ), $$key ); // 'mail' => 'Server Mail', ?>
											</select>
										</div>		
									</div>
									<div class="col-md-6" >
										<div class="form-group">
											<label>From Name</label>
											<?php $key = 'MAIL_FROM_NAME';?>
											<input type="text" class="form-control"  name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
									</div>
									<div class="col-md-6" >
										<div class="form-group">
											<label>From Email</label>
											<?php $key = 'MAIL_FROM_EMAIL';?>
											<input type="text" class="form-control"  name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
									</div>
									<div class="col-md-6" >
										<div class="form-group">
											<label>Reply To Name</label>
											<?php $key = 'MAIL_REPLY_NAME';?>
											<input type="text" class="form-control"  name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
									</div>
									<div class="col-md-6" >
										<div class="form-group">
											<label>Reply To Email</label>
											<?php $key = 'MAIL_REPLY_EMAIL';?>
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
						
						<!-- SMTP Settings -->
						<div class="block-flat">
							<div class="header"><h3>SMTP Settings</h3></div>
							<div class="content">
								<div class="row" >
									<div class="col-md-4" >
										<div class="form-group">
											<label>SMTP Host</label>
											<?php $key = 'MAIL_SMTP_HOST';?>
											<input type="text" class="form-control"  name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
									</div>
									<div class="col-md-4" >
										<div class="form-group">
											<label>SMTP Encryption</label>
											<?php $key = 'MAIL_SMTP_ENCRYPTION';?>
											<input type="text" class="form-control"  name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
									</div>
									<div class="col-md-4" >
										<div class="form-group">
											<label>SMTP Port</label>
											<?php $key = 'MAIL_SMTP_PORT';?>
											<input type="text" class="form-control"  name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
									</div>
									<div class="col-md-4" >
										<div class="form-group">
											<label>SMTP Username</label>
											<?php $key = 'MAIL_SMTP_USERNAME';?>
											<input type="text" class="form-control"  name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
									</div>
									<div class="col-md-4" >
										<div class="form-group">
											<label>SMTP Password</label>
											<?php $key = 'MAIL_SMTP_PASSWORD';?>
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
						
						
						<!-- EMAIL TEMPLATE -->
						<div class="block-flat">
							<div class="header"><h3>Manage Email Template</h3></div>
							<div class="content">
								<div class="row">
									<?php
									foreach( $globLangArr as $_langK => $_langV ){ 
										$key = 'EMAIL_TEMPLATE_'.$_langK;
										?>
										<div class="col-md-12" >
											<div class="form-group"> 
												<label>Email Template (<?php echo $_langV?>)</label>
												<a href="javascript:;" class="md-trigger fright" data-modal="form-primary">
													<span class="label label-primary">Keywords for Email Template</span>
												</a>
												<textarea class="ckeditor form-control" id="<?php echo $key;?>"  name="<?php echo $key;?>" required ><?php echo stripcslashes($$key); ?></textarea>
											</div>
										</div> <?php
									} ?>
								</div>
								
								<div class="form-group">
									<button value="Update" name="submit_btn" type="submit" class="btn btn-primary">Update</button>
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
