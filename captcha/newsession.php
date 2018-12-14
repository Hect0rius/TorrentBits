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
// Include the random string file
//require 'rand.php';
$str = '';
	for($i=0; $i<6; $i++){
$str .= chr(rand(0,25)+65);
}

// Begin a new session
session_start();

// Set the session contents
$_SESSION['captcha_id'] = $str;

?>