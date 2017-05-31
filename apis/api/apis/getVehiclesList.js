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
	
	var v_type = gnrl._is_undf( params.v_type ).trim();
	var l_latitude = gnrl._is_undf( params.l_latitude, 0 ).trim();
	var l_longitude = gnrl._is_undf( params.l_longitude, 0 ).trim();
	
	if( !v_type ){ _status = 0; _message = 'err_req_vehicle_type'; }
	if( _status && !l_latitude ){ _status = 0; _message = 'err_req_latitude'; } // 
	if( _status && !l_longitude ){ _status = 0; _message = 'err_req_longitude'; } // 
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else {
		
		var _vehicle_type = {};
		var _vehicle_list = [];
		
		async.series([
		
			// Get Vehicle Type
			function( callback ){
				dclass._select( '*', 'tbl_vehicle_type', " AND v_type = '"+v_type+"' AND e_status = 'active' ", function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, '', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_vehicles', {} );
					}
					else{
						_vehicle_type = data[0];
						callback( null );
					}
				});
			},
			
			// Find Nearest Vehicles
			function( callback ){
				
				var radius = _vehicle_type.l_data.other.vehicle_list_radious ? _vehicle_type.l_data.other.vehicle_list_radious : 20;
				
				var _q = "";
				_q += " SELECT ";
				_q += " * ";
				_q += " FROM ";
				_q += " ( ";
					_q += " SELECT ";
					_q += " a.id, a.i_driver_id, a.v_type, b.l_latitude, b.l_longitude ";
					_q += ", "+gnrl._distQuery( l_latitude, l_longitude, "b.l_latitude::double precision", "b.l_longitude::double precision" )+" AS distance"
					_q += " FROM ";
					_q += " tbl_vehicle a ";
					_q += " LEFT JOIN tbl_user b ON b.id = a.i_driver_id ";
					_q += " WHERE true ";
					_q += " AND a.v_type = '"+v_type+"' ";
					_q += " AND b.v_role = 'driver' ";
					_q += " AND b.e_status = 'active' ";
					_q += " AND b.is_onduty = '1' ";
					_q += " AND b.is_onride = '0' ";
					_q += " AND b.is_buzzed = '0' ";
					
				_q += " ) AS sub ";
				_q += " WHERE true ";
				_q += " AND distance <= "+radius+" ";
				_q += " ORDER BY ";
				_q += " distance ASC ";
				
				_response._q = _q;
				
				
				dclass._query( _q, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, '', [] );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_vehicles', [] );
					}
					else{
						_vehicle_list = data;
						callback( null );
					}
				});
				
				
			},
			
		], 
		function( error, results ){
			
			gnrl._api_response( res, 1, '', _vehicle_list );
			
		});
		
	}
	
};

module.exports = currentApi;
