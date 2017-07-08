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
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	var payment_mode = gnrl._is_undf( params.payment_mode ).trim();
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	if( !payment_mode ){ _status = 0; _message = 'err_req_payment_mode'; }
	
	if( _status ){
		
		/*
			
			Start : Get Number of Round
		
			Process
			>>> Get Ride
			>>> Get Round
			>>> Update Round ID to Ride
			>>> Get Query Type
			>>> Find Drivers Make Query
			>>> Find Drivers Fire Query
			>>> Get Device Tokens & Make Buzz Insert Query
			
			IF Found
				IF Premium
					>>> Direct Assign
				ELSE
					>>> Send Buzz
					>>> Checking Buzz, if any accept
			ELSE
				Continue
		*/
		
		var isProcess = 1;
		
		var _ride = {};
		var _round = {};
		var _driverArr = [];
		var _query_type = '';
		var pickup_lat = '';
		var pickup_lng = '';
				
		var mainData = {
			'isAccepted' : 0,
			'isProcess' : 1,
			'isDirectAssign' : 0,
			'isDriverFound' : 0,
		};
		
		
		// Get Round Count
		dclass._select( 'COUNT(*) AS total_rounds', 'tbl_round', " AND i_delete = '0' AND e_status = 'active' ", function( round_count_status, round_count_data ){ 

			if( !round_count_status ){
				gnrl._api_response( res, 0, '', { confirm : 0 }, 1 );
			}
			else{
				
				var roundCount = round_count_data[0].total_rounds;
				var processArr = [];
				
				for( var i = 0; i < roundCount; i++ ){
					
					// >>> Get Ride
					processArr.push( function( callback ){
						if( !mainData.isProcess ){
							callback( null );
						}
						else{
							dclass._select( '*', 'tbl_ride', " AND id = '"+i_ride_id+"' ", function( ride_status, ride_data ){ 
								if( !ride_status ){
									mainData.isProcess = 0;
								}
								else if( !ride_data.length ){
									mainData.isProcess = 0;
								}
								else{
									_ride = ride_data[0];
									pickup_lat = _ride.l_data.pickup_latitude;
									pickup_lng = _ride.l_data.pickup_longitude;
									mainData._ride = _ride;
								}
								callback( null );
							});
						}
					});
					
					// >>> Get Round
					processArr.push( function( callback ){
						
						if( !mainData.isProcess ){
							callback( null );
						}
						else {
							
							var round_id = _ride.l_data.round_id;
							var round_order = _ride.l_data.round_order;
							
							var whRound = " AND i_delete = '0' AND e_status = 'active' ";
							whRound += " AND id != '"+round_id+"' AND i_order > '"+round_order+"' ";
							whRound += " ORDER BY i_order ASC LIMIT 1";
							
							dclass._select( '*', 'tbl_round', whRound, function( round_status, round_data ){ 
								if( !round_status ){
									mainData.isProcess = 0;
									callback( null );
								}
								else if( !round_data.length ){
									mainData.isProcess = 0;
									callback( null );
								}
								else{
									_round = round_data[0];
									mainData._round = _round;
									mainData.buzzTime = _round.l_data.buzz_time;
									mainData.buzz_count = _round.l_data.buzz_count;
									callback( null );
								}
							});
						}
						
					});
					
					// >>> Update Round ID & Payment Mode To Ride
					processArr.push( function( callback ){
						var _ins = [
							( " i_round_id = '"+_round.id+"' " ),
							( " l_data = l_data || '"+gnrl._json_encode({
								'round_id' : _round.id,
								'round_order' : _round.i_order,
								'payment_mode' : payment_mode,
							})+"' " ),
						];
						dclass._updateJsonb( 'tbl_ride', _ins, " AND id = '"+_ride.id+"' ", function( updateRideStatus, updateRideData ){ 
							if( !updateRideStatus ){
								callback( null );
							}
							else{
								callback( null );
							}
						});
					});
					
					// >>> Get Query Type
					processArr.push( function( callback ){
						if( _query_type != '' ){
							callback( null );
						}
						else{
							dclass._select( "*", "tbl_sitesetting", " AND v_key = 'DRIVER_SEARCH_QUERY' ", function( q_status, q_data ){ 
								if( !q_status ){
									callback( null );
								}
								else if( !q_data.length ){
									callback( null );
								}
								else{
									_query_type = q_data[0].l_value;
									callback( null );
								}
							});
						}
					});
					
					// >>> Find Drivers Make Query
					processArr.push( function( callback ){
						
						if( !mainData.isProcess ){
							callback( null );
						}
						else{
							
							var _entity = _round.l_data.entity;
							for( var k in _entity ){
								_entity[k].check = parseFloat( _entity[k].check );
								_entity[k].value = parseFloat( _entity[k].value );
							}
							
							if( _query_type == 'simple' ){
								
								var _q = " SELECT ";
								_q += " * ";
								_q += " FROM ( ";
									_q += " SELECT ";
									_q += " U.id ";
									_q += " , inb.id AS vehicle_id ";
									_q += " , "+gnrl._distQuery( pickup_lat, pickup_lng, "U.l_latitude::double precision", "U.l_longitude::double precision" )+" AS distance";
									_q += " , U.v_device_token ";
									_q += " , COALESCE( U.l_data->'lang', '"+_lang+"' ) AS lang ";
									_q += " FROM tbl_user U ";
									_q += " LEFT JOIN tbl_vehicle inb ON U.id = inb.i_driver_id ";
									_q += " WHERE true ";
									_q += " AND inb.v_type = '"+_ride.l_data.vehicle_type+"' ";
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
								_q += " LIMIT "+( _round.l_data.buzz_count ? _round.l_data.buzz_count : 10 ); 
								
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
									_q += " , COALESCE( U.l_data->>'lang', '"+_lang+"' ) AS lang ";
									
									_q += " , COALESCE( U.l_data->>'rate', '0' ) AS rate ";
									_q += " , U.is_premium ";
									_q += " , ( SELECT COUNT(id) FROM tbl_ride WHERE e_status = 'complete' AND i_driver_id = U.id AND d_time >= '"+gnrl._db_ymd('Y-m-d')+" 00:00:00' AND d_time <= '"+gnrl._db_ymd('Y-m-d')+" 23:59:00' ) AS today_trip_count ";
									_q += " , ( SELECT COUNT(id) FROM tbl_buzz WHERE ( i_status = -1 OR i_status = -2 ) AND i_driver_id = U.id AND d_time >= '"+gnrl._db_ymd('Y-m-d')+" 00:00:00' AND d_time <= '"+gnrl._db_ymd('Y-m-d')+" 23:59:00' ) AS today_buzz_count ";
									_q += " , ( SELECT COUNT(id) FROM tbl_buzz WHERE ( i_status != -1 AND i_status != 1 ) AND i_ride_id = '"+i_ride_id+"' AND i_driver_id = U.id ) AS same_ride_buzz_count ";
									
									_q += " FROM tbl_user U ";
									_q += " LEFT JOIN tbl_vehicle inb ON U.id = inb.i_driver_id ";
									_q += " WHERE true ";
									_q += " AND inb.v_type = '"+_ride.l_data.vehicle_type+"' ";
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
									_q += " LIMIT "+( _round.l_data.buzz_count ? _round.l_data.buzz_count : 10 ); 
								}
							
							}
							
							
							_p( 'Round', _round.id );
							_p( '_q', _q );
							
							mainData.findDriverQuery = _q;
							
							callback( null );
						}
					});
					
					// >>> Find Drivers Fire Query
					processArr.push( function( callback ){
						
						mainData.isDriverFound = 0;
						
						if( !mainData.isProcess ){
							callback( null );
						}
						else{
							
							var _q = mainData.findDriverQuery;
							
							dclass._query( _q, function( driver_status, driver_data ){ 
								if( !driver_status ){
									callback( null );
								}
								else if( !driver_data.length ){
									callback( null );
								}
								else{
									mainData.isDriverFound = 1;
									_driverArr = driver_data;
									mainData._driverArr = _driverArr;
									callback( null );
								}
								
							});
							
						}
					});
					
					// >>> Get Device Tokens & Make Buzz Insert Query
					processArr.push( function( callback ){
						
						if( !mainData.isProcess ){
							callback( null );
						}
						else if( !mainData.isDriverFound ){
							callback( null );
						}
						else{
							
							var directBuzzQuery = [];
							var multiBuzzQuery = [];
							var _tokensArr = [];
							// _p( '_driverArr', _driverArr );
							
							for( var i = 0; i < _driverArr.length; i++ ){
								
								var singleDriver = _driverArr[i];
								singleDriver.lang = singleDriver.lang ? singleDriver.lang : _lang;
								
								if( !gnrl._isNull( singleDriver.v_device_token ) ){
									
									var tempObj = {
										id 		: singleDriver.id,
										token 	: singleDriver.v_device_token,
										lang 	: singleDriver.lang,
									};
									_tokensArr.push( tempObj );
									
									var _q = " INSERT INTO tbl_buzz ";
									_q += " ( i_ride_id, i_driver_id, i_vehicle_id, i_round_id, d_time, i_status ) ";
									_q += " VALUES ";
									_q += " ( "+i_ride_id+", "+singleDriver.id+", "+singleDriver.vehicle_id+", "+_round.id+", '"+gnrl._db_datetime()+"', 0); ";
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
									
								}
							}

							mainData.directBuzzQuery = directBuzzQuery;
							mainData.multiBuzzQuery = multiBuzzQuery;


							mainData._tokensArr = _tokensArr;
							
							if( !multiBuzzQuery.length ){
								mainData.isDriverFound = 0;
							}
							
							callback( null );
						}
					});
					
					// >>> Direct Assign [PREMIUM MEMBERS]
					processArr.push( function( callback ){
						
						if( !mainData.isProcess ){
							callback( null );
						}
						else if( !mainData.isDriverFound ){
							callback( null );
						}
						else if( !mainData.isDirectAssign ){
							callback( null );
						}
						else{
														
							var i_buzz_id = 0;
							var isBuzzSent = 0;
							var buzzIns = mainData.directBuzzQuery[0];
							
							async.series([
								
								// Insert Buzz
								function( callback ){
									dclass._insert( 'tbl_buzz', buzzIns, function( buzz_status, buzz_data ){
										if( !buzz_status ){
											callback( null );
										}
										else{
											i_buzz_id = buzz_data.id;
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
										
										var pickup_address = _ride.l_data.pickup_address ? _ride.l_data.pickup_address : '';
										var destination_address = _ride.l_data.destination_address ? _ride.l_data.destination_address : '';
									
										var notificationParams = {
											_key : 'driver_ride_assign', // buzz_show
											_role : 'driver',
											_tokens : mainData._tokensArr,
											_keywords : {
												'[pickup_address]' : pickup_address,
												'[destination_address]' : destination_address
											},
											_custom_params : {
												i_ride_id      : i_ride_id,
												i_user_id      : login_id,
												buzz_time      : mainData.buzzTime,
												pickup_address : pickup_address,
												destination_address : destination_address,
												i_round_id     : _round.id
											},
											_need_log : 1,
										};
										Notification.send( notificationParams, function( notiErr, notiStatus ){
											
											if( notiStatus.succ.length > 0 ){
												isBuzzSent = 1;
											}
											callback( null );
										});
									}
								},
								
								// Update Related Tables
								function( callback ){
									if( i_buzz_id == 0 ){
										callback( null );
									}
									else{
										
										if( isBuzzSent ){
											
											var _q_Arr = [];
											_q_Arr.push( "UPDATE tbl_ride SET i_driver_id = '"+buzzIns.i_driver_id+"', i_vehicle_id = '"+buzzIns.i_vehicle_id+"', e_status = 'confirm' WHERE id = '"+_ride.id+"'; ");
											_q_Arr.push( "UPDATE tbl_user SET is_onride = '1', is_buzzed = '1' WHERE id = '"+buzzIns.i_driver_id+"'; ");
											_q_Arr.push( "UPDATE tbl_buzz SET i_status = '1' WHERE id = '"+i_buzz_id+"'; ");
											_q_Arr = _q_Arr.join('');
											dclass._query( _q_Arr, function( _q_Arr_status, _q_Arr_data ){
												mainData.isAccepted = 1;
												mainData.isProcess = 0;
												Ride.overWriteChargeVehicleWise( i_ride_id, function(){
													callback( null );
												});
											});
											
										}
										else{
											
											var _q_Arr = [];
											_q_Arr.push( "UPDATE tbl_user SET is_onride = '0', is_buzzed = '0' WHERE id = '"+buzzIns.i_driver_id+"'; ");
											_q_Arr.push( "UPDATE tbl_buzz SET i_status = '-4' WHERE id = '"+i_buzz_id+"'; ");
											_q_Arr = _q_Arr.join('');
											dclass._query( _q_Arr, function( _q_Arr_status, _q_Arr_data ){
												callback( null );
											});
											
										}
									}
								}
								
							], function( error_direct_assign, results_direct_assign ){
								callback( null );
							});
							
						}
					});
					
					// >>> Send Buzz [NON PREMIUM MEMBERS]
					processArr.push( function( callback ){
						
						if( !mainData.isProcess ){
							callback( null );
						}
						else if( !mainData.isDriverFound ){
							callback( null );
						}
						else if( mainData.isDirectAssign ){
							callback( null );
						}
						else{
							
							var _multiBuzzQ = mainData.multiBuzzQuery.join(';');
							
							var buzzSentDriverIDs = {
								'succ' : [],
								'fail' : [],
							};
							
							async.series([
								
								// Insert Buzz
								function( callback ){
									dclass._query( _multiBuzzQ, function( insert_buzz_status, insert_buzz_data ){
										callback( null );
									});
								},
								
								// Send Notification
								function( callback ){
									
									var pickup_address = _ride.l_data.pickup_address ? _ride.l_data.pickup_address : '';
									var destination_address = _ride.l_data.destination_address ? _ride.l_data.destination_address : '';
									
									var notificationParams = {
										
										_key : 'driver_ride_buzz', // buzz_show
										_role : 'driver',
										_tokens : mainData._tokensArr,
										_keywords : {
											'[pickup_address]' : pickup_address,
											'[destination_address]' : destination_address
										},
										_custom_params : {
											i_ride_id      : i_ride_id,
											i_user_id      : login_id,
											buzz_time      : mainData.buzzTime,
											pickup_address : pickup_address,
											destination_address : destination_address,
											i_round_id     : _round.id
										},
										_need_log : 0,
									};
									
									Notification.send( notificationParams, function( notiErr, notiStatus ){
										
										buzzSentDriverIDs['succ'] = notiStatus.succ;
										buzzSentDriverIDs['fail'] = notiStatus.fail;
										callback( null );
									});
									
								},
								
								// Update Success Data
								function( callback ){
									if( !buzzSentDriverIDs['succ'].length ){
										callback( null );
									}
									else {
										var _q_Arr = [];
										_q_Arr.push( "UPDATE tbl_user SET is_buzzed = '1' WHERE id IN ("+buzzSentDriverIDs['succ'].join(',')+"); ");
										_q_Arr = _q_Arr.join('');
										dclass._query( _q_Arr, function( _q_Arr_status, _q_Arr_data ){
											callback( null );
										});
									}
								},
								
								// Update Failed Data
								function( callback ){
									if( !buzzSentDriverIDs['fail'].length ){
										callback( null );
									}
									else{
										var _q_Arr = [];
										_q_Arr.push( "UPDATE tbl_user SET is_buzzed = '0' WHERE id IN ("+buzzSentDriverIDs['fail'].join(',')+"); ");
										_q_Arr.push( "UPDATE tbl_buzz SET i_status = '-4' WHERE i_ride_id = '"+_ride.id+"' AND i_round_id = '"+_round.id+"' i_driver_id IN ("+buzzSentDriverIDs['fail'].join(',')+"); ");
										_q_Arr = _q_Arr.join('');
										dclass._query( _q_Arr, function( _q_Arr_status, _q_Arr_data ){
											callback( null );
										});
									}
								}
								
								
							], function( error_direct_assign, results_direct_assign ){
								callback( null );
							});
							
						}
					});
					
					// >>> Checking Buzz, if any accept
					processArr.push( function( callback ){
						
						if( !mainData.isProcess ){
							callback( null );
						}
						else if( !mainData.isDriverFound ){
							callback( null );
						}
						else if( mainData.isDirectAssign ){
							callback( null );
						}
						else{
							
							var buzzAcceptArray = [];
							
							var totalTimeLoop = parseInt( mainData.buzzTime ) / 2;
							
							while( totalTimeLoop ){
								buzzAcceptArray.push( function( bzCheckCallback ){
									if( mainData.isAccepted ){
										bzCheckCallback( null );
									}
									else{
										setTimeout( function(){
											_p( 'Sleep' );
											dclass._select( 'id', 'tbl_ride', " AND id = '"+i_ride_id+"' AND e_status = 'confirm' ", function( is_confirm_status, is_confirm_data ){
												if( !is_confirm_status ){
													bzCheckCallback( null );
												}
												else if( !is_confirm_data.length ){
													bzCheckCallback( null );
												}
												else{
													mainData.isAccepted = 1;
													bzCheckCallback( null );
												}
											});
										}, 2000 );
									}
								});
								totalTimeLoop--;
							}
							
							async.series( buzzAcceptArray, function( error_1, results_1 ){
								callback( null );
							});
							
						}
					});
					
					// >>> Make Other Idle
					processArr.push( function( callback ){
						if( mainData.isAccepted ){
							callback( null );
						}
						else{
							callback( null );
						}
					});
					
				}
				
				async.series( processArr, function( error, results ){
					
					_response.confirm = mainData.isAccepted ? mainData.isAccepted : 0;
					
					if( _response.confirm ){
						gnrl._api_response( res, _response.confirm, _message, _response );
					}
					else{
						gnrl._api_response( res, _response.confirm, 'err_drivers_not_found_try_again', _response, 1 );
					}
					
				});
				
			}
		});
		
		
	}
	else{
		gnrl._api_response( res, 0, _message, { confirm : 0 }, 1 );
	}
};

module.exports = currentApi;
