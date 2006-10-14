<?php
class xpwiki_plugin_amazon extends xpwiki_plugin {
	
	/////////////////////////////////////////////////
	
	function plugin_amazon_init()
	{

	// PukiWiki - Yet another WikiWikiWeb clone.
	// $Id: amazon.inc.php,v 1.2 2006/10/14 15:39:12 nao-pon Exp $
	// Id: amazon.inc.php,v 1.1 2003/07/24 13:00:00 �׼�
	//
	// Amazon plugin: Book-review maker via amazon.com/amazon.jp
	//
	// Copyright:
	//	2004-2005 PukiWiki Developers Team
	//	2003 �׼� <raku@rakunet.org> (Original author)
	//
	// License: GNU/GPL
	//
	// ChangeLog:
	// * 2004/04/03 PukiWiki Developer Team (arino <arino@users.sourceforge.jp>)
	//        - replace plugin_amazon_get_page().
	//        - PLUGIN_AMAZON_XML 'xml.amazon.com' -> 'xml.amazon.co.jp'
	// * 0.6  URL ��¸�ߤ��ʤ���硢No image ��ɽ�����������֤ʤɽ�����
	//        ����饤��ץ饰����θƤӽФ���������
	//	  ASIN �ֹ���ʬ������å����롣
	//	  �����������ȥ�Υ���å���ˤ��®�٤��������åס�
	// * 0.7  �֥å���ӥ塼�����ΥǥХå���ǧ������ΰ���Υ��ꥢ��
	// * 0.8  amazon �����ʤβ�����ɽ����
	//	  ������������ ID ���б���
	// * 0.9  RedHat9+php4.3.2+apache2.0.46 �ǲ���������ޤǤ����ɤ߹��ޤ�ʤ�������н衣
	//        ���ܸ�ڡ����β��˥֥å���ӥ塼�����Ȥ����ʸ���������ƺ��ʤ�����β�衣
	//        ���ҤǤʤ� CD �ʤɡ�ASIN ��ʬ��Ĺ���Ƥ⥿���ȥ�򤦤ޤ������褦�ˤ��롣
	//        �̱ƤΤ߼�����ΤǤʤ���С�B000002G6J.01 �Ƚ񤫤� B000002G6J �Ƚ񤤤Ƥ�̱Ƥ��Ф�褦�ˤ��롣
	//	  ASIN ���б����륭��å������/����å��奿���ȥ�򤽤줾�������뵡ǽ�ɲá�
	//	  proxy �б�(�Ū)��
	//	  proxy �����β����ǰ��̥桼���Τ���� AID �Ϥʤ��Ȥ⼫ư��������뤳�Ȥ��狼�ꡢ���������
	// * 1.0  �֥å���ӥ塼�Ǥʤ�����ӥ塼�Ȥ��롣
	//        �����Υ���å������������¤��ߤ��롣
	//        �����ȥ롢�̱Ƥ� Web Services �� XML ������������ˡ�ˤ�ä� get ���뤳�Ȥǻ��֤�û�̤��롣
	//        ��ӥ塼�ڡ��������Υ����ߥ󥰤ˤĤ����������롣
	// * 1.1  �Խ����¤򤫤��Ƥ����硢�����Ԥ���ӥ塼�����Ȥ��ơ��ڡ����ϤǤ��ʤ��� ASIN4774110655.tit �ʤɤΥ���å��夬�Ǥ���Τ��衣
	//        �����κǸ夬 01 �ξ�硢image ��������� noimage.jpg �ȤʤäƤ��ޤ��Х�������
	//        1.0 ��Ƴ������ XML ���������Ϲ�®�������֤��������󤬥����ʤΤǡ�09 ������ʤ� 01 ��ȥ饤���롢�ǻ���Ū�˲�衣
	//
	// Caution!:
	// * �������Ϣ����١�www.amazon.co.jp �Υ����������ȥץ������ǧ�ξ头���Ѳ�������
	// * ��ӥ塼�ϡ�amazon �ץ饰���󤬸ƤӽФ��Խ����̤Ϥ⤦����� PukiWiki ����Ͽ����Ƥ���Τǡ�
	//   ��ߤ���ʤ���ʸ�������ƥڡ����ι����ܥ���򲡤����ȡ�
	// * ���� PLUGIN_AMAZON_AID��PROXY �����Ф���ʬ��expire ����ʬ��Ŭ�����Խ����ƻ��Ѥ��Ƥ�������(¾�Ϥ��ΤޤޤǤ� Ok)��
	//
	// Thanks to: Reimy and PukiWiki Developers Team
	//
	
	/////////////////////////////////////////////////
	// Settings
	
	// Amazon associate ID
	//define('PLUGIN_AMAZON_AID',''); // None
		$this->cont['PLUGIN_AMAZON_AID'] = '';
	
	// Expire caches per ? days
		$this->cont['PLUGIN_AMAZON_EXPIRE_IMAGECACHE'] =    1;
		$this->cont['PLUGIN_AMAZON_EXPIRE_TITLECACHE'] =  356;
	
	// Alternative image for 'Image not found'
		$this->cont['PLUGIN_AMAZON_NO_IMAGE'] =  $this->cont['IMAGE_DIR'] . 'noimage.png';
	
	// URI prefixes
		switch($this->cont['LANG']){
		case 'ja':
		// Amazon shop
			$this->cont['PLUGIN_AMAZON_SHOP_URI'] =  'http://www.amazon.co.jp/exec/obidos/ASIN/';
	
		// Amazon information inquiry (dev-t = default value in the manual)
			$this->cont['PLUGIN_AMAZON_XML'] =  'http://xml.amazon.co.jp/onca/xml3?t=webservices-20&' .
		'dev-t=GTYDRES564THU&type=lite&page=1&f=xml&locale=jp&AsinSearch=';
			break;
		default:
		// Amazon shop
			$this->cont['PLUGIN_AMAZON_SHOP_URI'] =  'http://www.amazon.com/exec/obidos/ASIN/';
	
		// Amazon information inquiry (dev-t = default value in the manual)
			$this->cont['PLUGIN_AMAZON_XML'] =  'http://xml.amazon.com/onca/xml3?t=webservices-20&' .
		'dev-t=GTYDRES564THU&type=lite&page=1&f=xml&locale=us&AsinSearch=';
			break;
		}

	//	global $amazon_aid, $amazon_body;
	
		if ($this->cont['PLUGIN_AMAZON_AID'] == '') {
			$this->root->amazon_aid = '';
		} else {
			$this->root->amazon_aid = $this->cont['PLUGIN_AMAZON_AID'] . '/';
		}
		$this->root->amazon_body = <<<EOD
-���: [[�����Խ��Τ���]]
-ɾ��: ��̾��
-����: &date;
**�������о�
[[�����Խ��Τ���]]

#amazon(,clear)
**����
[[�����Խ��Τ���]]

// �ޤ������Υ�ӥ塼��ߤ���硢��ʸ���������ڡ�����[�����ܥ���]�򲡤��Ƥ���������(PukiWiki �ˤϤ⤦��Ͽ����Ƥ��ޤ�)
// ³����ʤ顢��Ρ�[[�����Խ��Τ���]]��ʬ���̤�ޤ�ƺ��������ľ���Ƥ���������
// ��̾������ʬ�Ϥ���ʬ��̾�����ѹ����Ƥ�������������ȡ��׼ˡ��Ǥ���
// **�������оݡ�����ϡ��������Ԥ��ɲä��ʤ��Ǥ����������ܼ������˻��Ѥ���Τǡ�
// //�ǻϤޤ륳���ȹԤϡ��ǽ�Ū���������åȤ��Ƥ����������ܼ�������˺����Ǥ��ʤ���ǽ��������ޤ���
#comment
EOD;
	}
	
