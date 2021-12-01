<?php

namespace Oblik\Memsource;

use Kirby\Http\Remote;

return [
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
];
