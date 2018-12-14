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
|   $Date: $
|   $Revision: $
|   $Author: $
|   $URL: $
+------------------------------------------------
*/

function mksecret($len=5)
	{
		$salt = '';
		
		for ( $i = 0; $i < $len; $i++ )
		{
			$num   = rand(33, 126);
			
			if ( $num == '92' )
			{
				$num = 93;
			}
			
			$salt .= chr( $num );
		}
		
		return $salt;
	}
	


function make_passhash_login_key($len=60)
	{
		$pass = mksecret( $len );
		
		return md5($pass);
	}
	


function make_passhash($salt, $md5_once_password)
	{
		return md5( md5( $salt ) . $md5_once_password );
	}
	


function make_password()
	{
		$pass = "";
		
		$unique_id 	= uniqid( mt_rand(), TRUE );
		$prefix		= mksecret();
		$unique_id .= md5( $prefix );
		
		usleep( mt_rand(15000,1000000) );
		
		mt_srand( (double)microtime()*1000000 );
		$new_uniqueid = uniqid( mt_rand(), TRUE );
		
		$final_rand = md5( $unique_id.$new_uniqueid );
		
		mt_srand();
		
		for ($i = 0; $i < 15; $i++)
		{
			$pass .= $final_rand{ mt_rand(0, 31) };
		}
	
		return $pass;
  }
	



?>