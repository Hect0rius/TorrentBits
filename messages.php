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
ob_start("ob_gzhandler");
require_once "include/bittorrent.php";
require_once "include/bbcode_functions.php";
require_once "include/user_functions.php";
// Connect to DB & check login
dbconn();
loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('messages') );
// Define constants
define('PM_DELETED',0); // Message was deleted
define('PM_INBOX',1); // Message located in Inbox for reciever
define('PM_SENTBOX',-1); // GET value for sent box
// Determine action
$action = isset($_GET['action']) ? (string) $_GET['action'] : false;
if (!$action)
{
	$action = isset($_POST['action']) ? (string) $_POST['action'] : 'viewmailbox';
	//if (!$action)
		//$action = 'viewmailbox';
}

    // View listing of Messages in mail box
    if ($action == "viewmailbox")
    {
    // Get Mailbox Number
    $mailbox = isset($_GET['box']) ? (int)$_GET['box'] : PM_INBOX;
    //if (!$mailbox)
      //$mailbox = PM_INBOX;

    // Get Mailbox Name
    if ($mailbox != PM_INBOX && $mailbox != PM_SENTBOX)
    {
    $res = mysql_query('SELECT name FROM pmboxes WHERE userid=' . sqlesc($CURUSER['id']) . ' AND boxnumber=' . sqlesc($mailbox) . ' LIMIT 1') or

    sqlerr(__FILE__,__LINE__);
    if (mysql_num_rows($res) == 0)
      stderr("{$lang['messages_error']}","{$lang['messages_invalid_box']}");

    $mailbox_name = mysql_fetch_array($res);
    $mailbox_name = htmlspecialchars($mailbox_name[0]);
    }
    else
    {
    if ($mailbox == PM_INBOX)
      $mailbox_name = "{$lang['messages_inbox']}";
    else
      $mailbox_name = "{$lang['messages_sentbox']}";
    }
    //$pmcount = mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM messages WHERE receiver = ".$CURUSER['id']));
    $pmcount = mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM messages WHERE receiver=".$CURUSER['id']." AND location >= '1' || sender=".$CURUSER['id']." AND saved = 'yes' ")) or sqlerr(__FILE__,__LINE__);

    $pm_perc = $pmcount[0] ? ($pmcount[0] / 50 * 100) : 0;
    $perc_image = ($pm_perc > 66) ? 'loadbarred.gif' : (($pm_perc > 33) ? 'loadbaryellow.gif' : 'loadbargreen.gif');
    //$image_width = ($pmcount[0] / 250 * 100);
    $image_width = $pm_perc > 0 ? round($pm_perc * 2.5) : 1;
    if($image_width > 250)
        $image_width = 250;
 
    
    $HTMLOUT = '';
    
    $HTMLOUT .= "<!-- <script type='text/javascript' src='js/checkall.js'></script> -->
    <!-- check all -->
    <script type='text/javascript'>
    function checkAll(field) {
       if (field.CheckAll.checked == true) {
          for (i = 0; i < field.length; i++) {
          
                field[i].checked = true;
             }
          
       }
       else {
          for (i = 0; i < field.length; i++) {
          
                field[i].checked = false;
             }
          
       }
    }
    </script>
    <!-- check all -->



    <table style='width: 250px;' cellspacing='1'>

              <tbody><tr>
                <td colspan='3'>".sprintf($lang['messages_percent_full'], $pm_perc)."</td>
              </tr>
              <tr>
                <td colspan='3' nowrap='nowrap' valign='middle'>
                <img src='pic/$perc_image' alt='' align='middle' height='10' width='$image_width' />
                </td>
              </tr>
              <tr>
                <td valign='middle' width='33%'>0%</td>

                <td align='center' valign='middle' width='33%'>50%</td>
                <td align='right' valign='middle' width='33%'>100%</td>
              </tr>
            </tbody></table><br />
            
    <table border='0' cellpadding='4' cellspacing='0' width='737'>
    <tr><td align='right'>".insertJumpTo($mailbox)."</td></tr>
    </table>

    <form action='messages.php' method='post' name='mutliact'>

    <input type='hidden' name='action' value='moveordel' />
    <table border='0' cellpadding='4' cellspacing='0' width='737'>
    <tr>
    <td width='1%' class='colhead'>{$lang['messages_status']}</td>
    <td class='colhead'>Subject </td>";

    if ($mailbox != PM_SENTBOX)
    {
      $HTMLOUT .= "<td width='35%' class='colhead'>{$lang['messages_sender']}</td>";
    }
    else
    {
      $HTMLOUT .= "<td width='35%' class='colhead'>{$lang['messages_receiver']}</td>";
    }

    $HTMLOUT .= "<td width='1%' class='colhead'>{$lang['messages_date']}</td>
    <td width='1%' class='colhead'><input name='CheckAll' id='CheckAll' class='checkbox' value='1' onclick='checkAll(mutliact)' type='checkbox' title='{$lang['messages_checkall']}' />
    </td>
    </tr>";

    if ($mailbox != PM_SENTBOX)
    {
      $res = mysql_query('SELECT * FROM messages WHERE receiver=' . sqlesc($CURUSER['id']) . ' AND location=' . sqlesc($mailbox) . ' ORDER BY id DESC') or sqlerr(__FILE__,__LINE__);
    }
    else
    {
      $res = mysql_query('SELECT * FROM messages WHERE sender=' . sqlesc($CURUSER['id']) . ' AND saved=\'yes\' ORDER BY id DESC') or sqlerr(__FILE__,__LINE__);
    }

    if (mysql_num_rows($res) == 0)
    {
      $HTMLOUT .= "<tr><td colspan='5' align='center'>{$lang['messages_no_messages']}</td></tr>\n";
    }
    else
    {
      while ($row = mysql_fetch_assoc($res))
      {
        // Get Sender Username
        if ($row['sender'] != 0 && $row['sender'] != $CURUSER['id'])
        {
          $res2 = mysql_query("SELECT username FROM users WHERE id=" . sqlesc($row['sender']));
          $username = mysql_fetch_array($res2);
          $username = "<a href='userdetails.php?id={$row['sender']}'>{$username[0]}</a>";

          $id = 0+$row['sender'];

          $r = mysql_query("SELECT id FROM friends WHERE userid={$CURUSER['id']} AND friendid=$id") or sqlerr(__FILE__, __LINE__);
          $friend = mysql_num_rows($r);


          if ($friend) 
          {
            $username .= "&nbsp;<a href='friends.php?action=delete&amp;type=friend&amp;targetid=$id'>{$lang['messages_remove_friends']}</a>";
          } 
          else 
          {
            $username .= "&nbsp;<a href='friends.php?action=add&amp;type=friend&amp;targetid=$id'>{$lang['messages_add_friends']}</a>";
          }
        }
        elseif ($row['sender'] == $CURUSER['id'])
        {
          $res2 = mysql_query("select username FROM users WHERE id=" . sqlesc($row['receiver']));
          $username = mysql_fetch_array($res2);
          $username = "<a href='userdetails.php?id={$row['receiver']}'>{$username[0]}</a>";

          //if ($row['sender'] == $CURUSER['id'])
          //$id = 0+$row['receiver'];
          //else
          $id = 0+$row['receiver'];

        }
        else
        {
          $username = "System";
        }
        
        $subject = htmlspecialchars($row['subject']);

        if (strlen($subject) <= 0)
        {
          $subject = "{$lang['messages_no_subject']}";
        }

        if ($row['unread'] == 'yes'/* && $mailbox != PM_SENTBOX*/)
        {
          $HTMLOUT .= "<tr>\n<td align='center'><img src=\"pic/unreadpm.gif\" title='{$lang['messages_unread']}' alt=\"{$lang['messages_unread_title']}\" /></td>\n";
        }
        else
        {
          $HTMLOUT .= "<tr>\n<td align='center'><img src='pic/readpm.gif' title='{$lang['messages_read']}e' alt='{$lang['messages_read_title']}' /></td>\n";
        }
        
        $HTMLOUT .= "<td align='left'>
        <a href='messages.php?action=viewmessage&amp;id={$row['id']}'>$subject</a></td>
        <td align='left'>$username</td>
        <td nowrap='nowrap'>" . get_date($row['added'], '') . "</td>
        <td><input type='checkbox' name='messages[]' value='{$row['id']}' /></td>\n</tr>";
        }
    }

    $HTMLOUT .= "<tr class='colhead'>
    <td colspan='5' align='right' class='colhead'>
    <input type='submit' name='move' value='{$lang['messages_move']}' class='btn' /> 
    <select name='box'>
        <option value='1'>{$lang['messages_inbox']}</option>";

            $res = mysql_query('select * FROM pmboxes WHERE userid=' . sqlesc($CURUSER['id']) . ' ORDER BY boxnumber') or sqlerr(__FILE__,__LINE__);
            while ($row = mysql_fetch_assoc($res))
            {
              $HTMLOUT .= "<option value='{$row['boxnumber']}'>" . htmlspecialchars($row['name']) . "</option>\n";
            }


 /*
          print("<p align='right'><input type='button' value=\"Check All\" onclick=\"this.value=check(form)\"><input type='submit' value=\"Delete selected\" /></p>");
    print("</form>");
         */
            $HTMLOUT .= "</select> or <input type='submit' name='delete' value='{$lang['messages_delete']}' class='btn' />
          </td>
        </tr>
       </table>
      </form>
      <table border='0' cellpadding='4' cellspacing='0' width='737'><tr><td colspan='5'>
    <div align='left'><img src='pic/unreadpm.gif' title='{$lang['messages_unread_msg']}' alt='{$lang['messages_unread_title']}' /> {$lang['messages_unread_msg']}<br />
    <img src='pic/readpm.gif' title='{$lang['messages_read_msg']}' alt='{$lang['messages_read_title']}' /> {$lang['messages_read_msg']}</div>
    <div align='right'>
    <a href='messages.php'>{$lang['messages_return']}</a>
    </div></td></tr></table>";
    
    /////////////////// HTML OUTPUT ///////////////////////////////////////
    print stdhead($mailbox_name, false) . $HTMLOUT . stdfoot();
    }
    
    if ($action == "viewmessage")
    {
      $pm_id = (int) $_GET['id'];
      if (!$pm_id)
      {
        stderr("{$lang['messages_error']}","{$lang['messages_access']}");
      }

      // Get the message
      $res = mysql_query("select * FROM messages WHERE id=" . sqlesc($pm_id)." AND (receiver=". sqlesc($CURUSER['id']) . " OR (sender=" . sqlesc($CURUSER['id']) . " AND saved='yes')) LIMIT 1") or sqlerr(__FILE__,__LINE__);
      if (!$res)
      {
        stderr("{$lang['messages_error']}","{$lang['messages_access']}");
      }

      // Prepare for displaying message
      $message = mysql_fetch_assoc($res) or header("Location: messages.php");
      
      if ($message['sender'] == $CURUSER['id'])
      {
        // Display to
        $res2 = mysql_query("select username FROM users WHERE id=" . sqlesc($message['receiver'])) or sqlerr(__FILE__,__LINE__);
        $sender = mysql_fetch_array($res2);
        $sender = "<a href=\"userdetails.php?id=" . $message['receiver'] . "\">" . $sender[0] . "</a>";
        $reply = "";
        $from = "To";
      }
      else
      {
        $from = "{$lang['messages_from']}";
        if ($message['sender'] == 0)
        {
          $sender = "{$lang['messages_system']}";
          $reply = "";
        }
        else
        {
          $res2 = mysql_query("select username FROM users WHERE id=" . sqlesc($message['sender'])) or sqlerr(__FILE__,__LINE__);
          $sender = mysql_fetch_array($res2);
          $sender = "<a href='userdetails.php?id={$message['sender']}'>{$sender[0]}</a>";
          $reply = "<a href='sendmessage.php?receiver={$message['sender']}&amp;replyto={$pm_id}'><span class='btn'>{$lang['messages_reply']}</span></a>";
        }
      }
      
      $body = format_comment($message['msg']);
      $added = get_date($message['added'], '');
      
      if ($CURUSER['class'] >= UC_MODERATOR && $message['sender'] == $CURUSER['id'])
      {
        $unread = ($message['unread'] == 'yes' ? "<span style='color: #FF0000;'><b>{$lang['messages_new']}</b></a>" : "");
      }
      else
      {
        $unread = "";
      }
      
      $subject = htmlspecialchars($message['subject']);
      
      if (strlen($subject) <= 0)
      {
        $subject = "{$lang['messages_no_subject']}";
      }

      if ($message['unread'] === 'yes')
      {
      // Mark message unread
      @mysql_query("UPDATE messages SET unread='no' WHERE id=" . sqlesc($pm_id) . " AND receiver=" . sqlesc($CURUSER['id']) . " LIMIT 1");
      }

      $HTMLOUT = '';
      
      $HTMLOUT .= "<h1>{$subject}</h1>
      <table width='737' border='0' cellpadding='4' cellspacing='0'>
      <tr>
      <td width='50%' class='colhead'>{$from}</td>
      <td width='50%' class='colhead'>{$lang['messages_date']}</td>
      </tr>
      <tr>
      <td>{$sender}</td>
      <td>{$added}&nbsp;&nbsp;{$unread}</td>
      </tr>
      <tr>
      <td colspan='2' align='left'>{$body}</td>
      </tr>
      <tr>
      <td colspan='2'><div style='float:left;vertical-align:middle'>
      <form action='messages.php' method='post'>
      <input type='hidden' name='action' value='moveordel' />
      <input type='hidden' name='id' value='{$pm_id}' />
      Move to: <select name='box'><option value='1'>{$lang['messages_inbox']}</option>";
      
      $res = mysql_query('select * FROM pmboxes WHERE userid=' . sqlesc($CURUSER['id']) . ' ORDER BY boxnumber') or sqlerr(__FILE__,__LINE__);
      while ($row = mysql_fetch_assoc($res))
      {
        $HTMLOUT .= "<option value='{$row['boxnumber']}'>" . htmlspecialchars($row['name']) . "</option>\n";
      }
      
      $HTMLOUT .= "</select> <input type='submit' name='move' value='{$lang['messages_move']}' class='btn' />
      </form></div>
      <span style='float:right;vertical-align:inherit'><a href='messages.php'><span class='btn'>{$lang['messages_return']}</span></a>&nbsp;<a href='messages.php?action=deletemessage&amp;id=$pm_id'><span class='btn'>{$lang['messages_delete']}</span></a>&nbsp;{$reply}&nbsp;<a href='messages.php?action=forward&amp;id={$pm_id}'><span class='btn'>{$lang['messages_forward']}</span></a></span></td>
      </tr>
      </table>";
      
      // Display message
      print stdhead("PM ($subject)", false). $HTMLOUT . stdfoot();
    }
    
    if ($action == "moveordel")
    {
      $pm_id = (int) $_POST['id'];
      $pm_box = (int) $_POST['box'];
      $pm_messages = $_POST['messages'];
      
      if ($_POST['move'])
      {
        if ($pm_id)
        {
          // Move a single message
          @mysql_query("UPDATE messages SET location=" . sqlesc($pm_box) . " WHERE id=" . sqlesc($pm_id) . " AND receiver=" . $CURUSER['id'] . " LIMIT 1");
        }
        else
        {
          // Move multiple messages
          @mysql_query("UPDATE messages SET location=" . sqlesc($pm_box) . " WHERE id IN (" . implode(", ", array_map("sqlesc",$pm_messages)) . ') AND receiver=' .

          $CURUSER['id']);
        }
      // Check if messages were moved
      if (@mysql_affected_rows() == 0)
      {
        stderr("{$lang['messages_error']}","{$lang['messages_not_moved']}");
      }

      header("Location: messages.php?action=viewmailbox&box=" . $pm_box);
      exit();
      }
      elseif ($_POST['delete'])
      {
        if ($pm_id)
        {
          // Delete a single message
          $res = mysql_query("select * FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
          $message = mysql_fetch_assoc($res);
          
          if ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'no')
          {
            mysql_query("DELETE FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
          }
          elseif ($message['sender'] == $CURUSER['id'] && $message['location'] == PM_DELETED)
          {
            mysql_query("DELETE FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
          }
          elseif ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'yes')
          {
            mysql_query("UPDATE messages SET location=0 WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
          }
          elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != PM_DELETED)
          {
            mysql_query("UPDATE messages SET saved='no' WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
          }
        }
        else
        {
          // Delete multiple messages
          foreach ($pm_messages as $id)
          {
            $res = mysql_query("select * FROM messages WHERE id=" . sqlesc((int) $id));
            $message = mysql_fetch_assoc($res);
            
            if ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'no')
            {
              mysql_query("DELETE FROM messages WHERE id=" . sqlesc((int) $id)) or sqlerr(__FILE__,__LINE__);
            }
            elseif ($message['sender'] == $CURUSER['id'] && $message['location'] == PM_DELETED)
            {
              mysql_query("DELETE FROM messages WHERE id=" . sqlesc((int) $id)) or sqlerr(__FILE__,__LINE__);
            }
            elseif ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'yes')
            {
              mysql_query("UPDATE messages SET location=0 WHERE id=" . sqlesc((int) $id)) or sqlerr(__FILE__,__LINE__);
            }
            elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != PM_DELETED)
            {
              mysql_query("UPDATE messages SET saved='no' WHERE id=" . sqlesc((int) $id)) or sqlerr(__FILE__,__LINE__);
            }
          }
        }
        // Check if messages were moved
        if (@mysql_affected_rows() == 0)
        {
          stderr("{$lang['messages_error']}","{$lang['messages_not_deleted']}");
        }
        else
        {
          header("Location: messages.php?action=viewmailbox");
          exit();
        }
      }
      stderr("{$lang['messages_error']}","{$lang['messages_no_action']}");
    }
    
    if ($action == "forward")
    {
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
      // Display form
      $pm_id = (int) $_GET['id'];

      // Get the message
      $res = mysql_query("select * FROM messages WHERE id=" . sqlesc($pm_id) . " AND (receiver=" . sqlesc($CURUSER['id']) . " OR sender=" . sqlesc($CURUSER['id']) .") LIMIT 1") or sqlerr(__FILE__,__LINE__);
      
      if (!$res)
      {
        stderr("{$lang['messages_error']}","{$lang['messages_access_forward']}");
      }
      
      if (mysql_num_rows($res) == 0)
      {
        stderr("{$lang['messages_error']}","{$lang['messages_access_forward']}");
      }
      
      $message = mysql_fetch_assoc($res);

      // Prepare variables
      $subject = "{$lang['messages_fwd']}" . htmlspecialchars($message['subject']);
      $from = $message['sender'];
      $orig = $message['receiver'];

      $res = mysql_query("select username FROM users WHERE id=" . sqlesc($orig) . " OR id=" . sqlesc($from)) or sqlerr(__FILE__,__LINE__);
      $orig2 = mysql_fetch_assoc($res);
      
      $orig_name = "<a href='userdetails.php?id=$from'>{$orig2['username']}</a>";
      
      if ($from == 0)
      {
        $from_name = "{$lang['messages_system']}";
        $from2['username'] = "{$lang['messages_system']}";
      }
      else
      {
        $from2 = mysql_fetch_array($res);
        $from_name = "<a href='userdetails.php?id=$from'>{$from2['username']}</a>";
      }

      $body = sprintf($lang['messages_original'], $from2['username']) . format_comment($message['msg']);

      $HTMLOUT = '';
      
      $HTMLOUT .= "<h1>$subject</h1>
      <form action='messages.php' method='post'>
      <input type='hidden' name='action' value='forward' />
      <input type='hidden' name='id' value='$pm_id' />
      <table border='0' cellpadding='4' cellspacing='0'  width='737'>
      <tr>
      <td class='colhead'>{$lang['messages_to']}</td>
      <td align='left'><input type='text' name='to' value='{$lang['messages_username']}' size='83' /></td>
      </tr>
      <tr>
      <td class='colhead'>Orignal<br />{$lang['messages_receiver']}</td>
      <td align='left'>$orig_name</td>
      </tr>
      <tr>
      <td class='colhead'>{$lang['messages_from']}</td>
      <td align='left'>$from_name</td>
      </tr>
      <tr>
      <td class='colhead'>{$lang['messages_subject']}</td>
      <td align='left'><input type='text' name='subject' value='$subject' size='83' /></td>
      </tr>
      <tr>
      <td class='colhead'>{$lang['messages_message']}</td>
      <td align='left'><textarea name='msg' cols='80' rows='8'></textarea><br />$body</td>
      </tr>
      <tr>
      <td colspan='2' align='left'>{$lang['messages_save']} <input type='checkbox' name='save' value='1' ".($CURUSER['savepms'] == 'yes' ? " checked='checked'":'')." />&nbsp;
      <input type='submit' value='{$lang['messages_forward_btn']}' class='btn' /></td>
      </tr>
      </table>
      </form>";
      
        print stdhead($subject, false) . $HTMLOUT . stdfoot();
      
      }
      else
      {
        // Forward the message
        $pm_id = (int) $_POST['id'];

        // Get the message
        $res = mysql_query("select * FROM messages WHERE id=" . sqlesc($pm_id) . " AND (receiver=" . sqlesc($CURUSER['id']) . " OR sender=" . sqlesc($CURUSER['id']) .") LIMIT 1") or sqlerr(__FILE__,__LINE__);
        
        if (!$res)
        {
          stderr("{$lang['messages_error']}","{$lang['messages_access_forward']}");
        }
        
        if (mysql_num_rows($res) == 0)
        {
          stderr("{$lang['messages_error']}","{$lang['messages_access_forward']}");
        }
        
        $message = mysql_fetch_assoc($res);

        $subject = (string) $_POST['subject'];
        $username = strip_tags($_POST['to']);

        // Try finding a user with specified name
        $res = mysql_query("select id FROM users WHERE LOWER(username)=LOWER(" . sqlesc($username) . ") LIMIT 1");
        if (!$res)
        {
          stderr("{$lang['messages_error']}","{$lang['messages_no_user']}");
        }
        
        if (mysql_num_rows($res) == 0)
        {
          stderr("{$lang['messages_error']}","{$lang['messages_no_user']}");
        }
        
        $to = mysql_fetch_array($res);
        $to = $to[0];

        // Get Orignal sender's username
        if ($message['sender'] == 0)
        {
          $from = "{$lang['messages_system']}";
        }
        else
        {
          $res = mysql_query("select * FROM users WHERE id=" . sqlesc($message['sender'])) or sqlerr(__FILE__,__LINE__);
          $from = mysql_fetch_assoc($res);
          $from = $from['username'];
        }

        $body = (isset($_POST['msg'])?(string)$_POST['msg']:'');
        $body .= sprintf($lang['messages_original1'], $from, $message['msg']);

        $save = (isset($_POST['save'])?(int)$_POST['save']:'');

        if ($save)
        {
          $save = 'yes';
        }
        else
        {
          $save = 'no';
        }

        //Make sure recipient wants this message
        if ($CURUSER['class'] < UC_MODERATOR)
        {
          if ($from["acceptpms"] == "yes")
          {
            $res2 = mysql_query("select * FROM blocks WHERE userid=$to AND blockid=" . $CURUSER["id"]) or sqlerr(__FILE__, __LINE__);
            if (mysql_num_rows($res2) == 1)
            stderr("{$lang['messages_refused']}", "{$lang['messages_blocked']}");
          }
          elseif ($from["acceptpms"] == "friends")
          {
            $res2 = mysql_query("select * FROM friends WHERE userid=$to AND friendid=" . $CURUSER["id"]) or sqlerr(__FILE__, __LINE__);
            if (mysql_num_rows($res2) != 1)
            stderr("{$lang['messages_refused']}", "{$lang['messages_only_friends']}");
          }
          elseif ($from["acceptpms"] == "no")
          {
            stderr("{$lang['messages_refused']}", "{$lang['messages_decline']}");
          }
        }

        @mysql_query("INSERT INTO messages (poster, sender, receiver, added, subject, msg, location, saved) VALUES({$CURUSER["id"]}, {$CURUSER["id"]}, $to, " . time() . ", " . sqlesc($subject) . "," .
        sqlesc($body) . ", " . sqlesc(PM_INBOX) . ", " . sqlesc($save) . ")") or sqlerr(__FILE__, __LINE__);

        stderr("{$lang['messages_success']}", "{$lang['messages_pm_forwarded']}");
      }
    }
    
    
    if ($action == "editmailboxes")
    {
      $res = mysql_query("select * FROM pmboxes WHERE userid=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__,__LINE__);

      
      $HTMLOUT = '';
      
      $HTMLOUT .= "<h1>{$lang['messages_edit_mb']}</h1>
      <table width='737' border='0' cellpadding='4' cellspacing='0'>
      <tr>
      <td class='colhead' align='left'>{$lang['messages_add_mb']}</td>
      </tr>
      <tr>
      <td align='left'>{$lang['messages_extra']}<br />
      <form action='messages.php' method='get'>
      <input type='hidden' name='action' value='editmailboxes2' />
      <input type='hidden' name='action2' value='add' />

      <input type='text' name='new1' size='40' maxlength='14' /><br />
      <input type='text' name='new2' size='40' maxlength='14' /><br />
      <input type='text' name='new3' size='40' maxlength='14' /><br />
      <input type='submit' value='{$lang['messages_add']}' class='btn' />
      </form></td>
      </tr>
      <tr>
      <td class='colhead' align='left'>{$lang['messages_mb_edit']}</td>
      </tr>
      <tr>
      <td align='left'>{$lang['messages_vir_dir']} 
      <form action='messages.php' method='get'>
      <input type='hidden' name='action' value='editmailboxes2' />
      <input type='hidden' name='action2' value='edit' />";

      if (!$res)
      {
        $HTMLOUT .= "<span align='center'><b>{$lang['messages_no_edit']}<b></span>";
      }
      
      if (mysql_num_rows($res) == 0)
      {
        $HTMLOUT .= "<span align='center'><b>{$lang['messages_no_edit']}</b></span>";
      }
      else
      {
        while ($row = mysql_fetch_assoc($res))
        {
          $id = $row['id'];
          $name = htmlspecialchars($row['name']);
          $HTMLOUT .= "<input type='text' name='edit$id' value='$name' size='40' maxlength='14' /><br />\n";
        }
      
        $HTMLOUT .= "<input type='submit' value='{$lang['messages_edit']}' class='btn' />";
      }
      
      $HTMLOUT .= "</form></td>
      </tr>
      </table>";
      
      print stdhead("{$lang['messages_edit_mb']}s", false) . $HTMLOUT . stdfoot();
    }
    
    
    
    if ($action == "editmailboxes2")
    {
      $action2 = (string) $_GET['action2'];
      
      if (!$action2)
      {
      stderr("Error","No action.");
      }
    
      if ($action2 == "add")
      {
        $name1 = $_GET['new1'];
        $name2 = $_GET['new2'];
        $name3 = $_GET['new3'];

        // Get current max box number
        $res = mysql_query("select MAX(boxnumber) FROM pmboxes WHERE userid=" . sqlesc($CURUSER['id']));
        $box = mysql_fetch_array($res);
        $box = (int) $box[0];
        
        if ($box < 2)
        {
          $box = 1;
        }

        if (strlen($name1) > 0)
        {
          ++$box;
          @mysql_query("INSERT INTO pmboxes (userid, name, boxnumber) VALUES (" . sqlesc($CURUSER['id']) . ", " . sqlesc($name1) . ", $box)") or sqlerr(__FILE__,__LINE__);
        }
        
        if (strlen($name2) > 0)
        {
          ++$box;
          @mysql_query("INSERT INTO pmboxes (userid, name, boxnumber) VALUES (" . sqlesc($CURUSER['id']) . ", " . sqlesc($name2) . ", $box)") or sqlerr(__FILE__,__LINE__);
        }
        
        if (strlen($name3) > 0)
        {
          ++$box;
          @mysql_query("INSERT INTO pmboxes (userid, name, boxnumber) VALUES (" . sqlesc($CURUSER['id']) . ", " . sqlesc($name3) . ", $box)") or sqlerr(__FILE__,__LINE__);
        }
        
        header("Location: messages.php?action=editmailboxes");
        exit();
      }
        
      if ($action2 == "edit");
      {
        $res = mysql_query("select * FROM pmboxes WHERE userid=" . sqlesc($CURUSER['id']));
        
        if (!$res)
        {
          stderr("{$lang['messages_error']}","{$lang['messages_no_mb_edit']}");
        }
        
        if (mysql_num_rows($res) == 0)
        {
          stderr("{$lang['messages_error']}","{$lang['messages_no_mb_edit']}");
        }
        else
        {
          while ($row = mysql_fetch_assoc($res))
          {
            if (isset($_GET['edit' . $row['id']]))
            {
              if ($_GET['edit' . $row['id']] != $row['name'])
              {
                // Do something
                if (strlen($_GET['edit' . $row['id']]) > 0)
                {
                  // Edit name
                  @mysql_query("UPDATE pmboxes SET name=" . sqlesc($_GET['edit' . $row['id']]) . " WHERE id=" . sqlesc($row['id']) . " LIMIT 1");
                }
                else
                {
                  // Delete
                  @mysql_query("DELETE FROM pmboxes WHERE id=" . sqlesc($row['id']) . " LIMIT 1");
                  // Delete all messages from this folder (uses multiple queries because we can only perform security checks in WHERE clauses)
                  @mysql_query("UPDATE messages SET location=0 WHERE saved='yes' AND location=" . sqlesc($row['boxnumber']) . " AND receiver=" . sqlesc($CURUSER['id']));
                  @mysql_query("UPDATE messages SET saved='no' WHERE saved='yes' AND sender=" . sqlesc($CURUSER['id']));
                  @mysql_query("DELETE FROM messages WHERE saved='no' AND location=" . sqlesc($row['boxnumber']) . " AND receiver=" . sqlesc($CURUSER['id']));
                  @mysql_query("DELETE FROM messages WHERE location=0 AND saved='yes' AND sender=" . sqlesc($CURUSER['id']));
                }
              }
            }
          }
        header("Location: messages.php?action=editmailboxes");
        exit();
        }
      }
    }
    
    
    
    if ($action == "deletemessage")
    {
      $pm_id = (int) $_GET['id'];

      // Delete message
      $res = mysql_query("select * FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
      
      if (!$res)
      {
        stderr("{$lang['messages_error']}","{$lang['messages_no_id']}");
      }
      
      if (mysql_num_rows($res) == 0)
      {
        stderr("{$lang['messages_error']}","{$lang['messages_no_id']}");
      }
      
      $message = mysql_fetch_assoc($res);
      
      if ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'no')
      {
        $res2 = mysql_query("DELETE FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
      }
      elseif ($message['sender'] == $CURUSER['id'] && $message['location'] == PM_DELETED)
      {
        $res2 = mysql_query("DELETE FROM messages WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
      }
      elseif ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'yes')
      {
        $res2 = mysql_query("UPDATE messages SET location=0 WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
      }
      elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != PM_DELETED)
      {
        $res2 = mysql_query("UPDATE messages SET saved='no' WHERE id=" . sqlesc($pm_id)) or sqlerr(__FILE__,__LINE__);
      }
      
      if (!$res2)
      {
        stderr("{$lang['messages_error']}","{$lang['messages_no_delete']}");
      }
      
      if (mysql_affected_rows() == 0)
      {
        stderr("{$lang['messages_error']}","{$lang['messages_no_delete']}");
      }
      else
      {
        header("Location: messages.php?action=viewmailbox&id=" . $message['location']);
        exit();
      }
    }

//----- FUNCTIONS ------
function insertJumpTo($selected = 0)
    {
      global $CURUSER, $lang;
      
      $htmlout = '';
      
      $res = mysql_query("select * FROM pmboxes WHERE userid=" . sqlesc($CURUSER['id']) ." ORDER BY boxnumber");
      
      $htmlout .= "<form action='messages.php' method='get'>
      <input type='hidden' name='action' value='viewmailbox' />{$lang['messages_jump']} <select name='box'>
      <option value='1'" .($selected == PM_INBOX ? " selected='selected'" : "").">{$lang['messages_inbox']}</option>
      <option value='-1'" .($selected == PM_SENTBOX ? " selected='selected'" : "").">{$lang['messages_sentbox']}</option>";
      
      while ($row = mysql_fetch_assoc($res))
      {
        if ($row['boxnumber'] == $selected)
        {
          $htmlout .= "<option value='{$row['boxnumber']}' selected='selected'>{$row['name']}</option>\n";
        }
        else
        {
          $htmlout .= "<option value='{$row['boxnumber']}'>{$row['name']}</option>\n";
        }
      }
      $htmlout .= "</select> <input type='submit' value='{$lang['messages_go']}' class='btn' /></form>";
      
      return $htmlout;
}
?>