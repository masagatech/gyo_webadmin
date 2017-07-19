var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var fs 			= require('fs');
var async       = require('async');


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
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	upload( req, res, function( err ){
		
		var params = gnrl._frm_data( req );
		
		var fileArr = {
			'v_image' 				: { 'name' : '', 'path' : '', },
			'v_image_rc_book' 		: { 'name' : '', 'path' : '', },
			'v_image_puc' 			: { 'name' : '', 'path' : '', },
			'v_image_insurance' 	: { 'name' : '', 'path' : '', },
			'v_image_license' 		: { 'name' : '', 'path' : '', },
			'v_image_adhar_card'	: { 'name' : '', 'path' : '', },
			'v_image_permit_copy'	: { 'name' : '', 'path' : '', },
			'v_image_police_copy' 	: { 'name' : '', 'path' : '', },
			
			'v_image_rc_book_2' 	: { 'name' : '', 'path' : '', },
			'v_image_adhar_card_2' 	: { 'name' : '', 'path' : '', },
			'v_image_license_2' 	: { 'name' : '', 'path' : '', },
		};
		
		for( var k in req.files ){
			if( req.files[k].filename ){
				fileArr[ req.files[k].fieldname ] = {
					'name' : req.files[k].filename,
					'path' : req.files[k].path
				};
			}
		}
		
		// _response.a = req.files;
		// _response.b = fileArr;
		
		var v_name = gnrl._is_undf( params.v_name );
		var v_email = gnrl._is_undf( params.v_email );
		var v_phone = gnrl._is_undf( params.v_phone );
		var v_gender = gnrl._is_undf( params.v_gender, 'male' );
		var v_password = gnrl._is_undf( params.v_password );
		var v_device_token = gnrl._is_undf( params.v_device_token );
		var v_imei_number = gnrl._is_undf( params.v_imei_number );
		var i_city_id = gnrl._is_undf( params.i_city_id );
		var v_vehicle_type = gnrl._is_undf( params.v_vehicle_type );
		var v_vehicle_number = gnrl._is_undf( params.v_vehicle_number );
		var l_latitude = gnrl._is_undf( params.l_latitude );
		var l_longitude = gnrl._is_undf( params.l_longitude );
		var l_data = gnrl._is_undf( params.l_data, {} );
		var v_otp = gnrl._get_otp();
		
		var refferal_code 	= gnrl._is_undf( params.refferal_code, '' );

		
		if( !v_name.trim() ){ _status = 0; _message = 'err_req_name'; }
		if( _status && !v_email.trim() ){ _status = 0; _message = 'err_req_email'; }
		if( _status && !validator.isEmail( v_email ) ){ _status = 0; _message = 'err_invalid_email'; }
		if( _status && !v_phone.trim() ){ _status = 0; _message = 'err_req_phone'; }
		if( _status && !validator.isLength( v_phone, { min : 10, max : 10 } ) ){ _status = 0; _message = 'err_validation_phone'; }
		if( _status && !v_password.trim() ){ _status = 0; _message = 'err_req_password'; }
		if( _status && !validator.isLength( v_password, { min : 6, max : 10 } ) ){ _status = 0; _message = 'err_validation_password'; }
		if( _status && !v_device_token.trim() ){ _status = 0; _message = 'err_req_device_token'; }
		if( _status && !v_imei_number.trim() ){ _status = 0; _message = 'err_req_imei_number'; }
		if( _status && !i_city_id.trim() ){ _status = 0; _message = 'err_req_city'; }
		if( _status && !l_latitude.trim() ){ _status = 0; _message = 'err_req_latitude'; }
		if( _status && !l_longitude.trim() ){ _status = 0; _message = 'err_req_longitude'; }
		if( _status && !v_vehicle_number.trim() ){ _status = 0; _message = 'err_req_vehicle_number'; }
		if( _status && !v_vehicle_type.trim() ){ _status = 0; _message = 'err_req_vehicle_type'; }
		
		if( _status && !fileArr['v_image'].name ){ _status = 0; _message = 'err_req_profile_image'; }
		if( _status && !fileArr['v_image_rc_book'].name ){ _status = 0; _message = 'err_req_rc_book_image'; }
		if( _status && !fileArr['v_image_rc_book_2'].name ){ _status = 0; _message = 'err_req_rc_book_image'; }
		
		if( _status && !fileArr['v_image_puc'].name ){ _status = 0; _message = 'err_req_puc_image'; }
		if( _status && !fileArr['v_image_insurance'].name ){ _status = 0; _message = 'err_req_insurance_image'; }
		if( _status && !fileArr['v_image_license'].name ){ _status = 0; _message = 'err_req_image_license'; }
		if( _status && !fileArr['v_image_license_2'].name ){ _status = 0; _message = 'err_req_image_license'; }
		if( _status && !fileArr['v_image_adhar_card'].name ){ _status = 0; _message = 'err_req_image_adhar_card'; }
		if( _status && !fileArr['v_image_adhar_card_2'].name ){ _status = 0; _message = 'err_req_image_adhar_card'; }
		if( _status && !fileArr['v_image_permit_copy'].name ){ _status = 0; _message = 'err_req_image_permit_copy'; }
		if( _status && !fileArr['v_image_police_copy'].name ){ _status = 0; _message = 'err_req_image_police_copy'; }
		
		var folder = 'drivers';
		var cityCode = '';
		
		if( err ){
			gnrl._remove_loop_file( fs, fileArr );
			gnrl._api_response( res, 0, 'error_file_upload' );
		}
		else{
			
			if( _status ){
				
				var _user_insert = [];
				
				var _code = {
					id : 0,
					amount : 0,
					wallet_type : '',
					wallet_apply : '',
				};
				
				async.series([
					
					// Check Email OR Phone Exists
					function( callback ){
						dclass._select( 'id,v_email,v_phone', 'tbl_user', " AND ( LOWER( v_email ) = '"+v_email.toLowerCase()+"' OR v_phone = '"+v_phone+"' )", function( status, user ){ 
							if( !status ){
								gnrl._remove_loop_file( fs, fileArr );
								gnrl._api_response( res, 0, 'error', {} );
							}
							else if( user.length ){
								gnrl._remove_loop_file( fs, fileArr );
								if( user[0].v_phone == v_phone ){
									gnrl._api_response( res, 0, 'err_msg_exists_phone', {} );
								}
								else{
									gnrl._api_response( res, 0, 'err_msg_exists_email', {} );
								}
							}
							else{
								callback( null );
							}
						});
					},
					
					// Check Referral Code is Valid or Not
					function( callback ){
						if( refferal_code ){
							dclass._select( 'id, v_role, v_phone, v_email', 'tbl_user', " AND v_phone = '"+refferal_code+"' ", function( status, ref_code ){ 
								if( status && ref_code.length ){
									_code = ref_code[0];
									if( _code.v_role == 'user' ){
										var keyArr = [ 'REFERRAL_USER_MONEY', 'REFERRAL_USER_COUPON', 'REFERRAL_USER_APPLY' ];
									}
									else{
										var keyArr = [ 'REFERRAL_DRIVER_MONEY', 'REFERRAL_DRIVER_COUPON', 'REFERRAL_DRIVER_APPLY' ];
									}
									Settings.getMulti( keyArr, function( status, data ){
										if( _code.v_role == 'user' ){
											_code.wallet_apply = data.REFERRAL_USER_APPLY;
											_code.money = parseFloat( data.REFERRAL_USER_MONEY );
											_code.coupon = parseFloat( data.REFERRAL_USER_COUPON );
											_code.amount = _code.money > 0 ? _code.money : _code.coupon;
											_code.wallet_type = _code.amount > 0 ? 'money' : 'coupon';
										}
										else{
											_code.wallet_apply = data.REFERRAL_DRIVER_APPLY;
											_code.money = parseFloat( data.REFERRAL_DRIVER_MONEY );
											_code.coupon = parseFloat( data.REFERRAL_DRIVER_COUPON );
											_code.amount = _code.money > 0 ? _code.money : _code.coupon;
											_code.wallet_type = _code.amount > 0 ? 'money' : 'coupon';
										}
										callback( null );
									});
								}
								else{
									callback( null );
								}
							});
						}
						else{
							callback( null );
						}
					},
					
					// Insert User
					function( callback ){
						var _ins = { 
							'v_role' 			: 'driver',
							'v_name' 			: v_name,
							'v_image' 			: fileArr['v_image'].name,
							'v_email' 			: v_email,
							'v_phone' 			: v_phone,
							'v_gender' 			: v_gender.toLowerCase(),
							'v_imei_number' 	: v_imei_number,
							'is_onduty'			: 0,
							'is_onride' 		: 0,
							'is_buzzed' 		: 0,
							'v_password' 		: md5( v_password ),
							'v_otp' 			: v_otp,
							'd_added' 			: gnrl._db_datetime(),
							'd_modified' 		: gnrl._db_datetime(),
							'e_status' 			: 'inactive',
							'v_device_token' 	: v_device_token,
							'l_latitude' 		: l_latitude,
							'l_longitude' 		: l_longitude,
							'i_city_id' 		: i_city_id,
							'v_token' 			: '',
							'lang'            : _lang,
							'l_data'            : gnrl._json_encode({
								'rate'            : 0,
								'rate_total'      : 0,
								'is_otp_verified' : 0,
								'lang'            : _lang,
								
								'referral_code' 		: _code.amount ? refferal_code : '',
								'referral_user_id' 		: _code.id ? _code.id : 0,
								'referral_amount' 		: _code.amount,
								'referral_wallet_type' 	: _code.wallet_type,
								'referral_wallet_apply' : _code.wallet_apply,
								
							}),
						};
						dclass._insert( 'tbl_user', _ins, function( status, user_insert ){ 
						
							_user_insert = user_insert;
						
							if( !status ){
								gnrl._api_response( res, 0, 'error', {} );
							}
							else{

								fs.rename( fileArr['v_image'].path, dirUploads+'/'+folder+'/'+fileArr['v_image'].name, function(err){});
								
								dclass._insert( 'tbl_vehicle', {
									'i_driver_id' 			: user_insert.id,
									'v_type' 				: v_vehicle_type,
									'v_vehicle_number' 		: v_vehicle_number,
									'v_image_rc_book' 		: fileArr['v_image_rc_book'].name,
									'v_image_puc' 			: fileArr['v_image_puc'].name,
									'v_image_insurance' 	: fileArr['v_image_insurance'].name,
									'v_image_license' 		: fileArr['v_image_license'].name,
									'v_image_adhar_card' 	: fileArr['v_image_adhar_card'].name,
									'v_image_permit_copy' 	: fileArr['v_image_permit_copy'].name,
									'v_image_police_copy' 	: fileArr['v_image_police_copy'].name,
									
									'v_image_rc_book_2' 	: fileArr['v_image_rc_book_2'].name,
									'v_image_license_2' 	: fileArr['v_image_license_2'].name,
									'v_image_adhar_card_2' 	: fileArr['v_image_adhar_card_2'].name,
								}, 
								function( vehicle_status, vehicle_data ){ 
									if( vehicle_status ){
										fs.rename( fileArr['v_image_rc_book'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_rc_book'].name, function(err){});
										fs.rename( fileArr['v_image_puc'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_puc'].name, function(err){});
										fs.rename( fileArr['v_image_insurance'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_insurance'].name, function(err){});
										fs.rename( fileArr['v_image_license'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_license'].name, function(err){});
										fs.rename( fileArr['v_image_adhar_card'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_adhar_card'].name, function(err){});
										fs.rename( fileArr['v_image_permit_copy'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_permit_copy'].name, function(err){});
										fs.rename( fileArr['v_image_police_copy'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_police_copy'].name, function(err){});
										fs.rename( fileArr['v_image_rc_book_2'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_rc_book_2'].name, function(err){});
										fs.rename( fileArr['v_image_license_2'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_license_2'].name, function(err){});
										fs.rename( fileArr['v_image_adhar_card_2'].path, dirUploads+'/'+folder+'/'+fileArr['v_image_adhar_card_2'].name, function(err){});
										callback( null );
									}
									else{
										dclass._delete( 'tbl_user', " AND id = '"+data.id+"' ", function( status, data ){
											gnrl._remove_loop_file( fs, fileArr );
											gnrl._api_response( res, 0, _message );
										});
									}
								});

								
							}
						});
					},
					
					// Get City
					function( callback ){
						dclass._select( 'v_code', 'tbl_city', " AND i_delete = '0' AND id = '"+i_city_id+"' ", function( status, data ){
							if( status && data.length ){
								cityCode = data[0].v_code;
								callback( null );
							}
							else{
								callback( null );
							}
						});
					},
					
					// Generate ID
					function( callback ){
						var _ins = { 
							'v_id' : cityCode+''+gnrl._pad_left( _user_insert.id, "00000" ),
						};
						dclass._update( 'tbl_user', _ins, " AND id = '"+_user_insert.id+"' ", function( status, updated ){ 
							callback( null );
						});
					},
					
					// Send SMS
					function( callback ){
						SMS.send({
							_to : v_phone,
							_lang : _lang,
							_key : 'driver_registration',
							_keywords : {
								'[user_name]' : v_name,
								'[otp]' : v_otp,
							},
						}, function( error_mail, error_info ){
							callback( null );
						});
					},
					
					// Send Email
					function( callback ){
						Email.send({
							_to : v_email,
							_lang : _lang,
							_key : 'driver_registration',
							_keywords : {
								'[user_name]' : v_name,
								'[otp]' : v_otp,
							},
						}, function( error_mail, error_info ){
							callback( null );
						});
					},
					
					
					// ##APPLY_REFERRAL
					function( callback ){
						if( refferal_code && _code.wallet_apply == 'signup' && _code.amount > 0 ){
							User.runReferralModule({
									user_id : _user_insert.id,
									user_name : v_name,
									referral_code : refferal_code,
									referral_amount : _code.amount,
									referral_user_id : _code.id,
									referral_wallet_type : _code.wallet_type,
								}, function( status, data ){
								callback( null );
							});
						}
						else{
							callback( null );
						}
					},
					
				], 
				function( error, results ){
					gnrl._api_response( res, 1, 'succ_register_successfully', { 
						'id' : _user_insert.id,
						'v_phone' : v_phone,
						//'_user_insert' : _user_insert
					});
				});

			}
			else{
				gnrl._remove_loop_file( fs, fileArr );
				gnrl._api_response( res, 0, _message, {} );
			}
		}
	});
	
};

module.exports = currentApi;
