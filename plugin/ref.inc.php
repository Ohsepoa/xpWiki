<?php
// $Id: ref.inc.php,v 1.44 2009/04/04 04:30:40 nao-pon Exp $
/*

	*�ץ饰���� ref
	�ڡ�����ź�դ��줿�ե������Ÿ������

	*�ѥ�᡼��
	-filename~
	 ź�եե�����̾�����뤤��URL
	-Page~
	 WikiName�ޤ���BracketName����ꤹ��ȡ����Υڡ�����ź�եե�����򻲾Ȥ���
	-Left|Center|Right~
	 ���ΰ��ֹ�碌
	-Wrap|Nowrap~
	 �ơ��֥륿���ǰϤ�/�Ϥޤʤ�
	-Around~
	 �ƥ����Ȥβ�����
	-nocache~
	 URL�����ե�����(�����ե�����)�򥭥�å��夷�ʤ�
	-w:�ԥ������
	-h:�ԥ������
	-����%
	 �����ե�����Υ��������ꡣ
	 w: h: �ɤ��餫�λ���ǽĲ�����Ψ���ݤäƥꥵ������
	 %����ǡ�����Υѡ�����Ȥ�ɽ����
	-t:�����ȥ�
	 �����Υ��åץƥ����Ȥ����

*/

class xpwiki_plugin_ref extends xpwiki_plugin {
	var $flg_lightbox_loaded = false;

	function plugin_ref_init () {
		// File icon image
		if (! isset($this->cont['FILE_ICON']))
			$this->cont['FILE_ICON'] = 
				'<img src="' . $this->cont['IMAGE_DIR'] . 'file.png" width="20" height="20"' .
				' alt="file" style="border-width:0px" />';
	
		// default alignment
		$this->cont['PLUGIN_REF_DEFAULT_ALIGN'] = 'none'; // 'none','left','center','right'

		// Text wrapping
		$this->cont['PLUGIN_REF_WRAP_TABLE'] =  FALSE; // TRUE, FALSE
		
		// URL������˲�����������������뤫
		$this->cont['PLUGIN_REF_URL_GET_IMAGE_SIZE'] =  FALSE; // FALSE, TRUE

		// UPLOAD_DIR �Υǡ���(�����ե�����Τ�)��ľ�ܥ�������������
		$this->cont['PLUGIN_REF_DIRECT_ACCESS'] =  FALSE; // FALSE or TRUE
		// - ����Ͻ���Υ���饤�󥤥᡼��������ߴ��Τ���˻Ĥ���Τ�
		//   ���ꡢ��®���Τ���Υ��ץ����ǤϤ���ޤ���
		// - UPLOAD_DIR ��Web�����С����Ϫ�Ф����Ƥ��ꡢ����ľ�ܥ�������
		//   �Ǥ���(�����������¤��ʤ�)���֤Ǥ���ɬ�פ�����ޤ�
		// - Apache �ʤɤǤ� UPLOAD_DIR/.htaccess ��������ɬ�פ�����ޤ�
		// - �֥饦���ˤ�äƤϥ���饤�󥤥᡼����ɽ���䡢�֥���饤��
		//   ���᡼��������ɽ���פ��������ʤɤ��Զ�礬�Ф��礬����ޤ�
		
		// Image suffixes allowed
		$this->cont['PLUGIN_REF_IMAGE_REGEX'] =  '/\.(gif|png|jpe?g)$/i';
		
		// Usage (a part of)
		$this->cont['PLUGIN_REF_USAGE'] =  "([pagename/]attached-file-name[,parameters, ... ][,title])";
		
		// ����ͥ�������������ɽ��������祵����
		$this->cont['PLUGIN_REF_IMG_MAX_WIDTH'] = 640;
		$this->cont['PLUGIN_REF_IMG_MAX_HEIGHT'] = 480;

		// ����ݸ�줿�����κ���ɽ��������(px) ���� (%)����
		$this->cont['PLUGIN_REF_COPYRIGHT_IMG_MAX'] = 100;
		$this->cont['PLUGIN_REF_COPYRIGHT_IMG_MAX%'] = 50;
		
		// Flash �ե�����Υ���饤��ɽ������
		// �ե����륪���ʡ���...���٤ƶػ�:0 , �����ͤΤ�:1 , ��Ͽ�桼�����Τ�:2 , ���٤Ƶ���:3
		// �������ƥ��塢0 or 1 �Ǥα��Ѥ򶯤�����
		// $this->cont['PLUGIN_REF_FLASH_INLINE'] = 1;
		// �嵭����� pukiwiki.ini.php �˰�ư���ޤ�����
		
		// Exif �ǡ���������� title°�����ղä��� (TRUE or FALSE)
		$this->cont['PLUGIN_REF_GET_EXIF'] = FALSE;
		
	}

