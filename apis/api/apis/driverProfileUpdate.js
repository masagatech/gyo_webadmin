var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var fs 			= require('fs');

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
			'v_image' 				: { 'name' : '', 'path' : '', },
			'v_image_rc_book' 		: { 'name' : '', 'path' : '', },
			'v_image_puc' 			: { 'name' : '', 'path' : '', },
			'v_image_insurance' 	: { 'name' : '', 'path' : '', },
			'v_image_license' 		: { 'name' : '', 'path' : '', },
			'v_image_adhar_card'	: { 'name' : '', 'path' : '', },
			'v_image_permit_copy'	: { 'name' : '', 'path' : '', },
			'v_image_police_copy' 	: { 'name' : '', 'path' : '', },
		};
		
		for( var k in req.files ){
			if( req.files[k].filename ){
				fileArr[ req.files[k].fieldname ] = {
					'name' : req.files[k].filename,
					'path' : req.files[k].path
				};
			}
		}
		
		var login_id 			= gnrl._is_undf( params.login_id ).trim();
		var v_token 			= gnrl._is_undf( params.v_token ).trim();
		var v_name 				= gnrl._is_undf( params.v_name ).trim();
		var v_email 			= gnrl._is_undf( params.v_email ).trim();
		var v_phone 			= gnrl._is_undf( params.v_phone ).trim();
		var v_gender 			= gnrl._is_undf( params.v_gender, 'male' );
		var v_vehicle_type 		= gnrl._is_undf( params.v_vehicle_type ).trim();
		var v_imei_number 		= gnrl._is_undf( params.v_imei_number ).trim();
		var v_vehicle_number 	= gnrl._is_undf( params.v_vehicle_number ).trim();
		var l_data 				= gnrl._is_undf( params.l_data );
		
		
		if( !login_id.trim() ){ _status = 0; _message = 'err_req_id'; }
		if( _status && !v_token ){ _status = 0; _message = 'err_req_auth_token'; }
		if( _status && !v_name.trim() ){ _status = 0; _message = 'err_req_name'; }
		if( _status && !v_email.trim() ){ _status = 0; _message = 'err_req_email'; }
		if( _status && !v_phone.trim() ){ _status = 0; _message = 'err_req_phone'; }
		if( _status && !validator.isEmail( v_email ) ){ _status = 0; _message = 'err_invalid_email'; }
		if( _status && !v_vehicle_number.trim() ){ _status = 0; _message = 'err_req_vehicle_number'; }
		if( _status && !v_vehicle_type.trim() ){ _status = 0; _message = 'err_req_vehicle_type'; }
		if( _status && !v_imei_number.trim() ){ _status = 0; _message = 'err_req_imei_number'; }
		
		var folder = 'drivers';
		
		if( err ){
			gnrl._remove_loop_file( fs, fileArr );
			gnrl._api_response( res, 0, 'error_file_upload' );
		}
		else{
			
			if( !_status ){
				
				gnrl._remove_loop_file( fs, fileArr );
				gnrl._api_response( res, 0, _message );
				
			}
			else{
				
				
				dclass._select( '*', 'tbl_user', " AND id != '"+login_id+"' AND ( v_email = '"+v_email+"' OR v_phone = '"+v_phone+"' )", function( status, data ){ 
				
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

						dclass._select( '*', 'tbl_user', " AND ( id = '"+login_id+"' )", function( status, data ){ 
							
							if( status && !data.length ){
								gnrl._remove_loop_file( fs, fileArr );
								gnrl._api_response( res, 0, 'err_msg_no_account', {} );
							}
							
							else{
								
								var _row = data[0];
								
								if( v_token != data[0].v_token ){
									gnrl._remove_loop_file( fs, fileArr );
									gnrl._api_response( res, 0, 'err_invalid_auth_token', {} );
								}
								
								else{
									
									fs.rename( fileArr['v_image'].path, dirUploads+'/'+folder+'/'+fileArr['v_image'].name, function(err){});
									var _ins = {
										'v_name'     	: v_name,
										'v_email'    	: v_email,
										'v_phone'    	: v_phone,
										'v_gender' 	 	: v_gender,
										'v_imei_number' : v_imei_number,
										'v_image'    	: fileArr['v_image'].name ? fileArr['v_image'].name : _row.v_image,
										'd_modified' 	: gnrl._db_datetime(),
									};
									
									dclass._update( 'tbl_user', _ins, " AND id = '"+( login_id )+"' ", function( status, data ){ 
										if( !status ){
											gnrl._remove_loop_file( fs, fileArr );
											gnrl._api_response( res, 0, _message );
										}
										else{
											dclass._select( '*', 'tbl_vehicle', " AND ( i_driver_id = '"+login_id+"' )", function( status, data ){ 
												if( !status ){
													gnrl._remove_loop_file( fs, fileArr );
													gnrl._api_response( res, 0, _message );
												}
												else{
													if( data.length ){
														
														var _ins = {
															'v_type' 			: v_vehicle_type,
															'v_vehicle_number' 	: v_vehicle_number,
															
															'v_image_rc_book'	: fileArr['v_image_rc_book'].name ? fileArr['v_image_rc_book'].name : data[0].v_image_rc_book,
															'v_image_puc'		: fileArr['v_image_puc'].name ? fileArr['v_image_puc'].name : data[0].v_image_puc,
															'v_image_insurance'	: fileArr['v_image_insurance'].name ? fileArr['v_image_insurance'].name : data[0].v_image_insurance,
															
															'v_image_license' 		: fileArr['v_image_license'].name ? fileArr['v_image_license'].name : data[0].v_image_license,
															'v_image_adhar_card' 	: fileArr['v_image_adhar_card'].name ? fileArr['v_image_adhar_card'].name : data[0].v_image_adhar_card,
															'v_image_permit_copy' 	: fileArr['v_image_permit_copy'].name ? fileArr['v_image_permit_copy'].name : data[0].v_image_permit_copy,
															'v_image_police_copy' 	: fileArr['v_image_police_copy'].name ? fileArr['v_image_police_copy'].name : data[0].v_image_police_copy,
															
														};
														_status = 1;
														_message = '';
														
														if( !_ins.v_image_rc_book ){ _status = 0; _message = 'err_req_rc_book_image'; }
														if( !_ins.v_image_puc ){ _status = 0; _message = 'err_req_puc_image'; }
														if( !_ins.v_image_insurance ){ _status = 0; _message = 'err_req_insurance_image'; }
														
														if( !_status ){
															gnrl._remove_loop_file( fs, fileArr );
															gnrl._api_response( res, 0, _message, {} );
														}
														else {
															dclass._update( 'tbl_vehicle', _ins, " AND i_driver_id = '"+login_id+"' ", function( status, data ){ 
																if( !status ){
																	gnrl._remove_loop_file( fs, fileArr );
																	gnrl._api_response( res, 0, _message );
																}
																else{
																	fs.rename( fileArr['v_image_rc_book'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_rc_book'].name, function(err){});
																	fs.rename( fileArr['v_image_puc'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_puc'].name, function(err){});
																	fs.rename( fileArr['v_image_insurance'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_insurance'].name, function(err){});
																	
																	fs.rename( fileArr['v_image_license'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_license'].name, function(err){});
																	fs.rename( fileArr['v_image_adhar_card'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_adhar_card'].name, function(err){});
																	fs.rename( fileArr['v_image_permit_copy'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_permit_copy'].name, function(err){});
																	fs.rename( fileArr['v_image_police_copy'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_police_copy'].name, function(err){});
																	
																	gnrl._api_response( res, 1, 'succ_profile_updated', {} );
																}
															});
														}
													}
													else{
														_status = 1;
														_message = '';
														if( _status && !fileArr['v_image_rc_book'].name ){ _status = 0; _message = 'err_req_rc_book_image'; }
														if( _status && !fileArr['v_image_puc'].name ){ _status = 0; _message = 'err_req_puc_image'; }
														if( _status && !fileArr['v_image_insurance'].name ){ _status = 0; _message = 'err_req_insurance_image'; }
														if( _status ){
															gnrl._remove_loop_file( fs, fileArr );
															gnrl._api_response( res, 0, _message, {}, 1 );
														}
														else{
															dclass._insert( 'tbl_vehicle', {
																'i_driver_id' 		: login_id,
																'v_type' 			: v_vehicle_type,
																'v_vehicle_number' 	: v_vehicle_number,
																'v_image_rc_book' 	: fileArr['v_image_rc_book'].name,
																'v_image_puc' 		: fileArr['v_image_puc'].name,
																'v_image_insurance' : fileArr['v_image_insurance'].name,
																
																
															}, function( status, data ){ 
																if( !status ){
																	gnrl._remove_loop_file( fs, fileArr );
																	gnrl._api_response( res, 0, _message );
																}
																else{
																	fs.rename( fileArr['v_image_rc_book'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_rc_book'].name, function(err){});
																	fs.rename( fileArr['v_image_puc'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_puc'].name, function(err){});
																	fs.rename( fileArr['v_image_insurance'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_insurance'].name, function(err){});
																	gnrl._api_response( res, 1, 'succ_profile_updated', {} );
																}
															});
														}
													}
												}
											});
										}
									});
								}
							}
						});
					}
				});
			}
		}
	});
};

module.exports = currentApi;