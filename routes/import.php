<?php

namespace Oblik\Memsource;

use Oblik\Outsource\Importer;

return [
    [
        'pattern' => 'import',
        'method' => 'POST',
        'action' => function () {
            $postData = file_get_contents('php://input');
            $input = json_decode($postData, true);

            if (empty($input['language'])) {
                throw new Exception('Missing language', 400);
            }

            if (empty($input['content'])) {
                throw new Exception('Missing content', 400);
            }

            $importer = new Importer(walkerSettings([
                'language' => $input['language']
            ]));

            return $importer->import($input['content']);
        }
    ]
];
