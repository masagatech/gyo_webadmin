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
		exit;
	}
	
	
	
?>