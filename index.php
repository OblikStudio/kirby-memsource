<?php

namespace Oblik\Memsource;

use Kirby\Cms\App;

load([
	'Oblik\\Memsource\\DiffWalker' => 'DiffWalker.php',
	'Oblik\\Memsource\\Exporter' => 'Exporter.php',
	'Oblik\\Memsource\\ExportWalker' => 'ExportWalker.php',
	'Oblik\\Memsource\\Importer' => 'Importer.php',
	'Oblik\\Memsource\\ImportWalker' => 'ImportWalker.php',
	'Oblik\\Memsource\\Service' => 'Service.php',
	'Oblik\\Memsource\\Snapshot' => 'Snapshot.php'
], __DIR__ . '/lib');

App::plugin('oblik/memsource', [
	'options' => require 'config/options.php',
	'api' => [
		'routes' => require 'config/api-routes.php'
	],
	'translations' => [
		'en' => require 'config/translations-en.php'
	]
]);
