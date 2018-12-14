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



    $lang = array_merge( $lang, load_language('ad_forummanage') );
    
    if( (get_user_class() < UC_MODERATOR) || ($CURUSER['id'] !== '1')) //sysop id check
    stderr("{$lang['stderr_error']}", "{$lang['text_permission']}");

    $mode = isset($_GET['mode']) ? $_GET['mode'] : ''; //if not goto default!


    switch($mode) {
					case 'edit': 
					editForum();
					break;
					
					case 'takeedit':
					takeeditForum();
					break;
					
					case 'delete':
					deleteForum();
					break;
					
					case 'takedelete':
					takedeleteForum();
					break;
					
					case 'add':
					addForum();
					break;
					
					case 'takeadd':
					takeaddForum();
					break;
					
					default:
					showForums();
	
	}



function showForums() {

    global $lang;
    
    $HTMLOUT = '';
    $HTMLOUT .= "<span class='btn'><a href='admin.php?action=forummanage&amp;mode=add'>{$lang['btn_addnew']}</a></span><br /><br />";
    $HTMLOUT .= begin_main_frame();
    $HTMLOUT .= "<table width='700' border='0' align='center' cellpadding='2' cellspacing='0'>";
    $HTMLOUT .= "<tr><td class='colhead' align='left'>{$lang['header_name']}</td><td class='colhead'>{$lang['header_topics']}</td><td class='colhead'>{$lang['header_posts']}</td><td class='colhead'>{$lang['header_read']}</td><td class='colhead'>{$lang['header_write']}</td><td class='colhead'>{$lang['header_createtopic']}</td><td class='colhead'>{$lang['header_modify']}</td></tr>";
    $result = mysql_query ("SELECT  * FROM forums ORDER BY sort ASC");
    if ( mysql_num_rows($result) > 0) {

      while($row = mysql_fetch_assoc($result)){

      $HTMLOUT .= "<tr><td><a href='forums.php?action=viewforum&amp;forumid={$row["id"]}'><b>".htmlentities($row["name"], ENT_QUOTES)."</b></a><br />".htmlentities($row["description"], ENT_QUOTES)."</td>";
      $HTMLOUT .= "<td>{$row["topiccount"]}</td><td>{$row["postcount"]}</td><td>{$lang['text_minimal']} " . get_user_class_name($row["minclassread"]) . "</td><td>{$lang['text_minimal']} " . get_user_class_name($row["minclasswrite"]) . "</td><td>{$lang['text_minimal']} " . get_user_class_name($row["minclasscreate"]) . "</td><td align='center' style='white-space: nowrap;'><b><a href='admin.php?action=forummanage&amp;mode=edit&amp;id={$row["id"]}'>{$lang['text_edit']}</a>&nbsp;|&nbsp;<a href='admin.php?action=forummanage&amp;mode=delete&amp;id={$row["id"]}'><font color='red'>{$lang['text_delete']}</font></a></b></td></tr>"; 
          
    } 
    } 
    else 
    {$HTMLOUT .= "<tr><td colspan='7'>{$lang['text_sorry']}</td></tr>";}       
    $HTMLOUT .= "</table>";

    $HTMLOUT .= end_main_frame();
    
    print stdhead("{$lang['stdhead_forummanagetools']}") . $HTMLOUT . stdfoot();
}

