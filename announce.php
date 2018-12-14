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
*/error_reporting(0);
////////////////// GLOBAL VARIABLES ////////////////////////////	
$TBDEV['baseurl'] = 'http://localhost/TB_ALPHA/';
$TBDEV['announce_interval'] = 60 * 30;
$TBDEV['user_ratios'] = 0;
$TBDEV['connectable_check'] = 0;
define ('UC_VIP', 2);
// DB setup
$TBDEV['mysql_host'] = "localhost";
$TBDEV['mysql_user'] = "root";
$TBDEV['mysql_pass'] = "blank";
$TBDEV['mysql_db']   = "tb";
////////////////// GLOBAL VARIABLES ////////////////////////////

// DO NOT EDIT BELOW UNLESS YOU KNOW WHAT YOU'RE DOING!!

$agent = $_SERVER["HTTP_USER_AGENT"];

// Deny access made with a browser...
if (
    ereg("^Mozilla\\/", $agent) || 
    ereg("^Opera\\/", $agent) || 
    ereg("^Links ", $agent) || 
    ereg("^Lynx\\/", $agent) || 
    isset($_SERVER['HTTP_COOKIE']) || 
    isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || 
    isset($_SERVER['HTTP_ACCEPT_CHARSET'])
    )
    err("torrent not registered with this tracker CODE 1");

/////////////////////// FUNCTION DEFS ///////////////////////////////////
function dbconn()
{
    global $TBDEV;

    if (!@mysql_connect($TBDEV['mysql_host'], $TBDEV['mysql_user'], $TBDEV['mysql_pass']))
    {
	  err('Please call back later');
    }
    mysql_select_db($TBDEV['mysql_db']) or err('Please call back later');
}

function err($msg)
{
	benc_resp(array('failure reason' => array('type' => 'string', 'value' => $msg)));
	
	exit();
}

function benc_resp($d)
{
	benc_resp_raw(benc(array('type' => 'dictionary', 'value' => $d)));
}

function benc_resp_raw($x)
{
    header( "Content-Type: text/plain" );
    header( "Pragma: no-cache" );

    if ( $_SERVER['HTTP_ACCEPT_ENCODING'] == 'gzip' )
    {
        header( "Content-Encoding: gzip" );
        echo gzencode( $x, 9, FORCE_GZIP );
    }
    else
        echo $x ;
}

function benc($obj) {
	if (!is_array($obj) || !isset($obj["type"]) || !isset($obj["value"]))
		return;
	$c = $obj["value"];
	switch ($obj["type"]) {
		case "string":
			return benc_str($c);
		case "integer":
			return benc_int($c);
		case "list":
			return benc_list($c);
		case "dictionary":
			return benc_dict($c);
		default:
			return;
	}
}

function benc_str($s) {
	return strlen($s) . ":$s";
}

function benc_int($i) {
	return "i" . $i . "e";
}

function benc_list($a) {
	$s = "l";
	foreach ($a as $e) {
		$s .= benc($e);
	}
	$s .= "e";
	return $s;
}

function benc_dict($d) {
	$s = "d";
	$keys = array_keys($d);
	sort($keys);
	foreach ($keys as $k) {
		$v = $d[$k];
		$s .= benc_str($k);
		$s .= benc($v);
	}
	$s .= "e";
	return $s;
}

function hash_where($name, $hash) {
    $shhash = preg_replace('/ *$/s', "", $hash);
    return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";
}

function sqlesc($x) {
    return "'".mysql_real_escape_string($x)."'";
}

function portblacklisted($port)
{
	// direct connect
	if ($port >= 411 && $port <= 413) return true;

	// bittorrent
	if ($port >= 6881 && $port <= 6889) return true;

	// kazaa
	if ($port == 1214) return true;

	// gnutella
	if ($port >= 6346 && $port <= 6347) return true;

	// emule
	if ($port == 4662) return true;

	// winmx
	if ($port == 6699) return true;

	return false;
}
/////////////////////// FUNCTION DEFS END ///////////////////////////////

$parts = array();
$pattern = '[0-9a-fA-F]{32}';
if( !isset($_GET['passkey']) OR !ereg($pattern, $_GET['passkey'], $parts) ) 
		err("Invalid Passkey");
	else
		$GLOBALS['passkey'] = $parts[0];
		
foreach (array("info_hash","peer_id","event","ip","localip") as $x) 
{
if(isset($_GET["$x"]))
$GLOBALS[$x] = "" . $_GET[$x];
}

