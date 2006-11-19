<?php
//
// Created on 2006/11/19 by nao-pon http://hypweb.net/
// $Id: recentchanges.inc.php,v 1.1 2006/11/19 11:22:15 nao-pon Exp $
//
class xpwiki_plugin_recentchanges extends xpwiki_plugin {
	function plugin_recentchanges_init () {

	}
	
	function plugin_recentchanges_action()
	{
		$where = $this->func->get_readable_where();
		
		$where = ($where)? " WHERE (editedtime!=0) AND (name NOT LIKE ':%') AND ($where)" : " WHERE (editedtime!=0) AND (name NOT LIKE ':%')";
	
		$query = "SELECT * FROM ".$this->xpwiki->db->prefix($this->root->mydirname."_pginfo").$where." ORDER BY editedtime DESC LIMIT {$this->root->maxshow};";
		$res = $this->xpwiki->db->query($query);
		
		if ($res)
		{
			$date = $items = "";
			$cnt = 0;
			$items = '<ul class="list1" style="padding-left:16px;margin-left:16px">';
			while($data = mysql_fetch_row($res))
			{
				$lastmod = $this->func->format_date($data[3]);
				$tb_tag = ($this->root->trackback)? "<a href=\"$script?plugin=tb&amp;__mode=view&amp;tb_id=".tb_get_id($data[1])."\" title=\"TrackBack\">TB(".$this->func->tb_count($data[1]).")</a> - " : "";
				$items .="<li>$lastmod - ".$tb_tag.$this->func->make_pagelink($data[1])."</li>\n";
			}
			$items .= '</ul>';
	
		}
		
		//$ret['msg'] = make_search($whatsnew)." Last $maxshow";
		$ret['msg'] = $this->root->whatsnew." Last {$this->root->maxshow}";
		$ret['body'] = $items;
		return $ret;
	}
}
?>