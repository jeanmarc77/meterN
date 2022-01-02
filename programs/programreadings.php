<?php
/**
 * /srv/http/metern/programs/programreadings.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/config_main.php';
date_default_timezone_set('UTC');
ini_set('serialize_precision', -1);
include '../languages/' . $LANG . '.php';

$getmeter = json_decode($_GET['meter'], true);
for ($i = 1; $i <= $NUMMETER; $i++) {
	if ($getmeter[$i]) {
		$metlist[] = $i;
	}
}
$metcnt           = count($metlist);
$unitlist         = array();
$totmeteryearlist = array();
for ($meter = 0; $meter < $metcnt; $meter++) { // Getting all years files
	$meteryearlist = array();
	$nummeter      = (int) $metlist[$meter];
	include "../config/config_met$nummeter.php";
	if (${'TYPE' . $nummeter} != 'Sensor') {
		$meteryearlist = glob('../data/meters/' . $nummeter . ${'METNAME' . $nummeter} . '*.csv'); // 1Elect*
		$yearscnt      = count($meteryearlist);
		for ($i = 0; $i < $yearscnt; $i++) {
			$year = substr($meteryearlist[$i], -8, 4);
			$year = (int) $year;
			if (!in_array($year, $totmeteryearlist)) {
				$totmeteryearlist[] = $year;
			}
		}
		if (!in_array(${'UNIT' . $nummeter}, $unitlist)) {
			array_push($unitlist, ${'UNIT' . $nummeter});
		}
	}
}
$cntunit = count($unitlist);
sort($totmeteryearlist);
$yearscnt = count($totmeteryearlist);
// Getting values
for ($meter = 0; $meter < $metcnt; $meter++) { // each meter
	$nummeter = (int) $metlist[$meter];
	include "../config/config_met$nummeter.php";
	if (${'TYPE' . $nummeter} != 'Sensor') {
		$conso_day = array();
		for ($i = 0; $i < $yearscnt; $i++) {
			$year     = $totmeteryearlist[$i];
			$filename = '../data/meters/' . $nummeter . ${'METNAME' . $nummeter} . $year . '.csv';

			if (file_exists($filename)) {
				$thefile    = file($filename);
				$contalines = count($thefile);

				for ($line_num = 0; $line_num < $contalines; $line_num++) {
					$array                                  = preg_split("/,/", $thefile[$line_num]);
					if (isset($array[1])){
						$month                                  = substr($array[0], 4, 2);
						$day                                    = substr($array[0], 6, 2);
						$month                                  = (int) ($month);
						$day                                    = (int) ($day);
						$conso_day[$year][$month][$day][$meter] = (float) $array[1];
						if (${'TYPE' . $nummeter} == 'Elect') {
							$conso_day[$year][$month][$day][$meter] /= 1000;
						}
					}
				} // end of looping through the file
			}

			if ($year == date('Y')) { // Add today
				$output = glob('../data/csv/*.csv');
				rsort($output);
				if (isset($output[0])) {
					$file                                   = file($output[0]);
					$month                                  = (int) substr($output[0], -8, 2);
					$day                                    = (int) substr($output[0], -6, 2);
					$contalines                             = count($file);
					$prevarray                              = preg_split('/,/', $file[1]);
					$linearray                              = preg_split('/,/', $file[$contalines - 1]);
					$val_first                              = (float) $prevarray[$nummeter];
					$conso_day[$year][$month][$day][$meter] = (float) $linearray[$nummeter];
					if (!empty($val_first) && !empty($conso_day[$year][$month][$day][$meter])) {
						if ($val_first <= $conso_day[$year][$month][$day][$meter]) {
							$conso_day[$year][$month][$day][$meter] -= $val_first;
						} else { // counter pass over
							$conso_day[$year][$month][$day][$meter] += ${'PASSO' . $nummeter} - $val_first;
						}
					} else {
						$conso_day[$year][$month][$day][$meter] = 0;
					}
					if (${'TYPE' . $nummeter} == 'Elect') {
						$conso_day[$year][$month][$day][$meter] /= 1000;
					}
				}
			} // end of today

			$conso_y[$year][$meter] = 0;
			for ($h = 1; $h <= 12; $h++) { // Fill blanks dates and drilldowndays
				$conso_m[$year][$h][$meter] = 0;
				$daythatm                   = cal_days_in_month(CAL_GREGORIAN, $h, $year);
				$day                        = 0;
				for ($j = 1; $j <= $daythatm; $j++) {
					$epochdate = strtotime($h . '/' . $j . '/' . $year) * 1000;
					if (!isset($conso_day[$year][$h][$j][$meter])) {
						$conso_day[$year][$h][$j][$meter]       = 0;
						$drilldowndays[$meter][$year][$h][$day] = array(
							$epochdate,
							0
						);
					} else {
						if (${'TYPE' . $nummeter} == 'Elect') {
							$dayval = round($conso_day[$year][$h][$j][$meter], 3);
						} else {
							$dayval = round($conso_day[$year][$h][$j][$meter], ${'PRECI' . $nummeter});
						}
						$drilldowndays[$meter][$year][$h][$day] = array(
							$epochdate,
							$dayval
						);
					}
					$conso_m[$year][$h][$meter] += $conso_day[$year][$h][$j][$meter];
					$conso_y[$year][$meter] += $conso_day[$year][$h][$j][$meter];
					$day++;
				}
				if (${'TYPE' . $nummeter} == 'Elect') {
					$conso_m[$year][$h][$meter] = round($conso_m[$year][$h][$meter], 3);
				} else {
					$conso_m[$year][$h][$meter] = round($conso_m[$year][$h][$meter], ${'PRECI' . $nummeter});
				}
			}
			if (${'TYPE' . $nummeter} == 'Elect') {
				$conso_y[$year][$meter] = round($conso_y[$year][$meter], 3);
			} else {
				$conso_y[$year][$meter] = round($conso_y[$year][$meter], ${'PRECI' . $nummeter});
			}
		} // each years
	}
} // each meter

// Topseries
for ($meter = 0; $meter < $metcnt; $meter++) { // each meter
	$nummeter = (int) $metlist[$meter];
	for ($h = 0; $h < $cntunit; $h++) {
		if ($unitlist[$h] == ${'UNIT' . $nummeter}) {
			$yaxis[$nummeter] = $h;
		}
	}
	if ($metcnt == 1) {
		$title = "${'METNAME'.$nummeter}";
	} else {
		$title = "$lgCONSUTITLE";
	}
	$topseries[$meter] = array(
		'name' => "${'METNAME'.$nummeter}",
		'title' => "${'METNAME'.$nummeter}",
		'color' => "#${'COLOR'.$nummeter}",
		'yAxis' => $yaxis[$nummeter],
		'keys' => array(
			'x',
			'y',
			'drilldown',
			'title'
		)
	);

	for ($i = 0; $i < $yearscnt; $i++) { // years
		$year = $totmeteryearlist[$i];
		if ($metcnt == 1) {
			$title = "${'METNAME'.$nummeter} $year: ";
			if (${'TYPE' . $nummeter} == 'Elect') {
				$title .= number_format($conso_y[$year][$meter], 3, $DPOINT, $THSEP);
				$title .= " kWh";
			} else {
				$title .= number_format($conso_y[$year][$meter], ${'PRECI' . $nummeter}, $DPOINT, $THSEP);
				$title .= " ${'UNIT'.$nummeter}";
			}
			if (${'PRICE' . $nummeter} > 0) {
				$money = number_format(($conso_y[$year][$meter] * ${'PRICE' . $nummeter}), 1, $DPOINT, $THSEP);
				$title .= "/$money $CURS";
			}
		} else {
			$title = "$lgCONSUTITLE $year";
		}
		$epochdate                   = strtotime('1/1/' . $year) * 1000;
		$topseries[$meter]['data'][] = array(
			$epochdate,
			$conso_y[$year][$meter],
			$meter . 'y' . $year, //1y2015
			$title
		);
	} // years
} // meter

// Preparing drilldowns
for ($i = 0; $i < $yearscnt; $i++) { // years
	$year  = $totmeteryearlist[$i];
	$month = 0;
	for ($h = 1; $h <= 12; $h++) { // drilldownmonths
		for ($meter = 0; $meter < $metcnt; $meter++) { // meter
			$nummeter = (int) $metlist[$meter];

			if ($metcnt == 1) {
				$title = "${'METNAME'.$nummeter} $lgMONTH[$h] $year: ";
				if (${'TYPE' . $nummeter} == 'Elect') {
					$title .= number_format($conso_m[$year][$h][$meter], 3, $DPOINT, $THSEP);
					$title .= ' kWh';
				} else {
					$title .= number_format($conso_m[$year][$h][$meter], ${'PRECI' . $nummeter}, $DPOINT, $THSEP);
					$title .= " ${'UNIT'.$nummeter}";
				}
				if (${'PRICE' . $nummeter} > 0) {
					$money = number_format(($conso_m[$year][$h][$meter] * ${'PRICE' . $nummeter}), 1, $DPOINT, $THSEP);
					$title .= "/$money $CURS";
				}
			} else {
				$title = "$lgCONSUTITLE $lgMONTH[$h] $year";
			}
			$epochdate                      = strtotime($h . '/1/' . $year) * 1000;
			$drilldownmonths[$year][$month] = array(
				$epochdate,
				$conso_m[$year][$h][$meter],
				$meter . 'm' . $year . $h, //1m20158
				$title
			);
			// fill drilldown monthly
			$filldd[$meter][$year][]        = $drilldownmonths[$year][$month];
			$month++;
		} //meter
	} // month
} // each year

// Drilldown years
$ddcnt = 0;
for ($i = 0; $i < $yearscnt; $i++) { // years
	$year = $totmeteryearlist[$i];
	for ($meter = 0; $meter < $metcnt; $meter++) { // meter
		$nummeter                             = (int) $metlist[$meter];
		$drilldown['series'][$ddcnt]['id']    = $meter . 'y' . $year; //1y2015
		$drilldown['series'][$ddcnt]['name']  = "${'METNAME'.$nummeter} $year";
		$drilldown['series'][$ddcnt]['yAxis'] = $yaxis[$nummeter];
		$drilldown['series'][$ddcnt]['keys']  = array(
			'x',
			'y',
			'drilldown',
			'title'
		);
		$drilldown['series'][$ddcnt]['data']  = $filldd[$meter][$year];
		$ddcnt++;
	}
} // year

// Drilldown months
for ($i = 0; $i < $yearscnt; $i++) { // years
	$year = $totmeteryearlist[$i];
	for ($h = 1; $h <= 12; $h++) { // months
		for ($meter = 0; $meter < $metcnt; $meter++) { // meter
			$nummeter                             = (int) $metlist[$meter];
			$drilldown['series'][$ddcnt]['id']    = $meter . 'm' . $year . $h; //1m20158
			$drilldown['series'][$ddcnt]['name']  = "${'METNAME'.$nummeter} $lgSMONTH[$h] $year";
			$drilldown['series'][$ddcnt]['yAxis'] = $yaxis[$nummeter];
			$drilldown['series'][$ddcnt]['keys']  = array(
				'x',
				'y',
				'drilldown'
			);
			$drilldown['series'][$ddcnt]['data']  = $drilldowndays[$meter][$year][$h];
			$ddcnt++;
		}
	}
}

$jsonreturn = array(
	'series' => $topseries,
	'drilldown' => $drilldown
);
header("Content-type: application/json");
echo json_encode($jsonreturn);
?>
