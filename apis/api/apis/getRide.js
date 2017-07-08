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
	
	var login_id = gnrl._is_undf( params.login_id );
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	if( _status ){
		
		var _row = {
		};
		
		/*
		>> Get Ride
		>> Get Vehicle Icon
		>> Get Driver
		>> Get User
		>> Get Rate & Comment
		>> Get Estimated Prices
		*/
		
		async.series([
			
			// Get Ride
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
						_row.d_time  = gnrl._timestamp( _row.d_time );
						_row.d_start = _row.d_start ? gnrl._timestamp( _row.d_start ) : '';
						_row.d_end 	 = _row.d_end ? gnrl._timestamp( _row.d_end ) : '';
						
						var temp_v_pin = _row.v_pin.toString();
						_row.v_pin = temp_v_pin[0]+temp_v_pin[1]+temp_v_pin[2]+temp_v_pin[3]+'-'+temp_v_pin[4]+temp_v_pin[5]+temp_v_pin[6]+temp_v_pin[7];
						
						callback( null );
					}
					
				});
			},
			
			// Get Vehicle Icon
			function( callback ){
				_row.vehicle_type_data = {
					list_icon : '',
					plotting_icon : '',
				};
				
				var _q = " SELECT ";
				_q += " l_data->>'list_icon' AS list_icon ";
				_q += " , l_data->>'plotting_icon' AS plotting_icon ";
				_q += " FROM tbl_vehicle_type WHERE v_type = '"+_row.l_data.vehicle_type+"'; ";
				
				dclass._query( _q, function( status, data ){ 
					if( status && data.length ){
						var temp = data[0];
						if( temp.list_icon ){ _row.vehicle_type_data.list_icon = gnrl._uploads( 'vehicle_type/'+temp.list_icon ); }
						if( temp.plotting_icon ){ _row.vehicle_type_data.plotting_icon = gnrl._uploads( 'vehicle_type/'+temp.plotting_icon ); }
						callback( null );
					}
					else{
						callback( null );
					}
				});
			},
			
			// Get Driver
			function( callback ){
				
				_row.driver_data = {
					'driver_name' : 'Not Assigned'
				};
				
				if( !_row.i_driver_id ){
					callback( null );
				}
				else{
					
					var _q = "SELECT ";
					
					_q += " a.id";
					_q += " , a.v_image AS driver_image";
					_q += " , a.v_name AS driver_name";
					_q += " , a.v_phone AS driver_phone";
					_q += " , a.v_id";
					
					_q += " , b.id AS vehicle_id";
					
					_q += " , b.v_image_rc_book";
					_q += " , b.v_image_puc";
					_q += " , b.v_image_insurance";
					_q += " , b.v_image_license";
					_q += " , b.v_image_adhar_card";
					_q += " , b.v_image_permit_copy";
					_q += " , b.v_image_police_copy";
					
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
							
							var imagesArr = [
								'driver_image',
								'v_image_puc',
								'v_image_insurance',
								'v_image_license',
								'v_image_adhar_card',
								'v_image_permit_copy',
								'v_image_police_copy',
							];
							for( var k in imagesArr ){
								if( driver_data[imagesArr[k]] != null ){
									driver_data[imagesArr[k]] = gnrl._uploads( 'drivers/'+driver_data[imagesArr[k]] );
								}
								else {
									driver_data[imagesArr[k]] = '';
								}
							}
							
							_row.driver_data = driver_data;
							
							callback( null );
						}
					});
					
				}
			},
			
			// Get User
			function( callback ){
				
				_row.user_data = {};
				
				var _q = "SELECT ";
					_q += " id ";
					_q += " ,v_name ";
					_q += " ,v_email ";
					_q += " ,v_phone ";
					_q += " ,v_image ";
					_q += " ,v_id ";
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
						callback( null );
					}
					
				});
			},
			
			// Get Rate & Comment
			function( callback ){
				
				_row.rate = {
					'i_rate' : 0,
					'rate_cmment' : '',
				};
				if( !_row.i_driver_id ){
					callback( null );
				}
				else{
					
					var _q = " SELECT ";
						_q += " i_rate, l_comment ";
						_q += " FROM ";
						_q += " tbl_ride_rate ";
						_q += " WHERE i_ride_id = '"+i_ride_id+"' AND i_target_user_id = '"+_row.i_driver_id+"' ";
						
					dclass._query( _q, function( status, data ){ 
						if( status && data.length ){
							_row.rate = {
								'i_rate' : data[0].i_rate,
								'rate_cmment' : data[0].l_comment,
							};
							callback( null );
						}
						else{
							callback( null );
						}
					});
				}
			},
			
			// Get Estimated Prices
			function( callback ){
				
				var estimate_km = parseFloat( _row.l_data.estimate_km );
				var estimate_time = parseFloat( _row.l_data.estimate_time );
				
				var estimation = {
					estimate_km : estimate_km,
					estimate_time : estimate_time,
					min_charge : 0,
					base_fare : 0,
					total_fare : 0,
					ride_time_charge : 0,
					service_tax : 0,
					surcharge : 0,
					final_total : 0,
				};
				
				// Min Charge
				var amt = parseFloat( _row.l_data.charges.min_charge );
				if( amt > 0 ){
					estimation.min_charge = amt;
				}
				
				// Base Fare
				var amt = parseFloat( _row.l_data.charges.base_fare );
				if( amt > 0 ){
					estimation.base_fare = amt;
				}
				
				// Total Fare
				if( estimate_km > 0 ){
					
					var upto_km = parseFloat( _row.l_data.charges.upto_km );
					var upto_km_charge = parseFloat( _row.l_data.charges.upto_km_charge );
					var after_km_charge = parseFloat( _row.l_data.charges.after_km_charge );
					
					var amt = 0; 
					if( estimate_km <= upto_km ){ 
						amt += ( upto_km_charge * estimate_km ); 
					}
					else{
						amt += ( upto_km_charge * upto_km ); 
						amt += ( after_km_charge * ( estimate_km - upto_km ) ); 
					}
					
					estimation.total_fare = amt;
					
				}
				
				// Ride Time Charge
				var amt = parseFloat( _row.l_data.charges.ride_time_charge );
				if( amt > 0 ){
					estimation.ride_time_charge = parseFloat( amt * estimate_time );
				}
				
				// Service Tax
				var amt = parseFloat( _row.l_data.charges.service_tax );
				if( amt > 0 ){
					var tempTotal = (
						estimation.min_charge
						+ estimation.base_fare
						+ estimation.total_fare
						+ estimation.ride_time_charge
					);
					estimation.service_tax = parseFloat( ( tempTotal * amt ) / 100 );
				}
				
				// Surcharge
				var amt = parseFloat( _row.l_data.charges.surcharge );
				if( amt > 0 ){
					var tempTotal = (
						estimation.min_charge
						+ estimation.base_fare
						+ estimation.total_fare
						+ estimation.ride_time_charge
						+ estimation.service_tax
					);
					estimation.surcharge = parseFloat( ( tempTotal * amt ) / 100 );
				}
				
				estimation.final_total = gnrl._round(
					estimation.min_charge
					+ estimation.base_fare
					+ estimation.total_fare
					+ estimation.ride_time_charge
					+ estimation.service_tax
					+ estimation.surcharge
				);
				
				_row.l_data.estimation = estimation;
				
				callback( null );
			},
			
			
		], 
		function( error, results ){
			
			gnrl._api_response( res, 1, '', _row );
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
