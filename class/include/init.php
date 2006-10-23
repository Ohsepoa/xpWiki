<?php

$root = & $this->root;
$const = & $this->cont;

$const['S_VERSION'] = $root->module['version'];
$const['S_COPYRIGHT'] = 
	'<strong>xpWiki ' . $const['S_VERSION'] . '</strong>' .
	' Copyright ' .
	$root->module['credits'] .
	' License is GPL.<br />' .
	' Based on "PukiWiki" 1.4.8_alpha';

/////////////////////////////////////////////////
// Init server variables

foreach (array('SCRIPT_NAME', 'SERVER_ADMIN', 'SERVER_NAME',
	'SERVER_PORT', 'SERVER_SOFTWARE') as $key) {
	if (!defined($key)) {
		define($key, isset($_SERVER[$key]) ? $_SERVER[$key] : '');
	}
	//unset(${$key}, $_SERVER[$key], $HTTP_SERVER_VARS[$key]);
}

/////////////////////////////////////////////////
// Init grobal variables

$root->foot_explain = array();	// Footnotes
$root->related      = array();	// Related pages
$root->head_tags    = array();	// XHTML tags in <head></head>
$root->head_precsses= array();	// XHTML CSS tags in <head></head> before skin's CSS.

// UI_LANG - Content encoding for buttons, menus,  etc
//$const['UI_LANG'] = $const['LANG']; // 'en' for Internationalized wikisite
$const['UI_LANG'] = $this->get_accept_language();

/////////////////////////////////////////////////
// INI_FILE: LANG �˴�Ť����󥳡��ǥ�������

switch ($const['LANG']){
case 'en':
	// Internal content encoding = Output content charset (for skin)
	$const['CONTENT_CHARSET'] = 'iso-8859-1'; // 'UTF-8', 'iso-8859-1', 'EUC-JP' or ...
	// mb_language (for mbstring extension)
	$const['MB_LANGUAGE'] = 'English';	// 'uni'(means UTF-8), 'English', or 'Japanese'
	// Internal content encoding (for mbstring extension)
	$const['SOURCE_ENCODING'] = 'ASCII';	// 'UTF-8', 'ASCII', or 'EUC-JP'
	break;
	
case 'ja': // EUC-JP
	$const['CONTENT_CHARSET'] = 'EUC-JP';
	$const['MB_LANGUAGE'] = 'Japanese';
	$const['SOURCE_ENCODING'] = 'EUC-JP';
	break;

default:
	$this->die_message('No such language "' . LANG . '"'.memory_get_usage());
}

mb_language($const['MB_LANGUAGE']);
mb_internal_encoding($const['SOURCE_ENCODING']);
ini_set('mbstring.http_input', 'pass');
mb_http_output('pass');
mb_detect_order('auto');

/////////////////////////////////////////////////
// INI_FILE: Require LANG_FILE

$const['LANG_FILE_HINT'] = $const['DATA_HOME'] . 'private/lang/' . $const['LANG'] . '.lng.php';	// For encoding hint
$const['LANG_FILE'] = $const['DATA_HOME'] . 'private/lang/' . $const['UI_LANG'] . '.lng.php';	// For UI resource
$die = '';

$langfiles = array($const['LANG_FILE_HINT'], $const['LANG_FILE']);
array_unique($langfiles);
foreach ($langfiles as $langfile) {
	if (! file_exists($langfile) || ! is_readable($langfile)) {
		$die .= 'File is not found or not readable. (' . $langfile . ')' . "\n";
	} else {
		require($langfile);
	}
}
if ($die) $this->die_message(nl2br("\n\n" . $die));

/////////////////////////////////////////////////
// LANG_FILE: Init encoding hint

$const['PKWK_ENCODING_HINT'] = isset($_LANG['encode_hint'][$const['LANG']]) ? $_LANG['encode_hint'][$const['LANG']] : '';
//unset($_LANG['encode_hint']);

/////////////////////////////////////////////////
// LANG_FILE: Init severn days of the week

$root->weeklabels = $root->_msg_week;

/////////////////////////////////////////////////
// INI_FILE: Init $script

$root->script = str_replace(XOOPS_ROOT_PATH, XOOPS_URL, $root->mydirpath)."/";

if (isset($root->script)) {
	$this->get_script_uri($root->script); // Init manually
} else {
	$root->script = $this->get_script_uri(); // Init automatically
}

/////////////////////////////////////////////////
// INI_FILE: $agents:  UserAgent�μ���

$root->ua = 'HTTP_USER_AGENT';
$root->user_agent = $matches = array();

