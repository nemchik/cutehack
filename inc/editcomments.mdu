<?php

if ($member_db[1] > 3) {
    msg("error", "Access Denied", "You don't have permission to edit comments");
}
$offset = timeoffset($config_date_adjust);
// ********************************************************************************
// Edit Comment
// ********************************************************************************
if ($action == "editcomment") {
    if ($source == "") {
        $all_comments = file("./data/comments.db.php");
    } else {
        $all_comments = file("./data/archives/${source}.comments.arch.php");
    }

    foreach ($all_comments as $null => $comment_line) {
        $comment_line_arr = explode("|>|", $comment_line);
        if ($comment_line_arr[0] == $newsid) {
            $comment_arr = explode("||", $comment_line_arr[1]);
            foreach ($comment_arr as $null => $single_comment) {
                $single_arr = explode("|", $single_comment);
                if ($comid == $single_arr[0]) {
                    break;
                }
            }
        }
    }

    $single_arr[4] = str_replace("<br />", "\n", $single_arr[4]);
    $comdate       = date("D, d F Y h:i:s", ($single_arr[0]+($config_date_adjust*60)));

    echo"<html>
    <head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\">
    <title>Edit Comment</title>
$skin_css
    </head>
    <body bgcolor=\"#FFFFFF\">
    <form method=post action=\"$PHP_SELF\">
    <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
    <td width=\"1108\" height=\"8%\" colspan=\"2\">
    <div class=header>Edit Comment</div>
    <tr>
    <td height=20 valign=middle width=\"102\" bgcolor=\"#F9F8F7\">
    Poster
    <td height=20 valign=middle width=\"1002\" bgcolor=\"#F9F8F7\">
    <input type=text name=poster value=\"$single_arr[1]\">
    </tr>

    <tr>
    <td height=20 valign=middle valign=\"top\" width=\"102\">
    Email
    <td height=20 valign=middle width=\"1002\">
    <input type=text name=mail value=\"$single_arr[2]\">
    </tr>

    <tr>
    <td height=20 valign=middle valign=\"top\" width=\"102\" bgcolor=\"#F9F8F7\">
    IP
    <td height=20 valign=middle width=\"1002\" bgcolor=\"#F9F8F7\">
    <a href=\"http://www.ripe.net/perl/whois?searchtext=$single_arr[3]\" target=_blank title=\"Get more information about this ip\">$single_arr[3]</a>
    </tr>

    <tr>
    <td height=20 valign=middle valign=\"top\" width=\"102\">
    Date
    <td height=20 valign=middle width=\"1002\">
    $comdate ($offset)
    </tr>
    <tr>
    <td height=20 valign=middle  width=\"102\" bgcolor=\"#F9F8F7\">
    Comments&nbsp;
    <td  height=20 valign=middle width=\"1002\" bgcolor=\"#F9F8F7\">
    <textarea rows=\"8\" name=\"comment\" cols=\"45\">".preg_replace(array("'\\&#039;'", "''", "''"), array("&#039;", "", ""), $single_arr[4])."</textarea>
    </tr>
    <tr>
    <td  valign=\"top\" width=\"1104\" colspan=\"2\">
    <p align=\"left\"><br />
    <input type=submit value=\"Save Changes\" accesskey=\"s\">&nbsp; <input type=button value=Cencel onClick=\"window.close();\" accesskey=\"c\">
    <input type=hidden name=mod value=editcomments>
    <input type=hidden name=newsid value=$newsid>
    <input type=hidden name=comid value=$comid>
    <input type=hidden name=source value=$source>
    <input type=hidden name=action value=doeditcomment>
    </tr>
    </table>
    </form>
    </body>
    </html>";
}
// ********************************************************************************
// Do Save Comment
// ********************************************************************************
elseif ($action == "doeditcomment") {
    if (!$poster and !$deletecomment) {
        echo"<br /><br /><br />The poster can not be blank !!!";
        exit();
    }
    if ($mail == "" and !$deletecomment) {
        $mail = "none";
    }
    if ($poster == "" and !$deletecomment) {
        $poster = "Anonymous";
    }
    if ($comment == "" and !$deletecomment) {
        die("comment can not be blank");
    }

    $comment = replace_comment("add", $comment);

    if ($source == "") {
        $news_file = "./data/news.db.php";
        $com_file = "./data/comments.db.php";
    } else {
        $news_file = "./data/archives/$source.news.arch.php";
        $com_file = "./data/archives/$source.comments.arch.php";
    }

    $old_com = file("$com_file");
    $new_com = fopen("$com_file", "w");
    fwrite($new_com, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_com as $null => $line) {
        if (eregi("<\?", $line)) {
            continue;
        }
        $line_arr = explode("|>|", $line);
        if ($line_arr[0] == $newsid) {
            fwrite($new_com, "$line_arr[0]|>|");


            $comments = explode("||", $line_arr[1]);
            foreach ($comments as $null => $single_comment) {
                $single_comment = trim($single_comment);
                $comment_arr = ascii_convert(explode("|", $single_comment));
                if ($comment_arr[0] == $comid and $comment_arr[0] != "" and $delcomid != "all") {
                    fwrite($new_com, "$comment_arr[0]|$poster|$mail|$comment_arr[3]|$comment||");
                } elseif ($delcomid[$comment_arr[0]] != 1 and $comment_arr[0] != ""  and $delcomid[all] != 1) {
                    fwrite($new_com, "$comment_arr[0]|$comment_arr[1]|$comment_arr[2]|$comment_arr[3]|$comment_arr[4]||");
                }
            }
            fwrite($new_com, "\n");
        } else {
            fwrite($new_com, "$line");
        }
    }

    if (isset($deletecomment) and $delcomid[all] == 1) {
        msg("info", "Comments Deleted", "All comments were deleted.", "$PHP_SELF?mod=editnews&action=editnews&id=$newsid&source=$source");
    } elseif (isset($deletecomment) and isset($delcomid)) {
        msg("info", "Comment Deleted", "The selected comment(s) has been deleted.", "$PHP_SELF?mod=editnews&action=editnews&id=$newsid&source=$source");
    } else {
        echo"<br /><br /><br /><br /><center><b>Comment is saved.";
    }
}
