var express = require('express');
var async = require('async');


var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_city';
	
	return {
		
		get : function( param, cb ){
			var _self = this;
			dclass._select( '*', table, " AND id = '"+param+"' ", function( status, data ){
				cb( status, data );
			});
		},
		
		getByName : function( param, cb ){
			var _self = this;
			dclass._select( '*', table, " AND LOWER( v_name ) LIKE '%"+( param.toLowerCase() )+"%' ", function( status, data ){
				cb( status, data );
			});
		},
		
		getActiveList : function( cb ){
			var _self = this;
			dclass._select( '*', table, " AND e_status = 'active' ORDER BY v_name ", function( status, data ){
				cb( status, data );
			});
		},
		
	}
};

module.exports = currClass;
