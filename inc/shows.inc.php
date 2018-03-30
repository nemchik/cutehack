<?php

do { // Used if we want to display some error to the user and halt the rest of the script
$user_query = cute_query_string($QUERY_STRING, array( "comm_start_from","start_from", "archive", "subaction", "id", "ucat"));
    $user_post_query = cute_query_string($QUERY_STRING, array( "comm_start_from", "start_from", "archive", "subaction", "id", "ucat"), "post");
    $offset = timeoffset($config_date_adjust);
//####################################################################################################################
//                         Define Categories
//####################################################################################################################
$cat_lines = file("$cutepath/data/category.db.php");
    foreach ($cat_lines as $null => $single_line) {
        if (eregi("<\?", $single_line)) {
            continue;
        }
        $cat_arr = explode("|", $single_line);
        $cat[$cat_arr[0]] = $cat_arr[1];
        $cat_icon[$cat_arr[0]]=$cat_arr[2];
    }
//####################################################################################################################
//                         Check for Comment Disabling
//####################################################################################################################
$comment_form = true;
    if (($allow_comments || $allow_add_comment) && $id) {
        $all_news = file("$news_file");
        foreach ($all_news as $null => $news_data) {
            $news_data = explode("|", $news_data);
            if (($id == $news_data[0]) && ($news_data[7] == 1)) {
                if ($allow_add_comment) {
                    unset($allow_add_comment);
                    echo "<div style=\"text-align: center;\">Commenting is disabled.</div>";
                }
                if ($allow_comments) {
                    $comment_form = false;
                }
            }
        }
    }
//####################################################################################################################
//                         Define Users
//####################################################################################################################
$all_users = file("$cutepath/data/users.db.php");
    foreach ($all_users as $null => $user) {
        if (!eregi("<\?", $member_db_line)) {
            $user_arr = explode("|", $user);
            if ($user_arr[4] != "") {
                if ($user_arr[7] != 1 and $user_arr[5] != "") {
                    $my_names[$user_arr[2]] = "<a href=\"mailto:$user_arr[5]\">$user_arr[4]</a>";
                } else {
                    $my_names[$user_arr[2]] = "$user_arr[4]";
                }
                $name_to_nick[$user_arr[2]] = $user_arr[4];
            } else {
                if ($user_arr[7] != 1 and $user_arr[5] != "") {
                    $my_names[$user_arr[2]] = "<a href=\"mailto:$user_arr[5]\">$user_arr[2]</a>";
                } else {
                    $my_names[$user_arr[2]] = "$user_arr[2]";
                }
                $name_to_nick[$user_arr[2]] = $user_arr[2];
            }

            if ($user_arr[7] != 1) {
                $my_mails[$user_arr[2]] = $user_arr[5];
            } else {
                $my_mails[$user_arr[2]] = "";
            }
            $my_passwords[$user_arr[2]] = $user_arr[3];
            $my_users[] = $user_arr[2];
        }
    }
//####################################################################################################################
//                         Check for MailOnComment v1.4
//####################################################################################################################
if ($archive == "" || !$archive) {
    $all_news = file("$news_file");
    foreach ($all_news as $null => $news_data) {
        $news_data = explode("|", $news_data);
        if (($id == $news_data[0]) && ($news_data[8] == 1)) {
            $mail_on_comment_run = true;
        }
    }
}
//####################################################################################################################
//                         Add Comment
//####################################################################################################################
if ($allow_add_comment) {
    if ($is_logged_in && $my_names[$news_arr[1]]) {
        $name = $my_names[$name];
    }
    $name = trim($name);
    $mail = trim($mail);
    $id = (int) $id;  // Yes it's stupid how I didn't thought about this :/

    //----------------------------------
    // Check the lenght of comment, include name + mail
    //----------------------------------

        if (!$is_logged_in) {
            if (strlen($name) > 50) {
                echo"<div style=\"text-align: center;\">Your name is too long!</div>";
                $CN_HALT = true;
                break 1;
            }
            if (strlen($mail) > 50) {
                echo"<div style=\"text-align: center;\">Your e-mail is too long!</div>";
                $CN_HALT = true;
                break 1;
            }
        }
    if (strlen($comments) > $config_comment_max_long and $config_comment_max_long != "" and $config_comment_max_long != "0") {
        echo"<div style=\"text-align: center;\">Your comment is too long!</div>";
        $CN_HALT = true;
        break 1;
    }

    //----------------------------------
    // Get the IP
    //----------------------------------
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "not detected";
        }

    //----------------------------------
    // Flood Protection
    //----------------------------------
    if ($config_flood_time != 0 and $config_flood_time != "") {
        if (flooder($ip, $id) == true) {
            echo("<div style=\"text-align: center;\">Flood protection activated !!!<br />you have to wait $config_flood_time seconds after your last comment before posting again at this article.</div>");
            $CN_HALT = true;
            break 1;
        }
    }

    //----------------------------------
    // Check if IP is blocked
    //----------------------------------
    $blockip = false;
    $old_ips = file("$cutepath/data/ipban.db.php");
    $new_ips = fopen("$cutepath/data/ipban.db.php", "w");
    @flock($new_ips, 2);
    fwrite($new_ips, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_ips as $null => $old_ip_line) {
        if (eregi("<\?", $old_ip_line)) {
            continue;
        }
        $ip_arr = explode("|", $old_ip_line);
        if ($ip_arr[0] != $ip) {
            fwrite($new_ips, $old_ip_line);
        } else {
            $countblocks = $ip_arr[1] = $ip_arr[1] + 1;
            fwrite($new_ips, "$ip_arr[0]|$countblocks||\n");
            $blockip = true;
        }
    }
    @flock($new_ips, 3);
    fclose($new_ips);
    if ($blockip) {
        echo("<div style=\"text-align: center;\">Sorry but you have been blocked from posting comments</div>");
        $CN_HALT = true;
        break 1;
    }

    //----------------------------------
    // Wrap the long words
    //----------------------------------
    if ($config_auto_wrap > 1) {
        $comments_arr = explode("\n", $comments);
        foreach ($comments_arr as $null => $line) {
            $wraped_comm .= ereg_replace("([^ \/\/]{".$config_auto_wrap."})", "\\1\n", $line) ."\n";
        }
        if (strlen($name) > $config_auto_wrap) {
            $name = substr($name, 0, $config_auto_wrap)." &hellip;";
        }
        $comments = $wraped_comm;
    }

    //----------------------------------
    // Do some validation checks
    //----------------------------------
    $comments = replace_comment("add", $comments);
    if ($is_logged_in) {
        $name    = $member_db[2];
        $mail    = $member_db[5];
    } else {
        if ($config_only_registered_comment == "yes") {
            echo"<div style=\"text-align: center;\">Sorry but only registered/logged in users can post comments, and you are not recognized as valid member.</div>";
            $CN_HALT = true;
            break 1;
        }
        if (trim($_POST['name']) == "") {
            echo("<div style=\"text-align: center;\">You must enter name.<br /><a href=\"javascript:history.go(-1)\">go back</a></div>");
            $CN_HALT = true;
            break 1;
        } else {
            $name= "* ".$_POST['name'];
        }
        $mail    = "";
    }

    if ($mail == "" or trim($mail) == "") {
        $mail = "none";
    } else {
        $ok = false;
        if (preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $mail)) {
            $ok = true;
        } elseif ($config_allow_url_instead_mail == "yes" and preg_match("/((http(s?):\/\/)|(www\.))([\w\.]+)([\/\w+\.-?]+)/", $mail)) {
            $ok = true;
        } elseif ($config_allow_url_instead_mail != "yes") {
            echo("<div style=\"text-align: center;\">This is not a valid e-mail<br /><a href=\"javascript:history.go(-1)\">go back</a></div>");
            $CN_HALT = true;
            break 1;
        } else {
            echo("<div style=\"text-align: center;\">This is not a valid e-mail or site URL<br /><a href=\"javascript:history.go(-1)\">go back</a></div>");
            $CN_HALT = true;
            break 1;
        }
    }

    if ($comments == "") {
        echo("<div style=\"text-align: center;\">Sorry but the comment can not be blank<br /><a href=\"javascript:history.go(-1)\">go back</a></div>");
        $CN_HALT = true;
        break 1;
    }

    $time = time()+($config_date_adjust*60);

    //----------------------------------
    // Add The Comment ... Go Go GO!
    //----------------------------------
    $old_comments = file("$comm_file");
    $new_comments = fopen("$comm_file", "w");
    fwrite($new_comments, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    @flock($new_comments, 2);
    $found = false;
    foreach ($old_comments as $null => $old_comments_line) {
        if (eregi("<\?", $old_comments_line)) {
            continue;
        }
        $old_comments_arr = explode("|>|", $old_comments_line);
        if ($old_comments_arr[0] == $id) {
            $old_comments_arr[1] = trim($old_comments_arr[1]);
            fwrite($new_comments, "$old_comments_arr[0]|>|$old_comments_arr[1]$time|".ascii_convert($name)."|".ascii_convert($mail)."|$ip|".ascii_convert($comments)."||\n");
// MailOnComment v1.4 - Start AddBlock
if ($mail_on_comment_run == true || $config_mail_admin_comments == "yes") {
    $all_news = file("$news_file");
foreach ($all_news as $null => $news_data) {
    if (eregi("<\?", $news_data)) {
        continue;
    }
    $news_arr = explode("|", $news_data);
    if ($news_arr[0] == $old_comments_arr[0] && isset($news_arr[2]) && $news_arr[2] != "") {
        $mail_comment = preg_replace(array("'<br>'", "'<br />'"), array("\n", "\n"), ascii_convert($comments, "mail"));
        $mail_name = ascii_convert($name, "mail");
        $mail_title = ascii_convert($news_arr[2], "mail");
        $send_them_mail = "";
        if (preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $mail)) {
            $send_them_mail = "\n Send them mail: $mail";
        }
        if ($mail_on_comment_run == true) {
            cute_mail($my_mails[$news_arr[1]], "Your post has received a comment", "$mail_name has commented on your post: \n $mail_title $send_them_mail \n\n Comment: \n $mail_comment");
        }
        if (($config_mail_admin_comments == "1" && ($archive == "" || !$archive)) || $config_mail_admin_comments == "2") {
            cute_mail($config_mail_admin_address, "$news_arr[1]'s post has received a comment", "$mail_name has commented on $news_arr[1]'s post: \n $mail_title $send_them_mail \n\n Comment: \n $mail_comment");
        }
    }
}
}
// MailOnComment v1.4 - End AddBlock
            $found = true;
        } else {
            fwrite($new_comments, $old_comments_line);
        }
    }
    if (!$found) {/* // do not add comment if News ID is not found \\ fwrite($new_comments, "$id|>|$time|$name|$mail|$ip|$comments||\n");*/
    }
    @flock($new_comments, 3);
    fclose($new_comments);

    //----------------------------------
    // Sign this comment in the Flood Protection
    //----------------------------------
    if ($config_flood_time != "0" and $config_flood_time != "") {
        $flood_file = fopen("$cutepath/data/flood.db.php", "a");
        @flock($flood_file, 2);
        fwrite($flood_file, time()."|$ip|$id|\n");
        @flock($flood_file, 3);
        fclose($flood_file);
    }
