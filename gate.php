<?php
/*
 * Created on 2007/06/29 by nao-pon http://hypweb.net/
 * $Id: gate.php,v 1.8 2010/05/10 02:31:23 nao-pon Exp $
 */

/*
 * $mydirname      : Module dirname
 * $mydirpath      : Module dirpath
 * $mytrustdirname : Trust dirname (xpwiki)
 */

@ ignore_user_abort(FALSE);

$xwGateOption['nocommonAllowWays'] = array('x2w');
$xwGateOption['nodosAllowWays'] = array('ref', 'fusen', 'dump');
$xwGateOption['noumbAllowWays'] = array('ref', 'attach');
$xwGateOption['hypmodeAllowWays'] = array('w2x');

$mytrustdirpath = dirname( __FILE__ ) ;

$way = (isset($_GET['way']))? $_GET['way'] : ((isset($_POST['way']))? $_POST['way'] : '');
$way = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , $way);

if ($xwGateOption['xmode']) {
	if (!in_array($way, $xwGateOption['nocommonAllowWays'])) xpWikiGate_goOut(400);
}

if ($xwGateOption['nodos']) {
	if (!in_array($way, $xwGateOption['nodosAllowWays'])) xpWikiGate_goOut(400);
}

if ($xwGateOption['noumb']) {
	if (!in_array($way, $xwGateOption['noumbAllowWays'])) xpWikiGate_goOut(400);
}

if (isset($xwGateOption['hypmode']) && $xwGateOption['hypmode']) {
	if (!in_array($way, $xwGateOption['hypmodeAllowWays'])) xpWikiGate_goOut(400);
}

$file_php = $mytrustdirpath . '/ways/' . $way . '.php';
if (file_exists($file_php)) {
	include $file_php;
} else {
	xpWikiGate_goOut(204);
}

function xpWikiGate_goOut($err) {
	error_reporting(0);
	while( ob_get_level() ) {
		ob_end_clean() ;
	}
	$str = '';
	switch($err) {
		case 204:
			header( 'HTTP/1.0 204 No Content' );
			break;
		case 400:
			header( 'HTTP/1.0 400 Bad Request' );
			$str = 'Bad Request.';
			break;
		default:
			header( 'HTTP/1.0 404 Not Found' );
			$str = 'Not Found.';
	}
	header( 'Content-Length: ' . strlen($str) );
	exit($str);
}
?>