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
	var v_payment_type = gnrl._is_undf( params.v_payment_type );
	var transaction_id = gnrl._is_undf( params.transaction_id );
	
	if( !f_amount.trim() ){ _status = 0; _message = 'err_req_amount'; }
	if( _status && !v_payment_type.trim() ){ _status = 0; _message = 'err_req_payment_mode'; }

	if( v_payment_type == 'payu' && !transaction_id.trim() ){
		_status = 0; _message = 'err_req_transaction_id';
	}
	
	if( _status ){

		var _user = {};
		var _wallet = {};
		var _payment_method = {};
		
		// Get Payment Method
		// Get User
		// Get Wallet
		// Add Money To Wallet
		// Refresh Wallet
		// Send 
			// Email 
			// SMS 
			// Push Notification
		
		async.series([
			
			// Get Payment Method
			function( callback ){
				dclass._select( "*", "tbl_payment_methods", " AND v_type = '"+v_payment_type+"' ", function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error' );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_invalid_payment_method', [] );
					}
					else{
						_payment_method = data[0];
						callback( null );
					}		
				});
			},
			
			// Get User
			function( callback ){
				dclass._select( "id, v_name, v_email, v_phone, v_device_token, lang", "tbl_user", " AND id = '"+login_id+"' ", function( status, data ){	
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
			
			// Get Wallet
			function( callback ){
				Wallet.get({
					user_id : login_id,
					role : 'user',
					wallet_type : 'money'
				}, function( status, wallet ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						_wallet = wallet;
						callback( null );
					}
				});
			},
			
			
			
			// Add Money To Wallet
			function( callback ){
				var _ins = {
					'i_wallet_id' 	: _wallet.id,
					'i_user_id' 	: login_id,
					'v_type' 		: 'payment_method',
					
					'f_amount' 		: f_amount,
					'd_added' 		: gnrl._db_datetime(),
					'l_data' 		: {
						'v_payment_type' : v_payment_type,
						'v_payment_name' : _payment_method.v_name,
						'v_payment_mode' : _payment_method.v_mode,
						'transaction_id' : transaction_id,
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
				Wallet.refreshWallet( _wallet.id, function( amount ){ 
					_wallet.f_amount = amount;
					callback( null );
				});
			},
			
			// Send Email / SMS / Push Notification
			function( callback ){
				
				async.series([
					
					// Email
					function( callback ){
						Email.send({
							_to      	: _user.v_email,
							_lang 		: _user.lang,
							_key 		: 'user_add_money',
							_keywords 	: {
								'[user_name]' 		: _user.v_name,
								'[amount]' 			: f_amount,
								'[from]' 			: _payment_method.v_name,
								'[balance]' 		: _wallet.f_amount,
								'[transaction_id]' 	: transaction_id,
								
							},
						}, function( error_mail, error_info ){
							callback( null );
						});
					},
					
					// SMS
					function( callback ){
						SMS.send({
							_to      	: _user.v_phone,
							_lang 		: _user.lang,
							_key 		: 'user_add_money',
							_keywords 	: {
								'[user_name]' 		: _user.v_name,
								'[amount]' 			: f_amount,
								'[from]' 			: _payment_method.v_name,
								'[balance]' 		: _wallet.f_amount,
								'[transaction_id]' 	: transaction_id,
								
							},
						}, function( error_sms, error_info ){
							callback( null );
						});
					},
					
					// Push Notification
					function( callback ){
						Notification.send({
							_key : 'user_add_money',
							_role : 'user',
							_tokens : [{ 
								id : _user.id, 
								lang : _user.lang,
								token : _user.v_device_token 
							}],
							_keywords : {
								'[user_name]' 		: _user.v_name,
								'[amount]' 			: f_amount,
								'[from]' 			: _payment_method.v_name,
								'[balance]' 		: _wallet.f_amount,
								'[transaction_id]' 	: transaction_id,
							},
							_custom_params : {},
							_need_log : 1,
						}, function( err, response ){
							callback( null );
						});
					},
				
				], function( error, results ){
					
					callback( null );
					
				});
				
			},
			

		], 
		function( error, results ){
			
			var _data = {};
			//_data._payment_method = _payment_method;
			//_data._user = _user;
			//_data._wallet = _wallet;
			
			gnrl._api_response( res, 1, 'succ_money_added', _data );
			
		});
	}
	else{
		gnrl._api_response( res, 0, _message, {} );
	}
};

module.exports = currentApi;
