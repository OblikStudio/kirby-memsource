<?php

return [
	'cache' => true,
	'importsDir' => kirby()->root('content') . '/_memsource/imports',
	'diffsDir' => kirby()->root('content') . '/_memsource/diffs',
	'login' => [
		'username' => getenv('MEMSOURCE_USERNAME'),
		'password' => getenv('MEMSOURCE_PASSWORD')
	]
];
