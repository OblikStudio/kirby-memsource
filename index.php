<?php

namespace Oblik\Memsource;

use Kirby\Cms\App;

load([
	'Oblik\\Memsource\\ApiException' => 'ApiException.php',
	'Oblik\\Memsource\\DiffWalker' => 'DiffWalker.php',
	'Oblik\\Memsource\\Exporter' => 'Exporter.php',
	'Oblik\\Memsource\\Importer' => 'Importer.php',
	'Oblik\\Memsource\\Service' => 'Service.php'
], __DIR__ . '/lib');

function lang_map(string $code)
{
	$mappedCode = option('oblik.memsource.langMap')[$code] ?? null;
	return is_string($mappedCode) ? $mappedCode : $code;
}

App::plugin('oblik/memsource', [
	'api' => [
		'routes' => [
			require 'config/routes/export.php',
			require 'config/routes/import.php',
			require 'config/routes/imports-entry.php',
			require 'config/routes/imports.php',
			require 'config/routes/picker-jobs.php',
			require 'config/routes/picker-pages.php',
			require 'config/routes/picker-projects.php',
			require 'config/routes/picker-workflows.php'
		]
	],
	'translations' => [
		'en' => require 'config/translations/en.php'
	],
	'options' => require 'config/options.php'
]);
