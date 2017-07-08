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
	
	if( _status ){
		dclass._select( 'id, v_key, v_name', 'tbl_language', " AND i_delete = '0' AND e_status = 'active' ORDER BY v_name ", function( status, data ){ 
			if( status && !data.length ){
				gnrl._api_response( res, 0, 'err_no_records', {} );
			}
			else{
				gnrl._api_response( res, 1, '', data );
			}
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
