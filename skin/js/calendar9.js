// calendar9.inc.php http://www.sakasama.com/dive/
function xpwiki_cal9_showResponse(orgRequest) {
	xpwiki_ajax_edit_var['func_post'] = '';
	var xmlRes = orgRequest.responseXML;
	if (xmlRes.getElementsByTagName('editform').length) {
		xpwiki_ajax_edit_var['func_post'] = xpwiki_cal9_showResponse;
		//Element.update($('xpwiki_cal9_editarea'), xmlRes.getElementsByTagName('editform')[0].firstChild.nodeValue);
		$('xpwiki_cal9_editarea').innerHTML = xmlRes.getElementsByTagName('editform')[0].firstChild.nodeValue;
		$('xpwiki_edit_textarea').style.height = '250px';
		Element.update($('xpwiki_cancel_form'), '<button id="c9cancel" onclick="return xpwiki_cal9_day_edit_close()">'+xpwiki_calender9_cancel+'</button>');
		wikihelper_initTexts($('xpwiki_cal9_editarea'));
		Element.hide($('xpwiki_cal9_loading_base'));
	} else if (xmlRes.getElementsByTagName('xpwiki').length) {
		//var objHead = document.getElementsByTagName('head').item(0);
		
		Element.update('xpwiki_cal9_editarea', '');

		var item = xmlRes.getElementsByTagName('xpwiki')[0];
		
		var str = item.getElementsByTagName('content')[0].firstChild.nodeValue;
		var mode = item.getElementsByTagName('mode')[0].firstChild.nodeValue;
		
		
		if (mode == 'read') {
			var ins;
			ins = document.createElement('div');
			Element.update(ins, item.getElementsByTagName('headPreTag')[0].firstChild.nodeValue);
			$('xpwiki_cal9_editarea').appendChild(ins);
			//new Insertion.Bottom(objHead, item.getElementsByTagName('headPreTag')[0].firstChild.nodeValue);

			ins = document.createElement('div');
			Element.update(ins, item.getElementsByTagName('headTag')[0].firstChild.nodeValue);
			$('xpwiki_cal9_editarea').appendChild(ins);
			//new Insertion.Bottom(objHead, item.getElementsByTagName('headTag')[0].firstChild.nodeValue);

			ins = document.createElement('div');
			Element.update(ins, item.getElementsByTagName('content')[0].firstChild.nodeValue);
			$('xpwiki_cal9_editarea').appendChild(ins);

			var close = document.createElement('input');
			close.type = 'button';
			close.value = 'Close';
			close.onclick = function() { xpwiki_cal9_day_edit_close(); }
			
			ins = document.createElement('form');
			ins.style.textAlign = 'center';
			ins.appendChild(close);
			
			$('xpwiki_cal9_editarea').appendChild(ins);
			wikihelper_initTexts($('xpwiki_cal9_editarea'));
			Element.hide($('xpwiki_cal9_loading_base'));
		} else if (mode == 'write') {
			xpwiki_cal9_thisreload();
		} else if (mode == 'preview'){
			xpwiki_ajax_edit_var['func_post'] = xpwiki_cal9_showResponse;
			//Element.update($('xpwiki_cal9_editarea'), str);
			$('xpwiki_cal9_editarea').innerHTML = str;
			$('xpwiki_edit_textarea').style.height = '250px';
			Element.update($('xpwiki_cancel_form'), '<button id="c9cancel" onclick="return xpwiki_cal9_day_edit_close()">'+xpwiki_calender9_cancel+'</button>');
			wikihelper_initTexts($('xpwiki_cal9_editarea'));
			Element.hide($('xpwiki_cal9_loading_base'));
		}
	}
}

