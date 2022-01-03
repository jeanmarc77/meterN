<?php
/**
 * /srv/http/metern/admin/admin_meter2.php
 *
 * @package default
 */


include 'secure.php';
include '../scripts/version.php';
include "../scripts/datasets/$DATASET.php";
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
if (!empty($_POST['met_numx']) && is_numeric($_POST['met_numx'])) {
	$met_numx = $_POST['met_numx'];
}
if (!empty($_POST['METNAMEx'])) {
	$METNAMEx = htmlspecialchars($_POST['METNAMEx'], ENT_QUOTES, 'UTF-8');
} else {
	$METNAMEx = "$met_numx";
}
if (!empty($_POST['TYPEx'])) {
	$TYPEx = htmlspecialchars($_POST['TYPEx'], ENT_QUOTES, 'UTF-8');
} else {
	$TYPEx = 'Elect';
}
if (!empty($_POST['PRODx']) && is_numeric($_POST['PRODx'])) {
	$PRODx = $_POST['PRODx'];
} else {
	$PRODx = 0;
}
if (!empty($_POST['PHASEx']) && is_numeric($_POST['PHASEx'])) {
	$PHASEx = $_POST['PHASEx'];
} else {
	$PHASEx = 1;
}
if (!empty($_POST['SKIPMONITORINGx'])) {
	$SKIPMONITORINGx = 'true';
} else {
	$SKIPMONITORINGx = 'false';
}
if (!empty($_POST['IDx']) && is_string($_POST['IDx'])) {
	$IDx = htmlspecialchars($_POST['IDx'], ENT_QUOTES, 'UTF-8');
} else {
	$IDx = '';
}
if (!empty($_POST['COMMANDx']) && is_string($_POST['COMMANDx'])) {
	$COMMANDx = htmlspecialchars($_POST['COMMANDx'], ENT_QUOTES, 'UTF-8');
} else {
	$COMMANDx = '';
}
if (!empty($_POST['UNITx']) && is_string($_POST['UNITx'])) {
	$UNITx = htmlspecialchars($_POST['UNITx'], ENT_QUOTES, 'UTF-8');
} else {
	$UNITx = '';
}
if (!empty($_POST['PRECIx']) && is_numeric($_POST['PRECIx'])) {
	$PRECIx = $_POST['PRECIx'];
} else {
	$PRECIx = 0;
}
if (!empty($_POST['PASSOx']) && is_numeric($_POST['PASSOx'])) {
	$PASSOx = $_POST['PASSOx'];
} else {
	$PASSOx = 0;
}
if (!empty($_POST['COLORx']) && is_string($_POST['COLORx'])) {
	$COLORx = htmlspecialchars($_POST['COLORx'], ENT_QUOTES, 'UTF-8');

	if ($TYPEx == 'Gas' && $COLORx == 'FFFFFF') {
		$COLORx = '89A54E';
	} else if ($TYPEx == 'Water' && $COLORx == 'FFFFFF') {
		$COLORx = '92A8CD';
	}
}
if (!empty($_POST['PRICEx']) && is_numeric($_POST['PRICEx'])) {
	$PRICEx = $_POST['PRICEx'];
} else {
	$PRICEx = 0;
}
if (!empty($_POST['LIDx']) && is_string($_POST['LIDx'])) {
	$LIDx = htmlspecialchars($_POST['LIDx'], ENT_QUOTES, 'UTF-8');
} else {
	$LIDx = '';
}
if (!empty($_POST['LIVECOMMANDx']) && is_string($_POST['LIVECOMMANDx'])) {
	$LIVECOMMANDx = htmlspecialchars($_POST['LIVECOMMANDx'], ENT_QUOTES, 'UTF-8');
} else {
	$LIVECOMMANDx = '';
}
if (!empty($_POST['LIVEUNITx']) && is_string($_POST['LIVEUNITx'])) {
	$LIVEUNITx = htmlspecialchars($_POST['LIVEUNITx'], ENT_QUOTES, 'UTF-8');
} else {
	$LIVEUNITx = '';
}
if (!empty($_POST['EMAILx']) && is_string($_POST['EMAILx'])) {
	$EMAILx = htmlspecialchars($_POST['EMAILx'], ENT_QUOTES, 'UTF-8');
} else {
	$EMAILx = '';
}
if (!empty($_POST['POAKEYx']) && is_string($_POST['POAKEYx'])) {
	$POAKEYx = htmlspecialchars($_POST['POAKEYx'], ENT_QUOTES, 'UTF-8');
} else {
	$POAKEYx = '';
}
if (!empty($_POST['POUKEYx']) && is_string($_POST['POUKEYx'])) {
	$POUKEYx = htmlspecialchars($_POST['POUKEYx'], ENT_QUOTES, 'UTF-8');
} else {
	$POUKEYx = '';
}
if (!empty($_POST['TLGRTOKx']) && is_string($_POST['TLGRTOKx'])) {
	$TLGRTOKx = htmlspecialchars($_POST['TLGRTOKx'], ENT_QUOTES, 'UTF-8');
} else {
	$TLGRTOKx = '';
}
if (!empty($_POST['TLGRCIDx']) && is_numeric($_POST['TLGRCIDx'])) {
	$TLGRCIDx = $_POST['TLGRCIDx'];
} else {
	$TLGRCIDx = 0;
}
if (!empty($_POST['WARNCONSODx']) && is_numeric($_POST['WARNCONSODx'])) {
	$WARNCONSODx = $_POST['WARNCONSODx'];
} else {
	$WARNCONSODx = 0;
}
if (!empty($_POST['NORESPMx'])) {
	$NORESPMx = 'true';
} else {
	$NORESPMx = 'false';
}
if (!empty($_POST['bntsubmit'])) {
	$bntsubmit = $_POST['bntsubmit'];
} else {
	$bntsubmit = null;
}

