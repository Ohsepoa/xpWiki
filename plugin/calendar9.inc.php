<?php
/*
 * Created on 2007/08/30 by nao-pon http://hypweb.net/
 * $Id: calendar9.inc.php,v 1.7 2007/09/06 09:10:45 nao-pon Exp $
 */

class xpwiki_plugin_calendar9 extends xpwiki_plugin {
	function plugin_calendar9_init () {


	// $Id: calendar9.inc.php Ver.1.5
	// *������off�Ƚ񤯤��ȤǺ�����������ɽ�����ʤ��褦�ˤ�����
	// * calendar3���¤���ޤ�����
	
	// �ѹ�����
	//   PageName/DayOff �����ꤷ����������򸵤˵�����ɽ������
	//   ��������������ξ��Ͻ����ޤǤ����ǡ�����ɽ������
	//   prototype.js ����Ѥ���AJAX�б���
	//  2007/07/17 Ver.1.1
	//   �ɹ�������Խ��Ǥ��ʤ��褦�˽���
	//   Ajax���̿���XML�����ˤ���Safari�б�
	//   ʸ�������ɻ���(SOURCE_ENCODING)�б�
	//   ���γ������λ�����б�
	//  2007/07/19 Ver.1.2
	//   ���γ�������ѥ�᡼����������Ǥ���褦�ˤ���
	//   ����Ƚ������٤�夲��
	//  2007/08/17 Ver.1.3
	//   Ajax���ɹ���XML�ǡ����� & < > ' " ��ʸ��������Ȥ����ɹ���ʤ��Х�����
	//  2007/08/20 Ver.1.4
	//   ��������ѻ��ˤ��줤��ɽ������ʤ����ݤ��б�
	//  2007/08/21 Ver.1.5
	//   ��뤵�줿�ڡ���������å����ʤ��ǹ������Ƥ����Τǹ����Ǥ��ʤ��褦�˽���
	//
	
	// prototype.js �λ���
	//	$this->cont['PROTOTYPEJS_FILE'] =  DATA_HOME . 'skin/prototype.js';
	
	// ���γ����� 0:�� 1:�� 2:�� 3:�� 4:�� 5:�� 6:��
		$this->cont['BEGINNING_DAY_OF_WEEK'] =  0;

	}
	
