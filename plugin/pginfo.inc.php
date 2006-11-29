<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: pginfo.inc.php,v 1.7 2006/11/29 14:06:51 nao-pon Exp $
//

class xpwiki_plugin_pginfo extends xpwiki_plugin {

	function plugin_pginfo_init()
	{
		// ����ե�������ɤ߹���
		$this->load_language();
	}
	
	function plugin_pginfo_action()
	{
		$pmode = (empty($this->root->post['pmode']))? '' : $this->root->post['pmode'];
		$page = (empty($this->root->vars['page']))? '' : $this->root->vars['page'];
		if (!$page) {
			// �������̥⡼�ɻ���
			if ($this->root->module['platform'] == "xoops") {
				$this->root->runmode = "xoops_admin";
			}
			return $this->show_admin_form();
		}
		
		// �ڡ��������ʡ������å�
		if (!$this->func->is_owner($page)) {
			$ret['msg'] = $this->msg['no_parmission_title'];
			$ret['body'] = $this->msg['no_parmission'];
			return $ret;
		}
		
		if ($pmode === 'setparm'){
			// ��Ͽ����
			return $this->save_parm($page);
		} else {
			// �ڡ�����θ�������ե�����
			return $this->show_page_form($page);
		}
	}
	
	
	
	// ��Ͽ����
	function save_parm ($page) {
	// inherit = 0:�Ѿ�����ʤ�, 1:�����ͷѾ�����, 2:�����Ѿ�����
	//           3:�����ͷѾ�������, 4:�����Ѿ�������
		
		// �������ɤ߹���
		$src = $this->func->get_source($page);
		
		// �ڡ��������ɤ߹���
		$pginfo = $this->func->get_pginfo($page);
		
		// ���إڡ����ΰ���
		$cpages = $this->func->get_existpages(NULL, $page.'/');
		$child_dat = array();
		$do_child = FALSE;
		
		// #pginfo �ƹ���
		if ($pginfo['einherit'] !== 4)
		{
			// �������Υڡ����Τߤ������ͤ��ä���
			$only_this = ($pginfo['einherit'] === 0)? TRUE : FALSE;
			
			$pginfo['einherit'] = (int)@$this->root->post['einherit'];
			if ($pginfo['einherit'] === 3) {
				//������
				$_pginfo = $this->func->pageinfo_inherit($page);
				$pginfo['egids'] = $_pginfo['egids'];
				$pginfo['eaids'] = $_pginfo['eaids'];
				// ���إڡ�����������
				if (!$only_this && $cpages) {
					foreach ($cpages as $_page) {
						$child_dat[$_page]['einherit'] = 3;
						$child_dat[$_page]['egids'] = $_pginfo['egids'];
						$child_dat[$_page]['eaids'] = $_pginfo['eaids'];
					}
					$do_child = TRUE;
				}
			} else {
				$egid = @$this->root->post['egid'];
				if ($egid === 'select') {
					$egid = @join('&', @$this->root->post['egids']);
					if (!$egid) {$egid = 'none';}
				}
				$pginfo['egids'] = $egid;
				
				$eaid = @$this->root->post['eaid'];
				if ($eaid === 'select') {
					$eaid = @str_replace(',', '&', @$this->root->post['eaids']);
					if (!$eaid) {$eaid = 'none';}
				}
				$pginfo['eaids'] = $eaid;
			}
			// ���إڡ����ηѾ�����
			if ($cpages) {
				if ($pginfo['einherit'] === 1 || $pginfo['einherit'] === 2) {
					foreach ($cpages as $_page) {
						$child_dat[$_page]['einherit'] = $pginfo['einherit'] + 2;
						$child_dat[$_page]['egids'] = $pginfo['egids'];
						$child_dat[$_page]['eaids'] = $pginfo['eaids'];
					}
					$do_child = TRUE;
				}
			}
		}
		
		if ($pginfo['vinherit'] !== 4)
		{
			// �������Υڡ����Τߤ������ͤ��ä���
			$only_this = ($pginfo['vinherit'] === 0)? TRUE : FALSE;

			$pginfo['vinherit'] = (int)@$this->root->post['vinherit'];
			if ($pginfo['vinherit'] === 3) {
				//������
				$_pginfo = $this->func->pageinfo_inherit($page);
				$pginfo['vgids'] = $_pginfo['vgids'];
				$pginfo['vaids'] = $_pginfo['vaids'];
				// ���إڡ�����������
				if (!$only_this && $cpages) {
					foreach ($cpages as $_page) {
						$child_dat[$_page]['vinherit'] = 3;
						$child_dat[$_page]['vgids'] = $_pginfo['vgids'];
						$child_dat[$_page]['vaids'] = $_pginfo['vaids'];
					}
					$do_child = TRUE;
				}
			} else {
				$vgid = @$this->root->post['vgid'];
				if ($vgid === 'select') {
					$vgid = @join('&', @$this->root->post['vgids']);
					if (!$vgid) {$vgid = 'none';}
				}
				$pginfo['vgids'] = $vgid;
				
				$vaid = @$this->root->post['vaid'];
				if ($vaid === 'select') {
					$vaid = @str_replace(',', '&', @$this->root->post['vaids']);
					if (!$vaid) {$vaid = 'none';}
				}
				$pginfo['vaids'] = $vaid;
			}
			if ($cpages) {
				// ���إڡ����ηѾ�����
				if ($pginfo['vinherit'] === 1 || $pginfo['vinherit'] === 2) {
					foreach ($cpages as $_page) {
						$child_dat[$_page]['vinherit'] = $pginfo['vinherit'] + 2;
						$child_dat[$_page]['vgids'] = $pginfo['vgids'];
						$child_dat[$_page]['vaids'] = $pginfo['vaids'];
					}
					$do_child = TRUE;
				}
			}
		}
		$pginfo_str = '#pginfo('.join("\t",$pginfo).')'."\n";
		
		// ��뤵��Ƥ���? #freeze ��ɬ���ե�������Ƭ
		$buf = array_shift($src);
		if (rtrim($buf) !== '#freeze') {
			array_unshift($src, $buf);
			$buf = '';
		}
		// #pginfo �����ؤ�
		$src = preg_replace("/^#pginfo\(.*\)[\r\n]*/m", '', join('', $src));
		$src = $buf . $pginfo_str . $src;		
		
		// �ڡ�����¸
		$this->func->file_write($this->cont['DATA_DIR'], $page, $src, TRUE);
		
		// pginfo DB ����
		$this->func->pginfo_perm_db_write($page, $pginfo);
		
		// ���إڡ�������
		if ($do_child) {
			$this->save_parm_child ($child_dat);
		}

		$msg  = $this->msg['done_ok'];
		$body = '';
		return array('msg'=>$msg, 'body'=>$body);

	}
	
