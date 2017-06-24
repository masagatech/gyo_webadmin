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
	
	if( $_REQUEST['show_log2'] == 1 ){
		_p( 'RIDE DATA' );
		$rideTracking = $dclass->select( '*', 'tbl_track_vehicle_location', " AND l_data->>'run_type' = 'ride' AND l_data->>'i_ride_id' = '".$_REQUEST['id']."' ORDER BY id ASC" );
		_p( $rideTracking );
		_p( '------------------------------------------' );
		
		_p( 'TRACK DATA' );
		$rideTracking = $dclass->select( '*', 'tbl_track_vehicle_location', " AND l_data->>'run_type' = 'track' AND l_data->>'i_ride_id' = '".$_REQUEST['id']."' ORDER BY id ASC" );
		_p( $rideTracking );
		_p( '------------------------------------------' );
		
		_p( 'DRY DATA' );
		$rideTracking = $dclass->select( '*', 'tbl_track_vehicle_location', " AND l_data->>'run_type' = 'dry_run' AND l_data->>'i_ride_id' = '".$_REQUEST['id']."' ORDER BY id ASC" );
		_p( $rideTracking );
		_p( '------------------------------------------' );
		exit;
	}
	
	if( $_REQUEST['show_log'] == 1 ){
		error_reporting( E_ALL );
		// AND l_data->>'run_type' = 'ride'
		$rideTracking = $dclass->select( '*', 'tbl_track_vehicle_location', " AND l_data->>'run_type' = 'ride' AND l_data->>'i_ride_id' = '".$_REQUEST['id']."' ORDER BY id ASC" );
		$totalLats = array();
		$totalLongs = array();
		$totalDistance = 0;
		$totalDistanceArr = array();
		
		
		$distArr = array();
		$distArrM = array();
		// waypoints
		
		$distArr = array();
		$distArrAlready = array();
		
		$cnt = 10;
		$address = array();
		foreach( $rideTracking as $k => $row ){
			
			$row['l_data'] = json_decode( $row['l_data'], true );
			$address[] = $row['l_latitude'].','.$row['l_longitude'];
			// $address[] = str_replace( ' ', '%2C', $row['l_data']['end_address'] );
			if( $row['l_data']['distance'] > 0.3 ){
				$row['l_data']['distance'] = 0.3;
			}
			$distArrAlready[] = $row['l_data']['distance'];
			$cnt--;
			
			if( $cnt == 0 || ($k+1) == count($rideTracking) ){
				
				_p($address);
				
				$url = 'http://maps.googleapis.com/maps/api/directions/json?sensor=false&origin='.$address[0];
				$url .= '&destination='.$address[(count($address[0])-1)];
				
				unset($address[0]);
				if( count( $address ) >= 9 ){
					unset($address[count($address)-1]);
				}
				
				
				$url .= '&waypoints='.implode('|',$address);
				$url .= '&alternative=true&mode=walking';
				$temppp = $gnrl->sendSMS( $url );
				$temppp = json_decode( $temppp, true );
				
				if( $temppp['status'] == 'OK' ){
					foreach( $temppp['routes'][0]['legs'] as $legs ){
						$xxxxx = explode( ' ', $legs['distance']['text'] );
						if( $xxxxx[1] == 'm' ){
							$xxxxx[0] = $xxxxx[0] / 1000;
						}
						if( $xxxxx[0] > 0.3 ){
							$xxxxx[0] = 0.3;
						}
						$distArr[] = $xxxxx[0];
					}
				}
				
				$address = array();
				$cnt = 10;
			}
			
		}
		
		_p(array_sum($distArr));
		_p($distArr);
		
		_p(array_sum($distArrAlready));
		_p($distArrAlready);
		
		
		
		_p($rideTracking);
		_p($temppp);
		
		
		echo ($url);
		_p($address);
		
		exit;
		
			
			foreach( $rideTracking as $k => $row ){
				$rideTracking[$k]['l_data'] = json_decode( $row['l_data'], true );
				
				$xx = "'".$row['l_latitude']."'";
				$yy = "'".$row['l_longitude']."'";
				if( 1 || !in_array($xx, $totalLats) || !in_array($yy, $totalLongs) ){
					$totalLats[] = $xx;
					$totalLongs[] = $yy;
					echo "'".$row['l_latitude'].",".$row['l_longitude']."',<br>";
					$row['l_data'] = json_decode( $row['l_data'], true );
					
					if(  $row['l_data']['distance'] > 0.25 ){
						// $row['l_data']['distance'] = 0.25;
					}
					
					if( 1 || $row['l_data']['distance'] <= 0.5 ) {
						$totalDistance += $row['l_data']['distance'];
						$totalDistanceArr[] = $row['l_data']['distance'];
					}
				}
			}
		
		_p( 'totalDistance : '.$totalDistance );
		_p( $totalDistanceArr );
		_p( $rideTracking ); exit;
	}
	
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
			else{
				
				$ssql = "SELECT 
						a.*,
						a.l_data->>'vehicle_type' as vehicle_type,
						a.l_data->>'city' as auto_city_name,
						a.l_data->>'v_gender' as ride_gender,
						c.v_name AS city_name,
						
						d.v_name AS d_name,
						u.v_name AS u_name,
						v.v_vehicle_number AS vehicle_number
					FROM ".$table." a
					
					LEFT JOIN tbl_user as d ON a.i_driver_id = d.id
					LEFT JOIN tbl_city as c ON c.id = COALESCE( a.l_data->>'i_city_id', '0' )::bigint
					LEFT JOIN tbl_user as u ON a.i_user_id = u.id
					LEFT JOIN tbl_vehicle as v ON a.i_vehicle_id = v.id
					WHERE true AND a.id= '".$id."' ";
				
				$restepm = $dclass->query($ssql);
                $row = $dclass->fetchResults($restepm);
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
                                View <?php echo $title2;?>
                              
                                <?php if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '' || isset($_REQUEST['srch_driver']) && $_REQUEST['srch_driver'] != '' || isset($_REQUEST['srch_filter_status']) && $_REQUEST['srch_filter_status'] != ''
                                                           || isset($_REQUEST['srch_filter_city']) && $_REQUEST['srch_filter_city'] != '' || isset($_REQUEST['srch_filter_type']) && $_REQUEST['srch_filter_type'] != '' || isset($_REQUEST['d_start_date']) && $_REQUEST['d_start_date'] != ''  ){ ?>
                                        <a href="<?php echo $page ?>.php" class="fright" >
                                            <button class="btn btn-primary" type="button">Clear Search</button>
                                        </a>
                                <?php } ?>
                            </h3>
                        </div>
                       
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && 1 ){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
		                                <table class="table table-bordered viewtable" id="datatable" style="width:100%;" >
                                            <thead>
                                                <tr>
													<th width="20%"><strong>Field</strong></th>
                                                    <th width="80%"><strong>Data</strong></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>Driver Name</td><td><?php echo $d_name;?></td></tr>
												<tr><td>User Name</td><td><?php echo $u_name;?></td></tr><tr><td>Vehicle Type</td><td><?php echo $vehicle_type;?></td></tr>
												<tr><td>Vehicle Number</td><td><?php echo $vehicle_number;?></td></tr>
												<tr><td>Status</td><td><?php echo $globalRideStatus[$row['e_status']];?></td></tr>
												<tr><td>Date</td><td><?php echo $gnrl->displaySiteDate($d_time);?></td></tr>
												<tr><td>Start Time</td><td><?php echo $gnrl->displaySiteDate($d_start);?></td></tr>
												<tr><td>End Time</td><td><?php echo $gnrl->displaySiteDate($d_end);?></td></tr>
												<tr><td>Paid</td><td><?php echo $i_paid ? 'Yes' : 'No';?></td></tr>
												<tr><td>Pin</td><td><?php echo $v_pin;?></td></tr>
												<tr><td>Track Code</td><td><?php echo $v_ride_code.'-'.$id;?></td></tr>
												<tr><td>Track Link</td><td><a target="_blank" href="<?php echo str_replace( '_track_code_', $v_ride_code.'-'.$id, RIDE_TRACK_URL );?>">Track</a></td></tr>
												
												<tr><td colspan="2" align="center" ><strong>Other Info</strong></td></tr>
												<?php 
												foreach( $l_data as $key => $value ){ 
													if( $key == 'charges' ){
														continue;
													} ?>
													<tr>
														<td><?php echo ucwords( str_replace( '_', ' ', $key ) );?></td>
														<td><?php if( is_array($value ) ) _p( $value) ; else echo $value;?></td>
													</tr> <?php 
												}
												?>
												<tr><td colspan="2" align="center" ><strong>Charges Data</strong></td></tr>
												<?php 
												foreach( $l_data['charges'] as $key => $value ){ 
													?>
													<tr>
														<td><?php echo ucwords( str_replace( '_', ' ', $key ) );?></td>
														<td><?php echo $value;?></td>
													</tr> <?php 
												} ?>
												
                                            </tbody>
                                		</table>
                                		<a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
                                	</div>
                                </div>
							</form>
							<?php 
                        }else{
							if( 1 ){
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
									
									d.v_name AS d_name,
									u.v_name AS u_name,
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
                                                    'd_name' => array( 'order' => 1, 'title' => 'Driver' ),
                                                    'u_name' => array( 'order' => 1, 'title' => 'User / Gender' ),
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
																<td><?php echo $row['d_name'] ? $row['d_name'] : '-';?></td>
																<td>
																	<?php echo $row['u_name'] ? $row['u_name'] : '-';?>
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
																	<a target="_blank" href="<?php echo str_replace( '_track_code_', $row['v_ride_code'].'-'.$row['id'], RIDE_TRACK_URL );?>">Track</a>
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
	                                                                        	<li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
                                                                            <?php }
                                                                        ?>

	                                                                        
	                                                                    </ul>
	                                                                </div>
	                                                            </td>
																<?php 
																if( $_REQUEST['col'] == 1 ){
																	$rideTracking = $dclass->select( '*', 'tbl_track_vehicle_location', " AND l_data->>'run_type' = 'ride' AND l_data->>'i_ride_id' = '".$row['id']."' ORDER BY id ASC" );
																	
																	?>
																	 <td class="text-left" width="70%" >
																	 
																	 	<?php
																		if( count( $rideTracking ) ){
																			$totalDistance = 0;
																			$totalDistanceArr = array();
																			$totalActDistanceArr = array();
																			
																			$totalFormulaDistanceArr = array();
																			foreach( $rideTracking as $kkk => $rowTrack ){
																				$rowTrack['l_data'] = json_decode( $rowTrack['l_data'], true );
																				
																				$totalActDistanceArr[] = $rowTrack['l_data']['distance'];
																				if( $rowTrack['l_data']['distance'] > 0.3 ){
																					$rowTrack['l_data']['distance'] = 0.3;
																				}
																				$totalDistance += $rowTrack['l_data']['distance'];
																				$totalDistanceArr[] = $rowTrack['l_data']['distance'];
																				
																				if( $kkk ){
																					$xxx = distance( $rideTracking[($kkk-1)]['l_latitude'], $rideTracking[($kkk-1)]['l_longitude'], $rowTrack['l_latitude'], $rowTrack['l_longitude'], "K");
																					$totalFormulaDistanceArr[] = round($xxx,2);
																				}
																				
																			}
																			?>
																		 
																			<table class="table table-bordered" style="width:100%;" >
																				<tr>
																					<td>Start Addr</td>
																					<td><?php echo $row['l_data']['pickup_address'];?></td>
																				</tr>
																				<tr>
																					<td>End Addr</td>
																					<td><?php echo $row['l_data']['destination_address'];?></td>
																				</tr>
																				<tr>
																					<td>Estimate Km</td>
																					<td><?php echo $row['l_data']['estimate_km'];?></td>
																				</tr>
																				<tr>
																					<td>Actual Distance</td>
																					<td><?php echo $row['l_data']['actual_distance'];?></td>
																				</tr>
																				<tr>
																					<td>Calculated Distance</td>
																					<td><?php echo $totalDistance;?></td>
																				</tr>
																				
																				<tr>
																					<td>Calculated Arr</td>
																					<td><?php _p($totalDistanceArr);?></td>
																				</tr>
																				<tr>
																					<td>Actual Arr</td>
																					<td><?php _p($totalActDistanceArr);?></td>
																				</tr>
																				
																				
																			</table>
																			<?php 
																		} ?>
																	</td>
																	<?php
																	
																}?>
																
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
							<?php }
                            else{ ?>
                                    
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
