<?php
/**
 * /srv/http/metern/index.php
 *
 * @package default
 */

include 'config/memory.php';
if (!file_exists($MEMORY)) {
	header('Location: admin/');
} else {
include 'styles/globalheader.php';
}
include 'config/config_main.php';
include 'config/config_indicator.php';
include 'config/config_layout.php';

$graphlist = array();
$unitlist  = array();

echo "
<script type='text/javascript'>
function formatNum(num) {
if(!isNaN(num) && num != null){
  var str = num.toLocaleString('en-US');
  str = str.replace(/\./, 'x');
  str = str.replace(/,/g, '$THSEP');
  str = str.replace('x', '$DPOINT');
  return str;
} else {
  return num;
}
}
</script>
<script type='text/javascript'>
$(document).ready(function()
{
function updateit() {
$.getJSON('programs/programlive.php', function(json){
GRIDTOT=0;
CTOT=0;
PTOT=0;";

$housecons = false;
$houseprod = false;
$showlastd = false;
$livetab   = false;

for ($i = 1; $i <= $NUMMETER; $i++) {
	include "config/config_met$i.php";
	if (!isset(${'GRAPH_MET' . $i})) {
		${'GRAPH_MET' . $i} = 0;
	}
	if (!in_array(${'GRAPH_MET' . $i}, $graphlist) && ${'GRAPH_MET' . $i} != 0) {
		$graphlist[] = ${'GRAPH_MET' . $i};
	}
	if (${'LASTD_MET' . $i}) {
		$showlastd = true;
		$check[$i] = true; //detailed
	} else {
		${'LASTD_MET' . $i} = false;
		$check[$i] = false;
	}
	if (!empty(${'LIVECOMMAND' . $i})) {
		$livetab = true;
		echo "
val$i =json['${'METNAME'.$i}$i'];";
	}
	if (${'TYPE' . $i} == 'Elect' && !empty(${'LIVECOMMAND' . $i})) {
		echo "
if(isNaN(val$i)){
val$i = 0;
} else {
val$i = parseFloat(val$i);
}";
		if (${'PROD' . $i} == 2) {
			$housecons = true;
			echo "
CTOT+=val$i;";
		} elseif (${'PROD' . $i} == 1) {
			$houseprod = true;
			echo "
PTOT+=val$i;";
		}
	}
}

if ($houseprod && $housecons) {
	echo '
GRIDTOT=parseInt(CTOT-PTOT);
';
	$Yg2 = round(($POWER_MIN / 3), 0);
	$Yg1 = round($Yg2 * 2, 0);
	$Yg3 = 0;
	$Yg4 = round(($POWER_MAX / 3), 0);
	$Yg5 = round($Yg4 * 2, 0);

	$gcolor = array(
		'0DB44C',
		'2EC846',
		'94DE40',
		'F29D16',
		'F76415',
		'F10D17'
	);
} elseif ($houseprod && !$housecons) {
	echo '
GRIDTOT=parseInt(PTOT);
';
	$POWER_MIN = 0;
	$Yg1       = round(($POWER_MAX / 5), 0);
	$Yg2       = round($Yg1 * 2, 0);
	$Yg3       = round($Yg1 * 3, 0);
	$Yg4       = round($Yg1 * 4, 0);
	$Yg5       = round($Yg1 * 5, 0);

	$gcolor = array(
		'F10D17',
		'F76415',
		'F29D16',
		'94DE40',
		'2EC846',
		'0DB44C'
	);

} elseif (!$houseprod && $housecons) {
	echo '
GRIDTOT=parseInt(CTOT);
';
	$POWER_MIN = 0;
	$Yg1       = round(($POWER_MAX / 6), 0);
	$Yg2       = round($Yg1 * 2, 0);
	$Yg3       = round($Yg1 * 3, 0);
	$Yg4       = round($Yg1 * 4, 0);
	$Yg5       = round($Yg1 * 5, 0);

	$gcolor = array(
		'0DB44C',
		'2EC846',
		'94DE40',
		'F29D16',
		'F76415',
		'F10D17'
	);
}

for ($i = 1; $i <= $NUMMETER; $i++) {
	if (!empty(${'LIVECOMMAND' . $i}) && !${'SKIPMONITORING' . $i}) {
		echo "
if (typeof val$i === 'undefined') {
document.getElementById('rval$i').innerHTML = 'err';
} else {
	document.getElementById('rval$i').innerHTML = formatNum(val$i);
}";
	}
}
if ($NUMIND > 0) { // indicators
	echo "

$.getJSON('programs/programindicator.php', function(json){";
	for ($i = 1; $i <= $NUMIND; $i++) {
		if (!empty(${'INDCOMMAND' . $i})) {
			echo "
ival$i =json['${'INDNAME'.$i}$i'];
if (typeof ival$i === 'undefined') {
document.getElementById('rival$i').innerHTML = 'err';
} else {
	document.getElementById('rival$i').innerHTML = formatNum(ival$i);
}";
		}
	}
	echo '
	})';
} // indicators
echo '
})
}
updateit();
setInterval(updateit, 1000);
});
</script>';

