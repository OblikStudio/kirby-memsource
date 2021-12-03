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
		$name = Service::IMPORT_SETTINGS_NAME;
		$entry = $service->request("importSettings?name={$name}")['content'][0] ?? null;

		if ($entry) {
			$settings = $service->request('importSettings', [
				'method' => 'PUT',
				'headers' => [
					'Content-Type' => 'application/json'
				],
				'data' => json_encode([
					'uid' => $entry['uid'],
					'name' => Service::IMPORT_SETTINGS_NAME,
					'fileImportSettings' => option('oblik.memsource.importSettings')
				])
			]);
		} else {
			$settings = $service->request('importSettings', [
				'method' => 'POST',
				'headers' => [
					'Content-Type' => 'application/json'
				],
				'data' => json_encode([
					'name' => Service::IMPORT_SETTINGS_NAME,
					'fileImportSettings' => option('oblik.memsource.importSettings')
				])
			]);
		}

		$memsourceHeader = [
			'targetLangs' => $req['targetLangs'],
			'importSettings' => [
				'uid' => $settings['uid']
			]
		];

		return $service->request("projects/$projectId/jobs", [
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
