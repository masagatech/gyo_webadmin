var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var async       = require('async');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	var v_track_code = gnrl._is_undf( params.v_track_code );
	
	if( !v_track_code.trim() ){ _status = 0; _message = 'err_req_track_code'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var _track = [];
		var _ride_code = '';
		var _ride_id = 0;
		
		/*
			STEPS
				>> Parse Tracking Code
				>> Check Ride
				>> Get Tracking Data
		*/
		
		async.series([
		
			// Parse Tracking Code
			function( callback ){
				_ride_code = v_track_code;
				callback( null );
			},
			
			// Check Ride
			function( callback ){
				dclass._select( 'id', 'tbl_ride', " AND v_ride_code = '"+_ride_code+"' ", function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else{
						_ride_id = data[0].id;
						callback( null );
					}
				});
			},
			
			// Get Tracking Data
			function( callback ){
				dclass._select( '*', 'tbl_track_vehicle_location', " AND l_data->>'run_type' = 'track' AND l_data->>'i_ride_id' = '"+_ride_id+"' ORDER BY id DESC ", function( status, track ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						_track = track;
						callback( null );
					}
				});
			},
			
		], 
		function( error, results ){
			gnrl._api_response( res, 1, '', _track );
		});
		
	}
	
};

module.exports = currentApi;
