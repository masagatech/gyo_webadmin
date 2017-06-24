<?php 
include('../admin/includes/configuration.php');

$rideInfo = $dclass->select( '*', 'tbl_ride', " AND id = '".$_GET['ride_id']."' AND v_ride_code = '".$_GET['ride_code']."' " );
if( !$_SESSION['adminid'] && !count( $rideInfo ) ){
	echo '<h2 style="color:#F00;">Invalid Ride Tracking</h2>'; exit;
}

$rideInfo = $rideInfo[0];

$datetime1 = strtotime( date('Y-m-d H:i:s', strtotime($rideInfo['d_end'] ) ) );
$datetime2 = strtotime( date('Y-m-d H:i:s' ) );
$interval  = abs($datetime2 - $datetime1);
$minutes   = round($interval / 60);
if( !$_SESSION['adminid'] && $rideInfo['e_status'] == 'complete' && $minutes > 60 ){
	echo '<h2 style="color:#F00;">Invalid Ride Tracking</h2>'; exit;
}

$l_data = json_decode( $rideInfo['l_data'], true );
// AND (l_data->>'distance')::numeric < 0.2
$rideTracking = $dclass->select( '*', 'tbl_track_vehicle_location', " AND l_data->>'run_type' = 'track' AND l_data->>'i_ride_id' = '".$rideInfo['id']."' ORDER BY id ASC" );

$totalLats = array();
$totalLongs = array();
$gps_tracking = array();
$last_track_id = 0;
foreach( $rideTracking as $rowData ){
	$last_track_id = $rowData['id'];
	$xx = "'".$rowData['l_longitude']."'";
	
	$xx = "'".$rowData['l_latitude']."'";
	$yy = "'".$rowData['l_longitude']."'";
	if( 1 || !in_array($xx, $totalLats) || !in_array($yy, $totalLongs) ){
		$totalLats[]=$xx;
		$totalLongs[]=$yy;
		$gps_tracking[] = array(
			'lat' => $rowData['l_latitude'],
			'lng' => $rowData['l_longitude'],
		);
	}
}

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
<meta charset="utf-8">
<title>Track Ride</title>
<style>
	#map{ height:100%; }
	html, body{ height:100%; margin:0; padding: 0; }
</style>
<script type="text/javascript" src="<?php echo SITE_URL?>pages/js/jquery.js"></script>
</head>
<body>
	<input type="hidden" id="ride_id" name="ride_id" value="<?php echo $rideInfo['id']?>" >
	<input type="hidden" id="last_track_id" name="last_track_id" value="<?php echo $last_track_id?>" >
	<div id="map"></div>
