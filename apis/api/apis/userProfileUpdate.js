var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var fs 			= require('fs');

var multer  	= require('multer');
var storage 	= multer.diskStorage({
	destination	: function( req, file, callback ){
		callback( null, __dirname + '/../../public/uploads/users/' );
	},
	filename	: function( req, file, callback ){
		callback( null, Date.now()+'-'+file.originalname );
	}
});

var upload = multer({ storage : storage }).any();
var dirUploads = __dirname + '/../../public/uploads/';



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	upload( req, res, function( err ){
		
		var params = gnrl._frm_data( req );
		var _lang = gnrl._getLang( params );
		
		if( err ){
			gnrl._api_response( res, 0, 'error_file_upload' );
		}
		else{
			
			var fileArr = {
				'v_image' : { 'name' : '', 'path' : '', },
			};
			for( var k in req.files ){
				if( req.files[k].filename ){
					fileArr[ req.files[k].fieldname ] = {
						'name' : req.files[k].filename,
						'path' : req.files[k].path
					};
				}
			}
			
			var login_id 		= gnrl._is_undf( params.login_id ).trim();
			var v_token 		= gnrl._is_undf( params.v_token ).trim();
			var v_name 			= gnrl._is_undf( params.v_name ).trim();
			var v_email 		= gnrl._is_undf( params.v_email ).trim();
			var v_phone 		= gnrl._is_undf( params.v_phone ).trim();
			var v_gender 		= gnrl._is_undf( params.v_gender, 'male' );
			
			if( !v_name.trim() ){ _status = 0; _message = 'err_req_name'; }
			if( _status && !login_id.trim() ){ _status = 0; _message = 'err_req_login_id'; }
			if( _status && !v_token.trim() ){ _status = 0; _message = 'err_req_auth_token'; }
			if( _status && !v_email.trim() ){ _status = 0; _message = 'err_req_email'; }
			if( _status && !v_phone.trim() ){ _status = 0; _message = 'err_req_phone'; }
			if( _status && !validator.isEmail( v_email ) ){ _status = 0; _message = 'err_invalid_email'; }
			
			
			if( _status ){
				
				dclass._select( '*', 'tbl_user', " AND id != '"+login_id+"' AND v_email = '"+v_email+"' ", function( status, data ){ 
					if( !status ){
						gnrl._remove_loop_file( fs, fileArr );
						gnrl._api_response( res, 0, '', {} );
					}
					else if( data.length ){
						gnrl._remove_loop_file( fs, fileArr );
						gnrl._api_response( res, 0, 'err_msg_exists_email', {} );
					}
					
					else{
						dclass._select( '*', 'tbl_user', " AND id != '"+login_id+"' AND v_phone = '"+v_phone+"' ", function( status, data ){ 
							if( !status ){
								gnrl._remove_loop_file( fs, fileArr );
								gnrl._api_response( res, 0, '', {} );
							}
							else if( data.length ){
								gnrl._remove_loop_file( fs, fileArr );
								gnrl._api_response( res, 0, 'err_msg_exists_phone', {} );
							}
							else{
								dclass._select( '*', 'tbl_user', " AND ( id = '"+login_id+"' ) AND v_token = '"+v_token+"' ", function( status, data ){ 
									
									if( !status ){
										gnrl._remove_loop_file( fs, fileArr );
										gnrl._api_response( res, 0, '', {} );
									}
									else if( !data.length ){
										gnrl._remove_loop_file( fs, fileArr );
										gnrl._api_response( res, 0, 'err_msg_no_account', {} );
									}
									else{
										var _row = data[0];
										var _ins = {
											'v_name' 	: v_name,
											'v_email' 	: v_email,
											'v_phone' 	: v_phone,
											'v_gender'	: v_gender,
											'v_image' 	: fileArr['v_image'].name ? fileArr['v_image'].name : _row.v_image,
											'd_modified': gnrl._db_datetime(),
										};
										dclass._update( 'tbl_user', _ins, " AND id = '"+login_id+"' ", function( status, data ){ 
											if( status ){
												fs.rename( fileArr['v_image'].path, dirUploads+'/users/'+fileArr['v_image'].name, function(err){});
												gnrl._api_response( res, 1, 'succ_profile_updated', {} );
											}
											else{
												gnrl._remove_loop_file( fs, fileArr );
												gnrl._api_response( res, 0, _message );
											}
										});
									}
								});
							}
						});
						
					}
					
				});
				
				
			}
			else{
				gnrl._remove_loop_file( fs, fileArr );
				gnrl._api_response( res, 0, _message );
			}
		}
	});
};

module.exports = currentApi;
