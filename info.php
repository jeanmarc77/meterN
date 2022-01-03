<?php
/**
 * /srv/http/metern/info.php
 *
 * @package default
 */


include 'styles/globalheader.php';
include 'scripts/version.php';

echo "
<script type='text/javascript'>
  function updateit() {
  $.getJSON('programs/programloggerinfo.php', function(data){
  json = eval(data);
  document.getElementById('cpu').value= json.cpuuse;
  document.getElementById('uptime').innerHTML = json.uptime;
  document.getElementById('cpuuse').innerHTML = json.cpuuse;
  document.getElementById('memtot').innerHTML = json.memtot;
  document.getElementById('mem').max= json.memtot;
  document.getElementById('mem').value= json.memuse;
  document.getElementById('mem').high = (json.memtot*0.85);
  document.getElementById('memfree').innerHTML = json.memfree;
  document.getElementById('diskuse').innerHTML = json.diskuse;
  document.getElementById('diskfree').innerHTML = json.diskfree;
  })
  }
  $(document).ready(function() {
  updateit();
  setInterval(updateit, 1000);
  })
</script>

<table width='95%' border=0 align=center cellpadding=0 CELLSPACING=20>
<tr valign='top'><td width='50%'>
<img src='images/24/calendar-clock.png' width=24 height=24 border=0><b>&nbsp;$lgEVENTS</b>
<br><hr align=left size=1 width='90%'>";
$filename = "data/infos/events.txt";
if (file_exists('data/events.txt')) {
	$lines = file('data/events.txt');
} else {
	$lines = array();
}
echo "
<textarea style='resize: none;background-color: #DCDCDC' cols=80 rows=10>";
foreach ($lines as $line_num => $line) {
	echo "$line";
}
echo "
</textarea>
</td><td width='50%'><img src='images/24/cog.png' width=24 height=24 border=0><b>&nbsp;$lgLOGGERINFO</b>
<br><hr align=left size=1 width='90%'>
Uptime: <span id='uptime'>--</span>
<br>OS: ";
echo exec('uname -ors');
echo "<br>System: ";
echo exec('uname -nmi');
echo exec("cat /proc/cpuinfo | grep 'Processor' | head -n 1");
echo "
<meter id='cpu' high=85 min=0 max=100></meter> <span id='cpuuse'>--</span>%
<br>Memory: <span id='memtot'>--</span>MB
<meter id='mem' min='0'></meter>  <font size='-1'>(<span id='memfree'>--</span>MB free)</font>
<br>Disk Usage: <span id='diskuse'>--</span>/<span id='diskfree'>--</span> avail.
<br>Software: $VERSION
</td></tr>
</table>";

include "styles/$STYLE/footer.php";
?>
