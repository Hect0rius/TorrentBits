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
require_once "include/password_functions.php";


ini_set('session.use_trans_sid', '0');

// Begin the session
session_start();

dbconn();

   $lang = array_merge( load_language('global'), load_language('recover') );
   
   if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
      
      if(empty($_POST['captcha']) || $_SESSION['captcha_id'] != strtoupper($_POST['captcha'])){
        header('Location: recover.php');
        exit();
    }
    $email = trim($_POST["email"]);
    if (!validemail($email))
      stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_invalidemail']}");
    $res = mysql_query("SELECT * FROM users WHERE email=" . sqlesc($email) . " LIMIT 1") or sqlerr();
    $arr = mysql_fetch_assoc($res) or stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_notfound']}");

    $sec = mksecret();

    mysql_query("UPDATE users SET editsecret=" . sqlesc($sec) . " WHERE id=" . $arr["id"]) or sqlerr();
    if (!mysql_affected_rows())
      stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_dberror']}");

    $hash = md5($sec . $email . $arr["passhash"] . $sec);


$body = sprintf($lang['email_request'], $email, $_SERVER["REMOTE_ADDR"], $TBDEV['baseurl'], $arr["id"], $hash).$TBDEV['site_name'];


    @mail($arr["email"], "{$TBDEV['site_name']} {$lang['email_subjreset']}", $body, "From: {$TBDEV['site_email']}") or stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_nomail']}");
    
    stderr($lang['stderr_successhead'], $lang['stderr_confmailsent']);
    }
    elseif($_GET)
    {
    //	if (!preg_match(':^/(\d{1,10})/([\w]{32})/(.+)$:', $_SERVER["PATH_INFO"], $matches))
    //	  httperr();

    //	$id = 0 + $matches[1];
    //	$md5 = $matches[2];

    $id = 0 + $_GET["id"];
    $md5 = $_GET["secret"];

    if (!$id)
      httperr();

    $res = mysql_query("SELECT username, email, passhash, editsecret FROM users WHERE id = $id");
    $arr = mysql_fetch_assoc($res) or httperr();

    $email = $arr["email"];
    $sec = $arr['editsecret'];
    //$sec = hash_pad($arr["editsecret"]);
    //if (preg_match('/^ *$/s', $sec))
      //httperr();
    if ($md5 != md5($sec . $email . $arr["passhash"] . $sec))
      httperr();

    // generate new password;
    /* $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    $newpassword = "";
    for ($i = 0; $i < 10; $i++)
      $newpassword .= $chars[mt_rand(0, strlen($chars) - 1)]; */
    $newpassword = make_password();
    $sec = mksecret();

    $newpasshash = make_passhash( $sec, md5($newpassword) );

    @mysql_query("UPDATE users SET secret=" . sqlesc($sec) . ", editsecret='', passhash=" . sqlesc($newpasshash) . " WHERE id=$id AND editsecret=" . sqlesc($arr["editsecret"]));

    if (!mysql_affected_rows())
      stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_noupdate']}");

    $body = sprintf($lang['email_newpass'], $arr["username"], $newpassword, $TBDEV['baseurl']).$TBDEV['site_name'];

  
    @mail($email, "{$TBDEV['site_name']} {$lang['email_subject']}", $body, "From: {$TBDEV['site_email']}")
      or stderr($lang['stderr_errorhead'], $lang['stderr_nomail']);
    stderr($lang['stderr_successhead'], sprintf($lang['stderr_mailed'], $email));
    }
    else
    {

    if (isset($_SESSION['captcha_time']))
    (time() - $_SESSION['captcha_time'] < 10) ? exit($lang['captcha_spam']) : NULL;
      
      
    $HTMLOUT = '';
    
    $HTMLOUT .= "<script type='text/javascript' src='captcha/captcha.js'></script>
      
      <h1>{$lang['recover_unamepass']}</h1>
      <p>{$lang['recover_form']}</p>
      
      <form method='post' action='recover.php'>
      <table border='1' cellspacing='0' cellpadding='10'>
        <tr>
        <td>&nbsp;</td>
        <td>
          <div id='captchaimage'>
          <a href='recover.php' onclick=\"refreshimg(); return false;\" title='{$lang['captcha_refresh']}'>
          <img class='cimage' src='captcha/GD_Security_image.php?".time()."' alt='{$lang['captcha_imagealt']}' />
          </a>
          </div>
         </td>
      </tr>
      <tr>
          <td class='rowhead'>{$lang['captcha_pin']}</td>
          <td>
            <input type='text' maxlength='6' name='captcha' id='captcha' onblur='check(); return false;'/>
          </td>
      </tr>

      <tr>
          <td class='rowhead'>{$lang['recover_regdemail']}</td>
          <td><input type='text' size='40' name='email' /></td></tr>
      <tr>
          <td colspan='2' align='center'><input type='submit' value='{$lang['recover_btn']}' class='btn' /></td>
      </tr>
      </table>
      </form>";

      print stdhead($lang['head_recover']). $HTMLOUT . stdfoot();
    }

?>