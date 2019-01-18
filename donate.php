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
    $HTMLOUT .= begin_main_frame(); 
    $HTMLOUT .= begin_frame(); 
    
    $HTMLOUT .= "<table border='0' cellspacing='0' cellpadding='0'>
    <tr valign='top'>
      <td class='embedded'>
        <img src='pic/flag/uk.gif' style='margin-right: 10px' alt='' />
      </td>
      <td class='embedded'>
        <p>{$lang['donate_donating']}</p>
        <form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_top\">
        <input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\">
        <input type=\"hidden\" name=\"hosted_button_id\" value=\"KQXM3SW2RKKSS\">
        <input type=\"image\" src=\"https://www.paypalobjects.com/en_GB/i/btn/btn_donate_SM.gif\" border=\"0\" name=\"submit\" title=\"PayPal - The safer, easier way to pay online!\" alt=\"Donate with PayPal button\">
        </form>
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