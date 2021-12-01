<?php

namespace Oblik\Memsource;

return [
	'pattern' => 'memsource/picker/projects',
	'method' => 'GET',
	'action' => function () {
		$res = (new Service())->getProjects([
			'page' => $this->requestQuery('page'),
			'limit' => 15,
			'search' => $this->requestQuery('search')
		]);

		foreach ($res['content'] as $project) {
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
				// Incremented with 1 because Memsource returns the page index
				// (starting from 0), while Kirby expects pages to start from 1.
				'page' => $res['pageNumber'] + 1,
				'limit' => $res['pageSize'],
				'total' => $res['totalElements']
			]
		];
	}
];
