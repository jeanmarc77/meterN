<?php
/**
 * /srv/http/metern/admin/decommissioning2.php
 *
 * @package default
 */


include 'secure.php';
include '../scripts/version.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" >
<title>meterN Administration</title>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<link rel="stylesheet" href="../styles/default/css/style.css" type="text/css">
</head>
<body>
<table width="95%" height="80%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr bgcolor="#FFFFFF" height="64">
  <td class="cadretopleft" width="128">&nbsp;<img src="../images/house48.png" width="48" height="48" alt="meterN"></td>
  <td class="cadretop" align="center"><b>meterN Administration</font></td>
  <td class="cadretopright" width="128" align="right"></td>
  </tr>
  <tr bgcolor="#CCCC66">
<td align=right COLSPAN="3" class="cadre" height="10">
&nbsp;
</td></tr>
<tr valign="top">
    <td COLSPAN="3" class="cadrebot" bgcolor="#d3dae2">
<!-- #BeginEditable "mainbox" -->
<?php
date_default_timezone_set($DTZ);

if (!empty($_POST['met_numx']) && is_numeric($_POST['met_numx'])) {
	$met_num = $_POST['met_numx'];
} else {
	die('Error');
}
if (file_exists("../config/config_met" . $met_num . ".php")) {
	include "../config/config_met" . $met_num . ".php";
}
if (!empty($_POST['bntsubmit']) && is_string($_POST['bntsubmit'])) {
	$bntsubmit = htmlspecialchars($_POST['bntsubmit'], ENT_QUOTES, 'UTF-8');
} else {
	$bntsubmit = null;
}

if ($bntsubmit != 'Continue' || !isset($met_num)) {
	die('Error');
}
if (!file_exists("../data/old/")) {
	if (!mkdir("../data/old/", 0777, true)) {
		die('Error mkdir');
	}
}
$str = '';

if (file_exists("../scripts/metern.pid")) {
	$pid     = (int) file_get_contents("../scripts/metern.pid");
	$command = exec("kill -9 $pid > /dev/null 2>&1 &");
	unlink("../scripts/metern.pid");
	usleep(500000);
}

// Yesterday
date_default_timezone_set($DTZ);
$yesterday = date('Ymd', time() - 60 * 60 * 24);
$dir       = '../data/csv/';
$d         = date('YmdHis');
if (file_exists($dir . "$yesterday.csv")) {
	copy($dir . "$yesterday.csv", "../data/old/d_$yesterday-$met_num-$d.csv");
	unlink($dir . "$yesterday.csv");
	$file       = file("../data/old/d_$yesterday-$met_num-$d.csv");
	$contalines = count($file);

	for ($line_num = 0; $line_num < $contalines; $line_num++) {
		$array       = preg_split("/,/", $file[$line_num]);
		$stringData5 = "$array[0]";
		for ($i = 1; $i <= $NUMMETER; $i++) {
			if (isset($array[$i])) {
				$val = trim($array[$i]);
			} else {
				$val = '';
			}
			if ($i != $met_num) {
				$stringData5 .= ",$val";
			} else {
				if ($i != $NUMMETER) {
					$stringData5 .= ",";
				}
			}
		}
		$stringData5 .= "\r\n";
		file_put_contents($dir . "$yesterday.csv", $stringData5, FILE_APPEND);
	}
	$str = "d_$yesterday-$met_num-$d.csv<br>";
}

// Today
$today = date('Ymd');
if (file_exists($dir . "$today.csv")) {
	copy($dir . "$today.csv", "../data/old/d_$today-$met_num-$d.csv");
	unlink($dir . "$today.csv");
	$file       = file("../data/old/d_$today-$met_num-$d.csv");
	$contalines = count($file);

	for ($line_num = 0; $line_num < $contalines; $line_num++) {
		$array       = preg_split("/,/", $file[$line_num]);
		$stringData5 = "$array[0]";
		for ($i = 1; $i <= $NUMMETER; $i++) {
			if (isset($array[$i])) {
				$val = trim($array[$i]);
			} else {
				$val = '';
			}
			if ($i != $met_num) {
				$stringData5 .= ",$val";
			} else {
				if ($i != $NUMMETER) {
					$stringData5 .= ",";
				}
			}
		}
		$stringData5 .= "\r\n";
		file_put_contents($dir . "$today.csv", $stringData5, FILE_APPEND);
	}
	$str .= " d_$today-$met_num-$d.csv<br>";
}

$myFile = '../config/config_met' . $met_num . '.php';
$fh = fopen($myFile, 'w+') or die("<font color='#8B0000'><b>Can't open $myFile file. Configuration not saved !</b></font>");
$stringData = "<?php
if(!defined('checkaccess')){die('Direct access not permitted');}

// ### CONFIG FOR METER #$met_num

\$METNAME$met_num=\"New $met_num\";
\$TYPE$met_num='Other';
\$PROD$met_num=0;
\$PHASE$met_num=1;
\$SKIPMONITORING$met_num='false';
\$ID$met_num='';
\$COMMAND$met_num='';
\$UNIT$met_num='';
\$PRECI$met_num=0;
\$PASSO$met_num=0;
\$COLOR$met_num='A5A5A5';
\$PRICE$met_num=0;
\$LID$met_num='';
\$LIVECOMMAND$met_num='';
\$LIVEUNIT$met_num='';
\$EMAIL$met_num='';
\$POAKEY$met_num='';
\$POUKEY$met_num='';
\$RPITOK$met_num='';
\$WARNCONSOD$met_num=0;
\$NORESPM$met_num=false;

\$cfgver=$CFGmet;
?>
";
fwrite($fh, $stringData);
fclose($fh);
echo '<div align=center>';
if (file_exists($dir . "$yesterday.csv") || file_exists($dir . "$today.csv")) {
	echo "
	<br><img src='../images/24/sign-warning.png' width='24' height='24' border='0'> Data have being deleted for <b>#$met_num ${'METNAME'.$met_num}</b>.
	<br><br>$str<br>has been made as backup in /data/old/<br>";
}
echo "
<br>Please restart meterN
<br><br>
<INPUT TYPE='button' onClick=\"location.href='admin.php'\" value='Back'>
<input type='hidden' name='met_num' value='$met_num'>
</div>";
?>
<br>
<br>
<!-- #EndEditable -->
          </td>
          </tr>
</table>
</body>
</html>
