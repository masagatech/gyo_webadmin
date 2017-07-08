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
	
	var v_username = gnrl._is_undf( params.v_username );
	var v_password = gnrl._is_undf( params.v_password );
	var v_otp = gnrl._is_undf( params.v_otp );
	
	if( !v_username.trim() ){ _status = 0; _message = 'err_req_email_or_phone'; }
	if( _status && !v_password.trim() ){ _status = 0; _message = 'err_req_password'; }
	if( _status && !validator.isLength( v_password.trim(), { min : 6, max : 10 } ) ){ _status = 0; _message = 'err_validation_password'; }
	if( _status && !v_otp.trim() ){ _status = 0; _message = 'err_req_otp'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message, {} );
	}
	else{

		var _user = {};
		
		async.series([
		
			// Check otp is correct
			function( callback ){
				
				var _q = " SELECT ";
				_q += " id, v_name, v_email, v_phone, v_otp, lang ";
				_q += " FROM ";
				_q += " tbl_user ";
				_q += " WHERE true ";
				_q += " AND v_role = 'driver' ";
				_q += " AND ( LOWER( v_email ) = '"+v_username.toLowerCase()+"' OR v_phone = '"+v_username+"' ); ";
				
				dclass._query( _q, function( status, user ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !user.length ){
						gnrl._api_response( res, 0, 'err_msg_no_account', {} );
					}
					else{
						_user = user[0];
						if( !validator.equals( v_otp, _user.v_otp ) ){
							gnrl._api_response( res, 0, 'err_invalid_otp', {} );
						}
						else{
							callback( null );
						}
					}
				});
			},
			
			// Update password
			function( callback ){
				var _ins = {
					'v_otp' 		: '',
					'v_password' 	: md5( v_password ),
				};
				dclass._update( 'tbl_user', _ins, " AND id = '"+( _user.id )+"' ", function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, _message );
					}
					else{
						callback( null );
					}
				});
			},

			// Send SMS
			function( callback ){
				SMS.send({
					_to      	: _user.v_phone,
					_lang 		: _user.lang,
					_key 		: 'driver_reset_password',
					_keywords 	: {
						'[user_name]' : _user.v_name,
					},
				}, function( error_mail, error_info ){
					callback( null );
				});
			},
			
			// Send Email
			function( callback ){
				Email.send({
					_to      	: _user.v_email,
					_lang 		: _user.lang,
					_key 		: 'driver_reset_password',
					_keywords 	: {
						'[user_name]' : _user.v_name,
					},
				}, function( error_mail, error_info ){
					callback( null );
				});
			},

		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_password_updated', {} );
		});
		
	}
};

module.exports = currentApi;
