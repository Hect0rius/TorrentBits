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
function linkcolor($num) {
    if (!$num)
        return "red";
//    if ($num == 1)
//        return "yellow";
    return "green";
}

function torrenttable($res, $variant = "index") {
    global $TBDEV, $CURUSER, $lang;

    $wait = 0;
    $htmlout = '';
    
    if ($CURUSER["class"] < UC_VIP)
    {
      $gigs = $CURUSER["uploaded"] / (1024*1024*1024);
      $ratio = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);
      if ($ratio < 0.5 || $gigs < 5) $wait = 48;
      elseif ($ratio < 0.65 || $gigs < 6.5) $wait = 24;
      elseif ($ratio < 0.8 || $gigs < 8) $wait = 12;
      elseif ($ratio < 0.95 || $gigs < 9.5) $wait = 6;
      else $wait = 0;
    }

    $htmlout .= "<table border='1' cellspacing='0' cellpadding='5'>
    <tr>
    <td class='colhead' align='center'>{$lang["torrenttable_type"]}</td>
    <td class='colhead' align='left'>{$lang["torrenttable_name"]}</td>
    <!--<td class='heading' align='left'>{$lang["torrenttable_dl"]}</td>-->";

	if ($wait)
	{
		$htmlout .= "<td class='colhead' align='center'>{$lang["torrenttable_wait"]}</td>\n";
	}

	if ($variant == "mytorrents")
  {
  	$htmlout .= "<td class='colhead' align='center'>{$lang["torrenttable_edit"]}</td>\n";
    $htmlout .= "<td class='colhead' align='center'>{$lang["torrenttable_visible"]}</td>\n";
	}


    $htmlout .= "<td class='colhead' align='right'>{$lang["torrenttable_files"]}</td>
    <td class='colhead' align='right'>{$lang["torrenttable_comments"]}</td>
    <!--<td class='colhead' align='center'>{$lang["torrenttable_rating"]}</td>-->
    <td class='colhead' align='center'>{$lang["torrenttable_added"]}</td>
    <td class='colhead' align='center'>{$lang["torrenttable_ttl"]}</td>
    <td class='colhead' align='center'>{$lang["torrenttable_size"]}</td>
    <!--
    <td class='colhead' align='right'>{$lang["torrenttable_views"]}</td>
    <td class='colhead' align='right'>{$lang["torrenttable_hits"]}</td>
    -->
    <td class='colhead' align='center'>{$lang["torrenttable_snatched"]}</td>
    <td class='colhead' align='right'>{$lang["torrenttable_seeders"]}</td>
    <td class='colhead' align='right'>{$lang["torrenttable_leechers"]}</td>";


    if ($variant == 'index')
        $htmlout .= "<td class='colhead' align='center'>{$lang["torrenttable_uppedby"]}</td>\n";

    $htmlout .= "</tr>\n";

    while ($row = mysql_fetch_assoc($res)) 
    {
        $id = $row["id"];
        $htmlout .= "<tr>\n";

        $htmlout .= "<td align='center' style='padding: 0px'>";
        if (isset($row["cat_name"])) 
        {
            $htmlout .= "<a href='browse.php?cat={$row['category']}'>";
            if (isset($row["cat_pic"]) && $row["cat_pic"] != "")
                $htmlout .= "<img border='0' src='{$TBDEV['pic_base_url']}caticons/{$row['cat_pic']}' alt='{$row['cat_name']}' />";
            else
            {
                $htmlout .= $row["cat_name"];
            }
            $htmlout .= "</a>";
        }
        else
        {
            $htmlout .= "-";
        }
        $htmlout .= "</td>\n";

        $dispname = htmlspecialchars($row["name"]);
        
        $htmlout .= "<td align='left'><a href='details.php?";
        if ($variant == "mytorrents")
            $htmlout .= "returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;";
        $htmlout .= "id=$id";
        if ($variant == "index")
            $htmlout .= "&amp;hit=1";
        $htmlout .= "'><b>$dispname</b></a>\n";

				if ($wait)
				{
				  $elapsed = floor((time() - $row["added"]) / 3600);
	        if ($elapsed < $wait)
	        {
	          $color = dechex(floor(127*($wait - $elapsed)/48 + 128)*65536);
	          $htmlout .= "<td align='center'><span style='white-space: nowrap;'><a href='faq.php#dl8'><font color='$color'>" . number_format($wait - $elapsed) . " ".$lang["torrenttable_wait_h"]."</font></a></span></td>\n";
	        }
	        else
	          $htmlout .= "<td align='center'><span style='white-space: nowrap;'>{$lang["torrenttable_wait_none"]}</span></td>\n";
        }

/*
        if ($row["nfoav"] && get_user_class() >= UC_POWER_USER)
          print("<a href='viewnfo.php?id=$row[id]''><img src='{$TBDEV['pic_base_url']}viewnfo.gif" border='0' alt='".$lang["torrenttable_view_nfo_alt"]."' /></a>\n");
        if ($variant == "index")
            print("<a href='download.php/$id/" . rawurlencode($row["filename"]) . "'><img src='{$TBDEV['pic_base_url']}download.gif' border='0' alt='".$lang["torrenttable_download_alt"]."' /></a>\n");

        else */ 
        if ($variant == "mytorrents")
            $htmlout .= "</td><td align='center'><a href='edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id={$row['id']}'>".$lang["torrenttable_edit"]."</a>\n";
        $htmlout .= "</td>\n";
        
        if ($variant == "mytorrents") 
        {
            $htmlout .= "<td align='right'>";
            if ($row["visible"] == "no")
                $htmlout .= "<b>".$lang["torrenttable_not_visible"]."</b>";
            else
                $htmlout .= "".$lang["torrenttable_visible"]."";
            $htmlout .= "</td>\n";
        }

        if ($row["type"] == "single")
        {
            $htmlout .= "<td align='right'>{$row["numfiles"]}</td>\n";
        }
        else 
        {
            if ($variant == "index")
            {
                $htmlout .= "<td align='right'><b><a href='filelist.php?id=$id'>" . $row["numfiles"] . "</a></b></td>\n";
            }
            else
            {
                $htmlout .= "<td align='right'><b><a href='filelist.php?id=$id'>" . $row["numfiles"] . "</a></b></td>\n";
            }
        }

        if (!$row["comments"])
        {
            $htmlout .= "<td align='right'>{$row["comments"]}</td>\n";
        }
        else 
        {
            if ($variant == "index")
            {
                $htmlout .= "<td align='right'><b><a href='details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . $row["comments"] . "</a></b></td>\n";
            }
            else
            {
                $htmlout .= "<td align='right'><b><a href='details.php?id=$id&amp;page=0#startcomments'>" . $row["comments"] . "</a></b></td>\n";
            }
        }

/*
        print("<td align='center'>");
        if (!isset($row["rating"]))
            print("---");
        else {
            $rating = round($row["rating"] * 2) / 2;
            $rating = ratingpic($row["rating"]);
            if (!isset($rating))
                print("---");
            else
                print($rating);
        }
        print("</td>\n");
*/
        $htmlout .= "<td align='center'><span style='white-space: nowrap;'>" . str_replace(",", "<br />", get_date( $row['added'],'')) . "</span></td>\n";
        
		$ttl = (28*24) - floor((time() - $row["added"]) / 3600);
		
		if ($ttl == 1) 
                   $ttl .= "<br />".$lang["torrenttable_hour_singular"].""; 
                else 
                   $ttl .= "<br />".$lang["torrenttable_hour_plural"]."";
    
    $htmlout .= "<td align='center'>$ttl</td>\n
    <td align='center'>" . str_replace(" ", "<br />", mksize($row["size"])) . "</td>\n";
//        print("<td align='right'>" . $row["views"] . "</td>\n");
//        print("<td align='right'>" . $row["hits"] . "</td>\n");

        
        if ($row["times_completed"] != 1)
          $_s = "".$lang["torrenttable_time_plural"]."";
        else
          $_s = "".$lang["torrenttable_time_singular"]."";
        $htmlout .= "<td align='center'>" . number_format($row["times_completed"]) . "<br />$_s</td>\n";

        if ($row["seeders"]) 
        {
            if ($variant == "index")
            {
               if ($row["leechers"]) $ratio = $row["seeders"] / $row["leechers"]; else $ratio = 1;
                $htmlout .= "<td align='right'><b><a href='peerlist.php?id=$id#seeders'>
                <font color='" .get_slr_color($ratio) . "'>{$row["seeders"]}</font></a></b></td>\n";
            }
            else
            {
                $htmlout .= "<td align='right'><b><a class='" . linkcolor($row["seeders"]) . "' href='peerlist.php?id=$id#seeders'>{$row["seeders"]}</a></b></td>\n";
            }
        }
        else
        {
            $htmlout .= "<td align='right'><span class='" . linkcolor($row["seeders"]) . "'>" . $row["seeders"] . "</span></td>\n";
        }

        if ($row["leechers"]) 
        {
            if ($variant == "index")
                $htmlout .= "<td align='right'><b><a href='peerlist.php?id=$id#leechers'>" .
                   number_format($row["leechers"]) . "</a></b></td>\n";
            else
                $htmlout .= "<td align='right'><b><a class='" . linkcolor($row["leechers"]) . "' href='peerlist.php?id=$id#leechers'>{$row["leechers"]}</a></b></td>\n";
        }
        else
            $htmlout .= "<td align='right'>0</td>\n";

        if ($variant == "index")
            $htmlout .= "<td align='center'>" . (isset($row["username"]) ? ("<a href='userdetails.php?id=" . $row["owner"] . "'><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>(".$lang["torrenttable_unknown_uploader"].")</i>") . "</td>\n";

       $htmlout .= "</tr>\n";
    }

    $htmlout .= "</table>\n";

    return $htmlout;
}

