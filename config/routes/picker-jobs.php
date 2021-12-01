<?php

namespace Oblik\Memsource;

use Kirby\Http\Remote;

return [
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
];
