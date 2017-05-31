<?php 
include('../admin/includes/configuration.php');

extract( $_REQUEST );

	if( $mode == 'load_ride_traker' ){
		
		$response = array(
			'gps_tracking' => array(),
			'last_track_id' => $last_track_id,
		);
		
		$rideTracking = $dclass->select( '*', 'tbl_track_vehicle_location', " AND id > '".$last_track_id."' AND l_data->>'run_type' = 'ride' AND l_data->>'i_ride_id' = '".$ride_id."' ORDER BY id" );
		foreach( $rideTracking as $rowData ){
			$response['last_track_id'] = $rowData['id'];
			$response['gps_tracking'][] = array(
				'title' => '',
				'description' => '',
				'type' => 'parking',
				'lat' => $rowData['l_latitude'],
				'lng' => $rowData['l_longitude'],
			);
		}
		
		echo json_encode( $response ); exit;
		
	}

?>