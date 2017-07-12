var express = require('express');
var validator = require('validator');
var md5 = require('md5');
var async = require('async');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status = 1;
	var _message = '';
	var _response = {
		'confirm' : 0,
	};
	
	var login_id = gnrl._is_undf( params.login_id );
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	var payment_mode = gnrl._is_undf( params.payment_mode );
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	if( !payment_mode ){ _status = 0; _message = 'err_req_payment_mode'; }
	
	if( !_status ){
		
		gnrl._api_response( res, 0, _message, _response );
		
	}
	
	else{
		
		/*
			
		> Get Ride
		> Get Rounds
		> Get Query Type
		> Make Loop Process
			> Get Single Round
			> Update Round ID & Payment Method To Ride
			> Find Drivers
			> Direct Assign [PREMIUM MEMBERS]
				> Insert Buzz
				> Sent Buzz
				> Update Related Tables
				> IF Buzz Not Sent
					> Update Related Tables
					> CONTINUE
				  ELSE 
				  	> Assign Ride
					> Overwrite Vehicle wise charges, IF Found
					>>>>>>>> RESPONSE
				
			> Send Buzz [NON PREMIUM MEMBERS]
				> Insert Buzz
				> Sent Buzz
				> Update Failed Data
				> Update Success Data
				> Checking Buzz, IF Any Accepted
					> Overwrite Vehicle wise charges, IF Found
					> Make other driver buzzed = 0
					>>>>>>>> RESPONSE
				ELSE 
					> CONTINUE
						
			
		*/
		
		var _ride = {};
		var _round = {};
		
		var _tokensArr = [];
		var _driverArr = [];
		var directBuzzQuery = [];
		
		var isMultiBuzzSent = 0;
		var multiBuzzQuery = [];
		var buzzSentDriverIDs = {
			'succ' : [],
			'fail' : [],
		};
		
		var pickup_lat = '';
		var pickup_lng = '';
		var pickup_Address = '';
		var destin_Address = '';
		var vehicle_type = '';
		
		var driverIDs = [];
		
		var mainData = {
			
			'isDirectAssign' : 0,
			
			'_query_type' : 'simple',
			'_ride' : {},
			'_rounds' : [],
			'_processedRounds' : [],
			'_processedQueries' : [],
			'isMultiBuzzSent' : [],
			'_q_Arr' : [],
			'buzzSentDriverIDs' : [],
		};
		
		async.series([
			
			// Get Ride
			function( callback ){
				
				var _q = " SELECT ";
				_q += " id ";
				_q += " , l_data ";
				_q += " FROM tbl_ride WHERE id = '"+i_ride_id+"' ";
				
				dclass._query( _q, function( status, _rideData ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', _response );
					}
					else if( !_rideData.length ){
						gnrl._api_response( res, 0, 'err_no_ride', _response );
					}
					else{
						
						_ride 			= _rideData[0];
						pickup_lat 		= _ride.l_data.pickup_latitude ? _ride.l_data.pickup_latitude : '';
						pickup_lng 		= _ride.l_data.pickup_longitude ? _ride.l_data.pickup_longitude : '';
						pickup_Address 	= _ride.l_data.pickup_address ? _ride.l_data.pickup_address : '';
						destin_Address 	= _ride.l_data.destination_address ? _ride.l_data.destination_address : '';
						vehicle_type 	= _ride.l_data.vehicle_type ? _ride.l_data.vehicle_type : '';
						
						//gnrl._api_response( res, 1, '', _rideData ); return;
						
						mainData._ride 	= _ride;
						
						callback( null );
					}
				});
			},
			
			// Get Rounds
			function( callback ){
				var _q = " SELECT * FROM tbl_round WHERE i_delete = '0' AND e_status = 'active' ORDER BY i_order ";
				dclass._query( _q, function( status, _rounds ){ 
				
					if( !status ){
						gnrl._api_response( res, 0, 'error', _response );
					}
					else if( !_rounds.length ){
						gnrl._api_response( res, 0, 'err_drivers_not_found_try_again', _response );
					}
					else{
						for( var k in _rounds ){
							var _entity = _rounds[k].l_data.entity;
							for( var k1 in _entity ){
								_entity[k1].check = parseFloat( _entity[k1].check );
								_entity[k1].value = parseFloat( _entity[k1].value );
							}
							_rounds[k].l_data.entity = _entity;
							_rounds[k].l_data.buzz_time = parseInt( _rounds[k].l_data.buzz_time ? _rounds[k].l_data.buzz_time : 20 );
							_rounds[k].l_data.buzz_count = parseInt( _rounds[k].l_data.buzz_count ? _rounds[k].l_data.buzz_count : 10 );
						}
						mainData._rounds = _rounds;
						callback( null );
					}
				});
			},
			
			// Get Query Type
			function( callback ){
				var _q = " SELECT l_value FROM tbl_sitesetting WHERE v_key = 'DRIVER_SEARCH_QUERY' ";
				dclass._query( _q, function( status, data ){ 
					if( status && data.length ){
						mainData._query_type = data[0].l_value;
					}
					callback( null );
				});
			},
			
			// Make Loop Process
			function( callback ){
				
				var roundCount = mainData._rounds.length;
				var processArr = [];
				
				for( var i = 0; i < roundCount; i++ ){
					
					// Get Single Round
					processArr.push( function( callback ){
						_round = mainData._rounds[0];
						mainData._processedRounds.push( _round );
						mainData._rounds.shift();
						callback( null );
					});
					
					// Update Round ID & Payment Method To Ride
					processArr.push( function( callback ){
						var _ins = [
							( " i_round_id = '"+_round.id+"' " ),
							( " l_data = l_data || '"+gnrl._json_encode({
								'round_id' : _round.id,
								'round_order' : _round.i_order,
								'payment_mode' : payment_mode
							})+"' " ),
						];
						dclass._updateJsonb( 'tbl_ride', _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){ 
							callback( null );
						});
					});
					
					// Find Drivers
					processArr.push( function( callback ){
						
						_tokensArr = [];
						_driverArr = [];
						directBuzzQuery = [];
						multiBuzzQuery = [];
						
						var _entity = _round.l_data.entity;
						
						if( mainData._query_type == 'simple' ){
							
							var _q = " SELECT ";
							_q += " * ";
							_q += " FROM ( ";
								_q += " SELECT ";
								_q += " U.id ";
								_q += " , inb.id AS vehicle_id ";
								_q += " , "+gnrl._distQuery( pickup_lat, pickup_lng, "U.l_latitude::double precision", "U.l_longitude::double precision" )+" AS distance";
								_q += " , U.v_device_token ";
								_q += " , U.lang ";
								_q += " FROM tbl_user U ";
								_q += " LEFT JOIN tbl_vehicle inb ON U.id = inb.i_driver_id ";
								_q += " WHERE true ";
								_q += " AND inb.v_type = '"+vehicle_type+"' ";
								_q += " AND inb.id > 0 ";
								_q += " AND U.v_role = 'driver' ";
								_q += " AND U.e_status = 'active' ";
								_q += " AND U.is_onduty = '1' ";
								_q += " AND U.is_onride = '0' ";
								_q += " AND U.is_buzzed = '0' ";
								_q += " AND U.v_token != '' ";
							_q += " ) a ";
							_q += " WHERE true ";
							_q += " ORDER BY a.distance ASC";
							_q += " LIMIT "+_round.l_data.buzz_count; 
							
						}
						else{
						
							var _q = " SELECT ";
							_q += " * ";
							_q += " FROM ( ";
								_q += " SELECT ";
								
								_q += " U.id ";
								_q += " , inb.id AS vehicle_id ";
								_q += " , "+gnrl._distQuery( pickup_lat, pickup_lng, "U.l_latitude::double precision", "U.l_longitude::double precision" )+" AS distance";
								_q += " , U.v_device_token ";
								_q += " , U.lang ";
								_q += " , COALESCE( ( U.l_data->>'rate' )::numeric, 0 ) AS rate ";
								_q += " , U.is_premium ";
								_q += " , ( SELECT COUNT(id) FROM tbl_ride WHERE e_status = 'complete' AND i_driver_id = U.id AND d_time >= '"+gnrl._db_ymd('Y-m-d')+" 00:00:00' AND d_time <= '"+gnrl._db_ymd('Y-m-d')+" 23:59:00' ) AS today_trip_count ";
								_q += " , ( SELECT COUNT(id) FROM tbl_buzz WHERE ( i_status = -1 OR i_status = -2 ) AND i_driver_id = U.id AND d_time >= '"+gnrl._db_ymd('Y-m-d')+" 00:00:00' AND d_time <= '"+gnrl._db_ymd('Y-m-d')+" 23:59:00' ) AS today_buzz_count ";
								_q += " , ( SELECT COUNT(id) FROM tbl_buzz WHERE ( i_status != -1 AND i_status != 1 ) AND i_ride_id = '"+i_ride_id+"' AND i_driver_id = U.id ) AS same_ride_buzz_count ";
								
								_q += " FROM tbl_user U ";
								_q += " LEFT JOIN tbl_vehicle inb ON U.id = inb.i_driver_id ";
								_q += " WHERE true ";
								_q += " AND inb.v_type = '"+vehicle_type+"' ";
								_q += " AND inb.id > 0 ";
								_q += " AND U.v_role = 'driver' ";
								_q += " AND U.e_status = 'active' ";
								_q += " AND U.is_onduty = '1' ";
								_q += " AND U.is_onride = '0' ";
								_q += " AND U.is_buzzed = '0' ";
								_q += " AND U.v_token != '' ";
							_q += " ) a ";
							_q += " WHERE true ";
							_q += " AND a.same_ride_buzz_count <= '0' ";
							
							mainData.isDirectAssign = _entity.premium_driver.check;
							if( _entity.premium_driver.check ){ _q += " AND a.is_premium = '1' "; }
							if( _entity.lowest_trip.check ){ _q += " AND a.today_trip_count <= '"+_entity.lowest_trip.value+"' "; }	
							if( _entity.max_dry_run.check ){ _q += " AND a.distance <= '"+_entity.max_dry_run.value+"' "; }
							if( _entity.rating.check ){ _q += " AND a.rate >= '"+_entity.rating.value+"' "; }
							if( _entity.already_offered.check ){ _q += " AND a.today_buzz_count <= '"+_entity.already_offered.value+"' "; }
							
							_q += " ORDER BY a.distance ASC, a.rate DESC ";
							
							if( mainData.isDirectAssign ){ 
								_q += " LIMIT 1 "; 
							} 
							else { 
								_q += " LIMIT "+_round.l_data.buzz_count; 
							}
						
						}
						
						_p( '------------ROUND : ', _round.id );
						_p( '------------ROUND QUERY : ', _q );
						
						mainData._processedQueries.push( _q );
						
						
						
						dclass._query( _q, function( status, data ){ 
						
							if( status && data.length ){
								
								_driverArr = data;
								
								for( var i = 0; i < _driverArr.length; i++ ){
									
									var singleDriver = _driverArr[i];
									
									_tokensArr.push({
										id 		: singleDriver.id,
										token 	: singleDriver.v_device_token,
										lang 	: singleDriver.lang,
									});
									
									// Multi Buzz Query
									var _q = " INSERT INTO tbl_buzz ";
									_q += " ( i_ride_id, i_driver_id, i_vehicle_id, i_round_id, d_time, i_status ) ";
									_q += " VALUES ";
									_q += " ( "+i_ride_id+", "+singleDriver.id+", "+singleDriver.vehicle_id+", "+_round.id+", '"+gnrl._db_datetime()+"', 0 ); ";
									multiBuzzQuery.push( _q );
									
									// For Direct Assign
									directBuzzQuery.push({
										'i_ride_id' : i_ride_id,
										'i_driver_id' : singleDriver.id,
										'i_vehicle_id' : singleDriver.vehicle_id,
										'i_round_id' : _round.id,
										'd_time' : gnrl._db_datetime(),
										'i_status' : 1,
									});
									
									if( mainData.isDirectAssign && directBuzzQuery.length ){
										driverIDs = [];
										driverIDs.push( _driverArr[0].id );
									}
									else{
										driverIDs = [];
										for( var i = 0; i < _driverArr.length; i++ ){
											driverIDs.push( _driverArr[i].id );
										}
									}
									
								}
								
								callback( null );
								
							}
							else{
								
								callback( null );
								
							}
						});
						
					});
					
					// Direct Assign [PREMIUM MEMBERS]
					processArr.push( function( callback ){
						
						if( mainData.isDirectAssign && directBuzzQuery.length ){
							
							var buzzIns = directBuzzQuery[0];							
							var i_buzz_id = 0;
							var isBuzzSent = 0;
							
							async.series([
								
								// Insert Buzz
								function( callback ){
									dclass._insert( 'tbl_buzz', buzzIns, function( status, data ){
										if( !status ){
											callback( null );
										}
										else{
											i_buzz_id = data.id;
											callback( null );
										}
									});	
								},
								
								// Send Buzz
								function( callback ){
									if( i_buzz_id == 0 ){
										callback( null );
									}
									else{
										Notification.send({
											_key 		: 'driver_ride_assign',
											_role 		: 'driver',
											_tokens 	: _tokensArr,
											_keywords : {
												'[pickup_address]' 		: pickup_Address,
												'[destination_address]' : destin_Address
											},
											_custom_params : {
												i_ride_id			: i_ride_id,
												i_round_id     		: _round.id,
												i_user_id      		: login_id,
												buzz_time      		: _round.l_data.buzz_time,
												pickup_address 		: pickup_Address,
												destination_address : destin_Address,
											},
											_need_log : 0,
										}, function( notiErr, notiStatus ){
											if( notiStatus.succ.length > 0 ){
												isBuzzSent = 1;
											}
											callback( null );
										});
									}
								},
								
								// Update Related Tables
								function( callback ){
									
									if( !isBuzzSent ){
										
										var _q_Arr = [];
										_q_Arr.push( "UPDATE tbl_user SET is_onride = '0', is_buzzed = '0' WHERE id = '"+buzzIns.i_driver_id+"'; ");
										_q_Arr.push( "UPDATE tbl_buzz SET i_status = '-4' WHERE id = '"+i_buzz_id+"'; ");
										_q_Arr = _q_Arr.join('');
										dclass._query( _q_Arr, function( status, data ){
											callback( null );
										});
										
									}
									else {
										
										async.series([
											
											// Assign Ride
											function( callback ){
												var _q_Arr = [];
												_q_Arr.push( "UPDATE tbl_ride SET i_driver_id = '"+buzzIns.i_driver_id+"', i_vehicle_id = '"+buzzIns.i_vehicle_id+"', e_status = 'confirm' WHERE id = '"+i_ride_id+"'; ");
												_q_Arr.push( "UPDATE tbl_user SET is_onride = '1', is_buzzed = '1' WHERE id = '"+buzzIns.i_driver_id+"'; ");
												_q_Arr = _q_Arr.join('');
												dclass._query( _q_Arr, function( status, data ){
													callback( null );
												});
											},
											
											// Overwrite Vehicle wise charges, IF Found
											function( callback ){
												Ride.overWriteChargeVehicleWise( i_ride_id, buzzIns.i_vehicle_id, _ride.l_data, function(){
													callback( null );
												});
											},
											
											// Make Drivers UnBuzzed
											function( callback ){
												var _q = " UPDATE tbl_user SET is_buzzed = '0' WHERE id IN ("+driverIDs.join(',')+"); ";
												dclass._query( _q, function( status, data ){
													callback( null );
												});
											},
											
										], function( error, results ){
											
											_response.confirm = 1;
											
											gnrl._api_response( res, 1, _message, _response );
											
										});
										
									}
									
								}
								
							], function( error, results ){
								
								callback( null );
								
							});
							
						}
						else{
							callback( null );
						}
					});
					
					// Send Buzz [NON PREMIUM MEMBERS]
					processArr.push( function( callback ){
						
						isMultiBuzzSent = 0;
						
						
						if( multiBuzzQuery.length ){
							
							var _multiBuzzQ = multiBuzzQuery.join(';');
							
							buzzSentDriverIDs = {
								'succ' : [],
								'fail' : [],
							};
							
							async.series([
								
								// Insert Buzz
								function( callback ){
									dclass._query( _multiBuzzQ, function( status, data ){
										callback( null );
									});
								},
								
								// Sent Buzz
								function( callback ){
									
									Notification.send({
										_key 		: 'driver_ride_buzz',
										_role 		: 'driver',
										_tokens 	: _tokensArr,
										_keywords 	: {
											'[pickup_address]' 		: pickup_Address,
											'[destination_address]' : destin_Address
										},
										_custom_params : {
											i_ride_id      		: i_ride_id,
											i_round_id     		: _round.id,
											i_user_id      		: login_id,
											buzz_time      		: _round.l_data.buzz_time,
											pickup_address 		: pickup_Address,
											destination_address : destin_Address,
										},
										_need_log : 0,
									}, function( notiErr, notiStatus ){
										
										buzzSentDriverIDs['succ'] = notiStatus.succ;
										buzzSentDriverIDs['fail'] = notiStatus.fail;
										
										if( buzzSentDriverIDs['succ'].length ){
											isMultiBuzzSent = 1;
										}
										
										mainData.buzzSentDriverIDs.push( buzzSentDriverIDs );
										mainData.isMultiBuzzSent.push( isMultiBuzzSent );
										
										callback( null );
									});
									
								},
								
								// Update Failed Data
								function( callback ){
									if( !buzzSentDriverIDs['fail'].length ){
										callback( null );
									}
									else{
										var _q_Arr = [];
										_q_Arr.push( "UPDATE tbl_buzz SET i_status = '-4' WHERE i_ride_id = '"+i_ride_id+"' AND i_round_id = '"+_round.id+"' AND i_driver_id IN ("+buzzSentDriverIDs['fail'].join(',')+"); ");
										dclass._query( _q_Arr.join(''), function( status, data ){
											callback( null );
										});
									}
								},
								
								// Update Success Data
								function( callback ){
									if( !buzzSentDriverIDs['succ'].length ){
										callback( null );
									}
									else {
										var _q_Arr = [];
										_q_Arr.push( "UPDATE tbl_user SET is_buzzed = '1' WHERE id IN ("+buzzSentDriverIDs['succ'].join(',')+"); ");
										dclass._query( _q_Arr.join(''), function( status, data ){
											callback( null );
										});
									}
								},
								
								// Checking Buzz, IF Any Accepted
								function( callback ){
									
									
									
									if( !isMultiBuzzSent ){
										
										callback( null );
										
									}
									else{
							
										var buzzAcceptArray = [];
										
										var totalTimeLoop = parseInt( _round.l_data.buzz_time ) / 2;
										
										while( totalTimeLoop > 0 ){
											
											buzzAcceptArray.push( function( callback ){
												setTimeout( function(){
													_p( 'Sleep' );
													callback( null );
												}, 2000 );
											});
											
											buzzAcceptArray.push( function( callback ){
											
												var _q = "SELECT i_driver_id, i_vehicle_id FROM tbl_ride WHERE id = '"+i_ride_id+"' AND e_status = 'confirm'; ";
												
												dclass._query( _q, function( status, data ){
													
													if( status && data.length ){
														
														var i_vehicle_id = data[0].i_vehicle_id;
														
														async.series([
															
															// Hide Other Buzz
															function( callback ){
																
																_tokensArr = [];
																
																for( var i = 0; i < _driverArr.length; i++ ){
																	
																	if( _driverArr[i].id == data[0].i_driver_id ){ continue; }
									
																	var singleDriver = _driverArr[i];
																	_tokensArr.push({
																		id 		: singleDriver.id,
																		token 	: singleDriver.v_device_token,
																		lang 	: singleDriver.lang,
																	});
																	
																}
																
																Notification.send({
																	_key 			: 'driver_ride_other_assign',
																	_role 			: 'driver',
																	_tokens 		: _tokensArr,
																	_keywords 		: {},
																	_custom_params 	: {},
																	_need_log 		: 0,
																}, function( err, response ){
																	callback( null );
																});
																
															},
															
															// Overwrite Vehicle wise charges, IF Found
															function( callback ){
																Ride.overWriteChargeVehicleWise( i_ride_id, i_vehicle_id, _ride.l_data, function(){
																	callback( null );
																});
															},
															
															// Make Drivers UnBuzzed
															function( callback ){
																
																var _q = " UPDATE tbl_user SET is_buzzed = '0' WHERE id IN ("+driverIDs.join(',')+"); ";
																dclass._query( _q, function( status, data ){
																	callback( null );
																});
																
															},
															
														], function( error, results ){
															
															_response.confirm = 1;
															
															gnrl._api_response( res, 1, _message, _response );
															
														});
														
													}
													else{
														
														callback( null );
														
													}
												});
												
											});
											
											totalTimeLoop--;
											
										}
										
										async.series( buzzAcceptArray, function( error, results ){
											
											var _q = " UPDATE tbl_user SET is_buzzed = '0' WHERE id IN ("+driverIDs.join(',')+"); ";
											dclass._query( _q, function( status, data ){
												callback( null );
											});
											
										});
										
									}
									
								}
								
							], function( error, results ){
								callback( null );
							});
							
						}
						else{
							callback( null );
						}
						
					});
					
				}
				
				async.series( processArr, function( error, results ){
					callback( null );
				});
				
			},
			
			
		], function( error, results ){
			
			gnrl._api_response( res, 0, 'err_drivers_not_found_try_again', _response );
			
		});
		
		
	}
	
};

module.exports = currentApi;
