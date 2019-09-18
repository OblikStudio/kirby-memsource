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
            'snapshot' => 'Snapshot',
            'export' => 'Export',
            'variables' => 'Variables',
            'strings' => 'Strings',
            'words' => 'Words',
            'data' => 'Data',
            'characters' => 'Characters',
            'memsource.label.job' => 'Job Name',
            'memsource.label.target_languages' => 'Target Languages',
            'memsource.help.snapshot' => 'Compare current site data with a snapshot to export only the differences.',
            'memsource.help.pages' => 'When set, only pages containing the given string will be exported.'
        ]
    ]
]);
