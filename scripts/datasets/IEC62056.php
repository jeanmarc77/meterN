<?php
function isvalid($id, $datareturn) //  IEC 62056 data set structure
{
	$regexp = "/^$id\(.+".'\x00'."*.+\)$/i"; //ID(VALUE*UNIT)
	if (preg_match($regexp, $datareturn)) {
		$datareturn = preg_replace("/^$id\(/i", '', $datareturn, 1); // VALUE*UNIT)
		$datareturn = preg_replace("/\*.+\)$/i", '', $datareturn, 1); // VALUE
		if(!isset($datareturn)) {
			$datareturn = null;
		}
	} else {
		$datareturn = null;
	}
	return $datareturn;
}
?>
