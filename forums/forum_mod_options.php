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
	print "{$lang['forum_mod_options_access']}";
	exit();
}


  //-------- Action: Lock topic

  if ($action == "locktopic")
  {
    $forumid = 0+$_GET["forumid"];
    $topicid = 0+$_GET["topicid"];
    $page = 0+$_GET["page"];

    if (!is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      stderr("{$lang['forum_mod_options_user_error']}", "{$lang['forum_mod_options_incorrect']}");

    @mysql_query("UPDATE topics SET locked='yes' WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    header("Location: {$TBDEV['baseurl']}/forums.php?action=viewforum&forumid=$forumid&page=$page");

    die;
  }

  //-------- Action: Unlock topic

  if ($action == "unlocktopic")
  {
    $forumid = 0+$_GET["forumid"];

    $topicid = 0+$_GET["topicid"];

    $page = 0+$_GET["page"];

    if (!is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      stderr("{$lang['forum_mod_options_user_error']}", "{$lang['forum_mod_options_incorrect']}");

    @mysql_query("UPDATE topics SET locked='no' WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    header("Location: {$TBDEV['baseurl']}/forums.php?action=viewforum&forumid=$forumid&page=$page");

    die;
  }

  //-------- Action: Set locked on/off

  if ($action == "setlocked")
  {
    $topicid = (int)$_POST["topicid"];

    if (!$topicid || get_user_class() < UC_MODERATOR)
      stderr("{$lang['forum_mod_options_user_error']}", "{$lang['forum_mod_options_incorrect']}");

    $locked = sqlesc($_POST["locked"]);
    
    @mysql_query("UPDATE topics SET locked=$locked WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    header("Location: {$TBDEV['baseurl']}/forums.php?action=viewtopic&topicid=$topicid");

    die;
  }

  //-------- Action: Set sticky on/off

  if ($action == "setsticky")
  {
    $topicid = (int)$_POST["topicid"];

    if (!$topicid || get_user_class() < UC_MODERATOR)
      stderr("{$lang['forum_mod_options_user_error']}", "{$lang['forum_mod_options_incorrect']}");

    $sticky = sqlesc($_POST["sticky"]);
    
    @mysql_query("UPDATE topics SET sticky=$sticky WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    header("Location: {$TBDEV['baseurl']}/forums.php?action=viewtopic&topicid=$topicid");

    die;
  }

  //-------- Action: Rename topic

  if ($action == 'renametopic')
  {
  	if (get_user_class() < UC_MODERATOR)
  	  stderr("{$lang['forum_mod_options_user_error']}", "{$lang['forum_mod_options_incorrect']}");

  	$topicid = (int)$_POST['topicid'];

  	if (!is_valid_id($topicid))
  	  stderr("{$lang['forum_mod_options_user_error']}", "{$lang['forum_mod_options_incorrect']}");

  	$subject = $_POST['subject'];

  	if ($subject == '')
  	  stderr("{$lang['forum_mod_options_error']}","{$lang['forum_mod_options_new_title']}");

  	$subject = sqlesc(trim(strip_tags($subject)));

  	@mysql_query("UPDATE topics SET subject=$subject WHERE id=$topicid") or sqlerr();

  	$returnto = $_POST['returnto'];

  	if ($returnto)
  	  header("Location: {$TBDEV['baseurl']}/forums.php?action=viewtopic&topicid=$topicid");

  	die;
  }

  //-------- Action: Delete topic

  if ($action == "deletetopic")
  {
    $topicid = isset($_POST["topicid"]) ? (int)$_POST["topicid"] : 0;
    $forumid = isset($_POST["forumid"]) ? (int)$_POST["forumid"] : 0;

    if (!is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      stderr("{$lang['forum_mod_options_user_error']}", "{$lang['forum_mod_options_incorrect']}");

    $sure = isset($_POST["sure"]) ? $_POST["sure"] : 0;

    if (!$sure)
    {
      
      $HTMLOUT = "<table>
      <tr>
        <td align='right'>{$lang['forum_mod_options_sanity']}</td>
      </tr>
      <tr>
        <td>
          <form method='post' action='forums.php?action=deletetopic'>
          <input type='hidden' name='action' value='deletetopic' />
          <input type='hidden' name='topicid' value='$topicid' />
          <input type='hidden' name='forumid' value='$forumid' />
          <input type='checkbox' name='sure' value='1' />{$lang['forum_mod_options_sure']}
          <input type='submit' value={$lang['forum_mod_options_ok']} />
          </form>
        </td>
      </tr>
	    </table>\n";
	    
      print stdhead("{$lang['forum_mod_options_delete']}") . $HTMLOUT . stdfoot();
      exit();
    }

    @mysql_query("DELETE FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    @mysql_query("DELETE FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);

    header("Location: {$TBDEV['baseurl']}/forums.php?action=viewforum&forumid=$forumid");

    die;
  }


  //-------- Action: Move topic

  if ($action == "movetopic")
  {
    $forumid = (int)$_POST["forumid"];

    $topicid = (int)$_POST["topicid"];

    if (!is_valid_id($forumid) || !is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      stderr("{$lang['forum_mod_options_user_error']}", "{$lang['forum_mod_options_incorrect']}");

    // Make sure topic and forum is valid

    $res = @mysql_query("SELECT minclasswrite FROM forums WHERE id=$forumid") or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) != 1)
      stderr("{$lang['forum_mod_options_error']}", "{$lang['forum_mod_options_notfound']}");

    $arr = mysql_fetch_row($res);

    if (get_user_class() < $arr[0])
      stderr("{$lang['forum_mod_options_user_error']}", "{$lang['forum_mod_options_incorrect']}");

    $res = @mysql_query("SELECT subject,forumid FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) != 1)
      stderr("{$lang['forum_mod_options_error']}", "{$lang['forum_mod_options_topic_notfound']}");

    $arr = mysql_fetch_assoc($res);

    if ($arr["forumid"] != $forumid)
      @mysql_query("UPDATE topics SET forumid=$forumid WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

    // Redirect to forum page

    header("Location: {$TBDEV['baseurl']}/forums.php?action=viewforum&forumid=$forumid");

    die;
  }

?>