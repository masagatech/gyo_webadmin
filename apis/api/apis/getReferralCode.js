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
	
	var login_id = gnrl._is_undf( params.login_id ).trim();	
	
	if( _status ){	
	
		var data = {
			'earn_money' : 0,
			'v_referral_code' : gnrl._lbl('msg_refer_code'),
			'message' : gnrl._lbl('msg_refer_code_string_off'),
		};
		
		async.series([
			
			// Get Amount
			function( callback ){
				Settings.get( 'REFER_AMOUNT', function( val ){
					data.earn_money = parseFloat( val ? val : 0 );
					data.message = gnrl._lbl('msg_refer_code_string_on').split('[amount]').join( data.earn_money );
					callback( null );
				});
			},
			
			// Get User
			function( callback ){
				if( data.earn_money > 0 ){
					User.getMyReferralCode( login_id, data.earn_money, function( v_referral_code ){
						data.v_referral_code = v_referral_code;
						callback( null );
					});
				}
				else{
					callback( null );
				}
			},
			
		], function( error, results ){
			gnrl._api_response( res, 1, '', data );
		});
	}
	else{
		gnrl._api_response( res, 0, _message, {} );
	}
};

module.exports = currentApi;
