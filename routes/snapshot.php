<?php

namespace Oblik\Memsource;

use Oblik\Outsource\Walker\Exporter;

return [
    [
        'pattern' => 'snapshot',
        'method' => 'GET',
        'action' => function () {
            return array_map(function ($file) {
                return [
                    'name' => basename($file, '.json'),
                    'date' => filemtime($file)
                ];
            }, Snapshot::list());
        }
    ],
    [
        'pattern' => 'snapshot',
        'method' => 'POST',
        'action' => function () {
            $lang = kirby()->defaultLanguage()->code();
            $exporter = new Exporter(walkerSettings());
            $exporter->export(site(), $lang);
            $data = $exporter->data();

            return Snapshot::create($_GET['name'], $data);
        }
    ],
    [
        'pattern' => 'snapshot',
        'method' => 'DELETE',
        'action' => function () {
            return Snapshot::remove($_GET['name']);
        }
    ]
];
