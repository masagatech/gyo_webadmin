var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');

var currentApi = function( req, res, next ){
	
	var dclass 	= req.app.get('Database');
	var gnrl 	= req.app.get('gnrl');
	var _p 		= req.app.get('_p');
	
	
	var params 	= gnrl._frm_data( req );
	var _lang 	= gnrl._getLang( params, req.app.get('_lang') );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var v_type = gnrl._is_undf( params.v_type ).trim();
	var l_latitude = gnrl._is_undf( params.l_latitude ).trim();
	var l_longitude = gnrl._is_undf( params.l_longitude ).trim();
	var radius = gnrl._is_undf( params.radius ).trim();
	var distance_in = gnrl._is_undf( params.distance_in ).trim();
	
	if( !v_type ){ _status = 0; _message = gnrl._lbl( 'err_req_vehicle_type', _lang ); }
	if( _status && !l_latitude ){ _status = 0; _message = gnrl._lbl( 'err_req_latitude', _lang ); } // 
	if( _status && !l_longitude ){ _status = 0; _message = gnrl._lbl( 'err_req_longitude', _lang ); } // 
	
	if( _status ){	

		var miles = 3959;
		var kilometers = 6371;
		var distance = kilometers;
		if( distance_in == "m" ){ distance = miles; }
		else if( distance_in == "km" ){ distance = kilometers; }
		if( !radius ){ radius = 20; }
		
		dclass._query( "SELECT * FROM ( SELECT id,i_driver_id,v_type,l_latitude,l_longitude,CONCAT(l_latitude,',',l_longitude) AS latlong, ("+distance+" * acos( cos( radians("+l_latitude+") ) * cos( radians( tbl_vehicle.l_latitude ) ) * cos( radians( tbl_vehicle.l_longitude ) - radians("+l_longitude+") ) + sin( radians("+l_latitude+") ) * sin( radians( tbl_vehicle.l_latitude ) ) ) ) AS distance FROM ( SELECT * FROM tbl_vehicle ) AS tbl_vehicle WHERE true AND tbl_vehicle.v_type = '"+v_type+"' AND e_status = 'active' ) SUB WHERE distance <= "+radius+" ORDER BY distance ASC", function( status, data ){
			if( status && !data.length ){
				gnrl._api_response( res, 0, 'No vehicles found.', _response );
			}
			else{
				gnrl._api_response( res, 1, '', data );
			}
		});
		
	}
	else{
		gnrl._api_response( res, 0, _message, {}, 0 );
	}
};

module.exports = currentApi;