foreach (array("port","downloaded","uploaded","left") as $x)
{
$GLOBALS[$x] = 0 + $_GET[$x];
}


foreach (array("passkey","info_hash","peer_id","port","downloaded","uploaded","left") as $x)

if (!isset($x)) err("Missing key: $x");



foreach (array("info_hash","peer_id") as $x)

if (strlen($GLOBALS[$x]) != 20) err("Invalid $x (" . strlen($GLOBALS[$x]) . " - " . urlencode($GLOBALS[$x]) . ")");

unset($x);

$info_hash = bin2hex($info_hash);

$ip = $_SERVER['REMOTE_ADDR'];

$port = 0 + $port;
$downloaded = 0 + $downloaded;
$uploaded = 0 + $uploaded;
$left = 0 + $left;

$rsize = 50;
foreach(array("num want", "numwant", "num_want") as $k)
{
	if (isset($_GET[$k]))
	{
		$rsize = 0 + $_GET[$k];
		break;
	}
}


if (!$port || $port > 0xffff)
	err("invalid port");

if (!isset($event))
	$event = "";

$seeder = ($left == 0) ? "yes" : "no";

dbconn();


$user_query = mysql_query("SELECT id, uploaded, downloaded, class, enabled FROM users WHERE passkey=".sqlesc($passkey)) or err("Tracker error 2");

if ( mysql_num_rows($user_query) != 1 )

 err("Unknown passkey. Please redownload the torrent from {$TBDEV['baseurl']}.");
 
	$user = mysql_fetch_assoc($user_query);
	if( $user['enabled'] == 'no' ) err('Permission denied, you\'re not enabled');
	
	
$res = mysql_query("SELECT id, banned, seeders + leechers AS numpeers, added AS ts FROM torrents WHERE info_hash = " .sqlesc($info_hash));//" . hash_where("info_hash", $info_hash));

$torrent = mysql_fetch_assoc($res);
if (!$torrent)
	err("torrent not registered with this tracker CODE 2");

$torrentid = $torrent["id"];

$fields = "seeder, peer_id, ip, port, uploaded, downloaded, userid";

$numpeers = $torrent["numpeers"];
$limit = "";
if ($numpeers > $rsize)
	$limit = "ORDER BY RAND() LIMIT $rsize";
$res = mysql_query("SELECT $fields FROM peers WHERE torrent = $torrentid AND connectable = 'yes' $limit");

//////////////////// START NEW COMPACT MODE/////////////////////////////

if($_GET['compact'] != 1)

{

$resp = "d" . benc_str("interval") . "i" . $TBDEV['announce_interval'] . "e" . benc_str("peers") . "l";

}

else

{

$resp = "d" . benc_str("interval") . "i" . $TBDEV['announce_interval'] ."e" . benc_str("min interval") . "i" . 300 ."e5:"."peers" ;

}

$peer = array();

$peer_num = 0;
while ($row = mysql_fetch_assoc($res))

{

    if($_GET['compact'] != 1)

{



$row["peer_id"] = str_pad($row["peer_id"], 20);



if ($row["peer_id"] === $peer_id)

{

 $self = $row;

 continue;

}



$resp .= "d" .

 benc_str("ip") . benc_str($row["ip"]);

       if (!$_GET['no_peer_id']) {

  $resp .= benc_str("peer id") . benc_str($row["peer_id"]);

 }

 $resp .= benc_str("port") . "i" . $row["port"] . "e" .

 "e";

      }

      else

      {

         $peer_ip = explode('.', $row["ip"]);

$peer_ip = pack("C*", $peer_ip[0], $peer_ip[1], $peer_ip[2], $peer_ip[3]);

$peer_port = pack("n*", (int)$row["port"]);

$time = intval((time() % 7680) / 60);

if($_GET['left'] == 0)

{

$time += 128;

}

$time = pack("C", $time);



   $peer[] = $time . $peer_ip . $peer_port;

$peer_num++;


      }

}



if ($_GET['compact']!=1)

$resp .= "ee";

else

{
$o = "";
for($i=0;$i<$peer_num;$i++)

 {

  $o .= substr($peer[$i], 1, 6);

 }

$resp .= strlen($o) . ':' . $o . 'e';

}

$selfwhere = "torrent = $torrentid AND " . hash_where("peer_id", $peer_id);

///////////////////////////// END NEW COMPACT MODE////////////////////////////////



if (!isset($self))
{
	$res = mysql_query("SELECT $fields FROM peers WHERE $selfwhere");
	$row = mysql_fetch_assoc($res);
	if ($row)
	{
		$userid = $row["userid"];
		$self = $row;
	}
}

