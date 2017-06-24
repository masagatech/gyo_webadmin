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
	var _sort_by  = ['today', 'weekly', 'monthly'];
	
	var login_id   = gnrl._is_undf( params.login_id ).trim();
	var sort_by    = gnrl._is_undf( params.sort_by, 'today' ).trim();

	
	if( !sort_by ){ _status = 0; _message = 'err_req_sort_by'; }
	if( _status && _sort_by.indexOf( sort_by ) == -1 ){ _status = 0; _message = 'err_req_sort_by'; }
	
	if( _status ){	

		var dates = gnrl._db_period_time( sort_by );
		
		var _q = "";
		_q += " SELECT ";
		_q += " tbl_ride.* ";
		_q += " , tbl_ride.id AS id ";
		_q += " , tbl_ride.v_ride_code AS v_ride_code ";
		_q += " , tbl_ride.e_status AS status ";
		_q += " , tbl_ride.d_time AS ride_time ";
		_q += " , ( SELECT tbl_user.v_name FROM tbl_user AS tbl_user WHERE tbl_user.id = tbl_ride.i_user_id ) AS user_v_name";
		_q += " , ( SELECT tbl_user.v_image FROM tbl_user AS tbl_user WHERE tbl_user.id = tbl_ride.i_user_id ) AS user_v_image";
		_q += " , ( SELECT tbl_user.v_id FROM tbl_user AS tbl_user WHERE tbl_user.id = tbl_ride.i_user_id ) AS user_v_id";
		
		_q += " , tbl_ride.l_data->>'vehicle_type' AS vehicle_type ";
		_q += " , tbl_ride.l_data->>'pickup_address' AS pickup_address ";
		_q += " , tbl_ride.l_data->>'destination_address' AS destination_address ";
		_q += " , tbl_user.v_name AS driver_name ";
		
		_q += " FROM tbl_ride AS tbl_ride LEFT JOIN tbl_user AS tbl_user ON tbl_user.id = tbl_ride.i_driver_id ";
		_q += " WHERE true ";
		_q += " AND ( tbl_ride.d_time >= '"+dates.start+"' AND tbl_ride.d_time <= '"+dates.end+"' ) ";
		_q += " AND tbl_ride.i_driver_id = '"+login_id+"' ";
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
				
				
				for (var i = 0; i < data.length; i++) {

					if( !data[i].ride_i_rate ){ data[i].ride_i_rate = ''; }
					if( !data[i].ride_l_comment ){ data[i].ride_l_comment = ''; }
					
					if( !data[i].user_v_name ){ data[i].user_v_name = ''; }
					
					if( !data[i].user_v_image ){ data[i].user_v_image = ''; }
					else{ data[i].user_v_image = gnrl._uploads( 'users/'+data[i].user_v_image ); }
					
					
					if( !data[i].l_data.trip_time ){ data[i].l_data.trip_time = ''; }
					if( !data[i].l_data.final_amount ){ data[i].l_data.final_amount = ''; }
					
					data[i].d_time = gnrl._timestamp( data[i].d_time );
					
					// data[i].d_start = gnrl._timestamp(data[i].d_start);
					// data[i].d_end = gnrl._timestamp(data[i].d_end);
					/*
					data[i].l_data.ride_time = gnrl._timestamp(data[i].l_data.ride_time);
					data[i].l_data.time_added = gnrl._timestamp(data[i].l_data.time_added);
					*/
				}
				
				

				gnrl._api_response( res, 1, '', data );
			}
		});
		
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
