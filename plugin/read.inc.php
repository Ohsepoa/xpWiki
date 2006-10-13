<?php
class xpwiki_plugin_read extends xpwiki_plugin {
	function plugin_read_init () {



	}
	// PukiWiki - Yet another WikiWikiWeb clone.
	// $Id: read.inc.php,v 1.1 2006/10/13 13:17:49 nao-pon Exp $
	//
	// Read plugin: Show a page and InterWiki
	
	function plugin_read_action()
	{
	//	global $vars, $_title_invalidwn, $_msg_invalidiwn;
	
		$page = isset($this->root->vars['page']) ? $this->root->vars['page'] : '';
	
		if ($this->func->is_page($page)) {
			// �ڡ�����ɽ��
			$this->func->check_readable($page, true, true);
			$this->func->header_lastmod($page);
			return array('msg'=>'', 'body'=>'');
	
		} else if (! $this->cont['PKWK_SAFE_MODE'] && $this->func->is_interwiki($page)) {
			return $this->func->do_plugin_action('interwiki'); // InterWikiName�����
	
		} else if ($this->func->is_pagename($page)) {
			$this->root->vars['cmd'] = 'edit';
			return $this->func->do_plugin_action('edit'); // ¸�ߤ��ʤ��Τǡ��Խ��ե������ɽ��
	
		} else {
			// ̵���ʥڡ���̾
			return array(
				'msg'=>$this->root->_title_invalidwn,
			'body'=>str_replace('$1', htmlspecialchars($page),
				str_replace('$2', 'WikiName', $this->root->_msg_invalidiwn))
			);
		}
	}
}
?>