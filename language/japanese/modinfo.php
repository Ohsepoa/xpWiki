<?php

if( defined( 'FOR_XOOPS_LANG_CHECKER' ) ) $mydirname = 'xpwiki' ;
$constpref = '_MI_' . strtoupper( $mydirname ) ;

if( defined( 'FOR_XOOPS_LANG_CHECKER' ) || ! defined( $constpref.'_LOADED' ) ) {

// a flag for this language file has already been read or not.
define( $constpref.'_LOADED' , 1 ) ;

define( $constpref.'_MODULE_DESCRIPTION' , 'PukiWiki�١�����Wiki�⥸�塼��' ) ;

define( $constpref.'_PLUGIN_CONVERTER' , '�ץ饰�����Ѵ��ġ���' ) ;
define( $constpref.'_SKIN_CONVERTER' , '�������Ѵ��ġ���' ) ;

}


?>