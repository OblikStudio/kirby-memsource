<?php

require_once(__DIR__ . DS . 'vendor' . DS . 'autoload.php');
require_once(__DIR__ . DS . 'src' . DS . 'Memsource.php');
require_once(__DIR__ . DS . 'src' . DS . 'BlueprintReader.php');
require_once(__DIR__ . DS . 'src' . DS . 'Exporter.php');

if (function_exists('panel')) {
    panel()->routes([
        [
            'pattern' => 'memsource/auth',
            'method' => 'POST',
            'action' => function () {
                $memsource = new Memsource\App;
                $response = $memsource->login(
                    $_POST['ms-username'],
                    $_POST['ms-password']
                );

                $data = json_decode($response->getBody());
                $_SESSION['memsource_token'] = $data->token;

                return $response->getBody();
            }
        ],
        [
            'pattern' => 'memsource/upload',
            'method' => 'GET',
            'action' => function () {
                $exporter = new Memsource\Exporter;

                // return response::json($exporter->exportPage(site()->children()->findByURI('home/testimonials/michael-arrington')));
                return response::json($exporter->export());
            }
        ]
    ]);
}


kirby()->set('widget', 'memsource', __DIR__ . DS . 'widgets' . DS . 'memsource');