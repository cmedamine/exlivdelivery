<?php
/** Creates persistent prices for client parcel types. Run: php migrations/20261005_package_type_prices.php */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only.\n"); }
$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
$_SERVER['PHP_SELF'] = $_SERVER['PHP_SELF'] ?? '/migrations/20261005_package_type_prices.php';
require_once __DIR__ . '/../config.php';
$bdd->exec("CREATE TABLE IF NOT EXISTS `package_type_prices` (
  `package_type` ENUM('particulier','rapide','normal') NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `active` ENUM('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`package_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$bdd->exec("INSERT IGNORE INTO `package_type_prices` (`package_type`,`price`,`active`) VALUES
('particulier',0.00,'1'),('rapide',0.00,'1'),('normal',0.00,'1')");
echo "Parcel type prices table is ready. Set the actual rates in parcelprices.php.\n";
