<?php

namespace Oblik\Memsource;

use Kirby;
use Oblik\Outsource\Variables;

load([
    'Oblik\\Memsource\\Snapshot' => 'Snapshot.php'
], __DIR__ . '/lib');

function walkerSettings($data = [])
{
    return array_replace_recursive([
        'language' => kirby()->defaultLanguage()->code(),
        'variables' => Variables::class,
        'blueprint' => option('oblik.outsource.blueprint'),
        'fields' => array_replace_recursive(
            option('oblik.outsource.fields'),
            option('oblik.memsource.fields')
        )
    ], $data);
}

Kirby::plugin('oblik/memsource', [
    'options' => [
        'snapshots' => kirby()->root('content') . '/__snapshots',
        'fields' => [
            'files' => [
                'ignore' => true
            ],
            'pages' => [
                'ignore' => true
            ],
            'link' => [
                'serialize' => [
                    'yaml' => true
                ],
                'export' => [
                    'filter' => [
                        'keys' => ['text']
                    ]
                ]
            ],
            'json' => [
                'serialize' => [
                    'json' => true
                ]
            ]
        ]
    ],
    'api' => [
        'routes' => array_merge(
            include 'routes/export.php',
            include 'routes/import.php',
            include 'routes/snapshot.php'
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
            'memsource.info.invalid_settings' => 'Invalid import settings',
            'memsource.info.using_settings' => 'Using settings {name} ({date})',
            'memsource.info.created_settings' => 'Created import settings {name}',
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
