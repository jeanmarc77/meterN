<?php
/**
 * /srv/http/metern/admin/admin_meter.php
 *
 * @package default
 */


include 'secure.php'
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" >
<title>meterN Administration</title>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<link rel="stylesheet" href="../styles/default/css/style.css" type="text/css">
<script type="text/javascript" src="../js/jscolor/jscolor.js"></script>
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
if (!empty($_POST['met_num'])) {
	$met_num = $_POST['met_num'];
} else {
	if (!empty($_GET['met_num'])) {
		$met_num = ($_GET['met_num']);
	} else {
		$met_num = 1;
	}
}

if ($NUMMETER > 1) { //multi
	echo "
<br>
<table border=0 cellspacing=5 cellpadding=0 width='80%' align='center'><tr><td>
<form method='POST' action='admin_meter.php'>
<div align=left><b>Select a meter | sensor : </b><select name='met_num' onchange='this.form.submit()'>";
	for ($i = 1; $i <= $NUMMETER; $i++) {
		if (file_exists("../config/config_met" . $i . ".php")) {
			include "../config/config_met" . $i . ".php";
		} else {
			${'METNAME' . $i}        = "New $i ";
			${'TYPE' . $i}           = 'Other';
			${'PROD' . $i}           = 0;
			${'PHASE' . $i}          = 1;
			${'SKIPMONITORING' . $i} = 'false';
			${'ID' . $i}             = '';
			${'COMMAND' . $i}        = '';
			${'UNIT' . $i}           = '';
			${'PRECI' . $i}          = 0;
			${'PASSO' . $i}          = 0;
			${'COLOR' . $i}          = 'A5A5A5';
			${'PRICE' . $i}          = 0;
			${'LID' . $i}            = '';
			${'LIVECOMMAND' . $i}    = '';
			${'LIVEUNIT' . $i}       = '';
			${'EMAIL' . $i}          = '';
			${'POAKEY' . $i}         = '';
			${'POUKEY' . $i}         = '';
			${'RPITOK' . $i}         = '';
			${'WARNCONSOD' . $i}     = 0;
			${'NORESPM' . $i}        = 'false';
		}
		if ($met_num == $i) {
			echo "<option value='$i' SELECTED>";
		} else {
			echo "<option value='$i'>";
		}
		echo "$i (${'METNAME'.$i})</option>";
	}
	echo "
</select>
</form></td><td align=right>
<form method='POST' action='decommissioning.php'>
<input type='submit' name='bntsubmit' onclick=\"return confirm('Are you sure ?')\" value='Decommissioning'>
<input type='hidden' name='met_numx' value='$met_num'>
</form>
</div>
</td></tr></table>";
} // multi

if (file_exists("../config/config_met" . $met_num . ".php")) {
	include "../config/config_met" . $met_num . ".php";
}
if (${'PROD' . $met_num} == 1) {
	$prodconsu = 'production';
} else if (${'PROD' . $met_num} == 2) {
	$prodconsu = 'consumption';
} else if (${'PROD' . $met_num} == 3) {
	$prodconsu = 'storage';
} else {
	$prodconsu = 'it';
}
echo "
<div align=center><form action='admin_meter2.php' method='post'>
<fieldset style='width:80%;'>
<legend><b>Meter#$met_num ${'METNAME'.$met_num}</b></legend>

