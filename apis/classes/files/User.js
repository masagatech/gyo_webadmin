var express = require('express');
var async = require('async');


var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_user';
	
	
	return {
		
		get : function( param, cb ){
			var _self = this;
			dclass._select( '*', table, " AND id = '"+param+"' ", function( status, data ){
				cb( status, data );
			});
		},
		
		
		startLog : function( user_id, role, type, cb ){
			var _self = this;
			var _ins = {
				'i_user_id' : user_id,
				'v_role' : role,
				'v_type' : type,
				'd_loged_in' : gnrl._db_datetime(),
			};
			dclass._insert( 'tbl_user_log', _ins, function( status, data ){
				cb( status, data );
			});
		},
		
		finishLog : function( user_id, role, type, cb ){
			var _self = this;
			var _q = " UPDATE tbl_user_log ";
			_q += " SET d_loged_out = '"+gnrl._db_datetime()+"' ";
			_q += " WHERE id = ( ";
			_q += " SELECT id FROM tbl_user_log WHERE v_role = '"+role+"' AND v_type = '"+type+"' AND i_user_id = '"+user_id+"' ORDER BY id DESC LIMIT 1 ";
			_q += " ) AND d_loged_out IS NULL ";
			dclass._query( _q, function( status, data ){
				cb( status, data );
			});
		},
		
		runReferralModule : function( params, cb ){
			
			var user_id					= params.user_id;
			var user_name				= params.user_name;
			
			var referral_user 			= {};
			var referral_code 			= params.referral_code;
			var referral_amount 		= parseFloat( params.referral_amount );
			var referral_user_id 		= parseInt( params.referral_user_id );
			var referral_wallet_type 	= params.referral_wallet_type;
			
			var wallet_amount = 0;
			
			if( !( referral_code && referral_user_id && referral_amount ) ){
				return cb( 0, {} );
			}
			
			async.series([
				
				// Get Referral User
				function( callback ){
					var _selection = "v_name, v_email, v_phone, v_role, lang";
					dclass._select( _selection, 'tbl_user', " AND id = '"+referral_user_id+"' ", function( status, data ){
						referral_user = data[0];
						callback( null );
					});
				},
				
				
				// Get Referral Wallet
				function( callback ){
					Wallet.get({
						'selection'		: 'id',
						'user_id' 		: referral_user_id,
						'role' 			: referral_user.v_role,
						'wallet_type' 	: referral_wallet_type,
					}, function( status, wallet ){
						referral_user.wallet_id = wallet.id;
						callback( null );
					});
				},
				
				
				// Add To Referral Wallet
				function( callback ){
					
					if( referral_user.v_role == 'driver' && referral_wallet_type == 'money' ){
						
						var _ins = {
							'i_wallet_id' 		: referral_user.wallet_id,
							'i_user_id' 		: referral_user_id,
							'v_type' 			: 'referral',
							
							'f_receivable' 		: gnrl._round( referral_amount ),
							'f_payable' 		: 0,
							'f_received' 		: gnrl._round( referral_amount ),
							
							'f_amount' 			: gnrl._round( referral_amount ),
							
							'd_added' 			: gnrl._db_datetime(),
							'l_data' 			: {
								'referred_user_id' 		: user_id,
								'referred_user_name' 	: user_name,
							},
						};
						
					}
					else{
						
						var _ins = {
							'i_wallet_id' 		: referral_user.wallet_id,
							'i_user_id' 		: referral_user_id,
							'v_type' 			: 'referral',
							'f_amount' 			: referral_amount,
							'd_added' 			: gnrl._db_datetime(),
							'l_data' 			: {
								'referred_user_id' 		: user_id,
								'referred_user_name' 	: user_name,
							},
						};
					}
					
					Wallet.addTransaction( _ins, function( status, data ){ 
						callback( null );
					});
					
				},
				
				
				
				// Refresh Wallet
				function( callback ){
					Wallet.refreshWallet( referral_user.wallet_id, function( amount ){ 
						wallet_amount = amount;
						callback( null );
					});
				},
				
				
				// Send SMS 
				function( callback ){
					SMS.send({
						_to : referral_user.v_phone,
						_lang : referral_user.lang,
						_key : 'user_add_money',
						_keywords : {
							'[user_name]' : referral_user.v_name,
							'[amount]' 	: referral_amount,
							'[from]' : Wallet.getPaymentModeName( 'referral' ),
							//'[balance]' : wallet_amount,
							//'[transaction_id]' : '-',
						},
					}, function( error_sms, error_info ){
						callback( null );
					});
				},
				
				// Send Email 
				function( callback ){
					Email.send({
						_to : referral_user.v_email,
						_lang : referral_user.lang,
						_key : 'user_add_money',
						_keywords : {
							'[user_name]' : referral_user.v_name,
							'[amount]' 	: referral_amount,
							'[from]' : Wallet.getPaymentModeName( 'referral' ),
							//'[balance]' : wallet_amount,
							//'[transaction_id]' 	: '-',
						},
					}, function( error_mail, error_info ){
						callback( null );
					});
				},
				
				// Update Current User
				function( callback ){
					var _ins = [
						" l_data = l_data || '"+gnrl._json_encode({
							'referral_code' : '',
						})+"' "
					];
					dclass._updateJsonb( 'tbl_user', _ins, " AND id = '"+user_id+"' ", function( status, data ){ 
					
						callback( null );
						
					});
				},
				
			], function( error, results ){
				
				return cb( 1, {} );
				
			});
			
		},
		
	}
};

module.exports = currClass;
