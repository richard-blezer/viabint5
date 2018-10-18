<?php

// Skript das die reviews-CSV-Datei updated (per Cron starten)


// Framework laden, hierin befinden sich Config und Plugin-Core-Klasse laden
$path = dirname(__FILE__);
require_once($path.'/../lib/framework.php');

$config = ShopgateConfig::validateAndReturnConfig();

// Plugin-Klasse initialisieren
$Plugin = ShopgatePluginCore::newInstance($config);

// CSV-Datei erstellen/updaten
$Plugin->startCreateReviewsCsv();

