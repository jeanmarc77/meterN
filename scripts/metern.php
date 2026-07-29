<?php
/**
 * /srv/http/metern/scripts/metern.php
 *
 * @package default
 */


include 'loadcfg.php';
while (true) { // To infinity ... and beyond!
	for ($metnum = 1; $metnum <= $NUMMETER; $metnum++) { // Meters/Sensors pooling
		///// Main memory
		$data        = file_get_contents($MEMORY);
		$memarray1st = $data;
		$memarray    = json_decode($data, true);

		///// Live memory
		$data         = file_get_contents($LIVEMEMORY);
		$livememarray = json_decode($data, true);

		// Reset for every meter: left over from the previous iteration it
		// would let one meter's reading raise an alert on the next one,
		// whenever that one has no $LIVECOMMAND of its own to run.
		$val_live = null;

		if (!${'SKIPMONITORING' . $metnum} && !empty(${'LIVECOMMAND' . $metnum})) {
			$val = null;
			exec(${'LIVECOMMAND' . $metnum}, $datareturn);
			$datareturn = trim(implode($datareturn));
			$val        = isvalid(${'LID' . $metnum}, $datareturn);
			// Only a real reading is worth comparing to a threshold:
			// isvalid() returns null when it rejects the response outright,
			// and '' is the reader's own "no fresh sample this cycle"
			// marker. Neither is a value, so neither may raise an alert -
			// nor clear one, see the latch below.
			$val_live = ($val !== null && $val !== '' && is_numeric($val)) ? (float) $val : null;
			if (isset($val)) {
				$livememarray['UTC'] = strtotime(date('Ymd H:i:s'));
				if ($val === '') {
					// The reader's own "no fresh sample this cycle" marker
					// (an IEC 62056 tuple with an empty value, eg. ID(*W)) -
					// not a communication failure, but there is nothing to
					// show either. Drop the key instead of writing a
					// fabricated 0: index.php already renders a missing key
					// as "err" (see its "typeof val === 'undefined'" check),
					// and dropping it also stops a stale reading from one
					// meter lingering under this key forever once dropped -
					// $livememarray is carried over from the previous cycle,
					// so leaving the old value in place would keep showing
					// it as if it were still current.
					unset($livememarray["${'METNAME'.$metnum}$metnum"]);
				} else {
					$livememarray["${'METNAME'.$metnum}$metnum"] = $val;
				}
				if (${'comlost' . $metnum} && ${'NORESPM' . $metnum}) {
					${'comlost' . $metnum} = false;
					$now = date($DATEFORMAT . ' H:i:s');
					logevents("$now\tConnection with #$metnum ${'METNAME'.$metnum} restored\n\n");
					if (!empty(${'POAKEY' . $metnum}) && !empty(${'POUKEY' . $metnum})) {
						$pushover = pushover(${'POAKEY' . $metnum}, ${'POUKEY' . $metnum}, "#metnum ${'METNAME' . $metnum} Warning", "Connection with ${'METNAME'.$metnum} restored\n\n");
					}
					if (!empty(${'TLGRTOK' . $metnum}) && !empty(${'TLGRCID' . $metnum})) {
						$telegram = telegram(${'TLGRTOK' . $metnum}, ${'TLGRCID' . $metnum}, "meterN Connection with #$metnum ${'METNAME'.$metnum} restored\n\n");
					}
				}
				if ($logc[$metnum]) {
					$logc[$metnum] = false;
				}
			} else {
				// isvalid() rejected the response outright: a real
				// communication failure, not just an empty reading. Same
				// reasoning as above - no key, no fabricated 0.
				unset($livememarray["${'METNAME'.$metnum}$metnum"]);
				$livememarray['UTC'] = '0';
				if ($LOGCOM && !$logc[$metnum]) {
					$logc[$metnum] = true;
					$now = date($DATEFORMAT . ' H:i:s');
					logevents("$now\tCommunication error(s) with #$metnum ${'METNAME'.$metnum}\n\n");
				}
			}
		} else {
			$val                 = '0';
			$livememarray['UTC'] = strtotime(date('Ymd H:i:s'));
			$livememarray["${'METNAME'.$metnum}$metnum"] = $val; // Live value
		}

		// Instant alert on a live value crossing a threshold - independent
		// of the daily WARNCONSOD check below, which only ever looks at the
		// running total for the day, not at what a meter is reporting right
		// now. Opt-in per meter via $WARNLIVE$metnum, read straight from
		// config_met$metnum.php: deliberately NOT wired into the admin
		// panel (admin_meter.php/admin_meter2.php) in this patch. Doing so
		// would mean admin_meter2.php stamps every saved meter with the
		// current $CFGmet, so admin.php would flag every existing
		// installation's meter configs as outdated until each one is
		// re-saved - a migration this patch has no need to force. Adding
		// the panel UI is a natural follow-up if this direction is wanted.
		if (!${'SKIPMONITORING' . $metnum} && isset(${'WARNLIVE' . $metnum}) && ${'WARNLIVE' . $metnum} != 0) {
			if (isset($val_live) && $val_live > ${'WARNLIVE' . $metnum}) {
				if (!${'livealert' . $metnum}) { // rising edge only, not every cycle spent above threshold
					${'livealert' . $metnum} = true;
					$now        = date($DATEFORMAT . ' H:i:s');
					$stringData = "$now\t#$metnum ${'METNAME'.$metnum} live value reached $val_live ${'LIVEUNIT'.$metnum}\n\n";
					logevents($stringData);
					if (!empty(${'POAKEY' . $metnum}) && !empty(${'POUKEY' . $metnum})) {
						$pushover = pushover(${'POAKEY' . $metnum}, ${'POUKEY' . $metnum}, "#$metnum ${'METNAME'.$metnum} Warning", $stringData);
					}
					if (!empty(${'TLGRTOK' . $metnum}) && !empty(${'TLGRCID' . $metnum})) {
						$telegram = telegram(${'TLGRTOK' . $metnum}, ${'TLGRCID' . $metnum}, "meterN $stringData");
					}
				}
			} elseif (isset($val_live)) {
				// Back under threshold with a real reading: ready to fire
				// again on the next excursion. A missing or rejected
				// reading ($val_live null) leaves the latch alone, so a
				// communication gap can never be misread as "back to
				// normal" and mask the second half of the same excursion.
				${'livealert' . $metnum} = false;
			}
		}

		$minute   = date('i');
		if (in_array($minute, $minlist) && !$memarray['5minflag']) { // 5 min jobs
			$memarray['5minflag'] = true;
			$today                = date('Ymd');

			for ($i = 1; $i <= $NUMMETER; $i++) { // For each meters
				$datareturn = null;
				$matches    = array();
				$giveup     = 0;
				$lastval    = null;

				if (!${'SKIPMONITORING' . $i}) {
					while (!isset($lastval) && $giveup < 3) { // Try 3 times
						exec(${'COMMAND' . $i}, $datareturn);
						$datareturn = trim(implode($datareturn));
						$lastval    = isvalid(${'ID' . $i}, $datareturn);
						sleep($giveup);
						$giveup++;
					}
					if (!is_numeric($lastval)) {
						$lastval = null;
					}
					if ($giveup > 2) {
						$now = date($DATEFORMAT . ' H:i:s');
						if (${'NORESPM' . $i} && !${'comlost' . $i}) {
							${'comlost' . $i} = true;
							logevents("$now\tConnection lost with #$i ${'METNAME'.$i}, missing 5' sample\n\n");
							if (!empty(${'POAKEY' . $i}) && !empty(${'POUKEY' . $i})) {
								$pushover = pushover(${'POAKEY' . $i}, ${'POUKEY' . $i}, "#$i ${'METNAME' . $i} Warning", "Connection lost with ${'METNAME'.$i}, missing 5' sample\n\n");
							}
							if (!empty(${'TLGRTOK' . $i}) && !empty(${'TLGRCID' . $i})) {
								$telegram = telegram(${'TLGRTOK' . $i}, ${'TLGRCID' . $i}, "meterN Connection lost with #$i ${'METNAME'.$i}, missing 5' sample\n\n");
							}
						} else {
							logevents("$now\tMissing #$i ${'METNAME'.$i} 5' sample\n\n");
						}
					}
				}
				if ($i == 1) {
					$PCtime      = date('H:i');
					$stringData5 = "$PCtime";
				}
				if (is_numeric($lastval) && ${'TYPE' . $i} != 'Sensor'
					&& ($lastval < 0 || (${'PASSO' . $i} > 0 && $lastval > ${'PASSO' . $i}))) {
					$now = date($DATEFORMAT . ' H:i:s');
					logevents("$now\tError #$i ${'METNAME'.$i} report a wrong value\n\n");
					$lastval = null;
				}

				$stringData5 .= ",$lastval";

				if (is_numeric($lastval)) {
					$memarray["Last$i"] = round((float) $lastval, ${'PRECI' . $i});
					if (!isset($memarray["First$i"])) {
						$memarray["First$i"] = $memarray["Last$i"];
					}
				}
			} // For each meters
			$stringData5 .= "\r\n";

			if (!file_exists($DATADIR . "csv/$today.csv")) { // Midnight or startup
				$yesterday = date('Ymd', time() - (60 * 60 * 24) + 30); // yesterday
				if ($PCtime == '00:00' && file_exists($DATADIR . "csv/$yesterday.csv")) {
					file_put_contents($DATADIR . "csv/$yesterday.csv", $stringData5, FILE_APPEND);
				}

				$stringData = "Time"; // Header line
				for ($i = 1; $i <= $NUMMETER; $i++) {
					if (isset($memarray["Last$i"])) {
						$memarray["First$i"] = $memarray["Last$i"];
					} else {
						$memarray["First$i"] = null;
					}
					$stringData .= ",${'METNAME'.$i}(${'UNIT'.$i})";
					${'comlost' . $i} = false;
				}
				$stringData .= "\r\n";
				file_put_contents($DATADIR . "csv/$today.csv", $stringData, FILE_APPEND);

				$csvlist = glob($DATADIR . 'csv/*.csv');
				sort($csvlist);
				$xdays = count($csvlist);

				if ($xdays > 1) { // previous day
					$lines      = file($csvlist[$xdays - 2]);
					$contalines = count($lines);
					$csvdate1   = substr($csvlist[$xdays - 2], -12, 8);
					$year       = (int) substr($csvlist[$xdays - 2], -12, 4); // For new year
					for ($i = 1; $i <= $NUMMETER; $i++) {
						$memarray["msgflag$i"] = false; // clear msgflag
						if (${'TYPE' . $i} != 'Sensor') {
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
							settype($val_last, 'float');
							settype($val_first, 'float');

							if ($val_first <= $val_last) {
								$val_last -= $val_first;
							} else { // counter pass over
								$val_last += ${'PASSO' . $i} - $val_first;
							}
							$val_last   = round($val_last, ${'PRECI' . $i});
							$stringData = "$csvdate1";
							$stringData .= ",$val_last\r\n";

							file_put_contents($DATADIR . 'meters/' . $i . ${'METNAME' . $i} . $year . '.csv', $stringData, FILE_APPEND);
						}
						// Report
						$adate = date('d');
						if ($adate == '01' && !empty(${'EMAIL' . $i})) {
							$y_year      = date('Y', time() - 60 * 60 * 24); // yesterday
							$y_month     = date('m', time() - 60 * 60 * 24);
							$mlines      = file($DATADIR . 'meters/' . $i . ${'METNAME' . $i} . $y_year . '.csv');
							$mcontalines = count($mlines);
							$j           = 0;
							for ($line_num = 0; $line_num < $mcontalines; $line_num++) {
								$array = preg_split('/,/', $mlines[$line_num]);
								$month = substr($array[0], 4, 2);
								if ($month == $y_month) {
									$month         = substr($array[0], 4, 2);
									$day           = substr($array[0], 6, 2);
									$dayname[$j]   = date($DATEFORMAT, mktime(0, 0, 0, $month, $day, $y_year));
									$conso_day[$j] = $array[1];
									$conso_day[$j] = round($conso_day[$j], ${'PRECI' . $i});
									$j++;
								}
							}
							$conso_month = number_format(array_sum($conso_day), ${'PRECI' . $i}, $DPOINT, $THSEP);
							$cnt         = count($dayname);
							$msg         = "${'METNAME'.$i}\t\t (${'UNIT'.$i})\r\n";
							for ($j = 0; $j < $cnt; $j++) {
								$conso_day[$j] = number_format($conso_day[$j], ${'PRECI' . $i}, $DPOINT, $THSEP);
								$msg .= "$dayname[$j]\t";
								$msg .= "$conso_day[$j]\r\n";
							}
							$msg .= "\r\n";
							$msg .= "$conso_month ${'UNIT'.$i} on $y_month/$y_year\r\n---\r\n";
							mail("${'EMAIL'.$i}", "meterN: ${'METNAME'.$i} Monthly $y_month report", $msg, "From: meterN <${'EMAIL'.$i}>");
						} // Report
					}
				} // previous day

				$stringData = date($DATEFORMAT . ' H:i:s') . "\tClean up "; // Morning cleanup
				$lines      = file('../data/events.txt');
				$cnt        = count($lines);
				if ($cnt >= $AMOUNTLOG) {
					array_splice($lines, $AMOUNTLOG);
					$file2 = fopen('../data/events.txt', 'w');
					fwrite($file2, implode('', $lines));
					fclose($file2);
					$stringData .= 'events log ';
				}
				if ($KEEPDDAYS != 0) {
					if ($xdays > $KEEPDDAYS) {
						$i = 0;
						while ($i < $xdays - $KEEPDDAYS) {
							unlink($csvlist[$i]);
							$i++;
						}
						$stringData .= "purging $i csv";
					}
				}
				logevents($stringData . "\n\n");
			} // Midnight
			file_put_contents($DATADIR . "csv/$today.csv", $stringData5, FILE_APPEND);
			
			for ($i = 1; $i <= $NUMMETER; $i++) { // Consumption/production sensor check
				$msgflag = (bool) $memarray["msgflag$i"];
				if (!$msgflag && !empty($memarray["First$i"]) && !empty($memarray["Last$i"])) {
					if (${'TYPE' . $i} != 'Sensor') { // Meter
						if ($memarray["First$i"] <= $memarray["Last$i"]) {
							$val_last = $memarray["Last$i"] - $memarray["First$i"];
						} else { // counter pass over
							$val_last = $memarray["Last$i"] + ${'PASSO' . $i} - $memarray["First$i"];
						}
					} else {
						$val_last = $memarray["Last$i"];
					}

					if ($val_last > ${'WARNCONSOD' . $i} && ${'WARNCONSOD' . $i} != 0 && !${'SKIPMONITORING' . $i}) {
						$memarray["msgflag$i"] = true;
						$now        = date($DATEFORMAT . ' H:i:s');
						$val_last   = number_format($val_last, ${'PRECI' . $i}, $DPOINT, $THSEP);
						$stringData = "#$i ${'METNAME'.$i} ";
						if (${'PROD' . $i} == 1) {
							$stringData .= "production have reached $val_last ${'UNIT'.$i}\n\n";
							$msg = "Production have reached $val_last ${'UNIT'.$i}\n\n";
							logevents("$now\t#$i ${'METNAME'.$i} production have reached $val_last ${'UNIT'.$i}\n\n");
						} elseif (${'PROD' . $i} == 2) {
							$stringData .= "consumption have reached $val_last ${'UNIT'.$i}\n\n";
							$msg = "Consumption have reached $val_last ${'UNIT'.$i}\n\n";
							logevents("$now\t#$i ${'METNAME'.$i} consumption have reached $val_last ${'UNIT'.$i}\n\n");
						} else {
							$stringData .= "have reached $val_last ${'UNIT'.$i}\n\n";
							$msg = "Reached $val_last ${'UNIT'.$i}\n\n";
							logevents("$now\t#$i ${'METNAME'.$i} have reached $val_last ${'UNIT'.$i}\n\n");							
						}

						if (!empty(${'POAKEY' . $i}) && !empty(${'POUKEY' . $i})) {
							$pushover = pushover(${'POAKEY' . $i}, ${'POUKEY' . $i}, "#$i ${'METNAME' . $i} Warning", $msg);
						}
						if (!empty(${'TLGRTOK' . $i}) && !empty(${'TLGRCID' . $i})) {
							$telegram = telegram(${'TLGRTOK' . $i}, ${'TLGRCID' . $i}, "meterN Warning #$i ${'METNAME'.$i} $msg");
						}
					}
				}
			} // Consumption/prod check
			include '../config/config_trigger.php';
		} // 5 min

		if (!in_array($minute, $minlist) && $memarray['5minflag']) { // Run once every 1,6,11,16,..
			$memarray['5minflag'] = false; // Reset 5minflag
		}

		$data = json_encode($memarray);
		if ($data != $memarray1st) { // Reduce write
			$data = json_encode($memarray);
			file_put_contents($MEMORY, $data);
		}

		$data = json_encode($livememarray);
		file_put_contents($LIVEMEMORY, $data);
	} // End meters pooling

	if ($NUMIND > 0) { // Indicators
		for ($i = 1; $i <= $NUMIND; $i++) {
			if (!empty(${'INDCOMMAND' . $i})) {
				$val = 0;
				exec(${'INDCOMMAND' . $i}, $datareturn);
				$datareturn                         = trim(implode($datareturn));
				$val                                = isvalid(${'INDID' . $i}, $datareturn);
				$ilivememarray["${'INDNAME'.$i}$i"] = $val;
			} else {
				$ilivememarray["${'INDNAME'.$i}$i"] = 0;
			}
		}

		$data = json_encode($ilivememarray);
		file_put_contents($ILIVEMEMORY, $data);
	} // end of indicator
	usleep($DELAY);
} // infinity
?>