sort($graphlist);
$cntgraph = count($graphlist);

echo "
<script type='text/javascript'>
$(document).ready(function()
{
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
});";

for ($i = 0; $i < $cntgraph; $i++) {
	$unitlist[$i] = array();
	echo "

var Mychart$i, options$i = {
chart: {
backgroundColor: null,
zoomType: 'xy',
         events: {
            load: function() {
              setInterval(function() {
               $.getJSON('programs/programmeter.php', function(JSONResponse){
        Mychart$i.setTitle({ text: JSONResponse.title[$i]});";
	$h = 0;
	for ($j = 0; $j < $NUMMETER; $j++) {
		$k = $j + 1;
		if (${'GRAPH_MET' . $k} == $graphlist[$i]) {
			echo "\n\t\tMychart$i.series[$h].update(JSONResponse.data[$j], true);";
			$h++;
		}
	}
	echo "\n\t\tMychart$i.redraw();
               });
               }, 60000);
            }
         }
},
loading: {
 labelStyle: { top: '45%'  },
  style: { backgroundColor: null }
},
title: {
text: 'loading..',
style: {fontSize: '1em'}
},
subtitle: {
},
credits: {enabled: false},
legend: {
align: 'left',
verticalAlign: 'top',
x: 70,
y: 40,
floating: true
},
plotOptions: {
areaspline: {
   threshold: null,
   softThreshold: true,
   marker: {
   enabled: false,
   symbol: 'circle',
   radius: 2,
   states: {hover: {enabled: true}}
   }
},
series: {
}
},
xAxis: {
type: 'datetime'
},
yAxis: [";
	for ($j = 1; $j <= $NUMMETER; $j++) {
		if (${'GRAPH_MET' . $j} == $graphlist[$i] && !in_array(${'UNIT' . $j}, $unitlist[$i])) {
			$unitlist[$i][] = ${'UNIT' . $j};
			$cnt            = count($unitlist[$i]);
			if ($cnt > 1) {
				echo ",";
			}
			echo "
{
title: {text: '${'UNIT'.$j}'}
}";

		}
	}
	echo "
],
tooltip: {
    crosshairs: {
        width: 1,
        color: 'gray',
        dashStyle: 'shortdot'
    },
formatter: function() {
";
	for ($j = 1; $j <= $NUMMETER; $j++) {
		if ($j == 1) {
			echo ' if';
		} else {
			echo ' else if';
		}
		echo "(this.series.name=='${'METNAME'.$j}') { return '<b>' + Highcharts.numberFormat(this.y,'${'PRECI'.$j}') + ' ${'UNIT'.$j}' + '</b>";
		if (${'TYPE' . $j} != 'Elect') {
			echo "<br>' + Highcharts.dateFormat('%a %e %b %H:%M', this.x) }";
		} else {
			echo " ~' + Highcharts.numberFormat(this.y*12,'0') + ' W' + '$lgAVG<br>' + Highcharts.dateFormat('%a %e %b %H:%M', this.x)}";
		}
	}
	echo "
}
},
exporting: {enabled: false},
series: []
};

Mychart$i= Highcharts.chart('container$i',options$i);
Mychart$i.showLoading();
";
}
echo "
$.getJSON('programs/programmeter.php', function(JSONResponse) {";
for ($i = 0; $i < $cntgraph; $i++) {
	$sernum = 0;
	for ($k = 0; $k < $NUMMETER; $k++) {
		$j = $k + 1;
		if (${'GRAPH_MET' . $j} == $graphlist[$i]) {
			echo "
    options$i.series[$sernum] = JSONResponse.data[$k];";
			$sernum++;
		}
	}

	echo "
    Mychart$i= Highcharts.chart('container$i',options$i);
    Mychart$i.setTitle({text: JSONResponse.title[$i]});
    Mychart$i.xAxis[0].addPlotBand({from: JSONResponse.ystrtp, to: JSONResponse.ystpp, color: 'rgba(190,190,190,.3)'});
    Mychart$i.xAxis[0].addPlotBand({from: JSONResponse.tstrtp, to: JSONResponse.tstpp, color: 'rgba(190,190,190,.3)'});
    Mychart$i.xAxis[0].addPlotLine ({ color: '#848484', value: JSONResponse.plotline, width: 1, dashStyle: 'Solid'});
    Mychart$i.hideLoading();
    ";
}
echo "
});
";

