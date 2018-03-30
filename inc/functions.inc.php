<?php

if ($HTTP_SESSION_VARS) {
    extract($HTTP_SESSION_VARS, EXTR_SKIP);
}
if ($_SESSION) {
    extract($_SESSION, EXTR_SKIP);
}
if ($HTTP_COOKIE_VARS) {
    extract($HTTP_COOKIE_VARS, EXTR_SKIP);
}
if ($_COOKIE) {
    extract($_COOKIE, EXTR_SKIP);
}
if ($HTTP_POST_VARS) {
    extract($HTTP_POST_VARS, EXTR_SKIP);
}
if ($_POST) {
    extract($_POST, EXTR_SKIP);
}
if ($HTTP_GET_VARS) {
    extract($HTTP_GET_VARS, EXTR_SKIP);
}
if ($_GET) {
    extract($_GET, EXTR_SKIP);
}
if ($HTTP_ENV_VARS) {
    extract($HTTP_ENV_VARS, EXTR_SKIP);
}
if ($_ENV) {
    extract($_ENV, EXTR_SKIP);
}


if ($PHP_SELF == "") {
    $PHP_SELF = $_SERVER["PHP_SELF"];
}

$phpversion = @phpversion();

$a7f89abdcf9324b3 = "";

$config_version_name = "CuteHack v0.1.9";
$config_version_id = 999;

///////////////////////////////////////////////////////
// Function:         timeoffser
// Description: give the time offset

function timeoffset($date_adjust)
{
    $offtime = (($date_adjust*60)/3600);
    if (strstr($offtime, "-")) {
        $sin = "-";
        $offtime = str_replace("-", "", $offtime);
    } else {
        $sin = "+";
    }
    if (strstr($offtime, ".")) {
        $hrsmin = explode(".", $offtime);
        $hrs = $hrsmin[0];
        if ($hrs <= 9) {
            $hrs = "0$hrs";
        }
        $min = $hrsmin[1]/(1/60);
        if ($min <= 9) {
            $min = "0$min";
        }
    } else {
        $hrs = $offtime;
        if ($hrs <= 9) {
            $hrs = "0$hrs";
        }
        $min = "00";
    }
    $offset = "GMT $sin$hrs:$min";
    return $offset;
}

///////////////////////////////////////////////////////
// Function:         HTML Fixes
// Description: Fixes HTML issues

$comm_start_from = htmlspecialchars($comm_start_from);
$start_from = htmlspecialchars($start_from);
$archive = htmlspecialchars($archive);
$subaction = htmlspecialchars($subaction);
$id = htmlspecialchars($id);
$ucat = htmlspecialchars($ucat);
$category = htmlspecialchars($category);
$number = htmlspecialchars($number);
$template = htmlspecialchars($template);

///////////////////////////////////////////////////////
// Function:         formatsize
// Description: Format the size of given file

function formatsize($file_size)
{
    if ($file_size >= 1073741824) {
        $file_size = round($file_size / 1073741824 * 100) / 100 . "Gb";
    } elseif ($file_size >= 1048576) {
        $file_size = round($file_size / 1048576 * 100) / 100 . "Mb";
    } elseif ($file_size >= 1024) {
        $file_size = round($file_size / 1024 * 100) / 100 . "Kb";
    } else {
        $file_size = $file_size . "b";
    }

    return $file_size;
}

///////////////////////////////////////////////////////
// Function:         check_login
// Description: Check login information

function check_login($fusername, $fpassword, $ftime, $fextravar)
{
    global $cutepath, $member_db;
    $result = false;
    $full_member_db = file($cutepath."/data/users.db.php");
    foreach ($full_member_db as $null => $member_db_line) {
        if (!eregi("<\?", $member_db_line)) {
            $member_db = explode("|", $member_db_line);
            if ($fextravar=="session") {
                if (strtolower($member_db[2]) == strtolower($fusername) and md5($member_db[3].$ftime) == $fpassword and $ftime!=time() and $ftime+3600>=$member_db[9]) {
                    $result = true;
                    break;
                }
            }
            if (strtolower($member_db[2]) == strtolower($fusername) and md5($member_db[3].$ftime) == $fpassword and $ftime!=time() and $ftime>=$member_db[9]) {
                $result = true;
                break;
            }
        }
    }
    return $result;
}

///////////////////////////////////////////////////////
// Function:         cute_query_string
// Description: Format the Query_String for CuteNews purpuses index.php?

function cute_query_string($q_string, $strips, $type="get")
{
    foreach ($strips as $null => $key) {
        $strips[$key] = true;
    }
    $var_value = explode("&", $q_string);

    foreach ($var_value as $null => $var_peace) {
        $parts = explode("=", $var_peace);
        if ($strips[$parts[0]] != true and $parts[0] != "") {
            if ($type == "post") {
                $my_q .= "<input type=\"hidden\" name=\"".$parts[0]."\" value=\"".$parts[1]."\" />\n";
            } else {
                if ($parts[0] == "PHPSESSID") {
                    $my_q = $my_q;
                } else {
                    $my_q .= "$var_peace&amp;";
                }
            }
        }
    }

    if (substr($my_q, -5) == "&amp;") {
        $my_q = substr($my_q, 0, -5);
    }
    return $my_q;
}

