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
	
	var login_id   = gnrl._is_undf( params.login_id ).trim();
	var from_date   = gnrl._is_undf( params.from_date, 0 );
	var to_date   = gnrl._is_undf( params.to_date, 0 );

	if( !from_date ){ _status = 0; _message = 'err_req_from_date'; }
	if( _status && !to_date ){ _status = 0; _message = 'err_req_to_date'; }
	
	if( _status ){	

		var _wallet = [];
		var _wallet_history = [];
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
				
				from_date = parseFloat( from_date );
				to_date = parseFloat( to_date );
				
				from_date = gnrl._db_ymd( '', new Date( from_date ) );
				to_date = gnrl._db_ymd( '', new Date( to_date ) );
				
				
				Wallet.getWalletHistory( login_id, {
					role : 'driver',
					lang : _lang,
					from_date : from_date,
					to_date : to_date,
					
				}, function( status, wallet_history ){
					
					for( var i = 0; i < wallet_history.length; i++ ){
						wallet_history[i].details = wallet_history[i].l_data;
						wallet_history[i].details.ride_date = wallet_history[i].d_date;
						wallet_history[i].details.i_ride_id = 0;
						wallet_history[i].details.vehicle_type = '';
						wallet_history[i].details.action = '';
						wallet_history[i].details.action_amount = '';
						wallet_history[i].details.balance = '';
					}
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
