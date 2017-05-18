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
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var v_name 			= gnrl._is_undf( params.v_name );
	var v_email 		= gnrl._is_undf( params.v_email );
	var v_phone 		= gnrl._is_undf( params.v_phone );
	var v_gender 		= gnrl._is_undf( params.v_gender, 'male' );
	var v_password 		= gnrl._is_undf( params.v_password );
	var v_device_token 	= gnrl._is_undf( params.v_device_token );
	var v_otp 			= gnrl._get_otp();
	var referred_by 	= gnrl._is_undf( params.referred_by, '' );
	var lang 	        = gnrl._is_undf( params.lang );
	
	if( !v_name.trim() ){ _status = 0; _message = 'err_req_name'; }
	if( _status && !v_email.trim() ){ _status = 0; _message = 'err_req_email'; }
	if( _status && !validator.isEmail( v_email ) ){ _status = 0; _message = 'err_invalid_email'; }
	if( _status && !v_phone.trim() ){ _status = 0; _message = 'err_req_phone'; }
	if( _status && !validator.isLength( v_phone, { min : 10, max : 10 } ) ){ _status = 0; _message = 'err_validation_phone'; }
	if( _status && !v_password.trim() ){ _status = 0; _message = 'err_req_password'; }
	if( _status && !validator.isLength( v_password, { min : 6, max : 10 } ) ){ _status = 0; _message = 'err_validation_password'; }
	if( _status && !v_device_token.trim() ){ _status = 0; _message = 'err_req_device_token'; }
	
	// ##EMAIL, ##SMS
	if( _status ){

		var _setting = [];
		var _email_template = [];
		var _user_insert = [];
		
		async.series([
			function( callback ){
				dclass._select( '*', 'tbl_user', " AND ( LOWER( v_email ) = '"+v_email.toLowerCase()+"' )", function( status, user ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( user.length ){
						gnrl._api_response( res, 0, 'err_msg_exists_email', {} );
					}
					else{
						callback( null );
					}
				});
			},
			function( callback ){
				dclass._select( '*', 'tbl_user', " AND ( v_phone = '"+v_phone+"' )", function( status, user ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( user.length ){
						gnrl._api_response( res, 0, 'err_msg_exists_phone', {} );
					}
					else{
						callback( null );
					}
				});
			},
			function( callback ){
				var _ins = { 
					'v_role' 			: 'user',
					'v_name' 			: v_name,
					'v_email' 			: v_email,
					'v_phone' 			: v_phone,
					'v_gender' 			: v_gender,
					'v_password' 		: md5( v_password ),
					'v_image' 			: '',
					'v_otp' 			: v_otp,
					'd_added' 			: gnrl._db_datetime(),
					'd_modified' 		: gnrl._db_datetime(),
					'e_status' 			: 'active',
					'v_device_token' 	: v_device_token,
					'l_data'            : gnrl._json_encode({
						'lang'            : lang ? lang : _lang,
						'is_otp_verified' : 0,
						'v_referral_code' : '',
						'referred_by'     : referred_by,
					}),
				};
				dclass._insert( 'tbl_user', _ins, function( status, user_insert ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						_user_insert = user_insert;
						callback( null );
					}
				});
			},
			
			// Send SMS
			function( callback ){
				var params = {
					_to      	: v_phone,
					_lang 		: _lang,
					_key 		: 'user_registration',
					_keywords 	: {
						'[user_name]' : v_name,
					},
				};
				SMS.send( params, function( error_mail, error_info ){
					callback( null );
				});
			},
			
			// Send Email
			function( callback ){
				var params = {
					_to      	: v_email,
					_lang 		: _lang,
					_key 		: 'user_registration',
					_keywords 	: {
						'[user_name]' : v_name,
					},
				};
				Email.send( params, function( error_mail, error_info ){
					callback( null );
				});
			},
		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_register_successfully', { 'id' : _user_insert.id } );
		});
		
	}
	else{
		gnrl._api_response( res, 0, _message, {} );
	}
};

module.exports = currentApi;