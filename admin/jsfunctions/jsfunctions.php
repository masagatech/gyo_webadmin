<script type="text/javascript">
	function redirectTo(path){
		window.location.href=path;
	}
	function showLoder(){
		
		jQuery("#mainLoader").css('display','block' );
			 
		
		//jQuery("#showLoader").show();
	}
	function hideLoder(){
		jQuery("#mainLoader").css('display','none' );
		//jQuery("#showLoader").hide();
	}
	function getError(str){
		return '<div class="alert alert-danger">'+
					'<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'+
					'<i class="fa fa-times-circle sign"></i><strong>Error!</strong>  '+ str
				'</div>';
	}
	function gerSuccess(str){
		return '<div class="alert alert-success">'+
					'<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'+
					'<i class="fa fa-check sign"></i><strong>Success!</strong>  '+ str
				'</div>';
	}
	function confirm_delete(label, id) {
		if(confirm("Are you sure you want to delete this Record?")) {
			document.location.href = label+".php?a=3&chkaction=delete&id="+id;
		}
		return false;
	}
	function confirm_restore(label, id) {
		if(confirm("Are you sure you want to restore this Record?")) {
			document.location.href = label+".php?a=3&chkaction=restore&id="+id;
		}
		return false;
	}
	function confirm_delete_image(label, id) {
		if(confirm("Are you sure you want to delete this Image?")) {
			document.location.href = label+".php?a=3&chkaction=deleteimage&id="+id;
		}
		return false;
	}
	function view_message(id) {
		jQuery("#modal_uname").val(jQuery("#u_name"+id).html());
		jQuery("#modal_role").val(jQuery("#u_role"+id).html());
		jQuery("#modal_subject").val(jQuery("#v_subject"+id).html());
		jQuery("#modal_message").val(jQuery("#l_message"+id).html());
		jQuery("#modal_date").val(jQuery("#d_added"+id).html());
	}
	function doLogin(){
		// alert('dsf');
		// return false;
		var v_admin_username = jQuery("#v_admin_username").val();
		var v_admin_password = jQuery("#v_admin_password").val();
		showLoder();
		jQuery.ajax({
			method:"POST",
			url: "adminActions.php",
			data:{ action:'doLogin', v_admin_username:v_admin_username, v_admin_password:v_admin_password },
			success:function( response ){
				var data = JSON.parse( response );
				var redirect_link = data.redirect_link;
				if( data.success ){
					redirectTo( 'invalid-access.php' );
				}
				else{
					jQuery("#response").html(getError(data.msg));
				}
			},
		});
		hideLoder();
	}
	function rideInfo(id){

		showLoder();
		jQuery.ajax({
			method	: "POST",
			url		: "<?php echo ADMIN_URL?>ajax_operations.php?mode=rideInfo",
			data	: {id:id},
			success : function( res ){
				$("#ride-info-modal").niftyModal("show");
				$("#rideInfoDisplay").html(res);
				hideLoder();
				
			},
		});
		
	}
	function track_vehicle(ride_id){
		
		// showLoder();
		jQuery.ajax({
			method	: "POST",
			url		: "<?php echo ADMIN_URL?>ajax_operations.php?mode=track_vehicle",
			data	: {ride_id:ride_id},
			success : function( res ){
				var res = JSON.parse(res);
				var markers = res;
				var mapOptions = {
	                center: new google.maps.LatLng(markers[0].l_latitude, markers[0].l_longitude),
	                zoom: 8,
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
				// hideLoder();
			},
		});
	}

	
	
	function doLogout(){
		showLoder();
		jQuery.ajax({
			method	: "POST",
			url		: "adminActions.php",
			data	: { action:'doLogout' },
			success	: function( response ){
				var data = JSON.parse( response );
				if( data.success ){
					redirectTo('index.php');
				}
			},
		});
		hideLoder();
	}
	
	
	/*** Added By D. ***/
	/*** Left Right Selection Menu ***/
	function left_right( key, action ){
		var k1 = "#"+key+"_all";
		var k2 = "#"+key;
		
		
		
		if( action == "add" ){
			var selected = jQuery( k1+" option:selected" ).length;
			if( selected > 0 ){
				jQuery( k1+" option:selected" ).each(function(){  
					jQuery( this ).attr( "selected", "selected" );
					jQuery( k2 ).append( jQuery( this ).clone() );
					jQuery( this ).remove();  
				});  
			}
			//sort_options( k2 );
		}
		else{
			var selected = jQuery( k2+" option:selected" ).length;
			if( selected > 0 ){
				jQuery( k2+" option:selected" ).each(function(){  
					jQuery( k1 ).append( jQuery( this ).clone() );  
					jQuery( this ).remove();  
				});
			}
			jQuery( k2+" option" ).prop('selected', true);
		}
		sort_options( k1 );
		sort_options( k2 );
	}
	function sort_options( key ){
		var selectOptions = jQuery( key+' option' );
		selectOptions.sort(function(a, b) {
			if( a.text > b.text ) {
				return 1;
			}
			else if( a.text < b.text ) {
				return -1;
			}
			else {
				return 0
			}
		});
		jQuery( key ).empty().append( selectOptions );
	}
	
	function chk_all_fun(){
		jQuery('input.cls_chk_all').click();
	}
	function mult_action_fun(){
		var v = jQuery('#mult_action').val();
		//name_chk_all
		
		
		
		if( v ){
			var data = jQuery('input[name="name_chk_all[]"]:checked').serialize();
			if( data ){
				if( v == 'active' ){
					var msg = 'Are you sure? you want to Active selected records';
				}
				else if( v == 'inactive' ){
					var msg = 'Are you sure? you want to Inactive selected records';
				}
				else if( v == 'delete' ){
					var msg = 'Are you sure? you want to Delete selected records';
				}
				
				var extra_params = "action="+v;
				extra_params += "&table=<?php echo $table;?>";
				
				if( confirm( msg ) ){
					jQuery.ajax({
						method	: "POST",
						url		: "<?php echo ADMIN_URL?>ajax_operations.php?mode=multi_action",
						data	: data+'&'+extra_params,
						success : function( res ){
							location.href = '';
						},
					});
				}
				else{
					jQuery('#mult_action').val('');
				}
			}
			else{
				alert('Please select at least 1 checkbox');
				jQuery('#mult_action').val('');
			}
		}
	}
	
	

	
	function submit_list_frm(){
		document.frm.limitstart.value = 0;
		setTimeout(function(){
			document.frm.submit();
		},200);
	}
	
</script>

<style>
	.left_right {
		height:200px !important;
		padding:5px !important;
		width:100% !important;
	}
	.left_right option {
		padding:5px 5px !important;
	}


</style>

<style>
 #mainLoader{ position:fixed; width:100%; height:100%; background:rgba(255,255,255,0.6); text-align:center; z-index:11111; display:none; }
 #mainLoader img{ margin:10% 0 0 0; max-width:280px; }
</style>
<div id="mainLoader" >
 <img src="./images/loading.gif" />
</div>
 