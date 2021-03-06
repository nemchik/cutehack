<?php
//If member access level is commenter, redirect him to personal options
if ($member_db[1] == 4 and $action == "dologin") {
    header("Location: $config_http_script_dir/index.php?mod=options&action=personal");
    exit;
}
if ($member_db[1] == 5 and $action == "dologin") {
    header("Location: $config_http_script_dir/index.php?action=logout");
    exit;
}


echoheader("home", "Welcome");

// Some Stats
    $todaynews = 0;
    $count_comments = 0;
    $count_my_news = 0;
    $count_new_news = 0;
    $news_db = file("./data/news.db.php");
     foreach ($news_db as $null => $line) {
         if (eregi("<\?", $line)) {
             continue;
         }
         $item_db = explode("|", $line);
         $itemdate = date("d/m/y", $item_db[0]);
         if ($itemdate == date("d/m/y")) {
             $todaynews++;
             if ($item_db[1] == $member_db[2]) {
                 $count_my_news++;
             }
             if (($item_db[0] > $member_db[9]) and ($member_db[9] != '')) {
                 $count_new_news++;
             }
         }
     }
    $stats_news = count($news_db)-1;
    $stats_users = count(file("./data/users.db.php")) - 1;
    $stats_archives = 0;
    $handle = opendir("./data/archives");
        while (false !== ($file = readdir($handle))) {
            if (preg_match("/.news.arch.php/", $file)) {
                $stats_archives++;
            }
        }
        closedir($handle);
    $stats_news_size = formatsize(filesize("./data/news.db.php"));
    $stats_comments_size = formatsize(filesize("./data/comments.db.php"));
    $stats_users_size = formatsize(filesize("./data/users.db.php"));

        // Count Comments
        $all_comments = file("./data/comments.db.php");
        foreach ($all_comments as $null => $news_comments) {
            if (eregi("<\?", $news_comments)) {
                continue;
            }
            $single_news_comments = explode("|>|", $news_comments);
            $individual_comments = explode("||", $single_news_comments[1]);
            $count_comments += count($individual_comments) - 1;
        }