function addForum() {
    global $CURUSER, $lang;

    
    $HTMLOUT = '';
    $HTMLOUT .= "<span class='btn'><a href='admin.php?action=forummanage'>{$lang['btn_cancel']}</a></span><br /><br />";
    $HTMLOUT .= begin_main_frame();


    $HTMLOUT .= "<form method='post' action='admin.php?action=forummanage&amp;mode=takeadd'>
    <table width='600'  border='0' cellspacing='0' cellpadding='3' align='center'>
      <tr align='center'>
        <td colspan='2' class='colhead'>{$lang['header_makenew']}</td>
      </tr>
      <tr>
        <td><b>{$lang['table_forumname']}</b></td>
        <td><input name='name' type='text' size='20' maxlength='60' /></td>
      </tr>
      <tr>
        <td><b>{$lang['table_forumdescr']}</b></td>
        <td><input name='desc' type='text' size='30' maxlength='200' /></td>
      </tr>
      <tr>
        <td><b>{$lang['table_minreadperm']}</b></td>
        <td><select name='readclass'>";

    $maxclass = get_user_class();
      for ($i = 0; $i <= $maxclass; ++$i)
      $HTMLOUT .= "<option value='$i'" . ($CURUSER["class"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n";

        $HTMLOUT .= "</select>
        </td>
      </tr>
      <tr>
        <td><b>{$lang['table_minwriteperm']}</b></td>
        <td><select name='writeclass'>";

    $maxclass = get_user_class();
      for ($i = 0; $i <= $maxclass; ++$i)
      $HTMLOUT .= "<option value='$i'" . ($CURUSER["class"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n";

        $HTMLOUT .= "</select></td>
      </tr>
      <tr>
        <td><b>{$lang['table_mincreatetperm']}</b></td>
        <td><select name='createclass'>";

    $maxclass = get_user_class();
      for ($i = 0; $i <= $maxclass; ++$i)
      $HTMLOUT .= "<option value='$i'" . ($CURUSER["class"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n";

        $HTMLOUT .= "</select></td>
      </tr>
      <tr>
        <td><b>{$lang['table_forumrank']}</b></td>
        <td><select name='sort'>";

    $res = mysql_query ("SELECT sort FROM forums");
    $nr = mysql_num_rows($res);
    $maxclass = $nr + 1;
      for ($i = 0; $i <= $maxclass; ++$i)
      $HTMLOUT .= "<option value='$i'>$i </option>\n";

        $HTMLOUT .= "</select>
     
        </td>
      </tr>

      <tr align='center'>
        <td colspan='2'>
        <!--<input type='hidden' name='action' value='takeadd' /> -->
        <input type='submit' name='Submit' value='{$lang['btn_makeforum']}' class='btn' /></td>
      </tr>
      </table>
            </form>";

    //	end_frame();
    $HTMLOUT .= end_main_frame();
   
    print stdhead("{$lang['stdhead_addforum']}") . $HTMLOUT . stdfoot();

}

function editForum() {

    global $lang;
    
    $id = isset($_GET["id"]) ? (int)$_GET["id"] : stderr("Error", "Not Found");

    $HTMLOUT = '';
    $HTMLOUT .= "<span class='btn'><a href='admin.php?action=forummanage'>{$lang['btn_cancel']}</a></span><br /><br />";

    $HTMLOUT .= begin_frame("{$lang['frame_editforum']}");
    
    $result = mysql_query ("SELECT * FROM forums where id = '$id'");
    if (mysql_num_rows($result) > 0) {
      while($row = mysql_fetch_assoc($result)){


      $HTMLOUT .= "<form method='post' action='admin.php?action=forummanage&amp;mode=takeedit'>
      <table width='600'  border='0' cellspacing='0' cellpadding='3' align='center'>
      <tr align='center'>
        <td colspan='2' class='colhead'>{$lang['header_editforum']} ".htmlentities($row["name"], ENT_QUOTES)."</td>
      </tr>
      <tr>
        <td><b>{$lang['table_forumname']}</b></td>
        <td><input name='name' type='text' size='30' maxlength='60' value='".htmlentities($row["name"], ENT_QUOTES)."' /></td>
      </tr>
      <tr>
        <td><b>{$lang['table_forumdescr']}</b></td>
        <td><input name='desc' type='text' size='30' maxlength='200' value='".htmlentities($row["description"], ENT_QUOTES)."' /></td>
      </tr>
      <tr>
        <td><b>{$lang['table_minreadperm']}</b></td>
        <td><select name='readclass'>";

    $maxclass = get_user_class();
      for ($i = 0; $i <= $maxclass; ++$i)
      if( get_user_class_name($i) != "" )
      $HTMLOUT .= "<option value='$i'" . ($row["minclassread"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>";

        $HTMLOUT .= "</select>
        </td>
      </tr>
      <tr>
        <td><b>{$lang['table_minpostrank']}</b></td>
        <td><select name='writeclass'>";

    $maxclass = get_user_class();
      for ($i = 0; $i <= $maxclass; ++$i)
      if( get_user_class_name($i) != "" )
      $HTMLOUT .= "<option value='$i'" . ($row["minclasswrite"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i)."</option>";

        $HTMLOUT .= "</select></td>
      </tr>
      <tr>
        <td><b>{$lang['table_mincreatetrank']}</b></td>
        <td><select name='createclass'>";

    $maxclass = get_user_class();
      for ($i = 0; $i <= $maxclass; ++$i)
      if( get_user_class_name($i) != "" )
      $HTMLOUT .= "<option value='$i'" . ($row["minclasscreate"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i)."</option>";

        $HTMLOUT .= "</select></td>
      </tr>
      <tr>
        <td><b>{$lang['table_forumrank']}</b></td>
        <td><select name='sort'>";

    $res = mysql_query ("SELECT sort FROM forums");
    $nr = mysql_num_rows($res);
    $maxclass = $nr + 1;
      for ($i = 0; $i <= $maxclass; ++$i)
      $HTMLOUT .= "<option value='$i'" . ($row["sort"] == $i ? " selected='selected'" : "") . ">$i</option>";

        $HTMLOUT .= "</select>
        </td>
      </tr>

      <tr align='center'>
        <td colspan='2'>
        <input type='hidden' name='id' value='{$row['id']}' />
        <input type='submit' name='Submit' value='{$lang['btn_editforum']}' class='btn' />
        </td>
      </tr>
      </table>
    </form>";

        }
    } 
    else 
    {$HTMLOUT .= "{$lang['text_sorry']}";}      

    //	end_frame();
    $HTMLOUT .= end_main_frame();
    
    print stdhead("{$lang['stdhead_editforum']}") . $HTMLOUT . stdfoot();
}

function takeaddForum() {
	
    global $lang;
    
    if (!$_POST['name'] && !$_POST['desc']) { header("Location: admin.php?action=forummanage"); die();}

    @mysql_query("INSERT INTO forums 
    (sort, name,  description,  minclassread,  minclasswrite, minclasscreate) VALUES(" . 
    sqlesc($_POST['sort']) . ", " . 
    sqlesc($_POST['name']). ", " . 
    sqlesc($_POST['desc']). ", " . 
    sqlesc($_POST['readclass']) . ", " . 
    sqlesc($_POST['writeclass']) . ", " . 
    sqlesc($_POST['createclass']) . ")");

    if(mysql_affected_rows() === 1)
      stderr("{$lang['stderr_success']}", "{$lang['text_added']}. <a href='admin.php?action=forummanage'>{$lang['text_return']}</a>");
    else
      stderr("{$lang['stderr_success']}", "{$lang['text_error']}. <a href='admin.php?action=forummanage'>{$lang['text_return']}</a>");
    die();

}

function takeeditForum() {

    global $lang;
    
    if (!$_POST['name'] && !$_POST['desc'] && !$_POST['id']) { header("Location: admin.php?action=forummanage"); die();}

    @mysql_query("UPDATE forums SET sort = " . 
    sqlesc($_POST['sort']) . ", name = " . 
    sqlesc($_POST['name']). ", description = " . 
    sqlesc($_POST['desc']). ", minclassread = " . 
    sqlesc($_POST['readclass']) . ", minclasswrite = " . 
    sqlesc($_POST['writeclass']) . ", minclasscreate = " . 
    sqlesc($_POST['createclass']) . " where id = ".
    sqlesc($_POST['id']));

    if(mysql_affected_rows() == 1)
      stderr("{$lang['stderr_success']}", "{$lang['text_edited']}. <a href='admin.php?action=forummanage'>{$lang['text_return']}</a>");
    else
      stderr("{$lang['stderr_error']}", "{$lang['text_error']}. <a href='admin.php?action=forummanage'>{$lang['text_return']}</a>");
    die();
}

function deleteForum() {

    global $lang;
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : stderr("{$lang['stderr_error']}", "{$lang['text_noid']}");
	
		
    $res = @mysql_query("SELECT id FROM topics WHERE forumid=$id");

    if (mysql_num_rows($res) >= 1) 
    {
      print stdhead() . forum_select($id) . stdfoot();
      exit();
    }
    else
    {
      $link =  "{$lang['text_warning']}<a href='admin.php?action=forummanage&amp;mode=takedelete&amp;id=$id'>{$lang['text_warning_cont']}</a>";
      stderr("{$lang['stderr_error']}", $link);
		}
	
}


function takedeleteForum() {

    global $lang;
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : stderr("{$lang['stderr_error']}", "{$lang['text_noid']}");

    if(!isset($_POST['deleteall'])) 
    {
      $res = @mysql_query("SELECT id FROM topics WHERE forumid=$id");
      
      if (mysql_num_rows($res) == 0) 
        @mysql_query("DELETE FROM forums WHERE id=$id");
      
      (mysql_affected_rows() > 0) ? 
    stderr("{$lang['stderr_success']}", "{$lang['text_forumdeleted']} <a href='admin.php?action=forummanage'>{$lang['text_deleted_text']}</a>" ) : stderr("{$lang['stderr_error']}", "{$lang['text_nowheretomove']}");
    }
    else
    {
      $forumid = (isset($_POST['forumid']) && ctype_digit($_POST['forumid'])) ? (int)$_POST['forumid'] : stderr("{$lang['stderr_error']}", "{$lang['text_smthbad']}");
      
      $res = @mysql_query("SELECT id FROM topics WHERE forumid=$id");
      
      if (mysql_num_rows($res) == 0) 
        stderr("{$lang['stderr_error']}", "{$lang['text_notopic']}");
      while($row = mysql_fetch_assoc($res)) 
        $tid[] = $row['id'];
      
      @mysql_query("UPDATE topics SET forumid=$forumid WHERE id IN (".join(',' , $tid).")");
      
      if(mysql_affected_rows() > 0)
      
        @mysql_query("DELETE FROM forums WHERE id=$id");
        
      (mysql_affected_rows() > 0) ? 
    stderr("{$lang['stderr_success']}", "{$lang['text_forumdeleted']}") : stderr("{$lang['stderr_error']}", "{$lang['text_smthbad']}");
        
      
    }

}




function forum_select($currentforum = 0) {

    global $lang;
    
    $HTMLOUT = '';
    
    $HTMLOUT .= "<p align='center'><form method='post' action='admin.php?action=forummanage&amp;mode=takedelete&amp;id=$currentforum' name='jump'>

    <input type='hidden' name='deleteall' value='true' />

    {$lang['text_select']}

    <select name='forumid'>";

    $res = mysql_query("SELECT * FROM forums ORDER BY name") or sqlerr(__FILE__, __LINE__);

    while ($arr = mysql_fetch_assoc($res))
    {
      if ($arr["id"] == $currentforum)
    continue;
        $HTMLOUT .= "<option value='" . $arr["id"] . ($currentforum == $arr["id"] ? "' selected='selected'>" : "'>") . $arr["name"] . "</option>\n";
    }

    $HTMLOUT .= "</select>

    <input type='submit' value='{$lang['btn_moveto']}' class='btn' />

    </form>\n</p>";
    
    return $HTMLOUT;
}

?>