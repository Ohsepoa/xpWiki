<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: popular.inc.php,v 1.2 2006/12/02 13:47:58 nao-pon Exp $
//

/*
 * PukiWiki popular �ץ饰����
 * (C) 2002, Kazunori Mizushima <kazunori@uc.netyou.jp>
 *
 * �͵��Τ���(������������¿��)�ڡ����ΰ����� recent �ץ饰����Τ褦��ɽ�����ޤ���
 * �̻�����Ӻ������̤��ư������뤳�Ȥ��Ǥ��ޤ���
 * counter �ץ饰����Υ�������������Ⱦ����ȤäƤ��ޤ���
 *
 * [������]
 * #popular
 * #popular(20)
 * #popular(20,FrontPage|MenuBar)
 * #popular(20,FrontPage|MenuBar,true)
 * #popular(20,FrontPage|MenuBar,true,XOOPS)
 * #popular(20,FrontPage|MenuBar,true,,1)
 *
 * [����]
 * 1 - ɽ��������                                    default 10
 * 2 - ɽ�������ʤ��ڡ���(Ⱦ�ѥ��ڡ����ޤ��� | ���ڤ�) default �ʤ�
 * 3 - ����(true)���̻�(false)�ΰ������Υե饰         default false
 * 4 - �����оݤβ��۳��إڡ���̾                      default �ʤ�
 * 5 - ¿���إڡ����ξ�硢�ǲ��ؤΤߤ�ɽ�� ( 0 or 1 ) default 0
 */

class xpwiki_plugin_popular extends xpwiki_plugin {
	
	function plugin_popular_init()
	{
		$this->cont['PLUGIN_POPULAR_DEFAULT'] =  10;
	}
	
	function plugin_popular_convert()
	{
		
		$max = $this->cont['PLUGIN_POPULAR_DEFAULT'];
		$except = '';
	
		$array = func_get_args();
		$today = FALSE;
		$prefix = "";
		$compact = 0;
	
		switch (func_num_args()) {
		case 5:
			if ($array[4]) $compact = 1;
		case 4:
			$prefix = $array[3];
			$prefix = preg_replace("/\/$/","",$prefix);
		case 3:
			if ($array[2])
				$today = $this->func->get_date('Y/m/d');
		case 2:
			$except = $array[1];
			$except = str_replace(array("&#124;","&#x7c;"," "),"|",$except);
		case 1:
			$max = $array[0];
		}
	
		$nopage = "";
		if ($except)
		{
			$excepts = explode("|",$except);
			foreach($excepts as $_except)
			{
				if (substr($_except,-1) == "/")
				{
					$_except .= "%";
				}
				$nopage .= " AND (p.name NOT LIKE '$_except')";
			}
		}
		$counters = array();
		
		$where = $this->func->get_readable_where('p.');

		if ($prefix)
		{
			$prefix = $this->func->strip_bracket($prefix);
			if ($where)
				$where = " (p.name LIKE '$prefix/%') AND ($where)";
			else
				$where = " p.name LIKE '$prefix/%'";
		}
	
		if ($where) $where = " AND ($where)";
		if ($today)
		{
			$where = " WHERE (c.pgid = p.pgid) AND (p.name NOT LIKE ':%') AND (today = '$today')$nopage$where";
			$sort = "today_count";
		}
		else
		{
			$where = " WHERE (c.pgid = p.pgid) AND (p.name NOT LIKE ':%')$nopage$where";
			$sort = "count";
		}
		//echo $where;
		$query = "SELECT p.`name`, c.`count`, c.`today_count` FROM ".$this->xpwiki->db->prefix($this->root->mydirname."_pginfo")." as p , ".$this->xpwiki->db->prefix($this->root->mydirname."_count")." as c $where ORDER BY $sort DESC LIMIT $max;";
		$res = $this->xpwiki->db->query($query);
		//echo $query."<br>";
		if ($res)
		{
			while($data = mysql_fetch_row($res))
			{
				//echo $data[0]."<br>";
				if ($today)
					$counters["_$data[0]"] = $data[2];
				else
					$counters["_$data[0]"] = $data[1];
			}
		}
	
	
		$items = '';
		if ($prefix)
		{
			$bypege = " [ ".$this->func->make_pagelink($prefix,$prefix)." ]";
			$prefix .= "/";
			$prefix = preg_quote($prefix,"/");
		}
		else
			$bypege = "";
		
		if (count($counters))
		{
			$_style = $this->root->_ul_left_margin + $this->root->_ul_margin;
			$_style = " style=\"margin-left:". $_style ."px;padding-left:". $_style ."px;\"";
			$items = '<ul class="popular_list"'.$_style.'">';
			$new_mark = "";
			
			foreach ($counters as $page=>$count) {
				$page = htmlspecialchars(substr($page,1));
				//New�ޡ����ղ�
				if ($this->func->exist_plugin_inline("new"))
					$new_mark = $this->func->do_plugin_inline("new","{$page},nolink",$_dum);
				
				if ($compact)
					$page = $this->func->make_pagelink($page,basename($page));
				else
				{
					if ($prefix)
						$page = $this->func->make_pagelink($page,preg_replace("/^$prefix/","",$page));
					else
						$page = $this->func->make_pagelink($page);
				}
				
				$items .= " <li>".$page."<span class=\"counter\">($count)</span>$new_mark</li>\n";
				}
			$items .= '</ul>';
		}
		//return sprintf($today ? $this->root->_popular_plugin_today_frame : $this->root->_popular_plugin_frame,count($counters),$bypege,$items);
		return sprintf($today ? $this->root->_popular_plugin_today_frame : $this->root->_popular_plugin_frame, count($counters), $items, $bypege);

	}
}
?>