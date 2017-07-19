var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var fs 			= require('fs');
var async = require('async');


var multer  	= require('multer');
var storage 	= multer.diskStorage({
	destination	: function( req, file, callback ){
		callback( null, __dirname + '/../../public/uploads/temp/' );
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
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	upload( req, res, function( err ){
		
		var params 				= gnrl._frm_data( req );
		
		var fileArr = {
			'v_image_puc' 			: { 'name' : '', 'path' : '', },
			'v_image_insurance' 	: { 'name' : '', 'path' : '', },
		};
		
		for( var k in req.files ){
			if( req.files[k].filename ){
				fileArr[ req.files[k].fieldname ] = {
					'name' : req.files[k].filename,
					'path' : req.files[k].path
				};
			}
		}
		
		var login_id 	= gnrl._is_undf( params.login_id );
		var v_token 	= gnrl._is_undf( params.v_token );
		var v_name 		= gnrl._is_undf( params.v_name );
		var v_email 	= gnrl._is_undf( params.v_email );
		var v_phone 	= gnrl._is_undf( params.v_phone );
		var i_city_id 	= gnrl._is_undf( params.i_city_id );
		
		if( !login_id.trim() ){ _status = 0; _message = 'err_req_id'; }
		if( _status && !v_token ){ _status = 0; _message = 'err_req_auth_token'; }
		if( _status && !v_name.trim() ){ _status = 0; _message = 'err_req_name'; }
		if( _status && !v_email.trim() ){ _status = 0; _message = 'err_req_email'; }
		if( _status && !v_phone.trim() ){ _status = 0; _message = 'err_req_phone'; }
		if( _status && !validator.isEmail( v_email ) ){ _status = 0; _message = 'err_invalid_email'; }
		if( _status && !i_city_id ){ _status = 0; _message = 'err_req_city'; }
		
		var folder = 'drivers';
		
		if( err ){
			gnrl._remove_loop_file( fs, fileArr );
			gnrl._api_response( res, 0, 'error_file_upload', {} );
		}
		else if( !_status ){
			gnrl._remove_loop_file( fs, fileArr );
			gnrl._api_response( res, 0, _message, {} );
		}
		else{
			
			
			async.series([
			
				// Check Validation
				function( callback ){
					var _q = " SELECT 1 ";
					_q += " FROM tbl_user WHERE true ";
					_q += " AND id = '"+login_id+"' AND v_token = '"+v_token+"'; ";
					dclass._query( _q, function( status, data ){ 
						if( status && data.length ){
							callback( null );
						}
						else{
							gnrl._remove_loop_file( fs, fileArr );
							gnrl._api_response( res, 0, 'err_invalid_auth_token', {} );
						}
					});
				},
				
				// Check Email + Phone
				function( callback ){
					var _q = " SELECT id, v_email ";
					_q += " FROM tbl_user WHERE true ";
					_q += " AND id != '"+login_id+"' ";
					_q += " AND ( LOWER( v_email ) = '"+v_email.toLowerCase()+"' OR v_phone = '"+v_phone+"' ); ";
					dclass._query( _q, function( status, data ){ 
						if( status && data.length ){
							gnrl._remove_loop_file( fs, fileArr );
							if( v_email == data[0].v_email ){
								gnrl._api_response( res, 0, 'err_msg_exists_email', {} );
							}
							else{
								gnrl._api_response( res, 0, 'err_msg_exists_phone', {} );
							}
						}
						else{
							callback( null );
						}
					});
				},
				
				
				// Update User
				function( callback ){
					var _ins = {
						'v_name' : v_name,
						'v_email' : v_email,
						'v_phone' : v_phone,
						'i_city_id' : i_city_id,
						'd_modified' : gnrl._db_datetime(),
					};
					dclass._update( 'tbl_user', _ins, " AND id = '"+login_id+"' ", function( status, data ){ 
						if( !status ){
							gnrl._remove_loop_file( fs, fileArr );
							gnrl._api_response( res, 0, 'error', {} );
						}
						else{
							callback( null );
						}
					});
				},
				
				
				// Update Vehicle Info
				function( callback ){
					
					var isVehicleUpdate = 0;
					
					var _ins = {};
					
					if( fileArr['v_image_puc'].name ){
						_ins.v_image_puc = fileArr['v_image_puc'].name;
						isVehicleUpdate = 1;
					}
					if( fileArr['v_image_insurance'].name ){
						_ins.v_image_insurance = fileArr['v_image_insurance'].name;
						isVehicleUpdate = 1;
					}
					
					if( !isVehicleUpdate ){
						callback( null );
					}
					else{
						dclass._update( 'tbl_vehicle', _ins, " AND i_driver_id = '"+login_id+"' ", function( status, data ){ 
							if( !status ){
								gnrl._remove_loop_file( fs, fileArr );
								gnrl._api_response( res, 0, 'error', {} );
							}
							else{
								if( fileArr['v_image_puc'].name ){
									fs.rename( fileArr['v_image_puc'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_puc'].name, function(err){});
								}
								if( fileArr['v_image_insurance'].name ){
									fs.rename( fileArr['v_image_insurance'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_insurance'].name, function(err){});
								}
								callback( null );
							}
						});
						
					}
				}
				
			], function( error, results ){
				
				gnrl._api_response( res, 1, 'succ_profile_updated', {} );
				
			});
			
		}
	});
};

module.exports = currentApi;