<?php

namespace Oblik\Memsource;

use Exception;
use Kirby\Cms\PagePicker;
use Kirby\Http\Remote;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

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

				$body = json_decode($remote->content(), true);
				$dry = $request['dry'] ?? false;
				$diff = null;
				$error = null;

				if ($remote->code() === 200) {
					try {
						$diff = Importer::import($body, [
							'lang' => lang_map($job['targetLang']),
							'dry' => $dry
						]);
					} catch (\Throwable $t) {
						$error = $t->__toString();
					}
				} else {
					$error = $body;
				}

				$isSuccess = empty($error);
				$fileName = date('Ymd-His') . '-' . $job['id'] . '.json';

				$logData = [
					'jobId' => $job['id'],
					'jobFile' => $job['name'],
					'jobLang' => $job['targetLang'],
					'importFile' => $fileName,
					'importDate' => date(DATE_ISO8601),
					'isSuccess' => $isSuccess,
					'isDry' => $dry
				];

				if ($error !== null) {
					$logData['importError'] = $error;
				}

				$logData = json_encode($logData, JSON_PRETTY_PRINT);
				$importsDir = option('oblik.memsource.importsDir');
				F::write("$importsDir/$fileName", $logData);

				if ($diff !== null) {
					$diffsDir = option('oblik.memsource.diffsDir');
					$diffJson = json_encode($diff, JSON_UNESCAPED_UNICODE);
					F::write("$diffsDir/$fileName", $diffJson);
				}

				// Used later in the History tab to display the job as "new".
				$job['importFile'] = $fileName;
			}

			return $jobs;
		}
	],
	[
		'pattern' => 'memsource/imports/(:any)',
		'method' => 'GET',
		'action' => function ($importFile) {
			$importsDir = option('oblik.memsource.importsDir');
			$importData = F::read("$importsDir/$importFile");

			if (is_string($importData)) {
				$importData = json_decode($importData, true);
			}

			if (is_array($importData)) {
				$diffsDir = option('oblik.memsource.diffsDir');
				$diffData = F::read("$diffsDir/$importFile");

				if (is_string($diffData)) {
					$importData['importDiff'] = json_decode($diffData, true);
				}

				return $importData;
			}
		}
	],
	[
		'pattern' => 'memsource/imports',
		'method' => 'GET',
		'action' => function () {
			$page = (int)$this->requestQuery('page');
			$limit = (int)$this->requestQuery('limit');

			$importsDir = option('oblik.memsource.importsDir');
			$entries = Dir::read($importsDir, null, true);
			$collection = (new Collection($entries))->flip();
			$results = $collection->paginate($limit, $page);
			$pagination = $results->pagination();

			$data = [];
			foreach ($results->toArray() as $file) {
				$fileData = F::read($file);
				$data[] = json_decode($fileData, true);
			}

			return [
				'data' => $data,
				'pagination' => [
					'page' => $pagination->page(),
					'limit' => $pagination->limit(),
					'total' => $pagination->total()
				]
			];
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
		// Project and workflow level are passed as path parameters because the
		// base <k-pages-field> has no option for additional GET parameters.
		'pattern' => 'memsource/picker/projects/(:any)/workflows/(:any)/jobs',
		'method' => 'GET',
		'auth' => false,
		'action' => function ($project, $workflowLevel) {
			$page = (int)$this->requestQuery('page') - 1;
			$page = $page >= 0 ? $page : 0;

			$service = new Service();
			$response = Remote::get(Service::API_URL . '/projects/' . $project . '/jobs', [
				'method' => 'GET',
				'data' => [
					'pageSize' => 20,
					'pageNumber' => $page,
					'filename' => $this->requestQuery('search'),
					'workflowLevel' => $workflowLevel
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
					'info' => $job['status'],
					'name' => $job['filename'],
					'text' => $job['filename'] . ' (' . $job['targetLang'] . ')',
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
	],
	[
		'pattern' => 'memsource/picker/projects/(:any)/workflowSteps',
		'method' => 'GET',
		'auth' => false,
		'action' => function ($project) {
			$service = new Service();
			$response = Remote::get(Service::API_URL . '/projects/' . $project . '/workflowSteps', [
				'method' => 'GET',
				'headers' => [
					'Authorization' => 'ApiToken ' . $service->token
				]
			]);

			$responseData = json_decode($response->content(), true);
			$workflowSteps = $responseData['projectWorkflowSteps'] ?? [];

			$data = [];

			foreach ($workflowSteps as $step) {
				$data[] = [
					'id' => $step['id'],
					'info' => $step['abbreviation'],
					'text' => $step['name'],
					'image' => true,
					'icon' => [
						'type' => 'funnel',
						'back' => 'white'
					],
					'workflowLevel' => $step['workflowLevel']
				];
			}

			return [
				'data' => $data,
				'pagination' => []
			];
		}
	]
];
