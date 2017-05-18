var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var FCM     = require('fcm-node');

var async = require('async');



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
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	var i_round_id = gnrl._is_undf( params.i_round_id ).trim();
	var i_vehicle_id = gnrl._is_undf( params.i_vehicle_id ).trim();
	var action = gnrl._is_undf( params.action ).trim();
		
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	if( _status && !i_round_id ){ _status = 0; _message = 'err_req_round_id'; }
	if( _status && !i_vehicle_id ){ _status = 0; _message = 'err_req_vehicle_id'; }
	if( _status && !action ){ _status = 0; _message = 'err_req_action'; }
	
	/*
		i_status
		[0 	: pending], 
		[1 	: accept], 
		[-1 : denied], 
		[-2 : time out], 
		[-3 : accepted by other]
		[-4 : buzz fail]
	*/
	
	if( _status ){	

		// Accept Ride
		if( action == 'accept' ){ 
			
			var _ride = {};
			var buzzAccepted = 0;
			var alredyAccepted = 0;
			
			async.series([
			
				// Check, If driver get buzz
				function( callback ){
					dclass._select( '*', 'tbl_buzz', " AND i_ride_id = '"+i_ride_id+"' AND i_driver_id = '"+login_id+"' " , function( status, data ){ 
						if( !status ){
							gnrl._api_response( res, 0, 'error', {} );
						}
						else if( !data.length ){
							gnrl._api_response( res, 0, 'err_no_ride', {} );
						}
						else{
							callback( null );
						}
					});
				},
			
				// Check, if ride exists
				function( callback ){
					dclass._select( '*', 'tbl_ride', " AND id = '"+i_ride_id+"' " , function( status, data ){ 
						if( !status ){
							gnrl._api_response( res, 0, 'error', {} );
						}
						else if( !data.length ){
							gnrl._api_response( res, 0, 'err_no_ride', {} );
						}
						else if( data[0].i_driver_id > 0 ){
							alredyAccepted = 1;
							gnrl._api_response( res, 0, 'err_msg_ride_alreay_accepted', {} );
						}
						else{
							_ride = data[0];
							callback( null );
						}
					});
				},
				
				// Accept Buzz
				function( callback ){
					var _insBuzz = {
						'i_status' : 1,
						'is_alive' : 0,
						'd_modified' : gnrl._db_datetime(),'i_round_id' : i_round_id,
					};
					
					var wh = " AND i_ride_id = '"+i_ride_id+"' ";
					wh += " AND i_round_id = '"+i_round_id+"' ";
					wh += " AND i_driver_id = '"+login_id+"' ";
					
					dclass._update( 'tbl_buzz', _insBuzz, wh, function( status, data ){ 
						if( !status ){
							gnrl._api_response( res, 0, 'error', {} );
						}
						else{
							buzzAccepted = 1;
							callback( null );
						}
					});
				},
				
				
				// Update Ride Table
				function( callback ){
					var _insRide = {
						'i_driver_id' : login_id,
						'i_vehicle_id' : i_vehicle_id,
						'i_round_id' : i_round_id,
						'e_status' : 'confirm',
					};
					dclass._update( 'tbl_ride', _insRide, " AND id = '"+i_ride_id+"' " , function( status, data ){ 
						if( !status ){
							gnrl._api_response( res, 0, 'error', {} );
						}
						else{
							callback( null );
						}
					});
				},
				
				
				// Over Ride Driver Charges
				function( callback ){
					Ride.overWriteChargeVehicleWise( { 'i_ride_id' : i_ride_id }, function( status, data ){
						callback( null );
					});
				},
				
				// Disabled Other Buzz
				function( callback ){
					
					var _insBuzz = {
						'i_status' : -3,
						'is_alive' : 0,
						'd_modified' : gnrl._db_datetime(),'i_round_id' : i_round_id,
					};
					
					var wh = " AND i_ride_id = '"+i_ride_id+"' ";
					wh += " AND i_round_id = '"+i_round_id+"' ";
					wh += " AND i_driver_id != '"+login_id+"' ";
					wh += " AND i_status = '0' ";
					
					dclass._update( 'tbl_buzz', _insBuzz, wh, function( status, data ){ 
						callback( null );
					});
					
				},
				
				// Close other driver Notification
				function( callback ){
					
					var _q = " SELECT * FROM tbl_user "
					_q += " WHERE id IN ( ";
						_q += " SELECT i_driver_id FROM tbl_buzz WHERE i_ride_id = '"+i_ride_id+"' ";
						_q += " AND i_round_id = '"+i_round_id+"' ";
						_q += " AND i_driver_id != '"+login_id+"' ";
					_q += " ) ";
					_q += " AND id != '"+login_id+"'";
					
					dclass._query( _q, function( status, _drivers ){ 
						
						if( !status ){
							callback( null );
						}
						else if( !_drivers.length ){
							callback( null );
						}
						else{
							var tokens = [];
							for( var i = 0; i < _drivers.length; i++ ){
								if( _drivers[i].v_device_token ){
									tokens.push({ 
										'id'    : _drivers[i].id,
										'lang'  : _lang, //_drivers[i].l_data.lang ? _drivers[i].l_data.lang : _lang,
										'token' : _drivers[i].v_device_token,
									});
								}
							}
							if( tokens.length ){
								var params = {
									_key : 'driver_ride_other_assign',
									_role : 'driver',
									_tokens : tokens,
									_keywords : {},
									_custom_params : {},
									_need_log : 1,
								};
								Notification.send( params, function( err, response ){
									callback( null );
								});
							}
							else{
								callback( null );
							}
						}
					});
					
				},
				
			], 
			function( error, results ){
				if( buzzAccepted ){
					gnrl._api_response( res, 1, 'succ_ride_accepted', results );
				}
				else{
					gnrl._api_response( res, 0, 'error', {} );
				}
			});
			
		}
		
		
		// Buzz Close By User
		else if( action == 'denied' ){
			
			async.series([
				
				function( callback ){
					
					var _q = [];
					
					// Update Vehicle To Idle 
					_q.push( "UPDATE tbl_vehicle SET is_idle = 1 WHERE true AND i_driver_id = '"+login_id+"'; " );
					
					// Update Buzz To Auto Close
					_q.push( "UPDATE tbl_buzz SET i_status = -1, is_alive = 0, d_modified = '"+gnrl._db_datetime()+"' WHERE true AND i_status = '0' AND i_ride_id = '"+i_ride_id+"' AND i_driver_id = '"+login_id+"' AND i_round_id = '"+i_round_id+"'; " );
					
					dclass._query( _q.join(''), function( status, data ){ 
						callback( null );
					});
					
				},
				
				
			], function( error, results ){
				
				gnrl._api_response( res, 1, '', {} );
				
			});
			
		}
		
		
		// Buzz Auto Close
		else{
			
			async.series([
			
				function( callback ){
					
					var _q = [];
					
					// Update Vehicle To Idle 
					_q.push( "UPDATE tbl_vehicle SET is_idle = 1 WHERE true AND i_driver_id = '"+login_id+"'; " );
					
					// Update Buzz To Auto Close
					_q.push( "UPDATE tbl_buzz SET i_status = -2, is_alive = 0, d_modified = '"+gnrl._db_datetime()+"' WHERE true AND i_status = '0' AND i_ride_id = '"+i_ride_id+"' AND i_driver_id = '"+login_id+"' AND i_round_id = '"+i_round_id+"'; " );
					
					dclass._query( _q.join(''), function( status, data ){ 
						callback( null );
					});
				},
				
			], function( error, results ){
				
				gnrl._api_response( res, 1, '', {} );
				
			});
			
		}
		
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
