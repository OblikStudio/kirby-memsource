<?php

namespace Oblik\Memsource;

use Kirby\Exception\Exception;

return [
	'pattern' => 'memsource/export',
	'method' => 'GET',
	'action' => function () {
		$req = kirby()->request()->data();
		$exportSite = !empty($req['site']);
		$exportPages = explode(',', $req['pages'] ?? '');
		$exportFiles = $req['files'] ?? 'off';

		$exporter = new Exporter([
			'lang' => kirby()->defaultLanguage()->code(),
			'options' => option('oblik.memsource.walker')
		]);

		if ($exportSite) {
			if ($exportFiles !== 'only') {
				$exporter->exportSite();
			}

			if ($exportFiles !== 'off') {
				foreach (site()->files() as $file) {
					$exporter->exportFile($file);
				}
			}
		}

		foreach ($exportPages as $pageId) {
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

		$data = $exporter->toArray();

		if (empty($data)) {
			throw new Exception([
				'key' => 'memsource.exportEmpty'
			]);
		}

		return $data;
	}
];
