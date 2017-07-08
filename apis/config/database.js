var express = require('express');
var promise = require('bluebird');
var options = {
	promiseLib: promise
};
var pgp = require('pg-promise')(options);

var _config = require('./config.js');

var dclass = pgp( _config._db_conn );

module.exports = {
	
	/*
	_isJson : function( data ){
		try{ JSON.parse( data ); } catch( e ){ return false; }
		return true;
	},
	*/
	
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
	
	// Insert Query
	_insertMulti : function( _insArr, _callback ){
		
		var keyArr = [];
		var valArr = [];
		var numArr = [];
		
		// _insArr
		
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
			/*
			if( this._isJson( _ins[ key ] ) ){
				setArr.push( key+" = COALESCE( "+key+", '{}' ) || '"+_ins[ key ]+"'" );
			}
			else{
				setArr.push( key+" = '"+_ins[ key ]+"'" );
			}
			*/
			setArr.push( key+" = '"+_ins[ key ]+"'" );
		}
		var _q = "UPDATE "+_table+" SET "+setArr.join(' , ')+" WHERE true "+( _wh ? _wh : " AND 0 " );
		
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
		
		// UPDATE tbl_user SET l_data = COALESCE( l_data, '{}' ) || '{"is_otp_verified":"1"}' WHERE id = '15' 
		// UPDATE tbl_ride SET l_data = jsonb_set( l_data, '{ charges, promocode_code_discount_amount22 }', '5', true) WHERE id = 61;
		// Delete Key = // " l_data = l_data #- '{ charges, promocode_code_discount_amount22 }' ",
		// " l_data = jsonb_set( l_data, '{charges}', l_data->'charges' || '"+gnrl._json_encode( tempJson )+"' ) ",
		
		//console.log( _q );
		
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