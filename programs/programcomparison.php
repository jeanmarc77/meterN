<?php
/**
 * /srv/http/metern/programs/programcomparison.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/config_main.php';
date_default_timezone_set('UTC');
ini_set('serialize_precision', -1);

if (!empty($_GET['metnum']) && is_numeric($_GET['metnum'])) {
	$metnum = $_GET['metnum'];
} else {
	$metnum = 1;
}
include "../config/config_met$metnum.php";
include '../languages/' . $LANG . '.php';

if (!empty($_GET['whichmonth']) && is_numeric($_GET['whichmonth'])) {
	$whichmonth = $_GET['whichmonth'];
} else {
	$whichmonth = date('n');
}
if (!empty($_GET['whichyear']) && is_numeric($_GET['whichyear'])) {
	$whichyear = $_GET['whichyear'];
} else {
	$whichyear = date('Y');
}
if (!empty($_GET['comparemet']) && is_numeric($_GET['comparemet'])) {
	$comparemet = $_GET['comparemet'];
} else {
	$comparemet = 1;
}
if ($metnum != $comparemet) {
	include "../config/config_met$comparemet.php";
}
if (!empty($_GET['comparemonth']) && is_numeric($_GET['comparemonth'])) {
	$comparemonth = $_GET['comparemonth'];
} else {
	$comparemonth = date('n');
}
if (!empty($_GET['compareyear']) && is_string($_GET['compareyear'])) {
	$compareyear = htmlspecialchars($_GET['compareyear'], ENT_QUOTES, "UTF-8");
} else {
	$compareyear = date('Y');
}


/**
 *
 * @param unknown $selectmonth
 * @param unknown $selectyear
 * @param unknown $metnum
 * @return unknown
 */
function getvalues($selectmonth, $selectyear, $metnum) {
	include '../config/config_main.php';
	include "../config/config_met$metnum.php";

	$dir = '../data/meters/';
	if (file_exists($dir . $metnum . ${'METNAME' . $metnum} . $selectyear . '.csv')) {
		$thisfile = $dir . $metnum . ${'METNAME' . $metnum} . $selectyear . '.csv';
		if ($handle = fopen("$thisfile", 'r')) {
			$i = 0;
			while ($line = fgetcsv($handle, 1000, ',')) {
				$year  = substr($line[0], 0, 4);
				$month = substr($line[0], 4, 2);
				$day   = substr($line[0], 6, 2);

				if ($month == $selectmonth || $selectmonth == 13) {
					$date1 = strtotime($year . '-' . $month . '-' . $day);
					$date1 *= 1000; // in ms
					$month                 = (int) ($month);
					$day                   = (int) ($day);
					$val_day[$month][$day] = (float) $line[1];
				}
			} // end of looping through the file
		}
	}

	if ($selectyear == date('Y') && ($selectmonth == date('n') || $selectmonth == 13)) { // Add today
		$output = glob('../data/csv/*.csv');
		rsort($output);
		if (isset($output[0])) {
			$file       = file($output[0]);
			$month      = (int) substr($output[0], -8, 2);
			$day        = (int) substr($output[0], -6, 2);
			$contalines = count($file);
			$prevarray  = preg_split('/,/', $file[1]);
			$linearray  = preg_split('/,/', $file[$contalines - 1]);
			$val_first  = (float) $prevarray[$metnum];
			$val_day[$month][$day]   = (float)$linearray[$metnum];
			if (!empty($val_first) && !empty($val_day[$month][$day])) {
				if ($val_first <= $val_day[$month][$day]) {
					$val_day[$month][$day] -= $val_first;
				} else { // counter pass over
					$val_day[$month][$day] += ${'PASSO' . $metnum} - $val_first;
				}
			} else {
				$val_day[$month][$day] = 0;
			}
			$val_day[$month][$day] = round($val_day[$month][$day], ${'PRECI' . $metnum});
		}
	} // end of today

	$i    = 0;
	$cumu = 0; // Cumulative
	if ($selectmonth == 13) { // all year
		$selectmonth_start = 1;
		$selectmonth_stop  = 12;
	} else {
		$selectmonth_start = $selectmonth;
		$selectmonth_stop  = $selectmonth;
	}

	for ($k = $selectmonth_start; $k <= $selectmonth_stop; $k++) {
		$daythatm = cal_days_in_month(CAL_GREGORIAN, $k, $selectyear);
		for ($j = 1; $j <= $daythatm; $j++) {
			$date1 = strtotime($selectyear . '-' . $k . '-' . $j);
			$date1 *= 1000;
			if (!isset($val_day[$k][$j])) {
				$val_day[$k][$j] = 0; // Filling blanks dates
			}
			if (${'TYPE' . $metnum} == 'Elect') {
				$val_day[$k][$j] /= 1000;
			}
			$cumu += $val_day[$k][$j];
			$stack[$i] = array(
				$date1,
				$cumu
			);
			$i++;
		}
	} // k selected month
	return $stack;
} // enf of fnct getvalues

$datareturn = getvalues($whichmonth, $whichyear, $metnum); // Call fnct

if ($compareyear == $whichyear && $comparemonth == $whichmonth && $metnum == $comparemet) { //Same req
	$datareturn2 = $datareturn;
	$xaxe        = 0;
} else {
	$datareturn2 = getvalues($comparemonth, $compareyear, $comparemet);
	$xaxe        = 1;
} // end of same req

if ($metnum == $comparemet) {
	$name  = "$lgSMONTH[$whichmonth] $whichyear";
	$cname = "$lgSMONTH[$comparemonth] $compareyear";
} else {
	$name  = "${'METNAME' . $metnum} $lgSMONTH[$whichmonth] $whichyear";
	$cname = "${'METNAME' . $comparemet} $lgSMONTH[$comparemonth] $compareyear";
}

$data = array(
	0 => array(
		'name' => "$name",
		'type' => 'areaspline',
		'color' => "#${'COLOR' . $metnum}",
		'data' => $datareturn,
		'xAxis' => 0
	),
	1 => array(
		'name' => "$cname",
		'type' => 'spline',
		'color' => "#${'COLOR' . $comparemet}",
		'dashStyle' => 'Dot',
		'data' => $datareturn2,
		'xAxis' => $xaxe
	)
);

header("Content-type: application/json");
echo json_encode($data);
?>
