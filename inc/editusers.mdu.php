<?php

if ($member_db[1] != 1) {
    msg("error", "Access Denied", "You don't have permission to edit users");
}
// ********************************************************************************
// List All Available Users + Show Add User Form
// ********************************************************************************
if ($action == "list") {
    echoheader("users", "Manage Users");

    echo'<script language="javascript">
	<!-- begin
	function popupedit(id){
	window.open(\''.$PHP_SELF.'?mod=editusers&action=edituser&id=\'+id,\'User\',\'toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=0,width=360,height=210\');
	}
	function confirmdelete(id){
	var agree=confirm("Are you sure you want to delete this user ?");
	if (agree)
	document.location="'.$PHP_SELF.'?mod=editusers&action=dodeleteuser&id="+id;
	}
	// end -->
	</script>
	<table border=0 cellpadding=0 cellspacing=0 width=654>
    <tr>
	<td width=654 colspan="6">
<!-- Start add edit users table + info + help -->
  <table border="0" width="657"  cellspacing="0" cellpadding="0" height="81" >
    <tr>
      <td valign="bottom" width="311" valign="top" height="1">

      <b>Add User</b>

      </td>
      <td width="5" valign="top"  rowspan="3" height="81">

      </td>
      <td valign="bottom" width="330" height="1"><b>User Levels</b></td>
    </tr>

    <tr>
      <td width="311" rowspan="2" valign="top" height="60" >

<!-- Add User Table -->
  <table class="panel" cellspacing="0" cellpadding="0" width="100%">
  <form method=post action="'.$PHP_SELF.'">
    <tr>
      <td >&nbsp;Username</td>
      <td ><input size=21 type=text name=regusername></td>
    </tr>
    <tr>
      <td >&nbsp;Password</td>
      <td ><input size=21 type=text name=regpassword></td>
    </tr>
    <tr>
      <td >&nbsp;Nickname</td>
      <td ><input size=21 type=text name=regnickname></td>
    </tr>
    <tr>
      <td >&nbsp;Email</td>
      <td ><input size=21 type=text name=regemail></td>
    </tr>
    <tr>
      <td >&nbsp;Access Level</td>
      <td ><select name=reglevel>
           <option value=5>5 (banned)</option>
           <option value=4>4 (commenter)</option>
           <option selected value=3>3 (journalist)</option>
           <option value=2>2 (editor)</option>
           <option value=1>1 (administrator)</option>
			 </select>
		</td>
    </tr>
    <tr>
      <td >&nbsp;</td>
      <td height="35"><input type=submit value="Add User">
          <input type=hidden name=action value=adduser>
    		<input type=hidden name=mod value=editusers>
      </td>
    </tr>
	</form>
  </table>
<!-- End Add User Table -->

      </td>
      <td width="330" height="1" valign="top" >

<!-- User Levels Table -->
  <table class="panel" cellspacing="3" cellpadding="0" width="100%">
    <tr>
      <td valign="top">&nbsp;Administrator : have full access and privilegies<br />
        &nbsp;Editor : can add news and edit others posts<br />
        &nbsp;Journalist : can only add and edit own news<br />
        &nbsp;Commenter : only post comments<br />
        &nbsp;Banned : can not do anything</td>
    </tr>
  </table>
<!-- End User Levels Table -->

      </td>
    </tr>
    <tr>
      <td width="330" valign="top" align=center height="70"><br />

      <!-- HELP -->
   <table height="25" cellspacing="0" cellpadding="0">
    <tr>
      <td width="25" align=middle><img border="0" src="skins/images/help_small.gif" width="25" height="25" /></td>
      <td >&nbsp;<a onClick="javascript:Help(\'users\')" href="#">Understanding user levels</a>&nbsp;</td>
    </tr>
  </table>
     <!-- END HELP -->
      </td>
    </tr>
  </table>
<!-- END add edit users table + info + help -->

    </tr>
    <tr>
	<td width=654 colspan="6">
    </tr>
    <tr>
	<td width=650 colspan="6">
    <img height=20 border=0 src="skins/images/blank.gif" width=1 /><br />
    <b>Edit Users</b>
    </tr>

<!-- Sort users v1.0 - Start addblock -->
    <tr>
    <td width=120 class=altern1>
	&nbsp;<u>Username</u> [<a href="index.php?mod=editusers&action=list&sortus=2&sortad=a">A</a>][<a href="index.php?mod=editusers&action=list&sortus=2&sortad=d">D</a>]
	<td width=220 class=altern1>
    <u>Registration Date</u> [<a href="index.php?mod=editusers&action=list&sortus=0&sortad=a">A</a>][<a href="index.php?mod=editusers&action=list&sortus=0&sortad=d">D</a>]
	<td width=2 class=altern1>
    &nbsp;
	<td width=90 class=altern1>
    <u>Posts</u> [<a href="index.php?mod=editusers&action=list&sortus=6&sortad=a">A</a>][<a href="index.php?mod=editusers&action=list&sortus=6&sortad=d">D</a>]
	<td width=132 class=altern1>
    <u>Access Level</u> [<a href="index.php?mod=editusers&action=list&sortus=1&sortad=a">A</a>][<a href="index.php?mod=editusers&action=list&sortus=1&sortad=d">D</a>]
	<td width=93 class=altern1>
    <u>Action</u>
    </tr>';

    $all_users = file("./data/users.db.php");
    if (isset($sortus)) {
        if (!function_exists('sortcmp')) {
            function sortcmp($a, $b)
            {
                global $all_users, $sortus;

                $users_a = explode('|', $all_users[$a]);
                $users_b = explode('|', $all_users[$b]);

                return strnatcasecmp($users_a[$sortus], $users_b[$sortus]);
            }
        }
        uksort($all_users, 'sortcmp');
        if ($sortad=="d") {
            $all_users = array_reverse($all_users);
        }
        unset($sortus);
    }
// Sort users v1.0 - End addblock
    $i = 1;
    foreach ($all_users as $null => $user_line) {
        $i++;
        $bg = "";
        if ($i%2 == 0) {
            $bg = "class=altern1";
        } else {
            $bg = "class=altern2";
        }
        if (!eregi("<\?", $user_line)) {
            $user_arr = explode("|", $user_line);

            if (isset($user_arr[9]) and $user_arr[9] != '') {
                $last_login = date('r', $user_arr[9]);
            } else {
                $last_login = 'never';
            }

            switch ($user_arr[1]) {
        case 1: $user_level = "administrator"; break;
        case 2: $user_level = "editor"; break;
        case 3: $user_level = "journalist"; break;
        case 4: $user_level = "commenter"; break;
        case 5: $user_level = "banned"; break;
        }
            echo"<tr $bg title='$user_arr[2]&#039;s last login was on: $last_login'>
		<td width=143>
	    &nbsp;$user_arr[2]
		<td width=197>";
            echo(date("F, d Y @ H:i a", $user_arr[0]));
            echo"<td width=2>
		<td width=83 >
	    &nbsp;&nbsp;$user_arr[6]
		<td width=122>
	    &nbsp;$user_level
		<td width=80 title=''>
	    <a  onClick=\"javascript:popupedit('$user_arr[0]'); return(false)\" href=#>[edit]</a>&nbsp;<a onClick=\"javascript:confirmdelete('$user_arr[0]'); return(false)\"  href=\"$PHP_SELF?mod=editusers&action=dodeleteuser&id=$user_arr[0]\">[delete]</a>
	    </tr>";
        }
    }

    echo"</table><br /><br /><br />";

    echo'<table border=0 cellpadding=0 cellspacing=0 width="645" >
    <form method=get action="'.$PHP_SELF.'">
    <td width=321 height="33">
    <b>Block IP</b>
<table border=0 cellpadding=0 cellspacing=0 width=379  class="panel" cellpadding="7" >
    <tr>
    <td width=79 height="25">
    &nbsp;IP Address :
	<td width=274 height="25">
    <input type=text name=add_ip>&nbsp;&nbsp; <input type=submit value="Block this IP">
    </tr>
    <input type=hidden name=action value=addip>
	<input type=hidden name=mod value=editusers>
    </form>
    </table>
    <tr>
	<td width=654 height="11">
        <img height=20 border=0 src="skins/images/blank.gif" width=1 />
    </tr><tr>
	<td width=654 height=14>
    <b>Blocked IP Addresses</b>
    </tr>
    <tr>
	<td width=654 height=1>
  <table width=641 height=100% cellspacing=0 cellpadding=0>
    <tr>
      <td width=15 class=altern1></td>
      <td width=260 class=altern1><u>IP</u></td>
      <td width=218 class=altern1><u>Times Blocked</u></td>
      <td width=140 class=altern1>&nbsp;<u>Unblock</u></td>
    </tr>';


    $all_ips = file("./data/ipban.db.php");
    $i = 0;
    foreach ($all_ips as $null => $ip_line) {
        if (!eregi("<\?", $ip_line)) {
            if ($i%2 != 0) {
                $bg = "class=altern1";
            } else {
                $bg = "class=altern2";
            }
            $i++;
            $ip_arr = explode("|", $ip_line);
            $ip_arr[0] = stripslashes(preg_replace(array("'\"'", "'\''"), array("&quot;", "&#039;"), $ip_arr[0]));
            echo"
        <tr $bg height=18>
        <td></td>
        <td>
        <a href=\"http://www.ripe.net/perl/whois?searchtext=$ip_arr[0]\" target=_blank title=\"Get more information about this ip\">$ip_arr[0]</a>
        </td>
   	    <td>$ip_arr[1]</td>
        <td>
        <a href=\"$PHP_SELF?mod=editusers&action=removeip&remove_ip=$ip_arr[0]\">[unblock]</a></td>
        </tr>
        ";
        }
    }

    if ($i == 0) {
        echo"<tr><td align=center colspan=5><br /> &nbsp;No blocked IP's</td></tr>";
    }

    echo'</table></table>';

    echofooter();
}
// ********************************************************************************
// Add User
// ********************************************************************************
elseif ($action == "adduser") {
    if (!$regusername) {
        msg("error", "Error !!!", "Username can not be blank", "javascript:history.go(-1)");
    }
    if (!$regpassword) {
        msg("error", "Error !!!", "Password can not be blank", "javascript:history.go(-1)");
    }

    $all_users = file("./data/users.db.php");
    foreach ($all_users as $null => $user_line) {
        $user_arr = explode("|", $user_line);
        if ($user_arr[2] == $regusername) {
            msg("error", "Error !!!", "Sory but user with this username already exist", "javascript:history.go(-1)");
        }
    }

    $add_time = time()+($config_date_adjust*60);
    $regpassword = md5($regpassword);

    $old_users_file = file("./data/users.db.php");
    $new_users_file = fopen("./data/users.db.php", "a");

    fwrite($new_users_file, "$add_time|$reglevel|$regusername|$regpassword|$regnickname|$regemail|0|0|||||\n");

    fclose($new_users_file);

    switch ($reglevel) {
    case "1": $level = "administrator"; break;
    case "2": $level = "editor"; break;
    case "3": $level = "journalist"; break;
    case "4": $level = "commenter"; break;
    case "5": $level = "banned"; break;
    }
    msg("info", "User Added", "The user <b>$regusername</b> was successfully added as <b>$level</b>", "$PHP_SELF?mod=editusers&action=list");
}
// ********************************************************************************
// Edit User Details
// ********************************************************************************
elseif ($action == "edituser") {
    $users_file = file("./data/users.db.php");
    foreach ($users_file as $null => $user_line) {
        $user_arr = explode("|", $user_line);
        if ($id == $user_arr[0]) {
            break;
        }
    }

    if (isset($user_arr[9]) and $user_arr[9] != '') {
        $last_login = date('r', $user_arr[9]);
    } else {
        $last_login = 'never';
    }

    echo"<html><head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\">
    <title>Edit Users</title>
    <style type=\"text/css\">
    <!--
        select, option, textarea, input {
        BORDER-RIGHT: #808080 1px solid;
        BORDER-TOP: #808080 1px solid;
        BORDER-BOTTOM: #808080 1px solid;
        BORDER-LEFT: #808080 1px solid;
        COLOR: #000000;
        FONT-SIZE: 11px;
        FONT-FAMILY: Verdana; BACKGROUND-COLOR: #ffffff }
            TD {text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt;}
            BODY {text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 3pt;}
            .header { font-size : 16px; font-weight: bold; color: #808080; font-family: verdana; text-decoration: none; }
    -->
    </style>
    </head>
    <body>
    <form action=\"$PHP_SELF\" method=post><table width=\"828\" cellspacing=\"0\" cellpadding=\"0\" height=\"13\">
    <td width=\"826\" height=\"21\" colspan=\"2\"><div class=header>$user_arr[2] <font size=\"2\">($user_arr[4])</font></div>

    <tr>
    <td width=\"126\" height=\"20\" class=altern1>written news
    <td  height=\"20\" class=altern1 width=\"698\">
    $user_arr[6]
    </tr>

    <tr>
    <td width=\"126\" height=\"20\" class=altern1>last login date
    <td  height=\"20\" class=altern1 width=\"698\">
    $last_login
    </tr>

    <tr>
    <td width=\"126\" height=\"20\">
    registration date
    <td  height=\"20\" width=\"698\">";
    echo date("r", $user_arr[0]);
    echo"
    </tr>

    <tr>
    <td width=\"126\" height=\"20\" class=altern1>
    Email
    <td  height=\"20\" class=altern1 width=\"698\">
    $user_arr[5]
    </tr>

    <tr>
 	<td width=\"126\" height=\"20\">
    New Password
    <td  height=\"20\" width=\"698\">
    <input size=\"20\" name=\"editpassword\" >
    </tr>

    <tr>
    <td width=\"126\" height=\"20\" class=altern1>
    Access Level
    <td  height=\"20\" class=altern1 width=\"698\">
    <select name=editlevel>";

    if ($user_arr[1] == 5) {
        echo" <option value=5 selected>5 (banned)</option>";
    } else {
        echo" <option value=5>5 (banned)</option>";
    }
    if ($user_arr[1] == 4) {
        echo" <option value=4 selected>4 (commenter)</option>";
    } else {
        echo" <option value=4>4 (commenter)</option>";
    }
    if ($user_arr[1] == 3) {
        echo" <option value=3 selected>3 (journalist)</option>";
    } else {
        echo" <option value=3>3 (journalist)</option>";
    }
    if ($user_arr[1] == 2) {
        echo" <option value=2 selected>2 (editor)</option>";
    } else {
        echo" <option value=2>2 (editor)</option>";
    }
    if ($user_arr[1] == 1) {
        echo" <option value=1 selected>1 (administrator)</option>";
    } else {
        echo" <option value=1>1 (administrator)</option>";
    }

    echo"</select>
    </tr>
    <tr>
    <td width=\"826\" height=\"7\" colspan=\"2\">
    <br />
    <input type=submit value=\"Save Changes\">   <input type=button value=\"Cancel\" onClick=\"window.close();\">
    <input type=hidden name=id value=$id>
    <input type=hidden name=mod value=editusers>
    <input type=hidden name=action value=doedituser>
    </tr>
    </table></form>
    </body>
    </html>";
}
// ********************************************************************************
// Do Edit User
// ********************************************************************************
elseif ($action == "doedituser") {
    if (!$id) {
        die("This is not a valid user.");
    }

    $old_db = file("./data/users.db.php");
    $new_db = fopen("./data/users.db.php", "w");
    foreach ($old_db as $null => $old_db_line) {
        $old_db_arr = explode("|", $old_db_line);
        if ($id != $old_db_arr[0]) {
            fwrite($new_db, "$old_db_line");
        } else {
            if ($editpassword != "") {
                $old_db_arr[3] = md5($editpassword);
                if ($old_db_arr[2] == $username) {
                    setcookie("md5_password", $old_db_arr[3]);
                }
            }
            fwrite($new_db, "$old_db_arr[0]|$editlevel|$old_db_arr[2]|$old_db_arr[3]|$old_db_arr[4]|$old_db_arr[5]|$old_db_arr[6]|$old_db_arr[7]|$old_db_arr[8]|$old_db_arr[9]|$old_db_arr[10]||\n");
        }
    }
    fclose($new_db);
    $result = "Changes Saved";

    echo"<html>
    <head>
    <title>Edit Users</title>
    </head>
    <body bgcolor=#FFFFFF>
    <table border=0 cellpadding=0 cellspacing=0 width=100% height=100% >
    <tr><td align=middle width=154>
    <p align=right><img border=0 src=\"skins/images/info.gif\" width=60 height=57 />
    </td><td align=middle width=558>
    <p align=left>$result
    </td></tr>
    </table>
    </body>
    </html>";
}
// ********************************************************************************
// Delete User
// ********************************************************************************
elseif ($action == "dodeleteuser") {
    if (!$id) {
        die("This is not a valid user.");
    }

    $old_users_file = file("./data/users.db.php");
    $new_users_file = fopen("./data/users.db.php", "w");
    foreach ($old_users_file as $null => $old_user_line) {
        $old_user_line_arr = explode("|", $old_user_line);
        if ($id != $old_user_line_arr[0]) {
            fwrite($new_users_file, $old_user_line);
        } else {
            $deleted = true;
            $user = $old_user_line_arr[2];
        }
    }
    $remdir = "";
    if ($deleted && $config_user_image_upload == "yes") {
        $user_img_dir = $display_path_image_upload."/".strtolower(str_replace(" ", "", $user))."/";
        $remdir = "The directory <b>$user_img_dir</b> was also deleted.";
        @rmdir($user_img_dir) or $remdir = "The directory <b>$user_img_dir</b> was NOT deleted or did not exist.";
    }
    fclose($new_users_file);

    msg("info", "User Deleted", "The user $user was successfully deleted. $remdir", "$PHP_SELF?mod=editusers&action=list");
}
// ********************************************************************************
// Add IP
// ********************************************************************************
elseif ($action == "addip") {
    if (!$add_ip) {
        msg("error", "Error !!!", "The IP can not be blank", "$PHP_SELF?mod=ipban");
    }

    $all_ip = file("./data/ipban.db.php");
    $exist = false;
    foreach ($all_ip as $null => $ip_line) {
        if (eregi("<\?", $ip_line)) {
            continue;
        }
        $ip_arr = explode("|", $ip_line);
        if ($ip_arr[0] == $add_ip) {
            $exist = true;
        }
    }
    if (!$exist) {
        $new_ips = fopen("./data/ipban.db.php", "a");
        $add_ip = stripslashes(preg_replace(array("'\|'",), array("I",), $add_ip));
        fwrite($new_ips, "$add_ip|0||\n");
        fclose($new_ips);
    }
    msg("info", "IP Blocked", "The ip <b>$add_ip</b> has been blocked", "$PHP_SELF?mod=editusers&action=list");
}
// ********************************************************************************
// Remove IP
// ********************************************************************************
elseif ($action == "removeip") {
    if (!$remove_ip) {
        msg("error", "Error !!!", "The IP can not be blank", "$PHP_SELF?mod=ipban");
    }

    $old_ips = file("./data/ipban.db.php");
    $new_ips = fopen("./data/ipban.db.php", "w");
    fwrite($new_ips, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");

    foreach ($old_ips as $null => $old_ip_line) {
        if (eregi("<\?", $old_ip_line)) {
            continue;
        }
        $ip_arr = explode("|", $old_ip_line);
        if ($ip_arr[0] != stripslashes($remove_ip)) {
            fwrite($new_ips, $old_ip_line);
        }
    }
    fclose($new_ips);
    msg("info", "IP Unblocked", "The ip <b>$remove_ip</b> was successfully unblocked", "$PHP_SELF?mod=editusers&action=list");
}
