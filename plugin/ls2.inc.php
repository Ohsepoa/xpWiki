<?php
class xpwiki_plugin_ls2 extends xpwiki_plugin {
	function plugin_ls2_init () {


	// PukiWiki - Yet another WikiWikiWeb clone.
	//
	// $Id: ls2.inc.php,v 1.3 2006/11/28 08:24:36 nao-pon Exp $
	//
	// List plugin 2
	
	/*
	 * �۲��Υڡ����䡢���θ��Ф�(*,**,***)�ΰ�����ɽ������
	 * Usage
	 *  #ls2(pattern[,title|include|link|reverse|compact, ...],heading title)
	 *
	 * pattern  : ��ά����Ȥ��⥫��ޤ�ɬ��
	 * 'title'  : ���Ф��ΰ�����ɽ������
	 * 'include': ���󥯥롼�ɤ��Ƥ���ڡ����θ��Ф���Ƶ�Ū����󤹤�
	 * 'link   ': action�ץ饰�����ƤӽФ���󥯤�ɽ��
	 * 'reverse': �ڡ������¤ӽ��ȿž�����߽�ˤ���
	 * 'compact': ���Ф���٥��Ĵ������
	 *     PLUGIN_LS2_LIST_COMPACT��TRUE�λ���̵��(�Ѳ����ʤ�)
	 * heading title: ���Ф��Υ����ȥ����ꤹ�� (link����ꤷ�����Τ�)
		 */
	
	// ���Ф����󥫡��ν�
		$this->cont['PLUGIN_LS2_ANCHOR_PREFIX'] =  '#content_1_';
	
	// ���Ф����󥫡��γ����ֹ�
		$this->cont['PLUGIN_LS2_ANCHOR_ORIGIN'] =  0;
	
	// ���Ф���٥��Ĵ������(�ǥե������)
		$this->cont['PLUGIN_LS2_LIST_COMPACT'] =  FALSE;

	}
	
	function plugin_ls2_action()
	{
	//	global $vars, $_ls2_msg_title;
	
		$params = array();
		foreach (array('title', 'include', 'reverse') as $key)
			$params[$key] = isset($this->root->vars[$key]);
	
		$prefix = isset($this->root->vars['prefix']) ? $this->root->vars['prefix'] : '';
		$body = $this->plugin_ls2_show_lists($prefix, $params);
	
		return array('body'=>$body,
		'msg'=>str_replace('$1', htmlspecialchars($prefix), $this->root->_ls2_msg_title));
	}
	
	function plugin_ls2_convert()
	{
	//	global $script, $vars, $_ls2_msg_title;
	
		$params = array(
			'link'    => FALSE,
		'title'   => FALSE,
		'include' => FALSE,
		'reverse' => FALSE,
		'compact' => $this->cont['PLUGIN_LS2_LIST_COMPACT'],
		'_args'   => array(),
		'_done'   => FALSE
		);
	
		$args = array();
		$prefix = '';
		if (func_num_args()) {
			$args   = func_get_args();
			$prefix = array_shift($args);
		}
		if ($prefix == '') $prefix = $this->func->strip_bracket($this->root->vars['page']) . '/';
	
		foreach ($args as $key => $arg)
			$this->plugin_ls2_check_arg($arg, $key, $params);
	
		$title = (! empty($params['_args'])) ? join(',', $params['_args']) :   // Manual
			str_replace('$1', htmlspecialchars($prefix), $this->root->_ls2_msg_title); // Auto
	
		if (! $params['link'])
			return $this->plugin_ls2_show_lists($prefix, $params);
	
		$tmp = array();
		$tmp[] = 'plugin=ls2&amp;prefix=' . rawurlencode($prefix);
		if (isset($params['title']))   $tmp[] = 'title=1';
		if (isset($params['include'])) $tmp[] = 'include=1';
	
		return '<p><a href="' . $this->root->script . '?' . join('&amp;', $tmp) . '">' .
		$title . '</a></p>' . "\n";
	}
	
	function plugin_ls2_show_lists($prefix, & $params)
	{
	//	global $_ls2_err_nopages;
	
		$pages = array();
		if ($prefix != '') {
			foreach ($this->func->get_existpages(FALSE, $prefix) as $_page)
				//if (strpos($_page, $prefix) === 0)
					$pages[] = $_page;
		} else {
			$pages = $this->func->get_existpages();
		}
	
		natcasesort($pages);
		if ($params['reverse']) $pages = array_reverse($pages);
	
		foreach ($pages as $page) $params["page_$page"] = 0;
	
		if (empty($pages)) {
			return str_replace('$1', htmlspecialchars($prefix), $this->root->_ls2_err_nopages);
		} else {
			$params['result'] = $params['saved'] = array();
			foreach ($pages as $page)
				$this->plugin_ls2_get_headings($page, $params, 1);
			return join("\n", $params['result']) . join("\n", $params['saved']);
		}
	}
	
