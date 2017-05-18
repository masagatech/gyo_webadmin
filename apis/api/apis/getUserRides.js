var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	
	if( _status ){	

		var condition = " AND tbl_ride.i_user_id = '"+login_id+"'";
		condition += " AND tbl_ride.e_status IN('cancel','scheduled','complete')";
		condition += " ORDER BY tbl_ride.d_time DESC";
		
		dclass._select( "tbl_ride.*, tbl_user.v_name AS user_v_name", 'tbl_ride AS tbl_ride LEFT JOIN tbl_user AS tbl_user ON tbl_user.id = tbl_ride.i_driver_id', condition, function( status, data ){
			if( status && !data.length ){
				gnrl._api_response( res, 0, 'err_no_records', _response );
			}
			else{

				for (var i = 0; i < data.length; i++) {

					if( gnrl._isNull(data[i].user_v_name) ){
						data[i].user_v_name = "Not Assigned";
					}
					if( gnrl._isNull(data[i].l_data.trip_time) ){
						data[i].l_data.trip_time = "";
					}

					data[i].d_time = gnrl._timestamp(data[i].d_time);
					data[i].d_start = gnrl._timestamp(data[i].d_start);
					data[i].d_end = gnrl._timestamp(data[i].d_end);
					
					data[i].l_data.ride_time = gnrl._timestamp(data[i].l_data.ride_time);
					data[i].l_data.time_added = gnrl._timestamp(data[i].l_data.time_added);
					
					
					var _subkeys = {
						actual_distance : 0,
						final_amount : 0,
						actual_dry_run : 0,
						apply_dry_run : 0,
						apply_dry_run_amount : 0,
						ride_paid_by_cash : 0,
						ride_paid_by_wallet : 0,
						trip_time : {
							days : 0,
							hours : 0,
							minutes : 0,
							seconds : 0,
						},
						trip_time_in_min : 0,
					};
					for( var k in _subkeys ){
						if( !data[i].l_data[k] ){
							data[i].l_data[k] = _subkeys[k];
						}
					}
					
					
				}

				gnrl._api_response( res, 1, '', data );
			}
		});
		
	}
	else{
		gnrl._api_response( res, 0, _message, _sort_by, 0 );
	}
};

module.exports = currentApi;