	// Output an image (fast, non-logging <==> attach plugin)
	function plugin_ref_action()
	{
		$usage = 'Usage: plugin=ref&amp;page=page_name&amp;src=attached_image_name';
	
		if (! isset($this->root->vars['page']) || ! isset($this->root->vars['src']))
			return array(array('header' => 'HTTP/1.0 404 Not Found', 'msg' => 'File Not Found.'));
		
		
		$page     = $this->root->vars['page'];
		$filename = $this->root->vars['src'] ;
		$ref = $this->cont['UPLOAD_DIR'] . $this->func->encode($page) . '_' . $this->func->encode(preg_replace('#^.*/#', '', $filename));
		
		$mtime = filemtime($ref);
		$etag = '"' . $mtime . '"';
		$expires = 'Expires: ' . gmdate( "D, d M Y H:i:s", $this->cont['UTC'] + $this->cont['BROWSER_CACHE_MAX_AGE'] ) . ' GMT';
				
		if ($etag == @ $_SERVER["HTTP_IF_NONE_MATCH"]) {
			// clear output buffer
			while( ob_get_level() ) {
				ob_end_clean() ;
			}
			header('HTTP/1.1 304 Not Modified' );
			header('Cache-Control: private, max-age=' . $this->cont['BROWSER_CACHE_MAX_AGE']);
			header('Pragma:');
			header($expires);
			exit();
		}

		if (! $this->func->check_readable($page, true, true)) {
			return array('header' => 'HTTP/1.0 403 Forbidden', 'msg' => '403 Forbidden.');
		}
		
		if(! file_exists($ref)) {
			return array('header' => 'HTTP/1.0 404 Not Found', 'msg' => 'File Not Found.');
		}
		
		// ���ե��������
		$status = $this->get_fileinfo($ref);

		if ($status['copyright']) {
			return array('header' => 'HTTP/1.0 403 Forbidden', 'msg' => '403 Forbidden.');
		}
		
		$imgtype = isset($status['imagesize'][2])? $status['imagesize'][2] : false;
		if ($status['noinline'] > 0) $imgtype = false;
		switch ($imgtype) {
		case 1: $type = 'image/gif' ; break;
		case 2: $type = 'image/jpeg'; break;
		case 3: $type = 'image/png' ; break;
		case 4:
		case 13:
			$type = 'application/x-shockwave-flash';
			$noimg = FALSE;
			// Flash �Υ���饤��ɽ�����¥����å�
			if ($status['noinline'] > -1) {
				if ($this->cont['PLUGIN_REF_FLASH_INLINE'] === 0) {
					// ���٤ƶػ�
					$noimg = TRUE;
				} else if ($this->cont['PLUGIN_REF_FLASH_INLINE'] === 1) {
					// �����ͽ�ͭ�Τߵ���
					if (! $status['admins']) {
						$noimg = TRUE;
					}
				} else if ($this->cont['PLUGIN_REF_FLASH_INLINE'] === 2) {
					// ��Ͽ�桼������ͭ�Τߵ���
					if (! $status['owner']) {
						$noimg = TRUE;
					}
				} 
			}
			if ($noimg) return array('header' => 'HTTP/1.0 403 Forbidden', 'msg' => '403 Forbidden.');
			break;
		default:
			$noimg = TRUE;
			if ($status['noinline'] < 0 || ($status['admins'] && $status['noinline'] < 1)) {
				list($ext, $type) = $this->get_file_extention($filename);
				if ($type) $noimg = FALSE;
			}
			if ($noimg) return array('header' => 'HTTP/1.0 403 Forbidden', 'msg' => '403 Forbidden.');
		}

		// Check Referer
		if ($this->cont['OPEN_MEDIA_REFCHECK']) {
			if (! $this->func->refcheck($this->cont['OPEN_MEDIA_REFCHECK'] - 1)) {
				return array('header' => 'HTTP/1.0 404 Not Found', 'msg' => 'File Not Found.');
			}
		}
	
		// Care for Japanese-character-included file name
		if ($this->cont['LANG'] === 'ja') {
			switch($this->cont['UA_NAME']){
				//case 'Safari':
				//	$filename = '';
				//	break;
				case 'MSIE':
					$filename = mb_convert_encoding($filename, 'SJIS-WIN', $this->cont['SOURCE_ENCODING']);
					break;
				default:
					// Care for using _auto-encode-detecting_ function
					$filename = mb_convert_encoding($filename, 'UTF-8', $this->cont['SOURCE_ENCODING']);
			}
		}
		if (strpos(strtolower($this->root->ua), 'windows') !== FALSE) {
			$filename = str_replace(array(':', '*', '?', '"', '<', '>', '|'), '_', $filename);
		}
		
		$size = filesize($ref);

		// Output
		// clear output buffer
		while( ob_get_level() ) {
			ob_end_clean() ;
		}
		$this->func->pkwk_common_headers();
		header('Content-Disposition: inline; filename="' . $filename . '"');
		header('Content-Length: ' . $size);
		header('Content-Type: '   . $type);
		header('Last-Modified: '  . gmdate( "D, d M Y H:i:s", $mtime ) . " GMT" );
		header('Etag: '           . $etag );
		header('Cache-Control: private, max-age=' . $this->cont['BROWSER_CACHE_MAX_AGE']);
		header('Pragma:');
		header($expires);
		
		@ readfile($ref);
		exit;
	}

	function can_call_otherdir_inline() {
		return 1;
	}
	
	function plugin_ref_inline() {
		//���顼�����å�
		if (!func_num_args()) return 'no argument(s).';
		
		$params = $this->get_body(func_get_args(), true);
		
		if ($params['_error']) {
			$ret = $params['_error'];
		} else {
			$ret = $params['_body'];
		}
		
		return $ret;
	}
	
	function can_call_otherdir_convert() {
		return 1;
	}
	
	function plugin_ref_convert() {
	
		//���顼�����å�
		if (!func_num_args()) return 'no argument(s).';
		
		$params = $this->get_body(func_get_args());
		
		if ($params['_error']) {
			$ret = $params['_error'];
		} else {
			$ret = $params['_body'];
		}
	
		if (($this->cont['PLUGIN_REF_WRAP_TABLE'] and !$params['nowrap']) or $params['wrap']) {
			$ret = $this->wrap_table($ret, $params['_align'], $params['around']);
		}
		$ret = $this->wrap_div($ret, $params['_align'], $params['around']);
		
		return $ret;
	}
	
