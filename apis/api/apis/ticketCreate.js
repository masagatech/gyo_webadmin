var express = require('express');
var validator = require('validator');
var md5 = require('md5');
var async = require('async');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var v_type = gnrl._is_undf( params.v_type ).trim();
	var i_type_id = gnrl._is_undf( params.i_type_id ).trim();
	var v_support_text = gnrl._is_undf( params.v_support_text ).trim();
	
	
	if( !v_type ){ _status = 0; _message = 'err_req_type'; }
	if( _status && !i_type_id ){ _status = 0; _message = 'err_req_type_id'; }
	if( _status && !v_support_text ){ _status = 0; _message = 'err_req_support_text'; }
	
	if( !_status ){
		
		gnrl._api_response( res, 0, _message );
		
	}
	else{
		
		/*
		>> Get Support Title
		>> Save Support Inquiry
		>> Update Support Inquiry ID
		>> Get User
		>> Send SMS + Email To Admin
		*/
		
		var _user = { id : 0 };
		var _ins = {
			'i_user_id' : login_id,
			'v_type' : v_type,
			'i_type_id' : i_type_id,
			'l_data' : {
				'j_title' : '',
				'j_text' : '',
				'v_support_text' : v_support_text,
			},
			'd_added' : gnrl._db_datetime(),
			'd_modified' : gnrl._db_datetime(),
			'e_status' : 'pending',
			'v_support_id' : '',
		};
		
		async.series([
			
			// Get Support Title
			function( callback ){
				
				if( v_type == 'faq' ){
					var _q = " SELECT id, j_title, j_text, i_textbox FROM tbl_faq WHERE id = '"+i_type_id+"' ";
				}
				else{
					var _q = " SELECT id, j_title, j_text, i_textbox FROM tbl_support_types WHERE id = '"+i_type_id+"' ";
				}
				
				
				dclass._query( _q, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error' );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_records' );
					}
					else{
						_ins.l_data.j_title = gnrl._getLangField( data[0].j_title, _lang );
						_ins.l_data.j_text = gnrl._getLangField( data[0].j_text, _lang );
						callback( null );
					}
				});
			},
			
			// Save Support Inquiry
			function( callback ){
				dclass._insert( 'tbl_support_ticket', _ins, function( status, inquiry ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						_ins.id = inquiry.id;
						callback( null );
					}
				});
			},
			
			// Update Support Inquiry ID
			function( callback ){
				_ins.v_support_id = 'SPINQ'+gnrl._pad_left( _ins.id, "000000" );
				var _ins2 = { 
					'v_support_id' : _ins.v_support_id,
				};
				dclass._update( 'tbl_support_ticket', _ins2, " AND id = '"+_ins.id+"' ", function( status, updated ){ 
					callback( null );
				});
			},
			
			// Get User
			function( callback ){
				
				var _q = " SELECT ";
				_q += " id, v_id, v_name, v_email, v_phone, l_data ";
				_q += " FROM tbl_user WHERE id = '"+login_id+"' ";
				
				dclass._query( _q, function( status, data ){
					if( status && data.length ){
						_user = data[0];
						callback( null );
					}
					else{
						callback( null );
					}
				});
			},
			
			
			// Send SMS
			function( callback ){
				if( _user.id != 0 ){
					SMS.send( {
						_to      	: _user.v_phone,
						_lang 		: User.lang( _user ),
						_key 		: 'user_submit_support_inquiry',
						_keywords 	: {
							'[user_name]' : _user.v_name,
							'[support_inq_id]' : _ins.v_support_id,
							'[support_inq_text]' : v_support_text,
						},
					}, function( error_mail, error_info ){
						callback( null );
					});	
				}
				else{
					callback( null );
				}
			},
			
			
			// Send Email
			function( callback ){
				if( _user.id != 0 ){
					var params = {
						_to      	: _user.v_email,
						_lang 		: User.lang( _user ),
						_key 		: 'user_submit_support_inquiry',
						_keywords 	: {
							'[user_name]' : _user.v_name,
							'[support_inq_id]' : _ins.v_support_id,
							'[support_inq_text]' : v_support_text,
						},
					};
					Email.send( params, function( error_mail, error_info ){
						callback( null );
					});
				}
				else{
					callback( null );
				}
			},
			
			
		], function( error, results ){
			
			gnrl._api_response( res, 1, 'succ_support_inquiry_submitted', {
				support_inq_id : _ins.v_support_id
			});
			
		});
		
	}
	
};

module.exports = currentApi;
