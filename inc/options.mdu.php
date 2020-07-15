<?php


// ********************************************************************************
// Options Menu
// ********************************************************************************
if ($action == "options" or $action == '') {
    echoheader("options", "Options");

    //----------------------------------
    // Predefine Options
    //----------------------------------

    // access means the lower level of user allowed; 1:admin, 2:editor+admin, 3:editor+admin+journalist, 4:all
    $options = array(
                    array(
                           'name'                => "Personal Options",
                           'url'                => "$PHP_SELF?mod=options&action=personal",
                           'access'        => "4",
                    ),

                    array(
                           'name'                => "System Configurations",
                           'url'                => "$PHP_SELF?mod=options&action=syscon&rand=".time(),
                           'access'        => "1",
                    ),

                    array(
                           'name'                => "Personal Profile",
                           'url'                => "$PHP_SELF?mod=options&action=profile",
                           'access'        => "3",
                    ),


                    array(
                           'name'                => "Manage Users/IPs",
                           'url'                => "$PHP_SELF?mod=editusers&action=list",
                           'access'        => "1",
                    ),

                    array(
                           'name'                => "Manage Uploaded Images",
                           'url'                => "$PHP_SELF?mod=images",
                           'access'        => "3",
                    ),

                    array(
                           'name'                => "Purge Users",
                           'url'                => "$PHP_SELF?mod=purgeusers&action=pick",
                           'access'        => "1",
                    ),

                    array(
                           'name'                => "Edit Templates",
                           'url'                => "$PHP_SELF?mod=options&action=templates",
                           'access'        => "1",
                    ),

                    array(
                           'name'                => "Edit Profile Template",
                           'url'                => "$PHP_SELF?mod=options&action=protemp",
                           'access'        => "1",
                    ),

                    array(
                           'name'                => "Edit Categories",
                           'url'                => "$PHP_SELF?mod=categories",
                           'access'        => "1",
                    ),

                    array(
                           'name'                => "Configure XFields",
                           'url'                => "$PHP_SELF?mod=xfields&xfieldsaction=configure",
                           'access'        => "1",
                    ),

                    array(
                           'name'                => "Archives Manager",
                           'url'                => "$PHP_SELF?mod=tools&action=archive",
                           'access'        => "1",
                    ),

                    array(
                           'name'                => "Backup Tool",
                           'url'                => "$PHP_SELF?mod=tools&action=backup",
                          'access'        => "1",
                    ),

                    );


    //------------------------------------------------
    // Cut the options for wich we don't have access
    //------------------------------------------------
    $count_options = count($options);
    for ($i=0; $i<$count_options; $i++) {
        if ($member_db[1] > $options[$i]['access']) {
            unset($options[$i]);
        }
    }
    echo'<table border="0" width="100%"><tr>';
    $i = 0;
    foreach ($options as $null => $option) {
        if ($i%2 == 0) {
            echo"</tr>\n<tr>\n<td width='47%'>&nbsp;&nbsp;&nbsp;<a href='".$option['url']."'><b>".$option['name']."</b></a></td>\n";
        } else {
            echo"\n<td width='53%'><a href='".$option['url']."'><b>".$option['name']."</b></a></td>\n";
        }
        $i++;
    }

    echo'</tr></table>';
    echofooter();
}
// ********************************************************************************
// Show Personal Options
// ********************************************************************************
elseif ($action == "personal") {
    echoheader("user", "Personal Options");

    $registrationdate = date("D, d F Y", $member_db[0]);        //registration date
    if ($member_db[7] == 1) {
        $ifchecked = "Checked";
    }                //if user wants to hide his e-mail

    foreach ($member_db as $key=>$value) {
        $member_db[$key]  = stripslashes(preg_replace(array("'\"'", "'\''"), array("&quot;", "&#039;"), $member_db[$key]));
    }

    $bg = "class=altern1";
    echo"
     <table border=0 height=1 width=617 cellspacing=\"0\" cellpadding=\"0\">
     <form method=POST action=\"$PHP_SELF\" name=personal>
     <td height=\"21\" width=\"99\" $bg>
         &nbsp;        Username
     <td height=\"21\" width=\"400\" $bg colspan=2>
     $member_db[2]
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=\"21\" width=\"200\" $bg>
         &nbsp;   New Password
     <td height=\"21\" width=\"400\" $bg colspan=2>
     <input name=editpassword >&nbsp;&nbsp;&nbsp;Only if you want to change the current
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=\"21\" width=\"200\" $bg>
         &nbsp;        Nickname
     <td height=\"21\" width=\"400\" $bg colspan=2>
     <input type=text name=editnickname value=\"$member_db[4]\">
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=\"21\" width=\"200\" $bg>
         &nbsp;        Email
     <td height=\"21\" width=\"400\" $bg colspan=2>
     <input type=text name=editmail value=\"$member_db[5]\">&nbsp;&nbsp;&nbsp;<input type=checkbox class=checkbox name=edithidemail $ifchecked>&nbsp;Hide my e-mail from visitors
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    if ($member_db[1] != 4) {
        echo"<tr>
     <td height=\"21\" width=\"200\" $bg>
         &nbsp;        Default Avatar URL
     <td height=\"21\" width=\"400\" $bg colspan=2>
     <input type=text name=change_avatar value=\"$member_db[8]\">&nbsp;&nbsp;&nbsp;&nbsp;Will appear on 'Add News' page
     </tr>";
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=\"21\" width=\"200\" $bg>
         &nbsp;    Access Level
     <td height=\"21\" width=\"400\" $bg colspan=2>";

    if ($member_db[1] == 4) {
        echo "Commenter";
    } elseif ($member_db[1] == 3) {
        echo "Journalist";
    } elseif ($member_db[1] == 2) {
        echo "Editor";
    } elseif ($member_db[1] == 1) {
        echo "Administrator";
    }
    echo"</tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    if ($member_db[1] != 4) {
        echo"
     <tr>
     <td height=\"21\" width=\"200\" $bg>
         &nbsp;        Written News
     <td height=\"21\" width=\"400\" $bg colspan=2>
     $member_db[6]
     </tr>";
    }
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=\"21\" width=\"200\" $bg>
         &nbsp;        Registration Date
     <td height=\"21\" width=\"400\" $bg colspan=2>
     $registrationdate
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
<!--// MailOnComment v1.4 - Start AddBlock-->
";
    if ($config_mail_allow_comments == "2") {
        if (trim($member_db[10]) == 1) {
            $mail_com = "checked";
        }
        if ($member_db[1] != 4) {
            echo"<tr>
<td height=\"21\" width=\"200\" $bg>
    &nbsp;         Mail On Comments
<td height=\"21\" width=\"400\" $bg colspan=2>
<input type=checkbox class=checkbox value=\"1\" name=change_mail_on_comment $mail_com>&nbsp;&nbsp;&nbsp;&nbsp;When someone comments you will recieve an e-mail
</tr>";
        }
    } else {
        echo"<input type=\"hidden\" name=\"change_mail_on_comment\" value=\"$config_mail_allow_comments\">";
    }
    echo "
<!--// MailOnComment v1.4 - End AddBlock-->
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=\"1\" width=\"611\" $bg colspan=3>
     <br /><input type=submit value=\"Save Changes\" accesskey=\"s\">
     </tr>
     <input type=hidden name=mod value=options>
     <input type=hidden name=action value=dosavepersonal>
     </form>
     </table>";

    echofooter();
}
// ********************************************************************************
// Save Personal Options
// ********************************************************************************
elseif ($action == "dosavepersonal") {
    $username=$member_db[2];
    $editnickname = replace_comment("add", $editnickname);
    $editmail = replace_comment("add", $editmail);
    $edithidemail = replace_comment("add", $edithidemail);
    $change_avatar = replace_comment("add", $change_avatar);
    if ($edithidemail) {
        $edithidemail = 1;
    } else {
        $edithidemail = 0;
    }

    $avatars = preg_replace(array("'\|'","'\n'","' '"), array("","","_"), $avatars);
    $editmail = trim($editmail, " \t\n\r\0");
    if (!$editmail) {
        msg("error", "Error !!!", "Email can not be blank, both fields must match");
    }
    if (!preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/i", $editmail)) {
        msg("error", "Error !!!", "Invalid Email.");
    }

    $old_user_db = file("./data/users.db.php");
    foreach ($old_user_db as $null => $user_line) {
        $user_arr = explode("|", $user_line);
        if ($config_register_multimail=="no" && $username != $user_arr[2] && stristr("|".$user_arr[5]."|", "|".$editmail."|")) {
            msg("error", "Error", "This email address is registered to another user!", "$PHP_SELF?mod=options&action=personal");
        }
    }
    $new_user_db = fopen("./data/users.db.php", w);
    $personal_success = false;
    foreach ($old_user_db as $null => $old_user_db_line) {
        $old_user_db_arr = explode("|", $old_user_db_line);
        if (strtolower($username) != strtolower($old_user_db_arr[2])) {
            fwrite($new_user_db, "$old_user_db_line");
        } else {
            if ($editpassword != "") {
                $old_user_db_arr[3] = md5($editpassword);
                if ($config_use_cookies == true) {
                    setcookie("md5_password", $old_user_db_arr[3]);
                }
                $_SESSION['md5_password'] = $old_user_db_arr[3];
            }
            fwrite($new_user_db, "$old_user_db_arr[0]|$old_user_db_arr[1]|$old_user_db_arr[2]|$old_user_db_arr[3]|$editnickname|$editmail|$old_user_db_arr[6]|$edithidemail|$change_avatar|$old_user_db_arr[9]|$change_mail_on_comment||\n");
            $personal_success = true;
        }
    }
    fclose($new_user_db);
    if ($personal_success) {
        msg("info", "Changes Saved", "Your personal information was saved.", "$PHP_SELF?mod=options&action=personal");
    } else {
        msg("error", "Error !!!", "Error while listing users, $username not found", "$PHP_SELF?mod=options&action=personal");
    }
}
// ********************************************************************************
// Show Personal Profile
// ********************************************************************************
elseif ($action == "profile") {
    if ($member_db[1] >= 4) {
        msg("error", "Access Denied", "You don't have permissions for this type of action");
    }

    $lines = file('data/profiles.db.php');

    $num_lines = count($lines);
    $num_lines = $num_lines -1;
    $num = "1";
    while ($num <= $num_lines) {
        $tmp = $lines[$num];
        $tmp = explode("|", $tmp);
        $$tmp[0] = $lines[$num];

        $num++;
    }
    $username = $member_db[2];
    if ($member_db[7] == 1) {
        $pflemail = "";
    } else {
        $pflemail = $member_db[5];
    }
    $profile = explode("|", $$username);
    if ($profile[5] == "") {
        $ifchecked = "Checked";
    }
    $months = array('01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December');
    $month = "<select size=\"1\" name=\"edit_month\">";
    for ($i=1;$i<=12;$i++) {
        if ($i < 10) {
            $i = '0'.$i;
        }
        if (date("m", $profile[5]) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $month .= "<option value=\"$i\"$selected>".$months[$i]."</option>";
    }
    $month .= "</select>";
    $day = "<select size=\"1\" name=\"edit_day\">";
    for ($i=1;$i<=31;$i++) {
        if (date("j", $profile[5]) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $day .= "<option value=\"$i\"$selected>$i</option>";
    }
    $day .= "</select>";
    $year = "<select size=\"1\" name=\"edit_year\">";
    for ($i=(date("Y"));$i>=(date("Y")-90);$i--) {
        if (date("Y", $profile[5]) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $year .= "<option value=\"$i\"$selected>$i</option>";
    }
    $year .= "</select>";

    echoheader("user", "Personal Profile");

    $pflbio = $profile[11];
    $pflbio = str_replace("<br />", "\n", $pflbio);
    $pflbio = str_replace("\\", "", $pflbio);

    $un = strtolower(str_replace(" ", "", $member_db[2]));

    $userpfl = fopen("./data/profiles/$un.pfl", "w");
    if (!fwrite($userpfl, "<?php\n\n\$cutepath = \"$cutepath\";\n\n\$user = \"$member_db[2]\";\ninclude (\"\$cutepath/inc/profiles.inc.php\");\n\n?>")) {
        echo "<span class=error>$un.pfl file could not be created/modified.</span><br /><br />";
    }
    fclose($userpfl);

    if (!$profile[1]) {
        $line = fopen("data/profiles.db.php", "a+");
        fwrite($line, "$member_db[2]||||||||||||\n");
        fclose($line);
        echo "<span class=warning>$username line has been created in profiles.db.php file.</span><br /><br />";
    }
    $bg = "class=altern1";
    echo"
     <table border=0 height=1 width=617 cellspacing=0 cellpadding=0>
     <form method=POST action=\"$PHP_SELF\" name=personal>

     <tr>
     <td height=21 width=200 $bg>
         &nbsp;   Full Name
     <td height=21 width=400 $bg colspan=2>
     <input type=text name=pflname value=\"$profile[1]\">
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=21 width=200 >
         &nbsp;        Date of Birth
    <td height=21 width=400  colspan=2>
     $month&nbsp;$day&nbsp;$year &nbsp; <input type=checkbox class=checkbox name=edithidebirth $ifchecked>&nbsp;Don't show my date of birth
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=21 width=200 $bg>
         &nbsp;        Location
     <td height=21 width=400 $bg colspan=2>
     <input type=text name=pfllocation value=\"$profile[6]\">
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=21 width=200 >
         &nbsp;        ICQ
     <td height=21 width=400  colspan=2>
     <input type=text name=pflicq value=\"$profile[7]\">
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=21 width=200 $bg>
         &nbsp;        AIM
     <td height=21 width=400 $bg colspan=2>
     <input type=text name=pflaim value=\"$profile[8]\">
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=21 width=200 >
         &nbsp;        YIM
     <td height=21 width=400  colspan=2>
     <input type=text name=pflyim value=\"$profile[9]\">
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=21 width=200 $bg>
         &nbsp;        MSN
     <td height=21 width=400 $bg colspan=2>
     <input type=text name=pflmsn value=\"$profile[10]\">
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=21 width=200 >
         &nbsp;        Bio
     <td height=21 width=400  colspan=2>
     <textarea style=\"height: 100px; width: 300px;\" name=pflbio>$pflbio</textarea>
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=21 width=200 $bg>
         &nbsp;   Avatar URL / Email / Nickname
     <td height=21 width=400 $bg colspan=2>
     These parts of your profile are set in the news system personal options.
     </tr>
";
    if ($bg == "class=altern1") {
        $bg = "class=altern2";
    } else {
        $bg = "class=altern1";
    }
    echo"
     <tr>
     <td height=1 width=611  colspan=3 align=right>
     <br /><input type=submit value=\"Save Changes\" accesskey=\"s\">
     </tr>
     <input type=hidden name=pflemail value=\"$pflemail\">
     <input type=hidden name=pflnick value=\"$member_db[4]\">
     <input type=hidden name=pflavatar value=\"$member_db[8]\">
     <input type=hidden name=mod value=options>
     <input type=hidden name=action value=dosaveprofile>
     </form>
     </table>";


    echofooter();
}



// ********************************************************************************
// Save Personal Profile
// ********************************************************************************
elseif ($action == "dosaveprofile") {
    $pflname = replace_comment("add", $pflname);
    $pfllocation = replace_comment("add", $pfllocation);
    $pflicq = replace_comment("add", $pflicq);
    $pflaim = replace_comment("add", $pflaim);
    $pflyim = replace_comment("add", $pflyim);
    $pflmsn = replace_comment("add", $pflmsn);
    $pflbio = replace_comment("add", $pflbio);

    $months = array('01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December');

    $avatars = preg_replace(array("'\|'","'\n'","' '"), array("","","_"), $avatars);

    $old_user_db = file("./data/profiles.db.php");
    $new_user_db = fopen("./data/profiles.db.php", w);
    fwrite($new_user_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    $personal_success = false;
    foreach ($old_user_db as $old_user_db_line) {
        if (eregi("<\?", $old_user_db_line)) {
            continue;
        }
        $old_user_db_arr = explode("|", $old_user_db_line);
        if (strtolower($username) != strtolower($old_user_db_arr[0])) {
            fwrite($new_user_db, "$old_user_db_line");
        } else {
            $pflbio = str_replace("\n", "<br />", $pflbio);
            $pflbio = str_replace("\r", "", $pflbio);
            $pflbirth = strtotime("$edit_day ".$months[$edit_month]." $edit_year 12:30:59");
            if ($edithidebirth) {
                $pflbirth = "";
            }

            fwrite($new_user_db, "$old_user_db_arr[0]|$pflname|$pflnick|$pflavatar|$pflemail|$pflbirth|$pfllocation|$pflicq|$pflaim|$pflyim|$pflmsn|$pflbio|\n");
            $personal_success = true;
        }
    }
    fclose($new_user_db);
    if ($personal_success) {
        msg("info", "Changes Saved", "Your personal information was saved.", "$PHP_SELF?mod=options&action=profile");
    } else {
        msg("error", "Error!", "Can't save personal profile info", "$PHP_SELF?mod=options&action=profile");
    }
}
// ********************************************************************************
// Edit Templates
// ********************************************************************************
elseif ($action == "templates") {
    if ($member_db[1] != 1) {
        msg("error", "Access Denied", "You don't have permissions for this type of action");
    }
    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Detect all template packs we have
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    $templates_list = array();
    if (!$handle = opendir("./data")) {
        die("<center>Can not open directory $cutepath/data ");
    }
    while (false !== ($file = readdir($handle))) {
        if (eregi(".tpl.php", $file)) {
            $file_arr                 = explode(".", $file);
            $templates_list[]= $file_arr[0];
        }
    }
    closedir($handle);

    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      If we want to create new template
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    if ($subaction == "new") {
        echoheader("options", "New Template");

        echo"<form method=post action=\"$PHP_SELF\"><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td >Create new template based on: <select name=base_template>";
        foreach ($templates_list as $null => $single_template) {
            echo "<option value=\"$single_template\">$single_template</option>";
        }
        echo '</select> with name <input type=text name=template_name> &nbsp;<input type=submit value="Create Template">
        <input type=hidden name=mod value=options>
        <input type=hidden name=action value=templates>
        <input type=hidden name=subaction value=donew>
        </td></tr></table></form>';
        echofooter();
        exit;
    }
    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Do Create the new template
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    if ($subaction == "donew") {
        if (!eregi("^[a-z0-9_-]+$", $template_name)) {
            msg("error", "Error", "The name of the template must be only with letters and numbers", "$PHP_SELF?mod=options&subaction=new&action=templates");
        }
        if (file_exists("./data/${template_name}.tpl.php")) {
            msg("error", "Error", "Template with this name already exists", "$PHP_SELF?mod=options&subaction=new&action=templates");
        }

        if ($base_template != "") {
            $base_file = "./data/${base_template}.tpl.php";
        } else {
            $base_file = "./data/Default.tpl.php";
        }

        if (!copy($base_file, "./data/${template_name}.tpl.php")) {
            msg("error", "Error", "Can not copy file $base_file to ./data/ folder with name ${template_name}.tpl.php");
        }
        @chmod("./data/${template_name}.tpl.php", 0777);

        msg("info", "Template Created", "A new template was created with name <b>${template_name}</b><br />", "$PHP_SELF?mod=options&action=templates");
    }
    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Deleting template, preparation
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    if ($subaction == "delete") {
        if (strtolower($do_template) == "default") {
            msg("Error", "Error !!!", "You can not delete the default template", "$PHP_SELF?mod=options&action=templates");
        }
        $msg = "<form method=post action=\"$PHP_SELF\">Are you sure you want to delete the template <b>$do_template</b> ?<br /><br />
        <input type=submit value=\" Yes, Delete This Template\"> &nbsp;<input onClick=\"document.location='$PHP_SELF?mod=options&action=templates';\" type=button value=\"Cancel\">
        <input type=hidden name=mod value=options>
        <input type=hidden name=action value=templates>
        <input type=hidden name=subaction value=dodelete>
        <input type=hidden name=do_template value=\"$do_template\">
        </form>";

        msg("info", "Deleting Template", $msg);
    }
    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      DO Deleting template
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    if ($subaction == "dodelete") {
        if (strtolower($do_template) == "default") {
            msg("Error", "Error !!!", "You can not delete the default template", "$PHP_SELF?mod=options&action=templates");
        }
        $unlink = unlink("./data/${do_template}.tpl.php");
        if (!$unlink) {
            msg("error", "Error", "Can not delete file ./data/${do_template}.tpl.php <br />maybe the is no permission from the server");
        } else {
            msg("info", "Template Deleted", "The template <b>${do_template}</b> was deleted.", "$PHP_SELF?mod=options&action=templates");
        }
    }


    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Show The Template Manager
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    if ($do_template == '' or !$do_template) {
        $do_template = 'Default';
        $show_delete_link = '';
    } elseif (strtolower($do_template) != 'default') {
        $show_delete_link = "<a href=\"$PHP_SELF?action=templates&mod=options&subaction=delete&do_template=$do_template\">[delete this template]</a>";
    }
    require("./data/${do_template}.tpl.php");



    if (eregi("opera", $_SERVER['HTTP_USER_AGENT'])) {
        $tr_hidden = "";
    } else {
        $tr_hidden = " style='display:none'";
    }


    $templates_names = array("template_active", "template_comment", "template_form", "template_login", "template_full", "template_prev_next", "template_comments_prev_next");
    foreach ($templates_names as $null => $template) {
        $$template = htmlspecialchars($$template);
    }
    echoheader("options", "Templates");

    echo'<table border=0 cellpadding=0 cellspacing=0 height="77" >
    <tr>
        <td width=373 height="75">
    <b>Manage Templates</b>


        <table border=0 cellpadding=0 cellspacing=0 width=347  class="panel" height="50" >
    <form method=get action="'.$PHP_SELF.'">
    <tr>
    <td width=126 height="23">
    &nbsp;Editing Template
        <td width=225 height="23">
    :&nbsp; <b>'.$do_template.'</b>
    </tr>
    <tr>
        <td width=126 height="27">
    &nbsp;Switch to Template
        <td width=225 height="27">
    :&nbsp; <select size=1 name=do_template>';

    foreach ($templates_list as $null => $single_template) {
        if ($single_template == $do_template) {
            echo"<option selected value=\"$single_template\">$single_template</option>";
        } else {
            echo"<option value=\"$single_template\">$single_template</option>";
        }
    }

    echo'</select>
    <input type=submit value=Go>
    </tr>
    <tr>
        <td width=351 height="25" colspan="2">
    &nbsp;<a href="'.$PHP_SELF.'?mod=options&subaction=new&action=templates">[create new template]</a>&nbsp;
    '.$show_delete_link.'</tr>
        <input type=hidden name=action value=templates><input type=hidden name=mod value=options>
        </form>
        </table>

        <td width=268 height="75" align="center">
  <!-- HELP -->
   <table cellspacing="0" cellpadding="0">
    <tr>
      <td width="25" align=middle><img border="0" src="skins/images/help_small.gif" /></td>
      <td >&nbsp;<a onClick="javascript:Help(\'templates\')" href="#">Understanding Templates</a></td>
    </tr>
   </table>
  <!-- END HELP -->

    </tr>
        </table>
    <img height=20 border=0 src="skins/images/blank.gif" width=1 />
    <br />
    <b>Edit Template Parts</b><table width="100%"><form method=post action="'.$PHP_SELF.'">

<tr> <!- start active news -->
    <td height="7"  class=altern1 colspan="2">
    <b><a class=header href="javascript:ShowOrHide(\'active-news1\',\'active-news2\')" >Active News</a></b>
    </tr>
<tr id=\'active-news1\' class=altern2 '.$tr_hidden.'>
    <td height="9" width="200" valign="top">
<strong class="error">Basic Tags</strong><br />
 <b>{title}<br />
    {avatar}<br />
    {short-story}<br />
    {full-story}<br />
    {author}<br />
    {author-name}<br />
    {author-lower}<br />
    {date}<br />
    [mail] </b>and<b> [/mail]<br />
    [link] </b>and<b> [/link]<br />
    [full-link] </b>and<b> [/full-link]<br />
    [com-link] </b>and<b> [/com-link]<br />
    {comments-num}<br />
    {views}<br />
    {category}<br />
    {category-icon}<br /><br />



<strong class="error">Advanced Tags</strong><br />
    [date] </b>and<b> [/date]<br />
    [alt] </b>and<b> [/alt]<br />
    [truncate=X] </b>and<b> [/truncate]<br />
    [logged-in]<br />
    [/logged-in]<br />
    [not-logged-in]<br />
    [/not-logged-in]<br />
    {archive-id}<br />
    {category-id}<br />
    {news-id}<br />
    {cute-http-path}<br />
    {avatar-url}<br /><br />



<strong class="error">XFIELDS Tags</strong><br />
    [xfvalue_NAME]<br />
    [xfgiven_NAME]<br />
    [/xfgiven_NAME]<br /><br />



<strong class="error">Profile Tags</strong><br />
    {pfl-name}<br />
    {pfl-nick}<br />
    {pfl-avatar}<br />
    {pfl-avatar-url}<br />
    {pfl-email}<br />
    {pfl-age}<br />
    [pfl-birth] </b>and<b> [/pfl-birth]<br />
    {pfl-location}<br />
    {pfl-icq}<br />
    {pfl-aim}<br />
    {pfl-yim}<br />
    {pfl-msn}<br />
    {pfl-bio}<br />
    {icon-email}<br />
    {icon-icq}<br />
    {icon-aim}<br />
    {icon-yim}<br />
    {icon-msn}<br />
    [link-email] </b>and<b> [/link-email]<br />
    [link-icq] </b>and<b> [/link-icq]<br />
    [link-aim] </b>and<b> [/link-aim]<br />
    [link-yim] </b>and<b> [/link-yim]<br />
    [link-msn] </b>and<b> [/link-msn]<br />
    <td height="9"  valign="top" width=430>
    <br />
    - Title of the article<br />
    - Show Avatar image (if any)<br />
    - Short story of news item<br />
    - The full story<br />
    - Author of the article, with link to his email (if any)<br />
    - The name of the author, without email<br />
    - The username of the author, in lowercase<br />
    - Date when the story is written<br />
    - Will generate a link to the author mail (if any) eg. <span class="error">[mail]Email[/mail]</span><br />
    - Will generate a permanent link to the full story<br />
    - Link to the full story of article, only if there is full story<br />
    - Generate link to the comments of article<br />
    - This will display the number of comments posted for article<br />
    - This will display the number of times visitors have viewed this article<br />
    - Name of the category where article is posted (if any)<br />
    - Shows the category icon (if any)<br /><br /><br />



    - Displays PHP date("FLAGS"); eg. <span class="error">[date]d M Y[/date]</span><br />
    - <span class="error">[alt]1,2[/alt]</span> Displays 1 or 2 alternating (COMMA SEPARATING!)<br />
    - Truncates (shortens) the between to X characters<br />
    - Shows Content when Logged in (open field)<br/>
    - Shows Content when Logged in (closes field)<br/>
    - Shows Content when Not Logged in (open field)<br/>
    - Shows Content when Not Logged in (closes field)<br />
    - The Full ID Number of the archive (if any)<br />
    - The Full ID Number of the category (if any)<br />
    - The Full ID Number of the news (if any)<br />
    - The Cutenews HTTP Path<br />
    - Displays the Full URL to the avatar image (if any)<br /><br /><br />



    - XField with the same name as provided in the tag as NAME.<br />
    - Shows content only if XField has a value.<br />
    - If XField has no value, it does not show.<br /><br /><br />



    - Full name of the Author<br />
    - Authors Nickname<br />
    - Authors Avatar<br />
    - Authors Avatar URL<br />
    - Authors Email Address<br />
    - Authors Age<br />
    - Authors Birthday EG. <span class="error">[pfl-birth]d M y[/pfl-birth]</span> use php date strings<br />
    - Authors Location<br />
    - Authors ICQ Number<br />
    - Authors AIM ID<br />
    - Authors Yahoo ID<br />
    - Authors MSN ID<br />
    - Authors Biography<br />
    - An Icon for Email<br />
    - An ICQ Satus Indicator Icon<br />
    - An AIM Satus Indicator Icon<br />
    - An YIM Satus Indicator Icon<br />
    - An MSN Satus Indicator Icon<br />
    - Link to send Author an Email<br />
    - Link to send Author an ICQ Message<br />
    - Link to send Author an AIM Message<br />
    - Link to send Author an YIM Message<br />
    - Link to send Author an Email (not MSN Message)<br />
    </td>
<tr id=\'active-news2\' class=altern1 '.$tr_hidden.'>
    <td height="8"  colspan="2">
    <textarea rows="25" cols="98" name="edit_active">'.$template_active.'</textarea>
    <br />
    &nbsp;
</tr> <!-- End active news -->

<tr> <!-- Start full story -->
    <td height="7"  class=altern1 colspan="2">
    <b><a class=header href="javascript:ShowOrHide(\'full-story1\',\'full-story2\')" >Full Story</a></b>
    </tr>
<tr id=\'full-story1\' class=altern2 '.$tr_hidden.'>
   <td height="9" width="200" valign="top">
<strong class="error">Basic Tags</strong><br />
 <b>{title}<br />
    {avatar}<br />
    {short-story}<br />
    {full-story}<br />
    {author}<br />
    {author-name}<br />
    {author-lower}<br />
    {date}<br />
    [mail] </b>and<b> [/mail]<br />
    [link] </b>and<b> [/link]<br />
    [com-link] </b>and<b> [/com-link]<br />
    {comments-num}<br />
    {views}<br />
    {category}<br />
    {category-icon}<br /><br />



<strong class="error">Advanced Tags</strong><br />
    [date] </b>and<b> [/date]<br />
    [alt] </b>and<b> [/alt]<br />
    [truncate=X] </b>and<b> [/truncate]<br />
    [logged-in]<br />
    [/logged-in]<br />
    [not-logged-in]<br />
    [/not-logged-in]<br />
    {archive-id}<br />
    {category-id}<br />
    {news-id}<br />
    {cute-http-path}<br />
    {avatar-url}<br /><br />



<strong class="error">XFIELDS Tags</strong><br />
    [xfvalue_NAME]<br />
    [xfgiven_NAME]<br />
    [/xfgiven_NAME]<br /><br />



<strong class="error">Profile Tags</strong><br />
    {pfl-name}<br />
    {pfl-nick}<br />
    {pfl-avatar}<br />
    {pfl-avatar-url}<br />
    {pfl-email}<br />
    {pfl-age}<br />
    [pfl-birth] </b>and<b> [/pfl-birth]<br />
    {pfl-location}<br />
    {pfl-icq}<br />
    {pfl-aim}<br />
    {pfl-yim}<br />
    {pfl-msn}<br />
    {pfl-bio}<br />
    {icon-email}<br />
    {icon-icq}<br />
    {icon-aim}<br />
    {icon-yim}<br />
    {icon-msn}<br />
    [link-email] </b>and<b> [/link-email]<br />
    [link-icq] </b>and<b> [/link-icq]<br />
    [link-aim] </b>and<b> [/link-aim]<br />
    [link-yim] </b>and<b> [/link-yim]<br />
    [link-msn] </b>and<b> [/link-msn]<br />
    <td height="9"  valign="top" width=430>
    <br />
    - Title of the article<br />
    - Show Avatar image (if any)<br />
    - Short story of news item<br />
    - The full story<br />
    - Author of the article, with link to his email (if any)<br />
    - The name of the author, without email<br />
    - The username of the author, in lowercase<br />
    - Date when the story is written<br />
    - Will generate a link to the author mail (if any) eg. <span class="error">[mail]Email[/mail]</span><br />
    - Will generate a permanent link to the full story<br />
    - Link to the full story of article, only if there is full story<br />
    - Generate link to the comments of article<br />
    - This will display the number of comments posted for article<br />
    - This will display the number of times visitors have viewed this article<br />
    - Name of the category where article is posted (if any)<br />
    - Shows the category icon (if any)<br /><br /><br />



    - Displays PHP date("FLAGS"); eg. <span class="error">[date]d M Y[/date]</span><br />
    - <span class="error">[alt]1,2[/alt]</span> Displays 1 or 2 alternating (COMMA SEPARATING!)<br />
    - Truncates (shortens) the between to X characters<br />
    - Shows Content when Logged in (open field)<br/>
    - Shows Content when Logged in (closes field)<br/>
    - Shows Content when Not Logged in (open field)<br/>
    - Shows Content when Not Logged in (closes field)<br />
    - The Full ID Number of the archive (if any)<br />
    - The Full ID Number of the category (if any)<br />
    - The Full ID Number of the news (if any)<br />
    - The Cutenews HTTP Path<br />
    - Displays the Full URL to the avatar image (if any)<br /><br /><br />



    - XField with the same name as provided in the tag as NAME.<br />
    - Shows content only if XField has a value.<br />
    - If XField has no value, it does not show.<br /><br /><br />



    - Full name of the Author<br />
    - Authors Nickname<br />
    - Authors Avatar<br />
    - Authors Avatar URL<br />
    - Authors Email Address<br />
    - Authors Age<br />
    - Authors Birthday EG. <span class="error">[pfl-birth]d M y[/pfl-birth]</span> use php date strings<br />
    - Authors Location<br />
    - Authors ICQ Number<br />
    - Authors AIM ID<br />
    - Authors Yahoo ID<br />
    - Authors MSN ID<br />
    - Authors Biography<br />
    - An Icon for Email<br />
    - An ICQ Satus Indicator Icon<br />
    - An AIM Satus Indicator Icon<br />
    - An YIM Satus Indicator Icon<br />
    - An MSN Satus Indicator Icon<br />
    - Link to send Author an Email<br />
    - Link to send Author an ICQ Message<br />
    - Link to send Author an AIM Message<br />
    - Link to send Author an YIM Message<br />
    - Link to send Author an Email (not MSN Message)<br />
    </td>
<tr id=\'full-story2\' class=altern1 '.$tr_hidden.'>
    <td height="8"  colspan="2">
    <textarea rows="25" cols="98" name="edit_full">'.$template_full.'</textarea>
    <br />
    &nbsp;
</tr> <!-- End full story -->

<tr> <!-- Start comment -->
    <td height="7"  class=altern1 colspan="2">
    <b><a class=header href="javascript:ShowOrHide(\'comment1\',\'comment2\')" >Comment</a></b>
    </tr>
<tr id=\'comment1\' class=altern2 '.$tr_hidden.'>
    <td height="9" width="200" valign="top">
<strong class="error">Basic Tags</strong><br />
 <b>{author}<br />
    {mail}<br />
    {date}<br />
    {comment}<br /><br />



<strong class="error">Advanced User Tags</strong><br />
    [date] </b>and<b> [/date]<br />
    [alt] </b>and<b> [/alt]<br />
    {comment-id}<br />
    {comment-number}<br />
    <td height="9"  valign="top">
    <br />
    - Name of the comment poster<br />
    - E-mail of the poster<br />
    - Date when the comment was posted<br />
    - The Comment<br /><br /><br />



    - Displays PHP date("FLAGS"); eg. <span class="error">[date]d M Y[/date]</span><br />
    - <span class="error">[alt]1,2[/alt]</span> Displays 1 or 2 alternating (COMMA SEPARATING!)<br />
    - The comment\'s time ID<br />
    - The comment id number<br />
    </td>
<tr id=\'comment2\' class=altern1 '.$tr_hidden.'>
    <td height="8"  colspan="2">
    <textarea rows="25" cols="98" name="edit_comment">'.$template_comment.'</textarea>
    <br />
    &nbsp;
</tr> <!-- End comment -->

<tr> <!-- Start add comment form -->
    <td height="7"  class=altern1 colspan="2">
    <b><a class=header href="javascript:ShowOrHide(\'add-comment-form1\',\'add-comment-form2\')" >Add comment form</a></b>
    </tr>
<tr id=\'add-comment-form1\' class=altern2 '.$tr_hidden.'>
    <td height="9" width="200" valign="top">
<strong class="error">Basic Tags</strong><br />
 <b>{comment-max}<br />
    {character-limiter}<br />
    {smilies}<br />
    {cute-http-path}<br />
    {stored-name}<br /><br />



<strong class="error">Advanced User Tags</strong><br />
    [logged-in]<br />
    [/logged-in]<br />
    [not-logged-in]<br />
    [/not-logged-in]<br />
    <td height="9"  valign="top">
    <br />
    - Maximum comment length (defined in Admin CP)<br />
    - Displays a character limiter, shows remaining characters.<br />
    - Displays the smilies you can use.<br />
    - The Cutenews HTTP Path.<br />
    - Shows the current username (if logged in)<br /><br /><br />


    - Shows Content when Logged in (open field)<br/>
    - Shows Content when Logged in (closes field)<br/>
    - Shows Content when Not Logged in (open field)<br/>
    - Shows Content when Not Logged in (closes field)<br />
    </td>
    </tr>
<tr id=\'add-comment-form2\' class=altern1 '.$tr_hidden.'>
    <td height="8"  colspan="2">    <span class=error>Please do not edit this unless you have basic HTML knowledge !!!</span>
    <textarea rows="25" cols="98" name="edit_form">'.$template_form.'</textarea>
    <br />
    &nbsp;
</tr> <!-- End add comment form -->

<tr> <!-- Start add login form -->
    <td height="7"  class=altern1 colspan="2">
    <b><a class=header href="javascript:ShowOrHide(\'login-form1\',\'login-form2\')" >Login form</a></b>
    </tr>
<tr id=\'login-form1\' class=altern2 '.$tr_hidden.'>
    <td height="9" width="200" valign="top">
 <b>{message}<br />
    {cute-http-path}<br />
    {last-username}<br />
    (check}<br />
    <td height="9"  valign="top">
    - Error Message (such as banned or wrong password)<br />
    - The Cutenews HTTP Path.<br />
    - The Last username used to log in (if any)<br />
    - To be used as: <span class="error">&lt;input type=checkbox class=checkbox name=rememberpw {check}&gt;</span><br />
    </td>
    </tr>
<tr id=\'login-form2\' class=altern1 '.$tr_hidden.'>
    <td height="8"  colspan="2">  <span class=error>Please do not edit this unless you have basic HTML knowledge !!!</span>
    <textarea rows="25" cols="98" name="edit_login">'.$template_login.'</textarea>
    <br />
    &nbsp;
</tr> <!-- End add login form -->

<tr> <!-- Start previous & next -->
    <td height="7"  class=altern1 colspan="2">
    <b><a class=header href="javascript:ShowOrHide(\'previous-next1\',\'previous-next2\')" >News Pagination</a></b>
    </tr>
<tr id=\'previous-next1\' class=altern2 '.$tr_hidden.'>
    <td height="9" width="200" valign="top">
 <b>[prev-link] </b>and<b> [/prev-link]<br />
    [next-link] </b>and<b> [/next-link]<br />
    {pages}<br />
    {current-page}<br />
    {total-pages}<br />
    <td height="9"  valign="top">
    - Will generate a link to preveous page (if there is)<br />
    - Will generate a link to next page (if there is)<br />
    - Shows linked numbers of the pages; example: <a href=\'#\'>1</a> <a href=\'#\'>2</a> <a href=\'#\'>3</a> <a href=\'#\'>4</a><br />
    - Will show "Page X ..." Eg. <span class="error">Page 1 of 6.</span><br />
    - Will show "...of X" Eg. <span class="error">Page 1 of 6.</span><br />
    </tr>

<tr id=\'previous-next2\' class=altern1 '.$tr_hidden.'>
    <td height="8"  colspan="2">
    <textarea rows="6" cols="98" name="edit_prev_next">'.$template_prev_next.'</textarea>
</tr> <!-- End previous & next -->

<tr> <!-- Start previous & next COMMENTS-->
   <td height="7"  class=altern1 colspan="2">
    <b><a class=header href="javascript:ShowOrHide(\'previous-next21\',\'previous-next22\')" >Comments Pagination</a></b>
    </tr>
<tr id=\'previous-next21\' class=altern2 '.$tr_hidden.'>
    <td height="9" width="200" valign="top">
 <b>[prev-link] </b>and<b> [/prev-link]<br />
    [next-link] </b>and<b> [/next-link]<br />
    {pages}<br />
    {current-page}<br />
    {total-pages}<br />
    <td height="9"  valign="top">
    - Will generate a link to preveous page (if there is)<br />
    - Will generate a link to next page (if there is)<br />
    - Shows linked numbers of the pages; example: <a href=\'#\'>1</a> <a href=\'#\'>2</a> <a href=\'#\'>3</a> <a href=\'#\'>4</a><br />
    - Will show "Page X ..." Eg. <span class="error">Page 1 of 6.</span><br />
    - Will show "...of X" Eg. <span class="error">Page 1 of 6.</span><br />
    </tr>

<tr id=\'previous-next22\' class=altern1 '.$tr_hidden.'>
    <td height="8"  colspan="2">
    <textarea rows="6" cols="98" name="edit_comments_prev_next">'.$template_comments_prev_next.'</textarea>
</tr> <!-- End previous & next COMMENTS -->

<tr class=altern2>
    <td height="8"  colspan="2">
    <input type=hidden name=mod value=options>
    <input type=hidden name=action value=dosavetemplates>
    <input type=hidden name=do_template value="'.$do_template.'">
    <br /><input type=submit value="   Save Changes   " accesskey="s">
    </tr></form>
    </table>';

    echofooter();
}
// ********************************************************************************
// Do Save Changes to Templates
// ********************************************************************************
elseif ($action == "dosavetemplates") {
    if ($member_db[1] != 1) {
        msg("error", "Access Denied", "You don't have permissions for this type of action");
    }
    $templates_names = array("edit_active", "edit_comment", "edit_form", "edit_login", "edit_full", "edit_prev_next", "edit_comments_prev_next");
    foreach ($templates_names as $null => $template) {
        $$template = stripslashes($$template);
    }

    if ($do_template == "" or !$do_template) {
        $do_template = "Default";
    }
    $template_file = "./data/${do_template}.tpl.php";

    $handle = fopen("$template_file", "w");
    fwrite($handle, "<?PHP\n///////////////////// TEMPLATE $do_template /////////////////////\n");
    fwrite($handle, "\$template_active = <<<HTML\n$edit_active\nHTML;\n\n\n");
    fwrite($handle, "\$template_full = <<<HTML\n$edit_full\nHTML;\n\n\n");
    fwrite($handle, "\$template_comment = <<<HTML\n$edit_comment\nHTML;\n\n\n");
    fwrite($handle, "\$template_form = <<<HTML\n$edit_form\nHTML;\n\n\n");
    fwrite($handle, "\$template_login = <<<HTML\n$edit_login\nHTML;\n\n\n");
    fwrite($handle, "\$template_prev_next = <<<HTML\n$edit_prev_next\nHTML;\n");
    fwrite($handle, "\$template_comments_prev_next = <<<HTML\n$edit_comments_prev_next\nHTML;\n");
    fwrite($handle, "?>\n");

    msg("info", "Changes Saved", "The changes to template <b>$do_template</b> were successfully saved.", "$PHP_SELF?mod=options&action=templates&do_template=$do_template");
}

// ********************************************************************************
// Edit Protemp
// ********************************************************************************
elseif ($action == "protemp") {
    if ($member_db[1] != 1) {
        msg("error", "Access Denied", "You don't have permissions for this type of action");
    }
    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Show The Template Manager
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    require("./data/protemp.db.php");
    $edit_pfl = htmlspecialchars($edit_pfl);
    echoheader("options", "Templates");

    echo'
    <img height=20 border=0 src="skins/images/blank.gif" width=1 />
    <br />
    <table width="100%"><form method=post action="'.$PHP_SELF.'">

<tr> <!- Start profile -->
    <td height="7"  class=altern1 colspan="2">
    <b><a class=header>Profile Template</a></b>
    </tr>
    <tr>
    <td height="9" width="200" valign="top">
    <strong class="error">Insert Author Information</strong><br />
 <b>{pfl-name}<br />
    {pfl-nick}<br />
    {pfl-avatar}<br />
    {pfl-avatar-url}<br />
    {pfl-email}<br />
    {pfl-age}<br />
    [pfl-birth] </b>and<b> [/pfl-birth]<br />
    {pfl-location}<br />
    {pfl-icq}<br />
    {pfl-aim}<br />
    {pfl-yim}<br />
    {pfl-msn}<br />
    {pfl-bio}<br />
    {icon-email}<br />
    {icon-icq}<br />
    {icon-aim}<br />
    {icon-yim}<br />
    {icon-msn}<br />
    [link-email] </b>and<b> [/link-email]<br />
    [link-icq] </b>and<b> [/link-icq]<br />
    [link-aim] </b>and<b> [/link-aim]<br />
    [link-yim] </b>and<b> [/link-yim]<br />
    [link-msn] </b>and<b> [/link-msn]<br />
    <td height="9"  valign="top" width=430>
    <br />
    - Full name of the Author<br />
    - Authors Nickname<br />
    - Authors Avatar<br />
    - Authors Avatar URL<br />
    - Authors Email Address<br />
    - Authors Age<br />
    - Authors Birthday EG. <span class="error">[pfl-birth]d M y[/pfl-birth]</span> use php date strings<br />
    - Authors Location<br />
    - Authors ICQ Number<br />
    - Authors AIM ID<br />
    - Authors Yahoo ID<br />
    - Authors MSN ID<br />
    - Authors Biography<br />
    - An Icon for Email<br />
    - An ICQ Satus Indicator Icon<br />
    - An AIM Satus Indicator Icon<br />
    - An YIM Satus Indicator Icon<br />
    - An MSN Satus Indicator Icon<br />
    - Link to send Author an Email<br />
    - Link to send Author an ICQ Message<br />
    - Link to send Author an AIM Message<br />
    - Link to send Author an YIM Message<br />
    - Link to send Author an Email (not MSN Message)<br />
    </td>
    <tr>
    <td height="8"  colspan="2">
    <textarea rows="25" cols="98" name="edit_pfl">'.$template_pfl.'</textarea>
    <br />
    &nbsp;
</tr> <!-- End profile -->

<tr>
    <td height="8"  colspan="2">
    <input type=hidden name=mod value=options>
    <input type=hidden name=action value=dosaveprotemp>
    <br /><input type=submit value="   Save Changes   " accesskey="s">
    </tr></form>
    </table>';

    echofooter();
}
// ********************************************************************************
// Do Save Changes to Protemp
// ********************************************************************************
elseif ($action == "dosaveprotemp") {
    if ($member_db[1] != 1) {
        msg("error", "Access Denied", "You don't have permissions for this type of action");
    }
    $edit_pfl = stripslashes($edit_pfl);
    $template_file = "./data/protemp.db.php";
    $handle = fopen("$template_file", "w");
    fwrite($handle, "<?PHP\n///////////////////// TEMPLATE $do_template /////////////////////\n");
    fwrite($handle, "\$template_pfl = <<<HTML\n$edit_pfl\nHTML;\n");
    fwrite($handle, "?>\n");

    msg("info", "Changes Saved", "The changes to the template were successfully saved.", "$PHP_SELF?mod=options&action=protemp");
}

// ********************************************************************************
// System Configuration
// ********************************************************************************
elseif ($action == "syscon") {
    echoheader("options", "System Configuration");

    function showRow($title="", $description="", $field="")
    {
        global $i;
        if ($i%2 == 0 and $title != "") {
            $bg = "class=altern1";
        } else {
            $bg = "class=altern2";
        }
        echo"<tr $bg >
                <td colspan=\"2\" style=\"padding:4\">
                &nbsp;<b>$title</b>
                <td width=294 rowspan=\"2\" valign=\"middle\" align=middle>
                $field<br />&nbsp;
                </tr>
                <tr $bg >
        <td height=15 width=\"27\" style=\"padding:4\">&nbsp;

        <td height=15 width=\"299\" valign=top>
        <font color=\"#808080\">$description</font>
                </tr>";
        $bg = "";
        $i++;
    }
    function makeDropDown($options, $name, $selected)
    {
        $output = "<select size=1 name=\"$name\">\r\n";
        foreach ($options as $value=>$description) {
            $output .= "<option value=\"$value\"";
            if ($selected == $value) {
                $output .= " selected ";
            }
            $output .= ">$description</option>\n";
        }
        $output .= "</select>";
        return $output;
    }

    if (!$handle = opendir("./skins")) {
        die("Can not open directory ./skins ");
    }
    while (false !== ($file = readdir($handle))) {
        $file_arr = explode(".", $file);
        if ($file_arr[1] == "skin") {
            $sys_con_skins_arr[$file_arr[0]] = $file_arr[0];
        } elseif ($file_arr[1] == "lang") {
            $sys_con_langs_arr[$file_arr[0]] = $file_arr[0];
        }
    }
    closedir($handle);

    $divcon_general = "display: none;";
    $divcon_news = "display: none;";
    $divcon_comments = "display: none;";
    if ($_GET['tab'] == "general" || $_GET['tab'] == "" || !isset($_GET['tab'])) {
        $conf_title = "General Config Options";
        $divcon_general = "";
    } elseif ($_GET['tab'] == "news") {
        $conf_title =  "News Config Options";
        $divcon_news = "";
    } elseif ($_GET['tab'] == "comments") {
        $conf_title =  "Comments Config Options";
        $divcon_comments = "";
    }
    echo "<form action=\"$PHP_SELF\" method=post>
<table cellpadding=0 cellspacing=0 width=650 height=1 class=grayborder>
  <tr>
    <td>
<table border=0 cellpadding=10 cellspacing=0 width=100% height=1 >
  <tr id=tablist>
    <td><a href=\"$PHP_SELF?mod=options&action=syscon&tab=general&rand=".time()."\">General</a></td>
    <td><a href=\"$PHP_SELF?mod=options&action=syscon&tab=news&rand=".time()."\">News</a></td>
    <td><a href=\"$PHP_SELF?mod=options&action=syscon&tab=comments&rand=".time()."\">Comments</a></td>
    <th width=100% align=right><b>$conf_title</b></font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th></tr></table></td></tr><tr><td>
<div style=\"$divcon_general\">
<table border=0 cellpadding=0 cellspacing=0 width=650 height=1>";
    $i=1;
    // GENERAL Config

    showRow("Full URL to CuteNews Directory", "example: http://yoursite.com/cutenews", "<input type=text style=\"text-align: center;\"  name='save_con[http_script_dir]' value='$display_http_script_dir' size=40>");
    showRow("CuteNews Skin", "you can download more from our website", makeDropDown($sys_con_skins_arr, "save_con[skin]", "$config_skin"));
    showRow("Smilies", "separate them with commas (<b>,</b>)", "<input type=text style=\"text-align: center;\"  name='save_con[smilies]' value=\"$config_smilies\" size=40>");
    showRow("Time Zone", "adjusts time in add/edit news and when displaying", makeDropDown(array((ceil((date("Z")-date("Z"))/60)-720)=>"GMT -12:00",(ceil((date("Z")-date("Z"))/60)-660)=>"GMT -11:00",(ceil((date("Z")-date("Z"))/60)-600)=>"GMT -10:00",(ceil((date("Z")-date("Z"))/60)-540)=>"GMT -09:00",(ceil((date("Z")-date("Z"))/60)-480)=>"GMT -08:00",(ceil((date("Z")-date("Z"))/60)-420)=>"GMT -07:00",(ceil((date("Z")-date("Z"))/60)-360)=>"GMT -06:00",(ceil((date("Z")-date("Z"))/60)-300)=>"GMT -05:00",(ceil((date("Z")-date("Z"))/60)-240)=>"GMT -04:00",(ceil((date("Z")-date("Z"))/60)-180)=>"GMT -03:00",(ceil((date("Z")-date("Z"))/60)-120)=>"GMT -02:00",(ceil((date("Z")-date("Z"))/60)-060)=>"GMT -01:00",(ceil((date("Z")-date("Z"))/60)+000)=>"GMT +-0:00",(ceil((date("Z")-date("Z"))/60)+060)=>"GMT +01:00",(ceil((date("Z")-date("Z"))/60)+120)=>"GMT +02:00",(ceil((date("Z")-date("Z"))/60)+180)=>"GMT +03:00",(ceil((date("Z")-date("Z"))/60)+240)=>"GMT +04:00",(ceil((date("Z")-date("Z"))/60)+300)=>"GMT +05:00",(ceil((date("Z")-date("Z"))/60)+360)=>"GMT +06:00",(ceil((date("Z")-date("Z"))/60)+420)=>"GMT +07:00",(ceil((date("Z")-date("Z"))/60)+480)=>"GMT +08:00",(ceil((date("Z")-date("Z"))/60)+540)=>"GMT +09:00",(ceil((date("Z")-date("Z"))/60)+600)=>"GMT +10:00",(ceil((date("Z")-date("Z"))/60)+660)=>"GMT +11:00",(ceil((date("Z")-date("Z"))/60)+720)=>"GMT +12:00"), "save_con[date_adjust]", "$config_date_adjust"));
    showRow("Use Avatars", "if not, the avatar URL field wont be shown", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[use_avatar]", "$config_use_avatar"));
    showRow("User Image Directories", "if yes, users keep their images in their own image directories", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[user_image_upload]", "$config_user_image_upload"));
    showRow("Users Can Delete Images", "if yes, users can delete images", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[user_image_delete]", "$config_user_image_delete"));
    showRow("Admin Email", "used as sender of mail, if blank mail will be from webmaster@yourdomain", "<input type=text style=\"text-align: center;\"  name='save_con[mail_admin_address]' value='$config_mail_admin_address' size=40>");
    showRow("Allow New User Registration", "allow visitors to register as new users", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[register_allow]", "$config_register_allow"));
    showRow("Registration Level", "the level that users register as", makeDropDown(array("4"=>"Commenter","3"=>"Journalist","2"=>"Editor","1"=>"Administrator"), "save_con[register_level]", "$config_register_level"));
    showRow("Allow Multiple Users per Email Address", "allow multiple users to use the same email address", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[register_multimail]", "$config_register_multimail"));
    showRow("Send Password In Email", "send password in an email (if no the script will ask for password)", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[register_mailpass]", "$config_register_mailpass"));
    showRow("Notify Admin of New Users", "send an email to the admin when a new user signs up", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[register_mailadmin]", "$config_register_mailadmin"));
    showRow("Username/Password Filter", "separate with commas (admin,owner)", "<input type=text style=\"text-align: center;\"  name='save_con[register_filter]' value=\"$config_register_filter\" size=40>");

    // SEPARATOR
    echo "</table></div><div style=\"$divcon_news\"><table border=0 cellpadding=0 cellspacing=0 width=650 height=1>";
    $i=1;
    // NEWS Config

    showRow("Time Format For News", "view help for time formatting <a href=\"http://www.php.net/manual/en/function.date.php\" target=\"_blank\">here</a>", "<input type=text style=\"text-align: center;\"  name='save_con[timestamp_active]' value='$config_timestamp_active' size=40>");
    showRow("Time Format For Archive List", "view help for time formatting <a href=\"http://www.php.net/manual/en/function.date.php\" target=\"_blank\">here</a>", "<input type=text style=\"text-align: center;\"  name='save_con[timestamp_archive]' value='$config_timestamp_archive' size=40>");
    showRow("Time Format For ArchiveHeader", "view help for time formatting <a href=\"http://www.php.net/manual/en/function.date.php\" target=\"_blank\">here</a>", "<input type=text style=\"text-align: center;\"  name='save_con[dateheader_archive]' value='$config_dateheader_archive' size=40>");
    showRow("Reverse News", "if yes, older news will be shown on the top", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[reverse_active]", "$config_reverse_active"));
    showRow("Show Archive Start/End Date", "show archives by start time, end time, or both", makeDropDown(array("b"=>"Both","s"=>"Start","e"=>"End"), "save_con[format_archive]", "$config_format_archive"));
    showRow("Prospective Posting", "news in the future does not display until its date", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[prospective_posting]", "$config_prospective_posting"));
    showRow("Allow Short Story", "if no, short story will be hidden in add/edit news (output will be full story)", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[allow_short]", "$config_allow_short"));
    showRow("Allow Full Story", "if no, full story will be hidden in add/edit news (output will be short story)", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[allow_full]", "$config_allow_full"));
    showRow("Preferred Story", "to prevent disabling of both of the options above, one must be chosen as the default to always show", makeDropDown(array("short"=>"Short","full"=>"Full"), "save_con[short_full]", "$config_short_full"));
    showRow("Short Story Autogen Length", "truncate full story to X characters and use for short story (0 to disable)", "<input type=text style=\"text-align: center;\"  name='save_con[max_story_length]' value=\"$config_max_story_length\" size=10>");
    showRow("Archive User Level", "minimum level for users to archive news", makeDropDown(array("1"=>"Administrator","2"=>"Editor","3"=>"Journalist"), "save_con[archive_level]", "$config_archive_level"));
    showRow("HTML In Articles", "force on or off or let the user decide when posting", makeDropDown(array("2"=>"User Option","1"=>"Force On","0"=>"Force Off"), "save_con[allow_html_articles]", "$config_allow_html_articles"));
    showRow("Show Full Story In PopUp", "full Story will be opened in PopUp window", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[full_popup]", "$config_full_popup"));
    showRow("Settings for Full Story PopUp", "only if 'Show Full Story In PopUp' is enabled", "<input type=text style=\"text-align: center;\"  name='save_con[full_popup_string]' value=\"$config_full_popup_string\" size=40>");
    showRow("Show Comments When Showing Full Story", "if yes, comments will be shown under the story", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[show_comments_with_full]", "$config_show_comments_with_full"));
    showRow("Show Prev/Next on First/Last pages", "show previous pagination link on first page and next pagination link on last page", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[prevnext_firstlast]", "$config_prevnext_firstlast"));

    // SEPARATOR
    echo "</table></div><div style=\"$divcon_comments\"><table border=0 cellpadding=0 cellspacing=0 width=650 height=1>";
    $i=1;
    // COMMENTS Config

    showRow("Time Format For Comments", "view help for time formatting <a href=\"http://www.php.net/manual/en/function.date.php\" target=\"_blank\">here</a>", "<input type=text style=\"text-align: center;\"  name='save_con[timestamp_comment]' value='$config_timestamp_comment' size=40>");
    showRow("Reverse Comments", "if yes, newest comments will be shown on the top", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[reverse_comments]", "$config_reverse_comments"));
    showRow("Max. Length of Comments in Characters", "enter <b>0</b> to disable checking", "<input type=text style=\"text-align: center;\"  name='save_con[comment_max_long]' value='$config_comment_max_long' size=10>");
    showRow("Comments Per Page (Pagination)", "enter <b>0</b> or leave empty to disable pagination", "<input type=text style=\"text-align: center;\"  name='save_con[comments_per_page]' value='$config_comments_per_page' size=10>");
    showRow("Only Registered Users Can Post Comments", "if yes, only registered users can post comments", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[only_registered_comment]", "$config_only_registered_comment"));
    showRow("Allow Mail Field to Act and as URL Field", "visitors will be able to put their site URL insted of mail", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[allow_url_instead_mail]", "$config_allow_url_instead_mail"));
    showRow("Auto Wrap Comments", "any word that is longer than this will be wrapped", "<input type=text style=\"text-align: center;\"  name='save_con[auto_wrap]' value=\"$config_auto_wrap\" size=10>");
    showRow("Comments Flood Protection", "in seconds; 0 = no protection", "<input type=text style=\"text-align: center;\"  name='save_con[flood_time]' value=\"$config_flood_time\" size=10>");
    showRow("Disable Comments", "force on or off or let the user decide when posting", makeDropDown(array("2"=>"User Option","1"=>"Force On","0"=>"Force Off"), "save_con[allow_disable_comments]", "$config_allow_disable_comments"));
    showRow("MailOnComment", "force on or off or let the user decide when posting", makeDropDown(array("2"=>"User Option","1"=>"Force On","0"=>"Force Off"), "save_con[mail_allow_comments]", "$config_mail_allow_comments"));
    showRow("Admin MailOnComment", "admin should always recieve mail when any user comments (overrides above)", makeDropDown(array("0"=>"No","1"=>"Only Active News","2"=>"All News"), "save_con[mail_admin_comments]", "$config_mail_admin_comments"));
    showRow("Show Comments In PopUp", "comments will be opened in PopUp window", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[comments_popup]", "$config_comments_popup"));
    showRow("Settings for Comments PopUp", "only if 'Show Comments In PopUp' is enabled", "<input type=text style=\"text-align: center;\"  name=\"save_con[comments_popup_string]\" value=\"$config_comments_popup_string\" size=40>");
    showRow("Show Full Story When Showing Comments", "if yes, comments will be shown under the story", makeDropDown(array("yes"=>"Yes","no"=>"No"), "save_con[show_full_with_comments]", "$config_show_full_with_comments"));

    // SEPARATOR
    echo "</table></div></td></tr></table>
<table align=center class=grayborder style=\"border-top: 0px;\" cellpadding=0 cellspacing=0 width=645 height=1 ><tr><td>
<input type=hidden name='save_con[path_image_upload]' value='./data/upimages'>
<input type=hidden name=mod value=options><input type=hidden name=action value=dosavesyscon>";
    showRow("", "Please Save before going to another tab.", "<br /><input type=submit value=\"     Save Changes     \" accesskey=\"s\">");
    echo "</td></tr></table></form>";
    echofooter();
}
// ********************************************************************************
// Save System Configuration
// ********************************************************************************
elseif ($action == "dosavesyscon") {
    if ($member_db[1] != 1) {
        msg("error", "Access Denied", "You don't have permission for this section");
    }
    $handler = fopen("./data/config.php", "w");
    fwrite($handler, "<?PHP \n\n//System Configurations (Auto Generated file)\n\n");
    foreach ($save_con as $name=>$value) {
        fwrite($handler, "\$config_$name = \"".htmlspecialchars($value)."\";\n\n");
    }
    fwrite($handler, "?>");
    fclose($handler);

    include("./skins/".$save_con["skin"].".skin.php");
    msg("info", "Configurations Saved", "The System Configurations were successfully saved.");
}
