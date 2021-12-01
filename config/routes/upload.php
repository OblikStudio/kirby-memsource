<?php

namespace Oblik\Memsource;

return [
	'pattern' => 'memsource/upload',
	'method' => 'POST',
	'action' => function () {
		$req = kirby()->request()->data();
		$projectId = $req['projectId'];
		$jobName = $req['jobName'];

		$service = new Service();
		$settingsName = Service::IMPORT_SETTINGS['name'];
		$settings = $service->request("importSettings?name={$settingsName}")['content'][0] ?? null;

		if (!$settings) {
			$settings = $service->request('importSettings', [
				'method' => 'POST',
				'headers' => [
					'Content-Type' => 'application/json'
				],
				'data' => json_encode(Service::IMPORT_SETTINGS)
			]);
		} else {
			$settingsData = array_merge([
				'uid' => $settings['uid']
			], Service::IMPORT_SETTINGS);

			$settings = $service->request('importSettings', [
				'method' => 'PUT',
				'headers' => [
					'Content-Type' => 'application/json'
				],
				'data' => json_encode($settingsData)
			]);
		}

		$memsourceHeader = [
			'targetLangs' => $req['targetLangs'],
			'importSettings' => [
				'uid' => $settings['uid']
			]
		];

		return (new Service())->request("projects/$projectId/jobs", [
			'method' => 'POST',
			'data' => json_encode($req['jobData'], JSON_UNESCAPED_UNICODE),
			'headers' => [
				'Memsource' => json_encode($memsourceHeader),
				'Content-Type' => 'application/octet-stream',
				'Content-Disposition' => "filename*=UTF-8''{$jobName}.json"
			]
		]);
	}
];