	// BodyMake
	function get_body($args, $inline = false){
		// �����
		$params = array(
			'left'    => FALSE, // ����
			'center'  => FALSE, // �����
			'right'   => FALSE, // ����
			'wrap'    => FALSE, // TABLE�ǰϤ�
			'nowrap'  => FALSE, // TABLE�ǰϤޤʤ�
			'around'  => FALSE, // ������
			'noicon'  => FALSE, // ���������ɽ�����ʤ�
			'nolink'  => FALSE, // ���ե�����ؤΥ�󥯤�ĥ��ʤ�
			'noimg'   => FALSE, // ������Ÿ�����ʤ�
			'zoom'    => FALSE, // �Ĳ�����ݻ�����
			'nocache' => FALSE, // URL�ξ��˥���å��夷�ʤ�
			'noinline'=> FALSE, // ����饤��ɽ�����ʤ�
			'btn'     => '',    // ���åץ��ɥ�󥯤Υƥ����Ȼ���
			'auth'    => FALSE, // ���åץ��ɥ��ɽ�����Խ����¥����å�
			'_size'   => FALSE, // ���������ꤢ��
			'_w'      => 0,     // ��
			'_h'      => 0,     // �⤵
			'_%'      => 0,     // ����Ψ
			'_align'  => $this->cont['PLUGIN_REF_DEFAULT_ALIGN'],
			'_args'   => array(),
			'_body'   => '',
			'_title'  => array(),
			'_error'  => ''
		);
		
		// local var
		$lvar = array(
			'refid' => '',
			'page'  => $this->cont['PageForRef'], // �ڡ���̾
			'name'  => array_shift($args), // ź�եե�����̾�����(������)
			'isurl' => FALSE,
			'title' => array()
		);

		if ($lvar['page'] === '#RenderMode') {
			$lvar['page'] = $this->root->render_attach;
		}

		// ���åץ��ɥ�󥯻���
		if (substr($lvar['name'],0,3) === 'ID$') {
			$lvar['refid'] = substr($lvar['name'], 3);
			$lvar['name'] = '';
		}
		if ($lvar['refid']) {
			$this->make_uploadlink($params, $lvar, $args);
			return $params;
		}

		// �Ĥ�ΰ����ν���
		$this->fetch_options($params, $args, $lvar);

		// �ե����륿���פ�����
		$this->get_type($lvar, $args, $params);

		// Check readable
		if ($lvar['page'] !== $this->root->render_attach && ! $this->func->check_readable($lvar['page'], false, false)) {
			$params['_error'] = '<small>[File display right none]</small>';
		}
		
		// ���顼����
		if ($params['_error']) {
			if ($params['_error'] === 'File not found.') {
				// ź�եե����뤬�ʤ��Τǥ��åץ��ɥ��
				$this->root->rtf['disable_render_cache'] = true;
				if (!$lvar['page']) {
					$lvar['page'] = $this->root->render_attach;
				}
				$this->make_uploadlink($params, $lvar, $args);
			}
			return $params;
		}

		// ����ͥ�����������ɽ��������祵����
		if (!$params['_size']) {
			$params['_size'] = true;
			$params['zoom'] = true;
			$params['_max'] = true;
			$params['_w'] = $this->cont['PLUGIN_REF_IMG_MAX_WIDTH'];
			$params['_h'] = $this->cont['PLUGIN_REF_IMG_MAX_HEIGHT'];
		}
		
		if ($lvar['type'] > 2 ) {
			// �ե��������
			$params['fsize'] = filesize($lvar['file']);
			if ($params['fsize'] < 103) {
				$params['fsize'] = round($params['fsize']) . 'B';
			} else if ($params['fsize'] < 1024 * 1024) {
				$params['fsize'] = sprintf('%01.1f',$params['fsize']/1024,1).'KB';
			} else {
				$params['fsize'] = sprintf('%01.1f',$params['fsize']/(1024*1024),1).'MB';
			}
			//$params['fsize'] = sprintf('%01.1f', round(filesize($lvar['file'])/1024, 1)) . 'KB';
			$lvar['info'] = $this->func->get_date('Y/m/d H:i:s', filemtime($lvar['file']) - $this->cont['LOCALZONE']) .
				' ' . $params['fsize'];
		} else {
			$params['fsize'] = '';
		}

		// $img �ѥ�᡼�������å�
		$img = array(
			'org_w' => 0,
			'org_h' => 0,
			'width' => 0,
			'height' => 0,
			'info' => '',
			'title' => array(),
			'class' => ' class="img_margin"'
		);
		
		// 
		if ($lvar['type'] === 1) {
			// URL����
			if ($this->cont['PLUGIN_REF_URL_GET_IMAGE_SIZE'] && (bool)ini_get('allow_url_fopen')) {
				$size = $this->getimagesize($lvar['name']);
				if (is_array($size)) {
					$img['org_w'] = $size[0];
					$img['org_h'] = $size[1];
				}
			}
			
			// ���᡼��ɽ���������μ���
			$this->get_show_imagesize($img, $params);
			$lvar['img'] = $img;
			$lvar['url'] = htmlspecialchars($lvar['name']);
			$lvar['link'] = $lvar['url'];
			$lvar['text'] = '';
			$lvar['title'][] =  (preg_match('/([^\/]+)$/', $lvar['name'], $match))? $match[1] : '';
			$lvar['title'] = htmlspecialchars(join(', ', $lvar['title'] + $params['_title']));
		} else if ($lvar['type'] === 2) {
			// URL�����ʳ�
			$lvar['url'] = '';
			$lvar['link'] = htmlspecialchars($lvar['name']);
			$lvar['text'] = htmlspecialchars($lvar['name']);
			$lvar['title'][] = (preg_match('/([^\/]+)$/', $lvar['name'], $match))? $match[1] : '';
			$lvar['title'] = htmlspecialchars(join(', ', $lvar['title'] + $params['_title']));
		} else if ($lvar['type'] === 3) {
			// ź�ղ���
			$size = $this->getimagesize($lvar['file']);
			if (is_array($size)) {
				$img['org_w'] = $size[0];
				$img['org_h'] = $size[1];
			}
			
			if ($lvar['isurl']) {
				$lvar['link'] = htmlspecialchars($lvar['isurl']);
			} else {
				$lvar['link'] = '';
			}
			$lvar['title'][] = (preg_match('/([^\/]+)$/', $lvar['status']['org_fname']? $lvar['status']['org_fname'] : $lvar['name'], $match))? $match[1] : '';
			
			if ($lvar['status']['copyright']) {
				//����ݸ��Ƥ�����ϥ�����$this->cont['PLUGIN_REF_COPYRIGHT_IMG_MAX%']%���⤫�ĽĲ� $this->cont['PLUGIN_REF_COPYRIGHT_IMG_MAX']px �����ɽ��
				$params['_size'] = TRUE;
				if ($img['org_w'] > $this->cont['PLUGIN_REF_COPYRIGHT_IMG_MAX'] || $img['org_h'] > $this->cont['PLUGIN_REF_COPYRIGHT_IMG_MAX'] ) {
					$params['_h'] = $params['_w'] = $this->cont['PLUGIN_REF_COPYRIGHT_IMG_MAX'];
					$params['zoom'] = TRUE;
					$params['_%'] = '';
				} else {
					$params['_%'] = $this->cont['PLUGIN_REF_COPYRIGHT_IMG_MAX%'];
				}
			}
			
			// ���᡼��ɽ���������μ���
			$this->get_show_imagesize($img, $params);
			
			// �����Ѳ���������������
			if ($this->cont['UA_PROFILE'] === 'keitai') {
				if ($img['width'] > $this->root->keitai_img_px || $img['height'] > $this->root->keitai_img_px) {
					$params['_h'] = $params['_w'] = $this->root->keitai_img_px;
					$params['zoom'] = TRUE;
					$params['_%'] = '';
					$this->get_show_imagesize($img, $params);
				}
			}
			
			$lvar['img'] = $img;
			$lvar['title'][] = $img['title'];

			//EXIF DATA
			if ($this->cont['PLUGIN_REF_GET_EXIF']) {
				$exif_data = $this->func->get_exif_data($lvar['file']);
				if ($exif_data){
					$lvar['title'][] = $exif_data['title'];
					foreach($exif_data as $key => $value){
						if ($key != "title") $lvar['title'][] = "$key: $value";
					}
				}
			}
			
			$lvar['url'] = $lvar['file'];
			if ($params['_%'] && $params['_%'] < 95) {
				$_file = preg_split('/(\.[a-zA-Z]+)?$/', $lvar['name'], -1, PREG_SPLIT_DELIM_CAPTURE);
				// Check original filename extention (for Renderer mode)
				if (! $_file[1] && preg_match('/(\.[a-zA-Z]+)$/', $lvar['status']['org_fname'], $_match)) {
					$_file[1] = $_match[1];
				} 
				$s_file = $this->cont['UPLOAD_DIR']."s/".$this->func->encode($lvar['page']).'_'.$params['_%']."_".$this->func->encode($_file[0]).$_file[1];
				if (file_exists($s_file)) {
					//����ͥ�������Ѥߤʤ餽��򻲾�
					$lvar['url'] = $s_file;
				} else {
					//����ͥ������
					$lvar['url'] = $this->make_thumb($lvar['file'], $s_file, $img['width'], $img['height']);
				}
			}
			
			// ���ե������ɽ��
			if ($lvar['url'] === $lvar['file']) {
				// URI for in-line image output
				if (! $this->cont['PLUGIN_REF_DIRECT_ACCESS']) {
					// With ref plugin (faster than attach)
					$lvar['url'] = $this->cont['DATA_HOME'] . 'gate.php?way=ref&amp;_nodos&amp;_noumb&amp;page=' . rawurlencode($lvar['page']) .
					'&amp;src=' . rawurlencode($lvar['name']); // Show its filename at the last
				} else {
					// Try direct-access, if possible
					$lvar['url'] = $lvar['file'];
				}
			} else if (! $lvar['status']['copyright']) {
				// ���������
				// URI for in-line image output
				if (! $this->cont['PLUGIN_REF_DIRECT_ACCESS']) {
					// With ref plugin (faster than attach)
					$lvar['link'] = $this->cont['DATA_HOME'] . 'gate.php?way=ref&amp;_nodos&amp;_noumb&amp;page=' . rawurlencode($lvar['page']) .
					'&amp;src=' . rawurlencode($lvar['name']); // Show its filename at the last
				} else {
					// Try direct-access, if possible
					$lvar['link'] = $lvar['file'];
				}
			}
			
			// URL �Υ�����ѥ���URI�ѥ����Ѵ�
			$lvar['url'] = str_replace($this->cont['DATA_HOME'], $this->cont['HOME_URL'], $lvar['url']);
			if ($lvar['link']) $lvar['link'] = str_replace($this->cont['DATA_HOME'], $this->cont['HOME_URL'], $lvar['link']);
			$lvar['text'] = '';
			$lvar['title'] = $lvar['title'] + $params['_title'];
			if (! empty($lvar['title'])) {
				$lvar['title'] = htmlspecialchars(join(', ', $lvar['title']));
				$lvar['title'] = $this->func->make_line_rules($lvar['title']);
			}
		} else {
			// Flash��ź�դ���¾
			$lvar['url'] = '';
			$filename = $lvar['status']['org_fname'];
			$filename = str_replace(array(':', '*', '?', '"', '<', '>', '|'), '_', $filename);
			$filename = '/' . rawurlencode($filename);
			$noinline = ($params['noinline'])? '&amp;ni=1' : '';
			$lvar['link'] = $this->cont['HOME_URL'] . 'gate.php' . $filename . '?way=attach&amp;_noumb' . $noinline . '&amp;refer=' . rawurlencode($lvar['page']) .
					'&amp;openfile=' . rawurlencode($lvar['name']); // Show its filename at the last
			if ($lvar['type'] !== 4) $lvar['title'] = $lvar['title'] + $params['_title'];
			if (! empty($lvar['title'])) {
				// �����ȥ뤬���ꤵ��Ƥ���
				$lvar['text'] = htmlspecialchars(join(', ', $lvar['title']));
				$lvar['title'] = htmlspecialchars(preg_replace('/([^\/]+)$/', "$1", $lvar['status']['org_fname']? $lvar['status']['org_fname'] : $lvar['name']) . ', ' . $lvar['info']);
			} else {
				$lvar['text'] = htmlspecialchars(preg_replace('/([^\/]+)$/', "$1", $lvar['status']['org_fname']? $lvar['status']['org_fname'] : $lvar['name']));
				$lvar['title'] = htmlspecialchars($lvar['info']);
			}
		}
		
		// �����Ȥ�Ω��
		if ($lvar['url']) {
			// ����
			// lightbox
			if (! $this->flg_lightbox_loaded && $this->root->ref_use_lightbox) {
				$this->flg_lightbox_loaded = true;
				$this->func->add_tag_head('lightbox.css');
				$this->func->add_tag_head('lightbox.js');
			}
			$_size = '';
			if ($img['width']) {
				$_size .= ' width="' . $img['width'] . '"';
			}
			if ($img['height']) {
				$_size .= ' height="' . $img['height'] . '"';
			}
			$align = '';
			if ($inline) {
				if ($params['right']) {
					$align = ' align="right" style="float:right;"';
				} else if ($params['left']) {
					$align = ' align="left" style="float:left;"';
				}
			}
			// �����ե�����
			$params['_body'] = '<img src="' . $lvar['url'] . '" alt="' . $lvar['title'] . '" title="' . $lvar['title'] . '"' . $img['class'] . $img['info'] . $_size . $align . ' />';
			if (!$params['nolink'] && $lvar['link']) {
				$params['_body'] = '<a href="' . $lvar['link'] . '" title="' . $lvar['title'] . '" type="img">' . $params['_body'] . '</a>';
			}
		} else {
			// ����¾�ե�����
			$icon = $params['noicon'] ? '' : $this->cont['FILE_ICON'];
			$params['_body'] = $icon . $lvar['text'];
			if (!$params['nolink']) {
				$params['_body'] = '<a href="' . $lvar['link'] . '" title="' . $lvar['title'] . '">' . $params['_body'] .'</a>';
			}
			if ($lvar['type'] === 4) {
				$this->set_flash_tag ($params, $lvar);
			} else if (isset($lvar['status']) && ! $params['noinline'] && ($lvar['status']['noinline'] < 0 || ($lvar['status']['admins'] && $lvar['status']['noinline'] < 1))) {
				$lvar['url'] = $this->cont['HOME_URL'] . 'gate.php?way=ref&amp;_nodos&amp;_noumb&amp;page=' . rawurlencode($lvar['page']) .
				'&amp;src=' . rawurlencode($lvar['name']); // Show its filename at the last


				$width = ($params['_w'])? ' width="'.$params['_w'].'"' : '';
				$height = ($params['_h'])? ' height="'.$params['_h'].'"' : '';
				list($ext, $type) = $this->get_file_extention($lvar['name']);
				if ($type) {
					if ($ext === 'svg') {
						//$this->func->add_js_var_head('XpWikiIeDomLoadedDisable', true);
						$this->func->add_tag_head('sie-mini.js', FALSE, 'UTF-8', TRUE);
					}
					$params['_body'] = '<object data="'.$lvar['url'].'" type="'.$type.'"'.$width.$height.'><param name="src" value="'.$lvar['url'].'"></object>';
				}
			}
		}

		return $params;
	}

