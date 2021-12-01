<?php

namespace Oblik\Memsource;

return [
	// Project and workflow level are passed as path parameters because the
	// base <k-pages-field> has no option for additional GET parameters.
	'pattern' => 'memsource/picker/projects/(:any)/workflows/(:any)/jobs',
	'method' => 'GET',
	'action' => function ($project, $workflowLevel) {
		// <k-pages-field> may request page=0 *or* page=1, depending on
		// pagination, but both should return the first page.
		$page = (int)$this->requestQuery('page');
		$page = $page > 0 ? $page : 1;

		$res = (new Service())->getJobs($project, $workflowLevel, [
			'page' => $page,
			'limit' => 15,
			'search' => $this->requestQuery('search')
		]);

		$data = [];
		foreach ($res['content'] as $job) {
			$data[] = [
				'id' => $job['uid'],
				'info' => strtolower($job['status']),
				'name' => $job['filename'],
				'text' => $job['filename'] . ' (' . $job['targetLang'] . ')',
				'image' => true,
				'icon' => [
					'type' => 'text',
					'color' => $job['status'] === 'COMPLETED' ? 'green' : null,
					'back' => 'white'
				]
			];
		}

		return [
			'data' => $data,
			'pagination' => [
				'page' => $res['pageNumber'] + 1, // kirby expects pages to start from 1
				'limit' => $res['pageSize'],
				'total' => $res['totalElements']
			]
		];
	}
];
