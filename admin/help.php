<?php
/**
 * /srv/http/metern/admin/help.php
 *
 * @package default
 */


include 'secure.php';
include '../scripts/version.php';
include '../config/memory.php';
include '../config/allowed_comapps.php';
date_default_timezone_set($DTZ);
$nowUTC = strtotime(date("Ymd H:i:s"));
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>meterN Debug</title>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<link rel="stylesheet" href="../styles/default/css/style.css" type="text/css">
</head>
<body>
<table width="95%" height="80%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr bgcolor="#FFFFFF" height="64">
  <td class="cadretopleft" width="128">&nbsp;<img src="../images/house48.png" width="48" height="48" alt="meterN"></td>
  <td class="cadretop" align="center"><b>meterN Debug</b></td>
  <td class="cadretopright" width="128" align="right"></td>
  </tr>
  <tr bgcolor="#CCCC66">
<td COLSPAN="3" class="cadre" height="10">
&nbsp;
</td></tr>
<tr valign="top">
    <td COLSPAN="3" class="cadrebot" bgcolor="#d3dae2">
<!-- #BeginEditable "mainbox" -->
<?php
$cpu = 0;
$mem = 0;
$PID = '--';
$tag = '--';
if (file_exists('../scripts/metern.pid')) {
	$PID = (int) file_get_contents('../scripts/metern.pid');
	$cpu = exec("ps -p $PID -o %cpu | tail -1 | awk '{print $1}'");
	$mem = exec("ps -p $PID -o %mem | tail -1 | awk '{print $1}'");
	$tag = date("$DATEFORMAT H:i:s", filemtime('../scripts/metern.pid'));
}
echo "
<br>
<table border=1 width='95%' cellspacing=0 cellpadding=5 align='center'>
<tr><td>
<b>$VERSION</b> - PID $PID since $tag - Usage : Memory $mem% CPU $cpu%
</td></tr>
<tr><td><b>tmpfs :</b><br>Make sure you only use a tmpfs, a temporary filesystem that resides in memory.
<br>";
$datareturn = null;
$datareturn = exec("df -h |grep $TMPFS | grep tmpfs");
if ($datareturn) {
	echo "<img src='../images/24/sign-check.png' width=24 height=24 border=0> $TMPFS is ok ";
} else {
	echo "<img src='../images/24/sign-error.png' width=24 height=24 border=0> $TMPFS is -NOT- OK ";
}
$datareturn = file_put_contents($TMPFS. '/test', 'test');
if ($datareturn) {
	echo "<img src='../images/24/sign-check.png' width=24 height=24 border=0> ok to write";
	unlink($TMPFS. '/test');
} else {
	echo "<img src='../images/24/sign-error.png' width=24 height=24 border=0> -NOT- OK to write";
}
echo "
</td></tr>
<tr><td valign='top'><b>Checking PHP :</b><br>
";
echo "PHP version: <a href='phpinfo.php'>" . phpversion() . '</a><br>';
$input = '{ "jsontest" : " <br>Json extension loaded" }';
$val   = json_decode($input, true);
if ($val["jsontest"] != "") {
	echo "<img src='../images/24/sign-check.png' width=24 height=24 border=0> Json extension loaded ";
} else {
	echo "<img src='../images/24/sign-error.png' width=24 height=24 border=0> Json extension -NOT- loaded ";
}
if (!extension_loaded('calendar')) {
	echo "<img src='../images/24/sign-error.png' width=24 height=24 border=0> Calendar extension -NOT- loaded ";
} else {
	echo "<img src='../images/24/sign-check.png' width=24 height=24 border=0> Calendar extension loaded ";
}
if (!extension_loaded('curl')) {
	echo "<img src='../images/24/sign-error.png' width=24 height=24 border=0> Curl extension -NOT- loaded ";
} else {
	echo "<img src='../images/24/sign-check.png' width=24 height=24 border=0> Curl extension loaded ";
}
$ndday = date($DATEFORMAT . " H:i:s", $nowUTC);
echo "<br><br>You timezone is set to $DTZ ($ndday)";
if (ini_get('open_basedir')) {
	echo '<br> open_basedir restriction set in php.ini ' . ini_get('open_basedir');
} else {
	echo '<br> No open_basedir restriction set in php.ini';
}
if (ini_get('sendmail_path')) {
	echo '<br>Your sendmail_path is set to ' . ini_get('sendmail_path');
} else {
	echo '<br>Your sendmail_path is NOT set';
}
echo "
</td></tr>
<tr><td valign='top'><b>/var/lock permissions :</b><br>";
$datareturn = file_put_contents('/var/lock/test', 'test');
$alt = substr(sprintf('%o', fileperms('/var/lock')), -4);
if ($datareturn) {
	echo "<img src='../images/24/sign-check.png' width=24 height=24 border=0 alt='$alt'> ok to write";
	unlink('/var/lock/test');
} else {
	echo "<img src='../images/24/sign-error.png' width=24 height=24 border=0 alt='$alt'> -NOT- OK to write";
}
$whoami = exec('whoami'); 
$CURDIR = dirname(dirname(__FILE__));
echo "<br><br>Some distros have 755 by default, some application need to write port lock in there.
<br>Change permissions to 777 (e.g. 'cp /usr/lib/tmpfiles.d/legacy.conf /etc/tmpfiles.d/' and 'nano /etc/tmpfiles.d/legacy.conf') and reboot.
</td></tr>
<tr><td valign='top'><b>Files permissions :</b> <a href='fperms.php'>$CURDIR files should be owned by $whoami user</a>
</td></tr>
<tr><td valign='top'><b>Hardware and communication apps. rights :</b><br>
<br><b>Grant the permission to execute your com. apps.</b> Locate them with 'whereis mycomapp' and 'chmod a+x /pathto/mycomapp.py'.
<br><b>Make a shortcut</b> (e.g. 'ln -s /pathto/mycomapp.py /usr/bin/mycomapp')<br>
<br><b>Allow the access the communication ports</b>
<br>The peripherals are usually owned by the uucp or dialout group, check (e.g. 'ls -al /dev/ttyUSB0'), add your user to the group: (e.g. 'usermod -a -G uucp $whoami')";
$whoami = exec('whoami');
echo "<br>You are '$whoami' user</b> which currently belong to those groups: ";
$datareturn = exec("groups $whoami");
echo "$datareturn<br>";
echo "<br><b>Since PHP 7.4 there is hardening options</b>.";
echo ' Current version is ' . PHP_VERSION;
echo ". Allow to use your com. devices by setting PrivateDevices=false in php-fpm.service. (e.g. 'systemctl edit --full php-fpm.service')
<br> After change you need to restart php and your webserver. (e.g. 'systemctl restart php-fpm' and 'systemctl restart nginx')
</td>
</tr>
<tr><td>
<b>Allowed communication apps :</b><br>";
$cnta = count($ALLWDCMD);
for ($i=0; $i<$cnta; $i++) {
	echo "\"$ALLWDCMD[$i]\" ";
}
echo "<br><br>Edit config/allowed_comapps.php file to define.
</td></tr>
<tr><td>
<b>Log error file :</b><br>";
if (!$DEBUG) {
	echo "<img src='../images/24/sign-warning.png' width=24 height=24 border=0> The debug mode have to be enable in the main configuration";
}
echo "<br>
<textarea style='resize: none;background-color: #DCDCDC' cols='100' rows='10'>";
if (file_exists('../data/metern.err')) {
	$lines = file('../data/metern.err');
	foreach ($lines as $line_num => $line) {
		echo "$line";
	}
}
echo "</textarea>
</td></tr>
<tr><td>
<b>Memory ($MEMORY) :</b>
<br>";
if (file_exists($MEMORY)) {
$data     = file_get_contents($MEMORY);
$array   = json_decode($data, true);
print_r($array);
}
echo "<br><b>Live ($LIVEMEMORY) :</b>
<br>";
if (file_exists($LIVEMEMORY)) {
$data     = file_get_contents($LIVEMEMORY);
$array   = json_decode($data, true);
print_r($array);
}
echo "<br><b>Indicators ($ILIVEMEMORY) :</b>
<br>";
if (file_exists($ILIVEMEMORY)) {
	$data     = file_get_contents($ILIVEMEMORY);
	$array   = json_decode($data, true);
	print_r($array);
}
echo "
<br>
</td></tr>
</table>";
?>
<br>
<div align=center><INPUT TYPE='button' onClick="location.href='admin.php'" value='Back'></div>
<br>
<br>
<!-- #EndEditable -->
</td>
</tr>
</table>
</body>
</html>
