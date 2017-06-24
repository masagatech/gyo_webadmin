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
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var vehicle_type = gnrl._is_undf( params.vehicle_type ).trim();
	var pickup_address = gnrl._is_undf( params.pickup_address ).trim();
	var pickup_latitude = gnrl._is_undf( params.pickup_latitude ).trim();
	var pickup_longitude = gnrl._is_undf( params.pickup_longitude ).trim();
	var destination_address = gnrl._is_undf( params.destination_address ).trim();
	var destination_latitude = gnrl._is_undf( params.destination_latitude ).trim();
	var destination_longitude = gnrl._is_undf( params.destination_longitude ).trim();
	
	var estimate_km = gnrl._is_undf( params.estimate_km, 0 ).trim();
	var estimate_time = gnrl._is_undf( params.estimate_time, 0 ).trim();

	var city = gnrl._is_undf( params.city ).trim();
	var charges = gnrl._is_undf( params.charges, {} );
	var ride_type = gnrl._is_undf( params.ride_type ).trim();
	var ride_time = gnrl._is_undf( params.ride_time ).trim();
	
	if( !vehicle_type ){ _status = 0; _message = 'err_req_vehicle_type'; }
	if( _status && !pickup_address ){ _status = 0; _message = 'err_req_pickup_address'; }
	if( _status && !pickup_latitude ){ _status = 0; _message = 'err_req_pickup_latitude'; }
	if( _status && !pickup_longitude ){ _status = 0; _message = 'err_req_pickup_longitude'; }
	if( _status && !destination_address ){ _status = 0; _message = 'err_req_destination_address'; }
	if( _status && !destination_latitude ){ _status = 0; _message = 'err_req_destination_latitude'; }
	if( _status && !destination_longitude ){ _status = 0; _message = 'err_req_destination_longitude'; }
	if( _status && gnrl._isNull( charges ) ){ _status = 0; _message = 'err_req_charges'; }
	
	ride_type = ride_type ? ride_type : 'ride_now';
	if( _status && ride_type == 'ride_later' && !ride_time ){
		_status = 0; _message = 'err_req_ride_time';
	}
	ride_time = ride_time ? ride_time : gnrl._db_datetime();
	
	if( !_status ){	
		gnrl._api_response( res, 0, _message );
	}
	else{
		var v_ride_code = '';
		var ride_id = 0;
		var new_pin = "";
		var v_pin = Ride.getPin();
		var i_city_id = 0;
		var _user = {};
		var v_gender = 'male';
		
		async.series([
			
			// Get User
			function( callback ){
				User.get( login_id, function( status, data ){
					if( status && data.length ){
						_user = data[0];
						v_gender = _user.v_gender;
					}
					
					callback( null );
				});
			},
			
			// Get City ID
			function( callback ){
				City.getByName( city, function( status, data ){
					if( status && data.length ){
						i_city_id = data[0].id;
					}
					callback( null );
				});
			},
			
			// Save Ride
			function( callback ){
				
				var _ins = { 
					'i_user_id' 		: login_id,
					'i_driver_id' 		: 0,
					'i_vehicle_id' 		: 0,
					'i_round_id' 		: 0,
					'i_paid' 			: 0,
					'v_pin' 			: v_pin,
					'd_time' 			: ( ride_type == 'ride_later' ) ? ride_time : gnrl._db_datetime(),
					'e_status' 		    : ( ride_type == 'ride_now' ? 'pending' : 'scheduled' ),
					'l_data'            : gnrl._json_encode({
						'round_id'              : 0,
						'round_order'           : 0,
						'vehicle_type'          : vehicle_type,
						'pickup_address'        : pickup_address,
						'pickup_latitude'       : pickup_latitude,
						'pickup_longitude'      : pickup_longitude,
						'destination_address'   : destination_address,
						'destination_latitude'  : destination_latitude,
						'destination_longitude' : destination_longitude,
						'estimate_km'           : estimate_km,
						'estimate_time'         : estimate_time,
						'time_added'       		: gnrl._db_datetime(),
						'ride_type'       		: ride_type,
						'ride_time'       		: ride_time,
						'city'       			: city,
						'i_city_id'       		: i_city_id,
						'charges'       		: JSON.parse( charges ),
						'v_gender'       		: v_gender,
					}),
				};
				
				v_pin = v_pin.toString();
				new_pin = v_pin[0]+v_pin[1]+v_pin[2]+v_pin[3]+'-'+v_pin[4]+v_pin[5]+v_pin[6]+v_pin[7];
				
				dclass._insert( 'tbl_ride', _ins, function( status, data ){ 
					if( status ){
						ride_id = data.id;
						callback( null );
					}
					else{
						gnrl._api_response( res, 0, _message );
					}
				});
			},
			
			// Generate ID
			function( callback ){
				v_ride_code = 'RD'+gnrl._pad_left( ride_id, "00000000" );
				var _ins = { 
					'v_ride_code' : v_ride_code,
				};
				dclass._update( 'tbl_ride', _ins, " AND id = '"+ride_id+"' ", function( status, updated ){ 
					callback( null );
				});
			},
			
			
			function( callback ){
				gnrl._api_response( res, 1, "", { 
					'i_ride_id' : ride_id,
					'v_pin' : new_pin,
					'v_ride_code' : v_ride_code,
					
				});
			}
			
		], function( error, results ){
			
			gnrl._api_response( res, 0, _message );
			
		});
		
		
	}
};

module.exports = currentApi;
