<?php

namespace Oblik\Memsource;

use Exception;
use Oblik\Outsource\Diff;
use Oblik\Outsource\Exporter;

return [
    [
        'pattern' => 'export',
        'method' => 'GET',
        'auth' => false,
        'action' => function () {
            $snapshot = $_GET['snapshot'] ?? null;
            $pattern = $_GET['pages'] ?? null;

            $models = [site()];

            if ($pattern === null || !empty($pattern)) {
                $pages = site()->index()->filter(function ($page) use ($pattern) {
                    return (!$pattern || preg_match($pattern, $page->id()) === 1);
                });

                $models = array_merge($models, $pages->values());
            }

            $exporter = new Exporter(walkerSettings());
            $exportData = $exporter->export($models);

            if ($snapshot) {
                $snapshotData = Snapshot::read($snapshot);
                $exportData = Diff::process($exportData, $snapshotData);
            }

            if ($exportData === null) {
                throw new Exception('Nothing to export', 400);
            }

            return $exportData;
        }
    ]
];