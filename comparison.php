<?php
/**
 * /srv/http/metern/comparison.php
 *
 * @package default
 */


include 'styles/globalheader.php';
include 'config/config_main.php';

if (!empty($_POST['met_num'])) {
	$metnum = $_POST['met_num'];
} else {
	$metnum = 1;
}
if (!empty($_POST['whichmonth']) && is_numeric($_POST['whichmonth'])) {
	$whichmonth = $_POST['whichmonth'];
} else {
	$whichmonth = date('n', time() - 60 * 60 * 24);
}
if (!empty($_POST['whichyear']) && is_numeric($_POST['whichyear'])) {
	$whichyear = $_POST['whichyear'];
} else {
	$whichyear = date("Y");
}
if (!empty($_POST['comparemet'])) {
	$comparemet = $_POST['comparemet'];
} else {
	$comparemet = 1;
}
if (!empty($_POST['comparemonth']) && is_numeric($_POST['comparemonth'])) {
	$comparemonth = $_POST['comparemonth'];
} else {
	$comparemonth = date('n', time() - 60 * 60 * 24);
}
if ($comparemonth == 13 || $whichmonth == 13) {
	$comparemonth = $whichmonth;
}
if (!empty($_POST['compareyear']) && is_numeric($_POST['compareyear'])) {
	$compareyear = $_POST['compareyear'];
} else {
	$compareyear = date('Y', time() - 60 * 60 * 24);
	$compareyear = (int) $compareyear - 1;
}
include "config/config_met$metnum.php";
include "config/config_met$comparemet.php";
if (${'TYPE' . $comparemet} != ${'TYPE' . $metnum}) {
	$comparemet = $metnum;
}
if (!file_exists('data/meters/' . $metnum . ${'METNAME' . $metnum} . $whichyear . '.csv')) {
	$whichyear = date("Y");
}
if (!file_exists('data/meters/' . $comparemet . ${'METNAME' . $comparemet} . $compareyear . '.csv')) {
	$compareyear = date('Y', time() - 60 * 60 * 24);
}

$dir   = 'data/meters/';
$stack = glob($dir . "$metnum*.csv");
sort($stack);
$xyears = count($stack);
$output = array();

for ($i = 0; $i < $xyears; $i++) {
	$option = substr($stack[$i], -8, 4);
	if (file_exists('data/meters/' . $comparemet . ${'METNAME' . $comparemet} . $option . '.csv')) {
		array_push($output, $stack[$i]);
	}
}
sort($output);
$xyears = count($output);
echo "
<table width='95%' border=0 align=center cellpadding=8>
<tr><td>
<form method='POST' action='comparison.php'>";
echo "$lgCHOOSEMET: <select name='met_num' onchange='this.form.submit()'>";
for ($i = 1; $i <= $NUMMETER; $i++) {
	include "config/config_met$i.php";
	if (${'TYPE' . $metnum} != 'Sensor') {
		if ($metnum == $i) {
			echo "<option value='$i' SELECTED>";
		} else {
			echo "<option value='$i'>";
		}
		echo "${'METNAME'.$i}</option>";
	}
}
echo "</select>
<select name='whichmonth' onchange='this.form.submit()'>";
for ($i = 1; $i <= 13; $i++) {
	if ($whichmonth == $i) {
		echo "<option SELECTED value='$i'>";
	} else {
		echo "<option value='$i'>";
	}
	echo "$lgMONTH[$i]</option>";
}
echo "
</select>
<select name='whichyear' onchange='this.form.submit()'>";
$newy = date("dm");
if ($xyears == 0 || $newy == "0101") {
	$newy = date("Y");
	echo "<option>$newy</option>";
}
for ($i = ($xyears - 1); $i >= 0; $i--) {
	$output[$i] = str_replace("$dir", '', "$output[$i]");
	$option     = substr($output[$i], -8, 4);
	if ($whichyear == $option) {
		echo "<option SELECTED>";
	} else {
		echo "<option>";
	}
	echo "$option</option>";
}
echo "</select>
$lgCOMPAREDWITH
<select name='comparemet' onchange='this.form.submit()'>";
for ($i = 1; $i <= $NUMMETER; $i++) {
	include "config/config_met$i.php";
	if (${'TYPE' . $metnum} == ${'TYPE' . $i} && ${'TYPE' . $metnum} != 'Other') { // same type
		if ($comparemet == $i) {
			echo "<option value='$i' SELECTED>";
		} else {
			echo "<option value='$i'>";
		}
		echo "${'METNAME'.$i}</option>";
	}
}
echo "</select>
<select name='comparemonth' onchange='this.form.submit()'>";
if ($comparemonth != 13) {
	for ($i = 1; $i <= 12; $i++) {
		if ($comparemonth == $i) {
			echo "<option SELECTED value='$i'>";
		} else {
			echo "<option value='$i'>";
		}
		echo "$lgMONTH[$i]</option>";
	}
} else {
	echo "<option SELECTED value=13>$lgMONTH[13]</option>";
}

echo "
</select>
<select name='compareyear' onchange='this.form.submit()'>";
for ($i = ($xyears - 1); $i >= 0; $i--) {
	$output[$i] = str_replace("$dir", '', "$output[$i]");
	$option     = substr($output[$i], -8, 4);
	if ($compareyear == $option) {
		echo "<option SELECTED>";
	} else {
		echo "<option>";
	}
	echo "$option</option>";
}
echo "
</select>
</form>
</td></tr>
</table>
<script type='text/javascript'>

