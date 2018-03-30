<?php
/***************************************************************************
 CuteNews @CutePHP.com
 Copyright (C) 2004 Georgi Avramov  (flexer@cutephp.com)

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 More Info About The Licence At http://www.gnu.org/copyleft/gpl.html
****************************************************************************/

error_reporting(E_ALL ^ E_NOTICE);

$cutepath = str_replace("\\", "/", dirname(__FILE__)."\\");

require_once($cutepath."/inc/functions.inc.php");
require_once($cutepath."/data/config.php");

// Check if CuteNews is not installed
$all_users_db = file("./data/users.db.php");
$check_users = $all_users_db;
$check_users[1] = trim($check_users[1]);
$check_users[2] = trim($check_users[2]);
if ((!$check_users[2] or $check_users[2] == "") and (!$check_users[1] or $check_users[1] == "")) {
    if (!file_exists("./inc/install.mdu.php")) {
        die('<h2>Error!</h2>CuteNews detected that you do not have users in your users.db.php file and wants to run the install module.<br />
    However, the install module (<b>./inc/install.mdu.php</b>) can not be located, please reupload this file and make sure you set the proper permissions so the installation can continue.');
    }
    require("./inc/install.mdu.php");
    die();
}

// Clean Up $config_http_script_dir
if (substr($config_http_script_dir, -1) == "/") {
    $config_http_script_dir = substr($config_http_script_dir, 0, -1);
}
$display_http_script_dir = $config_http_script_dir;

require_once($cutepath."/loginout.php");

// Start DIFDU v1.0
$display_path_image_upload = $config_path_image_upload;
if ($config_user_image_upload == "yes") {
    $config_path_image_upload = $config_path_image_upload."/".strtolower(str_replace(" ", "", $username));
}
if (!is_dir($config_path_image_upload) && $member_db[1] < 4) {
    @mkdir($config_path_image_upload, 0777);
    @chmod($config_path_image_upload, 0777);
}
// End DIFDU v1.0

###########################

if (isset($config_skin) and $config_skin != "" and file_exists("./skins/${config_skin}.skin.php")) {
    require_once("$cutepath/skins/${config_skin}.skin.php");
} else {
    $using_safe_skin = true;
    require_once("$cutepath/skins/default.skin.php");
}

if ($is_logged_in) {

    // ********************************************************************************
    // Include System Module
    // ********************************************************************************
    if ($_SERVER["QUERY_STRING"] == "debug") {
        debug();
    }
    //name of mod   //access
    $system_modules = array(
        // user stuff
        "about"                    => "user",
        "addnews"            => "user",
        "editcomments"    => "user",
        "editnews"            => "user",
        "help"                    => "user",
        "images"                => "user",
        "main"                    => "user",
        "massactions"    => "user",
        "options"            => "user",
        "preview"            => "user",
        // admin stuff
        "addpoll"                => "admin",
        "categories"        => "admin",
        "debug"                => "admin",
        "editpolls"            => "admin",
        "editusers"        => "admin",
        "ipban"                => "admin",
        "purgeusers"        => "admin",
        "tools"                => "admin",
// XFields v2.1 - addblock
'xfields' => 'admin',
// XFields v2.1 - End addblock
    );

    if ($mod == "") {
        require("./inc/main.mdu.php");
    } elseif ($system_modules[$mod]) {
        if ($system_modules[$mod] == "user") {
            require("./inc/". $mod . ".mdu.php");
        } elseif ($system_modules[$mod] == "admin" and $member_db[1] == 1) {
            require("./inc/". $mod . ".mdu.php");
        } elseif ($system_modules[$mod] == "admin" and $member_db[1] != 1) {
            msg("error", "Access denied", "Only admin can access this module");
            exit;
        } else {
            die("Module access must be set to <b>user</b> or <b>admin</b>");
        }
    } else {
        die("$mod is NOT a valid module");
    }
} elseif (!$is_logged_in) {
    // ********************************************************************************
// Recover Pass
// ********************************************************************************
if ($action == "recoverpass") {
    echoheader("user", "Lost Password");
    echo"

<table border=0 cellpadding=0 cellspacing=0 width=\"654\" height=\"59\" >
	<form method=post action=\"$PHP_SELF\">
	<tr>
		<td width=\"18\" height=\"11\"></td>
		<td width=\"71\" height=\"11\" align=\"left\">
			Username
		</td>
		<td width=\"203\" height=\"11\" align=\"left\">
			<input tabindex=1 type=text name=user seize=20>
		</td>
		<td width=\"350\" height=\"26\" align=\"left\" rowspan=\"2\" valign=\"middle\">
			If the username and email match in our users database,<br /> the new password will be automatically mailed to you.
		</td>
	</tr>
	<tr>
		<td width=\"18\" valign=\"top\" height=\"15\">
		</td>
		<td width=\"71\" height=\"15\" align=\"left\">
			Email
		</td>
		<td width=\"203\" height=\"15\" align=\"left\">
			<input tabindex=2 type=text name=email size=\"20\">
		</td>
	</tr>
	<tr>
		<td width=\"18\" valign=\"top\" height=\"15\">
		</td>
		<td width=\"628\" height=\"15\" align=\"left\" colspan=\"3\">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td width=\"18\" valign=\"top\" height=\"15\">
		</td>
		<td         width=\"628\" height=\"15\" align=\"left\" colspan=\"3\">
			<input tabindex=3 type=submit value=\"Send me the password\">
		</td>
	</tr>
	<input type=hidden name=action value=dorecoverpass>
	</form>
</table>

";
    echofooter();
}
// ********************************************************************************
// DO Recover Pass
// ********************************************************************************
elseif ($action == "dorecoverpass") {
    if ($user == "" or $email == "") {
        msg("error", "Error !!!", "All fields are required");
    }
    $found = false;
    $all_users = file("./data/users.db.php");
    foreach ($all_users as $null => $user_line) {
        $user_arr = explode("|", $user_line);
        if (stristr("|".$user_arr[2]."|", "|".$user."|") && stristr("|".$user_arr[5]."|", "|".$email."|")) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        msg("error", "Error !!!", "The username/email you enter did not match in our users database");
    } else {
        $new_pass = makeRandomPassword();
        $new_db = fopen("./data/users.db.php", "w");
        foreach ($all_users as $null => $all_users_line) {
            $all_users_arr = explode("|", $all_users_line);
            if ($user != $all_users_arr[2]) {
                fwrite($new_db, "$all_users_line");
            } else {
                fwrite($new_db, "$all_users_arr[0]|$all_users_arr[1]|$all_users_arr[2]|".md5($new_pass)."|$all_users_arr[4]|$all_users_arr[5]|$all_users_arr[6]|$all_users_arr[7]|$all_users_arr[8]|$all_users_arr[9]|$all_users_arr[10]||\n");
            }
        }
        fclose($new_db);

        cute_mail("$email", "Your New Password", "Hello $user,\n Your new password for the ".$_SERVER['SERVER_NAME']." news system is $new_pass, please change this password after you login.");

        msg("info", "Password Sent", "The new password for <b>$user</b> was sent to <b>$email</b>");
    }
}

// ********************************************************************************
// Register User
// ********************************************************************************
elseif ($action == "register") {
    echoheader("user", "User Registration");
    if ($config_register_allow == "no") {
        echo "User Registration is not enabled.";
    } else {
        echo "
   <table leftmargin=0 marginheight=0 marginwidth=0 topmargin=0 border=0 height=100% cellspacing=0>
    <form  name=login action=\"$PHP_SELF\" method=post>
    <tr>
      <td width=80><br />Username: </td>
      <td><br /><input tabindex=1 type=text name=reguser style=\"width:134\" size=20></td>
    </tr>
";
        if ($config_register_mailpass == "no") {
            echo "
    <tr>
      <td width=80>Password: </td>
      <td><input tabindex=2 type=text name=regpass style=\"width:134\" size=20></td>
    </tr>
    <tr>
      <td width=80>Confirm Password: </td>
      <td><input tabindex=3 type=text name=conpass style=\"width:134\" size=20></td>
    </tr>
";
        }
        echo "
    <tr>
      <td width=80>Email: </td>
      <td><input tabindex=4 type=text name=regmail style=\"width:134\" size=20></td>
    </tr>
    <tr>
      <td width=80>Confirm Email: </td>
      <td><input tabindex=5 type=text name=conmail style=\"width:134\" size=20></td>
    </tr>
    <tr>
      <td width=80>Hide Email: </td>
      <td><input tabindex=6 type=checkbox class=checkbox name=reghide></td>
    </tr>
     <tr>
      <td></td>
      <td ><input tabindex=7 accesskey=s type=submit class=altern1 value=Register></td>
     </tr>
    <input type=hidden name=action value=doregister>
    </form>
   </table>
";
    }
    echofooter();
}
// ********************************************************************************
// DO Register User
// ********************************************************************************
elseif ($action == "doregister") {
    if ($config_register_allow == "no") {
        msg("error", "Error !!!", "User Registration is not enabled.");
    } else {
        $filter = explode(",", str_replace(" ", "", str_replace(",,", ",", $config_register_filter)));
        if ($config_register_level=="4") {
            $level_mail="Commenter";
        }
        if ($config_register_level=="3") {
            $level_mail="Journalist";
        }
        if ($config_register_level=="2") {
            $level_mail="Editor";
        }
        if ($config_register_level=="1") {
            $level_mail="Administrator";
        }
        if ($config_register_mailpass == "yes") {
            $regpass = makeRandomPassword();
            $conpass = $regpass;
        }
        $reguser = trim($reguser, " \t\n\r\0");
        $regpass = trim($regpass, " \t\n\r\0");
        $regmail = trim($regmail, " \t\n\r\0");
        $conpass = trim($conpass, " \t\n\r\0");
        $conmail = trim($conmail, " \t\n\r\0");
        if (!$reguser) {
            msg("error", "Error !!!", "Username can not be blank");
        }
        if (!$regpass || !$conpass || $regpass != $conpass) {
            msg("error", "Error !!!", "Password can not be blank, both fields must match");
        }
        if (!$regmail || !$conmail || $regmail != $conmail) {
            msg("error", "Error !!!", "Email can not be blank, both fields must match");
        }
        if (!preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/i", $regmail)) {
            msg("error", "Error !!!", "Invalid Email.");
        }
        if (preg_match("/[^\.A-z0-9_\-$]/i", $reguser, $matches)) {
            $match = "";
            if ($matches) {
                $match = "You entered '$matches[0]'.";
            }
            msg("error", "Error !!!", "Invalid Username. Must contain only A-Z, a-z, 0-9, underscores, dashes, or periods. $match");
        }
        if (preg_match("/[^\.A-z0-9_\-$]/i", $regpass, $matches)) {
            $match = "";
            if ($matches) {
                $match = "You entered '$matches[0]'.";
            }
            msg("error", "Error !!!", "Invalid Password. Must contain only A-Z, a-z, 0-9, underscores, dashes, or periods. $match");
        }
        foreach ($filter as $null => $block) {
            if (stristr($reguser, $block)) {
                msg("error", "Error !!!", "Invalid Username. <b>\"$block\"</b> not allowed.");
            }
        }

        $add_time = time()+($config_date_adjust*60);
        if ($reghide=="on") {
            $hidemail="1";
        } else {
            $hidemail="0";
        }

        $all_users = file("./data/users.db.php");
        foreach ($all_users as $null => $user_line) {
            $user_arr = explode("|", $user_line);
            if (stristr("|".$user_arr[2]."|", "|".$reguser."|")) {
                msg("error", "Error", "This username is already taken");
            }
            if ($config_register_multimail=="no" && stristr("|".$user_arr[5]."|", "|".$regmail."|")) {
                msg("error", "Error", "This email address is registered to another user!");
            }
        }

        $users_file = fopen("./data/users.db.php", "a");
        fwrite($users_file, "$add_time|$config_register_level|$reguser|".md5($regpass)."||$regmail|0|$hidemail|||||\n");
        fclose($users_file);

        if ($config_register_mailpass == "yes") {
            cute_mail($regmail, "Registration at $SERVER_NAME", "$reguser, you have registered successfully. \n ---------- \n Your User Level is: $level_mail \n Your Password is: $regpass \n You can change this once you login. \n ---------- \n You can login here: $config_http_script_dir/ \n Thank You for Registering!");
        }
        if ($config_register_mailadmin == "yes") {
            cute_mail($config_mail_admin_address, "New User: $reguser", "$reguser has registered on your CuteNews system. \n The address used to register was: $regmail \n To delete this user go to the following address: $config_http_script_dir/index.php?mod=editusers&action=dodeleteuser&id=$add_time");
        }

        if (!isset($config_mail_admin_address) || $config_mail_admin_address == "") {
            $problem_contact = "an administrator";
        } else {
            $problem_contact = "<a href=\"mailto:".$config_register_mailadmin_address."\" target=\"_blank\">".$config_register_mailadmin_address."</a>";
        }
        if ($config_register_mailpass == "yes") {
            msg("user", "$level_mail Added", "You have successfully registered as <b>\"$reguser\"</b>.<br />Your password has been emailed to <b>\"$regmail\"</b>.<br />If this information is wrong or you do not recieve your password please contact $problem_contact.");
        } else {
            msg("user", "$level_mail Added", "You have successfully registered as <b>\"$reguser\"</b>.<br />Your password is <b>\"$regpass\"</b>.<br />Your email address is <b>\"$regmail\"</b>.<br />If this information is wrong please contact $problem_contact.");
        }
    }
}

// ********************************************************************************
// User Login
// ********************************************************************************
else {
    logout($action);
    echoheader("user", "Please Login"); ?>
<body onload="if(document.login)document.login.enteredpw.focus();">
<script type="text/javascript" src="md5.js"></script>
<script>
function flogin(frm) {
	if(!md5_vm_test())
		alert("Your javascript doesn't work");
	frm.password.value=hex_md5(hex_md5(frm.enteredpw.value)+frm.time.value);
	frm.enteredpw.value='';}
</script>

	<form name=login action="<?php echo $PHP_SELF; ?>" onSubmit="flogin(this)" method=post>
	<table border=0 cellspacing=0 cellpadding=1>
	<tr>
		<td width=80>Username: </td>
		<td><input tabindex=1 type=text name=username value='<?php echo $_COOKIE["lastusername"]; ?>' style='width:134'></td>
		<td><input accesskey='r' type=button class=altern1 style='width:134;' value='Register' onclick="window.location='<?php echo $config_http_script_dir; ?>/index.php?action=register'"></td>
	</tr>
	<tr>
		<td>Password: </td>
		<td><input tabindex=2 type=password name=enteredpw style='width:134'></td>
		<td><input accesskey='p' type=button class=altern1 style='width:134;' value='Recover Password' onclick="window.location='<?php echo $config_http_script_dir; ?>/index.php?action=recoverpass'"></td>
	</tr>
	<tr>
		<td></td>
		<td align=left valign=bottom>
			<input tabindex=3 type=checkbox class=checkbox name=rememberpw value=true<?php if ($_COOKIE["rememberpw"] || !$config_use_sessions) {
        echo " checked=checked";
    } ?><?php if (!$config_use_sessions) {
        echo " disabled=disabled";
    } ?> onclick="document.login.enteredpw.focus();"> Remember Me
			<a href="#" onclick="alert('Check \'Remember Me\' to be logged in automatically for a whole year.\nDon\'t do this in public places, or if you do be sure to logout.');">(?)</a>
		</td>
		<td><input tabindex=4 accesskey='s' type=submit class=altern1 style='width:134;' value='      Login...      '></td></tr>
	</tr>
	<tr>
		<td colspan="3" align=right colspan=2><?php echo $message; ?></td></tr></table>
	<input type=hidden name=action value=dologin>
	<input type=hidden name=password>
	<input type=hidden name=time value="<?php echo time(); ?>"></form>
<?php
    echofooter();
}
}

echo "<!-- execution time: ".substr((array_sum(explode(' ', microtime()))-$timer), 0, 7)." -->";
?>
</body>
