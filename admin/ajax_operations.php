<?php 
include('includes/configuration.php');
$gnrl->check_login();
extract( $_REQUEST );

	if( isset( $_REQUEST["mode"] ) ){
		
		if( $mode == "multi_action" ){
			$ids = implode( ',', $name_chk_all );
			if( !$ids ) exit;
			
			
			$_SESSION['succ'] = 1;
			
			if( $action == 'active' ){
				$ins = array( 'e_status' => 'active' );
				$dclass->update( $table, $ins, " id IN (".$ids.") ");
				$_SESSION['msg'] = 'multiact';
			}
			else if( $action == 'inactive' ){
				$ins = array( 'e_status' => 'inactive' );
				$dclass->update( $table, $ins, " id IN (".$ids.") ");
				$_SESSION['msg'] = 'multiinact';
			}
			else if( $action == 'delete' ){
				$dclass->delete( $table, " id IN (".$ids.") ");
				if( $table == '' ){
					// $dclass->delete( '', " ");
				}
				$_SESSION['msg'] = 'del';
			}
		}
		
		
		if( $mode == "load_vehicles" ){
			
			$wh = " AND v_type = ( SELECT v_type FROM tbl_vehicle_type WHERE id = '".$i_vehicle_type_id."' ) ";
			if($i_vehicle_id){
				$wh .= " AND b.id = '".$i_vehicle_id."' ";
			}
			
			$sql = 
			" SELECT 
				a.v_name AS driver_name,
				b.v_name AS vehicle_name,
				b.v_vehicle_number,
				b.id AS vehicle_id
				FROM 
			tbl_user a
			LEFT JOIN tbl_vehicle b ON a.id = b.i_driver_id 
			WHERE true
			AND v_role = 'driver'
			".$wh."
			ORDER BY a.v_name, b.v_name ";
			
			$restepm = $dclass->query($sql);
			$row_Data = $dclass->fetchResults($restepm);
			
			
			if( !$i_vehicle_id ){
				echo '<option value="" >- Select -</option>';
			}
			foreach( $row_Data as $row ){
				echo '<option value="'.$row['vehicle_id'].'" >';
				echo $row['driver_name'].' / ';
				echo $row['vehicle_name'].' / ';
				echo $row['v_vehicle_number'];
				echo '</option>';
			}
		}

		if( $mode == "rideInfo" ){
			
			$sql = " SELECT  * FROM tbl_ride WHERE id=".$id." ";
			$restepm = $dclass->query($sql);
			$row_Data = $dclass->fetchResults($restepm);
			$result=array();
			if(count($row_Data) && !empty($row_Data)){
				$row=$row_Data[0];
				$l_data= json_decode($row['l_data'],true);
				// _P($row_Data);

				$result =array(
					'ride_start_date' => $row['d_start'],
					'ride_end_date' => $row['d_end'],
					'city' => $l_data['city'],
					'ride_type' => $l_data['ride_type'],
					'trip_time' => $l_data['trip_time'],
					'final_amount' => $l_data['final_amount'],
					'payment_mode' => $l_data['payment_mode'],
					'vehicle_type' => $l_data['vehicle_type'],
					'pickup_address' => $l_data['pickup_address'],
					'ride_paid_by_cash' => $l_data['ride_paid_by_cash'],
					'destination_address' => $l_data['destination_address'],
					'ride_paid_by_wallet' => $l_data['ride_paid_by_wallet'],
					'ride_driver_payable' => $l_data['ride_driver_payable'],
					'ride_driver_receivable' => $l_data['ride_driver_receivable'],
				);
				$str="";
				foreach ($result as $key => $value) {
					# code...
					$str.="<div class='col-sm-4'><div class='form-group'><label>".ucwords(str_replace('_', ' ',$key)). " :-<span class='text-success'> " .$value. "</span</label></div></div>";
				}
				
			}
			echo $str;
		}

		if( $mode == "track_vehicle" ){
			
			$sql = " SELECT  * FROM 
                        tbl_track_vehicle_location
                        WHERE TRUE AND l_data->>'i_ride_id' = '".$ride_id."' 
                         ORDER BY id ASC";
			$restepm = $dclass->query($sql);
			$row_Data = $dclass->fetchResults($restepm);
			
			$l_data=array();
			if(count($row_Data) && !empty($row_Data)){
				$data=array();
				foreach($row_Data as $r_key => $r_value ){
					$data = array(
						'record_id' => $r_value['id'],
						'i_vehicle_id' => $r_value['i_vehicle_id'],
						'l_latitude' => $r_value['l_latitude'],
						'l_longitude' => $r_value['l_longitude'],
						'd_time' => $r_value['l_data'],
						'i_driver_id' => $r_value['i_driver_id'],
					);
				}

				$l_data= json_encode($row_Data);
			}
			
			echo $l_data;
		}
		
		
		if( $mode == "send_mail" ){
			
			// $data = $gnrl->prepare_and_send_email();
			
			$email_to = "deven.crestinfotech@gmail.com";
			$email_from = "GoYo <noreply@goyo.in>";
			$reply_to = "GoYo <noreply@goyo.in>";
			$email_cc = "";
			$email_bcc = "";
			$email_data["subject"] = "Deven Testing";
			$email_data["email_body"] = "Deven Testing";
			
			$data = $gnrl->custom_email( $email_to, $email_from, $reply_to, $email_cc, $email_bcc, $email_data["subject"], $email_data["email_body"], $email_format = "" );
			_p( $data );
			
		}

		if( $mode == "load_driver_warroom" ){

			if( isset( $srch_filter_city ) && $srch_filter_city != '' ){
				$wh .= " AND i_city_id = '".$srch_filter_city."' ";
			}
			if( isset( $srch_filter_type ) && $srch_filter_type != ''){
				$wh .= " AND LOWER( v.v_type ) like LOWER('".trim( $srch_filter_type )."') ";
			}
			if( isset( $srch_on_off ) && $srch_on_off != '' ){
				if( $srch_on_off == 'online' ){
					$wh .= " AND ( u.v_token !='' OR u.v_token IS NOT NULL )";
				}
				if($srch_on_off == 'offline'){
					$wh .= " AND ( u.v_token ='' OR u.v_token IS NULL )";
				}
			}
			if( isset( $srch_on_duty ) && $srch_on_duty != '' ){
				$wh .= " AND ( u.is_onduty = ".$srch_on_duty." )";
			}
			
			if( isset( $srch_on_ride ) && $srch_on_ride != ''){
				$keyword =  trim( $srch_on_ride );
				$wh .= " AND ( u.is_onride = ".$keyword." )";
			}
			
			$data = array();
			
			
			
			if( !$srch_filter_city ){
			
				$msg = 'Please Select City.';
			
			}
			else{
				
				$ssql = " SELECT 
				
					u.v_name,
					u.v_token,
					u.v_id,
					u.is_onduty,
					u.is_onride,
					u.l_latitude,
					u.l_longitude,
					
					v.v_name AS vehicle_name,
					v.v_type AS vehicle_type,
					v.v_vehicle_number AS vehicle_number
					
					FROM tbl_user as u
						LEFT JOIN tbl_vehicle as v ON u.id = v.i_driver_id
					WHERE 
						true 
						AND u.l_latitude IS NOT NULL 
						AND u.l_longitude IS NOT NULL 
						AND u.v_role = 'driver' ".$wh;
						
				
				$restepm = $dclass->query( $ssql );
				$data = $dclass->fetchResults( $restepm );
				
				if( count( $data ) ){

					#SAVE QUERY IN SESSION FOR REPORT GENERATE
					// unset($_SESSION['report_query']);
					// unset($_SESSION['warroom']);
					// exit;
					$_SESSION['report_query']['warroom']= $ssql;
					$rowData = array();
					
					foreach( $data as $k => $row ){
						
						$row['icon'] = '';
						
						$row['str'] = array(
							( $k + 1 ),
							$row['v_id'],
							$row['v_name'],
							$row['vehicle_type'],
						);
						
						if( $row['is_onride'] ){
							$row['str'][] = 'On Ride';
							$row['icon'] = 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png';
						}
						else if( $row['v_token'] ){
							$row['str'][] = 'Online';
							$row['str'][] = $row['is_onduty'] ? 'Available' : 'Not Available';
							$row['icon'] = 'http://maps.google.com/mapfiles/ms/icons/green-dot.png';
						}
						else{
							$row['str'][] = 'Offline';
							$row['icon'] = 'http://maps.google.com/mapfiles/ms/icons/red-dot.png';
						}
						
						$row['str'] = implode( ' | ', $row['str'] );
						
						unset( $row['v_name'] );
						unset( $row['v_token'] );
						unset( $row['v_id'] );
						unset( $row['is_onduty'] );
						unset( $row['is_onride'] );
						unset( $row['vehicle_number'] );
						unset( $row['vehicle_type'] );
						unset( $row['vehicle_name'] );
						
						$data[$k] = $row;
					}
				}
				
			}
			
			if( !$msg ){
				$msg = count( $data ) ? '' : 'No records found.';
			}
			
			echo json_encode( array(
				'status' => count( $data ) ? 1 : 0,
				'count' => count( $data ),
				'data' => $data,
				'msg' => ( $msg ? ( '<h3 style="color:#F00; text-align:center;" >'.$msg.'</h3>' ) : '' ),
			) ); 
			exit;
			
		}
		
		exit;
	}
	
	
	
?>