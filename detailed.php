<?php
/**
 * /srv/http/metern/detailed.php
 *
 * @package default
 */


include 'styles/globalheader.php';
include 'config/config_main.php';

$output = glob('data/csv/*.csv');
sort($output);
$contalogs = count($output);
$ollog     = $output[0];
$lstlog    = $output[$contalogs - 1];
$startdate = (substr($ollog, -12, 4)) . ',' . (substr($ollog, -8, 2)) . '-1,' . (substr($ollog, -6, 2));
$stopdate  = (substr($lstlog, -12, 4)) . ',' . (substr($lstlog, -8, 2)) . '-1,' . (substr($lstlog, -6, 2));
echo "
<script>
  $(function() {
	$('#datepickid' ).datepicker({ dateFormat: 'dd/mm/yy' ,minDate: new Date($startdate), maxDate: new Date($stopdate)});
	$('#oneDayBack').click(function() {
		var new_dateb = $('#datepickid').datepicker('getDate');
		new_dateb.setDate(new_dateb.getDate() - 1);
		$('#datepickid').datepicker('setDate', new_dateb);
	});
	$('#oneDayFwd').click(function() {
		var new_datef = $('#datepickid').datepicker('getDate');
		new_datef.setDate(new_datef.getDate() + 1);
		$('#datepickid').datepicker('setDate', new_datef);
	});
    });
</script>\n";

$regexp = "/[0-9]{1,2}+\/[0-9]{1,2}+\/[0-9]{4}/";
if (!empty($_POST['date1']) && preg_match($regexp, $_POST['date1'])) {
	$date1 = htmlspecialchars($_POST['date1'], ENT_QUOTES, 'UTF-8');
} else {
	$date1 = (substr($lstlog, -6, 2)) . '/' . (substr($lstlog, -8, 2)) . '/' . (substr($lstlog, -12, 4));
}
if (!empty($_GET['date2']) && is_numeric($_GET['date2'])) {
	$date2 = htmlspecialchars($_GET['date2'], ENT_QUOTES, 'UTF-8');
	$ts    = strftime("%s", floor($date2 / 1000));
	$date1 = date('d/m/Y', $ts);
}
if (isset($_GET['meter'])) {
	$check = json_decode($_GET['meter'], true);
}
for ($i = 1; $i <= $NUMMETER; $i++) {
	include "config/config_met$i.php";
	if (!isset($check[$i])) {
		$check[$i] = false;
	}
	if (isset($_POST["check$i"]) || $check[$i]) {
		$check[$i] = true;
	} else {
		$check[$i] = false;
	}
}
$metcnt = count(array_filter($check));
if ($metcnt == 0) { //Nothing selected
	$check[1] = true;
}
$getvalue = json_encode($check);
if (isset($_POST["cumul"])) {
	$cumul = true;
} else {
	$cumul = false;
}

$titledate = substr($date1, 0, 10);
$csvdate1  = (substr($date1, 6, 4)) . (substr($date1, 3, 2)) . (substr($date1, 0, 2)) . ".csv";
?>
<script type="text/javascript">

