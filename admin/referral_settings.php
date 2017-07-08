<?php 
include('includes/configuration.php');
$gnrl->check_login();

$page_title = "Referral Settings";
$page = "referral_settings";
$table = 'tbl_sitesetting';
extract( $_POST );

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
                            <div class="header"><h3>Referral Program</h3></div>
							<div class="row">
								<div class="col-sm-6">
									<div class="content">
										<div class="form-group ">
											<?php $key = 'REFER_AMOUNT'; ?>
											<label>Refer Amount</label>
											<input type="number" min="0" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required >
										</div>
										<div class="form-group ">
											<button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
										</div>
									</div>
								</div>
							</div>
                        </div>
                        <div class="block-flat">
                            <div class="header"><h3>Driver Referral Program</h3></div>
							<div class="content">
								<div class="row">
									<div class="col-sm-4">
										<div class="form-group "> 
											<?php $key = 'REFERRAL_DRIVER_MONEY'; ?>
											<label>Money</label>
											<input type="number" min="0" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required  >
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group "> 
											<?php $key = 'REFERRAL_DRIVER_COUPON'; ?>
											<label>Coupon</label>
											<input type="number" min="0" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required  >
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group "> 
											<?php $key = 'REFERRAL_DRIVER_APPLY'; ?>
											<label>When</label>
											<select class="select2 required" name="<?php echo $key;?>" id="<?php echo $key;?>" >
												<?php echo $gnrl->get_keyval_drop( array(
													'signup' => 'Sign Up',
													'first_ride' => '1st Ride',
												), $$key ); ?>
											</select>
										</div>
									</div>
									<div class="col-sm-12">
										<div class="form-group">
											<button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
										</div>
									</div>
								</div>
							</div>
							
                        </div>
                        
						
						<div class="block-flat">
                            <div class="header"><h3>User Referral Program</h3></div>
							<div class="content">
								<div class="row">
									<div class="col-sm-4">
										<div class="form-group "> 
											<?php $key = 'REFERRAL_USER_MONEY'; ?>
											<label>Money</label>
											<input type="number" min="0" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required  >
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group "> 
											<?php $key = 'REFERRAL_USER_COUPON'; ?>
											<label>Coupon</label>
											<input type="number" min="0" class="form-control" id="<?php echo $key;?>" name="<?php echo $key;?>" value="<?php echo $$key?>" required  >
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group "> 
											<?php $key = 'REFERRAL_USER_APPLY'; ?>
											<label>When</label>
											<select class="select2 required" name="<?php echo $key;?>" id="<?php echo $key;?>" >
												<?php echo $gnrl->get_keyval_drop( array(
													'signup' => 'Sign Up',
													'first_ride' => '1st Ride',
												), $$key ); ?>
											</select>
										</div>
									</div>
									<div class="col-sm-12">
										<div class="form-group">
											<button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
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
