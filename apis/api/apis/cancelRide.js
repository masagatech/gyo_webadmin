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
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id );
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	var cancel_reason_id = gnrl._is_undf( params.cancel_reason_id );
	var cancel_reason_text = gnrl._is_undf( params.cancel_reason_text );
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	if( _status && !cancel_reason_id && !cancel_reason_text ){
		_status = 0; _message = 'err_req_ride_cancel_reason';
	}
	
	if( _status ){
		
		var rideCancelled = 0;
		var _reason = {};
		var _user = {};
		var v_role = '';
		var cancel_reason_id_text = '';
		var isDay = 1;
		var _ride = [];
		var deduct_amount = 0;
		
		var _data = {
			isCancelled : 0,
			deductAmount : 0,
			role : '',
			reason : {},
			reason_id : cancel_reason_id,
			reason_text : '',
			reason_other_text : cancel_reason_text,
			
			ride : {},
			user : {},
			driver : {},
		};
		
		/*
		STEPS
			>> Get Ride
			>> Get User
			>> Get Driver
			>> Get Cancle Resason
			>> Update Ride
			>> Free Driver
			>> Deduct Cancellation Charges
				>> Get Wallet
				>> Deduct Amount
				>> Refresh Wallet
				>> Send Push Notifications For Cancellation Charge
				
			>> Send Push Notifications For Cancel Ride
			
			>> Send SMS
			>> Send Email
			
		*/
		
		async.series([
			
			// Get Ride
			function( callback ){
				
				var _q = " SELECT ";
				_q += " id ";
				_q += " , v_ride_code ";
				_q += " , i_driver_id ";
				_q += " , i_user_id ";
				_q += " , l_data->'charges'->>'cancel_charge_driver' AS charge_driver ";
				_q += " , l_data->'charges'->>'cancel_charge_user' AS charge_user ";
				_q += " , v_ride_code ";
				_q += " , e_status ";
				
				_q += " FROM ";
				_q += " tbl_ride ";
				_q += " WHERE id = '"+i_ride_id+"'; ";
				
				dclass._query( _q, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else if( data[0].i_user_id != login_id && data[0].i_driver_id != login_id ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else if( data[0].e_status == 'cancel' ){
						gnrl._api_response( res, 0, 'err_msg_ride_alreay_cancelled', {} );
					}
					else{
						_data.ride = data[0];
						callback( null );
					}
				});
			},
			
			// Get User & Driver
			function( callback ){
				var _q = " SELECT id, v_name, v_phone, v_email, v_device_token, lang FROM tbl_user WHERE true ";
				_q += " AND ( id = '"+_data.ride.i_user_id+"' OR id = '"+_data.ride.i_driver_id+"' ); ";
				dclass._query( _q, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_records', {} );
					}
					else{
						
						for( var k in data ){
							if( data[k].id == _data.ride.i_user_id ){
								_data.user = data[k];
							}
							else if( data[k].id == _data.ride.i_driver_id ){
								_data.driver = data[k];
							}
						}
						
						if( _data.user.id == login_id ){
							_data.role = 'user';
						}
						if( _data.driver.id == login_id ){
							_data.role = 'driver';
						}
						
						callback( null );
					}
				});
			},
			
			
			// Get Cancle Resason
			function( callback ){
				if( !_data.reason_id ){
					callback( null );
				}
				else{
					dclass._select( '*', 'tbl_ride_cancel_reason', " AND i_delete = '0' AND id = '"+_data.reason_id+"' ", function( status, data ){
						if( !status ){
							gnrl._api_response( res, 0, 'error', {} );
						}
						else{
							_data.reason = data[0];
							_data.reason_text =  _data.reason.j_title[_lang] ? _data.reason.j_title[_lang] : '';
							callback( null );
						}
					});
				}
			},
			
			// Update Ride
			function( callback ){
				var _ins = [
					"e_status = 'cancel'",
					"l_data = l_data || '"+( gnrl._json_encode({
						'cancel_by' : login_id,
						'cancel_by_role' : _data.role,
						'cancel_reason_id' : _data.reason_id,
						'cancel_reason_id_text' : _data.reason_text,
						'cancel_reason_text' : _data.reason_other_text,
					}) )+"'",
				];
				dclass._updateJsonb( "tbl_ride", _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						_data.isCancelled = 1;
						callback( null );
					}
				});
			},
			
			// Free Driver
			function( callback ){
				var _ins = {
					'is_onride' : 0,
					'is_buzzed' : 0,
				};
				dclass._update( 'tbl_user', _ins, " AND id = '"+_data.driver.id+"' ", function( status, data ){ 
					callback( null );
				});
			},
			
			// Deduct Cancellation Charges
			function( callback ){
				if( _data.role == 'user' ){
					if( _data.ride.charge_user ){
						_data.deductAmount = _data.ride.charge_user;
					}
				}
				else{
					if( _data.ride.charge_driver ){
						_data.deductAmount = _data.ride.charge_driver;
					}
				}
				_data.deductAmount = parseFloat( _data.deductAmount );
				
				if( _data.deductAmount <= 0 ){
					callback( null );
				}
				else{
					
					if( _data.role == 'user' ){
						
						async.series([
						
							// Get Wallet
							function( callback ){
								Wallet.get({
									user_id : _data.user.id,
									role : 'user',
									wallet_type : 'money'
								}, function( status, _wallet ){
									_data._user_wallet = _wallet;
									callback( null );
								});
							},
							
							// Deduct Amount
							function( callback ){
								var _ins = {
									'i_user_id' : _data.user.id,
									'v_type'    : 'ride_cancel',
									
									'f_amount'  : gnrl._minus( _data.deductAmount ),
									'd_added'   : gnrl._db_datetime(),
									'l_data'    : {
										'ride_id' : i_ride_id,
										'ride_code' : _data.ride.v_ride_code,
									},
								};
								Wallet.addTransaction( _ins, function( status, data ){ 
									callback( null );
								});
							},
							
							// Refresh Wallet
							function( callback ){
								Wallet.refreshWallet({
									wallet_id 	: _data._user_wallet.id,
									special 	: 0,
								}, function( status, data ){ 
									callback( null );
								});
							},
							
							// Send Push Notifications For Cancellation Charge
							function( callback ){
								var tokens = [];
								tokens.push({
									'id' : _data.user.id,
									'lang' : _data.user.lang,
									'token' : _data.user.v_device_token,
								});
								Notification.send({
									_key : 'user_ride_cancel_charge',
									_role : 'user',
									_tokens : tokens,
									_keywords : {},
									_custom_params : {
										'i_ride_id' : i_ride_id,
										'ride_code' : _data.ride.v_ride_code,
										'deduct_amount' : _data.deductAmount,
									},
									_need_log : 1,
								}, function( err, response ){
									callback( null );
								});
							},
							
							// Send SMS For Cancellation Charge
							function( callback ){
								SMS.send({
									_to      	: _data.user.v_phone,
									_lang 		: _data.user.lang,
									_key 		: 'user_ride_cancel_charge',
									_keywords 	: {
										'[user_name]' : _data.user.v_name,
										'[amount]' : _data.deductAmount,
										'[ride_code]' : _data.ride.v_ride_code,
									},
								}, function( error_mail, error_info ){
									callback( null );
								});	
							},
							
							// Send Email For Cancellation Charge
							function( callback ){
								Email.send({
									_to      	: _data.user.v_email,
									_lang 		: _data.user.lang,
									_key 		: 'user_ride_cancel_charge',
									_keywords 	: {
										'[user_name]' : _data.user.v_name,
										'[amount]' : _data.deductAmount,
										'[ride_code]' : _data.ride.v_ride_code,
									},
								}, function( error_mail, error_info ){
									callback( null );
								});
							},
							
							
						], function( err, result ){
							callback( null );
						});
						
					}
					else{
						
						async.series([
						
							// Get Wallet
							function( callback ){
								Wallet.get({
									user_id : _data.driver.id,
									role : 'driver',
									wallet_type : 'money'
								}, function( status, _wallet ){
									_data._driver_wallet = _wallet;
									callback( null );
								});
							},
							
							// Deduct Amount
							function( callback ){
								var _ins = {
									'i_user_id' : _data.driver.id,
									'v_type'    : 'ride_cancel', 
									
									'f_amount'  : gnrl._minus( _data.deductAmount ),
									'd_added'   : gnrl._db_datetime(),
									'l_data'    : {
										'ride_id' : i_ride_id,
										'ride_code' : _data.ride.v_ride_code,
										
									},
								};
								Wallet.addTransaction( _ins, function( status, data ){ 
									callback( null );
								});
							},
							
							// Refresh Wallet
							function( callback ){
								Wallet.refreshWallet({
									wallet_id 	: _data._driver_wallet.id,
									special 	: 1,
								}, function( status, data ){ 
									callback( null );
								});
							},
							
							// Send Push Notifications For Cancellation Charge
							function( callback ){
								var tokens = [];
								tokens.push({
									'id' : _data.driver.id,
									'lang' : _data.driver.lang,
									'token' : _data.driver.v_device_token,
								});
								Notification.send({
									_key : 'driver_ride_cancel_charge',
									_role : 'driver',
									_tokens : tokens,
									_keywords : {},
									_custom_params : {
										'i_ride_id' : i_ride_id,
										'ride_code' : _data.ride.v_ride_code,
										'deduct_amount' : _data.deductAmount,
									},
									_need_log : 1,
								}, function( err, response ){
									callback( null );
								});
							},
							
							// Send SMS For Cancellation Charge
							function( callback ){
								SMS.send({
									_to      	: _data.driver.v_phone,
									_lang 		: _data.driver.lang,
									_key 		: 'driver_ride_cancel_charge',
									_keywords 	: {
										'[user_name]' : _data.driver.v_name,
										'[amount]' : _data.deductAmount,
									},
								}, function( error_mail, error_info ){
									callback( null );
								});	
							},
							
							// Send Email For Cancellation Charge
							function( callback ){
								Email.send({
									_to      	: _data.driver.v_email,
									_lang 		: _data.driver.lang,
									_key 		: 'driver_ride_cancel_charge',
									_keywords 	: {
										'[user_name]' : _data.driver.v_name,
										'[amount]' : _data.deductAmount,
									},
								}, function( error_mail, error_info ){
									callback( null );
								});
							},
							
							
						], function( err, result ){
							
							callback( null );
							
						});
					}
				}
			},
			
			// Send Push Notifications For Cancel Ride
			function( callback ){
				
				if( _data.role == 'user' ){
					
					Notification.send({
						_key : 'driver_ride_cancel',
						_role : 'driver',
						_tokens : [{
							'id' : _data.driver.id,
							'lang' : _data.driver.lang,
							'token' : _data.driver.v_device_token,
						}],
						_keywords : {},
						_custom_params : {
							'i_ride_id' : i_ride_id,
							'ride_code' : _data.ride.v_ride_code,
						},
						_need_log : 1,
					}, function( err, response ){
						callback( null );
					});
					
				}
				else{
					
					Notification.send({
						_key : 'user_ride_cancel',
						_role : 'user',
						_tokens : [{
							'id' : _data.user.id,
							'lang' : _data.user.lang,
							'token' : _data.user.v_device_token,
						}],
						_keywords : {},
						_custom_params : {
							'i_ride_id' : i_ride_id,
							'ride_code' : _data.ride.v_ride_code,
						},
						_need_log : 1,
					}, function( err, response ){
						callback( null );
					});
				}
				
			},
			
		], 
		
		function( error, results ){
			if( _data.isCancelled ){
				gnrl._api_response( res, 1, 'succ_msg_ride_cancelled', _data );
			}
			else{
				gnrl._api_response( res, 0, 'error', {} );
			}
		});
		
		
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
