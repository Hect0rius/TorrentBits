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

    $lang = array_merge( load_language('global'), load_language('confirmemail') );
    
    if ( !isset($_GET['uid']) OR !isset($_GET['key']) OR !isset($_GET['email']) )
      stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_idiot']}");

    if (! preg_match( "/^(?:[\d\w]){32}$/", $_GET['key'] ) )
		{
			stderr( "{$lang['confirmmail_user_error']}", "{$lang['confirmmail_no_key']}" );
		}
		
		if (! preg_match( "/^(?:\d){1,}$/", $_GET['uid'] ) )
		{
			stderr( "{$lang['confirmmail_user-error']}", "{$lang['confirmmail_no_id']}" );
		}

    $id = intval($_GET['uid']);
    $md5 = $_GET['key'];
    $email = urldecode($_GET['email']);
    
    if( !validemail($email) )
      stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_false_email']}");

dbconn();


    $res = mysql_query("SELECT editsecret FROM users WHERE id = $id");
    $row = mysql_fetch_assoc($res);

    if (!$row)
      stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");

    //$sec = hash_pad($row["editsecret"]);
    $sec = $row['editsecret'];
    if (preg_match('/^ *$/s', $sec))
      stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");
      
    if ($md5 != md5($sec . $email . $sec))
      stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");

   @mysql_query("UPDATE users SET editsecret='', email=" . sqlesc($email) . " WHERE id=$id AND editsecret=" . sqlesc($row["editsecret"]));

    if (!mysql_affected_rows())
      stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");

    header("Refresh: 0; url={$TBDEV['baseurl']}/my.php?emailch=1");


?>