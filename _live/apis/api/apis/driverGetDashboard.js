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
	
	var login_id = gnrl._is_undf( params.login_id );
	var period = gnrl._is_undf( params.period, 'daily' );
	
	if( _status ){
		
		var dates = gnrl._db_period_time( period );
		
		var _q = " SELECT ";
		_q += " COUNT(id) as my_trips ";
		_q += " , COALESCE( SUM( ( l_data->>'ride_driver_receivable' )::double precision ), 0 ) as ride_driver_receivable ";
		_q += " , COALESCE( SUM( ( l_data->>'ride_driver_payable' )::double precision ), 0 ) as ride_driver_payable ";
		
		_q += " FROM tbl_ride ";
		_q += " WHERE true  ";
		_q += " AND i_driver_id = '"+login_id+"' ";
		_q += " AND ( d_time >= '"+dates.start+"' AND d_time <= '"+dates.end+"' ) ";
		_q += " AND e_status = 'complete' ";
		_q += " GROUP BY i_driver_id ";
		
		dclass._query( _q, function( status, data ){
			if( !status ){
				gnrl._api_response( res, 0, '', {});
			}
			else{
				gnrl._api_response( res, 1, '', {
					'my_trips' : data.length ? data[0].my_trips : 0,
					'my_earning' : data.length ? data[0].ride_driver_receivable : 0,
					'ride_driver_payable' : data.length ? data[0].ride_driver_payable : 0,
				});
			}
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
