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
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	var v_code = gnrl._is_undf( params.v_code );
	var dtest = gnrl._is_undf( params.dtest, 0 );
	
	if( _status && !v_code ){ _status = 0; _message = 'err_req_promo_code'; }
	if( _status && !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	
	/*
	STEPS
		>> Check if Already Used
		>> Get Coupon Code
		>> Get Ride
		>> Check Other Validation
		>> Update Ride Table
	*/
	
	if( !_status ){
		
		gnrl._api_response( res, 0, _message );
		
	}
	else{

		var _data = {
			promotion_code : {},
			ride : {},
		};
		
		async.series([
		
			// Check if Already Used
			function( callback ){
				var _q = " SELECT ";
					_q += " 1 ";
					_q += " FROM tbl_ride WHERE true ";
					_q += " AND i_user_id = '"+login_id+"' ";
					_q += " AND e_status = 'complete' ";
					_q += " AND LOWER( l_data->'charges'->>'promocode_code' ) = '"+v_code.toLowerCase()+"' ";
					_q += " AND LOWER( l_data->'charges'->>'promocode_code' ) IS NOT NULL ";
					
				dclass._query( _q, function( status, data ){
					if( status && data.length ){
						gnrl._api_response( res, 0, 'err_promotion_code_redeemed', {} );
					}
					else{
						callback( null );
					}
				});
			},
			
			// Get Coupon Code
			function( callback ){
				
				var _q = " SELECT ";
				_q += " id, i_user_ids, i_city_ids, v_code, discount_amount, upto_amount, d_start_date, d_end_date, v_type, e_status ";
				_q += " FROM tbl_coupon_code WHERE i_delete = '0' AND LOWER( v_code ) = '"+v_code.toLowerCase()+"'; ";
				
				dclass._query( _q, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_invalid_promotion_code', {} );
					}
					else if( data[0].v_type != 'ride' ){
						gnrl._api_response( res, 0, 'err_invalid_promotion_code', {} );
					}
					else if( data[0].e_status == 'inactive' ){
						gnrl._api_response( res, 0, 'err_invalid_promotion_code', {} );
					}
					else{
						_data.promotion_code = data[0];
						callback( null );
					}
				});
			},
			
			// Get Ride
			function( callback ){
				
				var _q = " SELECT ";
					_q += " id, i_user_id, l_data ";
					_q += " FROM tbl_ride WHERE id = '"+i_ride_id+"'; ";
					
				dclass._query( _q, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else if( data[0].i_user_id != login_id ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else{
						_data.ride = data[0];
						callback( null );
					}
				});
			},
			
			
			// Check Other Validation
			function( callback ){
				
				var cityIDs = _data.promotion_code.i_city_ids ? _data.promotion_code.i_city_ids.split(',') : [];
				var currTime = gnrl._timestamp( gnrl._db_ymd() );
				var startTime = gnrl._timestamp( _data.promotion_code.d_start_date );
				var endTime = gnrl._timestamp( _data.promotion_code.d_end_date );
				
				if( !gnrl._inArray( _data.ride.l_data.i_city_id, cityIDs ) ){
					gnrl._api_response( res, 0, 'err_promotion_code_not_in_city', {} );
				}
				else if( !( currTime > startTime && currTime < endTime ) ){
					gnrl._api_response( res, 0, 'err_promotion_code_expired', {} );
				}
				else{
					callback( null );
				}
			},
			
			
			// Update Ride Table
			function( callback ){
				
				_data.ride.l_data.charges.promocode_id = _data.promotion_code.id;
				_data.ride.l_data.charges.promocode_code = _data.promotion_code.v_code;
				_data.ride.l_data.charges.promocode_code_discount = _data.promotion_code.discount_amount;
				_data.ride.l_data.charges.promocode_code_discount_upto = _data.promotion_code.upto_amount;
				_data.ride.l_data.charges.promocode_code_discount_amount = 0;
				
				var _ins = [
					" l_data = l_data || '"+gnrl._json_encode( 
						_data.ride.l_data
					)+"' ",
				];
				dclass._updateJsonb( 'tbl_ride', _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', { status : status, data : data } );
					}
					else{
						callback( null );
					}
				});
				
			},
			
		], 
		
		
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_promotion_code_avail', {
				'promocode_id' : _data.promotion_code.id,
				'promocode_code' : _data.promotion_code.v_code,
				'promocode_code_discount' : _data.promotion_code.discount_amount,
				'promocode_code_discount_upto' : _data.promotion_code.upto_amount,
			});
		});
		
	}
};

module.exports = currentApi;
