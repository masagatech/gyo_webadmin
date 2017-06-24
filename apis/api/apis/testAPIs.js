var express = require('express');
var validator = require('validator');
var md5 = require('md5');
var http = require('http');
var FCM = require('fcm-node');


var async = require('async');

var currentApi = function saveDriverInfo( req, res, done ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	var action = gnrl._is_undf( params.action ).trim();
	
	if( action == 'timestamp' ){
		gnrl._api_response( res, 1, 'Done', gnrl._timestamp( '2017-04-21 12:50:55+05:30' ) );
	}
	
	else if( action == 'fcm2' ){
		
		var tokens = [];
		tokens.push({
			'id' : '1',
			'lang' : _lang, // _user.l_data.lang ? _user.l_data.lang : 
			'token' : 'cdf7SeEmlJI:APA91bF9-wVwFVTFqagJX5qJKh-3afiasEhrJkkv3Bgkm1f8iHy6rJSovs6aKrzyX52uTQsTsntBz4Xd7jcEwpq64q-n1JSxdXeCHJSfuILqva5Atve2sDbJpYiuSgtnacwYn5xTGEpx',
		});
		var params = {
			_key : 'user_ride_start',
			_role : 'user',
			_tokens : tokens,
			_keywords : {},
			_custom_params : {
				// i_ride_id : i_ride_id,
			},
			_need_log : 0,
		};
		Notification.send( params, function( err, response ){
			_p( 'err', err );
			_p( 'response', response );
			
		});
	}
	else if( action == 'fcm' ){
		
		
		var fcm = new FCM( 'AIzaSyCU8agC8CBQ4h1STU969yQaFCOwtXxeziE' );
		fcm.send({
			registration_ids : [
				'cdf7SeEmlJI:APA91bF9-wVwFVTFqagJX5qJKh-3afiasEhrJkkv3Bgkm1f8iHy6rJSovs6aKrzyX52uTQsTsntBz4Xd7jcEwpq64q-n1JSxdXeCHJSfuILqva5Atve2sDbJpYiuSgtnacwYn5xTGEpx'
			],
			notification : {
				title : 'Testing Demo',
				body : 'Testing Demo',
			},
			data : {
			}
		}, function( err, response ){
			gnrl._api_response( res, 1, 'Done', {
				err : err, 
				response : response,
			});
		});
	
	}
	
	else if( action == 'free_drivers'  ){
		var _q = [];
		_q.push( "update tbl_user SET is_onride = 0, is_buzzed = 0 WHERE v_role = 'driver' ;" );
		dclass._query( _q.join(''), function( status, data ){
			gnrl._api_response( res, 1, 'free_drivers', {});
		})
	}
	
	else if( action == 'truncate' && gnrl._live == 0 ){
		var tables = [
			// 'tbl_user',
			// 'tbl_vehicle',
			'tbl_wallet',
			'tbl_wallet_transaction',
			'tbl_track_vehicle_location',
			'tbl_track_push_notification',
			'tbl_track_messages',
			'tbl_ride_sos',
			'tbl_ride_rate',
			'tbl_ride_payments',
			'tbl_ride_charges',
			'tbl_ride',
			'tbl_buzz',
			
			//'xxxx',
			//'xxxx',
			//'xxxx',
		];
		var _q = [];
		for( var k in tables ){
			_q.push( 'TRUNCATE '+tables[k]+' RESTART IDENTITY;' );
		}
		dclass._query( _q.join(''), function( status, data ){
			gnrl._api_response( res, 1, 'Tables Truncated', {});
		})
	}
	else if( action == 'sms1' ){
	
		var url = 'http://sms.cell24x7.com:1111/mspProducerM/sendSMS?user=Goyo&pwd=goyo123&sender=GoYooo';
		url += '&mt=2';
		url += '&mobile=8866207256';
		url += '&msg=LiveSmsTesting';
		url = url.replace(/ /g, "%20" );
		
		try{
			http.get( url, function( response ){
				var data = '';
				response.on('data', function (chunk) {
					data += chunk;
				});
				response.on('end', function () {
					gnrl._api_response( res, 1, 'Done', { 'data' : data });
				});
			}).end();
		}
		catch ( e ){
			console.log('e ', e );
			gnrl._api_response( res, 1, 'EEEE', { 'e' : e });
		}
	}
	else if( action == 'sms2' ){
		var params = {
			_to      	: '8866207256',
			_lang 		: _lang,
			_key 		: 'user_forgot_password',
			_keywords 	: {
				'[user_name]' : 'USER_NAME',
				'[otp]' : 'OTP',
			},
		};
		SMS.send( params, function( error_mail, error_info ){
			gnrl._api_response( res, 1, 'Done', {
				error_mail : error_mail, 
				error_info : error_info
			});
		});
		
	}
	
	else if( action == 'commission' ){
		var company_commission = '5%';
		gnrl._api_response( res, 1, 'Done', { 
			'company_commission' : gnrl._isPercent( 200, company_commission ),
		});
	}
	else if( action == 'waterfall' ){
		async.waterfall([
			function( callback ){
				callback( null, { 'data_1' : 1 });
			},
			function( arg1, callback ){
				Object.assign( arg1, { 'data_2' : 2 });
				callback( null, arg1 );
			},
			function( arg1, callback ){
				Object.assign( arg1, {'data_3' : 3 });
				callback( null, arg1 );
			}
		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'Done', results );
		});
	}
	else if( action == 'calculateDistances' ){
		Ride.calculateDistances( 314, function( status, data ){
			gnrl._api_response( res, 1, 'Done', {
				data : data,
			});
		});
	}
	else if( action == '_db_period_time' ){
		var dates = gnrl._db_period_time( params.time );
		gnrl._api_response( res, 1, 'Done', dates );
	}
	else if( action == 'distancematrix' ){
		
		var distance = require('google-distance-matrix');
		distance.key('AIzaSyAqet_2gOEySAzx2xLXIBWUZzJGuwb1q6k');
		distance.mode('transit');
		
		
		var funArr = [];
		var lat_longs = [
			'23.0401456,72.51905',
			'23.04034255,72.51911079',
			'23.04029273,72.51881713',
			'23.0401456,72.51905',
			'23.03938623,72.51815984',
			'23.0402041,72.5187727',
			'23.03746533,72.51696945',
			'23.0383959,72.5171089',
			'23.03619647,72.51787658',
			'23.03563094,72.51924804',
			'23.0356845,72.5185879',
			'23.03479716,72.52116003',
			'23.0352141,72.5203903',
			'23.0342204,72.52274442',
			'23.0337687,72.5231171',
		];
		
		var lat_longs = [
			"Platinum Plaza, Satya Marg, Bodakdev, Ahmedabad, Gujarat 380054",
			"Mansi Circle, Satellite, Ahmedabad, Gujarat",
		];
		
		var tempData = [];
		var fulldata = [];
		var finalTotal = 0;
		
		for( var i = 0; i < lat_longs.length; i++ ){
			
			funArr.push( function( callback ){
				
				if( lat_longs.length < 2 ){
					callback( null );
				}
				else{
				
					var origins = [ lat_longs[0] ];
					var destinations = [ lat_longs[1] ];
					
					lat_longs.shift();
					
					distance.matrix( origins, destinations, function( err, distances ){
						
						if (err) {
							callback( null );
							
						}
						else if(!distances) {
							callback( null );
							
						}
						
						else if( distances.status == 'OK' ){
							for (var i=0; i < origins.length; i++) {
								
								for (var j = 0; j < destinations.length; j++) {
									var origin = distances.origin_addresses[i];
									var destination = distances.destination_addresses[j];
									
									if( distances.rows[0].elements[j].status == 'OK' ){
										var distance = distances.rows[i].elements[j].distance.text;
										
										fulldata.push( {
											origin : origin,
											destination : destination,
											distance : distance
										});
										
										distance = distance.split(' ');
										distance[0] = parseFloat( distance[0] );
										
										if( distance[1] == 'm' ){
											distance[0] = distance[0] / 1000;
										}
										tempData.push( distance[0] );
										
										finalTotal += distance[0];
										
									} else {
										tempData.push(destination + ' is not reachable by land from ' + origin);
									}
								}
								
							}
							
							callback( null );
							
						}
						
						
						
					});
					
					
				}
			} );
			
			
		}
		
		
		
		
		async.series( funArr, function( error, results ){
			gnrl._api_response( res, 1, 'Done', {
				finalTotal : finalTotal,
				tempData : tempData,
				fulldata : fulldata,
				results : results
			});
		});
		
	}
	
	else if( action == 'googlemaps' ){
		var gm = require('googlemaps');
		var util = require('util');
		gm.config('key', 'AIzaSyDl178nLe52M8Q8NhTu_rlqnHNHtGxp-l8');
		gm.directions( '23.033888,72.5229784', '23.04056929,72.51872472', function( err, data ){
			// util.puts(JSON.stringify(data));
			gnrl._api_response( res, 1, 'Done', {
				err : err,
				data : data,
			});
		});

		
	}
	
	
	
	else if( action == 'support_types' ){
		var i = 10;
		var ins = {
			'j_title' : {
				'en' : 'Support '+i,
				'hi' : 'Support '+i,
				'gu' : 'Support '+i,
			},
			'j_text' : {
				'en' : 'Support '+i,
				'hi' : 'Support '+i,
				'gu' : 'Support '+i,
			},
			'i_textbox' : 0,
			'i_order' : i,
			'd_added' : gnrl._db_datetime(),
			'd_modified' : gnrl._db_datetime(),
			'e_status' : 'active',
		};
		dclass._insert( 'tbl_support_types', ins, function( is_confirm_status, is_confirm_data ){
			gnrl._api_response( res, 1, 'Done', {
				is_confirm_status : is_confirm_status,
				is_confirm_data : is_confirm_data,
			});
		});
		
	}
	
	else if( action == 'simpleUser' ){
		
		var pickup_lat = '23.0401863';
		var pickup_lng = '72.518715';
		var _ride = {
			l_data : {
				vehicle_type : 'auto'
			}
		};
		var _round = {
			l_data : {
				buzz_count : 20
			}
		};
		
		
		var _q = " SELECT ";
		_q += " id ";
		_q += " , COALESCE( l_data->>'lang', '"+_lang+"' ) as lang ";
		_q += " FROM tbl_user ";
		_q += " WHERE true ";
		
		dclass._query( _q, function( status, data ){
			
			for( var k in data ){
				// data[k].lang = data[k].lang ? data[k].lang : _lang;
			}
			
			gnrl._api_response( res, 1, 'Done', {
				status : status,
				_q : _q,
				data : data,
			});
		});
		
	}
	
	else if( action == 'simpleConfirmRide' ){
		
		var pickup_lat = '23.0401863';
		var pickup_lng = '72.518715';
		var _ride = {
			l_data : {
				vehicle_type : 'auto'
			}
		};
		var _round = {
			l_data : {
				buzz_count : 20
			}
		};
		
		
		var _q = " SELECT ";
		_q += " * ";
		_q += " FROM ( ";
			_q += " SELECT ";
			_q += " U.id ";
			_q += " , inb.id AS vehicle_id ";
			_q += " , "+gnrl._distQuery( pickup_lat, pickup_lng, "U.l_latitude::double precision", "U.l_longitude::double precision" )+" AS distance";
			_q += " , U.v_device_token ";
			_q += " , COALESCE( U.l_data->>'lang', '"+_lang+"' ) AS lang ";
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
		
		dclass._query( _q, function( status, data ){
			
			for( var k in data ){
				// data[k].lang = data[k].lang ? data[k].lang : _lang;
			}
			
			gnrl._api_response( res, 1, 'Done', {
				status : status,
				_q : _q,
				data : data,
			});
		});
		
	}
	
	else if( action == 'complexConfirmRide' ){
		
		// testAPIs?action=complexConfirmRide&i_ride_id=1071&round_id=1&pickup_lat=23.0401863&pickup_lng=72.518715
		
		var pickup_lat 	= params.pickup_lat;
		var pickup_lng 	= params.pickup_lng;
		var i_ride_id 	= params.i_ride_id;
		var round_id 	= params.round_id;
		
		var _ride = {};
		var _round = {};
		var _data = {};
		var mainData = {};
		
		async.series( [
			
			function( callback ){
				
				dclass._select( '*', 'tbl_ride', " AND id = '"+i_ride_id+"' ", function( ride_status, ride_data ){ 
					_ride = ride_data[0];
					_data._ride = _ride;
					callback( null );
				});
				
			},
			
			function( callback ){
				dclass._select( '*', 'tbl_round', " AND id = '"+round_id+"' ", function( round_status, round_data ){ 
					_round = round_data[0];
					_data._round = _round;
					callback( null );
				});
			},
			
			
			function( callback ){
				
				var _entity = _round.l_data.entity;
				for( var k in _entity ){
					_entity[k].check = parseFloat( _entity[k].check );
					_entity[k].value = parseFloat( _entity[k].value );
				}
				
				_data._entity = _entity;
				
				
				
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
				
				_data._q = _q;
				
				
				
				dclass._query( _q, function( status, data ){
					_data.status= status;
					_data.data = data;
					gnrl._api_response( res, 1, 'Done', _data );
				});
				
			}
			
		], function( error, results ){
					
			gnrl._api_response( res, 1, 'Done', _data );
			
		});
		
		
		
	}
	
	else{
		gnrl._api_response( res, 0, 'Action Needed', {} );
	}
	
	
	
};
module.exports = currentApi;