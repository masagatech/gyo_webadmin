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
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var v_username 		= gnrl._is_undf( params.v_username ).trim();
	var v_password 		= gnrl._is_undf( params.v_password ).trim();
	var v_device_token 	= gnrl._is_undf( params.v_device_token ).trim();
	
	if( !v_username ){ _status = 0; _message = 'err_req_email_or_phone'; }
	if( _status && !v_password ){ _status = 0; _message = 'err_req_password'; }
	if( _status && !v_device_token ){ _status = 0; _message = 'err_req_device_token'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var _user = [];
		var v_token;
		async.series([
			function( callback ){
				
				v_username = v_username.toLowerCase();
				
				dclass._select( '*', 'tbl_user', " AND v_role = 'driver' AND ( LOWER( v_email ) = '"+v_username+"' OR LOWER( v_phone ) = '"+v_username+"' )", function( status, user ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !user.length ){
						gnrl._api_response( res, 0, 'err_no_records', {} );
					}
					else{
						
						_user = user[0];
						v_password = v_password ? md5( v_password ) : v_password;
						if( !_user.l_data.is_otp_verified ){
							gnrl._api_response( res, 2, 'err_not_verified', {
								'id' 		: _user.id,
								'phone' 	: _user.v_phone,
							});
						}
						else if( _user.e_status == 'inactive' ){
							gnrl._api_response( res, 0, 'err_acc_inactive', {} );
						}
						else if( !validator.equals( v_password, _user.v_password ) ){
							gnrl._api_response( res, 0, 'err_invalid_password', {} );
						}
						else{
							callback( null );
						}
						
					}
				});				
			},

			// Login
			function( callback ){
				v_token = md5( _user.id+'-'+gnrl._db_datetime() );
				var _ins = {
					'v_token' 		 : v_token,
					'v_device_token' : v_device_token,
					'd_last_login' 	 : gnrl._db_datetime(),
				};
				dclass._update( 'tbl_user', _ins, " AND id = '"+( _user.id )+"' ", function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, _message );
					}
					else{
						callback( null );
					}
				});
			},
		], 
		function( error, results ){
			dclass._select( '*', 'tbl_vehicle', " AND i_driver_id = '"+( _user.id )+"' ", function( vehicle_status, vehicle_data ){ 
				gnrl._api_response( res, 1, 'succ_login_successfully', {
					'id' 		: _user.id,
					'v_token' 	: v_token,
					'vehicle_id': vehicle_data.length ? vehicle_data[0].id : 0,
					'lang'      : User.lang( _user ),
				});
			});
		});
	}
};

module.exports = currentApi;