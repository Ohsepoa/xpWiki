<?php
/*
 * Created on 2008/03/24 by nao-pon http://hypweb.net/
 * $Id: attach.php,v 1.16 2009/03/03 06:45:17 nao-pon Exp $
 */

//-------- ���饹
//�ե�����
class XpWikiAttachFile
{
	var $page,$file,$age,$basename,$filename,$logname,$copyright;
	var $time = 0;
	var $size = 0;
	var $pgid = 0;
	var $time_str = '';
	var $size_str = '';
	var $owner_str = '';
	var $status = array(
			'count'    => array(0),
			'age'      => '',
			'pass'     => '',
			'freeze'   => FALSE,
			'copyright'=> FALSE,
			'owner'    => 0,
			'ucd'      => '',
			'uname'    => '',
			'md5'      => '',
			'admins'   => 0,
			'org_fname'=> '',
			'imagesize'=> NULL,
			'noinline' => 0
		);
	var $action = 'update';
	var $dbinfo = array();
	
	function XpWikiAttachFile(& $xpwiki, $page, $file, $age=0, $pgid=0)
	{
		$this->xpwiki =& $xpwiki;
		$this->root   =& $xpwiki->root;
		$this->cont   =& $xpwiki->cont;
		$this->func   =& $xpwiki->func;

		$this->page = $page;
		$this->pgid = ($pgid)? $pgid : $this->func->get_pgid_by_name($page);
		$this->file = $this->func->basename($file);
		$this->age  = is_numeric($age) ? $age : 0;
		$this->id   = $this->get_id();
		
		$this->basename = $this->cont['UPLOAD_DIR'].$this->func->encode($page).'_'.$this->func->encode($this->file);
		$this->filename = $this->basename . ($age ? '.'.$age : '');
		$this->logname = $this->basename.'.log';
		
		if ($this->id) {
			$this->get_dbinfo();
			$this->exist = TRUE;
			$this->time = $this->dbinfo['mtime'];
		} else {
			$this->exist = file_exists($this->filename);
			$this->time = $this->exist ? filemtime($this->filename) - $this->cont['LOCALZONE'] : 0;
		}
		$this->owner_id = 0;
	}
	// �ե�����������
	function getstatus()
	{
		if (!$this->exist)
		{
			return FALSE;
		}
		// ���ե��������
		if (file_exists($this->logname))
		{
			$data = array_pad(file($this->logname), count($this->status), '');
			foreach ($this->status as $key=>$value)
			{
				$this->status[$key] = chop(array_shift($data));
			}
			$this->status['count'] = explode(',',$this->status['count']);
			if (empty($this->status['org_fname'])) $this->status['org_fname'] = $this->file;
			if (is_null($this->status['imagesize']) || $this->status['imagesize'] === '') {
				$this->status['imagesize'] = @ getimagesize($this->filename);
				$this->putstatus(FALSE);
			} else {
				$this->status['imagesize'] = unserialize($this->status['imagesize']);
			}
		}
		$this->time_str = $this->func->get_date('Y/m/d H:i:s',$this->time);
		$this->size = isset($this->dbinfo['size'])? $this->dbinfo['size'] : filesize($this->filename);
		if ($this->size < 103) {
			$this->size_str = round($this->size) . 'B';
		} else if ($this->size < 1024 * 1024) {
			$this->size_str = sprintf('%01.1f',$this->size/1024,1).'KB';
		} else {
			$this->size_str = sprintf('%01.1f',$this->size/(1024*1024),1).'MB';
		}
		$this->type = isset($this->dbinfo['type'])? $this->dbinfo['type'] : xpwiki_plugin_attach::attach_mime_content_type($this->filename, $this->status);
		$this->owner_id = intval($this->status['owner']);
		$user = $this->func->get_userinfo_by_id($this->status['owner']);
		$user = htmlspecialchars($user['uname']);
		if (!$this->status['owner']) {
			if ($this->status['uname']) {
				$user = htmlspecialchars($this->status['uname']);
			}
			$user = $user . " [".$this->status['ucd'] . "]";
		}
		$this->owner_str = $user;

		return TRUE;
	}
	//���ơ�������¸
	function putstatus($dbup = TRUE)
	{
		if ($dbup) $this->update_db();
		$status = $this->status;
		$status['count'] = join(',', $status['count']);
		$status['imagesize'] = serialize($status['imagesize']);
		$fp = fopen($this->logname,'wb')
			or $this->func->die_message('cannot write '.$this->logname);
		flock($fp,LOCK_EX);
		foreach ($status as $key=>$value)
		{
			fwrite($fp,$value."\n");
		}
		fclose($fp);
	}

	// DB id ����
	function get_id() {
		return $this->func->get_attachfile_id($this->page, $this->file, $this->age);
	}
	
	// Get attachDB info
	function get_dbinfo () {
		$this->dbinfo = $this->func->get_attachdbinfo($this->id);
	}
	
