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
    );
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
        $email_exit = $dclass->select('*',$table," AND v_email = '".$v_email."'");
        
        if(count($email_exit) && !empty($email_exit)){
             $gnrl->redirectTo($page.".php?script=add&succ=0&msg=email_exit");

        }else{
            $ins = array(
                'v_name'  => $v_name,
                'v_email' =>$v_email,
                'v_phone'   => $v_phone,
                'v_password'  => $v_password ? md5($v_password):'',
                'v_role'=> $v_role,
                'v_imei_number' => $v_imei_number,
                'e_status' => $e_status ,
                'l_data' => json_encode($l_data) ,
                'd_added' => date('Y-m-d H:i:s'),
                'd_modified' => date('Y-m-d H:i:s')
            );
            $id = $dclass->insert( $table, $ins );
            $id=$id['0'];
            $keyVal = array();
            if( isset( $_FILES['v_image']['name'] ) && $_FILES['v_image']['name'] != "" ) {
                $dest = UPLOAD_PATH.$folder."/";
                $file_name = $gnrl->removeChars( time().'-'.$_FILES['v_image']['name'] ); 
                if( move_uploaded_file( $_FILES['v_image']['tmp_name'], $dest.$file_name ) ){
                    $keyVal['v_image'] = $file_name;
                    // @unlink( $dest.$OLDNAME );
                }
            }
            if( count( $keyVal ) ){
                $upd['v_image'] = $file_name;
                $upd['d_modified'] = date('Y-m-d H:i:s');
                $dclass->update( $table, $upd, " id = '".$id."' ");   
            }
            ##IN VEHICLE TABLE ENTRY
            $ins2 = array(
                'i_driver_id'  => $id,
                'v_name' =>$vehicle_name,
                'v_type' =>$v_type,
                'v_vehicle_number'   => $v_vehicle_number,
                
            );
            
            ## FOR PROOF 
            $keyVal = array();
            foreach( $filesArray as $imgKey ){
                if( isset( $_FILES[$imgKey]['name'] ) && $_FILES[$imgKey]['name'] != "" ) {
                    $dest = UPLOAD_PATH.$folder."/";
                    $file_name = $gnrl->removeChars( time().'-'.$_FILES[$imgKey]['name'] ); 
                    if( move_uploaded_file( $_FILES[$imgKey]['tmp_name'], $dest.$file_name ) ){
                        $keyVal[$imgKey] = $file_name;
                        $ins2[$imgKey] = $file_name;
                        if($imgKey=='v_image_rc_book'){
                            $OLDNAME= $oldname_rc_book;
                        }
                        if($imgKey=='v_image_puc'){
                            $OLDNAME= $oldname_puc;
                        }
                        if($imgKey=='v_image_insurance'){
                            $OLDNAME= $oldname_insurance;
                        }
                        if($imgKey=='v_image_license'){
                            $OLDNAME= $oldname_license;
                        }
                        if($imgKey=='v_image_adhar_card'){
                            $OLDNAME= $oldname_adhar_card;
                        }
                        if($imgKey=='v_image_permit_copy'){
                            $OLDNAME= $oldname_permit_copy;
                        }
                        if($imgKey=='v_image_police_copy'){
                            $OLDNAME= $oldname_police_copy;
                        }
                        @unlink( $dest.$OLDNAME );
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
				//print_r( $email_exit ); exit;
				$phone_exit = $dclass->select('*',$table," AND id != '".$id."' AND v_phone = '".$v_phone."'");
                if( 0 && count( $email_exit ) ){
					$gnrl->redirectTo($page.'.php?succ=0&msg=emailexists&a=2&script=edit&id='.$_REQUEST['id']);
                }
				else if( 0 &&count( $phone_exit ) ){
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
						'l_longitude' => $l_longitude,
						'is_premium' => $is_premium,
						'v_token' => $v_token,
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
                    $keyVal = array();
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
													<a href="javascript:;" onclick="open_Image('<?php echo $putFile;?>');"><img class="edit_img" src="<?php echo $putFile;?>" ></a>
													<input type="hidden" name="old_files[v_image]" value="<?php echo $v_image; ?>">
												<?php } ?>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label>Name</label>
												<input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $v_name;?>" required />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Email</label>
												<input type="email" class="form-control" id="v_email" name="v_email" value="<?php echo $v_email; ?>" required />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Phone</label>
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
												<label>Password</label>
												<?php 
												$required="";
												if($script=='add'){
													$required='required';
												} ?>
												<input type="password" class="form-control" id="v_password" name="v_password" value="" <?php echo $required ?> />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Premium</label>
												<select class="select2" name="is_premium" id="is_premium">
													<?php echo $gnrl->get_keyval_drop( array(
														'1' => 'Yes',
														'0' => 'No',
													), $is_premium ); ?>
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
												<label>Vehicle Type</label>
												<select class="select2" name="v_type" id="v_type">
												 <?php $gnrl->getVehicleTypeDropdownList( $v_type, 'v_type' ); ?>
												</select> 
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Vehicle Number</label>
												<input type="text" class="form-control" id="v_vehicle_number" name="v_vehicle_number" value="<?php echo $v_vehicle_number; ?>" required />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Vehicle Name</label>
												<input type="text" class="form-control" id="vehicle_name" name="vehicle_name" value="<?php echo $vehicle_name; ?>" required />
											</div>
										</div>
									</div>
									<div class="row">
										<?php
										$vehicleImages = array(
											'v_image_license' => 'Driving license',
											'v_image_adhar_card' => 'Adhar Card',
											'v_image_permit_copy' => 'Permit Copy',
											'v_image_rc_book' => 'RC Book',
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
														 <a href="javascript:;" onclick="open_Image('<?php echo $putFile;?>');"><img class="edit_img" src="<?php echo $putFile;?>" ></a>
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
							
							<div class="block-flat">
								<div class="header"><h3>Bank Account Information</h3></div>
								<div class="content">
									<div class="row" style="margin-top:0" >
										<div class="col-md-12">
											<div class="form-group">
												<textarea type="text" class="form-control" name="l_data[bank_info]" style="min-height:200px;" /><?php echo $l_data['bank_info'];?></textarea>
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
												<label>Latitude</label>
												<input type="text" class="form-control" id="l_latitude" name="l_latitude" value="<?php echo $l_latitude;?>" required />
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label>Longitude</label>
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
							//$wh .= " AND u.i_delete='1'";
							$checked="checked";
						}else{
							//$wh .= " AND u.i_delete='0'";
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
									
									<a href="warroom.php?srch_filter_city=<?php echo $_GET['srch_filter_city'];?>
										&srch_filter_type=<?php echo $_GET['srch_filter_type'];?>
										" target="_blank" class="fright" >
										<button class="btn btn-primary" type="button">Show Warrrom</button>
									</a>
									
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
												<div class="pull-right">
													<div class="dataTables_filter" id="datatable_filter">
														<label>
															<input type="text" aria-controls="datatable" class="form-control fleft" placeholder="Search" name="keyword" value="<?php echo isset( $_REQUEST['keyword'] ) ? $_REQUEST['keyword'] : ""?>" style="width:auto;"/>
															<button type="submit" class="btn btn-primary fleft" style="margin-left:0px;"><span class="fa fa-search"></span></button>
															<div class="clearfix"></div> 
															<div class="pull-right" style="">
																<input class="all_access" name="deleted" value=""  type="checkbox"  onclick="document.frm.submit();" <?php echo $checked; ?>>
																Show Deleted Data
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

												<label style="margin-left:5px">Status wise 
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
												<label style="margin-left:5px">Verified wise 
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
												<label style="margin-left:5px">City wise 
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
																	<a href="javascript:;" onclick="open_Image('<?php echo $putFile;?>');" ><img class="edit_img" name="" id="v_image<?php echo $row['id']; ?>" src="<?php echo $putFile;?>" ></a>
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
																		<li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>"> Login Log</a></li>
																		<li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>"> Available Log</a></li>
																		<li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>"> Edit</a></li>
																		<?php 
																		if( !$l_data['is_otp_verified'] ){ ?>
																		<li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=verifynactive&amp;id=<?php echo $row['id'];?>">Verify & Active</a></li>
																		<?php } ?>
																		<li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=active&amp;id=<?php echo $row['id'];?>">Active</a></li>
																		<li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=inactive&amp;id=<?php echo $row['id'];?>">Inactive</a></li>
																		<li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
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

<!-- The Modal -->
<div id="myModal" class="modal">
  <span class="close">&times;</span>
  <img class="modal-content" id="img01">
  <div id="caption"></div>
</div>

<div class="md-modal colored-header  md-effect-9" id="form-primary" >
        <div class="md-content">
            <div class="modal-header">
                <h3>View On Map</h3>
                <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body form"  >
                <div id="dvMap" style="width: 590px; height: 300px"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
            </div>
        </div>
</div>
<style>
    #myImg {
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    #myImg:hover {opacity: 0.7;}

    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
    }

    /* Modal Content (image) */
    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        height: 80%;
        max-width: 850px;
        max-height: 850px;
    }

    /* Caption of Modal Image */
    #caption {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
        text-align: center;
        color: #ccc;
        padding: 10px 0;
        height: 150px;
    }

    /* Add Animation */
    .modal-content, #caption {    
        -webkit-animation-name: zoom;
        -webkit-animation-duration: 0.6s;
        animation-name: zoom;
        animation-duration: 0.6s;
    }

    @-webkit-keyframes zoom {
        from {-webkit-transform:scale(0)} 
        to {-webkit-transform:scale(1)}
    }

    @keyframes zoom {
        from {transform:scale(0)} 
        to {transform:scale(1)}
    }

    /* The Close Button */
    .close {
        position: absolute;
        top: 50px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
    }

    .close:hover,
    .close:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }

    /* 100% Image Width on Smaller Screens */
    @media only screen and (max-width: 700px){
        .modal-content {
            width: 100%;
        }
    }
