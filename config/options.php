<?php

return [
	'importsFolder' => kirby()->root('content') . '/_memsource/imports',
	'login' => [
		'username' => getenv('MEMSOURCE_USERNAME'),
		'password' => getenv('MEMSOURCE_PASSWORD')
	]
];
