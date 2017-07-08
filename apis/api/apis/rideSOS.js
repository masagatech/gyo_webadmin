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
	
	var login_id = gnrl._is_undf( params.login_id );
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	var l_latitude = gnrl._is_undf( params.l_latitude, 0 );
	var l_longitude = gnrl._is_undf( params.l_longitude, 0 );
	var city = gnrl._is_undf( params.city, '' );
	
	if( !i_ride_id.trim() ){ _status = 0; _message = 'err_req_ride_id'; }
	// if( _status && !city.trim() ){ _status = 0; _message = 'err_req_city'; }
	
	if( _status ){

		var _admin = [];
		var _ride = {};
		var _user = {};
		var _driver = {};
		
		var l_data = {
			city_id 		: 0,
			city_name 		: city,
			ride_code 		: '',
			
			phone_sos 		: '',
			phone_user 		: '',
			phone_driver 	: '',
		};
		
		async.series([
		
		
			// Get Ride
			function( callback ){
				var _slct = "id, i_driver_id, i_user_id, v_ride_code";
				_slct += " , l_data->>'i_city_id' AS i_city_id ";
				_slct += " , l_data->>'city' AS city ";
				dclass._select( _slct, 'tbl_ride', " AND id = '"+i_ride_id+"' ", function( status, data ){
					if( status && data.length ){
						_ride = data[0];
						l_data.city_id = _ride.i_city_id ? _ride.i_city_id : 0;
						l_data.city_name = _ride.city;
						l_data.ride_code = _ride.v_ride_code;
						callback( null );
					}
					else{
						callback( null );
					}
				});
			},
			
			// Get SOS Number
			function( callback ){
				dclass._select( 'v_phone', 'tbl_sos', " AND i_delete = '0' AND i_city_id = '"+l_data.city_id+"' ", function( status, data ){
					if( status && data.length ){
						l_data.phone_sos = data[0].v_phone;
						callback( null );
					}
					else{
						callback( null );
					}
				});
			},
			
			
			// Get User
			function( callback ){
				dclass._select( 'id, v_name, v_email, v_phone', 'tbl_user', " AND id = '"+_ride.i_user_id+"' ", function( status, data ){
					if( status && data.length ){
						_user = data[0];
						l_data.phone_user = _user.v_phone;
						callback( null );
					}
					else{
						callback( null );
					}
				});
			},
			
			// Get Driver
			function( callback ){
				dclass._select( 'id, v_name, v_email, v_phone', 'tbl_user', " AND id = '"+_ride.i_driver_id+"' ", function( status, data ){
					if( status && data.length ){
						_driver = data[0];
						l_data.phone_driver = _driver.v_phone;
						callback( null );
					}
					else{
						callback( null );
					}
				});
			},
			
			// ADD in SOS table
			function( callback ){
				var _ins = {
					'i_ride_id'   : i_ride_id,
					'l_latitude'  : l_latitude,
					'l_longitude' : l_longitude,
					'd_added'     : gnrl._db_datetime(),
					'l_data'      : gnrl._json_encode( l_data ),
				};
				dclass._insert( 'tbl_ride_sos', _ins, function( status, sos_insert ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						callback( null );
					}
				});
			},
			
			// Send SMS
			function( callback ){
				SMS.send({
					_to      	: l_data.phone_sos,
					_lang 		: _lang,
					_key 		: 'ride_alert_sos',
					_keywords 	: {
						'[i_ride_id]' : i_ride_id,
						'[city]' : l_data.city_name,
						
						'[user_id]' : _user.id,
						'[user_name]' : _user.v_name,
						'[user_email]' : _user.v_email,
						'[user_phone]' : _user.v_phone,
						
						'[driver_id]' : _driver.id,
						'[driver_name]' : _driver.v_name,
						'[driver_email]' : _driver.v_email,
						'[driver_phone]' : _driver.v_phone,
						
						'[ride_code]' : l_data.ride_code,
						
					},
				}, function( error_mail, error_info ){
					callback( null );
				});
			},
			
			/*
			// Send Email
			function( callback ){
				var params = {
					_to      	: _admin.v_email,
					_lang 		: _lang,
					_key 		: 'ride_alert_sos',
					_keywords 	: {
						'[city]' : city,
						
						'[user_id]' : _user.id,
						'[user_name]' : _user.v_name,
						'[user_email]' : _user.v_email,
						'[user_phone]' : _user.v_phone,
						
						'[driver_id]' : _driver.id,
						'[driver_name]' : _driver.v_name,
						'[driver_email]' : _driver.v_email,
						'[driver_phone]' : _driver.v_phone,
						
						'[ride_code]' : _ride.v_ride_code,
						'[i_ride_id]' : i_ride_id,
					},
				};
				Email.send( params, function( error_mail, error_info ){
					callback( null );
				});
			},
			*/
			

		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_sos_send', {
				'phone_sos' : l_data.phone_sos
			});
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
