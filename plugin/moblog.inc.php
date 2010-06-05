<?php
// $Id: moblog.inc.php,v 1.13 2010/06/05 00:45:27 nao-pon Exp $
// Author: nao-pon http://hypweb.net/
// Bace script is pop.php of mailbbs by Let's PHP!
// Let's PHP! Web: http://php.s3.to/

class xpwiki_plugin_moblog extends xpwiki_plugin {
	function plugin_moblog_init () {
		////// ɬ��������� ///////

		// �����ѥ᡼�륢�ɥ쥹
		$this->config['mail'] = '';
		// POP�����С�
		$this->config['host'] = 'localhost';
		// POP�����С����������
		$this->config['user'] = '';
		// POP�����С��ѥ����
		$this->config['pass'] = '';
		// POP�����С��ݡ����ֹ�
		$this->config['port'] = 110;

		// ���������ɥ쥹�ˤ�äƿ���ʬ����ڡ����λ���
		// �ڡ���̾���� '' ��̵��(�����Ͽ���)
		$this->config['adr2page'] = array(
		//	'�᡼�륢�ɥ쥹'   => array('�ڡ���̾', UserID�ʥ�С�),
		//	'hoge@example.com' => array('����', 1),	// ������
			'other'            => array('', 0),	    // ��Ͽ�᡼�륢�ɥ쥹�ʳ�
		);

		////// ɬ��������ܽ�λ //////

		//////////////////////////////
		///// �ʲ��Ϥ����ߤ����� /////

		// ref�ץ饰������ɲå��ץ����
		$this->config['ref'] = ',left,around,mw:320,mh:320';

		// googlemaps ���ɲå��ץ����
		$this->config['gmap'] = ',width=90%,height=300px,zoom=15,type=normal,overviewctrl=1,autozoom=1';

		// ����10��ޤ���13��ΤߤιԤ�ISBN �Ȥ��ư��������Ѵ��� (������Ѵ�̵��)
		$this->config['isbn'] = "#isbn(__ISBN__,h)\n#isbn(__ISBN__,info)";

		// �������@amazon �ΤߤιԤ��Ѵ��� (������Ѵ�̵��)
		$this->config['amazon'] = '#aws(w5,blended,__KEYWORD__)';

		// ����ź���̡ʥХ��ȡ�1�ե�����ˤĤ��ˢ�Ķ�����Τ���¸���ʤ�
		$this->config['maxbyte'] = 1048576; //1MB

		// ��ʸʸ�����¡�Ⱦ�Ѥ�
		$this->config['body_limit'] = 6000;

		// �Ǿ���ư�����ֳ֡�ʬ��
		$this->config['refresh_min'] = 5;

		// ��̾���ʤ��Ȥ�����̾
		$this->config['nosubject'] = "";

		// ���Ĥ��� Received-SPF: �إå�
		// Received-SPF: �إå����ղä��ʤ�MTA�ϡ��֥����å����ʤ��פˤ��롣
		$this->config['allow_spf'] = '';                     // �����å����ʤ�
		//$this->config['allow_spf'] = '/pass/i';              // pass �Τߵ��� (����)
		//$this->config['allow_spf'] = '/pass|none|neutral/i'; // pass, none, neutral �����

		// �������ĥ��ɥ쥹�ʥ��˵�Ͽ���ʤ���
		$this->config['deny'] = array('163.com','bigfoot.com','boss.com','yahoo-delivers@mail.yahoo.co.jp');

		// �������ĥ᡼�顼(perl�ߴ�����ɽ��)�ʥ��˵�Ͽ���ʤ���
		$this->config['deny_mailer'] = '';

		// �������ĥ����ȥ�(perl�ߴ�����ɽ��)�ʥ��˵�Ͽ���ʤ���
		$this->config['deny_title'] = '';

		// �������ĥ���饯�������å�(perl�ߴ�����ɽ��)�ʥ��˵�Ͽ���ʤ���
		$this->config['deny_lang'] = '';

		// �б�MIME�����ס�����ɽ����Content-Type: image/jpeg�θ�����ʬ��octet-stream�ϴ�����
		$this->config['subtype'] = "gif|jpe?g|png|bmp|octet-stream|x-pmd|x-mld|x-mid|x-smd|x-smaf|x-mpeg";

		// ��¸���ʤ��ե�����(����ɽ��)
		$this->config['viri'] = ".+\.exe$|.+\.zip$|.+\.pif$|.+\.scr$";

		// 25���ʾ�β����Ϻ���ʹ�����ڤ��
		$this->config['del_ereg'] = "[_]{25,}";

		// ��ʸ����������ʸ����
		$this->config['word'][] = "http://auction.msn.co.jp/";
		$this->config['word'][] = "Do You Yahoo!?";
		$this->config['word'][] = "Yahoo! BB is Broadband by Yahoo!";
		$this->config['word'][] = "http://bb.yahoo.co.jp/";

		// ź�ե᡼��Τߵ�Ͽ���롩Yes=1 No=0����ʸ�Τߤϥ��˺ܤ��ʤ���
		$this->config['imgonly'] = 0;
	}
	function plugin_moblog_action()
	{
		error_reporting(0);
		//error_reporting(E_ALL);
		$this->debug = array();
		$this->admin = $this->root->userinfo['admin'];
		//����ե������ɤ߹���
		$host = (string)$this->config['host'];
		$mail = (string)$this->config['mail'];
		$user = (string)$this->config['user'];
		$pass = (string)$this->config['pass'];
		$port = (int)$this->config['port'];
		foreach(array('mail', 'host', 'port', 'user', 'pass') as $key) {
			$_key = 'moblog_pop_' . $key;
			if (! empty($this->root->$_key)) {
				$$key = $this->root->$_key;
			}
		}

		$ref_option = (string)$this->config['ref'];
		$maxbyte = (int)$this->config['maxbyte'];
		$body_limit = (int)$this->config['body_limit'];
		$refresh_min = (int)$this->config['refresh_min'];
		$nosubject = (string)$this->config['nosubject'];
		$deny = (array)$this->config['deny'];
		$deny_mailer = (string)$this->config['deny_mailer'];
		$deny_title = (string)$this->config['deny_title'];
		$deny_lang = (string)$this->config['deny_lang'];
		$subtype = (string)$this->config['subtype'];
		$viri = (string)$this->config['viri'];
		$del_ereg = (string)$this->config['del_ereg'];
		$word = (array)$this->config['word'];
		$imgonly = (int)$this->config['imgonly'];

		if (!$host || ! $user || ! $pass) $this->plugin_moblog_output();

		$chk_file = $this->cont['CACHE_DIR']."moblog.chk";
		if (! file_exists($chk_file)) {
			touch($chk_file);
		} else if ($refresh_min * 60 > $this->cont['UTC'] - filemtime($chk_file) && empty($this->root->vars['now'])) {
			$this->plugin_moblog_output();
		} else {
			$this->func->pkwk_touch_file($chk_file);
		}

		// user_pref �ɤ߹���
		$adr2page = (array)$this->config['adr2page'];
		$user_pref_all = $this->func->get_user_pref();
		if ($user_pref_all) {
			foreach($user_pref_all as $_uid => $_dat) {
				$_dat = unserialize($_dat);
				if (! empty($_dat['moblog_base_page'])) {
					if (! empty($_dat['moblog_mail_address'])) {
						$adr2page[$_dat['moblog_mail_address']] = array($_dat['moblog_base_page'], $_uid);
					} else if (! empty($_dat['moblog_user_mail'])) {
						$adr2page[$_dat['moblog_user_mail']] = array($_dat['moblog_base_page'], $_uid);
					}
				}
			}
		}

		// wait ����
		$wait = (empty($this->root->vars['wait']))? 0 : (int)$this->root->vars['wait'];
		sleep(min(5, $wait));

		// ��³����
		$err = "";
		$num = $size = $errno = 0;
		$this->sock = fsockopen($host, $port, $err, $errno, 10) or $this->plugin_moblog_error_output('Could not connect to ' . $host . ':' . $port);
		$buf = fgets($this->sock, 512);
		if(substr($buf, 0, 3) != '+OK') {
			$this->plugin_moblog_error_output($buf);
		}
		$buf = $this->plugin_moblog_sendcmd("USER $user");
		if(substr($buf, 0, 3) != '+OK') {
			$this->plugin_moblog_error_output($buf);
		}
		$buf = $this->plugin_moblog_sendcmd("PASS $pass");
		if(substr($buf, 0, 3) != '+OK') {
			$this->plugin_moblog_error_output($buf);
		}
		$data = $this->plugin_moblog_sendcmd("STAT");//STAT -����ȥ��������� +OK 8 1234
		sscanf($data, '+OK %d %d', $num, $size);

		if ($num == "0") {
			$buf = $this->plugin_moblog_sendcmd("QUIT"); //�Х��Х�
			fclose($this->sock);
			$this->debug[] = 'No mail.';
			$this->plugin_moblog_output ();
		}

		$this->debug[] = $num . ' message(s) found.';
		// ���ʬ
		for($i=1;$i<=$num;$i++) {
			$line = $this->plugin_moblog_sendcmd("RETR $i");//RETR n -n���ܤΥ�å����������ʥإå���
			$dat[$i] = "";
			while (!ereg("^\.\r\n",$line)) {//EOF��.�ޤ��ɤ�
				$line = fgets($this->sock,512);
				$dat[$i].= $line;
			}
			$data = $this->plugin_moblog_sendcmd("DELE $i");//DELE n n���ܤΥ�å��������
		}
		$buf = $this->plugin_moblog_sendcmd("QUIT"); //�Х��Х�
		fclose($this->sock);

		for($j=1;$j<=$num;$j++) {
			$write = true;
			$subject = $from = $text = $atta = $part = $attach = $filename = $charset = '';
			$this->user_pref = array();
			$this->post_options = array();
			$filenames = array();
			$body_text = array();
			$rotate = 0;
			unset($this->root->rtf['esummary'], $this->root->rtf['twitter_update']);

			list($head, $body) = $this->plugin_moblog_mime_split($dat[$j]);

			// To:�إå���ǧ
			$treg = array();
			$to_ok = FALSE;
			if (preg_match("/^To:[ \t]*([^\r\n]+)/im", $head, $treg)){
				$treg[1] = $this->plugin_moblog_addr_search($treg[1]);
				$mail_reg = preg_quote($mail, '/');
				$mail_reg = '/' . str_replace('\\*', '[^@]*?', $mail_reg) . '/i';
				//if ($mail === $treg[1]) {
				if (preg_match($mail_reg, $treg[1])) {
					$to = $treg[1];
					$to_ok = TRUE;
				} else if (preg_match("/^X-Forwarded-To:[ \t]*([^\r\n]+)/im", $head, $treg)) {
					//if ($mail === $treg[1]) {
					$treg[1] = $this->plugin_moblog_addr_search($treg[1]);
					if (preg_match($mail_reg, $treg[1])) {
						$to = $treg[1];
						$to_ok = TRUE;
					}
				}
			}
			if (! $to_ok) {
				$write = false;
				$this->debug[] = 'Bad To: '. $to;
			}

			// Received-SPF: �Υ����å�
			if ($this->config['allow_spf']) {
				if (preg_match('/^Received-SPF:\s*([a-z]+)/im', $head, $match)) {
					if (! preg_match($this->config['allow_spf'], $match[1])) {
						$write = false;
						$this->debug[] = 'Bad SPF.';
					}
				}
			}

			// �᡼�顼�Υ����å�
			$mreg = array();
			if ($write && (preg_match("#^(X-Mailer|X-Mail-Agent):[ \t]*([^\r\n]+)#im", $head, $mreg))) {
				if ($deny_mailer){
					if (preg_match($deny_mailer,$mreg[2])) {
						$write = false;
						$this->debug[] = 'Bad mailer.';
					}
				}
			}
			// ����饯�������åȤΥ����å�
			if ($write && (preg_match('/charset\s*=\s*"?([^"\r\n]+)/i', $head, $mreg))) {
				$charset = $mreg[1];
				if ($deny_lang){
					if (preg_match($deny_lang,$charset)) {
						$write = false;
						$this->debug[] = 'Bad charset.';
					}
				}
			}
			// ���դ����
			$datereg = array();
			preg_match("#^Date:[ \t]*([^\r\n]+)#im", $head, $datereg);
			$now = strtotime($datereg[1]);
			if ($now == -1) $now = $this->cont['UTC'];

			// �����ԥ��ɥ쥹�����
			$freg = array();
			if (preg_match("#^From:[ \t]*([^\r\n]+)#im", $head, $freg)) {
				$from = $this->plugin_moblog_addr_search($freg[1]);
			} elseif (preg_match("#^Reply-To:[ \t]*([^\r\n]+)#im", $head, $freg)) {
				$from = $this->plugin_moblog_addr_search($freg[1]);
			} elseif (preg_match("#^Return-Path:[ \t]*([^\r\n]+)#im", $head, $freg)) {
				$from = $this->plugin_moblog_addr_search($freg[1]);
			}

			// ���֥������Ȥ����
			$subreg = array();
			if (preg_match("#^Subject:[ \t]*([^\r\n]+)#im", $head, $subreg)) {

				if (HypCommonFunc::get_version() >= '20081215') {
					if (! XC_CLASS_EXISTS('MobilePictogramConverter')) {
						HypCommonFunc::loadClass('MobilePictogramConverter');
					}
					$mpc =& MobilePictogramConverter::factory_common();
				}

				// ����ʸ�����
				$subject = str_replace(array("\r","\n"),"",$subreg[1]);
				// ���󥳡���ʸ���֤ζ������
				$subject = preg_replace("/\?=[\s]+?=\?/","?==?",$subject);
				$regs = array();
				while (preg_match("#(.*)=\?([^\?]+)\?B\?([^\?]+)\?=(.*)#i",$subject,$regs)) {//MIME B
					$_charset = $regs[2];
					$p_subject = base64_decode($regs[3]);
					if (isset($mpc)) {
						$p_subject = $mpc->mail2ModKtai($p_subject, $from, $_charset);
					}
					$subject = $regs[1].$p_subject.$regs[4];
				}
				$regs = array();
				while (preg_match("#(.*)=\?[^\?]+\?Q\?([^\?]+)\?=(.*)#i",$subject,$regs)) {//MIME Q
					$subject = $regs[1].quoted_printable_decode($regs[2]).$regs[3];
				}

				$subject = trim($subject);

				// ^\*\d+ ǧ�ڥ������
				$_reg = '/^\*(\d+)/i';
				if (preg_match($_reg, $subject, $match)) {
					$this->post_options['auth_code'] = $match[1];
					$subject = trim(preg_replace($_reg, '', $subject, 1));
				}

				// ��ž���ꥳ�ޥ�ɸ���
				$_reg = '/@(r|l)\b/i';
				if (preg_match($_reg, $subject, $match)) {
					$rotate = (strtolower($match[1]) == "r")? 1 : 3;
					$subject = trim(preg_replace($_reg, '', $subject, 1));
				}
				$_reg = '/\b(r|l)@/i';
				if (preg_match($_reg, $subject, $match)) {
					$rotate = (strtolower($match[1]) == "r")? 1 : 3;
					$subject = trim(preg_replace($_reg, '', $subject, 1));
				}

				// @new �����ڡ������ꥳ�ޥ�ɸ���
				$_reg = '/@new\b/i';
				if (preg_match($_reg, $subject)) {
					$this->post_options['new'] = true;
					$subject = trim(preg_replace($_reg, '', $subject, 1));
				}

				// @p\d+ �оݥڡ�������(����x�ڡ���)���ޥ�ɸ���
				$_reg = '/@p(\d+)/i';
				if (preg_match($_reg, $subject, $match)) {
					$this->post_options['page_past'] = $match[1];
					$subject = trim(preg_replace($_reg, '', $subject));
				}

				$subject = trim(mb_convert_encoding($subject,$this->cont['SOURCE_ENCODING'],"AUTO"));

				// ���������
				$_reg = '/#([^#]*)/';
				if (preg_match($_reg, $subject, $match)) {
					$_tag = trim($match[1]);
					if ($_tag) {
						$this->post_options['tag'] = $_tag;
					}
					$subject = trim(preg_replace($_reg, '', $subject, 1));
				}

				// ̤�������𥫥å�
				if ($write && $deny_title){
					if (preg_match($deny_title,$subject)) {
						$write = false;
						$this->debug[] = 'Bad title.';
					}
				}
			}

			$today = getdate($now);
			$date = sprintf("/%04d-%02d-%02d-0",$today['year'],$today['mon'],$today['mday']);

			// ���ݥ��ɥ쥹
			if ($write){
				for ($f=0; $f<count($deny); $f++) {
					if (strpos($from, $deny[$f]) !== false) {
						$write = false;
						$this->debug[] = 'Bad from addr.';
					}
				}
			}

			// ��Ͽ�оݥڡ���������
			if ($write) {
				$page = "";
				$uid = 0;
				if (!empty($adr2page[$to])) {
					$_page = (is_array($adr2page[$to]))? $adr2page[$to][0] : $adr2page[$to];
					if (is_array($adr2page[$to])) $uid = $adr2page[$to][1];
				} else if (!empty($adr2page[$from])) {
					$_page = (is_array($adr2page[$from]))? $adr2page[$from][0] : $adr2page[$from];
					if (is_array($adr2page[$from])) $uid = $adr2page[$from][1];
				} else {
					$_page = (is_array($adr2page['other']))? $adr2page['other'][0] : $adr2page['other'];
				}
				$uid = intval($uid);
				if ($_page) $page = $this->get_pagename($_page, $uid, $today);
				if ($page) {
					// userinfo ������
					$this->root->userinfo = $this->func->get_userinfo_by_id($uid);
					$this->root->userinfo['ucd'] = '';
					$this->root->cookie['name']  = '';
					$this->user_pref = $this->func->get_user_pref($uid);
					if (! empty($this->user_pref['moblog_auth_code'])) {
						if ($this->user_pref['moblog_auth_code'] != $this->post_options['auth_code']) {
							$write = false;
							$this->debug[] = 'User auth key dose not mutch.';
						}
					}
				} else {
					$write = false;
					$this->debug[] = 'Allow page not found.';
				}
			}

			if ($write) {
				// �ޥ���ѡ��Ȥʤ�ХХ�������ʬ��
				if (preg_match("#\nContent-type:.*multipart/#i",$head)) {
					$boureg = array();
					preg_match('#boundary="([^"]+)"#i', $head, $boureg);
					$body = str_replace($boureg[1], urlencode($boureg[1]), $body);
					$part = split("\r\n--".urlencode($boureg[1])."-?-?",$body);
					$boureg2 = array();
					if (preg_match('#boundary="([^"]+)"#i', $body, $boureg2)) {//multipart/altanative
						$body = str_replace($boureg2[1], urlencode($boureg2[1]), $body);
						$body = preg_replace("#\r\n--".urlencode($boureg[1])."-?-?\r\n#i","",$body);
						$part = split("\r\n--".urlencode($boureg2[1])."-?-?",$body);
					}
				} else {
					$part[0] = $dat[$j];// ���̤Υƥ����ȥ᡼��
				}

				$file_count = 0;
				foreach ($part as $multi) {
					if (! $write) break;
					@ list($m_head, $m_body) = $this->plugin_moblog_mime_split($multi);
					if (!$m_body) continue;
					$filename = '';
					$m_body = preg_replace("/\r\n\.\r\n$/", "", $m_body);
					// ����饯�������åȤΥ����å�
					if ($write && (preg_match('/charset\s*=\s*"?([^"\r\n]+)/i', $m_head, $mreg))) {
						$charset = $mreg[1];
						if ($deny_lang){
							if (preg_match($deny_lang,$charset)) {
								$write = false;
								$this->debug[] = 'Bad charset.';
							}
						}
					}
					$type = array();
					if (! preg_match("#Content-type: *([^;\n]+)#i", $m_head, $type)) continue;
					list($main, $sub) = explode("/", $type[1]);
					$sub = strtolower($sub);
					// ��ʸ��ǥ�����
					if (strtolower($main) == "text") {
						if (preg_match("#Content-Transfer-Encoding:.*base64#i", $m_head))
							$m_body = base64_decode($m_body);
						if (preg_match("#Content-Transfer-Encoding:.*quoted-printable#i", $m_head))
							$m_body = quoted_printable_decode($m_body);

						if (HypCommonFunc::get_version() >= '20081215') {
							if (! isset($mpc)) {
								if (! XC_CLASS_EXISTS('MobilePictogramConverter')) {
									HypCommonFunc::loadClass('MobilePictogramConverter');
								}
								$mpc =& MobilePictogramConverter::factory_common();
							}
							$m_body = $mpc->mail2ModKtai($m_body, $from, $charset);
						}

						$text = trim(mb_convert_encoding($m_body, $this->cont['SOURCE_ENCODING'], 'AUTO'));
						if ($sub === 'html') {
							$text = preg_replace('#<br([^>]+)?>|</?(?:p|tr|table|div)([^>]+)?>#i', "\n\n", $text);
							$text = strip_tags($text);
						}
						$text = str_replace(array("\r\n", "\r"), "\n", $text);
						$text = preg_replace("/\n{2,}/", "\n\n", $text);
						if ($write) {
							// �����ֹ���
							//$text = preg_replace("#([[:digit:]]{11})|([[:digit:]\-]{13})#", "", $text);
							// �������
							$text = preg_replace('#'.$del_ereg.'#', '', $text);
							// mac���
							$text = preg_replace("#Content-type: multipart/appledouble;[[:space:]]boundary=(.*)#","",$text);
							// ���������
							if (is_array($word)) {
								foreach ($word as $delstr) {
									$text = str_replace($delstr, "", $text);
								}
							}
							if (strlen($text) > $body_limit) $text = substr($text, 0, $body_limit)."...";
						}
						// ISBN, ASIN �Ѵ�
						if (! empty($this->config['isbn'])) {
							$isbn = $this->config['isbn'];
							$text = preg_replace('/^([A-Za-z0-9]{10}|\d{13})$/me', 'str_replace(\'__ISBN__\', \'$1\', \''.$isbn.'\')', $text);
						}

						// �������@amazon �Ѵ�
						if (! empty($this->config['amazon'])) {
							$amazon = $this->config['amazon'];
							$text = preg_replace('/^(.+)@amazon$/mei', 'str_replace(\'__KEYWORD__\', \'$1\', \''.$amazon.'\')', $text);
						}

						$body_text[$sub][] = $text;
					}
					// �ե�����̾�����
					$filereg = array();
					if (preg_match("#name=\"?([^\"\n]+)\"?#i",$m_head, $filereg)) {
						$filename = trim($filereg[1]);
						// ���󥳡���ʸ���֤ζ������
						$filename = preg_replace("/\?=[\s]+?=\?/","?==?",$filename);
						while (preg_match("#(.*)=\?iso-[^\?]+\?B\?([^\?]+)\?=(.*)#i",$filename,$regs)) {//MIME B
							$filename = $regs[1].base64_decode($regs[2]).$regs[3];
						}
						$filename = mb_convert_encoding($filename, $this->cont['SOURCE_ENCODING'], 'AUTO');
					}
					// ź�եǡ�����ǥ����ɤ�����¸
					if (preg_match("#Content-Transfer-Encoding:.*base64#i", $m_head) && preg_match('#'.$subtype.'#i', $sub)) {
						++$file_count;
						$tmp = base64_decode($m_body);
						if (!$filename) $filename = $this->cont['UTC'].'_'.$file_count.'.'.$sub;

						$save_file = $this->cont['CACHE_DIR'].$this->func->encode($filename).".tmp";

						if (strlen($tmp) < $maxbyte && $write && $this->func->exist_plugin('attach'))
						{
							$fp = fopen($save_file, "wb");
							fputs($fp, $tmp);
							fclose($fp);
							//��ž����
							if ($rotate) {
								HypCommonFunc::rotateImage($save_file, $rotate);
							}
							// �ڡ�����̵����ж��ڡ��������
							if (!$this->func->is_page($page)) {
								$this->func->make_empty_page($page, false);
							}
							$attach = $this->func->get_plugin_instance('attach');
							$res = $attach->do_upload($page,$filename,$save_file,false,null,true);
							if ($res['result']) {
								$filenames[] = $res['name'];
							}
						} else {
							$write = false;
							$this->debug[] = 'Attach not found.';
						}
					}
				}
				if ($imgonly && $attach=="") $write = false;

				$subject = trim($subject);
			}

			if (! empty($body_text['plain'])) {
				$text = join("\n\n", $body_text['plain']);
			} else if (! empty($body_text['html'])) {
				$text = join("\n\n", $body_text['html']);
			} else {
				$text = '';
			}
			// wiki�ڡ����񤭹���
			if ($write) $this->plugin_moblog_page_write($page,$subject,$text,$filenames,$ref_option,$now);
		}
		// img�����ƤӽФ�
		$this->plugin_moblog_output();
	}
	function plugin_moblog_convert() {
		$host = (string)$this->config['host'];
		$user = (string)$this->config['user'];
		$pass = (string)$this->config['pass'];
		foreach(array('host', 'user', 'pass') as $key) {
			$_key = 'moblog_pop_' . $key;
			if (! empty($this->root->$_key)) {
				$$key = $this->root->$_key;
			}
		}
		if (! $host || ! $user || ! $pass) {
			return '';
		} else {
			//POP�����С��˥����������뤿��Υ��᡼������������
			return '<div style="float:left;"><img src="' . $this->root->script . '?plugin=moblog" width="1" height="1" /></div>' . "\n";
		}
	}

