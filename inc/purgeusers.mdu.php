<?php

if ($member_db[1] != 1) {
    msg("error", "Access Denied", "You don't have permission to purge users");
}
// ********************************************************************************
// Pick which users to list
// ********************************************************************************
if ($action == "pick") {
    echoheader("users", "Purge Users");
    echo "

<form method=POST name=purgeusers action=\"$PHP_SELF?mod=purgeusers&action=listusers\">
Display users who have not logged in for:<br /><input class=checkbox onclick=\"document.purgeusers.show_never_logins.disabled=false;\" type=radio name=show_method value=inactive checked>
<input type=text name=years value=0 size=4 tabindex=1> years,
<input type=text name=months value=0 size=4 tabindex=1> months,
<input type=text name=weeks value=0 size=4 tabindex=1> weeks,
<input type=text name=days value=0 size=4 tabindex=1> days.<br />
<input class=checkbox onclick=\"document.purgeusers.show_never_logins.checked=true; document.purgeusers.show_never_logins.disabled=true;\" type=radio name=show_method value=noposts> Show users who have zero posts.<br />
&nbsp;&nbsp;&nbsp;&nbsp;
<input type=checkbox class=checkbox value=yes name=show_never_logins> Show users who have never logged in.<br />
<input type=submit value=\"Display Users\" accesskey=\"d\"><br />
</form>

";
    echofooter();
}
// ********************************************************************************
// List picked users
// ********************************************************************************
elseif ($action == "listusers") {
    echoheader("users", "Purge Users");

    echo '<form method=POST name="purgeusers" action="'.$PHP_SELF.'?mod=purgeusers&action=mass_delete">';
    echo "<script language='JavaScript' type='text/javascript'>
<!--
function ckeck_uncheck_all() {
        var frm = document.purgeusers;
        for (var i=0;i<frm.elements.length;i++) {
                var elmnt = frm.elements[i];
                if (elmnt.type=='checkbox') {
                        if(frm.master_box.checked == true){ elmnt.checked=false; }
            else{ elmnt.checked=true; }
                }
        }
        if(frm.master_box.checked == true){ frm.master_box.checked = false; }
    else{ frm.master_box.checked = true; }
}

-->
</script>";
    echo'
<table border=0 cellpadding=0 cellspacing=0 width=654>
	<td width=650 colspan="6">
    <b>Inactive Users</b>
    </tr>

<!-- // Sort users v1.0 - Start addblock -->
    <tr>
    <td class=altern1 style="white-space:nowrap">
	&nbsp;<u>Username</u>[<a href="index.php?mod=purgeusers&action=listusers&sortus=2&sortad=a">A</a>][<a href="index.php?mod=purgeusers&action=listusers&sortus=2&sortad=d">D</a>]
	<td class=altern1 style="white-space:nowrap">
    <u>Registration Date</u>[<a href="index.php?mod=purgeusers&action=listusers&sortus=0&sortad=a">A</a>][<a href="index.php?mod=purgeusers&action=listusers&sortus=0&sortad=d">D</a>]
	<td class=altern1>
    &nbsp;
	<td class=altern1 style="white-space:nowrap">
    <u>Last Login</u>[<a href="index.php?mod=purgeusers&action=listusers&sortus=9&sortad=a">A</a>][<a href="index.php?mod=purgeusers&action=listusers&sortus=9&sortad=d">D</a>]
	<td class=altern1 style="white-space:nowrap">
    <u>Access Level</u>[<a href="index.php?mod=purgeusers&action=listusers&sortus=1&sortad=a">A</a>][<a href="index.php?mod=purgeusers&action=listusers&sortus=1&sortad=d">D</a>]
	<td class=altern1 style="white-space:nowrap">
    <u>Posts</u>[<a href="index.php?mod=purgeusers&action=listusers&sortus=6&sortad=a">A</a>][<a href="index.php?mod=purgeusers&action=6istusers&sortus=1&sortad=d">D</a>]
	<td class=altern1 align="center">
    <input type=checkbox class=checkbox name=master_box title="Check All" onclick="javascript:ckeck_uncheck_all()">
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

            $inactive_time = time()-$user_arr[9];
            $old_time = 31536000*$years + 2628000*$months + 604800*$weeks + 86400*$days;
            if ($show_method == "inactive" && $inactive_time <= $old_time && $last_login != "never") {
                continue;
            } elseif ($show_method == "noposts" && $user_arr[6] > 0) {
                continue;
            }
            if ($show_method == "inactive" && $show_never_logins != "yes" && $last_login == "never") {
                continue;
            }

            switch ($user_arr[1]) {
        case 1: $user_level = "administrator"; break;
        case 2: $user_level = "editor"; break;
        case 3: $user_level = "journalist"; break;
        case 4: $user_level = "commenter"; break;
        case 5: $user_level = "banned"; break;
        }
            echo"<tr $bg title='$user_arr[2]&#039;s last login was on: $last_login'>
		<td>
	    &nbsp;$user_arr[2]
		<td>";
            echo(date("F, d Y @ H:i", $user_arr[0]));
            echo"<td>
		<td>
";
            if (isset($user_arr[9]) and $user_arr[9] != '') {
                $last_login = date("F, d Y @ H:i", $user_arr[9]);
            } else {
                $last_login = 'never';
            }
            echo $last_login;
            echo"
		<td>
	    &nbsp;$user_level
	  <td>
	    &nbsp;$user_arr[6]
		<td>
	    <input name=\"selected_users[]\" value=\"{$user_arr[0]}\" type='checkbox' class=checkbox>
	    </tr>";
        }
    }

    echo"<tr><td colspan=6 align=center><a href=\"$PHP_SELF?mod=purgeusers&action=pick\">[ Go Back ]</a> <img src=\"skins/images/blank.gif\" height=100% width=300 /> <input type=submit value=\"Delete Users\" accesskey=\"d\"><br /></form></table>";

    echofooter();
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Mass Delete
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if ($action == "mass_delete") {
    if (!$selected_users) {
        msg("error", "Error", "You have not specified any users", "$PHP_SELF?mod=purgeusers&action=listusers");
    }

    echoheader("options", "Delete Users");
    echo "<form method=post action=\"$PHP_SELF\"><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td >
Are you sure you want to delete all selected users (<b>".count($selected_users)."</b>) ?<br /><br />
<input type=button value=\" No \" onclick=\"javascript:document.location='$PHP_SELF?mod=purgeusers&action=listusers'\"> &nbsp; <input type=submit value=\"   Yes   \">
<input type=hidden name=action value=\"do_mass_delete\">
<input type=hidden name=mod value=\"purgeusers\">";
    foreach ($selected_users as $null => $userid) {
        echo "<input type=hidden name=\"selected_users[]\" value=\"$userid\">\n";
    }
    echo "</td></tr></table></form>";

    echofooter();
    exit;
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Do Mass Delete
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "do_mass_delete") {
    if (!$selected_users) {
        msg("error", "Error", "You have not specified any users to be deleted", "$PHP_SELF?mod=purgeusers&action=listusers");
    }
    $user_file = "data/users.db.php";

    $deleted_users = 0;

    // Delete News
    $old_db = file("$user_file");
    $new_db = fopen("$user_file", w);
    foreach ($old_db as $null => $old_db_line) {
        $old_db_arr = explode("|", $old_db_line);
        if (@!in_array($old_db_arr[0], $selected_users)) {
            fwrite($new_db, "$old_db_line");
        } else {
            $have_perm = 0;
            if (($member_db[1] == 1) or ($member_db[1] == 2)) {
                $have_perm = 1;
            } elseif ($member_db[1] == 3 and $old_db_arr[1] == $member_db[2]) {
                $have_perm = 1;
            }
            if (!$have_perm) {
                fwrite($new_db, "$old_db_line");
            } else {
                $user = $old_db_arr[2];
                $remdir = array();
                if ($config_user_image_upload == "yes") {
                    $user_img_dir = $display_path_image_upload."/".strtolower(str_replace(" ", "", $user))."/";
                    $remdir[$deleted_users] = "The directory <b>$user_img_dir</b> was also deleted.";
                    @rmdir($user_img_dir) or $remdir[$deleted_users] = "The directory <b>$user_img_dir</b> was NOT deleted or did not exist.";
                }
                $deleted_users ++;
            }
        }
    }
    fclose($new_db);

    $out_remdir = "";
    foreach ($remdir as $null => $removed) {
        $out_remdir .= $removed."<br>";
    }

    if (count($selected_users) == $deleted_users) {
        msg("info", "Deleted Users", "All users that you selected (<b>$deleted_users</b>) were deleted.<br>$out_remdir", "$PHP_SELF?mod=purgeusers&action=pick");
    } else {
        msg("error", "Deleted Users (some errors occured !!!)", "$deleted_users of ".count($selected_users)." users that you selected were deleted.<br>$out_remdir", "$PHP_SELF?mod=purgeusers&action=pick");
    }
}
