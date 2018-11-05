<?php

require_once(__DIR__ . DS . 'vendor' . DS . 'autoload.php');
require_once(__DIR__ . DS . 'src' . DS . 'Memsource.php');

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
                echo "<pre>";
                include __DIR__ . DS . 'src' . DS . 'utils' . DS . 'export-content.php';
                return response::json($site_content);
            }
        ]
    ]);
}

kirby()->set('widget', 'memsource', __DIR__ . DS . 'widgets' . DS . 'memsource');