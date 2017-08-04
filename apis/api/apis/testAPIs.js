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
	
	var action = gnrl._is_undf( params.action );
	
	if( action == 'timestamp' ){
		gnrl._api_response( res, 1, 'Done', gnrl._timestamp( '2017-04-21 12:50:55+05:30' ) );
	}
	
	
	
	if( action == 'sendMail' ){
		Email.send({
			_to 		: 'deven.crestinfotech@gmail.com',
			_lang 		: 'en',
			_key 		: '', 
			_title 		: 'Testing Email From GoYo',
			_body 		: 'Testing Email From GoYo',
			_keywords 	: {},
		}, function( error_mail, error_info ){
			gnrl._api_response( res, 1, 'Done', {
				error_mail : error_mail,
				error_info : error_info
			});
		});
	}
	
	else if( action == 'sendTestingMail' ){
		var email = gnrl._is_undf( params.email, 'deven.crestinfotech@gmail.com' );
		Email.send({
			_to 		: email,
			_lang 		: _lang,
			_key 		: 'testing',
			_keywords 	: {},
		}, function( error_mail, error_info ){
			gnrl._api_response( res, 1, 'Done', {
				error_mail : error_mail,
				error_info : error_info
			});
		});
	}
	else if( action == 'sendTestingMailTemplates' ){
		var email = 'deven.crestinfotech@gmail.com';
		Email.send({
			_to 		: email,
			_lang 		: _lang,
			_key 		: 'user_ride_complete',
			_keywords 	: {},
		}, function( error_mail, error_info ){
			gnrl._api_response( res, 1, 'Done', {
				status : error_mail,
				info : error_info
			});
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
			data : {}
		}, function( err, response ){
			gnrl._api_response( res, 1, 'Done', {
				err : err, 
				response : response,
			});
		});
	
	}
	else if( action == 'fcm2' ){
		var tokens = [];
		tokens.push({
			'id' 	: '1',
			'lang' 	: 'en',
			
			//'token' : 'eQAchuXHZPA:APA91bHJ_KGl8Jjy7Vc8FtEI56sIRL3RXDwhidRZcui0fGiTJrAZs1ADRvufRC7xbnW-4Hw4_8vIRCY4ijvZ9-pB9dlNRF2mOzclz966GdWDHZ7fYAspa2kECalIg1RGWKrK4XM9eJF6',
			//'token' : 'cqSOqsyIPTU:APA91bHFSN5O-AZwclDNYLkVY0Du4oLpe4HgDHSganksKeoqXbbGQVc4zrsIq4YH0ZQqPXOrgzGW06jTzyeeY4YVk1M1dzgexVsrc4sLtTHn4VM1wkm7ArHq_sCm54OEjnhNbokETYC4',
			'token' : 'cg6s0VPq-2k:APA91bFPNtc9lT9G7cA2gVTuITdozQN_btMCGKvaMYuRwl_-J7SEA68-F-711hBM0aTZGhcc60MS3ORb3E4JCCVgO4MP47dBKIbQ6x8aP6gfYqkcifC6FbRb0T0tcZ9tx-1O6G2vdeNh',
			
		});
		var params = {
			_key 		: 'user_manual_update',
			//_role 		: 'driver',
			_role 		: 'user',
			_tokens 	: tokens,
			_keywords 	: {},
			_custom_params : {},
			_need_log : 0,
		};
		Notification.send( params, function( err, response ){
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
			'xxxx',
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
		/*{
			_to : '8866207256',
			_lang : 'en',
			_key : '', // resend_otp
			_body : 'Testing Email From GoYo',
			_keywords : {},
		},*/
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
	
	else if( action == 'dbprocdure' && 0 ){
		
		var _row = {}
		async.series([
			
			function( callback ){
				var _ins = { 
					'd_date' : gnrl._db_datetime(),
					'd_date_w' : gnrl._db_datetime(),
				};
				dclass._insert( 'tbl_testing', _ins, function( status, data ){ 
					callback( null );
				});
			},
			
			function( callback ){
				dclass._query( 'SELECT * FROM tbl_testing order by id desc;', function( status, data ){ 
					_row = data;
					for( k in _row ){
						_row[k] = _row[k];
						_row[k].F_d_date = gnrl._timestamp( _row[k].d_date );
						_row[k].F_d_date_w = gnrl._timestamp( _row[k].d_date_w );
						
						_row[k].FFF_d_date = gnrl._db_ymd('Y-m-d h:i A', new Date( _row[k].F_d_date ) );
						_row[k].FFF_d_date_w = gnrl._db_ymd('Y-m-d h:i A', new Date( _row[k].F_d_date_w ) );
					}
					callback( null );
				});
			},
			
		], 
		function( error, results ){
			gnrl._api_response( res, 1, '', _row );
		});
		
	}
	
	else if( action == 'getRide' ){
		
		var _row = {}
		var _times = {};
		async.series([
			function( callback ){
				
				var _q = " SELECT ";
				_q += " * ";
				_q += " FROM tbl_ride WHERE id = 1770; ";
				
				dclass._query( _q, function( status, data ){ 
					_row = data[0];
					
					_times.d = gnrl._db_datetime();
					
					_times.dd = gnrl._db_ymd('Y-m-d h:i A', new Date( _times.d ) );
					_times.dt = gnrl._timestamp( _times.d );
					_times.dtd = gnrl._db_ymd('Y-m-d h:i A', new Date( _times.dt ) );
					
					_times.d_time = _row.d_time;
					_times.d_time_1 = _row.d_time.getTime();
					
					_times.d_time_2 = gnrl._db_ymd('Y-m-d h:i A', new Date( _times.d_time_1 ) );
					
					_times.d_time_3 = ( new Date( _times.d_time_1 ) ).toUTCString();
					_times.d_time_33 = new Date( _times.d_time_3 );
					
					_times.d_time_3_Y = _times.d_time_33.getUTCFullYear();
					_times.d_time_3_M = _times.d_time_33.getUTCMonth() + 1;
					_times.d_time_3_D = _times.d_time_33.getUTCDate();
					
					_times.d_time_3_H = _times.d_time_33.getUTCHours();
					_times.d_time_3_M = _times.d_time_33.getUTCDate();
					_times.d_time_3_S = _times.d_time_33.getUTCDate();
					
					//this.(),
					//this.getUTCMinutes(), 
					//this.getUTCSeconds()
					
					callback( null );
				});
			},
		], 
		function( error, results ){
			gnrl._api_response( res, 1, '', {
				_times : _times,
				_row : _row
			});
		});
		
	}
	
	else{
		
		var _row = {
		};
		
		var i_ride_id = '';
		var login_id = '';
		
		/*
		>> Get Ride
		>> Get Vehicle Icon
		>> Get Driver
		>> Get User
		>> Get Rate & Comment
		>> Get Estimated Prices
		*/
		
		async.series([
			
			
			// Get Ride
			function( callback ){
				
				var _q = " SELECT ";
				_q += " rd.* ";
				
				_q += " , COALESCE( vt.l_data->>'list_icon', '' ) AS list_icon ";
				_q += " , COALESCE( vt.l_data->>'plotting_icon', '' ) AS plotting_icon ";
				
				_q += " , COALESCE( ur.id, 0 ) AS user_id ";
				_q += " , COALESCE( ur.v_name, '' ) AS user_v_name ";
				_q += " , COALESCE( ur.v_email, '' ) AS user_v_email ";
				_q += " , COALESCE( ur.v_phone, '' ) AS user_v_phone ";
				_q += " , COALESCE( ur.v_image, '' ) AS user_v_image ";
				_q += " , COALESCE( ur.v_id, '' ) AS user_v_id ";
				
				_q += " , COALESCE( dr.id, 0 ) AS driver_id";
				_q += " , COALESCE( dr.v_image, '' ) AS driver_v_image";
				_q += " , COALESCE( dr.v_name, '' ) AS driver_v_name";
				_q += " , COALESCE( dr.v_email, '' ) AS driver_v_email";
				_q += " , COALESCE( dr.v_phone, '' ) AS driver_v_phone";
				_q += " , COALESCE( dr.v_id, '' ) AS driver_v_id";
				
				_q += " , COALESCE( vh.id, 0 ) AS vehicle_id ";
				_q += " , COALESCE( vh.v_vehicle_number, '' ) AS vehicle_number ";
				_q += " , COALESCE( vh.v_image_rc_book, '' ) AS v_image_rc_book ";
				_q += " , COALESCE( vh.v_image_puc, '' ) AS v_image_puc ";
				_q += " , COALESCE( vh.v_image_insurance, '' ) AS v_image_insurance ";
				_q += " , COALESCE( vh.v_image_license, '' ) AS v_image_license ";
				_q += " , COALESCE( vh.v_image_adhar_card, '' ) AS v_image_adhar_card ";
				_q += " , COALESCE( vh.v_image_permit_copy, '' ) AS v_image_permit_copy ";
				_q += " , COALESCE( vh.v_image_police_copy, '' ) AS v_image_police_copy ";
				
				_q += " , COALESCE( rr.i_rate, 0 ) AS i_rate";
				_q += " , COALESCE( rr.l_comment, '' ) AS rate_cmment";
				
				_q += " , COALESCE( drr.i_rate, 0 ) AS driver_i_rate";
				_q += " , COALESCE( drr.l_comment, '' ) AS driver_rate_cmment";
				
				
				_q += " FROM tbl_ride rd ";
				
				_q += " LEFT JOIN tbl_vehicle_type vt ON vt.v_type = rd.l_data->>'vehicle_type' ";
				
				_q += " LEFT JOIN tbl_user ur ON ur.id = rd.i_user_id ";
				
				_q += " LEFT JOIN tbl_user dr ON dr.id = rd.i_driver_id ";
				
				_q += " LEFT JOIN tbl_vehicle vh ON vh.i_driver_id = dr.id ";
				
				_q += " LEFT JOIN tbl_ride_rate rr ON rr.i_ride_id = rd.id AND rr.i_target_user_id = dr.id ";
				
				_q += " LEFT JOIN tbl_ride_rate drr ON drr.i_ride_id = rd.id AND drr.i_target_user_id = ur.id ";
				
				_q += " WHERE rd.id = '"+i_ride_id+"' ";
				
				_q += " AND ( rd.i_user_id = '"+login_id+"' OR rd.i_driver_id = '"+login_id+"' ) ";
				
				dclass._query( _q, function( status, data ){
					
					if( !status ){
						gnrl._api_response( res, 0, 'error', _row );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', _row );
					}
					else{
						
						_row 			= data[0];
						
						_row.d_time  	= gnrl._timestamp( _row.d_time );
						_row.d_start 	= _row.d_start ? gnrl._timestamp( _row.d_start ) : '';
						_row.d_end 	 	= _row.d_end ? gnrl._timestamp( _row.d_end ) : '';
						
						_row.v_pin		= _row.v_pin.toString();
						_row.v_pin 		= _row.v_pin.substring(0,4)+"-"+_row.v_pin.substring(4,8);
						
						// Vehicle Type Icons
						_row.vehicle_type_data = {
							list_icon 		: gnrl._uploads( 'vehicle_type/'+_row.list_icon ),
							plotting_icon 	: gnrl._uploads( 'vehicle_type/'+_row.plotting_icon ),
						};
						
						// User Data
						_row.user_data = {
							"id"		: _row.user_id,
							"v_name"	: _row.user_v_name,
							"v_email"	: _row.user_v_email,
							"v_phone"	: _row.user_v_phone,
							"v_image"	: _row.user_v_image ? gnrl._uploads( 'users/'+_row.user_v_image ) : '',
							"v_id"		: _row.user_v_id
						};
						
						// Driver Data
						_row.driver_data = {
							
							"id"					: _row.driver_id,
							"driver_name"			: _row.driver_v_name,
							"driver_email"			: _row.driver_v_email,
							"driver_phone"			: _row.driver_v_phone,
							"driver_image"			: _row.driver_v_image ? gnrl._uploads( 'drivers/'+_row.driver_v_image ) : '',
							"v_id"					: _row.driver_v_id,
							
							
							"vehicle_id"			: _row.vehicle_id,
							"vehicle_number"		: _row.vehicle_number,
							"v_image_rc_book"		: _row.v_image_rc_book 		? gnrl._uploads( 'drivers/'+_row.v_image_rc_book ) : '',
							"v_image_puc"			: _row.v_image_puc 			? gnrl._uploads( 'drivers/'+_row.v_image_puc ) : '',
							"v_image_insurance"		: _row.v_image_insurance 	? gnrl._uploads( 'drivers/'+_row.v_image_insurance ) : '',
							"v_image_license"		: _row.v_image_license 		? gnrl._uploads( 'drivers/'+_row.v_image_license ) : '',
							"v_image_adhar_card"	: _row.v_image_adhar_card 	? gnrl._uploads( 'drivers/'+_row.v_image_adhar_card ) : '',
							"v_image_permit_copy"	: _row.v_image_permit_copy 	? gnrl._uploads( 'drivers/'+_row.v_image_permit_copy ) : '',
							"v_image_police_copy"	: _row.v_image_police_copy 	? gnrl._uploads( 'drivers/'+_row.v_image_police_copy ) : ''
							
						};
						
						// Rate
						_row.rate = {
							"i_rate"		: _row.i_rate,
							"rate_cmment"	: _row.rate_cmment,
						};
						
						_row.user_rate = {
							"i_rate"		: _row.i_rate,
							"rate_cmment"	: _row.rate_cmment,
						};
						
						_row.driver_rate = {
							"i_rate"		: _row.driver_i_rate,
							"rate_cmment"	: _row.driver_rate_cmment,
						};
						
						
						var _deletable = [
							'list_icon',
							'plotting_icon',
							
							'user_id',
							'user_v_name',
							'user_v_email',
							'user_v_phone',
							'user_v_image',
							'user_v_id',
							
							'driver_id',
							'driver_v_name',
							'driver_v_email',
							'driver_v_phone',
							'driver_v_image',
							'driver_v_id',
							'vehicle_id',
							'vehicle_number',
							'v_image_rc_book',
							'v_image_puc',
							'v_image_insurance',
							'v_image_license',
							'v_image_adhar_card',
							'v_image_permit_copy',
							'v_image_police_copy',
							
							'i_rate',
							'rate_cmment',
							
							'driver_i_rate',
							'driver_rate_cmment',
							
						];
						
						for( var k in _deletable ){
							delete _row[_deletable[k]];
						}
						
						callback( null );
					}
				});
			},
			
			// Ride Set Charges
			function( callback ){
				Ride.getChargesData( _row.l_data, function( data ){
					_row.l_data = data;
					callback( null );
				});
			},
			
			// Get Estimated Prices
			function( callback ){
				
				var estimate_km 	= parseFloat( _row.l_data.estimate_km );
				var estimate_time 	= parseFloat( _row.l_data.estimate_time );
				
				var estimation = {
					estimate_km 	 : estimate_km,
					estimate_time 	 : estimate_time,
					min_charge 		 : 0,
					base_fare 		 : 0,
					total_fare 		 : 0,
					ride_time_charge : 0,
					service_tax 	 : 0,
					surcharge 		 : 0,
					final_total 	 : 0,
				};
				
				// Min Charge
				var amt = parseFloat( _row.l_data.charges.min_charge );
				if( amt > 0 ){
					estimation.min_charge = amt;
				}
				
				// Base Fare
				var amt = parseFloat( _row.l_data.charges.base_fare );
				if( amt > 0 ){
					estimation.base_fare = amt;
				}
				
				// Total Fare
				if( estimate_km > 0 ){
					
					var upto_km 		= parseFloat( _row.l_data.charges.upto_km );
					var upto_km_charge 	= parseFloat( _row.l_data.charges.upto_km_charge );
					var after_km_charge = parseFloat( _row.l_data.charges.after_km_charge );
					
					var amt = 0; 
					if( estimate_km > upto_km ){ 
						amt += ( upto_km_charge * upto_km ); 
						amt += ( after_km_charge * ( estimate_km - upto_km ) ); 
					}
					else{
						amt += ( upto_km_charge * estimate_km ); 
					}
					
					estimation.total_fare = amt;
					
				}
				
				// Ride Time Charge
				var amt = parseFloat( _row.l_data.charges.ride_time_charge );

				if( amt > 0 ){
					estimation.ride_time_charge = parseFloat( amt * estimate_time );
				}
				
				// Service Tax
				var amt = parseFloat( _row.l_data.charges.service_tax );
				if( amt > 0 ){
					var tempTotal = (
						estimation.min_charge
						+ estimation.base_fare
						+ estimation.total_fare
						+ estimation.ride_time_charge
					);
					estimation.service_tax = parseFloat( ( tempTotal * amt ) / 100 );
				}
				
				// Surcharge
				var amt = parseFloat( _row.l_data.charges.surcharge );
				if( amt > 0 ){
					var tempTotal = (
						estimation.min_charge
						+ estimation.base_fare
						+ estimation.total_fare
						+ estimation.ride_time_charge
						+ estimation.service_tax
					);
					estimation.surcharge = parseFloat( ( tempTotal * amt ) / 100 );
				}
				
				estimation.final_total = gnrl._round(
					estimation.min_charge
					+ estimation.base_fare
					+ estimation.total_fare
					+ estimation.ride_time_charge
					+ estimation.service_tax
					+ estimation.surcharge
				);
				
				_row.l_data.estimation = estimation;
				
				callback( null );
			},
			
		], 
		function( error, results ){
			
			gnrl._api_response( res, 1, '', _row );
			
		});
		
	}
	
};
module.exports = currentApi;