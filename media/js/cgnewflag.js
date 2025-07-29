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
    for (var o=0;o < cgnewflag_options.params.length;o++) {
        let cgparams = cgnewflag_options.params[o];
        if (cgparams.posflg != 'header') { // before/after title
            headers = document.querySelectorAll('h2');
            for (var i=0; i< headers.length;i++) {
                inner = headers[i].innerHTML;
                if (inner.indexOf('<'+cgparams.tag+'>') >= 0) {
                    headers[i].innerHTML = inner.replace('<'+cgparams.tag+'>',cgparams.newstr);
                }
                if (inner.indexOf('&lt;'+cgparams.tag+'&gt;') >= 0) {
                    headers[i].innerHTML = inner.replace('&lt;'+cgparams.tag+'&gt;',cgparams.newstr);
                }
            }
            if (!headers.length) { // no H2, check .list-title (tags view)
                headers = document.querySelectorAll('.list-title');
                for (var i=0; i< headers.length;i++) {
                    inner = headers[i].innerHTML;
                    if (inner.indexOf('<'+cgparams.tag+'>') >= 0) {
                        headers[i].innerHTML = inner.replace('<'+cgparams.tag+'>',cgparams.newstr);
                    }
                    if (inner.indexOf('&lt;'+cgparams.tag+'&gt;') >= 0) {
                        headers[i].innerHTML = inner.replace('&lt;'+cgparams.tag+'&gt;',cgparams.newstr);
                    }
                }
            }
            if (!headers.length) {
                header = document.querySelector('h1'); // suppose only one h1 tag
                if (header) {
                    if ((header.innerHTML.indexOf('<'+cgparams.tag+'>') >= 0) ||
                        (document.title.indexOf('<'+cgparams.tag+'>') >= 0)){
                        if (cgparams.posflg == 'before') {
                            header.innerHTML = cgparams.newstr + header.innerHTML;
                        } else {
                            header.innerHTML += cgparams.newstr;
                        }
                    }
                }
            }
            document.title = document.title.replace('<'+cgparams.tag+'>','');
        }
    }
})
