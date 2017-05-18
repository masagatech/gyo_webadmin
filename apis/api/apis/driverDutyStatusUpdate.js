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
	var i_vehicle_id = gnrl._is_undf( params.i_vehicle_id ).trim();
	var e_status = gnrl._is_undf( params.e_status ).trim();
	var l_latitude = gnrl._is_undf( params.l_latitude ).trim();
	var l_longitude = gnrl._is_undf( params.l_longitude ).trim();
	
	if( !e_status ){ _status = 0; _message = 'err_req_status'; }
	if( _status && !i_vehicle_id ){ _status = 0; _message = 'err_req_vehicle_id'; }
	if( _status && ['active','inactive'].indexOf( e_status ) < 0 ){ _status = 0; _message = 'err_invalid_status'; }

				
	var _ins = {
		'e_status' : e_status,
	};
	if( l_latitude && l_longitude ){ 
		_ins.l_latitude = l_latitude; 
		_ins.l_longitude = l_longitude;
	}
	
	dclass._update( 'tbl_vehicle', _ins, " AND i_driver_id = '"+( login_id )+"' ", function( status, data ){ 
		if( !status ){
			gnrl._api_response( res, 0, _message );
		}
		else{
			var _ins = {
				'i_vehicle_id' 	: i_vehicle_id,
				'd_time' 		: gnrl._db_datetime(),
				'e_status' 		: e_status,
			};
			if( l_latitude && l_longitude ){ 
				_ins.l_latitude = l_latitude; 
				_ins.l_longitude = l_longitude;
			}
			dclass._insert( 'tbl_track_vehicle_status', _ins, function( status, data ){
				gnrl._api_response( res, 1, 'succ_status_updated', {} );
			});
		}
	});
};

module.exports = currentApi;