	// attach DB ����
	function update_db()
	{
		if ($this->action == "insert")
		{
			$this->size = filesize($this->filename);
			$this->type = xpwiki_plugin_attach::attach_mime_content_type($this->filename, $this->status);
			$this->time = filemtime($this->filename) - $this->cont['LOCALZONE'];
		}
		$data['id']   = $this->id;
		$data['pgid'] = $this->pgid;
		$data['name'] = $this->file;
		$data['mtime'] = $this->time;
		$data['size'] = $this->size;
		$data['type'] = $this->type;
		$data['status'] = $this->status;

		$this->func->attach_db_write($data,$this->action);
		
	}
	// ���դ���Ӵؿ�
	function datecomp($a,$b)
	{
		return ($a->time == $b->time) ? 0 : (($a->time > $b->time) ? -1 : 1);
	}
	function toString($showicon,$showinfo,$mode="")
	{
		$this->getstatus();
		$param = '&amp;refer='.rawurlencode($this->page)
		       . ($this->age ? '&amp;age='.$this->age : '')
		       . '&amp;';
		$param2 = 'file='.rawurlencode($this->file);
		$title = $this->time_str.' '.$this->size_str;
		$label = ($showicon ? $this->cont['FILE_ICON'] : '').htmlspecialchars($this->status['org_fname']);
		if ($this->age) {
			if ($mode == "imglist"){
				$label = 'backup No.'.$this->age;
			} else {
				$label .= ' (backup No.'.$this->age.')';
			}
		}
		
		$info = $count = '';
		if ($showinfo) {
			$_title = str_replace('$1',rawurlencode($this->file),$this->root->_attach_messages['msg_info']);
			if (isset($this->root->vars['popup']) && $this->root->vars['cmd'] !== 'read') {
				$info = '[ &build_js(refInsert,"'.str_replace('|', '&#124;', htmlspecialchars($this->file, ENT_QUOTES)).'",'.$this->type.'); ]';
			} else {
				if ($mode == "imglist") {
					$info = "[ [[{$this->root->_attach_messages['btn_info']}:{$this->root->script}?plugin=attach&pcmd=info".str_replace("&amp;","&", ($param . $param2))."]] ]";
				} else {
					$info = "\n<span class=\"small\">[<a href=\"{$this->root->script}?plugin=attach&amp;pcmd=info{$param}{$param2}\" title=\"$_title\">{$this->root->_attach_messages['btn_info']}</a>]</span>";
				}
			}
			$count = ($showicon and !empty($this->status['count'][$this->age])) ?
				sprintf($this->root->_attach_messages['msg_count'],$this->status['count'][$this->age]) : '';
		}
		if ($mode == "imglist") {
			if ($this->age) {
				return "&size(12){".$label.$info."};";
			} else {
				return "&size(12){&ref(\"".str_replace(array('"', '|'), array('""', '&#124;'), $this->page."/".$this->file)."\"".$this->cont['ATTACH_CONFIG_REF_OPTION'].");&br();".$info."};";
			}
		} else {
			$filename = $this->status['org_fname'];
			$filename = str_replace(array(':', '*', '?', '"', '<', '>', '|'), '_', $filename);
			$filename = '/' . rawurlencode($filename);
			return "<a href=\"{$this->cont['HOME_URL']}gate.php{$filename}?way=attach&amp;_noumb{$param}open{$param2}\" title=\"{$title}\">{$label}</a>{$count}{$info}";
		}
	}
	// ����ɽ��
	function info($err) {
		
		$r_page = rawurlencode($this->page);
		$s_page = htmlspecialchars($this->page);
		$s_file = htmlspecialchars($this->file);
		$s_err = ($err == '') ? '' : '<p style="font-weight:bold">'.$this->root->_attach_messages[$err].'</p>';
		$ref = "";
		$img_info = "";
		$script = $this->func->get_script_uri();
		$pass = '';
		$msg_require = '';
		$is_editable = $this->is_owner();
		if ($this->cont['ATTACH_PASSWORD_REQUIRE'] && !$this->cont['ATTACH_UPLOAD_ADMIN_ONLY'] && !$is_editable)
		{
			$title = $this->root->_attach_messages[$this->cont['ATTACH_UPLOAD_ADMIN_ONLY'] ? 'msg_adminpass' : 'msg_password'];
			$pass = $title.': <input type="password" name="pass" size="8" />';
			$msg_require = $this->root->_attach_messages['msg_require'];
		}

		$msg_rename = '';
		if ($this->age)
		{
			$msg_freezed = '';
			$msg_delete  = '<input type="radio" id="pcmd_d" name="pcmd" value="delete" /><label for="pcmd_d">'.$this->root->_attach_messages['msg_delete'].'</label>';
			$msg_delete .= $this->root->_attach_messages['msg_require'];
			$msg_delete .= '<br />';
			$msg_freeze  = '';
		}
		else
		{
			// ���᡼���ե�����ξ��
			$isize = @getimagesize($this->filename);
			if (is_array($isize) && $isize[2] !== 4)
			{
				$img_info = "Image: {$isize[0]} x {$isize[1]} px";
				if ($is_editable && (defined('HYP_JPEGTRAN_PATH') || $isize[2] == 2))
				{
					$img_info = <<<EOD
<form action="{$script}" method="post">
 <div>
  $img_info
  <input type="hidden" name="plugin" value="attach" />
  <input type="hidden" name="refer" value="$s_page" />
  <input type="hidden" name="file" value="$s_file" />
  <input type="hidden" name="age" value="{$this->age}" />
  <input type="hidden" name="pcmd" value="rotate" />
  [ Rotate:
  <input type="radio" id="rotate90" name="rd" value="1" /> <label for="rotate90">90&deg;</label>
  <input type="radio" id="rotate180" name="rd" value="2" /> <label for="rotate180">180&deg;</label>
  <input type="radio" id="rotate270" name="rd" value="3" /> <label for="rotate270">270&deg;</label>
  $pass
  <input type="submit" value="{$this->root->_attach_messages['btn_submit']}" /> ]
 </div>
</form>
EOD;
				}
			}

			// ref�ץ饰�����ɽ��
			if ($this->func->exist_plugin_inline("ref"))
			{
				$ref .= "<dd><hr /></dd><dd>".$this->func->do_plugin_inline("ref", '"'. $this->page.'/'.str_replace('"', '""', $this->file) . '"' . $this->cont['ATTACH_CONFIG_REF_OPTION'])."</dd>\n";
			}
			
			if ($this->status['freeze'])
			{
				$msg_freezed = "<dd>{$this->root->_attach_messages['msg_isfreeze']}</dd>";
				$msg_delete = '';
				$msg_freeze  = '<input type="radio" id="pcmd_u" name="pcmd" value="unfreeze" /><label for="pcmd_u">'.$this->root->_attach_messages['msg_unfreeze'].'</label>';
				$msg_freeze .= $msg_require.'<br />';
			}
			else
			{
				$msg_freezed = '';
				$msg_delete = '<input type="radio" id="pcmd_d" name="pcmd" value="delete" /><label for="pcmd_d">'.$this->root->_attach_messages['msg_delete'].'</label>';
				$msg_delete .= $msg_require.'<br />';
				$msg_freeze  = '<input type="radio" id="pcmd_f" name="pcmd" value="freeze" /><label for="pcmd_f">'.$this->root->_attach_messages['msg_freeze'].'</label>';
				$msg_freeze .= $msg_require.'<br />';
				if ($this->cont['PLUGIN_ATTACH_RENAME_ENABLE']) {
					$msg_rename  = '<input type="radio" name="pcmd" id="_p_attach_rename" value="rename" />' .
						'<label for="_p_attach_rename">' .  $this->root->_attach_messages['msg_rename'] .
						$msg_require . '</label><br />&nbsp;&nbsp;&nbsp;&nbsp;' .
						'<label for="_p_attach_newname">' . $this->root->_attach_messages['msg_newname'] .
						':</label> ' .
						'<input type="text" name="newname" id="_p_attach_newname" size="40" value="' .
						(htmlspecialchars(empty($this->status['org_fname'])? $this->file : $this->status['org_fname'])) . '" /><br />';
				}
				if ($this->status['copyright']) {
					$msg_copyright  = '<input type="radio" id="pcmd_c" name="pcmd" value="copyright0" /><label for="pcmd_c">'.$this->root->_attach_messages['msg_copyright0'].'</label>';
				} else {
					$msg_copyright  = '<input type="radio" id="pcmd_c" name="pcmd" value="copyright1" /><label for="pcmd_c">'.$this->root->_attach_messages['msg_copyright'].'</label>';
				}
				$msg_copyright .= $msg_require.'<br />';
				if ($this->root->userinfo['admin']) {
					$allow_inlne = $this->is_allow_inline()? '1' : '-1';
					$noinline_m = $noinline = (intval($this->status['noinline']) === 0)? $allow_inlne : '0';
					if ($noinline === '0') {
						$noinline_m .= $allow_inlne;
					}
					$msg_noinline = '<input type="radio" id="pcmd_n" name="pcmd" value="noinline'.$noinline.'" /><label for="pcmd_n">'.$this->root->_attach_messages['msg_noinline'.$noinline_m].'</label>';
					$msg_noinline .= '<br />';
				} else {
					$msg_noinline = '';
				}
			}
		}
		$info = $this->toString(TRUE,FALSE);
		$copyright = ($this->status['copyright'])? ' checked=TRUE' : '';
		
		$retval = array('msg'=>sprintf($this->root->_attach_messages['msg_info'],htmlspecialchars($this->file)));
		$page_link = $this->func->make_pagelink($s_page);
		//EXIF DATA
		$exif_data = $this->func->get_exif_data($this->filename);
		$exif_tags = '';
		if ($exif_data){
			$exif_tags = "<hr>".$exif_data['title'];
			foreach($exif_data as $key => $value){
				if ($key != "title") $exif_tags .= "<br />$key: $value";
			}
		}
		$v_filename = "<dd>{$this->root->_attach_messages['msg_filename']}:".$s_file;
		if ($this->root->userinfo['admin']) {
			$v_filename .=  '<br />&nbsp;&nbsp;&nbsp;'.basename($this->filename).'</dd>';
		} else {
			$v_filename .=  '</dd>';
		}
		$v_md5hash  = ($this->status['copyright'])? "" : "<dd>{$this->root->_attach_messages['msg_md5hash']}:{$this->status['md5']}</dd>";
		if ($img_info) $img_info = "<dd>{$img_info}</dd>";
		if ($exif_tags) $exif_tags = "<dd>{$exif_tags}</dd>";
		
		$retval['body'] = <<<EOD
<p class="small">
 [<a href="{$this->root->script}?plugin=attach&amp;pcmd=list&amp;refer=$r_page">{$this->root->_attach_messages['msg_list']}</a>]
 [<a href="{$this->root->script}?plugin=attach&amp;pcmd=list">{$this->root->_attach_messages['msg_listall']}</a>]
</p>
<dl style="word-break: break-all;">
 <dt>$info</dt>
 <dd>{$this->root->_attach_messages['msg_page']}:$page_link</dd>
 {$v_filename}
 {$v_md5hash}
 <dd>{$this->root->_attach_messages['msg_filesize']}:{$this->size_str} ({$this->size} bytes)</dd>
 <dd>Content-type:{$this->type}</dd>
 <dd>{$this->root->_attach_messages['msg_date']}:{$this->time_str}</dd>
 <dd>{$this->root->_attach_messages['msg_dlcount']}:{$this->status['count'][$this->age]}</dd>
 <dd>{$this->root->_attach_messages['msg_owner']}:{$this->owner_str}</dd>
 $ref
 $img_info
 $exif_tags
 $msg_freezed
</dl>
$s_err
EOD;
		if ($is_editable || (! $this->owner_id && $pass && $this->status['uname'] !== 'System'))
		{
			$retval['body'] .= <<<EOD
<hr />
<form action="{$script}" method="post">
 <div>
  <input type="hidden" name="plugin" value="attach" />
  <input type="hidden" name="refer" value="$s_page" />
  <input type="hidden" name="file" value="$s_file" />
  <input type="hidden" name="age" value="{$this->age}" />
  <input type="hidden" name="docmd" value="1" />
  $msg_delete
  $msg_freeze
  $msg_rename
  $msg_copyright
  $msg_noinline
  $pass
  <input type="submit" value="{$this->root->_attach_messages['btn_submit']}" />
 </div>
</form>
EOD;
		}
		return $retval;
	}
	function delete($pass)
	{
		if ($this->status['freeze'])
		{
			return xpwiki_plugin_attach::attach_info('msg_isfreeze');
		}
		
		$uid = $this->func->get_pg_auther($this->root->vars['page']);
		$admin = FALSE;
		if (!$this->is_owner())
		// �����Ԥȥڡ��������Ԥȥե������ͭ�԰ʳ�
		{
			if (! $this->func->pkwk_login($pass)) {
				if (($this->cont['ATTACH_PASSWORD_REQUIRE'] and (!$pass || md5($pass) != $this->status['pass'])) || $this->status['owner'])
					return xpwiki_plugin_attach::attach_info('err_password');
				
				if ($this->cont['ATTACH_DELETE_ADMIN_ONLY'] or $this->age)
					return xpwiki_plugin_attach::attach_info('err_adminpass');
			}
		}
		else
			$admin = TRUE;

		//�Хå����å�
		if ($this->age or 
			($admin and $this->cont['ATTACH_DELETE_ADMIN_NOBACKUP']))
		{
			@unlink($this->filename);
			$this->del_thumb_files();
			$this->func->attach_db_write(array('pgid'=>$this->pgid,'name'=>$this->file),"delete");
		}
		else
		{
			do
			{
				$age = ++$this->status['age'];
			}
			while (file_exists($this->basename.'.'.$age));
			
			if (!rename($this->basename,$this->basename.'.'.$age))
			{
				// ������� why?
				return array('msg'=>$this->root->_attach_messages['err_delete']);
			}

			$this->del_thumb_files();
			
			$this->status['count'][$age] = $this->status['count'][0];
			$this->status['count'][0] = 0;
			$this->putstatus();
		}
		if ($this->func->is_page($this->page))
		{
			$this->func->pkwk_touch_file($this->func->get_filename($this->page));
			$this->func->touch_db($this->page);
		}
		
		return array('msg'=>$this->root->_attach_messages['msg_deleted'],'redirect'=>$this->root->script."?plugin=attach&pcmd=upload&page=".rawurlencode($this->page));
	}
	
