<?php 
include('includes/configuration.php');
$gnrl->check_login();
$gnrl->isPageAccess(BASE_FILE);
// _P($_REQUEST);
// exit;
	extract( $_POST );
	$page_title = "Manage Vehicle";
	$page = "vehicle";
	$table = 'tbl_vehicle';
	$title2 = 'Vehicle';
    $folder = 'vehicle_type';
	// $v_role ='driver';
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
		
		$ins = array(
			'v_name'  => $v_name,
			'v_type' =>$v_type,
			'v_vehicle_number' 	=> $v_vehicle_number,
            'l_description' => $l_description,
            'e_status' => $e_status ,
            'd_added' => date('Y-m-d H:i:s'),
            'd_modified' => date('Y-m-d H:i:s')
		);
		
		$id = $dclass->insert( $table, $ins );
		$gnrl->redirectTo($page.".php?succ=1&msg=add");
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


				    $ins = array(
                        'v_name'  => $v_name,
                        'v_type' =>$v_type,
                        'v_vehicle_number'  => $v_vehicle_number,
                        'l_description' => $l_description,
                        'e_status' => $e_status ,
                        'd_added' => date('Y-m-d H:i:s'),
                        'd_modified' => date('Y-m-d H:i:s')
                    );
                    
    				// $dclass->update( $table, $ins, " id = '".$id."' ");

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
                    
                    $upd_vehicle=array_merge($ins,$keyVal);
                    
                    
                    $dclass->update( $table, $upd_vehicle, " id = '".$id."' ");
    				$gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
			}
			else {
				// $row = $dclass->select('*',$table," AND id = '".$id."'");
                $ssql = "SELECT ".$table.".*,
                            u.v_name AS NAME
                          FROM ".$table." 
                         LEFT JOIN tbl_user 
                        as u ON ".$table.".i_driver_id = u.id
                         WHERE true AND ".$table.".id=".$id." ";
                $restepm = $dclass->query($ssql);
                $row = $dclass->fetchResults($restepm);
                // _P($row);
                // exit;
				$row = $row[0];
               

				extract( $row );
                // $l_data=json_decode($l_data,true);
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
                                    <!-- <?php if( !$script && $gnrl->checkAction('add') == '1'){?>
                                        <a href="<?php echo $page?>.php?script=add" class="fright">
                                            <button class="btn btn-primary" type="button">Add</button>
                                        </a>
                                    <?php } ?> -->
								<!--<a href="manage_ordering.php?type=brand" class="fright" >
                                    <button class="btn btn-primary" type="button">Manage Ordering</button>
                                </a>-->
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
                                                <label>Driver Name</label>
                                                <input type="text" class="form-control" id="driver_name" name="driver_name" value="<?php echo $name; ?>" required readonly="" />
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
                                                <input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $v_name; ?>" required />
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
                                                <label>Description</label>
                                                <textarea class="form-control" name="l_description"><?php echo $l_description; ?></textarea>
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
                                if( isset( $_REQUEST['keyword'] ) && $_REQUEST['keyword'] != '' ){
                                    $keyword =  trim( $_REQUEST['keyword'] );
                                    $wh = " AND ( 
                                       LOWER(u.v_name) like LOWER('%".$keyword."%')  OR
                                       LOWER(v.v_type) like LOWER('%".$keyword."%')  OR
                                        LOWER(v.e_status) like LOWER('%".$keyword."%')  OR
                                       LOWER(v.v_vehicle_number) like LOWER('%".$keyword."%') 
                                         
                                    )";
                                }
                                
                               $ssql = "SELECT v.*,
                                                u.v_name AS NAME
                                              FROM ".$table." v
                                             LEFT JOIN tbl_user 
                                            as u ON v.i_driver_id = u.id
                                             WHERE true ".$wh;
                                            
                                $sortby = ( isset( $_REQUEST['sb'] ) && $_REQUEST['sb'] != '') ? $_REQUEST['sb'] : 'id';
                                $sorttype = ( isset( $_REQUEST['st'] ) && $_REQUEST['st']=='0') ? 'ASC' : 'DESC';
                                
                                $nototal = $dclass->numRows($ssql);
                                $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
                               $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
                                $restepm = $dclass->query($sqltepm);
                                $row_Data = $dclass->fetchResults($restepm);
                                // _P($row_Data);
                                // exit;
                                
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
                                                        <?php 
                                                            if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != ''){ ?>
                                                                <a href="<?php echo $page ?>.php" class="fright" style="margin: -10px 0px 20px 0px ;" > Clear Search </a>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="pull-left">
                                                        <div id="datatable_length" class="dataTables_length">
                                                            <label><?php $pagen->writeLimitBox(); ?></label>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                                            
                                            <!-- <?php chk_all('drop');?> -->
                                            <table class="table table-bordered" id="datatable" style="width:100%;" >
                                                <thead>
                                                    <tr>
                                                        
                                                        <th width="25%">Driver Name</th>
                                                        <th width="10%">Vehicle Type</th>
                                                        <th width="10%">Vehicle No.</th>
                                                        <th width="5%">Status</th>
                                                        <th width="5%">Added Date</th>
                                                        <th width="5%"><span class="pull-right">Action</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    if($nototal > 0){
                                                            // $i=0;
                                                        foreach($row_Data as $row){
                                                            // $i++;
                                                            ?>
                                                            <tr>
                                                                <!--  -->
                                                                <td><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>"><?php echo $row['name']; ?></a></td>

                                                                <td><?php echo $row['v_type'];?></td>
                                                                <td><?php echo $row['v_vehicle_number'];?></td>
                                                                 <td><?php echo $row['e_status'];?></td>
                                                                 <td><?php echo $gnrl->removeTimezone($row['d_added']) ; ?></td>
                                                                <td>
                                                                    <?php if($gnrl->checkAction('edit')=='1'){?> 
                                                                         <div class="btn-group">
                                                                        <button class="btn btn-default btn-xs" type="button">Actions</button>
                                                                        <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
                                                                            <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                                                        </button>
                                                                        <ul role="menu" class="dropdown-menu pull-right">
                                                                            <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
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
<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
