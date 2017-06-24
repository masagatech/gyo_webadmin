var express = require('express');
var fs = require('fs');
var async = require('async');

var dirUploads = __dirname + '/../public/';

var lables = require( dirUploads+'./translation.json' );
module.exports = lables;