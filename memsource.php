<?php

require_once(__DIR__ . DS . 'vendor' . DS . 'autoload.php');
require_once(__DIR__ . DS . 'src' . DS . 'Memsource.php');
require_once(__DIR__ . DS . 'src' . DS . 'BlueprintReader.php');
require_once(__DIR__ . DS . 'src' . DS . 'Exporter.php');
require_once(__DIR__ . DS . 'src' . DS . 'Importer.php');

$memsource = new Memsource\App;

if (function_exists('panel')) {
    panel()->routes([
        [
            'pattern' => 'memsource/auth',
            'method' => 'POST',
            'action' => function () {
                $memsource = new Memsource\App;
                $data = $memsource->login(
                    $_POST['ms-username'],
                    $_POST['ms-password']
                );

                return response::json($data);
            }
        ],
        [
            // PUT method is used because GET can't have JSON data in its body
            // and POST automatically gets a `csrf` parameter appended from
            // the Kirby panel, which makes the body invalid JSON.
            'pattern' => 'memsource/store',
            'method' => 'PUT',
            'action' => function () use ($memsource) {
                $data = null;
                $response = ['status' => 'success'];

                $data = json_decode(kirby()->request()->body(), true);
                if (!$data) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Empty or invalid input data.'
                    ];
                }

                try {
                    $memsource->store(function (&$store) use ($data) {
                        $store = array_replace_recursive($store, $data);
                    });
                } catch (\Exception $e) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Could not merge data.'
                    ];
                }

                return response::json($response);
            }
        ],
        [
            'pattern' => 'memsource/upload',
            'method' => 'GET',
            'action' => function () {
                $memsource = new Memsource\App;
                $exporter = new Memsource\Exporter;
                // $siteData = $exporter->export();


                var_dump($memsource->getTargetLanguages());

                // $client = new Memsource\App;
                // $response = $client->createJob('nexo.json', $siteData);

                // return response::json($exporter->exportPage(site()->children()->findByURI('home/testimonials/michael-arrington')));
                return response::json(null);
            }
        ],
        [
            'pattern' => 'memsource/download',
            'method' => 'GET',
            'action' => function () {
                echo "<pre>";

                // $jsonData = file_get_contents(__DIR__ . DS . 'import.json');
                // $parsedData = json_decode($jsonData, true);

                // $importer = new Memsource\Importer;
                // $importer->import($parsedData, 'zh');


                $memsource = new Memsource\App;

                $response = $memsource->login('joroyordanov', 'BCRm6C6uZmjrQ9a');
                return response::json($response);
                // var_dump(json_decode($response->getBody(), true));
                
                return '_';

                // return response::json($response);
            }
        ]
    ]);
}


kirby()->set('widget', 'memsource', __DIR__ . DS . 'widgets' . DS . 'memsource');