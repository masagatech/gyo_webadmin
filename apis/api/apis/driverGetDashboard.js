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
	var period = gnrl._is_undf( params.period, 'daily' ).trim();
	
	if( _status ){
		
		var dates = gnrl._db_period_time( period );
		var whPeriod = " AND ( d_time >= '"+( dates.start )+"' AND d_time <= '"+( dates.end )+"' ) ";
		
		var _q = "";
		_q += "SELECT ";
		_q += " a.*, ";
		_q += " ( SELECT COUNT(*) FROM tbl_ride WHERE i_driver_id = '"+login_id+"' "+whPeriod+" ) AS my_trips, ";
		_q += " ( SELECT COALESCE( SUM( (l_data->>'earning')::double precision ), 0) FROM tbl_ride WHERE true AND e_status = 'complete' AND i_driver_id = '"+login_id+"' "+whPeriod+" ) AS my_earning ";
		_q += " FROM tbl_user a ";
		_q += " WHERE true AND v_role = 'driver' AND id = '"+login_id+"' ";
		
		dclass._query( _q, function( status, data ){
			
			
			if( !status ){
				gnrl._api_response( res, 0, '', {} );
			}
			else{
				gnrl._api_response( res, 1, '', {
					//'_q' : _q,
					'my_trips' : gnrl._isNull( data[0].my_trips, 0 ),
					'my_earning' : gnrl._isNull( data[0].my_earning, 0 )
				});
			}
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
