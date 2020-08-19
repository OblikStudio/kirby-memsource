<?php

namespace Oblik\Memsource;

use Exception;
use Oblik\Outsource\Util\Diff;
use Oblik\Outsource\Walker\Exporter;
use Oblik\Outsource\Walker\Importer;

return [
    [
        'pattern' => 'memsource/login',
        'method' => 'POST',
        'action' => function () {
            return (new Service)->login();
        }
    ],
    [
        'pattern' => 'memsource/upload/(:any)/(:any)',
        'method' => 'POST',
        'action' => function ($project, $filename) {
            return (new Service)->upload($project, $filename);
        }
    ],
    [
        'pattern' => 'memsource/import',
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

            $importer = new Importer(walkerSettings());
            return $importer->import($content, $language);
        }
    ],
    [
        'pattern' => 'memsource/export',
        'method' => 'GET',
        'action' => function () {
            $pattern = $_GET['pages'] ?? null;
            $snapshot = $_GET['snapshot'] ?? null;
            $lang = kirby()->defaultLanguage()->code();

            $exporter = new Exporter(walkerSettings());
            $pages = site()->index();

            if ($pattern) {
                $pages = $pages->filter(function ($page) use ($pattern) {
                    return empty($pattern) || preg_match($pattern, $page->id()) === 1;
                });
            }

            $exporter->export(site(), $lang, false);
            $exporter->export($pages, $lang, false);
            $exporter->exportVariables($lang);

            $data = $exporter->data();

            if ($snapshot) {
                $snapshotData = Snapshot::read($snapshot);
                $data = Diff::process($data, $snapshotData);
            }

            if ($data === null) {
                throw new Exception('Nothing to export', 400);
            }

            return $data;
        }
    ],
    [
        'pattern' => 'memsource/(:all)',
        'method' => 'GET',
        'action' => function ($resource) {
            return (new Service)->get($resource);
        }
    ]
];
