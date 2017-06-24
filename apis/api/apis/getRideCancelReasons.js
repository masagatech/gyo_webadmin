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
	
	var v_type = gnrl._is_undf( params.v_type ).trim();
	if( !v_type ){ _status = 0; _message = 'err_req_type'; }
	
	if( _status ){
		dclass._select( '*', 'tbl_ride_cancel_reason', " AND i_delete = '0' AND v_type = '"+v_type+"' ORDER BY i_order ", function( status, data ){ 
			if( status && !data.length ){
				gnrl._api_response( res, 0, 'err_no_records', {} );
			}
			else{
				var lang_columns = [
					'j_title'
				];
				gnrl._api_response( res, 1, '', gnrl._getLangWiseData( data, _lang, lang_columns ) );
			}
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
