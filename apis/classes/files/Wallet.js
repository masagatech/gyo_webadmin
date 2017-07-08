var express = require('express');
var async = require('async');


var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_wallet';
	var table2 = 'tbl_wallet_transaction';
	
	return {
		
		getPaymentModeName : function( type ){
			var types = {
				'payu' 			: 'PayUmoney',
				'ride_cancel' 	: 'Ride Cancel',
				'ride_dry_run' 	: 'Ride Dry Run',
				'ride' 			: 'Ride',
				'referral' 		: 'Referral',
			};
			return types[type];
		},
		
		get : function( params, cb ){
			
			var _self = this;
			
			var selection 	= params.selection ? params.selection : '*';
			var user_id 	= params.user_id;
			var role 		= params.role;
			var wallet_type = params.wallet_type;
			
			dclass._select( selection, table, " AND v_wallet_type = '"+wallet_type+"' AND i_user_id = '"+user_id+"' ", function( status, data ){
				if( !status ){
					cb( status, data );
				}
				else if( data.length > 0 ){
					cb( status, data[0] );
				}
				else if( !data.length ){
					var _ins = {
						'i_user_id' 	: user_id,
						'v_type' 		: role,
						'v_wallet_type' : wallet_type,
						'f_amount' 		: 0,
					};
					dclass._insert( table, _ins, function( status, temp ){
						dclass._select( selection, table, " AND i_user_id = '"+user_id+"' ", function( status, data ){
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
		
		refreshWallet : function( wallet_id, cb ){
			var _self = this;
			dclass._select( 'COALESCE( SUM( f_amount ), 0 ) AS f_amount', table2, " AND i_wallet_id = '"+wallet_id+"' ", function( status, data ){
				if( status && data.length ){
					var f_amount = gnrl._round( data[0].f_amount );
					dclass._query( " UPDATE "+table+" SET f_amount = "+f_amount+" "+" WHERE id = '"+wallet_id+"'; ", function( status, data ){
						return cb( f_amount );
					});
				}
				else{
					return cb( 0 );
				}
			});
		},
		
		getWalletHistory : function( user_id, params, cb ){
			
			var _self = this;
			
			var wallet_type = params.wallet_type;
			
			var wh = "";
			wh += " AND i_user_id = '"+user_id+"' ";
			
			if( params.from_date ){
				wh += " AND d_added >= '"+params.from_date+"' ";
			}
			if( params.to_date ){
				wh += " AND d_added <= '"+params.to_date+"' ";
			}
			if( wallet_type ){
				wh += ( " AND i_wallet_id IN ( SELECT id FROM "+table+" WHERE v_wallet_type = '"+wallet_type+"' AND i_user_id = '"+user_id+"' ) " );
			}
			wh += " ORDER BY id DESC ";
			
			dclass._select( "*", table2, wh, function( status, data ){
				
				if( status ){
					
					for( var k in data ){
						
						data[k].from = data[k].v_type;
						data[k].d_date = gnrl._timestamp( data[k].d_added );
						data[k].reason = _self.getPaymentModeName( data[k].v_type );
						
						var msg = 'msg_wallet_'+params.role+'_'+data[k].v_type;
						
						if( data[k].v_type == 'payment_method' ){
							msg = 'msg_wallet_payment_method';
							data[k].reason = data[k].l_data.v_payment_name;
						}
						
						if( params.role == 'user' || wallet_type == 'coupon' ){
							
							data[k].amount = data[k].f_amount;
							if( data[k].l_data.transaction_id ){
								data[k].l_data.transaction_id = data[k].l_data.transaction_id;
							}
							var s = gnrl._lbl( msg );
							s = s.split('[amount]').join( data[k].amount > 0 ? data[k].amount : ( data[k].amount * -1 ) );
							if( data[k].l_data.ride_code ){
								s = s.split('[ride_code]').join( data[k].l_data.ride_code );
							}
							if( data[k].v_type == 'payment_method' ){
								s = s.split('[payment_method]').join( data[k].l_data.v_payment_name );
							}
							
							data[k].message = s;
							
						}
						else{
							data[k].f_amount = data[k].f_received;
							data[k].amount = data[k].f_amount;
								
							var s = gnrl._lbl( msg );
							
							s = s.split('[receivable_amount]').join( data[k].f_receivable > 0 ? data[k].f_receivable : ( data[k].f_receivable * -1 ) );
							s = s.split('[payable_amount]').join( data[k].f_payable > 0 ? data[k].f_payable : ( data[k].f_payable * -1 ) );
							s = s.split('[received_amount]').join( data[k].f_received > 0 ? data[k].f_received : ( data[k].f_received * -1 ) );
							s = s.split('[amount]').join( data[k].amount > 0 ? data[k].amount : ( data[k].amount * -1 ) );
							if( data[k].l_data.ride_code ){
								s = s.split('[ride_code]').join( data[k].l_data.ride_code );
							}
							if( data[k].v_type == 'payment_method' ){
								s = s.split('[payment_method]').join( data[k].l_data.v_payment_name );
							}
							
							data[k].message = s;
							
						}
						
						data[k].msg = msg;
						data[k].from = data[k].reason;
						
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
