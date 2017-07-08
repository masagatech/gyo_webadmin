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
		
		var _data = {
			
			trip_time_in_min			: 0,
			actual_distance 			: 0,
			actual_dry_run 				: 0,
			actual_amount 				: 0,
			service_tax 				: 0,
			surcharge 					: 0,
			final_amount 				: 0,
			discount 					: 0,
			apply_dry_run 				: 0,
			apply_dry_run_amount		: 0,
			ride_paid_by_cash 			: 0,
			ride_paid_by_wallet 		: 0,
			company_commision 			: 0,
			company_commision_amount 	: 0,
			ride_driver_receivable 		: 0,
			ride_driver_payable 		: 0,
			
			calculated_charges 			: {
				'min_charge' 		: 0,
				'base_fare' 		: 0,
				'total_fare' 		: 0,
				'ride_time_charge' 	: 0,
				'service_tax' 		: 0,
				'surcharge' 		: 0,
				'discount' 			: 0,
			},
			
			charges : {
				'min_charge' 		: 0,
				'base_fare' 		: 0,
				'total_fare' 		: 0,
				'ride_time_charge' 	: 0,
				'service_tax' 		: 0,
				'surcharge' 		: 0,
				'discount' 			: 0,
			},
			
			
			ins_charges_Queries : [],
			
			_ins : {},
			
			
			ride : {},
			user : {},
			driver : {},
			
			
		};
		
		var paymentArr = {
			'wallet' : 0,
			'cash' : 0,
		};
		
		var end_date = gnrl._db_datetime();
		var trip_time = {};
		
		
		/*
		STEPS
		
			>> Get Ride
			>> Update ride to complete
			>> Get Ride Charges
			
			>> Calculate Total Distance
			>> Calculate Total Time
			>> Calculate Dry Run
			
			>> Entry of Min Charge
			>> Entry of Base Fare
			>> Entry of Service Tax
			>> Entry of Surcharge
			>> Entry of Discount
			
			>> Get Final Total
			
			>> Calculate Company Comission
			
			>> Get User Wallet

			>> Update ride to paid
			
			>> Add Payment [Wallet,Cash]
				>> Via Wallet
				>> Via Cash
			
			>> Select User
			>> Select Driver
			
			>> User Wallet Actions
				>> Deduct Ride Amount, If any
				>> Refresh User Wallet, If any
				>> Send Notification, If any
			
			>> Driver Manage Payment
				>> Add
			
			>> Dry Run, If any
				>> Get Wallet
				>> Add
				>> Refresh
				>> Send notification
			
			>> Send Notification for Ride Complete User
			>> Send Notification for Ride Complete Driver
			
			>> Ride Completion ##EMAIL ##SMS ## Driver
			>> Ride Completion ##EMAIL ##SMS ## User
			
		*/
		
		var dTest = 1;
		
		
		async.series([
		
			// Get Ride
			function( callback ){
				
				var _q = "SELECT ";
				
				_q += " dr.id AS driver_id ";
				_q += " , dr.v_name AS driver_name ";
				_q += " , dr.v_email AS driver_email ";
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
					else if( !dTest && data[0].e_status == 'complete' ){
						gnrl._api_response( res, 0, 'err_msg_ride_alreay_completed', {} );
					}
					else{
						_data.ride = data[0];
						callback( null );
					}
				});
				
			},
			
			// Update ride to complete
			function( callback ){
				var _ins = [
					"e_status = 'complete'",
					"d_end = '"+end_date+"'",
				];
				dclass._updateJsonb( 'tbl_ride', _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){ 
					callback( null );
				});
			},
			
			// Get Ride Charges
			function( callback ){
				Ride.getChargesData( _data.ride, function( data ){
					_data.charges = data;
					callback( null );
				});
			},
			
			// Calculate Total Distance
			function( callback ){
				if( force_close == 1 ){
					_data.actual_distance = estimate_km;
					_data.actual_dry_run = estimate_dry_run;
					callback( null );
				}
				else{
					Ride.calculateDistances( i_ride_id, function( status, data ){
						_data.actual_distance = data.actual_distance;
						_data.actual_dry_run = data.actual_dry_run;
						
						if( dTest ){
							_data.actual_distance = 5;
							_data.actual_dry_run = 1;
						}
						
						callback( null );
					});	
				}
			},
			
			// Calculate Total Time
			function( callback ){
				
				trip_time = gnrl._dateDiff( _data.ride.d_start, end_date );
				
				if( parseFloat( trip_time.days ) > 0 ){ _data.trip_time_in_min += ( parseFloat( trip_time.days ) * 60 * 60 ); }
				if( parseFloat( trip_time.hours ) > 0 ){ _data.trip_time_in_min += ( parseFloat( trip_time.hours ) * 60 ); }
				if( parseFloat( trip_time.minutes ) > 0 ){ _data.trip_time_in_min += parseFloat( trip_time.minutes ); }
				if( parseFloat( trip_time.seconds ) > 30 ){ _data.trip_time_in_min += 1; }
				else if( parseFloat( trip_time.seconds ) > 0 ){ _data.trip_time_in_min += 0.5; }
				
				if( dTest ){
					_data.trip_time_in_min = 10;
				}
				
				callback( null );
			},
			
			// Calculate Dry Run
			function( callback ){
				if( _data.actual_dry_run <= 0 ){
					callback( null );
				}
				else{
					_data.apply_dry_run = ( _data.actual_dry_run > _data.charges.max_dry_run_km ) ? _data.charges.max_dry_run_km : _data.actual_dry_run;
					_data.apply_dry_run_amount = gnrl._round( _data.apply_dry_run * _data.charges.max_dry_run_charge );
					callback( null );
				}
			},
			
			// Entry of Ride Time Charge
			function( callback ){
				
				if( _data.charges.ride_time_charge > 0 ){
					
					var chrg = _data.calculated_charges.ride_time_charge = gnrl._round( _data.charges.ride_time_charge * _data.trip_time_in_min );
					
					_data.actual_amount += chrg;
					
					_data.ins_charges_Queries.push( {
						'i_ride_id' : i_ride_id,
						'v_charge_type' : 'ride_time_charge',
						'f_amount' : chrg,
						'd_added' : gnrl._db_datetime(),
						'l_data' : gnrl._json_encode({
							'i_added_by' : login_id,
							'v_charge_info' : '',
							'ride_time_charge' : _data.charges.ride_time_charge,
							'trip_time_in_min' : _data.trip_time_in_min,
						}),
					} );
					
					callback( null );
					
				}
				else{
					
					callback( null );
					
				}
			},
			
			
			
			// Entry of Min Charge
			function( callback ){
				
				var chrg = _data.calculated_charges.min_charge = gnrl._round( _data.charges.min_charge );
				
				_data.actual_amount += chrg;
				
				_data.ins_charges_Queries.push({
					'i_ride_id' : i_ride_id,
					'v_charge_type' : 'min_charge',
					'f_amount' : chrg,
					'd_added' : gnrl._db_datetime(),
					'l_data' : gnrl._json_encode({
						'i_added_by' : login_id,
						'v_charge_info' : '',
					}),
				});
				
				callback( null );
				
			},
			
			
			
			// Entry of Base Fare
			function( callback ){
				
				var chrg = _data.calculated_charges.base_fare = gnrl._round( _data.charges.base_fare );
				
				_data.actual_amount += chrg;
				
				_data.ins_charges_Queries.push({
					'i_ride_id' : i_ride_id,
					'v_charge_type' : 'base_fare',
					'f_amount' : chrg,
					'd_added' : gnrl._db_datetime(),
					'l_data' : gnrl._json_encode({
						'i_added_by' : login_id,
						'v_charge_info' : '',
					}),
				});
				
				callback( null );
				
			},
			
			
			
			// Entry of Total Fare
			function( callback ){
				
				var chrg = 0;
				
				if( _data.actual_distance > _data.charges.upto_km ){ 
					chrg += ( _data.charges.upto_km_charge * _data.charges.upto_km ); 
					chrg += ( _data.charges.after_km_charge * ( _data.actual_distance - _data.charges.upto_km ) );
				}
				else{
					chrg += ( _data.charges.upto_km_charge * _data.actual_distance ); 
				}
				
				chrg = ( chrg > 0 ) ? gnrl._round( chrg ) : 0;
				
				_data.calculated_charges.total_fare = chrg;
				
				_data.actual_amount += chrg;
				
				_data.ins_charges_Queries.push({
					'i_ride_id' : i_ride_id,
					'v_charge_type' : 'total_fare',
					'f_amount' : chrg,
					'd_added' : gnrl._db_datetime(),
					'l_data' : gnrl._json_encode({
						'i_added_by' : login_id,
						'v_charge_info' : '',
						'actual_distance' : _data.actual_distance,
						'upto_km' : _data.charges.upto_km,
						'upto_km_charge' : _data.charges.upto_km_charge,
						'after_km_charge' : _data.charges.after_km_charge,
					}),
				});
				
				callback( null );
				
			},
			
			// Entry of Service Tax
			function( callback ){
				
				var tempTotal = _data.actual_amount;
				
				var chrg = 0;
				
				if( _data.charges.service_tax > 0 ){
					chrg = parseFloat( ( tempTotal * _data.charges.service_tax ) / 100 );
				}
				else{
					chrg = 0;
				}
				
				chrg = gnrl._round( chrg );
				
				_data.calculated_charges.service_tax = gnrl._round( chrg );
				
				_data.actual_amount += chrg;
				
				_data.ins_charges_Queries.push({
					'i_ride_id' : i_ride_id,
					'v_charge_type' : 'service_tax',
					'f_amount' : chrg,
					'd_added' : gnrl._db_datetime(),
					'l_data' : gnrl._json_encode({
						'i_added_by' : login_id,
						'v_charge_info' : '',
						'service_tax' : _data.charges.service_tax,
						
					}),
				});
				
				
				
				callback( null );
				
			},
			
			
			
			// Entry of Surcharge
			function( callback ){
				
				var tempTotal = _data.actual_amount;
				
				var chrg = 0;
				
				if( _data.charges.surcharge > 0 ){
					chrg = parseFloat( ( tempTotal * _data.charges.surcharge ) / 100 );
				}
				else{
					chrg = 0;
				}
				
				chrg = gnrl._round( chrg );
				
				_data.calculated_charges.surcharge = gnrl._round( chrg );
				
				_data.actual_amount += chrg;
				
				var _ins = {
					'i_ride_id' : i_ride_id,
					'v_charge_type' : 'surcharge',
					'f_amount' : chrg,
					'd_added' : gnrl._db_datetime(),
					'l_data' : gnrl._json_encode({
						'i_added_by' : login_id,
						'v_charge_info' : '',
						'surcharge' : _data.charges.surcharge,
					}),
				};
				
				callback( null );
				
			}, 
			
			////////////////////////////////////////////////////
			
			// Entry of Discount
			function( callback ){
				
				if( _data.ride.l_data.charges.promocode_id > 0 && _data.ride.l_data.charges.promocode_code_discount != '' ){
					
						var tempTotal = _data.actual_amount;
						
						var tempDiscount = gnrl._isPercent( tempTotal, _data.ride.l_data.charges.promocode_code_discount );
						
						_data.discount = tempDiscount.comm_amount;
						if( _data.discount > _data.ride.l_data.charges.promocode_code_discount_upto ){
							_data.discount = _data.ride.l_data.charges.promocode_code_discount_upto;
						}
						
						if( _data.discount > 0 ){
							_data.discount = gnrl._minus( _data.discount );
							var _ins = {
								'i_ride_id' : i_ride_id,
								'v_charge_type' : 'discount',
								'f_amount' : gnrl._minus( _data.discount ),
								'd_added' : gnrl._db_datetime(),
								'l_data' : gnrl._json_encode({
									'i_added_by' : login_id,
									'v_charge_info' : '',
									'promocode_id' : _data.ride.l_data.charges.promocode_id,
									'promocode_code' : _data.ride.l_data.charges.promocode_code,
									'promocode_code_discount' : _data.ride.l_data.charges.promocode_code_discount,
									'promocode_code_discount_upto' : _data.ride.l_data.charges.promocode_code_discount_upto,
									'promocode_code_discount_amount' : _data.ride.l_data.charges.promocode_code_discount_amount,
								}),
							};
							dclass._insert( 'tbl_ride_charges', _ins, function( ins_status, ins_data ){
								callback( null );
							});
						}
						else{
							callback( null );
						}
					
				}
				else{
					callback( null );
				}
			}, 
			
			/*
			
			// Get Final Total
			function( callback ){
				Ride.getFinalTotal( i_ride_id, function( total ){
					_data.final_amount = total;
					callback( null );
				});
			},
			
			// Calculate Company Comission
			function( callback ){
				_data.company_commision = _data.ride.l_data.charges.company_commission;
				_data.company_commision_amount = gnrl._calc_commision( _data.final_amount, _data.company_commision );
				_data.ride_driver_payable = _data.company_commision_amount;
				_data.ride_driver_receivable = gnrl._round( _data.final_amount - _data.ride_driver_payable );
				callback( null );
			},
			
			// Get User Wallet
			function( callback ){
				
				Wallet.get({
					user_id : _data.ride.i_user_id,
					role : 'user',
					wallet_type : 'money'
				}, function( status, _wallet ){
					
					_data._wallet = _wallet;
					
					if( _data._wallet.f_amount <= 0 ){
						paymentArr.cash = _data.final_amount;
					}
					else if( _data.final_amount < _data._wallet.f_amount ){
						paymentArr.wallet = _data.final_amount;
					}
					else{
						paymentArr.wallet = _data._wallet.f_amount;
						paymentArr.cash = _data.final_amount - paymentArr.wallet;
					}
					
					paymentArr.wallet = gnrl._round( paymentArr.wallet );
					paymentArr.cash = gnrl._round( paymentArr.cash );
					
					_data.paymentArr = paymentArr;
					
					callback( null );
					
				});
			},
			
			// Update Ride = Complete & Paid
			function( callback ){
				
				var _ins = [
					" l_data = l_data || '"+gnrl._json_encode({
						
						'trip_time' 						: trip_time,
						'trip_time_in_min' 					: _data.trip_time_in_min,
						
						'actual_distance' 					: _data.actual_distance,
						'actual_dry_run' 					: _data.actual_dry_run,
						
						'final_amount' 						: _data.final_amount,
						
						
						'apply_dry_run' 					: _data.apply_dry_run,
						'apply_dry_run_amount' 				: _data.apply_dry_run_amount,

						'promocode_code_discount_amount' 	: _data.discount,
						
						'ride_paid_by_cash' 				: paymentArr.cash,
						'ride_paid_by_wallet' 				: paymentArr.wallet,
						
						'company_commision' 				: _data.company_commision,
						'company_commision_amount' 			: _data.company_commision_amount,
						
						'ride_driver_receivable' 			: _data.ride_driver_receivable,
						'ride_driver_payable' 				: _data.ride_driver_payable,
						
						
					})+"' "
				];
				
				dclass._updateJsonb( 'tbl_ride', _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						callback( null );
					}
				});
				
			},
			
			// Add Payments [Wallet,Cash]
			function( callback ){
				
				async.series([
					
					// Via Wallet
					function( callback ){
						if( paymentArr.wallet > 0 ){
							var _ins = {
								'i_ride_id' : i_ride_id,
								'v_type' : 'wallet',
								'f_amount' : paymentArr.wallet,
								'd_added' : gnrl._db_datetime(),
								'i_success' : 1,
								'l_data' : gnrl._json_encode({
								})
							};
							dclass._insert( 'tbl_ride_payments', _ins, function( status, data ){
								callback( null );
							});
						}
						else{
							callback( null );
						}
					},
					
					// Via Cash
					function( callback ){
						if( paymentArr.cash > 0 ){
							var _ins = {
								'i_ride_id' : i_ride_id,
								'v_type' : 'cash',
								'f_amount' : paymentArr.cash,
								'd_added' : gnrl._db_datetime(),
								'i_success' : 0,
								'l_data' : gnrl._json_encode({
								})
							};
							dclass._insert( 'tbl_ride_payments', _ins, function( status, data ){
								callback( null );
							});
						}
						else{
							callback( null );
						}
					},
					
				
				], function( payent_error, payent_results ){
					callback( null );
				});
			},
			
			// Select User
			function( callback ) {
				User.get( _data.ride.i_user_id, function( status, data ){
					_data.user = data[0];
					callback( null );
				});
			},
			
			// Select Driver
			function( callback ) {
				User.get( _data.ride.driver_id, function( status, data ){
					_data.driver = data[0];
					callback( null );
				});
			},
			
			// User Wallet Actions
			function( callback ) {
				
				if( paymentArr.wallet <= 0 ){
					callback( null );
				}
				else{
					async.series([
						
						// Deduct Ride Amount, If any
						function( callback ){
							Wallet.get({
								user_id : _data.ride.i_user_id,
								role : 'user',
								wallet_type : 'money'
							}, function( status, _wallet ){
								_data._user_wallet = _wallet;
								callback( null );
							});
						},
						
						// Deduct Ride Amount, If any
						function( callback ){
							var _ins = {
								'i_wallet_id' : _data._user_wallet.id,
								'i_user_id' : _data.ride.i_user_id,
								'v_type'    : 'ride',
								'v_action'  : 'minus',
								'f_amount'  : gnrl._minus( paymentArr.wallet ),
								'd_added'   : gnrl._db_datetime(),
								'l_data'    : {
									'ride_id' : i_ride_id,
									'ride_code' : _data.ride.v_ride_code,
									'vehicle_type' : _data.ride.l_data.vehicle_type,
								},
							};
							Wallet.addTransaction( _ins, function( insert_transaction_status, insert_transaction_data ){ 
								callback( null );
							});
						},
						
						// Refresh User Wallet, If any
						function( callback ){
							Wallet.refreshWallet({
								wallet_id 	: _data._user_wallet.id,
								special 	: 0,
							}, function( status, data ){ 
								callback( null );
							});
						},
						
						// Send Notification, If any
						function( callback ){
							var tokens = [{
								'id' : _data.user.id,
								'lang' : _data.user.lang,
								'token' : _data.user.v_device_token,
							}];
							var params = {
								_key : 'user_ride_wallet_payment',
								_role : 'user',
								_tokens : tokens,
								_keywords : {},
								_custom_params : {
									i_ride_id : i_ride_id,
									ride_code : _data.ride.v_ride_code,
									paid_wallet_amount : paymentArr.wallet,
								},
								_need_log : 0,
							};
							Notification.send( params, function( err, response ){
								callback( null );
							});
						},
						
					
					],function( payent_error, payent_results ){
						callback( null );
					});
				}
				
			},
			
			// Driver Manage Payment
			function( callback ) {
				async.series([
					
					// Get Wallet
					function( callback ){
						Wallet.get({
							user_id : _data.ride.driver_id,
							role : 'driver',
							wallet_type : 'money'
						}, function( status, _wallet ){
							_data._driver_wallet = _wallet;
							callback( null );
						});
					},
				
					// Add
					function( callback ){
						
						var f_receivable = _data.ride_driver_receivable;
						var f_payable = _data.ride_driver_payable;
						var f_received = paymentArr.cash;
						var f_amount = gnrl._round( _data.ride_driver_receivable - f_received );
						var f_running_balance = 0;
						
						var _ins = {
							'i_wallet_id' : _data._driver_wallet.id,
							'i_user_id' : _data.ride.driver_id,
							'v_type'    : 'ride',
							'v_action'  : 'plus',
							'f_receivable' 		: gnrl._round( f_receivable ),
							'f_payable' 		: gnrl._round( f_payable ),
							'f_received' 		: gnrl._round( f_received ),
							'f_running_balance' : gnrl._round( f_running_balance ),
							'f_amount' 			: gnrl._round( f_amount ),
							'd_added'   : gnrl._db_datetime(),
							'l_data'    : {
								'ride_id' : i_ride_id,
								'ride_code' : _data.ride.v_ride_code,
								'vehicle_type' : _data.ride.l_data.vehicle_type,
							},
						};
						Wallet.addTransaction( _ins, function( status, data ){ 
							callback( null );
						});
					},
					
					// Refresh
					function( callback ){
						Wallet.refreshWallet({
							wallet_id 	: _data._driver_wallet.id,
							special 	: 1,
						}, function( status, data ){ 
							callback( null );
						});
					},
					
					// Send notification
					function( callback ){
						var tokens = [{
							'id' : _data.driver.id,
							'lang' : _data.driver.lang,
							'token' : _data.driver.v_device_token,
						}];
						var params = {
							_key : 'driver_ride_get_payment',
							_role : 'driver',
							_tokens : tokens,
							_keywords : {},
							_custom_params : {
								i_ride_id : i_ride_id,
								ride_code : _data.ride.v_ride_code,
							},
							_need_log : 0,
						};
						Notification.send( params, function( err, response ){
							callback( null );
						});
					},
				
				], 
				function( payent_error, payent_results ){
					callback( null );
				});
			},
			
			// Dry Run, If any
			function( callback ) {
				
				if( _data.apply_dry_run_amount > 0 ){
					
					async.series([
						
						// Get Wallet
						function( callback ){
							Wallet.get({
								user_id : _data.ride.driver_id,
								role : 'driver',
								wallet_type : 'money'
							}, function( status, _wallet ){
								_data._driver_wallet = _wallet;
								callback( null );
							});
						},
					
						// Add
						function( callback ){
							
							var f_receivable = _data.apply_dry_run_amount;
							var f_payable = 0;
							var f_received = 0;
							var f_amount = _data.apply_dry_run_amount;
							var f_running_balance = 0;
							
							if( _data._driver_wallet.f_amount < 0 ){
								var temp = ( -1 * _data._driver_wallet.f_amount );
								if( _data.apply_dry_run_amount < temp ){
									f_received = _data.apply_dry_run_amount;
								}
								else{
									f_received = temp;
								}
							}
							
							var _ins = {
								'i_wallet_id' : _data._driver_wallet.id,
								'i_user_id' : _data.ride.driver_id,
								'v_type'    : 'ride_dry_run',
								'v_action'  : 'plus',
								'f_receivable' 		: gnrl._round( f_receivable ),
								'f_payable' 		: gnrl._round( f_payable ),
								'f_received' 		: gnrl._round( f_received ),
								'f_running_balance' : gnrl._round( f_running_balance ),
								'f_amount' 			: gnrl._round( f_amount ),
								'd_added'   : gnrl._db_datetime(),
								'l_data'    : {
									'ride_id' : i_ride_id,
									'ride_code' : _data.ride.v_ride_code,
									'vehicle_type' : _data.ride.l_data.vehicle_type,
								},
							};
							
							Wallet.addTransaction( _ins, function( status, data ){ 
								callback( null );
							});
							
						},
						
						// Refresh
						function( callback ){
							Wallet.refreshWallet({
								wallet_id 	: _data._driver_wallet.id,
								special 	: 1,
							}, function( status, data ){ 
								callback( null );
							});
						},
						
						// Send notification
						function( callback ){
							var tokens = [{
								'id' : _data.driver.id,
								'lang' : _data.driver.lang,
								'token' : _data.driver.v_device_token,
							}];
							var params = {
								_key : 'driver_ride_get_dry_run',
								_role : 'driver',
								_tokens : tokens,
								_keywords : {},
								_custom_params : {
									i_ride_id : i_ride_id,
									ride_code : _data.ride.v_ride_code,
								},
								_need_log : 0,
							};
							Notification.send( params, function( err, response ){
								callback( null );
							});
						},
					
					], 
					function( payent_error, payent_results ){
						callback( null );
					});
				}
				else{
					callback( null );
				}
			},
			
			// Send Notification for Ride Complete User
			function( callback ){
				
				var tokens = [{
					'id' : _data.user.id,
					'lang' : _lang,
					'token' : _data.user.v_device_token,
				}];
				var params = {
					_key : 'user_ride_complete',
					_role : 'user',
					_tokens : tokens,
					_keywords : {},
					_custom_params : {
						i_ride_id : i_ride_id,
						ride_code : _data.ride.v_ride_code,
					},
					_need_log : 0,
				};
				Notification.send( params, function( err, response ){
					callback( null );
				});
				
			},
			
			*/
			
			/*
			// Send Notification for Ride Complete Driver
			function( callback ){
				
				var tokens = [{
					'id' : _data.driver.id,
					'lang' : _lang, //_data.driver.l_data.lang ? _data.driver.l_data.lang : _lang,
					'token' : _data.driver.v_device_token,
				}];
				var params = {
					_key : 'driver_ride_complete',
					_role : 'driver',
					_tokens : tokens,
					_keywords : {},
					_custom_params : {
						i_ride_id : i_ride_id,
						ride_code : _data.ride.v_ride_code,
					},
					_need_log : 0,
				};
				Notification.send( params, function( err, response ){
					callback( null );
				});
				
			},
			*/
			
		], 
		
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_ride_completed', _data );
		});
		
	}
};

module.exports = currentApi;
