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

	function tag_info() {
		
		$result = mysql_query("SELECT searchedfor, howmuch FROM searchcloud ORDER BY id DESC LIMIT 50");
  
		while($row = mysql_fetch_assoc($result)) {
			// suck into array
			$arr[$row['searchedfor']] = $row['howmuch'];
		}
		//sort array by key
		if (isset($arr)) {
		ksort($arr);
		
		return $arr;
		}
	}

	function cloud() {
		//min / max font sizes
		$small = 10;
		$big = 35;
		//get tag info from worker function
		$tags = tag_info();
		//amounts
		if (isset($tags)) {
		$minimum_count = min(array_values($tags));
		$maximum_count = max(array_values($tags));
		$spread = $maximum_count - $minimum_count;
      
		if($spread == 0) {$spread = 1;}
		
		$cloud_html = '';

		$cloud_tags = array();
		
		foreach ($tags as $tag => $count) {

			$size = $small + ($count - $minimum_count) * ($big - $small) / $spread;
			//set up colour array for font colours.
			$colour_array = array('yellow', 'green', 'blue', 'purple', 'orange', '#0099FF');
			//spew out some html malarky!
			$cloud_tags[] = '<a style="color:'.$colour_array[mt_rand(0, 5)].'; font-size: '. floor($size) . 'px'
    . '" class="tag_cloud" href="browse.php?search=' . urlencode($tag) . '&amp;cat=0&amp;incldead=1'
    . '" title="\'' . htmlentities($tag)  . '\' returned a count of ' . $count . '">'
    . htmlentities(stripslashes($tag)) . '</a>';
		}
		
		$cloud_html = join("\n", $cloud_tags) . "\n";
		
		return $cloud_html;
		}
	}
?>