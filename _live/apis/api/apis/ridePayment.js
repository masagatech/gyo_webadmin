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
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id );
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	
	if( !i_ride_id.trim() ){ _status = 0; _message = 'err_req_ride_id'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var _ride = {};
		var _payment_data = [];
		
		async.series([
			
			// Get Ride
			function( callback ){
				var _q = " SELECT * FROM tbl_ride WHERE id = '"+i_ride_id+"' AND ( i_driver_id = '"+login_id+"' OR i_user_id = '"+login_id+"' ) ";
				dclass._query( _q, function( status, ride ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !ride.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else{
						_ride = ride[0];
						callback( null );
					}
				});
			},
			
			// Get Payments
			function( callback ){
				dclass._select( '*', 'tbl_ride_payments', " AND i_ride_id = '"+_ride.id+"' ", function( status, payment_data ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						for( var k in payment_data ){
							payment_data[k].d_added = gnrl._timestamp( payment_data[k].d_added );
						}
						_payment_data = payment_data;
						callback( null );
					}
				});
			},
			
		], 
		function( error, results ){
			
			gnrl._api_response( res, 1, '', {
				ride : _ride,
				payment_data : _payment_data
			});
			
		});
		
	}
	
};

module.exports = currentApi;