	function plugin_moblog_page_write($page,$subject,$text,$filenames,$ref_option,$now) {

		$aids = $gids = $freeze = "";
		$date = "at ".date("g:i a", $now);

		$set_data = (! $this->is_newpage && $subject)?  "**$subject\n" : "----\n";
		if ($filenames) {
			$_c = count($filenames);
			$_i = 1;
			foreach($filenames as $filename) {
				$set_data .= "#ref(".$filename.$ref_option.")\n";
				if ($_c !== $_i++) {
					$set_data .= "#clear\n";
				}
			}
		}
		$set_data .= $text."\n\n".$date."\n#clear";

		// ǰ�Τ���ڡ����������
		$set_data = $this->func->remove_pginfo($set_data);

		// ����ʸ��Ĵ��
		$set_data = ltrim($set_data, "\r\n");
		$set_data = rtrim($set_data)."\n\n";

		if ($this->is_newpage) {
			//�ڡ�����������
			$auto_template_rules = array(
				'((.+)\/([^\/]+))' => array('\2/template_m', ':template_m/\2', ':template_m/\3') ,
				'(()(.+))'         => array('template_m', ':template_m/default') ,
			);
			$page_data = $this->func->auto_template($page, $auto_template_rules);
//			$template = ':template_m/' . preg_replace('/(.*)\/[^\/]+/', '$1', $page);
//			if ($this->func->is_page($template)) {
//				$page_data = rtrim(join('',$this->func->get_source($template)))."\n";
				if (strpos($page_data, '__TITLE__') !== false) {
					$page_data = str_replace('__TITLE__', $subject? $subject : 'notitle', $page_data);
				} else {
					if ($subject) $set_data = "* $subject\n" . $set_data;
				}
//			} else {
//				$page_data = '';
//			}
		} else {
			$page_data = rtrim(join('',$this->func->get_source($page)))."\n";
		}
		$page_data = $this->func->remove_pginfo($page_data);

		$this->make_googlemaps($page_data, $set_data, $subject, $date);

		if (preg_match("/\/\/ Moblog Body\n/",$page_data)) {
			$page_data = preg_split("/\/\/ Moblog Body[ \t]*\n/",$page_data,2);
			$save_data = rtrim($page_data[0]) . "\n\n" . $set_data . "// Moblog Body\n" . $page_data[1];
		} else 	{
			$save_data = $page_data . "\n" . $set_data . "// Moblog Body\n";
		}

		if (! empty($this->post_options['tag'])) {
			$p_tag = $this->func->get_plugin_instance('tag');
			if (is_object($p_tag)) {
				$old_tags = $this->func->csv_explode(',', $p_tag->get_tags($save_data, $page));
				$new_tags = $this->func->csv_explode(',', $this->post_options['tag']);
				$tags = array_unique(array_merge($old_tags, $new_tags));
				$tags = array_diff($tags, array(''));
				$p_tag->set_tags($save_data, $page, join(',', $tags));
			}
		}

		if ((! $this->is_newpage || ! $this->root->pagename_num2str) && $subject) {
			$this->root->rtf['esummary'] = $subject;
		}
		if ($this->user_pref['moblog_to_twitter']) {
			$this->root->rtf['twitter_update'] = '1';
		}

		// �ڡ�������
		$this->func->page_write($page, $save_data);
		$this->debug[] = $save_data;
		$this->debug[] = 'Page write ' . $page;
	}

