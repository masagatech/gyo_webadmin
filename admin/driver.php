<?php 
include('includes/configuration.php');
$gnrl->check_login();
// $gnrl->isPageAccess(BASE_FILE);
// _P($_REQUEST);
// exit;
	extract( $_POST );
	$page_title = "Manage Driver";
	$page = "driver";
	$table = 'tbl_user';
    $table2 = 'tbl_vehicle';
	$title2 = 'Driver';
	$v_role ='driver';
    $folder = 'drivers';
    $script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";

	$filesArray = array(
        'v_image_rc_book',
        'v_image_puc',
        'v_image_insurance',
        'v_image_license',
        'v_image_adhar_card',
        'v_image_permit_copy',
        'v_image_police_copy',
		
		'v_image_rc_book_2',
		'v_image_license_2',
		'v_image_adhar_card_2',
    );
	
	
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
        
        
        $email_exit = $dclass->select('*',$table," AND v_email = '".$v_email."'");
		$phone_exit = $dclass->select('*',$table," AND v_phone = '".$v_phone."'");
		// _P($phone_exit);
		if( count( $email_exit ) && $email_exit != '' ){
			$gnrl->redirectTo($page.".php?succ=0&script=add&msg=emailexists");
		}
		else if( count( $phone_exit ) &&  $phone_exit != ''){
			$gnrl->redirectTo($page.".php?succ=0&script=add&msg=phoneexists");
		}

		else{
            $ins = array(
                'v_name'  => $v_name,
                'v_email' =>$v_email,
                'v_phone'   => $v_phone,
                'v_password'  => $v_password ? md5($v_password):'',
                'v_role'=> $v_role,
                'v_imei_number' => $v_imei_number,
                'l_latitude' => $l_latitude,
				'l_longitude' => $l_longitude,
                'e_status' => $e_status ,
				'i_city_id' => $i_city_id,
                'l_data' => json_encode($l_data) ,
                'd_added' => date('Y-m-d H:i:s'),
                'd_modified' => date('Y-m-d H:i:s')
            );
            
			if( isset( $_FILES['v_image']['name'] ) && $_FILES['v_image']['name'] != "" ) {
                $dest = UPLOAD_PATH.$folder."/";
                $file_name = $gnrl->removeChars( time().'-'.$_FILES['v_image']['name'] ); 
                if( move_uploaded_file( $_FILES['v_image']['tmp_name'], $dest.$file_name ) ){
                    $ins['v_image'] = $file_name;
                }
            }
			
			$id = $dclass->insert( $table, $ins );
			$id = $id['0'];
			
			
            ## IN VEHICLE TABLE ENTRY
            $ins2 = array(
                'i_driver_id'  		=> $id,
                'v_name' 			=> $vehicle_name,
                'v_type' 			=> $v_type,
                'v_vehicle_number'  => $v_vehicle_number,
            );
			
            ## FOR PROOF 
            foreach( $filesArray as $imgKey ){
                if( isset( $_FILES[$imgKey]['name'] ) && $_FILES[$imgKey]['name'] != "" ) {
                    $dest = UPLOAD_PATH.$folder."/";
                    $file_name = $gnrl->removeChars( time().'-'.$_FILES[$imgKey]['name'] ); 
                    if( move_uploaded_file( $_FILES[$imgKey]['tmp_name'], $dest.$file_name ) ){
                        $ins2[$imgKey] = $file_name;
                    }
                }
            }
            $id = $dclass->insert( 'tbl_vehicle', $ins2 );
            $gnrl->redirectTo($page.".php?succ=1&msg=add");
        }
	}

	## Delete Record from the database starts
	if(isset($_REQUEST['a']) && $_REQUEST['a']==3) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
			$id = $_REQUEST['id'];
			if($_REQUEST['chkaction'] == 'delete') {
				$ins = array('i_delete'=>'1');
				$dclass->update( $table, $ins, " id = '".$id."'");
				$gnrl->redirectTo($page.".php?succ=1&msg=del");
            }
            // make records restore
	        if($_REQUEST['chkaction'] == 'restore') {
	            $ins = array('i_delete'=>'0');
	            $dclass->update( $table, $ins, " id = '".$id."'");
	            $gnrl->redirectTo($page.".php?succ=1&msg=del");
	        }
            // make records active
            else if($_REQUEST['chkaction'] == 'active'){
                if(1){
                    $ins = array('e_status'=>'active');
                    $dclass->update( $table, $ins, " id = '".$id."'");
                    $gnrl->redirectTo($page.".php?succ=1&msg=multiact");
                }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                }
            }
			else if($_REQUEST['chkaction'] == 'verifynactive'){
                if(1){
					$ins = array();
					$ins[] = " v_otp = '' ";
					$ins[] = " e_status = 'active' ";
					$ins[] = " l_data = l_data || '".json_encode(array(
						'is_otp_verified' => 1,
					))."' ";
					$dclass->updateJsonb( $table, $ins, " id = '".$id."' ");	
					
                    $gnrl->redirectTo($page.".php?succ=1&msg=multiact");
                }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                }
            }
			
            // make records inactive
            else if($_REQUEST['chkaction'] == 'inactive'){
                if(1){
                    $ins = array( 'e_status' => 'inactive' );
                    $dclass->update( $table, $ins, " id = '".$id."'");
                    $gnrl->redirectTo($page.".php?succ=1&msg=multiinact");
                }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                }
            }
            // make records active
            else if($_REQUEST['chkaction'] == 'delete_image'){
                $ins = array('v_image'=>'');
                $dclass->update($table,$ins," id='$id'");
                $gnrl->redirectTo($page.".php?succ=1&msg=multiact");
            }
			
		}	
	}
	
	## Edit Process
	if(isset($_REQUEST['a']) && $_REQUEST['a']==2) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
           $id = $_REQUEST['id'];
			if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ) {
				
				
				$email_exit = $dclass->select( '*', $table, " AND id != '".$id."' AND v_email = '".$v_email."' " );
				$phone_exit = $dclass->select('*',$table," AND id != '".$id."' AND v_phone = '".$v_phone."'");
                if( count( $email_exit ) && $email_exit != '' ){
					$gnrl->redirectTo($page.'.php?succ=0&msg=emailexists&a=2&script=edit&id='.$_REQUEST['id']);
                }
				else if( count( $phone_exit ) && $phone_exit != '' ){
					$gnrl->redirectTo($page.'.php?succ=0&msg=phoneexists&a=2&script=edit&id='.$_REQUEST['id']);
                }
				else{
					
					######### Update Driver
                    $ins = array(
						'v_name' => $v_name,
						'v_email' => $v_email,
						'v_phone' => $v_phone,
						'e_status' => $e_status,
						'l_latitude' => $l_latitude,
						'v_imei_number' => $v_imei_number,
						'l_longitude' => $l_longitude,
						'is_premium' => $is_premium,
						'v_token' => $v_token,
						'i_city_id' => $i_city_id,
						'd_modified' => date('Y-m-d H:i:s'),
					);
					if( $v_password ){
						$ins['v_password']= md5( $v_password );
					}
                   
                    ## Profile Image
                    if( isset( $_FILES['v_image']['name'] ) && $_FILES['v_image']['name'] != "" ){
                        $dest = UPLOAD_PATH.$folder."/";
                        $file_name = $gnrl->removeChars( time().'-'.$_FILES['v_image']['name'] ); 
                        if( move_uploaded_file( $_FILES['v_image']['tmp_name'], $dest.$file_name ) ){
                            $ins['v_image'] = $file_name;
							@unlink( $dest.$old_files['v_image'] );
                        }
                    }
                    $dclass->update( $table, $ins, " id = '".$id."' ");
					
					## Update l_data

					$ins = array(
						" l_data = COALESCE( NULLIF( l_data::text, null )::jsonb, ('{\"a\":1}'::jsonb) ) || '".json_encode(array(
							'bank_info' => $l_data['bank_info']
						))."'"
					);
					$dclass->updateJsonb( $table, $ins, " id = '".$id."' ");
					
					
					
					######### Update Vehicle
                    $ins = array(
                        'v_type' 			=>	$v_type,
                        'v_name' 			=> $vehicle_name,
						'v_vehicle_number' 	=> $v_vehicle_number,
                    );
                    
                    foreach( $filesArray as $imgKey ){
                        if( isset( $_FILES[$imgKey]['name'] ) && $_FILES[$imgKey]['name'] != "" ) {
                            $dest = UPLOAD_PATH.$folder."/";
                            $file_name = $gnrl->removeChars( time().'-'.$_FILES[$imgKey]['name'] ); 
                            if( move_uploaded_file( $_FILES[$imgKey]['tmp_name'], $dest.$file_name ) ){
                                $ins[$imgKey] = $file_name;
                                @unlink( $dest.$old_files[$imgKey] );
                            }
                        }
                    }
                    $dclass->update( $table2, $ins, " i_driver_id = '".$id."' ");
					
					
                    $gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
                }
			}
			else {
				
				$row = $dclass->select( '*', $table , " AND id = '".$id."' ");
				$row = $row[0];
				extract( $row );
				$l_data = json_decode( $l_data, true );
				
				$row2 = $dclass->select( '*', $table2, " AND i_driver_id = '".$id."' ");
				$row2 = $row2[0];
				$row2['vehicle_name'] = $row2['v_name'];
				unset( $row2['id'] );
				unset( $row2['i_driver_id'] );
				unset( $row2['d_added'] );
				unset( $row2['d_modified'] );
				unset( $row2['e_status'] );
				unset( $row2['v_name'] );
				unset( $row2['l_data'] );
				extract( $row2 );
				
			}
		}
	}
	$bank_row = $dclass->select('*','tbl_bank', " AND i_delete=0 ORDER BY v_name ");
	$bank_arr=array();
	foreach($bank_row as $key => $val){
		$bank_arr[$val['v_name']] =$val['v_name'];
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
	<?php include('inc/sidebar.php'); ?>
	<div class="container-fluid" id="pcont">
		<?php include('all_page_head.php'); ?>

        <div class="cl-mcont">
        	<?php include('all_alert_msg.php'); ?>
            <div class="row">
                <div class="col-md-12">
                    
                        
					<?php 
					if( ($script == 'add' || $script == 'edit') && 1 ){?>
						<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
						
							<div class="block-flat">
								<div class="header">
									<h3>
										<?php echo $script ? ucfirst( $script ).' '.ucfirst( $title2 ) : 'List Of '.' '.ucfirst( $title2 ); ?> 
										<?php if( !$script ){?>
											<?php if( !$script && 1){?>
												<a href="<?php echo $page?>.php?script=add" class="fright">
													<button class="btn btn-primary" type="button">Add</button>
												</a>
											<?php } ?>
										<?php } ?>
									</h3>
								</div>
								<div class="content">
									<div class="row" >
										<div class="col-md-12">
											<div class="form-group">
												<label>Profile Image</label>
												<input class="form-control" type="file" name="v_image" style="height:auto;"  >
												<?php 
													if( $putFile = _is_file( $folder, $v_image ) ){ //echo $putFile; ?>
													<a href="javascript:;"><img class="edit_img gallery-items" src="<?php echo $putFile;?>" style="max-width: 100px;max-height: 100px" ></a>
													<input type="hidden" name="old_files[v_image]" value="<?php echo $v_image; ?>">
												<?php } ?>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label>Name <?php echo $gnrl->getAstric(); ?></label>
												<input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $v_name;?>" required />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Email <?php echo $gnrl->getAstric(); ?></label>
												<input type="email" class="form-control" id="v_email" name="v_email" value="<?php echo $v_email; ?>" required />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Phone <?php echo $gnrl->getAstric(); ?></label>
												<input type="text" class="form-control" id="v_phone" name="v_phone" value="<?php echo $v_phone; ?>" required />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>IMEI Number</label>
												<input type="text" class="form-control" id="v_imei_number" name="v_imei_number" value="<?php echo $v_imei_number; ?>" />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<?php 
												$required="";
												if($script=='add'){ ?>
													
													<label>Password <?php echo $gnrl->getAstric(); ?></label>
													<input type="password" class="form-control" id="v_password" name="v_password" value="" required="" />
												<?php }else{ ?>
													<label>Password </label>
													<input type="password" class="form-control" id="v_password" name="v_password" value="" />
												<?php } ?>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Premium</label>
												<select class="select2" name="is_premium" id="is_premium">
													<?php echo $gnrl->get_keyval_drop( array(
														'0' => 'No',
														'1' => 'Yes',
													), $is_premium ); ?>
												</select> 
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
											   <label>Select City <?php echo $gnrl->getAstric(); ?></label>
											   <select class="select2" name="i_city_id" id="i_city_id" required="">
													<option value="" >- Select -</option>
													<?php $gnrl->getCityDropdownList($i_city_id); ?>
												</select> 
											</div>
										</div>
										
										
										
										
									</div>
								</div>
							</div>
							
							
							<div class="block-flat">
								<div class="header"><h3>Vehicle Info</h3></div>
								<div class="content">
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label>Vehicle Type </label>
												<select class="select2" name="v_type" id="v_type">
												<option value=""> -- Select --</option>
												 <?php $gnrl->getVehicleTypeDropdownList( $v_type, 'v_type' ); ?>
												</select> 
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Vehicle Number <?php echo $gnrl->getAstric(); ?></label>
												<input type="text" class="form-control" id="v_vehicle_number" name="v_vehicle_number" value="<?php echo $v_vehicle_number; ?>" required />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Vehicle Name <?php echo $gnrl->getAstric(); ?></label>
												<input type="text" class="form-control" id="vehicle_name" name="vehicle_name" value="<?php echo $vehicle_name; ?>" required />
											</div>
										</div>
									</div>
									<div class="row">
										<?php
										$vehicleImages = array(
											'v_image_license' => 'Driving license',
											'v_image_license_2' => 'Driving license Back Side',
											
											'v_image_adhar_card' => 'Adhar Card',
											'v_image_adhar_card_2' => 'Adhar Card Back Side',
											
											'v_image_permit_copy' => 'Permit Copy',
											
											'v_image_rc_book' => 'RC Book',
											'v_image_rc_book_2' => 'RC Book Back Side',
											
											'v_image_puc' => 'PUC Image',
											'v_image_insurance' => 'Insurance Image',
											'v_image_police_copy' => 'Police Verification',
										);
										$i = 0;
										foreach( $vehicleImages as $kk => $vv ){
											?>
											<div class="col-sm-3 col-md-3">
												<div class="form-group"> 
													<label><?php echo $vv;?></label>
													<input class="form-control" type="file" id="<?php echo $kk;?>" name="<?php echo $kk;?>" style="height:auto;"  >
													<?php 
													if( $putFile = _is_file( $folder, $$kk ) ){ //echo $putFile; ?>
														 <a href="javascript:;" ><img class="edit_img gallery-items" src="<?php echo $putFile;?>"  style="max-width: 125px;max-height: 125px"></a>
														<input type="hidden" name="old_files[<?php echo $kk;?>]" value="<?php echo $$kk; ?>">
													<?php } ?>
												</div>
											</div>
											<?php
											$i++;
											if( $i == '4' ){
												$i = 0;
												echo '</div><div class="row">';
											}
										}
										?>
									</div>
								</div>
							</div>
							<?php 

							// Bank Info Array
							// $globalBankInfoArr=array(
							// 	'bank_info_account_name' => 'Account Name',
							// 	'bank_info_bank_name' => 'Bank Name',
							// 	'bank_info_current_ac_no' => 'Current A/c No.',
							// 	'bank_info_branch_address' => 'Branch Address',
							// 	'bank_info_ifsc_code' => 'IFSC Code',
							// 	'bank_info_pan_card' => 'PAN Card ',
							// );
								
							?>
							<div class="block-flat">
								<div class="header"><h3>Bank Account Information</h3></div>
								<div class="content">
									<div class="row" style="margin-top:0" >
										<div class="col-md-12">
											
											<div class="form-group">
												<label>Account Name <span class="text-danger">*</span></label>
												<input class="form-control" type="text" id="<?php ?>" name="l_data[bank_info][account_name]" value="<?php echo $l_data['bank_info']['account_name'] ?>"  required="">
											</div>
											<div class="form-group">
												<label>Bank Name <span class="text-danger">*</span></label>
												<select class="select2 required" name="l_data[bank_info][bank_name]" id="l_data[bank_info][bank_name]">
													<option value="">-- select --</option>
													<?php echo $gnrl->get_keyval_drop( $bank_arr , $l_data['bank_info']['bank_name'] ); ?>
												</select> 
											</div>
											<div class="form-group">
												<label>Current A/c No. <span class="text-danger">*</span></label>
												<input class="form-control" type="text" parsley-maxlength="16" id="<?php ?>" name="l_data[bank_info][current_ac_no]" value="<?php echo $l_data['bank_info']['current_ac_no'] ?>"  required="">
											</div>
											<div class="form-group">
												<label>Branch Address <span class="text-danger">*</span></label>
												<input class="form-control" type="text" id="<?php ?>" name="l_data[bank_info][branch_address]" value="<?php echo $l_data['bank_info']['branch_address'] ?>"  required="">
											</div>
											<div class="form-group">
												<label>IFSC Code <span class="text-danger">*</span></label>
												<input class="form-control" type="text" parsley-minlength="11" parsley-maxlength="11" id="<?php ?>" name="l_data[bank_info][ifsc_code]" value="<?php echo $l_data['bank_info']['ifsc_code'] ?>"  required="">
											</div>
											<div class="form-group">
												<label>PAN Card <span class="text-danger">*</span></label>
												<input class="form-control" type="text" id="<?php ?>" name="l_data[bank_info][pan_card]" value="<?php echo $l_data['bank_info']['pan_card'] ?>"  required="">
											</div>

											
										</div>
									</div>
								</div>
							</div>
							
							<div class="block-flat">
								<div class="header"><h3>Other Info</h3></div>
								<div class="content">
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label>Login Token</label>
												<input type="text" class="form-control" id="v_token" name="v_token" value="<?php echo $v_token;?>" />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Latitude <span class="text-danger">*</span></label>
												<input type="text" class="form-control" id="l_latitude" name="l_latitude" value="<?php echo $l_latitude;?>" required />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Longitude <span class="text-danger">*</span></label>
												<input type="text" class="form-control" id="l_longitude" name="l_longitude" value="<?php echo $l_longitude;?>" required />
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<label>Status</label>
										<select class="select2" name="e_status" id="e_status">
											<?php $gnrl->getDropdownList(array('active','inactive'),$e_status); ?>
										</select>
									</div>
									<div class="form-group">
										<button class="btn btn-primary" type="submit" name="submit_btn" value="<?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?>"><?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?></button>
										<a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
									</div>
									
								</div>
							</div>
							
						</form> <?php 
					}
					else{
						   
						if ( isset( $_REQUEST['pageno'] ) && $_REQUEST['pageno'] != '' ){
							$limit = $_REQUEST['pageno'];
						}
						else{
							$limit = $gnrl->getSettings('RECORD_PER_PAGE');
						}
				
						$form = 'frm';
						
						if ( isset($_REQUEST['limitstart']) && $_REQUEST['limitstart'] != '' ){
							$limitstart = $_REQUEST['limitstart'];
						}
						else{
							$limitstart = 0;
						}
						
						$wh = '';
						if( isset( $_REQUEST['srch_filter'] ) && $_REQUEST['srch_filter'] != '' ){
							$keyword =  trim( $_REQUEST['srch_filter'] );
							$wh .= " AND ( 
							   LOWER(u.e_status) like LOWER('".$keyword."') 
								 
							)";
						}
						if( isset( $_REQUEST['srch_filter_city'] ) && $_REQUEST['srch_filter_city'] != '' ){
							
							$keyword =  trim( $_REQUEST['srch_filter_city'] );
							$wh .= " AND u.i_city_id = '".$keyword."' ";
						   
								 
						}
						if( isset( $_REQUEST['srch_filter_type'] ) && $_REQUEST['srch_filter_type'] != ''){
							$keyword =  trim( $_REQUEST['srch_filter_type'] );
							$wh .= " AND ( 
							   LOWER(v.v_type) like LOWER('".$keyword."') 
							)";
						}
						if( isset( $_REQUEST['srch_otp_verified'] ) && $_REQUEST['srch_otp_verified'] != '' ){
							$keyword =  trim( $_REQUEST['srch_otp_verified'] );
							$wh .= " AND ( 
							  u.l_data->>'is_otp_verified' = '".$keyword."' 
							)";
						}
						if( isset( $_REQUEST['keyword'] ) && $_REQUEST['keyword'] != '' ){
						 
							$keyword =  trim( $_REQUEST['keyword'] );
							$wh .= " AND ( 
								LOWER(u.v_name) like LOWER('%".$keyword."%')
								OR LOWER(u.v_email) like LOWER('%".$keyword."%')
								OR LOWER(u.v_phone) like LOWER('%".$keyword."%')
								OR LOWER(v.v_type) like LOWER('%".$keyword."%')
								OR LOWER(v.v_vehicle_number) like LOWER('%".$keyword."%')
								OR LOWER(u.e_status) like LOWER('%".$keyword."%')
							)";
						}
						if( isset( $_REQUEST['deleted'] ) ){
							$keyword =  trim( $_REQUEST['keyword'] );
							$wh .= " AND u.i_delete='1'";
							$checked="checked";
						}else{
							$wh .= " AND u.i_delete='0'";
						}
						$ssql = 
						"SELECT u.*,
							v.v_name AS vehicle_name,
							v.v_type AS vehicle_type,
							v.v_vehicle_number AS vehicle_number,
							u.l_latitude AS lat,
							u.l_longitude AS long
							FROM ".$table." as u
								LEFT JOIN tbl_vehicle 
								as v ON u.id = v.i_driver_id
							WHERE true AND u.v_role='".$v_role."' ".$wh;

						$sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'u.v_name' );
						$sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'ASC' );
						
						$nototal = $dclass->numRows($ssql);
						$pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
					    $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
						$restepm = $dclass->query($sqltepm);
						$row_Data = $dclass->fetchResults($restepm);
						
						$otp_arr = array(
							'0' => 'OTP Verified',
							'1' =>'OTP Not Verified'
						);

						$vehicle_row = $dclass->select('*','tbl_vehicle_type', " ORDER BY v_name ");
						$vehicle_arr=array();
						foreach($vehicle_row as $key => $val){
							$vehicle_arr[$val['v_name']] =$val['v_name'];
						}
						?>
						<div class="block-flat">
							<div class="header">
								<h3>
									<?php echo $script ? ucfirst( $script ).' '.ucfirst( $title2 ) : 'List Of '.' '.ucfirst( $title2 ); ?> 
									
									
									
									
									
									<?php if( !$script ){?>
										<?php if( !$script && 1){?>
											<a href="<?php echo $page?>.php?script=add" class="fright">
												<button class="btn btn-primary" type="button">Add</button>
											</a>
										<?php } ?>
									<?php } ?>
								</h3>
							</div>
							<div class="content">
								<form name="frm" action="" method="get" >
									<div class="table-responsive">
										<div class="row">
											<div class="col-sm-12">
												<div class="pull-right" style="margin-top: 20px;" >
													<div class="dataTables_filter" id="datatable_filter">
														<label>
															<input type="text" aria-controls="datatable" class="form-control fleft" placeholder="Search" name="keyword" value="<?php echo isset( $_REQUEST['keyword'] ) ? $_REQUEST['keyword'] : ""?>" style="width:auto;"/>
															<button type="submit" class="btn btn-primary fleft" style="margin-left:0px;"><span class="fa fa-search"></span></button>
															<div class="clearfix"></div> 
															<div class="pull-right" style="">
																<input class="all_access" name="deleted" value=""  type="checkbox"  onclick="document.frm.submit();" <?php echo $checked; ?>>
																Show Deleted Data
																<div class="clearfix"></div>
	                                                            <div style="margin: 10px 10px 10px 65px;">
	                                                            	<a href="top_drivers.php"> Top Drivers </a>
	                                                            </div>
															</div>
														</label>
													</div>
													<?php if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '' || isset($_REQUEST['srch_filter']) && $_REQUEST['srch_filter'] != '' || isset($_REQUEST['srch_otp_verified']) && $_REQUEST['srch_otp_verified'] != ''
													   || isset($_REQUEST['srch_filter_city']) && $_REQUEST['srch_filter_city'] != '' || isset($_REQUEST['srch_filter_type']) && $_REQUEST['srch_filter_type'] != ''   ){ ?>
																<a href="<?php echo $page ?>.php" class="fright" style="margin: -10px 0px 20px 0px ;" >
																<h4> Clear Search </h4></a>
														<?php } ?>
												</div>
												<div class="pull-left">
													<div id="datatable_length" class="dataTables_length">
														<label><?php $pagen->writeLimitBox(); ?></label>
													</div>
												</div>

												<label style="margin-left:5px">Status 
													 <div class="clearfix"></div>
														<div class="pull-left" style="">
														<div>
														<select class="select2" name="srch_filter" id="srch_filter" onChange="document.frm.submit();">
																<option value="" >--Select--</option>
																 <?php $gnrl->getDropdownList(array('active','inactive'),$_GET['srch_filter']); ?>
														</select>
														</div>
													</div>
												</label>
												<label style="margin-left:5px">Verified 
													 <div class="clearfix"></div>
														<div class="pull-left" style="">
														<div>
														 <select class="select2" name="srch_otp_verified" id="srch_otp_verified" onChange="document.frm.submit();">
																<option value="" >--Select--</option>
																 <?php echo $gnrl->get_keyval_drop($otp_arr,$_GET['srch_otp_verified']); ?>
																</select>
														</div>
													</div>
												</label>
												<label style="margin-left:5px">City 
													 <div class="clearfix"></div>
														<div class="pull-left" style="">
														<div>
														 <select class="select2" name="srch_filter_city" id="srch_filter_city" onChange="document.frm.submit();">
																<option value="">--Select--</option>
																 <?php echo $gnrl->getCityDropdownList($_GET['srch_filter_city']); ?>
																</select>
														</div>
													</div>
												</label>
												<label style="margin-left:5px"> Vehicle Type 
													 <div class="clearfix"></div>
														<div class="pull-left" style="">
														<div>
														 <select class="select2" name="srch_filter_type" id="srch_filter_type" onChange="document.frm.submit();">
															<option value="">--Select--</option>
															 <?php echo $gnrl->get_keyval_drop($vehicle_arr,$_GET['srch_filter_type']); ?>
														</select>
														</div>
													</div>
												</label>
												<div class="clearfix"></div>
											</div>
										</div>
										
										<!-- <?php chk_all('drop');?> -->
										<table class="table table-bordered" id="datatable" style="width:100%;" >
											<?php
											echo $gnrl->renderTableHeader(array(
												'v_image' => array( 'order' => 0, 'title' => 'Profile Image' ),
												'v_name' => array( 'order' => 1, 'title' => 'Name' ),
												'vehicle_type' => array( 'order' => 1, 'title' => 'Vehicle Type' ),
												'vehicle_number' => array( 'order' => 1, 'title' => 'Vehicle No.' ),
												'e_status' => array( 'order' => 1, 'title' => 'Status', 'title2' => ' / Online / Available / On Ride' ),
												'action' => array( 'order' => 0, 'title' => 'Action' ),
											));
											?>
											<tbody>
												<?php 
												if( $nototal > 0 ){
													foreach( $row_Data as $row ){
														$l_data = json_decode( $row['l_data'], true ); ?>
														<tr>
															<td>
																<?php 
																if( $putFile = _is_file( $folder, $row['v_image'] ) ){ //echo $putFile; ?>
																	<!-- <a href="javascript:;" onclick="open_Image('<?php echo $putFile;?>');" ><img class="edit_img" name="" id="v_image<?php echo $row['id']; ?>" src="<?php echo $putFile;?>" ></a> -->
																	<a href="javascript:;" >
																		<img class="edit_img  gallery-items" name="" id="v_image<?php echo $row['id']; ?>" src="<?php echo $putFile;?>" style="max-width: 100px;max-height: 100px" >
																	</a>
																<?php }else{ ?>
																	<span class="text-danger">No Image.</span>
																<?php } ?>
															</td>
															<td><?php echo $row['v_name']; ?></td>
															<td><?php echo $row['vehicle_type'];?></td>
															<td><?php echo $row['vehicle_number'];?></td>
															<td>
																<?php echo $row['e_status'];?>
																<br>Online : <?php echo $row['v_token'] ? 'Yes' : 'No';?>
																<br>Available : <?php echo $row['is_onduty'] ? 'Yes' : 'No';?>
																<br>On Ride : <?php echo $row['is_onride'] ? 'Yes' : 'No';?>
															</td>
															<td>
																<div class="btn-group">
																	<button class="btn btn-default btn-xs" type="button">Actions</button>
																	<button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
																		<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
																	</button>
																	<ul role="menu" class="dropdown-menu pull-right">

																		 <?php
                                                                           if(isset($_REQUEST['deleted'])){ ?>
                                                                                <li><a href="javascript:;" onclick="confirm_restore('<?php echo $page;?>','<?php echo $row['id'];?>');">Restore</a></li>
                                                                            <?php  
                                                                            }else{ ?>
                                                                                <li><a href="log.php?v_type=login&id=<?php echo $row['id'];?>"> Login Log</a></li>
																				<li><a href="log.php?v_type=duty&id=<?php echo $row['id'];?>"> Available Log</a></li>
																				<li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>"> Edit</a></li>
																				<?php 
																				if( !$l_data['is_otp_verified'] ){ ?>
																				<li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=verifynactive&amp;id=<?php echo $row['id'];?>">Verify & Active</a></li>
																				<?php } ?>
																				<li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=active&amp;id=<?php echo $row['id'];?>">Active</a></li>
																				<li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=inactive&amp;id=<?php echo $row['id'];?>">Inactive</a></li>
																				<li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
                                                                            <?php }
                                                                        ?>
																	</ul>
																</div> 
																<br>
																<a href="warroom.php?id=<?php echo $row['id'];?>" target="_blank" >View Location</a>
															</td>
														</tr><?php 
													}
												}
												else{ ?>
													<tr><td colspan="8">No Record found.</td></tr><?php 
												}?>
											</tbody>
										</table>
										<div class="row">
											<div class="col-sm-12">
												<div class="pull-left"> <?php echo $pagen->getPagesCounter();?> </div>
												<div class="pull-right">
													<div class="dataTables_paginate paging_bs_normal">
														<ul class="pagination">
															<?php $pagen->writePagesLinks(); ?>
														</ul>
													</div>
												</div>
												<div class="clearfix"></div>
											</div>
										</div>
										<input type="hidden" name="a" value="<?php echo @$_REQUEST['a'];?>" />
										<input type="hidden" name="st" value="<?php echo @$_REQUEST['st'];?>" />
										<input type="hidden" name="sb" value="<?php echo @$_REQUEST['sb'];?>" />
										<input type="hidden" name="np" value="<?php //echo @$_SERVER['HTTP_REFERER'];?>" />
									</div>
								</form>
							</div>
						</div> <?php
				   
					}?>
				
                    
                </div>
            </div>
        </div>
	</div>
</div>

<?php include('_scripts.php');?>
<script type="text/javascript">
	$(function () {
    var viewer = ImageViewer();
    $('.gallery-items').click(function () {
        var imgSrc = this.src,
            highResolutionImage = $(this).data('high-res-img');
 
        viewer.show(imgSrc, highResolutionImage);
    });
});
</script>

<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
