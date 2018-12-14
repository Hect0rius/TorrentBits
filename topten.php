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
ob_start("ob_gzhandler");

  require_once "include/bittorrent.php";
  require_once "include/html_functions.php";
  require_once "include/user_functions.php";
  
  dbconn(false);
  
  loggedinorreturn();
  
  $lang = array_merge( load_language('global'), load_language('topten') );

/*
  function donortable($res, $frame_caption)
  {
    begin_frame($frame_caption, true);
    begin_table();
?>
<tr>
<td class='colhead'>Rank</td>
<td class='colhead' align=left>User</td>
<td class='colhead' align=right>Donated</td>
</tr>
<?php
    $num = 0;
    while ($a = mysql_fetch_assoc($res))
    {
        ++$num;
		$this = $a["donated"];
		if ($this == $last)
			$rank = "";
		else
		{
		  $rank = $num;
		}
	if ($rank && $num > 10)
    	break;
      print("<tr><td>$rank</td><td align='left'><a href='userdetails.php?id=$a[id]'><b>$a[username]" .
         "</b></a></td><td align='right'>$this</td></tr>");
		$last = $this;
    }
    end_table();
    end_frame();
  }
*/

function usertable($res, $frame_caption)
  {
  	global $CURUSER, $lang;
  	
  	$htmlout = '';
  	
    $htmlout .= begin_frame($frame_caption, true);
    $htmlout .= begin_table();

    $htmlout .= "<tr>
    <td class='colhead'>{$lang['common_rank']}</td>
    <td class='colhead' align='left'>{$lang['user']}</td>
    <td class='colhead'>{$lang['user_ul']}</td>
    <td class='colhead' align='left'>{$lang['user_ulspeed']}</td>
    <td class='colhead'>{$lang['user_dl']}</td>
    <td class='colhead' align='left'>{$lang['user_dlspeed']}</td>
    <td class='colhead' align='right'>{$lang['common_ratio']}</td>
    <td class='colhead' align='left'>{$lang['user_joined']}</td>

    </tr>";

        $num = 0;
        while ($a = mysql_fetch_assoc($res))
        {
          ++$num;
          $highlight = $CURUSER["id"] == $a["userid"] ? " bgcolor='#BBAF9B'" : "";
          if ($a["downloaded"])
          {
            $ratio = $a["uploaded"] / $a["downloaded"];
            $color = get_ratio_color($ratio);
            $ratio = number_format($ratio, 2);
            if ($color)
              $ratio = "<font color='$color'>$ratio</font>";
          }
          else
            $ratio = $lang['common_infratio'];
          $htmlout .= "<tr$highlight><td align='center'>$num</td><td align='left'$highlight><a href='userdetails.php?id=" .
              $a["userid"] . "'><b>" . $a["username"] . "</b></a>" .
              "</td><td align='right'$highlight>" . mksize($a["uploaded"]) .
              "</td><td align='right'$highlight>" . mksize($a["upspeed"]) . "/s" .
              "</td><td align='right'$highlight>" . mksize($a["downloaded"]) .
              "</td><td align='right'$highlight>" . mksize($a["downspeed"]) . "/s" .
              "</td><td align='right'$highlight>" . $ratio .
              "</td><td align='left'>" . get_date( $a['added'],'') . " (" .
              get_date( $a['added'],'',0,1) . ")</td></tr>";
        }
        $htmlout .= end_table();
        $htmlout .= end_frame();
        
     return $htmlout;
  }

