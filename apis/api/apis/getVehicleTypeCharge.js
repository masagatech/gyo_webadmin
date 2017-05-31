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
	
	var city = gnrl._is_undf( params.city ).trim();
	var latitude = gnrl._is_undf( params.latitude, 0 );
	var longitude = gnrl._is_undf( params.longitude, 0 );
	var vehicle_type = gnrl._is_undf( params.vehicle_type );
	
	latitude = parseFloat( latitude );
	longitude = parseFloat( longitude );
	
	if( !city.trim() ){ _status = 0; _message = 'err_req_city'; }
	if( _status && !vehicle_type.trim() ){ _status = 0; _message = 'err_req_vehicle_type'; }
	if( _status && latitude <= 0 ){ _status = 0; _message = 'err_req_latitude'; }
	if( _status && longitude <= 0 ){ _status = 0; _message = 'err_req_longitude'; }
	
	var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	
	var newData = {};
	
	var currTimeStamp = new Date().getTime();
	var currDate = gnrl._db_ymd('Y-m-d');
	var currDay = days[ new Date().getDay() ];
	var currHour = gnrl._db_ymd('H');
	
	if( !_status ){
		gnrl._api_response( res, 0, _message, {} );
	}
	else{
		
		/*
			>> Get Vehicle Types
			>> Get City Wise Prices
			>> Get Area Wise Prices
		*/
		
		
		async.series([
			
			// Get Vehicle Types
			function( callback ){
				dclass._select( '*', 'tbl_vehicle_type', " AND v_type = '"+vehicle_type+"' AND e_status = 'active' ", function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, '', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_records', {} );
					}
					else{
						newData = data[0];
						
						var temp = newData;
						if( temp.l_data.list_icon ){ temp.l_data.list_icon = gnrl._uploads( 'vehicle_type/'+temp.l_data.list_icon ); }
						if( temp.l_data.active_icon ){ temp.l_data.active_icon = gnrl._uploads( 'vehicle_type/'+temp.l_data.active_icon ); }
						if( temp.l_data.plotting_icon ){ temp.l_data.plotting_icon = gnrl._uploads( 'vehicle_type/'+temp.l_data.plotting_icon ); }
						newData = temp;
						
						// Based Selection
						newData.l_data.charges.city_wise_id = 0;
						newData.l_data.charges.area_wise_id = 0;
						newData.l_data.charges.vehicle_wise_id = 0;
						
						// Coupon Code
						newData.l_data.charges.promocode_id = 0;
						newData.l_data.charges.promocode_code = 0;
						newData.l_data.charges.promocode_code_discount = 0;
						newData.l_data.charges.promocode_code_discount_amount = 0;
						newData.l_data.charges.promocode_code_discount_upto = 0;
						
						
						newData.subCharges = [];
						callback( null );
					}
				});
			},
			
			// Get City Wise Prices
			function( callback ){
				
				var _q = " SELECT ";
				_q += " * ";
				_q += " FROM ";
				_q += " tbl_vehicle_fairs ";
				_q += " WHERE i_city_id IN ( ";
					_q += " SELECT c.id FROM tbl_city c WHERE lower( v_name ) = '"+( city.toLowerCase() )+"' LIMIT 1 ";
				_q += "  ) ";
				_q += "  AND v_type = 'city_wise' ";
				
				dclass._query( _q, function( status, data ){
					if( !status ){
						callback( null );
					}
					else if( !data.length ){
						callback( null );
					}
					else{
						if( data.length ){
							var temp = data[0];
							for( var k in temp.l_data.charges ){
								if( temp.l_data.charges[k] ){
									newData.l_data.charges[k] = temp.l_data.charges[k];
								}
							}
							
							newData.l_data.charges.city_wise_id = temp.id;
						
						}
						callback( null );
					}
				});
				
			},
			
			// Get Area Wise Prices
			function( callback ){
				
				var _q = " SELECT ";
				_q += " * ";
				_q += " FROM ";
				_q += " ( SELECT *, ("+gnrl._distQuery( latitude, longitude, "( l_data->'geo'->>'latitude' )::double precision", "( l_data->'geo'->>'longitude' )::double precision" )+") AS distance, l_data->'hours'->>'start_hour' AS start_hour, l_data->'hours'->>'end_hour' AS end_hour FROM tbl_vehicle_fairs ) AS a ";
				_q += " WHERE i_city_id IN ( ";
					_q += " SELECT c.id FROM tbl_city c WHERE lower( v_name ) = '"+( city.toLowerCase() )+"' LIMIT 1 ";
				_q += "  ) ";
				_q += "  AND v_type = 'area_wise' ";
				_q += " AND ( l_data->'days' )::jsonb ? '"+currDay+"' ";
				_q += " AND ( l_data->'dates'->>'start_date' <= '"+currDate+"' AND l_data->'dates'->>'end_date' >= '"+currDate+"' ) ";
				_q += " AND distance < ( l_data->'geo'->>'cover_area' )::numeric ";
				_q += " ORDER BY ";
				_q += " distance ASC ";
				_q += " , l_data->'geo'->'cover_area' ASC ";
				
				// newData._q = _q;
				
				dclass._query( _q, function( status, data ){
					if( !status ){
						callback( null );
					}
					else if( !data.length ){
						callback( null );
					}
					else{
						
						for( var k in data ){
							
							var temp = data[k];
							var isPush = 1;
							temp.start_hour = gnrl._timestamp( currDate+' '+temp.start_hour );
							temp.end_hour = gnrl._timestamp( currDate+' '+temp.end_hour );
							if( !( temp.start_hour < currTimeStamp && currTimeStamp < temp.end_hour ) ){
								isPush = 0;
							}
							if( isPush ){
								newData.subCharges.push( temp );
							}
						}
						
						
						if( newData.subCharges.length ){
							subCharges = newData.subCharges[0];
							for( var k in subCharges.l_data.charges ){
								if( subCharges.l_data.charges[k] ){
									newData.l_data.charges[k] = subCharges.l_data.charges[k];
								}
							}
							newData.l_data.charges.area_wise_id = subCharges.id;
						}
						
						delete newData.subCharges;
						
						callback( null );
					}
				});
				
			},
			
			
		], function( error, results ){
			
			gnrl._api_response( res, 1, '', newData );
			
		});
		
	}
	
};

module.exports = currentApi;