	function save_parm_child ($dat) {
		foreach ($dat as $page=>$_pginfo) {
			// �������ɤ߹���
			$src = $this->func->get_source($page);
			
			// �ڡ��������ɤ߹���
			$pginfo = $this->func->get_pginfo($page);
			
			// �Ѿ������å� & ���
			$do = FALSE;
			if (isset($_pginfo['einherit']) && ($pginfo['einherit'] > 2 || $_pginfo['einherit'] === 4)) {
				$pginfo['einherit'] = $_pginfo['einherit'];
				$pginfo['egids'] = $_pginfo['egids'];
				$pginfo['eaids'] = $_pginfo['eaids'];				
				$do = TRUE;
			}
			if (isset($_pginfo['vinherit']) && ($pginfo['vinherit'] > 2 || $_pginfo['vinherit'] === 4)) {
				$pginfo['vinherit'] = $_pginfo['vinherit'];
				$pginfo['vgids'] = $_pginfo['vgids'];
				$pginfo['vaids'] = $_pginfo['vaids'];				
				$do = TRUE;
			}

			// ��¸	
			if ($do) {
				$pginfo_str = '#pginfo('.join("\t",$pginfo).')'."\n";
				
				// ��뤵��Ƥ���? #freeze ��ɬ���ե�������Ƭ
				$buf = array_shift($src);
				if (rtrim($buf) !== '#freeze') {
					array_unshift($src, $buf);
					$buf = '';
				}
				// #pginfo �����ؤ�
				$src = preg_replace("/^#pginfo\(.*\)[\r\n]*/m", '', join('', $src));
				$src = $buf . $pginfo_str . $src;		
				
				// �ڡ�����¸
				$this->func->file_write($this->cont['DATA_DIR'], $page, $src, TRUE);

				// pginfo DB ����
				$this->func->pginfo_perm_db_write($page, $pginfo);

			}
		}
	}
	