if ($showlastd) {
	$unitlist = array();
	$getvalue = json_encode($check);
	echo "
/// Last days prod ///
var Mychart$i, options$i = {
chart: {
type: 'column',
backgroundColor: null,
defaultSeriesType: 'column',
zoomType: 'xy'
},
loading: {
   labelStyle: { top: '45%'  },
   style: { backgroundColor: null }
},
credits: {enabled: false},
title: {
text: '$lgLAST 15 $lgDAYS',
style: {fontSize: '1em'}
},
subtitle: {text: '$lgLASTPRODSUBTITLE'},
xAxis: {
type: 'datetime',
dateTimeLabelFormats: {day: '%e %b'}
},
yAxis: [";
	for ($j = 1; $j <= $NUMMETER; $j++) {
		if (${'LASTD_MET' . $j} && !in_array(${'UNIT' . $j}, $unitlist)) {
			$unitlist[] = ${'UNIT' . $j};
			$cnt        = count($unitlist);
			if ($cnt > 1) {
				echo ',';
			}
			if (${'TYPE' . $j} != 'Elect') {
				echo "
{
title: {text: '${'UNIT'.$j}'}
}";
			} else {
				echo "
{
title: {text: 'kWh'}
}";
			}

		}
	}
	echo "
],
legend: {
},
tooltip: {
    formatter: function() {
";
	for ($j = 1; $j <= $NUMMETER; $j++) {
		if ($j == 1) {
			echo "     if";
		} else {
			echo " else if";
		}
		echo "(this.series.name=='${'METNAME'.$j}') {
      return Highcharts.dateFormat('%A %d %B', this.x) + '<br>'+ this.series.name + ' <b>' +";
		if (${'TYPE' . $j} == 'Elect') {
			echo "Highcharts.numberFormat(this.y,2) + ' k${'UNIT'.$j}' + '</b>'";
		} else {
			echo "Highcharts.numberFormat(this.y,'${'PRECI'.$j}') + ' ${'UNIT'.$j}' + '</b>'";
		}
		if (${'PRICE' . $j} > 0) {
			echo " + ' (' + Highcharts.numberFormat((this.y*${'PRICE'.$j}),1) + '$CURS)'";
		}
		echo '
    }';
	}
	echo "
}
},
plotOptions: {
    series: {
    shadow: false,
    minPointLength: 3,
    pointWidth: 15,
        point:{
      events: {
        click: function(event) {
        window.location = 'detailed.php?meter=$getvalue&date2='+this.x;
        }
      }
    }
  }
},
exporting: {enabled: false},
series: []
};

Mychart$i= Highcharts.chart('container$i',options$i);
Mychart$i.showLoading();

    $.getJSON('programs/programlastdays.php', function(JSONResponse) {";
	$k = 0;
	for ($j = 1; $j <= $NUMMETER; $j++) {
		if (${'LASTD_MET' . $j}) {
			echo "
    options$i.series[$k] = JSONResponse.data[$k]";
			$k++;
		}
	}
	echo "
    Mychart$i= Highcharts.chart('container$i',options$i);
    Mychart$i.hideLoading();
    });
";
	$i++;
} // if showlastd

