<?php
include_once dirname(__DIR__).'/vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("SmartProfiler\\", __DIR__ . "/../src", true);
$classLoader->register();
