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
require_once "include/password_functions.php";


dbconn();

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('takeprofedit') );
    
    if (!mkglobal("email:chpassword:passagain:chmailpass"))
      stderr("Update failed!", $lang['takeprofedit_no_data']);

    // $set = array();

    $updateset = array();
    $changedemail = 0;

    if ($chpassword != "") 
    {
      if (strlen($chpassword) > 40)
        stderr("Update failed!", $lang['takeprofedit_pass_long']);
      if ($chpassword != $passagain)
        stderr("Update failed!", $lang['takeprofedit_pass_not_match']);
      
      $secret = mksecret();

      $passhash = make_passhash( $secret, md5($chpassword) );

      $updateset[] = "secret = " . sqlesc($secret);
      $updateset[] = "passhash = " . sqlesc($passhash);
      logincookie($CURUSER['id'], $passhash);
    }

    if ($email != $CURUSER["email"]) 
    {
      if (!validemail($email))
        stderr("Update failed!", $lang['takeprofedit_not_valid_email']);
      $r = mysql_query("SELECT id FROM users WHERE email=" . sqlesc($email)) or sqlerr();
      if ( mysql_num_rows($r) > 0 || ($CURUSER["passhash"] != make_passhash( $CURUSER['secret'], md5($chmailpass) ) ) )
        stderr("Update failed!", $lang['takeprofedit_address_taken']);
      $changedemail = 1;
    }


    $acceptpms = $_POST["acceptpms"];
    $deletepms = isset($_POST["deletepms"]) ? "yes" : "no";
    $savepms = (isset($_POST['savepms']) && $_POST["savepms"] != "" ? "yes" : "no");
    $pmnotif = isset($_POST["pmnotif"]) ? $_POST["pmnotif"] : '';
    $emailnotif = isset($_POST["emailnotif"]) ? $_POST["emailnotif"] : '';
    $notifs = ($pmnotif == 'yes' ? "[pm]" : "");
    $notifs .= ($emailnotif == 'yes' ? "[email]" : "");
    $r = mysql_query("SELECT id FROM categories") or sqlerr();
    $rows = mysql_num_rows($r);
    for ($i = 0; $i < $rows; ++$i)
    {
      $a = mysql_fetch_assoc($r);
      if (isset($_POST["cat{$a['id']}"]) && $_POST["cat{$a['id']}"] == 'yes')
        $notifs .= "[cat{$a['id']}]";
    }

    /////// do the avatar stuff
    $avatars = ($_POST["avatars"] != "" ? "yes" : "no");
    $avatar = trim( urldecode( $_POST["avatar"] ) );
      
      if ( preg_match( "/^http:\/\/$/i", $avatar ) 
          or preg_match( "/[?&;]/", $avatar ) 
          or preg_match("#javascript:#is", $avatar ) 
          or !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $avatar ) 
          )
      {
        $avatar='';
      }
      
      if( !empty($avatar) ) 
      {
        $img_size = @GetImageSize( $avatar );

        if($img_size == FALSE || !in_array($img_size['mime'], $TBDEV['allowed_ext']))
          stderr($lang['takeprofedit_user_error'], $lang['takeprofedit_image_error']);

        if($img_size[0] < 5 || $img_size[1] < 5)
          stderr($lang['takeprofedit_user_error'], $lang['takeprofedit_small_image']);
      
        if ( ( $img_size[0] > $TBDEV['av_img_width'] ) OR ( $img_size[1] > $TBDEV['av_img_height'] ) )
        { 
            $image = resize_image( array(
                             'max_width'  => $TBDEV['av_img_width'],
                             'max_height' => $TBDEV['av_img_height'],
                             'cur_width'  => $img_size[0],
                             'cur_height' => $img_size[1]
                        )      );
                        
          }
          else 
          {
            $image['img_width'] = $img_size[0];
            $image['img_height'] = $img_size[1];
          }
          
    $updateset[] = "av_w = " . $image['img_width'];
    $updateset[] = "av_h = " . $image['img_height'];
    }
    /////////////// avatar end /////////////////

    // $ircnick = $_POST["ircnick"];
    // $ircpass = $_POST["ircpass"];
    $info = $_POST["info"];
    $stylesheet = $_POST["stylesheet"];
    $country = $_POST["country"];

    if(isset($_POST["user_timezone"]) && preg_match('#^\-?\d{1,2}(?:\.\d{1,2})?$#', $_POST['user_timezone']))
    $updateset[] = "time_offset = " . sqlesc($_POST['user_timezone']);

    $updateset[] = "auto_correct_dst = " .(isset($_POST['checkdst']) ? 1 : 0);
    $updateset[] = "dst_in_use = " .(isset($_POST['manualdst']) ? 1 : 0);

    /*
    if ($privacy != "normal" && $privacy != "low" && $privacy != "strong")
      bark("whoops");

    $updateset[] = "privacy = '$privacy'";
    */

    $updateset[] = "torrentsperpage = " . min(100, 0 + $_POST["torrentsperpage"]);
    $updateset[] = "topicsperpage = " . min(100, 0 + $_POST["topicsperpage"]);
    $updateset[] = "postsperpage = " . min(100, 0 + $_POST["postsperpage"]);

    if (is_valid_id($stylesheet))
      $updateset[] = "stylesheet = '$stylesheet'";
      
    if (is_valid_id($country))
      $updateset[] = "country = $country";


    $updateset[] = "info = " . sqlesc($info);
    $updateset[] = "acceptpms = " . sqlesc($acceptpms);
    $updateset[] = "deletepms = '$deletepms'";
    $updateset[] = "savepms = '$savepms'";
    $updateset[] = "notifs = '$notifs'";
    $updateset[] = "avatar = " . sqlesc($avatar);
    $updateset[] = "avatars = '$avatars'";

    /* ****** */

    $urladd = "";

    if ($changedemail) {
      $sec = mksecret();
      $hash = md5($sec . $email . $sec);
      $obemail = urlencode($email);
      $updateset[] = "editsecret = " . sqlesc($sec);
      //$thishost = $_SERVER["HTTP_HOST"];
      //$thisdomain = preg_replace('/^www\./is', "", $thishost);
      
      $body = str_replace(array('<#USERNAME#>', '<#SITENAME#>', '<#USEREMAIL#>', '<#IP_ADDRESS#>', '<#CHANGE_LINK#>'),
                        array($CURUSER['username'], $TBDEV['site_name'], $email, $_SERVER['REMOTE_ADDR'], "{$TBDEV['baseurl']}/confirmemail.php?uid={$CURUSER['id']}&key=$hash&email=$obemail"),
                        $lang['takeprofedit_email_body']);
      
      
      mail($email, "{$TBDEV['site_name']} {$lang['takeprofedit_confirm']}", $body, "From: {$TBDEV['site_email']}");

      $urladd .= "&mailsent=1";
    }

    @mysql_query("UPDATE users SET " . implode(", ", $updateset) . " WHERE id = " . $CURUSER["id"]) or sqlerr(__FILE__,__LINE__);

    header("Location: {$TBDEV['baseurl']}/my.php?edited=1" . $urladd);

/////////////////////////////////
//worker function
 /////////////////////////////////
function resize_image($in)
    {

        $out = array(
                'img_width'  => $in['cur_width'],
                'img_height' => $in['cur_height']
              );
        
        if ( $in['cur_width'] > $in['max_width'] )
        {
          $out['img_width']  = $in['max_width'];
          $out['img_height'] = ceil( ( $in['cur_height'] * ( ( $in['max_width'] * 100 ) / $in['cur_width'] ) ) / 100 );
          $in['cur_height'] = $out['img_height'];
          $in['cur_width']  = $out['img_width'];
        }
        
        if ( $in['cur_height'] > $in['max_height'] )
        {
          $out['img_height']  = $in['max_height'];
          $out['img_width']   = ceil( ( $in['cur_width'] * ( ( $in['max_height'] * 100 ) / $in['cur_height'] ) ) / 100 );
        }
        
      
        return $out;
    }

?>