	function set_flash_tag (&$params, $lvar) {
		// Flash
		// $img �ѥ�᡼�������å�
		$img = array(
			'org_w' => 0,
			'org_h' => 0,
			'width' => 0,
			'height' => 0,
			'info' => '',
			'title' => array(),
			'class' => ' class="img_margin"'
		);
		$size = $this->getimagesize($lvar['file']);
		if (is_array($size)) {
			$img['org_w'] = $size[0];
			$img['org_h'] = $size[1];
		}
		// ���᡼��ɽ���������μ���
		$this->get_show_imagesize($img, $params);
		
		//�����
		$f_a = $f_p = array();
		
		foreach ($params['_args'] as $arg){
			$m = array();
			if (preg_match("/^a(?:lign)?:(left|right|top|bottom)$/i",$arg,$m)){
				$f_a['_a'] = "align:\"{$m[1]}\"";
			}
			if (preg_match("/^q(?:uality)?:((auto)?(high|low|best|medium))$/i",$arg,$m)){
				$f_p['_q']  = "quality:\"{$m[1]}\"";
			}
			if (preg_match("/^p(?:lay)?:(TRUE|FALSE)$/i",$arg,$m)){
				$f_p['_p']  = "play:\"{$m[1]}\"";
			}
			if (preg_match("/^l(?:oop)?:(TRUE|FALSE)$/i",$arg,$m)){
				$f_p['_l']  = "loop:\"{$m[1]}\"";
			}
			if (preg_match("/^b(?:gcolor)?:#?([abcdef\d]{6,6})$/i",$arg,$m)){
				$f_p['_b']  = "bgcolor:\"#{$m[1]}\"";
			}
			if (preg_match("/^sc(?:ale)?:(showall|noborder|exactfit|noscale)$/i",$arg,$m)){
				$f_p['_sc']  = "scale:\"{$m[1]}\"";
			}
			if (preg_match("/^sa(?:lign)?:(l|r|t|b|tl|tr|bl|br)$/i",$arg,$m)){
				$f_p['_sa']  = "salign:\"{$m[1]}\"";
			}
			if (preg_match("/^m(?:enu)?:(TRUE|FALSE)$/i",$arg,$m)){
				$f_p['_m']  = "menu:\"{$m[1]}\"";
			}
			if (preg_match("/^wm(?:ode)?:(window|opaque|transparent)$/i",$arg,$m)){
				$f_p['_wmp'] = "wmode:\"{$m[1]}\"";
			}
		}
		$params['_w'] = " width=".$img['width'];
		$params['_h'] = " height=".$img['height'];

		$cid = $this->root->mydirname . '_' . basename($lvar['file']);
		$params['_body'] = '<span id="'.$cid.'">'.$params['_body'].'</span>';

		$this->func->add_tag_head('swfobject.js');
		$f_file = $this->cont['HOME_URL'] . 'gate.php?way=ref&_nodos&page=' . rawurlencode($lvar['page']) .
					'&src=' . rawurlencode($lvar['name']);
		$obj_p = 'var params = {' . join(',', $f_p) . '};';
		$obj_a = 'var attributes = {' . join(',', $f_a) . '};';
		$js = <<<_HTML_

// <![CDATA[
(function(){var flashvars = {}; {$obj_p} {$obj_a}
swfobject.embedSWF("$f_file", "$cid", "{$img['width']}", "{$img['height']}", "9.0.0", "{$this->cont['HOME_URL']}skin/loader.php?src=expressInstall.swf",flashvars,params,attributes);})();
// ]]>

_HTML_;
		$this->func->add_js_var_head($js);
		$this->func->add_js_var_head($js);
	}

