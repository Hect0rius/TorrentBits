<?php

if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	print "<h1>{$lang['text_incorrect']}</h1>{$lang['text_cannot']}";
	exit();
}

require_once "include/user_functions.php";
  
    $lang = array_merge( $lang, load_language('ad_log') );
    
    // delete items older than a week
    $secs = 24 * 60 * 60;
    
    @mysql_query("DELETE FROM sitelog WHERE " . time() . " - added > $secs") or sqlerr(__FILE__, __LINE__);
    
    $res = mysql_query("SELECT added, txt FROM sitelog ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);
    
    $HTMLOUT = "<h1>{$lang['text_sitelog']}</h1>\n";
    
    if (mysql_num_rows($res) == 0)
    {
      $HTMLOUT .= "<b>{$lang['text_logempty']}</b>\n";
    }
    else
    {
      $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='5'>
      <tr>
        <td class='colhead' align='left'>{$lang['header_date']}</td>
        <td class='colhead' align='left'>{$lang['header_time']}</td>
        <td class='colhead' align='left'>{$lang['header_event']}</td>
      </tr>\n";
      
      while ($arr = mysql_fetch_assoc($res))
      {
        $date = explode( ',', get_date( $arr['added'], 'LONG' ) );
        $HTMLOUT .= "<tr><td>{$date[0]}</td>
        <td>{$date[1]}</td>
        <td align='left'>".htmlentities($arr['txt'], ENT_QUOTES)."</td>
        </tr>\n";
      }
      
      $HTMLOUT .= "</table>\n";
    }
    $HTMLOUT .= "<p>{$lang['text_times']}</p>\n";
    
    print stdhead("{$lang['stdhead_log']}") . $HTMLOUT . stdfoot();

?>