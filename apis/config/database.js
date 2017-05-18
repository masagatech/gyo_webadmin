var express = require('express');
var promise = require('bluebird');
var options = {
	promiseLib: promise
};
var pgp = require('pg-promise')(options);

var _config = require('./config.js');

var dclass = pgp( _config._db_conn );

module.exports = {
	
	// Fire Query
	_query : function( _q, _callback ){
		//console.log( _q );
		dclass.any( _q )
		.then( data => {
			return _callback( 1, data );
		})
		.catch( error => {
			return _callback( 0, error );
		});
	},
	
	// Select Query
	_select : function( _sel, _table, _wh, _callback ){
		var _q = "SELECT "+_sel+" FROM "+_table+" WHERE true "+_wh;
		//console.log(_q);
		dclass.any( _q )
		.then( data => {
			return _callback( 1, data );
		})
		.catch( error => {
			return _callback( 0, error );
		});
	},
	
	// Insert Query
	_insert : function( _table, _ins, _callback ){
		
		var keyArr = [];
		var valArr = [];
		var numArr = [];
		
		var i = 1;
		for( var key in _ins ){
			keyArr.push( key );
			numArr.push( '$'+i );
			valArr.push( _ins[ key ] );
			i++;
		}
		
		var _q = "INSERT INTO "+_table+" ("+keyArr.join(',')+") VALUES ("+numArr.join(',')+") RETURNING id";
		//console.log(_q);
		dclass.one( _q, valArr )
		.then( data => { 
			return _callback( 1, data ); 
		})
		.catch( error => { 
			return _callback( 0, "ERROR: "+( error.message || error ) ); 
		});
		
	},
	
	// Update Query
	_update : function( _table, _ins, _wh, _callback ){
		var setArr = [];
		for( var key in _ins ){
			setArr.push( key+" = '"+_ins[ key ]+"' " );			
		}
		var _q = "UPDATE "+_table+" SET "+setArr.join(',')+" WHERE true "+( _wh ? _wh : " AND 0 " );
		
		dclass.none( _q )
		.then( data => { 
			return _callback( 1, data ); 
		})
		.catch( error => { 
			return _callback( 0, "ERROR: "+( error.message || error ) ); 
		});
	},
	
	// Update Json Query
	_updateJsonb : function( _table, _ins, _wh, _callback ){
		var setArr = [];
		for( var key in _ins ){
			setArr.push( _ins[ key ] );
		}
		var _q = "UPDATE "+_table+" SET "+setArr.join(',')+" WHERE true "+( _wh ? _wh : " AND 0 " );
		
		dclass.none( _q )
		.then( data => { 
			return _callback( 1, data ); 
		})
		.catch( error => { 
			return _callback( 0, "ERROR: "+( error.message || error ) ); 
		});
	},
	
	// Update Query
	_delete : function _insert( _table, _wh ){
		dclass.any( "DELETE FROM "+_table+" WHERE true "+( _wh ? _wh : " AND 0 " ) )
		.then( data => {
			return _callback( 1, data );
		})
		.catch( error => {
			return _callback( 0, error );
		});
	}
	
};