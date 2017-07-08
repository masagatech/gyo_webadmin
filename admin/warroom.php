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
	
	$vehicle_arr = array();
	$vehicle_row = $dclass->select( '*','tbl_vehicle_type', " ORDER BY v_name ");
	foreach($vehicle_row as $key => $val){
		$vehicle_arr[$val['v_type']] = $val['v_name'];
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
								 <?php echo ucfirst( $page );?> <a href="javascript:;" class="fright">
								<button class="btn btn-primary" type="button" onclick="load_driver_warroom();">Refresh</button>
								</a> 
								<span id="reports_div" style="display: hidden;">
                             		<a href="reports.php?page=<?php echo $page; ?>&page_title=<?php echo $page_title; ?> " class="fright btn_reports">
										<button class="btn btn-primary" type="button">Export Excel </button>
									</a>
									<a href="table.php?page=<?php echo $page; ?>&page_title=<?php echo $page_title; ?> " class="fright btn_reports">
										<button class="btn btn-primary" type="button">Export PDF </button>
									</a>
                             	</span>
							</h3>
						</div>
						<div class="content">
							<form name="frm" id="frm_warroom" action="" method="get" >
								<label style="margin-left:5px">
									City
									<div class="clearfix"></div>
									<div class="pull-left" style="">
										<div>
											<select class="select2" name="srch_filter_city" id="srch_filter_city" onChange="load_driver_warroom();">
												<option value="">--Select--</option>
												<?php echo $gnrl->getCityDropdownList( $_GET['srch_filter_city'] ); ?>
											</select>
										</div>
									</div>
								</label>
								<label style="margin-left:5px">
									Vehicle Type
									<div class="clearfix"></div>
									<div class="pull-left" style="">
										<div>
											<select class="select2" name="srch_filter_type" id="srch_filter_type" onChange="load_driver_warroom();">
												<option value="">--Select--</option>
												<?php echo $gnrl->get_keyval_drop( $vehicle_arr, $_GET['srch_filter_type'] ); ?>
											</select>
										</div>
									</div>
								</label>
								
								<label style="margin-left:5px">
									Online/Offline
									<div class="clearfix"></div>
									<div class="pull-left" style="">
										<div>
											<select class="select2" name="srch_on_off" id="srch_on_off" onChange="load_driver_warroom();">
												<option value="">--Select--</option>
												<?php echo $gnrl->get_keyval_drop( array(
													'online' => 'Online',
													'offline' => 'Offline',
												), $_GET['srch_on_off'] ); ?>
											</select>
										</div>
									</div>
								</label>
							
								<label style="margin-left:5px">
									On/Off Duty
									<div class="clearfix"></div>
									<div class="pull-left" style="">
										<div>
											<select class="select2" name="srch_on_duty" id="srch_on_duty" onChange="load_driver_warroom();">
												<option value="">--Select--</option>
												<?php echo $gnrl->get_keyval_drop( array(
													'1' => 'Available',
													'0' => 'Not Available',
												), $_GET['srch_on_duty'] ); ?>
											</select>
										</div>
									</div>
								</label>
								
								<label style="margin-left:5px">
									On Ride
									<div class="clearfix"></div>
									<div class="pull-left" style="">
										<div>
											<select class="select2" name="srch_on_ride" id="srch_on_ride" onChange="load_driver_warroom();">
												<option value="">--Select--</option>
												<?php echo $gnrl->get_keyval_drop( array(
													'1' => 'On Ride',
													'0' => 'No Ride',
												) , $_GET['srch_on_ride'] );?>
											</select>
										</div>
									</div>
								</label>
								
								<div class="table-responsive">
									<div class="row">
										<div class="col-sm-12">
											<div id="alert_msg_div" style="width:100%;"><span></span></div>
											<div id="div_map" style="width: 100%; height:500px"></div>
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
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $gnrl->getSettings('GOOGLE_TRACK_RIDE_API_KEY');?>"></script>
<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>
<script type="text/javascript" >

	function load_driver_warroom(){
		
		// showLoder();
		jQuery.ajax({
			method	: "POST",
			url		: "<?php echo ADMIN_URL?>ajax_operations.php?mode=load_driver_warroom",
			data    : jQuery('#frm_warroom').serialize(),
			success : function( res ){
				
				var res = JSON.parse( res );
				
				jQuery('.page-head h2 span').remove();
				jQuery('.page-head h2').append('<span>&nbsp;&nbsp;('+( res.count )+' Driver Found.)</span>');
				
				jQuery('#alert_msg_div').html( res.msg );
				
				if( res.status == 0 ){
					
					jQuery('#div_map, .btn_reports').hide();

					
				}
				else{
					
					jQuery('#div_map, .btn_reports').show();

					
					var data = res.data;
					
					var mapOptions = {
						center: new google.maps.LatLng( data[0]['l_latitude'], data[0]['l_longitude'] ),
						zoom: 15,
					};
					var map = new google.maps.Map( document.getElementById('div_map'),mapOptions );
					
					_renderGeoCode( map, data, 0 );
					
				}
			}
		});
		return false;
	}
	
	function _renderGeoCode( map, data, k ){
		if( data[k] == undefined ){
			return false;
		}
		else{
			
			var obj = data[k];
			
			var geocoder = new google.maps.Geocoder;
			var infowindow = new google.maps.InfoWindow;
			var latlng = { 
				lat	: parseFloat( obj.l_latitude ),
				lng	: parseFloat( obj.l_longitude )
			};
			geocoder.geocode({ 
				'location': latlng 
			}, function( results, status ){
				
				if( status === 'OK' ){
					
					if( results[1] ){
						// map.setZoom( 15 );
						var marker = new google.maps.Marker({
							position	: latlng,
							icon		: obj.icon,
							map			: map
						});
						google.maps.event.addListener( marker,"click",function(){
							if( infowindow ) infowindow.close();
							
							infowindow = new google.maps.InfoWindow({ 
								content : results[1].formatted_address+ '<br>( '+obj.str+ ' )'
							});
							infowindow.open( map, marker );
						});
					}
					else{
						window.alert('No results found');
					}
				}
				else {
					console.log( obj.str );
					console.log( status );
					console.log( results );
					// window.alert( 'Geocoder failed due to: ' + status );
				}
				
				setTimeout( function(){
					k++;
					_renderGeoCode( map, data, k );
				}, 1500 );
				
			});
		}
	}

	
	function geocodeLatLng( map, obj, cnt, cb ){
				
		var geocoder = new google.maps.Geocoder;
		var infowindow = new google.maps.InfoWindow;
		var latlng = { 
			lat	: parseFloat( obj.l_latitude ),
			lng	: parseFloat( obj.l_longitude )
		};
		geocoder.geocode({ 
			'location': latlng 
		}, function( results, status ){
			if( status === 'OK' ){
				
				if( results[1] ){
					
					map.setZoom( 15 );
					var marker = new google.maps.Marker({
						position	: latlng,
						icon		: obj.icon,
						map			: map
					});
					google.maps.event.addListener( marker,"click",function(){
						if( infowindow )infowindow.close();
						infowindow = new google.maps.InfoWindow({ 
							content : results[1].formatted_address+ '<br>( '+obj.str+ ' )'
						});
						infowindow.open( map, marker );
					});
				}
				else{
					window.alert('No results found');
				}
			}
			else {
				//console.log( obj.str );
				//console.log( status );
				//console.log( results );
				// window.alert( 'Geocoder failed due to: ' + status );
			}			
		});
	}
	
	load_driver_warroom();
	
</script>
</body>
</html>
