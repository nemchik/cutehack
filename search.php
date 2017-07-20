<?php

error_reporting(E_ALL ^ E_NOTICE);

$cutepath =  __FILE__;
$cutepath = preg_replace("'\\\search\.php'", "", $cutepath);
$cutepath = preg_replace("'/search\.php'", "", $cutepath);

require_once("$cutepath/inc/functions.inc.php");

$user_query = cute_query_string($QUERY_STRING, array("search_in_archives", "start_from", "archive", "subaction", "id", "cnshow",
"ucat","dosearch", "story", "title", "user", "category", "from_date_day", "from_date_month", "from_date_year", "to_date_day", "to_date_month", "to_date_year"));
$user_post_query = cute_query_string($QUERY_STRING, array("search_in_archives", "start_from", "archive", "subaction", "id", "cnshow",
"ucat","dosearch", "story", "title", "user", "category", "from_date_day", "from_date_month", "from_date_year", "to_date_day", "to_date_month", "to_date_year"), "post");

// Define Users
$all_users = file("$cutepath/data/users.db.php");
foreach ($all_users as $null => $my_user) {
    if (!eregi("<\?", $member_db_line)) {
        $user_arr = explode("|", $my_user);
        if ($user_arr[4] != "") {
            $my_names[$user_arr[2]] = "$user_arr[4]";
        } else {
            $my_names[$user_arr[2]] = "$user_arr[2]";
        }
    }
}
// Define Categories
$cat_lines = file("$cutepath/data/category.db.php");
foreach ($cat_lines as $null => $single_line) {
    if (eregi("<\?", $single_line)) {
        continue;
    }
    $cat_arr = explode("|", $single_line);
    $cat[$cat_arr[0]] = $cat_arr[1];
    $cat_icon[$cat_arr[0]]=$cat_arr[2];
}
// Show Search Form
echo<<<HTML
<script language='javascript' type="text/javascript">
        function mySelect(form){
            form.select();
    }
        function ShowOrHide(d1, d2) {
          if (d1 != '') DoDiv(d1);
          if (d2 != '') DoDiv(d2);
        }
        function DoDiv(id) {
          var item = null;
          if (document.getElementById) {
                item = document.getElementById(id);
          } else if (document.all){
                item = document.all[id];
          } else if (document.layers){
                item = document.layers[id];
          }
          if (!item) {
          }
          else if (item.style) {
                if (item.style.display == "none"){ item.style.display = ""; }
                else {item.style.display = "none"; }
          }else{ item.visibility = "show"; }
         }
</script>
<form method=GET action="$PHP_SELF?subaction=search">
<input type=hidden name=dosearch value=yes>

  <table align="center" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td><table width="100%" cellspacing="0" cellpadding="0">
          <td width="100%">
            <p align="right">News <input type=text value="$story" name=story size="24">
        </table></td>
    </tr>
    <tr>
      <td>

<div id='advanced' style='display:none;z-index:1;'>
<table width="100%" cellspacing="0" cellpadding="0">
          <td width="100%" align="right">
            <p align="right">Title&nbsp;<input type=text value="$title" name=title size="24">
  <tr>
    <td width="100%" align="right">Author&nbsp;<input type=text value="$user" name=user size="24">
  </tr>
<!-- search cats -->
HTML;
    if ((count($cat_lines)-1) > 0) {
        echo"<tr>
              <td width=\"100%\" align=\"right\">
              Category&nbsp;
              <select name=category>\n
              <option value=\"\" selected>- All -</option>\n";
        foreach ($cat_lines as $null => $single_line) {
            $cat_arr = explode("|", $single_line);
            if (strlen($cat_arr[1]) > 17) {
                $cat_arr['disp'] = substr($cat_arr[1], 0, 17 - 3) . '&hellip;';
            } else {
                $cat_arr['disp'] = $cat_arr[1];
            }
            echo"<option value=\"$cat_arr[0]\">$cat_arr[disp]</option>\n";
        }
        echo"</select></tr>";
    }
echo <<<HTML
<!-- search cats -->
  <tr>
    <td width="100%" align="right">From date
       <select name=from_date_day>
       <option value="">  </option>
HTML;
for ($i=1;$i<32;$i++) {
    if ($from_date_day == $i) {
        echo"<option selected value=$i>$i</option>";
    } else {
        echo"<option value=$i>$i</option>";
    }
}

echo"</select><select name=from_date_month>       <option value=\"\">  </option>";

for ($i=1;$i<13;$i++) {
    $timestamp = mktime(0, 0, 0, $i, 1, 2003);
    if ($from_date_month == $i) {
        echo"<option selected value=$i>". date("M", $timestamp) ."</option>";
    } else {
        echo"<option value=$i>". date("M", $timestamp) ."</option>";
    }
}

echo"</select><select name=from_date_year>       <option value=\"\">  </option>";

for ($i=2003;$i<2011;$i++) {
    if ($from_date_year == $i) {
        echo"<option selected value=$i>$i</option>";
    } else {
        echo"<option value=$i>$i</option>";
    }
}
//////////////////////////////////////////////////////////////////////////
echo<<<HTML
  </tr>
  <tr>
    <td width="100%" align="right">To date
       <select name=to_date_day>
       <option value="">  </option>
HTML;
for ($i=1;$i<32;$i++) {
    if ($to_date_day == $i) {
        echo"<option selected value=$i>$i</option>";
    } else {
        echo"<option value=$i>$i</option>";
    }
}

echo"</select><select name=to_date_month><option value=\"\">  </option>";

