<?php

namespace Oblik\Memsource;

use Kirby\Cms\App;

load([
	'Oblik\\Memsource\\Service' => 'Service.php',
	'Oblik\\Memsource\\Snapshot' => 'Snapshot.php'
], __DIR__ . '/lib');

function walkerSettings($config = [])
{
	$outsourceConfig = [
		'blueprint' => option('oblik.outsource.blueprint'),
		'fields' => option('oblik.outsource.fields')
	];

	$memsourceConfig = [
		'fields' => option('oblik.memsource.fields')
	];

	return array_replace_recursive(
		$outsourceConfig,
		$memsourceConfig,
		$config
	);
}

App::plugin('oblik/memsource', [
	'options' => [
		'snapshots' => kirby()->root('content') . '/__snapshots',
		'fields' => [
			'files' => [
				'ignore' => true
			],
			'pages' => [
				'ignore' => true
			],
			'text' => [
				'serialize' => [
					'kirbytags' => true
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
	],
	'api' => [
		'routes' => array_merge(
			include 'routes/snapshot.php',
			include 'routes/export.php'
		)
	],
	'translations' => [
		'en' => [
			'username' => 'Username',
			'service' => 'Service',
			'project' => 'Project',
			'projects' => 'Projects',
			'export' => 'Export',
			'import' => 'Import',
			'results' => 'Results',
			'snapshots' => 'Snapshots',
			'snapshot' => 'Snapshot',
			'variables' => 'Variables',
			'strings' => 'Strings',
			'words' => 'Words',
			'characters' => 'Characters',
			'data' => 'Data',
			'jobs' => 'Jobs',
			'memsource.info.session_expired' => 'Your session expired, please log in again',
			'memsource.info.deleted_jobs' => 'Deleted {count} jobs!',
			'memsource.info.invalid_language' => 'Invalid site language',
			'memsource.info.created_jobs' => 'Successfully created {count} jobs!',
			'memsource.info.no_changed' => 'Nothing was changed',
			'memsource.info.error' => 'Error: {message}',
			'memsource.info.changed_values' => 'Changed {count} values in {language}',
			'memsource.info.hidden_jobs' => '{count} hidden jobs',
			'memsource.info.jobs_deletion' => 'Confirm deletion of {count} jobs?',
			'memsource.info.jobs_empty' => 'No jobs found in this project',
			'memsource.label.source_langs' => 'Source language',
			'memsource.label.target_langs' => 'Target languages',
			'memsource.label.filter_jobs' => 'Filter jobs',
			'memsource.label.job' => 'Job Name',
			'memsource.help.snapshot' => 'Compare current site data with a snapshot to export only the differences.',
			'memsource.help.pages' => 'When set, only pages containing the given string will be exported.'
		]
	]
]);
