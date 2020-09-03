<?php

namespace Oblik\Memsource;

use Kirby\Cms\App;

load([
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