	function rename($pass, $newname)
	{
		if ($this->status['freeze']) return xpwiki_plugin_attach::attach_info('msg_isfreeze');

		if (! $this->func->pkwk_login($pass)) {
			if ($this->cont['PLUGIN_ATTACH_DELETE_ADMIN_ONLY'] || $this->age) {
				return xpwiki_plugin_attach::attach_info('err_adminpass');
			} else if ($this->cont['PLUGIN_ATTACH_PASSWORD_REQUIRE'] &&
				md5($pass) != $this->status['pass']) {
				return xpwiki_plugin_attach::attach_info('err_password');
			}
		}

		$fname = xpwiki_plugin_attach::regularize_fname ($newname, $this->page);
		if ($fname !== $newname) {
			$this->status['org_fname'] = $newname;
		} else {
			$this->status['org_fname'] = '';
		}

		$newbase = $this->cont['UPLOAD_DIR'] . $this->func->encode($this->page) . '_' . $this->func->encode($fname);
		if (file_exists($newbase)) {
			return array('msg'=>$this->root->_attach_messages['err_exists']);
		}
		if (! $this->cont['PLUGIN_ATTACH_RENAME_ENABLE'] || ! rename($this->basename, $newbase)) {
			return array('msg'=>$this->root->_attach_messages['err_rename']);
		}
		
		@unlink($this->logname);
		
		//$this->rename_thumb_files($fname);
		$this->del_thumb_files();
		
		$this->file = $fname;
		$this->basename = $newbase;
		$this->filename = $this->basename;
		$this->logname  = $this->basename . '.log';
		
		$this->action = 'update';
		
		$this->putstatus();
				
		return array('msg'=>$this->root->_attach_messages['msg_renamed']);
	}

