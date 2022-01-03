 <?php
/**
 * /srv/http/metern/dashboard.php
 *
 * @package default
 */


include 'styles/globalheader.php';
include 'config/config_main.php';
include 'config/config_indicator.php';
include 'config/config_layout.php';

$currentFile = $_SERVER["PHP_SELF"];
if (!empty($_POST['pool']) && is_numeric($_POST['pool'])) {
    $pool = ($_POST['pool']);
} else {
    $pool = 1000;
}

$poollst = array(
    '5000',
    '2000',
    '1000',
    '500'
);

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
CTOT=0;
PTOT=0;
SCTOT=0;
SDTOT=0;";

$housecons = false;
$houseprod = false;
$housestor = false;
$grid      = array();
for ($i = 1; $i <= $NUMMETER; $i++) {
    include "config/config_met$i.php";
    if (!isset($grid[${'PHASE' . $i}]) && ${'TYPE' . $i} == 'Elect' && ${'PROD' . $i} != 0) {
        $grid[${'PHASE' . $i}] = ${'PHASE' . $i}; // grid value on ph n
        echo "
C${'PHASE'.$i}=0;
P${'PHASE'.$i}=0;
";
    }
    
    echo "val$i =json['${'METNAME'.$i}$i'];
";
    if (${'TYPE' . $i} == 'Elect' && isset(${'LIVECOMMAND' . $i})) {
        echo "
if(isNaN(val$i)){
val$i = 0;
} else {
val$i = parseFloat(val$i);
}
";
        if (${'PROD' . $i} == 2) { // on ph n
            $housecons = true;
            echo "CTOT+=val$i;
C${'PHASE'.$i}+=val$i;
";
        } elseif (${'PROD' . $i} == 1) {
            $houseprod = true;
            echo "PTOT+=val$i;
P${'PHASE'.$i}+=val$i;
"; 
        } elseif (${'PROD' . $i} == 3) {
            $housestor = true;
            echo "SCTOT+=val$i;
SC${'PHASE'.$i}+=val$i;
"; 
        }
        elseif (${'PROD' . $i} == 4) {
            $housestor = true;
            echo "SDTOT+=val$i;
SD${'PHASE'.$i}+=val$i;
"; 
        }
        
    }
}

sort($grid);
$cnt = count($grid);
if ($cnt > 1) {
    for ($i = 0; $i < $cnt; $i++) {
        echo "
if(isNaN(C$grid[$i])){
document.getElementById('cval$grid[$i]').innerHTML = 0;
} else {
document.getElementById('cval$grid[$i]').innerHTML = C$grid[$i];
}
if(isNaN(P$grid[$i])){
document.getElementById('pval$grid[$i]').innerHTML = 0;
} else {
document.getElementById('pval$grid[$i]').innerHTML = P$grid[$i];
}
grid$grid[$i]=P$grid[$i]-C$grid[$i];
document.getElementById('G$grid[$i]').innerHTML = grid$grid[$i];
";
    }
}

for ($i = 1; $i <= $NUMMETER; $i++) {
    if (!empty(${'LIVECOMMAND' . $i}) && !${'SKIPMONITORING' . $i}) {
        echo "
if (typeof val$i === 'undefined') {
    document.getElementById('rval$i').innerHTML = 'err';
} else {
    document.getElementById('rval$i').innerHTML = formatNum(val$i);
}";
    } else {
        echo "
document.getElementById('rval$i').innerHTML = '---';";
    }
}

if ($housecons || $houseprod) {
	if($POWER_MAX > abs($POWER_MIN)) {
	$POW = $POWER_MAX;
	} else {
	$POW = abs($POWER_MIN);
	}
}

