var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	if( _status ){	

		var _q = " SELECT tbl_ride.*";
		_q += " ,(SELECT tbl_user.v_name FROM tbl_user AS tbl_user WHERE tbl_user.id = tbl_ride.i_user_id) AS user_v_name";
		_q += " ,(SELECT tbl_ride_rate.i_rate FROM tbl_ride_rate AS tbl_ride_rate WHERE tbl_ride_rate.i_ride_id = tbl_ride.id) AS ride_i_rate";
		_q += " ,(SELECT tbl_ride_rate.l_comment FROM tbl_ride_rate AS tbl_ride_rate WHERE tbl_ride_rate.i_ride_id = tbl_ride.id) AS ride_l_comment";
		_q += " FROM tbl_ride AS tbl_ride ";
		_q += " WHERE tbl_ride.id = '"+i_ride_id+"' ";
		_q += " AND tbl_ride.i_user_id = '"+login_id+"' ";
		_q += " LIMIT 1 ";

		dclass._query( _q, function( status, data ){
			if( status ){
				
				data = data[0];
				
				if( gnrl._isNull( data.ride_i_rate ) ){
					data.ride_i_rate = 0;
				}
				if( gnrl._isNull(data.ride_l_comment) ){
					data.ride_l_comment = "";
				}
				if( gnrl._isNull( data.l_data.trip_time ) ){
					data.l_data.trip_time = "";
				}
				
				data.d_time = gnrl._timestamp( data.d_time );
				data.d_start = gnrl._timestamp( data.d_start );
				data.d_end = gnrl._timestamp( data.d_end );
				
				data.l_data.ride_time = gnrl._timestamp( data.l_data.ride_time );
				data.l_data.time_added = gnrl._timestamp( data.l_data.time_added );
				
				
				var _subkeys = {
					actual_distance : 0,
					actual_amount : 0,
					actual_dry_run : 0,
					service_tax : 0,
					surcharge : 0,
					final_amount : 0,
					apply_dry_run : 0,
					apply_dry_run_amount : 0,
					ride_paid_by_cash : 0,
					ride_paid_by_wallet : 0,
				};
				for( var k in _subkeys ){
					if( !data.l_data[k] ){
						data.l_data[k] = _subkeys[k];
					}
				}
				
				gnrl._api_response( res, 1, '', data );
			}
			else{
				gnrl._api_response( res, 0, 'err_no_records', _response );
			}
		});
		
	}
	else{
		gnrl._api_response( res, 0, _message, _sort_by, 0 );
	}
};

module.exports = currentApi;