	// div�����
	function wrap_div($text, $align, $around) {
		if ($around) {
			$style = 'width:auto;' . (($align === 'right') ? 'float:right;' : 'float:left;');
		} else {
			$style = ($align !== 'none')? 'text-align:' . $align . ';' : '';
		}
		return "<div style=\"$style\"><div class=\"img_margin\">$text</div></div>\n";
	}
	// �Ȥ����
	// margin:auto Moz1=x(wrap,around�������ʤ�),op6=oNN6=x(wrap,around�������ʤ�)IE6=x(wrap,around�������ʤ�)
	// margin:0px Moz1=x(wrap�Ǵ󤻤������ʤ�),op6=x(wrap�Ǵ󤻤������ʤ�),nn6=x(wrap�Ǵ󤻤������ʤ�),IE6=o
	function wrap_table($text, $align, $around) {
		$margin = ($around ? '0px' : 'auto');
		$margin_align = ($align == 'center') ? '' : ";margin-$align:0px";
		return "<table class=\"style_table\" style=\"margin:$margin$margin_align\">\n<tr><td class=\"style_td\">\n$text\n</td></tr>\n</table>\n";
	}

	//-----------------------------------------------------------------------------
	// �ե����륿���פ�Ƚ��
	function get_type(& $lvar, & $args, & $params) {
		// $lvar['type']
		// 1:URL����
		// 2:URL����¾
		// 3:ź�ղ���
		// 4:ź�եե�å���
		// 5:ź�դ���¾
		
		if ($this->func->is_url($lvar['name'])) {
			$lvar['isurl'] = $lvar['name'];
			// URL
			if (! $params['noimg'] &&
				! $this->cont['PKWK_DISABLE_INLINE_IMAGE_FROM_URI'] &&
				preg_match($this->cont['PLUGIN_REF_IMAGE_REGEX'], $lvar['name'])) {
				// ����
				if ($params['nocache']) {
					// ����å��夷�ʤ�����
					$lvar['type'] = 2;
				} else {
					// ����å��夹��
					$this->cache_image_fetch($lvar);
					if ($lvar['file']) {
						// ����å���OK
						if ($this->is_picture($lvar['file'])) {
							$lvar['type'] = 3;
						} else {
							$lvar['type'] = 5;
						}
					} else {
						// ����å���NG
						$lvar['type'] = 2;
					}
				}
			} else {
				// URL�����ʳ�
				$lvar['type'] = 2;
			}
		} else {
			// ź�եե�����
			// �ڡ���̾�ȥե�����̾��������
			// ź�եե�����
			if (! is_dir($this->cont['UPLOAD_DIR'])) {
				$params['_error'] = 'No UPLOAD_DIR';
				return;
			}
			
			if (!empty($args[0]))
			// Try the second argument, as a page-name or a path-name
			$_page = $this->func->get_fullname($this->func->strip_bracket($args[0]), $lvar['page']); // strip is a compat

			$matches = array();
			// �ե�����̾�˥ڡ���̾(�ڡ������ȥѥ�)����������Ƥ��뤫
			//   (Page_name/maybe-separated-with/slashes/ATTACHED_FILENAME)
			if (preg_match('#^(.+)/([^/]+)$#', $lvar['name'], $matches)) {
				if ($matches[1] == '.' || $matches[1] == '..') {
					$matches[1] .= '/'; // Restore relative paths
				}
				// �ڡ���ID�Ǥλ���
				if (preg_match('/^#(\d+)$/', $matches[1], $arg)) {
					$matches[1] = $this->func->get_name_by_pgid($arg[1]);
				}
				
				$lvar['name'] = $matches[2];
				$lvar['page'] = $this->func->get_fullname($this->func->strip_bracket($matches[1]), $lvar['page']); // strip is a compat
				$lvar['file'] = $this->cont['UPLOAD_DIR'] . $this->func->encode($lvar['page']) . '_' . $this->func->encode($lvar['name']);
				$is_file = @ is_file($lvar['file']);
	
			// ��������ʹߤ�¸�ߤ�������ϥڡ���̾
			} else if (!empty($args[0]) && $this->func->is_page($_page)) {
				$e_name = $this->func->encode($lvar['name']);

				// Try the second argument, as a page-name or a path-name
				$lvar['file'] = $this->cont['UPLOAD_DIR'] .  $this->func->encode($_page) . '_' . $e_name;
				$is_file_second = @ is_file($lvar['file']);
	
				//if ($is_file_second && $is_bracket_bracket) {
				if ($is_file_second) {
					// Believe the second argument (compat)
					array_shift($args);
					$lvar['page'] = $_page;
					$is_file = TRUE;
				} else {
					// Try default page, with default params
					$is_file_default = @ is_file($this->cont['UPLOAD_DIR'] . $this->func->encode($lvar['page']) . '_' . $e_name);
	
					// Promote new design
					if ($is_file_default && $is_file_second) {
						// Because of race condition NOW
						$params['_error'] = htmlspecialchars('The same file name "' .
						$lvar['name'] . '" at both page: "' .  $lvar['page'] . '" and "' .  $_page .
						'". Try ref(pagename/filename) to specify one of them');
					} else {
						// Because of possibility of race condition, in the future
						$params['_error'] = 'The style ref(filename,pagename) is ambiguous ' .
						'and become obsolete. ' .
						'Please try ref(pagename/filename)';
					}
					return;
				}
			} else {
				// Simple single argument
				$lvar['file'] = $this->cont['UPLOAD_DIR'] . $this->func->encode($lvar['page']) . '_' . $this->func->encode($lvar['name']);
				$is_file = @ is_file($lvar['file']);
			}
			if (! $is_file) {
				if ($this->root->render_mode !== 'render' && !$this->func->is_page($lvar['page'])) {
					$params['_error'] = $this->root->_msg_notfound . '(' . htmlspecialchars($lvar['page']) .  ')';
				} else {
					if (strlen($lvar['name']) < 252) {
						$params['_error'] = 'File not found.';
					} else {
						$params['_error'] = 'File name is too long.';
					}
				}
				return;
			}
			
			// ���ե��������
			$lvar['status'] = $this->get_fileinfo($lvar['file']);

			if ($lvar['status']['noinline'] > 0 || $params['noinline']) {
				$params['noimg'] = TRUE;
			} else if ($lvar['status']['noinline'] > -1) {
				if ($this->is_flash($lvar['file'])) {
					// Flash �Υ���饤��ɽ�����¥����å�
					if ($this->cont['PLUGIN_REF_FLASH_INLINE'] === 0) {
						// ���٤ƶػ�
						$params['noimg'] = TRUE;
					} else if ($this->cont['PLUGIN_REF_FLASH_INLINE'] === 1) {
						// �����ͽ�ͭ�Τߵ���
						if (! $lvar['status']['admins']) {
							$params['noimg'] = TRUE;
						}
					} else if ($this->cont['PLUGIN_REF_FLASH_INLINE'] === 2) {
						// ��Ͽ�桼������ͭ�Τߵ���
						if (! $lvar['status']['owner']) {
							$params['noimg'] = TRUE;
						}
					} 
				}
			}

			if (!$params['noimg'] && $this->is_picture($lvar['file'])) {
				$lvar['type'] = 3;
			} else if ($this->is_flash($lvar['file'])) {
				$params['_title'] = array();
				if ($lvar['status']['copyright'] || $params['noimg']) {
					$lvar['type'] = 5;
				} else {
					$lvar['type'] = 4;
				}
			} else {
				$lvar['type'] = 5;
			}
		}
		return;
	}
	
