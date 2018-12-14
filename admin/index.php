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
if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}

require_once "include/html_functions.php";
require_once "include/user_functions.php";


    $lang = array_merge( $lang, load_language('ad_index') );

    $HTMLOUT = '';

    $HTMLOUT .= "<br />

    <br />
		<table width='75%' cellpadding='10px'>
		<tr><td class='colhead'>Staff Tools</td></tr>
		<!-- row 1 -->
		<tr><td>
		
			
			<span class='btn'><a href='admin.php?action=bans'>{$lang['index_bans']}</a></span>
			
			<span class='btn'><a href='admin.php?action=adduser'>{$lang['index_new_user']}</a></span>
			
			<span class='btn'><a href='admin.php?action=log'>{$lang['index_log']}</a></span>
			
			<span class='btn'><a href='admin.php?action=docleanup'>{$lang['index_mcleanup']}</a></span>
			
			<span class='btn'><a href='users.php'>{$lang['index_user_list']}</a></span>
			
			</td></tr>
			<!-- row 2 -->
			<tr><td>
			
			<span class='btn'><a href='tags.php'>{$lang['index_tags']}</a></span>
			

			<span class='btn'><a href='smilies.php'>{$lang['index_emoticons']}</a></span>
			
			<span class='btn'><a href='admin.php?action=delacct'>{$lang['index_delacct']}</a></span>
			

			<span class='btn'><a href='admin.php?action=stats'>{$lang['index_stats']}</a></span>
			
			</td></tr>
			<!-- roow 3 -->
			<tr><td>
			
			<span class='btn'><a href='admin.php?action=testip'>{$lang['index_testip']}</a></span>
			

			<span class='btn'><a href='admin.php?action=usersearch'>{$lang['index_user_search']}</a></span>
			

			<span class='btn'><a href='admin.php?action=mysql_overview'>{$lang['index_mysql_overview']}</a></span>
			

			<span class='btn'><a href='admin.php?action=mysql_stats'>{$lang['index_mysql_stats']}</a></span>
			
			
			</td></tr>
			<!-- row 4 -->
			<tr><td>
			
			<span class='btn'><a href='admin.php?action=forummanage'>{$lang['index_forummanage']}</a></span>
			

			<span class='btn'><a href='admin.php?action=categories'>{$lang['index_categories']}</a></span>
			
			</td></tr>
			<!-- row 5 -->
			<tr><td>
			
			<span class='btn'><a href='reputation_ad.php'>{$lang['index_rep_system']}</a></span>
			
			<span class='btn'><a href='reputation_settings.php'>{$lang['index_rep_settings']}</a></span>
			
			<span class='btn'><a href='admin.php?action=news'>{$lang['index_news']}</a></span>
			
			
		</td></tr></table>";
 

    print stdhead("Staff") . $HTMLOUT . stdfoot();

?>