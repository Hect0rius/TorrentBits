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
require_once("bittorrent.php");

function deadtime() {
    global $TBDEV;
    return time() - floor($TBDEV['announce_interval'] * 1.3);
}

function docleanup() {
	global $TBDEV;

	set_time_limit(0);
	ignore_user_abort(1);

	do {
		$res = mysql_query("SELECT id FROM torrents");
		$ar = array();
		while ($row = mysql_fetch_array($res,MYSQL_NUM)) {
			$id = $row[0];
			$ar[$id] = 1;
		}

		if (!count($ar))
			break;

		$dp = @opendir($TBDEV['torrent_dir']);
		if (!$dp)
			break;

		$ar2 = array();
		while (($file = readdir($dp)) !== false) {
			if (!preg_match('/^(\d+)\.torrent$/', $file, $m))
				continue;
			$id = $m[1];
			$ar2[$id] = 1;
			if (isset($ar[$id]) && $ar[$id])
				continue;
			$ff = $TBDEV['torrent_dir'] . "/$file";
			unlink($ff);
		}
		closedir($dp);

		if (!count($ar2))
			break;

		$delids = array();
		foreach (array_keys($ar) as $k) {
			if (isset($ar2[$k]) && $ar2[$k])
				continue;
			$delids[] = $k;
			unset($ar[$k]);
		}
		if (count($delids))
			mysql_query("DELETE FROM torrents WHERE id IN (" . join(",", $delids) . ")");

		$res = mysql_query("SELECT torrent FROM peers GROUP BY torrent");
		$delids = array();
		while ($row = mysql_fetch_array($res,MYSQL_NUM)) {
			$id = $row[0];
			if (isset($ar[$id]) && $ar[$id])
				continue;
			$delids[] = $id;
		}
		if (count($delids))
			mysql_query("DELETE FROM peers WHERE torrent IN (" . join(",", $delids) . ")");

		$res = mysql_query("SELECT torrent FROM files GROUP BY torrent");
		$delids = array();
		while ($row = mysql_fetch_array($res,MYSQL_NUM)) {
			$id = $row[0];
			if (isset($ar[$id]) && $ar[$id])
				continue;
			$delids[] = $id;
		}
		if (count($delids))
			mysql_query("DELETE FROM files WHERE torrent IN (" . join(",", $delids) . ")");
	} while (0);

	$deadtime = deadtime();
	@mysql_query("DELETE FROM peers WHERE last_action < $deadtime");

	$deadtime -= $TBDEV['max_dead_torrent_time'];
	@mysql_query("UPDATE torrents SET visible='no' WHERE visible='yes' AND last_action < $deadtime");

	$deadtime = time() - $TBDEV['signup_timeout'];
	@mysql_query("DELETE FROM users WHERE status = 'pending' AND added < $deadtime AND last_login < $deadtime AND last_access < $deadtime");

	$torrents = array();
	$res = @mysql_query("SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder");
	while ($row = mysql_fetch_assoc($res)) {
		if ($row["seeder"] == "yes")
			$key = "seeders";
		else
			$key = "leechers";
		$torrents[$row["torrent"]][$key] = $row["c"];
	}

	$res = @mysql_query("SELECT torrent, COUNT(*) AS c FROM comments GROUP BY torrent");
	while ($row = mysql_fetch_assoc($res)) {
		$torrents[$row["torrent"]]["comments"] = $row["c"];
	}
	
	
	$fields = explode(":", "comments:leechers:seeders");
	$res = @mysql_query("SELECT id, seeders, leechers, comments FROM torrents");
	while ($row = mysql_fetch_assoc($res)) {
		$id = $row["id"];
		if(isset($torrents[$id]))
		$torr = $torrents[$id];
		foreach ($fields as $field) {
			if (!isset($torr[$field]))
				$torr[$field] = 0;
		}
		$update = array();
		foreach ($fields as $field) {
			if ($torr[$field] != $row[$field])
				$update[] = "$field = " . $torr[$field];
		}
		if (count($update))
			@mysql_query("UPDATE torrents SET " . implode(",", $update) . " WHERE id = $id");
	}

	//delete inactive user accounts
	$secs = 42*86400;
	$dt = (time() - $secs);
	$maxclass = UC_POWER_USER;
	@mysql_query("DELETE FROM users WHERE status='confirmed' AND class <= $maxclass AND last_access < $dt");

	// lock topics where last post was made more than x days ago
/*	$secs = 7*86400;
	$res = mysql_query("SELECT topics.id FROM topics LEFT JOIN posts ON topics.lastpost = posts.id WHERE topics.locked = 'no' AND topics.sticky = 'no' AND " . gmtime() . " - UNIX_TIMESTAMP(posts.added) > $secs") or sqlerr(__FILE__, __LINE__);
  if(mysql_num_rows($res) > 0) {
	while ($arr = mysql_fetch_assoc($res))
    $pids[] = $arr['id'];
		mysql_query("UPDATE topics SET locked='yes' WHERE id IN (".join(',', $pids).")") or sqlerr(__FILE__, __LINE__);
  }
*/  
  //remove expired warnings
  $res = @mysql_query("SELECT id FROM users WHERE warned='yes' AND warneduntil < ".time()." AND warneduntil <> 0") or sqlerr(__FILE__, __LINE__);
  if (mysql_num_rows($res) > 0)
  {
    $dt = time();
    $msg = sqlesc("Your warning has been removed. Please keep in your best behaviour from now on.\n");
    while ($arr = mysql_fetch_assoc($res))
    {
      @mysql_query("UPDATE users SET warned = 'no', warneduntil = 0 WHERE id = {$arr['id']}") or sqlerr(__FILE__, __LINE__);
      @mysql_query("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES(0, {$arr['id']}, $dt, $msg, 0)") or sqlerr(__FILE__, __LINE__);
    }
  }

	// promote power users
	$limit = 25*1024*1024*1024;
	$minratio = 1.05;
	$maxdt = (time() - 86400*28);
	$res = @mysql_query("SELECT id FROM users WHERE class = 0 AND uploaded >= $limit AND uploaded / downloaded >= $minratio AND added < $maxdt") or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) > 0)
	{
		$dt = time();
		$msg = sqlesc("Congratulations, you have been auto-promoted to [b]Power User[/b]. :)\nYou can now download dox over 1 meg and view torrent NFOs.\n");
		while ($arr = mysql_fetch_assoc($res))
		{
			@mysql_query("UPDATE users SET class = 1 WHERE id = {$arr['id']}") or sqlerr(__FILE__, __LINE__);
			@mysql_query("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES(0, {$arr['id']}, $dt, $msg, 0)") or sqlerr(__FILE__, __LINE__);
		}
	}

	// demote power users
	$minratio = 0.95;
	$res = mysql_query("SELECT id FROM users WHERE class = 1 AND uploaded / downloaded < $minratio") or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) > 0)
	{
		$dt = time();
		$msg = sqlesc("You have been auto-demoted from [b]Power User[/b] to [b]User[/b] because your share ratio has dropped below $minratio.\n");
		while ($arr = mysql_fetch_assoc($res))
		{
			@mysql_query("UPDATE users SET class = 0 WHERE id = {$arr['id']}") or sqlerr(__FILE__, __LINE__);
			@mysql_query("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES(0, {$arr['id']}, $dt, $msg, 0)") or sqlerr(__FILE__, __LINE__);
		}
	}

	// Update stats
	$seeders = get_row_count("peers", "WHERE seeder='yes'");
	$leechers = get_row_count("peers", "WHERE seeder='no'");
	@mysql_query("UPDATE avps SET value_u=$seeders WHERE arg='seeders'") or sqlerr(__FILE__, __LINE__);
	@mysql_query("UPDATE avps SET value_u=$leechers WHERE arg='leechers'") or sqlerr(__FILE__, __LINE__);

	// update forum post/topic count
	//$forums = @mysql_query("SELECT t.forumid, count( DISTINCT p.topicid ) AS topics, count( * ) AS posts FROM posts p LEFT JOIN topics t ON t.id = p.topicid LEFT JOIN forums f ON f.id = t.forumid GROUP BY t.forumid");
	$forums = @mysql_query("SELECT f.id, count( DISTINCT t.id ) AS topics, count( * ) AS posts
                          FROM forums f
                          LEFT JOIN topics t ON f.id = t.forumid
                          LEFT JOIN posts p ON t.id = p.topicid
                          GROUP BY f.id");
	while ($forum = mysql_fetch_assoc($forums))
	{/*
		$postcount = 0;
		$topiccount = 0;
		$topics = mysql_query("select id from topics where forumid=$forum[id]");
		while ($topic = mysql_fetch_assoc($topics))
		{
			$res = mysql_query("select count(*) from posts where topicid=$topic[id]");
			$arr = mysql_fetch_row($res);
			$postcount += $arr[0];
			++$topiccount;
		} */
		$forum['posts'] = $forum['topics'] > 0 ? $forum['posts'] : 0;
		@mysql_query("update forums set postcount={$forum['posts']}, topiccount={$forum['topics']} where id={$forum['id']}");
	}

	// delete old torrents
	$days = 28;
	$dt = (time() - ($days * 86400));
	$res = mysql_query("SELECT id, name FROM torrents WHERE added < $dt");
	while ($arr = mysql_fetch_assoc($res))
	{
		@unlink("{$TBDEV['torrent_dir']}/{$arr['id']}.torrent");
		@mysql_query("DELETE FROM torrents WHERE id={$arr['id']}");
		@mysql_query("DELETE FROM peers WHERE torrent={$arr['id']}");
		@mysql_query("DELETE FROM comments WHERE torrent={$arr['id']}");
		@mysql_query("DELETE FROM files WHERE torrent={$arr['id']}");
		write_log("Torrent {$arr['id']} ({$arr['name']}) was deleted by system (older than $days days)");
	}

    // Remove expired readposts...
    $dt = (time() - $TBDEV['readpost_expiry']);

    @mysql_query("DELETE readposts FROM readposts ".
        "LEFT JOIN posts ON readposts.lastpostread = posts.id ".
        "WHERE posts.added < $dt") or sqlerr(__FILE__,__LINE__);

}

?>