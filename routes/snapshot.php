<?php

namespace Oblik\Memsource;

use Kirby\Toolkit\F;
use Oblik\Outsource\Exporter;

return [
    [
        'pattern' => 'snapshot',
        'method' => 'GET',
        'auth' => false,
        'action' => function () {
            $folder = realpath(__DIR__ . '/../snapshots');
            $files = glob($folder . '/*.json');

            $files = array_map(function ($entry) {
                return [
                    'filename' => basename($entry),
                    'date' => filemtime($entry)
                ];
            }, $files);

            return $files;
        }
    ],
    [
        'pattern' => 'snapshot',
        'method' => 'POST',
        'auth' => false,
        'action' => function () {
            $exporter = new Exporter(walkerSettings());
            $models = site()->index()->prepend(site());
            $data = $exporter->export($models);

            $filename = $_POST['name'] ?? 'snapshot';
            $filepath = __DIR__ . "/snapshots/$filename.json";

            if (!F::exists($filepath)) {
                return F::write($filepath, json_encode($data));
            } else {
                return false;
            }
        }
    ]
];
