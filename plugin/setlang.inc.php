<?php
class xpwiki_plugin_setlang extends xpwiki_plugin {
	function plugin_setlang_init () {
		// Usage:
		$this->usage_inline = 'Usage: &amp;setlang(ja|zh|cn|ko){Text};';
		// ���Ĥ������
		$this->config['accepts'] = array('ja', 'zh', 'cn', 'ko');
		// ������Ф��륹������̾
		$this->config['classes'] = array(
									'ja' => 'jp' ,
									'zh' => 'cn' ,
									'cn' => 'cn' ,
									'ko' => 'ko' ,
								   );
		// ����饤����Υƥ�ץ졼��
		$this->config['inline'] = '<span class="$class" xml:lang="$lang" lang="$lang">$body</span>';
	}
	
	function plugin_setlang_inline () {
		// �����ο�������å�
		if (func_num_args() < 2) {
			$this->usage_inline;
		}
		// �����μ���
		$args = func_get_args();
		// body��
		$body = array_pop($args);
		// �������
		$lang = $args[0];
		// ���Ĥ��줿����?
		if (! in_array($lang, $this->config['accepts'])) {
			return $this->usage_inline;
		}
		// ���饹̾
		$class = $this->config['classes'][$lang];
		
		// �ƥ�ץ졼�Ȥ��ִ����ƽ���
		return str_replace(array('$class', '$lang', '$body'), array($class, $lang, $body), $this->config['inline']);
	}
}
