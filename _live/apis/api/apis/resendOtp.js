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
	
	var id = gnrl._is_undf( params.id );
	var v_phone = gnrl._is_undf( params.v_phone );
	var type = gnrl._is_undf( params.type );
	
	if( !id ){ _status = 0; _message = 'err_req_id'; }
	if( _status && !v_phone ){ _status = 0; _message = 'err_req_phone'; }
	if( _status && !type ){ _status = 0; _message = 'err_req_type'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		/*
		STEPS
			>> Get User
			>> Resend OTP
			>> Update OTP
		*/
		
		var _data = {
			v_otp : gnrl._get_otp()
		};
		
		async.series([
			
			// Get User
			function( callback ){
				
				
				var _q = " SELECT ";
				_q += " id, v_name, v_role, v_phone, lang ";
				_q += " FROM ";
				_q += " tbl_user ";
				_q += " WHERE true ";
				_q += " AND id = '"+id+"' ";
				
				dclass._query( _q, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 1, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 1, 'err_msg_no_account', {} );
					}
					else if( data[0].v_role != type ){
						gnrl._api_response( res, 1, 'err_msg_no_account', {} );
					}
					else if( data[0].v_phone != v_phone ){
						gnrl._api_response( res, 1, 'err_invalid_phone', {} );
					}
					else{
						_data.user = data[0];
						callback( null );
					}
				});
				
			},
			
			// Update OTP
			function( callback ){
				var _ins = [
					"v_otp = "+_data.v_otp,
				];
				dclass._updateJsonb( "tbl_user", _ins, " AND id = '"+id+"' ", function( status, data ){ 
					if( status ){
						callback( null );
					}
					else{
						gnrl._api_response( res, 0, 'error', {} );
					}
				});
			},
			
			// Resend OTP
			function( callback ){
				var params = {
					_key : 'resend_otp',
					_to : v_phone,
					_lang : _data.user.lang,
					_keywords : {
						'[user_name]' : _data.user.v_name,
						'[otp]' : _data.v_otp,
					},
				};
				SMS.send( params, function( succ, err_info ){
					if( succ ){
						callback( null );
					}
					else{
						gnrl._api_response( res, 0, 'error', {} );
						// gnrl._api_response( res, 0, err_info, {} );
					}
				});
			},
			
		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_otp_sent', {} );
		});
	}
};

module.exports = currentApi;