	function plugin_calendar9_convert()
	{
		$this->func->add_tag_head('calendar.css');
		
		$date_str = $this->func->get_date('Ym');
		$base = $this->func->strip_bracket($this->root->vars['page']);
		$beginningDayOfWeek = $this->cont['BEGINNING_DAY_OF_WEEK'];
		
		// �ѥ�᡼������
		if (func_num_args() > 0) {
			$args = func_get_args();
			foreach ($args as $arg) {
				if (is_numeric($arg) && strlen($arg) == 6) {
					$date_str = $arg;
				}
				else if (is_numeric($arg) && strlen($arg) == 1) {
					$beginningDayOfWeek = $arg;
				}
				else {
					$base = $this->func->strip_bracket($arg);
				}
			}
		}
		
		
		$this_weeklabels = array_merge(array_slice($this->root->weeklabels, $beginningDayOfWeek, 7 - $beginningDayOfWeek), array_slice($this->root->weeklabels, 0, $beginningDayOfWeek));
		$diffday = 7 - $beginningDayOfWeek;
		
		if ($base == '*') {
			$base = '';
			$prefix = '';
		}
		else {
			$prefix = $base.'/';
		}
		$r_base = rawurlencode($base);
		$s_base = htmlspecialchars($base);
		$r_prefix = rawurlencode($prefix);
		$s_prefix = htmlspecialchars($prefix);
		
		$yr = substr($date_str,0,4);
		$mon = substr($date_str,4,2);
		
		if ($yr != $this->func->get_date('Y') || $mon != $this->func->get_date('m')) {
			$now_day = 1;
			$other_month = 1;
		}
		else {
			$now_day = $this->func->get_date('d');
			$other_month = 0;
		}
		
		// �����ɤ߹���
		$dayoff_list = array();
		$day_type = array();		// 0:���������� 1:��ǰ��(�����ȥ�Τ�) 2:���˥�������
		$day_title = array();
		$page = $prefix.'DayOff';
		$r_page = rawurlencode($page);
		$s_page = htmlspecialchars($page);
		foreach ($this->func->get_source($s_page) as $line) {
			$line = rtrim($line, "\n");
			$d = split(",", $line);
			$dayoff_list[] = $d[0];
			if($d[2] != "") {
				$day_type[$d[0]] = $d[2];
			}
			if($d[1] != "") {
				$day_title[$d[0]] = $d[1];
			}
		}
		
		$today = getdate(mktime(0,0,0,$mon,$now_day,$yr) - $this->cont['LOCALZONE'] + $this->cont['ZONETIME']);
		
		$m_num = $today['mon'];
		$d_num = $today['mday'];
		$year = $today['year'];
		
		$f_today = getdate(mktime(0,0,0,$m_num,1,$year) - $this->cont['LOCALZONE'] + $this->cont['ZONETIME']);
		$wday = $f_today['wday'];
		$day = 1;
		
		$m_name = "$year.$m_num";
		
		$y = substr($date_str,0,4)+0;
		$m = substr($date_str,4,2)+0;
		
		$prev_date_str = ($m == 1) ?
			sprintf('%04d%02d',$y - 1,12) : sprintf('%04d%02d',$y,$m - 1);
		
		$next_date_str = ($m == 12) ?
			sprintf('%04d%02d',$y + 1,1) : sprintf('%04d%02d',$y,$m + 1);
		
		$ret = '';
		$ret .= <<<EOD
<script>
<!--
// calendar9.inc.php http://www.sakasama.com/dive/
var browserOP = window.opera;             // OP
var browserGK = document.getElementById;  // Gecko or IE
var browserIE = document.all;             // IE

function showResponse(orgRequest) {
	var xmlRes = orgRequest.responseXML;
	if (xmlRes.getElementsByTagName("editform").length) {
		xpwiki_ajax_edit_var['func_update_post'] = thisreload;
		xpwiki_ajax_edit_var['func_preview_pre'] = day_edit_close;
		//$('xpwiki_cal9_editarea').innerHTML = xmlRes.getElementsByTagName("editform")[0].firstChild.nodeValue;
		Element.update($('xpwiki_cal9_editarea'), xmlRes.getElementsByTagName("editform")[0].firstChild.nodeValue);
		//$('xpwiki_cancel_form').innerHTML = '<button id="c9cancel" onclick="return day_edit_close()">{$this->root->_btn_cancel}</button>';
		Element.update($('xpwiki_cancel_form'), '<button id="c9cancel" onclick="return day_edit_close()">{$this->root->_btn_cancel}</button>');
		
		wikihelper_initTexts($('xpwiki_cal9_editarea'));
	} else if (xmlRes.getElementsByTagName("xpwiki").length) {
		var item = xmlRes.getElementsByTagName("xpwiki")[0];
		var str = item.getElementsByTagName("content")[0].firstChild.nodeValue;

		//$('xpwiki_cal9_editarea').innerHTML = str;
		Element.update($('xpwiki_cal9_editarea'), str);
		
		var close = document.createElement("input");
		close.type = 'button';
		close.value = 'Close';
		close.onclick = function() { day_edit_close(); }
		
		var obj = document.createElement("form");
		obj.style.textAlign = 'center';
		obj.appendChild(close);
		
		$('xpwiki_cal9_editarea').appendChild(obj);
		
		wikihelper_initTexts($('xpwiki_cal9_editarea'));
	}
	Element.remove($("loading_base"));
}

function day_edit(id,mode,event) {

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
	if(browserIE) {
		windowWidth = document.body.scrollWidth || document.documentElement.scrollWidth || 0;
		windowHeight = document.body.scrollHeight || document.documentElement.scrollHeight || 0;
	}
	else {
		windowWidth = document.documentElement.scrollWidth || document.body.scrollWidth || 0;
		windowHeight = document.documentElement.scrollHeight || document.body.scrollHeight || 0;
	}

	xpwiki_ajax_edit_var['id'] = 'xpwiki_body';
	xpwiki_ajax_edit_var["html"] = $(xpwiki_ajax_edit_var["id"]).innerHTML;
	
	// HTML BODY���֥������ȼ���
	var objBody = document.getElementsByTagName("body").item(0);
	
	// �ط�ȾƩ�����֥������Ⱥ���
	var objBack = document.createElement("div");
	objBack.setAttribute('id', 'popupback');
	objBack.onclick = function() { day_edit_close(); }
	Element.setStyle(objBack, {'display': 'none'});
	Element.setStyle(objBack, {'position': 'absolute'});
	Element.setStyle(objBack, {'zIndex': 90});
	Element.setStyle(objBack, {'textAlign': 'center'});
	Element.setStyle(objBack, {'backgroundColor': 'black'});
	Element.setStyle(objBack, {'filter': 'alpha(opacity=75)'});		// IE
	Element.setStyle(objBack, {'-mozOpacity': '0.75'});		// FF
	Element.setStyle(objBack, {'opacity': '0.75'});		// opera
	
	objBack.style.top = 0;
	objBack.style.left = 0;
	objBack.style.width = windowWidth + "px";
	objBack.style.height = windowHeight + "px";
	objBody.appendChild(objBack);
	
	// ���ϥܥå������֥������Ⱥ���
	var objPopup = document.createElement("div");
	objPopup.setAttribute('id', 'popupmain');
	var editHtml = 
	    '<div id="xpwiki_cal9_editarea">'
	  + '<div style="text-align:center;"> [ <span id="pagename">' + id + '</span> ] Now loading...</div>'
	  + '</div>';
	Element.update(objPopup, editHtml);

	var objLoading = document.createElement("div");
	objLoading.setAttribute('id','loading_base');
	objLoading.style.height = '100px';
	objLoading.style.padding = '50px';
	objLoading.style.textAlign = 'center';
	objPopup.appendChild(objLoading);

	var objLoadingLink = document.createElement("a");
	objLoadingLink.setAttribute('id','loadingLink');
	objLoadingLink.setAttribute('href','#');
	objLoadingLink.onclick = function() { day_edit_close(); }
	objLoading.appendChild(objLoadingLink);

	var objLoadingImage = document.createElement("img");
	objLoadingImage.setAttribute('src', wikihelper_root_url + '/skin/loader.php?src=loading.gif');
	objLoadingLink.appendChild(objLoadingImage);
	
	Element.setStyle(objPopup, {'display': 'none'});
	Element.setStyle(objPopup, {'position': 'absolute'});
	Element.setStyle(objPopup, {'zIndex': 100});
	Element.setStyle(objPopup, {'border': '2px black solid'});
	Element.setStyle(objPopup, {'backgroundColor': 'white'});
	Element.setStyle(objPopup, {'padding': '20px'});
	
	var popupH = ((window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight || 768) - 120);
	var popupW = ((window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth || 1024) - 300);
	objPopup.style.top = (windowTop + 20) + "px";
	objPopup.style.left = windowLeft + "px";
	objPopup.style.width = popupW + 'px';
	objPopup.style.maxHeight = popupH + 'px';
	objPopup.style.left = ((windowWidth / 2) - popupW/2) + "px";
	objPopup.style.overflow = 'auto';
	$('xpwiki_body').appendChild(objPopup);
	
	objBack.show();
	objPopup.show();
	
	// �ڡ���������ɹ���ȿ�Ǥ���
	var url = wikihelper_root_url + '?cmd=' + mode;
	var pars = '';
	// pars +=  'mode=get'
	pars += 'page=' + encodeURIComponent(id);
	pars += '&ajax=1';
	pars += '&encode_hint=' + encodeURIComponent("{$this->cont['PKWK_ENCODING_HINT']}");
	
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get',
			parameters: pars,
			onComplete: showResponse
		});
	
