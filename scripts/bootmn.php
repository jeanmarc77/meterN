<?php
/**
 * /srv/http/metern/scripts/bootmn.php
 *
 * @package default
 */


if ($_SERVER['SERVER_ADDR'] != '127.0.0.1' && $_SERVER['SERVER_ADDR'] != '::1') {
	die('Direct access not permitted');
}

define('checkaccess', TRUE);
include '../config/config_main.php';
date_default_timezone_set($DTZ);
$DATADIR = dirname(dirname(__FILE__)) . '/data/';
$PID    = null;
$now    = date($DATEFORMAT . ' H:i:s');

if (file_exists('metern.pid')) {
	exec('pkill -f metern.php> /dev/null 2>&1 &'); // make sure there is only one instance
	usleep(500000);
	unlink('metern.pid');
}

if ($DEBUG) {
	$command = 'php metern.php' . ' >> ../data/metern.err 2>&1 & echo $!; ';
	$PID     = exec($command);
	file_put_contents('metern.pid', $PID);
	$stringData = "$now\tStarting meterN on boot debug ($PID)\n\n";
	$myFile     = $DATADIR . 'metern.err';
	file_put_contents($myFile, $stringData, FILE_APPEND);
} else {
	$command = 'php metern.php' . ' > /dev/null 2>&1 & echo $!;';
	$PID     = exec($command);
	file_put_contents('metern.pid', $PID);
	$stringData = "$now\tStarting meterN on boot ($PID)\n\n";
}
include '../config/config_daemon.php';

$stringData .= file_get_contents($DATADIR . 'events.txt');
file_put_contents($DATADIR . 'events.txt', $stringData);
?>
