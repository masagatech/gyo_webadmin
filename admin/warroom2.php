<?php 
include('includes/configuration.php');
$gnrl->check_login();

$page_title = "Track Driver";
$page = "warroom";
$table = 'tbl_user';

	$wh = "";
	if( $_REQUEST['id'] ){
		$wh .= " AND id = '".$_REQUEST['id']."' ";
	}
	if( $_REQUEST['srch_filter_city'] ){
		$wh .= " AND i_city_id = '".$_REQUEST['srch_filter_city']."' ";
	}
	if( isset( $_REQUEST['srch_filter_type'] ) && $_REQUEST['srch_filter_type'] != ''){
							
		$keyword =  trim( $_REQUEST['srch_filter_type'] );
		$wh .= " AND ( 
		   LOWER(v.v_type) like LOWER('".$keyword."') 
			 
		)";
	}
	
	

	// $data = $dclass->select( '*', 'tbl_user', $wh." AND l_latitude IS NOT NULL AND l_longitude IS NOT NULL " );
	$ssql = "SELECT u.*,
			v.v_name AS vehicle_name,
			v.v_type AS vehicle_type,
			v.v_vehicle_number AS vehicle_number,
			u.l_latitude IS NOT NULL AS lat,
			u.l_longitude IS NOT NULL AS long
			FROM tbl_user as u
				LEFT JOIN tbl_vehicle 
				as v ON u.id = v.i_driver_id
			WHERE true AND u.l_latitude IS NOT NULL AND u.l_longitude IS NOT NULL AND u.v_role='driver' ".$wh;
	$restepm = $dclass->query($ssql);
	$data = $dclass->fetchResults($restepm);
	
	if( !count( $data ) || $data ==''){
		echo 'No Data Found'; exit;
	}else{
		$total_driver=count($data);
	}
	$vehicle_row = $dclass->select('*','tbl_vehicle_type', " ORDER BY v_name ");
	$vehicle_arr=array();
	foreach($vehicle_row as $key => $val){
		$vehicle_arr[$val['v_name']] =$val['v_name'];
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
                    	<?php 
                    	echo ucfirst( $page );
                    	?>
                    </h3>
                    </div>
                        <div class="content">
                            <form name="frm" action="" method="get" >
                            	<label style="margin-left:5px">City 
								 <div class="clearfix"></div>
									<div class="pull-left" style="">
									<div>
									 <select class="select2" name="srch_filter_city" id="srch_filter_city" onChange="document.frm.submit();">
											<option value="">--Select--</option>
											 <?php echo $gnrl->getCityDropdownList($_GET['srch_filter_city']); ?>
											</select>
									</div>
								</div>
							</label>
							<label style="margin-left:5px"> Vehicle Type 
								 <div class="clearfix"></div>
									<div class="pull-left" style="">
									<div>
									 <select class="select2" name="srch_filter_type" id="srch_filter_type" onChange="document.frm.submit();">
										<option value="">--Select--</option>
										 <?php echo $gnrl->get_keyval_drop($vehicle_arr,$_GET['srch_filter_type']); ?>
									</select>
									</div>
								</div>
							</label>
							 <?php 
	                        	if($_REQUEST['srch_filter_city'] == ''){ ?>
	                        		<h3 class="text-danger"> Please select city.</h3>
	                        	<?php }
	                        	?>
                                <div class="table-responsive">
                                
                                    <div class="row">
                                        <div class="col-sm-12">

                                        	<?php 
                                        	if($_REQUEST['srch_filter_city'] != ''){ ?>
                                            <div id="testing_div_map" style="width: 100%; height: 500px"></div>
                                        	<?php }
                                        	?>
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


<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $gnrl->getSettings('GOOGLE_TRACK_RIDE_API_KEY');?>&callback=initMap&sensor=false"></script> 
<?php 
	if($_REQUEST['srch_filter_city'] != ''){ ?>
		<script>
	function initMap(){
		var mapOptions = {
			center: new google.maps.LatLng( <?php echo $data[0]['l_latitude']?>, <?php echo $data[0]['l_longitude']?> ),
			zoom: 15,
		};
		var map = new google.maps.Map( document.getElementById('testing_div_map'),mapOptions );
		
		
		<?php
		foreach( $data as $row ){
			?>
			geocodeLatLng( map,  {
				lat : <?php echo $row['l_latitude']?>,
				lng : <?php echo $row['l_longitude']?>,
				vid : '<?php echo $row['v_id']." | " ;?>',
				vtype : '<?php echo $row['vehicle_type']." | " ;?>',
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
							content : results[1].formatted_address+ '<br>( '+obj.vid+obj.vtype+obj.name+obj.online+obj.onduty+obj.onride+ ' )'
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
	<?php }
?>

<?php include('_scripts.php');?>

<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
