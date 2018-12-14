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

dbconn();

loggedinorreturn();

  //$lang = load_language('download');
  $lang = array_merge( load_language('global'), load_language('download') );
  
  $id = isset($_GET['torrent']) ? intval($_GET['torrent']) : 0;

  if ( !is_valid_id($id) )
    stderr("{$lang['download_user_error']}", "{$lang['download_no_id']}");


  $res = mysql_query("SELECT name, filename FROM torrents WHERE id = $id") or sqlerr(__FILE__, __LINE__);
  $row = mysql_fetch_assoc($res);

  $fn = "{$TBDEV['torrent_dir']}/$id.torrent";

  if (!$row || !is_file($fn) || !is_readable($fn))
    httperr();


  @mysql_query("UPDATE torrents SET hits = hits + 1 WHERE id = $id");

  require_once "include/benc.php";



  if (!isset($CURUSER['passkey']) || strlen($CURUSER['passkey']) != 32) 
  {

    $CURUSER['passkey'] = md5($CURUSER['username'].time().$CURUSER['passhash']);

    @mysql_query("UPDATE users SET passkey='{$CURUSER['passkey']}' WHERE id={$CURUSER['id']}");

  }



  $dict = bdec_file($fn, filesize($fn));

  $dict['value']['announce']['value'] = "{$TBDEV['announce_urls'][0]}?passkey={$CURUSER['passkey']}";

  $dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];

  $dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);



  header('Content-Disposition: attachment; filename="[TBDev]'.$row['filename'].'"');

  header("Content-Type: application/x-bittorrent");



  print(benc($dict));



?>