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

    $lang = array_merge( load_language('global'), load_language('search') );
    
    $HTMLOUT = '';
    
    $HTMLOUT .= "<table width='750' class='main' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>

    <form method='get' action='browse.php'>
    <p align='center'>
    {$lang['search_search']}
    <input type='text' name='search' size='40' value='' />
    {$lang['search_in']}
    <select name='cat'>
    <option value='0'>{$lang['search_all_types']}</option>";



    $cats = genrelist();
    $catdropdown = "";
    foreach ($cats as $cat) {
        $catdropdown .= "<option value=\"" . $cat["id"] . "\"";
        $getcat = (isset($_GET["cat"])?$_GET["cat"]:'');
        if ($cat["id"] == $getcat)
            $catdropdown .= " selected='selected'";
        $catdropdown .= ">" . htmlspecialchars($cat["name"]) . "</option>\n";
    }

    $deadchkbox = "<input type='checkbox' name='incldead' value='1'";
    if (isset($_GET["incldead"]))
        $deadchkbox .= " checked='checked'";
    $deadchkbox .= " /> {$lang['search_inc_dead']}";


    $HTMLOUT .= $catdropdown;
    
    $HTMLOUT .= "</select>
    $deadchkbox
    <input type='submit' value='{$lang['search_search_btn']}' class='btn' />
    </p>
    </form>
    </td></tr></table>

    <table width='750' class='main' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
    <form method='post' action='takefilesearch.php'>
    <p align='center'>
    Search:
    <input type='text' name='search' size='40' value='' />
    <input type='submit' value='{$lang['search_search_btn']}' class='btn' />
    </p>
    </form>
    </td></tr></table>";


    print stdhead("{$lang['search_search']}") . $HTMLOUT . stdfoot();

?>