var express = require('express');
var async = require('async');
var FCM = require('fcm-node');

var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_push_notification';
	var table2 = 'tbl_track_push_notification';
	
	// https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
	
	return {
		
		send : function( params, cb ){
			
			var _key 			= params._key ? params._key : '';
			var _role 			= params._role ? params._role : '';
			var _tokens 		= params._tokens ? params._tokens : [];
			var _keywords 		= params._keywords ? params._keywords : {};
			var _custom_params 	= params._custom_params ? params._custom_params : {};
			var _need_log 		= params._need_log ? params._need_log : 0;
			
			_custom_params.type = _key;
			
			var _template = {};
			var _langwise_tokens = {};
			var _langsArr = [];
			var _insQuery = [];
			
			var _result = {
				'msg' : '',
				'succ' : [],
				'fail' : [],
				'succ_data' : {},
				'fail_data' : {},
				'all_result' : {},
			};
			
			async.series([
				
				// Check Requirnments
				function( callback ){
					if( !_tokens.length ){
						_result.msg = 'err_msg_no_device_tokens';
						cb( 0, _result );
					}
					else if( _role != 'user' && _role != 'driver' ){
						_result.msg = 'err_invalid_role';
						cb( 0, _result );
					}
					else if( _key == '' ){
						_result.msg = 'err_invalid_key';
						cb( 0, _result );
					}
					else{
						callback( null );
					}
				},
				
				// Get Template
				function( callback ){
					
					var server_key = '';
					if( _role == 'user' ){
						server_key = 'USER_NOTIFICATION_KEY';
					}
					else{
						server_key = 'DRIVER_NOTIFICATION_KEY';
					}
					
					var _q = "SELECT ";
					_q += " a.* ";
					_q += " , ( SELECT l_value FROM tbl_sitesetting WHERE v_key = '"+server_key+"' ) AS server_key";
					_q += " FROM ";
					_q += ""+table+" a";
					_q += " WHERE v_type = '"+_key+"' ";
					_q += " AND i_delete = '0' AND e_status = 'active' ";
					
					
					dclass._query( _q, function( status, data ){
						if( !status ){
							_result.msg = 'err_msg_no_notification_template';
							cb( 0, _result );
						}
						else if( !data.length ){
							_result.msg = 'err_msg_no_notification_template';
							cb( 0, _result );
						}
						else{
							_template = data[0];
							for( var k in _template.j_title ){
								var title = _template.j_title[k];
								var body = _template.j_content[k];
								for( var k1 in _keywords ){
									title = title.split( k1 ).join( _keywords[k1] );
									body = body.split( k1 ).join( _keywords[k1] );
								}
								_template.j_title[k] = title;
								_template.j_content[k] = body;
							}
							callback( null );
						}
					});
				},
				
				// Merge Language Wise
				function( callback ){
					for( var k in _tokens ){
						var lang = _tokens[k].lang;
						if( !_langwise_tokens[lang] ){ 
							_langsArr.push( lang );
							_langwise_tokens[lang] = {
								ids : [],
								tokens : [],
							}; 
						}
						_langwise_tokens[lang].ids.push( _tokens[k].id );
						_langwise_tokens[lang].tokens.push( _tokens[k].token );
					}
					callback( null );
				},
				
				// Send Notification
				function( callback ){
					
					var fcm = new FCM( _template.server_key );
					
					var sendNotiArr = [];
					
					for( var i = 0; i < _langsArr.length; i++ ){
						
						sendNotiArr.push( function( callback ){
							var index = _langsArr.length - 1;
							var indexVal = _langsArr[index];
							_langsArr.splice( index, 1 );
							
							var k = indexVal;
							var temp = _langwise_tokens[k];
							
							_custom_params.title = _template.j_title[k];
							_custom_params.body = _template.j_content[k];
							
							var fcm_parmas = {
								registration_ids : temp.tokens,
								notification : {
									title 	: _template.j_title[k],
									body 	: _template.j_content[k],
									sound	: ( _key == "driver_ride_buzz" ) ? "notification_tone" : "notification_tone_2",
								},
								data : _custom_params
							};
							
							fcm.send( fcm_parmas, function( err, response ){
								var resp = ( response ? response : err );
								if( gnrl._isJson( resp ) ){
									resp = JSON.parse( resp );
								}
								_result.all_result[k] = resp;
								callback( null );
							});
							
						});
					}
					
					async.series( sendNotiArr, function( error_1, results_1 ){
						callback( null );
					});
					
				},
				
				// Arrange Results Sets
				function( callback ){
					if( _result.all_result ){
						for( var k in _result.all_result ){
							var val = _result.all_result[k];
							var temp = _langwise_tokens[k];
							for( var k1 in val.results ){
								if( val.results[k1].error ){
									_result.fail.push( temp.ids[k1] );
									_result.fail_data[temp.ids[k1]] = val.results[k1].error;
								}
								else{
									_result.succ.push( temp.ids[k1] );
									_result.succ_data[temp.ids[k1]] = val.results[k1].message_id;
								}
							}
						}
					}
					delete _result.all_result;
					callback( null );
				},
				
				// Take Entry in Track Table
				function( callback ){
					if( _need_log == 1 ){
					
						var i_view = 0;
						var l_data = {
							'title' : _template.j_title,
							'content' : _template.j_content,
							'i_view' : i_view,
							'view_time' : gnrl._db_datetime(),
							'message_id' : '',
							'error' : '',
						};
						for( var k in _custom_params ){
							l_data[k] = _custom_params[k];
						}
						
						
						for( var k in _result.succ_data ){
							l_data.message_id = _result.succ_data[k];
							_insQuery.push(  
								" INSERT INTO tbl_track_push_notification "
								+" ( i_user_id, i_push_notification_id, i_status, d_added, v_type, l_data ) "
								+" VALUES "
								+" ( "
									+k+", "
									+_template.id+", "
									+"1, "
									+"'"+gnrl._db_datetime()+"',"
									+"'"+_key+"',"
									+"'"+gnrl._json_encode( l_data )+"'"
								+" ); "
							);
						}
						for( var k in _result.fail_data ){
							
							l_data.error = _result.fail_data[k];
							
							_insQuery.push(  
								" INSERT INTO tbl_track_push_notification "
								+" ( i_user_id, i_push_notification_id, i_status, d_added, v_type, l_data ) "
								+" VALUES "
								+" ( "
									+k+", "
									+_template.id+", "
									+"0, "
									+"'"+gnrl._db_datetime()+"',"
									+"'"+_key+"',"
									+"'"+gnrl._json_encode( l_data )+"'"
								+" ); "
							);
						}
					
						if( _insQuery.length ){
							_insQuery = _insQuery.join(';');
							dclass._query( _insQuery, function( status, data ){
								callback( null );
							});
						}
						else{
							callback( null );
						}
					}
					else{
						callback( null );
					}
				}
				
			], 
			
			function( error, results ){
				cb( 1, _result );
			});
			
		},
		
	}
};

module.exports = currClass;
