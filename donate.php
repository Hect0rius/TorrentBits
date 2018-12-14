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
require_once "include/html_functions.php";
require_once "include/user_functions.php";

dbconn();
    
    $lang = array_merge( load_language('global'), load_language('donate') );
    
    $HTMLOUT = '';

    $HTMLOUT .= "<b>{$lang['donate_click']}</b>


    <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
    <input name='cmd' value='_s-xclick' type='hidden' />
    <input src='https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif' name='submit' alt='{$lang['donate_make']}' type='image' />
    <input name='encrypted' value='-----BEGIN PKCS7-----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-----END PKCS7-----' type='hidden' />
    </form>
    <br />

    <br />";
    $HTMLOUT .= begin_main_frame(); 
    $HTMLOUT .= begin_frame(); 
    
    $HTMLOUT .= "<table border='0' cellspacing='0' cellpadding='0'>
    <tr valign='top'>
      <td class='embedded'>
        <img src='pic/flag/uk.gif' style='margin-right: 10px' alt='' />
      </td>
      <td class='embedded'>
        <p>{$lang['donate_donating']}</p>
<p>{$lang['donate_thanks']}</p>
      </td>
    </tr>
    </table>";
    
    $HTMLOUT .= end_frame(); 
    $HTMLOUT .= begin_frame("{$lang['donate_other']}");
    
    $HTMLOUT .= "{$lang['donate_no_other']}";
    $HTMLOUT .= end_frame(); 
    $HTMLOUT .= end_main_frame();


    $HTMLOUT .= "<b>{$lang['donate_after']}<a href='sendmessage.php?receiver=1'>{$lang['donate_send']}</a>{$lang['donate_the']}<font color='red'>{$lang['donate_transaction']}</font>{$lang['donate_credit']}</b>";

    print stdhead() . $HTMLOUT . stdfoot();
?>