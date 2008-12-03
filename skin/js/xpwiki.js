var XpWiki = {
	Version: '20081003',
	
	MyUrl: '',
	EncHint: '',
	
	PopupDiv: null,
	
	PopupTop:    '10%',
	PopupBottom: '',
	PopupLeft:   '10px',
	PopupRight:  '',
	PopupHeight: '80%',
	PopupWidth:  '300px',

	fileupPopupTop:    '0px',
	fileupPopupBottom: '',
	fileupPopupLeft:   '0px',
	fileupPopupRight:  '',
	fileupPopupHeight: '99%',
	fileupPopupWidth:  '220px',
	new_window: '',

	dir: '',
	page: '',
	title: '',
	UploadDir: '',
	UploadPage: '',
	RendererDir: '',
	RendererPage: '',
	
	isIE8: (Prototype.Browser.IE && typeof(window.localStorage) != "undefined"),
	isIE7: (Prototype.Browser.IE && typeof(document.documentElement.style.msInterpolationMode) != "undefined" && typeof(window.localStorage) == "undefined"),
	isIE6: (Prototype.Browser.IE && typeof(document.documentElement.style.msInterpolationMode) == "undefined"),
	
	onDomLoaded: function () {
		this.IEVer = this.isIE8? 8 : (this.isIE7? 7 : (this.isIE6? 6 : 0));
		this.MyUrl = XpWikiModuleUrl;
		this.EncHint = XpWikiEncHint;

		// cookie
		wikihelper_adv = wikihelper_load_cookie("__whlp");
		if (wikihelper_adv) wikihelper_save_cookie("__whlp",wikihelper_adv,90,"/");

		this.addCssInHead('base.css');

		var body = document.getElementsByTagName('body')[0];
		this.remakeTextArea(body);
		wikihelper_initTexts(body);
		this.initDomExtension(body);
		this.faviconSet(body);
	},
	
	initPopupDiv: function (arg) {
		
		if (typeof arg == 'undefined') {
			var arg = [];
		}
		
		if (!$('XpWikiPopup')) {
			
			// base
			this.PopupDiv = document.createElement('div');
			this.PopupDiv.id = 'XpWikiPopup';
			Element.setStyle(this.PopupDiv,{
				position: 'fixed',
				overflow: 'hidden',
				marginRight: '5px',
				marginBottom: '5px',
				zIndex: '1000'
			});
			
			// body (iframe)
			var elem = document.createElement('iframe');
			elem.id = 'XpWikiPopupBody';
			elem.src = '';
			Element.setStyle(elem,{
				position: 'absolute',
				top: '22px',
				left: '0px',
				margin: '0px',
				padding: '0px',
				overflow: 'auto',
				border: 'none',
				width: '100%',
				height: '100%'
			});
			this.PopupDiv.appendChild(elem);

			// cover for event
			var elem = document.createElement('div');
			elem.id = 'XpWikiPopupCover';
			Element.setStyle(elem,{
				position: 'absolute',
				top: '22px',
				left: '0px',
				margin: '0px',
				padding: '0px',
				overflow: 'hidden',
				border: 'none',
				width: '100%',
				height: '100%',
				zIndex: '10000'
			});
			this.PopupDiv.appendChild(elem);
			
			// header
			elem = document.createElement('div');
			elem.id = 'XpWikiPopupHeader';
			Element.setStyle(elem,{
				position: 'absolute',
				top: '0px',
				right: '0px',
				margin: '0px',
				padding: '0px',
				width: '100%',
				height: '22px',
				fontSize: '14px',
				cursor: 'move'
			});
			elem.innerHTML = '<div style="float:right;cursor:pointer;padding-top:4px;padding-right:5px;" onclick="Element.hide(\'XpWikiPopup\');"><img src="' + this.MyUrl + '/' + this.dir + '/skin/loader.php?src=close.gif" alt="Close" title="Close"></div>' +
					'<span id="XpWikiPopupHeaderTitle" style="padding-left:5px;"></span>';
			this.PopupDiv.appendChild(elem);
			
			var objBody = $('xpwiki_body') || document.getElementsByTagName('body').item(0);
			objBody.appendChild(this.PopupDiv);

			if (!!arg.bottom) {
				this.PopupDiv.style.bottom = this.PopupBottom = arg.bottom;
			} else if (!!this.PopupBottom) {
				this.PopupDiv.style.bottom = this.PopupBottom;
			}

			if (!!arg.top) {
				this.PopupDiv.style.top = this.PopupTop = arg.top;
			} else if (!!this.PopupTop && !this.PopupBottom) {
				this.PopupDiv.style.top = this.PopupTop;
			}
			
			if (!!arg.right) {
				this.PopupDiv.style.right = this.PopupRight = arg.right;
			} else if (!!this.PopupRight) {
				this.PopupDiv.style.right = this.PopupRight
			}

			if (!!arg.left) {
				this.PopupDiv.style.left = this.PopupLeft = arg.left;
			} else if (!!this.PopupLeft && !this.PopupRight) {
				this.PopupDiv.style.left = this.PopupLeft;
			}
			
			if (!!arg.width) {
				this.PopupDiv.style.width = this.PopupWidth = arg.width;
			} else if (!!this.PopupWidth) {
				this.PopupDiv.style.width = this.PopupWidth;
			}
			
			if (!!arg.height) {
				this.PopupDiv.style.height = this.PopupHeight = arg.height;
			} else if (!!this.PopupHeight) {
				this.PopupDiv.style.height = this.PopupHeight;
			}
			
			if (!!this.PopupDiv.style.top) {
				this.PopupDiv.style.top = this.PopupTop = this.PopupDiv.offsetTop + 'px';
			}
			if (!!this.PopupDiv.style.left) {
				this.PopupDiv.style.left = this.PopupLeft = this.PopupDiv.offsetLeft + 'px'; 
			}
			
			$('XpWikiPopupBody').src = '';
			$('XpWikiPopupBody').observe("load", function(){
				$('XpWikiPopupHeaderTitle').innerHTML = this.title.replace(/(\w)/g, "$1&#8203;");
			}.bind(this));

			Element.hide('XpWikiPopupCover');
			
			new Draggable(this.PopupDiv.id, {handle:'XpWikiPopupHeader', starteffect:this.dragStart, endeffect:this.dragEnd });
			new Resizable(this.PopupDiv.id, {mode:'xy', element:'XpWikiPopupBody', starteffect:this.dragStart, endeffect:this.dragEnd });
		}
		Element.hide(this.PopupDiv);
	},
	
	dragStart: function () {
		Element.show('XpWikiPopupCover');
		if (Prototype.Browser.IE) { Element.hide('XpWikiPopupBody'); }
	},
	
	dragEnd: function () {
		if (Prototype.Browser.IE) { Element.show('XpWikiPopupBody'); }
		Element.hide('XpWikiPopupCover');
	},
	pagePopup: function (arg) {
		if (!arg.dir || !arg.page) return true;
		
		if (typeof(document.body.style.maxHeight) != 'undefined') {
			if (!!$('XpWikiPopup') && this.dir == arg.dir && this.page == arg.page) {
				Element.show(this.PopupDiv);
				return false;
			}
			
			this.dir = arg.dir;
			this.page = arg.page.replace(/(#[^#]+)?$/, '');
			var hash = arg.page.replace(/^[^#]+/, '');
			
			this.title = this.htmlspecialchars(this.page);
			
			this.initPopupDiv(arg);
			$('XpWikiPopupHeaderTitle').innerHTML = 'Now loading...';
	
			var url = this.MyUrl + '/' + this.dir + '/?cmd=read';
			url += '&page=' + encodeURIComponent(this.page);
			url += '&popup=1';
			url += '&encode_hint=' + encodeURIComponent(this.EncHint);
			url += hash;
			
			$('XpWikiPopupBody').src = url;
			Element.show(this.PopupDiv);
		} else {
			this.dir = arg.dir;
			this.page = arg.page.replace(/(#[^#]+)?$/, '');
			var hash = arg.page.replace(/^[^#]+/, '');
			
			this.title = this.htmlspecialchars(this.page);
			
			if (!window.self.name) {
				window.self.name = "xpwiki_opener";
			}
			this.window_name = window.self.name;
			
			var url = this.MyUrl + '/' + this.dir + '/?cmd=read';
			url += '&page=' + encodeURIComponent(this.page);
			url += '&popup=' + encodeURIComponent(this.window_name);
			url += '&encode_hint=' + encodeURIComponent(this.EncHint);
			url += hash;
			
			var width = '250';
			var height = '400';
			var top = '10';
			var left = '10';
		    var options = "width=" + width + ",height=" + height + ",top=" + top + ",left=" + left + "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no";

		    this.new_window = window.open(url, 'xpwiki_popup', options);
		    //this.new_window.document.title = this.title;
		    this.new_window.focus();
		}
		return false;
	},
	
	pagePopupAjax: function (arg) {
		if (!arg.dir || !arg.page) return;
		
		if (!!$('XpWikiPopup') && this.dir == arg.dir && this.page == arg.page) {
			Element.show(this.PopupDiv);
			return;
		}
		
		this.dir = arg.dir;
		this.page = arg.page;
		this.title = arg.page;
		
		if (!!arg.top) { this.PopupTop = arg.top; }
		if (!!arg.left) { this.PopupLeft = arg.left; }
		if (!!arg.width) { this.PopupWidth = arg.width; }
		if (!!arg.height) { this.PopupHeight = arg.height; }

		this.initPopupDiv();
		
		var url = this.MyUrl + '/' + this.dir + '/?cmd=read';
		var pars = '';
		pars += 'page=' + encodeURIComponent(arg.page);
		pars += '&ajax=1';
		pars += '&encode_hint=' + encodeURIComponent(this.EncHint);
		
		var myAjax = new Ajax.Request(
			url, 
			{
				method: 'get',
				parameters: pars,
				onComplete: this.ShowPopup.bind(this)
			}
		);
		
	},
	
	ShowPopup: function (orgRequest) {
		var xmlRes = orgRequest.responseXML;
		if (xmlRes.getElementsByTagName('xpwiki').length) {
		
			var item = xmlRes.getElementsByTagName('xpwiki')[0];
			var str = item.getElementsByTagName('content')[0].firstChild.nodeValue;
			var mode = item.getElementsByTagName('mode')[0].firstChild.nodeValue;
			
			if (mode == 'read') {
				var objHead = document.getElementsByTagName('head').item(0);
				var ins;
				ins = document.createElement('div');
				Element.update(ins, item.getElementsByTagName('headPreTag')[0].firstChild.nodeValue);
				objHead.appendChild(ins);
	
				ins = document.createElement('div');
				Element.update(ins, item.getElementsByTagName('headTag')[0].firstChild.nodeValue);
				objHead.appendChild(ins);
				
				var body = item.getElementsByTagName('content')[0].firstChild.nodeValue;
				
				this.Popup(body, this.title);
			}
		}
	},
	
	Popup: function (body, title) {
		this.initPopupDiv();
		Element.setStyle(this.PopupDiv,{
			top: this.PopupTop,
			left: this.PopupLeft,
			height: this.PopupHeight,
			width: this.PopupWidth
		});
		$('XpWikiPopupHeaderTitle').innerHTML = title.replace(/([\w])/g, "$1&#8203;");
		$('XpWikiPopupBody').innerHTML = '<div style="margin:10px;">' + body + '</div>';
		//wikihelper_initTexts(this.PopupDiv.id);
		Element.show(this.PopupDiv);
	},
	
	PopupHide: function () {
		if (this.new_window) {
			this.new_window.close();
			this.new_window = '';
		} else if ($('XpWikiPopup')) {
			Element.hide('XpWikiPopup');
		}
	},
	
	textaraWrap: function (id) {
	    var txtarea = $(id);
	    var wrap = txtarea.getAttribute('wrap');
	    if(wrap && wrap.toLowerCase() == 'off'){
	        txtarea.setAttribute('wrap', 'soft');
	        var ret = wikihelper_msg_nowrap;
	    }else{
	        txtarea.setAttribute('wrap', 'off');
	        var ret = wikihelper_msg_wrap;
	    }
	    // Fix display for mozilla
	    var parNod = txtarea.parentNode;
	    var nxtSib = txtarea.nextSibling;
	    parNod.removeChild(txtarea);
	    parNod.insertBefore(txtarea, nxtSib);
	    return ret;
	},
	
	addWrapButton: function (id) {
		var txtarea = $(id);
		id = txtarea.id;
		
		if (typeof(txtarea.XpWiki_addWrap_done) != 'undefined') return false;
		txtarea.XpWiki_addWrap_done = true;

		var btn = document.createElement('div');
		btn.id = id + '_WrapBtn';
		btn.className = 'xpwikiWrapBtn';
		btn.innerHTML = wikihelper_msg_nowrap;
		Event.observe(btn, 'click', function(){
			this.innerHTML = XpWiki.textaraWrap(id);
		});
		
		var refNode = ($(id + '_resize_base_resizeXY'))? $(id + '_resize_base_resizeXY') : $(id);
		this.DOMNode_insertAfter(btn, refNode);
		
		if (txtarea.getAttribute("rel") == "wikihelper") {
			if (id.match(/^[a-z0-9_-]+:/i)) {
				var mydir = id.replace(/^([a-z0-9_-]+):.+$/i, "$1");
			} else {
				var mydir = this.RendererDir;
			}
			this.addFckButton(id, mydir);
		}

	},

	addFckButton: function (id, mydir) {
		if (this.FCKeditor_path) {
			var txtarea = $(id);
			
			if (typeof(txtarea.XpWiki_addFck_done) != 'undefined') return false;
			txtarea.XpWiki_addFck_done = true;

			var btn = document.createElement('div');
			btn.id = id + '_FckBtn';
			btn.className = 'xpwikiFckBtn';
			btn.innerHTML = wikihelper_msg_rich_editor;
			Event.observe(btn, 'click', function(){
				XpWiki.switch2FCK(id, mydir);
			});
			var refNode = ($(id + '_resize_base_resizeXY'))? $(id + '_resize_base_resizeXY') : $(id);
			this.DOMNode_insertAfter(btn, refNode);
		}
	},
	
	addCssInHead: function (filename) {
		var doload = true;
		var links = document.getElementsByTagName('link');
		for (var i=0; i<links.length; i++){
			var link = links[i];
			if (link.getAttribute('href')) {
				var href = String(link.getAttribute('href')).toLowerCase();
				if (href.match(wikihelper_root_url + '/skin/loader.php')
					&& href.match(filename)) {
					doload = false;
					break;
				}
			}
		}
		if (doload) {
			var css = document.createElement('link');
			css.href = wikihelper_root_url + '/skin/loader.php?src=' + filename;
			css.rel  = 'stylesheet';
			css.type = 'text/css';
			document.getElementsByTagName('head')[0].appendChild(css);
		}
	},
	
	faviconSetDone: false,
	faviconSet: function (body) {
		if (this.faviconSetDone || typeof(this.faviconSetClass) == 'undefined' || this.faviconSetClass == '') return;
		
		var em = document.createElement('div');
		em.style.height = '1em';
		em.style.width = '1px';
		em.style.visibility = 'hidden';
		body.appendChild(em);
		var pxPerEm = em.clientHeight;
		body.removeChild(em);
		
		var ins_a = new Array();
		var ins_img = new Array();
		this.faviconSetDone = true;
		var time_limit = 3000; // (ms)
		time_limit += new Date().getTime();
		var x = document.evaluate('//a[@class="' + this.faviconSetClass + '"]', body, null, 6, null);
		var n = 0;
		for (var i = 0; i < x.snapshotLength; i++) {
			if (time_limit < new Date().getTime()) break;
			var obj = x.snapshotItem(i);
			if (obj.className == this.faviconSetClass && obj.firstChild && obj.firstChild.nodeName.toUpperCase() != 'IMG') {
				var height = Element.getStyle(obj ,'fontSize');
				if (height.match(/%$/)) {
					height = parseFloat(height)/100 * pxPerEm;
				} else if (height.match(/em$/)) {
					height = parseFloat(height) * pxPerEm;
				} else {
					height = parseFloat(height);
				}
				if (isNaN(height)) {
					var _span = document.createElement('span');
					_span.innerHTML = 'x';
					obj.appendChild(_span);
					height = _span.offsetHeight + '';
					obj.removeChild(_span);
				}
				height = Math.min(32, height) + 'px';
				var img = document.createElement('img');
				img.src = wikihelper_root_url + '/skin/loader.php?src=favicon&url=' + this.rawurlencode(obj.readAttribute("href").replace(/\?.*/, ''));
				img.alt = '';
				img.style.width = height;
				img.style.height = height;
				img.className = 'xpwikiFavicon';
				
				ins_a[n] = obj
				ins_img[n] = img; 

				n++;
			}
		}
		if (ins_a.length) {
			for (var i = 0; i < ins_a.length; i++) {
				if (typeof(this.faviconReplaceClass) == 'undefined') {
					ins_a[i].style.backgroundImage = 'none';
					ins_a[i].style.paddingLeft = "0px";
				} else {
					ins_a[i].className = this.faviconReplaceClass;
				}
				ins_a[i].insertBefore(ins_img[i], ins_a[i].firstChild);
			}
		}
	},
	
	checkUseHelper: function (obj) {
		if (!!this.UseWikihelperAtAll || obj.id.match(/^xpwiki/)) {
			return true;
		} else {
			var scripts = document.getElementsByTagName('script');
			for (var i=0; i<scripts.length; i++){
				if (!!scripts[i].src && scripts[i].src.match(/wikihelper_loader\.js$/)) {
					return true;
				}
			}
		}
		var pnode;
		while(pnode = obj.parentNode) {
			if (typeof pnode.className != 'undefined') {
				if (pnode.className.match(/^NoWikiHelper/)) {
					return false;
				}
				if (pnode.className.match(/^xpwiki/)) {
					return true;
				}
			}
			obj = pnode;
		}
		return false;
	},
	
	remakeTextArea: function (obj) {
		var tareas = obj.getElementsByTagName('textarea');
		for (var i=0; i<tareas.length; i++){
			if (tareas[i].style.display == 'none') continue;
			if (!tareas[i].getAttribute('rel') && !tareas[i].getAttribute('readonly') && this.checkUseHelper(tareas[i])) {
				tareas[i].setAttribute("rel", "wikihelper");
			}
			if (!tareas[i].id) {
				tareas[i].id = 'textarea_autoid_' + i;
			}
			new Resizable(tareas[i].id, {mode:'xy'});
			
			this.addWrapButton(tareas[i].id);
		}
	},

	initDomExtension: function (obj) {
		var pres = new Array();
		var pNode;
		var inTable;
		var tocId = 0;
		var tocCond = this.cookieLoad('_xwtoc');

		var x = document.evaluate('//div[@class="pre"]', document.body, null, 6, null);
		var n = 0;
		for (var i = 0; i < x.snapshotLength; i++) {
			var obj = x.snapshotItem(i);
			var overflow = (obj.style.overflow || obj.style.overflowX);
			if (overflow.toUpperCase() == 'AUTO') {
				inTable = false;
				pNode = obj.parentNode;
				do {
					if (pNode.nodeName.toUpperCase() == 'TD' || this.isIE6) {
						inTable = true;
						break;
					}
					pNode = pNode.parentNode;
				} while(pNode);
				if (inTable && obj.offsetParent) {
					obj.style.width = '500px';
					pres.push(obj);
				}
			}
		}
		for (var i=0; i<pres.length; i++) {
			var width = pres[i].offsetParent.offsetWidth - pres[i].offsetLeft - 30;
			if (width > 0) {
				pres[i].style.width = width + 'px';
			}
		}

		var x = document.evaluate('//div[@class="toc_header"]', document.body, null, 6, null);
		var n = 0;
		for (var i = 0; i < x.snapshotLength; i++) {
			var obj = x.snapshotItem(i);
			obj.id = 'xpwiki_toc_header' + tocId;
			var base = obj.parentNode;
			base.id = 'xpwiki_toc_base' + tocId;
			var toc_childlen = base.getElementsByTagName('div');
			var toc_body = null;
			for (var toc_i=0; toc_i<toc_childlen.length; toc_i++){
				if (toc_childlen[toc_i].className === "toc_body") {
					toc_body = toc_childlen[toc_i];
					toc_body.id = 'xpwiki_toc_body' + tocId;
					break;
				}
			}
			if (toc_body) {
				var toc_marker = document.createElement('span');
				toc_marker.id = 'xpwiki_toc_marker' + tocId;
				toc_marker.title = 'Toggle';
				obj.insertBefore(toc_marker, obj.firstChild);
				eval( 'obj.onclick = function(){ XpWiki.tocToggle("' + tocId + '"); };');
				this.tocSetMarker(toc_body, toc_marker);
				if (tocCond == '+') {
					this.tocToggle(tocId);
				}
				
				var lis = toc_body.getElementsByTagName('li');
				var licnt = 0;
				for (var li_i=0; li_i<lis.length; li_i++) {
					var li = lis[li_i];
					var ul = li.getElementsByTagName('ul');
					if (ul.length && li.firstChild.nodeName.toUpperCase() != 'UL') {
						var handle = document.createElement('div');
						handle.innerHTML = '<img src="' + wikihelper_root_url + '/skin/loader.php?src=minus.gif" />';
						handle.id = 'xpwiki_toc_hd' + tocId + '_' + licnt;
						handle.className = 'toc_handle';
						eval( 'handle.onclick = function(){ XpWiki.listTreeToggle("' + handle.id + '"); };');
						li.insertBefore(handle, li.firstChild);
						li.style.listStyleType = 'none';
						licnt++;
					}
				}
				
				if (!this.isIE6) {
					var toc_pin = document.createElement('div');
					toc_pin.className = 'toc_pin';
					toc_pin.id = 'xpwiki_toc_pin' + tocId;
					toc_pin.title = 'Fix';
					toc_body.insertBefore(toc_pin, toc_body.firstChild);
					eval( 'toc_pin.onclick = function(e){ XpWiki.tocFix("' + tocId + '"); };');
					obj.style.cursor = 'pointer';
				}
				
				tocId++;
			}
		}
	},

	tocToggle: function (tocId) {
		body = $('xpwiki_toc_body' + tocId);
		marker = $('xpwiki_toc_marker' + tocId);
		Element.toggle(body);
		this.tocSetMarker(body, marker);
	},
	
	tocSetMarker: function (body, marker) {
		var cond;
		if (body.style.display === 'none') {
			marker.className = 'toc_open';
			cond = '+';
		} else {
			marker.className = 'toc_close';
			cond = '-';
		}
		marker.innerHTML = '<span>' + cond + '</span>';
		this.cookieSave('_xwtoc', cond, 90, '/');
	},
	
	tocFix: function (tocId) {
		Element.remove($('xpwiki_toc_pin' + tocId));
		var base = $('xpwiki_toc_base' + tocId);
		var width = base.getWidth();
		var pos = base.cumulativeOffset();
		var offset = [0,0];
		offset[0] = parseInt(base.getStyle('paddingLeft'));
		offset[1] = parseInt(base.getStyle('paddingTop'));
		base.style.width = (width - offset[0] - parseInt(base.getStyle('paddingRight'))) + 'px';
		base.className = base.className + ' contentsFixed';
		base.style.left = (pos[0] + offset[0] - (document.documentElement.scrollLeft || document.body.scrollLeft || 0))+ 'px';
		base.style.top = (pos[1] + offset[1] - (document.documentElement.scrollTop || document.body.scrollTop || 0)) + 'px';
		base.style.zIndex = '1000';
		base.style.right = '';
		base.style.bottom = '';
		base.style.padding = '0';

		var handle = base;
		
		var body = $('xpwiki_toc_body' + tocId);
		
		var ul = body.getElementsByTagName('ul')[0];
		if (!Prototype.Browser.IE || this.IEVer > 7) {
			body.style.maxHeight = (document.viewport.getHeight() - 40) + 'px';
			body.style.overflowY = 'auto';
			handle = ul;
		}
		handle.style.cursor = 'move';
		
		new Draggable(base, { handle:handle });
		new Resizable(base, { mode:'x', element:base.id });

	},
	
	listTreeToggle: function (id) {
		var elms = $(id).parentNode.childNodes;
		for (var i=0; i<elms.length; i++) {
			if (elms[i].nodeName.toUpperCase() == 'UL' || elms[i].nodeName.toUpperCase() == 'OL') {
				Element.toggle(elms[i]);
				if (elms[i].style.display == 'none') {
					var src = 'plus';
				} else {
					var src = 'minus';
				}
				$(id).innerHTML = '<img src="' + wikihelper_root_url + '/skin/loader.php?src=' + src + '.gif" />';
				break;
			}
		}
	},
	
	htmlspecialchars: function (str) {
		return str.
		replace(/&/g,"&amp;").
		replace(/</g,"&lt;").
		replace(/>/g,"&gt;").
		replace(/"/g,"&quot;").
		replace(/'/g,"&#039;");
	},

	rawurlencode: function (str) {
		try {
			return encodeURIComponent(str)
				.replace(/!/g,  "%21")
				.replace(/'/g,  "%27")
				.replace(/\(/g, "%28")
				.replace(/\)/g, "%29")
				.replace(/\*/g, "%2A")
				.replace(/~/g,  "%7E");
		} catch(e) {
			return escape(str)
				.replace(/\+/g, "%2B")
				.replace(/\//g, "%2F")
				.replace(/@/g,  "%40");
		}
	},
	
	cookieSave: function (arg1, arg2, arg3, arg4) {
		// arg1=dataname, arg2=data, arg3=expiration days, arg4=path
		var xDay;
		var _exp;
		var _path;
		if(arg1 && arg2) {
			if (arg3) {
				xDay = new Date;
				xDay.setDate(xDay.getDate() + eval(arg3));
				xDay = xDay.toGMTString();
				_exp = ";expires=" + xDay;
			} else {
				_exp ="";
			}
			if(arg4) {
				_path = ";path=" + arg4;
			} else {
				_path= "";
			}
			document.cookie = escape(arg1) + "=" + escape(arg2) + _exp + _path +";";
		}
	},
	
	cookieLoad: function (arg) {
		if (arg) {
			var cookieData = document.cookie + ";" ;
			arg = escape(arg);
			var startPoint1 = cookieData.indexOf(arg);
			var startPoint2 = cookieData.indexOf("=", startPoint1) + 1;
			var endPoint = cookieData.indexOf(";", startPoint1);
			if(startPoint2 < endPoint && startPoint1 > -1 && startPoint2-startPoint1 == arg.length + 1) {
				cookieData = cookieData.substring(startPoint2,endPoint);
				cookieData = unescape(cookieData);
				return cookieData;
			}
		}
		return false;
	},

	insertClone: function (srcId, toId) {
		var src = $(srcId);
		var cln = src.cloneNode(true);
		cln.id = '';
		var inp = cln.getElementsByTagName('INPUT');
		for (var i=0; i < inp.length; i++) {
			if (inp[i].type === 'file') {
				inp[i].value = '';
			}
		}
		var to = $(toId);
		to.appendChild(cln);
	},
	
	fileupFormPopup: function (mode, page) {
		
		if (typeof page != "undefined") {
			this.dir = mode;
			this.UploadPage = page;
			this.title = this.htmlspecialchars(page);
		} else {
			this.dir = this.UploadDir;
			this.title = this.htmlspecialchars(this.UploadPage);
		}
		if (typeof mode == "undefind") {
			mode = '';
		}
		
		var url = this.MyUrl + '/' + this.dir + '/?plugin=attach&pcmd=imglist&refer=';
		url += encodeURIComponent(this.UploadPage);
		url += '&base=' + encodeURIComponent(this.UploadPage);
		url += '&popup=_self';
		url += '&cols=1';
		url += '&max=10';
		url += '&mode=' + mode;
		url += '&encode_hint=' + encodeURIComponent(this.EncHint);
		
		if (this.isIE6) {
			url += '&winop=1';
			if (!window.self.name) {
				window.self.name = "xpwiki_opener";
			}
			this.window_name = window.self.name;
			
			var width = '250';
			var height = '400';
			var top = '10';
			var left = '10';
			var options = "width=" + width + ",height=" + height + ",top=" + top + ",left=" + left + "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no";

			this.new_window = window.open(url, 'xpwiki_popup', options);
			this.new_window.focus();
		
		} else {
		
			var arg = [];
			arg.top = this.fileupPopupTop;
			arg.bottom = this.fileupPopupBottom;
			arg.left = this.fileupPopupLeft;
			arg.right = this.fileupPopupRight;
			arg.width = this.fileupPopupWidth;
			arg.height = this.fileupPopupHeight;
			
			this.initPopupDiv(arg);
			
			if ($('XpWikiPopupBody').src != url) {
				$('XpWikiPopupHeaderTitle').innerHTML = 'Now loading...';
				$('XpWikiPopupBody').src = url;
				this.PopupBodyUrl = url;
			}
			
			var zindex = this.getLargestZIndex('iframe') + 1;
			this.PopupDiv.style.zIndex = Math.max(this.PopupDiv.style.zIndex, zindex);

			Element.show(this.PopupDiv);
		
		}
		
		return false;
	},
	
	setUploadVar: function (elm) {
		if (!!elm) {
			elm = $(elm);
			if (elm.id.match(/^[a-z_]+:/i)) {
				var form;
				var element = elm;
				 while (element = element.parentNode) {
					if (element.nodeName.toUpperCase() == 'FORM') {
						form = element;
						break;
					}		
				}
				if (form && (typeof form.page != 'undefined' || typeof form.refer != 'undefined')) {
					var dir = elm.id.replace(/^([a-z_]+):.+$/i, "$1");
					var reg = new RegExp('/'+dir);
					if (form.action.match(reg)) {
						this.UploadDir = dir;
						this.UploadPage = (form.page || form.refer).value;
					}
				}
			} else {
				if (elm.nodeName.toUpperCase() == 'TEXTAREA' && this.RendererDir && this.RendererPage) {
					this.UploadDir = this.RendererDir;
					this.UploadPage = this.RendererPage;
				}
			}
		}
	},
	
	refInsert: function(file, type) {
		if (!wikihelper_elem) {
			alert(wikihelper_msg_elem);
			return false;
		}
		var size = '';
		if (type == 'image') {
			inp = prompt(wikihelper_msg_thumbsize, '');
			if (inp == null) { return; }
			inp = this.z2h_digit(inp);
			var size = '';
			if (inp.match(/[\d]{1,3}[^\d]+[\d]{1,3}/)) {
				size = inp.replace(/([\d]{1,3})[^\d]+([\d]{1,3})/, ",mw:$1,mh:$2");
			} else if (inp.match(/[\d]{1,3}/)) {
				size = inp.replace(/([\d]{1,3})/, ",mw:$1,mh:$1");
			}
		}
		if (this.isIE6) {
			this.PopupHide();
		}
		var v = "&ref("+file+size+");";
		wikihelper_ins(v);
		
		return false;
	},
	
	FCKrefInsert: function(file, type) {
		var r = document.evaluate('//iframe[contains(@src,\'/editor/fckdialog.html\')]', document, null, 7, null);
		if (r) {
			var base = (r.snapshotItem(0).contentWindow.document || r.snapshotItem(0).contentDocument);
			var fckdialog = (base.getElementById('frmMain').contentWindow.document || base.getElementById('frmMain').contentDocument);
			fckdialog.getElementById('name').value = file;
		}
		this.PopupHide();
		return false;
	},
	
	switch2FCK: function(id, dir) {
		if (typeof FCKeditor == 'undefined') {
			xpwiki_now_loading(true, $(id).parentNode);
			FCKeditor = false;
			var sc = document.createElement('script');
			sc.type = 'text/javascript';
			sc.charset = 'UTF-8';
			if (window.ActiveXObject) {
				sc.onreadystatechange = function(){
					if (sc.readyState == 'complete' || sc.readyState == 'loaded') {
						XpWiki.switch2FCK(id, dir);
					}
				};
			} else {
				sc.onload = function(){
					XpWiki.switch2FCK(id, dir);
				};
				sc.onerror = function(){
					XpWiki.switch2FCK(id, dir);
				};
			}
			sc.src = this.FCKeditor_path + 'fckeditor.js';
			document.body.appendChild(sc);
		} else if (typeof FCKeditor == "function") {
			if (typeof FCKeditorAPI == "object" && FCKeditorAPI.GetInstance(id)) {
				return this.toggleFCK(id);
			}
			
			this.setUploadVar(id);
			var myDir = XpWikiModuleUrl + '/' + dir;

			var oFCKeditor = new FCKeditor(id);
			
			if (this.UploadPage == this.RendererPage) {
				oFCKeditor.Config['xpWiki_LineBreak'] = 1;
			} else {
				oFCKeditor.Config['xpWiki_LineBreak'] = "";
			}
			oFCKeditor.Config['xpWiki_myPath'] = myDir + '/';
			oFCKeditor.Config['xpWiki_FCKxpwikiPath'] = this.FCKxpwiki_path;
			oFCKeditor.Config['xpWiki_PageName'] = this.UploadPage;
			
			oFCKeditor.BasePath = this.FCKeditor_path;

			oFCKeditor.Height = "100%";
			
			oFCKeditor.Config['CustomConfigurationsPath'] = myDir + "/skin/loader.php?src=fck.config.js";
			oFCKeditor.Config['EditorAreaCSS'] = myDir + "/skin/loader.php?src=main+fckeditor.css&f=1";
			oFCKeditor.Config['SkinPath'] = this.FCKxpwiki_path + "skin/";
			oFCKeditor.Config['PluginsPath'] = this.FCKxpwiki_path + "plugins/";
			oFCKeditor.Config['SmileyImages'] = this.FCKSmileys;
			
			oFCKeditor.ReplaceTextarea();
			
			Element.hide(id + '_WrapBtn');
			Element.hide(id + '_FckBtn');
			wikihelper_hide_helper();
		} else {
			$(id + '_FckBtn').innerHTML = 'x';
		}
	},
	
	toggleFCK: function(id) {
		var oEditorIns = FCKeditorAPI.GetInstance(id);
		var oEditorIframe = $(id + '___Frame');
		var tArea = $(id);
		var bIsWysiwyg = ( oEditorIns.EditMode == FCK_EDITMODE_WYSIWYG );
		Element.hide(id + '_WrapBtn');
		Element.hide(id + '_FckBtn');
		if (tArea.style.display == 'none') {
			if (!tArea._FCKBlurRegisted) {
				tArea._FCKBlurRegisted = true;
				Event.observe(tArea, 'blur', function(){
					oEditorIns.EditingArea.Textarea.value = tArea.value;
				});
			}
			if ( bIsWysiwyg ) oEditorIns.SwitchEditMode(); //switch to plain
			var text = oEditorIns.GetData( oEditorIns.Config.FormatSource );
			tArea.value = text;
			oEditorIframe.style.display = 'none';
			tArea.style.display = '';
			$(id + '_FckBtn').innerHTML = wikihelper_msg_rich_editor;
			Element.show(id + '_FckBtn');
			Element.show(id + '_WrapBtn');
		} else {
			if ( bIsWysiwyg ) oEditorIns.SwitchEditMode(); //switch to plain
			oEditorIns.EditingArea.Textarea.value = tArea.value
			if ( !bIsWysiwyg ) oEditorIns.SwitchEditMode(); //switch to WYSIWYG
			tArea.style.display = 'none';
			oEditorIframe.style.display = '';
			$(id + '_FckBtn').innerHTML = wikihelper_msg_normal_editor;
			Element.show(id + '_FckBtn');
		}
	},
	
	removeFCK: function(areaId) {
		var wait = 0;
		if (typeof FCKeditor == "function" && typeof FCKeditorAPI == "object") {
			var tareas = $(areaId).getElementsByTagName('textarea');
			for (var i=0; i<tareas.length; i++){
				var iframe = $(tareas[i].id + '___Frame');
				if (iframe) {
					delete FCKeditorAPI.Instances[ tareas[i].id ];
					iframe.parentNode.removeChild(iframe);
					if (Prototype.Browser.IE) wait = 10;
				}
			}
		}
		return wait;
	},
	
	// Copyright (c) 2003 AOK <soft@aokura.com>
	z2h_digit: function(src) {
		var str = new String;
		var len = src.length;
		for (var i = 0; i < len; i++) {
			var c = src.charCodeAt(i);
			if (c >= 65296 && c <= 65305) {
				str += String.fromCharCode(c - 65248);
			} else {
				str += src.charAt(i);
			} 
		}
		return str;
	},
	
	getLargestZIndex: function(){
		var largestZIndex = 0; 
		var defaultView = document.defaultView;
		var func = function(tagname){
			var elems = document.getElementsByTagName(tagname), len=elems.length;
			for(var i=0; i<len; i++){
				var elem = elems[i];
				var zIndex = elem.style.zIndex;
				if (!zIndex) {
					var css = elem.currentStyle || defaultView.getComputedStyle(elem,null);
					zIndex = css ? css.zIndex : 0;
				}
				zIndex -= 0;
				if(largestZIndex < zIndex) largestZIndex=zIndex;
			}
		};
		if(arguments.length == 0) func('*');
		else for(var i=0; i<arguments.length; i++) func(arguments[i]);
		return largestZIndex;
	},
	
	DOMNode_insertAfter: function(newChild, refChild) {
		var parent=refChild.parentNode;
		if(parent.lastChild==refChild) return parent.appendChild(newChild);
		else return parent.insertBefore(newChild,refChild.nextSibling);
	},

	cumulativeOffset: function(tgtElement) {
		var valueT = 0, valueL = 0;
		var element = tgtElement;
		do {
			valueT += element.offsetTop  || 0;
			valueL += element.offsetLeft || 0;
			if (Prototype.Browser.IE &&
				element == tgtElement && 
				element.tagName.toUpperCase() == 'DIV') {
				valueL -= element.offsetLeft || 0;
			}
			element = element.offsetParent;
		} while (element);
		return Element._returnOffset(valueL, valueT);
  }
};

// For FCKeditor
function FCKeditor_OnComplete(editorInstance) {
	var iframe = $(editorInstance.Name + '___Frame');
	iframe.style.marginTop = "3px";
	iframe.style.marginLeft = "3px";
	$(editorInstance.Name + '_FckBtn').innerHTML = wikihelper_msg_normal_editor;
	Element.show(editorInstance.Name + '_FckBtn');
	// For FormValidater (d3forum etc...)
	if (!$(editorInstance.Name).value) {
		$(editorInstance.Name).value = '&nbsp;';
	}

}