// Comments Refresh Fix v1.1 - addblock
    echo '<meta http-equiv="refresh" content="0; url=http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?subaction=showcomments&amp;id='.$id.'&amp;archive='.$archive.'&amp;start_from='.$start_from.'&amp;ucat='.$ucat.'&amp;'.$user_query.'">';
// Comments Refresh Fix v1.1 - End addblock
}
//####################################################################################################################
//                 Show Full Story
//####################################################################################################################
if ($allow_full_story) {
    $all_active_news = file("$news_file");
    $alt = 0;
    foreach ($all_active_news as $null => $active_news) {
        if (eregi("<\?", $active_news)) {
            continue;
        }
        $news_arr = explode("|", $active_news);
        if ($news_arr[0] == $id and (!$catid or $catid == $news_arr[6])) {
            if ($counted != "yes") {
                $counted = "yes";
                $article_counter = file("$cutepath/data/counter.db.php");
                $article_counteradd = fopen("$cutepath/data/counter.db.php", w);
                fwrite($article_counteradd, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
                foreach ($article_counter as $null => $counter_line) {
                    if (eregi("<\?", $counter_line)) {
                        continue;
                    }
                    $count_arr = explode("|", $counter_line);
                    if ($count_arr[0] != $news_arr[0] && $counter_line != "") {
                        fwrite($article_counteradd, $counter_line);
                    } else {
                        $foundcount = 1;
        //$count=;
        //$count++;
        fwrite($article_counteradd, "$count_arr[0]|".($count_arr[1]+1)."|\n");
                    }
                }
                if ($foundcount != 1) {
                    $foundcount = 1;
                    fwrite($article_counteradd, "$news_arr[0]|1|\n");
                }
                fclose($article_counteradd);
            }
            $found = true;
            if ($news_arr[4] == "" and (!eregi("\{short-story\}", $template_full))) {
                $news_arr[4] = $news_arr[3];
            }

            if ($my_names[$news_arr[1]]) {
                $my_author = $my_names[$news_arr[1]];
            } else {
                $my_author = $news_arr[1];
            }
            $output = $template_full;
// Date Header
if ($show_dh == true) {
    if ($dateheader != date("mdY", $news_arr[0]+($config_date_adjust*60))) {
        $dateheader = date("mdY", $news_arr[0]+($config_date_adjust*60));
        if (!isset($template) || $template == "") {
            include("$cutepath/data/Default_dh.tpl.php");
        } else {
            include("$cutepath/data/".$template."_dh.tpl.php");
        }
        $template_full = str_replace("{archiveheader}", "", $template_full);
        $output = $template_full.$output;
        if (!isset($template) || $template == "") {
            include("$cutepath/data/Default.tpl.php");
        } else {
            include("$cutepath/data/".$template.".tpl.php");
        }
    }
}
// Date Header
$article_counter = file("$cutepath/data/counter.db.php");
            foreach ($article_counter as $null => $counter_line) {
                if (eregi("<\?", $counter_line)) {
                    continue;
                }
                $count_arr = explode("|", $counter_line);
                if ($count_arr[0] == $news_arr[0]) {
                    $output = str_replace("{views}", $count_arr[1], $output);
                }
            }
            $output = str_replace("{views}", "0", $output);
            $output = str_replace("{title}", $news_arr[2], $output);
            $output = str_replace("{date}", date($config_timestamp_active, $news_arr[0]+($config_date_adjust*60)), $output);
            $output = str_replace("{offset}", timeoffset($config_date_adjust), $output);
            if (!function_exists(tagdate)) {
                function tagdate($match)
                {
                    global $news_arr;
                    return date($match[1], $news_arr[0]+($config_date_adjust*60));
                }
            }
            $output = preg_replace_callback('#\[date\](.*?)\[/date\]#i', tagdate, $output);
            $output = preg_replace('#\[alt\](.*?),(.*?)\[/alt\]#i', (($alt%2)==0) ? '\\1' : '\\2', $output);
            $output = str_replace("{author}", $my_author, $output);
            $output = str_replace("{short-story}", $news_arr[3], $output);
            $output = str_replace("{full-story}", $news_arr[4], $output);
            if ($news_arr[5] != "") {
                $output = str_replace("{avatar}", "<img alt=\"\" src=\"$news_arr[5]\" style=\"border: none;\" />", $output);
            } else {
                $output = str_replace("{avatar}", "", $output);
            }
            $output = str_replace("{avatar-url}", "$news_arr[5]", $output);
            $output = str_replace("{comments-num}", countComments($news_arr[0], $archive), $output);
            $output = str_replace("{category}", $cat[$news_arr[6]], $output);
            $output = str_replace("{category-id}", $news_arr[6], $output);
            if ($cat_icon[$news_arr[6]] != "") {
                $output = str_replace("{category-icon}", "<img style=\"border: none;\" alt=\"".$cat[$news_arr[6]]." icon\" src=\"".$cat_icon[$news_arr[6]]."\" />", $output);
            } else {
                $output = str_replace("{category-icon}", "", $output);
            }

            if ($config_comments_popup == "yes") {
                $output = str_replace("[com-link]", "<a href=\"#\" onclick=\"window.open('$config_http_script_dir/show_news.php?subaction=showcomments&amp;template=$template&amp;id=$news_arr[0]&amp;archive=$archive&amp;start_from=$my_start_from&amp;ucat=$news_arr[6]', '_News', '$config_comments_popup_string');return false;\">", $output);
            } else {
                $output = str_replace("[com-link]", "<a href=\"$PHP_SELF?subaction=showcomments&amp;id=$news_arr[0]&amp;archive=$archive&amp;start_from=$my_start_from&amp;ucat=$news_arr[6]&amp;$user_query\">", $output);
            }
            $output = str_replace("[/com-link]", "</a>", $output);

            $output = str_replace("{author-name}", $name_to_nick[$news_arr[1]], $output);
            $output = str_replace("{author-lower}", strtolower($news_arr[1]), $output);

            $output = str_replace("[full-link]", "<a href=\"$PHP_SELF?subaction=showfull&amp;id=$news_arr[0]&amp;archive=$archive&amp;start_from=$my_start_from&amp;ucat=$news_arr[6]&amp;$user_query\">", $output);
            $output = str_replace("[/full-link]", "</a>", $output);

            if ($my_mails[$news_arr[1]] != "") {
                $output = str_replace("[mail]", "<a href=\"mailto:".$my_mails[$news_arr[1]]."\">", $output);
                $output = str_replace("[/mail]", "</a>", $output);
            } else {
                $output = str_replace("[mail]", "", $output);
                $output = str_replace("[/mail]", "", $output);
            }
            $output = str_replace("{news-id}", $news_arr[0], $output);
            $output = str_replace("{archive-id}", $archive, $output);
            $output = str_replace("{php-self}", $PHP_SELF, $output);
            $output = str_replace("{cute-http-path}", $config_http_script_dir, $output);

            $output = profiledata($name_to_nick[$news_arr[1]], $output);

// XFields v2.1 - addblock
$xfieldsaction = "templatereplace";
            $xfieldsinput = $output;
            $xfieldsid = $news_arr[0];
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

// Valid Amps
$output = ascii_convert($output);
// Valid Amps
                        echo $output;
        }
        $alt++;
    }
    if (!$found) {
        echo("<div style=\"text-align: center;\">Can not find an article with id: <strong>". @(int) htmlspecialchars($id)."</strong></div>");
        $CN_HALT = true;
        break 1;
    }
}
//####################################################################################################################
//                 Show Comments
//####################################################################################################################
if ($allow_comments) {
    echo "<script type=\"text/javascript\" src=\"$config_http_script_dir/limiter.js\"></script>";

    $comm_per_page = $config_comments_per_page;

    $total_comments = 0;
    $showed_comments = 0;
    $comment_number = 0;
    $showed = 0;
    $all_comments = file("$comm_file");

    $alt = 0;

    foreach ($all_comments as $null => $comment_line) {
        if (eregi("<\?", $comment_line)) {
            continue;
        }
        $comment_line = trim($comment_line);
        $comment_line_arr = explode("|>|", $comment_line);
        if ($id == $comment_line_arr[0]) {
            $individual_comments = explode("||", $comment_line_arr[1]);

            $total_comments = @count($individual_comments) - 1;

            //show the page with our new comment, if we just added one
            /* causes some problems, will be updated !!!
                        if($allow_add_comment and true){
                                $comm_start_from = $total_comments-1;
                                if($config_reverse_comments == "yes"){
                                        $comm_start_from = 0;
                                }
                        }
                        */

            if ($config_reverse_comments == "yes") {
                $individual_comments = array_reverse($individual_comments);
            }
            foreach ($individual_comments as $null => $comment) {
                $comment_arr = explode("|", $comment);
                if ($comment_arr[0] != "") {
                    if (isset($comm_start_from) and $comm_start_from != "") {
                        if ($comment_number < $comm_start_from) {
                            $comment_number++;
                            continue;
                        } elseif ($showed_comments == $comm_per_page) {
                            break;
                        }
                    }

                    $comment_number ++;
                    $comment_arr[4] = stripslashes(rtrim($comment_arr[4]));

                    if ($comment_arr[2] != "none") {
                        if (preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $comment_arr[2])) {
                            $url_target = "";
                            $mail_or_url = "mailto:";
                        } else {
                            $url_target = "target=\"_blank\"";
                            $mail_or_url = "";
                            if (substr($comment_arr[2], 0, 3) == "www") {
                                $mail_or_url = "http://";
                            }
                        }
                        $output = str_replace("{author}", "<a $url_target href=\"$mail_or_url".stripslashes($comment_arr[2])."\">".stripslashes($comment_arr[1])."</a>", $template_comment);
                    } else {
                        $output = str_replace("{author}", $comment_arr[1], $template_comment);
                    }

                    $comment_arr[4] = preg_replace("/\b((http(s?):\/\/)|(www\.))([\w\.]+)([-~\/\w+\.-?]+)\b/i", "<a href=\"http$3://$4$5$6\" target=\"_blank\">$2$4$5$6</a>", $comment_arr[4]);
                    $comment_arr[4] = preg_replace("/([\w\.]+)(@)([-\w\.]+)/i", "<a href=\"mailto:$0\">$0</a>", $comment_arr[4]);
                    if ($config_reverse_comments == "yes") {
                        $output = str_replace("{comment-number}", $total_comments - $comment_number, $output);
                    } else {
                        $output = str_replace("{comment-number}", $comment_number+1, $output);
                    }
                    $output = str_replace("{mail}", "$comment_arr[2]", $output);
                    $output = str_replace("{date}", date($config_timestamp_comment, $comment_arr[0]+($config_date_adjust*60)), $output);
                    $output = str_replace("{offset}", timeoffset($config_date_adjust), $output);
                    if (!function_exists(tagdate)) {
                        function tagdate($match)
                        {
                            global $news_arr;
                            return date($match[1], $news_arr[0]+($config_date_adjust*60));
                        }
                    }
                    $output = preg_replace_callback('#\[date\](.*?)\[/date\]#i', tagdate, $output);
                    $output = preg_replace('#\[alt\](.*?),(.*?)\[/alt\]#i', (($alt%2)==0) ? '\\1' : '\\2', $output);
                    $output = str_replace("{comment-id}", $comment_number, $output);
                    $output = str_replace("{comment}", "<a name=\"".$comment_arr[0]."\"></a>$comment_arr[4]", $output);

                    $output = replace_comment("show", $output);
// loginout 0.73 / Login Box v1.0 - addblock
$output = preg_replace('/\\[logged-in\\](.*?)\\[\\/logged-in\\]/is', ($is_logged_in) ? '\\1' : '', $output);
                    $output = preg_replace('/\\[not-logged-in\\](.*?)\\[\\/not-logged-in\\]/is', ($is_logged_in) ? '' : '\\1', $output);
// loginout 0.73 / Login Box v1.0 - End addblock
// Valid Amps
$output = ascii_convert($output);
// Valid Amps
                                        echo $output;
                    $showed_comments++;
                    if ($comm_per_page != 0 and $comm_per_page == $showed_comments) {
                        break;
                    }
                }
            }
        }
        $alt++;
    }

    //----------------------------------
    // Prepare the Comment Pagination
    //----------------------------------

    $prev_next_msg = $template_comments_prev_next;

    // Previous link
    if (isset($comm_start_from) and $comm_start_from != "" and $comm_start_from > 0) {
        $prev = $comm_start_from - $comm_per_page;
        $prev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"$PHP_SELF?comm_start_from=$prev&amp;archive=$archive&amp;subaction=showcomments&amp;id=$id&amp;$user_query\">\\1</a>", $prev_next_msg);
    } else {
        if ($config_prevnext_firstlast == "yes") {
            $prev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", "\\1", $prev_next_msg);
        } else {
            $prev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", "", $prev_next_msg);
        }
        $no_prev = true;
    }

    // Pages
        if ($comm_per_page) {
            $pages_count = @ceil($total_comments/$comm_per_page);
            $pages_start_from = 0;
            $pages = "";
            for ($j=1;$j<=$pages_count;$j++) {
                if ($pages_start_from != $comm_start_from) {
                    $pages .= "<a href=\"$PHP_SELF?comm_start_from=$pages_start_from&amp;archive=$archive&amp;subaction=showcomments&amp;id=$id&amp;$user_query\">$j</a> ";
                } else {
                    $pages .= " <strong>$j</strong> ";
                }
                $pages_start_from += $comm_per_page;
            }
            $prev_next_msg = str_replace("{pages}", $pages, $prev_next_msg);
// Current/Total Pagination v1.0 - addblock
$prev_next_msg = str_replace('{total-pages}', $pages_count, $prev_next_msg);
            $prev_next_msg = str_replace('{current-page}', $comm_start_from / $comm_per_page + 1, $prev_next_msg);
// Current/Total Pagination v1.0 - End addblock
        }

    // Next link
    if ($comm_per_page < $total_comments and $comment_number < $total_comments) {
        $prev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"$PHP_SELF?comm_start_from=$comment_number&amp;archive=$archive&amp;subaction=showcomments&amp;id=$id&amp;$user_query\">\\1</a>", $prev_next_msg);
    } else {
        if ($config_prevnext_firstlast == "yes") {
            $prev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", "\\1", $prev_next_msg);
        } else {
            $prev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", "", $prev_next_msg);
        }
        $no_next = true;
    }
    $prev_next_msg = str_replace("{cute-http-path}", $config_http_script_dir, $prev_next_msg);
    if (!$no_prev or !$no_next) {
        echo $prev_next_msg;
    }
    if ($comment_form) {
        if (!function_exists(tagdate)) {
            function tagdate($match)
            {
                global $news_arr;
                return date($match[1], $news_arr[0]+($config_date_adjust*60));
            }
        }
        $template_form = preg_replace_callback('#\[date\](.*?)\[/date\]#i', tagdate, $template_form);

        $template_form = str_replace("{cute-http-path}", $config_http_script_dir, $template_form);
        $template_form = str_replace("{comment-max}", "$config_comment_max_long", $template_form);
        $template_form = str_replace("{character-limiter}", "<script type=\"text/javascript\">displaylimit('document.comment.comments',$config_comment_max_long)</script>", $template_form);

        $smilies_form = "\n<script type=\"text/javascript\">
        //<![CDATA[
        function insertext(text){
       document.comment.comments.value+=\" \"+ text;
        document.comment.comments.focus();
        }
        //]]></script>
        <noscript>Your browser is not Javascript enable or you have turn it off. We recommend you to activate, otherwise you will have to enter the emoticons representations manually.
        </noscript>".insertSmilies('short', false);

        $template_form = str_replace("{smilies}", $smilies_form, $template_form);
        $template_form = preg_replace('/\\{stored-name\\}/is', ($is_logged_in) ? $member_db[2] : '', $template_form);
// loginout 0.73 / Login Box v1.0 - addblock
$template_form = preg_replace('/\\[logged-in\\](.*?)\\[\\/logged-in\\]/is', ($is_logged_in) ? '\\1' : '', $template_form);
        $template_form = preg_replace('/\\[not-logged-in\\](.*?)\\[\\/not-logged-in\\]/is', ($is_logged_in) ? '' : '\\1', $template_form);
// loginout 0.73 / Login Box v1.0 - End addblock
echo"<form method=\"post\" name=\"comment\" id=\"comment\" action=\"\">".$template_form."<div><input type=\"hidden\" name=\"subaction\" value=\"addcomment\" /><input type=\"hidden\" name=\"ucat\" value=\"$ucat\" /><input type=\"hidden\" name=\"show\" value=\"$show\" />$user_post_query</div></form>\n";
    }
    if (!$is_logged_in) {
        $template_login = str_replace("{message}", "$message", $template_login);
        $template_login = str_replace("{cute-http-path}", "$config_http_script_dir", $template_login);
        $template_login = str_replace("{last-username}", $_COOKIE["lastusername"], $template_login);
        $check = " value=true onclick=\"document.login.enteredpw.focus();\" ";
        if ($_COOKIE["rememberpw"] || !$config_use_sessions) {
            $check = $check." checked=checked ";
        }
        if (!$config_use_sessions) {
            $check = $check." disabled=disabled ";
        }
        $template_login = str_replace("{check}", $check, $template_login);

        echo "
<script type=\"text/javascript\" src=\"$config_http_script_dir/md5.js\"></script><script type=\"text/javascript\">
function flogin(frm) {
	if(!md5_vm_test())
		alert(\"Your javascript doesn't work\");
	frm.password.value=hex_md5(hex_md5(frm.enteredpw.value)+frm.time.value);
	frm.enteredpw.value='';
}
</script>
<form method=\"post\" name=\"login\" id=\"login\" action=\"\" onSubmit=\"flogin(this)\">
".$template_login."
<div>
	<input type=\"hidden\" name=\"action\" value=\"dologin\" />
	<input type=\"hidden\" name=\"password\" value=\"\" />
	<input type=\"hidden\" name=\"time\" value=\"".time()."\" />
	$user_post_query
</div>
</form>\n
";
    }
}
//####################################################################################################################
//                 Active News
//####################################################################################################################

