<?php 
include('includes/configuration.php');
$gnrl->check_login();
$gnrl->isPageAccess(BASE_FILE);

	extract( $_POST );
	$page_title = "Manage Driver Trip";
	$page = "driver_trips";
	$page2 = "track";
	$table = 'tbl_ride';
	
	$title2 = 'Driver Trip';
	$folder = 'vehicle_type';
	
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'citywise' ) ) ? $_REQUEST['script'] : "";
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){

		$row = $dclass->select('*',$table," AND v_type = '".$v_type."' AND v_name
		 = '".$v_name."'");

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

			$id = $_REQUEST['id'];
			if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ){
				
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
				$ssql = "SELECT 
                			".$table.".*,
                            d.v_name AS d_name,
                            u.v_name AS u_name,
                            v.v_type AS vehicle_type,
                            v.v_vehicle_number AS vehicle_number
                            FROM ".$table." 
                            LEFT JOIN tbl_user as d ON ".$table.".i_driver_id = d.id
                            LEFT JOIN tbl_user as u ON ".$table.".i_user_id = u.id
                            LEFT JOIN tbl_vehicle as v ON ".$table.".i_vehicle_id = v.id
                             WHERE true AND ".$table.".id= '".$id."' ";
				
                $restepm = $dclass->query($ssql);
                $row = $dclass->fetchResults($restepm);
                // _P($row);
                // exit;
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
                                View Driver Trip
                                <?php 
                                    if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '' || isset($_REQUEST['status_sel']) && $_REQUEST['status_sel'] != ''  || isset($_REQUEST['driver']) && $_REQUEST['driver'] != ''){ ?>
                                        <a href="<?php echo $page ?>.php" class="fright" >
                                            <button class="btn btn-primary" type="button">Clear Search</button>
                                        </a>
                                <?php } ?>
                            </h3>
                        </div>
                       <!--  <style type="text/css">
                        	.viewtable tr td{
                        		text-align:  center !important;
                        	}
                        </style> -->
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && $gnrl->checkAction($script) == '1' ){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-10">
		                                <table class="table table-bordered viewtable" id="datatable" style="width:100%;" >
                                            <thead>
                                                <tr>
													<th width="40%"><strong>Fields</strong></th>
                                                    <th width="60%"><strong> Data </strong></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
													<td>Driver Name</td>
													<td><?php echo $d_name; ?>
													</td>
                                                </tr>
                                                <tr>
													<td>User Name</td>
													<td><?php echo $u_name; ?>
													</td>
                                                </tr>
                                                <tr>
													<td>Vehicle Type</td>
													<td><?php echo $vehicle_type; ?>
													</td>
                                                </tr>
                                                <tr>
													<td>Vehicle No.</td>
													<td><?php echo $vehicle_number; ?>
													</td>
                                                </tr>
                                                <tr>
													<td>Starting Time</td>
													<td><?php echo $d_start; ?>
													</td>
                                                </tr>
                                                <tr>
													<td>Ending Time</td>
													<td><?php echo $d_end; ?>
													</td>
                                                </tr>
                                                <tr>
													<td>Other Info</td>
													<td>
														<?php 
															foreach ($l_data as $key => $value) {
																echo ucwords(str_replace('_',' ', $key.' :- '.$value."</br>"));
																
															 }
														?>
													</td>
                                                </tr>
                                                <tr>
													<td>Date</td>
													<td><?php echo $row['d_time']; ?>
													</td>
                                                </tr>
                                                <tr>
													<td>Status</td>
													<td><?php echo $row['e_status']; ?>
													</td>
                                                </tr>
                                            </tbody>
                                		</table>
                                		<a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
                                	</div>
                                </div>
							</form>
							<?php 
                        }else{
							if( $gnrl->checkAction($script) == '1' ){
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
	                                   LOWER(d.v_name) like LOWER('%".$keyword."%')  OR
	                                   LOWER(u.v_name) like LOWER('%".$keyword."%')  OR
	                                   LOWER(v.v_type) like LOWER('%".$keyword."%')  OR
	                                   LOWER(v.v_vehicle_number) like LOWER('%".$keyword."%')  OR
	                                   LOWER(tbl_ride.e_status) like LOWER('%".$keyword."%') 
	                                     
	                                )";
	                            }
	                            if( isset( $_REQUEST['status_sel'] ) && $_REQUEST['status_sel'] != '' ){
	                                $keyword =  trim( $_REQUEST['status_sel'] );
									$wh = " AND ( 
	                                   LOWER(tbl_ride.e_status) like LOWER('%".$keyword."%') 
	                                     
	                                )";
	                            }
	                            if( isset( $_REQUEST['driver'] ) && $_REQUEST['driver'] != '' ){
	                                $keyword =  trim( $_REQUEST['driver'] );
									$wh = " AND tbl_ride.i_driver_id = '".$keyword."'";
	                            }
	                            $ssql = "SELECT 
	                            			".$table.".*,
	                                        d.v_name AS d_name,
	                                        u.v_name AS u_name,
	                                        v.v_type AS vehicle_type,
	                                        v.v_vehicle_number AS vehicle_number
	                                        FROM ".$table." 
	                                        LEFT JOIN tbl_user as d ON ".$table.".i_driver_id = d.id
	                                        LEFT JOIN tbl_user as u ON ".$table.".i_user_id = u.id
	                                        LEFT JOIN tbl_vehicle as v ON ".$table.".i_vehicle_id = v.id
	                                         WHERE true ".$wh;
	                           
	                           // $ssql = "SELECT * FROM ".$table." WHERE true ".$wh;
	                                        
	                            $sortby = ( isset( $_REQUEST['sb'] ) && $_REQUEST['sb'] != '') ? $_REQUEST['sb'] : 'id';
	                            $sorttype = ( isset( $_REQUEST['st'] ) && $_REQUEST['st']=='0') ? 'ASC' : 'DESC';
	                            
	                            $nototal = $dclass->numRows($ssql);
	                            $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
	                            $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
	                            $restepm = $dclass->query($sqltepm);
	                            $row_Data = $dclass->fetchResults($restepm);
	                            

	                            #USE FOR DRIVER DROPDOWN MENU
	                            $ssql2 = "SELECT id,v_name FROM tbl_user WHERE true AND v_role= 'driver' ORDER BY v_name ASC";
	                            $restepm2 = $dclass->query($ssql2);
	                            $driver_Data = $dclass->fetchResults($restepm2);
	                            foreach ($driver_Data as $d_key => $d_value) {
	                            	$driver_name_arr[$d_value['id']]= $d_value['v_name'];
	                            }
	                           
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
	                                                </div>
	                                                <div class="pull-left">
	                                                    <div id="" class="dataTables_length">
	                                                        <label><?php $pagen->writeLimitBox(); ?></label>
	                                                    </div>
	                                                </div>
	                                                <label style="margin-left:15px">Driver wise : 
	                                                	 <div class="clearfix"></div>
	                                                	<div class="pull-left" style="">
	                                                    <div>
		                                                 <select class="select2" name="driver_sel" id="driver_sel" onChange="searchDriverName(this.options[this.selectedIndex].value)">
		                                                 		<option value="">--Select--</option>
		                                                   		 <?php echo $gnrl->get_keyval_drop($driver_name_arr,$_GET['driver']); ?>
		                                               		</select>
	                                                    </div>
	                                                </div>
	                                                </label>
	                                                
	                                                <label style="margin-left:15px">Status wise : 
	                                                	<div class="clearfix"></div>
	                                                	<div class="pull-left" style="">
	                                                    <div>
		                                                 <select class="select2" name="status_sel" id="status_sel" onChange="searchDriver(this.options[this.selectedIndex].value)">
		                                                 <option value="">--Select--</option>
		                                                   		 <?php $gnrl->getDropdownList($globalRideStatus,$_GET['status_sel']); ?>
		                                               		</select>
	                                                    </div>
	                                                </div>
	                                                </label>
	                                                
	                                                <div class="clearfix"></div>
	                                            </div>
	                                        </div>
	                                        
	                                        <!-- <?php chk_all('drop');?> -->
	                                        <table class="table table-bordered" id="datatable" style="width:100%;" >
	                                            <thead>
	                                                <tr>
														<th width="15%">Driver</th>
	                                                    <th width="5%">User</th>
	                                                    <th width="5%">Vehicle Type</th>
	                                                    <th width="5%">Vehicle No.</th>
	                                                    <th width="5%">Round Id</th>
	                                                    <th width="5%">Trip Date</th>
	                                                    <th width="5%">Status/Track</th>
	                                                    <th width="5%"><span class="pull-right">Action</span></th>
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
																<td><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>"><?php echo $row['d_name']; ?></a>
																</td>
																<td><?php echo $row['u_name']; ?>
																</td>
																<td><?php echo $row['vehicle_type']; ?>
																<td><?php echo $row['vehicle_number']; ?>
																</td>
																<td><?php echo $row['i_round_id']; ?>
																</td>
																<td><?php echo $gnrl->removeTimezone($row['d_time']) ; ?></td>
																<td>
																	<?php echo $row['e_status'];?>
																	<br>
																	<a href="<?php echo $page2?>.php?ride_id=<?php echo $row['id'];?>">Track</a></td>
	                                                            <td class="text-right" >
	                                                                <div class="btn-group">
	                                                                    <button class="btn btn-default btn-xs" type="button">Actions</button>
	                                                                    <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
	                                                                        <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
	                                                                    </button>
	                                                                    <ul role="menu" class="dropdown-menu pull-right">
	                                                                        <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">View</a></li>
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
							<?php }
                            else{ ?>
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
<div class="md-modal colored-header  md-effect-9" id="form-location" >
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
<script type="text/javascript">
	function searchDriver(val){
		window.document.location.href=window.location.pathname+'?status_sel='+val;
	}
	function searchDriverName(val){
		window.document.location.href=window.location.pathname+'?driver='+val;
	}
</script>
<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>

</body>
</html>