<table border=0 cellspacing=5 cellpadding=0 width='100%' align='center'>
<tr><td colspan=4><b>Specs :</b></td></tr>
<tr><td>Short description name <input type='text' name='METNAMEx' value='${'METNAME'.$met_num}' size=10></td>
<td>Color <input type='text' class=\"jscolor\" name='COLORx' value='${'COLOR'.$met_num}' maxlength=6 size=6></td>
<td>Type
<select name='TYPEx' onchange='this.form.submit()'>";
$TYPE_array = array(
	'Elect',
	'Gas',
	'Water',
	'Other',
	'Sensor'
);
$cnt        = count($TYPE_array);
if (!isset(${'TYPE' . $met_num})) {
	${'TYPE' . $met_num} = 'Other';
}
for ($i = 0; $i < $cnt; $i++) {
	if (${'TYPE' . $met_num} == $TYPE_array[$i]) {
		echo "<option SELECTED>";
	} else {
		echo "<option>";
	}
	echo "$TYPE_array[$i]</option>";
}
echo "</select>";
if (${'TYPE' . $met_num} == 'Elect') {
	echo "<select name='PRODx' onchange='this.form.submit()'>";
	if (${'PROD' . $met_num} == 1) {
		echo "<option SELECTED value=1>House production</option><option value=2>House consumption</option><option value=3>House storage charge</option><option value=4>House storage discharge</option><option value=0>Other</option>";
	} elseif (${'PROD' . $met_num} == 2) {
		echo "<option value=1>House production</option><option SELECTED value=2>House consumption</option><option value=3>House storage charge</option><option value=4>House storage discharge</option><option value=0>Other</option>";
	} elseif (${'PROD' . $met_num} == 3) {
		echo "<option value=1>House production</option><option value=2>House consumption</option><option value=3 SELECTED>House storage charge</option><option value=4>House storage discharge</option><option value=0>Other</option>";
	} elseif (${'PROD' . $met_num} == 4) {
		echo "<option value=1>House production</option><option value=2>House consumption</option><option value=3>House storage charge</option><option value=4 SELECTED>House storage discharge</option><option value=0>Other</option>";
	} else {
		echo "<option value=1>House production</option><option value=2>House consumption</option><option value=3>House storage charge</option><option value=4>House storage discharge</option><option SELECTED value=0>Other</option>";
	}
	echo "</select>";

	if (${'PROD' . $met_num} !=0) {
		if (!isset(${'PHASE' . $met_num}) || ${'PHASE' . $met_num} == 0) {
			${'PHASE' . $met_num} = 1;
		}
		echo " phase <input type='number' name='PHASEx' value='${'PHASE'.$met_num}' min=1 max=16 style='width:40px' title='Enter the phase number'>";
	}
} else { // elect
	${'PHASE' . $met_num} = 0;
}
echo "
</td>
<td>Skip monitoring
<select name='SKIPMONITORINGx'>";
if (${'SKIPMONITORING' . $met_num} == 1) {
	echo "<option SELECTED value=true>Yes</option><option value=''>No</option>";
} else {
	echo "<option value=true>Yes</option><option SELECTED value=''>No</option>";
}
echo "
</select></td>
</tr>
</table>
<hr>
<table border=0 cellspacing=5 cellpadding=0 width='100%' align='center'>
<tr><td colspan=6><b>Main 5min pooling :</b></td></tr>
<tr><td>
Meter ID <input type='text' name='IDx' value='${'ID'.$met_num}' required size=10>
<td>Command <input type='text' name='COMMANDx' value='${'COMMAND'.$met_num}' size=25 required title='This command should return a quantity value (e.g.: Watts per hour)'> <input type='submit' name='bntsubmit' value='Test command' ";
if (file_exists('../scripts/metern.pid')) {
	echo "onclick=\"if(!confirm('meterN will be stopped for this test, continue ?')){return false;}\"";
}
echo "></td>
<td>Unit <input type='text' name='UNITx' value='${'UNIT'.$met_num}' size=5";
if (${'TYPE' . $met_num} == 'Elect') {
	echo " disabled";
}
echo "></td>
<td>Precision <input type='number' name='PRECIx' value='${'PRECI'.$met_num}' min=0 max=8 style='width:40px' title='Enter your meter resolution, the number behind the unit (eg: 3 for a 0.001m³ accuracy)'";
if (${'TYPE' . $met_num} == 'Elect') {
	echo " disabled";
}
echo "
>
</td>
<td>Pass over <input type='number' name='PASSOx' value='${'PASSO'.$met_num}' style='width:100px' min=0 title='Up to where your meter can count. Put to 0 for infinity'";
if (${'TYPE' . $met_num} == 'Sensor') {
	echo " disabled";
}
echo ">
</td>
<td>Price per unit <input type='number' step='any' name='PRICEx' value='${'PRICE'.$met_num}' style='width:100px' min=0 title='Null value if not applicable' ";
if (${'TYPE' . $met_num} == 'Sensor') {
	echo " disabled";
}
echo "> $CURS/";
if (${'TYPE' . $met_num} == 'Elect') {
	echo 'k';
}
echo "${'UNIT'.$met_num}</td>
</tr>
</table>
<hr>
<table border=0 cellspacing=5 cellpadding=0 width='100%' align='center'>
<tr><td colspan=3><b>Dashboard live pooling :</b></td></tr>
<tr><td>
Meter ID <input type='text' name='LIDx' value='${'LID'.$met_num}' size=10>
</td>
<td>Live command <input type='text' name='LIVECOMMANDx' value='${'LIVECOMMAND'.$met_num}' size=25 title='Leave empty to disable'> <input type='submit' name='bntsubmit' value='Test live command' ";
if (file_exists('../scripts/metern.pid')) {
	echo "onclick=\"if(!confirm('meterN will be stopped for this test, continue ?')){return false;}\"";
}
echo ">
</td>
<td>
Live unit <input type='text' name='LIVEUNITx' value='${'LIVEUNIT'.$met_num}' size=5";
if (${'TYPE' . $met_num} == 'Elect') {
	echo " disabled";
}
echo ">
</td>
</tr>
</table>
<hr>
<table border=0 cellspacing=5 cellpadding=0 width='100%' align='center'>
<tr><td><b>Monthly $prodconsu report : </b></td></tr>
<tr><td>Email <input type='email' name='EMAILx' value='${'EMAIL'.$met_num}' title='You had to configure a mail client for PHP. Leave empty to disable.'> <input type='submit' name='bntsubmit' value='Test mail'></td>
</tr>
</table>
<hr>
<table border=0 cellspacing=5 cellpadding=0 width='100%' align='center'>
<tr><td colspan=2><b>Checks & Instant notification : </b></td></tr>
<tr>
<td>
Warn if $prodconsu is over <input type='number' name='WARNCONSODx' value='${'WARNCONSOD'.$met_num}' style='width:80px' step='any' min=0 title='Put 0 to disable'> ${'UNIT'.$met_num} during the day
</td>
<td>
Warn connection lost
<select name='NORESPMx' title='Check if the main pooling didnt achieved properly'>";
if (${'NORESPM' . $met_num}) {
	echo "<option SELECTED value='true'>Yes</option><option value=''>No</option>";
} else {
	echo "<option value='true'>Yes</option><option SELECTED value=''>No</option>";
}
echo "
</select>
</td>
</tr>
<tr>
<td>
Pushover <a href='https://pushover.net/' target='_blank'><img src='../images/link.png' width='16' height='16' border=0></a>
API key <input type='text' size=42 name='POAKEYx' value='${'POAKEY'.$met_num}' title='Leave empty to disable.'>
</td><td>
User key <input type='text' size=42 name='POUKEYx' value='${'POUKEY'.$met_num}' title='Leave empty to disable.'> <input type='submit' name='bntsubmit' value='Test Pushover'>
</td>
</tr>
<tr>
<td>
Telegram <a href='https://telegram.me/botfather' target='_blank'><img src='../images/link.png' width='16' height='16' border=0></a> Bot token <input type='text' size=42 name='TLGRTOKx' value='${'TLGRTOK'.$met_num}' title='Leave empty to disable.'> </td>
<td>Chat ID <input type='number' size=42 name='TLGRCIDx' value='${'TLGRCID'.$met_num}' title='Leave empty to disable.'> <input type='submit' name='bntsubmit' value='Test Telegram'></td>
</tr>
</table>
</fieldset>
<br>
<div align=center><INPUT TYPE='button' onClick=\"location.href='admin.php'\" value='Back'> <input type='submit' name='bntsubmit' value='Save config.'></div>
<input type='hidden' name='met_numx' value='$met_num'>
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
