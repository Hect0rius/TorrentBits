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
	print "{$lang['forum_view_access']}";
	exit();
}

  //$lang = array_merge( $lang, load_language('forums') );

  //-------- Action: View forum

    $HTMLOUT = '';
    
    $forumid = isset($_GET["forumid"]) ? (int)$_GET["forumid"] : 0;

    if (!is_valid_id($forumid))
      header("Location: {$TBDEV['baseurl']}/forum.php");

    $page = isset($_GET["page"]) ? (int)$_GET["page"] : 0;

    $userid = $CURUSER["id"];

    //------ Get forum name

    $res = @mysql_query("SELECT name, minclassread FROM forums WHERE id=$forumid") or sqlerr(__FILE__, __LINE__);

    if( false == mysql_num_rows($res) )
    {
      header("Location: {$TBDEV['baseurl']}/forums.php");
    }
    
    $arr = mysql_fetch_assoc($res);

    $forumname = $arr["name"];

    if (get_user_class() < $arr["minclassread"])
      header("Location: {$TBDEV['baseurl']}/forums.php");
      //die("Not permitted");

    //------ Page links

/////////////////// Get topic count & Do Pager thang! ////////////////////////////

    $perpage = $CURUSER["topicsperpage"];
    
    if (!$perpage) 
      $perpage = 20;

    $res = @mysql_query("SELECT COUNT(*) FROM topics WHERE forumid=$forumid") or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_row($res);

    $num = $arr[0];

    if ($page == 0)
      $page = 1;

    $first = ($page * $perpage) - $perpage + 1;

    $last = $first + $perpage - 1;

    if ($last > $num)
      $last = $num;

    $pages = floor($num / $perpage);

    if ($perpage * $pages < $num)
      ++$pages;

    //------ Build menu

    $menu = "<p style='text-align:center;'><b>\n";

    $lastspace = false;

    for ($i = 1; $i <= $pages; ++$i)
    {
    	if ($i == $page)
        $menu .= "<font class='gray'>$i</font>\n";

      elseif ($i > 3 && ($i < $pages - 2) && ($page - $i > 3 || $i - $page > 3))
    	{
    		if ($lastspace)
    		  continue;

  		  $menu .= "... \n";

     		$lastspace = true;
    	}

      else
      {
        $menu .= "<a href='forums.php?action=viewforum&amp;forumid=$forumid&amp;page=$i'>$i</a>\n";

        $lastspace = false;
      }
      if ($i < $pages)
        $menu .= "</b>|<b>\n";
    }

    $menu .= "<br />\n";

    if ($page == 1)
      $menu .= "<font class='gray'>{$lang['forum_view_prev']}</font>";

    else
      $menu .= "<a href='forums.php?action=viewforum&amp;forumid=$forumid&amp;page=" . ($page - 1) . "'>{$lang['forum_view_prev']}</a>";

    $menu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    if ($last == $num)
      $menu .= "<font class='gray'>{$lang['forum_view_next']}</font>";

    else
      $menu .= "<a href='forums.php?action=viewforum&amp;forumid=$forumid&amp;page=" . ($page + 1) . "'>{$lang['forum_view_next']}</a>";

    $menu .= "</b></p>\n";

    $offset = $first - 1;
