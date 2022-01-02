<?php
/**
 * /srv/http/metern/scripts/loadcfg.php
 *
 * @package default
 */


// Load configuration
if (isset($_SERVER['REMOTE_ADDR'])) {
	die('Direct access not permitted');
}
if (get_current_user() == 'root') {
	//die('Abording. meterN cannot be as root');
}
// Few checks
$input = '{ "jsontest" : " <br>Json extension loaded" }';
$val   = json_decode($input, true);
if ($val["jsontest"] != "") {
} else {
	die("/!\ Json extension -NOT- loaded. Abording, please update php.ini.\n");
}

define('checkaccess', TRUE);
if (is_readable('../config/config_main.php')) {
	include '../config/config_main.php';
} else {
	die("Abording. Can't open config_main.php.\n");
}
if (is_readable('../config/config_indicator.php')) {
	include '../config/config_indicator.php';
} else {
	die("Abording. Can't open config_indicator.php.\n");
}
if (is_readable('../config/memory.php')) {
	include '../config/memory.php';
} else {
	die("Abording. Can't open memory.php.\n");
}
if (file_exists($MEMORY) && !is_writable($MEMORY)) {
	die("Abording. Can't write $MEMORY.php.\n");
}
if (version_compare(phpversion(), '7.1', '>=')) { // json_encode() uses EG(precision)
	ini_set('serialize_precision', -1);
}
if (file_exists($MEMORY)) {
	$data     = file_get_contents($MEMORY);
	$memarray = json_decode($data, true);
}

date_default_timezone_set($DTZ);
// Date check
$output = array();
$output = glob('../data/csv/*.csv');
sort($output);
$xdays = count($output);

$nowutc = strtotime(date('Ymd H:i:s'));
$todayUTC    = strtotime(date('Ymd'));
if ($xdays > 1) {
	$lastlog    = $output[$xdays - 1];
	$lines      = file($lastlog);
	$contalines = count($lines);
	$array      = preg_split('/,/', $lines[$contalines - 1]);
	$date1      = substr($output[$xdays - 1], -12);

	$year   = (int) substr($date1, 0, 4);
	$month  = (int) substr($date1, 4, 2);
	$day    = (int) substr($date1, 6, 2);
	$hour   = (int) substr($array[0], 0, 2);
	$minute = (int) substr($array[0], 3, 2);
	$fileUTCdate = strtotime(date("$year/$month/$day"));
	$epochdate = strtotime($year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute);
} else {
	$contalines = 0;
	$fileUTCdate = 0;
	$epochdate  = 1242975600;
}
$i = 1;
while ($nowutc < $epochdate) {
	$nowutc = strtotime(date('Ymd H:i:s'));
	echo "Computer time is not correct, trying again in $i sec\n";
	sleep("$i");
	$i++;
	if ($i > 1200) {
		die("Abording..\n");
	}
}

// Initialize variables
$minlist = array(
	'00',
	'05',
	'10',
	'15',
	'20',
	'25',
	'30',
	'35',
	'40',
	'45',
	'50',
	'55'
);

$DATADIR = dirname(dirname(__FILE__)) . '/data/';
$DELAY *= 1000;


/**
 *
 * @param unknown $aid
 * @param unknown $uid
 * @param unknown $title
 * @param unknown $msg
 */
function pushover($aid, $uid, $title, $msg) // Push-over
{
	curl_setopt_array($ch = curl_init(), array(
			CURLOPT_URL => 'https://api.pushover.net/1/messages.json',
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_POSTFIELDS => array(
				'token' => "$aid",
				'user' => "$uid",
				'message' => "$msg"
			)
		));
	curl_exec($ch);
	curl_close($ch);
}


/**
 *
 * @param unknown $token
 * @param unknown $chatid
 * @param unknown $msg
 */
function telegram($token, $chatid, $msg) // Telegram
{
	$tosend = array('chat_id' => $chatid, 'text' => $msg);
	$ch = curl_init('https://api.telegram.org/bot'.$token.'/sendMessage');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tosend));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$output = curl_exec($ch);
	curl_close($ch);
}

/**
 *
 * @param unknown $stringData
 */
function logevents($stringData) // Log to events
{
	$dir = dirname(dirname(__FILE__)) . '/data/';
	$stringData .= file_get_contents($dir . 'events.txt');
	file_put_contents($dir . '/events.txt', $stringData);
}


/**
 *
 * @param unknown $id
 * @param unknown $datareturn
 * @return unknown
 */
include "datasets/$DATASET.php";

// Meters config and memory variables
for ($i = 1; $i <= $NUMMETER; $i++) {
	if (is_readable("../config/config_met$i.php")) {
		include "../config/config_met$i.php";
	} else {
		die("Abording. Can't open config_met$i.php.\n");
	}
	if (!isset($memarray["msgflag$i"])) {
		$memarray["msgflag$i"] = false;
	}
	$logc[$i] = false;

	$livememarray['UTC']               = strtotime(date('Ymd H:i:s'));
	$livememarray["${'METNAME'.$i}$i"] = 0;
	${'comlost' . $i}                  = false;

	if (${'TYPE' . $i} != 'Sensor' && $fileUTCdate == $todayUTC) { // Firsts and Lasts of the day
		$val_last  = null;
		$val_first = null;
		$j         = 0;
		while (!isset($val_first)) {
			$j++;
			$array     = preg_split('/,/', $lines[$j]);
			$val_first = isset($array[$i]) ? trim($array[$i]) : '';
			if ($val_first == '') { // if skipped
				$val_first = null;
			}
			if ($j == $contalines - 1) {
				$val_first = 0; // didn't find any prev. first value
			}
		}
		$j = 0;
		while (!isset($val_last)) {
			$j++;
			$array    = preg_split('/,/', $lines[$contalines - $j]);
			$val_last = isset($array[$i]) ? trim($array[$i]) : '';
			if ($val_last == '') {
				$val_last = null;
			}
			if ($j == $contalines - 1) {
				$val_last = 0;
			}
		}
		$memarray["First$i"] = round($val_first, ${'PRECI' . $i});
		$memarray["Last$i"]  = round($val_last, ${'PRECI' . $i});
	}
}

if (!isset($memarray['5minflag'])) {
	$memarray['5minflag'] = false;
}

$errfile = $DATADIR . 'metern.err';
if (file_exists($errfile)) {
	$lines = file($errfile);
	$cnt   = count($lines);
	if ($cnt >= $AMOUNTLOG) {
		$cnt   -= $AMOUNTLOG;
		array_splice($lines, 0, $cnt);
		$file2 = fopen($errfile, 'w');
		fwrite($file2, implode('', $lines));
		fclose($file2);
	}
}

/////  Main memory
$data = json_encode($memarray);
file_put_contents($MEMORY, $data);
/////  Live memory
$data = json_encode($livememarray);
file_put_contents($LIVEMEMORY, $data);
?>
