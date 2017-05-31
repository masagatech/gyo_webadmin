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
	
	var v_username = gnrl._is_undf( params.v_username );
	var v_otp = gnrl._is_undf( params.v_otp );
	
	if( !v_username ){ _status = 0; _message = 'err_req_email_or_phone'; }
	if( _status && !v_otp ){ _status = 0; _message = 'err_req_otp'; }
	
	if( !_status ){
		
		gnrl._api_response( res, 0, _message, {} );
		
	}
	
	else{
		var _data = {};
		var _user = {};
		
		/*
		STEPS
			// Get User
			// Check Validation
			// Update User
			// Send SMS & Email
		*/
		
		async.series([
		
			// Get User
			function( callback ){
				User.getByPhone( v_username, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_msg_no_account', {} );
					}
					else{
						_user = data[0];
						callback( null );	
					}
				});
			},
			
			// Check Validation
			function( callback ){
				if( User.isVerified( _user ) && _user.e_status == 'inactive' ){
					gnrl._api_response( res, 0, 'err_acc_inactive', {} );
				}
				else if( User.isVerified( _user ) ){
					gnrl._api_response( res, 0, 'err_already_verified', {} );
				}
				else if( !validator.equals( v_otp, _user.v_otp ) ){
					gnrl._api_response( res, 0, 'err_invalid_otp', {} );
				}
				else{
					callback( null );
				}
			},
			
			
			// Update User
			function( callback ){
				
				_user.l_data.is_otp_verified = 1;
				
				var _ins = [
					" v_otp = '' ",
					" l_data = '"+gnrl._json_encode( _user.l_data )+"' "
				];
				
				// Check, if customer
				if( User.isUser( _user ) ){
					_ins.push( " e_status = 'active' " );
				}
				
				dclass._updateJsonb( 'tbl_user', _ins, " AND id = '"+_user.id+"' ", function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', { data : data } );
					}
					else{
						callback( null );
					}
				});
			},
			
			
			
			// Send SMS & Email
			function( callback ){
				
				// Check, if customer
				if( User.isUser( _user ) ){
					
					async.series([
						
						// Send SMS
						function( callback ){
							SMS.send({
								_to : _user.v_phone,
								_lang : User.lang( _user ),
								_key : 'user_otp_verified',
								_keywords : {
									'[user_name]' : _user.v_name,
									'[otp]' : v_otp,
								},
							}, function( error_sms, error_info ){
								callback( null );
							});
						},
						
						// Send Email
						function( callback ){
							Email.send({
								_to : _user.v_email,
								_lang : User.lang( _user ),
								_key : 'user_otp_verified',
								_keywords : {
									'[user_name]' : _user.v_name,
									'[otp]' : v_otp,
								},
							}, function( error_mail, error_info ){
								callback( null );
							});
						},
						
					], function( error, results ){
						callback( null );
					});
					
				}
				else{
					
					async.series([
						
						// Send SMS
						function( callback ){
							SMS.send({
								_to : _user.v_phone,
								_lang : User.lang( _user ),
								_key : 'driver_otp_verified',
								_keywords : {
									'[user_name]' : _user.v_name,
									'[otp]' : v_otp,
								},
							}, function( error_sms, error_info ){
								callback( null );
							});
						},
						
						// Send Email
						function( callback ){
							Email.send({
								_to : _user.v_email,
								_lang : User.lang( _user ),
								_key : 'driver_otp_verified',
								_keywords : {
									'[user_name]' : _user.v_name,
									'[otp]' : v_otp,
								},
							}, function( error_mail, error_info ){
								callback( null );
							});
						}
						
					], function( error, results ){
						callback( null );
					});
				}
				
			},
			
		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_account_verified', {} );
		});

	}
	
};

module.exports = currentApi;
