<?php

if( defined( 'FOR_XOOPS_LANG_CHECKER' ) ) $mydirname = 'xpwiki' ;
$constpref = '_MI_' . strtoupper( $mydirname ) ;

if( defined( 'FOR_XOOPS_LANG_CHECKER' ) || ! defined( $constpref.'_LOADED' ) ) {

// a flag for this language file has already been read or not.
define( $constpref.'_LOADED' , 1 ) ;

// Names of blocks for this module (Not all module has blocks)
define( $constpref."_BNAME_A_PAGE","ページ表示 ({$mydirname})");
define( $constpref."_BDESC_A_PAGE","ページ名を指定してその内容をブロックに表示することができます");

define( $constpref.'_MODULE_DESCRIPTION' , 'PukiWikiベースのWikiモジュール' ) ;

define( $constpref.'_PLUGIN_CONVERTER' , 'プラグイン変換ツール' ) ;
define( $constpref.'_SKIN_CONVERTER' , 'スキン変換ツール' ) ;
define( $constpref.'_ADMIN_TOOLS' , '管理用ツール一覧' ) ;

define( $constpref.'_COM_DIRNAME','コメント統合するd3forumのdirname');
define( $constpref.'_COM_FORUM_ID','コメント統合するフォーラムの番号');
}


?>