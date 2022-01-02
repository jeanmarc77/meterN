<?php
/**
 * /srv/http/metern/admin/admin_indicator2.php
 *
 * @package default
 */


include 'secure.php';
include "../scripts/datasets/$DATASET.php";
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
if (!empty($_POST['NUMINDx']) && is_numeric($_POST['NUMINDx'])) {
	$NUMINDx = $_POST['NUMINDx'];
} else {
	$NUMINDx = 0;
}
$comtest = false;

for ($ind_num = 1; $ind_num <= $NUMINDx; $ind_num++) {
	if (!empty($_POST["bntsubmit$ind_num"])) {
		$bntsubmit = $_POST["bntsubmit$ind_num"];
		$comtest   = true;
	} else {
		$bntsubmit = null;
	}

	if ($bntsubmit == "Test command") {
		if (!empty($_POST["IDx$ind_num"]) && is_string($_POST["IDx$ind_num"])) {
			$IDx = htmlspecialchars($_POST["IDx$ind_num"], ENT_QUOTES, 'UTF-8');
		} else {
			$IDx = '';
		}
		if (!empty($_POST["COMMANDx$ind_num"]) && is_string($_POST["COMMANDx$ind_num"])) {
			$COMMANDx = htmlspecialchars($_POST["COMMANDx$ind_num"], ENT_QUOTES, 'UTF-8');
		} else {
			$COMMANDx = '';
		}
		if (!empty($_POST["UNITx$ind_num"]) && is_string($_POST["UNITx$ind_num"])) {
			$UNITx = htmlspecialchars($_POST["UNITx$ind_num"], ENT_QUOTES, 'UTF-8');
		} else {
			$UNITx = '';
		}
		if (file_exists('../scripts/metern.pid')) {
			$pid     = (int) file_get_contents('../scripts/metern.pid');
			$command = exec("kill -9 $pid > /dev/null 2>&1 &");
			unlink('../scripts/metern.pid');
		}
		exec("$COMMANDx 2>&1", $datareturn);
		$datareturn = trim(implode($datareturn));
		$val        = isvalid($IDx, $datareturn);
		if (isset($val)) {
			echo "
<br><div align=center>$datareturn <font color='#228B22'><b>is a valid entry !</b></font>
";
		} else {
			$COMMANDx = htmlentities($COMMANDx);
			if (empty($datareturn)) {
				$datareturn = 'null';
			}
			echo "
<br><div align=center><b>Command :</b> $COMMANDx <br><br>$datareturn <font color='#8B0000'><b>is not valid</b></font>";
			if ($DATASET=='IEC62056') {
				echo ", the correct format is $IDx(1234.5*$UNITx)";
			}
		}

	}
}

if (!$comtest) {
	$myFile = '../config/config_indicator.php';
	$fh = fopen($myFile, 'w+') or die("<font color='#8B0000'><b>Can't open $myFile file. Configuration not saved !</b></font>");
	$stringData = "<?php
if(!defined('checkaccess')){die('Direct access not permitted');}

// ### CONFIG FOR INDICATOR(S)
\$NUMIND=$NUMINDx;
";

	for ($ind_num = 1; $ind_num <= $NUMINDx; $ind_num++) {
		if (!empty($_POST["INDNAMEx$ind_num"]) && is_string($_POST["INDNAMEx$ind_num"])) {
			$INDNAMEx = htmlspecialchars($_POST["INDNAMEx$ind_num"], ENT_QUOTES, 'UTF-8');
		} else {
			$INDNAMEx = '';
		}
		if (!empty($_POST["IDx$ind_num"]) && is_string($_POST["IDx$ind_num"])) {
			$IDx = htmlspecialchars($_POST["IDx$ind_num"], ENT_QUOTES, 'UTF-8');
		} else {
			$IDx = '';
		}
		if (!empty($_POST["COMMANDx$ind_num"]) && is_string($_POST["COMMANDx$ind_num"])) {
			$COMMANDx =  htmlspecialchars($_POST["COMMANDx$ind_num"], ENT_QUOTES, 'UTF-8');
		} else {
			$COMMANDx = '';
		}
		if (!empty($_POST["UNITx$ind_num"]) && is_string($_POST["UNITx$ind_num"])) {
			$UNITx = htmlspecialchars($_POST["UNITx$ind_num"], ENT_QUOTES, 'UTF-8');
		} else {
			$UNITx = '';
		}
		$stringData .= "
\$INDNAME$ind_num=\"$INDNAMEx\";
\$INDID$ind_num=\"$IDx\";
\$INDCOMMAND$ind_num=\"$COMMANDx\";
\$INDUNIT$ind_num=\"$UNITx\";
\$cfgver=$CFGind;
";

	}
	$stringData .= "?>";

	fwrite($fh, $stringData);
	fclose($fh);

	echo "
<br><div align=center><font color='#228B22'><b>Configuration for indicator(s) saved</b></font>
<br>
<br><br><font size='-1'>meterN must be restarted for these changes to take effect</font>
";
}
echo "
<br>
<br>
<form method='post'>
<INPUT TYPE='button' onClick=\"location.href='admin_indicator.php'\" value='Back'>
</form>
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