	function plugin_amazon_convert()
	{
	//	global $script, $vars, $asin, $asin_all;
	
		if (func_num_args() > 3) {
			if ($this->cont['PKWK_READONLY']) return ''; // Show nothing
	
			return '#amazon([ASIN-number][,left|,right]' .
			'[,book-title|,image|,delimage|,deltitle|,delete])';
	
		} else if (func_num_args() == 0) {
			// ��ӥ塼����
			if ($this->cont['PKWK_READONLY']) return ''; // Show nothing
	
			$s_page = htmlspecialchars($this->root->vars['page']);
			if ($s_page == '') $s_page = isset($this->root->vars['refer']) ? $this->root->vars['refer'] : '';
			$ret = <<<EOD
<form action="{$this->root->script}" method="post">
 <div>
  <input type="hidden" name="plugin" value="amazon" />
  <input type="hidden" name="refer" value="$s_page" />
  ASIN:
  <input type="text" name="asin" size="30" value="" />
  <input type="submit" value="��ӥ塼�Խ�" /> (ISBN 10 �� or ASIN 12 ��)
 </div>
</form>
EOD;
			return $ret;
		}
	
		$aryargs = array_pad(func_get_args(),3,"");
	
		$align = strtolower($aryargs[1]);
		if ($align == 'clear') return '<div style="clear:both"></div>'; // ��������
		if ($align != 'left') $align = 'right'; // ���ַ���
	
		$this->root->asin_all = htmlspecialchars($aryargs[0]);  // for XSS
		if ($this->is_asin() == FALSE && $align != 'clear') return FALSE;
	
		if ($aryargs[2] != '') {
			// �����ȥ����
			$title = $alt = htmlspecialchars($aryargs[2]); // for XSS
			if ($alt == 'image') {
				$alt = $this->plugin_amazon_get_asin_title();
				if ($alt == '') return FALSE;
				$title = '';
			} else if ($alt == 'delimage') {
				if (unlink($this->cont['CACHE_DIR'] . 'ASIN' . $this->root->asin . '.jpg')) {
					return 'Image of ' . $this->root->asin . ' deleted...';
				} else {
					return 'Image of ' . $this->root->asin . ' NOT DELETED...';
				}
			} elseif ($alt == 'deltitle') {
				if (unlink($this->cont['CACHE_DIR'] . 'ASIN' . $this->root->asin . '.tit')) {
					return 'Title of ' . $this->root->asin . ' deleted...';
				} else {
					return 'Title of ' . $this->root->asin . ' NOT DELETED...';
				}
			} elseif ($alt == 'delete') {
				if ((unlink($this->cont['CACHE_DIR'] . 'ASIN' . $this->root->asin . '.jpg') &&
				     unlink($this->cont['CACHE_DIR'] . 'ASIN' . $this->root->asin . '.tit'))) {
					return 'Title and Image of ' . $this->root->asin . ' deleted...';
				} else {
					return 'Title and Image of ' . $this->root->asin . ' NOT DELETED...';
				}
			}
		} else {
			// �����ȥ뼫ư����
			$alt = $title = $this->plugin_amazon_get_asin_title();
			if ($alt == '') return FALSE;
		}
	
		return $this->plugin_amazon_print_object($align, $alt, $title);
	}
	