///////////////////////////////////////////////////////
// Function:        Flooder
// Description: Flood Protection Function
function flooder($ip, $comid)
{
    global $cutepath, $config_flood_time;

    $old_db = file("$cutepath/data/flood.db.php");
    $new_db = fopen("$cutepath/data/flood.db.php", w);
    fwrite($new_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    $result = false;
    foreach ($old_db as $null => $old_db_line) {
        if (eregi("<\?", $old_db_line)) {
            continue;
        }
        $old_db_arr = explode("|", $old_db_line);

        if (($old_db_arr[0] + $config_flood_time) > time()) {
            fwrite($new_db, $old_db_line);
            if ($old_db_arr[1] == $ip and $old_db_arr[2] == $comid) {
                $result = true;
            }
        }
    }
    fclose($new_db);
    return $result;
}

////////////////////////////////////////////////////////
// Function:         msg
// Description: Displays message to user

function msg($type, $title, $text, $back=false)
{
    echoheader($type, $title);
    global $lang;
    echo"<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td >$text";
    if ($back) {
        echo"<br /><br /> <a href=\"$back\">go back</a>";
    }
    echo"</td></tr></table>";
    echofooter();
    exit();
}



////////////////////////////////////////////////////////
// Function:         echoheader
// Description: Displays header skin

function echoheader($image, $header_text)
{
    global $PHP_SELF, $is_logged_in, $config_skin, $skin_header, $lang_content_type, $skin_menu, $skin_prefix, $config_version_name;

    if ($is_logged_in == true) {
        $skin_header = preg_replace("/{menu}/", "$skin_menu", "$skin_header");
    } else {
        $skin_header = preg_replace("/{menu}/", " &nbsp; $config_version_name", "$skin_header");
    }

    $skin_header = get_skin($skin_header);
    $skin_header = preg_replace("/{image-name}/", "${skin_prefix}${image}", $skin_header);
    $skin_header = preg_replace("/{header-text}/", $header_text, $skin_header);
    $skin_header = preg_replace("/{content-type}/", $lang_content_type, $skin_header);
    $skin_header = preg_replace("/{css}/", $skin_css, $skin_header);

    echo $skin_header;
}

////////////////////////////////////////////////////////
// Function:         echofooter
// Description: Displays footer skin

function echofooter()
{
    global $PHP_SELF, $is_logged_in, $config_skin, $skin_footer, $lang_content_type, $skin_menu, $skin_prefix, $config_version_name;

    if ($is_logged_in == true) {
        $skin_footer = preg_replace("/{menu}/", "$skin_menu", "$skin_footer");
    } else {
        $skin_footer = preg_replace("/{menu}/", " &nbsp; $config_version_name", "$skin_footer");
    }

    $skin_footer = get_skin($skin_footer);
    $skin_footer = preg_replace("/{image-name}/", "${skin_prefix}${image}", $skin_footer);
    $skin_footer = preg_replace("/{header-text}/", $header_text, $skin_footer);
    $skin_footer = preg_replace("/{content-type}/", $lang_content_type, $skin_footer);
    $skin_footer = preg_replace("/{css}/", $skin_css, $skin_footer);

    // Do not remove the Copyrights!
    $skin_footer = preg_replace("/{copyrights}/", "<span style='font-size: 9px'>Powered by <a style='font-size: 9px' href=\"http://cutephp.com/cutenews/\" target=_blank>$config_version_name</a> � 2004  <a style='font-size: 9px' href=\"http://cutephp.com/\" target=_blank>CutePHP</a>.</span>", $skin_footer);

    echo $skin_footer;
}

////////////////////////////////////////////////////////
// Function:         b64dck
// Description: And the duck fly away.
function b64dck()
{
    $cr = bd_config('e2NvcHlyaWdodHN9');
    $shder = bd_config('c2tpbl9oZWFkZXI=');
    $sfter = bd_config('c2tpbl9mb290ZXI=');
    global $$shder,$$sfter;
    $HDpnlty = bd_config('PGNlbnRlcj48aDE+Q3V0ZU5ld3M8L2gxPjxhIGhyZWY9Imh0dHA6Ly9jdXRlcGhwLmNvbSI+Q3V0ZVBIUC5jb208L2E+PC9jZW50ZXI+PGJyPg==');
    $FTpnlty = bd_config('PGNlbnRlcj48c3BhbiBkaXNwbGF5PWlubGluZSBzdHlsZT0iZm9udC1zaXplOiAxMXB4Ij5Qb3dlcmVkIGJ5IDxhIHN0eWxlPSJmb250LXNpemU6IDExcHgiIGhyZWY9Imh0dHA6Ly9jdXRlcGhwLmNvbS9jdXRlbmV3cy8iIHRhcmdldD1fYmxhbms+Q3V0ZUhhY2s8L2E+IKkgMjAwNSAgPGEgc3R5bGU9ImZvbnQtc2l6ZTogMTFweCIgaHJlZj0iaHR0cDovL2N1dGVwaHAuY29tLyIgdGFyZ2V0PV9ibGFuaz5DdXRlUEhQPC9hPi48L3NwYW4+PC9jZW50ZXI+');

    if (!stristr($$shder, $cr) and !stristr($$sfter, $cr)) {
        $$shder = $HDpnlty.$$shder;
        $$sfter = $$sfter.$FTpnlty;
    }
}
////////////////////////////////////////////////////////
// Function:         CountComments
// Description: Count How Many Comments Have a Specific Article

function CountComments($id, $archive = false)
{
    global $cutepath;

    if ($cutepath == "") {
        $cutepath = ".";
    }
    $result = "0";
    if ($archive) {
        $all_comments = file("$cutepath/data/archives/${archive}.comments.arch.php");
    } else {
        $all_comments = file("$cutepath/data/comments.db.php");
    }

    foreach ($all_comments as $null => $comment_line) {
        if (eregi("<\?", $comment_line)) {
            continue;
        }
        $comment_arr_1 = explode("|>|", $comment_line);
        if ($comment_arr_1[0] == $id) {
            $comment_arr_2 = explode("||", $comment_arr_1[1]);
            $result = count($comment_arr_2)-1;
        }
    }

    return $result;
}

////////////////////////////////////////////////////////
// Function:         insertSmilies
// Description: insert smilies for adding into news/comments

function insertSmilies($insert_location, $break_location = false)
{
    global $config_http_script_dir, $config_smilies;

    $smilies = explode(",", $config_smilies);
    foreach ($smilies as $null => $smile) {
        $i++;
        $smile = trim($smile);

        $output .= "<a href=\"javascript:insertext(':$smile:','$insert_location')\"><img style=\"border: none;\" alt=\"$smile\" src=\"$config_http_script_dir/data/emoticons/$smile.gif\" /></a>";
        if ($i%$break_location == 0 and $break_location) {
            $output .= "<br />";
        } else {
            $output .= "&nbsp;";
        }
    }
    return $output;
}

////////////////////////////////////////////////////////
// Function:         replace_comments
// Description: Replaces comments charactars
function replace_comment($way, $sourse)
{
    global $config_allow_html_in_news, $config_allow_html_in_comments, $config_http_script_dir, $config_smilies;

    $sourse = stripslashes(trim($sourse));

    if ($way == "add") {
        $find = array();
        $find[] = "'\"'";
        $find[] = "'\''";
        $find[] = "'<'";
        $find[] = "'>'";
        $find[] = "'\|'";
        $find[] = "'\n'";
        $find[] = "'\r'";

        $replace = array();
        $replace[] = "&#034;";
        $replace[] = "&#039;";
        $replace[] = "&#060;";
        $replace[] = "&#062;";
        $replace[] = "&#124;";
        $replace[] = " <br />";
        $replace[] = "";
    } elseif ($way == "show") {
        $find = array();
        $find[] = "'\[b\](.*?)\[/b\]'i";
        $find[] = "'\[i\](.*?)\[/i\]'i";
        $find[] = "'\[u\](.*?)\[/u\]'i";
        $find[] = "'\[link\](.*?)\[/link\]'i";
        $find[] = "'\[link=(.*?)\](.*?)\[/link\]'i";

        $find[] = "'\[quote=(.*?)\](.*?)\[/quote\]'";
        $find[] = "'\[quote\](.*?)\[/quote\]'";

        $replace = array();
        $replace[] = "<strong>\\1</strong>";
        $replace[] = "<em>\\1</em>";
        $replace[] = "<span style=\"text-decoration: underline;\">\\1</span>";
        $replace[] = "<a href=\"\\1\">\\1</a>";
        $replace[] = "<a href=\"\\1\">\\2</a>";

        $replace[] = "<blockquote><div style=\"font-size: 13px;\">quote (\\1):</div><hr style=\"border: 1px solid #ACA899;\" /><div>\\2</div><hr style=\"border: 1px solid #ACA899;\" /></blockquote>";
        $replace[] = "<blockquote><div style=\"font-size: 13px;\">quote:</div><hr style=\"border: 1px solid #ACA899;\" /><div>\\1</div><hr style=\"border: 1px solid #ACA899;\" /></blockquote>";

        $smilies_arr = explode(",", $config_smilies);
        foreach ($smilies_arr as $null => $smile) {
            $smile = trim($smile);
            $find[] = "':$smile:'";
            $replace[] = "<img style=\"border: none;\" alt=\"$smile\" src=\"$config_http_script_dir/data/emoticons/$smile.gif\" />";
        }
    }

    $sourse = preg_replace($find, $replace, $sourse);
    $source = ascii_convert($source);
    return $sourse;
}
////////////////////////////////////////////////////////
// Function:         get_skin
// Description: Hello skin!

function get_skin($skin)
{
    $msn = bd_config('c2tpbg==');
    $cr = bd_config('e2NvcHlyaWdodHN9');
    $lct = bd_config('PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogOXB4Ij5Qb3dlcmVkIGJ5IDxhIHN0eWxlPSJmb250LXNpemU6IDlweCIgaHJlZj0iaHR0cDovL2N1dGVwaHAuY29tL2N1dGVuZXdzLyIgdGFyZ2V0PV9ibGFuaz5DdXRlSGFjayAwLjIuMDwvYT4gqSAyMDA1ICA8YSBzdHlsZT0iZm9udC1zaXplOiA5cHgiIGhyZWY9Imh0dHA6Ly9jdXRlcGhwLmNvbS8iIHRhcmdldD1fYmxhbms+Q3V0ZVBIUDwvYT4uPC9zcGFuPg==');

    $$msn = preg_replace("/$cr/", $lct, $$msn);

    return $$msn;
}

////////////////////////////////////////////////////////
// Function:         replace_news
// Description: Replaces news charactars

function replace_news($way, $sourse, $replce_n_to_br=true, $use_html=true)
{
    global $config_allow_html_in_news, $config_allow_html_in_comments, $config_http_script_dir, $config_smilies;
    $sourse = stripslashes($sourse);

    if ($way == "show") {
        $find= array(

/* 1 */                              "'\[upimage=([^\]]*?) ([^\]]*?)\]'i",
/* 2 */                                        "'\[upimage=(.*?)\]'i",
/* 3 */                                        "'\[b\](.*?)\[/b\]'i",
/* 4 */                                        "'\[i\](.*?)\[/i\]'i",
/* 5 */                                        "'\[u\](.*?)\[/u\]'i",
/* 6 */                                        "'\[link\](.*?)\[/link\]'i",
/* 7 */                                        "'\[color=(.*?)\](.*?)\[/color\]'i",
/* 8 */                                        "'\[size=(.*?)\](.*?)\[/size\]'i",
/* 9 */                                        "'\[font=(.*?)\](.*?)\[/font\]'i",
/* 10 */                                 "'\[align=(.*?)\](.*?)\[/align\]'i",
/* 12 */                                 "'\[image=(.*?)\]'i",
/* 13 */                                 "'\[link=(.*?)\](.*?)\[/link\]'i",

/* 14 */                "'\[quote=(.*?)\](.*?)\[/quote\]'i",
/* 15 */                "'\[quote\](.*?)\[/quote\]'i",

/* 16 */                "'\[list\]'i",
/* 17 */                "'\[/list\]'i",
/* 18 */                "'\[\*\]'i",

                            "'{nl}'",
                       );

        $replace=array(

/* 1 */                                        "<img \\2 src=\"${config_http_script_dir}/skins/images/upskins/images/\\1\" style=\"border: none;\" alt=\"\" />",
/* 2 */                                        "<img src=\"${config_http_script_dir}/skins/images/upskins/images/\\1\" style=\"border: none;\" alt=\"\" />",
/* 3 */                                        "<strong>\\1</strong>",
/* 4 */                                        "<em>\\1</em>",
/* 5 */                                        "<span style=\"text-decoration: underline;\">\\1</span>",
/* 6 */                                        "<a href=\"\\1\">\\1</a>",
/* 7 */                                        "<span style=\"color: \\1;\">\\2</span>",
/* 8 */                                        "<span style=\"font-size: \\1pt;\">\\2</span>",
/* 9 */                                        "<span style=\"font-family: \\1;\">\\2</span>",
/* 10 */                                "<div style=\"text-align: \\1;\">\\2</div>",
/* 12 */                                "<img src=\"\\1\" style=\"border: none;\" alt=\"\" />",
/* 13 */                                "<a href=\"\\1\">\\2</a>",

/* 14 */                                "<blockquote><div style=\"font-size: 13px;\">quote (\\1):</div><hr style=\"border: 1px solid #ACA899;\" /><div>\\2</div><hr style=\"border: 1px solid #ACA899;\" /></blockquote>",
/* 15 */                                "<blockquote><div style=\"font-size: 13px;\">quote:</div><hr style=\"border: 1px solid #ACA899;\" /><div>\\1</div><hr style=\"border: 1px solid #ACA899;\" /></blockquote>",

/* 16 */                                "<ul>",
/* 17 */                                "</ul>",
/* 18 */                                "<li>",

                                            "\n",
                        );

        $smilies_arr = explode(",", $config_smilies);
        foreach ($smilies_arr as $null => $smile) {
            $smile = trim($smile);
            $find[] = "':$smile:'";
            $replace[] = "<img style=\"border: none;\" alt=\"$smile\" src=\"$config_http_script_dir/data/emoticons/$smile.gif\" />";
        }
    } elseif ($way == "add") {
        $find = array();
        $find[] = "'\|'";
        $find[] = "'\r'";

        $replace = array();
        $replace[] = "&#124;";
        $replace[] = "";

        if ($use_html != true) {
            $find[]         = "'<'";
            $find[]         = "'>'";
            $replace[]         = "&#060;";
            $replace[]         = "&#062;";
        }
        if ($replce_n_to_br == true) {
            $find[]         = "'\n'";
            $replace[]         = "<br />";
        } else {
            $find[]         = "'\n'";
            $replace[]         = "{nl}";
        }
    } elseif ($way == "admin") {
        $find = array(
                                        "''",
                                        "'<br />'",
                                        "'{nl}'",
                    );
        $replace = array(
                                        "",
                                        "\n",
                                        "\n",
                         );
    }

    $sourse = preg_replace($find, $replace, $sourse);
    $source = ascii_convert($source);
    return $sourse;
}

function bd_config($str)
{
    return base64_decode($str);
}

////////////////////////////////////////////////////////
// Function:         cute_mail Version: 1.0a
// Description: Send mail with cutenews
function cute_mail($to, $subject, $message)
{
    // PHP Mail Headers
//  http://us2.php.net/manual/en/function.mail.php
if (!isset($config_mail_admin_address) || $config_mail_admin_address == "") {
    $mail_from = "webmaster@".str_replace("www.", "", $_SERVER['SERVER_NAME']);
} else {
    $mail_from = $config_mail_admin_address;
}
    $mail_headers = "";
    $mail_headers .= "From: $mail_from\n";
    $mail_headers .= "Reply-to: $mail_from\n";
    $mail_headers .= "Return-Path: $mail_from\n";
    $mail_headers .= "Message-ID: <" . md5(uniqid(time())) . "@" . $_SERVER['SERVER_NAME'] . ">\n";
    $mail_headers .= "MIME-Version: 1.0\n";
    $mail_headers .= "Content-type: text/plain; charset=US-ASCII\n";
    $mail_headers .= "Content-transfer-encoding: 7bit\n";
    $mail_headers .= "Date: " . date('r', time()) . "\n";
    $mail_headers .= "X-Priority: 3\n";
    $mail_headers .= "X-MSMail-Priority: Normal\n";
    $mail_headers .= "X-Mailer: PHP\n";
    $mail_headers .= "X-MimeOLE: Produced By CuteNews\n";
    mail($to, $subject, $message, $mail_headers);
}

////////////////////////////////////////////////////////
// Function:         makeRandomPassword Version: 1.0
// Description: Make a random password
function makeRandomPassword()
{
    $salt = "abchefghjkmnpqrstuvwxyz0123456789";
    $pass = "";
    srand((double)microtime()*1000000);
    $i = 0;
    while ($i <= 7) {
        $num = rand() % 33;
        $tmp = substr($salt, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
}

////////////////////////////////////////////////////////
// Function:         special_convert
// Description: Convert from Characters, Entities, or ASCII
function ascii_convert($in, $method="toascii")
{
    $out = $in;
    $char[] = '"';
    $entity[] = "&quot;";
    $ascii[] = "&#034;";
    $char[] = "'";
    $entity[] = "&apos;";
    $ascii[] = "&#039;";
    $char[] = "<";
    $entity[] = "&lt;";
    $ascii[] = "&#060;";
    $char[] = ">";
    $entity[] = "&gt;";
    $ascii[] = "&#062;";
    $char[] = " ";
    $entity[] = "&nbsp;";
    $ascii[] = "&#160;";
    $char[] = "|";
    $entity[] = "&#124;";
    $ascii[] = "&#124;";
    $char[] = "�";
    $entity[] = "&iexcl;";
    $ascii[] = "&#161;";
    $char[] = "�";
    $entity[] = "&curren;";
    $ascii[] = "&#164;";
    $char[] = "�";
    $entity[] = "&cent;";
    $ascii[] = "&#162;";
    $char[] = "�";
    $entity[] = "&pound;";
    $ascii[] = "&#163;";
    $char[] = "�";
    $entity[] = "&yen;";
    $ascii[] = "&#165;";
    $char[] = "�";
    $entity[] = "&brvbar;";
    $ascii[] = "&#166;";
    $char[] = "�";
    $entity[] = "&sect;";
    $ascii[] = "&#167;";
    $char[] = "�";
    $entity[] = "&uml;";
    $ascii[] = "&#168;";
    $char[] = "�";
    $entity[] = "&copy;";
    $ascii[] = "&#169;";
    $char[] = "�";
    $entity[] = "&ordf;";
    $ascii[] = "&#170;";
    $char[] = "�";
    $entity[] = "&laquo;";
    $ascii[] = "&#171;";
    $char[] = "�";
    $entity[] = "&not;";
    $ascii[] = "&#172;";
    $char[] = "�";
    $entity[] = "&shy;";
    $ascii[] = "&#173;";
    $char[] = "�";
    $entity[] = "&reg;";
    $ascii[] = "&#174;";
    $char[] = "�";
    $entity[] = "&trade;";
    $ascii[] = "&#8482;";
    $char[] = "�";
    $entity[] = "&macr;";
    $ascii[] = "&#175;";
    $char[] = "�";
    $entity[] = "&deg;";
    $ascii[] = "&#176;";
    $char[] = "�";
    $entity[] = "&plusmn;";
    $ascii[] = "&#177;";
    $char[] = "�";
    $entity[] = "&sup2;";
    $ascii[] = "&#178;";
    $char[] = "�";
    $entity[] = "&sup3;";
    $ascii[] = "&#179;";
    $char[] = "�";
    $entity[] = "&acute;";
    $ascii[] = "&#180;";
    $char[] = "�";
    $entity[] = "&micro;";
    $ascii[] = "&#181;";
    $char[] = "�";
    $entity[] = "&para;";
    $ascii[] = "&#182;";
    $char[] = "�";
    $entity[] = "&middot;";
    $ascii[] = "&#183;";
    $char[] = "�";
    $entity[] = "&cedil;";
    $ascii[] = "&#184;";
    $char[] = "�";
    $entity[] = "&sup1;";
    $ascii[] = "&#185;";
    $char[] = "�";
    $entity[] = "&ordm;";
    $ascii[] = "&#186;";
    $char[] = "�";
    $entity[] = "&raquo;";
    $ascii[] = "&#187;";
    $char[] = "�";
    $entity[] = "&frac14;";
    $ascii[] = "&#188;";
    $char[] = "�";
    $entity[] = "&frac12;";
    $ascii[] = "&#189;";
    $char[] = "�";
    $entity[] = "&frac34;";
    $ascii[] = "&#190;";
    $char[] = "�";
    $entity[] = "&iquest;";
    $ascii[] = "&#191;";
    $char[] = "�";
    $entity[] = "&times;";
    $ascii[] = "&#215;";
    $char[] = "�";
    $entity[] = "&divide;";
    $ascii[] = "&#247;";
    $char[] = "�";
    $entity[] = "&Agrave;";
    $ascii[] = "&#192;";
    $char[] = "�";
    $entity[] = "&Aacute;";
    $ascii[] = "&#193;";
    $char[] = "�";
    $entity[] = "&Acirc;";
    $ascii[] = "&#194;";
    $char[] = "�";
    $entity[] = "&Atilde;";
    $ascii[] = "&#195;";
    $char[] = "�";
    $entity[] = "&Auml;";
    $ascii[] = "&#196;";
    $char[] = "�";
    $entity[] = "&Aring;";
    $ascii[] = "&#197;";
    $char[] = "�";
    $entity[] = "&AElig;";
    $ascii[] = "&#198;";
    $char[] = "�";
    $entity[] = "&Ccedil;";
    $ascii[] = "&#199;";
    $char[] = "�";
    $entity[] = "&Egrave;";
    $ascii[] = "&#200;";
    $char[] = "�";
    $entity[] = "&Eacute;";
    $ascii[] = "&#201;";
    $char[] = "�";
    $entity[] = "&Ecirc;";
    $ascii[] = "&#202;";
    $char[] = "�";
    $entity[] = "&Euml;";
    $ascii[] = "&#203;";
    $char[] = "�";
    $entity[] = "&Igrave;";
    $ascii[] = "&#204;";
    $char[] = "�";
    $entity[] = "&Iacute;";
    $ascii[] = "&#205;";
    $char[] = "�";
    $entity[] = "&Icirc;";
    $ascii[] = "&#206;";
    $char[] = "�";
    $entity[] = "&Iuml;";
    $ascii[] = "&#207;";
    $char[] = "�";
    $entity[] = "&ETH;";
    $ascii[] = "&#208;";
    $char[] = "�";
    $entity[] = "&Ntilde;";
    $ascii[] = "&#209;";
    $char[] = "�";
    $entity[] = "&Ograve;";
    $ascii[] = "&#210;";
    $char[] = "�";
    $entity[] = "&Oacute;";
    $ascii[] = "&#211;";
    $char[] = "�";
    $entity[] = "&Ocirc;";
    $ascii[] = "&#212;";
    $char[] = "�";
    $entity[] = "&Otilde;";
    $ascii[] = "&#213;";
    $char[] = "�";
    $entity[] = "&Ouml;";
    $ascii[] = "&#214;";
    $char[] = "�";
    $entity[] = "&Oslash;";
    $ascii[] = "&#216;";
    $char[] = "�";
    $entity[] = "&Ugrave;";
    $ascii[] = "&#217;";
    $char[] = "�";
    $entity[] = "&Uacute;";
    $ascii[] = "&#218;";
    $char[] = "�";
    $entity[] = "&Ucirc;";
    $ascii[] = "&#219;";
    $char[] = "�";
    $entity[] = "&Uuml;";
    $ascii[] = "&#220;";
    $char[] = "�";
    $entity[] = "&Yacute;";
    $ascii[] = "&#221;";
    $char[] = "�";
    $entity[] = "&THORN;";
    $ascii[] = "&#222;";
    $char[] = "�";
    $entity[] = "&szlig;";
    $ascii[] = "&#223;";
    $char[] = "�";
    $entity[] = "&agrave;";
    $ascii[] = "&#224;";
    $char[] = "�";
    $entity[] = "&aacute;";
    $ascii[] = "&#225;";
    $char[] = "�";
    $entity[] = "&acirc;";
    $ascii[] = "&#226;";
    $char[] = "�";
    $entity[] = "&atilde;";
    $ascii[] = "&#227;";
    $char[] = "�";
    $entity[] = "&auml;";
    $ascii[] = "&#228;";
    $char[] = "�";
    $entity[] = "&aring;";
    $ascii[] = "&#229;";
    $char[] = "�";
    $entity[] = "&aelig;";
    $ascii[] = "&#230;";
    $char[] = "�";
    $entity[] = "&ccedil;";
    $ascii[] = "&#231;";
    $char[] = "�";
    $entity[] = "&egrave;";
    $ascii[] = "&#232;";
    $char[] = "�";
    $entity[] = "&eacute;";
    $ascii[] = "&#233;";
    $char[] = "�";
    $entity[] = "&ecirc;";
    $ascii[] = "&#234;";
    $char[] = "�";
    $entity[] = "&euml;";
    $ascii[] = "&#235;";
    $char[] = "�";
    $entity[] = "&igrave;";
    $ascii[] = "&#236;";
    $char[] = "�";
    $entity[] = "&iacute;";
    $ascii[] = "&#237;";
    $char[] = "�";
    $entity[] = "&icirc;";
    $ascii[] = "&#238;";
    $char[] = "�";
    $entity[] = "&iuml;";
    $ascii[] = "&#239;";
    $char[] = "�";
    $entity[] = "&eth;";
    $ascii[] = "&#240;";
    $char[] = "�";
    $entity[] = "&ntilde;";
    $ascii[] = "&#241;";
    $char[] = "�";
    $entity[] = "&ograve;";
    $ascii[] = "&#242;";
    $char[] = "�";
    $entity[] = "&oacute;";
    $ascii[] = "&#243;";
    $char[] = "�";
    $entity[] = "&ocirc;";
    $ascii[] = "&#244;";
    $char[] = "�";
    $entity[] = "&otilde;";
    $ascii[] = "&#245;";
    $char[] = "�";
    $entity[] = "&ouml;";
    $ascii[] = "&#246;";
    $char[] = "�";
    $entity[] = "&oslash;";
    $ascii[] = "&#248;";
    $char[] = "�";
    $entity[] = "&ugrave;";
    $ascii[] = "&#249;";
    $char[] = "�";
    $entity[] = "&uacute;";
    $ascii[] = "&#250;";
    $char[] = "�";
    $entity[] = "&ucirc;";
    $ascii[] = "&#251;";
    $char[] = "�";
    $entity[] = "&uuml;";
    $ascii[] = "&#252;";
    $char[] = "�";
    $entity[] = "&yacute;";
    $ascii[] = "&#253;";
    $char[] = "�";
    $entity[] = "&thorn;";
    $ascii[] = "&#254;";
    $char[] = "�";
    $entity[] = "&yuml;";
    $ascii[] = "&#255;";
    $char[] = "�";
    $entity[] = "&OElig;";
    $ascii[] = "&#338;";
    $char[] = "�";
    $entity[] = "&oelig;";
    $ascii[] = "&#339;";
    $char[] = "�";
    $entity[] = "&Scaron;";
    $ascii[] = "&#352;";
    $char[] = "�";
    $entity[] = "&scaron;";
    $ascii[] = "&#353;";
    $char[] = "�";
    $entity[] = "&Yuml;";
    $ascii[] = "&#376;";
    $char[] = "�";
    $entity[] = "&circ;";
    $ascii[] = "&#710;";
    $char[] = "�";
    $entity[] = "&tilde;";
    $ascii[] = "&#732;";
    $char[] = "�";
    $entity[] = "&ndash;";
    $ascii[] = "&#8211;";
    $char[] = "�";
    $entity[] = "&mdash;";
    $ascii[] = "&#8212;";
    $char[] = "�";
    $entity[] = "&lsquo;";
    $ascii[] = "&#8216;";
    $char[] = "�";
    $entity[] = "&rsquo;";
    $ascii[] = "&#8217;";
    $char[] = "�";
    $entity[] = "&sbquo;";
    $ascii[] = "&#8218;";
    $char[] = "�";
    $entity[] = "&ldquo;";
    $ascii[] = "&#8220;";
    $char[] = "�";
    $entity[] = "&rdquo;";
    $ascii[] = "&#8221;";
    $char[] = "�";
    $entity[] = "&bdquo;";
    $ascii[] = "&#8222;";
    $char[] = "�";
    $entity[] = "&dagger;";
    $ascii[] = "&#8224;";
    $char[] = "�";
    $entity[] = "&Dagger;";
    $ascii[] = "&#8225;";
    $char[] = "�";
    $entity[] = "&hellip;";
    $ascii[] = "&#8230;";
    $char[] = "�";
    $entity[] = "&permil;";
    $ascii[] = "&#8240;";
    $char[] = "�";
    $entity[] = "&lsaquo;";
    $ascii[] = "&#8249;";
    $char[] = "�";
    $entity[] = "&rsaquo;";
    $ascii[] = "&#8250;";
    $char[] = "�";
    $entity[] = "&euro;";
    $ascii[] = "&#8364;";
    $char[] = "&";
    $entity[] = "&amp;";
    $ascii[] = "&#038;";
    foreach ($char as $r => $char) {
        if ($entity[$r] != "&amp;") {
            $out = str_replace($entity[$r], $ascii[$r], $out);
        }
        if ($char[$r] != '"' && $char[$r] != "'" && $char[$r] != "<" && $char[$r] != ">" && $char[$r] != " ") {
            $out = str_replace($char[$r], $ascii[$r], $out);
        }
    }
    $out = str_replace("&amp;#", "&#", $out);
    $out = str_replace("&#038;#", "&#", $out);
    $out = str_replace("&#038;amp;", "&amp;", $out);
    $out = str_replace("&amp;amp;", "&amp;", $out);
    if ($method == "mail") {
        foreach ($char as $r => $char) {
            $out = str_replace($entity[$r], $char[$r], $out);
            $out = str_replace($ascii[$r], $char[$r], $out);
        }
    }
    return $out;
}

////////////////////////////////////////////////////////
// Function:         profiledata
// Description: Send profile data to string
function profiledata($user, $output)
{
    global $cutepath;
    $lines = file($cutepath.'/data/profiles.db.php');
    foreach ($lines as $num => $line) {
        if (eregi("<\?", $line)) {
            continue;
        }
        $tmp = explode("|", $line);
        $$tmp[0] = $line;
    }

    global $pro;
    $pro = explode("|", $$user);

    if (isset($pro[1]) && $pro[1]) {
        $pflname = $pro[1];
    }
    if (isset($pro[2]) && $pro[2]) {
        $pflnick = $pro[2];
    }
    if (isset($pro[3]) && $pro[3]) {
        $pflavatar = $pro[2];
    }
    if (isset($pro[4]) && $pro[4]) {
        $pflemail = $pro[4];
    }
    if (isset($pro[5]) && $pro[5]) {
        $pflbirth = $pro[5];
    }
    if (isset($pro[6]) && $pro[6]) {
        $pfllocation = $pro[6];
    }
    if (isset($pro[7]) && $pro[7]) {
        $pflicq = $pro[7];
    }
    if (isset($pro[8]) && $pro[8]) {
        $pflaim = $pro[8];
    }
    if (isset($pro[9]) && $pro[9]) {
        $pflyim = $pro[9];
    }
    if (isset($pro[10]) && $pro[10]) {
        $pflmsn = $pro[10];
    }
    $probio = $pro[11];
    $probio = str_replace("/n", "<br />", $probio);
    $probio = str_replace("\\", "", $probio);
    if (isset($probio) && $probio) {
        $pflbio = $probio;
    }

    $output = str_replace("{pfl-name}", $pflname, $output);
    $output = str_replace("{pfl-nick}", $pflnick, $output);
    $output = preg_replace('/\\{pfl-avatar\\}/is', ($pflavatar) ? '<img alt="" src="'.$pflavatar.'" style="border: none;" />' : '', $output);
    $output = str_replace("{pfl-avatar-url}", $pflavatar, $output);
    $output = str_replace("{pfl-email}", $pflemail, $output);
    $output = preg_replace('/\\{pfl-age\\}/is', ($pflbirth) ? (date("Y")-date("Y", $pflbirth)) : '', $output);

    if (!function_exists(tagdate)) {
        function tagdate($match)
        {
            global $pro;
            return date($match[1], $pro[5]);
        }
    }
    $output = preg_replace_callback('#\[pfl-birth\](.*?)\[/pfl-birth\]#i', tagdate, $output);
    $output = str_replace("{pfl-location}", $pfllocation, $output);
    $output = str_replace("{pfl-icq}", $pflicq, $output);
    $output = str_replace("{pfl-aim}", $pflaim, $output);
    $output = str_replace("{pfl-yim}", $pflyim, $output);
    $output = str_replace("{pfl-msn}", $pflmsn, $output);
    $output = str_replace("{pfl-bio}", $pflbio, $output);
// contact icons
    $output = str_replace("{icon-email}", ($pflemail) ? "<img style=\"border: none;\" alt=\"\" src=\"http://tdknights.com/checker/email.gif\" />" : "", $output);
    $output = str_replace("{icon-icq}", ($pflicq) ? "<img style=\"border: none;\" alt=\"\" src=\"http://web.icq.com/whitepages/online?icq=".$pflicq."&img=5\" />" : "", $output);
    $output = str_replace("{icon-aim}", ($pflaim) ? "<img style=\"border: none;\" alt=\"\" src=\"http://big.oscar.aol.com/".str_replace(" ", "", $pflaim)."?on_url=http://tdknights.com/checker/aimonline.gif&off_url=http://tdknights.com/checker/aimoffline.gif\" />" : "", $output);
    $output = str_replace("{icon-yim}", ($pflyim) ? "<img style=\"border: none;\" alt=\"\" src=\"http://opi.yahoo.com/online?u=".$pflyim."\" />" : "", $output);
    $output = str_replace("{icon-msn}", ($pflmsn) ? "<img style=\"border: none;\" alt=\"\" src=\"http://checker.tdknights.com:1337/msn/".$pflmsn."\" />" : "", $output);
// contact links
    $output = preg_replace('/\\[link-email\\](.*?)\\[\\/link-email\\]/is', ($pflemail) ? '<a href="mailto:'.$pflemail.'">\\1</a>' : '', $output);
    $output = preg_replace('/\\[link-icq\\](.*?)\\[\\/link-icq\\]/is', ($pflicq) ? '<a href="http://www.icq.com/whitepages/cmd.php?uin='.$pflicq.'&action=message">\\1</a>' : '', $output);
    $output = preg_replace('/\\[link-aim\\](.*?)\\[\\/link-aim\\]/is', ($pflaim) ? '<a href="aim:goim?screenname='.str_replace(" ", "", $pflaim).'">\\1</a>' : '', $output);
    $output = preg_replace('/\\[link-yim\\](.*?)\\[\\/link-yim\\]/is', ($pflyim) ? '<a href="ymsgr:sendIM?'.$pflyim.'">\\1</a>' : '', $output);
    $output = preg_replace('/\\[link-msn\\](.*?)\\[\\/link-msn\\]/is', ($pflmsn) ? '<a href="mailto:'.$pflmsn.'">\\1</a>' : '', $output);

    return $output;
}