/////////////////// Get topic count & Do Pager thang end! ////////////////////////////

    //------ Get topics data

    $topicsres = @mysql_query("SELECT * FROM topics WHERE forumid=$forumid ORDER BY sticky, lastpost DESC LIMIT $offset,$perpage") or sqlerr(__FILE__, __LINE__);

    

    $numtopics = mysql_num_rows($topicsres);

    $HTMLOUT .= "<h1>$forumname</h1>\n";

    if ($numtopics > 0)
    {
      $HTMLOUT .=  $menu;

      $HTMLOUT .=  "<table border='1' cellspacing='0' cellpadding='5' width='80%'>";

      $HTMLOUT .=  "<tr><td class='colhead' style='align:left;'>{$lang['forum_view_topic']}</td><td class='colhead'>{$lang['forum_view_replies']}</td><td class='colhead'>{$lang['forum_view_views']}</td>\n" .
        "<td class='colhead' style='align:left;'>{$lang['forum_view_author']}</td><td class='colhead' style='align:left;'>{$lang['forum_view_lastpost']}</td>\n";

      $HTMLOUT .=  "</tr>\n";

      while ($topicarr = mysql_fetch_assoc($topicsres))
      {
        $topicid = $topicarr["id"];

        $topic_userid = $topicarr["userid"];

        $topic_views = $topicarr["views"];

        $views = number_format($topic_views);

        $locked = $topicarr["locked"] == "yes";

        $sticky = $topicarr["sticky"] == "yes";

        //---- Get reply count

        $res = mysql_query("SELECT COUNT(*) FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);

        $arr = mysql_fetch_row($res);

        $posts = $arr[0];

        $replies = max(0, $posts - 1);

        $tpages = floor($posts / $postsperpage);

        if ($tpages * $postsperpage != $posts)
          ++$tpages;

        if ($tpages > 1)
        {
          $topicpages = " (<img src=\"{$forum_pic_url}multipage.gif\" alt='' title='' />";

          for ($i = 1; $i <= $tpages; ++$i)
            $topicpages .= " <a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=$i'>$i</a>";

          $topicpages .= ")";
        }
        else
          $topicpages = "";

        //---- Get userID and date of last post

        $res = mysql_query("SELECT * FROM posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);

        $arr = mysql_fetch_assoc($res);

        $lppostid = 0 + $arr["id"];
		
		//..rp..
		$lppostadd = $arr["added"];
		// ..rp..
		
        $lpuserid = 0 + $arr["userid"];

        $lpadded = "<span style='white-space: nowrap;'>" . get_date( $arr['added'],'') . "</span>";

        //------ Get name of last poster

        $res = mysql_query("SELECT * FROM users WHERE id=$lpuserid") or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) == 1)
        {
          $arr = mysql_fetch_assoc($res);

          $lpusername = "<a href='userdetails.php?id=$lpuserid'><b>{$arr['username']}</b></a>";
        }
        else
          $lpusername = sprintf($lang['forum_view_unknown'], $topic_userid);

        //------ Get author

        $res = mysql_query("SELECT username FROM users WHERE id=$topic_userid") or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) == 1)
        {
          $arr = mysql_fetch_assoc($res);

          $lpauthor = "<a href='userdetails.php?id=$topic_userid'><b>{$arr['username']}</b></a>";
        }
        else
          $lpauthor = sprintf($lang['forum_view_unknown'], $topic_userid);

        //---- Print row

        $r = mysql_query("SELECT lastpostread FROM readposts WHERE userid=$userid AND topicid=$topicid") or sqlerr(__FILE__, __LINE__);

        $a = mysql_fetch_row($r);

        $new = !$a || $lppostid > $a[0];

		// ..rp..
		$new = ($lppostadd > (time() - $TBDEV['readpost_expiry'])) ? (!$a || $lppostid > $a[0]) : 0;
		//..rp..

        $topicpic = ($locked ? ($new ? "lockednew" : "locked") : ($new ? "unlockednew" : "unlocked"));

        $subject = ($sticky ? "{$lang['forum_view_sticky']}" : "") . "<a href='forums.php?action=viewtopic&amp;topicid=$topicid'><b>" .
        htmlspecialchars($topicarr["subject"], ENT_QUOTES, 'UTF-8') . "</b></a>$topicpages";

        $HTMLOUT .=  "<tr><td style='align:left;'><table border='0' cellspacing='0' cellpadding='0'><tr>" .
        "<td class='embedded' style='padding-right: 5px'><img src='{$forum_pic_url}{$topicpic}.gif' alt='' title='' />" .
        "</td><td class='embedded' style='align:left;'>\n" .
        "$subject</td></tr></table></td><td style='align:right;'>$replies</td>\n" .
        "<td style='align:right;'>$views</td><td style='align:left;'>$lpauthor</td>\n" .
        "<td style='align:left;'>$lpadded<br />by&nbsp;$lpusername<br /><a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=p$lppostid#$lppostid'>Last Post</a></td>\n";

        $HTMLOUT .=  "</tr>\n";
      } // while

      $HTMLOUT .=  "</table>\n";

      $HTMLOUT .=  $menu;

    } // if
    else
      $HTMLOUT .= "<p style='text-align:center;'>{$lang['forum_view_no_topics']}</p>\n";

    $HTMLOUT .=  "<table class='main' border='0' cellspacing='0' cellpadding='0'><tr valign='middle'>\n";

    $HTMLOUT .=  "<td class='embedded'><img src=\"{$forum_pic_url}unlockednew.gif\" style='margin-right: 5px' alt='' title='' /></td><td class='embedded'>{$lang['forum_view_new_posts']}</td>\n";

    $HTMLOUT .=  "<td class='embedded'><img src=\"{$forum_pic_url}locked.gif\" style='margin-left: 10px; margin-right: 5px' alt='' title='' />" .
    "</td><td class='embedded'>{$lang['forum_view_locked_topic']}</td>\n";

    $HTMLOUT .=  "</tr></table>\n";

    $arr = get_forum_access_levels($forumid) or die;

    $maypost = get_user_class() >= $arr["write"] && get_user_class() >= $arr["create"];

    if (!$maypost)
      $HTMLOUT .=  "<p><i>{$lang['forum_view_permitted']}</i></p>\n";

    $HTMLOUT .=  "<table border='0' class='main' cellspacing='0' cellpadding='0'><tr>\n";

    $HTMLOUT .=  "<td class='embedded'><form method='get' action='forums.php?'><input type='hidden' " .
    "name='action' value='viewunread' /><input type='submit' value='{$lang['forum_view_unread']}' class='btn' /></form></td>\n";

    if ($maypost)
      $HTMLOUT .=  "<td class='embedded'><form method='get' action='forums.php?'><input type='hidden' " .
      "name='action' value='newtopic' /><input type='hidden' name='forumid' " .
      "value='$forumid' /><input type='submit' value='{$lang['forum_view_new_topic']}' class='btn' style='margin-left: 10px' /></form></td>\n";

    $HTMLOUT .=  "</tr></table>\n";

    $HTMLOUT .= insert_quick_jump_menu($forumid);

    print stdhead("{$lang['forum_view_forum_title']}") . $HTMLOUT . stdfoot();

    die;

?>