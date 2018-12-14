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
	function show_gd_img($content="")
	{
		
		
		$content = '  '. preg_replace( "/(\w)/", "\\1 ", $content ) .' ';
		$gd_version = 2;
		@header("Content-Type: image/jpeg");
		
		$tmp_x = 140;
		$tmp_y = 20;
		
		$image_x = 210;
		$image_y = 65;
		
		$circles = 3;
		
		if ( $gd_version == 1 )
		{
			$tmp = imagecreate($tmp_x, $tmp_y);
			$im  = imagecreate($image_x, $image_y);
		}
		else
		{
			$tmp = imagecreatetruecolor($tmp_x, $tmp_y);
			$im  = imagecreatetruecolor($image_x, $image_y);
		}
		
		$white  = ImageColorAllocate($tmp, 255, 255, 255);
		$black  = ImageColorAllocate($tmp, 0, 0, 0);
		$grey   = ImageColorAllocate($tmp, 210, 210, 210 );
		
		imagefill($tmp, 0, 0, $white);
		
		for ( $i = 1; $i <= $circles; $i++ )
		{
			$values = array(
							0  => rand(0, $tmp_x - 10),
							1  => rand(0, $tmp_y - 3),
							2  => rand(0, $tmp_x - 10),
							3  => rand(0, $tmp_y - 3),
							4  => rand(0, $tmp_x - 10),
							5  => rand(0, $tmp_y - 3),
							6  => rand(0, $tmp_x - 10),
							7  => rand(0, $tmp_y - 3),
							8  => rand(0, $tmp_x - 10),
							9  => rand(0, $tmp_y - 3),
							10 => rand(0, $tmp_x - 10),
							11 => rand(0, $tmp_y - 3),
					     );
	   
			$randomcolor = imagecolorallocate( $tmp, rand(100,255), rand(100,255),rand(100,255) );
			imagefilledpolygon($tmp, $values, 6, $randomcolor );
		}

		imagestring($tmp, 5, 0, 2, $content, $black);
		
		//-----------------------------------------
		// Distort by resizing
		//-----------------------------------------
		
		imagecopyresized($im, $tmp, 0, 0, 0, 0, $image_x, $image_y, $tmp_x, $tmp_y);
		
		imagedestroy($tmp);
		
		$white   = ImageColorAllocate($im, 255, 255, 255);
		$black   = ImageColorAllocate($im, 0, 0, 0);
		$grey    = ImageColorAllocate($im, 100, 100, 100 );
		
		$random_pixels = $image_x * $image_y / 10;
			
		for ($i = 0; $i < $random_pixels; $i++)
		{
			ImageSetPixel($im, rand(0, $image_x), rand(0, $image_y), $black);
		}
		
		$no_x_lines = ($image_x - 1) / 5;
		
		for ( $i = 0; $i <= $no_x_lines; $i++ )
		{
			// X lines
			
			ImageLine( $im, $i * $no_x_lines, 0, $i * $no_x_lines, $image_y, $grey );
			
			// Diag lines
			
			ImageLine( $im, $i * $no_x_lines, 0, ($i * $no_x_lines)+$no_x_lines, $image_y, $grey );
		}
		
		$no_y_lines = ($image_y - 1) / 5;
		
		for ( $i = 0; $i <= $no_y_lines; $i++ )
		{
			ImageLine( $im, 0, $i * $no_y_lines, $image_x, $i * $no_y_lines, $grey );
		}
		
		ImageJPEG($im);
		ImageDestroy($im);
		
		exit();
	}

if(!isset($_SESSION))
require_once('newsession.php');
$_SESSION['captcha_id'] = $str;
$_SESSION['captcha_time'] = time();

show_gd_img($str);
?>