	function plugin_amazon_action()
	{
	//	global $vars, $script, $edit_auth, $edit_auth_users;
	//	global $amazon_body, $asin, $asin_all;
	
		if ($this->cont['PKWK_READONLY']) $this->func->die_message('PKWK_READONLY prohibits editing');
	
		$s_page   = isset($this->root->vars['refer']) ? $this->root->vars['refer'] : '';
		$this->root->asin_all = isset($this->root->vars['asin']) ?
			htmlspecialchars(rawurlencode($this->func->strip_bracket($this->root->vars['asin']))) : '';
	
		if (! $this->is_asin()) {
			$retvars['msg']   = '�֥å���ӥ塼�Խ�';
			$retvars['refer'] = & $s_page;
			$retvars['body']  = $this->plugin_amazon_convert();
			return $retvars;
	
		} else {
			$r_page     = $s_page . '/' . $this->root->asin;
			$r_page_url = rawurlencode($r_page);
			$auth_user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
	
			$this->func->pkwk_headers_sent();
			if ($this->root->edit_auth && ($auth_user == '' || ! isset($this->root->edit_auth_users[$auth_user]) ||
			    $this->root->edit_auth_users[$auth_user] != $_SERVER['PHP_AUTH_PW'])) {
			    	// Edit-auth failed. Just look the page
				header('Location: ' . $this->func->get_script_uri() . '?' . $r_page_url);
			} else {
				$title = $this->plugin_amazon_get_asin_title();
				if ($title == '' || preg_match('#^/#', $s_page)) {
					// Invalid page name
					header('Location: ' . $this->func->get_script_uri() . '?' . rawurlencode($s_page));
				} else {
					$body = '#amazon(' . $this->root->asin_all . ',,image)' . "\n" .
					'*' . $title . "\n" . $this->root->amazon_body;
					$this->plugin_amazon_review_save($r_page, $body);
					header('Location: ' . $this->func->get_script_uri() .
					'?cmd=edit&page=' . $r_page_url);
				}
			}
			exit;
		}
	}
	
	function plugin_amazon_inline()
	{
	//	global $amazon_aid, $asin, $asin_all;
	
		list($this->root->asin_all) = func_get_args();
	
		$this->root->asin_all = htmlspecialchars($this->root->asin_all); // for XSS
		if (! $this->is_asin()) return FALSE;
	
		$title = $this->plugin_amazon_get_asin_title();
		if ($title == '') {
			return FALSE;
		} else {
			return '<a href="' . $this->cont['PLUGIN_AMAZON_SHOP_URI'] .
			$this->root->asin . '/' . $this->root->amazon_aid . 'ref=nosim">' . $title . '</a>' . "\n";
		}
	}
	
