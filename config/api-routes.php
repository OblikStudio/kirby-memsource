<?php

namespace Oblik\Memsource;

use Exception;
use Kirby\Cms\PagePicker;
use Kirby\Http\Remote;

return [
	[
		'pattern' => 'memsource/import',
		'method' => 'POST',
		'action' => function () {
			$request = kirby()->request()->data();
			$projectId = $request['project'];
			$jobs = $request['jobs'];

			$service = new Service();

			kirby()->impersonate('kirby');

			foreach ($jobs as &$job) {
				$remote = Remote::get(Service::API_URL . '/projects/' . $projectId . '/jobs/' . $job['id'] . '/targetFile', [
					'method' => 'GET',
					'headers' => [
						'Authorization' => 'ApiToken ' . $service->token
					]
				]);

				if ($remote->code() === 200) {
					$content = json_decode($remote->content(), true);

					$importer = new Importer([
						'lang' => $job['targetLang'],
						'options' => option('oblik.memsource.walker')
					]);

					$diffs = $importer->import($content);
					file_put_contents(__DIR__ . '/diffs.json', json_encode($diffs, JSON_UNESCAPED_UNICODE));

					$job['success'] = true;
				} else {
					$job['success'] = false;
				}
			}

			return $jobs;
		}
	],
	[
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
					'targetLangs' => $project['targetLangs'],
					'image' => true,
					'icon' => [
						'type' => 'template',
						'back' => 'white'
					]
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
		'pattern' => 'memsource/picker/projects/(:any)/jobs',
		'method' => 'GET',
		'auth' => false,
		'action' => function ($project) {
			$page = (int)$this->requestQuery('page') - 1;
			$page = $page >= 0 ? $page : 0;

			$service = new Service();
			$response = Remote::get(Service::API_URL . '/projects/' . $project . '/jobs', [
				'method' => 'GET',
				'data' => [
					'pageSize' => 20,
					'pageNumber' => $page,
					'filename' => $this->requestQuery('search')
				],
				'headers' => [
					'Authorization' => 'ApiToken ' . $service->token
				]
			]);

			$responseData = json_decode($response->content(), true);
			$jobs = $responseData['content'] ?? [];

			$data = [];

			foreach ($jobs as $job) {
				$data[] = [
					'id' => $job['uid'],
					'targetLang' => $job['targetLang'],
					'info' => strtoupper($job['targetLang']) . ' (' . $job['status'] . ')',
					'text' => $job['filename'],
					'image' => true,
					'icon' => [
						'type' => 'text',
						'back' => 'white',
						'color' => $job['status'] === 'COMPLETED' ? 'green' : null
					]
				];
			}

			return [
				'data' => $data,
				'pagination' => [
					'page' => $responseData['pageNumber'] + 1,
					'limit' => $responseData['pageSize'],
					'total' => $responseData['totalElements']
				]
			];
		}
	]
];
