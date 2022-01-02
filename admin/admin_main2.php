<?php
/**
 * /srv/http/metern/admin/admin_main2.php
 *
 * @package default
 */


include 'secure.php';
include '../scripts/version.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
$Err           = 'false';

if (!empty($_POST['NUMMETERx']) && is_numeric($_POST['NUMMETERx'])) {
	$NUMMETERx = $_POST['NUMMETERx'];
} else {
	die('form error');
}
if (!empty($_POST['DELAYx']) && is_numeric($_POST['DELAYx'])) {
	$DELAYx = $_POST['DELAYx'];
} else {
	$DELAYx = 1000;
}
if (!empty($_POST['DISTROx']) && is_string($_POST['DISTROx'])) {
	$DISTROx = htmlspecialchars($_POST['DISTROx'], ENT_QUOTES, 'UTF-8');
} else {
	die('form error');
}
if (!empty($_POST['DEBUGx'])) {
	$DEBUGx = 'true';
} else {
	$DEBUGx = 'false';
}
if (!empty($_POST['DATASx']) && is_string($_POST['DATASx'])) {
	$DATASx = htmlspecialchars($_POST['DATASx'], ENT_QUOTES, 'UTF-8');
} else {
	die('form error');
}
if (!empty($_POST['LOGCOMx'])) {
	$LOGCOMx = 'true';
} else {
	$LOGCOMx = 'false';
}
if (!empty($_POST['DTZx']) && is_string($_POST['DTZx'])) {
	$DTZx =  htmlspecialchars($_POST['DTZx'], ENT_QUOTES, 'UTF-8');
} else {
	die('form error');
}
if (!empty($_POST['LATITUDEx']) && is_numeric($_POST['LATITUDEx'])) {
	$LATITUDEx = $_POST['LATITUDEx'];
} else {
	$LATITUDEx = 50.61;
}
if (!empty($_POST['LONGITUDEx']) && is_numeric($_POST['LONGITUDEx'])) {
	$LONGITUDEx = $_POST['LONGITUDEx'];
} else {
	$LONGITUDEx = 4.635;
}
if (!empty($_POST['DATEFORMATx']) && is_string($_POST['DATEFORMATx'])) {
	$DATEFORMATx = htmlspecialchars($_POST['DATEFORMATx'], ENT_QUOTES, 'UTF-8');
} else {
	$DATEFORMATx = 'd/m/Y';
}
if (!empty($_POST['DPOINTx']) && is_string($_POST['DPOINTx'])) {
	$DPOINTx = htmlspecialchars($_POST['DPOINTx'], ENT_QUOTES, 'UTF-8');
} else {
	$DPOINTx = ',';
}
if (!empty($_POST['THSEPx']) && is_string($_POST['THSEPx'])) {
	$THSEPx = htmlspecialchars($_POST['THSEPx'], ENT_QUOTES, 'UTF-8');
} else {
	$THSEPx = '.';
}
if (!empty($_POST['CURSx']) && is_string($_POST['CURSx'])) {
	$CURSx = htmlspecialchars($_POST['CURSx'], ENT_QUOTES, 'UTF-8');
} else {
	$CURSx = '€';
}
if (!empty($_POST['TITLEx']) && is_string($_POST['TITLEx'])) {
	$TITLEx = htmlspecialchars($_POST['TITLEx'], ENT_QUOTES, 'UTF-8');
} else {
	$TITLEx = '';
}
if (!empty($_POST['SUBTITLEx']) && is_string($_POST['SUBTITLEx'])) {
	$SUBTITLEx = htmlspecialchars($_POST['SUBTITLEx'], ENT_QUOTES, 'UTF-8');
} else {
	$SUBTITLEx = '';
}
if (!empty($_POST['STYLEx']) && is_string($_POST['STYLEx'])) {
	$STYLEx = htmlspecialchars($_POST['STYLEx'], ENT_QUOTES, 'UTF-8');
} else {
	$STYLEx = 'default';
}
if (!empty($_POST['LANGx']) && is_string($_POST['LANGx'])) {
	$LANGx = htmlspecialchars($_POST['LANGx'], ENT_QUOTES, 'UTF-8');
} else {
	$LANGx = 'English';
}
if (!empty($_POST['KEEPDDAYSx']) && is_numeric($_POST['KEEPDDAYSx'])) {
	$KEEPDDAYSx = $_POST['KEEPDDAYSx'];
} else {
	$KEEPDDAYSx = 0;
}
if (!empty($_POST['AMOUNTLOGx']) && is_numeric($_POST['AMOUNTLOGx'])) {
	$AMOUNTLOGx = $_POST['AMOUNTLOGx'];
} else {
	$AMOUNTLOGx = 2500;
}

if ($Err != 'true') {
	$myFile = '../config/config_main.php';
	$fh = fopen($myFile, 'w+') or die("<font color='#8B0000'><b>Can't open $myFile file. Configuration not saved !</b></font>");
	$stringData = "<?php
if(!defined('checkaccess')){die('Direct access not permitted');}
// ### GENERAL
\$NUMMETER=$NUMMETERx;
\$DELAY=$DELAYx;
\$DISTRO='$DISTROx';
\$LOGCOM=$LOGCOMx;
\$DEBUG=$DEBUGx;
\$DATASET='$DATASx';

// ### LOCALIZATION
\$DTZ='$DTZx';
\$LATITUDE=$LATITUDEx;
\$LONGITUDE=$LONGITUDEx;
\$DATEFORMAT='$DATEFORMATx';
\$DPOINT='$DPOINTx';
\$THSEP='$THSEPx';
\$CURS='$CURSx';

// ### WEB PAGE
\$TITLE=\"$TITLEx\";
\$SUBTITLE=\"$SUBTITLEx\";
\$STYLE=\"$STYLEx\";
\$LANG=\"$LANGx\";

// ### CLEANUP
\$KEEPDDAYS=$KEEPDDAYSx;
\$AMOUNTLOG=$AMOUNTLOGx;

\$cfgver=$CFGmain;
?>
";
	fwrite($fh, $stringData);
	fclose($fh);

	echo "
<br><div align=center><font color='#228B22'><b>Main configuration saved</b></font>
<br>
<br><br><font size='-1'>meterN might need to be restarted for these changes to take effect</font>
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin.php'\" value='Back'>
</div>
";
} else {
	echo "
<br><div align=center><font color='#8B0000'><b>Error configuration not saved !</b></font><br>
<INPUT type='button' value='Back' onclick='history.back()'>
</div>
";
}

?>
<br>
<br>
          <!-- #EndEditable -->
          </td>
          </tr>
</table>
</body>
</html>
