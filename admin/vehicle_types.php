<?php 
include('includes/configuration.php');
$gnrl->check_login();

// _P($_REQUEST);exit;
	extract( $_POST );
	$page_title = "Manage Vehicle Types";
	$page = "vehicle_types";
	$table = 'tbl_vehicle_type';
	$table2 = 'tbl_vehicle_fairs';
	$title2 = 'Vehicle Type';
	$folder = 'vehicle_type';
	
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'citywise' ) ) ? $_REQUEST['script'] : "";
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){

		$row = $dclass->select('*',$table," AND v_type = '".$v_type."' AND v_name = '".$v_name."'");

		if(empty($row)){
			$ins = array(
				'v_name'  => $v_name,
				'v_type' =>$v_type,
				'l_data' => json_encode($l_data),
	            'e_status' => $e_status ,
	            'd_added' => date('Y-m-d H:i:s'),
	            'd_modified' => date('Y-m-d H:i:s')
			);
			
			$id = $dclass->insert( $table, $ins );
			$filesArray = array(
				'list_icon',
				'active_icon',
				'plotting_icon',
			);
			$keyVal = array();
			foreach( $filesArray as $imgKey ){
				if( isset( $_FILES['l_data']['name'][$imgKey] ) && $_FILES['l_data']['name'][$imgKey] != "" ) {
					$dest = UPLOAD_PATH.$folder."/";
					$file_name = $gnrl->removeChars( time().'-'.$_FILES['l_data']['name'][$imgKey] ); 
					if( move_uploaded_file( $_FILES['l_data']['tmp_name'][$imgKey], $dest.$file_name ) ){
						$keyVal[$imgKey] = $file_name;
						// @unlink( $dest.$OLDNAME );
					}
				}
			}
			if( count( $keyVal ) ){
				$ins[] = "l_data = l_data || '".json_encode($keyVal)."'";
				$dclass->updateJsonb( $table, $ins, " id = '".$id."' ");	
			}
					
			$gnrl->redirectTo($page.".php?succ=1&msg=add");
		}else{
			$gnrl->redirectTo($page.".php?succ=0&msg=cityexit");
		}
		
	}

	## Delete Record from the database starts
	if(isset($_REQUEST['a']) && $_REQUEST['a']==3) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
			$id = $_REQUEST['id'];
			if($_REQUEST['chkaction'] == 'delete') {
				if(1){
					$dclass->delete( $table ," id = '".$id."'");
					$gnrl->redirectTo($page.".php?succ=1&msg=del");
				}else{
					$gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
				}
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

				
				$if_exist = $dclass->select('*',$table," AND v_type = '".$v_type."' AND v_name = '".$v_name."' AND id !=".$id." ");
				if(empty($if_exist)){
					$ins = array(
						" v_name = '".$v_name."' ",
						" v_type = '".$v_type."' ",
						" l_data = l_data || '".json_encode($l_data)."' ",
						" d_modified = '".date('Y-m-d H:i:s')."' ",
						" e_status =	'".$e_status."'	",
					);
					$dclass->updateJsonb( $table, $ins, " id = '".$id."' ");
					$ins = array();
					$filesArray = array(
						'list_icon',
						'active_icon',
						'plotting_icon',
					);
					$keyVal = array();
					foreach( $filesArray as $imgKey ){
						if( isset( $_FILES['l_data']['name'][$imgKey] ) && $_FILES['l_data']['name'][$imgKey] != "" ) {
							$dest = UPLOAD_PATH.$folder."/";
							$file_name = $gnrl->removeChars( time().'-'.$_FILES['l_data']['name'][$imgKey] ); 
							if( move_uploaded_file( $_FILES['l_data']['tmp_name'][$imgKey], $dest.$file_name ) ){
								$keyVal[$imgKey] = $file_name;
								if($imgKey=='list_icon'){
									$OLDNAME= $oldname_list;
								}
								if($imgKey=='active_icon'){
									$OLDNAME= $oldname_active;
								}
								if($imgKey=='plotting_icon'){
									$OLDNAME= $oldname_plotting;
								}
								@unlink( $dest.$OLDNAME );
							}
						}
					}
					if( count( $keyVal ) ){
						$ins[] = "l_data = l_data || '".json_encode($keyVal)."'";
						$dclass->updateJsonb( $table, $ins, " id = '".$id."' ");	
					}
					
					$gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
				}else{
					$gnrl->redirectTo($page.'.php?succ=0&msg=cityexit&a=2&script=edit&id='.$_REQUEST['id']);
				}
				
			}
			else {
				$row = $dclass->select('*',$table," AND id = '".$id."'");
                $row = $row[0];
               	extract( $row );
               	$l_data = json_decode( $l_data, true );

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
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && 1 ){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
                                            <div class="form-group">
                                                <label>Vehicle Type Name</label>
                                                <input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $v_name;?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Vehicle Type</label>
                                                <input type="text" class="form-control" id="v_type" name="v_type" value="<?php echo $v_type;?>" required />
                                            </div>
											
											<div class="row">
												<div class="col-md-12">
													<h3>Icons</h3>
													<div class="row" >
														<div class="col-md-4">
															<div class="form-group">
																<label>List Icon</label>
																<input class="form-control" type="file" name="l_data[list_icon]" style="height:auto;"  >
																<?php 
																if( $putFile = _is_file( $folder, $l_data['list_icon'] ) ){ //echo $putFile; ?>
																<img class="edit_img" src="<?php echo $putFile;?>" >
																<input type="hidden" name="oldname_list" value="<?php echo $l_data['list_icon']; ?>">
																<?php } ?>
															</div>
														</div>
														<div class="col-md-4">
															<div class="form-group">
																<label>Active Icon</label>
																<input class="form-control" type="file" name="l_data[active_icon]" style="height:auto;"  >
																<?php 
																if( $putFile = _is_file( $folder, $l_data['active_icon'] ) ){ ?>
																<img class="edit_img" src="<?php echo $putFile;?>" >
																<?php } ?>
																<input type="hidden" name="oldname_active" value="<?php echo $l_data['active_icon']; ?>">
															</div>
														</div>
														<div class="col-md-4">
															<div class="form-group">
																<label>Plotting Icon</label>
																<input class="form-control" type="file" name="l_data[plotting_icon]" style="height:auto;"  >
																<?php 
																if( $putFile = _is_file( $folder, $l_data['plotting_icon'] ) ){ ?>
																<img class="edit_img" src="<?php echo $putFile;?>" >
																<?php } ?>
																<input type="hidden" name="oldname_plotting" value="<?php echo $l_data['plotting_icon']; ?>">
															</div>
														</div>
													</div>
												</div>
										  </div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<h3>Show Estimate Charge</h3>
														<?php $key = "i_show_estimate_charge"; ?>
														<select class="select2" name="l_data[charges][<?php echo $key; ?>]" id="i_show_estimate_charge">
															<?php echo $gnrl->get_keyval_drop($globalShowEstimateCharge,$l_data['charges'][$key]); ?>
														</select>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<h3>Ride Charges</h3>
													<div class="row" >
														<div class="col-md-12">
															<?php 
															foreach( $globalCharges as $chargeKey => $chargeVal ) { ?>
																<div class="form-group">
																	<label><?php echo $chargeVal;?></label>
																	<input type="text" class="form-control" name="l_data[charges][<?php echo $chargeKey;?>]" value="<?php echo $l_data['charges'][$chargeKey];?>"  />
																</div> <?php 
															}?>		
														</div>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<h3>Other Settings</h3>
													<div class="row" >
														<div class="col-md-12">
															<?php 
															foreach( $globalVehicleOtherSettings as $rowK => $rowV ) { ?>
																<div class="form-group">
																	<label><?php echo $rowV;?></label>
																	<input type="text" class="form-control" name="l_data[other][<?php echo $rowK;?>]" value="<?php echo $l_data['other'][$rowK];?>"  />
																</div> <?php 
															}?>		
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
                        }else{
                        	
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
	                                   LOWER(v_name) like LOWER('%".$keyword."%')
	                                   OR LOWER(v_type) like LOWER('%".$keyword."%')
	                                   OR LOWER(e_status) like LOWER('%".$keyword."%')
									     
	                                )";
	                            }
	                            
	                           $ssql = "SELECT * FROM ".$table." WHERE true ".$wh;
	                                        
	                            $sortby = ( isset( $_REQUEST['sb'] ) && $_REQUEST['sb'] != '') ? $_REQUEST['sb'] : 'id';
	                            $sorttype = ( isset( $_REQUEST['st'] ) && $_REQUEST['st']=='0') ? 'ASC' : 'DESC';
	                            
	                            $nototal = $dclass->numRows($ssql);
	                            $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
	                            $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
	                            $restepm = $dclass->query($sqltepm);
	                            $row_Data = $dclass->fetchResults($restepm);
	                            
	                            
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
														<th width="25%">Vehicle Type</th>
	                                                    <th width="5%">Status</th>
	                                                    <th width="5%">Added Date</th>
	                                                    <th width="7%"><span class="pull-right">Action</span> </th>
	                                                </tr>
	                                            </thead>
	                                            <tbody>
	                                                <?php 
	                                                if( $nototal > 0 ){
														$i = 0;
														foreach( $row_Data as $row ){
	                                                    	$i++;
	                                                    	?>
	                                                        <tr>
																<td>
																	<a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">
																	<?php echo $row['v_name']; ?>
																	</a>
																	<br>
																	(<?php echo $row['v_type'];?>)
																</td>
																<td><?php echo $row['e_status'];?></td>
																<td><?php echo $gnrl->removeTimezone($row['d_added']) ; ?></td>
	                                                            <td class="text-right" >
	                                                            	
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
