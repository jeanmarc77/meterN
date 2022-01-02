<?php
/**
 * /srv/http/metern/admin/admin_layout2.php
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
if (!empty($_POST['GRAPHH']) && is_numeric($_POST['GRAPHH'])) {
	$GRAPHH = $_POST['GRAPHH'];
} else {
	$GRAPHH = 220;
}
if (!empty($_POST['POWER_MIN']) && is_numeric($_POST['POWER_MIN'])) {
	$POWER_MIN = $_POST['POWER_MIN'];
} else {
	$POWER_MIN = 0;
}
if (!empty($_POST['POWER_MAX']) && is_numeric($_POST['POWER_MAX'])) {
	$POWER_MAX = $_POST['POWER_MAX'];
} else {
	$POWER_MAX = 1000;
}

for ($i = 1; $i <= $NUMMETER; $i++) {
	include "../config/config_met" . $i . ".php";
	if (!empty($_POST["GRAPH_METx$i"]) && is_numeric($_POST["GRAPH_METx$i"])) {
		${'GRAPH_METx' . $i} = $_POST["GRAPH_METx$i"];
	} else {
		${'GRAPH_METx' . $i} = 0;
	}
	if (!empty($_POST["LASTD_METx$i"])) {
		${'LASTD_METx' . $i} = 'true';
	} else {
		${'LASTD_METx' . $i} = 'false';
	}
	if (!empty($_POST["FILL_METx$i"])) {
		${'FILL_METx' . $i} = 'true';
	} else {
		${'FILL_METx' . $i} = 'false';
	}
}

$myFile = '../config/config_layout.php';
$fh = fopen($myFile, 'w+') or die("<font color='#8B0000'><b>Can't open $myFile file. Configuration not saved !</b></font>");
$stringData = "<?php
if(!defined('checkaccess')){die('Direct access not permitted');}
";

$stringData .= "
\$GRAPHH=$GRAPHH;
\$POWER_MIN=$POWER_MIN;
\$POWER_MAX=$POWER_MAX;
";

for ($i = 1; $i <= $NUMMETER; $i++) {
	// ### GENERAL
	$stringData .= "\$GRAPH_MET$i=${'GRAPH_METx'.$i};\n";
	$stringData .= "\$LASTD_MET$i=${'LASTD_METx'.$i};\n";
	$stringData .= "\$FILL_MET$i=${'FILL_METx'.$i};\n";
}

$stringData .= "
\$cfgver=$CFGlay;
?>";
fwrite($fh, $stringData);
fclose($fh);

echo "
<br><div align=center><font color='#228B22'><b>Layout saved</b></font>
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin.php'\" value='Back'>
</div>
";

?>
<br>
<br>
<!-- #EndEditable -->
          </td>
          </tr>
</table>
</body>
</html>
