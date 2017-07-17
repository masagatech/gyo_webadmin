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
	
	
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		// STEPS
		
		// Get Ride
		// Update Ride To Paid + Update Vehicle To Idle + Update Cash Payment Active
		// Get Charges STR
		
		// User Ride Completion Action
			// Email
			// SMS
		
		// Driver Ride Completion Action
			// Email
		
		// Run Referral Modules
		
		var _data = {};
		var _keywords = {};
		
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
				
				_q += " , rd.i_user_id ";
				_q += " , rd.i_paid ";
				_q += " , rd.v_pin ";
				_q += " , rd.v_ride_code ";
				_q += " , rd.d_start ";
				_q += " , rd.d_end ";
				_q += " , rd.l_data AS ride_l_data ";
				
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
					else if( data[0].i_paid ){
						gnrl._api_response( res, 0, 'err_msg_ride_alreay_confirmed', {} );
					}
					else{
						
						_data = data[0];
						
						var tempPaymentMethod = [];
						_data.ride_l_data.ride_paid_by_wallet = gnrl._round( _data.ride_l_data.ride_paid_by_wallet );
						if( _data.ride_l_data.ride_paid_by_wallet > 0 ){
							tempPaymentMethod.push('Wallet');
						}
						
						_data.ride_l_data.ride_paid_by_cash = gnrl._round( _data.ride_l_data.ride_paid_by_cash );
						if( _data.ride_l_data.ride_paid_by_cash > 0 ){
							tempPaymentMethod.push('Cash');
						}
						
						_keywords = {
							'[user_name]' : _data.user_name,
							'[i_ride_id]' : i_ride_id,
							'[ride_pin]' : _data.v_pin,
							'[ride_code]' : _data.v_ride_code,
							'[ride_total]' : _data.ride_l_data.final_amount,
							'[ride_total_time]' : _data.ride_l_data.trip_time_in_min,
							'[ride_discount]' : _data.ride_l_data.promocode_code_discount,
							
							'[ride_start_time]' : gnrl._db_ymd('Y-m-d h:i A', new Date( gnrl._timestamp( _data.d_start ) ) ),
							'[ride_end_time]' : gnrl._db_ymd('Y-m-d h:i A', new Date( gnrl._timestamp( _data.d_end ) ) ),
							
							'[ride_start_address]' : _data.ride_l_data.pickup_address,
							'[ride_end_address]' : _data.ride_l_data.destination_address,
							
							'[ride_distance]' : _data.ride_l_data.actual_distance,
							'[ride_promocode_code]' : _data.ride_l_data.promocode_code,
							
							'[ride_payment_method]' : tempPaymentMethod.join( ', ' ),
							
							'[ride_paid_by_wallet]' : _data.ride_l_data.ride_paid_by_wallet,
							'[ride_paid_by_cash]' : _data.ride_l_data.ride_paid_by_cash,
							
							'[ride_bill_table]' : '',
							
							'[city]' : _data.ride_l_data.city,
							'[driver_name]' : _data.driver_name,
						};
						
						callback( null );
					}
					
				});
				
			},
			
			// Update Ride To Paid + Update Vehicle To Idle + Update Cash Payment Active
			function( callback ){
				
				
				var _q = [];
					
				// Update Ride To Paid
				_q.push( "UPDATE tbl_ride SET i_paid = 1 WHERE id = '"+i_ride_id+"'; ");
				
				// Update Vehicle To Idle
				_q.push( "UPDATE tbl_user SET is_onride = 0, is_buzzed = 0 WHERE id = '"+login_id+"'; " );
				
				// Update Cash Payment Active
				_q.push( "UPDATE tbl_ride_payments SET i_success = 1 WHERE v_type = 'cash' AND i_ride_id = '"+i_ride_id+"'; " );
				
				dclass._query( _q.join(''), function( status, data ){ 
					callback( null );
				});
				
			},
			
			// Get Charges STR
			function( callback ) {
				Ride.getChargesTableStr( i_ride_id, function( str ){
					_keywords['[ride_bill_table]'] = str;
					callback( null );
				});
			},
			
			// User Ride Completion Action
			function( callback ){
				
				async.series([
					
					// Email
					function( callback ){
						
						Email.send({
							_to : _data.user_email,
							_lang : _data.user_lang,
							_key : 'user_ride_complete',
							_keywords : _keywords,
						}, function( error_mail, error_info ){
							
							callback( null );
							
						});
						
					},
					
					// SMS
					function( callback ){
						
						_keywords['[ride_bill_table]'] = '';
						
						SMS.send({
							_to : _data.user_phone,
							_lang : _data.user_lang,
							_key : 'user_ride_complete',
							_keywords : _keywords,
						}, function( status, error_info ){
							
							callback( null );
							
						});
					},
					
				], function( error, results ){
					
					callback( null );
					
				});
				
			},
			
			// Driver Ride Completion Action
			function( callback ){
				
				async.series([
					
					// Email
					function( callback ){
						
						_keywords['[user_name]'] = _keywords['[driver_name]'];
						
						Email.send({
							_to : _data.driver_email,
							_lang : _data.driver_lang,
							_key : 'driver_ride_complete',
							_keywords : _keywords,
						}, function( error_mail, error_info ){
							
							callback( null );
							
						});
					},
					
					// SMS
					function( callback ){
						
						callback( null );
						
					},
					
				], function( error, results ){
					
					callback( null );
					
				});
				
			},
			
			// ##APPLY_REFERRAL - User
			function( callback ){
				
				if( _data.user_referral_code && _data.user_referral_wallet_apply == 'first_ride' ){
					User.runReferralModule({
							user_id : _data.user_id,
							user_name : _data.user_name,
							referral_code : _data.user_referral_code,
							referral_amount : _data.user_referral_amount,
							referral_user_id : _data.user_referral_user_id,
							referral_wallet_type : _data.user_referral_wallet_type,
						}, function( status, data ){
						callback( null );
					});	
				}
				else{
					callback( null );
				}
			},
			
			// ##APPLY_REFERRAL - Driver
			function( callback ){
				
				if( _data.driver_referral_code && _data.driver_referral_wallet_apply == 'first_ride' ){
					
					User.runReferralModule({
							user_id : _data.driver_id,
							user_name : _data.driver_name,
							referral_code : _data.driver_referral_code,
							referral_amount : _data.driver_referral_amount,
							referral_user_id : _data.driver_referral_user_id,
							referral_wallet_type : _data.driver_referral_wallet_type,
						}, function( status, data ){
						callback( null );
					});	
					
				}
				else{
					callback( null );
				}
			},
			
		], 
		
		function( error, results ){
			
			gnrl._api_response( res, 1, 'succ_ride_completed', {} );
			
		});
		
	}
};

module.exports = currentApi;
