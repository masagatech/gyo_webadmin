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
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	
	if( _status ){	
		
		var _wh = {
			'wallet' : {},
			'wallet_amount' : 0,
			'wallet_history' : [],
		};
		
		/*
			>> Get Wallet
			>> Get Wallet History
		*/

		async.series([
		
			// Get Wallet
			function( callback ){
				Wallet.get( login_id, 'driver', function( status, wallet ){
					_wh.wallet = wallet;
					_wh.wallet_amount = wallet.f_amount;
					callback( null );
				});
			},
			
			// Get Wallet History
			function( callback ){
				Wallet.getWalletHistory( login_id, {
					role : 'driver',
					lang : _lang
				}, function( status, wallet_history ){
					_wh.wallet_history = wallet_history;
					callback( null );
				});
			},
			
		], 
		function( error, results ){
			gnrl._api_response( res, 1, '', _wh );
		});
		
	}
	else{
		gnrl._api_response( res, 0, '_message' );
	}
};

module.exports = currentApi;
