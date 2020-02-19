<?php

namespace Oblik\Memsource;

use Exception;
use Oblik\Outsource\Util\Diff;
use Oblik\Outsource\Walker\Exporter;

return [
    [
        'pattern' => 'export',
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
    ]
];