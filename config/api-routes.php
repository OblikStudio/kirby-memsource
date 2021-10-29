<?php

namespace Oblik\Memsource;

use Exception;
use Oblik\Walker\Walker\Importer;

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

			$pages = $content['pages'] ?? null;

			if (is_array($pages)) {
				kirby()->impersonate('kirby');

				foreach ($pages as $id => $input) {
					if ($page = page($id)) {
						$data = Importer::walk($page, $language, $input);
						$page->update($data, $language);
					}
				}
			}
		}
	],
	[
		'pattern' => 'memsource/export',
		'method' => 'GET',
		'auth' => false,
		'action' => function () {
			$pages = site()->index();
			$pattern = $_GET['pages'] ?? null;

			if ($pattern) {
				$pages = $pages->filter(function ($page) use ($pattern) {
					return preg_match($pattern, $page->id()) === 1;
				});
			}

			$lang = kirby()->defaultLanguage()->code();
			$exporter = new Exporter($lang);
			$exporter->exportSite();
			$exporter->exportPages($pages);
			$data = $exporter->toArray();

			if (empty($data)) {
				throw new Exception('Nothing to export', 400);
			}

			return $data;
		}
	],
	[
		'pattern' => 'memsource/snapshot',
		'method' => 'GET',
		'action' => function () {
			return array_map(function ($file) {
				return [
					'name' => basename($file, '.json'),
					'date' => filemtime($file)
				];
			}, Snapshot::list());
		}
	],
	[
		'pattern' => 'memsource/snapshot',
		'method' => 'POST',
		'action' => function () {
			// $lang = kirby()->defaultLanguage()->code();
			// $exporter = new Exporter(walkerSettings());
			// $exporter->export(site(), $lang);
			// $data = $exporter->data();

			// return Snapshot::create($_GET['name'], $data);
		}
	],
	[
		'pattern' => 'memsource/snapshot',
		'method' => 'DELETE',
		'action' => function () {
			return Snapshot::remove($_GET['name']);
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
