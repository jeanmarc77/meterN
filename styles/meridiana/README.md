# Meridiana theme

A responsive, light/dark alternative to the default meterN style, built on
[Bootstrap 5](https://getbootstrap.com/) and
[Font Awesome](https://fontawesome.com/) - loaded via CDN `@import`, no
build step, no local copy of either library to keep updated.

## Features

- Responsive from phone to desktop: a collapsible top navbar replaces the
  legacy fixed-width table layout.
- Light/dark switch, remembered in a cookie (`color_scheme`), including a
  matching Highcharts palette applied both on load and on toggle.
- Icon set (Font Awesome) for the nav and header, in place of the default
  theme's plain text links.
- Pure CSS + two small template files: no engine code is touched, and
  nothing here talks to meterN's data layer directly.

## Installation

1. Copy this `meridiana/` directory into your installation's `styles/`
   folder, so you end up with `styles/meridiana/{css/style.css,
   footer.php, header.php, head.php}`.
2. In `config/config_main.php`, set:
   ```php
   $STYLE = "meridiana";
   ```
3. That's it - no admin panel step, no schema change, no new config keys.

### Requirement

Needs the per-style `<head>` hook merged in
[8850909](https://github.com/jeanmarc77/meterN/commit/8850909) (`styles/<
STYLE>/head.php`, included by `styles/globalheader.php` before the theme's
own stylesheet). Without it, `head.php` here is simply never included and
the pages render without a viewport meta tag - everything else still
works, but mobile layout won't. Any checkout of `main` from that commit
onward already has it.

## Notes for anyone adapting this theme

- The `@import` lines **must stay the first rule** in `css/style.css`
  (only `@charset` may precede an `@import`), and deliberately load
  Bootstrap/Font Awesome from there rather than from `head.php`:
  `globalheader.php` links this stylesheet *before* including
  `styles/yourheader.php`, so anything a site owner adds to
  `yourheader.php` would otherwise win specificity ties against Bootstrap's
  reset. Importing it in the CSS file instead means the cascade order is
  decided in one place, not split across two files with different owners.
- The light/dark switch defaults to light on first visit rather than
  reading `prefers-color-scheme`, on purpose: a monitoring dashboard is as
  often read on a bright screen outdoors as on a phone at night, so the
  choice is left to an explicit, remembered click.

## License

Same license as the parent project (GNU GPLv3).