if ($TYPEx == 'Elect') {
	$UNITx     = 'Wh';
	$LIVEUNITx = 'W';
	$PRECIx    = 0;
}


/**
 *
 * @param unknown $adress
 * @return unknown
 */
function testemail($adress) {
	$Syntaxe = '#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#';
	if (preg_match($Syntaxe, $adress) || $adress == '')
		return 'true';
	else
		return 'false';
}


$Err = 'false';

if ($bntsubmit == "Test mail") {
	if (!testemail($EMAILx) || empty($EMAILx)) {
		echo "EMAIL is not correct<br>";
	} else {
		$sent = mail("$EMAILx", "meterN: Hello", "Hi,\r\n\r\nThanks for using meterN !", "From: meterN <$EMAILx>");
		if ($sent) {
			echo "
<br><div align=center><font color='#228B22'><b>Mail sent to $EMAILx</b></font>
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>";
		} else {
			echo "
<br><div align=center><font color='#8B0000'><b>We encountered an error sending your mail</b></font>
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>";
		}
	}
} elseif ($bntsubmit == 'Test Pushover') {

	curl_setopt_array($ch = curl_init(), array(
			CURLOPT_URL => "https://api.pushover.net/1/messages.json",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_POSTFIELDS => array(
				'token' => "$POAKEYx",
				'user' => "$POUKEYx",
				'title' => 'Hello',
				'message' => 'Thanks for using meterN !',
				'sound' => 'classical'
			)
		));
	$output = curl_exec($ch);
	curl_close($ch);

	if (preg_match('/"status":1/', $output)) {
		echo "
<br><div align=center><font color='#228B22'><b>Push message send !</b></font>
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>";
	} else {
		echo "
<br><div align=center><font color='#8B0000'><b>We encountered an error sending the message</b></font>
<br>&nbsp;
<br>$output
<br>&nbsp;
<br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>";
	}
} elseif ($bntsubmit == 'Test Telegram') {
	$msg = array('chat_id' => $TLGRCIDx, 'text' => 'Thanks for using meterN !');
	$ch = curl_init('https://api.telegram.org/bot'.$TLGRTOKx.'/sendMessage');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($msg));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$output = curl_exec($ch);
	curl_close($ch);
	$output = json_decode($output, true);
	if ($output['ok']) {
		echo "
<br><div align=center><font color='#228B22'><b>Push message send !</b></font>
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>";
	} else {
		echo "
      <br><div align=center><br><font color='#8B0000'><b>We encountered an error sending the message</b></font>
      <br>&nbsp;
      <br>";
      print_r($output);
      echo "
      <br>
      <br>&nbsp;
      <br><INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'></div>";
	}
} elseif ($bntsubmit == "Test command") {
	if (file_exists('../scripts/metern.pid')) {
		$pid     = (int) file_get_contents('../scripts/metern.pid');
		$command = exec("kill -9 $pid > /dev/null 2>&1 &");
		unlink('../scripts/metern.pid');
		usleep(500000);
	}
	exec("$COMMANDx 2>&1", $datareturn);
	$datareturn = trim(implode($datareturn));
	$val        = isvalid($IDx, $datareturn);
	if (isset($val) && is_numeric($val)) {
		echo "
<br><div align=center>$datareturn <font color='#228B22'><b>is a valid entry !</b></font>
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>";
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
		echo "
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>";
	}
} elseif ($bntsubmit == "Test live command") {
	if (file_exists('../scripts/metern.pid')) {
		$pid     = (int) file_get_contents('../scripts/metern.pid');
		$command = exec("kill -9 $pid > /dev/null 2>&1 &");
		unlink('../scripts/metern.pid');
	}
	exec("$LIVECOMMANDx 2>&1", $datareturn);
	$datareturn = trim(implode($datareturn));
	$val        = isvalid($LIDx, $datareturn);
	if (isset($val)) {
		echo "
<br><div align=center>$datareturn <font color='#228B22'><b>is a valid entry !</b></font>
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>";
	} else {
		$LIVECOMMANDx = htmlentities($LIVECOMMANDx);
		if (empty($datareturn)) {
			$datareturn = 'null';
		}
		echo "
<br><div align=center><b>Command</b> : $LIVECOMMANDx <br><br>$datareturn <font color='#8B0000'><b>is not valid</b></font>";
		if ($DATASET=='IEC62056') {
			echo ", the correct format is $LIDx(1234.5*$LIVEUNITx)";
		}
echo "
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>";
	}
} else {
	if (!testemail($EMAILx)) {
		echo "EMAIL is not correct<br>";
		$Err = 'true';
	}

	if ($Err != 'true') {
		$myFile = '../config/config_met' . $met_numx . '.php';
		$fh = fopen($myFile, 'w+') or die("<font color='#8B0000'><b>Can't open $myFile file. Configuration not saved !</b></font>");
		$stringData = "<?php
if(!defined('checkaccess')){die('Direct access not permitted');}

// ### CONFIG FOR METER #$met_numx

\$METNAME$met_numx=\"$METNAMEx\";
\$TYPE$met_numx='$TYPEx';
\$PROD$met_numx=$PRODx;
\$PHASE$met_numx=$PHASEx;
\$SKIPMONITORING$met_numx=$SKIPMONITORINGx;
\$ID$met_numx=\"$IDx\";
\$COMMAND$met_numx=\"$COMMANDx\";
\$UNIT$met_numx=\"$UNITx\";
\$PRECI$met_numx=$PRECIx;
\$PASSO$met_numx=$PASSOx;
\$COLOR$met_numx='$COLORx';
\$PRICE$met_numx=$PRICEx;
\$LID$met_numx=\"$LIDx\";
\$LIVECOMMAND$met_numx=\"$LIVECOMMANDx\";
\$LIVEUNIT$met_numx=\"$LIVEUNITx\";
\$EMAIL$met_numx=\"$EMAILx\";
\$POAKEY$met_numx='$POAKEYx';
\$POUKEY$met_numx='$POUKEYx';
\$TLGRTOK$met_numx='$TLGRTOKx';
\$TLGRCID$met_numx=$TLGRCIDx;
\$WARNCONSOD$met_numx=$WARNCONSODx;
\$NORESPM$met_numx=$NORESPMx;

\$cfgver=$CFGmet;
?>
";
		fwrite($fh, $stringData);
		fclose($fh);

		echo "
<br><div align=center><font color='#228B22'><b>Configuration for meter #$met_numx saved</b></font>
<br>
<br><br><font size='-1'>meterN must be restarted for these changes to take effect</font>
<br>
<br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>
";

	} else {
		echo "
<br><div align=center><font color='#8B0000'><b>Error configuration not saved !</b></font><br>
<INPUT TYPE='button' onClick=\"location.href='admin_meter.php?met_num=$met_numx'\" value='Back'>
</div>
";
	}

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
