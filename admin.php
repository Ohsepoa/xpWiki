<?php

$mytrustdirname = basename( dirname( __FILE__ ) ) ;
$mytrustdirpath = dirname( __FILE__ ) ;

// check $xoopsModule
if ( ! is_object( @$xoopsModule ) ) {
	$module_handler =& xoops_gethandler('module');
	$xoopsModule =& $module_handler->getByDirname($mydirname);
}

// check permission of 'module_admin' of this module
$moduleperm_handler =& xoops_gethandler( 'groupperm' ) ;

if( ! is_object( @$xoopsUser ) || ! $moduleperm_handler->checkRight( 'module_admin' , $xoopsModule->getVar( 'mid' ) , $xoopsUser->getGroups() ) ) die( 'only admin can access this area' ) ;

$xoopsOption['pagetype'] = 'admin' ;
require XOOPS_ROOT_PATH.'/include/cp_functions.php' ;

// language files (admin.php)
$language = empty( $xoopsConfig['language'] ) ? 'english' : $xoopsConfig['language'] ;
if( file_exists( "$mydirpath/language/$language/admin.php" ) ) {
	// user customized language file
	include_once "$mydirpath/language/$language/admin.php" ;
} else if( file_exists( "$mytrustdirpath/language/$language/admin.php" ) ) {
	// default language file
	include_once "$mytrustdirpath/language/$language/admin.php" ;
} else {
	// fallback english
	include_once "$mytrustdirpath/language/english/admin.php" ;
}

if( ! empty( $_GET['lib'] ) ) {
	// common libs (eg. altsys)
	$lib = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , $_GET['lib'] ) ;
	$page = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_GET['page'] ) ;
	
	if( file_exists( XOOPS_TRUST_PATH.'/libs/'.$lib.'/'.$page.'.php' ) ) {
		include XOOPS_TRUST_PATH.'/libs/'.$lib.'/'.$page.'.php' ;
	} else if( file_exists( XOOPS_TRUST_PATH.'/libs/'.$lib.'/index.php' ) ) {
		include XOOPS_TRUST_PATH.'/libs/'.$lib.'/index.php' ;
	} else {
		die( 'wrong request' ) ;
	}
} else {
	// fork each pages of this module
	$page = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_GET['page'] ) ;
	
	if( file_exists( "$mytrustdirpath/admin/$page.php" ) ) {
		include "$mytrustdirpath/admin/$page.php" ;
	} else if( file_exists( "$mytrustdirpath/admin/index.php" ) ) {
		include "$mytrustdirpath/admin/index.php" ;
	} else {
		die( 'wrong request' ) ;
	}
}

?>