$root->user_agent['agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
//unset(${$ua}, $_SERVER[$ua], $HTTP_SERVER_VARS[$ua], $ua);	// safety

foreach ($root->agents as $agent) {
	if (preg_match($agent['pattern'], $root->user_agent['agent'], $matches)) {
		$root->user_agent['profile'] = isset($agent['profile']) ? $agent['profile'] : '';
		$root->user_agent['name']    = isset($matches[1]) ? $matches[1] : '';	// device or browser name
		$root->user_agent['vers']    = isset($matches[2]) ? $matches[2] : ''; // 's version
		break;
	}
}
unset($root->agents);

// Profile-related init and setting
$const['UA_PROFILE'] = isset($root->user_agent['profile']) ? $root->user_agent['profile'] : '';

$const['UA_INI_FILE'] = $const['DATA_HOME'] .'private/ini/'. $const['UA_PROFILE'] . '.ini.php';
if (! file_exists($const['UA_INI_FILE']) || ! is_readable($const['UA_INI_FILE'])) {
	$this->die_message('UA_INI_FILE for "' . $const['UA_PROFILE'] . '" not found.');
} else {
	require($const['UA_INI_FILE']); // Also manually
}

$const['UA_NAME'] = isset($user_agent['name']) ? $user_agent['name'] : '';
$const['UA_VERS'] = isset($user_agent['vers']) ? $user_agent['vers'] : '';
unset($user_agent);	// Unset after reading UA_INI_FILE

/////////////////////////////////////////////////
// �ǥ��쥯�ȥ�Υ����å�

$die = '';
foreach(array($const['DATA_DIR'], $const['DIFF_DIR'], $const['BACKUP_DIR'], $const['CACHE_DIR']) as $dir){
	if (! is_writable($dir))
		$die .= 'Directory is not found or not writable (' . $dir . ')' . "\n";
}

// ����ե�������ѿ������å�
$temp = '';
foreach(array('rss_max', 'page_title', 'note_hr', 'related_link', 'show_passage',
	'rule_related_str', 'load_template_func') as $var){
	if (! isset($root->{$var})) $temp .= '$' . $var . "\n";
}
if ($temp) {
	if ($die) $die .= "\n";	// A breath
	$die .= 'Variable(s) not found: (Maybe the old *.ini.php?)' . "\n" . $temp;
}

$temp = '';
foreach(array($const['LANG'], $const['PLUGIN_DIR']) as $def){
	if (! isset($def)) $temp .= $def . "\n";
}
if ($temp) {
	if ($die) $die .= "\n";	// A breath
	$die .= 'Define(s) not found: (Maybe the old *.ini.php?)' . "\n" . $temp;
}

if($die) $this->die_message(nl2br("\n\n" . $die));
unset($die, $temp);

// ����ڡ���ɽ���⡼��
if (!empty($const['page_show'])) {
	
	$get['cmd']  = $post['cmd']  = $vars['cmd']  = 'read';
	$get['page'] = $post['page'] = $vars['page'] = $const['page_show'];

} else {

	/////////////////////////////////////////////////
	// ɬ�ܤΥڡ�����¸�ߤ��ʤ���С����Υե�������������
	
	foreach(array($root->defaultpage, $root->whatsnew, $root->interwiki) as $page){
		if (! $this->is_page($page)) touch($this->get_filename($page));
	}
	
	/////////////////////////////////////////////////
	// �������餯���ѿ��Υ����å�
	
	// Prohibit $_GET attack
	foreach (array('msg', 'pass') as $key) {
		if (isset($_GET[$key])) die_message('Sorry, already reserved: ' . $key . '=');
	}
	
	// Expire risk
	unset($HTTP_GET_VARS, $HTTP_POST_VARS);	//, 'SERVER', 'ENV', 'SESSION', ...
	unset($_REQUEST);	// Considered harmful
	
	// Remove null character etc.
	$_GET    = $this->input_filter($_GET);
	$_POST   = $this->input_filter($_POST);
	$_COOKIE = $this->input_filter($_COOKIE);
	
	// ʸ���������Ѵ� ($_POST)
	// <form> ���������줿ʸ�� (�֥饦�������󥳡��ɤ����ǡ���) �Υ����ɤ��Ѵ�
	// POST method �Ͼ�� form ��ͳ�ʤΤǡ�ɬ���Ѵ�����
	//
	if (isset($_POST['encode_hint']) && $_POST['encode_hint'] != '') {
		// do_plugin_xxx() ����ǡ�<form> �� encode_hint ��Ź���Ǥ���Τǡ�
		// encode_hint ���Ѥ��ƥ����ɸ��Ф��롣
		// ���Τ򸫤ƥ����ɸ��Ф���ȡ������¸ʸ���䡢̯�ʥХ��ʥ�
		// �����ɤ������������ˡ������ɸ��Ф˼��Ԥ��붲�줬���롣
		$encode = mb_detect_encoding($_POST['encode_hint']);
		mb_convert_variables($const['SOURCE_ENCODING'], $encode, $_POST);
	
	} else if (isset($_POST['charset']) && $_POST['charset'] != '') {
		// TrackBack Ping �ǻ��ꤵ��Ƥ��뤳�Ȥ�����
		// ���ޤ������ʤ����ϼ�ư���Ф��ڤ��ؤ�
		if (mb_convert_variables($const['SOURCE_ENCODING'],
		    $_POST['charset'], $_POST) !== $_POST['charset']) {
			mb_convert_variables($const['SOURCE_ENCODING'], 'auto', $_POST);
		}
	
	} else if (! empty($_POST)) {
		// �����ޤȤ�ơ���ư���С��Ѵ�
		mb_convert_variables($const['SOURCE_ENCODING'], 'auto', $_POST);
	}
	
	// ʸ���������Ѵ� ($_GET)
	// GET method �� form ����ξ��ȡ�<a href="http://script/?key=value> �ξ�礬����
	// <a href...> �ξ��ϡ������С��� rawurlencode ���Ƥ���Τǡ��������Ѵ�������
	if (isset($_GET['encode_hint']) && $_GET['encode_hint'] != '')
	{
		// form ��ͳ�ξ��ϡ��֥饦�������󥳡��ɤ��Ƥ���Τǡ������ɸ��С��Ѵ���ɬ�ס�
		// encode_hint ���ޤޤ�Ƥ���Ϥ��ʤΤǡ�����򸫤ơ������ɸ��Ф����塢�Ѵ����롣
		// ��ͳ�ϡ�post ��Ʊ��
		$encode = mb_detect_encoding($_GET['encode_hint']);
		mb_convert_variables($const['SOURCE_ENCODING'], $encode, $_GET);
	}
	
	
	/////////////////////////////////////////////////
	// QUERY_STRING�����
	
	// cmd��plugin����ꤵ��Ƥ��ʤ����ϡ�QUERY_STRING��
	// �ڡ���̾��InterWikiName�Ǥ���Ȥߤʤ�
	$arg = '';
	if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
		$arg = $_SERVER['QUERY_STRING'];
	} else if (isset($_SERVER['argv']) && ! empty($_SERVER['argv'])) {
		$arg = $_SERVER['argv'][0];
	}
	if ($const['PKWK_QUERY_STRING_MAX'] && strlen($arg) > $const['PKWK_QUERY_STRING_MAX']) {
		// Something nasty attack?
		$this->pkwk_common_headers();
		sleep(1);	// Fake processing, and/or process other threads
		echo('Query string too long');
		exit;
	}
	$arg = $this->input_filter($arg); // \0 ����
	
	// unset QUERY_STRINGs
	// Now use plugin or xoops. 
	//foreach (array('QUERY_STRING', 'argv', 'argc') as $key) {
	////	unset(${$key}, $_SERVER[$key], $HTTP_SERVER_VARS[$key]);
	//	unset(${$key}, $_SERVER[$key]);
	//}
	// $_SERVER['REQUEST_URI'] is used at func.php NOW
	//unset($REQUEST_URI, $HTTP_SERVER_VARS['REQUEST_URI']);
	
	// mb_convert_variables�ΥХ�(?)�к�: ������Ϥ��ʤ��������
	$arg = array($arg);
	mb_convert_variables($const['SOURCE_ENCODING'], 'auto', $arg);
	$arg = $arg[0];
	
	/////////////////////////////////////////////////
	// QUERY_STRING��ʬ�򤷤ƥ������Ѵ�����$_GET �˾��
	
	// URI �� urlencode ���������Ϥ��������н褹��
	$matches = array();
	foreach (explode('&', $arg) as $key_and_value) {
		if (preg_match('/^([^=]+)=(.+)/', $key_and_value, $matches) &&
		    mb_detect_encoding($matches[2]) != 'ASCII') {
			$_GET[$matches[1]] = $matches[2];
		}
	}
	unset($matches);
	
	/////////////////////////////////////////////////
	// GET, POST, COOKIE
	
	$get    = & $_GET;
	$post   = & $_POST;
	$cookie = & $_COOKIE;
	
	// GET + POST = $vars
	if (empty($_POST)) {
		$vars = & $_GET;  // Major pattern: Read-only access via GET
	} else if (empty($_GET)) {
		$vars = & $_POST; // Minor pattern: Write access via POST etc.
	} else {
		$vars = array_merge($_GET, $_POST); // Considered reliable than $_REQUEST
	}
	
	// ���ϥ����å�: cmd, plugin ��ʸ����ϱѿ����ʳ����ꤨ�ʤ�
	foreach(array('cmd', 'plugin') as $var) {
		if (isset($vars[$var]) && ! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $vars[$var]))
			unset($get[$var], $post[$var], $vars[$var]);
	}
	
	// ����: page, strip_bracket()
	if (isset($vars['page'])) {
		$get['page'] = $post['page'] = $vars['page']  = $this->strip_bracket($vars['page']);
	} else {
		$get['page'] = $post['page'] = $vars['page'] = '';
	}
	
	// ����: msg, ���Ԥ������
	if (isset($vars['msg'])) {
		$get['msg'] = $post['msg'] = $vars['msg'] = str_replace("\r", '', $vars['msg']);
	}
	
	// �����ߴ��� (?md5=...)
	if (isset($vars['md5']) && $vars['md5'] != '') {
		$get['cmd'] = $post['cmd'] = $vars['cmd'] = 'md5';
	}
	
	// TrackBack Ping
	if (isset($vars['tb_id']) && $vars['tb_id'] != '') {
		$get['cmd'] = $post['cmd'] = $vars['cmd'] = 'tb';
	}
	
	// cmd��plugin����ꤵ��Ƥ��ʤ����ϡ�QUERY_STRING��ڡ���̾��InterWikiName�Ǥ���Ȥߤʤ�
	if (! isset($vars['cmd']) && ! isset($vars['plugin'])) {
	
		$get['cmd']  = $post['cmd']  = $vars['cmd']  = 'read';
	
		if ($arg == '') $arg = $root->defaultpage;
		$arg = rawurldecode($arg);
		// XOOPS �� redirect_header ���ղä���뤳�Ȥ����� &�ʹߤ���
		$arg = preg_replace("/&.*$/", "", $arg);
		$arg = $this->strip_bracket($arg);
		$arg = $this->input_filter($arg);
		$get['page'] = $post['page'] = $vars['page'] = $arg;
	}
	
	// ���ϥ����å�: 'cmd=' prohibits nasty 'plugin='
	if (isset($vars['cmd']) && isset($vars['plugin']))
		unset($get['plugin'], $post['plugin'], $vars['plugin']);
		
	//exit ($vars['cmd']);
}
$root->get =& $get;
$root->post =& $post;
$root->vars =& $vars;

