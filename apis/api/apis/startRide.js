var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var async = require('async');



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
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	var i_vehicle_id = gnrl._is_undf( params.i_vehicle_id ).trim();
	var v_pin = gnrl._is_undf( params.v_pin ).trim();
		
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	if( _status && !i_vehicle_id ){ _status = 0; _message = 'err_req_vehicle_id'; }
	if( _status && !v_pin ){ _status = 0; _message = 'err_req_pin'; }
	
	if( !_status ){	
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var _ride = {};
		var _user = {};
		
		async.series([
				
			// Get Ride
			function( callback ){
				
				dclass._select( '*', 'tbl_ride', " AND id = '"+i_ride_id+"'", function( status, ride ){
					if( !status ){
						gnrl._api_response( res, 0, 'error' );
					}
					else if( !ride.length ){
						gnrl._api_response( res, 0, 'err_no_ride' );
					}
					else if( ride[0].i_driver_id != login_id ){
						gnrl._api_response( res, 0, 'err_no_ride' );
					}
					else{
						_ride = ride[0];
						callback( null );
					}
				});
			},
			
			// Check PIN
			function( callback ){
				_ride.v_pin = _ride.v_pin.toString();
				var last_digits = _ride.v_pin[4]+_ride.v_pin[5]+_ride.v_pin[6]+_ride.v_pin[7];
				if( last_digits != v_pin ){
					gnrl._api_response( res, 0, 'err_invalid_pin' );
				}
				else{
					callback( null );
				}
			},
			
			
			// Update Ride
			function( callback ){
				var _ins = {
					'i_vehicle_id' 	: i_vehicle_id,
					'd_start'       : gnrl._db_datetime(),
					'e_status' 		: 'start',
				};
				dclass._update( 'tbl_ride', _ins, " AND id = '"+i_ride_id+"' ", function( status, updateRide ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						callback( null );
					}
				}); 
			},
			
			// Send Notification To User
			function( callback ){
				
				User.get( _ride.i_user_id, function( status, user ){
					
					_user = user[0];
					
					if( _user.v_device_token ){
						
						var tokens = [];
						tokens.push({
							'id' : _user.id,
							'lang' : _user.l_data.lang,
							'token' : _user.v_device_token,
						});
						var params = {
							_key : 'user_ride_start',
							_role : 'user',
							_tokens : tokens,
							_keywords : {},
							_custom_params : {
								i_ride_id : i_ride_id,
							},
							_need_log : 0,
						};
						Notification.send( params, function( err, response ){
							callback( null );
						});
					}
					else{
						callback( null );
					}
				}); 
			}
			
			
		], function( error, results ){
			
			gnrl._api_response( res, 1, 'succ_msg_ride_started', {} );
			
		});
		
	}
};

module.exports = currentApi;
