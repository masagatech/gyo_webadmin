var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	
	var _q = "SELECT ";
	_q += " a.v_name, ";
	_q += " a.v_image, ";
	_q += " a.l_data->>'rate' AS rate, ";
	_q += " b.v_type, ";
	_q += " b.v_vehicle_number ";
	_q += " FROM tbl_user a ";
	_q += " LEFT JOIN tbl_vehicle b ON a.id = b.i_driver_id ";
	_q += " WHERE a.v_role = 'driver' ";
	_q += " AND a.l_data->>'rate' != '' ";
	// _q += " AND id IN ("+data[0].l_value+") ";
	_q += " ORDER BY a.l_data->>'rate_total', a.l_data->>'rate' DESC LIMIT 10";
	
	
	dclass._query( _q, function( sarathi_status, sarathi_data ){
		if( !sarathi_status ){
			gnrl._api_response( res, 0, '', {} );
		}
		else if( !sarathi_data.length ){
			gnrl._api_response( res, 0, 'err_no_records', {} );
		}
		else{
			for( var i = 0; i < sarathi_data.length; i++ ){
				var temp = sarathi_data[i];
				temp.v_image = gnrl._isNull( temp.v_image ) ? '' : gnrl._uploads( 'users/'+temp.v_image );
				sarathi_data[i] = temp;
			}
			gnrl._api_response( res, 1, 'succ_record_found', sarathi_data );
		}
	});
	
	
};

module.exports = currentApi;
