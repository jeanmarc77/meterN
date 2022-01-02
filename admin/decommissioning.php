<?php
/**
 * /srv/http/metern/admin/decommissioning.php
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
if (!empty($_POST['met_numx']) && is_numeric($_POST['met_numx'])) {
	$met_num = $_POST['met_numx'];
}
if (file_exists("../config/config_met" . $met_num . ".php")) {
	include "../config/config_met" . $met_num . ".php";
}
if (!empty($_POST['bntsubmit']) && is_string($_POST['bntsubmit'])) {
	$bntsubmit = htmlspecialchars($_POST['bntsubmit'], ENT_QUOTES, 'UTF-8');
} else {
	$bntsubmit = null;
}
if ($bntsubmit != 'Decommissioning' || !isset($met_num)) {
	die('Error');
}
echo "
<br>
<div align=center>
<form method='POST' action='decommissioning2.php'>
<img src='../images/24/sign-warning.png' width='24' height='24' border='0'><b> Beware : </b>You are about to decommission a meter/sensor: it allow to reassign a meter to another one.<br>
The detailled data of the last 2 days shall be removed for <b>#$met_num ${'METNAME'.$met_num}<b>.<br>
</div>
<br><br>
<div align=center><INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_num'\" value='Cancel'> <input type='submit' name='bntsubmit' value='Continue'></div>
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