if ($housecons) {
    if ($houseprod) {
        echo "
    GR=parseInt((PTOT/CTOT)*100);
    document.getElementById('rGR').innerHTML = GR;
    ";
    } else {
        echo "PTOT = 0;";
        
    }
    echo "
GRIDTOT=parseInt(PTOT-CTOT);

if(GRIDTOT<0) { 
    var speed = ($POW/GRIDTOT)*0.25;
    var size = 24/($POW/GRIDTOT);
} else {
    var speed = ($POW/GRIDTOT)*0.25;
    var size = 24/($POW/GRIDTOT);
}
speed = Math.abs(speed).toPrecision(2);
if(speed<0.1) {
    speed = 0.1;
}
if(speed>15) {
    speed = 15;
}
rspeed = ' ' + speed + 's ';
size = Math.abs(Math.round(size));
if(size<4) {
    size = 4;
}
if(size>18) {
    size = 18;
}
rsize = size + 'px';

if(GRIDTOT<0) { 
    for (i = 1; i < 5; i++) {
        document.getElementById('lds' + i ).style.background = '#8B0000';
        document.getElementById('lds'+i ).style.height = rsize;
    }
    document.getElementById('lds1').style.left = '8px';
    document.getElementById('lds2').style.left = '8px';
    document.getElementById('lds3').style.left = '32px';
    document.getElementById('lds4').style.left = '56px';
    document.getElementById('lds1').style.animation = 'lds-ellipsis1 ' + rspeed + 'infinite';
    document.getElementById('lds2').style.animation = 'lds-ellipsis2 ' + rspeed + 'infinite';
    document.getElementById('lds3').style.animation = 'lds-ellipsis2 ' + rspeed + 'infinite';
    document.getElementById('lds4').style.animation = 'lds-ellipsis3 ' + rspeed + 'infinite';
} else if (GRIDTOT>0) {
    for (i = 1; i < 5; i++) {
        document.getElementById('lds' + i ).style.background = '#228B22';
        document.getElementById('lds'+i ).style.height = rsize;
    }
    document.getElementById('lds1').style.left = '8px';
    document.getElementById('lds2').style.left = '32px';
    document.getElementById('lds3').style.left = '56px';
    document.getElementById('lds4').style.left = '56px';
    document.getElementById('lds1').style.animation = 'lds-ellipsis3 ' + rspeed + 'infinite';
    document.getElementById('lds2').style.animation = 'lds-rellipsis2 ' + rspeed + 'infinite';
    document.getElementById('lds3').style.animation = 'lds-rellipsis2 ' + rspeed + 'infinite';
    document.getElementById('lds4').style.animation = 'lds-ellipsis1 '+ rspeed + 'infinite';
} else {
    for (i = 1; i < 5; i++) {
        document.getElementById('lds' + i ).style.background = '#595959';
        document.getElementById('lds' + i ).style.height = '4px';
        document.getElementById('lds' + i ).style.animation = 'lds-ellipsis2 0s';
    }
    document.getElementById('lds1').style.left = '8px';
    document.getElementById('lds2').style.left = '8px';
    document.getElementById('lds3').style.left = '32px';
    document.getElementById('lds4').style.left = '56px';
}

if (typeof GRIDTOT === 'undefined') {
    document.getElementById('rGRIDTOT').innerHTML = 'err';
} else {
    if (GRIDTOT>=0) {
    document.getElementById('rGRIDTOT').style.color = '#228B22';
    } else {
    document.getElementById('rGRIDTOT').style.color = '#8B0000';
    }
    document.getElementById('rGRIDTOT').innerHTML = GRIDTOT + ' W';
}
";
}

