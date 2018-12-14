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
require_once "include/user_functions.php" ;

dbconn();

    $lang = array_merge( load_language('global'), load_language('ok') );
    
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    
    $HTMLOUT = '';

    if ( $type == "signup" && isset($_GET['email']) ) 
    {
      stderr( "{$lang['ok_success']}", sprintf($lang['ok_email'], htmlentities($_GET['email'], ENT_QUOTES)) );
    }
    elseif ($type == "sysop") 
    {
      $HTMLOUT = stdhead("{$lang['ok_sysop_account']}");
      $HTMLOUT .= "{$lang['ok_sysop_activated']}";
      
      if (isset($CURUSER))
      {
        $HTMLOUT .= "{$lang['ok_account_activated']}";
      }
      else
      {
        $HTMLOUT .= "{$lang['ok_account_login']}";
      }
      $HTMLOUT .= stdfoot();
      
      print $HTMLOUT;
    }
    elseif ($type == "confirmed") 
    {
      $HTMLOUT .= stdhead("{$lang['ok_confirmed']}");
      $HTMLOUT .= "<h1>{$lang['ok_confirmed']}</h1>\n";
      $HTMLOUT .= "{$lang['ok_user_confirmed']}";
      $HTMLOUT .= stdfoot();
      print $HTMLOUT;
    }
    elseif ($type == "confirm") 
    {
      if (isset($CURUSER)) 
      {
        $HTMLOUT .= stdhead("{$lang['ok_signup_confirm']}");
        $HTMLOUT .= "<h1>{$lang['ok_success_confirmed']}</h1>\n";
        $HTMLOUT .= "<p>".sprintf($lang['ok_account_active_login'], "<a href='{$TBDEV['baseurl']}/index.php'><b>{$lang['ok_account_active_login_link']}</b></a>")."</p>\n";
        $HTMLOUT .= sprintf($lang['ok_read_rules'], $TBDEV['site_name']);
        $HTMLOUT .= stdfoot();
        print $HTMLOUT;
      }
      else 
      {
        $HTMLOUT .= stdhead("{$lang['ok_signup_confirm']}");
        $HTMLOUT .= "<h1>{$lang['ok_success_confirmed']}</h1>\n";
        $HTMLOUT .= "{$lang['ok_account_cookies']}";
        $HTMLOUT .= stdfoot();
        print $HTMLOUT;
      }
    }
    else
    {
    stderr("{$lang['ok_user_error']}", "{$lang['ok_no_action']}");
    }
?>