$(document).ready(function()
{
Highcharts.setOptions({
global: {
useUTC: true
},
lang: {
decimalPoint: '<?php
echo $DPOINT;
?>',
thousandsSep: '<?php
echo "$THSEP',
drillUpText: '$lgDRILLUP',
loading: '$lgLOAD',
printChart: '$lgPRINT',
resetZoom: '$lgRESETZ'
";
?>
}
});

var Mychart, options = {
chart: {
backgroundColor: null,
zoomType: 'xy',
resetZoomButton: {
                position: {
                    align: 'right',
                    verticalAlign: 'top'
                }
},
loading: {
 labelStyle: { top: '45%' },
 style: { backgroundColor: null }
},
spaceRight:20
},
credits: {enabled: false},
<?php
echo "
subtitle: { text: '$lgDETAILSUBTITLE' },
xAxis: {
type: 'datetime',
minRange: 300000,
dateTimeLabelFormats: {minute: '%H:%M'}
},
yAxis: [";
$yaxislist = array();
for ($i = 1; $i <= $NUMMETER; $i++) {
	if ($check[$i]) {
		if (!in_array(${'UNIT' . $i}, $yaxislist)) {
			array_push($yaxislist, ${'UNIT' . $i});
		}
	}
}
$conta = count($yaxislist);
$conta--;
for ($i = 0; $i <= $conta; $i++) {
	echo "{
  labels: { formatter: function() { return this.value +'$yaxislist[$i]';}},
  title: { text: '$yaxislist[$i]'}
  }";
	if ($i != $conta) {
		echo ',';
	}
}
echo "],
tooltip: {
formatter: function() {
";
for ($i = 1; $i <= $NUMMETER; $i++) {
	settype(${'PRECI' . $i}, 'integer');
	if ($i == 1) {
		echo "if";
	} else {
		echo " else if";
	}
	echo "(this.series.name=='${'METNAME'.$i}') {
return '<b>' + Highcharts.numberFormat(this.y,'${'PRECI'.$i}') + '${'UNIT'.$i} </b>'";
	if (${'TYPE' . $i} == 'Elect' && $cumul != 'on') {
		echo " + '~' + Highcharts.numberFormat(this.y*12,'0') + 'W$lgAVG'";
	}
	echo "+ '<br>' + Highcharts.dateFormat('%H:%M', this.x)
}";
}
echo "else {return '<b>' + Highcharts.numberFormat(this.y,'1') + '</b><br/>' + Highcharts.dateFormat('%H:%M', this.x)
}
}
},
legend: {
layout: 'horizontal',
align: 'center',
floating: false,
backgroundColor: '#FFFFFF'
},
plotOptions: {
 areaspline: {
 threshold: null,
 softThreshold: true,
 fillOpacity: 0.3
 }
},
exporting: {
filename: 'meterN-chart',
width: 1200
},
series: []
};
var date1 = '$csvdate1';
var cumul= '$cumul';
var meter = '$getvalue';
Mychart= Highcharts.chart('container',options);
Mychart.showLoading();
  $.getJSON('programs/programdetailed.php', {date1: date1, meter: meter ,cumul:cumul}, function(JSONResponse)
{
options.series = JSONResponse.data;
Mychart= Highcharts.chart('container',options);
Mychart.setTitle({text: JSONResponse.title});
Mychart.hideLoading();
";
$j = 0;
for ($i = 1; $i <= $NUMMETER; $i++) {
	if ($check[$i]) {
		echo "document.getElementById('r24val$i').innerHTML = JSONResponse.data[$j].val24;
		   ";
		$j++;
	}
}
echo "
});
});
</script>

<div align='center'>
<div id='container' style='width: 100%; height: 400px'></div>
<br>
<table border=1 cellspacing=0 cellpadding=3 width='30%' align='center'>
<tr><td>
<form method='POST' action='detailed.php' name='chooseDateForm' id='chooseDateForm' action='#'>
$lgCHOOSEDATE :&nbsp;
<button id='oneDayBack'> < </button>
<input name='date1' id='datepickid' value='$date1' size=8 maxlength=10>
<button id='oneDayFwd'> > </button>";

for ($i = 1; $i <= $NUMMETER; $i++) {
	include "config/config_met$i.php";
	echo "<tr><td><input type='checkbox' name='check$i' value='on'";
	if ($check[$i]) {
		echo ' checked';
	}
	echo ">${'METNAME'.$i} <span id='r24val$i'></span></td></tr>";
}
echo "
</table>
<br>
<input type='submit' value='   $lgOK   '>
<input  type='checkbox' name='cumul' value='on'";
if ($cumul) {
	echo ' checked';
}
echo "> $lgCUMU
<br>
</form>
<br>";
include "styles/$STYLE/footer.php";
?>
