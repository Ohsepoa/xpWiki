<?php
/*
 * Created on 2012/01/14 by nao-pon http://hypweb.net/
 * $Id: bbcode_image.php,v 1.1 2012/01/14 11:56:35 nao-pon Exp $
 */

$_patterns = $_replaces = array();

// BB Code url
$_patterns[] = '/\[url=([\'"]?)((?:ht|f)tp[s]?:\/\/[!~*\'();\/?:\@&=+\$,%#\w.-]+)\\1\](.+)\[\/url\]/esUS';
$_replaces[] = '\'[[\'.XpWikiFunc::nl2br(\'$3\',true).\':$2]]\'';

$_patterns[] = '/\[url=([\'"]?)([!~*\'();\/?:\@&=+\$,%#\w.-]+)\\1\](.+)\[\/url\]/esUS';
$_replaces[] = '\'[[\'.XpWikiFunc::nl2br(\'$3\',true).\':http://$2]]\'';

$_patterns[] = '/\[siteurl=([\'"]?)([!~*\'();\/?:\@&=+\$,%#\w.-]+)\\1\](.+)\[\/siteurl\]/esUS';
$_replaces[] = '\'[[\'.XpWikiFunc::nl2br(\'$3\',true).\':http:///$2]]\'';

// BB Code image with align
$_patterns[] = '/\[img\s+align=([\'"]?)(left|center|right)\1(?:\s+title=([\'"]?)([^\'"][^\]\s]*?)\3)?(?:\s+w(?:idth)?=([\'"]?)([\d]+?)\5)?(?:\s+h(?:eight)?=([\'"]?)([\d]+?)\7)?]([!~*\'();\/?:\@&=+\$,%#\w.-]+)\[\/img\]/US';
$_replaces[] = '&ref($9,$2,"t:$4",mw:$6,mw:$8);';

// BB Code image normal
$_patterns[] = '/\[img(?:\s+title=([\'"]?)([^\'"][^\]\s]*?)\1)?(?:\s+w(?:idth)?=([\'"]?)([\d]+?)\3)?(?:\s+h(?:eight)?=([\'"]?)([\d]+?)\5)?]([!~*\'();\/?:\@&=+\$,%#\w.-]+)\[\/img\]/US';
$_replaces[] = '&ref($7,"t:$2",mw:$4,mw:$6);';

// BB Code siteimage with align
$_patterns[] = '/\[siteimg\s+align=([\'"]?)(left|center|right)\1(?:\s+title=([\'"]?)([^\'"][^\]\s]*?)\3)?(?:\s+w(?:idth)?=([\'"]?)([\d]+?)\5)?(?:\s+h(?:eight)?=([\'"]?)([\d]+?)\7)?]\/?([!~*\'();?\@&=+\$,%#\w.-][!~*\'();\/?\@&=+\$,%#\w.-]+?)\[\/siteimg\]/US';
$_replaces[] = '&ref('.$this->cont['ROOT_URL'].'/$9,$2,"t:$4",mw:$6,mw:$8);';

// BB Code siteimage normal
$_patterns[] = '/\[siteimg(?:\s+title=([\'"]?)([^\'"][^\]\s]*?)\1)?(?:\s+w(?:idth)?=([\'"]?)([\d]+?)\3)?(?:\s+h(?:eight)?=([\'"]?)([\d]+?)\5)?]\/?([!~*\'();?\@&=+\$,%#\w.-][!~*\'();\/?\@&=+\$,%#\w.-]+?)\[\/siteimg\]/US';
$_replaces[] = '&ref('.$this->cont['ROOT_URL'].'/$7,"t:$2",mw:$4,mw:$6);';

$root->str_rules['bbcode_image'] = array($_patterns, $_replaces);
unset($_patterns, $_replaces);