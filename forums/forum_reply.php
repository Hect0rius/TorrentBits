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
	print "{$lang['forum_reply_access']}";
	exit();
}


  //-------- Action: Reply
if ($action == "reply")
  {
    $topicid = isset($_GET["topicid"]) ? (int)$_GET["topicid"] : 0;

    if (!is_valid_id($topicid))
      header("Location: {$TBDEV['baseurl']}/forums.php");
    
    $q = @mysql_query( "SELECT t.id, f.minclassread, f.minclasswrite 
                        FROM topics t
                        LEFT JOIN forums f ON t.forumid = f.id
                        WHERE t.id = $topicid");

    if( mysql_num_rows($q) != 1 )
      stderr("{$lang['forum_reply_user_error']}", "{$lang['forum_reply_select_topic']}");
    
    $check = @mysql_fetch_assoc($q);
    
    if( $CURUSER['class'] < $check['minclassread'] OR $CURUSER['class'] < $check['minclasswrite'] )
      stderr("{$lang['forum_reply_user_error']}", "{$lang['forum_reply_permission']}");
    
    $HTMLOUT = '';

    $HTMLOUT .= begin_main_frame();

    $HTMLOUT .= insert_compose_frame($topicid, false);

    $HTMLOUT .= end_main_frame();

    print stdhead("{$lang['forum_reply_reply']}") . $HTMLOUT . stdfoot();

    die;
}

  //-------- Action: Quote

if ($action == "quotepost")
	{
		$topicid = isset($_GET["topicid"]) ? (int)$_GET["topicid"] : 0;

		if (!is_valid_id($topicid))
			header("Location: {$TBDEV['baseurl']}/forums.php");

    $q = @mysql_query( "SELECT t.id, f.minclassread, f.minclasswrite 
                        FROM topics t
                        LEFT JOIN forums f ON t.forumid = f.id
                        WHERE t.id = $topicid");

    if( mysql_num_rows($q) != 1 )
      stderr("{$lang['forum_reply_user_error']}", "{$lang['forum_reply_select_topic']}");
    
    $check = @mysql_fetch_assoc($q);
    
    if( $CURUSER['class'] < $check['minclassread'] OR $CURUSER['class'] < $check['minclasswrite'] )
      stderr("{$lang['forum_reply_user_error']}", "{$lang['forum_reply_permission']}");
    
    $HTMLOUT = '';

    $HTMLOUT .= begin_main_frame();

    $HTMLOUT .= insert_compose_frame($topicid, false, true);

    $HTMLOUT .= end_main_frame();

    print stdhead("{$lang['forum_reply_reply']}") . $HTMLOUT . stdfoot();

    die;
}

?>