<?php 
include('includes/configuration.php');
$gnrl->check_login();

// _P($_REQUEST);
// exit;
	extract( $_POST );
	
	$page_title = "Manage Vehicle Fair (Area Wise)";
	$page = "fair_area_wise";
	$table = 'tbl_vehicle_fairs';
	$table2 = 'tbl_vehicle_type';
	$title2 = 'Vehicle Fair (Area Wise)';
	$folder = 'vehicle_type';
	
	
	$v_type = 'area_wise';
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'citywise' ) ) ? $_REQUEST['script'] : "";
	

		// _P($_REQUEST);
		// exit;
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
		//$row = $dclass->select('*',$table," AND i_vehicle_type_id = '".$i_vehicle_type_id."' AND i_city_id = '".$i_city_id."'");
        //$row = $row[0];
		$row = array();
        if(empty($row)){
        		# get vehicle type from tbl_vehicle_type
        		$vehicle_type_data = $dclass->select('*',$table2," AND id = '".$i_vehicle_type_id."'");
        		
        		$vehicle_type_data=$vehicle_type_data[0];
	        	$ins = array(
				'v_type'  => $v_type,
				'i_vehicle_type_id'  => $i_vehicle_type_id,
				'v_vehicle_type' =>$vehicle_type_data['v_type'],
				'i_city_id' => $i_city_id,
				'l_data' => json_encode($l_data),
	            'd_added' => date('Y-m-d H:i:s'),
	            'd_modified' => date('Y-m-d H:i:s'),
				'e_status' => $e_status,
			);

			$id = $dclass->insert( $table, $ins );
			$filesArray = array(
				'list_icon',
				
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
				//$if_exist = $dclass->select('*',$table," AND i_vehicle_type_id = '".$i_vehicle_type_id."' AND i_city_id = '".$i_city_id."' AND id !=".$id." ");
				$if_exist = array();
				if(empty($if_exist)){
					# get vehicle type from tbl_vehicle_type
        			$vehicle_type_data = $dclass->select('*',$table2," AND id = '".$i_vehicle_type_id."'");
	        		$vehicle_type_data=$vehicle_type_data[0];
					$ins = array(
					" v_type = '".$v_type."' ",
						" i_vehicle_type_id = '".$i_vehicle_type_id."' ",
						" v_vehicle_type = '".$vehicle_type_data['v_type']."' ",
						" i_city_id = '".$i_city_id."' ",
						" d_modified = '".date('Y-m-d H:i:s')."' ",
						" l_data = l_data || '".json_encode($l_data)."' ",
						" e_status = '".$e_status."' ",
					);
					
					
					$dclass->updateJsonb( $table, $ins, " id = '".$id."' ");
					 // $dclass->update( $table, $ins, " id = '".$id."' ");
					$ins = array();
					$filesArray = array(
						'list_icon',
						
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
                        if( ($script == 'add' || $script == 'edit')  && 1 ){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
                                        	<div class="row">
		                                        <div class="col-md-12">
		                                        	<div class="form-group">
		                                        	   <label>Select City <?php echo $gnrl->getAstric(); ?></label>
		                                               <select class="select2 required" name="i_city_id" id="i_city_id">
		                                               		<option value=""> --Select-- </option>
		                                                    <?php $gnrl->getCityDropdownList($i_city_id); ?>
		                                                </select> 
		                                            </div>
		                                        </div>
		                                        <div class="col-md-12">
		                                            <div class="form-group">
		                                                <label>Vehicle Type <?php echo $gnrl->getAstric(); ?></label>
		                                                <select class="select2 required" name="i_vehicle_type_id" id="i_vehicle_type_id" required="">
		                                                <option value=""> --Select-- </option>
		                                                 <?php $gnrl->getVehicleTypeDropdownList($i_vehicle_type_id); ?>
		                                                </select> 
		                                            </div>
		                                        </div>
                                        	</div>
                                        	
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<h3>Geo Fancing</h3>
														<div class="row">
															<div class="col-md-5">
																<?php $key = "area_name"; ?>
																<label>Area Name <?php echo $gnrl->getAstric(); ?></label>
																<input class="form-control" type="text"  name="l_data[geo][<?php echo $key;?>]" value="<?php echo $l_data['geo'][$key];?>" required="" />
															</div>
															<div class="col-md-2">
																<?php $key = "latitude"; ?>
																<label>Latitude <?php echo $gnrl->getAstric(); ?></label>
																<input class="form-control" type="text" name="l_data[geo][<?php echo $key;?>]" value="<?php echo $l_data['geo'][$key];?>" required="" />
															</div>
															<div class="col-md-2">
																<?php $key = "longitude"; ?>
																<label>Longitude <?php echo $gnrl->getAstric(); ?></label>
																<input class="form-control" type="text" name="l_data[geo][<?php echo $key;?>]" value="<?php echo $l_data['geo'][$key];?>" required="" />
															</div>
															<div class="col-md-3">
																<?php $key = "cover_area"; ?>
																<label>Cover Area (In Km) <?php echo $gnrl->getAstric(); ?></label>
																<input class="form-control" type="text" name="l_data[geo][<?php echo $key;?>]" value="<?php echo $l_data['geo'][$key];?>"  required="" />
															</div>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row" >
												<div class="col-md-6">
													<h3>Date Range</h3>
													<div class="row" style="margin-top:0;" >
														<div class="form-group col-md-6">
															<label>From Date <?php echo $gnrl->getAstric(); ?></label>
															<?php $key = "start_date"; ?> 
															<div class="input-group date datetime" data-min-view="2" data-date-format="yyyy-mm-dd" data-link-field="dtp_input1">
																<span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
																<input class="form-control" type="text" name="l_data[dates][<?php echo $key; ?>]" value="<?php echo $l_data['dates'][$key];?>" required readonly />
															</div>
														</div>
														<div class="form-group col-md-6">
															<label>End Date <?php echo $gnrl->getAstric(); ?></label>
															<?php $key="end_date"; ?> 
															<div class="input-group date datetime" data-min-view="2" data-date-format="yyyy-mm-dd" data-link-field="dtp_input1">
																<span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
																<input class="form-control" type="text" name="l_data[dates][<?php echo $key; ?>]" value="<?php echo $l_data['dates'][$key];?>" required readonly />
															</div>		
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<h3>Timing Hours</h3>
													<div class="row" style="margin-top:0;" >
														<div class="form-group col-md-6">
															<label>From Hours <?php echo $gnrl->getAstric(); ?></label>
															<?php $key = "start_hour"; ?> 
															<div class="input-group date datetime" data-start-view="1" data-date="" data-date-format="hh:ii" data-link-field="dtp_input1">
																<span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
																<input class="form-control" type="text"  id="l_data[hours][<?php echo $key;?>]" name="l_data[hours][<?php echo $key; ?>]" value="<?php echo $l_data['hours'][$key];?>" required="" readonly />
															</div>
														</div>
														<div class="form-group col-md-6">
															<label>End Hours <?php echo $gnrl->getAstric(); ?></label>
															<?php $key="end_hour"; ?> 
															<div class="input-group date datetime" data-start-view="1" data-date="" data-date-format="hh:ii" data-link-field="dtp_input1">
																<span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
																<input class="form-control" type="text"  id="l_data[hours][<?php echo $key;?>]" name="l_data[hours][<?php echo $key; ?>]" value="<?php echo $l_data['hours'][$key];?>" required="" readonly />
															</div>		
														</div>
													</div>
												</div>
								            </div>
											
											<div class="row" >
												<div class="col-md-12">
													<h3>Select Days <?php echo $gnrl->getAstric(); ?></h3>
													<div class="row" >
														<?php
														$dayArr = array(
															'Monday',
															'Tuesday',
															'Wednesday',
															'Thursday',
															'Friday',
															'Saturday',
															'Sunday',
														);
														foreach( $dayArr as $rowDay ){ ?>
															<div class="col-md-2" >
																<div class="radio"> 
																	<label class="">
																		<div class="icheckbox_square-blue checkbox" style="position: relative;" aria-checked="" aria-disabled="false">
																			<input name="l_data[days][]" class="icheck" value="<?php echo $rowDay?>" style="position: absolute; opacity: 0;" type="checkbox" <?php echo in_array( $rowDay, $l_data['days'] ) ? 'checked' : ''?> required="">
																			<ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255) none repeat scroll 0% 0%; border: 0px none; opacity: 0;"></ins>
																		</div> 
																		&nbsp; <?php echo $rowDay?>
																	</label>
																</div>
															</div> <?php
														} ?>
													</div>
												</div>
								            </div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<h3>Show Estimate Charge </h3>
														<?php $key = "i_show_estimate_charge"; ?>
														<select class="select2" name="l_data[charges][<?php echo $key; ?>]" id="i_show_estimate_charge">
															<?php echo $gnrl->get_keyval_drop($globalShowEstimateCharge,$l_data['charges'][$key]); ?>
														</select>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<h3>Ride Charges </h3>
													<div class="row" >
														<div class="col-md-12">
															<?php 
															foreach( $globalCharges as $chargeKey => $chargeVal ) {
																if( !in_array( $chargeKey, array( 'surcharge' ) ) ){
																	continue;
																}
																?>
																<div class="form-group">
																	<label><?php echo $chargeVal;?> <?php echo $gnrl->getAstric(); ?></label>
																	<input type="text" class="form-control" name="l_data[charges][<?php echo $chargeKey;?>]" value="<?php echo $l_data['charges'][$chargeKey];?>" required=""  />
																</div> <?php 
															}?>		
														</div>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<h3>Driver Charges For Tarrif Card </h3>
													<div class="row" >
														<div class="col-md-12">
															<?php 
															foreach( $globalCharges as $chargeKey => $chargeVal ) {
																if( !in_array( $chargeKey, array( 'surcharge' ) ) ){
																	continue;
																}
																?>
																<div class="form-group">
																	<label><?php echo $chargeVal;?> <?php echo $gnrl->getAstric(); ?></label>
																	<input type="text" class="form-control" name="l_data[driver_charges][<?php echo $chargeKey;?>]" value="<?php echo $l_data['driver_charges'][$chargeKey];?>" required=""  />
																</div> <?php 
															}?>		
														</div>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<h3>Other Settings </h3>
													<div class="row" >
														<div class="col-md-12">
															<?php 
															foreach( $globalVehicleOtherSettings as $rowK => $rowV ) { ?>
																<div class="form-group">
																	<label><?php echo $rowV;?> <?php echo $gnrl->getAstric(); ?></label>
																	<input type="text" class="form-control" name="l_data[other][<?php echo $rowK;?>]" value="<?php echo $l_data['other'][$rowK];?>" required=""  />
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
	                            
								$wh = " AND v_type = '".$v_type."' ";
	                            if( isset( $_REQUEST['keyword'] ) && $_REQUEST['keyword'] != '' ){
	                                $keyword =  trim( $_REQUEST['keyword'] );
									$wh = " AND ( 
	                                   LOWER(v_vehicle_type) like LOWER('%".$keyword."%')
	                                   OR LOWER(e.v_name) like LOWER('%".$keyword."%')
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
									e.v_name as city_name
								FROM 
									tbl_vehicle_fairs a
								LEFT JOIN 
									tbl_city as e ON a.i_city_id = e.id 
									
								WHERE true ".$wh;
	                                        
	                            $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'city_name' );
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
				                                        'city_name' => array( 'order' => 1, 'title' => 'City' ),
				                                        'l_data' => array( 'order' => 0, 'title' => 'Area Info' ),
				                                        'v_vehicle_type' => array( 'order' => 0, 'title' => 'Charge Condtion' ),
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
															// geo
	                                                    	?>
	                                                        <tr>
																<td>
																	<?php echo $row['city_name'];?>
																	<br> (<?php echo ucfirst( $row['v_vehicle_type'] );?>)
																</td>
																<td>
																	<?php 
																	$geoInfo = array();
																	if( $l_data['geo']['area_name'] ){ $geoInfo[] = $l_data['geo']['area_name']; }
																	if( $l_data['geo']['latitude'] ){ $geoInfo[] = 'Latitude : '.$l_data['geo']['latitude']; }
																	if( $l_data['geo']['longitude'] ){ $geoInfo[] = 'Longitude : '.$l_data['geo']['longitude']; }
																	if( $l_data['geo']['cover_area'] ){ $geoInfo[] = 'Area : '.$l_data['geo']['cover_area'].' Km'; }
																	echo implode( ',<br>', $geoInfo ) ? implode( ',<br>', $geoInfo ) : '-';
																	?>
																</td>
																<td>
																	<?php
																	$chargeInfo = array();
																	if( $l_data['dates']['start_date'] && $l_data['dates']['end_date'] ){
																		echo 'Date Range : ('.$l_data['dates']['start_date'].' TO '.$l_data['dates']['end_date'].')<Br>';
																	}
																	if( $l_data['days'] ){
																		echo 'Days : ('.implode( ', ', $l_data['days'] ).')<Br>';
																	}
																	if( $l_data['hours'] ){
																		echo 'Applicable Time : ('.$l_data['hours']['start_hour'].' - '.$l_data['hours']['end_hour'].')<Br>';
																	}
																	?>
																</td>
																<td><?php echo $row['e_status'];?></td>
																<td><?php echo $row['d_added'];?></td>
																<td class="text-right" style="width: 15%">
																	
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
	                                                                               <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
	                                                                        	   <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=active&amp;id=<?php echo $row['id'];?>">Active</a></li>
	                                                                               <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=inactive&amp;id=<?php echo $row['id'];?>">Inactive</a></li>
	                                                                               <li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
	                                                                            <?php }
	                                                                        ?>
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

</body>
</html>
