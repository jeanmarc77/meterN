<?php
/**
 * /srv/http/metern/readings.php
 *
 * @package default
 */


include 'styles/globalheader.php';
include 'config/config_main.php';
$output  = array();
$dir     = 'data/meters/';
$output  = glob($dir . '*.csv');
$cnt     = count($output);
$metlist = array();

if ($cnt > 0) {
	for ($i = 1; $i <= $NUMMETER; $i++) {
		include "config/config_met$i.php";
		if (isset($_POST["check$i"])) {
			$check[$i] = true;
			$metlist[] = $i;
		} else {
			$check[$i] = false;
		}
	}
	$metcnt = count($metlist);
	if ($metcnt == 0) { //Nothing selected
		$check[1]   = true;
		$metlist[0] = 1;
		$metcnt     = 1;
	}
	$getvalue = json_encode($check);
	echo "<table width='95%' border=0 align=center cellpadding=8>
<tr><td>
<form method='POST' action='readings.php'>
$lgCHOOSEMET: ";
	for ($i = 1; $i <= $NUMMETER; $i++) {
		include "config/config_met$i.php";
		if (${'TYPE' . $i} != 'Sensor') {
			echo "<input type='checkbox' name='check$i' value='on'";
			if ($check[$i]) {
				echo ' checked';
			}
			echo ">${'METNAME'.$i} ";
		}
	}
	echo "&nbsp;<input type='submit' value='   $lgOK   '>
</form>
</td></tr>
</table>

<script type=\"text/javascript\">
$(document).ready(function() {
Highcharts.setOptions({
global: {useUTC: true},
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

var defaultTitle = ";
	if ($metcnt == 1) {
		echo "\"$lgCONSUTITLE: ${'METNAME'.$metlist[0]}\"";
	} else {
		echo "\"$lgCONSUTITLE\"";
	}
	echo ",prevPointTitle = null;

var Mychart, options = {
        chart: {
                type: 'column',
                backgroundColor: null,
				events: {
					drilldown: function (e) {
						this.setTitle({
							text: e.point.title
						});
						if (!prevPointTitle) {
							prevPointTitle = e.point.title;
						}
					},
					drillup: function (e) {
						if (this.series[0].options._levelNumber==1) {
							this.setTitle({
								text: defaultTitle
							});
							prevPointTitle = defaultTitle;
						} else {
							this.setTitle({
								text: prevPointTitle
							});
						}
					}
				},
            },
			title: {
				text: defaultTitle,
				style: {fontSize: '1em'}
			},
            subtitle: {text: '$lgCONSUSUBTITLE'},
            xAxis: {
            type: 'datetime'
            },
";
	$yaxislist = array();
	for ($i = 1; $i <= $NUMMETER; $i++) {
		if ($check[$i] == 'on') {
			if (!in_array(${'UNIT' . $i}, $yaxislist)) {
				array_push($yaxislist, ${'UNIT' . $i});
			}
		}
	}
	$conta = count($yaxislist);
	echo "\t\tyAxis: [";
	for ($i = 0; $i < $conta; $i++) {
		echo "{
\t\tlabels: { formatter: function() { return this.value +'$yaxislist[$i]';}},
\t\ttitle: { text: '$yaxislist[$i]'}
\t\t}";
		if ($i < $conta - 1) {
			echo ',';
		}
	}
	echo "],
		plotOptions: {
			series: {
				borderWidth: 1,
				dataLabels: {
					enabled: true,
					formatter:function() {
						";
	$y = 0;
	for ($i = 1; $i <= $metcnt; $i++) {
		if ($i == 1) {
			echo "if";
		} else {
			echo " else if";
		}
		echo "(this.series.index==$y) {\n";
		if (${'TYPE' . $metlist[$y]} == 'Elect') {
			echo "\t\t\t\t\t\treturn Highcharts.numberFormat(this.y,2) + ' kWh';\n";
		} else {
			echo "\t\t\t\t\t\treturn Highcharts.numberFormat(this.y,${'PRECI'. $metlist[$y]}) + ' ${'UNIT'. $metlist[$y]}';\n";
		}
		echo "\t\t\t\t\t\t}";
		$y++;
	}
	echo "\n\t\t\t\t\t}\n\t\t\t\t},point: {
					events: {
						click: function(event) {
							var point = this;
								if (point.y) {
									if (confirm('$lgSHOWDETAIL')) {
									window.location = 'detailed.php?meter=$getvalue&date2='+this.x;
									}
								}
						}
					}
				}
			},
		column: {
		  cursor: 'pointer'
				}
		},
		drilldown: {
        allowPointDrilldown: false
		},
        tooltip: {
			formatter: function() {
				if (Mychart.series[0].options._levelNumber==1) {
				s= '<b>'+ Highcharts.dateFormat(' %B %Y', this.x);
				} else if (Mychart.series[0].options._levelNumber==2) {
				s= '<b>' +Highcharts.dateFormat(' %a. %d %B %Y', this.x);
				} else {
				s=  '<b>'+ this.series.name+ Highcharts.dateFormat(' %Y', this.x);
				}
				s+= '</b><br>';
";
	$y = 0;
	for ($i = 1; $i <= $metcnt; $i++) {
		if ($i == 1) {
			echo "\t\t\t\tif";
		} else {
			echo " else if";
		}
		echo "(this.series.index==$y) {\n";
		if (${'TYPE' . $metlist[$y]} == 'Elect') {
			echo "\t\t\t\ts+= '<b>'+ Highcharts.numberFormat(this.y,2) + ' kWh</b>';\n";
			if (${'PRICE' . $metlist[$y]} > 0) {
				echo "\t\t\t\ts += ' (' + Highcharts.numberFormat((this.y*${'PRICE'. $metlist[$y]}),1) + '$CURS)';\n";
			}
		} else {
			echo "\t\t\t\ts+= '<b>'+ Highcharts.numberFormat(this.y,${'PRECI'. $metlist[$y]}) + ' ${'UNIT'. $metlist[$y]}</b>';\n";
			if (${'PRICE' . $metlist[$y]} > 0) {
				echo "\t\t\t\ts += ' (' + Highcharts.numberFormat((this.y*${'PRICE'. $metlist[$y]}),1) + '$CURS)';\n";
			}
		}
		echo "\t\t\t\t}";
		$y++;
	}
	echo "
			return s;
			}
		 },
  exporting: {
  filename: 'meterN-chart',
  width: 1200
  },
  credits: {
  enabled: false
  },
    series: []
 };
var meter = '$getvalue';
Mychart= Highcharts.chart('container',options);

Mychart.showLoading();
$.getJSON('programs/programreadings.php', { meter: meter }, function(JSONResponse) {
  options.series = JSONResponse.series;
  options.drilldown.series = JSONResponse.drilldown.series;
  Mychart= Highcharts.chart('container',options);
  Mychart.hideLoading();
});
});
</script>";
} else {
	echo '<br>No data';
}
echo "
<table width='100%' border=0 align=center cellpadding=0>
<tr><td><div id='container' style='width: 95%; height: 450px'></div></td></tr>
</table>
";

include "styles/$STYLE/footer.php";
?>
