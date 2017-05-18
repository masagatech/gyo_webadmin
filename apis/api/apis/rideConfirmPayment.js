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
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var _data = {
			ride : {},
		};
		
		/*
		STEPS
		
			>> Get Ride
			
			>> Make Cash Payment Active
			
			>> Select User
			>> Select Driver
			
			>> User Ride Completion Action
				>> Email
				>> SMS
			
			>> Driver Ride Completion Action
				>> Email
				>> SMS
			
			
		*/
		
		var staticSettings = 1;
		
		async.series([
		
			// Get Ride
			function( callback ){
				Ride.get( i_ride_id, function( ride_status, ride_data ){
					if( !ride_status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !ride_data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else{
						_data.ride = ride_data[0];
						callback( null );
					}
				});
			},
			
			// Make Cash Payment Active
			function( callback ) {
				var _ins = {
					'i_success' : 1,
				};
				dclass._update( 'tbl_ride_payments', _ins, " AND v_type = 'cash' AND i_ride_id = '"+i_ride_id+"' ", function( status, data ){
					callback( null );
				});
			},
			
			// Select User
			function( callback ) {
				User.get( _data.ride.i_user_id, function( status, data ){
					_data.user = data[0];
					callback( null );
				});
			},
			
			// Select Driver
			function( callback ) {
				User.get( _data.ride.i_driver_id, function( status, data ){
					_data.driver = data[0];
					callback( null );
				});
			},
			
			// User Ride Completion Action
			function( callback ){
				
				async.series([
					
					// Email
					function( callback ){
						var params = {
							_to      	: _data.user.v_email,
							_lang 		: _lang,
							_key 		: 'user_ride_complete',
							_keywords 	: {
								'[user_name]' : _data.user.v_name,
								'[i_ride_id]' : i_ride_id,
							},
						};
						Email.send( params, function( error_mail, error_info ){
							callback( null );
						});
					},
					
					// SMS
					function( callback ){
						callback( null );
						/*
						var params = {
							_to      	: _data.user.v_phone,
							_lang 		: _lang,
							_key 		: 'user_ride_complete',
							_keywords 	: {
								'[user_name]' : _data.user.v_name,
								'[i_ride_id]' : i_ride_id,
							},
						};
						SMS.send( params, function( error_mail, error_info ){
							callback( null );
						});
						*/
					}
					
				], function( error, results ){
					callback( null );
				});
				
				
			},
			
			
			// Driver Ride Completion Action
			function( callback ){
				
				async.series([
					
					// Email
					function( callback ){
						var params = {
							_to      	: _data.driver.v_email,
							_lang 		: _lang,
							_key 		: 'driver_ride_complete',
							_keywords 	: {
								'[user_name]' : _data.driver.v_name,
								'[i_ride_id]' : i_ride_id,
							},
						};
						Email.send( params, function( error_mail, error_info ){
							callback( null );
						});
					},
					
					// SMS
					function( callback ){
						callback( null );
					}
					
				], function( error, results ){
					callback( null );
				});
				
				
			},
			
		], 
		
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_ride_completed', _data );
		});
		
	}
};

module.exports = currentApi;
