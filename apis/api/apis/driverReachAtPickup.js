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
	
	var login_id = gnrl._is_undf( params.login_id );
	var v_token = gnrl._is_undf( params.v_token );
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		/*
		Get Ride
		Get User
		Send Notification
		*/
		
		var _data = {
			
		};
		
		async.series([
			
			// Get Ride
			function( callback ){
				var _q = " SELECT id, i_user_id, v_ride_code FROM tbl_ride WHERE true AND id = '"+i_ride_id+"' AND i_driver_id = '"+login_id+"' ";
				dclass._query( _q, function( status, ride ){
					if( !status ){
						gnrl._api_response( res, 0, _message );
					}
					else if( !ride.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else{
						_data.ride = ride[0];
						_data.user_id = _data.ride.i_user_id;
						callback( null );
					}
				});
			},
			
			
			// Get User
			function( callback ){
				
				var _q = " SELECT id, v_name, v_device_token, lang FROM tbl_user WHERE true AND id = '"+_data.user_id+"' ";
				dclass._query( _q, function( status, data ){
					if( status, data.length ){
						_data.user = data[0];
						callback( null );
					}
					else{
						_data.user_id = 0;
						callback( null );
					}
				});
			},
			
			// Send Notification
			function( callback ){
				
				if( parseInt( _data.user_id ) > 0 ){
					
					var tokens = [{
						'id' : _data.user.id,
						'lang' : _data.user.lang,
						'token' : _data.user.v_device_token,
					}];
					var params = {
						_key : 'user_driver_arrived',
						_role : 'user',
						_tokens : tokens,
						_keywords : {
							'[user_name]' : _data.user.v_name,
							'[ride_code]' : _data.ride.v_ride_code,
						},
						_custom_params : {
							i_ride_id : i_ride_id,
							ride_code : _data.ride.v_ride_code,
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
				
			},
			
		], 
		function( error, results ){
			
			gnrl._api_response( res, 1, '', {} );
		});
	}
	
};

module.exports = currentApi;
