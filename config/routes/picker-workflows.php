<?php

namespace Oblik\Memsource;

use Kirby\Http\Remote;

return [
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
];