	function make_googlemaps ($pagedata, & $set_data, $subject, $date) {
		if (preg_match('/pos=N([0-9.]+)E([0-9.]+)([^\s]+)(.*)$/mi', $set_data, $prm)) {
			$lats = explode('.', $prm[1]);
			$lngs = explode('.', $prm[2]);
			$lats = array_pad($lats, 4, 0);
			$lngs = array_pad($lngs, 4, 0);
			$title = (! empty($prm[4]))? trim($prm[4]) : '';
			$title = $title? $title : $date;
			$lat = $lats[0] + ($lats[1] / 60 + ((float)($lats[2] . '.' . $lats[3]) / 3600));
			$lng = $lngs[0] + ($lngs[1] / 60 + ((float)($lngs[2] . '.' . $lngs[3]) / 3600));
			$map = '';
			if (! preg_match('/^#googlemaps2/m', $pagedata)) {
				$map = "\n" . '#googlemaps2(lat=' . $lat . ',lng=' . $lng . $this->config['gmap'] . ')' . "\n";
			}
			$marker = "\n" . '-&googlemaps2_mark(' . $lat . ',' . $lng . ',"title=Marker: ' . $title . '"){' . ($subject? $subject . '&br;' : '') . '( ' . $date . ' )};' . "\n";
			$set_data = preg_replace('/^(.+pos=N[0-9.]+E[0-9.]+[^\s]+).*$/mi', $map . '$1' . $marker, $set_data);
		}
	}