	return false;
}

// �ݥåץ��åץ�����ɥ����Ĥ���
function day_edit_close() {
	wikihelper_hide_helper();
	Element.remove($("popupback"));
	Element.remove($("popupmain"));
	return false;
}

function thisreload() {
	window.location.reload();
}

function day_focus(id) {
	//var tdobj = $(id);
	//tdobj.style.border = "red 1px solid";
	Element.setStyle($(id), {'border': 'red 1px solid'});
}

function day_unfocus(id, orgstyle) {
	//var tdobj = $(id);
	//tdobj.style.border = "#eeeeee 1px solid";
	Element.setStyle($(id), {'border': '#eeeeee 1px solid'});
}

-->
</script>
   <table class="style_calendar" cellspacing="1" border="0" summary="calendar body" style="width:98%;">
    <tr>
     <td class="style_td_caltop" colspan="7">
      <a href="{$this->root->script}?plugin=calendar9&amp;file=$r_base&amp;date=$prev_date_str">&lt;&lt;</a>
      <strong>$m_name</strong>
      <a href="{$this->root->script}?plugin=calendar9&amp;file=$r_base&amp;date=$next_date_str">&gt;&gt;</a>
EOD;
		
		if ($prefix) {
			$ret .= "\n      <br />[<a href=\"{$this->root->script}?$r_base\">$s_base</a>]";
		}
		
		$ret .= "\n     </td>\n    </tr>\n    <tr>\n";
		
		foreach($this_weeklabels as $label) {
			$ret .= "     <td class=\"style_td_week\">$label</td>\n";
		}
		
		$ret .= "    </tr>\n    <tr>\n";
		// Blank
		for ($i = 0; $i < ($wday + $diffday) % 7; $i++) {
			$ret .= "     <td class=\"style_td_blank\">&nbsp;</td>\n";
		}
		
		$next_month = false;
		while (true) {
			// ���η�Υǡ��������
			if(checkdate($m_num,$day,$year) == false) {
				$next_month = true;
				$day = 1;
				$m_num++;
				if($m_num > 12) {
					$m_num = 1;
					$year++;
				}
			}
			if($next_month == true and ($wday + $diffday) % 7 == 0) {
				break;
			}
			$dt = sprintf('%4d-%02d-%02d', $year, $m_num, $day);
			$dtnum = $year * 10000 + $m_num * 100 + $day;
			$dtkey = '';
			$titlecolor = '';
			$page = $prefix.$dt;
			$r_page = rawurlencode($page);
			$s_page = htmlspecialchars($page);
			
			if (($wday + $diffday) % 7 == 0 and $day > 1) {
				$ret .= "    </tr>\n    <tr>\n";
			}
			
			// �Хå����顼������
			$style = 'style_td_day';		// Weekday
			
			$matchMonthDay = 0;
			$matchFull = 0;
			if (array_search($dtnum % 10000, $dayoff_list) == true) {
				$matchMonthDay = 1;
				$dtkey = substr($dtnum, 4, 4);
			}
			if(array_search($dtnum, $dayoff_list) == true) {
				$matchFull = 1;
				$dtkey = $dtnum;
			}
			
			if ($wday == 0) {		// Sunday 
				$style = 'style_td_sun';
			}
			if ($wday == 6) {		//  Saturday is_page
				$style = 'style_td_sat';
			}
			if ($matchFull == 1 || $matchMonthDay == 1) {		// Day-Off
				if ($day_type[$dtkey] == 0) {
					$style = 'style_td_sun';
					$titlecolor = 'red';
				}
				else if ($day_type[$dtkey] == 2) {
					$style = 'style_td_sat';
				}
				else {
					$titlecolor = 'black';
				}
			}
			if (!$other_month && ($day == $today['mday']) && ($m_num == $today['mon']) && ($year == $today['year'])) {		// Today
				$style = 'style_td_today';
			}
			
			// �����դΥ����ȥ��������ƥ����ȥ�Τ߽��Ϥ���
			$strr = "";
			if(array_key_exists($dtkey, $day_title)) {
				$strr = '<font color="' . $titlecolor . '">' . $day_title[$dtkey] . "</font><br />\n";
			}
			
			$freeze = -1;
			
			$_page = $page;
			$i = 0;

			// ���դؤΥ��
			while($this->func->is_page($_page)) {
				$subtitle = '';
				
				// �����ȥ�����Ƥμ�����Ԥ�
				if ($this->func->check_readable($_page, false, false)) {
					foreach ($this->func->get_source($_page) as $line) {
						if($freeze == -1) {
							if(strncmp($line, "#freeze", 7) == 0) {
								$freeze = 1;
							}
							else {
								$freeze = 0;
							}
						}
						if( $line{0} === '*' || $line{0} === '#' || !trim($line)) {
							continue;
						}
						else {
							$subtitle = strip_tags($this->func->convert_html($line));
							break;
						}
					}
				
					// �ܥå����������ƥ����Ȥ�������롣
					$href = $this->func->get_page_uri($_page, true);
					$linkstr = '<a href="' . $href . '" onclick="return day_edit(\''.$_page.'\',\'read\',event);">' . $this->func->get_heading($_page) . '</a>'."\n";
					if ($this->func->check_editable($_page, false, false)) {
						$short = htmlspecialchars('Edit');
						$title = htmlspecialchars(str_replace('$1', $s_page.$page, $this->root->_title_edit));
						$icon = '<img src="' . $this->cont['IMAGE_DIR'] . 'paraedit.png' .
							'" width="9" height="9" alt="' .
							$short . '" title="' . $title . '" /> ';
						$r_page = rawurlencode($_page);
						$linkstr .= "<a href=\"{$this->root->script}?cmd=edit&amp;page=$r_page\" onclick=\"return day_edit('$_page','edit',event);\">$icon</a>";
					}
					
					if($subtitle) {
						$linkstr .= '<br />' . $subtitle;
					}
					
					$strr .= '<div style="border:solid 1px #aaaaaa;margin:1px;padding:1px;background-color:white;font-weight:normal;">' . $linkstr . '</div>';
				}
				++$i;
				$_page = $base.'/'.$dt.'-'.$i;
			}

			if ($this->func->check_editable($_page, false, false) && $freeze !== 1) {
				$r_page = rawurlencode($_page);
				$link = "<a href=\"{$this->root->script}?cmd=edit&amp;page=$r_page\" title=\"$s_page\" style=\"font-weight:bold;\">$day</a>";
				$js = " onmouseover=\"day_focus('$dt')\" onmouseout=\"day_unfocus('$dt', '$style')\" onclick=\"return day_edit('$_page')\"";
			} else {
				$link = "<span style=\"font-weight:bold;\">$day</span>";
				$js = '';
			}
			// 
			$ret .= "     <td class=\"$style\" style=\"border:#eeeeee 1px solid;width:14.2%;height:50px;text-align:left;vertical-align:top;\" id=\"$dt\"{$js}>\n      $link <div class=\"related\" style=\"margin:3px 0px 0px 0px;text-align:left;\">$strr</div>\n     </td>\n";		//���դϾ�����ɽ�����ޤ��������������ƤϾ�����Υե���Ȥ�
			$day++;
			$wday = ++$wday % 7;
		}
		
		$ret .= "    </tr>\n   </table>\n";
		
		return $ret;
	}

	function plugin_calendar9_action()
	{
		$page = $this->func->strip_bracket($this->root->vars['page']);
		$this->root->vars['page'] = '*';
		if ($this->root->vars['file'])
		{
			$this->root->vars['page'] = $this->root->vars['file'];
		}
		
		$date = $this->root->vars['date'];
		
		if ($date == '')
		{
			$date = $this->func->get_date("Ym");
		}
		$yy = sprintf("%04d.%02d",substr($date,0,4),substr($date,4,2));
		
		$aryargs = array($this->root->vars['page'],$date);
		$s_page = htmlspecialchars($this->root->vars['page']);
		
		$ret['msg'] = "calendar $s_page/$yy";
		$ret['body'] = call_user_func_array (array(& $this, "plugin_calendar9_convert"),$aryargs);
		
		$this->root->vars['page'] = $page;
		
		return $ret;
	}
}
?>