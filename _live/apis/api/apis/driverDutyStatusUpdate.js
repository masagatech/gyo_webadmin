var express = require('express');
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
	var i_vehicle_id = gnrl._is_undf( params.i_vehicle_id );
	var e_status = gnrl._is_undf( params.e_status );
	
	if( !e_status ){ _status = 0; _message = 'err_req_status'; }
	if( _status && !i_vehicle_id ){ _status = 0; _message = 'err_req_vehicle_id'; }
	if( _status && ['active','inactive'].indexOf( e_status ) < 0 ){ _status = 0; _message = 'err_invalid_status'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message, {} );
	}
	else{
		
		async.series([
			
			// Update Driver
			function( callback ){
				var _ins = {
					'is_onduty' : ( e_status == 'active' ) ? 1 : 0,
					'is_onride' : 0,
					'is_buzzed' : 0,
				};
				dclass._update( 'tbl_user', _ins, " AND id = '"+login_id+"' ", function( status, data ){ 
					callback( null );
				});
			},
			
			// Take Tracking
			function( callback ){
				if( e_status == 'active' ){
					User.startLog( login_id, 'driver', 'duty', function( status, data ){
						callback( null );
					});
				}
				else{
					User.finishLog( login_id, 'driver', 'duty', function( status, data ){
						callback( null );
					});
				}
			}
			
		], function( error, results ){
			
			gnrl._api_response( res, 1, 'succ_status_updated', {} );
			
		});
		
	}
};

module.exports = currentApi;