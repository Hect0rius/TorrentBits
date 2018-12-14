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

/////////////// REP SYSTEM /////////////
//$CURUSER['reputation'] = 650;

function get_reputation($user, $mode = 0, $rep_is_on = TRUE)
	{
	global $TBDEV;
	
	
	
	$member_reputation = "";
	if( $rep_is_on )
		{
			@include 'cache/rep_cache.php';
			// ok long winded file checking, but it's much better than file_exists
			if( ! isset( $reputations ) || ! is_array( $reputations ) || count( $reputations ) < 1)
			{
				return '<span title="Cache doesn\'t exist or zero length">Reputation: Offline</span>';
			}
			
			$user['g_rep_hide'] = isset( $user['g_rep_hide'] ) ? $user['g_rep_hide'] : 0;
	
			// Hmmm...bit of jiggery-pokery here, couldn't think of a better way.
			$max_rep = max(array_keys($reputations));
			if($user['reputation'] >= $max_rep)
			{
				$user_reputation = $reputations[$max_rep];
			}
			else
			foreach($reputations as $y => $x) 
			{
				if( $y > $user['reputation'] ) { $user_reputation = $old; break; }
				$old = $x;
			}
			
			//$rep_is_on = TRUE;
			//$CURUSER['g_rep_hide'] = FALSE;
					
			$rep_power = $user['reputation'];
			$posneg = '';
			if( $user['reputation'] == 0 )
			{
				$rep_img   = 'balance';
				$rep_power = $user['reputation'] * -1;
			}
			elseif( $user['reputation'] < 0 )
			{
				$rep_img   = 'neg';
				$rep_img_2 = 'highneg';
				$rep_power = $user['reputation'] * -1;
			}
			else
			{
				$rep_img   = 'pos';
				$rep_img_2 = 'highpos';
			}

			if( $rep_power > 500 )
			{
				// work out the bright green shiny bars, cos they cost 100 points, not the normal 100
				$rep_power = ( $rep_power - ($rep_power - 500) ) + ( ($rep_power - 500) / 2 );
			}

			// shiny, shiny, shiny boots...
			// ok, now we can work out the number of bars/pippy things
			$rep_bar = intval($rep_power / 100);
			if( $rep_bar > 10 )
			{
				$rep_bar = 10;
			}

			if( $user['g_rep_hide'] ) // can set this to a group option if required, via admin?
			{
				$posneg = 'off';
				$rep_level = 'rep_off';
			}
			else
			{ // it ain't off then, so get on with it! I wanna see shiny stuff!!
				$rep_level = $user_reputation ? $user_reputation : 'rep_undefined';// just incase

				for( $i = 0; $i <= $rep_bar; $i++ )
				{
					if( $i >= 5 )
					{
						$posneg .= "<img src='pic/rep/reputation_$rep_img_2.gif' border='0' alt=\"Reputation Power $rep_power\n{$user['username']} $rep_level\" title=\"Reputation Power $rep_power {$user['username']} $rep_level\" />";
					}
					else
					{
						$posneg .= "<img src='pic/rep/reputation_$rep_img.gif' border='0' alt=\"Reputation Power $rep_power\n{$user['username']} $rep_level\" title=\"Reputation Power $rep_power {$user['username']} $rep_level\" />";
					}
				}
			}
			
			// now decide if we in a forum or statusbar?
			if( $mode === 0 )
			return "Rep: ".$posneg . "<br /><a href='javascript:;' onclick=\"PopUp('{$TBDEV['baseurl']}/reputation.php?pid={$user['id']}','Reputation',400,241,1,1);\"><img src='./pic/plus.gif' border='0' alt='Add reputation:: {$user['username']}' title='Add reputation:: {$user['username']}' /></a>";
			else
			return "Rep: ".$posneg;
			
		} // END IF ONLINE
		
		// default
		return '<span title="Set offline by admin setting">Rep System Offline</span>';
	}
////////////// REP SYSTEM END //////////

function get_user_icons($arr, $big = false)
  {
    global $TBDEV;
    
    if ($big)
    {
      $donorpic = "starbig.gif";
      $warnedpic = "warnedbig.gif";
      $disabledpic = "disabledbig.gif";
      $style = "style='margin-left: 4pt'";
    }
    else
    {
      $donorpic = "star.gif";
      $warnedpic = "warned.gif";
      $disabledpic = "disabled.gif";
      $style = "style=\"margin-left: 2pt\"";
    }
    $pics = $arr["donor"] == "yes" ? "<img src=\"{$TBDEV['pic_base_url']}{$donorpic}\" alt='Donor' border='0' $style />" : "";
    if ($arr["enabled"] == "yes")
      $pics .= $arr["warned"] == "yes" ? "<img src=\"{$TBDEV['pic_base_url']}{$warnedpic}\" alt=\"Warned\" border='0' $style />" : "";
    else
      $pics .= "<img src=\"{$TBDEV['pic_base_url']}{$disabledpic}\" alt=\"Disabled\" border='0' $style />\n";
    return $pics;
}

function get_ratio_color($ratio)
  {
    if ($ratio < 0.1) return "#ff0000";
    if ($ratio < 0.2) return "#ee0000";
    if ($ratio < 0.3) return "#dd0000";
    if ($ratio < 0.4) return "#cc0000";
    if ($ratio < 0.5) return "#bb0000";
    if ($ratio < 0.6) return "#aa0000";
    if ($ratio < 0.7) return "#990000";
    if ($ratio < 0.8) return "#880000";
    if ($ratio < 0.9) return "#770000";
    if ($ratio < 1) return "#660000";
    return "#000000";
  }

function get_slr_color($ratio)
  {
    if ($ratio < 0.025) return "#ff0000";
    if ($ratio < 0.05) return "#ee0000";
    if ($ratio < 0.075) return "#dd0000";
    if ($ratio < 0.1) return "#cc0000";
    if ($ratio < 0.125) return "#bb0000";
    if ($ratio < 0.15) return "#aa0000";
    if ($ratio < 0.175) return "#990000";
    if ($ratio < 0.2) return "#880000";
    if ($ratio < 0.225) return "#770000";
    if ($ratio < 0.25) return "#660000";
    if ($ratio < 0.275) return "#550000";
    if ($ratio < 0.3) return "#440000";
    if ($ratio < 0.325) return "#330000";
    if ($ratio < 0.35) return "#220000";
    if ($ratio < 0.375) return "#110000";
    return "#000000";
  }


function get_user_class()
{
    global $CURUSER;
    return $CURUSER["class"];
}

function get_user_class_name($class)
{
  switch ($class)
  {
    case UC_USER: return "User";

    case UC_POWER_USER: return "Power User";

    case UC_VIP: return "VIP";

    case UC_UPLOADER: return "Uploader";

    case UC_MODERATOR: return "Moderator";

    case UC_ADMINISTRATOR: return "Administrator";

    case UC_SYSOP: return "SysOp";
  }
  return "";
}

function is_valid_user_class($class)
{
  return is_numeric($class) && floor($class) == $class && $class >= UC_USER && $class <= UC_SYSOP;
}

function is_valid_id($id)
{
  return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}

?>