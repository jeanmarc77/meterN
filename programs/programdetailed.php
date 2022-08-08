<?php
/**
 * /srv/http/metern/programs/programdetailed.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/config_main.php';
date_default_timezone_set('UTC');
ini_set('serialize_precision', -1);
include '../languages/' . $LANG . '.php';

if (!empty($_GET['date1'])) {
	$date1 = htmlspecialchars($_GET['date1'], ENT_QUOTES, 'UTF-8');
} else {
	$date1 = FALSE;
}
$grid = array();

$check = json_decode($_GET['meter'], true);
for ($i = 1; $i <= $NUMMETER; $i++) {
	include "../config/config_met$i.php";
	if (!$check[$i]) {
		$check[$i] = false;
	}
}
if ($_GET["cumul"] == 1) {
	$cumul = true;
} else {
	$cumul = false;
}

if (file_exists("../data/csv/$date1")) {
	$file       = file("../data/csv/$date1");
	$contalines = count($file);

	$year  = substr($date1, 0, 4);
	$month = substr($date1, 4, 2);
	$day   = substr($date1, 6, 2);

	$unitlist = array();
	$metlist  = array();
	$nbrstack = 0;

	for ($i = 1; $i <= $NUMMETER; $i++) {
		$val_first24[$i] = null;
		$val_last24[$i]  = null;
		if ($check[$i]) {
			array_push($metlist, $i);
			$stackname[$nbrstack] = "${'METNAME'.$i}";
			$color[$nbrstack]     = "#${'COLOR'.$i}";
			if (!in_array(${'UNIT' . $i}, $unitlist)) {
				array_push($unitlist, ${'UNIT' . $i});
			}
			$nbrstack++;
		}
	}

	$stack = array(
		array()
	);

	for ($line_num = 2; $line_num < $contalines; $line_num++) {
		$pastline_num = $line_num - 1;
		$prevarray    = preg_split("/,/", $file[$pastline_num]);
		$array        = preg_split("/,/", $file[$line_num]);

		$prevhour    = substr($prevarray[0], 0, 2);
		$prevmin     = substr($prevarray[0], 3, 2);
		$prevUTCdate = strtotime($year . '-' . $month . '-' . $day . ' ' . $prevhour . ':' . $prevmin);
		$prevUTCdate *= 1000;

		$SDTE   = $array[0];
		$hour   = substr($SDTE, 0, 2);
		$minute = substr($SDTE, 3, 2);

		if ($line_num == ($contalines - 1) && $minute == '00' && $hour == '00') {
			$epochdate = strtotime(date("$year$month$day")) + 86400;
		} else {
			$epochdate = strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute);
		}
		$epochdate *= 1000;

		$nbrstack = 0;
		for ($i = 1; $i <= $NUMMETER; $i++) {
			if ($check[$i]) {
				$val_first = null;
				$val_last  = null;

				if (isset($array[$i])) {
					$val_first = trim($prevarray[$i]);
				}
				if (isset($prevarray[$i])) {
					$val_last = trim($array[$i]);
				}
				if (${'TYPE' . $i} != 'Sensor') { // Meter
					if (empty($val_first24[$i]) && !empty($val_first)) {
						$val_first24[$i] = $val_first;
					}
					if (!empty($val_last)) {
						$val_last24[$i] = $val_last;
					}
				}
				if (${'TYPE' . $i} != 'Sensor' && $line_num > 1) { // meter
					if (!$cumul) {
						if (!empty($val_first) && !empty($val_last) && $epochdate - $prevUTCdate == 300000) {
							if ($val_first <= $val_last) {
								$val_last -= $val_first;
							} else { // counter pass over
								$val_last += ${'PASSO' . $i} - $val_first;
							}

							$val_last = round($val_last, ${'PRECI' . $i});
						} else {
							$val_last = null;
						}

						$stack[$nbrstack][$line_num] = array(
							$epochdate,
							$val_last
						);
					} else { // cumulative
						if (!empty($val_first24[$i]) && !empty($val_last)) {
							if ($val_first24[$i] <= $val_last) {
								$val_last = $val_last - $val_first24[$i];
							} else { // counter pass over
								$val_last += ${'PASSO' . $i} - $val_first24[$i];
							}

							$stack[$nbrstack][$line_num] = array(
								$epochdate,
								$val_last
							);
						}
					}
					$nbrstack++;
				} elseif (${'TYPE' . $i} == 'Sensor' && $line_num > 1 && !empty($val_last)) { // sensor
					settype($val_last, 'float');
					$val_last                    = round($val_last, ${'PRECI' . $i});
					$stack[$nbrstack][$line_num] = array(
						$epochdate,
						$val_last
					);
					$nbrstack++;
				}

			}
		} // each meters
	} // End of foreach

	$dday = date($DATEFORMAT, mktime(0, 0, 0, $month, $day, $year));

	settype($titledate, "string");
	$title = "$lgDETAILEDOFTITLE $dday $titledate";

	$data    = array();
	$cntunit = count($unitlist);

	// // Return datas via json
	$j = 0;
	for ($i = 1; $i <= $NUMMETER; $i++) {
		if ($check[$i]) {
			settype($val_first24[$i], 'float');
			settype($val_last24[$i], 'float');

			if (${'TYPE' . $i} != 'Sensor') { // Meter
				if ($val_first24[$i] <= $val_last24[$i]) {
					$val_last24[$i] -= $val_first24[$i];
				} else { // counter pass over
					$val_last24[$i] += ${'PASSO' . $i} - $val_first24[$i];
				}
				$val_last24[$i] = number_format($val_last24[$i], ${'PRECI' . $i}, $DPOINT, $THSEP);
			}

			sort($stack[$j]);
			for ($h = 0; $h < $cntunit; $h++) {
				if ($unitlist[$h] == ${'UNIT' . $i}) {
					$thisyaxis = $h;
				}
			}
			$data[$j] = array(
				'name' => $stackname[$j],
				'data' => $stack[$j],
				'yAxis' => $thisyaxis,
				'type' => 'areaspline',
				'color' => $color[$j],
				'dashStyle' => 'Solid',
				'val24' => "($val_last24[$i] ${'UNIT' . $i})"
			);
			$j++;
		}
	}

	$jsonreturn = array(
		'data' => $data,
		'title' => $title
	);
} else {
	$jsonreturn = array(
		'data' => null,
		'title' => 'No Data'
	);
}
header("Content-type: application/json");
echo json_encode($jsonreturn);
?>
