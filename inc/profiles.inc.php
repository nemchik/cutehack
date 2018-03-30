<?php
require_once($cutepath."/data/config.php");
require_once($cutepath."/inc/functions.inc.php");
include("$cutepath/data/protemp.db.php");

$output = $template_pfl;
$output = profiledata($user, $output);
$output = ascii_convert($output);
echo $output;
