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
	var _sort_by  = ['today', 'weekly', 'monthly'];
	
	var login_id   = gnrl._is_undf( params.login_id ).trim();
	var sort_by    = gnrl._is_undf( params.sort_by ).trim();
	
	if( !sort_by ){ _status = 0; _message = 'err_req_sort_by'; }
	if( _status && _sort_by.indexOf(sort_by) == -1 ){ _status = 0; _message = 'err_req_sort_by'; }
	
	if( _status ){	

		var now_date = gnrl._db_date();

		var _q = " SELECT tbl_ride.*";
		_q += " ,(SELECT tbl_user.v_name FROM tbl_user AS tbl_user WHERE tbl_user.id = tbl_ride.i_user_id) AS user_v_name";
		_q += " ,(SELECT tbl_user.v_image FROM tbl_user AS tbl_user WHERE tbl_user.id = tbl_ride.i_user_id) AS user_v_image";
		_q += " ,(SELECT tbl_ride_rate.i_rate FROM tbl_ride_rate AS tbl_ride_rate WHERE tbl_ride_rate.i_ride_id = tbl_ride.id) AS ride_i_rate";
		_q += " ,(SELECT tbl_ride_rate.l_comment FROM tbl_ride_rate AS tbl_ride_rate WHERE tbl_ride_rate.i_ride_id = tbl_ride.id) AS ride_l_comment";
		_q += " FROM tbl_ride AS tbl_ride ";
		_q += " WHERE true ";
		_q += " AND tbl_ride.e_status IN('cancel','scheduled','complete')";
		_q += " AND tbl_ride.i_driver_id = '"+login_id+"' ";

		if( sort_by == 'today' ){
			var today = now_date.D+'-'+now_date.M+'-'+now_date.Y;
			_q += " AND to_char(tbl_ride.d_start, 'DD-MM-YYYY') = '"+today+"'";
		}
		if( sort_by == 'weekly' ){
			var FD = now_date.WEEK_FIRST_DATE.D+'-'+now_date.WEEK_FIRST_DATE.M+'-'+now_date.WEEK_FIRST_DATE.Y;
			var LD = now_date.WEEK_LAST_DATE.D+'-'+now_date.WEEK_LAST_DATE.M+'-'+now_date.WEEK_LAST_DATE.Y;
			_q += " AND to_char(tbl_ride.d_start, 'DD-MM-YYYY') >= '"+FD+"' AND to_char(tbl_ride.d_start, 'DD-MM-YYYY') <= '"+LD+"'";
		}
		if( sort_by == 'monthly' ){
			var monthly = now_date.M;
			_q += " AND to_char(tbl_ride.d_start, 'MM') = '"+monthly+"'";
		}

		//gnrl._api_response( res, 1, '', _q );
		dclass._query( _q, function( status, data ){
			if( status && !data.length ){
				gnrl._api_response( res, 0, 'err_no_records', _response );
			}
			else{

				for (var i = 0; i < data.length; i++) {

					if( gnrl._isNull(data[i].ride_i_rate) ){
						data[i].ride_i_rate = 0;
					}
					if( gnrl._isNull(data[i].ride_l_comment) ){
						data[i].ride_l_comment = "";
					}
					if( gnrl._isNull(data[i].user_v_name) ){
						data[i].user_v_name = "";
					}
					if( gnrl._isNull(data[i].user_v_image) ){
						data[i].user_v_image = "";
					}
					else{
						data[i].user_v_image = gnrl._uploads( 'users/'+data[i].user_v_image );
					}

					if( gnrl._isNull(data[i].l_data.trip_time) ){
						data[i].l_data.trip_time = "";
					}

					data[i].d_time = gnrl._timestamp(data[i].d_time);
					data[i].d_start = gnrl._timestamp(data[i].d_start);
					data[i].d_end = gnrl._timestamp(data[i].d_end);
					
					data[i].l_data.ride_time = gnrl._timestamp(data[i].l_data.ride_time);
					data[i].l_data.time_added = gnrl._timestamp(data[i].l_data.time_added);
				}

				gnrl._api_response( res, 1, '', data );
			}
		});
		
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
