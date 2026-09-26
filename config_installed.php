<?php
// Installation completion marker.
// The database configuration already exists in .env; this prevents the app
// from incorrectly redirecting authenticated users back to the installer.
define('INSTALLED', true);
define('INSTALL_DATE', '2026-09-26 00:00:00');
