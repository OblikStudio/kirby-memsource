<?php

namespace Oblik\Memsource;

use Kirby\Cms\Pages;
use Oblik\Outsource\Exporter;

return [
    [
        'pattern' => 'export',
        'method' => 'GET',
        'auth' => false,
        'action' => function () {
            $pattern = $_GET['pages'] ?? null;
            $models = new Pages();
            $models->append(site());

            if ($pattern === null || !empty($pattern)) {
                $pages = site()->index()->filter(function ($page) use ($pattern) {
                    return (!$pattern || preg_match($pattern, $page->id()) === 1);
                });

                $models->add($pages);
            }

            $exporter = new Exporter(walkerSettings());
            return $exporter->export($models);
        }
    ]
];
