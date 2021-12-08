<?php

namespace Oblik\Memsource;

return [
	'pattern' => 'memsource/upload',
	'method' => 'POST',
	'action' => function () {
		$service = new Service();

		if ($entry = $service->getImportSettings()) {
			$settings = $service->updateImportSettings($entry['uid']);
		} else {
			$settings = $service->createImportSettings();
		}

		$req = kirby()->request()->data();
		return $service->postJobs([
			'projectId' => $req['projectId'],
			'settingsId' => $settings['uid'],
			'targetLangs' => $req['targetLangs'],
			'jobName' => $req['jobName'],
			'jobData' => $req['jobData']
		]);
	}
];
