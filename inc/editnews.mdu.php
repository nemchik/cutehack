<?php

if ($member_db[1] > 3) {
    msg("error", "Access Denied", "You don't have permission to edit news");
}
// <!-- Start ModifyTime v2.0 -->
$months = array('01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December');
if ($config_prospective_posting == "yes") {
    $prospective_message = "<font class=\"smallesttext\">(posts in the future will not be displayed until after their dates)</font>";
} else {
    $prospective_message = "";
}
$offset = timeoffset($config_date_adjust);
// <!-- End ModifyTime v2.0 -->
// ********************************************************************************
// List all news available for editing
// ********************************************************************************
if ($action == "list") {
    echoheader("editnews", "Edit News");

    $cat_lines = @file("./data/category.db.php");
    foreach ($cat_lines as $null => $single_line) {
        if (eregi("<\?", $single_line)) {
            continue;
        }
        $cat_arr = explode("|", $single_line);
        if ($member_db[1] <= $cat_arr[3]) {
            $cat[$cat_arr[0]] = $cat_arr[1];
        }
    }

    // How Many News to show on one page
    if ($news_per_page == "") {
        $news_per_page = 21;
    }

    $all_db = array();
    if ($source == "") {
        $all_db = file("./data/news.db.php");
    } else {
        $all_db = file("./data/archives/${source}.news.arch.php");
    }



// choose only needed news items
if ($category != "" or $author != "" or $member_db[1] == 3) {
    foreach ($all_db as $null => $raw_line) {
        $raw_arr = explode("|", $raw_line);
        if (($category == "" or $raw_arr[6] == $category) and ($author == "" or $raw_arr[1] == $author) and($member_db[1] != 3 or $raw_arr[1] == $member_db[2])) {
            $all_db_tmp[] = $raw_line;
        }
    }
    $all_db = $all_db_tmp;
}


// Prelist Entries
    $flag = 1;
    if ($start_from == "0") {
        $start_from = "";
    }
    $i = $start_from;
    $entries_showed = 0;

    if (!empty($all_db)) {
        foreach ($all_db as $null => $line) {
            if (eregi("<\?", $line)) {
                continue;
            }
            if ($j < $start_from) {
                $j++;
                continue;
            }
            $i++;

            $item_db = explode("|", $line);
            $itemdate = date("d/m/y", ($item_db[0]+($config_date_adjust*60)));

            if ($flag == 1) {
                $bg="class=altern1";
                $flag = 0;
            } else {
                $bg = "class=altern2";
                $flag = 1;
            }

            if (strlen($item_db[2]) > 74) {
                $title = substr($item_db[2], 0, 70)." ...";
            }
            $title = stripslashes(preg_replace(array("'\|'", "'\"'", "'\''"), array("I", "&quot;", "&#039;"), $item_db[2]));
            $entries .= "<tr>

                <td height=18 $bg>
                 <!-- ID: $item_db[0]--><a title='EDIT: $item_db[2]' href=\"$PHP_SELF?mod=editnews&action=editnews&id=$item_db[0]&source=$source\">$title</a>
                 <td height=18 $bg align=right>";
            $count_comments = countComments($item_db[0], $source);
            if ($count_comments == 0) {
                $entries .= "<font color=gray>$count_comments</font>";
            } else {
                $entries .= "$count_comments";
            }

            $entries .= "<td height=18 $bg align=right>&nbsp;<td height=18 $bg align=right>";
            $article_counter = file("$cutepath/data/counter.db.php");
            foreach ($article_counter as $null => $counter_line) {
                if (eregi("<\?", $counter_line)) {
                    continue;
                }
                $count_arr = explode("|", $counter_line);
                if ($count_arr[0] == $item_db[0]) {
                    $count_views = $count_arr[1];
                }
            }
            if (!$count_views || $count_views == "") {
                $count_views = 0;
            }
            if ($count_comments == 0) {
                $entries .= "<font color=gray>$count_views</font>";
            } else {
                $entries .= "$count_views";
            }

            $entries .= "&nbsp;&nbsp;&nbsp;&nbsp;<td height=18 $bg>&nbsp;&nbsp;&nbsp;";

            if ($item_db[6] == "") {
                $my_cat = "<font color=gray>---</font>";
            } elseif ($cat[$item_db[6]] == "") {
                $my_cat = "<font class=error>ID <b>$item_db[6]</b></font>";
            } else {
                $my_cat = $cat[$item_db[6]];
            }

            $entries .= "$my_cat&nbsp;<td height=18 $bg>
                 $itemdate
                 <td height=18 $bg>
                       $item_db[1]

                       <td align=center $bg><input name=\"selected_news[]\" value=\"{$item_db[0]}\" class=checkbox type='checkbox'>

             </tr>
            ";
            $entries_showed ++;

            if ($i >= $news_per_page + $start_from) {
                break;
            }
        }//foreach news line
    }
// End prelisting



    $all_count_news = count($all_db)-1;
    $all_count_news_journalists = count($all_db);
    if ($category != "") {
        $cat_msg = "Category: <b>$cat[$category]</b>;";
    }
    if ($source != "") {
        $news_lines = file("./data/archives/$source.news.arch.php");
        $count = count($news_lines)-1;
        $last = $count-1;
        $first_news_arr = explode("|", $news_lines[$last]);
        $last_news_arr        = explode("|", $news_lines[1]);
        $first_timestamp = ($first_news_arr[0]+($config_date_adjust*60));
        $last_timestamp         = ($last_news_arr[0]+($config_date_adjust*60));
        $source_msg = "Archive: <b>". date("d M Y", $first_timestamp) ." - ". date("d M Y", $last_timestamp) ."</b>;";
    }






///////////////////////////////////////////
// Options Bar
echo"
        <table class=panel border=0 cellpadding=0 cellspacing=0 width=99% >
        <tr>
          <td align=center>
         Showing <b>$entries_showed</b> articles from total <b>";

    if ($member_db[1] != 3) {
        echo"$all_count_news";
    } else {
        echo"$all_count_news_journalists";
    }
    echo"</b>$cat_msg $source_msg</td>
	  </table>
	  <table border=0 cellpadding=0 cellspacing=0 width=99% >

    <tr>

          <td colspan=\"2\" >





<!--SHOW OPTIONS-->

<form class=compact action=\"$PHP_SELF?mod=editnews&action=list\" method=POST name=options_bar>
<table class=panel width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
<tr>
<td height=\"1\" class=panel width=\"100%\" align=\"right\" colspan=\"3\"> </td>
</tr>
<tr ><br />
<td height=\"1\" width=\"25%\" align=\"center\">Source</td>
<td height=\"1\" width=\"25%\" align=\"center\">Category</td>";
    if ($member_db[1] != 3) {
        echo "<td height=\"1\" width=\"20%\" align=\"center\">Author</td>";
    } else {
        echo"<td></td>";
    }
    echo"<td height=\"1\" width=\"10%\" align=\"center\">Display</td>
</tr>
<tr>
<td height=\"1\" width=\"25%\" align=\"center\">
<select name=\"source\" size=\"1\"><option value=\"\">- Active News -</option>";

    if (!$handle = opendir("./data/archives")) {
        die("<center>Can not open directory ./data/archives ");
    }
    while (false !== ($file = readdir($handle))) {
        if ($file != "." and $file != ".." and !is_dir("./data/archives/$file") and eregi("news.arch.php", $file)) {
            $file_arr = explode(".", $file);
            $id                  = $file_arr[0];
            @chmod("./data/archives/$id.news.arch.php", 0777);
            @chmod("./data/archives/$id.comments.arch.php", 0777);
            $news_lines = file("./data/archives/$file");
            $count = count($news_lines)-1;
            $last = $count-1;
            $first_news_arr = explode("|", $news_lines[$last]);
            $last_news_arr        = explode("|", $news_lines[1]);

            $first_timestamp = ($first_news_arr[0]+($config_date_adjust*60));
            $last_timestamp         = ($last_news_arr[0]+($config_date_adjust*60));

            $arch_date = date("d M Y", $first_timestamp) ." - ". date("d M Y", $last_timestamp);
            $ifselected = "";
            if ($source == $file_arr[0]) {
                $ifselected = "selected";
            }
            echo "<option $ifselected value=\"$file_arr[0]\">Archive: $arch_date ($count)</option>";
        }
    }
    closedir($handle);

    echo"</select>

</td>

<td height=\"1\" width=\"25%\" align=\"center\">
    <select name=\"category\" ><option selected value=\"\">- All -</option>";

    $cat_lines = file("./data/category.db.php");
    foreach ($cat_lines as $null => $single_line) {
        if (eregi("<\?", $single_line)) {
            continue;
        }
        $cat_arr = explode("|", $single_line);
        if ($member_db[1] <= $cat_arr[3]) {
            $ifselected = "";
            if ($category == $cat_arr[0]) {
                $ifselected = "selected";
            }

            if (strlen($cat_arr[1]) > 25) {
                $cat_arr['disp'] = substr($cat_arr[1], 0, 25 - 3) . '&hellip;';
            } else {
                $cat_arr['disp'] = $cat_arr[1];
            }
            echo"<option $ifselected value=\"$cat_arr[0]\">".$cat_arr['disp']."</option>\n";
        }
    }

    echo"</select>

";

    if ($member_db[1] != 3) {
        echo"
<td height=\"1\" width=\"20%\" align=\"center\">
    <select name=author size=\"1\"><option value=\"\">- Any -</option>";
        $user_lines = file("./data/users.db.php");
        foreach ($user_lines as $null => $single_line) {
            if (!eregi("<\?", $single_line)) {
                $user_arr = explode("|", $single_line);
                $ifselected = "";
                if ($user_arr[1] != 4 || $user_arr[1] != 5) {
                    if ($author == $user_arr[2]) {
                        $ifselected = "selected";
                    }
                    if (strlen($user_arr[2]) > 15) {
                        $user_arr['disp'] = substr($user_arr[2], 0, 15 - 3) . '&hellip;';
                    } else {
                        $user_arr['disp'] = $user_arr[2];
                    }
                    echo"<option $ifselected value=\"$user_arr[2]\">".$user_arr['disp']."</option>\n";
                }
            }
        }
        echo"</select>
";
    } else {
        echo"
<td height=\"1\" width=\"20%\" align=\"center\">
</td>
";
    }

    echo"
<td height=\"1\" width=\"14%\" align=\"center\">
<input style=\"text-align: Center\" name=\"news_per_page\" value=\"$news_per_page\" type=text size=3>
</td>


<td height=\"1\" width=\"20%\" align=\"center\" rowspan=\"2\">
<input type=submit value=\"Show\">
</td>
</tr></form>
</table>

<!--SHOW OPTIONS-->




&nbsp;";

// End Options Bar
////////////////////////////////////////////////////////////////////////////////    Showing List of News
if ($entries_showed == 0) {
    echo"<table border=0 cellpadding=0 cellspacing=0 width=100% >
        <form method=post name=editnews>
        <td colspan=6 ><p style=\"border: solid black 1px;  margin: 22px 22px 22px 22px; padding: 4px 4px 4px 4px;\" align=center>- No articles found -</p>";
} else {
    echo<<<JSCRIPT
<script language='JavaScript' type="text/javascript">
<!--
function ckeck_uncheck_all() {
        var frm = document.editnews;
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
</script>
JSCRIPT;

    echo"<table border=0 cellpadding=0 cellspacing=0 width=99% >
        <form method=post name=editnews>
        <td width=312>
        Title
        <td width=65>
        Comments
        <td width=35 align=right>
        &nbsp;Views
        <td>
        &nbsp;
        <td width=150>
        &nbsp;Category

        <td width=58>
        &nbsp;Date

        <td width=78>
        Author

        <td width=21 align=center> <input style=\"border: 0px; background:transparent;\" type=checkbox class=checkbox name=master_box title=\"Check All\" onclick=\"javascript:ckeck_uncheck_all()\"> </a> ";
}
#####################################################################################################################
echo $entries;
#####################################################################################################################

if ($start_from > 0) {
    $previous = $start_from - $news_per_page;
    $npp_nav .= "<a href=\"$PHP_SELF?mod=editnews&action=list&start_from=$previous&category=$category&author=$author&source=$source&news_per_page=$news_per_page\"><< Previous</a>";
    $tmp = 1;
}

    if ((count($all_db)-1) > $i) {
        if ($tmp) {
            $npp_nav .= "&nbsp;&nbsp;||&nbsp;&nbsp;";
        }
        $how_next = (count($all_db)-1) - $i;
        if ($how_next > $news_per_page) {
            $how_next = $news_per_page;
        }
        $npp_nav .= "<a href=\"$PHP_SELF?mod=editnews&action=list&start_from=$i&category=$category&author=$author&source=$source&news_per_page=$news_per_page\">Next $how_next >></a>";
    }

    if ($entries_showed != 0) {
        echo<<<HTML
<tr>
<td colspan=8 align=right>&nbsp;
</tr>

<tr>
<td>
$npp_nav
<td colspan=8 align=right>

With selected:
<select name=action>
<option value="" selected>-- Choose Action --</option>
<option title="delete all selected news" value="mass_delete">Delete</option>
HTML;
        if ($config_allow_disable_comments == "2") {
            echo "<option title=\"enable or disable comment posting for selected news\" value=\"mass_ed_comments\">Enable/Disable Commenting</option>";
        }
        if ($config_mail_allow_comments == "2") {
            echo "<option title=\"enable or disable mail on comments for selected news\" value=\"mass_mail_comments\">Enable/Disable Mail on Comments</option>";
        }
        if ($member_db[1] <= $config_archive_level) {
            if ($source == "") {
                echo "<option title=\"make new archive with all selected news\" value=\"mass_archive\">Send to Archive</option>";
            } else {
                echo "<option title=\"bring selected back from archive\" value=\"mass_unarchive\">Make News Active</option>";
            }
        }
        echo "<option title=\"move all selected news to one category\" value=\"mass_move_to_cat\">Change Category</option>";
        if ($member_db[1] == "1") {
            echo "<option title=\"repair broken databases\" value=\"mass_repair\">Repair Broken Databases</option>";
        }
        echo <<<HTML
</select>
<input type=hidden name=source value="$source">
<input type=hidden name=mod value="massactions">
<input type=submit value=Go>
</tr>
HTML;
    }


    echo<<<HTML
</tr>
<tr>
<td  colspan=1>
</tr>
<tr>
<td colspan=7>
</tr>
</form></table>
HTML;

    echofooter();
}
// ********************************************************************************
// Edit News Article
// ********************************************************************************
elseif ($action == "editnews") {
    // Show The Article for Editing
        if ($source == "") {
            $all_db = file("./data/news.db.php");
        } else {
            $all_db = file("./data/archives/$source.news.arch.php");
        }
    $found = false;
    foreach ($all_db as $null => $line) {
        $item_db=explode("|", $line);
        if ($id == $item_db[0]) {
            $found = true;
            break;
        }
    }//foreach news line

        $have_perm = 0;
    if (($member_db[1] == 1) or ($member_db[1] == 2)) {
        $have_perm = 1;
    } elseif ($member_db[1] == 3 and $item_db[1] == $member_db[2]) {
        $have_perm = 1;
    }
    if (!$have_perm) {
        msg("error", "NO Access", "You dont have access for this action", "$PHP_SELF?mod=editnews&action=list");
    }

    if (!$found) {
        msg("error", "Error !!!", "The selected news item can <b>not</b> be found.");
    }

    $item_db['time'] = ($item_db[0]+($config_date_adjust*60));
    $newstime   = date("D, d F Y h:i:s", $item_db['time']);

    $item_db[2] = stripslashes(preg_replace(array("'\|'", "'\"'", "'\''"), array("I", "&quot;", "&#039;"), $item_db[2]));
    $item_db[3] = replace_news("admin", $item_db[3]);
    $item_db[4] = replace_news("admin", $item_db[4]);

    echoheader("editnews", "Edit News");

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
        if (date("m", $item_db['time']) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $month .= "<option value=\"$i\"$selected>".$months[$i]."</option>";
    }
    $month .= "</select>";
    $day = "<select size=\"1\" name=\"edit_day\">";
    for ($i=1;$i<=31;$i++) {
        if (date("j", $item_db['time']) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $day .= "<option value=\"$i\"$selected>$i</option>";
    }
    $day .= "</select>";
    $year = "<select size=\"1\" name=\"edit_year\">";
    for ($i=(date("Y")-30);$i<=(date("Y")+30);$i++) {
        if (date("Y", $item_db['time']) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $year .= "<option value=\"$i\"$selected>$i</option>";
    }
    $year .= "</select>";
    $hour = "<select size=\"1\" name=\"edit_hour\">";
    for ($i=0;$i<=23;$i++) {
        if (date("G", $item_db['time']) == $i) {
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
        if (date("i", $item_db['time']) == $i) {
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
        if (date("s", $item_db['time']) == $i) {
            $selected = " selected";
        } else {
            $selected = "";
        }
        $sec .= "<option value=\"$i\"$selected>$i</option>";
    }
    $sec .= "</select>";
// <!-- End ModifyTime v2.0 -->

    echo"
    <SCRIPT LANGUAGE=\"JavaScript\">
        function preview(){
        dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1')
        document.addnews.mod.value='preview';document.addnews.target='prv'
        document.addnews.submit();dd.focus()
        setTimeout(\"document.addnews.mod.value='editnews';document.addnews.target='_self'\",500)
        }
    function confirmDelete(url){
        var agree=confirm(\"Do you really want to permanently delete this article ?\");
        if (agree)
        document.location=url;
        }
        </SCRIPT>

    <form method=POST name=addnews action=\"$PHP_SELF\">
        <table border=0 cellpadding=0 cellspacing=0 width=\"654\" height=\"100%\" >
        <tr>
        <td valign=middle width=\"75\">
        Info.
        <td width=\"571\" colspan=\"6\">
        Posted on $newstime ($offset) by $item_db[1]
        </tr>

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
        <input type=hidden name=pass_time value=$item_db[0]>
	<!-- End ModifyTime v2.0 -->

        <tr>
        <td valign=middle width=\"75\" valign=\"top\">
        Title
        <td width=\"464\" colspan=\"3\">
        <input type=text name=title value=\"$item_db[2]\" size=55 tabindex=1>
    <td width=\"103\" valign=\"top\">
        </tr>";

    if ($config_use_avatar == "yes") {
        echo"
        <tr>
                <td valign=middle width=\"75\" valign=\"top\">
                Avatar URL
                <td width=\"464\" colspan=\"3\">
                <input type=text name=editavatar value=\"$item_db[5]\" size=42 tabindex=2>&nbsp;&nbsp;&nbsp;<font class=\"smallesttext\">(optional)</font>
                <td width=\"103\" valign=\"top\">
                </tr>";
    }

    echo"
           <tr>
        <td valign=middle width=\"75\" valign=\"top\">
        Category
        <td width=\"464\" colspan=\"3\">
        <select name=\"category\" id=\"category\" onchange=\"onCategoryChange(this.value)\">";

    $cat_lines = file("./data/category.db.php");
    foreach ($cat_lines as $null => $single_line) {
        if (eregi("<\?", $single_line)) {
            continue;
        }
        $cat_arr = explode("|", $single_line);
        if ($member_db[1] <= $cat_arr[3]) {
            if ($item_db[6] == $cat_arr[0]) {
                echo"<option selected=\"selected\" value=\"$cat_arr[0]\">$cat_arr[1]</option>\n";
            } else {
                echo"<option value=\"$cat_arr[0]\">$cat_arr[1]</option>\n";
            }
        }
    }
    echo "</select><td width=\"103\" valign=\"top\">";
    $xfieldsaction = "list";
    $xfieldsid = $id;
    $xfieldscat = $item_db[6];
    include("xfields.mdu.php");
    if ($config_allow_short == "yes"  || $config_short_full == "short") {
        echo"</tr>

    <tr>
        <td width=\"75\" valign=\"top\">
        <br />Short Story";
        if ($config_allow_full == "yes") {
            echo"<br /><font class=\"smallesttext\">(leave blank for shortened full story)</font>";
        }
        echo"
        <td width=\"464\" colspan=\"3\">
        <textarea rows=\"8\" cols=\"74\" name=\"short_story\" tabindex=3>$item_db[3]</textarea>
        <td width=\"103\" valign=\"top\" align=center>
        <p align=\"center\"><a href=\"$PHP_SELF?mod=images&action=quick&area=short_story\" onclick=\"window.open('$PHP_SELF?mod=images&action=quick&area=short_story', '_Addimage', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=360');return false;\" target=\"_Addimage\"><br />
        [insert image]</a><br />
        <a href=# onclick=\"window.open('$PHP_SELF?&mod=about&action=cutecode&target=short_story', '_Addimage', 'HEIGHT=280,resizable=yes,scrollbars=yes,WIDTH=360');return false;\" target=\"_CuteCode\">[quick tags]</a><br />
        <br />

        <script>
         function insertext(text,area){
        if(area==\"short\"){document.addnews.short_story.focus(); document.addnews.short_story.value=document.addnews.short_story.value +\" \"+ text; document.addnews.short_story.focus()}
        if(area==\"full\") {document.addnews.full_story.focus(); document.addnews.full_story.value=document.addnews.full_story.value +\" \"+ text; document.addnews.full_story.focus()}
     }
    </script>";

        echo insertSmilies('short', 4);
    } else {
        echo"<input type=\"hidden\" name=\"short_story\" value=\"".str_replace("\"", "`:`", $item_db[3])."\">";
    }
    if ($config_allow_full == "yes" || $config_short_full == "full") {
        echo"</tr>

        <tr>
        <td width=\"75\" valign=\"top\">
        <br />Full Story";
        if ($config_allow_short == "yes") {
            echo"<br /><font class=\"smallesttext\">(optional)</font>";
        }
        echo"
        <td width=\"464\" colspan=\"3\">
        <textarea rows=\"12\" cols=\"74\" name=\"full_story\" tabindex=4>$item_db[4]</textarea>
        <td width=\"103\" valign=\"top\" align=center>
        <br />
        <a href=\"$PHP_SELF?mod=images&action=quick&area=full_story\" onclick=\"window.open('$PHP_SELF?mod=images&action=quick&area=full_story', '_Addimage', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=360');return false;\" target=\"_Addimage\">[insert image]</a><br />
        <a href=# onclick=\"window.open('$PHP_SELF?&mod=about&action=cutecode&target=full_story', '_Addimage', 'HEIGHT=280,resizable=yes,scrollbars=yes,WIDTH=360');return false;\" target=\"_CuteCode\">[quick tags]</a><br />
    <br />";

        echo insertSmilies('full', 4);
    } else {
        echo"<input type=\"hidden\" name=\"full_story\" value=\"".str_replace("\"", "`:`", $item_db[4])."\">";
    }
    echo"</tr>
        <tr>
        <td width=\"75\">
        <td width=\"571\" colspan=\"4\">
        <input type=hidden name=id value=$id>
        <input type=hidden name=action value=doeditnews>
        <input type=hidden name=mod value=editnews>
        <input type=hidden name=source value=$source>

        <input type=submit value=\"Save Changes\" accesskey=\"s\">&nbsp;
    <input type=button value=\"Preview\" onClick=\"preview()\" accesskey=\"p\">&nbsp; <a href=\"javascript:ShowOrHide('options','')\">[options]</a>
        &nbsp;&nbsp;<a href=\"javascript:confirmDelete('$PHP_SELF?mod=editnews&action=doeditnews&source=$source&ifdelete=yes&id=$id')\">[delete]</a>
        </tr>

        <tr id='options' style='display:none;'>
        <td width=\"75\">
    <br />Options
        <td width=\"575\" colspan=\"4\">
    &nbsp;<br />
";
    if ($config_allow_disable_comments == "2") {
        if (trim($item_db[7]) == 1) {
            $dis_com = "checked";
            $discom_val = "1";
        }
        echo "
<span style=\"white-space:nowrap\">&nbsp;&nbsp;<input class=checkbox type=checkbox value=\"1\" name=\"disable_comments\" $dis_com> Disable Comments</span>
";
    } else {
        echo"<input type=\"hidden\" name=\"disable_comments\" value=\"$config_allow_disable_comments\">";
    }
    if ($config_mail_allow_comments == "2") {
        if (trim($item_db[8]) == 1) {
            $mail_com = "checked";
            $mailcom_val = "1";
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

        </form>
        <tr>
        <td width=\"75\">
        <td width=\"571\" colspan=\"4\">
        &nbsp;
        </tr>
        <tr>
        <td width=\"75\">
        Comments";

// Show the Comments for Editing

    if ($source == "") {
        $all_comments_db = file("./data/comments.db.php");
    } else {
        $all_comments_db = file("./data/archives/${source}.comments.arch.php");
    }

    $found_newsid = false;
    foreach ($all_comments_db as $null => $comment_line) {
        if (eregi("<\?", $comment_line)) {
            continue;
        }
        $comment_line = trim($comment_line);
        $comments_arr = explode("|>|", $comment_line);
        if ($comments_arr[0] == $id) {//if these are comments for our story
            $found_newsid = true;
            if ($comments_arr[1] != "") {
                echo"<td width=210>
                                <b>&nbsp;".strtolower(Poster)."</b>
                                <td width=219>
                                <b>".strtolower(Date)."</b>
                                <td width=1>
                                <td width=105>
                                </tr>
                                <form method=post name=comments action=\"$PHP_SELF\">";

                $flag = 1;
                $different_posters = explode("||", $comments_arr[1]);
                foreach ($different_posters as $null => $individual_comment) {
                    if ($flag == 1) {
                        $bg = "class=altern1";
                        $flag = 0;
                    } else {
                        $bg = "class=altern2";
                        $flag = 1;
                    }

                    $comment_arr = explode("|", $individual_comment);
                    $comtime = date("D, d F Y h:i:s", ($comment_arr[0]+($config_date_adjust*60)));
                    if ($comment_arr[1]) {
                        if (strlen($comment_arr[1]) > 25) {
                            $comment_arr[1] = substr($comment_arr[1], 0, 22)."...";
                        }
                        echo"<tr>
                                           <td width=\"75\" >
                                           <td width=\"180\" $bg>
                                            &nbsp; <a title=\"edit this comment\nip:$comment_arr[3]\" href=\"$PHP_SELF?mod=editcomments&action=editcomment&newsid=$id&comid=$comment_arr[0]&source=$source\" onclick=\"window.open('$PHP_SELF?mod=editcomments&action=editcomment&newsid=$id&comid=$comment_arr[0]&source=$source', 'Comments', 'HEIGHT=270,resizable=yes,scrollbars=yes,WIDTH=400');return false;\">$comment_arr[1]</a>
                                           <td width=\"249\" $bg>
                                            <a title=\"edit this comment\nip:$comment_arr[3]\" href=\"$PHP_SELF?mod=editcomments&action=editcomment&newsid=$id&comid=$comment_arr[0]&source=$source\" onclick=\"window.open('$PHP_SELF?mod=editcomments&action=editcomment&newsid=$id&comid=$comment_arr[0]&source=$source', 'Comments', 'HEIGHT=270,resizable=yes,scrollbars=yes,WIDTH=400');return false;\">$comtime ($offset)</a>
                                           <td width=\"1\" $bg>
                                            <input type=checkbox class=checkbox name=\"delcomid[$comment_arr[0]]\" value=1>
                                           <td width=\"105\" $bg>
                                           </tr>";
                    }//if not blank
                }//foreach comment

                    echo"<tr>
                    <td width=\"75\">
                    <td width=\"210\">
                    <td width=\"219\">
                    <p align=\"right\">delete all?
                    <td width=\"1\">
                    <input type=checkbox class=checkbox name=delcomid[all] value=1>
                    <td width=\"105\">
                    </tr>

                    <tr>
                    <td width=\"75\">
                    <td width=\"466\" colspan=\"3\">
                    <p align=\"right\"><input type=submit value=\"Delete Selected\">
                    <td width=\"105\">
                    </tr>

                    <input type=hidden name=newsid value=$id>
                    <input type=hidden name=deletecomment value=yes>
                    <input type=hidden name=action value=doeditcomment>
                    <input type=hidden name=mod value=editcomments>
                <input type=hidden name=source value=$source>
                    </form>
                    </table>";

                break;//foreach comment line
            }//if there are any comments
           else {
               echo"<td width=\"210\">
                           No Comments
                           <td width=\"219\">
                           <td width=\"1\">
                           <td width=\"105\">
                           </tr>
                           </tr>
                           </table>";
           }
        }//if these are comments for our story
    }//foreach comments line
    if ($found_newsid == false) {
        echo"<td width=\"210\">
           No Comments
           <td width=\"219\">
           <td width=\"1\">
           <td width=\"105\">
           </tr>
           </tr>
           </table>";
    }
    echofooter();
}
// ********************************************************************************
// Do Edit News
// ********************************************************************************
elseif ($action == "doeditnews") {
    if ($short_story == "") {
        $short_story = $full_story;
        if (strlen($short_story) > $config_max_story_length && $config_max_story_length > 0) {
            $short_story = substr(trim($short_story), 0, $config_max_story_length);
            $short_story = substr($short_story, 0, strlen($short_story)-strpos(strrev($short_story), " "));
            $short_story .= '&hellip;';
        }
    }
    if (trim($title) == "" and $ifdelete != "yes") {
        msg("error", "Error !!!", "The title can not be blank.", "javascript:history.go(-1)");
    }

    if ($if_convert_new_lines        == "1") {
        $n_to_br                = true;
    }
    if ($if_use_html                                == "1") {
        $use_html        = true;
    }

    $short_story =         replace_news("add", rtrim($short_story), $n_to_br, $use_html);
    $full_story = ascii_convert(str_replace("`:`", "\"", $full_story));
    $full_story =         replace_news("add", rtrim($full_story), $n_to_br, $use_html);
    $title =                 ascii_convert(stripslashes(preg_replace(array("'\n'", "''"), array("<br />", ""), $title)));
    $avatar =                stripslashes(preg_replace(array("'\|'", "'\n'", "''"), array("&#124;", "<br />", ""), $avatar));

    if ($source == "") {
        $news_file = "./data/news.db.php";
        $com_file = "./data/comments.db.php";
    } else {
        $news_file = "./data/archives/$source.news.arch.php";
        $com_file = "./data/archives/$source.comments.arch.php";
    }
// XFields v2.1 - addblock
if ($ifdelete != "yes") {
    $xfieldsaction = "init";
    $xfieldsid = $id;
    include("xfields.mdu.php");
}
// XFields v2.1 - End addblock
        $old_db = file("$news_file");
    $new_db = fopen("$news_file", w);
    fwrite($new_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_db as $null => $old_db_line) {
        if (eregi("<\?", $old_db_line)) {
            continue;
        }
        $old_db_arr = ascii_convert(explode("|", $old_db_line));
        if ($id != $old_db_arr[0]) {
            fwrite($new_db, "$old_db_line");
        } else {
            $have_perm = 0;
            if (($member_db[1] == 1) or ($member_db[1] == 2)) {
                $have_perm = 1;
            } elseif ($member_db[1] == 3 and $old_db_arr[1] == $member_db[2]) {
                $have_perm = 1;
            }
            if ($have_perm) {
                // XFields v2.1 - addblock
$xfieldsaction = ($ifdelete == "yes" ? "delete" : "save");
                $xfieldsid = $id;
                include("xfields.mdu.php");
// XFields v2.1 - End addblock
                if ($ifdelete != "yes") {
                    $okchanges = true;
    //<!-- Start ModifyTime v2.0 -->
            $converted_input_date = (strtotime("$edit_day ".$months[$edit_month]." $edit_year $edit_hour:$edit_min:$edit_sec")-($config_date_adjust*60));
                    $all_db = file("./data/news.db.php");
                    rsort($all_db);
                    reset($all_db);
                    foreach ($all_db as $null => $news_line) {
                        $news_arr = explode("|", $news_line);
                        if ($news_arr[0] == $converted_input_date) {
                            $converted_input_date++;
                        }
                    }
                    if ($disable_comments != 1) {
                        $disable_comments = 0;
                    }
            // MailOnComment v1.4 - Start ChangeBlock
            if ($mail_on_comment != 1) {
                $mail_on_comment = 0;
            }
                    if ($config_mail_allow_comments != "yes") {
                        $mail_on_comment = 0;
                    }
                    fwrite($new_db, "$converted_input_date|$old_db_arr[1]|$title|$short_story|$full_story|$editavatar|$category|$disable_comments|$mail_on_comment||\n");
            // MailOnComment v1.4 - End ChangeBlock

            // convert view counter
            $view_file = "$cutepath/data/counter.db.php";
                    $cvf="";
                    $converted_view_file = file($view_file);
                    for ($v = 0; $v < sizeof($converted_view_file); $v++) {
                        $cvf=$cvf.$converted_view_file[$v];
                    }
                    $fp = fopen($view_file, "wb");
                    fwrite($fp, str_replace($pass_time, $converted_input_date, $cvf));
                    fclose($fp);

            // convert comments
            $ccf="";
                    $converted_com_file = file($com_file);
                    for ($c = 0; $c < sizeof($converted_com_file); $c++) {
                        $ccf=$ccf.$converted_com_file[$c];
                    }
                    $fp = fopen($com_file, "wb");
                    fwrite($fp, str_replace($pass_time, $converted_input_date, $ccf));
                    fclose($fp);

    //<!-- End ModifyTime v2.0 -->
                } else {
                    $okdeleted = true;
                    $all_file = file("$com_file");
                    $new_com=fopen("$com_file", "w");
                    foreach ($all_file as $null => $line) {
                        $line_arr = explode("|>|", $line);
                        if ($line_arr[0] == $id) {
                            $okdelcom = true;
                        } else {
                            fwrite($new_com, "$line");
                        }
                    }
                    fclose($new_com);
                }
            } else {
                fwrite($new_db, "$old_db_line");
                $no_permission = true;
            }
        }
    }
    fclose($new_db);
//<!-- Start ModifyTime v2.0 -->
        $sorted_news = file("./data/news.db.php");
    sort($sorted_news);
    reset($sorted_news);
    foreach ($sorted_news as $null => $sorted_news_line) {
        $sorted_news_output = str_replace("\n", "", $sorted_news_line)."\n".$sorted_news_output;
    }
    $fp = fopen($news_file, "wb");
    fwrite($fp, $sorted_news_output);
    fclose($fp);
//<!-- End ModifyTime v2.0 -->
    if ($no_permission) {
        msg("error", "NO Access", "You dont have access for this action", "$PHP_SELF?mod=editnews&action=list");
    }
    if ($okdeleted and $okdelcom) {
        msg("info", "News Deleted", "The news item successfully was deleted.<br />If there were comments for this article they are also deleted.");
    }
    if ($okdeleted and !$okdelcom) {
        msg("info", "News Deleted", "The news item successfully was deleted.<br />If there were comments for this article they are also deleted.<br /><font class=error>But can not delete comments of this article !!!</font>");
    } elseif ($okchanges) {
        //<!-- Start ModifyTime v2.0 -->
    msg("info", "Changes Saved", "The changes were successfully saved", "$PHP_SELF?mod=editnews&action=editnews&id=$converted_input_date&source=$source");
    //<!-- End ModifyTime v2.0 -->
    } else {
        msg("error", "Error !!!", "The news item can not be found or there is an error with the news database file.");
    }
}
