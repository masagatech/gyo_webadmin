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
	
	var login_id = gnrl._is_undf( params.login_id );
	var f_amount = gnrl._is_undf( params.f_amount );
	var v_payment_mode = gnrl._is_undf( params.v_payment_mode );
	var transaction_id = gnrl._is_undf( params.transaction_id );
	
	//var v_card_no = gnrl._is_undf( params.v_card_no );
	//var v_expiry_date = gnrl._is_undf( params.v_expiry_date );
	//var v_cvv = gnrl._is_undf( params.v_cvv );
	//var v_name_on_card = gnrl._is_undf( params.v_name_on_card );
	
	
	if( !f_amount.trim() ){ _status = 0; _message = 'err_req_amount'; }
	if( _status && !v_payment_mode.trim() ){ _status = 0; _message = 'err_req_payment_mode'; }

	if( _status && [ 'payu' ].indexOf( v_payment_mode ) >= 0 ){ 
		//if( _status && !v_card_no.trim() ){ _status = 0; _message = 'err_req_card_no'; }
		//if( _status && !gnrl._cardValidation(v_card_no) ){ _status = 0; _message = 'err_invalid_card_no'; }
		//if( _status && !v_expiry_date.trim() ){ _status = 0; _message = 'err_req_expiry_date'; }
		//if( _status && !v_cvv.trim() ){ _status = 0; _message = 'err_req_cvv'; }
		//if( _status && !v_name_on_card.trim() ){ _status = 0; _message = 'err_req_name_on_card'; }
		if( _status && !transaction_id.trim() ){ _status = 0; _message = 'err_req_transaction_id'; }
		
	}
	
	if( _status ){

		var _user_insert = [];
		var _user = [];
		
		var _data = {
			user : {},
			wallet : {},
		};
		
		/*
			>> Get User
			>> Get Wallet
			>> Check Validations
			>> Add Money To Wallet
			>> Refresh Wallet
			>> Send 
				>> Email 
				>> SMS 
				>> Push Notification
		*/
		
		async.series([
			
			// Get User
			function( callback ){
				User.get( login_id, function( status, user ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !user.length ){
						gnrl._api_response( res, 0, 'err_msg_no_account', {} );
					}
					else{
						_data.user = user[0];
						callback( null );
					}
				});
			},
			
			// Get Wallet
			function( callback ){
				Wallet.get( login_id, 'user', function( status, wallet ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						_data.wallet = wallet;
						callback( null );
					}
				});
			},
			
			// Check Validations
			function( callback ){
				if( [ 'payu' ].indexOf( v_payment_mode ) >= 0 ){
					callback( null );
				}
				else{
					callback( null );
				}
			},
			
			// Add Money To Wallet
			function( callback ){
				var _ins = {
					'i_wallet_id' : _data.wallet.id,
					'i_user_id' : login_id,
					'v_type' : v_payment_mode,
					'v_action' : 'plus',
					'f_amount' : f_amount,
					'd_added' : gnrl._db_datetime(),
					'l_data' : {
						//'v_payment_mode' : Crypt.encrypt( v_payment_mode ),
						//'v_card_no' : Crypt.encrypt( v_card_no ),
						//'v_expiry_date' : Crypt.encrypt( v_expiry_date ),
						//'v_cvv' : Crypt.encrypt( v_cvv ),
						//'v_name_on_card' : Crypt.encrypt( v_name_on_card ),
						'transaction_id' : Crypt.encrypt( transaction_id ),
					},
				};
				Wallet.addTransaction( _ins, function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						callback( null );
					}
				});
			},
			
			// Refresh Wallet
			function( callback ){
				Wallet.refreshUserWallet( _data.user.id, function( status, data ){ 
					callback( null );
				});
			},
			
			// Send Email / SMS / Push Notification
			function( callback ){
				
				async.series([
					
					// Email
					function( callback ){
						var params = {
							_to      	: _data.user.v_email,
							_lang 		: User.lang( _data.user ),
							_key 		: 'user_add_money',
							_keywords 	: {
								'[user_name]' : _data.user.v_name,
								'[amount]' : f_amount,
								'[from]' : Wallet.getPaymentModeName( v_payment_mode ),
							},
						};
						Email.send( params, function( error_mail, error_info ){
							_data.email = {
								error_mail : error_mail, 
								error_info : error_info
							};
							callback( null );
						});
					},
					
					// SMS
					function( callback ){
						var params = {
							_to      	: _data.user.v_phone,
							_lang 		: User.lang( _data.user ),
							_key 		: 'user_add_money',
							_keywords 	: {
								'[user_name]' : _data.user.v_name,
								'[amount]' : f_amount,
								'[from]' : Wallet.getPaymentModeName( v_payment_mode ),
							},
						};
						SMS.send( params, function( error_sms, error_info ){
							_data.sms = {
								error_sms : error_sms, 
								error_info : error_info
							};
							callback( null );
						});
					},
					
					// Push Notification
					function( callback ){
						
						var params = {
							_key : 'user_add_money',
							_role : 'user',
							_tokens : [{ 
								id : _data.user.id, 
								lang : _data.user.l_data.lang,
								token : _data.user.v_device_token 
							}],
							_keywords : {
								'[user_name]' : _data.user.v_name,
								'[amount]' : f_amount,
								'[from]' : Wallet.getPaymentModeName( v_payment_mode ),
							},
							_custom_params : {},
							_need_log : 1,
						};
						
						Notification.send( params, function( err, response ){
							_data.noti = {
								err : err, 
								response : response
							};
							callback( null );
						});
						
					},
				
				], function( error, results ){
					
					callback( null );
					
				});
				
			},
			

		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_money_added', {} );
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
