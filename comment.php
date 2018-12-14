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
require_once("include/bittorrent.php");
require_once "include/user_functions.php";

$action = $_GET["action"];

dbconn(false);


loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('comment') );
    
    if ($action == "add")
    {
      if ($_SERVER["REQUEST_METHOD"] == "POST")
      {
        $torrentid = 0 + $_POST["tid"];
        if (!is_valid_id($torrentid))
          stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");

        $res = @mysql_query("SELECT name FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__,__LINE__);
        $arr = mysql_fetch_array($res,MYSQL_NUM);
        if (!$arr)
          stderr("{$lang['comment_error']}", "{$lang['comment_invalid_torrent']}");

        $text = trim($_POST["text"]);
        if (!$text)
          stderr("{$lang['comment_error']}", "{$lang['comment_body']}");

        @mysql_query("INSERT INTO comments (user, torrent, added, text, ori_text) VALUES (" .
            $CURUSER["id"] . ",$torrentid, " . time() . ", " . sqlesc($text) .
             "," . sqlesc($text) . ")");

        $newid = mysql_insert_id();

        @mysql_query("UPDATE torrents SET comments = comments + 1 WHERE id = $torrentid");

        header("Refresh: 0; url=details.php?id=$torrentid&viewcomm=$newid#comm$newid");
        die;
      }

      $torrentid = 0 + $_GET["tid"];
      if (!is_valid_id($torrentid))
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");

      $res = mysql_query("SELECT name FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__,__LINE__);
      $arr = mysql_fetch_assoc($res);
      if (!$arr)
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_torrent']}");
      
      $HTMLOUT = '';

      $HTMLOUT .= "<h1>{$lang['comment_add']}\"" . htmlspecialchars($arr["name"]) . "\"</h1>
      <p><form method='post' action='comment.php?action=add'>
      <input type='hidden' name='tid' value='{$torrentid}'/>
      <textarea name='text' rows='10' cols='60'></textarea></p>
      <p><input type='submit' class='btn' value='{$lang['comment_doit']}' /></p></form>";

      $res = mysql_query("SELECT comments.id, text, comments.added, comments.editedby, comments.editedat, username, users.id as user, users.title, users.avatar, users.av_w, users.av_h, users.class, users.donor, users.warned FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $torrentid ORDER BY comments.id DESC LIMIT 5");

      $allrows = array();
      while ($row = mysql_fetch_assoc($res))
        $allrows[] = $row;

      if (count($allrows)) {
              require_once "include/torrenttable_functions.php";
              require_once "include/html_functions.php";
              require_once "include/bbcode_functions.php";
          $HTMLOUT .= "<h2>{$lang['comment_recent']}</h2>\n";
          $HTMLOUT .= commenttable($allrows);
        }

      print stdhead("{$lang['comment_add']}\"" . $arr["name"] . "\"") . $HTMLOUT . stdfoot();
      die;
    }
    elseif ($action == "edit")
    {
      $commentid = 0 + $_GET["cid"];
      if (!is_valid_id($commentid))
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");

      $res = mysql_query("SELECT c.*, t.name FROM comments AS c LEFT JOIN torrents AS t ON c.torrent = t.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
      $arr = mysql_fetch_assoc($res);
      if (!$arr)
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}.");

      if ($arr["user"] != $CURUSER["id"] && get_user_class() < UC_MODERATOR)
        stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");

      if ($_SERVER["REQUEST_METHOD"] == "POST")
      {
        $text = $_POST["text"];
        $returnto = htmlspecialchars($_POST["returnto"]);

        if ($text == "")
          stderr("{$lang['comment_error']}", "{$lang['comment_body']}");

        $text = sqlesc($text);

        $editedat = time();

        mysql_query("UPDATE comments SET text=$text, editedat=$editedat, editedby={$CURUSER['id']} WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);

        if ($returnto)
          header("Location: $returnto");
        else
          header("Location: {$TBDEV['baseurl']}/");      // change later ----------------------
        die;
      }

      
      $HTMLOUT = '';
      $HTMLOUT .= "<h1>{$lang['comment_edit']}\"" . htmlspecialchars($arr["name"]) . "\"</h1><p>
      <form method='post' action='comment.php?action=edit&amp;cid=$commentid'>
      <input type='hidden' name='returnto' value='{$_SERVER["HTTP_REFERER"]}' />
      <input type='hidden' name='cid' value='$commentid' />
      <textarea name='text' rows='10' cols='60'>" . htmlspecialchars($arr["text"]) . "</textarea></p>
      <p><input type='submit' class='btn' value='{$lang['comment_doit']}' /></p></form>";

      print stdhead("{$lang['comment_edit']}\"" . $arr["name"] . "\"") . $HTMLOUT . stdfoot();
      die;
    }
    elseif ($action == "delete")
    {
      if (get_user_class() < UC_MODERATOR)
        stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");

      $commentid = 0 + $_GET["cid"];

      if (!is_valid_id($commentid))
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");

      $sure = isset($_GET["sure"]) ? (int)$_GET["sure"] : false;

      if (!$sure)
      {
        $referer = $_SERVER["HTTP_REFERER"];
        stderr("{$lang['comment_delete']}", "{$lang['comment_about_delete']}\n" .
          "<a href='comment.php?action=delete&amp;cid=$commentid&amp;sure=1" .
          ($referer ? "&amp;returnto=" . urlencode($referer) : "") .
          "'>here</a> {$lang['comment_delete_sure']}");
      }


      $res = mysql_query("SELECT torrent FROM comments WHERE id=$commentid")  or sqlerr(__FILE__,__LINE__);
      $arr = mysql_fetch_assoc($res);
      if ($arr)
        $torrentid = $arr["torrent"];

      @mysql_query("DELETE FROM comments WHERE id=$commentid") or sqlerr(__FILE__,__LINE__);
      if ($torrentid && mysql_affected_rows() > 0)
        mysql_query("UPDATE torrents SET comments = comments - 1 WHERE id = $torrentid");

      $returnto = $_GET["returnto"];

      if ($returnto)
        header("Location: $returnto");
      else
        header("Location: {$TBDEV['baseurl']}/");      // change later ----------------------
      die;
    }
    elseif ($action == "vieworiginal")
    {
      if (get_user_class() < UC_MODERATOR)
        stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");

      $commentid = 0 + $_GET["cid"];

      if (!is_valid_id($commentid))
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");

      $res = mysql_query("SELECT c.*, t.name FROM comments AS c LEFT JOIN torrents AS t ON c.torrent = t.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
      $arr = mysql_fetch_assoc($res);
      if (!$arr)
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']} $commentid.");

      
      $HTMLOUT = '';
      $HTMLOUT .= "<h1>{$lang['comment_original_content']}#$commentid</h1><p>
      <table width='500' border='1' cellspacing='0' cellpadding='5'>
      <tr><td class='comment'>
      ".htmlspecialchars($arr["ori_text"])."
      </td></tr></table>";

      $returnto = htmlspecialchars($_SERVER["HTTP_REFERER"]);

    //	$returnto = "details.php?id=$torrentid&amp;viewcomm=$commentid#$commentid";

      if ($returnto)
        print("<p><font size='small'>(<a href='$returnto'>{$lang['comment_back']}</a>)</font></p>\n");

      print stdhead("{$lang['comment_original']}") . $HTMLOUT . stdfoot();
      die;
    }
    else
      stderr("{$lang['comment_error']}", "{$lang['comment_unknown']}");

    die;
?>