	function get_pagename($base, $uid, $today) {
		$page = '';
		$date = sprintf('/%04d-%02d-%02d',$today['year'],$today['mon'],$today['mday']);
		$_page = $base . $date;

		$list = array();
		if (empty($this->post_options['new'])) {
			// uid �����פ���ڡ��������
			$options = array(
				'where' => '`uid`=\'' . $uid . '\'',
				'nochild' => true
			);
			$list = $this->func->get_existpages(FALSE, $_page, $options);
			if ($list) {
				// ��������˥�����
				natsort($list);
				$list = array_reverse($list);
			}
		}
		if ($list) {
			$count = 0;
			$check_tmp = '';
			$page_past = (! empty($this->post_options['page_past']))? $this->post_options['page_past'] : 0;
			foreach($list as $check) {
				$source = $this->func->get_source($check, true, true);
				if (preg_match('#^// Moblog Body#m', $source)) {
					$check_tmp = $check;
					if ($page_past == $count++) {
						$page = $check;
						break;
					}
				}
			}
			if (! $page && $check_tmp) {
				$page = $check_tmp;
			}
		}
		if (! $page) {
			$page = $this->check_page($_page, $uid);
			if (! $page) {
				$i = 1;
				while(! $page) {
					$_page = $base . $date . '-' . $i++;
					$page = $this->check_page($_page, $uid);
				}
			}
		}
		if ($page === true) {
			// �ڡ����Խ����¤��ʤ�
			$page = '';
		}
		return $page;
	}

