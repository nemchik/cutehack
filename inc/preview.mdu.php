<HTML>
<TITLE>Preview</TITLE>
<BODY>
<center>
<?php
    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Detect all template packs we have
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
        $templates_list = array();
        if (!$handle = opendir("./data")) {
            die("<center>Can not open directory $cutepath/data ");
        }
                   while (false !== ($file = readdir($handle))) {
                       if (eregi(".tpl.php", $file) && !eregi("_dh.tpl.php", $file)) {
                           $file_arr                 = explode(".", $file);
                           $templates_list[]= $file_arr[0];
                       }
                   }
        closedir($handle);
    if ($do_template == '' or !$do_template) {
        $do_template = 'Default';
    }
echo '
    <table border=0 cellpadding=0 cellspacing=0 width=400  class="panel" height="50" >
    <form method=get action="'.$PHP_SELF.'">
    <tr>
    <td width=126 height="23">
    &nbsp;Viewing Template
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
        <input type=hidden name=title value='.$title.'>
        <input type=hidden name=author value='.$author.'>
        <input type=hidden name=avatar value='.$avatar.'>
        <input type=hidden name=short_story value='.$short_story.'>
        <input type=hidden name=full_story value='.$full_story.'>
        <input type=hidden name=category value='.$category.'>
        <input type=hidden name=archive value='.$archive.'>
        <input type=hidden name=mod value=preview>
        </form>
        </table>
';
require("./data/".$do_template.".tpl.php");

$alt=0;