</body>
<script>
	function initMap() {
		
		
		var markerArray = [];
		
		// Instantiate a directions service.
		var directionsService = new google.maps.DirectionsService;
		
		// Create a map and center it on Manhattan.
		var map = new google.maps.Map( document.getElementById('map'), {
			zoom 	: 13,
			center	: { 
				lat	: parseFloat( <?php echo $l_data['pickup_latitude']?> ), 
				lng	: parseFloat( <?php echo $l_data['pickup_longitude']?> ), 
			}
		});
		
		// Create a renderer for directions and bind it to the map.
		var directionsDisplay = new google.maps.DirectionsRenderer({ 
			map	: map
		});
		
		// Instantiate an info window to hold step text.
		var stepDisplay = new google.maps.InfoWindow;
		
		// Display the route between the initial start and end selections.
		calculateAndDisplayRoute( directionsDisplay, directionsService, markerArray, stepDisplay, map );
		
		// Listen to change events from the start and end lists.
		/*
		var onChangeHandler = function(){
			calculateAndDisplayRoute( directionsDisplay, directionsService, markerArray, stepDisplay, map );
		};
		document.getElementById('start').addEventListener('change', onChangeHandler);
		document.getElementById('end').addEventListener('change', onChangeHandler);
		*/
		
		if( 1 ){
			
			var markers = [];
			<?php foreach( $gps_tracking as $row ){ ?>
				markers.push({
					lat : parseFloat( <?php echo $row['lat'];?> ),
					lng : parseFloat( <?php echo $row['lng'];?> ),
				});
			<?php } ?>
			
			/*
			var map = new google.maps.Map(document.getElementById('map'), {
				zoom 		: 15,
				center		: { lat: markers[0].lat, lng: markers[0].lng },
				mapTypeId	: google.maps.MapTypeId.ROADMAP
			});*/
			
			var flightPlanCoordinates = markers;
			var flightPath = new google.maps.Polyline({
				path			: flightPlanCoordinates,
				geodesic		: true,
				strokeColor		: '#FF0000',
				strokeWeight	: 5,
				strokeOpacity	: 0.6,
			});
			
			flightPath.setMap( map );
			
		}
		setInterval( function(){
			// return false;
			jQuery.ajax({
				type	: "POST",
				url		: "<?php echo SITE_URL?>pages/ajax.php?mode=load_ride_traker",
				data	: {
					ride_id : jQuery('#ride_id').val(),
					last_track_id : jQuery('#last_track_id').val(),
				},
				success	: function( res ){
					
					var res = JSON.parse( res );
					
					jQuery('#last_track_id').val( res.last_track_id );
					
					if( res.gps_tracking.length ){
						
						var newMarkers = [{
							lat : parseFloat( markers[(markers.length-1)].lat ),
							lng : parseFloat( markers[(markers.length-1)].lng ),
						}];
						
						for( var k in res.gps_tracking ){
							markers.push( {
								lat : parseFloat( res.gps_tracking[k].lat ),
								lng : parseFloat( res.gps_tracking[k].lng ),
							});
							newMarkers.push( {
								lat : parseFloat( res.gps_tracking[k].lat ),
								lng : parseFloat( res.gps_tracking[k].lng ),
							});
						}
						
						var flightPlanCoordinates = newMarkers;
						var flightPath = new google.maps.Polyline({
							path			: flightPlanCoordinates,
							geodesic		: true,
							strokeColor		: '#FF0000',
							strokeWeight	: 5,
							strokeOpacity	: 0.6,
						});
						flightPath.setMap(map);
						
					}
					
				}
			});
		}, 5000 );
	}
	
	

	function calculateAndDisplayRoute( directionsDisplay, directionsService, markerArray, stepDisplay, map ){
		// First, remove any existing markers from the map.
		for( var i = 0; i < markerArray.length; i++ ){
			markerArray[i].setMap(null);
		}
		
		// Retrieve the start and end locations and create a DirectionsRequest using WALKING directions.
		directionsService.route({
			origin: { 
				lat	: parseFloat( <?php echo $l_data['pickup_latitude']?> ), 
				lng	: parseFloat( <?php echo $l_data['pickup_longitude']?> ), 
			},
			destination: { 
				lat	: parseFloat( <?php echo $l_data['destination_latitude']?> ), 
				lng	: parseFloat( <?php echo $l_data['destination_longitude']?> ), 
			},
			travelMode: 'WALKING'
		}, function( response, status ){
			// Route the directions and pass the response to a function to create markers for each step.
			if( status === 'OK' ){
				//document.getElementById('warnings-panel').innerHTML = '<b>' + response.routes[0].warnings + '</b>';
				directionsDisplay.setDirections(response);
				// showSteps(response, markerArray, stepDisplay, map);
			}
			else{
				window.alert('Directions request failed due to ' + status);
			}
		});
	}

	function showSteps( directionResult, markerArray, stepDisplay, map ){
		// For each step, place a marker, and add the text to the marker's infowindow.
		// Also attach the marker to an array so we can keep track of it and remove it
		// when calculating new routes.
		var myRoute = directionResult.routes[0].legs[0];
		for( var i = 0; i < myRoute.steps.length; i++ ){
			var marker = markerArray[i] = markerArray[i] || new google.maps.Marker;
			marker.setMap(map);
			marker.setPosition(myRoute.steps[i].start_location);
			attachInstructionText( stepDisplay, marker, myRoute.steps[i].instructions, map );
		}
	}

	function attachInstructionText( stepDisplay, marker, text, map ){
		google.maps.event.addListener(marker, 'click', function() {
			// Open an info window when the marker is clicked on, containing the text of the step.
			stepDisplay.setContent( text );
			stepDisplay.open( map, marker );
		});
	}
	
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $gnrl->getSettings('GOOGLE_TRACK_RIDE_API_KEY');?>&callback=initMap"></script>
</body>
</html>