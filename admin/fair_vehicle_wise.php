<?php 
include('includes/configuration.php');
$gnrl->check_login();

// _P($_REQUEST);
// exit;
	extract( $_POST );
	$page_title = "Manage Vehicle Fair (Vehicle Wise)";
	
	$page = "fair_vehicle_wise";
	$table = 'tbl_vehicle_fairs';
	$table2 = 'tbl_vehicle_type';
	$title2 = 'Vehicle Fair (Vehicle Wise)';
	$folder = 'vehicle_type';
	
	
	$v_type = 'vehicle_wise';
	
	
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'citywise' ) ) ? $_REQUEST['script'] : "";
	

		// _P($_REQUEST);
		// exit;
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
		
		// 
		
		$row = $dclass->select('*',$table," AND v_type = '".$v_type."' AND i_vehicle_type_id = '".$i_vehicle_type_id."' AND i_vehicle_id = '".$i_vehicle_id."'");
        $row = $row[0];
		// _P($row);
		// exit;
        if(empty($row)){
        		# get vehicle type from tbl_vehicle_type
        		$vehicle_type_data = $dclass->select('*',$table2," AND id = '".$i_vehicle_type_id."'");
        		
        		$vehicle_type_data=$vehicle_type_data[0];
	        	$ins = array(
				'v_type'  => $v_type,
				'i_vehicle_type_id'  => $i_vehicle_type_id,
				'v_vehicle_type' =>$vehicle_type_data['v_type'],
				'i_vehicle_id' => $i_vehicle_id,
				'l_data' => json_encode($l_data),
	            'd_added' => date('Y-m-d H:i:s'),
	            'd_modified' => date('Y-m-d H:i:s'),
				'e_status' => $e_status,
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
					$ins = array('i_delete'=>'1');
            		$dclass->update( $table, $ins, " id = '".$id."'");
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
				
				$if_exist = $dclass->select('*',$table," AND v_type = '".$v_type."' AND i_vehicle_type_id = '".$i_vehicle_type_id."' AND i_vehicle_id = '".$i_vehicle_id."' AND id !=".$id." ");
			
				if(empty($if_exist)){
					# get vehicle type from tbl_vehicle_type
        			$vehicle_type_data = $dclass->select('*',$table2," AND id = '".$i_vehicle_type_id."'");
	        		$vehicle_type_data=$vehicle_type_data[0];
					$ins = array(
						" v_type = '".$v_type."' ",
						" i_vehicle_type_id = '".$i_vehicle_type_id."' ",
						" v_vehicle_type = '".$vehicle_type_data['v_type']."' ",
						" i_vehicle_id = '".$i_vehicle_id."' ",
						" d_modified = '".date('Y-m-d H:i:s')."' ",
						" l_data = l_data || '".json_encode($l_data)."' ",
						" e_status = '".$e_status."' ",
					);
					
					$dclass->updateJsonb( $table, $ins, " id = '".$id."' ");
					 // $dclass->update( $table, $ins, " id = '".$id."' ");
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
	
	$chargesTypes = array(
		'city_wise' => 'City Wise',
		'day_wise' => 'Day Wise',
		'date_wise' => 'Date Wise',
	);

	

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
                                <?php echo $script ? ucfirst( $script ).' '.ucfirst( $title2 ) : 'List Of '.' '.ucfirst( $title2 ).''; ?> 
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
                        if( ($script == 'add' || $script == 'edit') && 1){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
                                        	<div class="row">
		                                        
												<div class="col-md-12">
		                                            <div class="form-group">
		                                                <label>Vehicle Type</label>
		                                                <select class="select2" name="i_vehicle_type_id" id="i_vehicle_type_id" onChange="load_vehicles();" >
		                                                 <?php $gnrl->getVehicleTypeDropdownList($i_vehicle_type_id); ?>
		                                                </select> 
		                                            </div>
		                                        </div>
												
												<div class="col-md-12">
		                                        	<div class="form-group div_vehicle_list">
		                                        	   <label>Select City (Driver Name / Vehicle Name / Vehicle Number)</label>
		                                               <select class="select2 required" name="i_vehicle_id" id="i_vehicle_id"></select> 
		                                            </div>
		                                        </div>
		                                        
                                        	</div>
                                        	
											<div class="row">
												<div class="col-md-12">
													<h3>Ride Charges</h3>
													<div class="row" >
														<div class="col-md-12">
															<?php 
															foreach( $globalCharges as $chargeKey => $chargeVal ) {
																if( !in_array( $chargeKey, array( 'max_dry_run_km', 'max_dry_run_charge' ) ) ){
																	continue;
																}
																?>
																<div class="form-group">
																	<label><?php echo $chargeVal;?></label>
																	<input type="text" class="form-control" name="l_data[charges][<?php echo $chargeKey;?>]" value="<?php echo $l_data['charges'][$chargeKey];?>"  />
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
							if(1){
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
	                            
								$wh = " AND a.v_type = '".$v_type."' ";
	                            if( isset( $_REQUEST['keyword'] ) && $_REQUEST['keyword'] != '' ){
	                                $keyword =  trim( $_REQUEST['keyword'] );
									$wh .= " AND ( 
	                                   LOWER(a.v_vehicle_type) like LOWER('%".$keyword."%')
	                                   OR LOWER(v.v_name) like LOWER('%".$keyword."%')
	                                   OR LOWER(u.v_name) like LOWER('%".$keyword."%')
	                                    OR LOWER(a.e_status) like LOWER('%".$keyword."%')
	                                   OR LOWER(v.v_vehicle_number) like LOWER('%".$keyword."%')
									   OR LOWER(a.l_data->'geo'->>'area_name') like LOWER('%".$keyword."%') 
	                                )";
	                            }
	                            if( isset( $_REQUEST['deleted'] ) ){
                                    $wh .= " AND a.i_delete='1'";
                                    $checked="checked";
                                }else{
                                    $wh .= " AND a.i_delete='0'";
                                }
	                            $ssql = 
							   	"SELECT
									a.*,
									v.v_name as vehicle_name,
									v.v_vehicle_number as vehicle_number,
									u.v_name as driver_name
								FROM 
									tbl_vehicle_fairs a

								LEFT JOIN 
									tbl_vehicle as v ON a.i_vehicle_id = v.id 
								LEFT JOIN 
									tbl_user as u ON v.i_driver_id = u.id 
									
								WHERE true ".$wh;
	                                        
	                            $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'v_vehicle_type' );
                            	$sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'ASC' );
	                            
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
	                                                            <div class="clearfix"></div> 
                                                                <div class="pull-right" style="">
                                                                    <input class="all_access" name="deleted" value=""  type="checkbox"  onclick="document.frm.submit();" <?php echo $checked; ?>>
                                                                    Show Deleted Data
                                                                </div>
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
	                                        	<?php
				                                    echo $gnrl->renderTableHeader(array(
				                                        'v_vehicle_type' => array( 'order' => 1, 'title' => 'Vehicle Type' ),
				                                        'vehicle_name' => array( 'order' => 1, 'title' => 'Vehicle Name' ),
				                                        'vehicle_number' => array( 'order' => 1, 'title' => 'Vehicle Number' ),
				                                        'driver_name' => array( 'order' => 1, 'title' => 'Driver Name' ),
				                                        'e_status' => array( 'order' => 1, 'title' => 'Status' ),
				                                        'd_added' => array( 'order' => 1, 'title' => 'Added Date' ),
				                                        'action' => array( 'order' => 0, 'title' => 'Action' ),
				                                    ));
				                                ?>	
	                                            <tbody>
	                                                <?php 
	                                                if( $nototal > 0 ){
														
														foreach( $row_Data as $row ){
	                                                    	$l_data = json_decode( $row['l_data'], true );
	                                                    	?>
	                                                        <tr>
																<td>
																	<?php echo ucfirst( $row['v_vehicle_type'] );?>
																</td>
																<td><?php echo ucfirst( $row['vehicle_name'] );?></td>
																<td><?php echo ucfirst( $row['vehicle_number'] );?></td>
																<td><?php echo ucfirst( $row['driver_name'] );?></td>
																<td><?php echo $row['e_status'];?></td>
																<td><?php echo $gnrl->displaySiteDate($row['d_added']);?></td>
																<td>
																	<?php if(1){?> 
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
	                                    									<!--  <li><a href="<?php echo $page;?>.php?a=4&script=citywise&id=<?php echo $row['id'];?>">Manage City Wise</a></li> -->
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

<script>
	function load_vehicles(){
		var i_vehicle_type_id = jQuery('#i_vehicle_type_id').val();
		var data = {
			'i_vehicle_type_id' : i_vehicle_type_id,
			'i_vehicle_id' : '<?php echo $i_vehicle_id;?>',
		}
		jQuery.ajax({
			type : 'POST',
			url : '<?php echo ADMIN_AJAX_URL;?>?mode=load_vehicles',
			data : data,
			success : function(res){
				jQuery('.div_vehicle_list div').remove();
				jQuery('#i_vehicle_id').html(res);
				jQuery('#i_vehicle_id').select2({
				  width: '100%'
				 });
			}
		});
	}
	load_vehicles();
</script>

</body>
</html>
