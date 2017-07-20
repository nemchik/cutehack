<?PHP

// START Configurable
$config_use_sessions		= TRUE;		// Use Sessions When Checking Authorization (if no the remember me chexkbox will be forced on)
$config_check_referer	= TRUE;		// Set to TRUE for more security
// END Configurable DO NOT EDIT BELOW

error_reporting (E_ALL ^ E_NOTICE);

$cutepath =  __FILE__;
$cutepath = preg_replace( "'\\\loginout\.php'", "", $cutepath);
$cutepath = preg_replace( "'/loginout\.php'", "", $cutepath);
chdir($cutepath);

require_once($cutepath."/inc/functions.inc.php");
require_once($cutepath."/data/config.php");

global $timer;
$timer=(float)array_sum(explode(' ', microtime()));

if($config_use_sessions) {
	@session_start();
	@header("Cache-control: private");
}

$temp_arr = explode("?", $_SERVER['HTTP_REFERER']); // http://[domain]/[serverpath]/[serverfilename]
$_SERVER['HTTP_REFERER'] = $temp_arr[0];
if(substr($_SERVER['HTTP_REFERER'], -1) == "/")
	$_SERVER['HTTP_REFERER'].= "index.php";

$message="";
$session_logged = FALSE;
$cookie_logged = FALSE;
$is_logged_in = FALSE;

if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
	$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
elseif(isset($_SERVER["HTTP_CLIENT_IP"]))
	$ip = $_SERVER["HTTP_CLIENT_IP"];
else
	$ip = $_SERVER["REMOTE_ADDR"];
if($ip == "")
	$ip = "ip not detected";

function checkReferer() {
	global $config_check_referer;
	if($config_check_referer) {
		if(!eregi("^".$_SERVER['HTTP_REFERER'],"http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']) and $_SERVER['HTTP_REFERER'] != "") {
			die("<h2>Sorry but your access to this page was denied !</h2><br>try to <a href='?action=logout'>logout</a> and then login again<br>To turn off this security check, change \$config_check_referer in loginout.php to FALSE");
		}
	}
}

function logout($faction) {
	@session_destroy();
	@session_unset();
	@setcookie(session_name(), "");
	@setcookie("username","",false,"/");
	@setcookie("password","",false,"/");
	@setcookie("rememberpw","",false,"/");
	@setcookie("logged_out", true, time()+1012324305, "/");
	$is_logged_in=false;
	if($faction=="logout")
		header('Location: '.preg_replace("/action=logout/i", "", $_SERVER['PHP_SELF']));
}

function modifyLastLoginDate($fmemberdb2,$ftime) {
	while(true) {
		$oldusersfile = file("./data/users.db.php");
		$newusersfile = fopen("./data/users.db.php", "w");
		if(flock($newusersfile, LOCK_EX)) {
			fwrite($newusersfile, $oldusersfile[0]);
			for($i=1;$i<count($oldusersfile);$i++) {
				$olduserline = explode(">", $oldusersfile[$i]);
				if(strtolower($fmemberdb2) != strtolower($olduserline[2]))
					fwrite($newusersfile, $oldusersfile[$i]);
				else {
					$olduserline[9]=$ftime;
					fwrite($newusersfile, trim(implode(">",$olduserline))."\n");
				}
			}
			fclose($newusersfile);
			break;
		}
		else {
			fclose($newusersfile);
			sleep(1);
		}
	}
}

#############################################

$time=time();
if($_GET['action']=="logout")
	logout($_GET['action']);
elseif($_POST['action']=="dologin" and check_login($_POST['username'], $_POST['password'], $_POST['time'], "")) {
	if($member_db[1] == "5"){
		$message = "<div class=error>This username is banned!</div>";
		logout($_GET['action']);
	} else {
		checkReferer();
		if($config_use_sessions) {
			$_SESSION["username"]	= $_POST['username'];
			$_SESSION["password"]	= $_POST['password'];
			$_SESSION["stime"]		= $_POST['time'];
			$_SESSION["ip"]			= $ip;
		}
		if($_POST['rememberpw']) {
			setcookie("lastusername", $_POST['username'], $time+1012324305, "/");
			setcookie("username", $_POST['username'], $time+1012324305, "/");
			setcookie("password", $_POST['password'], $time+1012324305, "/");
			setcookie("rememberpw", true, $time+1012324305, "/");
			setcookie("timelastlogin", $_POST['time'], $time+1012324305, "/");
		} else {
			setcookie("username","",false,"/");
			setcookie("password","",false,"/");
			setcookie("rememberpw","",false,"/");
		}
		setcookie("logged_out","");
		setcookie("logged_out","",false,"/");
		modifyLastLoginDate($member_db[2],$_POST['time']);
		$is_logged_in=true;
	}
} elseif($_POST['action']!="dologin" and isset($_COOKIE["logged_out"]) and isset($_COOKIE["rememberpw"]))
	logout($_GET['action']);
elseif($config_use_sessions and !isset($_COOKIE["logged_out"]) and $_SESSION["ip"]==$ip and check_login($_SESSION["username"], $_SESSION["password"], $_SESSION["stime"], 'session')) {
	setcookie("logged_out","");
	$is_logged_in=true;
} elseif(check_login($_COOKIE["username"], $_COOKIE["password"], $_COOKIE["timelastlogin"], "")) {
	$time=time();
	$password=md5($member_db[3].$time);
	if($_COOKIE["timelastlogin"]+60<$time or $_POST['action']=="dologin") {
		setcookie("password", $password, $time+1012324305, "/");
		setcookie("timelastlogin", $time, $time+1012324305, "/");
		modifyLastLoginDate($member_db[2],$time);
	}
	if($config_use_sessions) {
		$_SESSION["username"]	= $_COOKIE["username"];
		$_SESSION["password"]	= $password;
		$_SESSION["stime"]		= $time;
		$_SESSION["ip"]			= $ip;
	}
	setcookie("lastusername", $_COOKIE["username"], $time+1012324305, "/");
	setcookie("logged_out","");
	$is_logged_in=true;
} else {
	if($_POST['action']=="dologin")
		$message = "<div class=error>Wrong username and/or password</div>";
	logout($_GET['action']);
}
?>