/////////////////////////////////////////////////
// �������($WikiName,$BracketName�ʤ�)
// $WikiName = '[A-Z][a-z]+(?:[A-Z][a-z]+)+';
// $WikiName = '\b[A-Z][a-z]+(?:[A-Z][a-z]+)+\b';
// $WikiName = '(?<![[:alnum:]])(?:[[:upper:]][[:lower:]]+){2,}(?![[:alnum:]])';
// $WikiName = '(?<!\w)(?:[A-Z][a-z]+){2,}(?!\w)';

// BugTrack/304�����н�
$root->WikiName = '(?:[A-Z][a-z]+){2,}(?!\w)';

// $BracketName = ':?[^\s\]#&<>":]+:?';
$root->BracketName = '(?!\s):?[^\r\n\t\f\[\]<>#&":]+:?(?<!\s)';

// InterWiki
$root->InterWikiName = '(\[\[)?((?:(?!\s|:|\]\]).)+):(.+)(?(1)\]\])';

// ���
$root->NotePattern = '/\(\(((?:(?>(?:(?!\(\()(?!\)\)(?:[^\)]|$)).)+)|(?R))*)\)\)/ex';

/////////////////////////////////////////////////
// �������(�桼������롼���ɤ߹���)
require($const['DATA_HOME'] . 'private/ini/rules.ini.php');

/////////////////////////////////////////////////
// �������(����¾�Υ����Х��ѿ�)

// ���߻���
$root->now = $this->format_date($const['UTIME']);

// �ե������ޡ�����$line_rules�˲ä���
if ($root->usefacemark) $root->line_rules += $root->facemark_rules;
//unset($facemark_rules);

// ���λ��ȥѥ����󤪤�ӥ����ƥ�ǻ��Ѥ���ѥ������$line_rules�˲ä���
//$entity_pattern = '[a-zA-Z0-9]{2,8}';
$root->entity_pattern = trim(join('', file($const['CACHE_DIR'] . 'entities.dat')));

$root->line_rules = array_merge(array(
	'&amp;(#[0-9]+|#x[0-9a-f]+|' . $root->entity_pattern . ');' => '&$1;',
	"\r"          => '<br />' . "\n",	/* �����˥�����ϲ��� */
	'#related$'   => '<del>#related</del>',
	'^#contents$' => '<del>#contents</del>'
), $root->line_rules);

$root->digest = "";
?>