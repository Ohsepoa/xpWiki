<?php
//
// Created on 2006/10/31 by nao-pon http://hypweb.net/
// $Id: tag.inc.php,v 1.3 2006/11/02 15:59:29 nao-pon Exp $
//
if (file_exists($this->cont['CACHE_DIR'] . $this->encode($page) . '_page.tag')) {
	// �ڡ�����tag�ǡ����ե����뤬������
	
	// temp
	$_temparr = array();
	
	// tag�ץ饰���󵭽Ҥ�¸�ߥ����å�
	$_temparr['found'] = FALSE;
	$_temparr['ic'] = new XpWikiInlineConverter($this->xpwiki, array('plugin'));
	$_temparr['data'] = explode("\n",$postdata);
	while (! empty($_temparr['data'])) {
		
		$_temparr['line'] =  array_shift($_temparr['data']);
		
		// The first character
		$_temparr['head'] = $_temparr['line']{0};
		
		if (
			// Escape comments
			substr($_temparr['line'], 0, 2) === '//' ||
			// Horizontal Rule
			substr($_temparr['line'], 0, 4) === '----' ||
			// Pre
			$_temparr['head'] === ' ' || $_temparr['head'] === "\t"
		) {	continue; }

		// Multiline-enabled block plugin
		if (!$this->cont['PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK'] && preg_match('/^#[^{]+(\{\{+)\s*$/', $_temparr['line'], $matches)) {
			$_temparr['len'] = strlen($matches[1]);
			while (! empty ($_temparr['data'])) {
				$_temparr['$next_line'] = preg_replace("/[\r\n]*$/", '', array_shift($_temparr['data']));
				if (preg_match('/\}{'.$_temparr['len'].'}/', $_temparr['$next_line'])) { break; }
			}
		}
		
		// ����äȽŤ����ɤ��ä�������å���
		$_temparr['arr'] = $_temparr['ic']->get_objects($_temparr['line'], $page);
		while( ! empty($_temparr['arr']) ) {
			$_temparr['obj'] = array_shift($_temparr['arr']);
			if ( $_temparr['obj']->name === 'tag' ) {
				$_temparr['found'] = TRUE;
				break 2;
			}
		}
		
		/*
		// ����äȼ�ȴ���η�����
		if (preg_match("/&tag\([\^)]*\)(\{.*?\})?;/",$_temparr['line'])) {
			$_temparr['found'] = TRUE;
			break;
		}
		*/
		
	}
	
	if ($mode === 'delete' || ($mode === 'update' && $_temparr['found'] === FALSE)) {
		// �ڡ�������ޤ���&tag();�����������
		$_temparr['plugin'] =& $this->get_plugin_instance('tag');
		if ($_temparr['plugin'] !== FALSE) {
			$aryargs = array($page, array());
			call_user_func_array(array($this->root->plugin_tag, 'renew_tagcache'), $aryargs);
		}
	} else if ($mode === 'update' && $notimestamp) {
		// �ڡ����Խ��ǥڡ��������ॹ����פ��ݻ�������
		$this->pkwk_touch_file($this->cont['CACHE_DIR'] . $this->encode($page) . '_page.tag',1);
	}
	unset($_temparr);
}
?>