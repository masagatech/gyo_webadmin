var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );

	var id 				= gnrl._is_undf( params.id ).trim();
	var v_token 		= gnrl._is_undf( params.v_token ).trim();
	var v_password 		= gnrl._is_undf( params.v_password ).trim();
	var v_old_password 	= gnrl._is_undf( params.v_old_password ).trim();
	
	if( !id ){ _status = 0; _message = 'err_req_user_id'; }
	if( _status && !v_token ){ _status = 0; _message = 'err_req_auth_token'; }
	if( _status && !v_password ){ _status = 0; _message = 'err_req_password'; }
	if( _status && !v_old_password ){ _status = 0; _message = 'err_req_old_password'; }
	if( _status && !validator.isLength( v_password, { min : 6, max : 10 } ) ){ _status = 0; _message = 'err_validation_password'; }
	
	if( _status ){
		
		v_password = v_password ? md5( v_password ) : v_password;
		v_old_password = v_old_password ? md5( v_old_password ) : v_old_password;
		
		dclass._select( '*', 'tbl_user', " AND id = '"+id+"' ", function( status, data ){ 
			if( status && !data.length ){
				gnrl._api_response( res, 0, 'err_msg_no_account', {} );
			}
			else{
				var _row = data[0];
				if( v_token != data[0].v_token ){
					gnrl._remove_loop_file( fs, fileArr );
					gnrl._api_response( res, 0, 'err_invalid_auth_token', {} );
				}
				else{
					if( _row.v_password != v_old_password ){
						gnrl._api_response( res, 0, 'err_invalid_old_password', {} );
					}
					else{
						var _ins = {
							'v_password' : v_password,
							'd_modified' : gnrl._db_datetime(),
						};
						dclass._update( 'tbl_user', _ins, " AND id = '"+id+"' ", function( status, data ){ 
							if( status ){
								gnrl._api_response( res, 1, 'succ_password_updated', {} );
							}
							else{
								gnrl._api_response( res, 0, _message );
							}
						});
					}
				}
			}
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
	
};

module.exports = currentApi;
