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
	var v_phone = gnrl._is_undf( params.v_phone );
	
	if( !i_ride_id.trim() ){ _status = 0; _message = 'err_req_ride_id'; }
	if( _status && !v_phone.trim() ){ _status = 0; _message = 'err_req_phone'; }

	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var _ride = {};
		var _data = {
			_link : '',
			_track_code : '',
		};
		
		/*
			STEPS
			>> Get Ride
			>> Get Track Link
		*/
		
		async.series([

			// Get Ride
			function( callback ){
				dclass._select( '*', 'tbl_ride', " AND id = '"+i_ride_id+"' AND i_user_id = '"+login_id+"' ", function( status, ride ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !ride.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else{
						_ride = ride[0];
						_data._track_code = _ride.v_ride_code+'-'+_ride.id;
						callback( null );
					}
				});
			},
			
			// Get Track Link
			function( callback ){
				Settings.get( 'RIDE_TRACK_URL', function( val ){
					_data._link = val.split('_track_code_').join( _data._track_code );
					callback( null );
				});
			},

			// Send SMS
			function( callback ){
				SMS.send({
					_to : v_phone,
					_key : 'ride_track_sms',
					_lang : _lang,
					_keywords : {
						'[ride_track_code]' : _data._track_code,
						'[ride_track_link]' : _data._link,
					},
				}, function( status, info ){
					if( !status ){
						gnrl._api_response( res, 0, '', {
							status : status,
							info : info
						});
					}
					else{
						callback( null );
					}
				});
			},
			
		], 
		function( error, results ){
			gnrl._api_response( res, 1, '', _data );
		});
		
	}
	
};

module.exports = currentApi;
