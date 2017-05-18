var express = require('express');
var async = require('async');


var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_sitesetting';
	
	return {
		
		get : function( key, cb ){
			var _self = this;
			dclass._select( '*', table, " AND v_key = '"+key+"' ", function( status, data ){
				if( !status ){ cb( '' ); }
				else if( !data.length ){ cb( '' ); }
				else{ cb( data[0].l_value ); }
			});
		},
		
		getMulti : function( keyArr, cb ){
			
			var _self = this;
			
			var obj = {};
			var wh = [];
			for( var k in keyArr ){
				obj[ keyArr[k] ] = '';
				wh.push(" v_key = '"+keyArr[k]+"' ");
			}
			wh = wh.join(" OR ");
			
			dclass._select( '*', table, " AND ("+wh+") ", function( status, data ){
				if( status && data.length ){
					for( var k in data ){
						obj[data[k].v_key] = data[k].l_value;
					}
				}
				cb( obj );
			});
		},
		
	}
};

module.exports = currClass;
