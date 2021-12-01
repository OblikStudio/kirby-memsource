<?php

namespace Oblik\Memsource;

return [
	'pattern' => 'memsource/picker/projects/(:any)/workflowSteps',
	'method' => 'GET',
	'action' => function ($project) {
		$res = (new Service())->getWorkflowSteps($project);

		$data = [];
		foreach ($res['projectWorkflowSteps'] as $step) {
			$data[] = [
				'id' => $step['id'],
				'text' => $step['name'],
				'image' => true,
				'icon' => [
					'type' => 'funnel',
					'back' => 'white'
				],

				// Used on the front-end to filter jobs by workflow level.
				'workflowLevel' => $step['workflowLevel']
			];
		}

		return [
			'data' => $data,
			'pagination' => []
		];
	}
];
