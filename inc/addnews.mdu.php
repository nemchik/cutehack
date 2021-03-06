<?php

if ($member_db[1] > 3) {
    msg("error", "Access Denied", "You don't have permission to add news");
}
// <!-- Start ModifyTime v2.0 -->
$months = array('01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December');
if ($config_prospective_posting == "yes") {
    $prospective_message = "<font class=\"smallesttext\">(posts in the future will not be displayed until after their dates)</font>";
} else {
    $prospective_message = "";
}
$current_time = (ceil(time()-date("Z"))+($config_date_adjust*60));
$offset = timeoffset($config_date_adjust);
// <!-- End ModifyTime v2.0 -->
if ($action == "addnews") {
    $cat_lines = file("./data/category.db.php");
    echoheader("addnews", "Add News");

    // XFields v2.1 - addblock
    $xfieldsaction = "categoryfilter";
    include("xfields.mdu.php");
    // XFields v2.1 - End addblock

    // <!-- Start ModifyTime v2.0 -->
    $month = "<select size=\"1\" name=\"edit_month\">";
    for ($i=1;$i<=12;$i++) {
        if ($i < 10) {
            $i = '0'.$i;
        }
        if (date("m", $current_time) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $month .= "<option value=\"$i\"$selected>".$months[$i]."</option>";
    }
    $month .= "</select>";
    $day = "<select size=\"1\" name=\"edit_day\">";
    for ($i=1;$i<=31;$i++) {
        if (date("j", $current_time) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $day .= "<option value=\"$i\"$selected>$i</option>";
    }
    $day .= "</select>";
    $year = "<select size=\"1\" name=\"edit_year\">";
    for ($i=(date("Y")-30);$i<=(date("Y")+30);$i++) {
        if (date("Y", $current_time) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $year .= "<option value=\"$i\"$selected>$i</option>";
    }
    $year .= "</select>";
    $hour = "<select size=\"1\" name=\"edit_hour\">";
    for ($i=0;$i<=23;$i++) {
        if (date("G", $current_time) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $hour .= "<option value=\"$i\"$selected>$i</option>";
    }
    $hour .= "</select>";
    $min = "<select size=\"1\" name=\"edit_min\">";
    for ($i=0;$i<=59;$i++) {
        if ($i < 10) {
            $i = '0'.$i;
        }
        if (date("i", $current_time) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $min .= "<option value=\"$i\"$selected>$i</option>";
    }
    $min .= "</select>";
    $sec = "<select size=\"1\" name=\"edit_sec\">";
    for ($i=0;$i<=59;$i++) {
        if ($i < 10) {
            $i = '0'.$i;
        }
        if (date("s", $current_time) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $sec .= "<option value=\"$i\"$selected>$i</option>";
    }
    $sec .= "</select>";
    // <!-- End ModifyTime v2.0 -->

    echo "
    <SCRIPT LANGUAGE=\"JavaScript\">
	function preview(){
    if(document.addnews.title.value == ''){ alert('Your article must have at least Title'); }
    if(document.addnews.short_story.value == '' && document.addnews.full_story.value == ''){ alert('Your article must have story'); }
    else{
	    dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1')
		document.addnews.mod.value='preview';document.addnews.target='prv'
		document.addnews.submit();dd.focus()
		setTimeout(\"document.addnews.mod.value='addnews';document.addnews.target='_self'\",500)
	}
    }
    onload=focus;function focus(){document.forms[0].title.focus();}
    </SCRIPT>

	<table border=0 cellpadding=0 cellspacing=0 width=\"654\" >
    <form method=post name=addnews action=\"$PHP_SELF\">
<!-- Start ModifyTime v2.0 -->
       <tr>
       <td valign=middle width=\"75\">
       Date:
       <td width=\"571\" colspan=\"7\">
       $month&nbsp;$day&nbsp;$year&nbsp;$offset
       </tr>
       <tr>
       <td valign=middle width=\"75\">
       Time:
       <td width=\"571\" colspan=\"7\">
       $hour:$min:$sec &nbsp; $prospective_message
       </tr>
<!-- End ModifyTime v2.0 -->
    <tr>
	<td width=\"75\">
	Title
	<td width=\"575\" colspan=\"2\">
	<input type=text size=\"55\" name=\"title\" tabindex=1>
	</tr>";

    if ($config_use_avatar == "yes") {
        echo"<tr>
		<td width=\"75\">
		Avatar URL
		<td width=\"575\" colspan=\"2\">
		<input tabindex=2 type=text size=\"42\" value=\"$member_db[8]\" name=\"manual_avatar\" >&nbsp;&nbsp;&nbsp;<font class=\"smallesttext\">(optional)</font>
		</tr>";
    }

    if ((count($cat_lines)-1) > 0) {
        echo"<tr>
		<td width=\"75\">
		Category
		<td width=\"575\" colspan=\"2\">
		<select name=category tabindex=3 id=\"category\" onchange=\"onCategoryChange(this.value)\">>";
        foreach ($cat_lines as $null => $single_line) {
            $cat_arr = explode("|", $single_line);
            if ($member_db[1] <= $cat_arr[3]) {
                $if_is_selected = "";
                if ($cat_arr[0] == 1) {
                    $if_is_selected = " selected ";
                }
                if ($category == $cat_arr[0]) {
                    $if_is_selected = " selected ";
                }
                echo"<option $if_is_selected value=\"$cat_arr[0]\">$cat_arr[1]</option>\n";
            }
        }
        echo"</select></tr>";
    }
    // XFields v2.1 - addblock
    $xfieldsaction = "list";
    $xfieldsadd = true;
    include("xfields.mdu.php");
    // XFields v2.1 - End addblock
    if ($config_allow_short == "yes"  || $config_short_full == "short") {
        echo"<tr>
	<td width=\"75\" valign=\"top\">
	<br />Short Story";
        if ($config_allow_full == "yes") {
            echo"<br /><font class=\"smallesttext\">(leave blank for shortened full story)</font>";
        }
        echo"
	<td>
	<textarea rows=\"8\" cols=\"74\" name=\"short_story\" tabindex=4></textarea>
	<td width=\"108\" valign=\"top\">
	<p align=\"center\"><a href=# onclick=\"window.open('$PHP_SELF?&mod=images&action=quick&area=short_story', '_Addimage', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=360');return false;\" target=\"_Addimage\"><br />
	[insert image]</a><br />
	<a href=# onclick=\"window.open('$PHP_SELF?&mod=about&action=cutecode&target=short_story', '_CuteCode', 'HEIGHT=280,resizable=yes,scrollbars=yes,WIDTH=360');return false;\" target=\"_Addimage\">[quick tags]</a><br />
	<br />

	<script>
	function insertext(text,area){
	if(area==\"short\"){document.addnews.short_story.focus(); document.addnews.short_story.value=document.addnews.short_story.value +\" \"+ text; document.addnews.short_story.focus() }
	if(area==\"full\") {document.addnews.full_story.focus(); document.addnews.full_story.value=document.addnews.full_story.value +\" \"+ text; document.addnews.full_story.focus()}
	}
    </script>";

        echo insertSmilies('short', 4);
    } else {
        echo"<input type=\"hidden\" name=\"short_story\" value=\"\">";
    }
    if ($config_allow_full == "yes" || $config_short_full == "full") {
        echo"
    </tr>

    <tr>
	<td width=\"75\" valign=\"top\">
	<br />Full Story";
        if ($config_allow_short == "yes") {
            echo"<br /><font class=\"smallesttext\">(optional)</font>";
        }
        echo"
	<td>
	<textarea rows=\"12\" cols=\"74\" name=\"full_story\" tabindex=5></textarea>
	<td width=\"108\" valign=\"top\">
	<p align=\"center\"><br />
	<a href=# onclick=\"window.open('$PHP_SELF?mod=images&action=quick&area=full_story', '_Addimage', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=360');return false;\" target=\"_Addimage\">[insert image]</a><br />
	<a href=# onclick=\"window.open('$PHP_SELF?&mod=about&action=cutecode&target=full_story', '_Addimage', 'HEIGHT=280,resizable=yes,scrollbars=yes,WIDTH=360');return false;\" target=\"_CuteCode\">[quick tags]</a><br />
	<br />";

        echo insertSmilies('full', 4);
    } else {
        echo"<input type=\"hidden\" name=\"full_story\" value=\"\">";
    }
    echo"
    </tr>

	<tr>
	<td width=\"75\">
	<td width=\"575\" colspan=\"2\">
	<input type=submit value=\"     Add News     \" accesskey=\"s\">&nbsp;
    <input type=button value=\"Preview\" onClick=\"preview()\" accesskey=\"p\">&nbsp; <a href=\"javascript:ShowOrHide('options','')\">[options]</a>
	</tr>

	<tr id='options' style='display:none;'>
	<td width=\"75\"><br />Options
	<td width=\"575\" colspan=\"4\">
    <br />
";
    if ($config_allow_disable_comments == "2") {
        echo "
<span style=\"white-space:nowrap\">&nbsp;&nbsp;<input class=checkbox type=checkbox value=\"1\" name=\"disable_comments\"> Disable Comments</span>
";
    } else {
        echo"<input type=\"hidden\" name=\"disable_comments\" value=\"$config_allow_disable_comments\">";
    }
    if ($config_mail_allow_comments == "2") {
        if (trim($member_db[10]) == 1) {
            $mail_com = "checked";
        }
        echo "
<span style=\"white-space:nowrap\">&nbsp;&nbsp;<input class=checkbox type=checkbox value=\"1\" name=\"mail_on_comment\" $mail_com> Recieve mail when this post is commented on</span>
";
    } else {
        echo"<input type=\"hidden\" name=\"mail_on_comment\" value=\"$config_mail_allow_comments\">";
    }
    echo "
<span style=\"white-space:nowrap\">&nbsp;&nbsp;<input class=checkbox type=checkbox value=\"1\" name=\"if_convert_new_lines\" checked> Convert new lines to &lt;br /&gt;</span>
";
    if ($config_allow_html_articles == "2") {
        echo "
<span style=\"white-space:nowrap\">&nbsp;&nbsp;<input class=checkbox type=checkbox value=\"1\" name=\"if_use_html\" checked> Use HTML in this article</span>
";
    } else {
        echo"<input type=\"hidden\" name=\"if_use_html\" value=\"$config_allow_html_articles\">";
    }
    echo "
    </tr>

    <input type=hidden name=mod value=addnews>
	<input type=hidden name=action value=doaddnews>
    </form>
	</table>";
    echofooter();
}
// ********************************************************************************
// Do add News to news.db.php
// ********************************************************************************
elseif ($action == "doaddnews") {
    if ($short_story == "") {
        $short_story = $full_story;
        if (strlen($short_story) > $config_max_story_length && $config_max_story_length > 0) {
            $short_story = substr(trim($short_story), 0, $config_max_story_length);
            $short_story = substr($short_story, 0, strlen($short_story)-strpos(strrev($short_story), " "));
            $short_story .= '&hellip;';
        }
    }

    if ($if_convert_new_lines    == "1") {
        $n_to_br        = true;
    }
    if ($if_use_html                == "1") {
        $use_html    = true;
    }

    $full_story  = replace_news("add", $full_story, $n_to_br, $use_html);
    $short_story = replace_news("add", $short_story, $n_to_br, $use_html);
    $title         = replace_news("add", $title, true, $use_html);

    if (trim($title) == "" or !$title) {
        msg("error", "Error !!!", "The title can not be blank.", "javascript:history.go(-1)");
    }
    if ((trim($short_story) == "" or !$short_story) && (trim($full_story) == "" or !$full_story)) {
        msg("error", "Error !!!", "There must be a story.", "javascript:history.go(-1)");
    }

    // <!-- Start ModifyTime v2.0 -->
    $added_time = (strtotime("$edit_day ".$months[$edit_month]." $edit_year $edit_hour:$edit_min:$edit_sec")-($config_date_adjust*60));
    // <!-- End ModifyTime v2.0 -->
    if ($member_db[7] == 1) {
        $added_by_email = $member_db[5];
    } else {
        $added_by_email = "none";
    }


    // Save The News Article In Active_News_File
    $all_db = file("./data/news.db.php");
    foreach ($all_db as $null => $news_line) {
        if (eregi("<\?", $news_line)) {
            continue;
        }
        $news_arr = explode("|", $news_line);
        if ($news_arr[0] == $added_time) {
            $added_time++;
        }
    }
    // XFields v2.1 - addblock
    $xfieldsid = $added_time;
    $xfieldsaction = "init";
    include("xfields.mdu.php");
    $xfieldsaction = "save";
    include("xfields.mdu.php");
    // XFields v2.1 - End addblock
    if ($disable_comments != 1) {
        $disable_comments = 0;
    }
    if ($mail_on_comment != 1) {
        $mail_on_comment = 0;
    }
    if ($config_mail_allow_comments != "2") {
        $mail_on_comment = $config_mail_allow_comments;
    }
    $all_db[] = "$added_time|$member_db[2]|$title|$short_story|$full_story|$manual_avatar|$category|$disable_comments|$mail_on_comment||\n";
    rsort($all_db);
    reset($all_db);
    $news_file = fopen("./data/news.db.php", "wb");
    fwrite($news_file, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($all_db as $null => $line) {
        if (eregi("<\?", $line)) {
            continue;
        }
        fwrite($news_file, "$line");
    }
    fclose($news_file);

    // Add Blank Comment In The Active_Comments_File
    $old_com_db = file("./data/comments.db.php");
    $new_com_db = fopen("./data/comments.db.php", "w");
    // <!-- Start ModifyTime v2.0 -->
    fwrite($new_com_db, "$added_time|>|\n");
    rsort($old_com_db);
    reset($old_com_db);
    foreach ($old_com_db as $null => $line) {
        if (eregi("<\?", $line)) {
            continue;
        }
        fwrite($new_com_db, "$line");
    }
    // <!-- End ModifyTime v2.0 -->
    fclose($new_com_db);

    // Incrase By 1 The Number of Written News for Current User
    $old_user_db = file("./data/users.db.php");
    $new_user_db = fopen("./data/users.db.php", w);
    foreach ($old_user_db as $null => $old_user_db_line) {
        $old_user_db_arr = explode("|", $old_user_db_line);
        if ($username!=$old_user_db_arr[2]) {
            fwrite($new_user_db, "$old_user_db_line");
        } else {
            $countplus = $old_user_db_arr[6]+1;
            fwrite($new_user_db, "$old_user_db_arr[0]|$old_user_db_arr[1]|$old_user_db_arr[2]|$old_user_db_arr[3]|$old_user_db_arr[4]|$old_user_db_arr[5]|$countplus|$old_user_db_arr[7]|$old_user_db_arr[8]|$old_user_db_arr[9]|$old_user_db_arr[10]||\n");
        }
    }
    fclose($new_user_db);

    msg("info", "News added", "The news item was successfully added.");
}
