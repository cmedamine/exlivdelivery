<?php
/**
 * Restores the legacy order, shipment, delivery-note, and invoice fields used
 * by the application.
 *
 * Safe to run more than once: existing columns and indexes are skipped.
 * Run from the project root with:
 *   php migrations/20260926_add_legacy_command_columns.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This migration can only be run from the command line.\n");
}

// config.php also builds URL helpers intended for web requests.
$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
$_SERVER['PHP_SELF'] = $_SERVER['PHP_SELF'] ?? '/migrations/20260926_add_legacy_command_columns.php';

require_once __DIR__ . '/../config.php';

$tables = [
    'commands' => [
        'columns' => [
            'client' => 'INT(11) NOT NULL DEFAULT 0',
            'extrafees' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
            'package' => 'INT(11) NOT NULL DEFAULT 0',
            'collected' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
            'invoiceddlm' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
            'confirmed' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
            'treated' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
            'archived' => "ENUM('0','1') NOT NULL DEFAULT '0'",
            'echange' => "ENUM('0','1') NOT NULL DEFAULT '0'",
            'openpackage' => "ENUM('0','1') NOT NULL DEFAULT '0'",
        ],
        'indexes' => ['client' => 'client', 'collected' => 'collected', 'archived' => 'archived'],
    ],
    'shipments' => [
        'columns' => [
            'stock' => 'INT(11) NOT NULL DEFAULT 0',
            'received' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
            'dateadd' => 'INT(11) NOT NULL DEFAULT 0',
        ],
        'indexes' => ['received' => 'received'],
    ],
    'stocks' => [
        'columns' => [
            'received' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
            'dateadd' => 'INT(11) NOT NULL DEFAULT 0',
        ],
        'indexes' => [],
    ],
    'stockdlms' => [
        'columns' => [
            'stock' => 'INT(11) NOT NULL DEFAULT 0',
            'received' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
            'dateadd' => 'INT(11) NOT NULL DEFAULT 0',
        ],
        'indexes' => [],
    ],
    'bls' => [
        'columns' => [
            'dlm' => 'INT(11) NOT NULL DEFAULT 0',
            'subdlm' => 'INT(11) NOT NULL DEFAULT 0',
            'client' => 'INT(11) NOT NULL DEFAULT 0',
            'code' => "VARCHAR(50) NOT NULL DEFAULT ''",
            'cmds' => 'TEXT',
            'done' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
        ],
        'indexes' => ['dlm' => 'dlm', 'client' => 'client', 'code' => 'code', 'done' => 'done'],
    ],
    'factures' => [
        'columns' => [
            'code' => "VARCHAR(50) NOT NULL DEFAULT ''",
            'dlm' => 'INT(11) NOT NULL DEFAULT 0',
            'client' => 'INT(11) NOT NULL DEFAULT 0',
            'nbcommands' => 'INT(11) NOT NULL DEFAULT 0',
            'price' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
            'charges' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
            'note' => 'TEXT',
            'validated' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
            'received' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
            'datecreated' => 'INT(11) NOT NULL DEFAULT 0',
            'datereceived' => 'INT(11) NOT NULL DEFAULT 0',
        ],
        'indexes' => ['code' => 'code', 'dlm' => 'dlm', 'client' => 'client', 'received' => 'received'],
    ],
    'spreadsheets' => [
        'columns' => [
            'sheetid' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'sheetname' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'lastrow' => 'INT(11) NOT NULL DEFAULT 1',
            'autofetch' => "ENUM('on','off') NOT NULL DEFAULT 'off'",
        ],
        'indexes' => ['autofetch' => 'autofetch'],
    ],
];

try {
    // The newer bootstrap schema has required fields that legacy INSERTs do not
    // supply. Defaults keep the existing columns while making both workflows
    // compatible.
    $bdd->exec("ALTER TABLE `bls` MODIFY COLUMN `type` ENUM('BRA','BL','BR','BRL') NOT NULL DEFAULT 'BL', MODIFY COLUMN `command` VARCHAR(50) NOT NULL DEFAULT ''");
    $bdd->exec("ALTER TABLE `factures` MODIFY COLUMN `type` ENUM('client','dlm') NOT NULL DEFAULT 'client', MODIFY COLUMN `target` INT(11) NOT NULL DEFAULT 0, MODIFY COLUMN `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00, MODIFY COLUMN `dateadd` INT(11) NOT NULL DEFAULT 0");
    $bdd->exec("ALTER TABLE `stockdlms` MODIFY COLUMN `title` VARCHAR(255) NOT NULL DEFAULT '', MODIFY COLUMN `ref` VARCHAR(100) NOT NULL DEFAULT ''");
    $bdd->exec("ALTER TABLE `spreadsheets` MODIFY COLUMN `url` VARCHAR(500) NOT NULL DEFAULT '', MODIFY COLUMN `dateadd` INT(11) NOT NULL DEFAULT 0");

    foreach ($tables as $table => $definition) {
        $existingColumns = $bdd->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($definition['columns'] as $name => $columnDefinition) {
            if (in_array($name, $existingColumns, true)) {
                echo "Column already present: {$table}.{$name}\n";
                continue;
            }

            $bdd->exec("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$columnDefinition}");
            echo "Added column: {$table}.{$name}\n";
        }

        $existingIndexes = $bdd->query("SHOW INDEX FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN, 2);
        foreach ($definition['indexes'] as $name => $column) {
            if (in_array($name, $existingIndexes, true)) {
                echo "Index already present: {$table}.{$name}\n";
                continue;
            }

            $bdd->exec("ALTER TABLE `{$table}` ADD INDEX `{$name}` (`{$column}`)");
            echo "Added index: {$table}.{$name}\n";
        }
    }

    $bdd->exec("CREATE TABLE IF NOT EXISTS `facturesdetails` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `facture` VARCHAR(50) NOT NULL,
        `command` INT(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `facture` (`facture`),
        KEY `command` (`command`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $bdd->exec("CREATE TABLE IF NOT EXISTS `historylog` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `description` TEXT NOT NULL,
        `dateadd` INT(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `dateadd` (`dateadd`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $historyColumns = $bdd->query('SHOW COLUMNS FROM `commandshistory`')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('agent', $historyColumns, true)) {
        $bdd->exec("ALTER TABLE `commandshistory` ADD COLUMN `agent` VARCHAR(255) NOT NULL DEFAULT ''");
        echo "Added column: commandshistory.agent\n";
    }

    $trackingColumns = $bdd->query('SHOW COLUMNS FROM `trackingstates`')->fetchAll(PDO::FETCH_COLUMN);
    $trackingDefinitions = [
        'color' => "VARCHAR(20) NOT NULL DEFAULT '#757575'",
        'agent' => 'TEXT',
        'kpi' => "VARCHAR(50) NOT NULL DEFAULT ''",
    ];
    foreach ($trackingDefinitions as $name => $definition) {
        if (!in_array($name, $trackingColumns, true)) {
            $bdd->exec("ALTER TABLE `trackingstates` ADD COLUMN `{$name}` {$definition}");
            echo "Added column: trackingstates.{$name}\n";
        }
    }

    $stateConfiguration = [
        ['Nouveau', '#2196F3', 'En cours', 'Modérateur'],
        ['Confirmé', '#03A9F4', 'En cours', 'Modérateur,Agent de confirmation'],
        ['Reporté', '#FF9800', 'En cours', 'Modérateur,Client,Agent de confirmation'],
        ['Interessé', '#9C27B0', 'En cours', 'Modérateur,Agent de confirmation'],
        ['Changement adresse', '#FF9800', 'En cours', 'Modérateur,Client,Agent de confirmation'],
        ['Annulé', '#F44336', 'Echouées', 'Modérateur,Client,Agent de confirmation,Livreur'],
        ['Refusé', '#F44336', 'Echouées', 'Modérateur,Agent de confirmation,Livreur'],
        ['Hors zone', '#F44336', 'Echouées', 'Modérateur,Agent de confirmation,Livreur'],
        ['Ramassé', '#00BCD4', 'En cours', 'Modérateur,Livreur'],
        ['En cours de livraison', '#2196F3', 'En cours', 'Modérateur,Livreur'],
        ['Livré', '#4CAF50', 'Livrées', 'Modérateur,Livreur'],
        ['Retour', '#795548', 'Echouées', 'Modérateur,Livreur'],
        ['Echec de livraison', '#F44336', 'Echouées', 'Modérateur,Livreur'],
    ];
    $configureState = $bdd->prepare("UPDATE `trackingstates` SET `color` = ?, `kpi` = ?, `agent` = ? WHERE `state` = ?");
    foreach ($stateConfiguration as [$state, $color, $kpi, $agent]) {
        $configureState->execute([$color, $kpi, $agent, $state]);
    }

    $bdd->exec("ALTER TABLE `smsmodels` MODIFY COLUMN `title` VARCHAR(255) NOT NULL DEFAULT '', MODIFY COLUMN `content` TEXT NULL");
    $smsModelColumns = $bdd->query('SHOW COLUMNS FROM `smsmodels`')->fetchAll(PDO::FETCH_COLUMN);
    $smsModelDefinitions = [
        'state' => "VARCHAR(255) NOT NULL DEFAULT ''",
        'message' => 'TEXT',
        'active' => "ENUM('on','off') NOT NULL DEFAULT 'on'",
    ];
    foreach ($smsModelDefinitions as $name => $definition) {
        if (!in_array($name, $smsModelColumns, true)) {
            $bdd->exec("ALTER TABLE `smsmodels` ADD COLUMN `{$name}` {$definition}");
            echo "Added column: smsmodels.{$name}\n";
        }
    }

    echo "Legacy workflow schema migration complete.\n";
} catch (PDOException $exception) {
    fwrite(STDERR, 'Migration failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
