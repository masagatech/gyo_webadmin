var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var async = require('async');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id );
	var notification_id = gnrl._is_undf( params.notification_id );
	if( !notification_id ){ _status = 0; _message = 'err_req_notification_id'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else {
		
		var _notification = {};
		
		async.series([
		
			// Getting Notification Info
			function( callback ){
				dclass._select( '*', 'tbl_track_push_notification', " AND i_user_id = '"+login_id+"' AND id = '"+notification_id+"' ", function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_records', {} );
					}
					else{
						_notification = data[0];
						
						if( _notification.l_data.title[_lang] ){
							_notification.l_data.title = _notification.l_data.title[_lang];
						}
						else if( _notification.l_data.title ){
							_notification.l_data.title = _notification.l_data.title;
						}
						else{
							_notification.l_data.title = '';
						}
						if( typeof( _notification.l_data.title ) == 'object' ){
							_notification.l_data.title = '';
						}
						
						
						if( _notification.l_data.content[_lang] ){
							_notification.l_data.content = _notification.l_data.content[_lang];
						}
						else if( _notification.l_data.content ){
							_notification.l_data.content = _notification.l_data.content;
						}
						else{
							_notification.l_data.content = '';
						}
						if( typeof( _notification.l_data.content ) == 'object' ){
							_notification.l_data.content = '';
						}
						
						
						callback( null );
					}
				});
			},
			
			// Check, If Notification is not viewed
			function( callback ){
				if( _notification.l_data.i_view == 0 ){
					var _ins = [
						"l_data = l_data || '"+( gnrl._json_encode({
							'i_view' : 1,
							'view_time' : gnrl._db_datetime(),
						}) )+"'",
					];
					dclass._updateJsonb( 'tbl_track_push_notification', _ins, " AND id = '"+notification_id+"' ", function( status, data ){ 
						callback( null );
					});	
				}
				else{
					callback( null );
				}
			},
		], 
		function( error, results ){
			gnrl._api_response( res, 1, '', _notification );
		});
		
	}
};

module.exports = currentApi;
