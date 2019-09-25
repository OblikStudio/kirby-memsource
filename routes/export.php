<?php

namespace Oblik\Memsource;

use Exception;
use Oblik\Outsource\Diff;
use Oblik\Outsource\Exporter;

class MemsourceExporter extends Exporter {
    public function fieldPredicate($field, $input)
    {
        $isTranslatable = ($this->blueprint('translate') ?? true);
        $isNotIgnored = parent::fieldPredicate($field, $input);
        return $isTranslatable && $isNotIgnored;
    }
}

return [
    [
        'pattern' => 'export',
        'method' => 'GET',
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

            $exporter = new MemsourceExporter(walkerSettings());
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