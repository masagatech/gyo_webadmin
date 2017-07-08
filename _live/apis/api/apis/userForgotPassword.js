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
	if( !v_username ){ _status = 0; _message = 'err_req_email_or_phone'; }
	
	// ##EMAIL, ##SMS

	var _user = [];
	var _v_otp = "";
	var _email_template = [];

	if( _status ){

		async.series([
		
			function( callback ){
				
				var _q = " SELECT ";
					_q += " id, v_name, v_email, v_phone ";
					_q += " FROM tbl_user WHERE v_role = 'user' AND ( LOWER( v_email ) = '"+v_username.toLowerCase()+"' OR v_phone = '"+v_username+"' ) ";
				
				dclass._query( _q, function( status, user ){
					if( !status ){
						gnrl._api_response( res, 0, '' );
					}
					else if( !user.length ){
						gnrl._api_response( res, 0, 'err_msg_no_account' );
					}
					else{
						_user  = user[0];
						_v_otp = gnrl._get_otp();
						callback( null );
					}
				});
			},
			
			
			function( callback ){
				var _ins   = {
					'v_otp' : _v_otp
				};
				dclass._update( 'tbl_user', _ins, " AND id = '"+_user.id+"' ", function( status, update_user ){ 
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
					_to      	: _user.v_phone,
					_lang 		: _lang,
					_key 		: 'user_forgot_password',
					_keywords 	: {
						'[user_name]' : _user.v_name,
						'[otp]'       : _v_otp,
					},
				};
				SMS.send( params, function( error_mail, error_info ){
					callback( null );
				});
			},
			// Send Email
			function( callback ){
				var params = {
					_to      	: _user.v_email,
					_lang 		: _lang,
					_key 		: 'user_forgot_password',
					_keywords 	: {
						'[user_name]' : _user.v_name,
						'[otp]'       : _v_otp,
					},
				};
				Email.send( params, function( error_mail, error_info ){
					callback( null );
				});
			},
		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_otp_sent', {'v_otp' : _v_otp});
		});

	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
