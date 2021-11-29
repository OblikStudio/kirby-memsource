<?php

namespace Oblik\Memsource;

use Kirby\Cms\App;

load([
	'Oblik\\Memsource\\ApiException' => 'ApiException.php',
	'Oblik\\Memsource\\DiffWalker' => 'DiffWalker.php',
	'Oblik\\Memsource\\Exporter' => 'Exporter.php',
	'Oblik\\Memsource\\Importer' => 'Importer.php',
	'Oblik\\Memsource\\Service' => 'Service.php',
	'Oblik\\Memsource\\Snapshot' => 'Snapshot.php'
], __DIR__ . '/lib');

function lang_map(string $code)
{
	$mappedCode = option('oblik.memsource.langMap')[$code] ?? null;
	return is_string($mappedCode) ? $mappedCode : $code;
}

App::plugin('oblik/memsource', [
	'options' => require 'config/options.php',
	'api' => [
		'routes' => require 'config/api-routes.php'
	],
	'translations' => [
		'en' => require 'config/translations-en.php'
	]
]);