function _torrenttable($res, $frame_caption)
    {
      global $lang;  
        
      $htmlout = '';
      
      $htmlout .= begin_frame($frame_caption, true);
      $htmlout .= begin_table();

      $htmlout .= "<tr>
      <td class='colhead' align='center'>{$lang['common_rank']}</td>
      <td class='colhead' align='left'>{$lang['torrent_name']}</td>
      <td class='colhead' align='right'>{$lang['torrent_snatch']}</td>
      <td class='colhead' align='right'>{$lang['torrent_data']}</td>
      <td class='colhead' align='right'>{$lang['torrent_seed']}</td>
      <td class='colhead' align='right'>{$lang['torrent_leech']}</td>
      <td class='colhead' align='right'>{$lang['torrent_total']}</td>
      <td class='colhead' align='right'>{$lang['common_ratio']}</td>
      </tr>";

          $num = 0;
          while ($a = mysql_fetch_assoc($res))
          {
            ++$num;
            if ($a["leechers"])
            {
              $r = $a["seeders"] / $a["leechers"];
              $ratio = "<font color='" . get_ratio_color($r) . "'>" . number_format($r, 2) . "</font>";
            }
            else
              $ratio = $lang['common_infratio'];
            $htmlout .= "<tr><td align='center'>$num</td><td align='left'><a href='details.php?id=" . $a["id"] . "&hit=1'><b>" .
              $a["name"] . "</b></a></td><td align='right'>" . number_format($a["times_completed"]) .
              "</td><td align='right'>" . mksize($a["data"]) . "</td><td align='right'>" . number_format($a["seeders"]) .
              "</td><td align='right'>" . number_format($a["leechers"]) . "</td><td align='right'>" . ($a["leechers"] + $a["seeders"]) .
              "</td><td align='right'>$ratio</td>\n";
          }
          $htmlout .= end_table();
          $htmlout .= end_frame();
          
      return $htmlout;
  }

  function countriestable($res, $frame_caption, $what)
  {
    global $CURUSER, $TBDEV, $lang;
    
    $htmlout = '';
    
    $htmlout .= begin_frame($frame_caption, true);
    $htmlout .= begin_table();

      $htmlout .= "<tr>
      <td class='colhead'>{$lang['common_rank']}</td>
      <td class='colhead' align='left'>{$lang['country']}</td>
      <td class='colhead' align='right'><?php echo $what?></td>
      </tr>";

          $num = 0;
          while ($a = mysql_fetch_assoc($res))
          {
            ++$num;
            if ($what == "Users")
              $value = number_format($a["num"]);
            elseif ($what == "Uploaded")
              $value = mksize($a["ul"]);
            elseif ($what == "Average")
              $value = mksize($a["ul_avg"]);
            elseif ($what == "Ratio")
              $value = number_format($a["r"],2);
            $htmlout .= "<tr><td align='center'>$num</td><td align='left'><table border='0' class='main' cellspacing='0' cellpadding='0'><tr><td class='embedded'>".
              "<img src=\"{$TBDEV['pic_base_url']}flag/{$a['flagpic']}\" alt='' /></td><td class='embedded' style='padding-left: 5px'><b>$a[name]</b></td>".
              "</tr></table></td><td align='right'>$value</td></tr>\n";
          }
          $htmlout .= end_table();
          $htmlout .= end_frame();
          
      return $htmlout;
  }

  function peerstable($res, $frame_caption)
  {
    global $lang;
    
    $htmlout = '';
    
    $htmlout .= begin_frame($frame_caption, true);
    $htmlout .= begin_table();

		$htmlout .= "<tr><td class='colhead'>{$lang['common_rank']}</td><td class='colhead'>{$lang['peers_uname']}</td><td class='colhead'>{$lang['peers_ulrate']}</td><td class='colhead'>{$lang['peers_dlrate']}</td></tr>";

		$n = 1;
		while ($arr = mysql_fetch_assoc($res))
		{
      $highlight = $CURUSER["id"] == $arr["userid"] ? " bgcolor='#BBAF9B'" : "";
			$htmlout .= "<tr><td$highlight>$n</td><td$highlight><a href='userdetails.php?id=" . $arr["userid"] . "'><b>" . $arr["username"] . "</b></a></td><td$highlight>" . mksize($arr["uprate"]) . "/s</td><td$highlight>" . mksize($arr["downrate"]) . "/s</td></tr>\n";
			++$n;
		}

    $htmlout .= end_table();
    $htmlout .= end_frame();
    
    return $htmlout;
  }


      $HTMLOUT = '';
      
      $HTMLOUT .= begin_main_frame();
    //  $r = mysql_query("SELECT * FROM users ORDER BY donated DESC, username LIMIT 100") or die;
    //  donortable($r, "Top 10 Donors");
      $type = isset($_GET["type"]) ? 0 + $_GET["type"] : 0;
      if (!in_array($type,array(1,2,3)))
        $type = 1;
      $limit = isset($_GET["lim"]) ? 0 + $_GET["lim"] : false;
      $subtype = isset($_GET["subtype"]) ? $_GET["subtype"] : false;

      $HTMLOUT .= "<p align='center'>"  .
        ($type == 1 && !$limit ? "<b>{$lang['common_users']}</b>" : "<a href='topten.php?type=1'>{$lang['common_users']}</a>") .	" | " .
        ($type == 2 && !$limit ? "<b>{$lang['nav_torrents']}</b>" : "<a href='topten.php?type=2'>{$lang['nav_torrents']}</a>") . " | " .
        ($type == 3 && !$limit ? "<b>{$lang['nav_countries']}</b>" : "<a href='topten.php?type=3'>{$lang['nav_countries']}</a>") . " | " .
        ($type == 4 && !$limit ? "<b>{$lang['nav_peers']}</b>" : "<a href='topten.php?type=4'>{$lang['nav_peers']}</a>") . "</p>\n";

      $pu = get_user_class() >= UC_POWER_USER;

      if (!$pu)
        $limit = 10;

      if ($type == 1)
      {
        $mainquery = "SELECT id as userid, username, added, uploaded, downloaded, uploaded / (".time()." - added) AS upspeed, downloaded / (".time()." - added) AS downspeed FROM users WHERE enabled = 'yes'";

        if (!$limit || $limit > 250)
          $limit = 10;

        if ($limit == 10 || $subtype == "ul")
        {
          $order = "uploaded DESC";
          $r = mysql_query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
          $HTMLOUT .= usertable($r, sprintf($lang['user_topulers'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=1&amp;lim=100&amp;subtype=ul'>{$lang['common_top100']}</a>] - [<a href='topten.php?type=1&amp;lim=250&amp;subtype=ul'>{$lang['common_top250']}</a>]</font>" : ""));
        }

        if ($limit == 10 || $subtype == "dl")
        {
          $order = "downloaded DESC";
          $r = mysql_query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
          $HTMLOUT .= usertable($r, sprintf($lang['user_topdlers'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=1&amp;lim=100&amp;subtype=dl'>{$lang['common_top100']}</a>] - [<a href='topten.php?type=1&amp;lim=250&amp;subtype=dl'>{$lang['common_top250']}</a>]</font>" : ""));
        }

        if ($limit == 10 || $subtype == "uls")
        {
          $order = "upspeed DESC";
          $r = mysql_query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
          $HTMLOUT .= usertable($r, sprintf($lang['user_fastestup'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=1&amp;lim=100&amp;subtype=uls'>{$lang['common_top100']}</a>] - [<a href='topten.php?type=1&amp;lim=250&amp;subtype=uls'>{$lang['common_top250']}</a>]</font>" : ""));
        }

        if ($limit == 10 || $subtype == "dls")
        {
          $order = "downspeed DESC";
          $r = mysql_query($mainquery . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
          $HTMLOUT .= usertable($r, sprintf($lang['user_fastestdown'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=1&amp;lim=100&amp;subtype=dls'>{$lang['common_top100']}</a>] - [<a href='topten.php?type=1&amp;lim=250&amp;subtype=dls'>{$lang['common_top250']}</a>]</font>" : ""));
        }

        if ($limit == 10 || $subtype == "bsh")
        {
          $order = "uploaded / downloaded DESC";
          $extrawhere = " AND downloaded > 1073741824";
          $r = mysql_query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
          $HTMLOUT .= usertable($r, sprintf($lang['user_bestshare'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=1&amp;lim=100&amp;subtype=bsh'>{$lang['common_top100']}</a>] - [<a href='topten.php?type=1&amp;lim=250&amp;subtype=bsh'>{$lang['common_top250']}</a>]</font>" : ""));
        }

        if ($limit == 10 || $subtype == "wsh")
        {
          $order = "uploaded / downloaded ASC, downloaded DESC";
          $extrawhere = " AND downloaded > 1073741824";
          $r = mysql_query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
          $HTMLOUT .= usertable($r, sprintf($lang['user_worstshare'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=1&amp;lim=100&amp;subtype=wsh'>{$lang['common_top100']}</a>] - [<a href='topten.php?type=1&amp;lim=250&amp;subtype=wsh'>{$lang['common_top250']}</a>]</font>" : ""));
        }
      }

      elseif ($type == 2)
      {
        if (!$limit || $limit > 50)
          $limit = 10;

        if ($limit == 10 || $subtype == "act")
        {
          $r = mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' GROUP BY t.id ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT $limit") or sqlerr();
          $HTMLOUT .= _torrenttable($r, sprintf($lang['torrent_mostact'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=2&amp;lim=25&amp;subtype=act'>{$lang['common_top25']}</a>] - [<a href='topten.php?type=2&amp;lim=50&amp;subtype=act'>{$lang['common_top50']}</a>]</font>" : ""));
        }

        if ($limit == 10 || $subtype == "sna")
        {
          $r = mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' GROUP BY t.id ORDER BY times_completed DESC LIMIT $limit") or sqlerr();
          $HTMLOUT .= _torrenttable($r, sprintf($lang['torrent_mostsna'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=2&amp;lim=25&amp;subtype=sna'>{$lang['common_top25']}</a>] - [<a href='topten.php?type=2&amp;lim=50&amp;subtype=sna'>{$lang['common_top50']}</a>]</font>" : ""));
        }

        if ($limit == 10 || $subtype == "mdt")
        {
          $r = mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND leechers >= 5 AND times_completed > 0 GROUP BY t.id ORDER BY data DESC, added ASC LIMIT $limit") or sqlerr();
          $HTMLOUT .= _torrenttable($r, sprintf($lang['torrent_datatrans'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=2&amp;lim=25&amp;subtype=mdt'>{$lang['common_top25']}</a>] - [<a href='topten.php?type=2&amp;lim=50&amp;subtype=mdt'>{$lang['common_top50']}</a>]</font>" : ""));
        }

        if ($limit == 10 || $subtype == "bse")
        {
          $r = mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND seeders >= 5 GROUP BY t.id ORDER BY seeders / leechers DESC, seeders DESC, added ASC LIMIT $limit") or sqlerr();
          $HTMLOUT .= _torrenttable($r, sprintf($lang['torrent_bestseed'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=2&amp;lim=25&amp;subtype=bse'>{$lang['common_top25']}</a>] - [<a href='topten.php?type=2&amp;lim=50&amp;subtype=bse'>{$lang['common_top50']}</a>]</font>" : ""));
        }

        if ($limit == 10 || $subtype == "wse")
        {
          $r = mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND leechers >= 5 AND times_completed > 0 GROUP BY t.id ORDER BY seeders / leechers ASC, leechers DESC LIMIT $limit") or sqlerr();
          $HTMLOUT .= _torrenttable($r, sprintf($lang['torrent_worstseed'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=2&amp;lim=25&amp;subtype=wse'>{$lang['common_top25']}</a>] - [<a href='topten.php?type=2&amp;lim=50&amp;subtype=wse'>{$lang['common_top50']}</a>]</font>" : ""));
        }
      }
      elseif ($type == 3)
      {
        if (!$limit || $limit > 25)
          $limit = 10;

        if ($limit == 10 || $subtype == "us")
        {
          $r = mysql_query("SELECT name, flagpic, COUNT(users.country) as num FROM countries LEFT JOIN users ON users.country = countries.id GROUP BY name ORDER BY num DESC LIMIT $limit") or sqlerr();
          $HTMLOUT .= countriestable($r, sprintf($lang['country_mostact'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=3&amp;lim=25&amp;subtype=us'>{$lang['common_top25']}</a>]</font>" : ""),$lang['common_users']);
        }

        if ($limit == 10 || $subtype == "ul")
        {
          $r = mysql_query("SELECT c.name, c.flagpic, sum(u.uploaded) AS ul FROM users AS u LEFT JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name ORDER BY ul DESC LIMIT $limit") or sqlerr();
          $HTMLOUT .= countriestable($r, sprintf($lang['country_totalul'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=3&amp;lim=25&amp;subtype=ul'>{$lang['common_top25']}</a>]</font>" : ""),$lang['common_ul']);
        }

        if ($limit == 10 || $subtype == "avg")
        {
          $r = mysql_query("SELECT c.name, c.flagpic, sum(u.uploaded)/count(u.id) AS ul_avg FROM users AS u LEFT JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name HAVING sum(u.uploaded) > 1099511627776 AND count(u.id) >= 100 ORDER BY ul_avg DESC LIMIT $limit") or sqlerr();
          $HTMLOUT .= countriestable($r, sprintf($lang['country_avperuser'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=3&amp;lim=25&amp;subtype=avg'>{$lang['common_top25']}</a>]</font>" : ""),$lang['country_avg']);
        }

        if ($limit == 10 || $subtype == "r")
        {
          $r = mysql_query("SELECT c.name, c.flagpic, sum(u.uploaded)/sum(u.downloaded) AS r FROM users AS u LEFT JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name HAVING sum(u.uploaded) > 1099511627776 AND sum(u.downloaded) > 1099511627776 AND count(u.id) >= 100 ORDER BY r DESC LIMIT $limit") or sqlerr();
          $HTMLOUT .= countriestable($r, sprintf($lang['country_ratio'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=3&amp;lim=25&amp;subtype=r'>{$lang['common_top25']}</a>]</font>" : ""),$lang['common_ratio']);
        }
      }
      elseif ($type == 4)
      {
    //		print("<h1 align='center'><font color=''red''>Under construction!</font></h1>\n");
        if (!$limit || $limit > 250)
          $limit = 10;

          if ($limit == 10 || $subtype == "ul")
          {
    //				$r = mysql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded, peers.uploaded / (UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_action)) - UNIX_TIMESTAMP(started)) AS uprate, peers.downloaded / (UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_action)) - UNIX_TIMESTAMP(started)) AS downrate FROM peers LEFT JOIN users ON peers.userid = users.id ORDER BY uprate DESC LIMIT $limit") or sqlerr();
    //				peerstable($r, "Top $limit Fastest Uploaders" . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=4&amp;lim=100&amp;subtype=ul'>Top 100</a>] - [<a href='topten.php?type=4&amp;lim=250&amp;subtype=ul'>Top 250</a>]</font>" : ""));

    //				$r = mysql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded, (peers.uploaded - peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, (peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS downrate FROM peers LEFT JOIN users ON peers.userid = users.id ORDER BY uprate DESC LIMIT $limit") or sqlerr();
    //				peerstable($r, "Top $limit Fastest Uploaders (timeout corrected)" . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=4&amp;lim=100&amp;subtype=ul'>Top 100</a>] - [<a href='topten.php?type=4&amp;lim=250&amp;subtype=ul'>Top 250</a>]</font>" : ""));

            $r = mysql_query( "SELECT users.id AS userid, username, (peers.uploaded - peers.uploadoffset) / (last_action - started) AS uprate, IF(seeder = 'yes',(peers.downloaded - peers.downloadoffset)  / (finishedat - started),(peers.downloaded - peers.downloadoffset) / (last_action - started)) AS downrate FROM peers LEFT JOIN users ON peers.userid = users.id ORDER BY uprate DESC LIMIT $limit") or sqlerr();
            $HTMLOUT .= peerstable($r, sprintf($lang['peers_fastestup'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=4&amp;lim=100&amp;subtype=ul'>{$lang['common_top100']}</a>] - [<a href='topten.php?type=4&amp;lim=250&amp;subtype=ul'>{$lang['common_top250']}</a>]</font>" : ""));
          }

          if ($limit == 10 || $subtype == "dl")
          {
    //				$r = mysql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded, (peers.uploaded - peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, (peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS downrate FROM peers LEFT JOIN users ON peers.userid = users.id ORDER BY downrate DESC LIMIT $limit") or sqlerr();
    //				peerstable($r, "Top $limit Fastest Downloaders (timeout corrected)" . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=4&amp;lim=100&amp;subtype=dl'>Top 100</a>] - [<a href='topten.php?type=4&amp;lim=250&amp;subtype=dl'>Top 250</a>]</font>" : ""));

            $r = mysql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded,(peers.uploaded - peers.uploadoffset) / (last_action - started) AS uprate, IF(seeder = 'yes',(peers.downloaded - peers.downloadoffset)  / (finishedat - started),(peers.downloaded - peers.downloadoffset) / (last_action - started)) AS downrate FROM peers LEFT JOIN users ON peers.userid = users.id ORDER BY downrate DESC LIMIT $limit") or sqlerr();
            $HTMLOUT .= peerstable($r, sprintf($lang['peers_fastestdown'], $limit) . ($limit == 10 && $pu ? " <font class='small'> - [<a href='topten.php?type=4&amp;lim=100&amp;subtype=dl'>{$lang['common_top100']}</a>] - [<a href='topten.php?type=4&amp;lim=250&amp;subtype=dl'>{$lang['common_top250']}</a>]</font>" : ""));
          }
      }
      $HTMLOUT .= end_main_frame();
      
      print stdhead($lang['head_title']) . $HTMLOUT . stdfoot();
?>


