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
	
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var i_city_id = gnrl._is_undf( params.i_city_id ).trim();
	var i_vehicle_type_id = gnrl._is_undf( params.i_vehicle_type_id ).trim();
	
	if( !i_city_id ){ _status = 0; _message = 'err_req_city'; }
	if( _status && !i_vehicle_type_id ){ _status = 0; _message = 'err_req_vehicle_type'; }
	
	var newData = {};
	var isDay = 1;
	if( !_status ){
		gnrl._api_response( res, 0, _message, {} );
	}
	else{
	
		async.series([
			
			// Get Vehicle Types
			function( callback ){
				dclass._select( '*', 'tbl_vehicle_type', " AND i_delete = '0' AND id = '"+i_vehicle_type_id+"' AND e_status = 'active' ", function( status, data ){ 
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

						if( temp.l_data.plotting_icon ){ temp.l_data.plotting_icon = gnrl._uploads( 'vehicle_type/'+temp.l_data.plotting_icon ); }
						newData = temp;
						
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
				_q += " WHERE i_city_id = '"+i_city_id+"' ";
				_q += " AND i_delete = '0' AND v_type = 'city_wise' ";
				
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
						}
						callback( null );
					}
				});
			},
			
		], function( error, results ){
			newData.l_data.charges.surcharge = newData.l_data.charges.surcharge+'%';
			newData.l_data.charges.service_tax = newData.l_data.charges.service_tax+'%';
			gnrl._api_response( res, 1, '', newData );
			
		});
	}
};

module.exports = currentApi;