//// Up/down stats ////////////////////////////////////////////////////////////



if (!isset($self))

{

$valid = @mysql_fetch_row(@mysql_query("SELECT COUNT(*) FROM peers WHERE torrent=$torrentid AND passkey=" . sqlesc($passkey)));

if ($valid[0] >= 1 && $seeder == 'no') err("Connection limit exceeded! You may only leech from one location at a time.");

if ($valid[0] >= 3 && $seeder == 'yes') err("Connection limit exceeded!");


	if ($left > 0 && $user['class'] < UC_VIP && $TBDEV['user_ratios'])
	{
		$gigs = $user["uploaded"] / (1024*1024*1024);
		$elapsed = floor((time() - $torrent["ts"]) / 3600);
		$ratio = (($user["downloaded"] > 0) ? ($user["uploaded"] / $user["downloaded"]) : 1);
		if ($ratio < 0.5 || $gigs < 5) $wait = 48;
		elseif ($ratio < 0.65 || $gigs < 6.5) $wait = 24;
		elseif ($ratio < 0.8 || $gigs < 8) $wait = 12;
		elseif ($ratio < 0.95 || $gigs < 9.5) $wait = 6;
		else $wait = 0;
		if ($elapsed < $wait)
				err("Not authorized (" . ($wait - $elapsed) . "h) - READ THE FAQ!");
	}
}
else
{
	$upthis = max(0, $uploaded - $self["uploaded"]);
	$downthis = max(0, $downloaded - $self["downloaded"]);

	if ($upthis > 0 || $downthis > 0)
		mysql_query("UPDATE users SET uploaded = uploaded + $upthis, downloaded = downloaded + $downthis WHERE id=".$user['id']) or err("Tracker error 3");
}

///////////////////////////////////////////////////////////////////////////////


$updateset = array();

if ($event == "stopped")
{
	if (isset($self))
	{
		mysql_query("DELETE FROM peers WHERE $selfwhere");
		if (mysql_affected_rows())
		{
			if ($self["seeder"] == "yes")
				$updateset[] = "seeders = seeders - 1";
			else
				$updateset[] = "leechers = leechers - 1";
		}
	}
}
else
{
	if ($event == "completed")
		$updateset[] = "times_completed = times_completed + 1";

	if (isset($self))
	{
		mysql_query("UPDATE peers SET uploaded = $uploaded, downloaded = $downloaded, to_go = $left, last_action = ".time().", seeder = '$seeder'"
			. ($seeder == "yes" && $self["seeder"] != $seeder ? ", finishedat = " . time() : "") . " WHERE $selfwhere");
		if (mysql_affected_rows() && $self["seeder"] != $seeder)
		{
			if ($seeder == "yes")
			{
				$updateset[] = "seeders = seeders + 1";
				$updateset[] = "leechers = leechers - 1";
			}
			else
			{
				$updateset[] = "seeders = seeders - 1";
				$updateset[] = "leechers = leechers + 1";
			}
		}
	}
	else
	{
		if ($event != "started")
			err("Peer not found. ".$passkey." Restart the torrent.");

		if (portblacklisted($port))
		{
			err("Port $port is blacklisted.");
		}
		elseif ( $TBDEV['connectable_check'] )
		{
			$sockres = @fsockopen($ip, $port, $errno, $errstr, 5);
			if (!$sockres)
				$connectable = "no";
			else
			{
				$connectable = "yes";
				@fclose($sockres);
			}
		}
		else
		{
      $connectable = 'yes';
		}

		$ret = mysql_query("INSERT INTO peers (connectable, torrent, peer_id, ip, port, uploaded, downloaded, to_go, started, last_action, seeder, userid, agent, passkey) VALUES ('$connectable', $torrentid, " . sqlesc($peer_id) . ", " . sqlesc($ip) . ", $port, $uploaded, $downloaded, $left, ".time().", ".time().", '$seeder', {$user['id']}, " . sqlesc($agent) . "," . sqlesc($passkey) . ")");
		
		if ($ret)
		{
			if ($seeder == "yes")
				$updateset[] = "seeders = seeders + 1";
			else
				$updateset[] = "leechers = leechers + 1";
		}
	}
}

if ($seeder == "yes")
{
	if ($torrent["banned"] != "yes")
		$updateset[] = "visible = 'yes'";
	$updateset[] = "last_action = ".time();
}

if (count($updateset))
	mysql_query("UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $torrentid");

benc_resp_raw($resp);



?>