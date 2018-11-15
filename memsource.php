<?php

kirby()->set('widget', 'memsource', __DIR__ . DS . 'widgets' . DS . 'memsource');

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
            'method' => 'PUT',
            'action' => function () {
                $postData = file_get_contents('php://input');
                $input = json_decode($postData, true);

                if (!$input) {
                    return response::json([
                        'status' => 'error',
                        'errorDescription' => 'No input data.'
                    ], 400);
                } else if (empty($input['data']) || empty($input['language'])) {
                    return response::json([
                        'status' => 'error',
                        'errorDescription' => 'Missing input data.'
                    ], 400);
                }

                $importer = new Memsource\Importer;
                $importer->import($input['data'], $input['language']);

                return response::json([
                    'status' => 'success'
                ]);
            }
        ]
    ]);
}