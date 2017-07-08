var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');

var currentApi = function( req, res, next ){
	
	var dclass 	= req.app.get('Database');
	var gnrl 	= req.app.get('gnrl');
	var _p 		= req.app.get('_p');
	
	
	var params 	= gnrl._frm_data( req );
	var _lang 	= gnrl._getLang( params, req.app.get('_lang') );
	
	var _status   = 1;
	var _message  = '';
	var _response = {
		'action' : 'finish',
		'buzz_driver_ids' : '',
		//'premium_driver_id' : 0,
		'buzz_time' : 0,
	};
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	if( !i_ride_id ){ _status = 0; _message = gnrl._lbl( 'err_req_ride_id', _lang ); }
	
	if( _status ){	

		// Get Ride Details
		dclass._select( '*', 'tbl_ride', " AND id = '"+i_ride_id+"'", function( status, ride ){ 

			if( status && !ride.length ){
				gnrl._api_response( res, 0, gnrl._lbl( 'err_no_ride', _lang ), {}, 0 );
			}
			else{
				
				var ride = ride[0];
				
				var round_id 	= ride.i_round_id;
				var round_order = ride.l_data.round_order;
				//round_id = 25;
				//round_order = 25;
				
				var pickup_lat = ride.l_data.pickup_latitude;
				var pickup_lng = ride.l_data.pickup_longitude;
				
				var whRound = " AND e_status = 'active' ";
				whRound += " AND id != '"+round_id+"' AND i_order > '"+round_order+"' ";
				whRound += " ORDER BY i_order ASC LIMIT 1";
				
				// Get Round Details
				dclass._select( '*', 'tbl_round', whRound, function( status_round, _round ){ 
					
					if( !status_round ){
						gnrl._api_response( res, 0, gnrl._lbl( '', _lang ), {} );
					}
					else if( !_round.length ){
						gnrl._api_response( res, 1, gnrl._lbl( 'err_drivers_not_found_try_again', _lang ), _response );
					}
					else{
						
						var _round = _round[0];
						var _entity = _round.l_data.entity;
						
						var _q = " SELECT ";
						_q += " a.* ";
						_q += " ,a.l_data->>'rate' as rating ";
						//_q += " ,b.distance ";
						
						_q += " FROM tbl_user a ";
						//_q += " LEFT JOIN (  ) b ON a.id = b.i_driver_id ";
						_q += " LEFT JOIN ( ";
							_q += " SELECT *";
								/*
								_q += " , ( ";
								_q += " ( ( ( 69.1 * ( l_latitude - "+pickup_lat+" ) ) * ( 69.1 * ( l_latitude - "+pickup_lat+" ) ) ) +  ";
								_q += " + ";
								_q += " ( ( 69.1 * ( l_longitude - "+pickup_lng+" ) * cos( "+pickup_lat+" / 57.3 ) ) * ( 69.1 * ( l_longitude - "+pickup_lng+" ) * cos( "+pickup_lat+" / 57.3 ) ) ) ) ";
								_q += " ) as distance  ";
								*/
							_q += " FROM tbl_vehicle ";
						_q += " ) b ON a.id = b.i_driver_id ";
						_q += " WHERE a.e_status = 'active' ";
						_q += " AND a.v_role = 'driver' ";
						_q += " AND b.v_type = '"+( ride.l_data.vehicle_type )+"' ";
						_q += " AND b.is_idle = '1' ";
						
						var isDirectAssign = 0;
						
						if( _entity.premium_driver.check ){
							_q += " AND b.is_premium = '"+parseInt( _entity.premium_driver.value )+"' ";
							// isDirectAssign = 1;
						}
						if( _entity.lowest_trip.check ){
							_q += " AND ( SELECT COUNT(*) FROM tbl_ride WHERE i_complete = '1' AND i_driver_id = a.id ) < '"+( _entity.lowest_trip.value )+"' ";
						}
						if( _entity.rating.check ){
							_q += " AND a.l_data->>'rate' != '' ";
							//_q += " AND a.l_data->>'rate' >= '"+( _entity.rating.value )+"' ";
						}
						_q += " ORDER BY a.l_data->>'rate' DESC "; //b.distance DESC, 
						if( isDirectAssign ){
							_q += " LIMIT 1 ";
						}
						
						/*
						if( _entity.max_dry_run.check ){ _q += " AND b.distance <= '"+( _entity.max_dry_run.value )+"' "; }
						if( _entity.nearest.check ){ _q += " "; }
						if( _entity.already_offered.check ){ _q += " "; }
						*/
						
						//_response._q = _q;
						//_response._entity = _entity;
						//_response._round = _round;
						//_response.ride = ride;
						
						dclass._query( _q, function( status_driver, drivers ){ 
							if( !status_driver ){
								gnrl._api_response( res, 0, gnrl._lbl( '', _lang ), {}, 0 );
							}
							else if( !drivers.length ){
								_response.action = 'next_call';
								gnrl._api_response( res, 1, gnrl._lbl( 'err_drivers_not_found_try_again', _lang ), _response );
							}
							else{
								
								if( isDirectAssign ){
									
									_response.action = 'direct_assign';
									//_response.premium_driver_id = drivers[0].id;
									_response.buzz_driver_ids = drivers[0].id;
									_response.buzz_time = _round.l_data.buzz_time;
									// _response.drivers = drivers;
									
									gnrl._api_response( res, 1, _message, _response, 0 );
									
								}
								else{
									
									var driverIDs = [];
									for( var i = 0; i < drivers.length; i++ ){
										driverIDs.push( drivers[i].id );										
									}
									
									// SEND BUZZ
									_response.action = 'send_buzz';
									_response.buzz_driver_ids = driverIDs.join(',');
									_response.buzz_time = _round.l_data.buzz_time;

									gnrl._api_response( res, 1, _message, _response, 0 );
									
								}
							}
						});
						
						/*
						// Update Ride Details
						var ride_l_data = ride.l_data;
						ride_l_data.i_round = round.i_order;
						var _ins = {
							'i_round_id' 	: round.id,
							'l_data'        : ride_l_data,
						};
						dclass._update( 'tbl_ride', _ins, " AND id = '"+i_ride_id+"' ", function( status, updateRide ){ 
							if( status ){
								//fs.rename( fileArr['v_image'].path, dirUploads+'/users/'+fileArr['v_image'].name, function(err){});
								//gnrl._api_response( res, 1, gnrl._lbl( 'succ_profile_updated', _lang ), {} );
							}
							else{
								//gnrl._remove_loop_file( fs, fileArr );
								//gnrl._api_response( res, 0, _message, {}, 0 );
							}
						}); */

						//gnrl._api_response( res, 1, _message, round, 0 );

					}

					//gnrl._api_response( res, 1, _message, round, 0 );
				});				

			}

		}); 
		
	}
	else{
		gnrl._api_response( res, 0, _message, {}, 0 );
	}
};

module.exports = currentApi;