	function freeze($freeze,$pass)
	{
		$uid = $this->func->get_pg_auther($this->root->vars['page']);
		if (!$this->is_owner())
		// �����Ԥȥڡ��������Ԥȥե������ͭ�԰ʳ�
		{
			if (! $this->func->pkwk_login($pass)) {
				if (($this->cont['ATTACH_PASSWORD_REQUIRE'] and (!$pass || md5($pass) != $this->status['pass'])) || $this->status['owner'])
					return xpwiki_plugin_attach::attach_info('err_password');
			}
		}
		$this->getstatus();
		$this->status['freeze'] = $freeze;
		$this->putstatus();
		
		$param  = '&file='.rawurlencode($this->file).'&refer='.rawurlencode($this->page).
			($this->age ? '&age='.$this->age : '');
		$redirect = "{$this->root->script}?plugin=attach&pcmd=info$param";
		
		return array('msg'=>$this->root->_attach_messages[$freeze ? 'msg_freezed' : 'msg_unfreezed'],'redirect'=>$redirect);
	}
	function rotate($count,$pass)
	{
		$uid = $this->func->get_pg_auther($this->root->vars['page']);
		if (!$this->is_owner())
		// �����Ԥȥڡ��������Ԥȥե������ͭ�԰ʳ�
		{
			if (! $this->func->pkwk_login($pass)) {
				if (($this->cont['ATTACH_PASSWORD_REQUIRE'] and (!$pass || md5($pass) != $this->status['pass'])) || $this->status['owner'])
					return xpwiki_plugin_attach::attach_info('err_password');
			}
		}
		
		$filemtime = filemtime($this->filename);
		$ret = HypCommonFunc::rotateImage($this->filename, $count);
		
		if ($ret) {
			$this->del_thumb_files();
			$this->func->pkwk_touch_file($this->filename, $filemtime);
			$this->getstatus();
			$this->status['imagesize'] = @ getimagesize($this->filename);
			$this->putstatus();
		}
		
		$param  = '&file='.rawurlencode($this->file).'&refer='.rawurlencode($this->page).
			($this->age ? '&age='.$this->age : '');
		$redirect = "{$this->root->script}?plugin=attach&pcmd=info$param";
		
		return array('msg'=>$this->root->_attach_messages[$ret ? 'msg_rotated_ok' : 'msg_rotated_ng'],'redirect'=>$redirect);
	}
	function copyright($copyright,$pass)
	{
		$uid = $this->func->get_pg_auther($this->root->vars['page']);
		if (!$this->is_owner())
		// �����Ԥȥڡ��������Ԥȥե������ͭ�԰ʳ�
		{
			if (! $this->func->pkwk_login($pass)) {
				if (($this->cont['ATTACH_PASSWORD_REQUIRE'] and (!$pass || md5($pass) != $this->status['pass'])) || $this->status['owner'])
					return xpwiki_plugin_attach::attach_info('err_password');
			}
		}
		
		$this->getstatus();
		$this->status['copyright'] = $copyright;
		$this->putstatus();
		
		$param  = '&file='.rawurlencode($this->file).'&refer='.rawurlencode($this->page).
			($this->age ? '&age='.$this->age : '');
		$redirect = "{$this->root->script}?plugin=attach&pcmd=info$param";
		
		return array('msg'=>$this->root->_attach_messages[$copyright ? 'msg_copyrighted' : 'msg_uncopyrighted'],'redirect'=>$redirect);
	}
	function noinline($noinline,$pass)
	{
		$uid = $this->func->get_pg_auther($this->root->vars['page']);
		if (!$this->root->userinfo['admin'])
		// �����԰ʳ�
		{
			if (! $this->func->pkwk_login($pass)) {
				return xpwiki_plugin_attach::attach_info('err_adminpass');
			}
		}
		
		$this->getstatus();
		$this->status['noinline'] = $noinline;
		$this->putstatus();
		
		$param  = '&file='.rawurlencode($this->file).'&refer='.rawurlencode($this->page).
			($this->age ? '&age='.$this->age : '');
		$redirect = "{$this->root->script}?plugin=attach&pcmd=info$param";
		
		return array('msg'=>$this->root->_attach_messages[$noinline != 0 ? 'msg_noinlined' : 'msg_unnoinlined'],'redirect'=>$redirect);
	}

