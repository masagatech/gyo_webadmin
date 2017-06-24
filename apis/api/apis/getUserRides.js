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

		var _q = "";
		_q += " SELECT ";
		_q += " tbl_ride.id AS id ";
		_q += " , tbl_ride.v_ride_code AS v_ride_code ";
		_q += " , tbl_ride.e_status AS status ";
		_q += " , tbl_ride.d_time AS ride_time ";
		_q += " , tbl_ride.l_data->>'vehicle_type' AS vehicle_type ";
		_q += " , tbl_ride.l_data->>'pickup_address' AS pickup_address ";
		_q += " , tbl_ride.l_data->>'destination_address' AS destination_address ";
		
		_q += " , tbl_user.v_name AS driver_name ";
		_q += " , tbl_user.v_id AS driver_v_id ";
		
		_q += " FROM tbl_ride AS tbl_ride LEFT JOIN tbl_user AS tbl_user ON tbl_user.id = tbl_ride.i_driver_id ";
		_q += " WHERE true ";
		_q += " AND tbl_ride.i_user_id = '"+login_id+"' ";
		_q += " AND tbl_ride.e_status IN( 'start', 'confirm', 'scheduled', 'complete', 'cancel' ) ";
		_q += " ORDER BY ";
			_q += " CASE tbl_ride.e_status ";
				_q += " WHEN 'start' THEN 1 ";
				_q += " WHEN 'confirm' THEN 2 ";
				_q += " WHEN 'scheduled' THEN 3 ";
				_q += " WHEN 'complete' THEN 4 ";
				_q += " WHEN 'cancel' THEN 5 ";
				_q += " ELSE 6 ";
			_q += " END ";
		_q += " , tbl_ride.d_time DESC ";
		
		
		dclass._query( _q, function( status, data ){
			if( status && !data.length ){
				gnrl._api_response( res, 0, 'err_no_records', _response );
			}
			else{
				for( var k in data ){
					data[k].ride_time = data[k].ride_time ? gnrl._timestamp( data[k].ride_time ) : '';
					data[k].driver_name = data[k].driver_name ? data[k].driver_name : 'Not Assigned';
					data[k].driver_v_id = data[k].driver_v_id ? data[k].driver_v_id : '';
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
