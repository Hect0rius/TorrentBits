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

if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	print "<h1>{$lang['text_incorrect']}</h1>{$lang['text_cannot']}";
	exit();
}

require_once "include/user_functions.php";
require_once "include/password_functions.php";

    $lang = array_merge( $lang, load_language('ad_adduser') );
    
    if (get_user_class() < UC_ADMINISTRATOR)
      stderr("{$lang['stderr_error']}", "{$lang['text_denied']}");
      
      
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
      if ($_POST["username"] == "" || $_POST["password"] == "" || $_POST["email"] == "")
        stderr("{$lang['stderr_error']}", "{$lang['text_missing']}");
      if ($_POST["password"] != $_POST["password2"])
        stderr("{$lang['stderr_error']}", "{$lang['text_passwd']}");
      if (!validemail($_POST['email']))
        stderr("{$lang['stderr_error']}", "{$lang['text_email']}");
      
      $username = sqlesc($_POST["username"]);
      $password = $_POST["password"];
      $email = sqlesc($_POST["email"]);
      $secret = mksecret();
      $passhash = sqlesc( make_passhash( $secret, md5($password) ) );
      $secret = sqlesc($secret);
      $time_now = time();
      
      @mysql_query("INSERT INTO users (added, last_access, secret, username, passhash, status, email) VALUES($time_now, $time_now, $secret, $username, $passhash, 'confirmed', $email)") or sqlerr(__FILE__, __LINE__);
      $res = @mysql_query("SELECT id FROM users WHERE username=$username");
      $arr = mysql_fetch_row($res);
      if (!$arr)
        stderr("{$lang['stderr_error']}", "{$lang['text_username']}");
      header("Location: {$TBDEV['baseurl']}/userdetails.php?id=$arr[0]");
      die;
    }
    

    $HTMLOUT = '';
    
    $HTMLOUT .= "<h1>{$lang['text_adduser']}</h1>
    <br />
    <form method='post' action='admin.php?action=adduser'>
    <table border='1' cellspacing='0' cellpadding='5'>
    <tr><td class='rowhead'>{$lang['table_username']}</td><td><input type='text' name='username' size='40' /></td></tr>
    <tr><td class='rowhead'>{$lang['table_password']}</td><td><input type='password' name='password' size='40' /></td></tr>
    <tr><td class='rowhead'>{$lang['table_repasswd']}</td><td><input type='password' name='password2' size='40' /></td></tr>
    <tr><td class='rowhead'>{$lang['table_email']}</td><td><input type='text' name='email' size='40' /></td></tr>
    <tr><td colspan='2' align='center'><input type='submit' value='{$lang['btn_okay']}' class='btn' /></td></tr>
    </table>
    </form>";
    
    print stdhead("{$lang['stdhead_adduser']}") . $HTMLOUT . stdfoot(); 
?>