	function plugin_ls2_get_headings($page, & $params, $level, $include = FALSE)
	{
	//	global $script;
	//	static $_ls2_anchor = 0;
		static $_ls2_anchor = array();
		if (!isset($_ls2_anchor[$this->xpwiki->pid])) {$_ls2_anchor[$this->xpwiki->pid] = 0;}
	
		// �ڡ�����̤ɽ���ΤȤ�
		$is_done = (isset($params["page_$page"]) && $params["page_$page"] > 0);
		if (! $is_done) $params["page_$page"] = ++$_ls2_anchor[$this->xpwiki->pid];
	
		$r_page = rawurlencode($page);
		$s_page = htmlspecialchars($page);
		$title  = $s_page . ' ' . $this->func->get_pg_passage($page, FALSE);
		$href   = $this->root->script . '?cmd=read&amp;page=' . $r_page;
	
		$this->plugin_ls2_list_push($params, $level);
		$ret = $include ? '<li>include ' : '<li>';
	
		if ($params['title'] && $is_done) {
			$ret .= '<a href="' . $href . '" title="' . $title . '">' . $s_page . '</a> ';
			$ret .= '<a href="#list_' . $params["page_$page"] . '"><sup>&uarr;</sup></a>';
			array_push($params['result'], $ret);
			return;
		}
	
		$ret .= '<a id="list_' . $params["page_$page"] . '" href="' . $href .
		'" title="' . $title . '">' . $s_page . '</a>';
		array_push($params['result'], $ret);
	
		$anchor = $this->cont['PLUGIN_LS2_ANCHOR_ORIGIN'];
		$matches = array();
		foreach ($this->func->get_source($page) as $line) {
			if ($params['title'] && preg_match('/^(\*{1,6})/', $line, $matches)) {
				$id    = $this->func->make_heading($line);
				$level = strlen($matches[1]);
				$id    = $this->cont['PLUGIN_LS2_ANCHOR_PREFIX'] . $anchor++;
				$this->plugin_ls2_list_push($params, $level + strlen($level));
				array_push($params['result'],
				'<li><a href="' . $href . $id . '">' . $line . '</a>');
			} else if ($params['include'] &&
				preg_match('/^#include\((.+)\)/', $line, $matches) &&
				$this->func->is_page($matches[1]))
			{
				$this->plugin_ls2_get_headings($matches[1], $params, $level + 1, TRUE);
			}
		}
	}
	
	//�ꥹ�ȹ�¤���ۤ���
	function plugin_ls2_list_push(& $params, $level)
	{
	//	global $_ul_left_margin, $_ul_margin, $_list_pad_str;
	
		$result = & $params['result'];
		$saved  = & $params['saved'];
		$cont   = TRUE;
		$open   = '<ul%s>';
		$close  = '</li></ul>';
	
		while (count($saved) > $level || (! empty($saved) && $saved[0] != $close))
			array_push($result, array_shift($saved));
	
		$margin = $level - count($saved);
	
		// count($saved)�����䤹
		while (count($saved) < ($level - 1)) array_unshift($saved, '');
	
		if (count($saved) < $level) {
			$cont = FALSE;
			array_unshift($saved, $close);
	
			$left = ($level == $margin) ? $this->root->_ul_left_margin : 0;
			if ($params['compact']) {
				$left  += $this->root->_ul_margin;   // �ޡ���������
				$level -= ($margin - 1); // ��٥����
			} else {
				$left += $margin * $this->root->_ul_margin;
			}
			$str = sprintf($this->root->_list_pad_str, $level, $left, $left);
			array_push($result, sprintf($open, $str));
		}
	
		if ($cont) array_push($result, '</li>');
	}
	
	// ���ץ�������Ϥ���
	function plugin_ls2_check_arg($value, $key, & $params)
	{
		if ($value == '') {
			$params['_done'] = TRUE;
			return;
		}
	
		if (! $params['_done']) {
			foreach (array_keys($params) as $param) {
				if (strtolower($value)  == $param &&
				    preg_match('/^[a-z]/', $param)) {
					$params[$param] = TRUE;
					return;
				}
			}
			$params['_done'] = TRUE;
		}
	
		$params['_args'][] = htmlspecialchars($value); // Link title
	}
}
?>