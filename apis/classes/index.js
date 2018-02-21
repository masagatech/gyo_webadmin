var express = require('express');
var fs = require('fs');

var _config = require('../config/config.js');
var _lables = require('../config/lable-translation.js');
var gnrl = require('../config/general.class.js');
var dclass = require('../config/database.js');

var classes = {
    _lables: _lables,
    _config: _config,
    gnrl: gnrl,
    dclass: dclass,
};

// Read Classes Directory
fs.readdir('./classes/files/', function(err, files) {
    if (err == null) {
        for (var k in files) {
            var singleFile = files[k].split('.js');

            var baseClasses = {
                _config: _config,
                _lables: _lables,
                gnrl: gnrl,
                dclass: dclass,
            };
            fs.readdir('./classes/files/', function(err_1, files_1) {
                if (err_1 == null) {
                    for (var k_1 in files_1) {
                        var singleFile_1 = files_1[k_1].split('.js');
                        if (singleFile_1[0] != singleFile[0]) {
                            baseClasses[singleFile_1[0]] = require('./files/' + files_1[k_1])(baseClasses);
                        }
                    }
                }
            });

            classes[singleFile[0]] = require('./files/' + files[k])(baseClasses);
        }
    }
});


module.exports = classes;