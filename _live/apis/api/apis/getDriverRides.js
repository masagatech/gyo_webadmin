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
	
	var login_id   = gnrl._is_undf( params.login_id );
	var sort_by    = gnrl._is_undf( params.sort_by, 'today' );

	
	if( !sort_by ){ _status = 0; _message = 'err_req_sort_by'; }
	if( _status && _sort_by.indexOf( sort_by ) == -1 ){ _status = 0; _message = 'err_req_sort_by'; }
	
	if( !_status ){	
		gnrl._api_response( res, 0, _message, {} );
	}
	
	else{
		
		var dates = gnrl._db_period_time( sort_by );
		
		var _q = "";
		_q += " SELECT ";
		
		_q += " rd.id ";
		_q += " , rd.d_time ";
		_q += " , rd.v_ride_code ";
		_q += " , rd.l_data->>'final_amount' AS final_amount ";
		_q += " , rd.l_data->>'payment_mode' AS payment_mode ";
		_q += " , rd.e_status ";
		
		_q += " , ur.v_id AS user_v_id ";
		_q += " , ur.v_name AS user_v_name ";
		_q += " , ur.v_image AS user_v_image ";
		
		_q += " FROM tbl_ride AS rd  ";
		_q += " LEFT JOIN tbl_user AS ur ON ur.id = rd.i_user_id ";
		
		_q += " WHERE true ";
		_q += " AND ( rd.d_time >= '"+dates.start+"' AND rd.d_time <= '"+dates.end+"' ) ";
		_q += " AND rd.i_driver_id = '"+login_id+"' ";
		_q += " AND rd.e_status IN( 'start', 'confirm', 'scheduled', 'complete', 'cancel' ) ";
		_q += " ORDER BY ";
			_q += " CASE rd.e_status ";
				_q += " WHEN 'start' THEN 1 ";
				_q += " WHEN 'confirm' THEN 2 ";
				_q += " WHEN 'scheduled' THEN 3 ";
				_q += " WHEN 'complete' THEN 4 ";
				_q += " WHEN 'cancel' THEN 5 ";
				_q += " ELSE 6 ";
			_q += " END ";
		_q += " , rd.d_time DESC ";
		
		dclass._query( _q, function( status, data ){
			if( !status ){
				gnrl._api_response( res, 0, 'error', {} );
			}
			else if( !data.length ){
				gnrl._api_response( res, 0, 'err_no_records', [] );
			}
			else{
				
				for( var i = 0; i < data.length; i++ ){
					
					data[i].user_v_id = data[i].user_v_id ? data[i].user_v_id : '';
					data[i].user_v_name = data[i].user_v_name ? data[i].user_v_name : '';
					
					data[i].user_v_image = data[i].user_v_image ? gnrl._uploads( 'users/'+data[i].user_v_image ) : '';
					
					data[i].final_amount = data[i].final_amount ? data[i].final_amount : 0;
					data[i].d_time = gnrl._timestamp( data[i].d_time );
					
				}
				
				gnrl._api_response( res, 1, '', data );
			}
		});
		
	}
	
	
};

module.exports = currentApi;
