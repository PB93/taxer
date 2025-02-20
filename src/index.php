<?php
require_once "vendor/autoload.php";
use app\Taxer;

$taxer = new Taxer();
$taxer->processFile($argv[1]);