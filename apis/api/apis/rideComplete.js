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
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var _data = {
			
			actual_distance : 0,
			actual_amount : 0,
			actual_dry_run : 0,
			service_tax : 0,
			surcharge : 0,
			final_amount : 0,
			apply_dry_run : 0,
			apply_dry_run_amount : 0,
			ride_paid_by_cash : 0,
			ride_paid_by_wallet : 0,
			company_commision : 0,
			company_commision_amount : 0,
			ride_driver_receivable : 0,
			ride_driver_payable : 0,
			
			_ins : {},
			
			charges : {
				'min_charge' : 0,
				'base_fare' : 0,
				'ride_time_charge' : 0,
				'service_tax' : 0,
				'surcharge' : 0,
			},
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
		var trip_time_in_min = 0;
		
		
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
			>> Entry of Surcharge
			>> Entry of Service Tax
			
			>> Get Final Total
			
			>> Calculate Company Comission
			
			>> Set Vehicle To Idle
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
			
			>> Send Notification for Ride Complete [Driver / User]
			
			>> Ride Completion ##EMAIL ##SMS ## Driver
			>> Ride Completion ##EMAIL ##SMS ## User
			
		*/
		
		
		async.series([
		
			// Get Ride
			function( callback ){
				Ride.get( i_ride_id, function( ride_status, ride_data ){
					if( !ride_status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !ride_data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else if( !ride_data[0].e_status == 'complete' ){
						gnrl._api_response( res, 0, 'err_msg_ride_alreay_completed', {} );
					}
					else{
						_data.ride = ride_data[0];
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
				Ride.calculateDistances( i_ride_id, function( status, data ){
					_data.actual_distance = data.actual_distance;
					_data.actual_dry_run = data.actual_dry_run;
					
					callback( null );
				});
			},
			
			// Calculate Total Time
			function( callback ){
				
				trip_time = gnrl._dateDiff( _data.ride.d_start, end_date );
				trip_time_in_min = 0;
				// if( parseFloat( trip_time.days ) > 0 ){ trip_time_in_min += ( parseFloat( trip_time.days ) * 60 * 60 ); }
				if( parseFloat( trip_time.hours ) > 0 ){ trip_time_in_min += ( parseFloat( trip_time.hours ) * 60 ); }
				if( parseFloat( trip_time.minutes ) > 0 ){ trip_time_in_min += parseFloat( trip_time.minutes ); }
				if( parseFloat( trip_time.seconds ) > 30 ){ trip_time_in_min += 1; }
				else if( parseFloat( trip_time.seconds ) > 0 ){ trip_time_in_min += 0.5; }
				
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
					var chrg = gnrl._round( _data.charges.ride_time_charge * trip_time_in_min );
					var _ins = {
						'i_ride_id' : _data.ride.id,
						'v_charge_type' : 'ride_time_charge',
						'f_amount' : chrg,
						'd_added' : gnrl._db_datetime(),
						'l_data' : gnrl._json_encode({
							'i_added_by' : login_id,
							'v_charge_info' : '',
						}),
					};
					dclass._insert( 'tbl_ride_charges', _ins, function( ins_status, ins_data ){
						callback( null );
					});
				}
				else{
					callback( null );
				}
			},
			
			// Entry of Min Charge
			function( callback ){
				var _ins = {
					'i_ride_id' : _data.ride.id,
					'v_charge_type' : 'min_charge',
					'f_amount' : gnrl._round( _data.charges.min_charge ),
					'd_added' : gnrl._db_datetime(),
					'l_data' : gnrl._json_encode({
						'i_added_by' : login_id,
						'v_charge_info' : '',
					}),
				};
				dclass._insert( 'tbl_ride_charges', _ins, function( ins_status, ins_data ){
					if( !ins_status ){
						callback( null );
					}
					else{
						callback( null );
					}
				});
			},
			
			// Entry of Base Fare
			function( callback ){
				var _ins = {
					'i_ride_id' : _data.ride.id,
					'v_charge_type' : 'base_fare',
					'f_amount' : gnrl._round( _data.charges.base_fare ),
					'd_added' : gnrl._db_datetime(),
					'l_data' : gnrl._json_encode({
						'i_added_by' : login_id,
						'v_charge_info' : '',
					}),
				};
				dclass._insert( 'tbl_ride_charges', _ins, function( ins_status, ins_data ){
					if( !ins_status ){
						callback( null );
					}
					else{
						callback( null );
					}
				});
			},
			
			// Entry of Total Fare
			function( callback ){
				
				if( _data.actual_distance > _data.charges.upto_km ){ 
					_data.actual_amount += ( _data.charges.upto_km_charge * _data.charges.upto_km ); 
				}
				_data.actual_amount += ( _data.charges.after_km_charge * ( _data.actual_distance - _data.charges.upto_km ) );
				if( _data.actual_amount <= 0 ){ 
					_data.actual_amount = 0; 
				}
				
				_data.actual_amount = gnrl._round( _data.actual_amount );
				
				var _ins = {
					'i_ride_id' : _data.ride.id,
					'v_charge_type' : 'total_fare',
					'f_amount' : gnrl._round( _data.actual_amount ),
					'd_added' : gnrl._db_datetime(),
					'l_data' : gnrl._json_encode({
						'i_added_by' : login_id,
						'v_charge_info' : '',
					}),
				};
				dclass._insert( 'tbl_ride_charges', _ins, function( ins_status, ins_data ){
					if( !ins_status ){
						callback( null );
					}
					else{
						callback( null );
					}
				});
				
			},
			
			// Entry of Service Tax
			function( callback ){
				
				var _q = "";
				_q += " SELECT ";
				_q += " COALESCE( SUM( f_amount ), 0 ) AS total_amount ";
				_q += " FROM ";
				_q += " tbl_ride_charges ";
				_q += " WHERE true ";
				_q += " AND i_ride_id = '"+i_ride_id+"' ";
				// _q += " AND v_charge_type IN ('total_fare') ";
				
				dclass._query( _q, function( ftotal_status, ftotal_data ){
					
					if( !ftotal_status ){
						callback( null );
					}
					else{
						
						if( _data.charges.service_tax > 0 ){
							_data.charges.service_tax = parseFloat( ( ftotal_data[0].total_amount * _data.charges.service_tax ) / 100 );
						}
						else{
							_data.charges.service_tax = 0;
						}
						
						_data.charges.service_tax = gnrl._round( _data.charges.service_tax );
						
						var _ins = {
							'i_ride_id' : _data.ride.id,
							'v_charge_type' : 'service_tax',
							'f_amount' : _data.charges.service_tax,
							'd_added' : gnrl._db_datetime(),
							'l_data' : gnrl._json_encode({
								'i_added_by' : login_id,
								'v_charge_info' : '',
							}),
						};
						dclass._insert( 'tbl_ride_charges', _ins, function( ins_status, ins_data ){
							if( !ins_status ){
								callback( null );
							}
							else{
								callback( null );
							}
						});
						callback( null );
						
					}
				});
				
			},
			
			// Entry of Surcharge
			function( callback ){
				
				var _q = "";
				_q += " SELECT ";
				_q += " COALESCE( SUM( f_amount ), 0 ) AS total_amount ";
				_q += " FROM ";
				_q += " tbl_ride_charges ";
				_q += " WHERE true ";
				_q += " AND i_ride_id = '"+i_ride_id+"' ";
				// _q += " AND v_charge_type IN ('total_fare') ";
				
				dclass._query( _q, function( ftotal_status, ftotal_data ){
					
					if( !ftotal_status ){
						callback( null );
					}
					else{
						
						if( _data.charges.surcharge > 0 ){
							_data.charges.surcharge = parseFloat( ( ftotal_data[0].total_amount * _data.charges.surcharge ) / 100 );
						}
						else{
							_data.charges.surcharge = 0;
						}
						
						_data.charges.surcharge = gnrl._round( _data.charges.surcharge );
						
						var _ins = {
							'i_ride_id' : _data.ride.id,
							'v_charge_type' : 'surcharge',
							'f_amount' : _data.charges.surcharge,
							'd_added' : gnrl._db_datetime(),
							'l_data' : gnrl._json_encode({
								'i_added_by' : login_id,
								'v_charge_info' : '',
							}),
						};
						dclass._insert( 'tbl_ride_charges', _ins, function( ins_status, ins_data ){
							if( !ins_status ){
								callback( null );
							}
							else{
								callback( null );
							}
						});
						
					}
				});
				
			}, 
			
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
			
			// Set Vehicle To Idle
			function( callback ){
				var ins = {
					is_idle : 1
				};
				dclass._update( 'tbl_user', ins, " AND id = '"+_data.ride.i_driver_id+"' ", function( status, data ){
					callback( null );
				});
			},
			
			// Get User Wallet
			function( callback ){
				
				Wallet.get( _data.ride.i_user_id, 'user', function( status, _wallet ){
					
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
					
					"i_paid = '1'",
					" l_data = l_data || '"+gnrl._json_encode({
						'actual_distance' : _data.actual_distance,
						'actual_dry_run' : _data.actual_dry_run,
						'final_amount' : _data.final_amount,
						'trip_time' : trip_time,
						'trip_time_in_min' : trip_time_in_min,
						'apply_dry_run' : _data.apply_dry_run,
						'apply_dry_run_amount' : _data.apply_dry_run_amount,
						
						'ride_paid_by_cash' : paymentArr.cash,
						'ride_paid_by_wallet' : paymentArr.wallet,
						
						'company_commision' : _data.company_commision,
						'company_commision_amount' : _data.company_commision_amount,
						'ride_driver_receivable' : _data.ride_driver_receivable,
						'ride_driver_payable' : _data.ride_driver_payable,
						
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
				User.get( _data.ride.i_driver_id, function( status, data ){
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
							var _ins = {
								'i_user_id' : _data.ride.i_user_id,
								'v_type'    : 'ride',
								'v_action'  : 'minus',
								'f_amount'  : gnrl._minus( paymentArr.wallet ),
								'd_added'   : gnrl._db_datetime(),
								'l_data'    : {
									'ride_id' : i_ride_id,
									'ride_code' : _data.ride.v_ride_code,
								},
							};
							Wallet.addTransaction( _ins, function( insert_transaction_status, insert_transaction_data ){ 
								callback( null );
							});
						},
						
						// Refresh User Wallet, If any
						function( callback ){
							Wallet.refreshUserWallet( _data.ride.i_user_id, function( status, data ){ 
								callback( null );
							});
						},
						
						// Send Notification, If any
						function( callback ){
							var tokens = [{
								'id' : _data.user.id,
								'lang' : _lang,
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
						Wallet.get( _data.ride.i_driver_id, 'driver', function( status, _wallet ){
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
							'i_user_id' : _data.ride.i_driver_id,
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
							},
						};
						Wallet.addTransaction( _ins, function( status, data ){ 
							callback( null );
						});
					},
					
					// Refresh
					function( callback ){
						Wallet.refreshDriverWallet( _data.ride.i_driver_id, function( status, data ){ 
							callback( null );
						});
					},
					
					// Send notification
					function( callback ){
						var tokens = [{
							'id' : _data.driver.id,
							'lang' : _lang, //_data.driver.l_data.lang ? _data.driver.l_data.lang : _lang,
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
							Wallet.get( _data.ride.i_driver_id, 'driver', function( status, _wallet ){
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
								'i_user_id' : _data.ride.i_driver_id,
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
								},
							};
							
							Wallet.addTransaction( _ins, function( status, data ){ 
								callback( null );
							});
							
							
						},
						
						// Refresh
						function( callback ){
							Wallet.refreshDriverWallet( _data.ride.i_driver_id, function( status, data ){ 
								callback( null );
							});
						},
						
						// Send notification
						function( callback ){
							var tokens = [{
								'id' : _data.driver.id,
								'lang' : _lang, //_data.driver.l_data.lang ? _data.driver.l_data.lang : _lang,
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
			
			
			// Send Notification for Ride Complete [Driver / User]
			function( callback ){
				
				async.series([
					
					// Send To User
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
					
					/*
					// Send To Driver
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
					},*/
					
					
				], function( payent_error, payent_results ){
					callback( null );
				});
				
			},
			
			
			
		], 
		
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_ride_completed', {} );
		});
		
	}
};

module.exports = currentApi;
