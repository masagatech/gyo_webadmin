var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id );
	var v_password = gnrl._is_undf( params.v_password );
	var v_old_password = gnrl._is_undf( params.v_old_password );
	
	if( !login_id ){ _status = 0; _message = 'err_req_user_id'; }
	if( _status && !v_password ){ _status = 0; _message = 'err_req_password'; }
	if( _status && !v_old_password ){ _status = 0; _message = 'err_req_old_password';; }
	if( _status && !validator.isLength( v_password, { min : 6, max : 10 } ) ){ _status = 0; _message = 'err_validation_password'; }
	
	if( _status ){
		
		v_password = v_password ? md5( v_password ) : v_password;
		v_old_password = v_old_password ? md5( v_old_password ) : v_old_password;
		
		var _q = " SELECT ";
			_q += " id ";
			_q += " , v_password ";
			_q += " FROM tbl_user WHERE v_role = 'driver' AND id = '"+login_id+"' ";
	
		dclass._query( _q, function( status, data ){
			
			if( !status ){
				gnrl._api_response( res, 0, 'error', {} );
			}
			else if( !data.length ){
				gnrl._api_response( res, 0, 'err_msg_no_account', {} );
			}
			else if( data[0].v_password != v_old_password ){
				gnrl._api_response( res, 0, 'err_invalid_old_password', {} );
			}
			else{
				var _ins = {
					'v_password' : v_password,
					'd_modified' : gnrl._db_datetime(),
				};
				dclass._update( 'tbl_user', _ins, " AND id = '"+login_id+"' ", function( status, data ){ 
					if( status ){
						gnrl._api_response( res, 1, 'succ_password_updated', {} );
					}
					else{
						gnrl._api_response( res, 0, _message );
					}
				});
			}
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
	
};

module.exports = currentApi;
