<?php
//
// Created on 2006/10/25 by nao-pon http://hypweb.net/
// $Id: loader.php,v 1.16 2007/09/11 06:24:44 nao-pon Exp $
//

error_reporting(0);

// �֥饦������å���ͭ������(��)
$maxage = 86400; // 60*60*24 (1day)

// �ѿ������
$src   = preg_replace("/[^\w.-]+/","",@ $_GET['src']);
$prefix = (isset($_GET['b']))? 'b_' : '';
$prefix = (isset($_GET['r']))? 'r_' : '';
$gzip_fname = $addcss = $dir = $out = $type = $src_file = '';
$length = $addcsstime = $facetagtime = 0;
$face_remake = $js_replace = $replace = false;
$root_path = dirname($skin_dirname);
$cache_path = $root_path.'/private/cache/';
$face_cache = $cache_path . 'facemarks.js';

if (preg_match("/^(.+)\.([^.]+)$/",$src,$match)) {
	$type = $match[2];
	$src = $match[1];
	if (substr($src, -5) === '.page') {
		$type = 'pagecss';
		$src = substr($src, 0, strlen($src) - 5);
	}
}

if (!$type || !$src) {
	header( 'HTTP/1.1 404 Not Found' );
	exit();
}

$basedir = ($type === "png" || $type === "gif")? "image/" : "";

if (file_exists("{$skin_dirname}/{$basedir}{$type}/{$src}.{$type}")) {
	if ($type !== 'css') {
		// html¦�˻���ե����뤬����С�����˥�����쥯��
		header("Location: {$basedir}{$type}/{$src}.{$type}");
		exit();
	} else {
		// CSS �Ͼ��
		$addcss = join('', file("{$skin_dirname}/{$basedir}{$type}/{$src}.{$type}"));
		$addcsstime = filemtime("{$skin_dirname}/{$basedir}{$type}/{$src}.{$type}");
	}
}

switch ($type) {
	case 'css':
		$c_type = 'text/css';
		$dir = $prefix.basename($root_path);
		$replace = true;
		$gzip_fname = $cache_path.$src.'_'.$dir.'.'.$type.'.gz';
		break;
	case 'js':
		if (substr($src,0,7) === "default") {
			$js_replace = true;
			$replace = true;
			if (!file_exists(dirname(__FILE__).'/skin/js/'.$src.'.js')) {
				$src = 'default.en';
			}
		} else 	if ($src === 'main') {
			$face_remake = ! file_exists($face_cache);
			if ($face_remake) {
				$facetagtime = time();
			} else {
				$facetagtime = filemtime($face_cache);
			}
			$replace = true;
			$js_replace = true;
		}
		$c_type = 'application/x-javascript';
		$gzip_fname = $cache_path.$src.'.'.$type.'.gz';
		break;
	case 'png':
		$c_type = 'image/png';
		break;
	case 'gif':
		$c_type = 'image/gif';
		break;
	case 'pagecss':
		$c_type = 'text/css';
		$dir = $prefix.basename($root_path);
		$src_file = $root_path . '/private/cache/' . $src . '.css';
		$replace = true;
		$gzip_fname = $cache_path.$src.'_'.$dir.'.'.$type.'.gz';
		break;
	case 'xml':
		$c_type = 'application/xml; charset=utf-8';
		break;
	default:
		exit();
}

if (!$src_file) {
	$src_file = dirname(__FILE__)."/skin/{$basedir}{$type}/".preg_replace("/[^\w.]/","",$src).".$type";
}

