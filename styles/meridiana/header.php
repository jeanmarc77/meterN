<?php
/**
 * styles/meridiana/header.php
 *
 * Header for the "Meridiana" theme. Replaces the early-2000s table layout
 * of the default theme with a modern, responsive navbar and a light/dark
 * switch. $TITLE, $SUBTITLE and $lgM* are already populated by
 * styles/globalheader.php (config_main.php + language file), which
 * includes this file right after closing </head>.
 *
 * The active theme is chosen via $STYLE (config/config_main.php), not
 * here: this file does not touch any meterN engine source, it is only an
 * official extension point (styles/<name>/header.php, see
 * styles/default/Explanation.txt).
 *
 */

// Always opens light unless the visitor has explicitly toggled to dark
// (cookie set by footer.php's toggle button): no system-preference
// auto-detection here, on purpose - a monitoring dashboard is as often
// read on a bright screen outdoors as on a phone at night, so the choice
// is left to an explicit, remembered click rather than guessed from the
// OS, which may not reflect the room the dashboard is actually read in.
$meridianaTheme = (isset($_COOKIE['color_scheme']) && $_COOKIE['color_scheme'] === 'dark') ? 'dark' : 'light';
$meridianaPage = basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH), '.php');
if ($meridianaPage === '') {
    $meridianaPage = 'index';
}
?>
<body data-theme="<?php echo htmlspecialchars($meridianaTheme, ENT_QUOTES); ?>" class="page-<?php echo htmlspecialchars($meridianaPage, ENT_QUOTES); ?>">

<header class="meridiana-header">
  <div class="meridiana-header-inner">
    <a class="meridiana-brand" href="index.php">
      <span class="meridiana-brand-icon"><i class="fa-solid fa-solar-panel"></i></span>
      <span class="meridiana-brand-text">
        <strong><?php echo htmlspecialchars($TITLE, ENT_QUOTES); ?></strong>
        <small><?php echo htmlspecialchars(strip_tags($SUBTITLE), ENT_QUOTES); ?></small>
      </span>
    </a>

    <button type="button" class="meridiana-theme-toggle" id="meridianaThemeToggle" aria-label="Cambia tema chiaro/scuro" title="Chiaro / scuro">
      <i class="fa-solid fa-sun"></i><i class="fa-solid fa-moon"></i>
    </button>
    <button type="button" class="meridiana-nav-toggle" id="meridianaNavToggle" aria-label="Menu" aria-expanded="false" aria-controls="meridianaNav">
      <i class="fa-solid fa-bars"></i>
    </button>

    <nav class="meridiana-nav" id="meridianaNav">
      <a href="index.php" class="<?php echo $meridianaPage === 'index' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i><?php echo $lgMINDEX; ?></a>
      <a href="detailed.php" class="<?php echo $meridianaPage === 'detailed' ? 'active' : ''; ?>"><i class="fa-solid fa-table-list"></i><?php echo $lgMDETAILED; ?></a>
      <a href="readings.php" class="<?php echo $meridianaPage === 'readings' ? 'active' : ''; ?>"><i class="fa-solid fa-gauge-high"></i><?php echo $lgMREAD; ?></a>
      <a href="comparison.php" class="<?php echo $meridianaPage === 'comparison' ? 'active' : ''; ?>"><i class="fa-solid fa-code-compare"></i><?php echo $lgMCOMPARISON; ?></a>
      <a href="dashboard.php" class="<?php echo $meridianaPage === 'dashboard' ? 'active' : ''; ?>"><i class="fa-solid fa-diagram-project"></i><?php echo $lgMDASH; ?></a>
      <a href="info.php" class="<?php echo $meridianaPage === 'info' ? 'active' : ''; ?>"><i class="fa-solid fa-circle-info"></i><?php echo $lgMINFO; ?></a>
      <a href="admin/" class="meridiana-nav-admin"><i class="fa-solid fa-gear"></i>admin</a>
    </nav>
  </div>
</header>

<script>
/* Visual theme for Highcharts. Must run before the page's own script
 * (index.php etc.), which also calls Highcharts.setOptions() but only for
 * locale/decimal formatting: since it's a deep merge, these settings
 * survive. Verified by reading index.php - see TODO.md point 9. */
if (window.Highcharts) {
  var meridianaDark = document.body.getAttribute('data-theme') === 'dark';
  Highcharts.setOptions({
    colors: ['#f59e0b', '#2563eb', '#16a34a', '#7c3aed', '#0891b2', '#ea580c'],
    chart: {
      backgroundColor: 'transparent',
      style: { fontFamily: 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif' }
    },
    title: { style: { color: meridianaDark ? '#e7ebf3' : '#161b26', fontWeight: '700' } },
    subtitle: { style: { color: meridianaDark ? '#94a1b8' : '#667085' } },
    xAxis: {
      gridLineColor: meridianaDark ? '#253048' : '#e3e8ef',
      lineColor: meridianaDark ? '#253048' : '#e3e8ef',
      tickColor: meridianaDark ? '#253048' : '#e3e8ef',
      labels: { style: { color: meridianaDark ? '#94a1b8' : '#667085' } }
    },
    yAxis: {
      gridLineColor: meridianaDark ? '#253048' : '#e3e8ef',
      labels: { style: { color: meridianaDark ? '#94a1b8' : '#667085' } },
      title: { style: { color: meridianaDark ? '#94a1b8' : '#667085' } }
    },
    legend: { itemStyle: { color: meridianaDark ? '#e7ebf3' : '#161b26' } },
    tooltip: {
      backgroundColor: meridianaDark ? '#121a29' : '#ffffff',
      borderColor: meridianaDark ? '#253048' : '#e3e8ef',
      style: { color: meridianaDark ? '#e7ebf3' : '#161b26' }
    }
  });
}
</script>

<main class="meridiana-main">
<!-- #BeginEditable "mainbox" -->
