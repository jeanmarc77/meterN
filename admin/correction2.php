<?php
/**
 * /srv/http/metern/admin/correction2.php
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
if (!empty($_POST['met_num']) && is_numeric($_POST['met_num'])) {
	$metnum = $_POST['met_num'];
} else {
	die('Error');
}
if (!empty($_POST['whichmonth']) && is_numeric($_POST['whichmonth'])) {
	$whichmonth = $_POST['whichmonth'];
} else {
	die('Error');
}
if (!empty($_POST['whichyear']) && is_numeric($_POST['whichyear'])) {
	$whichyear = $_POST['whichyear'];
} else {
	die('Error');
}

$daythatm = cal_days_in_month(CAL_GREGORIAN, $whichmonth, $whichyear);
for ($j = 1; $j <= $daythatm; $j++) {
	if (isset($_POST["new$j"]) && is_numeric($_POST["new$j"])) {
		$val_day[$whichmonth][$j] = (float) $_POST["new$j"];
	} else {
		$val_day[$whichmonth][$j] = 0;
	}
}

if (!file_exists("../data/old/")) {
	if (!mkdir("../data/old/", 0777, true)) {
		die('Error mkdir');
	}
}
include "../config/config_met" . $metnum . ".php";
$str      = '';
$thisfile = '../data/meters/' . $metnum . ${'METNAME' . $metnum} . $whichyear . '.csv';
if (file_exists($thisfile)) {
	if ($handle = fopen("$thisfile", 'r')) {
		while ($line = fgetcsv($handle, 1000, ',')) {
			$month = (int) substr($line[0], 4, 2);
			$day   = (int) substr($line[0], 6, 2);
			if (!isset($val_day[$month][$day])) {
				$val_day[$month][$day] = (float) $line[1];
			}
		}
	}
	// Backup
	$d   = date('YmdHis');
	$tdb = $metnum . ${'METNAME' . $metnum} . $whichyear;
	copy($thisfile, "../data/old/c_$tdb-$d.csv");

	$str = "<b>Rewrited $whichyear data for #$metnum ${'METNAME' . $metnum}</b><br><br>c_$tdb-$d.csv<br>has been made as backup in /data/old/<br>";
} else {
	$str = "<b>Creating $whichyear data for #$metnum ${'METNAME' . $metnum}</b><br>";
}
// Rewriting
$txt   = '';
$today = strtotime(date('Ymd'));
for ($k = 1; $k <= 12; $k++) {
	$daythatm = cal_days_in_month(CAL_GREGORIAN, $k, $whichyear);
	for ($j = 1; $j <= $daythatm; $j++) {
		$stamp = strtotime(date($whichyear . '/' . $k . '/' . $j));
		if ($stamp < $today) {
			$stamp = strtotime(date($whichyear . '/' . $k . '/' . $j));
			$dday  = date('Ymd', $stamp);
			if (!isset($val_day[$k][$j])) {
				$val = 0;
			} else {
				$val = round($val_day[$k][$j], ${'PRECI' . $metnum});
			}
			$txt .= "$dday,$val\r\n";
		}
	}
} // k selected month
if (file_exists($thisfile)) {
	$fh = fopen($thisfile, 'w+') or die();
} else {
	$fh = fopen($thisfile, 'x+');
}
fwrite($fh, $txt);
fclose($fh);

echo "<br><div align=center>$str<br><br>
<INPUT TYPE='button' onClick=\"location.href='admin.php'\" value='Back'>
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
