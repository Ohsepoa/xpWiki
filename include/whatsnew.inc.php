<?php
//
// Created on 2006/10/29 by nao-pon http://hypweb.net/
// $Id: whatsnew.inc.php,v 1.2 2007/04/12 05:01:30 nao-pon Exp $
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
		return $xpwiki->extension->whatsnew->get ($limit, $offset);
	}
}
?>