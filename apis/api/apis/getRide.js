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
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	if( _status ){
		
		var _row = {
		};
		
		async.series([
		
			function( callback ){
				Ride.get( i_ride_id, function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, _message );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else if( data[0].i_user_id != login_id && data[0].i_driver_id != login_id ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else{
						_row = data[0];
					}
					callback( null );
				});
			},
			
			function( callback ){
				_row.driver_data = {};
				if( !_row.i_driver_id ){
					callback( null );
				}
				else{
					var _q = "SELECT ";
					_q += " a.id";
					_q += " , a.v_image AS driver_image";
					_q += " , a.v_name AS driver_name";
					_q += " , a.v_phone AS driver_phone";
					_q += " , b.id AS vehicle_id";
					_q += " , b.v_image_rc_book";
					_q += " , b.v_image_puc";
					_q += " , b.v_image_insurance";
					_q += " FROM ";
					_q += " tbl_user AS a ";
					_q += " LEFT JOIN tbl_vehicle AS b ON a.id = b.i_driver_id ";
					_q += " WHERE a.id = '"+_row.i_driver_id+"' ";
					dclass._query( _q, function( driver_status, driver_data ){
						if( !driver_status ){
							callback( null );
						}
						else if( !driver_data.length ){
							callback( null );
						}
						else if( driver_data.length ){
							driver_data = driver_data[0];
							driver_data.driver_image = gnrl._uploads( 'users/'+driver_data.driver_image );
							driver_data.v_image_rc_book = gnrl._uploads( 'vehicle/'+driver_data.v_image_rc_book );
							driver_data.v_image_puc = gnrl._uploads( 'vehicle/'+driver_data.v_image_puc );
							driver_data.v_image_insurance = gnrl._uploads( 'vehicle/'+driver_data.v_image_insurance );
							_row.driver_data = driver_data;
							callback( null );
						}
					});
				}
			},
			
			function( callback ){
				_row.user_data = {};
				var _q = "SELECT ";
					_q += " id ";
					_q += " ,v_name ";
					_q += " ,v_email ";
					_q += " ,v_phone ";
					_q += " ,v_image ";
					_q += " FROM ";
					_q += " tbl_user ";
					_q += " WHERE id = '"+_row.i_user_id+"' ";
					
				dclass._query( _q, function( status, data ){ 
					if( !status ){
						callback( null );
					}
					else if( !data.length ){
						callback( null );
					}
					else{
						var user_data = data[0];
						user_data.v_image = gnrl._uploads( 'users/'+user_data.v_image );
						_row.user_data = user_data;
					}
					callback( null );
				});
			}
			
		], 
		function( error, results ){
			_row.d_time = gnrl._timestamp( _row.d_time );
			gnrl._api_response( res, 1, '', _row );
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