$cat_lines = @file("./data/category.db.php");
foreach ($cat_lines as $null => $single_line) {
    if (eregi("<\?", $single_line)) {
        continue;
    }
    $cat_arr = explode("|", $single_line);
    $cat[$cat_arr[0]] = $cat_arr[1];
    $cat_icon[$cat_arr[0]]=$cat_arr[2];
}

    if ($manual_avatar != "") {
        $avatar = $manual_avatar;
    } elseif ($select_avatar != "" and $select_avatar != "none") {
        $avatar = $select_avatar;
    } else {
        $avatar = "";
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

    if ($member_db[4] != "") {
        if ($member_db[7] != 1 and $member_db[5] != "") {
            $author = "<a href=mailto:$member_db[5]>$member_db[4]</a>";
        } else {
            $author = "$member_db[4]";
        }
    } else {
        if ($member_db[7] != 1 and $member_db[5] != "") {
            $author = "<a href=mailto:$member_db[5]>$member_db[2]</a>";
        } else {
            $author = "$member_db[2]";
        }
    }

    $output = $template_active;
    $output = str_replace("{title}", $title, $output);
    $output = str_replace("{date}", date($config_timestamp_active, time()+($config_date_adjust*60)), $output);
if (!function_exists(tagdate)) {
    function tagdate($match)
    {
        return date($match[1], time()+($config_date_adjust*60));
    }
}
$output = preg_replace_callback('#\{date=(.*?)\}#i', tagdate, $output);
$output = preg_replace_callback('#\[date\](.*?)\[/date\]#i', tagdate, $output);
$output = preg_replace('#\[alt\](.*?),(.*?)\[/alt\]#i', (($alt%2)==0) ? '\\1' : '\\2', $output);
    $output = str_replace("{author}", $author, $output);
    if ($avatar != "") {
        $output = str_replace("{avatar}", "<img src=\"$avatar\" border=0 />", $output);
    } else {
        $output = str_replace("{avatar}", "", $output);
    }
    $output = str_replace("{avatar-url}", "$avatar", $output);
    $output = str_replace("[link]", "<a href=#>", $output);
    $output = str_replace("[/link]", "</a>", $output);
    $output = str_replace("{comments-num}", countComments($id), $output);
if ($short_story == "") {
    $maxLenght=$config_max_story_length;
    $short_story = $full_story;
    if (strlen($short_story) > $maxLenght) {
        $short_story = substr(trim($short_story), 0, $maxLenght);
        $short_story = substr($short_story, 0, strlen($short_story)-strpos(strrev($short_story), " "));
        $short_story .= '&hellip;';
    }
}
    $output = str_replace("{short-story}", $short_story, $output);
    $output = str_replace("{full-story}", $full_story, $output);
    if ($full_story) {
        $output = str_replace("[full-link]", "<a href=#>", $output);
    } else {
        $output = preg_replace("'\[full-link\].*?\[/full-link\]'", "", $output);
    }
    $output = str_replace("[/full-link]", "</a>", $output);
    $output = str_replace("[com-link]", "<a href=#>", $output);
    $output = str_replace("[/com-link]", "</a>", $output);
    $output = str_replace("{category}", $cat[$category], $output);
    $output = str_replace("{category-id}", $category, $output);
    if ($cat_icon[$category] != "") {
        $output = str_replace("{category-icon}", "<img border=0 src=\"".$cat_icon[$category]."\" />", $output);
    } else {
        $output = str_replace("{category-icon}", "", $output);
    }


                $output = str_replace("{author-name}", $member_db[2], $output);
                $output = str_replace("{author-lower}", strtolower($member_db[2]), $output);

                if ($member_db[5] != "") {
                    $output = str_replace("[mail]", "<a href=\"mailto:". $member_db[5] ."\">", $output);
                    $output = str_replace("[/mail]", "</a>", $output);
                } else {
                    $output = str_replace("[mail]", "", $output);
                    $output = str_replace("[/mail]", "", $output);
                }
                $output = str_replace("{news-id}", "ID Unknown", $output);
                $output = str_replace("{archive-id}", $archive, $output);
                $output = str_replace("{php-self}", $PHP_SELF, $output);
                $output = str_replace("{cute-http-path}", $config_http_script_dir, $output);
                $output = str_replace("{views}", "0", $output);
// XFields v2.1 - addblock
$xfieldsaction = "templatereplacepreview";
$xfieldsinput = $output;
include("xfields.mdu.php");
$output = $xfieldsoutput;
// XFields v2.1 - End addblock
    $output = replace_news("show", $output);

// loginout 0.73 / Login Box v1.0 - addblock
$output = preg_replace('/\\[logged-in\\](.*?)\\[\\/logged-in\\]/is', ($is_logged_in) ? '\\1' : '', $output);
$output = preg_replace('/\\[not-logged-in\\](.*?)\\[\\/not-logged-in\\]/is', ($is_logged_in) ? '' : '\\1', $output);
// loginout 0.73 / Login Box v1.0 - End addblock

// Truncate v1.0 - addblock
if (!function_exists(clbTruncate)) {
    function clbTruncate($match)
    {
        if (strlen($match[2]) > $match[1]) {
            return substr($match[2], 0, $match[1] - 3) . '&hellip;';
        } else {
            return $match[2];
        }
    }
}
$output = preg_replace_callback('#\[truncate=(.*?)\](.*?)\[/truncate\]#i', clbTruncate, $output);
// Truncate v1.0 - End addblock

echo("<fieldset style=\"border-style:solid; border-width:1; border-color:black;\"><legend> <span style=\"font-size: 13px;\">Active News:</span> </legend>".$output."</fieldset>");


if ($full_story) {
    $alt=1;
    $output = $template_full;
    $output = str_replace("{title}", $title, $output);
    $output = str_replace("{date}", date($config_timestamp_active, time()+($config_date_adjust*60)), $output);
    if (!function_exists(tagdate)) {
        function tagdate($match)
        {
            return date($match[1], time()+($config_date_adjust*60));
        }
    }
    $output = preg_replace_callback('#\{date=(.*?)\}#i', tagdate, $output);
    $output = preg_replace_callback('#\[date\](.*?)\[/date\]#i', tagdate, $output);
    $output = preg_replace('#\[alt\](.*?),(.*?)\[/alt\]#i', (($alt%2)==0) ? '\\1' : '\\2', $output);
    $output = str_replace("{author}", $author, $output);
    if ($avatar != "") {
        $output = str_replace("{avatar}", "<img src=\"$avatar\" border=0 />", $output);
    } else {
        $output = str_replace("{avatar}", "", $output);
    }
    $output = str_replace("{avatar-url}", "$avatar", $output);
    $output = str_replace("[link]", "<a href=#>", $output);
    $output = str_replace("[/link]", "</a>", $output);
    $output = str_replace("{comments-num}", countComments($id), $output);
    if ($short_story == "") {
        $maxLenght=$config_max_story_length;
        $short_story = $full_story;
        if (strlen($short_story) > $maxLenght) {
            $short_story = substr(trim($short_story), 0, $maxLenght);
            $short_story = substr($short_story, 0, strlen($short_story)-strpos(strrev($short_story), " "));
            $short_story .= '&hellip;';
        }
    }
    $output = str_replace("{short-story}", $short_story, $output);
    $output = str_replace("{full-story}", $full_story, $output);
    if ($full_story) {
        $output = str_replace("[full-link]", "<a href=#>", $output);
    } else {
        $output = preg_replace("'\[full-link\].*?\[/full-link\]'", "", $output);
    }
    $output = str_replace("[/full-link]", "</a>", $output);
    $output = str_replace("[com-link]", "<a href=#>", $output);
    $output = str_replace("[/com-link]", "</a>", $output);
    $output = str_replace("{category}", $cat[$category], $output);
    $output = str_replace("{category-id}", $category, $output);
    if ($cat_icon[$category] != "") {
        $output = str_replace("{category-icon}", "<img border=0 src=\"".$cat_icon[$category]."\" />", $output);
    } else {
        $output = str_replace("{category-icon}", "", $output);
    }


    $output = str_replace("{author-name}", $member_db[2], $output);
    $output = str_replace("{author-lower}", strtolower($member_db[2]), $output);

    if ($member_db[5] != "") {
        $output = str_replace("[mail]", "<a href=\"mailto:". $member_db[5] ."\">", $output);
        $output = str_replace("[/mail]", "</a>", $output);
    } else {
        $output = str_replace("[mail]", "", $output);
        $output = str_replace("[/mail]", "", $output);
    }
    $output = str_replace("{news-id}", "ID Unknown", $output);
    $output = str_replace("{archive-id}", $archive, $output);
    $output = str_replace("{php-self}", $PHP_SELF, $output);
    $output = str_replace("{cute-http-path}", $config_http_script_dir, $output);
    $output = str_replace("{views}", "0", $output);
    // XFields v2.1 - addblock
    $xfieldsaction = "templatereplacepreview";
    $xfieldsinput = $output;
    include("xfields.mdu.php");
    $output = $xfieldsoutput;
    // XFields v2.1 - End addblock
    $output = replace_news("show", $output);

    // loginout 0.73 / Login Box v1.0 - addblock
    $output = preg_replace('/\\[logged-in\\](.*?)\\[\\/logged-in\\]/is', ($is_logged_in) ? '\\1' : '', $output);
    $output = preg_replace('/\\[not-logged-in\\](.*?)\\[\\/not-logged-in\\]/is', ($is_logged_in) ? '' : '\\1', $output);
    // loginout 0.73 / Login Box v1.0 - End addblock

    // Truncate v1.0 - addblock
    if (!function_exists(clbTruncate)) {
        function clbTruncate($match)
        {
            if (strlen($match[2]) > $match[1]) {
                return substr($match[2], 0, $match[1] - 3) . '&hellip;';
            } else {
                return $match[2];
            }
        }
    }
    $output = preg_replace_callback('#\[truncate=(.*?)\](.*?)\[/truncate\]#i', clbTruncate, $output);
    // Truncate v1.0 - End addblock

    echo("<br /><fieldset style=\"border-style:solid; border-width:1; border-color:black;\"><legend> <span style=\"font-size: 13px;\">Full Story:</span> </legend>".$output."</fieldset>");
}

?>
</center>
</BODY>
</HTML>
