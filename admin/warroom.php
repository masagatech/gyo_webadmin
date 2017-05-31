<?php 
include('includes/configuration.php');
$gnrl->check_login();

$page_title = 'Track Driver';
$table = 'tbl_user';

	$wh = "";
	if( $_REQUEST['id'] ){
		$wh .= " AND id = '".$_REQUEST['id']."' ";
	}
	if( $_REQUEST['srch_filter_city'] ){
		$wh .= " AND i_city_id = '".$_REQUEST['srch_filter_city']."' ";
	}
	
	if( !$wh ){
		echo 'No Data Found'; exit;
	}

	$data = $dclass->select( '*', 'tbl_user', $wh." AND l_latitude IS NOT NULL AND l_longitude IS NOT NULL " );
	if( !count( $data ) ){
		echo 'No Data Found'; exit;
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo SITE_NAME." :: ".$page_title;?></title>
</head>
<body>
<div id="dvMap" ></div>
<style>
	#dvMap{ height: 100%; }
	html, body{ height: 100%; margin: 0; padding: 0; }
	#latlng{ width: 225px; }
</style>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $gnrl->getSettings('GOOGLE_TRACK_RIDE_API_KEY');?>&callback=initMap&sensor=false"></script> 
<script>
	function initMap(){
		var mapOptions = {
			center: new google.maps.LatLng( <?php echo $data[0]['l_latitude']?>, <?php echo $data[0]['l_longitude']?> ),
			zoom: 15,
		};
		var map = new google.maps.Map( document.getElementById('dvMap'),mapOptions );
		
		
		<?php
		foreach( $data as $row ){
			?>
			geocodeLatLng( map,  {
				lat : <?php echo $row['l_latitude']?>,
				lng : <?php echo $row['l_longitude']?>,
				name : '<?php echo $row['v_name'];?>',
				online : '<?php echo $row['v_token'] ? ' | Online' : ' | Offline'?>',
				onduty : '<?php echo $row['is_onduty'] ? ' | Available' : ' | Not Available'?>',
				onride : '<?php echo $row['is_onride'] ? ' | On Ride' : ''?>',
			});
			<?php
		}
		?>
		
		
	}
	function geocodeLatLng( map, obj ){
		
		var geocoder = new google.maps.Geocoder;
		var infowindow = new google.maps.InfoWindow;
		
		var latlng = { 
			lat	: obj.lat,
			lng	: obj.lng
		};
		geocoder.geocode({ 
			'location': latlng 
		}, function(results, status) {
			if( status === 'OK' ){
				if( results[1] ){
					map.setZoom( 15 );
					var marker = new google.maps.Marker({
						position: latlng,
						map: map
					});
					google.maps.event.addListener( marker,"click",function(){
						if(infowindow)infowindow.close();
						infowindow = new google.maps.InfoWindow({ 
							content : results[1].formatted_address+ '<br>( '+obj.name+obj.online+obj.onduty+obj.onride+ ' )'
						});
						infowindow.open( map, marker );
					});
				}
				else{
					window.alert('No results found');
				}
			}
			else {
				// window.alert('Geocoder failed due to: ' + status);
			}
		});
	}
</script>
</body>
</html>
