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
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		
		/*
		STEPS
		
			>> Get Ride
			>> Update ride to Paid
			>> Set Vehicle To Idle
			>> Make Cash Payment Active
			>> Select User
			>> Select Driver
			
			>> User Ride Completion Action
				>> Email
				>> SMS
			
			>> Driver Ride Completion Action
				>> Email
				>> SMS
			
			>> Send Money To Referral
				>> Get Referral User
				>> Get Referral Wallet
				>> Add To Referral Wallet
				>> Refresh Wallet
				>> Send SMS
				>> Send Email
				>> Update Current User
			
		*/
		
		
		
		var staticSettings = 1;
		var _data = {
			ride : {},
		};
		
		async.series([
		
			// Get Ride
			function( callback ){
				Ride.get( i_ride_id, function( ride_status, ride_data ){
					if( !ride_status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !ride_data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else if( ride_data[0].i_paid != 0 ){
						gnrl._api_response( res, 0, 'err_msg_ride_alreay_confirmed', {} );
					}
					else{
						_data.ride = ride_data[0];
						callback( null );
					}
				});
			},
			
			// Update ride to complete
			function( callback ){
				var _ins = [
					"i_paid = '1'",
				];
				dclass._updateJsonb( 'tbl_ride', _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){ 
					callback( null );
				});
			},
			
			// Set Vehicle To Idle
			function( callback ){
				var ins = {
					'is_onride' : 0,
					'is_buzzed' : 0,
				};
				dclass._update( 'tbl_user', ins, " AND id = '"+_data.ride.i_driver_id+"' ", function( status, data ){
					callback( null );
				});
			},
			
			// Make Cash Payment Active
			function( callback ) {
				var _ins = {
					'i_success' : 1,
				};
				dclass._update( 'tbl_ride_payments', _ins, " AND v_type = 'cash' AND i_ride_id = '"+i_ride_id+"' ", function( status, data ){
					callback( null );
				});
			},
			
			// Select User
			function( callback ) {
				User.get( _data.ride.i_user_id, function( status, data ){
					_data.user = data[0];
					callback( null );
				});
			},
			
			// Select Driver
			function( callback ) {
				User.get( _data.ride.i_driver_id, function( status, data ){
					_data.driver = data[0];
					callback( null );
				});
			},
			
			// User Ride Completion Action
			function( callback ){
				
				async.series([
					
					// Email
					function( callback ){
						Email.send({
							_to : _data.user.v_email,
							_lang : User.lang( _data.user ),
							_key : 'user_ride_complete',
							_keywords : {
								'[user_name]' : _data.user.v_name,
								'[i_ride_id]' : i_ride_id,
							},
						}, function( error_mail, error_info ){
							callback( null );
						});
					},
					
					// SMS
					function( callback ){
						
						SMS.send({
							_to : _data.user.v_phone,
							_lang : User.lang( _data.user ),
							_key : 'user_ride_complete',
							_keywords : {
								'[user_name]' : _data.user.v_name,
								'[i_ride_id]' : i_ride_id,
							},
						}, function( status, error_info ){
							callback( null );
						});
					}
					
				], function( error, results ){
					callback( null );
				});
				
				
			},
			
			// Driver Ride Completion Action
			function( callback ){
				
				async.series([
					
					// Email
					function( callback ){
						Email.send({
							_to : _data.driver.v_email,
							_lang : User.lang( _data.driver ),
							_key : 'driver_ride_complete',
							_keywords : {
								'[user_name]' : _data.driver.v_name,
								'[i_ride_id]' : i_ride_id,
							},
						}, function( error_mail, error_info ){
							callback( null );
						});
					},
					
					// SMS
					function( callback ){
						callback( null );
					}
					
				], function( error, results ){
					callback( null );
				});
				
				
			},
			
			// Send Money To Referral
			function( callback ){
				
				var referral_code 		= _data.user.l_data.referral_code ? _data.user.l_data.referral_code : '';
				var referral_code_id 	= parseFloat( _data.user.l_data.referral_code_id ? _data.user.l_data.referral_code_id : 0 );
				var referral_user_id 	= parseFloat( _data.user.l_data.referral_user_id ? _data.user.l_data.referral_user_id : 0 );
				var referral_amount 	= parseFloat( _data.user.l_data.referral_amount ? _data.user.l_data.referral_amount : 0 );
				var referral_user 		= {};
				var referral_wallet 	= {};
				
				/*
				_p( 'referral_code', referral_code );
				_p( 'referral_code_id', referral_code_id );
				_p( 'referral_user_id', referral_user_id );
				_p( 'referral_amount', referral_amount );
				_p( '_data.user', _data.user );
				*/
				
				if( referral_code_id && referral_user_id && referral_code && referral_amount > 0 ){
				
					async.series([
						
						// Get Referral User
						function( callback ){
							User.get( referral_user_id, function( status, data ){
								referral_user = data[0];
								_p( '----------------', 'Get Referral User' );
								callback( null );
							});
						},
					
						// Get Referral Wallet
						function( callback ){
							Wallet.get( referral_user_id, 'user', function( status, wallet ){
								referral_wallet = wallet;
								_p( '----------------', 'Get Referral Wallet' );
								callback( null );
							});
						},
						
						// Add To Referral Wallet
						function( callback ){
							var _ins = {
								'i_wallet_id' : referral_wallet.id,
								'i_user_id' : referral_user_id,
								'v_type' : 'referral',
								'v_action' : 'plus',
								'f_amount' : referral_amount,
								'd_added' : gnrl._db_datetime(),
								'l_data' : {
									'referred_user_id' : _data.user.id,
									'referred_user_name' : _data.user.v_name,
									'i_ride_id' : i_ride_id,
								},
							};
							Wallet.addTransaction( _ins, function( status, data ){ 
								_p( '----------------', 'Add To Referral Wallet' );
								callback( null );
							});
							
						},
						
						// Refresh Wallet
						function( callback ){
							Wallet.refreshUserWallet( referral_user_id, function( status, data ){ 
								_p( '----------------', 'Refresh Wallet' );
								callback( null );
							});
						},
						
						// Send SMS 
						function( callback ){
							SMS.send({
								_to : referral_user.v_phone,
								_lang : User.lang( referral_user ),
								_key : 'user_add_money',
								_keywords : {
									'[user_name]' : referral_user.v_name,
									'[amount]' : referral_amount,
									'[from]' : Wallet.getPaymentModeName( 'referral' ),
								},
							}, function( error_sms, error_info ){
								_p( '----------------', 'Send SMS ' );
								callback( null );
							});
						},
						
						// Send Email 
						function( callback ){
							Email.send({
								_to : referral_user.v_email,
								_lang : User.lang( referral_user ),
								_key : 'user_add_money',
								_keywords : {
									'[user_name]' : referral_user.v_name,
									'[amount]' : referral_amount,
									'[from]' : Wallet.getPaymentModeName( 'referral' ),
								},
							}, function( error_mail, error_info ){
								_p( '----------------', 'Send Email ' );
								callback( null );
							});
						},
						
						// Update Current User
						function( callback ){
							var _ins = [
								" l_data = l_data || '"+gnrl._json_encode({
									'referral_code' 	: '',
									// 'referral_code_id' 	: 0,
									'referral_user_id' 	: 0,
									'referral_amount' 	: 0,
								})+"' "
							];
							dclass._updateJsonb( 'tbl_user', _ins, " AND id = '"+_data.user.id+"' ", function( status, data ){ 
								_p( '----------------', 'Update Current User' );
								callback( null );
							});
						},
						
					], function( error, results ){
						
						callback( null );
						
					});
				}
				else{
					_p( '----------------', 'No Referral' );
					callback( null );
				}
			},
			
		], 
		
		function( error, results ){
			
			gnrl._api_response( res, 1, 'succ_ride_completed', _data );
			
		});
		
	}
};

module.exports = currentApi;
