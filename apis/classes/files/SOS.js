var express = require('express');
var async = require('async');


var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_sos';
	
	return {
		
		get : function( param, cb ){
			var _self = this;
			dclass._select( '*', table, " AND id = '"+param+"' ", function( status, data ){
				cb( status, data );
			});
		},
		
		getByCityID : function( param, cb ){
			var _self = this;
			dclass._select( '*', table, " AND i_city_id = '"+param+"' ", function( status, data ){
				cb( status, data );
			});
		},
		
		
	}
};

module.exports = currClass;
