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
require_once "include/bittorrent.php" ;

    ini_set('session.use_trans_sid', '0');

    $lang = array_merge( load_language('global'), load_language('login') );
    
    // Begin the session
    session_start();
    if (isset($_SESSION['captcha_time']))
    (time() - $_SESSION['captcha_time'] < 10) ? exit("{$lang['login_spam']}") : NULL;

    $HTMLOUT = '';

    unset($returnto);
    if (!empty($_GET["returnto"])) {
      $returnto = $_GET["returnto"];
      if (!isset($_GET["nowarn"])) 
      {
        $HTMLOUT .= "<h1>{$lang['login_not_logged_in']}</h1>\n";
        $HTMLOUT .= "{$lang['login_error']}";
      }
    }


    $HTMLOUT .= "<script type='text/javascript' src='captcha/captcha.js'></script>

    <form method='post' action='takelogin.php'>
    <p>Note: You need cookies enabled to log in.</p>
    <table border='0' cellpadding='5'>
      <tr>
        <td class='rowhead'>{$lang['login_username']}</td>
        <td align='left'><input type='text' size='40' name='username' /></td>
      </tr>
      <tr>
        <td class='rowhead'>{$lang['login_password']}</td>
        <td align='left'><input type='password' size='40' name='password' /></td>
      </tr>
    <!--<tr><td class='rowhead'>{$lang['login_duration']}</td><td align='left'><input type='checkbox' name='logout' value='yes' checked='checked' />{$lang['login_15mins']}</td></tr>-->
      <tr>
        <td>&nbsp;</td>
        <td>
          <div id='captchaimage'>
          <a href='login.php' onclick=\"refreshimg(); return false;\" title='{$lang['login_refresh']}'>
          <img class='cimage' src='captcha/GD_Security_image.php?".TIME_NOW."' alt='{$lang['login_captcha']}' />
          </a>
          </div>
         </td>
      </tr>
      <tr>
          <td class='rowhead'>{$lang['login_pin']}</td>
          <td>
            <input type='text' maxlength='6' name='captcha' id='captcha' onblur='check(); return false;'/>
          </td>
      </tr>
      <tr>
        <td colspan='2' align='center'>
          <input type='submit' value='{$lang['login_login']}' class='btn' />
        </td>
      </tr>
    </table>";


    if (isset($returnto))
      $HTMLOUT .= "<input type='hidden' name='returnto' value='" . htmlentities($returnto) . "' />\n";


    $HTMLOUT .= "</form>
    {$lang['login_signup']}";


    print stdhead("{$lang['login_login_btn']}") . $HTMLOUT . stdfoot();

?>