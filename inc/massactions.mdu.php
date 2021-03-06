<?php

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Mass Delete
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if ($action == "mass_delete") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }

    echoheader("options", "Delete News");
    echo "<form method=post action=\"$PHP_SELF\"><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td >
Are you sure you want to delete all selected news (<b>".count($selected_news)."</b>)?<br /><br />
<input type=button value=\" No \" onclick=\"javascript:document.location='$PHP_SELF?mod=editnews&action=list&source=$source'\"> &nbsp; <input type=submit value=\"   Yes   \">
<input type=hidden name=action value=\"do_mass_delete\">
<input type=hidden name=mod value=\"massactions\">
<input type=hidden name=source value=\"$source\">";
    foreach ($selected_news as $null => $newsid) {
        echo "<input type=hidden name=selected_news[] value=\"$newsid\">\n";
    }
    echo "</td></tr></table></form>";

    echofooter();
    exit;
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Do Mass Delete
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "do_mass_delete") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles to be deleted", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
    if ($source == "") {
        $news_file = "data/news.db.php";
        $comm_file = "data/comments.db.php";
    } else {
        $news_file = "./data/archives/$source.news.arch.php";
        $comm_file = "./data/archives/$source.comments.arch.php";
    }

    $deleted_articles = 0;

    // Delete News
    $old_db = file("$news_file");
    $new_db = fopen("$news_file", w);
    fwrite($new_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_db as $null => $old_db_line) {
        if (eregi("<\?", $old_db_line)) {
            continue;
        }
        $old_db_arr = explode("|", $old_db_line);
        if (@!in_array($old_db_arr[0], $selected_news)) {
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
                $deleted_articles ++;
            }
            // XFields v2.1 - addblock
            $xfieldsaction = "delete";
            $xfieldsid = $old_db_arr[0];
            include("xfields.mdu.php");
            // XFields v2.1 - End addblock
        }
    }
    fclose($new_db);

    // Delete Comments
    $old_db = file("$comm_file");
    $new_db = fopen("$comm_file", w);
    fwrite($new_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_db as $null => $old_db_line) {
        if (eregi("<\?", $old_db_line)) {
            continue;
        }
        $old_db_arr = explode("|", $old_db_line);
        if (@!in_array($old_db_arr[0], $selected_news)) {
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
            } else { /* Do Nothing => Delete :) */
            }
        }
    }
    fclose($new_db);


    if (count($selected_news) == $deleted_articles) {
        msg("info", "Deleted News", "All articles that you selected (<b>$deleted_articles</b>) were deleted", "$PHP_SELF?mod=editnews&action=list&source=$source");
    } else {
        msg("error", "Deleted News (some errors occured !!!)", "$deleted_articles of ".count($selected_news)." articles that you selected were deleted", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Mass Move to Cat
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "mass_move_to_cat") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
    $cat_lines = file("./data/category.db.php");

    echoheader("options", "Move Articles to Category");

    echo "<form action=\"$PHP_SELF\" method=post><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td >Move selected articles (<b>".count($selected_news)."</b>) to category:
<select name=move_to_category><option value=\"\"> </option>";
    foreach ($cat_lines as $null => $single_line) {
        if (eregi("<\?", $single_line)) {
            continue;
        }
        $cat_arr = explode("|", $single_line);
        $if_is_selected = "";
        echo "<option value=\"$cat_arr[0]\">$cat_arr[1]</option>";
    }
    echo "</select>";

    foreach ($selected_news as $null => $newsid) {
        echo "<input type=hidden name=selected_news[] value=\"$newsid\">";
    }

    echo "&nbsp;<input type=hidden name=action value=\"do_mass_move_to_cat\"><input type=hidden name=source value=\"$source\"><input type=hidden name=mod value=\"massactions\">&nbsp;<input type=submit value=\"Move\"></td></tr></table></form>";

    echofooter();
    exit;
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  DO Mass Move to One Category
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "do_mass_move_to_cat") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
    if ($source == "") {
        $news_file = "./data/news.db.php";
    } else {
        $news_file = "./data/archives/$source.news.arch.php";
    }
    $moved_articles = 0;
    $old_db = file("$news_file");
    $new_db = fopen("$news_file", w);
    fwrite($new_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_db as $null => $old_db_line) {
        if (eregi("<\?", $old_db_line)) {
            continue;
        }
        $old_db_arr = explode("|", $old_db_line);
        if (@!in_array($old_db_arr[0], $selected_news)) {
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
                if ($old_db_arr[7] != 1) {
                    $old_db_arr[7] = 0;
                }
                if ($old_db_arr[8] != 1) {
                    $old_db_arr[8] = 0;
                }
                fwrite($new_db, "$old_db_arr[0]|$old_db_arr[1]|$old_db_arr[2]|$old_db_arr[3]|$old_db_arr[4]|$old_db_arr[5]|$move_to_category|$old_db_arr[7]|$old_db_arr[8]||\n");
                $moved_articles ++;
            }
        }
    }
    fclose($new_db);
    if (count($selected_news) == $moved_articles) {
        msg("info", "News Moved", "All articles that you selected ($moved_articles) were moved to the specified category", "$PHP_SELF?mod=editnews&action=list&source=$source");
    } else {
        msg("error", "News Moved (with errors)", "$moved_articles of ".count($selected_news)." articles that you selected were moved to the specified category", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Mass Archive
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "mass_archive") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
    if ($source != "") {
        msg("error", "Error", "These news are already in the archive", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }

    echoheader("options", "Send News To Archive");

    echo "<form method=post action=\"$PHP_SELF\"><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td >
Are you sure you want to send all selected news (<b>".count($selected_news)."</b>) to the archive?<br /><br />
<input type=button value=\" No \" onclick=\"javascript:document.location='$PHP_SELF?mod=editnews&action=list&source=$source'\"> &nbsp; <input type=submit value=\"   Yes   \">
<input type=hidden name=action value=\"do_mass_archive\">
<input type=hidden name=mod value=\"massactions\">";
    foreach ($selected_news as $null => $newsid) {
        echo"<input type=hidden name=selected_news[] value=\"$newsid\">\n";
    }
    echo"</td></tr></table></form>";

    echofooter();
    exit;
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  DO Mass Send To Archive
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "do_mass_archive") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
    if (!is_writable("./data/archives/")) {
        msg("error", "Error", "The ./data/archives/ directory is not writable, CHMOD it to 777");
    }
    $news_file = "./data/news.db.php";
    $comm_file = "./data/comments.db.php";

    $prepeared_for_archive = array();
    $prepeared_comments_for_archive = array();
    $archived_news = 0;

    // Prepear the news for Archiving

    $old_db = file("$news_file");
    $new_db = fopen("$news_file", w);
    fwrite($new_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_db as $null => $old_db_line) {
        if (eregi("<\?", $old_db_line)) {
            continue;
        }
        $old_db_arr = explode("|", $old_db_line);
        if (@!in_array($old_db_arr[0], $selected_news)) {
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
                $prepeared_news_for_archive[] = $old_db_line;
                $archived_news++;
            }
        }
    }
    fclose($new_db);

    if ($archived_news == 0) {
        msg("error", "Error", "No news were found for archiving.");
    }

    // Prepear the comments for Archiving

    $old_db = file("$comm_file");
    $new_db = fopen("$comm_file", w);
    fwrite($new_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_db as $null => $old_db_line) {
        if (eregi("<\?", $old_db_line)) {
            continue;
        }
        $old_db_arr = explode("|", $old_db_line);
        if (@!in_array($old_db_arr[0], $selected_news)) {
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
                $prepeared_comments_for_archive[] = $old_db_line;
            }
        }
    }
    fclose($new_db);

    // Start Archiving

    $arch_name = time()+($config_date_adjust*60);

    $arch_news = fopen("./data/archives/$arch_name.news.arch.php", w);
    @chmod("./data/archives/$arch_name.news.arch.php", 0777);
    fwrite($arch_news, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($prepeared_news_for_archive as $null => $item) {
        if (eregi("<\?", $item)) {
            continue;
        }
        fwrite($arch_news, "$item");
    }
    fclose($arch_news);

    $arch_comm = fopen("./data/archives/$arch_name.comments.arch.php", w);
    @chmod("./data/archives/$arch_name.comments.arch.php", 0777);
    fwrite($arch_comm, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($prepeared_comments_for_archive as $null => $item) {
        if (eregi("<\?", $item)) {
            continue;
        }
        fwrite($arch_comm, "$item");
    }
    fclose($arch_comm);

    msg("info", "News Archived", "All articles that you selected ($archived_news) are now archived under ./data/archives/<b>$arch_name</b>.news.arch.php", "$PHP_SELF?mod=editnews&action=list&source=$source");
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Mass MailOnComment v1.4
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "mass_mail_comments") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }

    echoheader("options", "Enable/Disable Mail On Comments");

    echo "<form action=\"$PHP_SELF\" method=post><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td><select name=mail_comments><option value=\"1\">Enable</option><option value=\"0\">Disable</option></select> mail on comments for these <b>".count($selected_news)."</b> posts?";

    foreach ($selected_news as $null => $newsid) {
        echo "<input type=hidden name=selected_news[] value=\"$newsid\">";
    }

    echo "&nbsp;<input type=hidden name=action value=\"do_mass_mail_comments\"><input type=hidden name=source value=\"$source\"><input type=hidden name=mod value=\"massactions\">&nbsp;<input type=submit value=\"Change\"></td></tr></table></form>";

    echofooter();
    exit;
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  DO Mass MailOnComment v1.4
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "do_mass_mail_comments") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
    if ($source == "") {
        $news_file = "./data/news.db.php";
    } else {
        $news_file = "./data/archives/$source.news.arch.php";
    }
    $changed_articles = 0;
    $old_db = file("$news_file");
    $new_db = fopen("$news_file", w);
    fwrite($new_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_db as $null => $old_db_line) {
        if (eregi("<\?", $old_db_line)) {
            continue;
        }
        $old_db_arr = explode("|", $old_db_line);
        if (@!in_array($old_db_arr[0], $selected_news)) {
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
                if ($old_db_arr[7] != 1) {
                    $old_db_arr[7] = 0;
                }
                fwrite($new_db, "$old_db_arr[0]|$old_db_arr[1]|$old_db_arr[2]|$old_db_arr[3]|$old_db_arr[4]|$old_db_arr[5]|$old_db_arr[6]|$old_db_arr[7]|$mail_comments||\n");
                $changed_articles ++;
            }
        }
    }
    fclose($new_db);
    if (count($selected_news) == $changed_articles) {
        msg("info", "News Changed", "All articles that you selected ($changed_articles) were changed successfully", "$PHP_SELF?mod=editnews&action=list&source=$source");
    } else {
        msg("error", "News Changed (with errors)", "$changed_articles of ".count($selected_news)." articles that you selected were changed successfully", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Mass Enable/Disable Comments
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "mass_ed_comments") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }

    echoheader("options", "Enable/Disable Commenting");

    echo "<form action=\"$PHP_SELF\" method=post><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td><select name=ed_comments><option value=\"1\">Enable</option><option value=\"0\">Disable</option></select> comment posting for these <b>".count($selected_news)."</b> posts?";

    foreach ($selected_news as $null => $newsid) {
        echo "<input type=hidden name=selected_news[] value=\"$newsid\">";
    }

    echo "&nbsp;<input type=hidden name=action value=\"do_mass_ed_comments\"><input type=hidden name=source value=\"$source\"><input type=hidden name=mod value=\"massactions\">&nbsp;<input type=submit value=\"Change\"></td></tr></table></form>";

    echofooter();
    exit;
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  DO Mass Enable/Disable Comments
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "do_mass_ed_comments") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
    if ($source == "") {
        $news_file = "./data/news.db.php";
    } else {
        $news_file = "./data/archives/$source.news.arch.php";
    }
    $changed_articles = 0;
    $old_db = file("$news_file");
    $new_db = fopen("$news_file", w);
    fwrite($new_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($old_db as $null => $old_db_line) {
        if (eregi("<\?", $old_db_line)) {
            continue;
        }
        $old_db_arr = explode("|", $old_db_line);
        if (@!in_array($old_db_arr[0], $selected_news)) {
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
                if (!$old_db_arr[6]) {
                    $old_db_arr[6] = 1;
                }
                if ($old_db_arr[7] != 1) {
                    $old_db_arr[7] = 0;
                }
                if ($old_db_arr[8] != 1) {
                    $old_db_arr[8] = 0;
                }
                fwrite($new_db, "$old_db_arr[0]|$old_db_arr[1]|$old_db_arr[2]|$old_db_arr[3]|$old_db_arr[4]|$old_db_arr[5]|$old_db_arr[6]|$ed_comments|$old_db_arr[8]||\n");
                $changed_articles ++;
            }
        }
    }
    fclose($new_db);
    if (count($selected_news) == $changed_articles) {
        msg("info", "News Changed", "All articles that you selected ($changed_articles) were changed successfully", "$PHP_SELF?mod=editnews&action=list&source=$source");
    } else {
        msg("error", "News Changed (with errors)", "$changed_articles of ".count($selected_news)." articles that you selected were changed successfully", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Mass Repair Broken Databases
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "mass_repair") {
    echoheader("options", "Repair Databases");

    echo "<form action=\"$PHP_SELF\" method=post><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td>Repair the databases?<br />
This will rewrite news and comment databases for the source you selected which may correct errors by writing proper values into proper locations.<br />
<span class=error>WARNING: This may also damage current data.<br />Use at your own risk.</span><br />
&nbsp;<input type=hidden name=action value=\"do_mass_repair\"><input type=hidden name=source value=\"$source\"><input type=hidden name=mod value=\"massactions\">&nbsp;<input type=submit value=\"Repair\"></td></tr></table></form>";

    echofooter();
    exit;
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  DO Mass Repair Broken Databases
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "do_mass_repair") {
    if ($member_db[1] != 1) {
        msg("error", "Access Denied", "You can not perfor this action if you are not an admin");
    }
    if ($source == "") {
        $news_file = "./data/news.db.php";
        $comm_file = "./data/comments.db.php";
    } else {
        $news_file = "./data/archives/$source.news.arch.php";
        $comm_file = "./data/archives/$source.comments.arch.php";
    }
    ///
    // build news
    $news_db = file("$news_file");
    $news_item = array();
    foreach ($news_db as $null => $news_db_line) {
        if (eregi("<\?", $news_db_line)) {
            continue;
        }
        $item_arr = explode("|", $news_db_line);
        if ($item_arr[0] == "") {
            $item_arr[0] = time();
        }
        if ($item_arr[1] == "") {
            $item_arr[1] = "* blank";
        }
        if ($item_arr[2] == "") {
            $item_arr[2] = "no title";
        }
        if ($item_arr[6] == "") {
            $item_arr[6] = 1;
        }
        if ($item_arr[7] != 1) {
            $item_arr[7] = 0;
        }
        if ($item_arr[8] != 1) {
            $item_arr[8] = 0;
        }
        $news_item[$item_arr[0]]['news-exists'] = true;
        $news_item[$item_arr[0]]['news-id'] = $item_arr[0];
        $news_item[$item_arr[0]]['news-value'] = $item_arr[0]."|".$item_arr[1]."|".$item_arr[2]."|".$item_arr[3]."|".$item_arr[4]."|".$item_arr[5]."|".$item_arr[6]."|".$item_arr[7]."|".$item_arr[8]."||";
    }
    // build comments
    $comm_db = file("$comm_file");
    foreach ($comm_db as $null => $comm_db_line) {
        if (eregi("<\?", $comm_db_line)) {
            continue;
        }
        $item_arr = explode("|>|", $comm_db_line);
        if ($item_arr[0] == "") {
            $item_arr[0] = time();
        }
        $news_item[$item_arr[0]]['comment-exists'] = true;
        $news_item[$item_arr[0]]['comment-value'] = $item_arr[0]."|>|".$item_arr[1];
    }
    // build views
    $view_file = "$cutepath/data/counter.db.php";
    $view_db = file("$view_file");
    foreach ($view_db as $null => $view_db_line) {
        if (eregi("<\?", $view_db_line)) {
            continue;
        }
        $item_arr = explode("|", $view_db_line);
        if ($item_arr[0] == "") {
            $item_arr[0] = time();
        }
        if ($item_arr[1] == "") {
            $item_arr[1] = 0;
        }
        $news_item[$item_arr[0]]['views-exists'] = true;
        $news_item[$item_arr[0]]['views-value'] = $item_arr[0]."|".$item_arr[1]."|";
    }

    // run validation
    rsort($news_item);
    reset($news_item);
    $fn = fopen($news_file, "wb");
    $fc = fopen($comm_file, "wb");
    $fv = fopen($view_file, "wb");
    fwrite($fn, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    fwrite($fc, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    fwrite($fv, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    foreach ($news_item as $null => $item) {
        if ($item['news-exists'] == true) {
            fwrite($fn, $item['news-value']."\n");
            if ($item['comment-exists'] == true) {
                $news_comm = $item['comment-value']."\n";
            } else {
                $news_comm = $item['news-id']."|>|\n";
            }
            fwrite($fc, $news_comm);
            if ($item['view-exists'] == true) {
                $news_view = $item['view-value']."\n";
            } else {
                $news_view = $item['news-id']."|0|\n";
            }
            if (!$source || $source == "") {
                fwrite($fv, $news_view);
            }
        }
    }
    fclose($fn);
    fclose($fc);
    fclose($fv);
    $news_item = null;
    ///
    msg("info", "Databases Repaired", "Databases Repaired.<br />This may have caused errors.", "$PHP_SELF?mod=editnews&action=list&source=$source");
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Mass UN-Archive
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "mass_unarchive") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
    if ($source == "") {
        msg("error", "Error", "That news is not in an archive", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }

    echoheader("options", "Bring News Back From Archive");

    echo "<form method=post action=\"$PHP_SELF\"><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td >
Are you sure you want to bring all selected news (<b>".count($selected_news)."</b>) back from the archive?<br /><br />
<input type=button value=\" No \" onclick=\"javascript:document.location='$PHP_SELF?mod=editnews&action=list&source=$source'\"> &nbsp; <input type=submit value=\"   Yes   \">
<input type=hidden name=source value=\"$source\">
<input type=hidden name=action value=\"do_mass_unarchive\">
<input type=hidden name=mod value=\"massactions\">";
    foreach ($selected_news as $null => $newsid) {
        echo"<input type=hidden name=selected_news[] value=\"$newsid\">\n";
    }
    echo"</td></tr></table></form>";

    echofooter();
    exit;
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  DO Mass UN-Archive
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif ($action == "do_mass_unarchive") {
    if (!$selected_news) {
        msg("error", "Error", "You have not specified any articles", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
    if ($source == "") {
        msg("error", "Error", "That news is not in an archive", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
    $moved_articles = 0;

    // build news
    $arch_file = "./data/archives/$source.news.arch.php";
    $news_file = "./data/news.db.php";
    $write_news = "";
    $write_arch = "";
    $old_arch = file("$arch_file");
    foreach ($old_arch as $null => $old_arch_line) {
        if (eregi("<\?", $old_arch_line)) {
            continue;
        }
        $old_arch_arr = explode("|", $old_arch_line);
        if (in_array($old_arch_arr[0], $selected_news)) {
            $write_news = str_replace("\n", "", $old_arch_line)."\n".$write_news;
            $write_arch = $write_arch;
            $moved_articles ++;
        } else {
            $write_news = $write_news;
            $write_arch = str_replace("\n", "", $old_arch_line)."\n".$write_arch;
        }
    }
    $old_news = file("$news_file");
    foreach ($old_news as $null => $old_news_line) {
        if (eregi("<\?", $old_arch_line)) {
            continue;
        }
        $write_news = str_replace("\n", "", $old_news_line)."\n".$write_news;
    }

    // write news
    $news_db = fopen("$news_file", "wb");
    fwrite($news_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    $news_sorted = explode("\n", $write_news);
    rsort($news_sorted);
    reset($news_sorted);
    foreach ($news_sorted as $null => $line) {
        if (eregi("<\?", $line)) {
            continue;
        }
        fwrite($news_db, "$line");
        if ($line != "") {
            fwrite($news_db, "\n");
        }
    }
    fclose($news_db);
    $arch_db = fopen("$arch_file", "wb");
    fwrite($arch_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    $arch_sorted = explode("\n", $write_arch);
    rsort($arch_sorted);
    reset($arch_sorted);
    foreach ($arch_sorted as $null => $line) {
        if (eregi("<\?", $line)) {
            continue;
        }
        fwrite($arch_db, "$line");
        if ($line != "") {
            fwrite($arch_db, "\n");
        }
    }
    fclose($arch_db);

    // remove empty
    if (filesize($arch_file) <= 70) {
        unlink($arch_file);
        $removed = true;
    }

    // build comments
    $arch_file = "./data/archives/$source.comments.arch.php";
    $news_file = "./data/comments.db.php";
    $write_news = "";
    $write_arch = "";
    $old_arch = file("$arch_file");
    foreach ($old_arch as $null => $old_arch_line) {
        if (eregi("<\?", $old_arch_line)) {
            continue;
        }
        $old_arch_arr = explode("|>|", $old_arch_line);
        if (in_array($old_arch_arr[0], $selected_news)) {
            $write_news = str_replace("\n", "", $old_arch_line)."\n".$write_news;
            $write_arch = $write_arch;
        } else {
            $write_news = $write_news;
            $write_arch = str_replace("\n", "", $old_arch_line)."\n".$write_arch;
        }
    }
    $old_news = file("$news_file");
    foreach ($old_news as $null => $old_news_line) {
        if (eregi("<\?", $old_news_line)) {
            continue;
        }
        $write_news = str_replace("\n", "", $old_news_line)."\n".$write_news;
    }

    // write comments
    $news_db = fopen("$news_file", "wb");
    fwrite($news_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    $news_sorted = explode("\n", $write_news);
    rsort($news_sorted);
    reset($news_sorted);
    foreach ($news_sorted as $null => $line) {
        if (eregi("<\?", $line)) {
            continue;
        }
        fwrite($news_db, "$line");
        if ($line != "") {
            fwrite($news_db, "\n");
        }
    }
    fclose($news_db);
    $arch_db = fopen("$arch_file", "wb");
    fwrite($arch_db, "<?PHP die(\"You don't have access to open this file !!!\"); ?>\n");
    $arch_sorted = explode("\n", $write_arch);
    rsort($arch_sorted);
    reset($arch_sorted);
    foreach ($arch_sorted as $null => $line) {
        if (eregi("<\?", $line)) {
            continue;
        }
        fwrite($arch_db, "$line");
        if ($line != "") {
            fwrite($arch_db, "\n");
        }
    }
    fclose($arch_db);

    // remove empty
    if ($removed) {
        unlink($arch_file);
        $removed = false;
    }

    if (count($selected_news) == $moved_articles) {
        msg("info", "News Moved", "All articles that you selected ($moved_articles) were moved successfully", "$PHP_SELF?mod=editnews&action=list&source=$source");
    } else {
        msg("error", "News Moved (with errors)", "$moved_articles of ".count($selected_news)." articles that you selected were moved successfully", "$PHP_SELF?mod=editnews&action=list&source=$source");
    }
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  If No Action Is Choosed
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
else {
    msg("info", "Choose Action", "Please choose action from the drop-down menu", "$PHP_SELF?mod=editnews&action=list&source=$source");
}
