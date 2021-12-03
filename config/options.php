<?php

return [
	'cache' => true,
	'importsDir' => kirby()->root('content') . '/_memsource/imports',
	'diffsDir' => kirby()->root('content') . '/_memsource/diffs',
	'langMap' => [],
	'login' => [
		'username' => getenv('MEMSOURCE_USERNAME'),
		'password' => getenv('MEMSOURCE_PASSWORD')
	],
	'importSettings' => [
		'json' => [
			'htmlSubFilter' => true,
			'tagRegexp' => '\{\{[^}]+\}\}',
			'excludeKeyRegexp' => '.*\/id$'
		]
	]
];
