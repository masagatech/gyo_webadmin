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
	
	if( !i_ride_id.trim() ){ _status = 0; _message = 'err_req_ride_id'; }

	if( _status ){

		var _admin = [];
		var _ride  = [];
		async.series([

			// Get Admin
			function( callback ){
				dclass._select( '*', 'tbl_admin', " AND v_role = 'superadmin' AND e_status = 'active' LIMIT 1 ", function( status, admin ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !admin.length ){
						gnrl._api_response( res, 0, 'error', {} );	
					}
					else if( admin.length ){
						_admin = admin[0];
						callback( null );
					}
				});
			},

			// Get Ride Details
			function( callback ){
				var _q = "SELECT * ";
				_q += " ,(SELECT v_name FROM tbl_user WHERE id = t1.i_driver_id) as driver_v_name ";
				_q += " ,(SELECT v_name FROM tbl_user WHERE id = t1.i_user_id) as user_v_name ";
				_q += " FROM tbl_ride t1";
				_q += " WHERE id = '"+i_ride_id+"'";
				_q += " LIMIT 1";
				
				dclass._query( _q, function( status, ride ){
					
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !ride.length ){
						gnrl._api_response( res, 0, 'error', {} );	
					}
					else if( ride.length ){
						_ride = ride[0];
						callback( null );
					}
				});
			},

			//Add in sos table
			function( callback ){
				var _ins = {
					'i_ride_id'   : i_ride_id,
					'l_latitude'  : l_latitude,
					'l_longitude' : l_longitude,
					'd_added'     : gnrl._db_datetime(),
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
				var params = {
					_to      	: _admin.v_phone,
					_lang 		: _lang,
					_key 		: 'ride_alert_sos',
					_keywords 	: {
						'[user_name]' : _admin.v_name,
						'[i_ride_id]' : i_ride_id,
						'[user_name_id]' : _ride.user_v_name+'('+_ride.i_user_id+')',
						'[driver_name_id]' : _ride.driver_v_name+'('+_ride.i_driver_id+')',
					},
				};
				SMS.send( params, function( error_mail, error_info ){
					callback( null );
				});
			},
			
			// Send Email
			function( callback ){
				var params = {
					_to      	: _admin.v_email,
					_lang 		: _lang,
					_key 		: 'ride_alert_sos',
					_keywords 	: {
						'[user_name]' : _admin.v_name,
						'[i_ride_id]' : i_ride_id,
						'[user_name_id]' : _ride.user_v_name+'('+_ride.i_user_id+')',
						'[driver_name_id]' : _ride.driver_v_name+'('+_ride.i_driver_id+')',
					},
				};
				Email.send( params, function( error_mail, error_info ){
					callback( null );
				});
			},
			

		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_sos_send', {} );
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
