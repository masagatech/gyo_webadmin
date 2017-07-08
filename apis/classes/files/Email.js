var express = require('express');
var nodemailer = require('nodemailer');
var async = require('async');

var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_email';
	
	return {
		
		send : function( params, cb ){
			
			var _to 		= params._to ? params._to : '';
			var _key 		= params._key ? params._key : '';
			var _lang 		= params._lang ? params._lang : global._lang;
			var _keywords 	= params._keywords ? params._keywords : {};
			
			var _title 		= params._title ? params._title : '';
			var _body 		= params._body ? params._body : '';
			
			var _template = {};
			var _result = {
				email : {},
				template : {},
			};
			
			async.series([
				
				// Check Requirnments
				function( callback ){
					if( !_to ){
						return cb( 0, 'err_req_email' );
					}
					else if( _key == '' && ( _title == '' && _body == '' ) ){
						return cb( 0, 'err_invalid_key' );
					}
					else{
						callback( null );
					}
				},
				
				// Get Template
				function( callback ){
					
					if( _key ){
						
						var _q = "SELECT j_title, j_content FROM tbl_email WHERE i_delete = '0' AND v_key = '"+_key+"' AND e_status = 'active' ";
						
						dclass._query( _q, function( status, data ){
							if( !status ){
								return cb( 0, 'err_msg_no_notification_template' );
							}
							else if( !data.length ){
								return cb( 0, 'err_msg_no_notification_template' );
							}
							else{
								data[0] = gnrl._getLangWiseData( data[0], _lang, [
									'j_title',
									'j_content',
								]);
								_result.email = data[0];
								callback( null );
							}
						});	
						
					}
					else{
						
						_result.email.j_title = _title;
						_result.email.j_content = _body;
						
						callback( null );
						
					}
					
				},
				
				// Get Settings
				function( callback ){
					var email_template = 'EMAIL_TEMPLATE_'+_lang;
					var keys = [
						email_template,
						'MAIL_FROM_NAME',
						'MAIL_FROM_EMAIL',
						'MAIL_VIA',
						'MAIL_SMTP_HOST',
						'MAIL_SMTP_PORT',
						'MAIL_SMTP_USERNAME',
						'MAIL_SMTP_PASSWORD',
					];
					Settings.getMulti( keys, function( status, val ){
						_result.settings = val;
						_result.settings.email_template = val[email_template];
						callback( null );
					});
				},
				
				// Repace Keywords
				function( callback ){
					
					var temp1 = _result.email.j_title;
					var temp2 = _result.settings.email_template.split('[email_body]').join( _result.email.j_content );
					
					for( var k in _keywords ){
						temp1 = temp1.split( k ).join( _keywords[k] );
						temp2 = temp2.split( k ).join( _keywords[k] );
					}
					
					_result.email.j_title = temp1.replace(/\\/g, '' );
					_result.settings.email_template = temp2.replace(/\\/g, '' );

					callback( null );
					
				},
				
				
				// Send Email
				function( callback ){
					
					let transporter = nodemailer.createTransport({
						service: _result.settings.MAIL_VIA,
						host: _result.settings.MAIL_SMTP_HOST,
						port: parseInt( _result.settings.MAIL_SMTP_PORT ),
						auth: {
							user: _result.settings.MAIL_SMTP_USERNAME,
							pass: _result.settings.MAIL_SMTP_PASSWORD
						}
					});
					
					// setup email data with unicode symbols
					let mailOptions = {
						to : _to,
						from : '"'+_result.settings.MAIL_FROM_NAME+' " <'+_result.settings.MAIL_FROM_EMAIL+'>',
						subject : _result.email.j_title,
						html : _result.settings.email_template,
						text : '',
					};
					
					// send mail with defined transport object
					transporter.sendMail( mailOptions, function( error, info ){
						if( error ){
							return cb( 0, error );
						}
						else{
							return cb( 1, info );
						}
					});
					
				},
				
			],  function( error, results ){
				
				return cb( 0, 'err_msg_email_not_sent' );
				
			});
			
		},
		
	}
};

module.exports = currClass;