if ($houseprod) {
    echo "
PTOT=parseInt(PTOT);
document.getElementById('rPTOT').innerHTML = PTOT;
if (PTOT>0) {
    var pspeed = ($POW/PTOT)*0.25;
    var psize = 24/($POW/PTOT);
    pspeed = Math.abs(pspeed).toPrecision(2);
    if(pspeed<0.2) {
    pspeed = 0.2;
    }
    if(pspeed>15) {
    pspeed = 15;
    }
    prspeed = ' ' + pspeed + 's ';
    psize = Math.abs(Math.round(psize));
    if(psize<4) {
    psize = 4;
    }
    if(psize>18) {
    psize = 18;
    }
    prsize = psize + 'px';

    document.getElementById('plds1').style.left = '8px';
    document.getElementById('plds2').style.left = '32px';
    document.getElementById('plds3').style.left = '56px';
    document.getElementById('plds4').style.left = '56px';
    for (i = 1; i < 5; i++) {
        document.getElementById('plds' + i ).style.background = '#228B22';
        document.getElementById('plds'+i ).style.height = prsize;
    }
    document.getElementById('plds1').style.animation = 'lds-ellipsis3 ' + prspeed + 'infinite';
    document.getElementById('plds2').style.animation = 'lds-rellipsis2 ' + prspeed + 'infinite';
    document.getElementById('plds3').style.animation = 'lds-rellipsis2 ' + prspeed + 'infinite';
    document.getElementById('plds4').style.animation = 'lds-ellipsis1 '+ prspeed + 'infinite';
} else {
    for (i = 1; i < 5; i++) {
        document.getElementById('plds' + i ).style.background = '#595959';
        document.getElementById('plds' + i ).style.height = '4px';
        document.getElementById('plds' + i ).style.animation = 'lds-ellipsis2 0s';
    }
}
";
}

if ($housecons) {
    echo "
CTOT=parseInt(CTOT);
document.getElementById('rCTOT').innerHTML = CTOT;
";
}

echo "
document.getElementById('rSTAMP').innerHTML = json['stamp'];
})";

if ($NUMIND > 0) { // indicators
    echo "
$.getJSON('programs/programindicator.php', function(json){";
    for ($i = 1; $i <= $NUMIND; $i++) {
        echo "
	ival$i =json['${'INDNAME'.$i}$i'];";
        if (!empty(${'INDCOMMAND' . $i})) {
        echo "
	if (typeof ival$i === 'undefined') {
		document.getElementById('rival$i').innerHTML = 'err';
	} else {
		document.getElementById('rival$i').innerHTML = formatNum(ival$i);
	}";
        } 
    }
    echo "})";
} // indicators
echo "
} // updateit

function updateit60() {
$.getJSON('programs/programtotal.php', function(json){";
for ($i = 1; $i <= $NUMMETER; $i++) {
    if (!empty(${'COMMAND' . $i}) && !${'SKIPMONITORING' . $i}) {
        echo "
	document.getElementById('rtval$i').innerHTML = json['Totalcounter$i'];
	document.getElementById('dayval$i').innerHTML = json['Dailycounter$i'];";
    } else {
        echo "document.getElementById('rtval$i').innerHTML = '--- ';
document.getElementById('dayval$i').innerHTML = '--- ';
";
    }
}
echo "
})
} // updateit60

updateit();
setInterval(updateit, $pool);

updateit60();
setInterval(updateit60, 60000);
});
</script>
<br>
<table border=0 cellspacing=0 cellpadding=5 width=600 align='center'>
<tr>
<td></td>
<td align='center' colspan=3><font size='-1'><span id='rSTAMP'>--</span></font></td>
<td align='right'>
<form method=\"POST\" action=\"$currentFile\">
<select name='pool' onchange='this.form.submit()'>
";
$cnt = count($poollst);
for ($i = 0; $i < $cnt; $i++) {
    if ($pool == $poollst[$i]) {
        echo "<option SELECTED value='$poollst[$i]'>$poollst[$i]</option>";
    } else {
        echo "<option value='$poollst[$i]'>$poollst[$i]</option>";
    }
}
echo "
</select> ms
</form>
</td>
</tr>";
if ($houseprod || $housecons) {
    echo "
    <tr valign='center'>
    <td align='center'><img src='images/powerlines.png' width=54 height=80>
    </td>
    <td align='center' valign='center'>";
    if ($housecons) {
        echo "
    <br><br><span id='rGRIDTOT'>--</span><br>
    <div class='lds-ellipsis' id='lds'><div id='lds1'></div><div id='lds2'></div><div id='lds3'></div><div id='lds4'></div></div>";
    }
    echo "
    </td>
    <td align='center' width='150'><img src='images/house96.png' width='96' height='96'>";
    if ($housecons) {
        echo "<br><b><font size='+1'><span id='rCTOT'>--</span> W</font></b>";
    }
    if ($houseprod && $housecons) {
        echo "<br><font size='-1'>(<span id='rGR'>-</span>% $lgAUTONOM)</font>";
    }
    echo "
    </td>
    <td align='center'>";
    if ($houseprod) {
        echo "<br><br><span id='rPTOT'>--</span> W<br>
        <div class='lds-ellipsis' id='plds'><div id='plds1'></div><div id='plds2'></div><div id='plds3'></div><div id='plds4'></div></div>";
    }
    echo "
    </td><td width=100>";
    if ($houseprod) {
        echo "<div align='center'><img src='images/solarcell.png' width=110 height=59></div><br>";
    }
    echo "
    </td>
    </tr>
    </table>
    <br>";
}

