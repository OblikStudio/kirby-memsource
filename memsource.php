<?php

require_once(__DIR__ . DS . 'src' . DS . 'BlueprintReader.php');
require_once(__DIR__ . DS . 'src' . DS . 'Exporter.php');
require_once(__DIR__ . DS . 'src' . DS . 'Importer.php');

if (function_exists('panel')) {
    panel()->routes([
        [
            'pattern' => 'memsource/export',
            'method' => 'GET',
            'action' => function () {
                $exporter = new Memsource\Exporter;
                $siteData = $exporter->export();

                return response::json($siteData);
            }
        ],
        [
            'pattern' => 'memsource/import',
            'method' => 'GET',
            'action' => function () {
                $jsonData = file_get_contents(__DIR__ . DS . 'import.json');
                $parsedData = json_decode($jsonData, true);

                $importer = new Memsource\Importer;
                $importer->import($parsedData, 'zh');

                return response::json([
                    'status' => 'success'
                ]);
            }
        ]
    ]);
}

kirby()->set('widget', 'memsource', __DIR__ . DS . 'widgets' . DS . 'memsource');