	function plugin_amazon_print_object($align, $alt, $title)
	{
	//	global $amazon_aid;
	//	global $asin, $asin_ext, $asin_all;
	
		$url      = $this->plugin_amazon_cache_image_fetch($this->cont['CACHE_DIR']);
		$url_shop = $this->cont['PLUGIN_AMAZON_SHOP_URI'] . $this->root->asin . '/' . $this->root->amazon_aid . 'ref=nosim';
		$center   = 'text-align:center';
	
		if ($title == '') {
			// Show image only
			$div  = '<div style="float:' . $align . ';margin:16px 16px 16px 16px;' . $center . '">' . "\n";
			$div .= ' <a href="' . $url_shop . '"><img src="' . $url . '" alt="' . $alt . '" /></a>' . "\n";
			$div .= '</div>' . "\n";
	
		} else {
			// Show image and title
			$div  = '<div style="float:' . $align . ';padding:.5em 1.5em .5em 1.5em;' . $center . '">' . "\n";
			$div .= ' <table style="width:110px;border:0;' . $center . '">' . "\n";
			$div .= '  <tr><td style="' . $center . '">' . "\n";
			$div .= '   <a href="' . $url_shop . '"><img src="' . $url . '" alt="' . $alt  .'" /></a></td></tr>' . "\n";
			$div .= '  <tr><td style="' . $center . '"><a href="' . $url_shop . '">' . $title . '</a></td></tr>' . "\n";
			$div .= ' </table>' . "\n";
			$div .= '</div>' . "\n";
		}
		return $div;
	}
	
	function plugin_amazon_get_asin_title()
	{
	//	global $asin, $asin_ext, $asin_all;
	
		if ($this->root->asin_all == '') return '';
	
		$nocache = $nocachable = 0;
	
		$url = $this->cont['PLUGIN_AMAZON_XML'] . $this->root->asin;
	
		if (file_exists($this->cont['CACHE_DIR']) === FALSE || is_writable($this->cont['CACHE_DIR']) === FALSE) $nocachable = 1; // ����å����ԲĤξ��
	
		if (($title = $this->plugin_amazon_cache_title_fetch($this->cont['CACHE_DIR'])) == FALSE) {
			$nocache = 1; // ����å��師�Ĥ��餺
			$body    = $this->plugin_amazon_get_page($url); // �������ʤ��ΤǼ��ˤ���
			$tmpary  = array();
			$body    = mb_convert_encoding($body, $this->cont['SOURCE_ENCODING'], 'UTF-8');
			preg_match('/<ProductName>([^<]*)</', $body, $tmpary);
			$title     = trim($tmpary[1]);
	//		$tmpary[1] = '';
	//		preg_match('#<ImageUrlMedium>http://images-jp.amazon.com/images/P/[^.]+\.(..)\.#',
	//			$body, $tmpary);
	//		if ($tmpary[1] != '') {
	//			$asin_ext = $tmpary[1];
	//			$asin_all = $asin . $asin_ext;
	//		}
		}
	
		if ($title == '') {
			return '';
		} else {
			if ($nocache == 1 && $nocachable != 1)
				$this->plugin_amazon_cache_title_save($title, $this->cont['CACHE_DIR']);
			return $title;
		}
	}
	
	// �����ȥ륭��å��夬���뤫Ĵ�٤�
	function plugin_amazon_cache_title_fetch($dir)
	{
	//	global $asin, $asin_ext, $asin_all;
	
		$filename = $dir . 'ASIN' . $this->root->asin . '.tit';
	
		$get_tit = 0;
		if (! is_readable($filename)) {
			$get_tit = 1;
		} elseif ($this->cont['PLUGIN_AMAZON_EXPIRE_TITLECACHE'] * 3600 * 24 < time() - filemtime($filename)) {
			$get_tit = 1;
		}
	
		if ($get_tit) return FALSE;
	
		if (($fp = @fopen($filename, 'r')) === FALSE) return FALSE;
		$title = fgets($fp, 4096);
	//	$tmp_ext = fgets($fp, 4096);
	//	if ($tmp_ext != '') $asin_ext = $tmp_ext;
		fclose($fp);
	
		if (strlen($title) > 0) {
			return $title;
		} else {
			return FALSE;
		}
	}
	
