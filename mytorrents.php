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
require_once "include/pager_functions.php";
require_once "include/torrenttable_functions.php";

dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('mytorrents') );
    $lang = array_merge( $lang, load_language( 'torrenttable_functions' ));
    $HTMLOUT = '';

    $where = "WHERE owner = " . $CURUSER["id"] . " AND banned != 'yes'";
    $res = mysql_query("SELECT COUNT(*) FROM torrents $where");
    $row = mysql_fetch_array($res,MYSQL_NUM);
    $count = $row[0];

    if (!$count) 
    {

      $HTMLOUT .= "{$lang['mytorrents_no_torrents']}";
      $HTMLOUT .= "{$lang['mytorrents_no_uploads']}";

    }
    else 
    {
      $pager = pager(20, $count, "mytorrents.php?");

      $res = mysql_query("SELECT torrents.type, torrents.comments, torrents.leechers, torrents.seeders, IF(torrents.numratings < {$TBDEV['minvotes']}, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.id, categories.name AS cat_name, categories.image AS cat_pic, torrents.name, save_as, numfiles, added, size, views, visible, hits, times_completed, category FROM torrents LEFT JOIN categories ON torrents.category = categories.id $where ORDER BY id DESC ".$pager['limit']);

      $HTMLOUT .= $pager['pagertop'];

      $HTMLOUT .= torrenttable($res, "mytorrents");

      $HTMLOUT .= $pager['pagerbottom'];
    }

    print stdhead($CURUSER["username"] . "'s torrents") . $HTMLOUT . stdfoot();

?>