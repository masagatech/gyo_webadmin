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
	
	var v_type = gnrl._is_undf( params.v_type );
	var l_latitude = gnrl._is_undf( params.l_latitude, 0 );
	var l_longitude = gnrl._is_undf( params.l_longitude, 0 );
	
	if( !v_type ){ _status = 0; _message = 'err_req_vehicle_type'; }
	if( _status && !l_latitude ){ _status = 0; _message = 'err_req_latitude'; } // 
	if( _status && !l_longitude ){ _status = 0; _message = 'err_req_longitude'; } // 
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else {
		
		var _vehicle_list = [];
		var radius = 0;
		
		async.series([
		
			// Get Vehicle Type
			function( callback ){
				var _selection = " COALESCE( ( l_data->'other'->>'vehicle_list_radious' )::numeric, 0 ) AS radius ";
				dclass._select( _selection, 'tbl_vehicle_type', " AND i_delete = '0' AND v_type = '"+v_type+"' AND e_status = 'active' ", function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, '', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_vehicles', {} );
					}
					else{
						radius = data[0].radius ? data[0].radius : 20;
						callback( null );
					}
				});
			},
			
			// Find Nearest Vehicles
			function( callback ){
				
				var _q = "";
				_q += " SELECT ";
				_q += " * ";
				_q += " FROM ";
				_q += " ( ";
					_q += " SELECT ";
					_q += " b.id, b.i_driver_id, b.v_type, c.l_latitude, c.l_longitude ";
					_q += ", "+gnrl._distQuery( l_latitude, l_longitude, "c.l_latitude::double precision", "c.l_longitude::double precision" )+" AS distance"
					_q += " FROM ";
					_q += " tbl_vehicle b ";
					_q += " LEFT JOIN tbl_user c ON c.id = b.i_driver_id ";
					_q += " WHERE true ";
					_q += " AND b.v_type = '"+v_type+"' ";
					_q += " AND c.v_role = 'driver' ";
					_q += " AND c.e_status = 'active' ";
					_q += " AND c.is_onduty = '1' ";
					_q += " AND c.is_onride = '0' ";
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