	// ��������å��夬���뤫Ĵ�٤�
	function plugin_amazon_cache_image_fetch($dir)
	{
	//	global $asin, $asin_ext, $asin_all;
	
		$filename = $dir . 'ASIN' . $this->root->asin . '.jpg';
	
		$get_img = 0;
		if (! is_readable($filename)) {
			$get_img = 1;
		} elseif ($this->cont['PLUGIN_AMAZON_EXPIRE_IMAGECACHE'] * 3600 * 24 < time() - filemtime($filename)) {
			$get_img = 1;
		}
	
		if ($get_img) {
			$url = 'http://images-jp.amazon.com/images/P/' . $this->root->asin . '.' . $this->root->asin_ext . '.MZZZZZZZ.jpg';
			if (! $this->func->is_url($url)) return FALSE;
	
			$body = $this->plugin_amazon_get_page($url);
			if ($body != '') {
				$tmpfile = $dir . 'ASIN' . $this->root->asin . '.jpg.0';
				$fp = fopen($tmpfile, 'wb');
				fwrite($fp, $body);
				fclose($fp);
				$size = getimagesize($tmpfile);
				unlink($tmpfile);
			}
			if ($body == '' || $size[1] <= 1) { // �̾��1���֤뤬ǰ�Τ���0�ξ���(reimy)
				// ����å���� PLUGIN_AMAZON_NO_IMAGE �Υ��ԡ��Ȥ���
				if ($this->root->asin_ext == '09') {
					$url = 'http://images-jp.amazon.com/images/P/' . $this->root->asin . '.01.MZZZZZZZ.jpg';
					$body = $this->plugin_amazon_get_page($url);
					if ($body != '') {
						$tmpfile = $dir . 'ASIN' . $this->root->asin . '.jpg.0';
						$fp = fopen($tmpfile, 'wb');
						fwrite($fp, $body);
						fclose($fp);
						$size = getimagesize($tmpfile);
						unlink($tmpfile);
					}
				}
				if ($body == '' || $size[1] <= 1) {
					$fp = fopen($this->cont['PLUGIN_AMAZON_NO_IMAGE'], 'rb');
					if (! $fp) return FALSE;
					
					$body = '';
					while (! feof($fp)) $body .= fread($fp, 4096);
					fclose ($fp);
				}
			}
			$this->plugin_amazon_cache_image_save($body, $this->cont['CACHE_DIR']);
		}
		return str_replace($this->cont["DATA_HOME"], $this->cont["HOME_URL"], $filename);
	}
	
	// Save title cache
	function plugin_amazon_cache_title_save($data, $dir)
	{
	//	global $asin, $asin_ext, $asin_all;
	
		$filename = $dir . 'ASIN' . $this->root->asin . '.tit';
		$fp = fopen($filename, 'w');
		fwrite($fp, $data);
		fclose($fp);
	
		return $filename;
	}
	
	// Save image cache
	function plugin_amazon_cache_image_save($data, $dir)
	{
	//	global $asin, $asin_ext, $asin_all;
	
		$filename = $dir . 'ASIN' . $this->root->asin . '.jpg';
		$fp = fopen($filename, 'wb');
		fwrite($fp, $data);
		fclose($fp);
	
		return $filename;
	}
	
	// Save book data
	function plugin_amazon_review_save($page, $data)
	{
	//	global $asin, $asin_ext, $asin_all;
	
		$filename = $this->cont['DATA_DIR'] . $this->func->encode($page) . '.txt';
		if (! is_readable($filename)) {
			$fp = fopen($filename, 'w');
			fwrite($fp, $data);
			fclose($fp);
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function plugin_amazon_get_page($url)
	{
		$data = $this->func->http_request($url);
		return ($data['rc'] == 200) ? $data['data'] : '';
	}
	
	// is ASIN?
	function is_asin()
	{
	//	global $asin, $asin_ext, $asin_all;
	
		$tmpary = array();
		if (preg_match('/^([A-Z0-9]{10}).?([0-9][0-9])?$/', $this->root->asin_all, $tmpary) == FALSE) {
			return FALSE;
		} else {
			$this->root->asin     = $tmpary[1];
			$this->root->asin_ext = isset($tmpary[2]) ? $tmpary[2] : '';
			if ($this->root->asin_ext == '') $this->root->asin_ext = '09';
			$this->root->asin_all = $this->root->asin . $this->root->asin_ext;
			return TRUE;
		}
	}
}
?>