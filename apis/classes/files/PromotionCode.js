var express = require('express');
var async = require('async');


var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_coupon_code';
	
	return {
		
		get : function( param, cb ){
			var _self = this;
			dclass._select( '*', table, " AND i_delete = '0' AND id = '"+param+"' ", function( status, data ){
				cb( status, data );
			});
		},
		
		getByCode : function( param, cb ){
			var _self = this;
			dclass._select( '*', table, " AND i_delete = '0' AND LOWER( v_code ) LIKE '%"+( param.toLowerCase() )+"%' ", function( status, data ){
				cb( status, data );
			});
		},
		
		getActiveList : function( cb ){
			var _self = this;
			dclass._select( '*', table, " AND i_delete = '0' AND e_status = 'active' ORDER BY v_name ", function( status, data ){
				cb( status, data );
			});
		},
		
	}
};

module.exports = currClass;