	function open()
	{
		$this->getstatus();

		// clear output buffer
		while( ob_get_level() ) {
			ob_end_clean() ;
		}
		$etag = $this->status['md5'] . ($this->status['copyright']? '1' : '0') . $this->status['noinline'];
		$expires = 'Expires: ' . gmdate( "D, d M Y H:i:s", $this->cont['UTC'] + $this->cont['BROWSER_CACHE_MAX_AGE'] ) . ' GMT';
		if ($etag == @ $_SERVER["HTTP_IF_NONE_MATCH"]) {
			header('HTTP/1.1 304 Not Modified' );
			header('Cache-Control: private');
			header('Pragma:');
			header($expires);
			exit();
		}

		if (!$this->is_owner())
		{
			if ($this->status['copyright'])
				return xpwiki_plugin_attach::attach_info('err_copyright');
		}
		$this->status['count'][$this->age]++;
		$this->putstatus();
		
		$filename = $this->status['org_fname'];

		$format = 'name="%1$s"';
		$encode = $this->cont['SOURCE_ENCODING'];
		// Care for Japanese-character-included file name
		if ($this->cont['LANG'] === 'ja') {
			switch($this->cont['UA_NAME']){
				case 'Opera':
				case 'Firefox':
					// RFC 2231 ( http://www.ietf.org/rfc/rfc2231.txt )
					$format = 'name*=%2$s\'ja\'%1$s';
					$filename = rawurlencode($filename);
					break;
				case 'MSIE':
					$filename = mb_convert_encoding($filename, 'SJIS-WIN', $this->cont['SOURCE_ENCODING']);
					break;
				default:
					if ($this->cont['SOURCE_ENCODING'] === 'UTF-8') {
						// RFC 2231 ( http://www.ietf.org/rfc/rfc2231.txt )
						$format = 'name*=%2$s\'ja\'%1$s';
						$filename = rawurlencode($filename);
					} else {
						$format = 'name="%1$s"; charset=UTF-8';
						$encode = 'UTF-8';
						$filename = mb_convert_encoding($filename, $encode, $this->cont['SOURCE_ENCODING']);
					}
				}
		}
		if (strpos(strtolower($this->root->ua), 'windows') !== FALSE) {
			$filename = str_replace(array(':', '*', '?', '"', '<', '>', '|'), '_', $filename);
		}
		$filename = sprintf($format, $filename, $encode);
		
		ini_set('default_charset','');
		mb_http_output('pass');
		
		// �����ʳ�(�����Խ�ͭ�����)�ϥ�������ɰ����ˤ���(XSS�к�)
		if ($this->is_allow_inline()) {
			header('Content-Disposition: inline; file' . $filename);
		} else 	{
			header('Content-Disposition: attachment; file' . $filename);
		}
		header('Content-Length: '.$this->size);
		header('Content-Type: '.$this->type.'; '.$filename);
		header('Last-Modified: '  . gmdate( "D, d M Y H:i:s", $this->time ) . " GMT" );
		header('Etag: '. $etag);
		header('Cache-Control: private');
		header('Pragma:');
		header($expires);

		@readfile($this->filename);
		exit;
	}

	// �����ե�����Υ���ͥ������
	function del_thumb_files(){
		$dir = opendir($this->cont['UPLOAD_DIR']."s/")
			or die('directory '.$this->cont['UPLOAD_DIR'].'s/ is not exist or not readable.');
		
		$root = $this->cont['UPLOAD_DIR']."s/".$this->func->encode($this->page).'_';
		$_file = preg_split('/(\.[a-zA-Z]+)?$/', $this->file, -1, PREG_SPLIT_DELIM_CAPTURE);
		// Check original filename extention (for Renderer mode)
		if (! $_file[1] && preg_match('/(\.[a-zA-Z]+)$/', $this->status['org_fname'], $_match)) {
			$_file[1] = $_match[1];
		} 
		$_file = $this->func->encode($_file[0]) . $_file[1];
		for ($i = 1; $i < 100; $i++)
		{
			$file = $root . $i . '_' . $_file;
			if (file_exists($file))
			{
				unlink($file);
			}
		}
	}
	
/* remove
	// �����ե�����Υ���ͥ�����͡���
	function rename_thumb_files($newname){
		$dir = opendir($this->cont['UPLOAD_DIR']."s/")
			or die('directory '.$this->cont['UPLOAD_DIR'].'s/ is not exist or not readable.');
		
		$root = $this->cont['UPLOAD_DIR']."s/".$this->func->encode($this->page).'_';
		for ($i = 1; $i < 100; $i++)
		{
			$base    = $root.$this->func->encode($i."%");
			$file    = $base.$this->func->encode($this->file);
			$newfile = $base.$this->func->encode($newname);
			if (file_exists($file))
			{
				rename($file, $newfile);
			}
		}
	}
*/
	
	// �����ԡ��ڡ��������Ԥޤ��ϥե������ͭ�Ԥ���
	function is_owner() {
		if ($this->func->is_owner($this->page)) return TRUE;
		if ($this->owner_id) {
			if ($this->root->userinfo['uid'] === $this->owner_id) return TRUE;
		} else {
			if ($this->root->userinfo['ucd'] === $this->status['ucd']) return TRUE;
		}
		return FALSE;
	}
	
	function is_allow_inline () {
		$status = $this->status;
		$noinline = intval($status['noinline']);

		$return = false;
		if ($noinline > 0) {
			$return = false;
		} else if ($noinline < 0) {
			$return = true;
		} else {
			if ($status['imagesize']) {
				if ($status['imagesize'][2] === 4 || $status['imagesize'][2] === 13) {
					// Flash �Υ���饤��ɽ�����¥����å�
					if ($this->cont['PLUGIN_REF_FLASH_INLINE'] === 3) {
						// ���٤Ƶ���
						$return = true;
					} else if ($this->cont['PLUGIN_REF_FLASH_INLINE'] === 2) {
						// ��Ͽ�桼������ͭ�Τߵ���
						if ($status['owner'] > 0) {
							$return = true;
						}
					} else if ($this->cont['PLUGIN_REF_FLASH_INLINE'] === 1) {
						// �����ͽ�ͭ�Τߵ���
						if ($status['admins']) {
							$return = true;
						}
					}
				} else {
					$return = true;
				}
			} else {
				if ($status['admins']) {
					$return = true;
				}
			}
		}
		return $return;
	}
}
	
// �ե����륳��ƥ�
class XpWikiAttachFiles
{
	var $page;
	var $pgid;
	var $files = array();
	var $count = 0;
	var $max = 50;
	var $start = 0;
	var $order = "";
	
