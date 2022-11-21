<?php
/**
 * /srv/http/metern/programs/programmeter.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/config_main.php';
include '../config/config_layout.php';
date_default_timezone_set('UTC');
ini_set('serialize_precision', -1);
include '../languages/' . $LANG . '.php';

for ($i = 1; $i <= $NUMMETER; $i++) {
	include "../config/config_met$i.php";
	$val_first24[$i] = null;
	$val_last24[$i]  = null;
	$stack[$i]       = array();
	if (!isset(${'GRAPH_MET' . $i})) {
		${'GRAPH_MET' . $i} = 0;
	}
	if (!isset(${'FILL_MET' . $i})) {
		${'FILL_MET' . $i} = false;
	}
}
$dir    = '../data/csv/';
$output = array();
$output = glob($dir . '*.csv');
sort($output);
$cnt = count($output);

if ($cnt > 0) {
	$file       = file($output[$cnt - 1]);
	$contalines = count($file);

	$startplot = 0;
	$stopplot  = 0;
	$startline = 0;
	$y         = 0;

	// Yesterday
	date_default_timezone_set($DTZ);
	$yesterday = date('Ymd', time() - 60 * 60 * 24);
	$nowutc    = strtotime(date('Ymd H:i')) * 1000;
	date_default_timezone_set('UTC');

	if (file_exists($dir . "$yesterday.csv")) {
		$file = file($dir . "$yesterday.csv");

		$contalines2 = count($file) - 1;
		$prevyear    = substr($output[$cnt - 2], -12, 4);
		$prevmonth   = substr($output[$cnt - 2], -8, 2);
		$prevday     = substr($output[$cnt - 2], -6, 2);

		$startline = $contalines2 - 290 + $contalines; // (24*12)+ 2 headers
		if ($startline < 1) {
			$startline = 2;
		}

		for ($line_num = $startline; $line_num <= $contalines2; $line_num++) {
			$linearray    = preg_split("/,/", $file[$line_num]);
			$pastline_num = $line_num - 1;
			$prevarray    = preg_split("/,/", $file[$pastline_num]);

			$prevhour    = substr($prevarray[0], 0, 2);
			$prevmin     = substr($prevarray[0], 3, 2);
			$prevUTCdate = strtotime($prevyear . '-' . $prevmonth . '-' . $prevday . ' ' . $prevhour . ':' . $prevmin);
			$prevUTCdate *= 1000;

			$hour   = substr($linearray[0], 0, 2);
			$minute = substr($linearray[0], 3, 2);

			if ($line_num == $contalines2 && $hour == '00' && $minute == '00') { // today midnight
				$UTCdate = strtotime(date("$yesterday")) + 86400;
			} else {
				$UTCdate = strtotime($prevyear . '-' . $prevmonth . '-' . $prevday . ' ' . $hour . ':' . $minute);
			}
			$UTCdate *= 1000;

			for ($i = 1; $i <= $NUMMETER; $i++) {
				$val_first = null;
				$val_last  = null;

				if ($UTCdate - $prevUTCdate == 300000 && $nowutc - $UTCdate <= 86400000) { // 5 min sample & avoid older than 24h
					if (isset($prevarray[$i]) && isset($linearray[$i])) {
						$val_first = trim($prevarray[$i]);
						$val_last  = trim($linearray[$i]);
					}
					if (empty($val_first24[$i]) && !empty($val_first)) {
						$val_first24[$i] = $val_first;
					}
				}

			if (${'TYPE' . $i} != 'Sensor') { // meter
				if(!empty($val_first) && !empty($val_last)) {
					settype($val_first, 'float');
					settype($val_last, 'float');
					if ($val_first <= $val_last) {
						$val_last -= $val_first;
					} else { // counter pass over
						$val_last += ${'PASSO' . $i} - $val_first;
					}
					$val_last      = round($val_last, ${'PRECI' . $i});
				} 
				$stack[$i][$y] = array(
					$UTCdate,
					$val_last
				);
			} elseif (${'TYPE' . $i} == 'Sensor') { // sensor
				if (!empty($val_last)) {
				settype($val_last, 'float');
				$val_last      = round($val_last, ${'PRECI' . $i});
				} 
				$stack[$i][$y] = array(
					$UTCdate,
					$val_last
				);
			}
			
			}
			$y++;
		}
	}

	// Today
	$year  = substr($output[$cnt - 1], -12, 4);
	$month = substr($output[$cnt - 1], -8, 2);
	$day   = substr($output[$cnt - 1], -6, 2);
	$file  = file($output[$cnt - 1]);

	$fileUTCdate = strtotime(date("$year$month$day"));
	$todayUTC    = strtotime(date('Ymd'));

	$plotline = 1000 * $fileUTCdate; //plotline

	for ($line_num = 2; $line_num < $contalines; $line_num++) {
		$linearray    = preg_split("/,/", $file[$line_num]);
		$pastline_num = $line_num - 1;
		$prevarray    = preg_split("/,/", $file[$pastline_num]);

		$prevhour    = substr($prevarray[0], 0, 2);
		$prevmin     = substr($prevarray[0], 3, 2);
		$prevUTCdate = strtotime($year . '-' . $month . '-' . $day . ' ' . $prevhour . ':' . $prevmin);
		$prevUTCdate *= 1000;

		$hour    = substr($linearray[0], 0, 2);
		$minute  = substr($linearray[0], 3, 2);
		$UTCdate = strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute);
		$UTCdate *= 1000;

		for ($i = 1; $i <= $NUMMETER; $i++) {
			$val_first = null;
			$val_last  = null;

			if ($UTCdate - $prevUTCdate == 300000) {
				if (isset($prevarray[$i]) && isset($linearray[$i])) {
					$val_first = trim($prevarray[$i]);
					$val_last  = trim($linearray[$i]);
				}
				if (empty($val_first24[$i]) && !empty($val_first)) {
					$val_first24[$i] = $val_first;
				}
				if (!empty($val_last)) {
					$val_last24[$i] = $val_last;
				}
			}

			if (${'TYPE' . $i} != 'Sensor') { // meter
				if(!empty($val_first) && !empty($val_last)) {
					settype($val_first, 'float');
					settype($val_last, 'float');
					if ($val_first <= $val_last) {
						$val_last -= $val_first;
					} else { // counter pass over
						$val_last += ${'PASSO' . $i} - $val_first;
					}
					$val_last      = round($val_last, ${'PRECI' . $i});
				} 
				$stack[$i][$y] = array(
					$UTCdate,
					$val_last
				);
			} elseif (${'TYPE' . $i} == 'Sensor') { // sensor
				if (!empty($val_last)) {
				settype($val_last, 'float');
				$val_last      = round($val_last, ${'PRECI' . $i});
				} 
				$stack[$i][$y] = array(
					$UTCdate,
					$val_last
				);
			}
		}
		$y++;
	}

	if ($fileUTCdate == $todayUTC) {
		$title = '';
	} elseif ($fileUTCdate == strtotime(date('Ymd', strtotime('-1 day')))) {
		$title = stripslashes("$lgYESTERDAYTITLE");
	} else {
		$dday  = date($DATEFORMAT, $fileUTCdate);
		$title = stripslashes("$dday");
	}

	$graphlist = array();
	$unitlist  = array();

	$j = 0;
	for ($i = 1; $i <= $NUMMETER; $i++) {
		settype($val_first24[$i], 'float');
		settype($val_last24[$i], 'float');

		if (${'TYPE' . $i} != 'Sensor') { // Meter
			if ($val_first24[$i] <= $val_last24[$i]) {
				$val_last24[$i] -= $val_first24[$i];
			} else { // counter pass over
				$val_last24[$i] += ${'PASSO' . $i} - $val_first24[$i];
			}
		}
		if (${'PRICE' . $i} > 0) {
			if (${'TYPE' . $i} == 'Elect') {
				$money[$i] = number_format(($val_last24[$i] * ${'PRICE' . $i} / 1000), 2, $DPOINT, $THSEP);
			} else {
				$money[$i] = number_format(($val_last24[$i] * ${'PRICE' . $i}), 2, $DPOINT, $THSEP);
			}
			$money[$i] = "($money[$i]"."$CURS)";
		} else {
			$money[$i] = '';
		}
		if (${'TYPE' . $i} == 'Elect') {
			if ($val_last24[$i] <= 1000) {
				$prefix[$i]     = '';
				$val_last24[$i] = number_format($val_last24[$i], 0, $DPOINT, $THSEP);
			} elseif ($val_last24[$i] > 1000000) {
				$val_last24[$i] /= 1000000;
				$prefix[$i]     = 'M';
				$val_last24[$i] = number_format($val_last24[$i], 2, $DPOINT, $THSEP);
			} elseif ($val_last24[$i] > 1000) {
				$val_last24[$i] /= 1000;
				$prefix[$i]     = 'k';
				$val_last24[$i] = number_format($val_last24[$i], 2, $DPOINT, $THSEP);
			}
		} else {
			$val_last24[$i] = number_format($val_last24[$i], ${'PRECI' . $i}, $DPOINT, $THSEP);
			$prefix[$i]     = '';
		}

		$graphnum = ${'GRAPH_MET' . $i};
		if ($graphnum != 0) {
			if (!in_array($graphnum, $graphlist)) {
				$graphlist[]           = (int) $graphnum;
				$unitlist[$graphnum][] = ${'UNIT' . $i};
				$yaxis[$graphnum]      = (int) 0;

				$titlelist[$graphnum] = $title . ' ' . ${'METNAME' . $i} . " $val_last24[$i] $prefix[$i]"."${'UNIT'.$i} $money[$i]";
			} else {
				if (!in_array(${'UNIT' . $i}, $unitlist[$graphnum])) {
					$unitlist[$graphnum][] = ${'UNIT' . $i};
					$yaxis[$graphnum]++;
				}
				$titlelist[$graphnum] .= ' - ' . ${'METNAME' . $i} . " $val_last24[$i] $prefix[$i]"."${'UNIT'.$i} $money[$i]";
			}

			$cntunit = count($unitlist[$graphnum]);
			for ($h = 0; $h < $cntunit; $h++) {
				if (${'UNIT' . $i} == $unitlist[$graphnum][$h]) {
					$thisyaxis = $h;
				}
			}
		} else {
			$stack[$i] = array();
			$thisyaxis = 0;
		}
		sort($stack[$i]);

		if (!${'FILL_MET' . $i}) {
			$opa = 0.5;
		} else {
			$opa = 0;
		}

		$data[$j] = array(
			'name' => "${'METNAME'.$i}",
			'type' => 'areaspline',
			'fillOpacity' => $opa,
			'color' => "#${'COLOR'.$i}",
			'data' => $stack[$i],
			'yAxis' => $thisyaxis
		);
		$j++;
	}
	ksort($titlelist);
	$titlelist = array_values($titlelist);

	date_default_timezone_set($DTZ);
	$sun_info = date_sun_info($fileUTCdate, $LATITUDE, $LONGITUDE);
	$tstrtp   = date('Ymd H:i', $sun_info['sunset']); // today plotbands
	if (file_exists($dir . "$yesterday.csv")) {
		$sun_info2 = date_sun_info(strtotime("$prevyear-$prevmonth-$prevday"), $LATITUDE, $LONGITUDE);
		$ystrtp    = date('Ymd H:i', $sun_info2['sunset']); // yestd night plotbands
		$ystpp     = date('Ymd H:i', $sun_info['sunrise']);
	}
	date_default_timezone_set('UTC');

	if (file_exists($dir . "$yesterday.csv")) {
		$ystrtp = (strtotime($ystrtp)) * 1000;
		$ystpp  = (strtotime($ystpp)) * 1000;
	} else {
		$ystrtp = 0;
		$ystpp  = 0;
	}

	$tstrtp = (strtotime($tstrtp)) * 1000;
	$tstpp  = (strtotime($year . '-' . $month . '-' . $day . ' 23:59:59')) * 1000;

	$jsonreturn = array(
		'data' => $data,
		'title' => $titlelist,
		'plotline' => $plotline,
		'ystrtp' => $ystrtp,
		'ystpp' => $ystpp,
		'tstrtp' => $tstrtp,
		'tstpp' => $tstpp
	);

} else { // No data
	$graphlist = array();
	$j         = 0;
	for ($i = 1; $i <= $NUMMETER; $i++) {
		$data[$j] = array(
			'name' => "${'METNAME'.$i}",
			'type' => 'areaspline',
			'color' => "#${'COLOR'.$i}",
			'data' => null,
			'yAxis' => 0
		);

		$graphnum = ${'GRAPH_MET' . $i};
		if ($graphnum != 0) {
			if (!in_array($graphnum, $graphlist)) {
				$graphlist[]          = (int) $graphnum;
				$titlelist[$graphnum] = 'No data';
			}
		}
		$j++;
	}
	ksort($titlelist);
	$titlelist = array_values($titlelist);

	$jsonreturn = array(
		'data' => $data,
		'title' => $titlelist,
		'plotline' => 0,
		'ystrtp' => 0,
		'ystpp' => 0,
		'tstrtp' => 0,
		'tstpp' => 0
	);
}

header("Content-type: application/json");
echo json_encode($jsonreturn);
?>
