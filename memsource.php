<?php

require_once(__DIR__ . DS . 'vendor' . DS . 'autoload.php');
require_once(__DIR__ . DS . 'src' . DS . 'Memsource.php');
require_once(__DIR__ . DS . 'src' . DS . 'BlueprintReader.php');
require_once(__DIR__ . DS . 'src' . DS . 'Exporter.php');
require_once(__DIR__ . DS . 'src' . DS . 'Importer.php');

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
            'pattern' => 'memsource/upload',
            'method' => 'GET',
            'action' => function () {
                $exporter = new Memsource\Exporter;
                $siteData = $exporter->export();

                // $client = new Memsource\App;
                // $response = $client->createJob('nexo.json', $siteData);

                // return response::json($exporter->exportPage(site()->children()->findByURI('home/testimonials/michael-arrington')));
                return response::json($siteData);
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