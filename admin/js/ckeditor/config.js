/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E'; 
	
	// Link dialog, "Browse Server" button
		config.filebrowserBrowseUrl = 'js/ckeditor/plugins/ckfinder/ckfinder.html';
		// Image dialog, "Browse Server" button
		config.filebrowserImageBrowseUrl = 'js/ckeditor/plugins/ckfinder/ckfinder.html?type=Images';
		// Flash dialog, "Browse Server" button
		config.filebrowserFlashBrowseUrl = 'js/ckeditor/plugins/ckfinder/ckfinder.html?type=Flash';
		// Upload tab in the Link dialog
		config.filebrowserUploadUrl = 'js/ckeditor/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
		// Upload tab in the Image dialog
		config.filebrowserImageUploadUrl = 'js/ckeditor/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
		// Upload tab in the Flash dialog
		config.filebrowserFlashUploadUrl = 'js/ckeditor/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
		
		config.contentsCss = ['../css/skel-noscript.css'];
		
		config.allowedContent= true;
};
