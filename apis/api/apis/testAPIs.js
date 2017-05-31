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
	
	
	
	else if( action == 'refer' ){
		
		
		

		var referral_code 		= '91UNLOLI';
		var referral_code_id 	= 2;
		var referral_user_id 	= 1;
		var referral_amount 	= 10;
		var referral_user 		= {};
		var referral_wallet 	= {};
		
		var _data = {};
		
		if( referral_code_id && referral_user_id && referral_code && referral_amount > 0 ){
		
			async.series([
				
				// Get Referral User
				function( callback ){
					User.get( referral_user_id, function( status, data ){
						referral_user = data[0];
						callback( null );
					});
				},
				
				
			
				// Get Referral Wallet
				function( callback ){
					Wallet.get( referral_user_id, 'user', function( status, wallet ){
						referral_wallet = wallet;
						callback( null );
					});
				},
				
				
				
				// Add To Referral Wallet
				function( callback ){
					var _ins = {
						'i_wallet_id' : referral_wallet.id,
						'i_user_id' : referral_user_id,
						'v_type' : 'referral',
						'v_action' : 'plus',
						'f_amount' : referral_amount,
						'd_added' : gnrl._db_datetime(),
						'l_data' : {
							'referred_user_id' : 21,
							'i_ride_id' : 15,
						},
					};
					Wallet.addTransaction( _ins, function( status, data ){ 
						callback( null );
					});
					
				},
				
				
				
				// Refresh Wallet
				function( callback ){
					Wallet.refreshUserWallet( referral_user_id, function( status, data ){ 
						callback( null );
					});
				},
				
				
				
				// Send SMS 
				function( callback ){
					SMS.send({
						_to : referral_user.v_phone,
						_lang : User.lang( referral_user ),
						_key : 'user_add_money',
						_keywords : {
							'[user_name]' : referral_user.v_name,
							'[amount]' : referral_amount,
							'[from]' : Wallet.getPaymentModeName( 'referral' ),
						},
					}, function( error_sms, error_info ){
						callback( null );
					});
				},
				
				// Send Email 
				function( callback ){
					Email.send({
						_to : referral_user.v_phone,
						_lang : User.lang( referral_user ),
						_key : 'user_add_money',
						_keywords : {
							'[user_name]' : referral_user.v_name,
							'[amount]' : referral_amount,
							'[from]' : Wallet.getPaymentModeName( 'referral' ),
						},
					}, function( error_mail, error_info ){
						callback( null );
					});
				},
				
				
				// Update Current User
				function( callback ){
					var _ins = [
						" l_data = l_data || '"+gnrl._json_encode({
							'referral_code' 	: '',
							// 'referral_code_id' 	: 0,
							'referral_user_id' 	: 0,
							'referral_amount' 	: 0,
						})+"' "
					];
					dclass._updateJsonb( 'tbl_user', _ins, " AND id = '22' ", function( status, data ){ 
						callback( null );
					});
				},
				
				
			], function( error, results ){
				
				gnrl._api_response( res, 1, 'succ_ride_completed', {
					referral_code : referral_code,
					referral_code_id : referral_code_id,
					referral_user_id : referral_user_id,
					referral_amount : referral_amount,
					referral_user : referral_user,
					referral_wallet : referral_wallet,
					
				});
				
			});
			
		}
		else{
			
			gnrl._api_response( res, 1, 'succ_ride_completed', {} );
			
		}

		
	}
	
	
	
	
	
	
	else{
		gnrl._api_response( res, 0, 'Action Needed', {} );
	}
	
	
	
};
module.exports = currentApi;