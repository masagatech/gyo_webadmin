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
	
	var login_id = gnrl._is_undf( params.login_id );
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	var i_round_id = gnrl._is_undf( params.i_round_id );
	var i_vehicle_id = gnrl._is_undf( params.i_vehicle_id );
	var action = gnrl._is_undf( params.action );
		
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
			
			var buzzID = 0;
			var buzzAccepted = 0;
			
			async.series([
				
				// Check, If driver get buzz
				function( callback ){
					dclass._select( 'id, i_status', 'tbl_buzz', " AND i_ride_id = '"+i_ride_id+"' AND i_driver_id = '"+login_id+"' " , function( status, data ){ 
						if( !status ){
							gnrl._api_response( res, 0, 'error', {} );
						}
						else if( !data.length ){
							gnrl._api_response( res, 0, 'err_no_ride', {} );
						}
						else if( data[0].i_status == '-3' || data[0].i_status == -3 ){
							gnrl._api_response( res, 0, 'err_msg_ride_alreay_accepted', {} );
						}
						else{
							buzzID = data[0].id;
							callback( null );
						}
					});
				},
				
				// Check, if ride exists
				function( callback ){
					dclass._select( 'id,i_driver_id', 'tbl_ride', " AND id = '"+i_ride_id+"' " , function( status, data ){ 
						if( !status ){
							gnrl._api_response( res, 0, 'error', {} );
						}
						else if( !data.length ){
							gnrl._api_response( res, 0, 'err_no_ride', {} );
						}
						else if( data[0].i_driver_id > 0 ){
							gnrl._api_response( res, 0, 'err_msg_ride_alreay_accepted', {} );
						}
						else{
							callback( null );
						}
					});
				},
				
				// Accept Buzz
				function( callback ){
					
					var _q = [];
					
					// Update Ride Table
					_q.push( " UPDATE tbl_ride SET e_status = 'confirm', i_driver_id = '"+login_id+"', i_vehicle_id = '"+i_vehicle_id+"' WHERE id = '"+i_ride_id+"'; ");
					
					// Update Driver On Ride
					_q.push( "UPDATE tbl_user SET is_onride = 1, is_buzzed = 0 WHERE id = '"+login_id+"'; " );
					
					// Accept Buzz
					_q.push( "UPDATE tbl_buzz SET i_status = 1, d_modified = '"+gnrl._db_datetime()+"' WHERE id = '"+buzzID+"'; " );
					
					// Update Buzz [accepted by other]
					_q.push( " UPDATE tbl_buzz SET i_status = '-3', d_modified = '"+gnrl._db_datetime()+"' WHERE id != '"+buzzID+"' AND i_status = '0' AND i_ride_id = '"+i_ride_id+"'; ");
					
					dclass._query( _q.join(';'), function( status, data ){ 
						if( !status ){
							gnrl._api_response( res, 0, 'error', {} );
						}
						else{
							callback( null );
						}
					});
					
				},
				
			], 
			function( error, results ){
				gnrl._api_response( res, 1, 'succ_ride_accepted', {} );
			});
			
		}
		
		
		// Buzz Close By User
		else if( action == 'denied' ){
			
			async.series([
				
				function( callback ){
					
					var _q = [];
					
					// Update Vehicle To Idle 
					_q.push( "UPDATE tbl_user SET is_buzzed = '0' WHERE id = '"+login_id+"'; " );
					
					// Update Buzz To Auto Close
					_q.push( "UPDATE tbl_buzz SET i_status = -1, d_modified = '"+gnrl._db_datetime()+"' WHERE i_status = '0' AND i_ride_id = '"+i_ride_id+"' AND i_driver_id = '"+login_id+"';" );
					
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
					_q.push( "UPDATE tbl_user SET is_buzzed = '0' WHERE id = '"+login_id+"'; " );
					
					// Update Buzz To Auto Close
					_q.push( "UPDATE tbl_buzz SET i_status = -2, d_modified = '"+gnrl._db_datetime()+"' WHERE i_status = '0' AND i_ride_id = '"+i_ride_id+"' AND i_driver_id = '"+login_id+"';" );
					
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
