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

loggedinorreturn();
    
    $lang = array_merge( load_language('global'), load_language('staff') );
    
    $HTMLOUT = '';
    
    $query = mysql_query("SELECT users.id, username, email, last_access, class, title, country, status, countries.flagpic, countries.name FROM users LEFT  JOIN countries ON countries.id = users.country WHERE class >=4 AND status='confirmed' ORDER BY username") or sqlerr();

    while($arr2 = mysql_fetch_assoc($query)) {
      
    /*	if($arr2["class"] == UC_VIP)
        $vips[] =  $arr2;
    */	
      if($arr2["class"] == UC_MODERATOR)
        $mods[] =  $arr2;
        
      if($arr2["class"] == UC_ADMINISTRATOR)
        $admins[] =  $arr2;
        
      if($arr2["class"] == UC_SYSOP)
        $sysops[] =  $arr2;
      }
    /*
    print_r($sysops);
    print("<br />");
    print_r($admins);
    print("<br />");
    print_r($mods);
    print("<br />");
    print(count($mods));
    */
    function DoStaff($staff, $staffclass, $cols = 2) 
    {
      global $TBDEV, $lang;
      
      $dt = time() - 180;
      $htmlout = '';
      
      if($staff===false) 
      {
        $htmlout .= "<br /><table width='75%' border='1' cellpadding='3'>";
        $htmlout .= "<tr><td class='colhead'><h2>{$staffclass}</h2></td></tr>";
        $htmlout .= "<tr><td>{$lang['text_none']}</td></tr></table>";
        return;
      }
      $counter = count($staff);
        
      $rows = ceil($counter/$cols);
      $cols = ($counter < $cols) ? $counter : $cols;
      //echo "<br />" . $cols . "   " . $rows;
      $r = 0;
      $htmlout .= "<br /><table width='75%' border='1' cellpadding='3'>";
      $htmlout .= "<tr><td class='colhead' colspan='{$counter}'><h2>{$staffclass}</h2></td></tr>";
      
      for($ia = 0; $ia < $rows; $ia++)
      {

            $htmlout .= "<tr>";
            for($i = 0; $i < $cols; $i++)
            {
              if( isset($staff[$r]) )  
              {
                $htmlout .= "<td><a href='userdetails.php?id={$staff[$r]['id']}'>".$staff[$r]["username"]."</a>".
          "   <img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}staff".
          ($staff[$r]['last_access']>$dt?"/online.gif":"/offline.gif" )."' border='0' alt='' />".
          "<a href='sendmessage.php?receiver={$staff[$r]['id']}'>".
          "   <img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}staff/users.png' border='0' title=\"{$lang['alt_pm']}\" alt='' /></a>".
          "<a href='email-gateway.php?id={$staff[$r]['id']}'>".
          "   <img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}staff/mail.png' border='0' alt='{$staff[$r]['username']}' title=\"{$lang['alt_sm']}\" /></a>".
          "   <img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}flag/{$staff[$r]['flagpic']}' border='0' alt='{$staff[$r]['name']}' /></td>";
          $r++;
              }
              else
              {
                $htmlout .= "<td>&nbsp;</td>";
              }
            }
            $htmlout .= "</tr>";
        
      }
      $htmlout .= "</table>";
    /*
    print("</table>");
    print("<br /><table border=1><tr>");
    for ($i = 0; $i <= count($staff)-1; $i++) {
        print("<td>{$staff[$i]["username"]}</td>");
        }
        print("</tr></table>");
    */
      return $htmlout;
    }

    $HTMLOUT .= "<h1>{$lang['text_staff']}</h1>";

    $HTMLOUT .= DoStaff($sysops, "{$lang['header_sysops']}");
    $HTMLOUT .= isset($admins) ? DoStaff($admins, "{$lang['header_admins']}") : DoStaff($admins=false, "{$lang['header_admins']}");
    $HTMLOUT .= isset($mods) ? DoStaff($mods, "{$lang['header_mods']}") : DoStaff($mods=false, "{$lang['header_mods']}");
    //$HTMLOUT .= isset($vips) ? DoStaff($vips, "{$lang['header_vips']}") : DoStaff($vips=false, "{$lang['header_vips']}");


    print stdhead("{$lang['stdhead_staff']}") . $HTMLOUT . stdfoot();

?>