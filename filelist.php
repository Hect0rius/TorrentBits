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
require_once "include/html_functions.php";

dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('filelist') );
    
    $id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

    if (!is_valid_id($id))
      stderr('USER ERROR', 'Bad id');


    $HTMLOUT = '';
    
		$HTMLOUT .= "<a name='top'></a><table class='main' border='1' cellspacing='0' cellpadding='5'>\n";

		$subres = mysql_query("SELECT * FROM files WHERE torrent = $id ORDER BY id");
		
		$HTMLOUT .= "<tr><td class='colhead'>{$lang["filelist_path"]}</td><td class='colhead' align='right'>{$lang["filelist_size"]}</td></tr>\n";
		$counter = 0;
			while ($subrow = mysql_fetch_assoc($subres)) {
				
				if($counter !== 0 && $counter % 10 == 0)
					$HTMLOUT .= "<tr><td colspan='2' align='right'><a href='#top'><img src='{$TBDEV['pic_base_url']}/top.gif' alt='' /></a></td></tr>";
				$HTMLOUT .= "<tr><td>" . htmlentities($subrow["filename"]) .
					"</td><td align='right'>" . htmlentities(mksize($subrow["size"])) . "</td></tr>\n";
				
				$counter++;
				}

				$HTMLOUT .= "</table>\n";


    print stdhead($lang["filelist_header"]) . $HTMLOUT . stdfoot();
?>