	// �ڡ�����θ�������ե�����
	function show_page_form ($page) {

		$this->func->add_tag_head('prototype.js');
		$this->func->add_tag_head('log.js');
		$this->func->add_tag_head('suggest.js');
		$this->func->add_tag_head('suggest.css');
		
		$pginfo = $this->func->get_pginfo($page);
		$spage = htmlspecialchars($page);
		
		$s_['einhelit'] = array_pad(array(), 4, '');
		$s_['einhelit'][$pginfo['einherit']] = ' checked="checked"';
		$s_['vinhelit'] = array_pad(array(), 4, '');
		$s_['vinhelit'][$pginfo['vinherit']] = ' checked="checked"';
		
		$efor_remove = $vfor_remove = $this->msg['for_remove'];
		$s_['edisable'] = $s_['vdisable'] = $s_['ecannot'] = $s_['vcannot'] = '';
		if ($pginfo['einherit'] === 4) {
			$s_['edisable'] = ' disabled="disabled "';
			$s_['ecannot'] = $this->msg['can_not_set'].'<br />';
			$efor_remove = '';
		}
		if ($pginfo['vinherit'] === 4) {
			$s_['vdisable'] = ' disabled="disabled "';
			$s_['vcannot'] = $this->msg['can_not_set'].'<br />';
			$vfor_remove = '';
		}
		
		foreach(array('eaids','egids','vaids','vgids') as $key) {
			$s_[$key]['all'] = $s_[$key]['none'] = $s_[$key]['select'] = '';  
			if ($pginfo[$key] === 'none' || $pginfo[$key] === 'all') {
				$$key = $pginfo[$key];
				$s_[$key][$pginfo[$key]] = ' checked="true"';
			} else {
				$$key = explode("&", $pginfo[$key]);
				$s_[$key]["select"] = ' checked="true"';
			}
		}
		$edit_group_list = $this->func->make_grouplist_form('egids', $egids, $s_['edisable']);
		$edit_user_list = '';
		if ($eaids && is_array($eaids)) {
			foreach($eaids as $eaid) {
				if ($pginfo['einherit'] === 4) {
					$edit_user_list .= htmlspecialchars($this->func->getUnameFromId($eaid)).'['.$eaid.'] '; 
				} else {
					$edit_user_list .= '<span class="exist">'.htmlspecialchars($this->func->getUnameFromId($eaid)).'['.$eaid.'] </span>'; 
				}
			}
		}
		
		$view_group_list = $this->func->make_grouplist_form('vgids', $vgids, $s_['vdisable']);
		$view_user_list = '';
		if ($eaids && is_array($vaids)) {
			foreach($vaids as $vaid) {
				if ($pginfo['vinherit'] === 4) {
					$view_user_list .= htmlspecialchars($this->func->getUnameFromId($vaid)).'['.$vaid.'] '; 
				} else {
					$view_user_list .= '<span class="exist">'.htmlspecialchars($this->func->getUnameFromId($vaid)).'['.$vaid.'] </span>'; 
				}
			}
		}

		
		$e_default = ($pginfo['einherit'] === 3)? '<p>'.$this->msg['default_inherit'].'</p>' : '';
		$v_default = ($pginfo['vinherit'] === 3)? '<p>'.$this->msg['default_inherit'].'</p>' : '';


		$ret['msg'] = $this->msg['title_permission'];
		$ret['body'] = '';
		$ret['body'] = <<<EOD
<script>
var XpWikiSuggest1 = null;
var onLoadHandler = function(){
	XpWikiSuggest1 = new XpWikiUnameSuggest('{$this->cont['HOME_URL']}','xpwiki_tag_input1','xpwiki_suggest_list1','xpwiki_tag_hidden1','xpwiki_tag_list1');
	XpWikiSuggest2 = new XpWikiUnameSuggest('{$this->cont['HOME_URL']}','xpwiki_tag_input2','xpwiki_suggest_list2','xpwiki_tag_hidden2','xpwiki_tag_list2');
};
if (window.addEventListener) {
    window.addEventListener("load", onLoadHandler, true);
} else {
    window.attachEvent("onload", onLoadHandler);
}
</script>

<form action="{$this->root->script}" method="post">
<p>
 <ul>
  <li><a href="#xpwiki_edit_parmission">{$this->msg['edit_permission']}</a></li>
  <li><a href="#xpwiki_view_parmission">{$this->msg['view_parmission']}</a></li>
 </ul>
</p>
<h2 id="xpwiki_edit_parmission">{$this->msg['edit_permission']}</h2>
<p>
 {$s_['ecannot']}
 <input name="einherit" id="_edit_permission_none" type="radio" value="3"{$s_['einhelit'][3]}{$s_['edisable']} /><label for="_edit_permission_none"> {$this->msg['permission_none']}</label><br />
</p>
{$e_default}
<h3>{$this->msg['lower_page_inherit']}</h3>
<p>
 <input name="einherit" id="_edit_inherit_default" type="radio" value="1"{$s_['einhelit'][1]}{$s_['edisable']} /><label for="_edit_inherit_default"> {$this->msg['inherit_default']}</label><br />
 <input name="einherit" id="_edit_inherit_forced" type="radio" value="2"{$s_['einhelit'][2]}{$s_['edisable']} /><label for="_edit_inherit_forced"> {$this->msg['inherit_forced']}</label><br />
 <input name="einherit" id="_edit_inherit_onlythis" type="radio" value="0"{$s_['einhelit'][0]}{$s_['edisable']} /><label for="_edit_inherit_onlythis"> {$this->msg['inherit_onlythis']}</label><br />
</p>
<h4>{$this->msg['parmission_setting']}</h4>
<table style="margin-left:2em;"><tr>
 <td>
  <input name="egid" id="_egid1" type="radio" value="all"{$s_['egids']['all']}{$s_['edisable']} /><label for="_egid1"> {$this->msg['admit_all_group']}</label><br />
  <input name="egid" id="_egid2" type="radio" value="none"{$s_['egids']['none']}{$s_['edisable']} /><label for="_egid2"> {$this->msg['not_admit_all_group']}</label><br />
  <input name="egid" id="_egid3" type="radio" value="select"{$s_['egids']['select']}{$s_['edisable']} /><label for="_egid3"> {$this->msg['admit_select_group']}</label><br />
  <div style="margin-left:2em;">{$edit_group_list}</div>
 </td>
 <td>
  <input name="eaid" id="_eaid1" type="radio" value="all"{$s_['eaids']['all']}{$s_['edisable']} /><label for="_eaid1"> {$this->msg['admit_all_user']}</label><br />
  <input name="eaid" id="_eaid2" type="radio" value="none"{$s_['eaids']['none']}{$s_['edisable']} /><label for="_eaid2"> {$this->msg['not_admit_all_user']}</label><br />
  <input name="eaid" id="_eaid3" type="radio" value="select"{$s_['eaids']['select']}{$s_['edisable']} /><label for="_eaid3"> {$this->msg['admit_select_user']}</label><br />
  <div style="margin-left:2em;">
    <div id="xpwiki_tag_list1" class="xpwiki_tag_list">{$edit_user_list}</div>
    <input type="hidden" name="eaids" id="xpwiki_tag_hidden1" value="" />
    {$this->msg['search_user']}: <input type="text" size="25" id="xpwiki_tag_input1" name="xpwiki_tag_input1" autocomplete='off' class="form_text"{$s_['edisable']} /><br />
    {$efor_remove}
    <div id='xpwiki_suggest_list1' class="auto_complete"></div>
  </div>
 </td>
</tr></table>

<hr />

<h2 id="xpwiki_view_parmission">{$this->msg['view_parmission']}</h2>
<p>
 {$s_['vcannot']}
 <input name="vinherit" id="_view_permission_none" type="radio" value="3"{$s_['vinhelit'][3]}{$s_['vdisable']} /><label for="_view_permission_none"> {$this->msg['permission_none']}</label><br />
</p>
{$v_default}
<h3>{$this->msg['lower_page_inherit']}</h3>
<p>
 <input name="vinherit" id="_view_inherit_default" type="radio" value="1"{$s_['vinhelit'][1]}{$s_['vdisable']} /><label for="_view_inherit_default"> {$this->msg['inherit_default']}</label><br />
 <input name="vinherit" id="_view_inherit_forced" type="radio" value="2"{$s_['vinhelit'][2]}{$s_['vdisable']} /><label for="_view_inherit_forced"> {$this->msg['inherit_forced']}</label><br />
 <input name="vinherit" id="_view_inherit_onlythis" type="radio" value="0"{$s_['vinhelit'][0]}{$s_['vdisable']} /><label for="_view_inherit_onlythis"> {$this->msg['inherit_onlythis']}</label><br />
</p>
<h4>{$this->msg['parmission_setting']}</h4>
<table style="margin-left:2em;"><tr>
 <td>
  <input name="vgid" id="_vgid1" type="radio" value="all"{$s_['vgids']['all']}{$s_['vdisable']} /><label for="_vgid1"> {$this->msg['admit_all_group']}</label><br />
  <input name="vgid" id="_vgid2" type="radio" value="none"{$s_['vgids']['none']}{$s_['vdisable']} /><label for="_vgid2"> {$this->msg['not_admit_all_group']}</label><br />
  <input name="vgid" id="_vgid3" type="radio" value="select"{$s_['vgids']['select']}{$s_['vdisable']} /><label for="_vgid3"> {$this->msg['admit_select_group']}</label><br />
  <div style="margin-left:2em;">{$view_group_list}</div>
 </td>
 <td>
  <input name="vaid" id="_vaid1" type="radio" value="all"{$s_['vaids']['all']}{$s_['vdisable']} /><label for="_vaid1"> {$this->msg['admit_all_user']}</label><br />
  <input name="vaid" id="_vaid2" type="radio" value="none"{$s_['vaids']['none']}{$s_['vdisable']} /><label for="_vaid2"> {$this->msg['not_admit_all_user']}</label><br />
  <input name="vaid" id="_vaid3" type="radio" value="select"{$s_['vaids']['select']}{$s_['vdisable']} /><label for="_vaid3"> {$this->msg['admit_select_user']}</label><br />
  <div style="margin-left:2em;">
    <div id="xpwiki_tag_list2" class="xpwiki_tag_list">{$view_user_list}</div>
    <input type="hidden" name="vaids" id="xpwiki_tag_hidden2" value="" />
    {$this->msg['search_user']}: <input type="text" size="25" id="xpwiki_tag_input2" name="xpwiki_tag_input2" autocomplete='off' class="form_text"{$s_['vdisable']} /><br />
    {$vfor_remove}
    <div id='xpwiki_suggest_list2' class="auto_complete"></div>
  </div>
 </td>
</tr></table>

<hr />
<input type="hidden" name="cmd" value="pginfo" />
<input type="hidden" name="page" value="{$spage}" />
<input type="hidden" name="pmode" value="setparm" />
<input id="xpwiki_parmission_submit" type="submit" value="{$this->msg['submit']}" />
</form>
EOD;
		return $ret;
	}
	
	function show_admin_form () {

	}
	
}
?>