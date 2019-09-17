<?php

namespace Oblik\Memsource;

use Exception;
use Oblik\Outsource\Importer;

return [
    [
        'pattern' => 'import',
        'method' => 'POST',
        'action' => function () {
            if ($config = $_SERVER['HTTP_MEMSOURCE'] ?? null) {
                $config = json_decode($config, true);
            }

            $language = $config['language'] ?? null;
            $postData = file_get_contents('php://input');
            $content = json_decode($postData, true);

            if (empty($language)) {
                throw new Exception('Missing language', 400);
            }

            if (empty($content)) {
                throw new Exception('Missing content', 400);
            }

            $importer = new Importer(walkerSettings([
                'language' => $language
            ]));

            return $importer->process($content);
        }
    ]
];
