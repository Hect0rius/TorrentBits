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
require "include/bittorrent.php";
require "include/user_functions.php";

dbconn(false);

    $lang = array_merge( load_language('global'), load_language('formats') );
    
print stdhead("{$lang['formats_download_title']}");
?>
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2><?php echo $lang['formats_guide_heading']; ?></h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'> 
<?php echo $lang['formats_guide_body']; ?>
</td></tr></table>
</td></tr></table>
<br />
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2><?php echo $lang['formats_compression_title']; ?></h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'> 
<?php echo $lang['formats_compression_body']; ?>
</td></tr></table>
</td></tr></table>
<br />
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2><?php echo $lang['formats_multimedia_title']; ?></h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'> 
<?php echo $lang['formats_multimedia_body']; ?>
</td></tr></table>
</td></tr></table>
<br />
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2><?php echo $lang['formats_image_title']; ?></h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'> 
<?php echo $lang['formats_image_body']; ?>
</td></tr></table>
</td></tr></table>
<br />
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<h2><?php echo $lang['formats_other_title']; ?></h2>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'> 
<?php echo $lang['formats_other_body']; ?>
</td></tr></table>
</td></tr></table>
<br />
<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'> 
<?php echo $lang['formats_questions']; ?>
</td></tr></table>
</td></tr></table>
<br />
<?php
print stdfoot();
?>