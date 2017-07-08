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
	
	var wallet_type = gnrl._is_undf( params.wallet_type );
	
	wallet_type = wallet_type ? wallet_type : 'money';
	
	if( _status ){	
	
		// Get Wallet
		// Get Wallet History
		
	
		/*
		wallet{ 
		 id
		 i_user_id
		 v_type 
		 f_amount
		 i_delete
		 v_wallet_type
		}
		wallet_history[
		  id
		  i_user_id 
		  v_type
		  from
		  message
		 ]
		*/
		
		var _wallet = {};
		
		var _data = {
			'wallet_amount' : 0,
			'wallet_history' : [],
		};
		
		async.series([
		
			// Get Wallet
			function( callback ){
				Wallet.get({
					selection : 'id, f_amount',
					user_id : login_id,
					role : 'driver',
					wallet_type : wallet_type
				}, function( status, wallet ){
					_wallet = wallet;
					_data.wallet_amount = wallet.f_amount;
					callback( null );
				});
			},
			
			// Get Wallet History
			function( callback ){
				
				var _q = " SELECT ";
				_q += " v_type ";
				_q += " , f_amount ";
				_q += " , f_receivable ";
				_q += " , f_payable ";
				_q += " , f_received ";
				_q += " , COALESCE( l_data->>'ride_code', '' ) AS ride_code ";
				_q += " FROM tbl_wallet_transaction WHERE true ";
				_q += " AND i_wallet_id = '"+_wallet.id+"' ";
				_q += " ORDER BY id DESC ";
				
				dclass._query( _q, function( status, data ){
					
					if( status && data.length ){
						
						for( var k in data ){
							
							data[k].from = Wallet.getPaymentModeName( data[k].v_type );
							
							var msg = 'msg_wallet_driver_'+data[k].v_type;
							
							var s = gnrl._lbl( msg );
							if( data[k].ride_code ){
								s = s.split('[ride_code]').join( data[k].ride_code );
							}
								
							if( wallet_type == 'coupon' ){
								
								s = s.split('[amount]').join( data[k].f_amount > 0 ? data[k].f_amount : ( data[k].f_amount * -1 ) );
								
							}
							else{
								
								data[k].f_amount = data[k].f_received;
								
								s = s.split('[amount]').join( data[k].f_amount > 0 ? data[k].f_amount : ( data[k].f_amount * -1 ) );
								s = s.split('[receivable_amount]').join( data[k].f_receivable > 0 ? data[k].f_receivable : ( data[k].f_receivable * -1 ) );
								s = s.split('[payable_amount]').join( data[k].f_payable > 0 ? data[k].f_payable : ( data[k].f_payable * -1 ) );
								s = s.split('[received_amount]').join( data[k].f_received > 0 ? data[k].f_received : ( data[k].f_received * -1 ) );
								
							}
							
							data[k].message = s;
							
							delete data[k].v_type;
							delete data[k].ride_code;
							delete data[k].f_receivable;
							delete data[k].f_payable;
							delete data[k].f_received;
							delete data[k].f_amount;
							
						}
						
						_data.wallet_history = data;
						
						callback( null );
						
					}
					else{
						
						callback( null );
						
					}
				});
		
				
				
			},
			
		], function( error, results ){
			
			gnrl._api_response( res, 1, '', _data );
			
		});
		
	}
	else{
		gnrl._api_response( res, 0, '_message' );
	}
};

module.exports = currentApi;
