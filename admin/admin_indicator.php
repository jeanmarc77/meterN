<?php
/**
 * /srv/http/metern/admin/admin_indicator.php
 *
 * @package default
 */


include 'secure.php';
include '../config/config_indicator.php';
include '../config/allowed_comapps.php';
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
echo "<br>
<table border='0' cellspacing='5' cellpadding='0' width='80%' align='center'><tr><td>
<form method='POST' action='admin_indicator2.php' method='post'>
<div align=left>
Number of indicator(s)* <input type='number' name='NUMINDx' value='$NUMIND' min='0' max='64' style='width:40px' onchange='this.form.submit()'> <font size='-1'>(*Not logged)</font>
</div>
</td></tr></table>
<div align=center>";
for ($ind_num = 1; $ind_num <= $NUMIND; $ind_num++) {
	if (!isset(${'INDNAME'.$ind_num})) {
		${'INDNAME'.$ind_num} = '';
	}
	if (!isset(${'INDID'.$ind_num})) {
		${'INDID'.$ind_num} = '';
	}
	if (!isset(${'INDCOMMAND'.$ind_num})) {
		${'INDCOMMAND'.$ind_num} = '';;
	}
	if (!isset(${'INDUNIT'.$ind_num})) {
		${'INDUNIT'.$ind_num} = '';
	}
	echo "
<fieldset style='width:80%;'>
<legend><b>Indicator#$ind_num ${'INDNAME'.$ind_num}</b></legend>
<table border='0' cellspacing='5' cellpadding='0' width='100%' align='center'>
<tr>
<td>Name <input type='text' name='INDNAMEx$ind_num' value='${'INDNAME'.$ind_num}' size=10></td>
<td>ID <input type='text' name='IDx$ind_num' value='${'INDID'.$ind_num}' size=10></td>
<td>Command <select name='COMMANDx$ind_num'>";
$cnt = count($ALLWDCMD);
$sel  = false;
for ($i=0; $i<$cnt; $i++) {
	echo "<option value='$ALLWDCMD[$i]'";
	if (${'INDCOMMAND'.$ind_num} == $ALLWDCMD[$i]) {
	echo ' SELECTED';
	$sel  = true;
	}
	echo ">$ALLWDCMD[$i]</option>";
}
if (!$sel) {
echo "<option disabled selected value=''>Set allowed com.app. in config/allowed_comapps.php</option>";
}
echo "<option value=''>disable</option></select>
<input type='submit' name='bntsubmit$ind_num' value='Test command' ";
	if (file_exists('../scripts/metern.pid')) {
		echo "onclick=\"if(!confirm('meterN will be stopped for this test, continue ?')){return false;}\"";
	}
	echo ">
</td>
<td>
Unit <input type='text' name='UNITx$ind_num' value='${'INDUNIT'.$ind_num}' size=5>
</td>
</tr>
</table>
</fieldset><br>";
}
echo "<div align=center><INPUT TYPE='button' onClick=\"location.href='admin.php'\" value='Back'> <input type='submit' name='bntsubmit' value='Save config.'></div>
</form>";
?>
<br>
<br>
<!-- #EndEditable -->
          </td>
          </tr>
</table>
<br>
</body>
</html>
