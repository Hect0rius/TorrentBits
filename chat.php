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
require_once 'include/bittorrent.php';
require_once 'include/user_functions.php';

dbconn();

loggedinorreturn();
    
    $lang = array_merge( load_language('global'), load_language('chat') );
    
    $nick = ($CURUSER ? $CURUSER['username'] : ('Guest' . rand(1000, 9999)));
    $irc_url = 'efnet.port80.se';
    $irc_channel = '#TBDEVNET';


    $HTMLOUT = '';



    $HTMLOUT .= "<p>{$lang['chat_channel']}<a href='irc://{$irc_url}'>{$irc_channel}</a>{$lang['chat_on']}<a href='http://www.gigadactyl.com'>gigadactyl</a> {$lang['chat_network']}</p>
    <div class='borderwrap' align='center'>
    <div class='maintitle'>{$TBDEV['site_name']}</div>
    <div class='row1' align='center'>
    <applet code='IRCApplet.class' codebase='./javairc' archive='irc.jar,pixx.jar' width='640' height='400'>
      <param name='CABINETS' value='irc.cab,securedirc.cab,pixx.cab' />
      <param name='nick' value='{$nick}' />
      <param name='alternatenick' value='{$nick}???' />
      <param name='fullname' value='Java User' />
      <param name='host' value='{$irc_url}' />
      <param name='gui' value='pixx' />
      <param name='quitmessage' value='{$TBDEV['site_name']} forever!' />
      <param name='asl' value='true' />
      <param name='command1' value='/join {$irc_channel}' />
      <param name='style:bitmapsmileys' value='true' />
      <param name='style:floatingasl' value='true' />
      <param name='pixx:highlight' value='true' />
      <param name='pixx:highlightnick' value='true' />
      <param name='pixx:nickfield' value='true' />
      <param name='style:smiley1' value='~:) pic/smilies/sleep.gif' />
    </applet>
    </div>
    </div>";


///////////////////// HTML OUTPUT ////////////////////////////

    print stdhead("{$lang['chat_chat']}"). $HTMLOUT .stdfoot();

?>