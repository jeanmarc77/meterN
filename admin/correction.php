<?php
/**
 * /srv/http/metern/admin/correction.php
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
    function recalculateSum()
    {
	 var x = document.getElementById("form2");
	 var text = "";
	 var i;
	 var d =0;
	 var l =0;
	 var nsum =0;
	 var sum = parseFloat(document.getElementById("sum").value);
	 var preci = parseInt(document.getElementById("preci").value);
	  for (i = 0; i < x.length ;i++) {
    	if(x.elements[i].id.indexOf('new')!=-1) {
		var val = parseFloat(x.elements[i].value.toLocaleString('en-US'));
		d += val;
		nsum += val;
    	}
	  }
	  d -= sum;
	  if (d===0) {
		document.getElementById("bntsubmit").disabled = true;
	  } else {
		document.getElementById("bntsubmit").disabled = false;
	  }
	  d = d.toFixed(preci);
	  nsum = nsum.toFixed(preci);
	  document.getElementById("diff").innerHTML = d;
	  document.getElementById("nsum").innerHTML = nsum;
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
include '../languages/English.php';

if (!empty($_POST['met_num'])) {
	$metnum = $_POST['met_num'];
} else {
	$metnum = 1;
}
if (!empty($_POST['whichmonth']) && is_numeric($_POST['whichmonth'])) {
	$whichmonth = $_POST['whichmonth'];
} else {
	$whichmonth = date("n");
}
if (!empty($_POST['whichyear']) && is_numeric($_POST['whichyear'])) {
	$whichyear = $_POST['whichyear'];
} else {
	$whichyear = date("Y");
}

$totmeteryearlist = array();
for ($i = 1; $i <= $NUMMETER; $i++) { // Getting all years files
	$meteryearlist = array();
	include "../config/config_met$i.php";
	if (${'TYPE' . $i} != 'Sensor') {
		$meteryearlist = glob('../data/meters/' . $i . ${'METNAME' . $i} . '*.csv'); // 1Elect*
		$yearscnt      = count($meteryearlist);
		for ($j = 0; $j < $yearscnt; $j++) {
			$year = substr($meteryearlist[$j], -8, 4);
			$year = (int) $year;
			if (!in_array($year, $totmeteryearlist)) {
				$totmeteryearlist[] = $year;
			}
		}
	}
}
rsort($totmeteryearlist);
if (!isset($totmeteryearlist[0])) {
	$totmeteryearlist[0] = $whichyear;
}
$xyears = count($totmeteryearlist);
echo "
<div align=center>
<br>
<form method='POST' action='correction.php'>
<table width='90%' border=0 cellspacing=0 cellpadding=0 align=center>
<tr align='center'><td>
<b>Correct daily value(s) : </b>
<select name='met_num' onchange='this.form.submit()'>";
for ($i = 1; $i <= $NUMMETER; $i++) {
	if (${'TYPE' . $i} != 'Sensor') {
		if ($metnum == $i) {
			echo "<option value='$i' SELECTED>";
		} else {
			echo "<option value='$i'>";
		}
		echo "${'METNAME'.$i}</option>";
	}
}
echo "</select>
&nbsp;
<select name='whichmonth' onchange='this.form.submit()'>";
for ($i = 1; $i <= 12; $i++) {
	if ($whichmonth == $i) {
		echo "<option SELECTED value='$i'>";
	} else {
		echo "<option value='$i'>";
	}
	echo "$lgSMONTH[$i]</option>";
}
echo "
</select> /
<select name='whichyear' onchange='this.form.submit()'>";
for ($i = 0; $i < $xyears; $i++) {
	if ($whichyear == $totmeteryearlist[$i]) {
		echo "<option SELECTED>";
	} else {
		echo "<option>";
	}
	echo "$totmeteryearlist[$i]</option>";
}
echo "</select>
</td></tr>
</table>
</form>
<br><br>
<fieldset style='width:80%;'>
<legend><b>Meter#$metnum ${'METNAME'.$metnum} $lgSMONTH[$whichmonth] $whichyear</b></legend>
<br>";
$thisfile = '../data/meters/' . $metnum . ${'METNAME' . $metnum} . $whichyear . '.csv';
if (file_exists($thisfile)) {
	if ($handle = fopen("$thisfile", 'r')) {
		while ($line = fgetcsv($handle, 1000, ',')) {
			$year  = (int) substr($line[0], 0, 4);
			$month = (int) substr($line[0], 4, 2);
			$month = trim($month);
			$day   = (int) substr($line[0], 6, 2);
			if ($month == $whichmonth && isset($line[1])) {
				$val_day[$day] = (float) $line[1];
			}
		}
	}
}
echo "
<form method='POST' action='correction2.php' id='form2'>
<table width='40%' border=1 cellspacing=0 cellpadding=0 align=center>
	<tr align='center'><td><b>Date</b></td><td><b>Previous</b></td><td><b>New</b></td></tr>";
$sum      = 0;
$step     = 1 / pow(10, ${'PRECI' . $metnum}); // a 0.01 precision with 2
$daythatm = cal_days_in_month(CAL_GREGORIAN, $whichmonth, $whichyear);
$today    = strtotime(date('Ymd'));
for ($j = 1; $j <= $daythatm; $j++) {
	if (isset($val_day[$j])) {
		$sum += $val_day[$j];
	} else {
		$val_day[$j] = 0;
	}
	$stamp = strtotime(date($whichyear . '/' . $whichmonth . '/' . $j));
	$dday  = date($DATEFORMAT, $stamp);
	echo "<tr align='center'><td>$dday</td><td>$val_day[$j]</td><td><input type='number' min=0 value='$val_day[$j]' id='new$j' name='new$j' style='width:100px' step=$step onchange=\"recalculateSum();\"/ ";
	if ($stamp >= $today) {
		echo 'disabled';
	}
	echo '></td></tr>';
}
echo "<tr valign='top' align='center'><td></td><td><b>&Sigma; $sum ${'UNIT' . $metnum}</b></td><td><b>&Sigma; <span id='nsum'>$sum</span> ${'UNIT' . $metnum}</b><br>(&#x394; <span id='diff'>0</span>${'UNIT' . $metnum})</td></tr>
</table>
<input id='sum' type='hidden' value=$sum>
<input id='preci' type='hidden' value=${'PRECI' . $metnum}>
<br>
</fieldset>
<br><br>
<INPUT TYPE='button' onClick=\"location.href='admin.php'\" value='Cancel'> <input type='submit' id='bntsubmit' name='bntsubmit' onclick=\"return confirm('Are you sure ?')\" value='Continue' disabled>
<input type='hidden' name='met_num' value='$metnum'>
<input type='hidden' name='whichmonth' value='$whichmonth'>
<input type='hidden' name='whichyear' value='$whichyear'>
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
