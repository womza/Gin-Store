/*
* 2014 Jorge Vargas
*
* NOTICE OF LICENSE
*
* This source file is subject to the End User License Agreement (EULA)
*
* See attachmente file LICENSE
*
* @author    Jorge Vargas <jorgevargaslarrota@hotmail.com>
* @copyright 2007-2015 Jorge Vargas
* @license   End User License Agreement (EULA)
* @package   sociallogin
* @version   1.0
*/

"use strict";

function deleteSocial(url, msj) {
	var r = confirm(msj);
	var w = windowOptions(640, 640);
	if (r === true) {
		window.open(url, '_self', w);
	}
	return;
}

function connectSocial(url, msj, open_in) {
	var r = confirm(msj);
	var w = windowOptions(640, 640);
	if (r === true) {
		window.open(url, open_in, w);
	}
	return;
}

function windowOptions(h, w) {
	var l = (screen.width/2)-(w/2);
	var t = (screen.height/2)-(h/2);
	var output = '\'menubar=no, status=no, copyhistory=no, width='+w+', height='+h+', top='+t+', left='+l+'\'';
	return output;
}

function createButton(item_name, item_link, size, button, open_in, sign_in) {
	var txt = '';
	var w = windowOptions(640, 640, '_blank');
	var sz = '';
	var bt = '';

	if (size != 'st') { sz = 'btn-'+size+' '; }
	if (button === '1') { bt += '-icon'; }

	txt += '<button class="btn btn-social'+bt+' '+sz+'btn-';
	if (item_name == 'google') {
		txt +=	'google-plus';
	} else {
		txt += item_name;
	}
	txt += '" onclick="window.open(\'';
	txt += item_link;
	txt += '\', \''+open_in+'\', '+w+')" title="';
	txt += item_name;
	txt += '"><i class="fa fa-';
	if (item_name == 'microsoft') {
		txt += 	'windows';
	} else if (item_name == 'microsoft') {
		txt += 'google-plus';
	} else {
		txt += item_name;
	}
	txt += '"></i> ';
	if (button === '0') {
		txt += sign_in+' '+item_name.capitalize();
	}
	txt += '</button> ';

	return txt;
}

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
};