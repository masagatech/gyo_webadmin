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
	
	var i_driver_id = gnrl._is_undf( params.i_driver_id ).trim();
	if( !i_driver_id ){ _status = 0; _message = 'err_req_driver_id'; }
	
	if( _status ){
		
		var _driver = {
			l_latitude : 0,
			l_longitude : 0,
			list_icon : '',
		
			plotting_icon : '',
		};
		
		async.series([
		
			function( callback ){
				dclass._select( 'l_latitude, l_longitude', 'tbl_user', " AND id = '"+i_driver_id+"'", function( status, driver ){ 
					if( !status ){
						gnrl._api_response( res, 0, _message );
					}
					else if( !driver.length ){
						gnrl._api_response( res, 0, 'err_no_driver', {} );
					}
					else{
						_driver = driver[0];
						callback( null );
					}
				});
			},
			
			function( callback ){
				dclass._select( '*', 'tbl_vehicle_type', " AND i_delete = '0' AND v_type = ( SELECT v_type FROM tbl_vehicle WHERE i_driver_id = '"+i_driver_id+"'  )", function( status, vehicle_type ){ 
					if( status && vehicle_type.length ){
						vehicle_type = vehicle_type[0];
						if( vehicle_type.l_data.list_icon ){ _driver.list_icon = gnrl._uploads( 'vehicle_type/'+vehicle_type.l_data.list_icon ); }

						if( vehicle_type.l_data.plotting_icon ){ _driver.plotting_icon = gnrl._uploads( 'vehicle_type/'+vehicle_type.l_data.plotting_icon ); }
						callback( null );
					}
					else{
						callback( null );
					}
				});
			},
			
		], 
		function( error, results ){
			gnrl._api_response( res, 1, '', _driver );
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
