<?php

namespace Oblik\Memsource;

use Exception;

return [
	'pattern' => 'memsource/export',
	'method' => 'GET',
	'auth' => false,
	'action' => function () {
		$data = kirby()->request()->data();
		$exporter = new Exporter([
			'lang' => kirby()->defaultLanguage()->code(),
			'options' => option('oblik.memsource.walker')
		]);

		$exportFiles = $data['files'] ?? null;

		if (!empty($data['site'])) {
			if ($exportFiles !== 'only') {
				$exporter->exportSite();
			}

			if ($exportFiles !== 'off') {
				foreach (site()->files() as $file) {
					$exporter->exportFile($file);
				}
			}
		}

		if (!empty($data['pages'])) {
			foreach (explode(',', $data['pages']) as $pageId) {
				if ($page = site()->findPageOrDraft($pageId)) {
					if ($exportFiles !== 'only') {
						$exporter->exportPage($page);
					}

					if ($exportFiles !== 'off') {
						foreach ($page->files() as $file) {
							$exporter->exportFile($file);
						}
					}
				}
			}
		}

		$data = $exporter->toArray();

		if (empty($data)) {
			throw new Exception('Nothing to export', 400);
		}

		return $data;
	}
];