for ($i=1;$i<13;$i++) {
    $timestamp = mktime(0, 0, 0, $i, 1, 2003);
    if ($to_date_month == $i) {
        echo"<option selected value=$i>". date("M", $timestamp) ."</option>";
    } else {
        echo"<option value=$i>". date("M", $timestamp) ."</option>";
    }
}

echo"</select><select name=to_date_year><option value=\"\">  </option>";

for ($i=2003;$i<2011;$i++) {
    if ($to_date_year == $i) {
        echo"<option selected value=$i>$i</option>";
    } else {
        echo"<option value=$i>$i</option>";
    }
}

if ($search_in_archives) {
    $selected_search_arch = "checked=\"checked\"";
}

echo<<<HTML
      </select>
  </tr>
  <tr>
    <td width="100%" align="right">
      <p align="right"><label>Search and archives
    <input type=checkbox $selected_search_arch name="search_in_archives" value="TRUE"></label>
  </tr>
</table>
</div>

          </td>
    </tr>
    <tr>
      <td>
        <p align="right">&nbsp;
    <a href="javascript:ShowOrHide('advanced','')">advanced</a>&nbsp;&nbsp; <input type=submit value=Search>
      </td>
    </tr>
  </table>
$user_post_query
</form>
HTML;

// Don't edit below this line unless you know what you are doing !!!

if ($dosearch == "yes") {
    if ($from_date_day != "" and $from_date_month != "" and $from_date_year != "" and $to_date_day != "" and $to_date_month != "" and $to_date_year != "") {
        $date_from         = mktime(0, 0, 0, $from_date_month, $from_date_day, $from_date_year);
        $date_to         = mktime(0, 0, 0, $to_date_month, $to_date_day, $to_date_year);

        $do_date = true;
    }


    $story = trim($story);

    if ($search_in_archives) {
        if (!$handle = opendir("$cutepath/data/archives")) {
            die("Can not open directory $cutepath/data/archives ");
        }
        while (false !== ($file = readdir($handle))) {
            if ($file != "." and $file != ".." and eregi("news", $file)) {
                $files_arch[] = "$cutepath/data/archives/$file";
            }
        }
    }
    $files_arch[] = "$cutepath/data/news.db.php";

    foreach ($files_arch as $null => $file) {
        if (eregi("<\?", $file)) {
            continue;
        }
        $archive = false;
        if (ereg("([[:digit:]]{0,})\.news\.arch\.php", $file, $regs)) {
            $archive = $regs[1];
        }
        $all_news_db = file("$file");
        foreach ($all_news_db as $null => $news_line) {
            $news_db_arr = explode("|", $news_line);
            $found  = 0;

            $fuser  = false;
            $ftitle = false;
            $fstory = false;
            $fcat   = false;
            if ($title and @preg_match("/$title/i", "$news_db_arr[2]")) {
                $ftitle = true;
            }
            if ($user  and @preg_match("/\b$user\b/i", "$news_db_arr[1]")) {
                $fuser = true;
            }
            if ($story and (@preg_match("/$story/i", "$news_db_arr[4]") or @preg_match("/$story/i", "$news_db_arr[3]"))) {
                $fstory = true;
            }
            if ($category == $news_db_arr[6]) {
                $fcat = true;
            }

            if ($title and $ftitle) {
                $ftitle = true;
            } elseif (!$title) {
                $ftitle = true;
            } else {
                $ftitle = false;
            }
            if ($story and $fstory) {
                $fstory = true;
            } elseif (!$story) {
                $fstory = true;
            } else {
                $fstory = false;
            }
            if ($user  and $fuser) {
                $fuser  = true;
            } elseif (!$user) {
                $fuser  = true;
            } else {
                $fuser  = false;
            }
            if ($category  and $fcat) {
                $fcat  = true;
            } elseif (!$category) {
                $fcat  = true;
            } else {
                $fcat  = false;
            }
            if ($do_date) {
                if ($date_from < $news_db_arr[0] and  $news_db_arr[0] < $date_to) {
                    $fdate = true;
                } else {
                    $fdate = false;
                }
            } else {
                $fdate = true;
            }

            if ($fdate and $ftitle and $fuser and $fstory and $fcat) {
                $found_arr[$news_db_arr[0]] = $archive;
            }
        }//foreach news line
    }

    echo"<br /><b>Found News articles [".(count($found_arr)-1)."]:</b><br />";


    if ($do_date) {
        echo"from ".@date("d F Y", $date_from)." to ".@date("d F Y", $date_to)."<br />";
    }

    // Display Search Results
    if (is_array($found_arr)) {
        foreach ($found_arr as $news_id => $archive) {
            if ($archive) {
                $all_news = file("$cutepath/data/archives/$archive.news.arch.php");
            } else {
                $all_news = file("$cutepath/data/news.db.php");
            }

            foreach ($all_news as $null => $single_line) {
                if (eregi("<\?", $single_line)) {
                    continue;
                }
                $item_arr = explode("|", $single_line);
                $local_id = $item_arr[0];

// search cats
if ($category == $item_arr[6] || $fcat == true || !isset($category)) {
    if ($local_id == $news_id) {
        echo"<br /><b><a href=\"$PHP_SELF?misc=search&subaction=showfull&id=$local_id&archive=$archive&cnshow=news&ucat=$item_arr[6]&start_from=&$user_query\">$item_arr[2]</a></b> (". date("d F, Y", $item_arr[0]) .")";
    }
}
// show cats
            }
        }
    } else {
        echo"There are no news matching your search criteria";
    }
}//if user wants to search
elseif (($misc == "search") and ($subaction == "showfull" or $subaction == "showcomments" or $_POST["subaction"] == "addcomment" or $subaction == "addcomment")) {
    require_once("$cutepath/show_news.php");

    unset($action, $subaction);
}
