<?php
//
// Created on 2006/10/29 by nao-pon http://hypweb.net/
// $Id: whatsnew.inc.php,v 1.3 2007/06/01 01:05:34 nao-pon Exp $
//

// DIRNAME_new() �ؿ���ưŪ������
eval( '

function '.$mydirname.'_new( $limit=0, $offset=0 )
{
	return xpwiki_whatsnew_base( "'.$mydirname.'" , $limit, $offset ) ;
}

' ) ;


if (! function_exists('xpwiki_whatsnew_base')) {
	// DIRNAME_new() �ؿ��μ���
	function xpwiki_whatsnew_base( $mydirname, $limit, $offset ) {
	
		// ɬ�פʥե�������ɤ߹���
		$mytrustdirpath = dirname(dirname( __FILE__ )) ;
		include_once "$mytrustdirpath/include.php";
		
		// XpWiki ���֥������Ⱥ���
		$xpwiki = new XpWiki($mydirname);
		
		// whatsnew extension �ɤ߹���
		$xpwiki->load_extensions("whatsnew");
		
		// �����
		$xpwiki->init();
		
		// whatsnew �ǡ�������
		$ret = $xpwiki->extension->whatsnew->get ($limit, $offset);
		
		// ���֥��������˴�
		$xpwiki = null;
		
		return $ret;
	}
}
?>