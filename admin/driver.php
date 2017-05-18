<?php 
include('includes/configuration.php');
$gnrl->check_login();
$gnrl->isPageAccess(BASE_FILE);
// _P($_REQUEST);
// exit;
	extract( $_POST );
	$page_title = "Manage Driver";
	$page = "driver";
	$table = 'tbl_user';
    $table2 = 'tbl_vehicle';
	$title2 = 'Driver';
	$v_role ='driver';
    $folder = 'vehicles';
    $folder2 = 'users';
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
                $dest = UPLOAD_PATH.$folder2."/";
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
                'e_status' => $e_status ,
                'd_added' => date('Y-m-d H:i:s'),
                'd_modified' => date('Y-m-d H:i:s')
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
                if($gnrl->checkAction('delete') == '1'){
                    $dclass->delete( $table ," id = '".$id."'");
                    $gnrl->redirectTo($page.".php?succ=1&msg=del");
                }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                }
            }
            // make records active
            else if($_REQUEST['chkaction'] == 'active'){
                if($gnrl->checkAction('edit') == '1'){
                    $ins = array('e_status'=>'active');
                    $dclass->update( $table, $ins, " id = '".$id."'");
                    $gnrl->redirectTo($page.".php?succ=1&msg=multiact");
                }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                }
            }
			else if($_REQUEST['chkaction'] == 'verifynactive'){
                if($gnrl->checkAction('edit') == '1'){
					$ins = array();
					$ins[] = " v_otp = '' ";
					$ins[] = " e_status = 'active' ";
					$ins[] = " l_data = l_data || '".json_encode(array(
						'is_otp_verified' => 1,
					))."' ";
					$dclass->updateJsonb( $table, $ins, " id = '".$id."' ");	
					
                    $ins = array('e_status'=>'active');
                    $dclass->update( $table, $ins, " id = '".$id."'");
                    $gnrl->redirectTo($page.".php?succ=1&msg=multiact");
                }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                }
            }
			
            // make records inactive
            else if($_REQUEST['chkaction'] == 'inactive'){
                if($gnrl->checkAction('edit') == '1'){
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
           // _P($_REQUEST);
           // _P($_FILES);
           // exit;
			$id = $_REQUEST['id'];
			if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ) {

                $email_exit = $dclass->select('*',$table," AND id != ".$id." AND v_email = '".$v_email."'");
               
                if(count($email_exit) && !empty($email_exit)){
                     $gnrl->redirectTo($page.'.php?succ=0&msg=email_exit&a=2&script=edit&id='.$_REQUEST['id']);
                }else{
                    $ins = array();
                        if(!empty($v_password)){
                            #FOR CHECK PASSWORD
                         $check_pass = $dclass->select('*',$table," AND id = ".$id."");
                         if(count($check_pass)){
                            if(md5($v_password) == $check_pass[0]['v_password']){
                            }else{
                                $ins['v_password']= md5($v_password);
                            }
                         }
                     }
                    $ins['v_name'] = $v_name;
                    $ins['v_email'] = $v_email;
                    $ins['v_phone'] = $v_phone;
                    $ins['e_status'] = $e_status;
                    $ins['l_data'] = json_encode($l_data);
                    $ins['d_modified'] = date('Y-m-d H:i:s');
                    
                    $keyVal = array();
                    ## for profile image
                    if( isset( $_FILES['v_image']['name'] ) && $_FILES['v_image']['name'] != "" ) {
                        $dest = UPLOAD_PATH.$folder."/";
                        $file_name = $gnrl->removeChars( time().'-'.$_FILES['v_image']['name'] ); 
                        if( move_uploaded_file( $_FILES['v_image']['tmp_name'], $dest.$file_name ) ){
                            $keyVal['v_image'] = $file_name;
                             @unlink( $dest.$oldname_vimage );
                        }
                    }
                    if( count( $keyVal ) ){
                        $ins['v_image'] = $file_name;
                    }
                    $dclass->update( $table, $ins, " id = '".$id."' ");
                    ## FOR ALL PROOF
                    $upd_vehicle=array(
                        'v_type' =>$v_type,
                        'v_vehicle_number' =>$v_vehicle_number,
                        'v_name' =>$vehicle_name,
                    );
                    $keyVal = array();
                    foreach( $filesArray as $imgKey ){
                        if( isset( $_FILES[$imgKey]['name'] ) && $_FILES[$imgKey]['name'] != "" ) {
                            $dest = UPLOAD_PATH.$folder."/";
                            $file_name = $gnrl->removeChars( time().'-'.$_FILES[$imgKey]['name'] ); 
                            if( move_uploaded_file( $_FILES[$imgKey]['tmp_name'], $dest.$file_name ) ){
                                $keyVal[$imgKey] = $file_name;
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
                    $upd_vehicle=array_merge($upd_vehicle,$keyVal);
                    
                    $dclass->update( $table2, $upd_vehicle, " i_driver_id = '".$id."' ");
                    $gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
                }
			}
			else {
				 $ssql = "SELECT ".$table.".*,
                            u.*,
                            ".$table.".v_name AS driver_name,
                            u.v_name AS vehicle_name
                            
                          FROM ".$table." 
                         LEFT JOIN ".$table2." 
                        as u ON ".$table.".id = u.i_driver_id
                         WHERE true AND ".$table.".id=".$id." ";
                $restepm = $dclass->query($ssql);
                $row = $dclass->fetchResults($restepm);
                $row = $row[0];
                if($_REQUEST['D']='1'){
                    _P($row);
                }
                extract( $row );
                $l_data = json_decode($l_data,true);
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
                    <div class="block-flat">
                        <div class="header">
                            <h3>
                                <?php echo $script ? ucfirst( $script ).' '.ucfirst( $title2 ) : 'List Of '.' '.ucfirst( $title2 ).'s'; ?> 
                                <?php if( !$script ){?>
                                    <?php if( !$script && $gnrl->checkAction('add') == '1'){?>
                                        <a href="<?php echo $page?>.php?script=add" class="fright">
                                            <button class="btn btn-primary" type="button">Add</button>
                                        </a>
                                    <?php } ?>
								<?php } ?>
                            </h3>
                        </div>
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && $gnrl->checkAction($script) == '1' ){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $driver_name; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" class="form-control" id="v_email" name="v_email" value="<?php echo $v_email; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Password</label>
                                                <?php 
                                                $required="";
                                                if($script=='add'){
                                                    $required='required';
                                                } ?>
                                                <input type="password" class="form-control" id="v_password" name="v_password" value="" <?php echo $required ?> />
                                            </div>
                                            <div class="form-group">
                                                <label>Phone</label>
                                                <input type="text" class="form-control" id="v_phone" name="v_phone" value="<?php echo $v_phone; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>IMEI Number</label>
                                                <input type="text" class="form-control" id="v_imei_number" name="v_imei_number" value="<?php echo $v_imei_number; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Image</label>
                                                <input class="form-control" type="file" name="v_image" style="height:auto;"  >
                                                <?php 
                                                    if( $putFile = _is_file( $folder, $v_image ) ){ //echo $putFile; ?>
                                                    <img class="edit_img" src="<?php echo $putFile;?>" >
                                                    <input type="hidden" name="oldname_vimage" value="<?php echo $v_image; ?>">
                                                <?php } ?>
                                            </div>
                                             
                                            <div class="form-group">
                                                <label>Vehicle Type</label>
                                                <input type="text" class="form-control" id="v_type" name="v_type" value="<?php echo $v_type; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Vehicle Number</label>
                                                <input type="text" class="form-control" id="v_vehicle_number" name="v_vehicle_number" value="<?php echo $v_vehicle_number; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Vehicle Name</label>
                                                <input type="text" class="form-control" id="vehicle_name" name="vehicle_name" value="<?php echo $vehicle_name; ?>" required />
                                            </div>
                                            <div class="row">
                                            <div class="col-sm-4 col-md-4">
                                                <div class="content">
                                                    <div class="form-group"> 
                                                        <label>Driving license</label>
                                                        <input class="form-control" type="file" id="v_image_license" name="v_image_license" style="height:auto;"  >
                                                        <?php 
                                                        if( $putFile = _is_file( $folder, $v_image_license ) ){ //echo $putFile; ?>
                                                            <img class="edit_img" src="<?php echo $putFile;?>" >
                                                            <input type="hidden" name="oldname_license" value="<?php echo $v_image_license; ?>">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4 col-md-4">
                                                <div class="content">
                                                    <div class="form-group"> 
                                                        <label>Aadhar card</label>
                                                        <input class="form-control" type="file" id="v_image_adhar_card" name="v_image_adhar_card" style="height:auto;"  >
                                                        <?php 
                                                        if( $putFile = _is_file( $folder, $v_image_adhar_card ) ){ //echo $putFile; ?>
                                                            <img class="edit_img" src="<?php echo $putFile;?>" >
                                                            <input type="hidden" name="oldname_adhar_card" value="<?php echo $v_image_adhar_card; ?>">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4 col-md-4">
                                                <div class="content">
                                                    <div class="form-group"> 
                                                       <label>Permit Copy</label>
                                                        <input class="form-control" type="file" id="v_image_permit_copy" name="v_image_permit_copy" style="height:auto;"  >
                                                        <?php 
                                                        if( $putFile = _is_file( $folder, $v_image_permit_copy ) ){ //echo $putFile; ?>
                                                            <img class="edit_img" src="<?php echo $putFile;?>" >
                                                            <input type="hidden" name="oldname_permit_copy" value="<?php echo $v_image_permit_copy; ?>">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                             </div>
                                            </div>
                                            <div class="row">
                                            <div class="col-sm-4 col-md-4">
                                                <div class="content">
                                                    <div class="form-group"> 
                                                        <label>RC Book Image</label>
                                                        <input class="form-control" type="file" id="v_image_rc_book" name="v_image_rc_book" style="height:auto;"  >
                                                        <?php 
                                                        if( $putFile = _is_file( $folder, $v_image_rc_book ) ){ //echo $putFile; ?>
                                                            <img class="edit_img" src="<?php echo $putFile;?>" >
                                                            <input type="hidden" name="oldname_rc_book" value="<?php echo $v_image_rc_book; ?>">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4 col-md-4">
                                                <div class="content">
                                                    <div class="form-group"> 
                                                        <label>PUC Image</label>
                                                        <input class="form-control" type="file" id="v_image_puc" name="v_image_puc" style="height:auto;"  >
                                                       <?php 
                                                        if( $putFile = _is_file( $folder, $v_image_puc ) ){ //echo $putFile; ?>
                                                            <img class="edit_img" src="<?php echo $putFile;?>" >
                                                            <input type="hidden" name="oldname_puc" value="<?php echo $v_image_puc; ?>">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4 col-md-4">
                                                <div class="content">
                                                    <div class="form-group"> 
                                                       <label>Insurance Image</label>
                                                        <input class="form-control" type="file" id="v_image_insurance" name="v_image_insurance" style="height:auto;"  >
                                                       <?php 
                                                        if( $putFile = _is_file( $folder, $v_image_insurance ) ){ //echo $putFile; ?>
                                                            <img class="edit_img" src="<?php echo $putFile;?>" >
                                                            <input type="hidden" name="oldname_insurance" value="<?php echo $v_image_insurance; ?>">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                             </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4 col-md-4">
                                                    <div class="content">
                                                        <div class="form-group"> 
                                                            <label>Police verification</label>
                                                            <input class="form-control" type="file" id="v_image_police_copy" name="v_image_police_copy" style="height:auto;"  >
                                                            <?php 
                                                            if( $putFile = _is_file( $folder, $v_image_police_copy ) ){ //echo $putFile; ?>
                                                                <img class="edit_img" src="<?php echo $putFile;?>" >
                                                                <input type="hidden" name="oldname_police_copy" value="<?php echo $v_image_police_copy; ?>">
                                                            <?php } ?>
                                                        </div>
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
                                </div>
							</form>
							<?php 
                        }
                        else{
						    if($gnrl->checkAction($script) == '1'){
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
                                if( isset( $_REQUEST['filter'] ) && $_REQUEST['filter'] != '' ){
                                    $keyword =  trim( $_REQUEST['filter'] );
                                    $wh = " AND ( 
                                       LOWER(u.e_status) like LOWER('".$keyword."') 
                                         
                                    )";
                                }
                                if( isset( $_REQUEST['otp_verified'] ) && $_REQUEST['otp_verified'] != '' ){
                                    $keyword =  trim( $_REQUEST['otp_verified'] );
                                    $wh = " AND ( 
                                      u.l_data->>'is_otp_verified' = '".$keyword."' 
                                    )";
                                }
                                if( isset( $_REQUEST['keyword'] ) && $_REQUEST['keyword'] != '' ){
                                    $keyword =  trim( $_REQUEST['keyword'] );
                                    $wh = " AND ( 
                                        LOWER(u.v_name) like LOWER('%".$keyword."%')
                                        OR LOWER(u.v_email) like LOWER('%".$keyword."%')
                                        OR LOWER(u.v_phone) like LOWER('%".$keyword."%')
                                        OR LOWER(v.v_type) like LOWER('%".$keyword."%')
                                        OR LOWER(v.v_vehicle_number) like LOWER('%".$keyword."%')
                                        OR LOWER(u.e_status) like LOWER('%".$keyword."%')
                                    )";
                                }

                               $ssql = "SELECT u.*,
                                                v.v_name AS vehicle_name,
                                                v.v_type AS vehicle_type,
                                                v.v_vehicle_number AS vehicle_number,
                                                v.l_latitude AS lat,
                                                v.l_longitude AS long
                                              FROM ".$table." as u
                                             LEFT JOIN tbl_vehicle 
                                            as v ON u.id = v.i_driver_id
                                             WHERE true AND u.v_role='".$v_role."' ".$wh;
                                            
                                $sortby = ( isset( $_REQUEST['sb'] ) && $_REQUEST['sb'] != '') ? $_REQUEST['sb'] : 'id';
                                $sorttype = ( isset( $_REQUEST['st'] ) && $_REQUEST['st']=='0') ? 'ASC' : 'DESC';
                                
                                $nototal = $dclass->numRows($ssql);
                                $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
                                $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
                                $restepm = $dclass->query($sqltepm);
                                $row_Data = $dclass->fetchResults($restepm);
                                if($_REQUEST['J']=='1'){
                                    _p($row_Data);
                                    
                                }
                                $otp_arr = array(
                                    '0' => 'otp verified',
                                    '1' =>'otp not verified'
                                );
                                
                                ?>
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
                                                            </label>
                                                        </div>
                                                        <?php if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '' || isset($_REQUEST['filter']) && $_REQUEST['filter'] != '' || isset($_REQUEST['otp_verified']) && $_REQUEST['otp_verified'] != ''  ){ ?>
                                                                <a href="<?php echo $page ?>.php" class="fright" style="margin: -10px 0px 20px 0px ;" > Clear Search </a>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="pull-left">
                                                        <div id="datatable_length" class="dataTables_length">
                                                            <label><?php $pagen->writeLimitBox(); ?></label>
                                                        </div>
                                                    </div>
                                                    <div class="pull-left" style="margin: 20px;">
                                                        <div>
                                                         <select class="select2" name="status_sel" id="status_sel" onChange="searchDriver('filter',this.options[this.selectedIndex].value)">
                                                                 <?php $gnrl->getDropdownList(array('active','inactive'),$_GET['filter']); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="pull-left" style="margin: 20px;">
                                                        <div>
                                                         <select class="select2" name="otp_verified" id="otp_verified" onChange="searchDriver('otp_verified',this.options[this.selectedIndex].value)">
                                                                 <?php echo $gnrl->get_keyval_drop($otp_arr,$_GET['otp_verified']); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                                            
                                            <!-- <?php chk_all('drop');?> -->
                                            <table class="table table-bordered" id="datatable" style="width:100%;" >
                                                <thead>
                                                    <tr>
                                                        <th width="15%">Name</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Vehicle Type</th>
                                                        <th>Vehicle No.</th>
                                                        <th>Status<br>Location</th>
                                                        <th><span class="pull-right">Action</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    if($nototal > 0){
                                                        foreach($row_Data as $row){
                                                            ?>
                                                            <tr>
                                                                <td><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>"><?php echo $row['v_name']; ?></a></td>

                                                                <td><?php echo $row['v_email'];?></td>
                                                                <td><?php echo $row['v_phone'];?></td>
                                                                <td><?php echo $row['vehicle_type'];?></td>
                                                                <td><?php echo $row['vehicle_number'];?></td>
                                                                  
                                                                <td><?php echo $row['e_status'];?><br><a id="view_map"class="md-trigger " href="javascript:;" data-modal="form-primary" onclick="mapCall(<?php echo $row['lat'].",".$row['long'] ?> )">View</a></td>
                                                               
                                                                <td>
                                                                    <?php if($gnrl->checkAction('edit')=='1'){?> 
                                                                         <div class="btn-group">
                                                                        <button class="btn btn-default btn-xs" type="button">Actions</button>
                                                                        <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
                                                                            <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                                                        </button>
                                                                        <ul role="menu" class="dropdown-menu pull-right">
                                                                            <li><a href="javascript:;">View Log</a></li>
                                                                            <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
																			<li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=verifynactive&amp;id=<?php echo $row['id'];?>">Verify & Active</a></li>
                                                                            <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=active&amp;id=<?php echo $row['id'];?>">Active</a></li>
                                                                            <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=inactive&amp;id=<?php echo $row['id'];?>">Inactive</a></li>
                                                                            <li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
                                                                        </ul>
                                                                    </div>
                                                                    <?php } ?>
                                                                   
                                                                </td>
                                                            </tr><?php 
                                                        }
                                                    }
                                                    else{?>
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
                            <?php
                            }else{ ?>
                                    <h3>
                                        <a href="<?php echo $page?>.php" class="fright">
                                            <button class="btn btn-primary" type="button">Back</button>
                                        </a>
                                    </h3>
                                    <h2 class="text-danger">You Have Not Permission to Access this Section.</h2>
                            <?php 
                            }
                        }?>
                    </div>
                    
                </div>
            </div>
        </div>
	</div>
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