if ($allow_active_news) {
    $all_news = file("$news_file");
    if ($reverse == true) {
        $all_news = array_reverse($all_news);
    }

    $count_all = 0;
    if (isset($category) and $category != "") {
        foreach ($all_news as $null => $news_line) {
            if (eregi("<\?", $news_line)) {
                continue;
            }
            $news_arr = explode("|", $news_line);
// Author Filter v1.0 - addblock
if (isset($author) and
        !in_array(strtolower($news_arr[1]), explode(",", strtolower($author)))) {
    continue;
}
// Author Filter v1.0 - End addblock

// Negative Categories v1.0 - changeblock - Old
  // 			if($requested_cats and $requested_cats[$news_arr[6]] == TRUE){ $count_all ++; }
// Negative Categories v1.0 - changeblock - New
            $is_requested = ($requested_cats and $requested_cats[$news_arr[6]]);
            if ($negativeCategories != $is_requested) {
                $count_all++;
            }
// Negative Categories v1.0 - End changeblock
                else {
                    continue;
                }
        }
// Author Filter v1.0 - changeblock - Old
  // 	}else{ $count_all = count($all_news); }
// Author Filter v1.0 - changeblock - New
    } else {
        if (isset($author)) {
            foreach ($all_news as $null => $news_line) {
                if (eregi("<\?", $news_line)) {
                    continue;
                }
                $news_arr = explode("|", $news_line);
                if (in_array(strtolower($news_arr[1]), explode(",", strtolower($author)))) {
                    $count_all++;
                }
            }
        } else {
            $count_all = count($all_news)-1;
        }
    }
// Author Filter v1.0 - End changeblock

// Top Comments
$comm_count = file($comm_file);
    foreach ($all_news as $a => $all_news) {
        $news_arr = explode("|", $all_news[$a]);
        foreach ($comm_count as $null => $comm_line) {
            if (eregi("<\?", $comm_line)) {
                continue;
            }
            if (stristr($comm_line, $news_arr[0])) {
                $comm_split = explode("|>|", $comm_line);
                $comm_arr = explode("||", $comm_split[1]);
                $com_number = count($comm_arr)-1;
                $news_c[$a] = $com_number-1;
            }
        }
        foreach ($news_arr as $null => $news_value) {
            $all_news[$a] .= $news_value."|";
        }
        $all_news[$a] .= $news_c[$a]."||";
    }
// Top Comments

// Top Views
$count_file = file($cutepath."/data/counter.db.php");
    foreach ($all_news as $a => $all_news) {
        $news_arr = explode("|", $all_news[$a]);
        foreach ($count_file as $null => $count_line) {
            if (eregi("<\?", $count_line)) {
                continue;
            }
            if (stristr($count_line, $news_arr[0])) {
                $news_v[$a] = $news_arr[1];
            }
        }
        if ($news_v[$a] == "") {
            $news_v[$a] = "0";
        }
        foreach ($news_arr as $null => $news_value) {
            $all_news[$a] .= $news_value."|";
        }
        $all_news[$a] .= $news_v[$a]."||";
    }
// Top Views

// Sort News v1.1 - Start addblock
// To specify sorting, put the following in your include code:
// $sortby = X;
// Where X is a number from 0-6, which mean the following:
// 0 = sort by time
// 1 = sort by author
// 2 = sort by title
// 3 = sort by short story
// 4 = sort by long story
// 5 = sort by avatar
// 6 = sort by category
//
// To specify ascending/descending, put the following in your include code:
// $sortad = X;
// Where X is either "a" or "d"
//
// The lines below will automatically sort your news if news if the sort variables are unset
if (!isset($sortby)) {
    $sortby="0";
}
    if (!isset($sortad)) {
        $sortad="d";
    }

// DO NOT EDIT BELOW
if (isset($sortby)) {
    if (!function_exists('sortcmp')) {
        function sortcmp($a, $b)
        {
            global $all_news, $sortby;

            $news_a = explode('|', $all_news[$a]);
            $news_b = explode('|', $all_news[$b]);

            return strnatcasecmp($news_a[$sortby], $news_b[$sortby]);
        }
    }
    uksort($all_news, 'sortcmp');
    if ($sortad=="d") {
        $all_news = array_reverse($all_news);
    }
    unset($sortby);
}
// Sort News v1.1 - End addblock

// Sort by XField v1.0 - addblock

if (isset($sortbyxfield)) {
    $xfieldsaction = 'noop';
    include_once($cutepath.'/inc/xfields.mdu.php');
    if (!function_exists("sortcmp")) {
        function sortcmp($a, $b)
        {
            global $all_news, $xfieldsdata, $sortbyxfield;

            $news_a = explode('|', $all_news[$a]);
            $news_b = explode('|', $all_news[$b]);

            $newsid_a = $news_a[0];
            $newsid_b = $news_b[0];

            return strnatcasecmp($xfieldsdata[$newsid_a][$sortbyxfield], $xfieldsdata[$newsid_b][$sortbyxfield]);
        }
    }
    $xfieldsdata = xfieldsdataload();
    uksort($all_news, 'sortcmp');
    if ($sortad=="d") {
        $all_news = array_reverse($all_news);
    }
    unset($sortingorder);
}
// Sort by XField v1.0 - End addblock

    $i = 0;
    $showed = 0;
    $repeat = true;
    $url_archive = $archive;
    while ($repeat != false) {
        $alt = 0;

        foreach ($all_news as $null => $news_line) {
            if (eregi("<\?", $news_line)) {
                continue;
            }
            $news_arr = explode("|", $news_line);
// <!-- Start ModifyTime v2.0 -->
if ($config_prospective_posting == "yes" && ($news_arr[0]+($config_date_adjust*60)) > time()) {
    continue;
}
// <!-- End ModifyTime v2.0 -->
// Negative Categories v1.0 - changeblock - Old
  // 		if($category and $requested_cats[$news_arr[6]] != TRUE){ continue; }
// Negative Categories v1.0 - changeblock - New
            $is_requested = ($requested_cats and $requested_cats[$news_arr[6]]);
            if ($category and ($negativeCategories == $is_requested)) {
                continue;
            }
// Negative Categories v1.0 - End changeblock
// Author Filter v1.0 - addblock
if (isset($author) and
        !in_array(strtolower($news_arr[1]), explode(",", strtolower($author)))) {
    continue;
}
// Author Filter v1.0 - End addblock
        if (isset($start_from) and $start_from != "") {
            if ($i < $start_from) {
                $i++;
                continue;
            } elseif ($showed == $number) {
                break;
            }
        }

            if ($my_names[$news_arr[1]]) {
                $my_author = $my_names[$news_arr[1]];
            } else {
                $my_author = $news_arr[1];
            }

            $output = $template_active;
// Date Header
if ($show_dh == true) {
    if ($dateheader != date("mdY", $news_arr[0]+($config_date_adjust*60))) {
        $dateheader = date("mdY", $news_arr[0]+($config_date_adjust*60));
        if (!isset($template) || $template == "") {
            include("$cutepath/data/Default_dh.tpl.php");
        } else {
            include("$cutepath/data/".$template."_dh.tpl.php");
        }
        $template_active = str_replace("{archiveheader}", "", $template_active);
        $output = $template_active.$output;
        if (!isset($template) || $template == "") {
            include("$cutepath/data/Default.tpl.php");
        } else {
            include("$cutepath/data/".$template.".tpl.php");
        }
    }
}
// Date Header
$article_counter = file("$cutepath/data/counter.db.php");
            foreach ($article_counter as $null => $counter_line) {
                $count_arr = explode("|", $counter_line);
                if ($count_arr[0] == $news_arr[0]) {
                    $output = str_replace("{views}", $count_arr[1], $output);
                }
            }
            $output = str_replace("{views}", "0", $output);
            $output = str_replace("{title}", $news_arr[2], $output);
            $output = str_replace("{date}", date($config_timestamp_active, $news_arr[0]+($config_date_adjust*60)), $output);
            $output = str_replace("{offset}", timeoffset($config_date_adjust), $output);
            if (!function_exists(tagdate)) {
                function tagdate($match)
                {
                    global $news_arr;
                    return date($match[1], $news_arr[0]+($config_date_adjust*60));
                }
            }
            $output = preg_replace_callback('#\[date\](.*?)\[/date\]#i', tagdate, $output);
            $output = preg_replace('#\[alt\](.*?),(.*?)\[/alt\]#i', (($alt%2)==0) ? '\\1' : '\\2', $output);
            $output = str_replace("{author}", $my_author, $output);
            if ($news_arr[5] != "") {
                $output = str_replace("{avatar}", "<img alt=\"\" src=\"$news_arr[5]\" style=\"border: none;\" />", $output);
            } else {
                $output = str_replace("{avatar}", "", $output);
            }
            $output = str_replace("{avatar-url}", "$news_arr[5]", $output);
            $output = str_replace("[link]", "<a href=\"$PHP_SELF?subaction=showfull&amp;id=$news_arr[0]&amp;archive=$archive&amp;start_from=$my_start_from&amp;ucat=$news_arr[6]&amp;$user_query\">", $output);
            $output = str_replace("[/link]", "</a>", $output);
            $output = str_replace("{comments-num}", countComments($news_arr[0], $archive), $output);
            $output = str_replace("{short-story}", $news_arr[3], $output);
            $output = str_replace("{full-story}", $news_arr[4], $output);
            $output = str_replace("{category}", $cat[$news_arr[6]], $output);
            $output = str_replace("{category-id}", $news_arr[6], $output);
            if ($cat_icon[$news_arr[6]] != "") {
                $output = str_replace("{category-icon}", "<img alt=\"".$cat[$news_arr[6]]." icon\" style=\"border: none;\" src=\"".$cat_icon[$news_arr[6]]."\" />", $output);
            } else {
                $output = str_replace("{category-icon}", "", $output);
            }

            $output = str_replace("{author-name}", $name_to_nick[$news_arr[1]], $output);
            $output = str_replace("{author-lower}", strtolower($news_arr[1]), $output);

            if ($my_mails[$news_arr[1]] != "") {
                $output = str_replace("[mail]", "<a href=\"mailto:".$my_mails[$news_arr[1]]."\">", $output);
                $output = str_replace("[/mail]", "</a>", $output);
            } else {
                $output = str_replace("[mail]", "", $output);
                $output = str_replace("[/mail]", "", $output);
            }

            $output = str_replace("{news-id}", $news_arr[0], $output);
            $output = str_replace("{archive-id}", $archive, $output);
            $output = str_replace("{php-self}", $PHP_SELF, $output);
            $output = str_replace("{cute-http-path}", $config_http_script_dir, $output);
// XFields v2.1 - addblock
$xfieldsaction = "templatereplace";
            $xfieldsinput = $output;
            $xfieldsid = $news_arr[0];
            include("xfields.mdu.php");
            $output = $xfieldsoutput;
// XFields v2.1 - End addblock
        $output = replace_news("show", $output);


            if ($news_arr[4] != "" or $action == "showheadlines") {//if full story
            if ($config_full_popup == "yes") {
                $output = preg_replace("/\\[full-link\\]/", "<a href=\"#\" onclick=\"window.open('$config_http_script_dir/show_news.php?subaction=showfull&amp;id=$news_arr[0]&amp;archive=$archive&amp;template=$template', '_News', '$config_full_popup_string');return false;\">", $output);
            } else {
                $output = str_replace("[full-link]", "<a href=\"$PHP_SELF?subaction=showfull&amp;id=$news_arr[0]&amp;archive=$archive&amp;start_from=$my_start_from&amp;ucat=$news_arr[6]&amp;$user_query\">", $output);
            }
                $output = str_replace("[/full-link]", "</a>", $output);
            } else {
                $output = preg_replace("'\\[full-link\\].*?\\[/full-link\\]'si", "<!-- no full story-->", $output);
            }

            if ($config_comments_popup == "yes") {
                $output = str_replace("[com-link]", "<a href=\"#\" onclick=\"window.open('$config_http_script_dir/show_news.php?subaction=showcomments&amp;template=$template&amp;id=$news_arr[0]&amp;archive=$archive&amp;start_from=$my_start_from&amp;ucat=$news_arr[6]', '_News', '$config_comments_popup_string');return false;\">", $output);
            } else {
                $output = str_replace("[com-link]", "<a href=\"$PHP_SELF?subaction=showcomments&amp;id=$news_arr[0]&amp;archive=$archive&amp;start_from=$my_start_from&amp;ucat=$news_arr[6]&amp;$user_query\">", $output);
            }
            $output = str_replace("[/com-link]", "</a>", $output);

            $output = profiledata($name_to_nick[$news_arr[1]], $output);

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

// Valid Amps
$output = ascii_convert($output);
// Valid Amps
                        echo $output;
            $showed++;
            $i++;

            if ($number != 0 and $number == $i) {
                break;
            }
            $alt++;
        }
        if ($output == "") {
            echo "Nothing To Display";
        }
        $used_archives[$archive] = true;
// Archives Looop
        if ($i < $number and $only_active != true) {
            if (!$handle = opendir("$cutepath/data/archives")) {
                die("<div style=\"text-align: center;\">Can not open directory $cutepath/data/archives</div>");
            }
            while (false !== ($file = readdir($handle))) {
                if ($file != "." and $file != ".." and eregi("news.arch.php", $file)) {
                    $file_arr = explode(".", $file);
                    $archives_arr[$file_arr[0]] = $file_arr[0];
                }
            }
            closedir($handle);

            $archives_arr[$in_use]="";
            $in_use = max($archives_arr);

            if ($in_use != "" and !$used_archives[$in_use]) {
                $all_news = file("$cutepath/data/archives/$in_use.news.arch.php");
                $archive = $in_use;
                $used_archives[$in_use] = true;
            } else {
                $repeat = false;
            }
        } else {
            $repeat = false;
        }
    }

// << Previous   &   Next >>

    $prev_next_msg = $template_prev_next;

    //----------------------------------
    // Previous link
    //----------------------------------
    if (isset($start_from) and $start_from != "" and $start_from > 0) {
        $prev = $start_from - $number;
        $prev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"$PHP_SELF?start_from=$prev&amp;ucat=$ucat&amp;archive=$url_archive&amp;subaction=$subaction&amp;id=$id&amp;$user_query\">\\1</a>", $prev_next_msg);
    } else {
        if ($config_prevnext_firstlast == "yes") {
            $prev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", "\\1", $prev_next_msg);
        } else {
            $prev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", "", $prev_next_msg);
        }
        $no_prev = true;
    }

    //----------------------------------
    // Pages
    //----------------------------------
    if ($number) {
        $pages_count = @ceil($count_all/$number);
        $pages_start_from = 0;
        $pages = "";
        for ($j=1;$j<=$pages_count;$j++) {
            if ($pages_start_from != $start_from) {
                $pages .= "<a href=\"$PHP_SELF?start_from=$pages_start_from&amp;ucat=$ucat&amp;archive=$url_archive&amp;subaction=$subaction&amp;id=$id&amp;$user_query\">$j</a> ";
            } else {
                $pages .= " <strong>$j</strong> ";
            }
            $pages_start_from += $number;
        }
        $prev_next_msg = str_replace("{pages}", $pages, $prev_next_msg);
// Current/Total Pagination v1.0 - addblock
$prev_next_msg = str_replace('{total-pages}', $pages_count, $prev_next_msg);
        $prev_next_msg = str_replace('{current-page}', $start_from / $number + 1, $prev_next_msg);
// Current/Total Pagination v1.0 - End addblock
    }
    //----------------------------------
    // Next link  (typo here ... typo there... typos everywhere !)
    //----------------------------------
    if ($number < $count_all and $i < $count_all) {
        $prev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"$PHP_SELF?start_from=$i&amp;ucat=$ucat&amp;archive=$url_archive&amp;subaction=$subaction&amp;id=$id&amp;$user_query\">\\1</a>", $prev_next_msg);
    } else {
        if ($config_prevnext_firstlast == "yes") {
            $prev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", "\\1", $prev_next_msg);
        } else {
            $prev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", "", $prev_next_msg);
        }
        $no_next = true;
    }
    $prev_next_msg = str_replace("{cute-http-path}", $config_http_script_dir, $prev_next_msg);
    if (!$no_prev or !$no_next) {
        echo $prev_next_msg;
    }
}
} while (0);
