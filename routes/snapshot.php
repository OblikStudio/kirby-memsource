<?php

namespace Oblik\Memsource;

use Exception;
use Kirby\Toolkit\F;
use Oblik\Outsource\Exporter;

function snapshot($name)
{
    $folder = option('oblik.memsource.snapshots');
    return "$folder/$name.json";
}

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
            }, glob(snapshot('*')));
        }
    ],
    [
        'pattern' => 'snapshot',
        'method' => 'POST',
        'action' => function () {
            $file = snapshot($_GET['name'] ?? null);
            $exporter = new Exporter(walkerSettings());
            $models = site()->index()->prepend(site());
            $data = $exporter->export($models);

            if (!F::exists($file)) {
                return F::write($file, json_encode($data));
            } else {
                throw new Exception('File already exists', 400);
            }
        }
    ],
    [
        'pattern' => 'snapshot',
        'method' => 'DELETE',
        'action' => function () {
            $file = snapshot($_GET['name'] ?? null);
            return F::remove($file);
        }
    ]
];
