<?php 
include('../admin/includes/configuration.php');

$rideInfo = $dclass->select( '*', 'tbl_ride', " AND v_ride_code = '".$_GET['ride_code']."' " );
$rideTracking = $dclass->select( '*', 'tbl_track_vehicle_location', " AND l_data->>'run_type' = 'ride' AND l_data->>'i_ride_id' = '".$rideInfo[0]['id']."' ORDER BY id ASC" );

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
_p($gps_tracking); exit;
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
<meta charset="utf-8">
<title>Track Ride</title>
<style>
	#map{ height: 100%; }
	html, body{ height:100%; margin:0; padding: 0; }
</style>
<script type="text/javascript" src="<?php echo SITE_URL?>pages/js/jquery.js"></script>
</head>
<body>
	<input type="hidden" id="ride_id" name="ride_id" value="<?php echo $rideInfo[0]['id']?>" >
	<input type="hidden" id="last_track_id" name="last_track_id" value="<?php echo $rideTracking[0]['id']?>" >
	<div id="map"></div>
</body>

<script type="text/javascript">
        var markers = <?php echo json_encode( $gps_tracking );?>;
		
        function initialize() {
            var mapOptions = {
                center			: new google.maps.LatLng( markers[0].lat, markers[0].lng ),
                zoom			: 13,
                mapTypeControl	: true,
                mapTypeId		: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map( document.getElementById("map"), mapOptions );
			
			var iconBase = 'images/';
			var icons = {
				parking : {
					icon : iconBase + 'imgpsh_fullsize_2.png'
				},
				library : {
					icon : iconBase + 'police.png'
				},
				info 	: {
					icon : iconBase + 'imgpsh_fullsize.png'
				}
			};
			
			var lat_lng = new Array();
            var infoWindow = new google.maps.InfoWindow();
            var latlngbounds = new google.maps.LatLngBounds();
			
            for( i = 0; i < markers.length; i++ ){

            	var data = markers[i]
            	
                var myLatlng = new google.maps.LatLng( data.lat, data.lng );
                lat_lng.push( myLatlng );
                var marker = new google.maps.Marker({
                    position	: myLatlng,
                    icon		: icons[data.type].icon,
                    map			: map,
                    title		: data.title
                });
                latlngbounds.extend( marker.position );
				
                ( function( marker, data ){
					google.maps.event.addListener(marker, "click", function(e){
						infoWindow.setContent( data.description );
						infoWindow.open( map, marker );
                    });
                })( marker, data );
				
            }
            map.setCenter( latlngbounds.getCenter() );
            map.fitBounds( latlngbounds );
			
            //***********ROUTING****************//
			
            //Intialize the Path Array
            var path = new google.maps.MVCArray();

            //Intialize the Direction Service
            var service = new google.maps.DirectionsService();

            //Set the Path Stroke Color
            var poly = new google.maps.Polyline({ 
				map			: map, 
				strokeColor	: '#4986E7' 
			});

            //Loop and Draw Path Route between the Points on MAP
            for( var i = 0; i < lat_lng.length; i++ ){
                if( ( i + 1 ) < lat_lng.length ){
                    var src = lat_lng[i];
                    var des = lat_lng[i + 1];
                    path.push(src);
                    poly.setPath(path);
                    service.route({
                        origin: src,
                        destination: des,
                        travelMode: google.maps.DirectionsTravelMode.DRIVING
                    }, function (result, status) {
                        if (status == google.maps.DirectionsStatus.OK) {
                            for (var i = 0, len = result.routes[0].overview_path.length; i < len; i++) {
                                path.push(result.routes[0].overview_path[i]);
                            }
                        }
                    });
                }
            }
			
			
            load_counter = 0;
            setInterval(function(){
				return false;
            	if(	load_counter == 200 ){
            		location.reload(); 
            	}
            	load_counter = load_counter + 1 ;
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
							
							for( var k in res.gps_tracking ){
								
								var temp = res.gps_tracking[k];
								
								var newmarker = new google.maps.Marker({
									position	: new google.maps.LatLng( temp.lat, temp.lng ),
									icon		: icons['parking'].icon,
									map			: map,
									title		: ''
								});
								var newlatlngbounds = new google.maps.LatLngBounds();
								newlatlngbounds.extend( newmarker.position );
								
								( function( newmarker, data ){
									google.maps.event.addListener(newmarker, "click", function (e){
										infoWindow.setContent(res.new_discription);
										infoWindow.open(map, newmarker);
									});
								})( newmarker, data );
								map.setCenter(newlatlngbounds.getCenter());
								map.fitBounds(newlatlngbounds);
								markers.push( newmarker );
								path.push( 	new google.maps.LatLng( temp.lat, temp.lng ) );
								
							}
							
						}
						
					}
				});
            }, 5000 );
			
        }
		
    </script>
	
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDm_daXq4Punc23zDJzieK4hdWNZU61yJQ&callback=initialize"></script>
</html>