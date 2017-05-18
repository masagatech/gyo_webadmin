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
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	if( _status ){	

		var _q = " SELECT tbl_ride.*";
		_q += " ,(SELECT tbl_user.v_name FROM tbl_user AS tbl_user WHERE tbl_user.id = tbl_ride.i_user_id) AS user_v_name";
		_q += " ,(SELECT tbl_user.v_image FROM tbl_user AS tbl_user WHERE tbl_user.id = tbl_ride.i_user_id) AS user_v_image";
		_q += " ,(SELECT tbl_ride_rate.i_rate FROM tbl_ride_rate AS tbl_ride_rate WHERE tbl_ride_rate.i_ride_id = tbl_ride.id) AS ride_i_rate";
		_q += " ,(SELECT tbl_ride_rate.l_comment FROM tbl_ride_rate AS tbl_ride_rate WHERE tbl_ride_rate.i_ride_id = tbl_ride.id) AS ride_l_comment";
		_q += " FROM tbl_ride AS tbl_ride ";
		_q += " WHERE tbl_ride.id = '"+i_ride_id+"' ";
		_q += " AND tbl_ride.i_driver_id = '"+login_id+"' ";
		_q += " LIMIT 1 ";

		dclass._query( _q, function( status, data ){
			if( status ){
				if( gnrl._isNull(data[0].ride_i_rate) ){
					data[0].ride_i_rate = 0;
				}
				if( gnrl._isNull(data[0].ride_l_comment) ){
					data[0].ride_l_comment = "";
				}
				if( gnrl._isNull(data[0].user_v_name) ){
					data[0].user_v_name = "";
				}
				if( gnrl._isNull(data[0].user_v_image) ){
					data[0].user_v_image = "";
				}
				else{
					data[0].user_v_image = gnrl._uploads( 'users/'+data[0].user_v_image );
				}

				if( gnrl._isNull(data[0].l_data.trip_time) ){
					data[0].l_data.trip_time = "";
				}
				
				data[0].d_time = gnrl._timestamp(data[0].d_time);
				data[0].d_start = gnrl._timestamp(data[0].d_start);
				data[0].d_end = gnrl._timestamp(data[0].d_end);
				
				data[0].l_data.ride_time = gnrl._timestamp(data[0].l_data.ride_time);
				data[0].l_data.time_added = gnrl._timestamp(data[0].l_data.time_added);

				gnrl._api_response( res, 1, '', data[0] );
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
