var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');

var currentApi = function (req, res, next) {
	
    var dclass 	= req.app.get('Database');
	var gnrl 	= req.app.get('gnrl');
	var db 		= req.app.get('db');
	var _p 		= req.app.get('_p');
	
	
	var _status  	= 1;
	var _message 	= '';
	var _response 	= {};
	var params 		= gnrl._frm_data( req );
	var _lang 	= gnrl._getLang( params, req.app.get('_lang') );
	
	db.callProcedure("SELECT " + gnrl._db_schema("fun_user_login") + "($1,$2::json);", [ 'data', params ], function( data ){
		if( data.rows.length ){
			gnrl._api_response( res, 1, gnrl._lbl( 'succ_login_successfully', _lang ), { id : data.rows[0].id });
		}
		else{
			gnrl._api_response( res, 1, gnrl._lbl( 'err_invalid_email_or_phone', _lang ), { id : 0 });
		}
	}, function( err ){
		gnrl._api_response( res, 0, _message, {}, 0 );
	});
	
}
module.exports = currentApi;