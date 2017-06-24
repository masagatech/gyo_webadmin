var express = require('express');
var validator = require('validator');
var md5 = require('md5');
var async = require('async');



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
	
	if( !_status ){
		
		gnrl._api_response( res, 0, _message );
		
	}
	else{
		
		async.series([
				
			function( callback ){
				
				var _q = " SELECT ";
				_q += " id, j_title, j_text, i_textbox ";
				_q += " FROM tbl_faq WHERE e_status = 'active' AND i_delete = '0' ORDER BY i_order ";
				
				dclass._query( _q, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error' );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_records' );
					}
					else{
						for( var k in data ){
							data[k].j_title = gnrl._getLangField( data[k].j_title, _lang );
							data[k].j_text = gnrl._getLangField( data[k].j_text, _lang );
						}
						gnrl._api_response( res, 1, 'succ_record_found', data );
					}
				});
			},
			
		], function( error, results ){
			
			gnrl._api_response( res, 0, 'err_no_records' );
			
		});
		
	}
	
};

module.exports = currentApi;
