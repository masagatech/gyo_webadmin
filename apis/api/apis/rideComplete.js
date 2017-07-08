var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var async = require('async');





var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status = 1;
	var _message = '';
	var _response = {};
	var login_id = gnrl._is_undf( params.login_id );
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	var force_close = parseInt( gnrl._is_undf( params.force_close, 0 ) );
	var estimate_km = parseInt( gnrl._is_undf( params.estimate_km, 0 ) );
	var estimate_dry_run = gnrl._is_undf( params.estimate_dry_run, 0 );
	
	if( force_close == 1 && estimate_km <= 0 ){
		_status = 0; _message = 'err_req_estimate_km';
	}
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var end_date 		= gnrl._db_datetime();
		var _data 			= {};
		var _ride 			= {};
		var l_data	 		= {};
		var multi_Queries 	= [];
		
		/*
		STEPS
		
			// Get Ride
			// Ride Set Charges
			// Calculate Total Time
			// Calculate Total Distance
			// Calculate Different Charges
				// Dry Run
				// Other Charges
				// Min Charge
				// Base Fare
				// Ride Time Charge
				// Total Fare
				// Service Tax
				// Surcharge
				// Discount
				// Calculate Company Comission
			
			// Get User Wallet
			// Get Driver Wallet
			// Update Ride To Complete
			
			// Fire All Queries
			
			// Ride Complete Notification
				// To User
				// To Driver - Not Using
			
			// User # Wallet Actions [Cut From Wallet, If Pay From Wallet]
				// Add Transaction
				// Refresh
				// Send Notification
				
			// Driver # Wallet Actions [Ride Money Add To Wallet, If Dry Run]
				// Add Transaction For Ride Money
				// Add Transaction For Dry Run
				// Refresh
				// Send Notification, Driver = Wallet Get Payment
				// Send Notification, Driver = Wallet Get Dry Run
				
		*/
		
		var dTest = 0;
		
		async.series([
		
			// Get Ride
			function( callback ){
				
				var _q = "SELECT ";
				
				_q += " dr.id AS driver_id ";
				_q += " , dr.v_name AS driver_name ";
				_q += " , dr.v_email AS driver_email ";
				_q += " , dr.v_phone AS driver_phone ";
				_q += " , dr.v_device_token AS driver_device_token ";
				_q += " , COALESCE( dr.l_data->>'referral_code', '' ) AS driver_referral_code ";
				_q += " , COALESCE( ( dr.l_data->>'referral_amount' )::numeric, 0 ) AS driver_referral_amount ";
				_q += " , COALESCE( ( dr.l_data->>'referral_user_id' )::numeric, 0 ) AS driver_referral_user_id ";
				_q += " , COALESCE( dr.l_data->>'referral_wallet_type', '' ) AS driver_referral_wallet_type ";
				_q += " , COALESCE( dr.l_data->>'referral_wallet_apply', '' ) AS driver_referral_wallet_apply ";
				_q += " , dr.lang AS driver_lang ";
				
				_q += " , ur.id AS user_id ";
				_q += " , ur.v_name AS user_name ";
				_q += " , ur.v_email AS user_email ";
				_q += " , ur.v_phone AS user_phone ";
				_q += " , ur.v_device_token AS user_device_token ";
				_q += " , COALESCE( ur.l_data->>'referral_code', '' ) AS user_referral_code ";
				_q += " , COALESCE( ( ur.l_data->>'referral_amount' )::numeric, 0 ) AS user_referral_amount ";
				_q += " , COALESCE( ( ur.l_data->>'referral_user_id' )::numeric, 0 ) AS user_referral_user_id ";
				_q += " , COALESCE( ur.l_data->>'referral_wallet_type', '' ) AS user_referral_wallet_type ";
				_q += " , COALESCE( ur.l_data->>'referral_wallet_apply', '' ) AS user_referral_wallet_apply ";
				_q += " , ur.lang AS user_lang ";
				
				_q += " , rd.* ";
				
				_q += " FROM tbl_ride rd ";
				
				_q += " LEFT JOIN tbl_user ur ON ur.id = rd.i_user_id ";
				_q += " LEFT JOIN tbl_user dr ON dr.id = rd.i_driver_id ";
				
				_q += " WHERE true ";
				_q += " AND rd.id = '"+i_ride_id+"' ";
				_q += " AND dr.id = '"+login_id+"'; ";
				
				dclass._query( _q, function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else if( data[0].e_status == 'complete' ){
						gnrl._api_response( res, 0, 'err_msg_ride_alreay_completed', {} );
					}
					else{
						_ride = data[0];
						l_data = data[0].l_data;
						callback( null );
					}
				});
				
			},
			
			// Ride Set Charges
			function( callback ){
				Ride.getChargesData( _ride.l_data, function( data ){
					l_data = data;
					callback( null );
				});
			},
			
			// Calculate Total Time
			function( callback ){
				
				var temp = gnrl._dateDiff( _ride.d_start, end_date );
				var mins = 0;
				
				mins += ( temp.days * 60 * 60 );
				mins += ( temp.hours * 60 );
				mins += ( temp.minutes );
				mins += ( temp.seconds > 30 ? 1 : 0.5 );
				
				l_data.trip_time = temp;
				l_data.trip_time_in_min = mins;
				
				callback( null );
			},
			
			// Calculate Total Distance
			function( callback ){
				if( force_close == 1 ){
					l_data.actual_distance 	= estimate_km;
					l_data.actual_dry_run 	= estimate_dry_run;
					callback( null );
				}
				else{
					Ride.calculateDistances( i_ride_id, function( status, data ){
						l_data.actual_distance	= data.actual_distance;
						l_data.actual_dry_run 	= data.actual_dry_run;
						callback( null );
					});	
				}
			},
			
			/*// Testing Data
			function( callback ){
				if( dTest ){
					l_data.actual_distance 	= 5;
					l_data.actual_dry_run 	= 3;
					l_data.charges.promocode_code_discount = '5%';
					l_data.charges.promocode_code_discount_upto = 10;
					l_data.trip_time_in_min = 20;
				}
				callback( null );
			},*/
			
			// Calculate Different Charges
			function( callback ){
				
				async.series([
				
					// Dry Run
					function( callback ){
						
						if( l_data.actual_dry_run > 0 ){
							
							var dryCharge 	= l_data.charges.max_dry_run_charge;
							var dryRun 		= ( l_data.actual_dry_run <= l_data.charges.max_dry_run_km ) ? l_data.actual_dry_run : l_data.charges.max_dry_run_km;
							
							l_data.apply_dry_run = dryRun;
							l_data.apply_dry_run_amount = gnrl._round( dryRun * dryCharge );
							
							callback( null );
						}
						else{
							callback( null );
						}
						
					},
					
					// Other Charges
					function( callback ){
						l_data.final_amount += l_data.charges.other_charge;
						callback( null );
					},
					
					// Min Charge
					function( callback ){
						
						var chrg = gnrl._round( l_data.charges.min_charge );
						
						l_data.final_amount += chrg;
						
						var _q = " INSERT INTO tbl_ride_charges ( i_ride_id, v_charge_type, f_amount, d_added, l_data ) VALUES ";
						_q += " ( "+i_ride_id+", 'min_charge', "+chrg+", '"+gnrl._db_datetime()+"', '"+gnrl._json_encode({
							'i_added_by' : login_id,
							'v_charge_info' : '',
						})+"' ); ";
						
						multi_Queries.push( _q );
						
						callback( null );
						
					},
					
					// Base Fare
					function( callback ){
						
						var chrg = gnrl._round( l_data.charges.base_fare );
						
						l_data.final_amount += chrg;
						
						var _q = " INSERT INTO tbl_ride_charges ( i_ride_id, v_charge_type, f_amount, d_added, l_data ) VALUES ";
						_q += " ( "+i_ride_id+", 'base_fare', "+chrg+", '"+gnrl._db_datetime()+"', '"+gnrl._json_encode({
							'i_added_by' : login_id,
							'v_charge_info' : '',
						})+"' ); ";
						
						multi_Queries.push( _q );
						
						callback( null );
						
					},
					
					// Ride Time Charge
					function( callback ){
						
						var chrg = gnrl._round( l_data.charges.ride_time_charge );
						
						if( chrg > 0 ){
							
							chrg = gnrl._round( chrg * l_data.trip_time_in_min );
							
							l_data.final_amount += chrg;
							
							var _q = " INSERT INTO tbl_ride_charges ( i_ride_id, v_charge_type, f_amount, d_added, l_data ) VALUES ";
							_q += " ( "+i_ride_id+", 'ride_time_charge', "+chrg+", '"+gnrl._db_datetime()+"', '"+gnrl._json_encode({
								'i_added_by' : login_id,
								'v_charge_info' : '',
							})+"' ); ";
							
							multi_Queries.push( _q );
							
						}
							
						callback( null );
						
					},
					
					// Total Fare
					function( callback ){
						
						var chrg = 0;
						
						if( l_data.actual_distance <= l_data.charges.upto_km ){ 
							chrg = ( l_data.charges.upto_km_charge * l_data.actual_distance );
						}
						else{
							chrg = ( l_data.charges.upto_km_charge * l_data.charges.upto_km );
							chrg += ( l_data.charges.after_km_charge * ( l_data.actual_distance - l_data.charges.upto_km ) );
						}
						
						chrg = chrg > 0 ? gnrl._round( chrg ) : 0;
						
						if( chrg > 0 ){
							
							l_data.final_amount += chrg;
							
							var _q = " INSERT INTO tbl_ride_charges ( i_ride_id, v_charge_type, f_amount, d_added, l_data ) VALUES ";
							_q += " ( "+i_ride_id+", 'total_fare', "+chrg+", '"+gnrl._db_datetime()+"', '"+gnrl._json_encode({
								'i_added_by' : login_id,
								'v_charge_info' : '',
							})+"' ); ";
							
							multi_Queries.push( _q );
							
						}
							
						callback( null );
						
					},
					
					// Service Tax
					function( callback ){
				
						var tempTotal = l_data.final_amount;
						
						var chrg = l_data.charges.service_tax;
						
						if( chrg ){
							
							chrg = gnrl._round( parseFloat( ( tempTotal * chrg ) / 100 ) );
							
							l_data.final_amount += chrg;
							
							var _q = " INSERT INTO tbl_ride_charges ( i_ride_id, v_charge_type, f_amount, d_added, l_data ) VALUES ";
							_q += " ( "+i_ride_id+", 'service_tax', "+chrg+", '"+gnrl._db_datetime()+"', '"+gnrl._json_encode({
								'i_added_by' : login_id,
								'v_charge_info' : '',
							})+"' ); ";
							
							multi_Queries.push( _q );
							
						}
						
						callback( null );
						
					},
					
					// Surcharge
					function( callback ){
				
						var tempTotal = l_data.final_amount;
						
						var chrg = l_data.charges.surcharge;
						
						if( chrg ){
							
							chrg = gnrl._round( parseFloat( ( tempTotal * chrg ) / 100 ) );
							
							l_data.final_amount += chrg;
							
							var _q = " INSERT INTO tbl_ride_charges ( i_ride_id, v_charge_type, f_amount, d_added, l_data ) VALUES ";
							_q += " ( "+i_ride_id+", 'surcharge', "+chrg+", '"+gnrl._db_datetime()+"', '"+gnrl._json_encode({
								'i_added_by' : login_id,
								'v_charge_info' : '',
							})+"' ); ";
							
							multi_Queries.push( _q );
							
						}
						
						callback( null );
						
					},
					
					// Discount
					function( callback ){
						
						if( l_data.charges.promocode_code_discount ){
							
							var tempTotal = l_data.final_amount;
							
							var tempDiscount = gnrl._isPercent( tempTotal, l_data.charges.promocode_code_discount );
							
							var chrg = gnrl._round( tempDiscount.comm_amount );
							
							if( chrg > l_data.charges.promocode_code_discount_upto ){
								chrg = l_data.charges.promocode_code_discount_upto;
							}
							
							chrg = gnrl._minus( chrg );
							
							l_data.final_amount += chrg;
							
							var _q = " INSERT INTO tbl_ride_charges ( i_ride_id, v_charge_type, f_amount, d_added, l_data ) VALUES ";
							_q += " ( "+i_ride_id+", 'discount', "+chrg+", '"+gnrl._db_datetime()+"', '"+gnrl._json_encode({
								'i_added_by' : login_id,
								'v_charge_info' : '',
							})+"' ); ";
							
							multi_Queries.push( _q );
							
						}
						
						l_data.final_amount = gnrl._round( l_data.final_amount );
						
						callback( null );
						
					},
					
					// Calculate Company Comission
					function( callback ){
						
						var chrg = gnrl._round( gnrl._calc_commision( l_data.final_amount, l_data.charges.company_commission ) );
						
						l_data.company_commision_amount = chrg;
						l_data.ride_driver_payable 		= chrg;
						l_data.ride_driver_receivable 	= gnrl._round( l_data.final_amount - chrg );
						
						callback( null );
					},
					
					
				], function( error, results ){
					
					callback( null );
					
				});
				
			},
			
			// Get User Wallet
			function( callback ){
				Wallet.get({
					selection : 'id, f_amount',
					user_id : _ride.user_id,
					role : 'user',
					wallet_type : 'money'
				}, function( status, _wallet ){
					
					_data._user_wallet = _wallet;
					
					var byCash = 0;
					var byWallet = 0;
					
					if( _wallet.f_amount <= 0 ){
						byCash = l_data.final_amount;
					}
					else if( l_data.final_amount <= _wallet.f_amount ){
						byWallet = l_data.final_amount;
					}
					else{
						byWallet 	= _wallet.f_amount;
						byCash 		= l_data.final_amount - byWallet;
					}
					
					byCash = gnrl._round( byCash );
					byWallet = gnrl._round( byWallet );
					
					if( byWallet > 0 ){
						
						l_data.ride_paid_by_wallet = byWallet;
						
						// Add Wallet Payment
						var _q = " INSERT INTO tbl_ride_payments ( i_ride_id, v_type, f_amount, d_added, i_success, l_data ) VALUES ";
						_q += " ( "+i_ride_id+", 'wallet', "+byWallet+", '"+gnrl._db_datetime()+"', 1, '"+gnrl._json_encode({})+"' ); ";
						multi_Queries.push( _q );
						
					}
					
					if( byCash > 0 ){
						
						l_data.ride_paid_by_cash = byCash;
						l_data.ride_driver_received = byCash;
						
						// Add Cash Payment
						var _q = " INSERT INTO tbl_ride_payments ( i_ride_id, v_type, f_amount, d_added, i_success, l_data ) VALUES ";
						_q += " ( "+i_ride_id+", 'cash', "+byCash+", '"+gnrl._db_datetime()+"', 0, '"+gnrl._json_encode({})+"' ); ";
						
						multi_Queries.push( _q );
					}
					
					callback( null );
					
				});
			},
			
			// Get Driver Wallet
			function( callback ){
				Wallet.get({
					selection : 'id, f_amount',
					user_id : _ride.driver_id,
					role : 'driver',
					wallet_type : 'money'
				}, function( status, _wallet ){
					_data._driver_wallet = _wallet;
					callback( null );
				});
			},
			
			// Update Ride To Complete
			function( callback ){
				
				var _q = " UPDATE tbl_ride SET ";
				_q += " e_status = 'complete' ";
				_q += " , d_end = '"+end_date+"' ";
				_q += " , l_data = l_data || '"+gnrl._json_encode( l_data )+"' ";
				_q += " WHERE id = '"+i_ride_id+"'; ";
				
				multi_Queries.push( _q );
				
				callback( null );
				
			},
			
			// Fire All Queries
			function( callback ){
				dclass._query( multi_Queries.join(''), function( status, data ){
					callback( null );
				});
			},
			
			// Ride Complete Notification
			function( callback ){
				
				async.series([
					
					// To User
					function( callback ){
						Notification.send({
							_key : 'user_ride_complete',
							_role : 'user',
							_tokens : [{
								'id' : _ride.user_id,
								'lang' : _ride.user_lang,
								'token' : _ride.user_device_token,
							}],
							_keywords : {},
							_custom_params : {
								i_ride_id : i_ride_id,
								ride_code : _ride.v_ride_code,
							},
							_need_log : 0,
						}, function( err, response ){
							callback( null );
						});
					},
					
					/*
					// To Driver - Not Using
					function( callback ){
						Notification.send( {
							_key : 'driver_ride_complete',
							_role : 'driver',
							_tokens : [{
								'id' : _ride.driver_id,
								'lang' : _ride.driver_lang,
								'token' : _ride.driver_device_token,
							}],
							_keywords : {},
							_custom_params : {
								i_ride_id : i_ride_id,
								ride_code : _ride.v_ride_code,
							},
							_need_log : 0,
						}, function( err, response ){
							callback( null );
						});
					},
					*/
				
				], function( error, results ){
					
					callback( null );
					
				});
				
				
			},
			
			// User # Wallet Actions [Cut From Wallet, If Pay From Wallet]
			function( callback ){
				if( l_data.ride_paid_by_wallet > 0 ){
					async.series([
						
						// Add Transaction
						function( callback ){
							Wallet.addTransaction({
								i_wallet_id : _data._user_wallet.id, 
								i_user_id 	: _ride.user_id, 
								v_type		: 'ride', 
								
								f_amount	: gnrl._minus( l_data.ride_paid_by_wallet ), 
								d_added		: gnrl._db_datetime(), 
								l_data		: gnrl._json_encode({
									'ride_id' 		: i_ride_id,
									'ride_code'		: _ride.v_ride_code,
									'vehicle_type' 	: _ride.l_data.vehicle_type,
								})
							}, function( status, data ){ 
								callback( null );
							});
						},
						
						// Refresh
						function( callback ){
							Wallet.refreshWallet2( _data._user_wallet.id, function( amount ){ 
								_data._user_wallet.f_amount = amount;
								callback( null );
							});
						},
						
						// Send Notification
						function( callback ){
							Notification.send({
								_key : 'user_ride_wallet_payment',
								_role : 'user',
								_tokens : [{
									'id' : _ride.user_id,
									'lang' : _ride.user_lang,
									'token' : _ride.user_device_token,
								}],
								_keywords : {},
								_custom_params : {
									i_ride_id : i_ride_id,
									ride_code : _ride.v_ride_code,
									paid_wallet_amount : l_data.ride_paid_by_wallet,
								},
								_need_log : 0,
							}, function( err, response ){
								callback( null );
							});
						}
						
					], function( error, results ){
						callback( null );
					});
				}
				else{
					callback( null );
				}
			},
			
			// Driver # Wallet Actions [Ride Money Add To Wallet, If Dry Run]
			function( callback ){
				
				async.series([
					
					// Add Transaction For Ride Money
					function( callback ){
						
						var f_receivable 	= l_data.ride_driver_receivable;
						var f_payable 		= l_data.ride_driver_payable;
						var f_received 		= l_data.ride_paid_by_cash;
						var f_amount 		= gnrl._round( l_data.ride_driver_receivable - f_received );
						
						Wallet.addTransaction({
							
							i_wallet_id 	: _data._driver_wallet.id, 
							i_user_id 		: _ride.driver_id, 
							v_type			: 'ride', 
							
							f_receivable	: f_receivable,
							f_payable		: f_payable,
							f_received		: f_received,
							f_amount		: f_amount,
							
							d_added			: gnrl._db_datetime(), 
							l_data			: gnrl._json_encode({
								'ride_id' 		: i_ride_id,
								'ride_code'		: _ride.v_ride_code,
								'vehicle_type' 	: _ride.l_data.vehicle_type,
							})
						}, function( status, data ){
							
							callback( null );
							
						});
					},
					
					// Add Transaction For Dry Run
					function( callback ){
						if( l_data.apply_dry_run_amount > 0 ){
							
							var f_receivable = l_data.apply_dry_run_amount;
							var f_payable 	 = 0;
							var f_received 	 = 0;
							var f_amount 	 = l_data.apply_dry_run_amount;
							
							if( _data._driver_wallet.f_amount < 0 ){
								var temp = ( -1 * _data._driver_wallet.f_amount );
								if( l_data.apply_dry_run_amount < temp ){
									f_received = l_data.apply_dry_run_amount;
								}
								else{
									f_received = temp;
								}
							}
							
							Wallet.addTransaction({
								
								i_wallet_id 	: _data._driver_wallet.id, 
								i_user_id 		: _ride.driver_id, 
								v_type			: 'ride_dry_run', 
								
								f_receivable	: f_receivable,
								f_payable		: f_payable,
								f_received		: f_received,
								f_amount		: f_amount,
								
								d_added			: gnrl._db_datetime(), 
								l_data			: gnrl._json_encode({
									'ride_id' 		: i_ride_id,
									'ride_code'		: _ride.v_ride_code,
									'vehicle_type' 	: _ride.l_data.vehicle_type,
								})
							}, function( status, data ){
								
								callback( null );
								
							});
						}
						else{
							callback( null );
						}
					},
					
					// Refresh
					function( callback ){
						Wallet.refreshWallet2( _data._driver_wallet.id, function( amount ){ 
							_data._driver_wallet.f_amount = amount;
							callback( null );
						});
					},
					
					// Send Notification, Driver = Wallet Get Payment
					function( callback ){
						Notification.send({
							_key : 'driver_ride_get_payment',
							_role : 'driver',
							_tokens : [{
								'id' : _ride.driver_id,
								'lang' : _ride.driver_lang,
								'token' : _ride.driver_device_token,
							}],
							_keywords : {},
							_custom_params : {
								i_ride_id : i_ride_id,
								ride_code : _ride.v_ride_code,
							},
							_need_log : 0,
						}, function( err, response ){
							callback( null );
						});
					},
					
					// Send Notification, Driver = Wallet Get Dry Run
					function( callback ){
						if( l_data.apply_dry_run_amount > 0 ){
							Notification.send({
								_key : 'driver_ride_get_dry_run',
								_role : 'driver',
								_tokens : [{
									'id' : _ride.driver_id,
									'lang' : _ride.driver_lang,
									'token' : _ride.driver_device_token,
								}],
								_keywords : {},
								_custom_params : {
									i_ride_id : i_ride_id,
									ride_code : _ride.v_ride_code,
								},
								_need_log : 0,
							}, function( err, response ){
								callback( null );
							});	
						}
						else{
							callback( null );
						}
					},
					
				], function( error, results ){
					
					callback( null );
					
				});
				
			},
			
		], 
		
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_ride_completed', {});
		});
		
	}
};

module.exports = currentApi;
