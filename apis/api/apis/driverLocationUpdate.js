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
	var l_latitude = gnrl._is_undf( params.l_latitude ).trim();
	var l_longitude = gnrl._is_undf( params.l_longitude ).trim();
	var i_ride_id = gnrl._is_undf( params.i_ride_id, 0 );
	var distance = gnrl._is_undf( params.distance, '' );
	var run_type = gnrl._is_undf( params.distance, '' );
	
	if( !i_vehicle_id ){ _status = 0; _message = 'err_req_vehicle_id'; }
	if( _status && !l_latitude ){ _status = 0; _message = 'err_req_latitude'; }
	if( _status && !l_longitude ){ _status = 0; _message = 'err_req_longitude'; } 
	
	if( !_status ){
		gnrl._api_response( res, 0, _message, {} );
	}
	else{
		var _ins = {
			'l_latitude' 	: l_latitude,
			'l_longitude'   : l_longitude,
		};
		
		if( distance ){
			var temp = distance.split(' ');
			temp[1] = temp[1].toLowerCase();
			distance = parseFloat( temp[0] );
			if( temp[1] = 'm' ){
				distance = parseFloat( temp[0] / 1000 );
			}
		}
		
		dclass._update( 'tbl_vehicle', _ins, " AND id = '"+i_vehicle_id+"' ", function( status, data ){
			var _ins = {
				'i_driver_id' 	: login_id,
				'i_vehicle_id' 	: i_vehicle_id,
				'd_time' 		: gnrl._db_datetime(),
				'l_latitude' 	: l_latitude,
				'l_longitude'   : l_longitude,
				'l_data'        : gnrl._json_encode({
					'i_ride_id' : i_ride_id,
					'distance' 	: distance,
					'run_type' 	: run_type,
				}),
			};
			dclass._insert( 'tbl_track_vehicle_location', _ins, function( status, data ){
				gnrl._api_response( res, 1, 'succ_location_updated', {} );
			});
		});
	}
};

module.exports = currentApi;