if (file_exists($src_file)) {
	
	$filetime = max(filemtime($src_file), $addcsstime, $facetagtime);

	$etag = md5($type.$dir.$src.$filetime);
		
	// �֥饦���Υ���å��������å�
	if ($etag == @$_SERVER['HTTP_IF_NONE_MATCH']) {
		header( 'HTTP/1.1 304 Not Modified' );
		header( 'Cache-Control: max-age=' . $maxage );
		header( 'Etag: '. $etag );
		exit();
	}

	// gzip ���������Բ�ǽ?
	if (! preg_match('/\b(gzip)\b/i', $_SERVER['HTTP_ACCEPT_ENCODING'])
		|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'safari') !== false
	) {
		$gzip_fname = '';
	}
	
	// html¦/private/cache �� ͭ���� gzip �ե����뤬������
	if ($gzip_fname && file_exists($gzip_fname) && filemtime($gzip_fname) >= $filetime) {
	
		header( 'Content-Type: ' . $c_type );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $filetime ) . ' GMT' );
		header( 'Cache-Control: max-age=' . $maxage );
		header( 'Etag: '. $etag );
		header( 'Content-length: '.filesize($gzip_fname) );
		header( 'Content-Encoding: gzip' );
		header( 'Vary: Accept-Encoding' );
		
		readfile($gzip_fname);
		exit();
	}
	
	// �ִ�������ɬ��?
	if ($replace) {
		$out = join("",file($src_file));
		if ($dir) {
			$out = str_replace('$dir', $dir, $out . "\n" . $addcss);
			$out = str_replace('$class', 'div.xpwiki_'.$dir, $out);
		}
		if ($type === 'js') {
			if ($src === 'main') {
				if ($face_remake) {
					include XOOPS_ROOT_PATH.'/include/common.php';
					list($face_tag, $face_tag_full) = xpwiki_make_facemarks ($skin_dirname, $face_cache);
				} else {
					list($face_tag, $face_tag_full) = array_pad(file($face_cache), 2, '');
					if (!$face_tag_full) $face_tag_full = $face_tag;
				}
				$out = str_replace(array('$face_tag_full', '$face_tag'), array($face_tag_full, $face_tag), $out);
			}
			if ($js_replace) {
				$xoops_root_path = XOOPS_ROOT_PATH;
				if ( substr(PHP_OS, 0, 3) === 'WIN' ) {
					$root_path = str_replace('\\', '/', $root_path);
					$xoops_root_path = str_replace('\\', '/', $xoops_root_path);
				}
				$out = str_replace('$wikihelper_root_url', str_replace($xoops_root_path, XOOPS_URL, $root_path) , $out);
			}
		}
		$length = strlen($out);
	}
	
	// html¦/private/cache �� gzip ���̤��ƥ���å��夹��
	if ($gzip_fname && function_exists('gzencode')) {
		if (!$replace) {
			$out = join("",file($src_file));
		}
		if ($gzip_out = gzencode($out)) {
			if ($fp = fopen($gzip_fname, 'wb')) {
				fwrite($fp, $gzip_out);
				fclose($fp);
				touch($gzip_fname, $filetime);
				$is_gz = true;
				$replace = true;
				$out = $gzip_out;
				$length = strlen($out);
			}
		}
	}
	
	if (!$length) { $length = filesize($src_file); }
	
	header( 'Content-Type: ' . $c_type );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $filetime ) . ' GMT' );
	header( 'Cache-Control: max-age=' . $maxage );
	header( 'Etag: '. $etag );
	header( 'Content-length: '.$length );
	if ($is_gz) {
		header( 'Content-Encoding: gzip' );
		header( 'Vary: Accept-Encoding' );
	}
} else {
	header( 'HTTP/1.1 404 Not Found' );
	exit();
}

if ($replace) {
	echo $out;
} else {
	readfile($src_file);
}
exit();

function xpwiki_make_facemarks ($skin_dirname, $cache) {
	include_once XOOPS_TRUST_PATH."/modules/xpwiki/include.php";
	$wiki =& XpWiki::getSingleton( basename(dirname($skin_dirname)) );
	$wiki->init('#RenderMode');
	$tags_full = $tags = array();
	foreach($wiki->root->wikihelper_facemarks as $key => $img) {
		$key = htmlspecialchars($key, ENT_QUOTES);
		$q_key = str_replace("'", "\'", $key);
		if ($img{0} === '*') {
			$img = substr($img, 1);
			$tags_full[] = '\'<img src="'.$img.'" border="0" title="'.$key.'" alt="'.$key.'" onClick="javascript:wikihelper_face(\\\''.$q_key.'\\\');return false;" \'+\'/\'+\'>\'+';
			continue;
		}
		$tags[] = '\'<img src="'.$img.'" border="0" title="'.$key.'" alt="'.$key.'" onClick="javascript:wikihelper_face(\\\''.$q_key.'\\\');return false;" \'+\'/\'+\'>\'+';
		$tags_full[] = '\'<img src="'.$img.'" border="0" title="'.$key.'" alt="'.$key.'" onClick="javascript:wikihelper_face(\\\''.$q_key.'\\\');return false;" \'+\'/\'+\'>\'+';
	}
	$tags = array(join('', $tags) ,join('', $tags_full));
	if ($fp = fopen($cache, 'wb')) {
		fwrite($fp, join("\n", $tags));
		fclose($fp);
	}
	return $tags;
}
?>