	function XpWikiAttachFiles(& $xpwiki, $page)
	{
		$this->xpwiki =& $xpwiki;
		$this->root   =& $xpwiki->root;
		$this->cont   =& $xpwiki->cont;
		$this->func   =& $xpwiki->func;

		$this->page = $page;
		$this->is_popup = (isset($this->root->vars['popup']) && $this->root->vars['cmd'] !== 'read');
	}
	function add($file,$age)
	{
		$this->files[$file][$age] = &new XpWikiAttachFile($this->xpwiki, $this->page,$file,$age,$this->pgid);
	}
	// �ե�������������
	function toString($flat,$fromall=FALSE,$mode="")
	{
		if (!$this->func->check_readable($this->page,FALSE,FALSE))
		{
			return str_replace('$1',$this->func->make_pagelink($this->page),$this->root->_title_cannotread);
		}
		if ($flat)
		{
			return $this->to_flat();
		}
		
		$this->func->add_tag_head('attach.css');
		
		$ret = '';
		$files = array_keys($this->files);
		$navi = "";
		$pcmd = ($mode == "imglist")? "imglist" : "list";
		$pcmd2 = ($mode == "imglist")? "list" : "imglist";
		
		$otherkeys = array('cols', 'max', 'base', 'mode', 'winop', 'basedir', 'encode_hint');
		if ($this->is_popup) {
			$otherkeys[] = 'popup';
		}
		if (! isset($this->root->vars['basedir'])) {
			$this->root->vars['basedir'] = $this->root->mydirname;
		}
		$otherparm = '';
		$otherprams = array();
		foreach($otherkeys as $key) {
			if (isset($this->root->vars[$key])) {
				$otherprams[] = htmlspecialchars($key) . '=' . rawurlencode($this->root->vars[$key]);
			}
		}
		if ($otherprams) {
			$otherparm = '&amp;' . join('&amp;', $otherprams);
		}
		
		if (!$fromall)
		{
			$url = $this->root->script."?plugin=attach&amp;pcmd={$pcmd}&amp;refer=".rawurlencode($this->page).$otherparm."&amp;order=".$this->order."&amp;start=";
			$url2 = $this->root->script."?plugin=attach&amp;pcmd={$pcmd}&amp;refer=".rawurlencode($this->page).$otherparm."&amp;start=";
			$url3 = $this->root->script."?plugin=attach&amp;pcmd={$pcmd2}&amp;refer=".rawurlencode($this->page).$otherparm."&amp;order=".$this->order."&amp;start=".$this->start;
			$sort_time = ($this->order == "name")? " [ <a href=\"{$url2}0&amp;order=time\">{$this->root->_attach_messages['msg_sort_time']}</a> |" : " [ <b>{$this->root->_attach_messages['msg_sort_time']}</b> |";
			$sort_name = ($this->order == "name")? " <b>{$this->root->_attach_messages['msg_sort_name']}</b> ] " : " <a href=\"{$url2}0&amp;order=name\">{$this->root->_attach_messages['msg_sort_name']}</a> ] ";
			
			if ($this->is_popup) {
				$mode_tag = '';
			} else {
				$mode_tag = ($mode == "imglist")? "[ <a href=\"$url3\">{$this->root->_attach_messages['msg_list_view']}<a> ]":"[ <a href=\"$url3\">{$this->root->_attach_messages['msg_image_view']}</a> ]";
			}
			
			if ($this->max < $this->count)
			{
				$_start = $this->start + 1;
				$_end = $this->start + $this->max;
				$_end = min($_end,$this->count);
				$now = $this->start / $this->max + 1;
				$total = ceil($this->count / $this->max);
				$navi = array();
				for ($i=1;$i <= $total;$i++)
				{
					if ($now == $i)
						$navi[] = "<b>$i</b>";
					else
						$navi[] = "<a href=\"".$url.($i - 1) * $this->max."\">$i</a>";
				}
				$navi = join(' | ',$navi);
				
				$prev = max(0,$now - 1);
				$next = $now;
				$prev = ($prev)? "<a href=\"".$url.($prev - 1) * $this->max."\" title=\"Prev\"> <img src=\"{$this->cont['LOADER_URL']}?src=prev.png\" width=\"6\" height=\"12\" alt=\"Prev\"> </a>|" : "";
				$next = ($next < $total)? "|<a href=\"".$url.$next * $this->max."\" title=\"Next\"> <img src=\"{$this->cont['LOADER_URL']}?src=next.png\" width=\"6\" height=\"12\" alt=\"Next\"> </a>" : "";
				
				$navi = "<div class=\"page_navi\">| $navi |<br />[{$prev} $_start - $_end / ".$this->count." files {$next}]<br />{$sort_time}{$sort_name}{$mode_tag}</div>";
			}
			else if ($this->count)
			{
				$navi = "<div class=\"page_navi\">{$sort_time}{$sort_name}{$mode_tag}</div>";
			}
			else
			{
				$navi = '';
			}
		}
		$col = 1;
		$cols = (! empty($this->root->vars['cols']))? max(1, min(intval($this->root->vars['cols']), 5)) : 4;
		$mod = 0;
		foreach ($files as $file)
		{
			$_files = array();
			foreach (array_keys($this->files[$file]) as $age)
			{
				$_files[$age] = $this->files[$file][$age]->toString(FALSE,TRUE,$mode);
			}
			if (!array_key_exists(0,$_files))
			{
				$_files[0] = htmlspecialchars($file);
			}
			ksort($_files);
			$_file = $_files[0];
			unset($_files[0]);
			if ($mode == "imglist")
			{
				$ret .= "|$_file";
				if (count($_files))
				{
					$ret .= "~\n".join("~\n-",$_files);
				}
				$mod = $col % $cols;
				if ($mod === 0)
				{
					$ret .= "|\n";
					$col = 0;
				}
				$col++;
			}
			else
			{
				$ret .= " <li>$_file\n";
				if (count($_files))
				{
					$ret .= "<ul>\n<li>".join("</li>\n<li>",$_files)."</li>\n</ul>\n";
				}
				$ret .= " </li>\n";
			}
		}
		
		if ($mode == "imglist")
		{
			if ($mod) $ret .= str_repeat("|>", $cols - $mod)."|\n";
			$ret = '|' . str_repeat('CENTER:|', $cols) . "c\n".$ret;
		 	$ret = $this->func->convert_html($ret);
		} else {
			$ret = "<ul>\n$ret</ul>";
		}
		
		$select_js = $otherDir = $select = $form = '';
		if ($this->is_popup) {
			$dirs = $otherDirs = array();
			if ($handle = opendir($this->cont['MODULE_PATH'])) {
				while (false !== ($dir = readdir($handle))) {
					if (is_dir($this->cont['MODULE_PATH'].$dir) && $dir[0] !== '.' && $this->func->isXpWikiDirname($dir)) {
						$other = XpWiki::getInitedSingleton($dir);
						if ($other->isXpWiki) {
							if ($other->root->pages_for_attach) {
								list($dirs[$dir]['defaultpage']) = explode('#', $other->root->pages_for_attach);
							} else {
								$dirs[$dir]['defaultpage'] = $other->root->defaultpage;
							}
							$dirs[$dir]['title'] = $other->root->module['title'];
						}
					}
				}
			}
			if (count($dirs) > 1) {
				ksort($dirs);
				foreach($dirs as $dir => $val) {
					$defaultpage = $val['defaultpage'];
					$selected = ($dir === $this->root->mydirname)? ' selected="selected"' : '';
					if ($this->root->vars['basedir'] === $dir) {
						$defaultpage = $this->root->vars['base'];
					}
					$otherDirs[] = '<option value="' . $dir . '#' . htmlspecialchars($defaultpage) . '"' . $selected . '>' . htmlspecialchars($val['title']) . '</option>';
				}
				$otherDir = '<form>Dir: <select name="otherdir" onchange="xpwiki_dir_selector_change(this.options[this.selectedIndex].value)">' . join('', $otherDirs) . '</select></form>';
			}
			
			$otherPages = array();
			$shown = array($this->root->vars['base']);
			if ($this->root->pages_for_attach) {
				$otherPages[] = '<optgroup label="' . $this->root->_attach_messages['msg_select_useful'] . '">';
				foreach(explode('#', $this->root->pages_for_attach) as $_page) {
					if ($this->func->check_readable($_page, false, false)) {
						$selected = ($_page === $this->page)? ' selected="selected"' : '';
						$shown[] = $_page;
						$_page = htmlspecialchars($_page);
						$otherPages[] = '<option value="' . $_page . '"' . $selected . '>' . $_page . '</option>';
					}
				}
				$otherPages[] = '</optgroup>';
			}
			$query = 'SELECT p.name, count( * ) AS count FROM `' . $this->xpwiki->db->prefix($this->root->mydirname.'_pginfo') . '` p INNER JOIN `' . $this->xpwiki->db->prefix($this->root->mydirname.'_attach') . '` a ON p.pgid = a.pgid WHERE a.age =0 AND a.name != "fusen.dat" GROUP BY a.pgid ORDER BY count DESC, p.name ASC LIMIT 0 , 50';
			if ($result = $this->xpwiki->db->query($query)) {
				$otherPages[] = '<optgroup label="' . $this->root->_attach_messages['msg_select_manyitems'] . '">';
				while($row = $this->xpwiki->db->fetchRow($result)) {
					if ($this->func->check_readable($row[0], false, false)) {
						if (in_array($row[0], $shown)) continue;
						$selected = ($row[0] === $this->page)? ' selected="selected"' : '';
						$_page = htmlspecialchars($row[0]);
						$otherPages[] = '<option value="' . $_page . '"' . $selected . '>' . $_page . ' (' . $row[1] . ')</option>';
					}
				}
				$otherPages[] = '</optgroup>';
			}
			if ($otherPages) {
				if ($this->root->vars['basedir'] === $this->root->mydirname) {
					$thisPage = htmlspecialchars($this->root->vars['base']);
					$thisPage = '<option value="'.$thisPage.'">' . $thisPage . $this->root->_attach_messages['msg_select_current'] . '</option>';
				} else {
					$thisPage = '';
				}
				$select = '<form><select name="othorpage" onchange="xpwiki_file_selector_change(this.options[this.selectedIndex].value)">' . $thisPage . join('', $otherPages) . '</select></form>';
			}
			$select_js = <<<EOD
<script>
<!--
function xpwiki_file_selector_change(page) {
	if (page) {
		location.href = location.href.replace(/&refer=[^&]+/, '&refer=' + encodeURIComponent(page)).replace(/&start=[^&]+/, '');
	}
}
function xpwiki_dir_selector_change(dir) {
	if (dir) {
		var arr = dir.split('#');
		location.href = location.href.replace(/\/modules\/[^\/]+/, '/modules/' + arr[0]).replace(/&refer=[^&]+/, '&refer=' + encodeURIComponent(arr[1])).replace(/&start=[^&]+/, '');
	}
}
-->
</script>
EOD;
			
			if (empty($this->root->vars['start'])) {
				$attach =& $this->func->get_plugin_instance('attach');
				$form = $attach->attach_form($this->page);
				if ($form) $form .= '<hr />';
			}
		}
		
		$showall = ($fromall && $this->max < $this->count)? " [ <a href=\"{$this->root->script}?plugin=attach&amp;pcmd={$pcmd}&amp;refer=".rawurlencode($this->page)."\">Show All</a> ]" : "";
		$allpages = ($this->is_popup || $fromall)? "" : " [ <a href=\"{$this->root->script}?plugin=attach&amp;pcmd={$pcmd}\" />All Pages</a> ]";
		$body = $this->is_popup? $ret : "<div class=\"filelist_page\">".$this->func->make_pagelink($this->page)."<small> (".$this->count." file".(($this->count===1)?"":"s").")".$showall.$allpages."</small></div>\n$ret";
		
		return $select_js.$otherDir.$select.$form.$navi.($navi? "<hr />":"").$body.($navi? "<hr />":"")."$navi\n";
	}
	// �ե�������������(inline)
	function to_flat()
	{
		$ret = '';
		$files = array();
		foreach (array_keys($this->files) as $file)
		{
			if (array_key_exists(0,$this->files[$file]))
			{
				$files[$file] = &$this->files[$file][0];
			}
		}
		uasort($files,array('XpWikiAttachFile','datecomp'));
		
		foreach (array_keys($files) as $file)
		{
			$ret .= $files[$file]->toString(TRUE,TRUE).' ';
		}
		$more = $this->count - $this->max;
		$more = ($this->count > $this->max)? "... more ".$more." files. [ <a href=\"{$this->root->script}?plugin=attach&amp;pcmd=list&amp;refer=".rawurlencode($this->page)."\">Show All</a> ]" : "";
		return $ret.$more;
	}
}
	// �ڡ�������ƥ�
