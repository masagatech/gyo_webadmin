var express = require('express');
var nodemailer = require('nodemailer');
var async = require('async');
var CryptoJS = require('crypto-js');

var crypto = require('crypto');

var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	return {
		
		makeHash : function( str, key ){
			
			var _self = this;
			
			var hash = crypto.createHash( 'sha512', key );
			
			return hash.update( str ).digest( 'hex' ).toLowerCase();
			
		},
		
		getHashes : function( params, cb ){
			
			var _self = this;
			
			var _row = {
				"payment_hash" : "",
				"get_merchant_ibibo_codes_hash" : "",
				"vas_for_mobile_sdk_hash": "",
				"payment_related_details_for_mobile_sdk_hash": "",
				"verify_payment_hash": "",
				"delete_user_card_hash": "",
				"get_user_cards_hash": "",
				"edit_user_card_hash": "",
				"save_user_card_hash": "",
				"send_sms_hash": ""
			};
		
			var key 	= '9Dz64u';
			var salt 	= 'SybIl3mO';
			
			var key 	= '';
			var salt 	= '';
			
			var txnid = gnrl._is_undf( params.txnid, '' );
			var amount = gnrl._is_undf( params.amount, '' );
			var productinfo = gnrl._is_undf( params.productinfo, '' );
			var firstname = gnrl._is_undf( params.firstname, '' );
			var email = gnrl._is_undf( params.email, '' );
			var user_credentials = gnrl._is_undf( params.user_credentials, '' );
			var udf1 = gnrl._is_undf( params.udf1, '' );
			var udf2 = gnrl._is_undf( params.udf2, '' );
			var udf3 = gnrl._is_undf( params.udf3, '' );
			var udf4 = gnrl._is_undf( params.udf4, '' );
			var udf5 = gnrl._is_undf( params.udf5, '' );
			var offerKey = gnrl._is_undf( params.offerKey, '' );
			var cardBin = gnrl._is_undf( params.cardBin, '' );
			
			async.series([
			
				// Get Key Salt
				function( callback ){
					
					var _selection = " id ";
					_selection += " , v_mode ";
					_selection += " , l_data ";
					
					dclass._select( _selection, "tbl_payment_methods", "  AND v_type = 'payubiz' AND i_delete = '0' AND e_status = 'active' ", function( status, data ){
						if( status && data.length ){
							data[0].l_data = data[0].l_data[ data[0].v_mode ];
							key = data[0].l_data.key;
							salt = data[0].l_data.salt;
					 	}
						callback( null );
					});
					
				},
				
				// Check Key Salt
				function( callback ){
					if( key && salt ){
						callback( null );
					}
					else{
						return cb( _row );
					}
				},
				
				
				function( callback ){
					
					// Payment Hash
					var str = key + '|' + txnid + '|' + amount + '|' + productinfo + '|' + firstname + '|' + email + '|' + udf1 + '|' + udf2 + '|' + udf3 + '|' + udf4 + '|' + udf5 + '||||||' + salt;
					_row.payment_hash = _self.makeHash( str, key );
					
					// Get Merchant Ibibo Codes Hash
					var str = key + '|' + 'get_merchant_ibibo_codes' + '|default|' + salt;
					_row.get_merchant_ibibo_codes_hash = _self.makeHash( str, key );
					
					// Vas For Mobile sdk
					var str = key + '|' + 'vas_for_mobile_sdk' + '|default|' + salt;
					_row.vas_for_mobile_sdk_hash = _self.makeHash( str, key );
					
					// Payment Related Details For Mobile sdk
					var str = key + '|' + 'payment_related_details_for_mobile_sdk' + '|default|' + salt;
					_row.payment_related_details_for_mobile_sdk_hash = _self.makeHash( str, key );
					
					// Verify Payment - used for verifying payment(optional)
					var str = key + '|' + 'verify_payment' + '|' + txnid + '|' + salt;
					_row.verify_payment_hash = _self.makeHash( str, key );
					
					if( user_credentials ){
						
						// Delete User Card
						var str = key + '|' + 'delete_user_card' + '|' + user_credentials + '|' + salt;
						_row.delete_user_card_hash = _self.makeHash( str, key );
						
						// Get User Card
						var str = key + '|' + 'get_user_cards' + '|' + user_credentials + '|' + salt;
						_row.get_user_cards_hash = _self.makeHash( str, key );
						
						// Edit User Card
						var str = key + '|' + 'edit_user_card' + '|' + user_credentials + '|' + salt;
						_row.edit_user_card_hash = _self.makeHash( str, key );
						
						// Save User Card
						var str = key + '|' + 'save_user_card' + '|' + user_credentials + '|' + salt;
						_row.save_user_card_hash = _self.makeHash( str, key );
						
						// Payment related details for mobile sdk
						var str = key + '|' + 'payment_related_details_for_mobile_sdk' + '|' + user_credentials + '|' + salt;
						_row.payment_related_details_for_mobile_sdk_hash = _self.makeHash( str, key );
						
					}
					
					// Send SMS
					var str = key + '|' + 'send_sms' + '|' + udf3 + '|' + salt;
					_row.send_sms_hash = _self.makeHash( str, key );
					
					// Check offer status hash
					if( offerKey ){
						var str = key + '|' + 'check_offer_status' + '|' + offerKey + '|' + salt;
						_row.check_offer_status_hash = _self.makeHash( str, key );
					}
					
					// Check isDomestic hash
					if( cardBin ){
						var str = key + '|' + 'check_isDomestic' + '|' + cardBin + '|' + salt;
						_row.check_isDomestic_hash = _self.makeHash( str, key );
					}
					
					callback( null );
				},
				
			], 
			function( error, results ){
				
				return cb( _row );
				
			});
			
		},
		
	}
};

module.exports = currClass;
