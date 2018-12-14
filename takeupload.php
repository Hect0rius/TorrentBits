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
require_once("include/benc.php");
require_once("include/bittorrent.php");
require_once "include/user_functions.php";

//@ini_set("upload_max_filesize",$TBDEV['max_torrent_size']);


dbconn(); 

loggedinorreturn();
    
    $lang = array_merge( load_language('global'), load_language('takeupload') );
    
    if ($CURUSER['class'] < UC_UPLOADER)
      header( "Location: {$TBDEV['baseurl']}/upload.php" );

    foreach(explode(":","descr:type:name") as $v) {
      if (!isset($_POST[$v]))
        stderr($lang['takeupload_failed'], $lang['takeupload_no_formdata']);
    }

    if (!isset($_FILES["file"]))
      stderr($lang['takeupload_failed'], $lang['takeupload_no_formdata']);

    $f = $_FILES["file"];
    $fname = unesc($f["name"]);
    if (empty($fname))
      stderr($lang['takeupload_failed'], $lang['takeupload_no_filename']);
      
    $nfo = sqlesc('');
    /////////////////////// NFO FILE ////////////////////////	
    if(isset($_FILES['nfo']) && !empty($_FILES['nfo']['name'])) {
    $nfofile = $_FILES['nfo'];
    if ($nfofile['name'] == '')
      stderr($lang['takeupload_failed'], $lang['takeupload_no_nfo']);

    if ($nfofile['size'] == 0)
      stderr($lang['takeupload_failed'], $lang['takeupload_0_byte']);

    if ($nfofile['size'] > 65535)
      stderr($lang['takeupload_failed'], $lang['takeupload_nfo_big']);

    $nfofilename = $nfofile['tmp_name'];

    if (@!is_uploaded_file($nfofilename))
      stderr($lang['takeupload_failed'], $lang['takeupload_nfo_failed']);

    $nfo = sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename)));
    }
    /////////////////////// NFO FILE END /////////////////////

    $descr = unesc($_POST["descr"]);
    if (!$descr)
      stderr($lang['takeupload_failed'], $lang['takeupload_no_descr']);

    $catid = (0 + $_POST["type"]);
    if (!is_valid_id($catid))
      stderr($lang['takeupload_failed'], $lang['takeupload_no_cat']);
      
    if (!validfilename($fname))
      stderr($lang['takeupload_failed'], $lang['takeupload_invalid']);
    if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
      stderr($lang['takeupload_failed'], $lang['takeupload_not_torrent']);
    $shortfname = $torrent = $matches[1];
    if (!empty($_POST["name"]))
      $torrent = unesc($_POST["name"]);

    $tmpname = $f["tmp_name"];
    if (!is_uploaded_file($tmpname))
      stderr($lang['takeupload_failed'], $lang['takeupload_eek']);
    if (!filesize($tmpname))
      stderr($lang['takeupload_failed'], $lang['takeupload_no_file']);

    $dict = bdec_file($tmpname, $TBDEV['max_torrent_size']);
    if (!isset($dict))
      stderr($lang['takeupload_failed'], $lang['takeupload_not_benc']);


    function dict_check($d, $s) {
      global $lang;
      
      if ($d["type"] != "dictionary")
        stderr($lang['takeupload_failed'], $lang['takeupload_not_dict']);
      $a = explode(":", $s);
      $dd = $d["value"];
      $ret = array();
      $t='';
      foreach ($a as $k) {
        unset($t);
        if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
          $k = $m[1];
          $t = $m[2];
        }
        if (!isset($dd[$k]))
          stderr($lang['takeupload_failed'], $lang['takeupload_no_keys']);
        if (isset($t)) {
          if ($dd[$k]["type"] != $t)
            stderr($lang['takeupload_failed'], $lang['takeupload_invalid_entry']);
          $ret[] = $dd[$k]["value"];
        }
        else
          $ret[] = $dd[$k];
      }
      return $ret;
    }

    function dict_get($d, $k, $t) {
      global $lang;
      
      if ($d["type"] != "dictionary")
        stderr($lang['takeupload_failed'], $lang['takeupload_not_dict']);
      $dd = $d["value"];
      if (!isset($dd[$k]))
        return;
      $v = $dd[$k];
      if ($v["type"] != $t)
        stderr($lang['takeupload_failed'], $lang['takeupload_dict_type']);
      return $v["value"];
    }

    list($ann, $info) = dict_check($dict, "announce(string):info");

    $tmaker = (isset($dict['value']['created by']) && !empty($dict['value']['created by']['value'])) ? sqlesc($dict['value']['created by']['value']) : sqlesc($lang['takeupload_unkown']);

    unset($dict);

    list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

    if (!in_array($ann, $TBDEV['announce_urls'], 1))
      stderr($lang['takeupload_failed'], sprintf($lang['takeupload_url'], $TBDEV['announce_urls'][0]));
     
    if (strlen($pieces) % 20 != 0)
      stderr($lang['takeupload_failed'], $lang['takeupload_pieces']);

    $filelist = array();
    $totallen = dict_get($info, "length", "integer");
    if (isset($totallen)) {
      $filelist[] = array($dname, $totallen);
      $type = "single";
    }
    else {
      $flist = dict_get($info, "files", "list");
      if (!isset($flist))
        stderr($lang['takeupload_failed'], $lang['takeupload_both']);
      if (!count($flist))
        stderr($lang['takeupload_failed'], $lang['takeupload_no_files']);
      $totallen = 0;
      foreach ($flist as $fn) {
        list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
        $totallen += $ll;
        $ffa = array();
        foreach ($ff as $ffe) {
          if ($ffe["type"] != "string")
            stderr($lang['takeupload_failed'], $lang['takeupload_error']);
          $ffa[] = $ffe["value"];
        }
        if (!count($ffa))
          stderr($lang['takeupload_failed'], $lang['takeupload_error']);
        $ffe = implode("/", $ffa);
        $filelist[] = array($ffe, $ll);
      }
      $type = "multi";
    }


    //$infohash = pack("H*", sha1($info["string"]));
    $infohash = sha1($info["string"]);

    unset($info);
    // Replace punctuation characters with spaces

    $torrent = str_replace("_", " ", $torrent);


    $ret = mysql_query("INSERT INTO torrents (search_text, filename, owner, visible, info_hash, name, size, numfiles, type, descr, ori_descr, category, save_as, added, last_action, nfo, client_created_by) VALUES (" .
        implode(",", array_map("sqlesc", array(searchfield("$shortfname $dname $torrent"), $fname, $CURUSER["id"], "no", $infohash, $torrent, $totallen, count($filelist), $type, $descr, $descr, 0 + $_POST["type"], $dname))) .
        ", " . time() . ", " . time() . ", $nfo, $tmaker)");
    if (!$ret) {
      if (mysql_errno() == 1062)
        stderr($lang['takeupload_failed'], $lang['takeupload_already']);
      stderr($lang['takeupload_failed'], "mysql puked: ".mysql_error());
    }
    $id = mysql_insert_id();

    @mysql_query("DELETE FROM files WHERE torrent = $id");

    function file_list($arr,$id)
    {
        foreach($arr as $v)
            $new[] = "($id,".sqlesc($v[0]).",".$v[1].")";
        return join(",",$new);
    }

    mysql_query("INSERT INTO files (torrent, filename, size) VALUES ".file_list($filelist,$id));

    move_uploaded_file($tmpname, "{$TBDEV['torrent_dir']}/$id.torrent");

   write_log(sprintf($lang['takeupload_log'], $id, $torrent, $CURUSER['username']));


    /* RSS feeds */

    if (($fd1 = @fopen("rss.xml", "w")) && ($fd2 = fopen("rssdd.xml", "w")))
    {
      $cats = "";
      $res = mysql_query("SELECT id, name FROM categories");
      while ($arr = mysql_fetch_assoc($res))
        $cats[$arr["id"]] = $arr["name"];
      $s = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n<rss version=\"0.91\">\n<channel>\n" .
        "<title>{$TBDEV['site_name']}</title>\n<description>TBDev is the best!</description>\n<link>{$TBDEV['baseurl']}/</link>\n";
      @fwrite($fd1, $s);
      @fwrite($fd2, $s);
      $r = mysql_query("SELECT id,name,descr,filename,category FROM torrents ORDER BY added DESC LIMIT 15") or sqlerr(__FILE__, __LINE__);
      while ($a = mysql_fetch_assoc($r))
      {
        $cat = $cats[$a["category"]];
        $s = "<item>\n<title>" . htmlspecialchars($a["name"] . " ($cat)") . "</title>\n" .
          "<description>" . htmlspecialchars($a["descr"]) . "</description>\n";
        @fwrite($fd1, $s);
        @fwrite($fd2, $s);
        @fwrite($fd1, "<link>{$TBDEV['baseurl']}/details.php?id=$a[id]&amp;hit=1</link>\n</item>\n");
        $filename = htmlspecialchars($a["filename"]);
        @fwrite($fd2, "<link>{$TBDEV['baseurl']}/download.php/$a[id]/$filename</link>\n</item>\n");
      }
      $s = "</channel>\n</rss>\n";
      @fwrite($fd1, $s);
      @fwrite($fd2, $s);
      @fclose($fd1);
      @fclose($fd2);
    }

    /* Email notifs */
    /*******************

    $res = mysql_query("SELECT name FROM categories WHERE id=$catid") or sqlerr();
    $arr = mysql_fetch_assoc($res);
    $cat = $arr["name"];
    $res = mysql_query("SELECT email FROM users WHERE enabled='yes' AND notifs LIKE '%[cat$catid]%'") or sqlerr();
    $uploader = $CURUSER['username'];

    $size = mksize($totallen);
    $description = ($html ? strip_tags($descr) : $descr);

    $body = <<<EOD
A new torrent has been uploaded.

Name: $torrent
Size: $size
Category: $cat
Uploaded by: $uploader

Description
-------------------------------------------------------------------------------
$description
-------------------------------------------------------------------------------

You can use the URL below to download the torrent (you may have to login).

{$TBDEV['baseurl']}/details.php?id=$id&hit=1

-- 
{$TBDEV['site_name']}
EOD;

    $to = "";
    $nmax = 100; // Max recipients per message
    $nthis = 0;
    $ntotal = 0;
    $total = mysql_num_rows($res);
    while ($arr = mysql_fetch_row($res))
    {
      if ($nthis == 0)
        $to = $arr[0];
      else
        $to .= "," . $arr[0];
      ++$nthis;
      ++$ntotal;
      if ($nthis == $nmax || $ntotal == $total)
      {
        if (!mail("Multiple recipients <{$TBDEV['site_email']}>", "New torrent - $torrent", $body,
        "From: {$TBDEV['site_email']}\r\nBcc: $to"))
        stderr("Error", "Your torrent has been been uploaded. DO NOT RELOAD THE PAGE!\n" .
          "There was however a problem delivering the e-mail notifcations.\n" .
          "Please let an administrator know about this error!\n");
        $nthis = 0;
      }
    }
    *******************/

    header("Location: {$TBDEV['baseurl']}/details.php?id=$id&uploaded=1");

?>