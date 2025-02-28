/**
 * @package		CGNewFlag content plugin
 * @author		ConseilGouz
 * @copyright	Copyright (C) 2024 ConseilGouz. All rights reserved.
 * @license		GNU/GPL v2; see LICENSE.php
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
