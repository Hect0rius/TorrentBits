<?php
/*
+------------------------------------------------
|   TBDev.net BitTorrent Tracker PHP
|   =============================================
|   by CoLdFuSiOn
|   (c) 2003 - 2009 TBDev.Net
|   http://www.tbdev.net
|   =============================================
|   svn: http://sourceforge.net/projects/tbdevnet/
|   Licence Info: GPL
+------------------------------------------------
|   $Date$
|   $Revision$
|   $Author$
|   $URL$
+------------------------------------------------
*/
if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "{$lang['forum_search_access']}";
	exit();
}

    $topicsperpage = 25;
    
    $HTMLOUT = '';
    
    $HTMLOUT .= "{$lang['forum_search_search']}";
    
    $keywords = isset($_GET["keywords"]) ? trim($_GET["keywords"]) : '';
    
    if ($keywords != "")
    {
      $perpage = 50;
      
      $page = isset($_GET["page"]) ? (int)$_GET["page"] : 0;
      
      $ekeywords = sqlesc($keywords);
      
      $HTMLOUT = '';
      
      $HTMLOUT .= sprintf($lang['forum_search_searched'], htmlspecialchars($keywords) );
      
      $res = mysql_query("SELECT COUNT(*) FROM posts WHERE MATCH (body) AGAINST ($ekeywords)") or sqlerr(__FILE__, __LINE__);
      
      $arr = mysql_fetch_row($res);
      
      $hits = 0 + $arr[0];
      
      if ($hits == 0)
      {
        $HTMLOUT .= "{$lang['forum_search_not_found']}";
      }
      else
      {
       /* $pages = 0 + ceil($hits / $perpage);
        
        if ($page > $pages)
        { 
          $page = $pages;
        }
        
        $pagemenu1 = '';
        
        for ($i = 1; $i <= $pages; ++$i)
        {
          if ($page == $i)
            $pagemenu1 .= "<font class='gray'><b>$i</b></font>\n";
          else
            $pagemenu1 .= "<a href='forums.php?action=search&amp;keywords=" . htmlspecialchars($keywords) . "&amp;page=$i'><b>$i</b></a>\n";
        }
        
        if ($page == 1)
          $pagemenu2 = "<font class='gray'>{$lang['forum_search_prev']}</font>\n";
        else
          $pagemenu2 = "<a href='forums.php?action=search&amp;keywords=" . htmlspecialchars($keywords) . "&amp;page=" . ($page - 1) . "'>{$lang['forum_search_prev']}</a>\n";
          
        $pagemenu2 .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
        
        if ($page == $pages)
          $pagemenu2 .= "<font class='gray'><b>{$lang['forum_search_next']}</b></font>\n";
        else
          $pagemenu2 .= "<a href='forums.php?action=search&amp;keywords=" . htmlspecialchars($keywords) . "&amp;page=" . ($page + 1) . "'>{$lang['forum_search_next']}</a>\n";
          
        $offset = ($page * $perpage) - $perpage; */
        
//------ Make page menu
          require_once "include/pager.php";
                  $pagemenu = pager( 
                  array( 
                  'count'  => $hits,
                  'perpage'    => $perpage,
                  'start_value'  => $page,
                  'url'    => "forums.php?action=search&amp;keywords=" . htmlspecialchars($keywords)
                        )
                  );
        
        $res = mysql_query("SELECT p.id, p.topicid, p.userid, p.added, t.forumid, t.subject, 
                            f.name, f.minclassread, u.username
                            FROM posts p
                            LEFT JOIN topics t ON t.id = p.topicid
                            LEFT JOIN forums f ON t.forumid = f.id
                            LEFT JOIN users u ON p.userid = u.id
                            WHERE MATCH ( p.body )
                            AGAINST ($ekeywords) LIMIT $page,$perpage") 
                            or sqlerr(__FILE__, __LINE__);
        
        $num = mysql_num_rows($res);
        
        $HTMLOUT .= "<div style='align:center;margin-bottom:10px;'>$pagemenu</div><br /><br />
        <table border='1' cellspacing='0' cellpadding='5'>
          <tr>
            <td class='colhead'>{$lang['forum_search_post']}</td>
            <td class='colhead' align='left'>{$lang['forum_search_topic']}</td>
            <td class='colhead' align='left'>{$lang['forum_search_forum']}</td>
            <td class='colhead' align='left'>{$lang['forum_search_posted']}</td>
          </tr>\n";
          
        while ( $post = mysql_fetch_assoc($res) )
        {
          
          
          //$res2 = @mysql_query("SELECT forumid, subject FROM topics WHERE id={$post['topicid']}") or
            //sqlerr(__FILE__, __LINE__);
            
          //$topic = mysql_fetch_assoc($res2);
          
          //$res2 = @mysql_query("SELECT name,minclassread FROM forums WHERE id={$topic['forumid']}") or
            //sqlerr(__FILE__, __LINE__);
            
          //$forum = mysql_fetch_assoc($res2);
          /*
          if ($post["name"] == "" || $post["minclassread"] > $CURUSER["class"])
          {
            --$hits;
            continue;
          }
          */
          //$res2 = mysql_query("SELECT username FROM users WHERE id={$post['userid']}") or
            //sqlerr(__FILE__, __LINE__);
            
          //$user = mysql_fetch_assoc($res2);
          
          if ($post["username"] == "")
          {
            $post["username"] = "[{$post['userid']}]";
          } 
            $HTMLOUT .= "<tr>
            <td>{$post['id']}</td>
            <td align='left'><a href='forums.php?action=viewtopic&amp;topicid={$post['topicid']}&amp;page=p{$post['id']}#{$post['id']}'><b>" . htmlspecialchars($post["subject"]) . "</b></a></td>
            <td align='left'><a href='forums.php?action=viewforum&amp;forumid={$post['forumid']}'><b>" . htmlspecialchars($post["name"]) . "</b></a></td>
            <td align='left'><a href='userdetails.php?id={$post['userid']}'><b>{$post['username']}</b></a><br />at ".get_date($post['added'], '')."</td>
            </tr>\n";
          
        }
        $HTMLOUT .= "</table>
        <p>$pagemenu</p>
        <p>".sprintf($lang['forum_search_found'], $hits) . ($hits != 1 ? "s" : "") . ".</p>
        <p><b>{$lang['forum_search_again']}</b></p>\n";
      }
    }
    $HTMLOUT .= "<form method='get' action='forums.php?'>
    <input type='hidden' name='action' value='search' />
    <table border='1' cellspacing='0' cellpadding='5'>
      <tr>
        <td class='rowhead'>{$lang['forum_search_words']}</td>
        <td align='left'>
        <input type='text' size='55' name='keywords' value='" . htmlspecialchars($keywords) .
"' /><br /><font class='small' size='-1'>{$lang['forum_search_3chars']}</font></td>
      </tr>
      <tr>
        <td align='center' colspan='2'><input type='submit' value='{$lang['forum_search_search_btn']}' class='btn' /></td>
      </tr>
    </table>
    </form>\n";
    
    print stdhead("{$lang['forum_search_forum_search']}") . $HTMLOUT . stdfoot();
    die;
	
?>