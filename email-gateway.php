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
require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn();

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('email-gateway') );
    
    $id = 0 + $_GET["id"];
    
    if ( !is_valid_id($id) )
      stderr("{$lang['email_error']}", "{$lang['email_bad_id']}");

    $res = mysql_query("SELECT username, class, email FROM users WHERE id=$id");
    $arr = mysql_fetch_assoc($res) or stderr("{$lang['email_error']}", "{$lang['email_no_user']}");
    $username = $arr["username"];
    
    if ($arr["class"] < UC_MODERATOR)
      stderr("{$lang['email_error']}", "{$lang['email_email_staff']}");

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
      $to = $arr["email"];

      $from = substr(trim($_POST["from"]), 0, 80);
      if ($from == "") $from = "{$lang['email_anon']}";

      $from_email = substr(trim($_POST["from_email"]), 0, 80);
      
      if ($from_email == "") $from_email = "{$TBDEV['site_email']}";
      if (!strpos($from_email, "@")) stderr("{$lang['email_error']}", "{$lang['email_invalid']}");

      $from = "$from <$from_email>";

      $subject = substr(trim($_POST["subject"]), 0, 80);
      if ($subject == "") $subject = "(No subject)";
      $subject = "Fw: $subject";

      $message = trim($_POST["message"]);
      if ($message == "") stderr("{$lang['email_error']}", "{$lang['email_no_text']}");

      $message = "Message submitted from {$_SERVER['REMOTE_ADDR']} at " . gmdate("Y-m-d H:i:s") . " GMT.\n" .
        "{$lang['email_note']}\n" .
        "---------------------------------------------------------------------\n\n" .
        $message . "\n\n" .
        "---------------------------------------------------------------------\n".
        "{$TBDEV['site_name']}{$lang['email_gateway']}\n";

      $success = mail($to, $subject, $message, "{$lang['email_from']}{$TBDEV['site_email']}");

      if ($success)
        stderr("{$lang['email_success']}", "{$lang['email_queued']}");
      else
        stderr("{$lang['email_error']}", "{$lang['email_failed']}");
    }

    $HTMLOUT = '';

    $HTMLOUT .= "<table border='0' class='main' cellspacing='0' cellpadding='0'>
    <tr>
      <td class='embedded'><img src='pic/email.gif' alt='' /></td>
      <td class='embedded' style='padding-left: 10px'><font size='3'><b>{$lang['email_send']}{$username}</b></font></td>
    </tr>
    </table>
    <form method='post' action='email-gateway.php?id=$id'>
    <table border='1' cellspacing='0' cellpadding='5'>
    <tr><td class='rowhead'>{$lang['email_your_name']}</td><td><input type='text' name='from' size='80' /></td></tr>
    <tr><td class='rowhead'>{$lang['email_your_email']}</td><td><input type='text' name='from_email' size='80' /></td></tr>
    <tr><td class='rowhead'>{$lang['email_subject']}</td><td><input type='text' name='subject' size='80' /></td></tr>
    <tr><td class='rowhead'>{$lang['email_message']}</td><td><textarea name='message' cols='80' rows='20'></textarea></td></tr>
    <tr><td colspan='2' align='center'><input type='submit' value='{$lang['email_send']}' class='btn' /></td></tr>
    </table>
    </form>
    <p>
    <font class='small'><b>{$lang['email_note_ip']}</b>{$lang['email_ip']}<br />
    {$lang['email_valid']}</font>
    </p>";

///////////////////////// HTML OUTPUT ////////////////////
    print stdhead("{$lang['email_gateway']}") . $HTMLOUT . stdfoot(); 
?>