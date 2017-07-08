var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var FCM     = require('fcm-node');

var currentApi = function( req, res, next ){
	
	var dclass 	= req.app.get('Database');
	var gnrl 	= req.app.get('gnrl');
	var _p 		= req.app.get('_p');
	
	var params 	= gnrl._frm_data( req );
	var _lang 	= gnrl._getLang( params, req.app.get('_lang') );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var action   = gnrl._is_undf( params.action ).trim();
	var buzz_driver_ids = gnrl._is_undf( params.buzz_driver_ids ).trim();
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	
	if( !action ){ _status = 0; _message = gnrl._lbl( 'err_req_action', _lang ); }
	if( _status && !buzz_driver_ids ){ _status = 0; _message = gnrl._lbl( 'err_req_buzz_driver_ids', _lang ); }
	if( _status && !i_ride_id ){ _status = 0; _message = gnrl._lbl( 'err_req_ride_id', _lang ); }

	if( _status ){	

		if( action == 'direct_assign' ){
			
			//Save Buzz
			var _ins = {
				i_ride_id    : i_ride_id,
				i_driver_id  : buzz_driver_ids,
				i_vehicle_id : 0,
				d_time       : gnrl._db_datetime(),
				i_status     : 1,
				is_alive     : 0,
				l_data       : '{}'
			};
			
			dclass._insert( 'tbl_buzz', _ins, function( status, data ){ 

				// Assign Ride
				var _ins = {
					'i_driver_id' : buzz_driver_ids,

				};
				dclass._update( 'tbl_ride', _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){ 

					if( status ){
						gnrl._api_response( res, 1, _message, data, 0 );
					}
					else{
						gnrl._api_response( res, 0, _message, {}, 0 );
					}

				});

			});

		}
		
		else if( action == 'send_buzz' ){

			//Get Driver Device Tokens 
			dclass._select( 'id, v_device_token', 'tbl_user', " AND id IN("+buzz_driver_ids+")", function( status, users ){ 

				var registration_ids = [];
				var _q = "";
				if( users.length ){
					for (var i = 0; i < users.length; i++) {
						if( !gnrl._isNull( users[i]['v_device_token'] ) ){
							
							registration_ids[i] = users[i]['v_device_token'];
							_q += "INSERT INTO tbl_buzz(i_ride_id, i_driver_id, i_vehicle_id, d_time, i_status, is_alive, l_data) VALUES("+i_ride_id+", "+users[i].id+", 0, '"+gnrl._db_datetime()+"', 0, 1, '{}');";

						}
					}
				}
				
				// Insert Buzz records for drivers
				dclass._query( _q, function( status, insert_buzz ){ 
					
					// Select ride
					dclass._select( '*', 'tbl_ride', " AND id = '"+i_ride_id+"'", function( status, ride ){ 

						if( ride.length ){

							var ride = ride[0];

							//var registration_ids = registration_ids;
							var notification = {
								title : "Ride Request",
								body  : "30 sec"
							};
							var data = {
								type           : 'buzz_show',
								i_ride_id      : i_ride_id,
								i_user_id      : login_id,
								buzz_time      : 30,
								pickup_address : ride.l_data.pickup_address ? ride.l_data.pickup_address : '',
								i_round_id     : 1
							};

							var serverKey = 'AIzaSyC6tkd9ePnqOX29_ymQaZ7HbM_CjmDWRkA';
						    var fcm       = new FCM(serverKey);
						    var message   = { 
						    	registration_ids : registration_ids,
						        notification : notification,
						        data : data
						    };
						    
						    //gnrl._api_response( res, 1, _message, message, 0 ); 	
						    fcm.send(message, function(err, response){
						        if (err) {
						       		gnrl._api_response( res, 1, _message, err, 0 );
						        } else {
						        	gnrl._api_response( res, 1, _message, response, 0 ); 	
						        }
						    });

						}

					});

				});

			});

		}
		
	}
	else{
		gnrl._api_response( res, 0, _message, {}, 0 );
	}
};

module.exports = currentApi;