function commenttable($rows)
{
	global $CURUSER, $TBDEV;
	
	
	$lang = load_language( 'torrenttable_functions' );
	
	$htmlout = '';
	$count = 0;
	
	$htmlout .= begin_main_frame();
	$htmlout .= begin_frame();
	
	foreach ($rows as $row)
	{
		$htmlout .= "<p class='sub'>#{$row["id"]} {$lang["commenttable_by"]} ";
    if (isset($row["username"]))
		{
			$title = $row["title"];
			if ($title == "")
				$title = get_user_class_name($row["class"]);
			else
				$title = htmlspecialchars($title);
        $htmlout .= "<a name='comm{$row["id"]}' href='userdetails.php?id={$row["user"]}'><b>" .
        	htmlspecialchars($row["username"]) . "</b></a>" . ($row["donor"] == "yes" ? "<img src='{$TBDEV['pic_base_url']}star.gif' alt='".$lang["commenttable_donor_alt"]."' />" : "") . ($row["warned"] == "yes" ? "<img src=".
    			"'{$TBDEV['pic_base_url']}warned.gif' alt='".$lang["commenttable_warned_alt"]."' />" : "") . " ($title)\n";
		}
		else
   		$htmlout .= "<a name='comm{$row["id"]}'><i>(".$lang["commenttable_orphaned"].")</i></a>\n";

		$htmlout .= get_date( $row['added'],'');
		$htmlout .= ($row["user"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR ? "- [<a href='comment.php?action=edit&amp;cid={$row['id']}'>".$lang["commenttable_edit"]."</a>]" : "") .
			(get_user_class() >= UC_MODERATOR ? "- [<a href='comment.php?action=delete&amp;cid={$row['id']}'>".$lang["commenttable_delete"]."</a>]" : "") .
			($row["editedby"] && get_user_class() >= UC_MODERATOR ? "- [<a href='comment.php?action=vieworiginal&amp;cid={$row['id']}'>".$lang["commenttable_view_original"]."</a>]" : "") . "</p>\n";
		$avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($row["avatar"]) : "");
		
		if (!$avatar)
			$avatar = "{$TBDEV['pic_base_url']}default_avatar.gif";
		$text = format_comment($row["text"]);
    if ($row["editedby"])
    	$text .= "<p><font size='1' class='small'>".$lang["commenttable_last_edited_by"]." <a href='userdetails.php?id={$row['editedby']}'><b>{$row['username']}</b></a> ".$lang["commenttable_last_edited_at"]." ".get_date($row['editedat'],'DATE')."</font></p>\n";
		$htmlout .= begin_table(true);
		$htmlout .= "<tr valign='top'>\n";
		$htmlout .= "<td align='center' width='150' style='padding: 0px'><img width='{$row['av_w']}' height='{$row['av_h']}' src='{$avatar}' alt='' /></td>\n";
		$htmlout .= "<td class='text'>$text</td>\n";
		$htmlout .= "</tr>\n";
     $htmlout .= end_table();
  }
	$htmlout .= end_frame();
	$htmlout .= end_main_frame();
	
	return $htmlout;
}


?>