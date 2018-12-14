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
require_once "emoticons.php";
  
//Finds last occurrence of needle in haystack
//in PHP5 use strripos() instead of this
function _strlastpos ($haystack, $needle, $offset = 0)
{
	$addLen = strlen ($needle);
	$endPos = $offset - $addLen;
	while (true)
	{
		if (($newPos = strpos ($haystack, $needle, $endPos + $addLen)) === false) break;
		$endPos = $newPos;
	}
	return ($endPos >= 0) ? $endPos : false;
}


function format_urls($s)
{
	return preg_replace(
    	"/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^()<>\s]+)/i",
	    "\\1<a href=\"\\2\">\\2</a>", $s);
}

/*

// Removed this fn, I've decided we should drop the redir script...
// it's pretty useless since ppl can still link to pics...
// -Rb

function format_local_urls($s)
{
	return preg_replace(
    "/(<a href=redir\.php\?url=)((http|ftp|https|ftps|irc):\/\/(www\.)?torrentbits\.(net|org|com)(:8[0-3])?([^<>\s]*))>([^<]+)<\/a>/i",
    "<a href=\\2>\\8</a>", $s);
}
*/

function format_quotes($s)
{
  $old_s = '';
  while ($old_s != $s)
  {
  	$old_s = $s;

	  //find first occurrence of [/quote]
	  $close = strpos($s, "[/quote]");
	  if ($close === false)
	  	return $s;

	  //find last [quote] before first [/quote]
	  //note that there is no check for correct syntax
	  $open = _strlastpos(substr($s,0,$close), "[quote");
	  if ($open === false)
	    return $s;

	  $quote = substr($s,$open,$close - $open + 8);

	  //[quote]Text[/quote]
	  $quote = preg_replace(
	    "/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
	    "<p class='sub'><b>Quote:</b></p><table class='main' border='1' cellspacing='0' cellpadding='10'><tr><td style='border: 1px black dotted'>\\1</td></tr></table><br />", $quote);

	  //[quote=Author]Text[/quote]
	  $quote = preg_replace(
	    "/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
	    "<p class='sub'><b>\\1 wrote:</b></p><table class='main' border='1' cellspacing='0' cellpadding='10'><tr><td style='border: 1px black dotted'>\\2</td></tr></table><br />", $quote);

	  $s = substr($s,0,$open) . $quote . substr($s,$close + 8);
  }

	return $s;
}

function format_comment($text, $strip_html = true)
{
	global $smilies, $TBDEV;

	$s = $text;
  unset($text);
  // This fixes the extraneous ;) smilies problem. When there was an html escaped
  // char before a closing bracket - like >), "), ... - this would be encoded
  // to &xxx;), hence all the extra smilies. I created a new :wink: label, removed
  // the ;) one, and replace all genuine ;) by :wink: before escaping the body.
  // (What took us so long? :blush:)- wyz

	$s = str_replace(";)", ":wink:", $s);

	if ($strip_html)
		$s = htmlentities($s, ENT_QUOTES, 'UTF-8');

  if( preg_match( "#function\s*\((.*?)\|\|#is", $s ) )
  {
    $s = str_replace( ":"     , "&#58;", $s );
		$s = str_replace( "["     , "&#91;", $s );
		$s = str_replace( "]"     , "&#93;", $s );
		$s = str_replace( ")"     , "&#41;", $s );
		$s = str_replace( "("     , "&#40;", $s );
		$s = str_replace( "{"	 , "&#123;", $s );
		$s = str_replace( "}"	 , "&#125;", $s );
		$s = str_replace( "$"	 , "&#36;", $s );   
  }
  
	// [*]
	$s = preg_replace("/\[\*\]/", "<li>", $s);
	
	// [b]Bold[/b]
	$s = preg_replace("/\[b\]((\s|.)+?)\[\/b\]/", "<b>\\1</b>", $s);

	// [i]Italic[/i]
	$s = preg_replace("/\[i\]((\s|.)+?)\[\/i\]/", "<i>\\1</i>", $s);

	// [u]Underline[/u]
	$s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/", "<u>\\1</u>", $s);

	// [u]Underline[/u]
	$s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/i", "<u>\\1</u>", $s);

	// [img]http://www/image.gif[/img]
	$s = preg_replace("/\[img\](http:\/\/[^\s'\"<>]+(\.(jpg|gif|png)))\[\/img\]/i", "<img border=\"0\" src=\"\\1\" alt='' />", $s);

	// [img=http://www/image.gif]
	$s = preg_replace("/\[img=(http:\/\/[^\s'\"<>]+(\.(gif|jpg|png)))\]/i", "<img border=\"0\" src=\"\\1\" alt='' />", $s);

	// [color=blue]Text[/color]
	$s = preg_replace(
		"/\[color=([a-zA-Z]+)\]((\s|.)+?)\[\/color\]/i",
		"<font color='\\1'>\\2</font>", $s);

	// [color=#ffcc99]Text[/color]
	$s = preg_replace(
		"/\[color=(#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\]((\s|.)+?)\[\/color\]/i",
		"<font color='\\1'>\\2</font>", $s);

	// [url=http://www.example.com]Text[/url]
	$s = preg_replace(
		"/\[url=([^()<>\s]+?)\]((\s|.)+?)\[\/url\]/i",
		"<a href=\"\\1\">\\2</a>", $s);

	// [url]http://www.example.com[/url]
	$s = preg_replace(
		"/\[url\]([^()<>\s]+?)\[\/url\]/i",
		"<a href=\"\\1\">\\1</a>", $s);

	// [size=4]Text[/size]
	$s = preg_replace(
		"/\[size=([1-7])\]((\s|.)+?)\[\/size\]/i",
		"<font size='\\1'>\\2</font>", $s);

	// [font=Arial]Text[/font]
	$s = preg_replace(
		"/\[font=([a-zA-Z ,]+)\]((\s|.)+?)\[\/font\]/i",
		"<font face=\"\\1\">\\2</font>", $s);

//  //[quote]Text[/quote]
//  $s = preg_replace(
//    "/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
//    "<p class=sub><b>Quote:</b></p><table class=main border=1 cellspacing=0 cellpadding=10><tr><td style='border: 1px black dotted'>\\1</td></tr></table><br />", $s);

//  //[quote=Author]Text[/quote]
//  $s = preg_replace(
//    "/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
//    "<p class=sub><b>\\1 wrote:</b></p><table class=main border=1 cellspacing=0 cellpadding=10><tr><td style='border: 1px black dotted'>\\2</td></tr></table><br />", $s);

	// Quotes
	$s = format_quotes($s);

	// URLs
	$s = format_urls($s);
//	$s = format_local_urls($s);

	// Linebreaks
	$s = nl2br($s);

	// [pre]Preformatted[/pre]
	$s = preg_replace("/\[pre\]((\s|.)+?)\[\/pre\]/i", "<tt><span style=\"white-space: nowrap;\">\\1</span></tt>", $s);

	// [nfo]NFO-preformatted[/nfo]
	$s = preg_replace("/\[nfo\]((\s|.)+?)\[\/nfo\]/i", "<tt><span style=\"white-space: nowrap;\"><font face='MS Linedraw' size='2' style='font-size: 10pt; line-height: " .
		"10pt'>\\1</font></span></tt>", $s);

	// Maintain spacing
	$s = str_replace("  ", " &nbsp;", $s);

	foreach($smilies as $code => $url) {
		$s = str_replace($code, "<img border='0' src=\"{$TBDEV['pic_base_url']}smilies/{$url}\" alt=\"" . htmlspecialchars($code) . "\" />", $s);
}
	return $s;
}

?>