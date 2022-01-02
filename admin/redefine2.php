<?php
/**
 * /srv/http/metern/admin/redefine2.php
 *
 * @package default
 */


include 'secure.php'?>
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

if (!empty($_POST['bntsubmit']) && is_string($_POST['bntsubmit'])) {
	$bntsubmit = htmlspecialchars($_POST['bntsubmit'], ENT_QUOTES, 'UTF-8');
} else {
	$bntsubmit = null;
}
if ($bntsubmit != 'Continue') {
	die('Error');
}

if (!file_exists("../data/old/")) {
	if (!mkdir("../data/old/", 0777, true)) {
		die('Error mkdir');
	}
}
$dir    = '../data/csv/';
$output = array();
$output = glob($dir . '*.csv');
sort($output);
$cnt = count($output);

for ($i = 1; $i <= $NUMMETER; $i++) {
	if (!empty($_POST["new$i"])) {
		$new[$i] = (float) htmlspecialchars($_POST["new$i"], ENT_QUOTES, 'UTF-8');
	} else {
		$new[$i] = null;
	}
	if (file_exists("../config/config_met" . $i . ".php")) {
		include "../config/config_met" . $i . ".php";
	}
}
$str = '';
echo '<br><div align=center>';

if ($cnt > 0) {
	if (file_exists("../scripts/metern.pid")) {
		$pid     = (int) file_get_contents("../scripts/metern.pid");
		$command = exec("kill -9 $pid > /dev/null 2>&1 &");
		unlink("../scripts/metern.pid");
		usleep(500000);
	}

	for ($cntcsv = 1; $cntcsv <= 2; $cntcsv++) { // correct today and yesterday
		$td         = $output[$cnt - $cntcsv];
		$file       = file($td);
		$contalines = count($file);
		if ($cntcsv == 1) {
			$lastlinearray = preg_split("/,/", $file[$contalines - 1]);
			$diff[0]       = 0;
			for ($i = 1; $i <= $NUMMETER; $i++) {
				if (${'TYPE' . $i} != 'Sensor' && $lastlinearray[$i] != $new[$i]) {
					$diff[$i] = $new[$i] - (float) $lastlinearray[$i];
					$diff[$i] = round($diff[$i], ${'PRECI' . $i});
				} else {
					$diff[$i] = 0;
				}
			}
		}
		// Backup
		$d   = date('YmdHis');
		$tdb = substr($td, -12, 8);
		copy($td, "../data/old/r_$tdb-$d.csv");
		$str .= "r_$tdb-$d.csv<br>";
		// Rewriting
		$myfile = fopen($td, "w") or die('Unable to open file!');
		$txt = '';
		for ($i = 0; $i < $contalines; $i++) {
			for ($j = 0; $j <= $NUMMETER; $j++) {
				$currentlinearray = preg_split("/,/", $file[$i]);
				if ($diff[$j] == 0 || $i == 0 || $j == 0) { // no diff, header, stamp
					$txt .= trim($currentlinearray[$j]);
				} else {
					$val = (float) $currentlinearray[$j] + $diff[$j];
					$val = round($val, ${'PRECI' . $j});
					if ($val <= 0) {
						$val = 0;
					}
					$txt .= $val;
				}
				if ($j != $NUMMETER) {
					$txt .= ',';
				} else {
					$txt .= "\r\n";
				}
			}
		}
		fwrite($myfile, $txt);
		fclose($myfile);
	}
	echo "<br>$str<br>has been made as backup in /data/old/<br>";
	for ($i = 1; $i <= $NUMMETER; $i++) {
		if ($diff[$i] != 0) {
			$val = number_format($diff[$i], ${'PRECI' . $i}, $DPOINT, $THSEP);
			echo "<br>${'METNAME'.$i} index is set to $new[$i] ${'UNIT' . $i} (difference $val ${'UNIT' . $i})";
		}
	}
} else {
	echo 'No daily csv';
}

echo "<br><br>Please restart meterN<br>
<br><INPUT TYPE='button' onClick=\"location.href='admin.php'\" value='Continue'></div>";
?>
<br>
<br>
<!-- #EndEditable -->
          </td>
          </tr>
</table>
</body>
</html>
