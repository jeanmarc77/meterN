<?php
/**
 * /srv/http/metern/admin/admin.php
 *
 * @package default
 */


include 'secure.php';
include '../config/memory.php';
include '../scripts/version.php';
include '../scripts/links.php';
$url = 'https://raw.githubusercontent.com/jeanmarc77/meterN/main/other/latest_version.json';

if (isset($_SERVER["PHP_AUTH_USER"])) {
	$me = $_SERVER["PHP_AUTH_USER"];
} else {
	$me = 'unknown';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>meterN Administration</title>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<link rel="stylesheet" href="../styles/default/css/style.css" type="text/css">
<?php
echo "<script src='$JSjquery'></script>";
?>
<script type='text/javascript'>
$(document).ready(function()
{
var vers='<?php
echo $VERSION;
?>';

$.ajax({
    url : '<?php
echo $url;
?>',
    dataType: 'json',
    type: 'GET',
    success: function(response){
	json =eval(response);
	lastvers =json['LASTVERSION'];

	if (vers!=lastvers) {
	document.getElementById('status').src = '../images/24/sign-warning.png';
	document.getElementById('msg').innerHTML = '<a href=\'update.php\'>Update</a>';
	document.getElementById('msgico').innerHTML = '<img src=\'../styles/default/images/sqe.gif\'>';
	} else {
	document.getElementById('status').src = '../images/24/sign-check.png';
	document.getElementById('msg').innerHTML = '';
	}
    },
    error: function(){
	document.getElementById('status').src = '../images/24/sign-question.png';
    document.getElementById('msg').innerHTML = '';
    },
    timeout: 3000
});

})
</script>
</head>
<body>
<table width="95%" height="80%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr bgcolor="#FFFFFF" height="64">
  <td class="cadretopleft" width="128">&nbsp;<img src="../images/house48.png" width="48" height="48" alt="meterN"></td>
  <td class="cadretop" align="center"><b>meterN Administration</font></td>
  <td class="cadretopright" width="128" align="right"></td>
  </tr>
  <tr bgcolor="#CCCC66">
<td COLSPAN="3" class="cadre" height="10">
&nbsp;
</td></tr>
<tr valign="top">
    <td COLSPAN="3" class="cadrebot" bgcolor="#d3dae2">
<!-- #BeginEditable "mainbox" -->
<br>
<div align=center><b>Welcome <?php
echo $me;
?></b></div>
<hr>
<br>
<div align=center><span id='messageSpan'></span></div>
<?php

$err_cfg = false;
$err_txt = '';
if ($cfgver < $CFGmain) {
	$err_txt .= " config_main";
	$err_cfg = true;
}
for ($i = 1; $i <= $NUMMETER; $i++) {
	$cfgver = 0;
	include "../config/config_met" . $i . ".php";
	if ($cfgver < $CFGmet) {
		$err_txt .= " config_met$i";
		$err_cfg = true;
	}
}
$cfgver     = 0;
include '../config/config_layout.php';
if ($cfgver < $CFGlay) {
	$err_txt .= " config_layout";
	$err_cfg = true;
}
$cfgver     = 0;
include '../config/config_indicator.php';
if ($cfgver < $CFGlay) {
	$err_txt .= " config_indicator";
	$err_cfg = true;
}

date_default_timezone_set($DTZ);

if (!empty($_GET['startstop'])) {
	$startstop = $_GET['startstop'];
} else {
	$startstop = null;
}
$PIDd = 'stop';
if (file_exists('../scripts/metern.pid')) {
	$PIDd = date("$DATEFORMAT H:i:s", filemtime('../scripts/metern.pid'));
	$PID = (int) file_get_contents('../scripts/metern.pid');
	exec("ps -ef | grep $PID | grep metern.php", $ret);
	if (!isset($ret[1])) {
		$PID = null;
		unlink('../scripts/metern.pid');
	}
} else {
	$PID = null;
}

if ($startstop == 'start' || $startstop == 'stop') {
	$now = date($DATEFORMAT . ' H:i:s');
	$errfile     = '../data/metern.err';
	if ($startstop == 'start' && is_null($PID)) {
		if ($DEBUG) {
			$command    = 'php ../scripts/metern.php' . ' >> ../data/metern.err 2>&1 & echo $!; ';
			$PID        = exec($command);
			$stringData = "$now\tStarting meterN debug ($PID)\n\n";
			if (!file_put_contents('../scripts/metern.pid', $PID)) {
				$stringData .= "\n\nCan't write scripts/metern.pid, you might need to restart php\n\n";
				exec('pkill -f metern.php');
			}
			file_put_contents($errfile, $stringData, FILE_APPEND);
		} else {
			$command    = 'php ../scripts/metern.php' . ' > /dev/null 2>&1 & echo $!;';
			$PID        = exec($command);
			$stringData = "$now\tStarting meterN ($PID)\n\n";
			file_put_contents('../scripts/metern.pid', $PID);
		}
		$stringData .= file_get_contents('../data/events.txt');
		file_put_contents('../data/events.txt', $stringData);
	}
	if ($startstop == 'stop') {
		if (!is_null($PID)) {
			$stringData = "$now\tStopping meterN ($PID)\n\n";
			$command = exec("kill $PID > /dev/null 2>&1 &");
			unlink('../scripts/metern.pid');
			if ($DEBUG) {
				$stringData = "$now\tStopping meterN debug ($PID)\n\n";
				file_put_contents($errfile, $stringData, FILE_APPEND);
			}
			$stringData .= file_get_contents('../data/events.txt');
			file_put_contents('../data/events.txt', $stringData);
		}
		$PID = null;
		unlink($LIVEMEMORY);
		unlink($ILIVEMEMORY);
	}
	include '../config/config_daemon.php'; // Daemon

	echo "
<script type='text/javascript'>
  document.getElementById('messageSpan').innerHTML = \"...Please wait...<br><img src=\'../images/loading.gif\'>\";
  setTimeout(function () {
    window.location.href = 'admin.php?startstop=done';
  }, 1000);
</script>
";
}
echo "
<table border=0 align='center' width='80%'>
<tr><td align='left'>";

if ($startstop != 'start' && $startstop != 'stop') {
	echo "<form action='admin.php' method='GET'>";
	if (is_null($PID)) {
		echo "<input type='image' src='../images/off.png' value='' width=121 height=57>
		<input type='hidden' name='startstop' value='start'>";
	} else {
		echo "<input type='image' src='../images/on.png' value='' title='mN run as pid $PID since $PIDd' width=121 height=57 onclick=\"if(!confirm('Stop meterN ?')){return false;}\">
		<input type='hidden' name='startstop' value='stop'>";
	}
	if ($err_cfg) {
		echo "<br><img src='../images/24/sign-error.png' width='24' height='24' border='0'> Your config need to be updated ! Please, check and save : $err_txt";
	}
	echo "
	</form>
<br>
<table border=0 align='center' width='100%'>
<tr><td width=13></td><td align='left'><b>Configuration</b></td></tr>
<tr><td width=13><img src='../styles/default/images/sqe.gif'></td><td align='left'><a href='admin_main.php'>Main</a></td></tr>
<tr><td width=13><img src='../styles/default/images/sqe.gif'></td><td align='left'><a href='admin_meter.php'>Meter(s) & sensor(s)</a></td></tr>
<tr><td width=13><img src='../styles/default/images/sqe.gif'></td><td align='left'><a href='admin_indicator.php'>Indicator(s)</a></td></tr>
<tr><td align='left'><img src='../styles/default/images/sqe.gif'></td><td align='left'><a href='admin_layout.php'>Layout</a></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td width=13></td><td align='left'><b>Correction</b></td></tr>
<tr><td width=13><img src='../styles/default/images/sqe.gif'></td><td align='left'><a href='correction.php'>Change daily value(s)</a></td></tr>
<tr><td width=13><img src='../styles/default/images/sqe.gif'></td><td align='left'><a href='redefine.php'>Redefine index(es)</a></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td width=13></td><td align='left'><b>Misc.</b></td></tr>
<tr><td width=13><img src='../styles/default/images/sqe.gif'></td><td align='left'><a href='help.php'>Help and debugger</a></td></tr>
<tr><td width=13><span id='msgico'><span></td><td><span id='msg'><span></td></tr>
</table>

</tr></td>
</table>
<form><div align=center>
<INPUT TYPE='button' onClick=\"location.href='../'\" value='Back'>
</div>
</form>
<hr>
<table border=0 cellspacing=0 cellpadding=0 width='100%' align=center>
<tr valign=top><td></td>
<td width='33%'>
<div align=center><a href='kiva.html'>meterN is free !</a></div>
</td>
<td width='33%' align=right><a href='update.php'><img src='../images/24/sign-sync.png' id='status' width=24 height=24> $VERSION</a></td>
</tr>
</table>";
}
?>
          <!-- #EndEditable -->
          </td>
          </tr>
</table>
</body>
</html>