if ($housecons || $houseprod) {
	echo "
/// Gauge ///
var Mygauge, options$i = {
  chart: {
    type: 'gauge',
    backgroundColor: null,
    plotBackgroundColor: null,
    plotBackgroundImage: null,
    plotBorderWidth: 0,
    plotShadow: false,
    height: 225,
  events: {
        load: function() {
        setInterval(function () {
        var point = Mygauge.series[0].points[0];
        point.update(GRIDTOT);
        }, 1000);
            }
         }
  },
  loading: {
  labelStyle: { top: '45%'  },
  style: { backgroundColor: null }
  },
  title: {
    text: ''
  },
  plotOptions: {
  gauge: {
    pivot: {
      radius: 8,
      borderWidth: 1,
      borderColor: '#303030',
      backgroundColor: {
        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
        stops: [
          [0, '#AAA'],
          [1, '#333']
        ]
      }
    },
    dial: {
      baseLength : 10,
      baseWidth: 8,
      backgroundColor: '#666',
      radius : 70,
      rearLength: 40
    }
  }},
  pane: {
    startAngle: -150,
    endAngle: 150,
            background: [{
                backgroundColor: {
                    linearGradient: { x1: 0, y1: 1, x2: 0, y2: 0 },
                    stops: [
                        [0, '#333'],
                        [1, '#AAA']
                    ]
                },
                borderWidth: 0,
                outerRadius: '115%'
            }, {
                backgroundColor: {
                    linearGradient: { x1: 0, y1: 1, x2: 0, y2:0 },
                    stops: [
                        [0, '#AAA'],
                        [1, '#FFF']
                    ]
                },
                borderWidth: 1,
                outerRadius: '113%'
            },{
                // default background
            }, {
                backgroundColor: Highcharts.svg ? {
                    radialGradient: {
                        cx: 0.5,
                        cy: -0.6,
                        r: 1.6
                    },
                    stops: [
                        [0.5, 'rgba(255, 255, 255, 0.1)'],
                        [0.3, 'rgba(200, 200, 200, 0.1)']
                    ]
                } : null },{
                backgroundColor: Highcharts.svg ? {
                    radialGradient: {
                        cx: 0.5,
                        cy: -0.9,
                        r: 2.6
                    },
                    stops: [
                        [0.5, 'rgba(255, 255, 255, 0.1)'],
                        [0.3, 'rgba(200, 200, 200, 0.1)']
                    ]
                } : null }
                        ]
  },
  yAxis: {
    min: $POWER_MIN,
    max: $POWER_MAX,

    minorTickInterval: 'auto',
    minorTickWidth: 1,
    minorTickLength: 5,
    minorTickPosition: 'inside',
    minorTickColor: '#666',

    tickPixelInterval: 50,
    tickWidth: 2,
    tickPosition: 'inside',
    tickLength: 15,
    tickColor: '#666',
    labels: {
      step: 2,
      rotation: 'auto'
    },
    title: {
      style: {
        color: '#555',
        fontSize: '18px'
      },
      y: 125,
      text: 'W'
    },
    plotBands: [{
      from: $POWER_MIN,
      to: $Yg1,
      color: '#$gcolor[0]'
    }, {
      from: $Yg1,
      to: $Yg2,
      color: '#$gcolor[1]'
    }, {
      from: $Yg2,
      to: $Yg3,
      color: '#$gcolor[2]'
    }, {
      from: $Yg3,
      to: $Yg4,
      color: '#$gcolor[3]'
    }, {
      from: $Yg4,
      to: $Yg5,
      color: '#$gcolor[4]'
    }, {
      from: $Yg5,
      to: $POWER_MAX,
      color: '#$gcolor[5]'
    }]
  },
  exporting: {enabled: false},
  credits: {enabled: false},
  series: [{
    name: 'power',
    data: [0],
    tooltip: {
      valueSuffix: 'W'
    },
    overshoot: 5,
    dataLabels: {
      enabled: true,
	  allowOverlap: true,
  formatter: function() {
        if (this.y>=1000 || this.y<=1000) {
        return Highcharts.numberFormat(this.y,0);
        } else {
        return Highcharts.numberFormat(this.y,1);
        }
  },
      color: '#666',
      x: 0,
      y: 40,
      style: {
      fontSize: '12px'
      }
    }
  }]
};

Mygauge = Highcharts.chart('gcontainer',options$i);
Mygauge.series[0].data[0].dataLabel.box.hide();
";
}

echo "
});
</script>
<table width='100%' border=0 align=center cellpadding=0 cellspacing=0>
<tr valign='top'><td>
<table width='100%' border=0 align=center cellpadding=0 cellspacing=0>";
for ($i = 0; $i < $cntgraph; $i++) {
	echo "<tr><td><div id='container$i' style='height: ${GRAPHH}px'></div></td></tr>";
}
if ($showlastd) {
	echo "<tr><td><div id='container$cntgraph' style='height: ${GRAPHH}px'></div></td></tr>";

}
echo '
</table>
</td>
<td width=225>';
if ($housecons || $houseprod) {
	echo "<div id='gcontainer' align='center'></div>";
}

if ($livetab) {
	echo "<br>
<table border=1 class='table' cellspacing=0 cellpadding=5 align='center' width='90%'>";
	for ($i = 1; $i <= $NUMMETER; $i++) {
		if (!empty(${'LIVECOMMAND' . $i})) {
			echo "<tr align='center'><td width='50%'><b>${'METNAME'.$i}</b></td><td><b><span id='rval$i'>...</span> ${'LIVEUNIT'.$i}</b></td></tr>";
		}
	}
	echo '
</table>';
}
if ($NUMIND > 0) { // indicators
	echo "
<br>
<table border=1 class='table' cellspacing=0 cellpadding=5 align='center' width='90%'>";
	for ($i = 1; $i <= $NUMIND; $i++) {
		if (!empty(${'INDCOMMAND' . $i})) {
			echo "<tr align='center'><td width='50%'><b>${'INDNAME'.$i}</b></td><td><b><span id='rival$i'>...</span> ${'INDUNIT'.$i}</b></td></tr>";
		}
	}
	echo '
</table>
<br>&nbsp;
';
} // indicators

echo '
</td></tr>
</table>';
include "styles/$STYLE/footer.php";
?>
