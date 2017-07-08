var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var async       = require('async');


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
	
	if( _status ){	
	
		var _data = {
			'earn_money' : 0,
			'v_referral_code' : gnrl._lbl('msg_refer_code'),
			'message' : gnrl._lbl('msg_refer_code_string_off'),
		};
		
		var _user = {};
		var _settings = {};
		
		async.series([
			
			// Get User
			function( callback ){
				dclass._select( 'id, v_phone, v_role', 'tbl_user', " AND id = '"+login_id+"' ", function( status, data ){
					_user = data[0];
					_data.v_referral_code = _user.v_phone;
					callback( null );
				});
			},
			
			// Get Settings
			function( callback ){
				
				if( _user.v_role == 'user' ){
					var keyArr = [ 'REFERRAL_USER_MONEY', 'REFERRAL_USER_COUPON' ];
				}
				else{
					var keyArr = [ 'REFERRAL_DRIVER_MONEY', 'REFERRAL_DRIVER_COUPON' ];
				}
				Settings.getMulti( keyArr, function( status, data ){
					if( _user.v_role == 'user' ){
						_settings.money 	= parseFloat( data.REFERRAL_USER_MONEY );
						_settings.coupon 	= parseFloat( data.REFERRAL_USER_COUPON );
					}
					else{
						_settings.money 	= parseFloat( data.REFERRAL_DRIVER_MONEY );
						_settings.coupon	= parseFloat( data.REFERRAL_DRIVER_COUPON );
					}
					_data.earn_money = parseFloat( _settings.money ? _settings.money : _settings.coupon );
					_data.message = gnrl._lbl('msg_refer_code_string_on').split('[amount]').join( _data.earn_money );
					callback( null );
				})
			},
			
		], function( error, results ){
			
			gnrl._api_response( res, 1, '', _data );
			
		});
	}
	else{
		gnrl._api_response( res, 0, _message, {} );
	}
};

module.exports = currentApi;
