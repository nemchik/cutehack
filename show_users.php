<?PHP
// Order Start
//	put them in whatever order you want
//	u = usernames
//	n = nicknames
//	r = registration dates
//	a = avatar links
//	p = post counts
//	l = access levels
//	e = emails
// NOTE: these must ALL be set, even if you want them hidden, hide them below
$display1 = "u";
$display2 = "n";
$display3 = "r";
$display4 = "a";
$display5 = "p";
$display6 = "l";
$display7 = "e";
// Order End

// Hiding Start
//	add the letters from above to hide them
// ex: $user_flags = "np"; hides nickname and posts
$user_flags = "";
// Hiding End

// Date Format Start
//	this is the format to display when users joined
//	if you dont know what it is look for the date function on php.net
//	or just leave it alone...
$date_format = "F d, Y";
// Date Format End

// DO NOT EDIT BELOW
error_reporting (E_ALL ^ E_NOTICE);

$cutepath =  __FILE__;
$cutepath = preg_replace( "'\\\show_users\.php'", "", $cutepath);
$cutepath = preg_replace( "'/show_users\.php'", "", $cutepath);

require_once("$cutepath/inc/functions.inc.php");
require_once("$cutepath/data/config.php");

$user_query = cute_query_string($QUERY_STRING, array( "sortus","sortad"));

if(isset($bgcolor) && $bgcolor != ""){ $bg = "bgcolor=\"$bgcolor\""; }
		if(!stristr($user_flags, "u")) { $u = "<td $bg>&nbsp;<u>Username</u>"; }
		if(!stristr($user_flags, "n")) { $n = "<td $bg>&nbsp;<u>Nickname</u>"; }
		if(!stristr($user_flags, "r")) { $r = "<td $bg>&nbsp;<u>Registration Date</u>"; }
		if(!stristr($user_flags, "a")) { $a = "<td $bg>&nbsp;<u>Avatar</u>"; }
		if(!stristr($user_flags, "p")) { $p = "<td $bg>&nbsp;<u>Posts</u>"; }
		if(!stristr($user_flags, "l")) { $l = "<td $bg>&nbsp;<u>Access Level</u>"; }
		if(!stristr($user_flags, "e")) { $e = "<td $bg>&nbsp;<u>EMail</u>"; }

echo "<table border=0 cellspacing=0 cellpadding=0 ><tr>";
		echo $$display1.$$display2.$$display3.$$display4.$$display5.$$display6.$$display7;
echo "</tr>";

$all_users = file("$cutepath/data/users.db.php");
// Sort users v1.0 - Start addblock
if (!isset($sortus)) { $sortus="0"; } if (!isset($sortad)) { $sortad="a"; }
if (isset($sortus)) {
if (!function_exists('sortcmp')) {
 function sortcmp($a, $b) {
  global $all_users, $sortus;

  $users_a = explode('|', $all_users[$a]);
  $users_b = explode('|', $all_users[$b]);

  return strnatcasecmp($users_a[$sortus], $users_b[$sortus]);
 }
}
uksort($all_users, 'sortcmp');
if ($sortad=="d") { $all_users = array_reverse($all_users); }
Unset($sortus);
}
// Sort users v1.0 - End addblock
    $i = 1;
    foreach($all_users as $null => $user_line)
    {
        $i++; $bg = "";
        if($i%2 == 0 && isset($bgcolor) && $bgcolor != ""){ $bg = "bgcolor=\"$bgcolor\""; }
        if(!eregi("<\?",$user_line)){
        $user_arr = explode("|", $user_line);

        if(isset($user_arr[9]) and $user_arr[9] != ''){ $last_login = date('r',$user_arr[9]); }
        else{ $last_login = 'never'; }

        if($user_arr[7]=="0"){$user_email = "<a href=mailto:$user_arr[5]>[send mail]</a>";}
        else{$user_email = "[hidden]";}
        if($user_arr[8]!=""){$user_av = "<a href=$user_arr[8]>[click]</a>";}
        else{$user_av = "[none]";}

        $user_joined = date($date_format,$user_arr[0]);

        switch($user_arr[1]){
        case 1: $user_level = "administrator"; break;
        case 2: $user_level = "editor"; break;
        case 3: $user_level = "journalist"; break;
        case 4: $user_level = "commenter"; break;
        case 5: $user_level = "banned"; break;
        }
		if(!stristr($user_flags, "u")) { $u = "<td>&nbsp;$user_arr[2]"; }
		if(!stristr($user_flags, "n")) { $n = "<td>&nbsp;$user_arr[4]"; }
		if(!stristr($user_flags, "r")) { $r = "<td>&nbsp;$user_joined"; }
		if(!stristr($user_flags, "a")) { $a = "<td>&nbsp;$user_av"; }
		if(!stristr($user_flags, "p")) { $p = "<td>&nbsp;$user_arr[6]"; }
		if(!stristr($user_flags, "l")) { $l = "<td>&nbsp;$user_level"; }
		if(!stristr($user_flags, "e")) { $e = "<td>&nbsp;$user_email"; }
echo "<tr $bg title='$user_arr[2]&#039;s last login was on: $last_login'>";
	echo $$display1.$$display2.$$display3.$$display4.$$display5.$$display6.$$display7;
echo "</tr>";
		}
    }
echo "</tr></table><form method=post action=\"$PHP_SELF?$user_query\" ><select name=sortus >";
	if(!stristr($user_flags, "u")) { echo"<option value=2 "; if($_POST['sortus']=="2"){echo "selected";} echo " >Username</option>"; }
	if(!stristr($user_flags, "n")) { echo"<option value=4 "; if($_POST['sortus']=="4"){echo "selected";} echo " >Nickname</option>"; }
	if(!stristr($user_flags, "r")) { echo"<option value=0 "; if($_POST['sortus']=="0"){echo "selected";} echo " >Registration Date</option>"; }
	if(!stristr($user_flags, "p")) { echo"<option value=6 "; if($_POST['sortus']=="6"){echo "selected";} echo " >Posts</option>"; }
	if(!stristr($user_flags, "l")) { echo"<option value=1 "; if($_POST['sortus']=="1"){echo "selected";} echo " >Access Level</option>"; }
echo "</select>
<select name=sortad >
<option value=d "; if($_POST['sortad']=="d"){echo "selected";} echo " >Descending Order</option>
<option value=a "; if($_POST['sortad']=="a"){echo "selected";} echo " >Ascending Order</option>
</select>
<input type=submit value=Sort >
</form>";
?>