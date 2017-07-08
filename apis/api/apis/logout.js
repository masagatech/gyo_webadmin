var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var async       = require('async');

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
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		/*
		>> Get Last Login Log
		>> Update User
		>> Terminate Logs
		*/
		
		var _user = {};
		
		async.series([
		
			// Get User
			function( callback ){
				
				var _q = " SELECT ";
				_q += " id, v_role";
				_q += " FROM ";
				_q += " tbl_user ";
				_q += " WHERE true ";
				_q += " AND id = '"+login_id+"' ";
				
				dclass._query( _q, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_msg_no_account', {} );
					}
					else{
						_user = data[0];
						callback( null );
					}
				});
			},
			
			// Update User
			function( callback ){
				var _ins = {
					'v_token' 	: '',
					'is_onduty'	: 0,
					'is_onride' : 0,
					'is_buzzed' : 0,
				};
				dclass._update( 'tbl_user', _ins, " AND id = '"+login_id+"'", function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, _message );
					}
					else{
						callback( null );
					}
				});
			},
		
			
			// Terminate Logs
			function( callback ){
				if( _user.v_role == 'user' ){
					User.finishLog( login_id, 'user', 'login', function( status, data ){
						callback( null );
					});
				}
				else{
					async.series([
						function( callback ){
							User.finishLog( login_id, 'driver', 'login', function( status, data ){
								callback( null );
							});
						},
						function( callback ){
							User.finishLog( login_id, 'driver', 'duty', function( status, data ){
								callback( null );
							});
						}
					], function( error, results ){
						callback( null );
					});
				}
			},
			
		], function( error, results ){
			
			gnrl._api_response( res, 1, 'succ_logout_successfully', {} );
			
		});

	}
};

module.exports = currentApi;
