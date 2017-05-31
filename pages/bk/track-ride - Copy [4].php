<?php 
include('../admin/includes/configuration.php');

$rideInfo = $dclass->select( '*', 'tbl_ride', " AND v_ride_code = '".$_GET['ride_code']."' " );
$rideTracking = $dclass->select( '*', 'tbl_track_vehicle_location', " AND l_data->>'run_type' = 'ride' AND l_data->>'i_ride_id' = '".$rideInfo[0]['id']."' ORDER BY id DESC" );

$gps_tracking = array();

foreach( $rideTracking as $rowData ){
	$gps_tracking[] = array(
		'title' => '',
		'description' => '',
		'type' => 'parking',
		'lat' => $rowData['l_latitude'],
		'lng' => $rowData['l_longitude'],
	);
}
//_p($gps_tracking); exit;
?>
<!DOCTYPE html>
<html>
    <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Directions service (complex)</title>
    <style>
/* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
	height: 100%;
}
/* Optional: Makes the sample page fill the window. */
      html, body {
	height: 100%;
	margin: 0;
	padding: 0;
}
#floating-panel {
	position: absolute;
	top: 10px;
	left: 25%;
	z-index: 5;
	background-color: #fff;
	padding: 5px;
	border: 1px solid #999;
	text-align: center;
	font-family: 'Roboto', 'sans-serif';
	line-height: 30px;
	padding-left: 10px;
}
#warnings-panel {
	width: 100%;
	height:10%;
	text-align: center;
}
</style>
    </head>
    <body>
<div id="floating-panel"> <b>Start: </b>
		<select id="start">
		<option value="penn station, new york, ny">Penn Station</option>
		<option value="grand central station, new york, ny">Grand Central Station</option>
		<option value="625 8th Avenue, New York, NY, 10018">Port Authority Bus Terminal</option>
		<option value="staten island ferry terminal, new york, ny">Staten Island Ferry Terminal</option>
		<option value="101 E 125th Street, New York, NY">Harlem - 125th St Station</option>
	</select>
		<b>End: </b>
		<select id="end">
		<option value="260 Broadway New York NY 10007">City Hall</option>
		<option value="W 49th St & 5th Ave, New York, NY 10020">Rockefeller Center</option>
		<option value="moma, New York, NY">MOMA</option>
		<option value="350 5th Ave, New York, NY, 10118">Empire State Building</option>
		<option value="253 West 125th Street, New York, NY">Apollo Theater</option>
		<option value="1 Wall St, New York, NY">Wall St</option>
	</select>
	</div>
