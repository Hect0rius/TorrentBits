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

$lang = array_merge( load_language('global'), load_language('bitbucket') );

$TBDEV['bb_upload_size'] = 256 * 1024;


if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$file = $_FILES["file"];
	if (!isset($file) || $file["size"] < 1)
		stderr("{$lang['bitbucket_failed']}", "{$lang['bitbucket_not_received']}");
	if ($file["size"] > $TBDEV['bb_upload_size'])
		stderr("{$lang['bitbucket_failed']}", "{$lang['bitbucket_too_large']}");
	$filename = $file["name"];
	if (strpos($filename, "..") !== false || strpos($filename, "/") !== false)
		stderr("{$lang['bitbucket_failed']}", "{$lang['bitbucket_bad_name']}");
	$tgtfile = "bitbucket/$filename";
	if (file_exists($tgtfile))
		stderr("{$lang['bitbucket_failed']}", "{$lang['bitbucket_no_name']}<b>" . htmlspecialchars($filename) . "</b> {$lang['bitbucket_exists']}");

	$it = @exif_imagetype($file["tmp_name"]);
	if ($it != IMAGETYPE_GIF && $it != IMAGETYPE_JPEG && $it != IMAGETYPE_PNG)
		stderr("{$lang['bitbucket_failed']}", "{$lang['bitbucket_not_recognized']}");

	$i = strrpos($filename, ".");
	if ($i !== false)
	{
		$ext = strtolower(substr($filename, $i));
		if (($it == IMAGETYPE_GIF && $ext != ".gif") || ($it == IMAGETYPE_JPEG && $ext != ".jpg") || ($it == IMAGETYPE_PNG && $ext != ".png"))
			stderr("{$lang['bitbucket_error']}", "{$lang['bitbucket_invalid_extension']}");
	}
	else
		stderr("{$lang['bitbucket_error']}", "{$lang['bitbucket_need_extension']}");
	move_uploaded_file($file["tmp_name"], $tgtfile) or stderr("{$lang['bitbucket_error']}", "{$lang['bitbucket_internal_error2']}");
	$url = str_replace(" ", "%20", htmlspecialchars("{$TBDEV['baseurl']}/bitbucket/$filename"));
	stderr("{$lang['bitbucket_success']}", "{$lang['bitbucket_url']}<b><a href=\"$url\">$url</a></b><p><a href='bitbucket-upload.php'>{$lang['bitbucket_upload_another']}</a>.");
}



    $HTMLOUT = "<h1>{$lang['bitbucket_bbupload']}</h1>
    <form method='post' action='{$TBDEV['baseurl']}/bitbucket-upload.php' enctype='multipart/form-data'>
    <p><b>{$lang['bitbucket_maximum']}".number_format($TBDEV['bb_upload_size'])."{$lang['bitbucket_bytes']}</b></p>
    <table border='1' cellspacing='0' cellpadding='5'>
    <tr><td class='rowhead'>{$lang['bitbucket_upload_file']}</td><td><input type='file' name='file' size='60' /></td></tr>
    <tr><td colspan='2' align='center'><input type='submit' value='{$lang['bitbucket_upload']}' class='btn' /></td></tr>
    </table>
    </form>
    
    <br />
    <table class='main' width='410' border='0' cellspacing='0' cellpadding='0'>
    <tr><td class='embedded'>
    <font class='small'>{$lang['bitbucket_disclaimer']}</font>
    </td></tr></table>";


    print stdhead("{$lang['bitbucket_bbupload']}") . $HTMLOUT .stdfoot();

?>