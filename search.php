<?php

eval( '

function '. $mydirname .'_global_search( $keywords , $andor , $limit , $offset , $userid )
{
	// for XOOPS Search module
	static $readed = FALSE;
	if($readed) { return array() ; }
	$readed = TRUE;
	return xpwiki_global_search_base( "'.$mydirname.'" , $keywords , $andor , $limit , $offset , $userid ) ;
}

' ) ;


if( ! function_exists( 'xpwiki_global_search_base' ) ) {

function xpwiki_global_search_base( $mydirname , $keywords , $andor , $limit , $offset , $userid )
{
	// ɬ�פʥե�������ɤ߹���
	$mytrustdirpath = dirname( __FILE__ ) ;
	include_once "$mytrustdirpath/include.php";
	
	// XpWiki ���֥������Ⱥ���
	$xpwiki = new XpWiki($mydirname);
	
	// xoopsSearch extension �ɤ߹���
	$xpwiki->load_extensions("xoopsSearch");
	
	// �����
	$xpwiki->init();
	
	// �ǡ�������
	return $xpwiki->extension->xoopsSearch->get ( $keywords , $andor , $limit , $offset , $userid );
}

}


?>