var express = require('express');
var validator = require('validator');
var md5 = require('md5');
var _config = require('../../config/config.js');

var currentApi = function(req, res, next) {
    var classes = req.app.get('classes');
    classes.gnrl._extract(classes, this); // Extract Classes

    var _p = gnrl._p;
    var params = gnrl._frm_data(req);
    var _lang = gnrl._getLang(params);

    var _status = 1;
    var _message = '';
    var _response = {};

    var login_id = gnrl._is_undf(params.login_id);
    var flag = gnrl._is_undf(params.flag);

    if (_status) {
        dclass._select('*', 'tbl_user', " AND v_role = 'user' AND id = '" + login_id + "' ", function(status, data) {
            if (!status) {
                gnrl._api_response(res, 0, '', {});
            } else if (!data.length) {
                gnrl._api_response(res, 0, 'err_msg_no_account', {});
            } else {
                if (data[0].v_image) {
                    if (flag == "web") {
                        data[0].v_image = _config._cloudinary_image_url + data[0].v_image;
                    } else {
                        data[0].v_image = gnrl._uploads('users/' + data[0].v_image);
                    }
                }

                delete data[0].v_password;
                gnrl._api_response(res, 1, '', data[0]);
            }
        });
    } else {
        gnrl._api_response(res, 0, _message, {});
    }
};

module.exports = currentApi;