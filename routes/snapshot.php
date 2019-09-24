<?php

namespace Oblik\Memsource;

use Oblik\Outsource\Exporter;

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
            $exporter = new Exporter(walkerSettings());
            $models = site()->index()->prepend(site());
            $data = $exporter->export($models);

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
