var express = require('express');
var validator = require('validator');
var md5 = require('md5');
var http = require('http');

var async = require('async');
var waterfall = require('async/waterfall');

var currentApi = function saveDriverInfo( req, res, done ){
	
	var dclass = req.app.get('Database');
	var gnrl = req.app.get('gnrl');
	var db = req.app.get('db');
	var _p = req.app.get('_p');
	
	var _status = 1;
	var _message = '';
	var _response = {};
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params, req.app.get('_lang') );
	
	if( 1 ){
		
		
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
		
		
		/*
		var params = {
			_to      	: '8866207256',
			_lang 		: _lang,
			_key 		: 'user_forgot_password',
			_keywords 	: {
				'[user_name]' : 'USER_NAME',
				'[otp]' : 'OTP',
			},
		};
		gnrl._sendSMS( params, function( error_mail, error_info ){
			gnrl._api_response( res, 1, 'Done', {
				error_mail : error_mail, 
				error_info : error_info
			});
		});*/
		
		
		//gnrl._api_response( res, 1, 'Done', gnrl._timestamp( '2017-04-21 12:50:55+05:30' ) );
		
		
		
		/*
		//waterfall
		_p('D1');
		
		*/
		
		
		
		
		/*async.waterfall([
			function( callback ){
				callback( null, {
					'data_1' : 1
				});
			},
			function( arg1, callback ){
				Object.assign( arg1, {
					'data_2' : 2
				});
				callback( null, arg1 );
			},
			
			function( arg1, callback ){
				firstData = 1;
				Object.assign( arg1, {
					'data_3' : 3
				});
				callback( null, arg1 );
			}
		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'Done', results );
		});*/
		
		/*
		var firstData = {};
		
		async.series([
			function( callback ){
				dclass._query( "SELECT * FROM tbl_city WHERE id = '1' ", function( status, data ){ 
					firstData = data[0];
					callback( null, data );
				});
			},
			function( callback ){
				dclass._query( "SELECT * FROM tbl_city WHERE id = '2' ", function( status, data ){ 
					_p( 'firstData', firstData );
					callback( null, data );
				});
			},
			function( callback ){
				dclass._query( "SELECT * FROM tbl_city WHERE id = '3' ", function( status, data ){ 
					callback( null, data );
				});
			}
		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'Done', results );
		});
		*/
		
		
		
		
		/*
		var _ins = [
			"INSERT INTO tbl_buzz (i_ride_id,i_driver_id) VALUES (100,100);",
			"INSERT INTO tbl_buzz (i_ride_id,i_driver_id) VALUES (100,100);",
			"INSERT INTO tbl_buzz (i_ride_id,i_driver_id) VALUES (100,100);"
		];
		var _q = _ins.join('');
		dclass._query( _q, function( status, data ){ 
			gnrl._api_response( res, status, 'Done', data );
		});*/
		
		/*
		for( i = 1; i <= 5; i++ ){
			var j_title = {
				'en' : 'User Reason '+i,
				'gu' : 'User Reason '+i,
				'hi' : 'User Reason '+i,
			};
			dclass._insert( 'tbl_ride_cancel_reason', {
				'j_title' 	: gnrl._json_encode( j_title ),
				'v_type' 	: 'user',
				'd_added' 	: gnrl._db_datetime(),
				'd_modified': gnrl._db_datetime(),
				'i_order'	: i,
				'l_data' 	: gnrl._json_encode( {} ),
				'i_status'	: 1,
			}, function( status, data ){ 
			_p(data);
			});
		}
		
		for( i = 1; i <= 5; i++ ){
			var j_title = {
				'en' : 'Driver Reason '+i,
				'gu' : 'Driver Reason '+i,
				'hi' : 'Driver Reason '+i,
			};
			dclass._insert( 'tbl_ride_cancel_reason', {
				'd_added' 	: gnrl._db_datetime(),
				'd_modified': gnrl._db_datetime(),
				'i_order'	: i,
				'l_data' 	: gnrl._json_encode( {} ),
				'i_status'	: 1,
				'v_type' 	: 'driver',
				'j_title' 	: gnrl._json_encode( j_title ),
			}, function( status, data ){ 
			
			});
		}*/
		
		
		
		
		/*
		var _ins = {
			'l_data' : "jsonb_set(l_data,'{\"image\",\"width\"}',to_jsonb(1024))",
		};
		var _q = " UPDATE tbl_user SET l_data = jsonb_set(l_data,'{D1}','5',true)  WHERE id = '1' ";
		dclass._query( _q, function( status, data ){ 
			if( status ){
				gnrl._api_response( res, 1, 'Done', {}, 0 );
			}
			else{
				gnrl._api_response( res, 0, _message, data, 1 );
			}
		});*/
		
		/*
		dclass._insert( 'tbl_vehicle_type', {
			'v_type' 	: 'mini',
			'v_name' 	: 'Mini',
			'd_added' 	: gnrl._db_datetime(),
			'd_modified': gnrl._db_datetime(),
			'e_status' 	: 'active',
			'l_data' 	: {
			},
		}, function( status, data ){ 
			if( status ){
				gnrl._api_response( res, 1, '', { 'id' : data.id ? data.id : 0 });
			}
			else{
				gnrl._api_response( res, 0, _message, {}, 0 );
			}
		});*/
		
		
		/*
		db.callProcedure("select " + gnrl._db_schema("fun_get_user_list") + "($1,$2::json);", [ 'users', params ], function( data ){
			gnrl._api_response( res, 1, _message, data.rows, 0 );
		}, function(err) {
			gnrl._api_response( res, 0, _message, {}, 0 );
		});*/
		
		/*
		db.callProcedure("select " + gnrl._db_schema("fun_get_vehicle_list") + "($1,$2::json);", [ 'users', params ], function( data ){
			gnrl._api_response( res, 1, _message, data.rows, 0 );
		}, function(err) {
			gnrl._api_response( res, 0, _message, {}, 0 );
		});*/
		
		/*
		function getCity( ids, cb ){
			var cities = [];
			var pending = ids.length;
			for( var i in ids ){
				dclass._query( "SELECT * FROM tbl_city WHERE id = '"+( ids[i] )+"' ", function( status, data ){ 
					cities.push( data );
					if( 0 === --pending ) {
						cb( cities ); //callback if all queries are processed
					}
				});
			}
		}
		var ids = [ 1, 2, 3 ];
		setTimeout(function(){
		getCity( ids, function( city ){
			_p( 'city', city );
		});
		},0);
		*/
		
		
	}
	else{
		gnrl._api_response( res, 0, _message, {}, 0 );
	}
};
module.exports = currentApi;