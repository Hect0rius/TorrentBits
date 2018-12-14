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
require_once ROOT_PATH."/cache/timezones.php";

dbconn();
    
    if( isset($CURUSER) )
    {
      header("Location: {$TBDEV['baseurl']}/index.php");
      exit();
    }
    
    ini_set('session.use_trans_sid', '0');

    $lang = array_merge( load_language('global'), load_language('signup') );
    
    // Begin the session
    session_start();
    if (isset($_SESSION['captcha_time']))
    (time() - $_SESSION['captcha_time'] < 10) ? exit($lang['captcha_spam']) : NULL;
    
    $HTMLOUT = '';
    
    $res = mysql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_row($res);
    if ($arr[0] >= $TBDEV['maxusers'])
      stderr($lang['stderr_errorhead'], sprintf($lang['stderr_ulimit'], $TBDEV['maxusers']));

    // TIMEZONE STUFF
        $offset = (string)$TBDEV['time_offset'];
        
        $time_select = "<select name='user_timezone'>";
        
        foreach( $TZ as $off => $words )
        {
          if ( preg_match("/^time_(-?[\d\.]+)$/", $off, $match))
          {
            $time_select .= $match[1] == $offset ? "<option value='{$match[1]}' selected='selected'>$words</option>\n" : "<option value='{$match[1]}'>$words</option>\n";
          }
        }
        
        $time_select .= "</select>";
    // TIMEZONE END
        
    


    $thistime = time();

    $HTMLOUT .= "<script type='text/javascript' src='captcha/captcha.js'></script>

    <p>{$lang['signup_cookies']}</p>

    <form method='post' action='takesignup.php'>
    <table border='1' cellspacing='0' cellpadding='10'>
    <tr><td align='right' class='heading'>{$lang['signup_uname']}</td><td align='left'><input type='text' size='40' name='wantusername' /></td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_pass']}</td><td align='left'><input type='password' size='40' name='wantpassword' /></td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_passa']}</td><td align='left'><input type='password' size='40' name='passagain' /></td></tr>
    <tr valign='top'><td align='right' class='heading'>{$lang['signup_email']}</td><td align='left'><input type='text' size='40' name='email' />
    <table width='250' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'><font class='small'>{$lang['signup_valemail']}</font></td></tr>
    </table>
    </td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_timez']}</td><td align='left'>{$time_select}</td></tr>
      <tr>
        <td>&nbsp;</td>
        <td>
          <div id='captchaimage'>
          <a href='signup.php' onclick=\"refreshimg(); return false;\" title='{$lang['captcha_refresh']}'>
          <img class='cimage' src='captcha/GD_Security_image.php?$thistime' alt='{$lang['captcha_image_alt']}' />
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
    <tr><td align='right' class='heading'></td><td align='left'><input type='checkbox' name='rulesverify' value='yes' /> {$lang['signup_rules']}<br />
    <input type='checkbox' name='faqverify' value='yes' /> {$lang['signup_faq']}<br />
    <input type='checkbox' name='ageverify' value='yes' /> {$lang['signup_age']}</td></tr>
    <tr><td colspan='2' align='center'><input type='submit' value='{$lang['signup_btn']}' style='height: 25px' /></td></tr>
    </table>
    </form>";


    print stdhead($lang['head_signup']) . $HTMLOUT . stdfoot();

?>