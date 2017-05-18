<?php 
include('includes/configuration.php');
$gnrl->check_login();

	extract( $_GET );
	$page_title = "Manage Tracking";
	$page = "track";
	$table = 'tbl_temp_track';
    // $table2 = 'tbl_vehicle';
	$title2 = 'Tracking';
	$v_role ='driver';
    $folder = 'vehicle_type';
    $sql = " SELECT  * FROM 
                        tbl_track_vehicle_location
                        WHERE TRUE AND l_data->>'i_ride_id' = '".$ride_id."'  ORDER BY id ASC";
                         

    $restepm = $dclass->query($sql);
    $row = $dclass->fetchResults($restepm);
    // _P($row);
    // exit;

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
                                Live Tracking
                            </h3>
                        </div>
                        <div class="content">
                            <form name="frm" action="" method="get" >
                                <div class="table-responsive">
                                
                                    <div class="row">
                                        <div class="col-sm-12">

                                            <div id="dvMap" style="width: 500px; height: 500px"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div> 
                    </div>
                    
                </div>
            </div>
        </div>
	</div>
</div>
<?php include('_scripts.php');?>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCH61_Tk5EArH8L9fEvVbqu3Q31F1t5uLQ&sensor=false"></script>
<script type="text/javascript">
        var markers = <?php echo json_encode($row);?>;
        function initialize() {
            var mapOptions = {
                center: new google.maps.LatLng(markers[0].l_latitude, markers[0].l_longitude),
                zoom: 10,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map(document.getElementById("dvMap"), mapOptions);
            var iconBase = 'images/';
            var icons = {
              parking: {
                icon: iconBase + 'imgpsh_fullsize_2.png'
              },
              library: {
                icon: iconBase + 'police.png'
              },
              info: {
                icon: iconBase + 'imgpsh_fullsize.png'
              }
            };
            var infoWindow = new google.maps.InfoWindow();
            var lat_lng = new Array();
            var latlngbounds = new google.maps.LatLngBounds();
            for (i = 0; i < markers.length; i++) {
                var data = markers[i]
                var myLatlng = new google.maps.LatLng(data.l_latitude, data.l_longitude);
                lat_lng.push(myLatlng);

                var marker = new google.maps.Marker({
                    position: myLatlng,
                    // icon: icons[data.type].icon,
                    map: map,
                    title: data.title
                });
                latlngbounds.extend(marker.position);
                (function (marker, data) {
                    google.maps.event.addListener(marker, "click", function (e) {
                        infoWindow.setContent(data.description);
                        infoWindow.open(map, marker);
                    });
                })(marker, data);
            }
            map.setCenter(latlngbounds.getCenter());
            map.fitBounds(latlngbounds);

            //***********ROUTING****************//

            //Intialize the Path Array
            var path = new google.maps.MVCArray();

            //Intialize the Direction Service
            var service = new google.maps.DirectionsService();

            //Set the Path Stroke Color
            var poly = new google.maps.Polyline({ map: map, strokeColor: '#4986E7' });

            //Loop and Draw Path Route between the Points on MAP
            for (var i = 0; i < lat_lng.length; i++) {
                if ((i + 1) < lat_lng.length) {
                    var src = lat_lng[i];
                    var des = lat_lng[i + 1];
                    // path.push(src);
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
        }
    </script>
    <script type="text/javascript">
        jQuery(function($) {
                initialize();
        });
    </script>

<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