<div id="map"></div>
&nbsp;
<div id="warnings-panel"></div>
<script>
	function initMap() {
		
		var markerArray = [{"title":"","description":"","type":"parking","lat":"23.04047535","lng":"72.51878641"},{"title":"","description":"","type":"parking","lat":"23.0403945","lng":"72.5188652"},{"title":"","description":"","type":"parking","lat":"23.04050958","lng":"72.51869155"},{"title":"","description":"","type":"parking","lat":"23.0400451","lng":"72.5189576"},{"title":"","description":"","type":"parking","lat":"23.04049622","lng":"72.51882669"},{"title":"","description":"","type":"parking","lat":"23.04054776","lng":"72.51892916"},{"title":"","description":"","type":"parking","lat":"23.04053645","lng":"72.51883195"},{"title":"","description":"","type":"parking","lat":"23.04050477","lng":"72.51873233"},{"title":"","description":"","type":"parking","lat":"23.04040481","lng":"72.51862297"},{"title":"","description":"","type":"parking","lat":"23.04022007","lng":"72.51851265"},{"title":"","description":"","type":"parking","lat":"23.03992764","lng":"72.51834711"},{"title":"","description":"","type":"parking","lat":"23.03955137","lng":"72.51810167"},{"title":"","description":"","type":"parking","lat":"23.03938598","lng":"72.51796962"},{"title":"","description":"","type":"parking","lat":"23.03956774","lng":"72.51806799"},{"title":"","description":"","type":"parking","lat":"23.03994473","lng":"72.51834306"},{"title":"","description":"","type":"parking","lat":"23.04026971","lng":"72.51859846"},{"title":"","description":"","type":"parking","lat":"23.04054502","lng":"72.51872941"},{"title":"","description":"","type":"parking","lat":"23.04071221","lng":"72.51876566"},{"title":"","description":"","type":"parking","lat":"23.04096584","lng":"72.5188758"},{"title":"","description":"","type":"parking","lat":"23.04137968","lng":"72.51912875"},{"title":"","description":"","type":"parking","lat":"23.039863","lng":"72.5185416"},{"title":"","description":"","type":"parking","lat":"23.04185753","lng":"72.51940063"},{"title":"","description":"","type":"parking","lat":"23.04233743","lng":"72.51977025"},{"title":"","description":"","type":"parking","lat":"23.04276994","lng":"72.51996943"},{"title":"","description":"","type":"parking","lat":"23.04308202","lng":"72.52014"},{"title":"","description":"","type":"parking","lat":"23.04336683","lng":"72.52034831"},{"title":"","description":"","type":"parking","lat":"23.04347508","lng":"72.52035892"},{"title":"","description":"","type":"parking","lat":"23.043251","lng":"72.5202517"},{"title":"","description":"","type":"parking","lat":"23.0419765","lng":"72.5193273"},{"title":"","description":"","type":"parking","lat":"23.04371271","lng":"72.52048552"},{"title":"","description":"","type":"parking","lat":"23.04405791","lng":"72.52076365"},{"title":"","description":"","type":"parking","lat":"23.04455697","lng":"72.52103888"},{"title":"","description":"","type":"parking","lat":"23.0400451","lng":"72.5189576"},{"title":"","description":"","type":"parking","lat":"23.04486886","lng":"72.52116163"},{"title":"","description":"","type":"parking","lat":"23.04516838","lng":"72.52133184"},{"title":"","description":"","type":"parking","lat":"23.04550921","lng":"72.52153418"},{"title":"","description":"","type":"parking","lat":"23.04589724","lng":"72.52169122"},{"title":"","description":"","type":"parking","lat":"23.04640716","lng":"72.5221446"},{"title":"","description":"","type":"parking","lat":"23.04699023","lng":"72.52235584"},{"title":"","description":"","type":"parking","lat":"23.0474728","lng":"72.52271663"},{"title":"","description":"","type":"parking","lat":"23.04793553","lng":"72.52296392"},{"title":"","description":"","type":"parking","lat":"23.04841989","lng":"72.52323333"},{"title":"","description":"","type":"parking","lat":"23.04885834","lng":"72.52351911"},{"title":"","description":"","type":"parking","lat":"23.048604","lng":"72.5230246"},{"title":"","description":"","type":"parking","lat":"23.04916699","lng":"72.5236963"},{"title":"","description":"","type":"parking","lat":"23.04936573","lng":"72.52380388"},{"title":"","description":"","type":"parking","lat":"23.04961424","lng":"72.52387666"},{"title":"","description":"","type":"parking","lat":"23.04999133","lng":"72.52417232"},{"title":"","description":"","type":"parking","lat":"23.05036691","lng":"72.52438283"},{"title":"","description":"","type":"parking","lat":"23.05077335","lng":"72.52463388"},{"title":"","description":"","type":"parking","lat":"23.05111071","lng":"72.52482493"},{"title":"","description":"","type":"parking","lat":"23.0490583","lng":"72.5238565"},{"title":"","description":"","type":"parking","lat":"23.05126085","lng":"72.52487916"},{"title":"","description":"","type":"parking","lat":"23.05135586","lng":"72.52497668"},{"title":"","description":"","type":"parking","lat":"23.05150577","lng":"72.52508252"},{"title":"","description":"","type":"parking","lat":"23.051333","lng":"72.5248733"},{"title":"","description":"","type":"parking","lat":"23.05173075","lng":"72.52518958"},{"title":"","description":"","type":"parking","lat":"23.05210084","lng":"72.52531035"},{"title":"","description":"","type":"parking","lat":"23.05245918","lng":"72.52553316"},{"title":"","description":"","type":"parking","lat":"23.05266621","lng":"72.52563571"},{"title":"","description":"","type":"parking","lat":"23.05276458","lng":"72.52567719"},{"title":"","description":"","type":"parking","lat":"23.05287547","lng":"72.52571178"},{"title":"","description":"","type":"parking","lat":"23.05302783","lng":"72.52591774"},{"title":"","description":"","type":"parking","lat":"23.05336309","lng":"72.52614665"},{"title":"","description":"","type":"parking","lat":"23.05379995","lng":"72.52617034"},{"title":"","description":"","type":"parking","lat":"23.0539974","lng":"72.52638357"},{"title":"","description":"","type":"parking","lat":"23.0524861","lng":"72.5255203"},{"title":"","description":"","type":"parking","lat":"23.05436094","lng":"72.52652776"},{"title":"","description":"","type":"parking","lat":"23.05481883","lng":"72.52690789"},{"title":"","description":"","type":"parking","lat":"23.05524493","lng":"72.5272208"},{"title":"","description":"","type":"parking","lat":"23.05569508","lng":"72.52746011"},{"title":"","description":"","type":"parking","lat":"23.0560668","lng":"72.5277386"},{"title":"","description":"","type":"parking","lat":"23.05608038","lng":"72.52750432"},{"title":"","description":"","type":"parking","lat":"23.05661102","lng":"72.52797021"},{"title":"","description":"","type":"parking","lat":"23.05704713","lng":"72.52818985"},{"title":"","description":"","type":"parking","lat":"23.0560353","lng":"72.5274613"},{"title":"","description":"","type":"parking","lat":"23.05745987","lng":"72.52853506"},{"title":"","description":"","type":"parking","lat":"23.05785763","lng":"72.52876127"},{"title":"","description":"","type":"parking","lat":"23.05815287","lng":"72.52886302"},{"title":"","description":"","type":"parking","lat":"23.05841667","lng":"72.529007"},{"title":"","description":"","type":"parking","lat":"23.0580235","lng":"72.5291251"},{"title":"","description":"","type":"parking","lat":"23.05865769","lng":"72.52908666"},{"title":"","description":"","type":"parking","lat":"23.05893419","lng":"72.52926744"},{"title":"","description":"","type":"parking","lat":"23.05936743","lng":"72.52954627"},{"title":"","description":"","type":"parking","lat":"23.05979026","lng":"72.52984789"},{"title":"","description":"","type":"parking","lat":"23.06033856","lng":"72.5300584"},{"title":"","description":"","type":"parking","lat":"23.06062403","lng":"72.53008848"},{"title":"","description":"","type":"parking","lat":"23.0628031","lng":"72.5310851"},{"title":"","description":"","type":"parking","lat":"23.06101442","lng":"72.53049157"},{"title":"","description":"","type":"parking","lat":"23.06126655","lng":"72.53056759"},{"title":"","description":"","type":"parking","lat":"23.06151013","lng":"72.53071486"},{"title":"","description":"","type":"parking","lat":"23.06191211","lng":"72.53086351"},{"title":"","description":"","type":"parking","lat":"23.060811","lng":"72.5306964"},{"title":"","description":"","type":"parking","lat":"23.06224942","lng":"72.53095626"},{"title":"","description":"","type":"parking","lat":"23.06258755","lng":"72.53109569"},{"title":"","description":"","type":"parking","lat":"23.06265901","lng":"72.53102303"},{"title":"","description":"","type":"parking","lat":"23.0626734","lng":"72.531251"},{"title":"","description":"","type":"parking","lat":"23.06275004","lng":"72.53106064"},{"title":"","description":"","type":"parking","lat":"23.06287317","lng":"72.53110636"},{"title":"","description":"","type":"parking","lat":"23.06296016","lng":"72.53113465"},{"title":"","description":"","type":"parking","lat":"23.0631045","lng":"72.531482"},{"title":"","description":"","type":"parking","lat":"23.0632425","lng":"72.5311123"},{"title":"","description":"","type":"parking","lat":"23.0630019","lng":"72.5309737"},{"title":"","description":"","type":"parking","lat":"23.0631023","lng":"72.5310661"},{"title":"","description":"","type":"parking","lat":"23.06308208","lng":"72.53109695"},{"title":"","description":"","type":"parking","lat":"23.06299976","lng":"72.53117288"},{"title":"","description":"","type":"parking","lat":"23.06300833","lng":"72.53127148"},{"title":"","description":"","type":"parking","lat":"23.0631443","lng":"72.5314358"},{"title":"","description":"","type":"parking","lat":"23.06307849","lng":"72.53137578"},{"title":"","description":"","type":"parking","lat":"23.06318353","lng":"72.53137023"},{"title":"","description":"","type":"parking","lat":"23.0619117","lng":"72.5308812"},{"title":"","description":"","type":"parking","lat":"23.06327113","lng":"72.53143584"},{"title":"","description":"","type":"parking","lat":"23.06335785","lng":"72.53140768"},{"title":"","description":"","type":"parking","lat":"23.0630836","lng":"72.5312972"},{"title":"","description":"","type":"parking","lat":"23.0619117","lng":"72.5308812"},{"title":"","description":"","type":"parking","lat":"23.06342136","lng":"72.53132793"},{"title":"","description":"","type":"parking","lat":"23.06357593","lng":"72.53120943"},{"title":"","description":"","type":"parking","lat":"23.0637199","lng":"72.53124192"},{"title":"","description":"","type":"parking","lat":"23.06381657","lng":"72.53128429"},{"title":"","description":"","type":"parking","lat":"23.06387003","lng":"72.53137775"},{"title":"","description":"","type":"parking","lat":"23.06398875","lng":"72.53129002"},{"title":"","description":"","type":"parking","lat":"23.06407782","lng":"72.5312585"},{"title":"","description":"","type":"parking","lat":"23.0638943","lng":"72.5314685"}];
		
		// Instantiate a directions service.
		var directionsService = new google.maps.DirectionsService;
		
		// Create a map and center it on Manhattan.
		var map = new google.maps.Map( document.getElementById('map'), {
			zoom 	: 13,
			center	: { lat: 23.04047535, lng: 72.51878641 }
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
	}

	function calculateAndDisplayRoute( directionsDisplay, directionsService, markerArray, stepDisplay, map ){
		
		// First, remove any existing markers from the map.
		for( var i = 0; i < markerArray.length; i++ ){
			markerArray[i].setMap( null );
		}
		console.log( 'markerArray', markerArray );
		
		// Retrieve the start and end locations and create a DirectionsRequest using WALKING directions.
		directionsService.route({
			origin: { lat: 23.04047535, lng: 72.51878641 },
			destination: { lat: 23.0638943, lng: 72.5314685 },
			travelMode: 'DRIVING'
		}, function( response, status ){
			// Route the directions and pass the response to a function to create markers for each step.
			if( status === 'OK' ){
				document.getElementById('warnings-panel').innerHTML = '<b>' + response.routes[0].warnings + '</b>';
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
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDl178nLe52M8Q8NhTu_rlqnHNHtGxp-l8&callback=initMap"></script>
</body>
</html>