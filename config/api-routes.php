<?php

namespace Oblik\Memsource;

use Exception;
use Kirby\Cms\PagePicker;
use Kirby\Http\Remote;

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

			kirby()->impersonate('kirby');

			$importer = new Importer([
				'lang' => $config['language'],
				'options' => option('oblik.memsource.walker')
			]);

			$importer->import($content);
		}
	],
	[
		'pattern' => 'memsource/export',
		'method' => 'GET',
		'auth' => false,
		'action' => function () {
			$data = kirby()->request()->data();
			$exporter = new Exporter([
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
		'pattern' => 'memsource/pages',
		'method' => 'GET',
		'action' => function () {
			return (new PagePicker([
				'page' => $this->requestQuery('page'),
				'parent' => $this->requestQuery('parent'),
				'search' => $this->requestQuery('search')
			]))->toArray();
		}
	],
	[
		'pattern' => 'memsource/picker/projects',
		'method' => 'GET',
		'auth' => false,
		'action' => function () {
			// <k-pages-field> may request page=0 *or* page=1, depending on
			// pagination, but both should return the first page.
			$page = (int)$this->requestQuery('page') - 1;
			$page = $page >= 0 ? $page : 0;

			$service = new Service();
			$response = Remote::get(Service::API_URL . '/projects', [
				'method' => 'GET',
				'data' => [
					'pageSize' => 20,
					'pageNumber' => $page,
					'name' => $this->requestQuery('search')
				],
				'headers' => [
					'Authorization' => 'ApiToken ' . $service->token
				]
			]);

			$responseData = json_decode($response->content(), true);
			$projects = $responseData['content'] ?? [];

			$data = [];

			foreach ($projects as $project) {
				$data[] = [
					'id' => $project['uid'],
					'info' => $project['internalId'],
					'text' => $project['name'],
					'sourceLang' => $project['sourceLang'],
					'targetLangs' => $project['targetLangs']
				];
			}

			return [
				'data' => $data,
				'pagination' => [
					// Memsource returns the page index (starting from 0), while
					// Kirby expects pages to start from 1.
					'page' => ($responseData['pageNumber'] ?? 0) + 1,

					'limit' => $responseData['pageSize'] ?? 50,
					'total' => $responseData['totalElements'] ?? 1
				]
			];
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
