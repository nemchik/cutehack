<?php
// show_random.php
error_reporting(E_ALL ^ E_NOTICE);
$cutepath =  __FILE__;
$cutepath = preg_replace("'\\\show_random\.php'", "", $cutepath);
$cutepath = preg_replace("'/show_random\.php'", "", $cutepath);
$news = file($cutepath."data/news.db.php");
$i = 0;
$newsids = array();
foreach ($news as $news_line) {
    if (eregi("<\?", $news_line)) {
        continue;
    }
    $news_arr = explode("|", $news_line);
    if (isset($category) && !stristr($category, $news_arr[6])) {
        continue;
    }
    $newsids[$i] = $news_arr[0];
    $i++;
}
$randid = rand(0, count($newsids)-1);
$subaction = "showfull";
$id = $newsids[$randid];
include($cutepath."show_news.php");
