/**
 * @package		CGNewFlag content plugin
 * @author		ConseilGouz
 * @copyright	Copyright (C) 2025 ConseilGouz. All rights reserved.
 * @license		GNU/GPL v3; see LICENSE.php
 **/
document.addEventListener('DOMContentLoaded', function() {
	if (typeof Joomla === 'undefined' || typeof Joomla.getOptions === 'undefined') {
		console.log('CG Memo : Joomla /Joomla.getOptions  undefined');
	} else {
		 cgnewflag_options = Joomla.getOptions('plg_content_cgnewflag');
	}
	if (typeof cgnewflag_options === 'undefined' ) {return false}
	
	bg = cgnewflag_options.bg;
	if (bg.startsWith('-')) bg = 'var('+bg+')';
	font = cgnewflag_options.font;
	if (font.startsWith('-')) font = 'var('+font+')';
	if (!bg) return false;
	if (cgnewflag_options.posflg != 'header') { // before/after title
		headers = document.querySelectorAll('h2');
		for (var i=0; i< headers.length;i++) {
			inner = headers[i].innerHTML;
			if (inner.indexOf('<cgnewflag>') >= 0) {
				headers[i].innerHTML = inner.replace('<cgnewflag>',cgnewflag_options.newstr);
			}
			if (inner.indexOf('&lt;cgnewflag&gt;') >= 0) {
				headers[i].innerHTML = inner.replace('&lt;cgnewflag&gt;',cgnewflag_options.newstr);
			}
		}
		if (!headers.length) { // no H2, check .list-title (tags view)
			headers = document.querySelectorAll('.list-title');
			for (var i=0; i< headers.length;i++) {
				inner = headers[i].innerHTML;
				if (inner.indexOf('<cgnewflag>') >= 0) {
					headers[i].innerHTML = inner.replace('<cgnewflag>',cgnewflag_options.newstr);
				}
				if (inner.indexOf('&lt;cgnewflag&gt;') >= 0) {
					headers[i].innerHTML = inner.replace('&lt;cgnewflag&gt;',cgnewflag_options.newstr);
				}
			}
		}			
		if (!headers.length) {
			header = document.querySelector('h1'); // suppose only one h1 tag
			if (header) {
				if (cgnewflag_options.posflg == 'before') {
					header.innerHTML = cgnewflag_options.newstr + header.innerHTML;
				} else {
					header.innerHTML += cgnewflag_options.newstr;
				}
			}
		}
		document.title = document.title.replace('<cgnewflag>','');
	}
	badges = document.querySelectorAll('.cgnewflag_badge');
	for (var i=0; i< badges.length;i++) {
		badges[i].style.backgroundColor = bg;
		badges[i].style.color = font;
		badges[i].style.fontSize = cgnewflag_options.fontsize+'em';
	}
	icones = document.querySelectorAll('.cgnewflag_icon');
	for (var i=0; i< icones.length;i++) {
		icones[i].style.color = bg;
		icones[i].style.fontSize = cgnewflag_options.fontsize+'em';
	}


})