	// ź�դ��줿�ե����뤬�������ɤ���
	function is_picture($file) {
		$size = $this->getimagesize($file);
		if (is_array($size)) {
			if ($size[2] > 0 && $size[2] < 4) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		return FALSE;
	}
	// Flash���ɤ���
	function is_flash($file) {
		if ($this->func->is_url($file))
		{
			return FALSE;
		}

		$size = $this->getimagesize($file);
		if ($size[2] === 4 || $size[2] === 13) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function get_show_imagesize (& $img, & $params) {
		// ���ꤵ�줿����������Ѥ���
		$width = $img['org_w'];
		$height = $img['org_h'];
		if (!$params['_%'] && $params['_size']) {
			if ($width === 0 && $height === 0) {
				$width  = $params['_w'];
				$height = $params['_h'];
			} else if ($params['zoom']) {
				$_w = $params['_w'] ? $width  / $params['_w'] : 0;
				$_h = $params['_h'] ? $height / $params['_h'] : 0;
				$zoom = max($_w, $_h);
				$params['_%'] = round(100 / $zoom);
			} else {
				$width  = $params['_w'] ? $params['_w'] : $width;
				$height = $params['_h'] ? $params['_h'] : $height;
			}
		}
		if ($params['_%']) {
			if (!empty($params['_max']) && $params['_%'] > 100) {
				$width = $img['org_w'];
				$height = $img['org_h'];
			} else {
				$width  = (int)($width  * $params['_%'] / 100);
				$height = (int)($height * $params['_%'] / 100);
			}
			$params['_%'] = round($params['_%']);
		}
		
		$img['title'] = "SIZE:{$img['org_w']}x{$img['org_h']}({$params['fsize']})";
		$img['info'] = ($width && $height)? ' width="'.$width.'" height="'.$height.'"' : '';
		$img['width'] = $width;
		$img['height'] = $height;
	}
	
	// ź�եե�����������
	function get_fileinfo($file)
	{
		static $ret = array();
		
		if (isset($ret[$this->xpwiki->pid][$file])) return $ret[$this->xpwiki->pid][$file];
		
		$ret[$this->xpwiki->pid][$file] = $this->load_attach_log($file);
		//$ret[$this->xpwiki->pid][$file] = $this->func->get_attachstatus($file);
		
		return $ret[$this->xpwiki->pid][$file];
	}

	// attach ���ե��������
	function load_attach_log($file) {
		$status = array(
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
		if (file_exists($file.'.log'))
		{
			$data = array_pad(file($file.'.log'), count($status), '');
			foreach ($status as $key=>$value)
			{
				$status[$key] = chop(array_shift($data));
			}
			$status['count'] = explode(',',$status['count']);
			$status['imagesize'] = @ unserialize($status['imagesize']);
		}
		return $status;
	}

	// ���᡼�������������
	function getimagesize($file) {
		// ź�եե�����ϻ����˥ե����륿���פ򸡺� (getimagesize �ϥ����Ȥ��⤤)
		if (strpos($file, $this->cont['UPLOAD_DIR']) === 0) {
			$status = $this->get_fileinfo($file);
			if ($status) {
				return $status['imagesize'];
			}
			return FALSE;
		} else {
			// URL �ξ�硢����������Ƥߤ�
			return @ getimagesize($file);
		}
	}
	
	// ��������å��夬���뤫Ĵ�٤�
	function cache_image_fetch (& $lvar) {
		$parse = parse_url($lvar['name']);
		$name = $parse['host']."_".basename($parse['path']);
		$filename = $this->cont['UPLOAD_DIR'].$this->func->encode($lvar['page'])."_".$this->func->encode($name);
		
		$cache = FALSE;
		$size = array();
		if (!file_exists($filename)) {
			//$dat = $this->func->http_request($lvar['name']);
			$ht = new Hyp_HTTP_Request();
			$ht->init();
			$ht->ua = 'Mozilla/5.0';
			$ht->url = $lvar['name'];
			$ht->get();

			if ($ht->rc == 200 && $ht->data) {
				$dat['data'] = $ht->data;
				// �������ȳ��Υե����������ݸ��
				$copyright = ! $this->func->refcheck(0,$lvar['name']);
			} else {
				// �ե����뤬�����Ǥ��ʤ��Τ� noimage �Ȥ���
				$copyright = 0;
				$dat['data'] = file_get_contents($this->cont['IMAGE_DIR'].'noimage.png');
			}
			if ($this->cache_image_save($dat['data'], $lvar['page'], $filename, $name, $copyright)) {
				$cache = TRUE;
			}
		} else {
			// ���Ǥ˥���å���Ѥ�
			$cache = TRUE;
		}
		if ($cache) {
			$lvar['name'] = $name;
			$lvar['file'] = $filename;
			
			// ���ե��������
			$lvar['status'] = array('count'=>array(0),'age'=>'','pass'=>'','freeze'=>FALSE,'copyright'=>FALSE,'owner'=>0,'ucd'=>'','uname'=>'','md5'=>'','admins'=>0,'org_fname'=>'');
			
			if (file_exists($lvar['file'].'.log'))
			{
				$data = file($lvar['file'].'.log');
				foreach ($lvar['status'] as $key=>$value)
				{
					$lvar['status'][$key] = chop(array_shift($data));
				}
				$lvar['status']['count'] = explode(',',$lvar['status']['count']);
			}
		} else {
			$lvar['file'] = '';
		}
		return;
	}

	// ��������å������¸
	function cache_image_save(& $data, $page, $filename, $name, $copyright)
	{
		$attach = $this->func->get_plugin_instance('attach');
		if (!$attach || !method_exists($attach, 'do_upload')) {
			return FALSE;
		}
		
		$fp = fopen($filename.".tmp", "wb");
		fwrite($fp, $data);
		fclose($fp);
		
		$options = array('asSystem' => TRUE);
		$attach->do_upload($page,$name,$filename.".tmp",$copyright,NULL,TRUE,$options);
		
		return TRUE;
	}

	// ����ͥ�����������
	function make_thumb($url,$s_file,$width,$height)
	{
		return HypCommonFunc::make_thumb($url,$s_file,$width,$height,"1,95");
	}
	
	function get_file_extention($filename) {
		$ext = strtolower(preg_replace('/^.*\.([^.]+)$/', '$1', $filename));
		switch($ext) {
			case 'svg':
				$type = 'image/svg+xml';
				break;
			//case 'pdf':
			//	$type = 'application/pdf';
			//	break;
			default:
				$type = '';
		}
		return array($ext, $type);
	}

/*
	// ���ץ�������Ϥ���
	function check_arg($val, & $params)
	{
		if ($val == '') {
			return;
		}
	
		foreach (array_keys($params) as $key) {
			if (strpos($key, strtolower($val)) === 0) {
				$params[$key] = TRUE;
				return;
			}
		}
	
		$params['_args'][] = $val;
	}
*/

	// ��ĥ�ѥ�᡼�����ν���
	function check_arg_ex (& $params, & $lvar) {
		foreach ($params['_args'] as $arg){
			$m = array();
			if (preg_match("/^(m)?w(?:idth)?:([0-9]+)$/i",$arg,$m)){
				$params['_size'] = TRUE;
				$params['_w'] = $m[2];
				$params['zoom'] = ($m[1])? TRUE: FALSE;
				$params['_max'] = $params['zoom'];
			} else if (preg_match("/^(m)?h(?:eight)?:([0-9]+)$/i",$arg,$m)){
				$params['_size'] = TRUE;
				$params['_h'] = $m[2];
				$params['zoom'] = ($m[1])? TRUE: FALSE;
				$params['_max'] = $params['zoom'];
			} else if (preg_match("/^([0-9.]+)%$/i",$arg,$m)){
				$params['_%'] = $m[1];
			} else if (preg_match("/^t:(.*)$/i",$arg,$m)){
				$m[1] = htmlspecialchars(str_replace("&amp;quot;","",$m[1]));
				if ($m[1]) $lvar['title'][] = $m[1];
			} else if (preg_match('/^([0-9]+)x([0-9]+)$/', $arg, $m)) {
				$params['_size'] = TRUE;
				$params['_w'] = $m[1];
				$params['_h'] = $m[2];
			} else {
				$params['_title'][] = $arg;
			}
		}
	}
	
	function make_uploadlink(& $params, & $lvar, $args) {
		
		$params['_error'] = '';
		
		$this->fetch_options($params, $args, $lvar);

		if ($params['btn']) {
			if (strtolower(substr($params['btn'], -4)) === "auth") {
				$params['btn'] = rtrim(substr($params['btn'], 0, strlen($params['btn'])-4),':');
				$params['auth'] = TRUE;
			}
		}
		if (! $params['btn']) {
			$params['btn'] = '[' . $this->root->_LANG['skin']['upload'] . ']';
		} else {
			$params['btn'] = htmlspecialchars($params['btn']);
		}

		if (! $_attach = $this->func->get_plugin_instance('attach')) {
			$params['_body'] = $params['btn'];
			return;
		}

		if (($params['auth'] || $this->cont['ATTACH_UPLOAD_EDITER_ONLY']) && ($this->cont['PKWK_READONLY'] === 1 || ! $this->func->check_editable($lvar['page'], FALSE, FALSE))) {
			$params['_body'] = $params['btn'];
		} else {
			$returi = ($this->root->render_mode !== 'render')? '' :
				'&amp;returi='.rawurlencode($_SERVER['REQUEST_URI']);
			$name = (!empty($lvar['refid']))? '&amp;refid=' . rawurlencode($lvar['refid']) : (($lvar['name'])? '&amp;filename=' . rawurlencode($lvar['name']) : '');
			$params['_body'] = '<a href="'.$this->root->script.
				'?plugin=attach&amp;pcmd=upload'.$name.
				'&amp;page='.rawurlencode($lvar['page']).
				$returi.
				'" title="'.$this->root->_LANG['skin']['upload'].'">'.
				'<img src="'.$this->cont['IMAGE_DIR'].'file.png" width="20" height="20" alt="'.$this->root->_LANG['skin']['upload'].'" title="'.$this->root->_LANG['skin']['upload'].'">'.
				$params['btn'].'</a>';
		}
	}
	
	function fetch_options (& $params, $args, & $lvar) {
		// �Ĥ�ΰ����ν���
		parent::fetch_options($params, $args);
		
		// ��ĥ�ѥ�᡼�����ν���
		$this->check_arg_ex($params, $lvar);
		
		//���饤�����Ƚ��
		if ($params['right']) {
			$params['_align'] = 'right';
		} else if ($params['left']) {
			$params['_align'] = 'left';
		} else if ($params['center']) {
			$params['_align'] = 'center';
		}
	}
}
?>