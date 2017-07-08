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
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	var i_vehicle_id = gnrl._is_undf( params.i_vehicle_id );
	var v_pin = gnrl._is_undf( params.v_pin );
		
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	if( _status && !i_vehicle_id ){ _status = 0; _message = 'err_req_vehicle_id'; }
	if( _status && !v_pin ){ _status = 0; _message = 'err_req_pin'; }
	
	if( !_status ){	
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var user_id = 0;
		
		async.series([
				
			// Get Ride
			function( callback ){
				
				dclass._select( 'id, i_user_id, SUBSTR( v_pin::text, 5, 4) as new_pin', 'tbl_ride', " AND i_driver_id = '"+login_id+"' AND id = '"+i_ride_id+"' ", function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error' );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_ride' );
					}
					else if( data[0].new_pin != v_pin ){
						gnrl._api_response( res, 0, 'err_no_ride' );
					}
					else{
						user_id = data[0].i_user_id;
						callback( null );
					}
				});
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
				dclass._select( 'id, v_device_token, lang', 'tbl_user', " AND id = '"+user_id+"' ", function( status, data ){
					if( status && data.length ){
						Notification.send({
							_key : 'user_ride_start',
							_role : 'user',
							_tokens : [{
								'id' : data[0].id,
								'lang' : data[0].lang,
								'token' : data[0].v_device_token,
							}],
							_keywords : {},
							_custom_params : {
								i_ride_id : i_ride_id,
							},
							_need_log : 0,
						}, function( err, response ){
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