$cnt = count($grid);
if (($houseprod || $housecons) && $cnt > 1) {
    echo "
<table border='1' cellspacing='0' cellpadding='5' width='500' align='center'>
<tr>
<td align='left' colspan=2><font size='-1'>$lgPHASE</td><td align='center'><font size='-1'>$lgCONSUMP</font></td><td align='right'><font size='-1'>$lgPRODUC</font></td>
</tr>
";
    for ($i = 0; $i < $cnt; $i++) {
        echo "<tr>
<td align='left'><font size='-1'>$grid[$i]</font></td>
<td align='left'><font size='-1'><span id='G$grid[$i]'></span> W</font></td>
<td align='center'><font size='-1'><span id='cval$grid[$i]'></span> W</font></td>
<td align='right'><font size='-1'><span id='pval$grid[$i]'></span> W</font></td></tr>
    ";
    }
    echo "
</table>
<br>&nbsp;";
}
echo "
<table border=0 cellspacing=0 cellpadding=5 width=700 align='center'>
<tr align='center'><td width='25%'></td><td width='25%'></td><td width='25%'><b>$lgDAYMETER</b></td><td width='25%'><b>$lgTOTMETER</b></td></tr>
</table>
<table border=1 cellspacing=0 cellpadding=5 width=700 align='center'>
<tbody>
";

for ($i = 1; $i <= $NUMMETER; $i++) {
    echo "<tr align='center'>
<th width='25%'>";
    echo "<b>${'METNAME'.$i}</b></th><th width='25%'><b><span id='rval$i'>--</span> ${'LIVEUNIT'.$i}</b></th><th width='25%'><span id='dayval$i'>--</span>";
    if (${'TYPE' . $i} != 'Sensor') {
        echo "${'UNIT'.$i}";
    }
    echo "</th><th width='25%'><span id='rtval$i'>--</span>";
    if (${'TYPE' . $i} != 'Sensor') {
        echo "${'UNIT'.$i}";
    }
    echo "</th></tr>";
}
echo "
</tbody>
</table>
<br>&nbsp;";

if ($NUMIND > 0) { // indicators
    echo "
<table border=1 cellspacing=0 cellpadding=5 width=700 align='center'>
<tbody>";
    for ($i = 1; $i <= $NUMIND; $i++) {
       if (!empty(${'INDCOMMAND' . $i})) {
        echo "<tr align='center'>
<th width='25%'>";
            echo "<b>${'INDNAME'.$i}</b></th><th width='25%'><span id='rival$i'>--</span> ${'INDUNIT'.$i}</b></th></tr>";
        }
    }
    echo "
</tbody>
</table>
<br>&nbsp;
";
} // indicators

include "styles/$STYLE/footer.php";
?> 