$(document).ready(function() {
Highcharts.setOptions({
global: {
useUTC: true
},
lang: {
decimalPoint: '$DPOINT',
thousandsSep: '$THSEP',
months: ['";
for ($i = 1; $i < 12; $i++) {
	echo "$lgMONTH[$i]','";
}
echo "$lgMONTH[12]'],
shortMonths: ['";
for ($i = 1; $i < 12; $i++) {
	echo "$lgSMONTH[$i]','";
}
echo "$lgSMONTH[12]'],
weekdays: ['";
for ($i = 1; $i < 7; $i++) {
	echo "$lgWEEKD[$i]','";
}
echo "$lgWEEKD[7]'],
drillUpText: '$lgDRILLUP',
loading: '$lgLOAD',
printChart: '$lgPRINT',
resetZoom: '$lgRESETZ'
}
});

var Mychart, options = {
chart: {
type: 'spline',
backgroundColor: null,
zoomType: 'xy',
resetZoomButton: {
                position: {
                    align: 'right',
                    verticalAlign: 'top'
                }
},
spaceRight:20
},
colors: [
	'#4572A7',
	'#AA4643',
	'#89A54E',
	'#80699B',
	'#3D96AE',
	'#DB843D',
	'#92A8CD',
	'#A47D7C',
	'#B5CA92'
],
credits: {
enabled: false
},";
if ($metnum == $comparemet) {
	$title = "${'METNAME' . $metnum}: $lgCOMPARETITLE $lgMONTH[$whichmonth] $whichyear $lgWITH $lgMONTH[$comparemonth] $compareyear";
} else {
	$title = "${'METNAME' . $metnum}: $lgCOMPARETITLE $lgMONTH[$whichmonth] $whichyear $lgWITH ${'METNAME' . $comparemet} $lgMONTH[$comparemonth] $compareyear";
}
echo "
title: {
  text: '$title'
},
subtitle: { text: '$lgCOMPARESUBTITLE' },
xAxis: [{
type: 'datetime',
maxZoom: 43200000
  }, {
type: 'datetime',
maxZoom: 43200000
}] ,
yAxis: [{
min: 0,
labels: { formatter: function() { return this.value +";
if (${'TYPE' . $metnum} != 'Elect') {
	echo "'${'UNIT' . $metnum}';}},";
} else {
	echo "'kWh';}},";
}
echo "
title: { text: '$lgCUMVAL'}
},
],
tooltip: {
formatter: function() {
    if ((Mychart.series[0].name== this.series.name)&& (Mychart.series[0].name!=Mychart.series[1].name)){
  var s = '';
  s += '<b>' + Highcharts.dateFormat('%A %e %b %Y',this.x) + ' :</b> ' + Highcharts.numberFormat(this.y,'${'PRECI' . $metnum}') +";
if (${'TYPE' . $metnum} != 'Elect') {
	echo " '${'UNIT' . $metnum}<br/>'";
} else {
	echo " 'kWh<br/>'";
}
echo "
  var secondSeriesLen =  Mychart.series[1].data.length;
  var daynum = ((this.x-Mychart.series[0].data[0].x)/86400000)+1;
  if(daynum<=secondSeriesLen) {
	var perf = (((this.y * 100)/(Mychart.series[1].data[daynum-1].y))-100).toFixed(1);
  } else {
    var firstSeriesLen = Mychart.series[0].data.length;
    var secondSeriesMax = Mychart.series[1].data[secondSeriesLen-1].y;
	var perf = (((this.y * 100 *firstSeriesLen)/(secondSeriesMax*daynum))-100).toFixed(1);
	perf = '~' + perf;
  }
  s += '$lgDIFF: '+perf+ '%';

  return s;
   } else {
      return '<b>' + Highcharts.dateFormat('%A %e %b %Y',this.x) + ' :</b> ' + Highcharts.numberFormat(this.y,'${'PRECI' . $metnum}') + ";
if (${'TYPE' . $metnum} != 'Elect') {
	echo " '${'UNIT' . $metnum}<br/>'";
} else {
	echo " 'kWh<br/>'";
}
echo "
   }
},
crosshairs: true
},
legend: {
layout: 'horizontal',
align: 'center',
floating: false,
backgroundColor: '#FFFFFF'
},
exporting: {
filename: 'meterN-chart',
width: 1200
},
series: []
};
";

// transmit the value to proceed them via _GET
$destination = "programs/programcomparison.php?whichmonth=$whichmonth&whichyear=$whichyear&comparemet=$comparemet&comparemonth=$comparemonth&compareyear=$compareyear";

echo "
Mychart= Highcharts.chart('container',options);
Mychart.showLoading();
var metnum = $metnum;
$.getJSON('$destination', { metnum: $metnum }, function(data)
{
options.series = data;
Mychart= Highcharts.chart('container',options);
Mychart.hideLoading();
});
});
</script>
"; //End of echo
?>

<table width="100%" border=0 align=center cellpadding="0">
<tr><td><div id="container" style="width: 95%; height: 450px"></div></td></tr>
</table>
<?php
include "styles/$STYLE/footer.php";
?>
