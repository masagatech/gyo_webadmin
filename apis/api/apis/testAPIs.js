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
		_q.push( "update tbl_user SET is_idle = 1 WHERE v_role = 'driver' ;" );
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
			'tbl_track_vehicle_status',
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
	else if( action == 'series' ){
		async.series([
			function( callback ){
				callback( null, {} );
			},
			function( callback ){
				callback( null, {} );
			},
		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'Done', results );
		});
	}
	
	else{
		gnrl._api_response( res, 0, 'Action Needed', {} );
	}
	
	
	
};
module.exports = currentApi;