	function check_page($_page, $uid) {
		$page = '';
		if (! $this->func->is_page($_page)) {
			if ($this->func->check_editable_page($_page, false, false, $uid)) {
				$page = $_page;
				$this->is_newpage = 1;
			} else {
				$page = true;
			}
//		} else if (empty($this->post_options['new'])) {
//			$pginfo = $this->func->get_pginfo($_page);
//			if ($pginfo['uid'] === $uid) {
//				$source = $this->func->get_source($_page, true, true);
//				if (preg_match('#^// Moblog Body#m', $source)) {
//					$page = $_page;
//				}
//			}
		}
		return $page;
	}

	// ���ޥ������
	function plugin_moblog_sendcmd($cmd) {
		fputs($this->sock, $cmd."\r\n");
		$buf = fgets($this->sock, 512);
		if(substr($buf, 0, 3) == '+OK') {
			return $buf;
		} else {
			$this->plugin_moblog_error_output($buf);
		}
		return false;
	}

	// �إå�����ʸ��ʬ�䤹��
	function plugin_moblog_mime_split($data) {
		$part = split("\r\n\r\n", $data, 2);
		$part[0] = preg_replace("/\r\n[\t ]+/", " ", $part[0]);
		return $part;
	}

	// �᡼�륢�ɥ쥹����Ф���
	function plugin_moblog_addr_search($addr) {
		if (preg_match('/<(.+?)>/', $addr, $match)) {
			return $match[1];
		} else {
			return $addr;
		}
	}

	// ���顼����
	function plugin_moblog_error_output($str) {
		while( ob_get_level() ) {
			ob_end_clean() ;
		}
		if ($this->admin) {
			echo 'error: ' . $str;
		} else {
			header("Content-Type: image/gif");
			HypCommonFunc::readfile($this->root->mytrustdirpath . '/skin/image/gif/poperror.gif');
		}
		exit();
	}

	// ���᡼������
	function plugin_moblog_output () {
		// clear output buffer
		while( ob_get_level() ) {
			ob_end_clean() ;
		}
		if (isset($this->root->get['debug']) && $this->admin) {
			echo 'Debug:<br />' . join('<br />', $this->debug);
		} else {
			// img�����ƤӽФ���
			header("Content-Type: image/gif");
			HypCommonFunc::readfile($this->root->mytrustdirpath . '/skin/image/gif/spacer.gif');
		}
		exit();
	}
}
?>