// Define Welcome Message
    echo"<table border=0 cellpadding=0 cellspacing=0 width=654>
        <tr><td width=650 colspan=5 height=1>
        &nbsp;

    <SCRIPT LANGUAGE=\"JavaScript\">
        <!-- Begin
        datetoday = new Date();
        timenow=datetoday.getTime();
        datetoday.setTime(timenow);
        thehour = datetoday.getHours();
        if                 (thehour < 9 )         display = \"Morning\";
        else if (thehour < 12)         display = \"Day\";
        else if (thehour < 17)         display = \"Afternoon\";
        else if (thehour < 20)         display = \"Evening\";
        else display = \"Night\";
        var greeting = (\"Good \" + display);
        document.write(greeting);
        //  End -->
        </script>

     $member_db[2]";

    if ($todaynews != 1) {
        $s = "s";
    }
    if ($member_db[1] != 4) {
        if ($stats_users > 1) {
            $rand_msg[] = ", we have <b>$count_new_news</b> new articles since your last login";
            $rand_msg[] = ", we have <b>$count_new_news</b> new articles since your last login";
            $rand_msg[] = ", we have <b>$count_new_news</b> new articles since your last login";
        }
        if ($todaynews == 0) {
            $rand_msg[] = ", we don't have new articles today";
            $rand_msg[] = ", we don't have new articles today, the first one can be yours.";
        } elseif ($count_my_news == 0) {
            if ($todaynews == 1) {
                $rand_msg[] = ", today we have <b>$todaynews</b> new article{$s} but it is not yours";
            } else {
                $rand_msg[] = ", today we have <b>$todaynews</b> new article{$s} but <b>$count_my_news</b> of them are yours";
            }
        } elseif ($count_my_news == $todaynews) {
            if ($count_my_news == 1) {
                $rand_msg[] = ", today we have <b>$todaynews</b> new article{$s} and you wrote it";
            } else {
                $rand_msg[] = ", today we have <b>$todaynews</b> new article{$s} and you wrote all of them";
                $rand_msg[] = ", today we have <b>$todaynews</b> new article{$s} and all are yours";
                $rand_msg[] = ", today we have <b>$todaynews</b> new article{$s}, want to <a href=\"$PHP_SELF?mod=addnews&action=addnews\">add</a> some more?";
            }
        } else {
            if ($count_my_news == 1) {
                $rand_msg[] = ", today we have <b>$todaynews</b> new article{$s}, <b>1</b> of them is yours";
            } else {
                $rand_msg[] = ", today we have <b>$todaynews</b> new article{$s}, <b>$count_my_news</b> of them are yours";
            }
        }
        $rand_msg[] = ", are you in a mood of <a href=\"$PHP_SELF?mod=addnews&action=addnews\">adding</a> some news?";
        $rand_msg[] = ", today we have <b>$todaynews</b> new article{$s}, from total <b>$stats_news</b>";
        if ($member_db[9] != "") {
            $rand_msg[] = ", your last login was on ".date("d M Y H:i:s", $member_db[9]);
            $rand_msg[] = ", your last login was on ".date("d M Y H:i:s", $member_db[9]);
        }

        $rand_msg[] = "";

        srand((double) microtime() * 1000000);
        echo $rand_msg[rand(0, count($rand_msg)-1)]."<br /><br /></td></tr>";
    }


  //----------------------------------
  // Do we have enough free space ?
  //----------------------------------
  $dfs = @disk_free_space("./");
//  $dfs = 5341;
  if ($dfs and $dfs < 10240) {
      $freespace = formatsize($dfs);
      echo"<tr><td class=warningbox colspan=5 height=1>
         <b>Warning!</b><br />
         According to CuteNews, your estimated free space is $freespace. Take action to enlarge your free space or
         some data files could be damaged during the writeing procedure. Backup your data now.
         </td></tr>";
  }

  //----------------------------------
  // Install script still exists ?
  //----------------------------------
  if (file_exists('./inc/install.mdu.php')) {
      $freespace = formatsize($dfs);
      echo"<tr><td class=warningbox colspan=5 height=1>
         <b>Attention!</b><br />
         CuteNews found that the installation module is still located in the /inc folder.<br />
         Please delete or rename the <b>/inc/install.mdu.php</b> file for security reasons.
         </td></tr>";
  }

  //----------------------------------
  // Are we using SafeSkin ?
  //----------------------------------
  if ($using_safe_skin) {
      $freespace = formatsize($dfs);
      echo"<tr><td class=warningbox colspan=5 height=1>
         <b>Attention!</b><br />
         CuteNews was unable to load the selected '$config_skin' skin, and automatically reverted to the default one.<br />
         Please ensure that the proper skin files exists or select another skin.
         </td></tr>";
  }

  //----------------------------------
  // Is our PHP version old ?
  //----------------------------------
  if ($phpversion and $phpversion < '4.1.0') {
      $freespace = formatsize($dfs);
      echo"<tr><td class=warningbox colspan=5 height=1>
         <b>Attention!</b><br />
         Your version of PHP ($phpversion) is too old. Please consider contacting your server administrator and updating to the
         latest stable PHP version.
         </td></tr>";
  }


  // Show Some stats
    if ($member_db[1] == 1) {
        function checkperm($filename)
        {
            $perm = 0;
            if (is_readable($filename)) {
                $permr = "Read";
                $perm++;
            }
            if (is_writable($filename)) {
                $permw = "Write";
                $perm++;
            }

            if ($perm == 2) {
                $permout = "<font class=pass>Can $permr and $permw</font>";
            } elseif ($perm == 1) {
                $permout = "<font class=warning>Can Only $permr$permw</font>";
            } else {
                $permout = "<font class=error>ERROR!</font>";
            }
            echo $permout;
        }
        echo "<tr><td valign=middle height=1 class=altern1 width=286 colspan=2>
              &nbsp;<b>Some stats</b>
              <td valign=middle height=1 width=35>
              <td valign=middle height=1 class=altern1 width=326 colspan=2>
              &nbsp;<b>System SelfCheck</b>
              </tr>
              <tr>
              <td valign=top height=1 width=137>
<!--s01-->              &nbsp; Active News<br>
<!--s02-->              &nbsp; News File Size<br>
<!--s03-->              &nbsp; Active Comments<br>
<!--s04-->              &nbsp; Comments File Size<br>
<!--s05-->              &nbsp; Users<br>
<!--s06-->              &nbsp; Users File Size<br>
<!--s07-->              &nbsp; Archives<br>
              <td valign=top height=1 width=146>
<!--s01-->              $stats_news<br>
<!--s02-->              $stats_news_size<br>
<!--s03-->              $count_comments<br>
<!--s04-->              $stats_comments_size<br>
<!--s05-->              $stats_users<br>
<!--s06-->              $stats_users_size<br>
<!--s07-->              $stats_archives<br>
              <td valign=top height=1 width=37>
              <td valign=top height=1 width=201>
<!--c01-->              &nbsp; Check for cat.num.php<br>
<!--c02-->              &nbsp; Check for category.db.php<br>
<!--c03-->              &nbsp; Check for comments.db.php<br>
<!--c04-->              &nbsp; Check for counter.db.php<br>
<!--c05-->              &nbsp; Check for flood.db.php<br>
<!--c06-->              &nbsp; Check for news.db.php<br>
<!--c07-->              &nbsp; Check for profiles.db.php<br>
<!--c08-->              &nbsp; Check for protemp.db.php<br>
<!--c09-->              &nbsp; Check for ipban.db.php<br>
<!--c10-->              &nbsp; Check for xfields.db.php<br>
<!--c11-->              &nbsp; Check for xfieldsdata.db.php<br>
<!--c12-->              &nbsp; Check for archives dir<br>
<!--c13-->              &nbsp; Check for profiles dir<br>
              <td valign=top height=1 width=121>
<!--c01-->              ";
        checkperm("./data/cat.num.php");
        echo"<br>
<!--c02-->              ";
        checkperm("./data/category.db.php");
        echo"<br>
<!--c03-->              ";
        checkperm("./data/comments.db.php");
        echo"<br>
<!--c04-->              ";
        checkperm("./data/counter.db.php");
        echo"<br>
<!--c05-->              ";
        checkperm("./data/flood.db.php");
        echo"<br>
<!--c06-->              ";
        checkperm("./data/news.db.php");
        echo"<br>
<!--c07-->              ";
        checkperm("./data/profiles.db.php");
        echo"<br>
<!--c08-->              ";
        checkperm("./data/protemp.db.php");
        echo"<br>
<!--c09-->              ";
        checkperm("./data/ipban.db.php");
        echo"<br>
<!--c10-->              ";
        checkperm("./data/xfields.db.php");
        echo"<br>
<!--c11-->              ";
        checkperm("./data/xfieldsdata.db.php");
        echo"<br>
<!--c12-->              ";
        checkperm("./data/archives");
        echo"<br>
<!--c13-->              ";
        checkperm("./data/profiles");
        echo"<br>

              </tr>";
    }
        echo"</table>";

echofooter();
