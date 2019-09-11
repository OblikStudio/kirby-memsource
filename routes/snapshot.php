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
            $folder = option('oblik.memsource.snapshots');
            $files = glob("$folder/*.json");

            $files = array_map(function ($entry) {
                return [
                    'name' => basename($entry, '.json'),
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

            $folder = option('oblik.memsource.snapshots');
            $filename = $_POST['name'] ?? 'snapshot';
            $filepath = "$folder/$filename.json";

            if (!F::exists($filepath)) {
                return F::write($filepath, json_encode($data));
            } else {
                return false;
            }
        }
    ]
];