class XpWikiAttachPages
{
	var $pages = array();
	var $start = 0;
	var $max = 50;
	var $mode = "";
	var $err = 0;
	
	function XpWikiAttachPages(& $xpwiki, $page='',$age=NULL,$isbn=true,$max=50,$start=0,$fromall=FALSE,$f_order="time",$mode="")
	{
		$this->xpwiki =& $xpwiki;
		$this->root   =& $xpwiki->root;
		$this->cont   =& $xpwiki->cont;
		$this->func   =& $xpwiki->func;
		
		if (! empty($this->root->vars['max'])) {
			$max = max(1, min($max, intval($this->root->vars['max'])));
		}
		
		$this->mode = $mode;
		if ($page)
		{
			// �������¥����å�
			if (!$fromall && !$this->func->check_readable($page,false,false)) return;
			
			$this->pages[$page] = &new XpWikiAttachFiles($this->xpwiki, $page);
			
			$pgid = $this->func->get_pgid_by_name($page);
			$this->pages[$page]->pgid = $pgid;
			
			// WHERE��
			$where = array();
			$where[] = "`pgid` = {$pgid}";
			if (isset($this->root->vars['popup']) && $this->root->vars['cmd'] !== 'read') $where[] = '`name` != "fusen.dat"';
			if (!$isbn) $where[] = "`mode` != '1'";
			if (!is_null($age)) $where[] = "`age` = $age";
			//if ($mode == "imglist") $where[] = "`type` LIKE 'image%' AND `age` = 0";
			//if ($mode == "imglist") $where[] = "`age` = 0";
			$where = " WHERE ".join(' AND ',$where);
			
			// ���Υڡ�����ź�եե����������
			$query = "SELECT count(*) as count FROM `".$this->xpwiki->db->prefix($this->root->mydirname."_attach")."`{$where};";
			if (!$result = $this->xpwiki->db->query($query))
				{
					$this->err = 1;
					return;
				}
			list($_count) = $this->xpwiki->db->fetchRow($result);
			if (!$_count) return;
			
			$this->pages[$page]->count = $_count;
			$this->pages[$page]->max = $max;
			$this->pages[$page]->start = $start;
			$this->pages[$page]->order = $f_order;
			
			// �ե�����������
			$order = ($f_order == "name")? " ORDER BY name ASC" : " ORDER BY mtime DESC";
			$limit = " LIMIT {$start},{$max}";
			$query = "SELECT name,age FROM `".$this->xpwiki->db->prefix($this->root->mydirname."_attach")."`{$where}{$order}{$limit};";
			$result = $this->xpwiki->db->query($query);
			while($_row = $this->xpwiki->db->fetchRow($result))
			{
				$_file = $_row[0];
				$_age = $_row[1];
				$this->pages[$page]->add($_file,$_age);
			}
		}
		else
		{
			// WHERE��
			$where = $this->func->get_readable_where('p.');
			
			if ($where) $where = ' WHERE '.$where;
			
			// ź�եե�����Τ���ڡ������������
			$query = "SELECT DISTINCT p.pgid FROM ".$this->xpwiki->db->prefix($this->root->mydirname."_pginfo")." p INNER JOIN ".$this->xpwiki->db->prefix($this->root->mydirname."_attach")." a ON p.pgid=a.pgid{$where}";
			$result = $this->xpwiki->db->query($query);
			
			$this->count = $result ? mysql_num_rows($result) : 0;
			
			$this->max = $max;
			$this->start = $start;
			$this->order = $f_order;
			
			// �ڡ����������
			$order = ($f_order == "name")? " ORDER BY p.name ASC" : " ORDER BY p.editedtime DESC";
			$limit = " LIMIT $start,$max";
			
			$query = "SELECT DISTINCT p.name FROM ".$this->xpwiki->db->prefix($this->root->mydirname."_pginfo")." p INNER JOIN ".$this->xpwiki->db->prefix($this->root->mydirname."_attach")." a ON p.pgid=a.pgid{$where}{$order}{$limit};";
			if (!$result = $this->xpwiki->db->query($query)) echo "QUERY ERROR : ".$query;
			
			while($_row = $this->xpwiki->db->fetchRow($result))
			{
				$this->XpWikiAttachPages($this->xpwiki,$_row[0],$age,$isbn,20,0,TRUE,$f_order,$mode);
			}
		}
	}
	function toString($page='',$flat=FALSE)
	{
		if ($page !== '')
		{
			if (!array_key_exists($page,$this->pages))
			{
				return '';
			}
			return $this->pages[$page]->toString($flat,FALSE,$this->mode);
		}
		$pcmd = ($this->mode == "imglist")? "imglist" : "list";
		$pcmd2 = ($this->mode == "imglist")? "list" : "imglist";
		$url = $this->root->script."?plugin=attach&amp;pcmd={$pcmd}&amp;order=".$this->order."&amp;start=";
		$url2 = $this->root->script."?plugin=attach&amp;pcmd={$pcmd}&amp;start=";
		$url3 = $this->root->script."?plugin=attach&amp;pcmd={$pcmd2}&amp;order=".$this->order."&amp;start=".$this->start;
		$sort_time = ($this->order == "name")? " [ <a href=\"{$url2}0&amp;order=time\">{$this->root->_attach_messages['msg_sort_time']}</a> |" : " [ <b>{$this->root->_attach_messages['msg_sort_time']}</b> |";
		$sort_name = ($this->order == "name")? " <b>{$this->root->_attach_messages['msg_sort_name']}</b> ] " : " <a href=\"{$url2}0&amp;order=name\">{$this->root->_attach_messages['msg_sort_name']}</a> ] ";
		$mode_tag = ($this->mode == "imglist")? "[ <a href=\"$url3\">{$this->root->_attach_messages['msg_list_view']}<a> ]":"[ <a href=\"$url3\">{$this->root->_attach_messages['msg_image_view']}</a> ]";
		
		$_start = $this->start + 1;
		$_end = $this->start + $this->max;
		$_end = min($_end,$this->count);
		$now = $this->start / $this->max + 1;
		$total = ceil($this->count / $this->max);
		$navi = array();
		
		for ($i=1;$i <= $total;$i++)
		{
			if ($now == $i)
				$navi[] = "<b>$i</b>";
			else
				$navi[] = "<a href=\"".$url.($i - 1) * $this->max."\">$i</a>";
		}
		$navi = join(' | ',$navi);
		$prev = max(0,$now - 1);
		$next = $now;
		$prev = ($prev)? "<a href=\"".$url.($prev - 1) * $this->max."\" title=\"Prev\"> <img src=\"{$this->cont['LOADER_URL']}?src=prev.png\" width=\"6\" height=\"12\" alt=\"Prev\"> </a>|" : "";
		$next = ($next < $total)? "|<a href=\"".$url.$next * $this->max."\" title=\"Next\"> <img src=\"{$this->cont['LOADER_URL']}?src=next.png\" width=\"6\" height=\"12\" alt=\"Next\"> </a>" : "";
		$navi = "<div class=\"page_navi\">| $navi |<br />[{$prev} $_start - $_end / ".$this->count." pages {$next}]<br />{$sort_time}{$sort_name}{$mode_tag}</div>";
		
		$ret = "";
		$pages = array_keys($this->pages);
		foreach ($pages as $page)
		{
			$ret .= $this->pages[$page]->toString($flat,TRUE,$this->mode)."\n";
		}
		return "\n$navi".($navi? "<hr />":"")."\n$ret\n".($navi? "<hr />":"")."$navi\n";
		
	}
}

?>