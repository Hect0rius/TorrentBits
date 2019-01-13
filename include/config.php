<?php
/*
+------------------------------------------------
|   TBDev.net BitTorrent Tracker PHP
|   =============================================
|   by CoLdFuSiOn
|   (c) 2003 - 2009 TBDev.Net
 *  by [Hect0r]
 *  (c) 2019 TBDev.Info
|   http://tbdev.info/
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
error_reporting(E_ALL);

define('SQL_DEBUG', 2);

/* Compare php version for date/time stuff etc! */
	if (version_compare(PHP_VERSION, "5.1.0RC1", ">="))
		date_default_timezone_set('Europe/London');


define('TIME_NOW', time());

$TBDEV['time_adjust'] =  0;
$TBDEV['time_offset'] = '0'; 
$TBDEV['time_use_relative'] = 1;
$TBDEV['time_use_relative_format'] = '{--}, h:i A';
$TBDEV['time_joined'] = 'j-F y';
$TBDEV['time_short'] = 'jS F Y - h:i A';
$TBDEV['time_long'] = 'M j Y, h:i A';
$TBDEV['time_tiny'] = '';
$TBDEV['time_date'] = '';


// DB setup
// FYNNON FUCKWIT FRENCH RETARD
$TBDEV['mysql_host'] = "localhost";
$TBDEV['mysql_user'] = "root";
$TBDEV['mysql_pass'] = "";
$TBDEV['mysql_db']   = "TBDev";

// Cookie setup
$TBDEV['cookie_prefix']  = 'tbalpha_'; // This allows you to have multiple trackers, eg for demos, testing etc.
$TBDEV['cookie_path']    = ''; // ATTENTION: You should never need this unless the above applies eg: /tbdev
$TBDEV['cookie_domain']  = ''; // set to eg: .somedomain.com or is subdomain set to: .sub.somedomain.com
                              
$TBDEV['site_online'] = 1;
$TBDEV['tracker_post_key'] = 'changethisorelse';
$TBDEV['max_torrent_size'] = 1000000;
$TBDEV['announce_interval'] = 60 * 30;
$TBDEV['signup_timeout'] = 86400 * 3;
$TBDEV['minvotes'] = 1;
$TBDEV['max_dead_torrent_time'] = 6 * 3600;

// Max users on site
$TBDEV['maxusers'] = 5000; // LoL Who we kiddin' here?


if ( strtoupper( substr(PHP_OS, 0, 3) ) == 'WIN' )
  {
    $file_path = str_replace( "\\", "/", dirname(__FILE__) );
    $file_path = str_replace( "/include", "", $file_path );
  }
  else
  {
    $file_path = dirname(__FILE__);
    $file_path = str_replace( "/include", "", $file_path );
  }
  
define('ROOT_PATH', $file_path);
$TBDEV['torrent_dir'] = ROOT_PATH . '/torrents'; # must be writable for httpd user   

# the first one will be displayed on the pages
$TBDEV['announce_urls'] = array();
$TBDEV['announce_urls'][] = "http://tbdev.info/announce.php";
//$TBDEV['announce_urls'] = "http://localhost:2710/announce";
//$TBDEV['announce_urls'] = "http://domain.com:83/announce.php";

if ($_SERVER["HTTP_HOST"] == "")
  $_SERVER["HTTP_HOST"] = $_SERVER["SERVER_NAME"];
  
$TBDEV['baseurl'] = "http://" . $_SERVER["HTTP_HOST"];

/*
## DO NOT UNCOMMENT THIS: IT'S FOR LATER USE!
$host = getenv( 'SERVER_NAME' );
$script = getenv( 'SCRIPT_NAME' );
$script = str_replace( "\\", "/", $script );

  if( $host AND $script )
  {
    $script = str_replace( '/index.php', '', $script );

    $TBDEV['baseurl'] = "http://{$host}{$script}";
  }
*/

//set this to true to make this a tracker that only registered users may use
//$TBDEV['membersonly'] = 1; //deprecated no longer needed

//maximum number of peers (seeders+leechers) allowed before torrents starts to be deleted to make room...
//set this to something high if you don't require this feature
//$TBDEV['peerlimit'] = 50000; //deprecated. no longer used.

// Email for sender/return path.
$TBDEV['site_email'] = "sysop@tbdev.info";

$TBDEV['site_name'] = "TBDEV.NET";

$TBDEV['language'] = 'en';
//charset
$TBDEV['char_set'] = 'UTF-8'; //also to be used site wide in meta tags
if (ini_get('default_charset') != $TBDEV['char_set']) {
ini_set('default_charset',$TBDEV['char_set']);
}
$TBDEV['msg_alert'] = 0; // saves a query when off

$TBDEV['autoclean_interval'] = 900;
$TBDEV['sql_error_log'] = ROOT_PATH.'/logs/sql_err_'.date("M_D_Y").'.log';
$TBDEV['pic_base_url'] = "./pic/";
$TBDEV['stylesheet'] = "./1.css";
$TBDEV['readpost_expiry'] = 14*86400; // 14 days
//set this to size of user avatars
$TBDEV['av_img_height'] = 100;
$TBDEV['av_img_width'] = 100;
$TBDEV['allowed_ext'] = array('image/gif', 'image/png', 'image/jpeg');
// Set this to the line break character sequence of your system
//$TBDEV['linebreak'] = "\r\n"; // not used at present.

define ('UC_USER', 0);
define ('UC_POWER_USER', 1);
define ('UC_VIP', 2);
define ('UC_UPLOADER', 3);
define ('UC_MODERATOR', 4);
define ('UC_ADMINISTRATOR', 5);
define ('UC_SYSOP', 6);

//Do not modify -- versioning system
//This will help identify code for support issues at tbdev.net
define ('TBVERSION','TBDev_2019_svn');

?>