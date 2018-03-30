<?php

if ($member_db[1] != 1) {
    msg("error", "Access Denied", "You don't have permission for this section");
}
$success = false;
// ********************************************************************************
// Archive
// ********************************************************************************
if ($action == "archive") {
    echoheader("archives", "Archives");

    echo<<<HTML

    <script language="javascript">
    function confirmdelete(id,news){
    	var agree=confirm("Do you really want to permanently delete this archive ?\\nAll ("+news+") news and comments in it will be deleted.");
	if (agree)
	document.location="$PHP_SELF?mod=tools&action=dodeletearchive&archive="+id;
	}
    </script>
	<form method=post action="$PHP_SELF"><table border=0 cellpadding=0 cellspacing=0 width="645" >
	<td width=321 height="33">
    <b>Send news to archive</b>
<table border=0 cellpadding=0 cellspacing=0 width=300  class="panel" cellpadding="10" >
    <tr>
    <td width=304 height="25">
    <p align="center">
    <input type=submit value="Archive All Active News ...">
    </tr>

    </table>
	<input type=hidden name=action value=doarchive>
	<input type=hidden name=mod value=tools>
    </form>

	<td width=320 height="33" align="center">
       <!-- HELP -->

   <table height="25" cellspacing="0" cellpadding="0">
    <tr>
      <td width="25" align=middle><img border="0" src="skins/images/help_small.gif" /></td>
      <td >&nbsp;<a onClick="javascript:Help('archives')" href="#">Explaining archives and<br />
        &nbsp;Their usage</a></td>
    </tr>
  </table>

    <tr>
	<td width=654 colspan="2" height="11">
    <img height=20 border=0 src="skins/images/blank.gif" width=1 />
    <br />    </tr>
    <tr>
	<td width=654 colspan=2 height=14>
    <b>Available archives</b>
    </tr>
    <tr>

	<td width=654 colspan=2 height=1>
  <table width=641 height=100% cellspacing=0 cellpadding=0>
    <tr>
      <td width=8 class=altern1>&nbsp;</td>
      <td width=160 class=altern1><u>Archivation Date</u></td>
      <td width=222 class=altern1><u>Duration</u></td>
      <td width=81 class=altern1><u>News</u></td>
      <td width=110 class=altern1><u>Action</u></td>

    </tr>
HTML;

    if (!$handle = opendir("./data/archives")) {
        die("<center>Can not open directory $cutepath/data/archives ");
    }
    while (false !== ($file = readdir($handle))) {
        if ($file != "." and $file != ".." and !is_dir("./data/archives/$file") and eregi("news.arch.php", $file)) {
            $file_arr = explode(".", $file);
            $id          = $file_arr[0];

            $news_lines = file("./data/archives/$file");
            $creation_date = date("d F Y", $file_arr[0]);
            $count = count($news_lines)-1;
            $last = $count-1;
            $first_news_arr = explode("|", $news_lines[$last]);
            $last_news_arr    = explode("|", $news_lines[1]);

            $first_timestamp = $first_news_arr[0];
            $last_timestamp     = $last_news_arr[0];

            $duration = (date("d M Y", $first_timestamp) ." - ". date("d M Y", $last_timestamp));
            echo "
				<tr>
			      <td ></td>
			      <td >$creation_date</td>
			      <td >$duration</td>
			      <td >$count</td>
			      <td ><a title='Edit the news in this archive' href=\"$PHP_SELF?mod=editnews&action=list&source=$id\">[edit]</a> <a title='Delete this archive' onClick=\"javascript:confirmdelete('$id', '$count');\" href=\"#\">[delete]</a></td>
				</tr>
                ";
        }
    }
    closedir($handle);

    if ($count == 0) {
        echo"<tr><td align=center colspan=6><br />There are no archives</td></tr>";
    }

    echo<<<HTML
</table>
</table>
HTML;

    echofooter();
}
// ********************************************************************************
// Make Archive
// ********************************************************************************
elseif ($action == "doarchive") {
    if (filesize("./data/news.db.php") <= 70) {
        msg("error", "Error !!!", "Sorry but there are no news to be archived", "$PHP_SELF?mod=tools&action=archive");
    }
    if (filesize("./data/comments.db.php") <= 70) {
        msg("error", "Error !!!", "The comments file is empty and can not be archived", "$PHP_SELF?mod=tools&action=archive");
    }

    $arch_name = time()+($config_date_adjust*60);
    if (!@copy("./data/news.db.php", "./data/archives/$arch_name.news.arch.php")) {
        msg("error", "Error !!!", "Can not create file ./data/archives/$arch_name.news.arch.php", "$PHP_SELF?mod=tools&action=archive");
    }
    if (!@copy("./data/comments.db.php", "./data/archives/$arch_name.comments.arch.php")) {
        msg("error", "Error !!!", "Can not create file ./data/archives/$arch_name.comments.arch.php", "$PHP_SELF?mod=tools&action=archive");
    }

    $handle = fopen("./data/news.db.php", "w");
    fclose($handle);
    $handle = fopen("./data/comments.db.php", "w");
    fclose($handle);

    msg("archives", "Archive Saved", "&nbsp&nbsp; All active news were successfully added to archives file with name  <b>$arch_name.news.arch.php</b>", "$PHP_SELF?mod=tools&action=archive");
}
// ********************************************************************************
// Do Delete Archive
// ********************************************************************************
elseif ($action == "dodeletearchive") {
    $success = 0;
    if (!$handle = opendir("./data/archives")) {
        die("<center>Can not open directory $cutepath/data/archive ");
    }
    while (false !== ($file = readdir($handle))) {
        if ($file == "$archive.news.arch.php" or  $file == "$archive.comments.arch.php") {
            unlink("./data/archives/$file");
            $success ++;
        }
    }
    closedir($handle);

    if ($success == 2) {
        msg("info", "Arhcive Deleted", "The archive was successfully deleted", "$PHP_SELF?mod=tools&action=archive");
    } elseif ($success == 1) {
        msg("error", "Error !!!", "Either the comments part or the news part of the archive was not deleted", "$PHP_SELF?mod=tools&action=archive");
    } else {
        msg("error", "Error !!!", "The archive you specified was not deleted, it is not on the server or you don't have permissions to delete it", "$PHP_SELF?mod=tools&action=archive");
    }
}
// ********************************************************************************
// Backup News and archives
// ********************************************************************************
elseif ($action == "backup") {
    echoheader("options", "Backup");
    echo'
     <script language="javascript">
    function confirmdelete(id){
	var agree=confirm("Do you really want to permanently delete this backup ?");
	if (agree)
	document.location="index.php?mod=tools&action=dodeletebackup&backup="+id;
	}
    function confirmrestore(id){
	var agree=confirm("Do you really want to restore your news from this backup ?\nAll current news and archives will be overwritten.");
	if (agree)
	document.location="index.php?mod=tools&action=dorestorebackup&backup="+id;
	}
    </script>
	<table border=0 cellpadding=0 cellspacing=0 width="645" >
    <td width=321 height="33">
    <b>Create BackUp</b>
<table border=0 cellpadding=0 cellspacing=0 class="panel" cellpadding="10" width="390" >
    <form method=post action="'.$PHP_SELF.'">
    <tr>
    <td height="25" width="366">
    Name of the BackUp: <input type=text name=back_name>&nbsp; <input type=submit value=" Proceed ">

    </td>
    </tr>
	<input type=hidden name=action value=dobackup>
	<input type=hidden name=mod value=tools>
</form>
</table>
    <tr>
	<td width=654 height="11">
    <img height=20 border=0 src="skins/images/blank.gif" width=1 />
    <br />    </tr>
    <tr>
	<td width=654 height=14>
    <b>Available BackUps</b>
    </tr>
    <tr>
	<td width=654 height=1>
  <table width=641 height=100% cellspacing=0 cellpadding=0>
    <tr>
      <td width=2% class=altern1>&nbsp;</td>
      <td width=40% class=altern1><u>Name</u></td>
      <td width=22% class=altern1><u>Active News</u></td>
      <td width=16% class=altern1><u>Archives</u></td>
      <td width=20% class=altern1><u>Action</u></td>
    </tr>';

    $count = 0;
    if (!$handle = opendir("./data/backup")) {
        die("<center>Can not open directory $cutepath/data/backup ");
    }
    while (false !== ($file = readdir($handle))) {
        if ($file != "." and $file != ".." and is_dir("./data/backup/$file")) {
            $archives_count = 0;
            $archives_handle = @opendir("./data/backup/$file/archives");
            while (false !== ($arch = readdir($archives_handle))) {
                if (eregi(".news.arch.php", $arch)) {
                    $archives_count++;
                }
            }
            closedir($archives_handle);


            $news_count = count(file("./data/backup/$file/news.db.php"))-1;
            echo "<tr>
				      <td></td>
				      <td>$file</td>
                      <td>&nbsp;$news_count</td>
                      <td>&nbsp;$archives_count</td>
				      <td><a onClick=\"javascript:confirmdelete('$file'); return(false)\" href=\"$PHP_SELF?mod=tools&action=dodeletebackup&backup=$file\">[delete]</a> <a onClick=\"javascript:confirmrestore('$file'); return(false)\" href=\"$PHP_SELF?mod=tools&action=dorestorebackup&backup=$file\">[restore]</a></td>
					  </tr>";
            $count++;
        }
    }
    closedir($handle);

    if ($count == 0) {
        echo"<tr><td colspan=5><p align=center><br />There are no backups</p></td></tr>";
    }

    echo'</table></table>';

    echofooter();
}

