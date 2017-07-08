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
	
	var login_id   = gnrl._is_undf( params.login_id );
	var from_date   = gnrl._is_undf( params.from_date, 0 );
	var to_date   = gnrl._is_undf( params.to_date, 0 );

	if( !from_date ){ _status = 0; _message = 'err_req_from_date'; }
	if( _status && !to_date ){ _status = 0; _message = 'err_req_to_date'; }
	
	if( _status ){	
	
		var wallet_type = 'money';
		
		var _wallet = {};
		var _wallet_history = [];
		var _data = {
			'wallet_history' : [],
		};
		
		/*
			>> Get Wallet
			>> Get Wallet History
		*/
		
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
					callback( null );
				});
			},
		
			// Get Wallet History
			function( callback ){
				
				from_date = parseFloat( from_date );
				to_date = parseFloat( to_date );
				
				from_date = gnrl._db_ymd( '', new Date( from_date ) );
				to_date = gnrl._db_ymd( '', new Date( to_date ) );
				
				
				var _q = " SELECT ";
				_q += " v_type ";
				_q += " , f_amount ";
				_q += " , f_receivable ";
				_q += " , f_payable ";
				_q += " , f_received ";
				_q += " , COALESCE( l_data->>'ride_id', '' ) AS ride_id ";
				_q += " , COALESCE( l_data->>'ride_code', '' ) AS ride_code ";
				_q += " , COALESCE( l_data->>'vehicle_type', '' ) AS vehicle_type ";
				_q += " , d_added ";
				
				_q += " FROM tbl_wallet_transaction WHERE true ";
				_q += " AND i_wallet_id = '"+_wallet.id+"' ";
				
				if( from_date ){
					_q += " AND d_added >= '"+from_date+"' ";
				}
				if( to_date ){
					_q += " AND d_added <= '"+to_date+"' ";
				}
				
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
								
							data[k].f_amount = data[k].f_received;
							
							s = s.split('[amount]').join( data[k].f_amount > 0 ? data[k].f_amount : ( data[k].f_amount * -1 ) );
							s = s.split('[receivable_amount]').join( data[k].f_receivable > 0 ? data[k].f_receivable : ( data[k].f_receivable * -1 ) );
							s = s.split('[payable_amount]').join( data[k].f_payable > 0 ? data[k].f_payable : ( data[k].f_payable * -1 ) );
							s = s.split('[received_amount]').join( data[k].f_received > 0 ? data[k].f_received : ( data[k].f_received * -1 ) );
							
							data[k].message = s;
							data[k].action_amount = data[k].f_amount > 0 ? data[k].f_amount : ( data[k].f_amount * -1 );
							if( data[k].v_type == 'ride' ){
								data[k].action_amount = data[k].f_receivable - data[k].f_payable;
							}
							
							_data.wallet_history.push({
								"from" : data[k].from,
								"message" : s,
								"details": {
									i_ride_id : data[k].ride_code,
									ride_date : gnrl._timestamp( data[k].d_added ),
									vehicle_type : '',
									action : data[k].v_type,
									action_amount : data[k].action_amount,
									balance : _wallet.f_amount,
								}
							});
						}
						
						callback( null );
						
					}
					else{
						
						callback( null );
						
					}
				});
				
			},
			
		], 
		function( error, results ){
			gnrl._api_response( res, 1, '', _data );
		});
		
		
		
		
	}
	else{
		gnrl._api_response( res, 0, '_message' );
	}
};

module.exports = currentApi;