</style>
<div class="md-overlay"></div>
<style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #dvMap {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #floating-panel {
        position: absolute;
        top: 10px;
        left: 25%;
        z-index: 5;
        background-color: #fff;
        padding: 5px;
        border: 1px solid #999;
        text-align: center;
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }
      #floating-panel {
        position: absolute;
        top: 5px;
        left: 50%;
        margin-left: -180px;
        width: 350px;
        z-index: 5;
        background-color: #fff;
        padding: 5px;
        border: 1px solid #999;
      }
      #latlng {
        width: 225px;
      }
    </style>
<script type="text/javascript">
    function searchDriver(slug,val){
        window.document.location.href=window.location.pathname+'?'+slug+'='+val;
    }
    function open_Image(path){
        var modal = document.getElementById('myModal');
        var img = document.getElementById('myImg');
        var modalImg = document.getElementById("img01");
        modal.style.display = "block";
        modalImg.src = path;
        var span = document.getElementsByClassName("close")[0];
        span.onclick = function() { 
            modal.style.display = "none";
        }
    }
</script>
<?php include('_scripts.php');?>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCH61_Tk5EArH8L9fEvVbqu3Q31F1t5uLQ&callback=initMap">
</script>
<script>
      function mapCall(lat1,lng1){
        var mapOptions = {
                center: new google.maps.LatLng(lat1,lng1),
                zoom: 15,
                mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById('dvMap'),mapOptions);
        var geocoder = new google.maps.Geocoder;
        var infowindow = new google.maps.InfoWindow;
        geocodeLatLng(geocoder, map, infowindow,lat1,lng1);
		
		geocodeLatLng(geocoder, map, infowindow,lat1,lng1);
		geocodeLatLng(geocoder, map, infowindow,23.048637,72.5125527);
		geocodeLatLng(geocoder, map, infowindow,23.0626746,72.5192664);
		
      }
      function geocodeLatLng(geocoder, map, infowindow,lat1,lng1) {
        var latlng = {lat: lat1, lng: lng1};
        geocoder.geocode({'location': latlng}, function(results, status) {
          if (status === 'OK') {
            if (results[1]) {
              map.setZoom(15);
              var marker = new google.maps.Marker({
                position: latlng,
                map: map
              });
              infowindow.setContent(results[1].formatted_address);
			  
              infowindow.open(map, marker);
            } else {
              window.alert('No results found');
            }
          } else {
            window.alert('Geocoder failed due to: ' + status);
          }
        });
      }
</script>

<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
