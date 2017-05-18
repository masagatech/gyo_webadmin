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
	if( !city.trim() ){ _status = 0; _message = 'err_req_city'; }
	
	var newData = {};
	
	async.series([
		
		// Get Vehicle Types
		function( callback ){
			dclass._select( '*', 'tbl_vehicle_type', " AND e_status = 'active' ORDER BY id ", function( status, data ){ 
				if( !status ){
					gnrl._api_response( res, 0, '', {} );
				}
				else if( !data.length ){
					gnrl._api_response( res, 0, 'err_no_records', {} );
				}
				else{
					for( var k in data ){
						var temp = data[k];
						if( temp.l_data.list_icon ){ temp.l_data.list_icon = gnrl._uploads( 'vehicle_type/'+temp.l_data.list_icon ); }
						if( temp.l_data.active_icon ){ temp.l_data.active_icon = gnrl._uploads( 'vehicle_type/'+temp.l_data.active_icon ); }
						if( temp.l_data.plotting_icon ){ temp.l_data.plotting_icon = gnrl._uploads( 'vehicle_type/'+temp.l_data.plotting_icon ); }
						
						delete temp.l_data.charges;
						
						newData[temp.id] = temp;
					}
					callback( null );
				}
			});
		},
		
	], function( error, results ){
		gnrl._api_response( res, 1, '', newData );
	});
	
};

module.exports = currentApi;
