<?php
include("config.php");

$req = $bdd->prepare("ALTER TABLE settings ADD COLUMN reseller INT AFTER requirednote");
$req->execute();
echo "done <br />";
$req = $bdd->prepare("ALTER TABLE commands ADD COLUMN clientname VARCHAR(255) AFTER client");
$req->execute();
echo "done <br />";
$req = $bdd->prepare("ALTER TABLE commands ADD COLUMN clientphone VARCHAR(255) AFTER clientname");
$req->execute();
echo "done <br />";
?>