/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	/*config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
		{ name: 'colors' },

		
	];
*/

	config.toolbar = [
					  ['Source'], 
		              	  ['Save','NewPage','Preview'], 
					  ['Cut','Copy','Paste'],
					  ['Print'],
					  ['Undo','Redo'],
					  ['Find','Replace'],
					  ['Select All','Remove Format'],
					  ['Checkbox', 'Radio'],
					  ['Bold','Italic','Underline','Strike'],
					  ['NumberedList', 'BulletedList'],
					  ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
					  ['Link','Unlink'],
					  ['Format','Font','FontSize'],
					  ['TextColor','BGColor']
					 ];


};
