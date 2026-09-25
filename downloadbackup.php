<?php
session_start();
include("config.php");

if($_SESSION['type'] == "moderator"){

function backupDatabase(PDO $bdd){
	try {
        // Get the database name
        $dbName = $bdd->query("SELECT DATABASE()")->fetchColumn();

        // Fetch all tables in the database
        $tables = $bdd->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        $backupFile = 'backup-' . date("Y-m-d_H-i-s") . '.sql';
        $handle = fopen($backupFile, 'w');

        // Add SQL dump header
        fwrite($handle, "-- Database: $dbName\n\n");

        // Iterate through tables and export data and structure
        foreach ($tables as $table) {
            $result = $bdd->query("SHOW CREATE TABLE $table");
            $createTableSQL = $result->fetchColumn(1);

            fwrite($handle, "-- Table structure for table `$table`\n");
            fwrite($handle, "$createTableSQL;\n\n");

            // Insert data into the table
            $result = $bdd->query("SELECT * FROM $table");
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $rowValues = array_map([$bdd, 'quote'], $row);
                    $rowString = implode(', ', $rowValues);
                    fwrite($handle, "INSERT INTO `$table` VALUES ($rowString);\n");
                }

                fwrite($handle, "\n\n");
            }
        }

        fclose($handle);

        return $backupFile;
    } catch (PDOException $e) {
        die("Backup failed: " . $e->getMessage());
    }
}

$backupFile = backupDatabase($bdd);

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
readfile($backupFile);
unlink($backupFile);

}

?>