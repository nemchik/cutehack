<?php

error_reporting(E_ALL ^ E_NOTICE);

$cutepath =  __FILE__;
$cutepath = preg_replace("'\\\show_archives\.php'", "", $cutepath);
$cutepath = preg_replace("'/show_archives\.php'", "", $cutepath);

require_once("$cutepath/inc/functions.inc.php");
require_once("$cutepath/data/config.php");
if (!isset($template) or $template == "" or strtolower($template) == "default") {
    require_once("$cutepath/data/Default.tpl.php");
} else {
    if (file_exists("$cutepath/data/${template}.tpl.php")) {
        require_once("$cutepath/data/${template}.tpl.php");
    } else {
        die("Error!<br />the template <b>".htmlspecialchars($template)."</b> does not exists, note that templates are case sensetive and you must write the name exactly as it is");
    }
}

// Prepare requested categories
if (eregi("[a-z]", $category)) {
    die("<b>Error</b>!<br />CuteNews has detected that you use \$category = \"".htmlspecialchars($category)."\"; but you can call the categories only with their <b>ID</b> numbers and not with names<br />
    example:<br /><blockquote>&lt;?PHP<br />\$category = \"1\";<br />include(\"path/to/show_archives.php\");<br />?&gt;</blockquote>");
}
$category = preg_replace("/ /", "", $category);
$tmp_cats_arr = explode(",", $category);
foreach ($tmp_cats_arr as $key=>$value) {
    if ($value != "") {
        $requested_cats[$value] = true;
    }
}


if ($archive == "" or !$archive) {
    $news_file = "$cutepath/data/news.db.php";
    $comm_file = "$cutepath/data/comments.db.php";
} else {
    $news_file = "$cutepath/data/archives/$archive.news.arch.php";
    $comm_file = "$cutepath/data/archives/$archive.comments.arch.php";
}

if ($subaction == "" or !isset($subaction)) {
    $user_query = cute_query_string($QUERY_STRING, array("start_from", "archive", "subaction", "id", "ucat"));

    if (!$handle = opendir("$cutepath/data/archives")) {
        die("<center>Can not open directory $cutepath/data/archives ");
    }
    while (false !== ($file = readdir($handle))) {
        $file_arr = explode(".", $file);
        if ($file != "." and $file != ".." and $file_arr[1] == "news") {
            $arch_arr[] = $file_arr[0];
        }
    }
    closedir($handle);

    if (is_array($arch_arr)) {
        $arch_arr = array_reverse($arch_arr);
        foreach ($arch_arr as $null => $arch_file) {
            $news_lines = file("$cutepath/data/archives/$arch_file.news.arch.php");
            $count = count($news_lines)-1;
            $last = $count-1;
            $first_news_arr = explode("|", $news_lines[$last]);
            $last_news_arr        = explode("|", $news_lines[1]);

            $first_timestamp = $first_news_arr[0];
            $last_timestamp         = $last_news_arr[0];

            $show_first = date($config_timestamp_archive, $first_timestamp);
            $show_last = date($config_timestamp_archive, $last_timestamp);
            if ($config_format_archive == "b") {
                $show_timestamp = $show_first." - ".$show_last;
            } elseif ($config_format_archive == "s") {
                $show_timestamp = $show_first;
            } elseif ($config_format_archive == "e") {
                $show_timestamp = $show_last;
            }

            // Archive Date Header
            if ($show_ah == true) {
                if (!$config_dateheader_archive || $config_dateheader_archive == "") {
                    $config_dateheader_archive = "Y";
                }
                if ($dateheader != date($config_dateheader_archive, $first_timestamp)) {
                    $dateheader = date($config_dateheader_archive, $first_timestamp);
                    if (!isset($template) || $template == "") {
                        include("$cutepath/data/Default_dh.tpl.php");
                    } else {
                        include("$cutepath/data/".$template."_dh.tpl.php");
                    }
                    $template_comment = str_replace("{archiveheader}", $dateheader, $template_comment);
                    echo $template_comment;
                    if (!isset($template) || $template == "") {
                        include("$cutepath/data/Default.tpl.php");
                    } else {
                        include("$cutepath/data/".$template.".tpl.php");
                    }
                }
            }
            // Archive Date Header

            if (date($config_timestamp_archive, $first_timestamp) == date($config_timestamp_archive, $last_timestamp)) {
                $show_timestamp = $show_first;
            }
            echo"<a href=\"$PHP_SELF?archive=$arch_file&subaction=list-archive&$user_query\">$show_timestamp, (<b>$count</b>)</a><br />";
        }
    }
} else {
    if ($CN_HALT != true and $static != true and ($subaction == "showcomments" or $subaction == "showfull" or $subaction == "addcomment") and ((!isset($category) or $category == "") or $requested_cats[$ucat] == true)) {
        if ($subaction == "addcomment") {
            $allow_add_comment        = true;
            $allow_comments = true;
        }
        if ($subaction == "showcomments") {
            $allow_comments = true;
        }
        if (($subaction == "showcomments" or $allow_comments == true) and $config_show_full_with_comments == "yes") {
            $allow_full_story = true;
        }
        if ($subaction == "showfull") {
            $allow_full_story = true;
        }
        if ($subaction == "showfull" and $config_show_comments_with_full == "yes") {
            $allow_comments = true;
        }
    } else {
        if ($config_reverse_active == "yes") {
            $reverse = true;
        }
        $allow_active_news = true;
    }
    require("$cutepath/inc/shows.inc.php");
}
unset($template, $requested_cats, $reverse, $in_use, $archive, $archives_arr, $number, $no_prev, $no_next, $i, $showed, $prev, $used_archives);
?>
<!-- News Powered by CuteHack: http://cutephp.com/ -->
