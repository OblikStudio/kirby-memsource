<?php

use Kirby\Cms\Pages;
use Oblik\Outsource\Exporter;

Kirby::plugin('oblik/memsource', [
    'options' => [
        'outsource' => [
            'fields' => [
                'files' => [
                    'ignore' => true
                ],
                'pages' => [
                    'ignore' => true
                ],
                'link' => [
                    'serialize' => [
                        'yaml' => true
                    ],
                    'export' => [
                        'filter' => [
                            'keys' => ['text']
                        ]
                    ]
                ]
            ]
        ]
    ],
    'api' => [
        'routes' => [
            [
                'pattern' => 'export',
                'method' => 'GET',
                'auth' => false,
                'action' => function () {
                    $pattern = $_GET['pages'] ?? null;
                    $lang = kirby()->defaultLanguage()->code();
 
                    $models = new Pages();
                    $models->append(site());

                    if ($pattern === null || !empty($pattern)) {
                        $pages = site()->index()->filter(function ($page) use ($pattern) {
                            return (!$pattern || preg_match($pattern, $page->id()) === 1);
                        });

                        $models->add($pages);
                    }

                    $exporter = new Exporter([
                        'language' => $lang,
                        'variables' => option('oblik.outsource.variables'),
                        'blueprint' => option('oblik.outsource.blueprint'),
                        'fields' => array_replace_recursive(
                            option('oblik.outsource.fields'),
                            option('oblik.memsource.outsource.fields')
                        )
                    ]);
    
                    return $exporter->export($models);
                }
            ],
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
    
                    $importer = new Importer($input['language']);
                    return $importer->import($input['content']);
                }
            ]
        ]
    ]
]);
