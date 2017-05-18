var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



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
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		dclass._select( '*', 'tbl_ride_charges', " AND i_ride_id = '"+i_ride_id+"' ORDER BY id ASC", function( status, data ){ 
			if( !status ){
				gnrl._api_response( res, 0, 'error', {} );
			}
			else{
				for( var i = 0; i < data.length; i++ ){
					data[i].display_charge_type = Ride.getAllChargeTypes( data[i]['v_charge_type'] );
				}
				gnrl._api_response( res, 1, '', data );
			}
		});
	}
};

module.exports = currentApi;