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
	
	var v_username 		= gnrl._is_undf( params.v_username );
	var v_otp 			= gnrl._is_undf( params.v_otp );
	
	if( !v_username ){ _status = 0; _message = 'err_req_email_or_phone'; }
	if( _status && !v_otp.trim() ){ _status = 0; _message = 'err_req_otp'; }
	
	if( _status ){
		
		var _user = [];
		async.series([
		
			function( callback ){
				
				dclass._select( '*', 'tbl_user', " AND ( v_email = '"+v_username+"' OR v_phone = '"+v_username+"' )", function( status, user ){ 

					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !user.length ){
						gnrl._api_response( res, 0, 'err_no_records', {} );
					}
					else{
						
						_user = user[0];
						
						if( _user.l_data.is_otp_verified ){
							gnrl._api_response( res, 0, 'err_already_verified', {} );
						}
						else if( _user.e_status == 'inactive' && _user.l_data.is_otp_verified == 1 ){
							gnrl._api_response( res, 0, 'err_acc_inactive', {} );
						}
						else if( !validator.equals( v_otp, _user.v_otp ) ){
							gnrl._api_response( res, 0, 'err_invalid_otp', {} );
						}
						else{
							callback( null );
						}
					}
					
				});		
						
			},

			// Verify Account
			function( callback ){
				
				if( _user.v_role = 'driver' ){
					var _ins = [
						"v_otp = ''",
						" l_data = l_data || '"+gnrl._json_encode({
							'is_otp_verified' : 1,
						})+"' "
					];	
				}
				else{
					var _ins = [
						" v_otp = '' ",
						" e_status = 'active' ",
						" l_data = l_data || '"+gnrl._json_encode({
							'is_otp_verified' : 1,
						})+"' "
					];	
				}
				
				dclass._updateJsonb( 'tbl_user', _ins, " AND id = '"+_user.id+"' ", function( status, data ){ 
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
				if( _user.v_role = 'driver' ){
					var params = {
						_to      	: _user.v_phone,
						_lang 		: _lang,
						_key 		: 'driver_otp_verified',
						_keywords 	: {
							'[user_name]' : _user.v_name,
							'[otp]' : v_otp,
						},
					};
					SMS.send( params, function( error_sms, error_info ){
						callback( null );
					});
				}
				else{
					var params = {
						_to      	: _user.v_phone,
						_lang 		: _lang,
						_key 		: 'user_otp_verified',
						_keywords 	: {
							'[user_name]' : _user.v_name,
							'[otp]' : v_otp,
						},
					};
					SMS.send( params, function( error_sms, error_info ){
						callback( null );
					});
				}
			},
			
			// Send Email
			function( callback ){
				if( _user.v_role = 'driver' ){
					var params = {
						_to      	: _user.v_email,
						_lang 		: _lang,
						_key 		: 'driver_otp_verified',
						_keywords 	: {
							'[user_name]' : _user.v_name,
							'[otp]' : v_otp,
						},
					};
					Email.send( params, function( error_mail, error_info ){
						callback( null );
					});
				}
				else{
					var params = {
						_to      	: _user.v_email,
						_lang 		: _lang,
						_key 		: 'user_otp_verified',
						_keywords 	: {
							'[user_name]' : _user.v_name,
							'[otp]' : v_otp,
						},
					};
					Email.send( params, function( error_mail, error_info ){
						callback( null );
					});
				}
			},
			
			
			
		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_account_verified', {} );
		});

	}
	else{
		gnrl._api_response( res, 0, _message, {} );
	}
};

module.exports = currentApi;