// ********************************************************************************
// Do Delete Backup
// ********************************************************************************
elseif ($action == "dodeletebackup") {
    function listdir($dir)
    {
        $current_dir = opendir($dir);
        while ($entryname = readdir($current_dir)) {
            if (is_dir("$dir/$entryname") and ($entryname != "." and $entryname!="..")) {
                listdir("${dir}/${entryname}");
            } elseif ($entryname != "." and $entryname!="..") {
                unlink("${dir}/${entryname}");
            }
        }
        @closedir($current_dir);
        rmdir(${dir});
    }
    listdir("./data/backup/$backup");

    msg("info", "Backup Deleted", "The backup was successfully deleted.", "$PHP_SELF?mod=tools&action=backup");
}
// ********************************************************************************
// Do restore backup
// ********************************************************************************
elseif ($action == "dorestorebackup") {
    if (!@copy("./data/backup/$backup/news.db.php", "./data/news.db.php")) {
        msg("error", "error", "./data/backup/$backup/news.db.php", "$PHP_SELF?mod=tools&action=backup");
    }
    if (!@copy("./data/backup/$backup/comments.db.php", "./data/comments.db.php")) {
        msg("error", "error", "./data/backup/$backup/comments.db.php", "$PHP_SELF?mod=tools&action=backup");
    }

    $dirp = opendir("./data/backup/$backup/archives");
    while ($entryname = readdir($dirp)) {
        if (!is_dir("./data/backup/$backup/archives/$entryname") and $entryname!="." and $entryname!="..") {
            if (!@copy("./data/backup/$backup/archives/$entryname", "./data/archives/$entryname")) {
                msg("error", "error", "Can not copy ./data/backup/$backup/archives/$entryname");
            }
        }
    }

    msg("info", "Backup Restored", "The backup was successfully restored.", "$PHP_SELF?mod=tools&action=backup");
}
// ********************************************************************************
// Make The BackUp
// ********************************************************************************
elseif ($action == "dobackup") {
    $back_name = eregi_replace(" ", "-", $back_name);


    if (filesize("./data/news.db.php") <= 70) {
        msg("error", "Error !!!", "The news file is empty and can not be backed-up", "$PHP_SELF?mod=tools&action=backup");
    }
    if (filesize("./data/comments.db.php") <= 70) {
        msg("error", "Error !!!", "The comments file is empty and can not be backed-up", "$PHP_SELF?mod=tools&action=backup");
    }

    if (is_readable("./data/backup/$back_name")) {
        msg("error", "Error !!!", "A backup with this name already exist", "$PHP_SELF?mod=tools&action=backup");
    }
    if (!is_readable("./data/backup")) {
        mkdir("./backup", 0777);
    }
    if (!is_writable("./data/backup")) {
        msg("error", "Error !!!", "The directory ./data/backup is not writable, please chmod it");
    }
    mkdir("./data/backup/$back_name", 0777);
    mkdir("./data/backup/$back_name/archives", 0777);

    if (!@copy("./data/news.db.php", "./data/backup/$back_name/news.db.php")) {
        die("Can not copy news.db.php file to ./data/backup/$back_name :(");
    }
    if (!@copy("./data/comments.db.php", "./data/backup/$back_name/comments.db.php")) {
        die("Can not copy comments.db.php file to ./data/backup/$back_name :(");
    }

    if (!$handle = opendir("./data/archives")) {
        die("Can not create file");
    }
    while (false !== ($file = readdir($handle))) {
        if ($file != "." and $file != "..") {
            if (!@copy("./data/archives/$file", "./data/backup/$back_name/archives/$file")) {
                die("Can not copy archive file to ./data/backup/$back_name/archives/$file :(");
            }
        }
    }
    closedir($handle);

    msg("info", "Backup", "All news and archives were successfully BackedUp under directory  './data/backup/$back_name'", "$PHP_SELF?mod=tools&action=backup");
}
