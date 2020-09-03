<?php

return [
	'snapshots' => kirby()->root('content') . '/_snapshots',
	'fields' => [
		'date' => [
			'ignore' => true
		],
		'files' => [
			'ignore' => true
		],
		'pages' => [
			'ignore' => true
		],
		'toggle' => [
			'ignore' => true
		],
		'number' => [
			'ignore' => true
		],
		'url' => [
			'ignore' => true
		],
		'text' => [
			'serialize' => [
				'kirbytags' => [
					'tags' => ['text']
				]
			]
		],
		'textarea' => [
			'serialize' => [
				'kirbytags' => [
					'tags' => ['text']
				],
				'markdown' => true
			]
		],
		'link' => [
			'export' => [
				'filter' => [
					'keys' => ['text']
				]
			]
		]
	]
];
