<?php
/**
 * /srv/http/metern/admin/redefine.php
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
<script>
    function checkx()
    {
		var test = false;
		<?php
for ($i = 1; $i <= $NUMMETER; $i++) {
	echo "
		var val$i = parseFloat(document.getElementById('val$i').value);
		var new$i = parseFloat(document.getElementById('new$i').value);
		if(val$i!=new$i) {
			test = true;
		}";
}
?>

		if (!test) {
		document.getElementById("bntsubmit").disabled = true;
		} else {
		document.getElementById("bntsubmit").disabled = false;
		}
	}
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
<td align=right COLSPAN="3" class="cadre" height="10">
&nbsp;
</td></tr>
<tr valign="top">
    <td COLSPAN="3" class="cadrebot" bgcolor="#d3dae2">
<!-- #BeginEditable "mainbox" -->
<?php
$dir    = '../data/csv/';
$output = array();
$output = glob($dir . '*.csv');
sort($output);
$cnt = count($output);
$dd = '';
if ($cnt > 0) {
	$year      = substr($output[$cnt - 1], -12, 4);
	$month     = substr($output[$cnt - 1], -8, 2);
	$day       = substr($output[$cnt - 1], -6, 2);
	$file      = file($output[$cnt - 1]);
	$last      = sizeof($file) - 1;
	$linearray = preg_split("/,/", $file[$last]);

	$hour    = substr($linearray[0], 0, 2);
	$minute  = substr($linearray[0], 3, 2);
	$UTCdate = strtotime($year . '-' . $month . '-' . $day);
	$dday    = date($DATEFORMAT, $UTCdate);
	$dday .= " $hour:$minute";
}

echo "
<br>
<div align=center>
<b>You are about to redefine meter(s) index(es), this allow to allign meter(s) that drift.</b>
<br><br>The last records where on $dday<br><br>
<form method='POST' action='redefine2.php' id='form2'>
<table width='40%' border=1 cellspacing=0 cellpadding=0 align=center>
<tr><td><b>Meter(s)</b></td><td><b>Last index(s)</b></td><td><b>New value(s)</b></td></tr>
";

for ($i = 1; $i <= $NUMMETER; $i++) {
	if (file_exists("../config/config_met" . $i . ".php")) {
		include "../config/config_met" . $i . ".php";
	} else {
		die('Error missing cfg');
	}
	if (${'TYPE' . $i} != 'Sensor') {
		$linearray[$i] = (float) trim($linearray[$i]);
		if (isset($linearray[$i])) {
			$val = number_format($linearray[$i], ${'PRECI' . $i}, $DPOINT, $THSEP);
		} else {
			$val           = 0;
			$linearray[$i] = 0;
		}
		$step = 1 / pow(10, ${'PRECI' . $i}); // a 0.01 precision with 2
		echo "<tr>
	<td>#$i (${'METNAME'.$i})</td><td>$val ${'UNIT' . $i}<input id='val$i' type='hidden' value='$linearray[$i]'></td>
	<td><input type='number' value='$linearray[$i]' id='new$i' name='new$i' style='width:100px' min=0 step=$step onchange=\"checkx();\"/> ${'UNIT' . $i}</td>
	</tr>";
	}
}
echo "
</table>
<br><img src='../images/24/sign-warning.png' width='24' height='24' border='0'><b> Beware : </b> Only realign real(s) meter(s) as it might interfere virtual(s) meter(s) that use previous saved values.
<br><br>
<INPUT TYPE='button' onClick=\"location.href='admin.php'\" value='Cancel'> <input type='submit' id='bntsubmit' name='bntsubmit' onclick=\"return confirm('Are you sure ?')\" value='Continue' disabled>
</form></div>";
?>
<br>
<br>
<!-- #EndEditable -->
          </td>
          </tr>
</table>
</body>
</html>