function xpwiki_cal9_day_edit(id,mode,event) {

	if (!!event) {
		if (Prototype.Browser.IE) {
			event.cancelBubble = true;
			event.returnValue = false;
		} else {
			Event.stop(event);
		}
	}

	if (!mode) mode = 'edit';
	
	var windowTop;
	var windowLeft;
	var windowWidht;
	var windowHeight;
	
	
	windowTop = document.documentElement.scrollTop || document.body.scrollTop || 0;
	windowLeft = document.documentElement.scrollLeft || document.body.scrollLeft || 0;
	if(Prototype.Browser.IE) {
		windowWidth = document.body.scrollWidth || document.documentElement.scrollWidth || 0;
		windowHeight = document.body.scrollHeight || document.documentElement.scrollHeight || 0;
	}
	else {
		windowWidth = document.documentElement.scrollWidth || document.body.scrollWidth || 0;
		windowHeight = document.documentElement.scrollHeight || document.body.scrollHeight || 0;
	}

	xpwiki_ajax_edit_var['id'] = 'xpwiki_body';
	xpwiki_ajax_edit_var['html'] = $(xpwiki_ajax_edit_var['id']).innerHTML;
	
	// HTML BODY���֥������ȼ���
	var objBody = document.getElementsByTagName('body').item(0);
	
	// �ط�ȾƩ�����֥������Ⱥ���
	if (!$('xpwiki_cal9_popupback')) {
		var objBack = document.createElement('div');
		objBack.setAttribute('id', 'xpwiki_cal9_popupback');
		objBack.onclick = function() { xpwiki_cal9_day_edit_close(); }
		Element.setStyle(objBack, {'display': 'none'});
		Element.setStyle(objBack, {'position': 'absolute'});
		Element.setStyle(objBack, {'zIndex': 90});
		Element.setStyle(objBack, {'textAlign': 'center'});
		Element.setStyle(objBack, {'backgroundColor': 'black'});
		Element.setStyle(objBack, {'filter': 'alpha(opacity=50)'});		// IE
		Element.setStyle(objBack, {'MozOpacity': '0.5'});		// FF
		Element.setStyle(objBack, {'opacity': '0.5'});		// opera
		
		objBack.style.top = 0;
		objBack.style.left = 0;
		objBody.appendChild(objBack);
	} else {
		var objBack = $('xpwiki_cal9_popupback');
	}
	objBack.style.width = windowWidth + 'px';
	objBack.style.height = windowHeight + 'px';

	// ���ϥܥå������֥������Ⱥ���
	if (!$('xpwiki_cal9_popupmain')) {
		var objPopup = document.createElement('div');
		objPopup.setAttribute('id', 'xpwiki_cal9_popupmain');
	
		var insobj = document.createElement('div');
		insobj.setAttribute('id','xpwiki_cal9_editarea');
		objPopup.appendChild(insobj);

		insobj = document.createElement('div');
		insobj.setAttribute('id','xpwiki_cal9_loading_base');
		insobj.style.height = '100px';
		insobj.style.padding = '50px';
		insobj.style.textAlign = 'center';
		
		var objLoadingImage = document.createElement('img');
		objLoadingImage.setAttribute('src', wikihelper_root_url + '/skin/loader.php?src=loading.gif');
		insobj.appendChild(objLoadingImage);
		
		objPopup.appendChild(insobj);
	
		Element.setStyle(objPopup, {'display': 'none'});
		Element.setStyle(objPopup, {'position': 'absolute'});
		Element.setStyle(objPopup, {'zIndex': 100});
		Element.setStyle(objPopup, {'border': '2px black solid'});
		Element.setStyle(objPopup, {'backgroundColor': 'white'});
		Element.setStyle(objPopup, {'padding': '20px'});
		
		$('xpwiki_body').appendChild(objPopup);
		//objBody.appendChild(objPopup);
	} else {
		var objPopup = $('xpwiki_cal9_popupmain');
	}
	var popupH = ((window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight || 768) - 120);
	var popupW = ((window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth || 1024) - 300);
	objPopup.style.top = (windowTop + 20) + 'px';
	objPopup.style.left = windowLeft + 'px';
	objPopup.style.width = popupW + 'px';
	objPopup.style.maxHeight = popupH + 'px';
	objPopup.style.left = ((windowWidth / 2) - popupW/2) + 'px';
	objPopup.style.overflow = 'auto';

	var editHtml = '<div style="text-align:center;"> [ <span id="pagename">' + id + '</span> ] Now loading...</div>';
	Element.update($('xpwiki_cal9_editarea'), editHtml);
	
	wikihelper_hide_helper();
	Element.show(objBack);
	Element.show(objPopup);
	Element.show($('xpwiki_cal9_loading_base'));
	
	// �ڡ���������ɹ���ȿ�Ǥ���
	var url = wikihelper_root_url + '/?cmd=' + mode;
	var pars = '';
	// pars +=  'mode=get'
	pars += 'page=' + encodeURIComponent(id);
	pars += '&ajax=1';
	pars += '&nonconvert=1';
	pars += '&encode_hint=' + encodeURIComponent(xpwiki_calender9_hint);
	
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get',
			parameters: pars,
			onComplete: xpwiki_cal9_showResponse
		});
	return false;
}

// �ݥåץ��åץ�����ɥ����Ĥ���
function xpwiki_cal9_day_edit_close() {
	wikihelper_hide_helper();
	xpwiki_ajax_edit_var['func_post'] = '';
	Element.hide($('xpwiki_cal9_popupback'));
	Element.hide($('xpwiki_cal9_popupmain'));
	Element.hide($('xpwiki_cal9_loading_base'));
	Element.update($('xpwiki_cal9_editarea'), '');
	return false;
}

function xpwiki_cal9_thisreload() {

	xpwiki_cal9_day_edit_close();

	// �ڡ���������ɹ���ȿ�Ǥ���
	var url = window.location.pathname;
	var pars = 'ajax=1';
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get',
			parameters: pars,
			onComplete: xpwiki_cal9_showReload
		});
}

function xpwiki_cal9_showReload(orgRequest) {

	var xmlRes = orgRequest.responseXML;
	if (xmlRes.getElementsByTagName('xpwiki').length) {
		if (!!$('wikihelper_base')) {
			var helper = $('wikihelper_base');
			Element.remove($('wikihelper_base'));
			document.body.appendChild(helper);
		}
		Element.remove($('xpwiki_cal9_popupback'));
		Element.remove($('xpwiki_cal9_loading_base'));
		Element.remove($('xpwiki_cal9_popupmain'));
		
		var item = xmlRes.getElementsByTagName('xpwiki')[0];
		$('xpwiki_body').innerHTML = item.getElementsByTagName('content')[0].firstChild.nodeValue;
		wikihelper_initTexts($('xpwiki_body'));
	}
}

function xpwiki_cal9_day_focus(id) {
	Element.setStyle($(id), {'border': 'red 1px solid'});
}

function xpwiki_cal9_day_unfocus(id, orgstyle) {
	Element.setStyle($(id), {'border': '#eeeeee 1px solid'});
}
