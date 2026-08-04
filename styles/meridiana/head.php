<?php
/**
 * styles/meridiana/head.php
 *
 * Per-style <head> additions for the "Meridiana" theme, included by
 * styles/globalheader.php right before the theme's own stylesheet.
 *
 * Holds only the viewport meta tag, without which no amount of CSS makes
 * the pages usable on a phone: meterN ships no viewport tag of its own,
 * so a mobile browser lays the page out at its default virtual width and
 * scales the result down.
 *
 * This used to live in styles/yourheader.php, which is the wrong place
 * for it on a live installation: admin/updater.php preserves custom style
 * *directories* but not loose files under styles/, so an update silently
 * reverts yourheader.php to the stock one - as happened here on
 * 2026-07-28, leaving both custom themes without a viewport tag until it
 * was noticed. Everything a theme needs belongs inside the theme's own
 * directory.
 *
 * Bootstrap and Font Awesome are deliberately NOT linked here even though
 * this file is included before the theme stylesheet: they are @imported as
 * the first rule of css/style.css instead. Both orders would work today,
 * but keeping every external dependency in one place means the cascade is
 * decided by a single file, the one that also has to live with it.
 *
 * @package default
 */
?>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php
// vim: set ts=4 sw=4 noet:
