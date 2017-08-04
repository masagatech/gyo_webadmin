<?php 
include('includes/configuration.php');
$gnrl->check_login();

function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}

	extract( $_POST );
	$page_title = "Manage Rides";
	$page = "driver_trips";
	$page2 = "track";
	$table = 'tbl_ride';
	
	$title2 = 'Rides';
	$folder = 'vehicle_type';
	
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'force_close' || $_REQUEST['script'] == 'citywise' ) ) ? $_REQUEST['script'] : "";
	
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
		if(isset($_REQUEST['v_ride_code']) && !empty($_REQUEST['v_ride_code'])){
			$v_ride_code=$_REQUEST['v_ride_code'];
			$ssql3="SELECT * FROM ".$table." WHERE true AND v_ride_code = '".$v_ride_code."' ";
			$restepm3 = $dclass->query( $ssql3 );
			$row3 = $dclass->fetchResults( $restepm3 );
			$row3 = $row3[0];
			$_REQUEST['id'] =$row3['id'];
		}
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {

			$id = $_REQUEST['id'];
			
			$ssql = 
			"SELECT 
			
				a.*
				
				, a.l_data->>'vehicle_type' as vehicle_type
				, a.l_data->>'city' as auto_city_name
				, a.l_data->>'v_gender' as ride_gender
				
				, u.v_id AS user_vid
				, u.v_name AS user_name
				, u.v_email AS user_email
				, u.v_phone AS user_phone
				
				, d.v_id AS driver_vid
				, d.v_name AS driver_name
				, d.v_email AS driver_email
				, d.v_phone AS driver_phone
				, v.v_vehicle_number AS vehicle_number
				
				, c.v_name AS city_name
				
			FROM ".$table." a
			LEFT JOIN tbl_user as d ON a.i_driver_id = d.id
			LEFT JOIN tbl_user as u ON a.i_user_id = u.id
			LEFT JOIN tbl_vehicle as v ON a.i_vehicle_id = v.id
			LEFT JOIN tbl_city as c ON c.id = COALESCE( a.l_data->>'i_city_id', '0' )::bigint
			WHERE 
				true 
			AND a.id= '".$id."' ";
			
			$restepm = $dclass->query( $ssql );
			$row = $dclass->fetchResults( $restepm );
			$row = $row[0];
			extract( $row );
			$l_data = json_decode( $l_data, true );
			// if($_REQUEST['test'] == 'test')
			// {
			// 	_P($row);
			// 	_P($l_data);
			// 	exit;
			// }

		
		}
	}
	
	
	## Force Close Process
	if( isset( $_REQUEST['a'] ) && $_REQUEST['a'] == 4 ) {
		if( isset( $_REQUEST['id']) && $_REQUEST['id'] != "" ){
			$ride_id = $_REQUEST['id'];
			if( isset( $_REQUEST['force_close_btn'] ) && $_REQUEST['force_close_btn'] == 'Submit' ){
				
				$sql = "SELECT 
					a.*,
					b.v_token as v_token
					FROM 
						tbl_ride a
						LEFT JOIN tbl_user b ON a.i_driver_id = b.id 
						WHERE true
						AND v_role = 'driver' AND a.id =".$ride_id."  ";
						
				$restepm = $dclass->query($sql);
				$row_Data = $dclass->fetchResults($restepm);
				$row_Data = $row_Data[0];
				
				$url = API_URL.'rideComplete'; // rideComplete
	            $fields = array(
	            	'v_token' =>$row_Data['v_token'],
	            	'login_id' =>$row_Data['i_driver_id'],
	                'i_ride_id' => $ride_id,
	                'force_close' => 1,
	                'estimate_km' => $estimate_km,
	                'estimate_dry_run' => $estimate_dry_run,
	            );
				
				
				$result = $gnrl->_curl( $url, $fields, 'POST' );
				$result = json_decode( $result, true );
				///_p($result); exit;
				if( $result['status'] == 1 ){
					$url = API_URL.'rideConfirmPayment';
					$fields = array(
						'login_id' => $row_Data['i_driver_id'],
						'v_token' => $row_Data['v_token'],
						'i_ride_id' => $ride_id,
						'force_close' => 1,
					);
					$result = $gnrl->_curl( $url, $fields, 'POST' );
					$result = json_decode( $result, true );
					$gnrl->redirectTo($page.'.php?succ=1&msg=edit');
				}
				else{
					$gnrl->redirectTo($page.'.php?a=4&script=force_close&id='.$ride_id.'&succ=0&msg='.$result['message']);
				}
				
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
                            	<?php if( $script == 'force_close' ){ ?>
                            		Force Close Trip
                            	<?php } else if( $script ) { ?>
									View Ride # <?php echo $v_ride_code;?>
									<a target="_blank" href="<?php echo str_replace( '_track_code_', $v_ride_code, RIDE_TRACK_URL );?>">(Track)</a></td>
									
									<a href="<?php echo $page?>.php"><button class="btn fright btn-primary" type="button" name="submit_btn">BACK</button></a> 
									
								<?php } else { ?>
									<?php echo $page_title; ?>
									
									
								<?php } ?>
                              
                                <?php if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '' || isset($_REQUEST['srch_driver']) && $_REQUEST['srch_driver'] != '' || isset($_REQUEST['srch_filter_status']) && $_REQUEST['srch_filter_status'] != ''
                                                           || isset($_REQUEST['srch_filter_city']) && $_REQUEST['srch_filter_city'] != '' || isset($_REQUEST['srch_filter_type']) && $_REQUEST['srch_filter_type'] != '' || isset($_REQUEST['d_start_date']) && $_REQUEST['d_start_date'] != ''  ){ ?>
                                        <a href="<?php echo $page ?>.php" class="fright" >
                                            <button class="btn btn-primary" type="button">Clear Search</button>
                                        </a>
                                <?php } ?>
                            </h3>
                        </div>
                       
					   	
					   
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && 1 ){
							
							if( $_REQUEST['D'] ){ unset( $row['l_data'] ); _p( $row ); _p( $l_data ); }
							
							?>
						
							
							<style>
								.viewtable th{ background:#EEE; }
							</style>
						
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                               
							    <!-- Ride Information-->
								<div class="row">
                                    <div class="col-md-12">
										<table class="table table-bordered viewtable" style="width:100%;" >
											<tr><th class="text-center" colspan="2" ><h4><strong>Ride Information</strong></h4></th></tr>
											<tr><td width="20%" >Ride ID</td><td width="80%" ><?php echo $v_ride_code;?></td></tr>
											<tr><td>Status</td><td><?php echo ucfirst( $e_status );?></td></tr>
											<tr><td>Date</td><td><?php echo $gnrl->displaySiteDate( $d_time );?></td></tr>
											<tr><td>Start Time</td><td><?php echo $gnrl->displaySiteDate( $d_start );?></td></tr>
											<tr><td>End Time</td><td><?php echo $gnrl->displaySiteDate( $d_end );?></td></tr>
											<!--
											<tr><td>Ride Time</td><td><?php echo $gnrl->displaySiteDate( $l_data['ride_time'] );?></td></tr>
											<tr><td>Time Added</td><td><?php echo $gnrl->displaySiteDate( $l_data['time_added'] );?></td></tr>
											-->
											<tr><td>Paid</td><td><?php echo $i_paid ? 'Yes' : 'No';?></td></tr>
											<tr><td>Pin</td><td><?php echo substr( $v_pin, 0, 4 )." - ".substr( $v_pin, 4, 4 );?></td></tr>

											<tr><td>Track Code</td><td><?php echo $v_ride_code;?></td></tr>
											<tr><td>Track Link</td><td><a target="_blank" href="<?php echo str_replace( '_track_code_', $v_ride_code, RIDE_TRACK_URL );?>">(Track)</a></td></td></tr>
											<tr><td>City</td><td><?php echo ucfirst( $l_data['city'] );?></td></tr>
											
											<tr><td>Pickup Latitude / Longitude</td><td><?php echo $l_data['pickup_latitude'];?> / <?php echo $l_data['pickup_longitude'];?></td></tr>
											<tr><td>Pickup Address</td><td><?php echo ucfirst( $l_data['pickup_address'] );?></td></tr>
											
											<tr><td>Destination Latitude / Longitude</td><td><?php echo $l_data['destination_latitude'];?> / <?php echo $l_data['destination_longitude'];?></td></tr>
											<tr><td>Destination Address</td><td><?php echo ucfirst( $l_data['destination_address'] );?></td></tr>
											
											<tr><td>Estimate Time</td><td><?php echo $l_data['estimate_time']; ?></td></tr>
											<tr><td>Estimate KM [In Min]</td><td><?php echo $l_data['estimate_km']; ?></td></tr>
											
											<tr><td>Actual Distance</td><td><?php echo ucfirst( $l_data['actual_distance'] );?></td></tr>
											<tr><td>Actual Time [In Min]</td><td><?php echo $l_data['trip_time_in_min']; ?></td></tr>
											
											<tr><td>Vehicle Type</td><td><?php echo ucwords( str_replace( '_', ' ', $vehicle_type ) );?></td></tr>
											
                                		</table>
									</div>
								</div>
								
								<!-- User Information-->
								<div class="row">
                                    <div class="col-md-12">
										<table class="table table-bordered viewtable" style="width:100%;" >
											<tr><th class="text-center" colspan="2" ><h4><strong>User Information</strong></h4></th></tr>
											<tr>
												<td width="20%" >ID</td>
												<td width="80%" ><?php echo $user_vid;?></td>
											</tr>
											<tr><td>Name</td><td><?php echo $user_name;?></td></tr>
											<tr><td>Phone</td><td><?php echo $user_phone;?></td></tr>
											<tr><td>Email</td><td><?php echo $user_email;?></td></tr>
											<tr><td>Gender</td><td><?php echo ucfirst( $l_data['v_gender'] );?></td></tr>
										</table>
									</div>
								</div>
								
								<!-- Driver & Vehicle Information-->
								<div class="row">
                                    <div class="col-md-12">
										<table class="table table-bordered viewtable" style="width:100%;" >
											<tr><th class="text-center" colspan="2" ><h4><strong>Driver & Vehicle Information</strong></h4></th></tr>
											<tr>
												<td width="20%" >ID</td>
												<td width="80%" ><?php echo $driver_vid;?></td>
											</tr>
											<tr><td>Name</td><td><?php echo $driver_name;?></td></tr>
											<tr><td>Email</td><td><?php echo $driver_email;?></td></tr>
											<tr><td>Phone</td><td><?php echo $driver_phone;?></td></tr>
											
											<tr><td>Vehicle Type</td><td><?php echo $vehicle_type;?></td></tr>
											<tr><td>Vehicle Number</td><td><?php echo $vehicle_number;?></td></tr>
											
										</table>
									</div>
								</div>
								
								<!-- Charges Information-->
								<div class="row">
                                    <div class="col-md-12">
										<table class="table table-bordered viewtable" style="width:100%;" >
											<tr><th class="text-center" colspan="3" ><h4><strong>Charges Information</strong></h4></th></tr>
											<tr>
												<td width="20%" >Base Fare</td>
												<td width="20%" ><?php echo _price( $l_data['display_base_fare'] );?></td>
												<td width="60%" ></td>
											</tr>
											<tr>
												<td>Min Charge</td>
												<td><?php echo _price( $l_data['display_min_charge'] );?></td>
												<td></td>
											</tr>
											<tr>
												<td>Ride Time Charge</td>
												<td><?php echo _price( $l_data['display_ride_time_charge'] );?></td>
												<td><?php echo $l_data['trip_time_in_min'];?> Mins</td>
											</tr>
											<tr>
												<td>Total Fare</td>
												<td><?php echo _price( $l_data['display_total_fare'] );?></td>
												<td>
													<table class="table table-bordered viewtable" style="width:100%;" >
														<tr>
															<td width="50%" >Actual Distance</td>
															<td width="50%" ><?php echo $l_data['actual_distance'];?> KMs</td>
														</tr>
														<tr>
															<td>Upto Km</td>
															<td><?php echo $l_data['charges']['upto_km'];?></td>
														</tr>
														<tr>
															<td>Upto Km Charge</td>
															<td><?php echo $l_data['charges']['upto_km_charge'];?></td>
														</tr>
														<tr>
															<td>After Km Charge</td>
															<td><?php echo $l_data['charges']['after_km_charge'];?></td>
														</tr>
													</table>
												</td>
												
											</tr>
											<tr>
												<td>Service Tax</td>
												<td><?php echo _price( $l_data['display_service_tax'] );?></td>
												<td><?php echo $l_data['charges']['service_tax'];?>%</td>
											</tr>
											<tr>
												<td>Surcharge</td>
												<td><?php echo _price( $l_data['display_surcharge'] );?></td>
												<td><?php echo $l_data['charges']['surcharge'];?>%</td>
											</tr>
											<tr>
												<td>Other Charges</td>
												<td><?php echo _price( $l_data['display_other_charges'] );?></td>
												<td>
													<?php
													
													$otherCharges = $dclass->select( '*', 'tbl_ride_charges', " AND v_charge_type IN ( 'other_charge', 'parking_charge', 'toll_charge' ) AND i_ride_id = '".$id."' ORDER BY id ASC" );
													if( count( $otherCharges ) ){
														?>
														<table class="table table-bordered viewtable" style="width:100%;" >
															<?php
															foreach( $otherCharges as $rowCharge ){
																$rowCharge['l_data'] = json_decode( $rowCharge['l_data'], true );
																?>
																<tr>
																	<td width="50%" ><?php echo ucwords( str_replace( '_', ' ', $rowCharge['v_charge_type'] ) );?></td>
																	<td width="50%" >
																		<?php echo _price( $rowCharge['f_amount'] );?>
																		<?php echo $rowCharge['l_data']['v_charge_info'] ? ' ('.$rowCharge['l_data']['v_charge_info'].')' : '';?>
																	</td>
																</tr>
																<?php
															} ?>
														</table> <?php
													} ?>
												</td>
											</tr>
											<tr>
												<td>Discount</td>
												<td><?php echo _price( $l_data['display_discount'] );?></td>
												<td>
													<table class="table table-bordered viewtable" style="width:100%;" >
														<tr>
															<td width="50%" >Promocode Code</td>
															<td width="50%" ><?php echo $l_data['charges']['promocode_code'];?></td>
														</tr>
														<tr>
															<td>Promocode Code Discount</td>
															<td><?php echo $l_data['charges']['promocode_code_discount'];?></td>
														</tr>
														<tr>
															<td>Promocode Code Discount Upto</td>
															<td><?php echo $l_data['charges']['promocode_code_discount_upto'];?></td>
														</tr>
														<tr>
															<td>Promocode Code Discount</td>
															<td><?php echo $l_data['charges']['promocode_code_discount'];?></td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td><strong>Final Total</strong></td>
												<td><?php echo _price( $l_data['final_amount'] );?></td>
												<td></td>
											</tr>
										</table>
									</div>
								</div>
								
								<!-- Payment Information-->
								<div class="row">
                                    <div class="col-md-12">
										<table class="table table-bordered viewtable" style="width:100%;" >
											<tr><th class="text-center" colspan="3" ><h4><strong>Payment Information</strong></h4></th></tr>
											<tr>
												<td width="20%" >Payment Mode</td>
												<td width="80%" ><?php echo "Cash/Wallet";?></td>
											</tr>
											<tr>
												<td>Paid through Wallet</td><td><?php echo _price( $l_data['ride_paid_by_wallet'] );?></td>
											</tr>
											<tr>
												<td>Paid through Cash </td><td><?php echo _price( $l_data['ride_paid_by_cash'] );?></td>
											</tr>
											
											<tr>
												<td>Final Amount</td>
												<td><?php echo _price( $l_data['final_amount'] );?></td>
											</tr>
										</table>
									</div>
								</div>
								
								<!-- Driver Receivable-->
								<div class="row">
                                    <div class="col-md-12">
										<table class="table table-bordered viewtable" style="width:100%;" >
											<tr><th class="text-center" colspan="3" ><h4><strong>Driver Receivable</strong></h4></th></tr>
											<tr>
												<td width="20%" ><strong>Final Total</strong></td>
												<td width="20%" ><?php echo _price( $l_data['final_amount'] );?></td>
												<td width="60%" ></td>
											</tr>
											<tr>
												<td>Company Commision</td>
												<td><?php echo _price( $l_data['company_commision_amount'] );?></td>
												<td></td>
											</tr>
											<tr>
												<td>Driver Receivable</td>
												<td><?php echo _price( $l_data['ride_driver_receivable'] );?></td>
												<td></td>
											</tr>
											<tr>
												<td>Applicable Dry Run Amount</td>
												<td><?php echo _price( $l_data['apply_dry_run_amount'] );?></td>
												<td>
													<table class="table table-bordered viewtable" style="width:100%;" >
														<tr><td>Dry Run Amount</td><td><?php echo _price( $l_data['max_dry_run_charge'] );?> / KM</td></tr>
														<tr><td width="40%" >Max Dry Run</td><td width="60%" ><?php echo ( $l_data['charges']['max_dry_run_km'] );?> KMs</td></tr>
														
														<tr>
															<td width="40%" >Actual Dry Run</td>
															<td width="60%" ><?php echo ( $l_data['actual_dry_run'] );?> KMs</td>
														</tr>
														<tr><td>Applied Dry Run KMs</td><td><?php echo ( $l_data['apply_dry_run'] );?> KMs</td></tr>
														<tr><td>Applied Dry Run Amount</td><td><?php echo _price( $l_data['apply_dry_run_amount'] );?></td></tr>
													</table>
												</td>
											</tr>
										</table>
									</div>
								</div>
								
								<!-- Charges Applied -->
								<div class="row">
                                    <div class="col-md-12">
										<table class="table table-bordered viewtable" style="width:100%;" >
											<tr><th class="text-center" colspan="3" ><h4><strong>Charges Applied</strong></h4></th></tr>
											<tr>
												<td width="20%" >City Wise</td>
												<td width="80%" ><?php echo $l_data['charges']['city_wise_id'] ? 'Yes' : 'No';?></td>
											</tr>
											<tr>
												<td>Area Wise</td>
												<td><?php echo $l_data['charges']['area_wise_id'] ? 'Yes' : 'No';?></td>
											</tr>
											<tr>
												<td>Vehicle Wise</td>
												<td><?php echo $l_data['charges']['vehicle_wise_id'] ? 'Yes' : 'No';?></td>
											</tr>
										</table>
									</div>
								</div>
								
							</form>
							
							<?php 
                        }
						elseif ($script == 'force_close'){ ?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
                                            <div class="form-group">
                                                <label>Kilometer <?php echo $gnrl->getAstric(); ?></label>
                                                <input type="number" class="form-control" id="estimate_km" name="estimate_km" value="0" required />
                                            </div>
                                             <div class="form-group">
                                                <label>Driver Dry Run <?php echo $gnrl->getAstric(); ?></label>
                                                <input type="number" class="form-control" id="estimate_dry_run" name="estimate_dry_run" value="0" required />
                                            </div>
											<div class="form-group"> 
												<label>Ride Close Reason </label>
												<textarea name="l_close_reason" class="form-control" style="min-height:200px" ></textarea>
											</div>
                                            <div class="form-group">
                                                <button class="btn btn-primary" type="submit" name="force_close_btn" value="Submit">Submit</button>
                                                <a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="Submit">Cancel</button></a> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
							</form>
							<?php 
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
							if( isset( $_REQUEST['keyword'] ) && $_REQUEST['keyword'] != '' ){
								$keyword =  trim( $_REQUEST['keyword'] );
								$wh .= " AND ( 
								   LOWER(d.v_name) like LOWER('%".$keyword."%')  OR
								   LOWER(u.v_name) like LOWER('%".$keyword."%')  OR
								   LOWER(v.v_type) like LOWER('%".$keyword."%')  OR
								   LOWER(v.v_vehicle_number) like LOWER('%".$keyword."%')  OR
								   LOWER(a.e_status) like LOWER('%".$keyword."%') OR
								   LOWER(a.v_ride_code) like LOWER('%".$keyword."%') 
									 
								)";
							}


							if( isset( $_REQUEST['d_start_date'] ) && $_REQUEST['d_start_date'] != ''){
								if(isset($_REQUEST['d_end_date']) && $_REQUEST['d_end_date']){
									$end= $_REQUEST['d_end_date'];
								}else{
									$end=date('Y-m-d');
								}
								$start =  trim( $_REQUEST['d_start_date'] );
								// $wh .= " AND  a.d_time BETWEEN  '".$start."' AND  '".$end."' ";
							}else{
								$start =  date('Y-m-d');
								$end=date('Y-m-d');
							}

							
							if( isset( $_REQUEST['srch_filter_status'] ) && $_REQUEST['srch_filter_status'] != '' ){
								$keyword =  trim( $_REQUEST['srch_filter_status'] );
								$wh .= " AND ( 
								   LOWER(a.e_status) like LOWER('%".$keyword."%') 
								)";
							}
							if( isset( $_REQUEST['srch_driver'] ) && $_REQUEST['srch_driver'] != '' ){
								$keyword =  trim( $_REQUEST['srch_driver'] );
								$wh .= " AND a.i_driver_id = '".$keyword."'";
							}
							if( isset( $_REQUEST['srch_filter_city'] ) && $_REQUEST['srch_filter_city'] != '' ){
								$keyword =  trim( $_REQUEST['srch_filter_city'] );
								$wh .= " AND ( a.l_data->>'i_city_id' = '".$keyword."' ) ";
							}
							if( isset( $_REQUEST['srch_filter_type'] ) && $_REQUEST['srch_filter_type'] != ''){
								$keyword =  trim( $_REQUEST['srch_filter_type'] );
								$wh .= " AND ( a.l_data->>'vehicle_type' = '".$keyword."' )";
							}
							if( isset( $_REQUEST['srch_gender'] ) && $_REQUEST['srch_gender'] != ''){
								$keyword =  trim( $_REQUEST['srch_gender'] );
								$wh .= " AND ( a.l_data->>'v_gender' = '".$keyword."' )";
							}

							if( isset( $_REQUEST['deleted'] ) ){
								$keyword =  trim( $_REQUEST['keyword'] );
								$wh .= " AND a.i_delete='1'";
								$checked="checked";
							}else{
								$wh .= " AND a.i_delete='0'";
							}
							
							if( $_REQUEST['col'] == 1 ){
								$wh .= " AND ( a.l_data->>'actual_distance' )::numeric > 0 AND a.l_data->>'actual_distance' IS NOT NULL ";
							}
							
							
							$ssql = "SELECT 
								a.*,
								a.l_data->>'vehicle_type' as vehicle_type,
								a.l_data->>'city' as auto_city_name,
								a.l_data->>'v_gender' as ride_gender,
								c.v_name AS city_name,
								
								d.v_name AS driver_name,
								u.v_name AS user_name,
								v.v_vehicle_number AS vehicle_number
							FROM ".$table." a
							
							LEFT JOIN tbl_user as d ON a.i_driver_id = d.id
							LEFT JOIN tbl_city as c ON c.id = COALESCE( a.l_data->>'i_city_id', '0' )::bigint
							LEFT JOIN tbl_user as u ON a.i_user_id = u.id
							LEFT JOIN tbl_vehicle as v ON a.i_vehicle_id = v.id
							WHERE true AND  a.d_time >=  '".$start." 00:00:00' AND a.d_time <= '".$end." 23:59:59'".$wh;
							
							$sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'a.d_time' );
							$sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'DESC' );
							
							$nototal = $dclass->numRows( $ssql );
							$pagen = new vmPageNav( $nototal, $limitstart, $limit, $form ,"black" );

							if($_REQUEST['D'] == '1'){
								echo $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;					                            	
							}

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

							## vehicle type dropdown array
							$vehicle_row = $dclass->select('*','tbl_vehicle_type', " ORDER BY v_name ");
							$vehicle_arr=array();
							foreach($vehicle_row as $key => $val){
								$vehicle_arr[$val['v_type']] =$val['v_name'];
							}
						   
							?>
							<div class="content">
								<form name="frm" action="" method="get" >
									<div class="table-responsive">
									
										<div class="row">
											<div class="col-sm-12">

												<div class="pull-right">
													<div class="dataTables_filter" id="datatable_filter">
														<label style="margin-top: 20px;">

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
											  
												<label style="margin-left:15px">
													Start Date
													<div class="clearfix"></div> 
													<div class="pull-left" style="">
														<div class="input-group date datetime" data-min-view="2" data-date-format="yyyy-mm-dd">
															<input class="form-control" type="date" id="d_start_date" name="d_start_date" value="<?php echo ($_REQUEST['d_start_date'])?$_REQUEST['d_start_date']:date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" readonly="" onChange="document.frm.submit();" placeholder="select">
															<span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
														  </div>
													</div>
												</label>
											   
												<label style="margin-left:15px">
													End Date
													<div class="clearfix"></div> 
													<div class="pull-left" style="">
														<div class="input-group date datetime" data-min-view="2" data-date-format="yyyy-mm-dd">
															<input class="form-control" type="date" id="d_end_date" name="d_end_date"  value="<?php echo ($_REQUEST['d_end_date'])?$_REQUEST['d_end_date']:date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" readonly="" onclick="datetimepicker()" onChange="document.frm.submit();" placeholder="select">
															<span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
														</div>
													</div>
												</label>
												<div class="clearfix"></div>
											</div>
											<div class="col-sm-12">
												<label>
													Vehicle Type
													<div class="clearfix"></div>
													<div class="pull-left" style="">
														 <select class="select2" name="srch_filter_type" id="srch_filter_type" onChange="document.frm.submit();">
															<option value="">--Select--</option>
															 <?php echo $gnrl->get_keyval_drop($vehicle_arr,$_GET['srch_filter_type']); ?>
														</select>
													</div>
												</label>
												<label style="margin-left:15px">
													City 
													<div class="clearfix"></div> 
													<div class="pull-left" style="">
														<select class="select2" name="srch_filter_city" id="srch_filter_city" onChange="document.frm.submit();">
															<option value="">--Select--</option>
															<?php echo $gnrl->getCityDropdownList($_GET['srch_filter_city']); ?>
														</select>
													</div>
												</label>
												<label style="margin-left:15px">
													Driver
													<div class="clearfix"></div>
													<div class="pull-left" style="">
														<select class="select2" name="srch_driver" id="srch_driver" onChange="document.frm.submit();">
															<option value="">--Select--</option>
															 <?php echo $gnrl->get_keyval_drop($driver_name_arr,$_GET['srch_driver']); ?>
														</select>
													</div>
												</label>
												<label style="margin-left:15px"> 
													Gender
													<div class="clearfix"></div>
													<div class="pull-left" style="">
														<select class="select2" name="srch_gender" id="srch_gender" onChange="document.frm.submit();">
															<option value="">--Select--</option>
															<?php echo $gnrl->get_keyval_drop(array('male'=>'Male','female'=>'Female'),$_GET['srch_gender']); ?>
														</select>
													</div>
												</label>
												<label style="margin-left:15px">
													Status
													<div class="clearfix"></div>
													<div class="pull-left" style="">
														<select class="select2" name="srch_filter_status" id="srch_filter_status" onChange="document.frm.submit();">
															<option value="">--Select--</option>
															<?php $gnrl->getDropdownList($globalRideStatus,$_GET['srch_filter_status']); ?>
														</select>
													</div>
												</label>
											  
												<label style="margin:15px 0px" class="pull-right">
													
													<div class="clearfix"></div>
													<input class="all_access" name="deleted" value=""  type="checkbox"  onclick="document.frm.submit();" <?php echo $checked; ?>>
														Show Deleted Data
														<div class="clearfix"></div>
														<div style="margin: 10px 10px 10px 65px;">
															<a href="top_drivers.php"> Top Drivers </a>
														</div>
												</label>
												
											
										</div>
										
										<table class="table table-bordered" id="datatable" style="width:100%;" >
											
											<?php 
											$columnArr = array(
												'a.v_ride_code' => array( 'order' => 1, 'title' => 'Ride Code' ),
												'c.v_name' => array( 'order' => 1, 'title' => 'City' ),
												'driver_name' => array( 'order' => 1, 'title' => 'Driver' ),
												'user_name' => array( 'order' => 1, 'title' => 'User / Gender' ),
												'v_type' => array( 'order' => 1, 'title' => 'Vehicle Type', 'title2' => ' / Vehicle No' ),
												'a.d_time' => array( 'order' => 1, 'title' => 'Trip Date', 'title2' => ' / Start Time / End Time' ),
												'e_status' => array( 'order' => 1, 'title' => 'Status', 'title2' => ' / Track' ),
												'action' => array( 'order' => 0, 'title' => 'Action' ),
											);
											if( $_REQUEST['col'] == 1 ){
												$columnArr['custom'] = array( 'order' => 0, 'title' => 'Custom' );
											}
											echo $gnrl->renderTableHeader($columnArr);
											?>
											<tbody>
												<?php 
												if( $nototal > 0 ){
													$i = 0;
													foreach( $row_Data as $row ){
														$row['l_data'] = json_decode( $row['l_data'], true );
														$i++;
														?>
														<tr>
															<td><?php echo $row['v_ride_code'];?></td>
															<td><?php echo $row['city_name'] ? $row['city_name'] : $row['auto_city_name'];?></td>
															<td><?php echo $row['driver_name'] ? $row['driver_name'] : '-';?></td>
															<td>
																<?php echo $row['user_name'] ? $row['user_name'] : '-';?>
																<br>(<?php echo ucfirst( $row['ride_gender'] ? $row['ride_gender'] : '-' );?>)
															</td>
															<td>
																<?php echo ucfirst( $row['vehicle_type'] ); ?> 
																(<?php echo $row['vehicle_number'] ? $row['vehicle_number'] : '-'; ?>)
															</td>
															<td>
																<?php echo $gnrl->displaySiteDate($row['d_time']);?>
																<br>Start Time <?php echo $gnrl->displaySiteDate($row['d_start']);?>
																<br>End Time <?php echo $gnrl->displaySiteDate($row['d_end']);?>
															</td>
															<td>
																<?php echo $globalRideStatus[ $row['e_status'] ];?>
																<br>
																<a target="_blank" href="<?php echo str_replace( '_track_code_', $row['v_ride_code'], RIDE_TRACK_URL );?>">Track</a>
															</td>
															<td class="text-right" >
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
																			<li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">View</a></li>
																		<?php 
																			if($row['e_status'] == 'start'){ ?>
																			<li><a href="<?php echo $page?>.php?a=4&script=force_close&id=<?php echo $row['id'];?>">Force Close</a></li>
																		<?php }
																		?>
																			
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
<?php include('_scripts.php');?>
<script>

// START DATE END DATE VALIDATION
function datetimepicker(){

    var startdate = $('#d_start_date').val();
    var enddate = $('#d_end_date').val();
    $("#d_start_date").datetimepicker('setEndDate', enddate);
    $("#d_end_date").datetimepicker('setStartDate', startdate);

}
</script>
<?php include('jsfunctions/jsfunctions.php');?>

</body>
</html>
