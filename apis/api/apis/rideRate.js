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
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var login_id    = gnrl._is_undf( params.login_id );
	var i_ride_id   = gnrl._is_undf( params.i_ride_id );
	var i_rate      = gnrl._is_undf( params.i_rate );
	var l_comment   = gnrl._is_undf( params.l_comment, '' );
	var v_type      = gnrl._is_undf( params.v_type );
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_id'; }
	if( _status && !i_rate ){ _status = 0; _message = 'err_req_rate'; }
	if( _status && !v_type ){ _status = 0; _message = 'err_req_type'; }
	// if( _status && !l_comment ){ _status = 0; _message = 'err_req_comment'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var i_target_user_id = 0;
		var _data = {};
		
		async.series([
		
			// Get Ride
			function( callback ){
				
				var _q = " SELECT ";
				_q += " id, i_user_id, i_driver_id ";
				_q += " FROM tbl_ride ";
				_q += " WHERE id = '"+i_ride_id+"' AND ( i_user_id = '"+login_id+"' OR i_driver_id = '"+login_id+"' ); ";
				
				dclass._query( _q, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else{
						i_target_user_id = ( v_type == 'user' ? data[0].i_driver_id : data[0].i_user_id );
						callback( null );
					}
				});
			},
			
			// Add Ride Rate
			function( callback ){
				var _ins = {
					'i_target_user_id' : i_target_user_id,
					'i_ride_id'  : i_ride_id,
					'i_rate'     : parseInt(i_rate),
					'l_comment'  : l_comment,
					'i_added_by' : login_id,
					'd_added'    : gnrl._db_datetime(),
					'l_data'     : { 
						'v_type' : v_type,
					}
				};
				dclass._insert( 'tbl_ride_rate', _ins, function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						callback( null );
					}
				});
			},
			
			
			// Update Total Rating
			function( callback ){
				
				var _q = " SELECT ";
					_q += " SUM( i_rate ) AS rate_sum ";
					_q += " , COUNT( 1 ) AS rate_count ";
					_q += " FROM tbl_ride_rate ";
					_q += " WHERE i_target_user_id = '"+i_target_user_id+"' ";
					
				dclass._query( _q, function( status, data ){
					
					if( status && data.length ){
						
						var temp = data[0];
						
						var _ins = [
							"l_data = l_data || '"+( gnrl._json_encode({
								'rate' : Math.round( parseFloat( temp.rate_sum ) / parseInt( temp.rate_count ) ),
								'rate_total' : parseInt( temp.rate_count )
							}) )+"'",
						];
						
						dclass._updateJsonb( "tbl_user", _ins, " AND id = '"+i_target_user_id+"' ", function( status, data ){ 
							callback( null );
						});
					}
					else{
						callback( null );
					}
				});
				
			}
			
		], 
		function( error, results ){
			
			gnrl._api_response( res, 1, 'succ_ride_rate_successfully', {
				//target_user_id : target_user_id,
				//_data : _data
			} );
			
		});
		
		
	}
};

module.exports = currentApi;
