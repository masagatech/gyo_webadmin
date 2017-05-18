var express = require('express');
var async = require('async');


var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_wallet';
	var table2 = 'tbl_wallet_transaction';
	
	return {
		
		getPaymentModes : function(){
			var _self = this;
			return {
				'payu' : 'PayUmoney',
				
			};
		},
		
		getPaymentModeName : function( type ){
			var types = {
				'payu' : 'PayUmoney',
				'ride_cancel' : 'Ride Cancel',
				'ride_dry_run' : 'Ride Dry Run',
				'ride' : 'Ride',
			};
			return types[type];
		},
		
		get : function( user_id, type, cb ){
			
			var _self = this;
			
			dclass._select( '*', table, " AND i_user_id = '"+user_id+"' ", function( status, data ){
				if( !status ){
					cb( status, data );
				}
				else if( data.length > 0 ){
					cb( status, data[0] );
				}
				else if( !data.length ){
					var _ins = {
						'i_user_id' : user_id,
						'v_type' : type,
						'f_amount' : 0,
					};
					dclass._insert( table, _ins, function( status, temp ){
						dclass._select( '*', table, " AND i_user_id = '"+user_id+"' ", function( status, data ){
							cb( status, data[0] );
						});
					});
				}
			});
		},
		
		addTransaction : function( _ins, cb ){
			
			var _self = this;
			
			dclass._insert( table2, _ins, function( status, data ){ 
				cb( status, data );
			});
		},
		
		refreshUserWallet : function( user_id, cb ){
			
			var _self = this;
			
			dclass._select( 'COALESCE( SUM( f_amount ), 0 ) AS total', table2, " AND i_user_id = '"+user_id+"' ", function( status, wallet ){
				if( !status ){
					cb( status, 0 );
				}
				else{
					var f_amount = gnrl._round( wallet[0].total );
					var _ins = {
						'f_amount' : f_amount,
					};
					dclass._update( table, _ins, " AND i_user_id = '"+user_id+"' ", function( status, data ){
						cb( status, f_amount );
					});
				}
			});
		},
		
		refreshDriverWallet : function( user_id, cb ){
			
			var _self = this;
			
			dclass._select( 'COALESCE( SUM( f_amount ), 0 ) AS total', table2, " AND i_user_id = '"+user_id+"' ", function( status, wallet ){
				if( !status ){
					cb( status, 0 );
				}
				else{
					var f_amount = gnrl._round( wallet[0].total );
					var _q = [];
					_q.push( " UPDATE "+table+" SET f_amount = "+f_amount+" "+" WHERE i_user_id = '"+user_id+"'; " );
					_q.push(
						" UPDATE "
							+table2
						+" SET f_running_balance = "+f_amount+" "
						+" WHERE i_user_id = '"+user_id+"' "
						+" AND id = ( "
							+"( SELECT id FROM "+table2+" WHERE i_user_id = '"+user_id+"' ORDER BY id DESC LIMIT 1 )"
						+" ); "
					);
					dclass._query( _q.join(';'), function( status, data ){
						cb( status, f_amount );
					});
				}
			});
		},
		
		getWalletHistory : function( user_id, params, cb ){
			
			var _self = this;
			
			var wh = "";
			wh += " AND i_user_id = '"+user_id+"' ";
			
			if( params.from_date ){
				wh += " AND d_added >= '"+params.from_date+"' ";
			}
			if( params.to_date ){
				wh += " AND d_added <= '"+params.to_date+"' ";
			}
			wh += " ORDER BY id DESC ";
			
			dclass._select( "*", table2, wh, function( status, data ){
				
				if( status ){
					
					for( var k in data ){
						data[k].from = data[k].v_type;
						data[k].d_date = gnrl._timestamp( data[k].d_added );
						
						data[k].reason = _self.getPaymentModeName( data[k].v_type );
						
						
						if( params.role == 'user' ){
							data[k].amount = data[k].f_amount;
							if( data[k].l_data.transaction_id ){
								data[k].l_data.transaction_id = Crypt.decrypt( data[k].l_data.transaction_id );
							}
						}
						else{
							data[k].f_amount = data[k].f_received;
							data[k].amount = data[k].f_amount;
						}
						
						data[k].message = gnrl._lbl( data[k].amount > 0 ? 'msg_wallet_credit' : 'msg_wallet_debit' );
						data[k].message = data[k].message.split('[amount]').join( data[k].amount > 0 ? data[k].amount : ( data[k].amount * -1 ) );
						
					}
					
					cb( status, data );
					
				}
				else{
					
					cb( status, data );
					
				}
			});
		},
		
	}
};

module.exports = currClass;