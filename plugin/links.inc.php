<?php
class xpwiki_plugin_links extends xpwiki_plugin {
	// PukiWiki - Yet another WikiWikiWeb clone
	// $Id: links.inc.php,v 1.1 2006/10/13 13:17:49 nao-pon Exp $
	//
	// Update link cache plugin
	
	// Message setting
	function plugin_links_init()
	{


		$messages = array(
			'_links_messages'=>array(
				'title_update'  => '����å��幹��',
			'msg_adminpass' => '�����ԥѥ����',
			'btn_submit'    => '�¹�',
			'msg_done'      => '����å���ι�������λ���ޤ�����',
			'msg_usage'     => "
* ��������
	
	:����å���򹹿�|
	���ƤΥڡ����򥹥���󤷡�����ڡ������ɤΥڡ��������󥯤���Ƥ��뤫��Ĵ�����ơ�����å���˵�Ͽ���ޤ���
	
	* ���
	�¹ԤˤϿ�ʬ��������⤢��ޤ����¹ԥܥ���򲡤������ȡ����Ф餯���Ԥ�����������
	
	* �¹�
	�����ԥѥ���ɤ����Ϥ��ơ�[�¹�]�ܥ���򥯥�å����Ƥ���������
	"
		)
		);
		$this->func->set_plugin_messages($messages);
	}
	
	function plugin_links_action()
	{
	//	global $script, $post, $vars, $foot_explain;
	//	global $_links_messages;
	
		if ($this->cont['PKWK_READONLY']) $this->func->die_message('PKWK_READONLY prohibits this');
	
		$msg = $body = '';
		if (empty($this->root->vars['action']) || empty($this->root->post['adminpass']) || ! $this->func->pkwk_login($this->root->post['adminpass'])) {
			$msg   = & $this->root->_links_messages['title_update'];
			$body  = $this->func->convert_html($this->root->_links_messages['msg_usage']);
			$body .= <<<EOD
<form method="POST" action="{$this->root->script}">
 <div>
  <input type="hidden" name="plugin" value="links" />
  <input type="hidden" name="action" value="update" />
  <label for="_p_links_adminpass">{$this->root->_links_messages['msg_adminpass']}</label>
  <input type="password" name="adminpass" id="_p_links_adminpass" size="20" value="" />
  <input type="submit" value="{$this->root->_links_messages['btn_submit']}" />
 </div>
</form>
EOD;
	
		} else if ($this->root->vars['action'] == 'update') {
			$this->func->links_init();
			$this->root->foot_explain = array(); // Exhaust footnotes
			$msg  = & $this->root->_links_messages['title_update'];
			$body = & $this->root->_links_messages['msg_done'    ];
		} else {
			$msg  = & $this->root->_links_messages['title_update'];
			$body = & $this->root->_links_messages['err_invalid' ];
		}
		return array('msg'=>$msg, 'body'=>$body);
	}
}
?>