<?php
//
// Created on 2006/10/31 by nao-pon http://hypweb.net/
// $Id: tag.inc.php,v 1.2 2006/11/01 00:38:38 nao-pon Exp $
//
if (file_exists($this->cont['CACHE_DIR'] . $this->encode($page) . '_page.tag')) {
	// �ڡ�����tag�ǡ����ե����뤬������
	if ($mode === 'delete' || ($mode === 'update' && strpos($postdata,'&tag') === FALSE)) {
		// �ڡ�������ޤ���&tag();�����������
		$tag_plugin =& $this->get_plugin_instance('tag');
		if ($tag_plugin !== FALSE) {
			$aryargs = array($page, array());
			call_user_func_array(array($this->root->plugin_tag, 'renew_tagcache'), $aryargs);
		}
		unset($tag_plugin);
	} else if ($mode === 'update' && $notimestamp) {
		// �ڡ����Խ��ǥڡ��������ॹ����פ��ݻ�������
		$this->pkwk_touch_file($this->cont['CACHE_DIR'] . $this->encode($page) . '_page.tag',1);
	}
}
?>