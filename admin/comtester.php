<?php
/**
 * /srv/http/metern/admin/comtester.php
 *
 * @package default
 */


include 'secure.php';
include '../config/config_indicator.php';
include '../config/allowed_comapps.php';
include "../scripts/datasets/$DATASET.php";
date_default_timezone_set($DTZ);

// Build the list of testable commands straight from the current
// configuration, one entry per meter/live/indicator command - not from
// $ALLWDCMD directly, so the label can name the meter or indicator it
// belongs to. Still gated by $ALLWDCMD: a command missing from the
// allowlist (eg. just edited and not yet re-saved through the admin
// panel, which is what regenerates it) is left out rather than offered,
// the same rule admin_meter2.php/admin_indicator2.php already apply
// before running anything.
$tests = array();
for ($i = 1; $i <= $NUMMETER; $i++) {
	include "../config/config_met$i.php";
	if (!empty(${'COMMAND' . $i}) && in_array(${'COMMAND' . $i}, $ALLWDCMD, true)) {
		$tests[] = array(
			'label' => "${'METNAME' . $i} - 5' command",
			'cmd'   => ${'COMMAND' . $i},
			'id'    => ${'ID' . $i},
		);
	}
	if (!empty(${'LIVECOMMAND' . $i}) && in_array(${'LIVECOMMAND' . $i}, $ALLWDCMD, true)) {
		$tests[] = array(
			'label' => "${'METNAME' . $i} - live command",
			'cmd'   => ${'LIVECOMMAND' . $i},
			'id'    => ${'LID' . $i},
		);
	}
}
for ($i = 1; $i <= $NUMIND; $i++) {
	if (!empty(${'INDCOMMAND' . $i}) && in_array(${'INDCOMMAND' . $i}, $ALLWDCMD, true)) {
		$tests[] = array(
			'label' => "${'INDNAME' . $i} - indicator command",
			'cmd'   => ${'INDCOMMAND' . $i},
			'id'    => ${'INDID' . $i},
		);
	}
}

// Selection and iteration count both come from a fixed list, never free
// text: the only thing that can ever reach exec() is a command already
// present in $ALLWDCMD, matched with the same in_array(..., true) check
// admin_meter2.php/admin_indicator2.php use before saving one. There is
// no field an admin (or anyone who guesses the .htpasswd) could use to
// run an arbitrary shell command through this page.
$cmd = null;
if (!empty($_POST['testcmd']) && is_string($_POST['testcmd'])) {
	foreach ($tests as $t) {
		if ($t['cmd'] === $_POST['testcmd']) {
			$cmd = $t;
			break;
		}
	}
}
$tries = 3;
if (!empty($_POST['tries']) && in_array($_POST['tries'], array('1', '3', '5'), true)) {
	$tries = (int) $_POST['tries'];
}
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
  <td class="cadretop" align="center"><b>Com tester</b></td>
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
<div align=center><b>Run one of the allowed commands a few times and inspect what it actually returns</b><br></div>
<br>
<table border=1 cellspacing=0 cellpadding=5 width='80%' align='center'>
<tr><td>
<form method='POST' action='comtester.php'>
<select name='testcmd'>";
if (empty($tests)) {
	echo "<option value=''>-- config/allowed_comapps.php is empty or missing, nothing to test --</option>";
}
foreach ($tests as $t) {
	$sel = ($cmd && $cmd['cmd'] === $t['cmd']) ? ' selected' : '';
	echo '<option value="' . htmlspecialchars($t['cmd'], ENT_QUOTES) . "\"$sel>" . htmlspecialchars($t['label'], ENT_QUOTES) . '</option>';
}
echo "</select>
Tries <select name='tries'>";
foreach (array(1, 3, 5) as $n) {
	$sel = ($n == $tries) ? ' selected' : '';
	echo "<option value='$n'$sel>$n</option>";
}
echo "</select>
<input type='submit' value='Test communication'>
</form>
</td></tr></table>
<br>";

if ($cmd) {
	$timemax = 0;
	$timemin = PHP_INT_MAX;
	$errcnt  = 0;
	$emptcnt = 0;
	echo "<div align=center><b>" . htmlspecialchars($cmd['label'], ENT_QUOTES) . '</b><br><code>' . htmlspecialchars($cmd['cmd'], ENT_QUOTES) . '</code></div><br>';
	for ($n = 1; $n <= $tries; $n++) {
		$start      = microtime(true);
		$datareturn = null;
		exec($cmd['cmd'], $datareturn);
		$raw     = trim(implode("\n", $datareturn));
		$elapsed = microtime(true) - $start;
		if ($elapsed > $timemax) {
			$timemax = $elapsed;
		}
		if ($elapsed < $timemin) {
			$timemin = $elapsed;
		}
		$parsed = isvalid($cmd['id'], $raw);
		$stamp  = round($elapsed * 1000, 2);
		if (!isset($parsed)) {
			$errcnt++;
			echo "<font color='#8B0000'>$n : rejected ($stamp ms) - raw: " . htmlspecialchars($raw !== '' ? $raw : '(empty)', ENT_QUOTES) . '</font><br>';
		} elseif ($parsed === '') {
			$emptcnt++;
			echo "<font color='#B8860B'>$n : valid, empty value ($stamp ms) - the reader's own \"no sample this cycle\" marker - raw: " . htmlspecialchars($raw, ENT_QUOTES) . '</font><br>';
		} else {
			echo "<font color='#888'>$n : " . htmlspecialchars($parsed, ENT_QUOTES) . " ($stamp ms) - raw: " . htmlspecialchars($raw, ENT_QUOTES) . '</font><br>';
		}
	}
	$timemin = round($timemin * 1000, 2);
	$timemax = round($timemax * 1000, 2);
	$stamp   = date($DATEFORMAT . ' H:i:s');
	echo "<br><b>$stamp - best $timemin ms - worst $timemax ms - $errcnt rejected - $emptcnt empty out of $tries</b>";
}
echo '
</td></tr>
</table>
<br>';
?>
<!-- #EndEditable -->
</td>
</tr>
</table>
</body>
</html>
