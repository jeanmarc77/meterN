<?php
/**
 * /srv/http/metern/admin/admin_layout.php
 *
 * @package default
 */


include 'secure.php';
include '../config/config_layout.php';
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
$housecons = false;
$houseprod = false;

for ($i = 1; $i <= $NUMMETER; $i++) {
	include "../config/config_met" . $i . ".php";
	if (!isset(${'GRAPH_MET' . $i})) {
		${'GRAPH_MET' . $i} = 0;
	}
	if (!isset(${'FILL_MET' . $i})) {
		${'FILL_MET' . $i} = false;
	}
	if (!isset(${'LASTD_MET' . $i})) {
		${'LASTD_MET' . $i} = false;
	}
	if (${'PROD' . $i} == 1) {
		$houseprod = true;
	} elseif (${'PROD' . $i} == 2) {
		$housecons = true;
	}
}

echo "
<br>
<div align=center><form action='admin_layout2.php' method='post'>
<fieldset style='width:80%;'>
<legend><b>Index page layout configuration</b></legend>
<br>
<table border='0' cellspacing='5' cellpadding='0' width='80%' align='center'>
<tr>
<td><b>Graphic(s) height</b> <input type='number' name='GRAPHH' min='100' max='800' value='$GRAPHH' style='width:60px'> px</td>
<td><b>Gauge power</b> min <input type='number' max='0' name='POWER_MIN' style='width:60px' title='You must enter a negative value if you own a production and consumption meters'";
if (!$housecons || !$houseprod) {
	echo " disabled value=0>";
} else {
	echo "value='$POWER_MIN'>";
}
echo "max <input type='number' name='POWER_MAX' value='$POWER_MAX' style='width:60px' ";
if (!$housecons && !$houseprod) {
	echo " disabled";
}
echo "> W
</tr></td>
</table>
<br>
<hr>
<table border='0' cellspacing='5' cellpadding='0' width='80%' align='center'>
<tr><td><b>Meter | Sensor</b></td><td><b>Show in graphic number</b></td><td><b>Don't fill the serie</b></td><td><b>Show in last 15 days</b></td></tr>
";

for ($i = 1; $i <= $NUMMETER; $i++) {
	echo "<tr><td>#$i ${'METNAME'.$i}</td><td><input type='number' name='GRAPH_METx$i' value='${'GRAPH_MET'.$i}' min='0' max='8' style='width:40px' title='Set to 0 to hide'></td>
<td><input type='checkbox' name='FILL_METx$i' value='true'";
	if (${'FILL_MET' . $i}) {
		echo " checked";
	}
	echo "></td>
<td><input type='checkbox' name='LASTD_METx$i' value='true'";
	if (${'TYPE' . $i} == 'Sensor') {
		${'LASTD_MET' . $i} = 0;
		echo " disabled";
	}
	if (${'LASTD_MET' . $i}) {
		echo "checked";
	}
	echo "></td>
</tr>
";
}
echo "
</table>
</fieldset>
<br>
<div align=center><INPUT TYPE='button' onClick=\"location.href='admin.php'\" value='Back'> <input type='submit' value='Save layout'></div>
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
