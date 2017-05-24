<?php 
include('includes/configuration.php');
$gnrl->check_login();

	
$page_title = "General Settings";
$page = "sitesettings";
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
											<?php $key = 'RIDE_TRACK_URL';?>
											<label>Ride Track Url</label>
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
										<div class="form-group">
											<?php $key = 'DRIVER_SEARCH_QUERY'; ?>
											<label>Driver Search Query</label>
											<select class="select2" name="<?php echo $key;?>" id="<?php echo $key;?>" >
												<?php echo $gnrl->get_keyval_drop($globalDriverSearchQuery,$$key); ?>
											</select>
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
