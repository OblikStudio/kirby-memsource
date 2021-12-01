<?php

namespace Oblik\Memsource;

use Kirby\Toolkit\F;

return [
	'pattern' => 'memsource/import',
	'method' => 'POST',
	'action' => function () {
		$request = kirby()->request()->data();
		$projectId = $request['projectId'];
		$jobId = $request['jobId'];
		$isDry = $request['dry'] ?? false;

		$diff = null;
		$error = null;
		$changes = null;

		try {
			$service = new Service();
			$jobData = $service->request('projects/' . $projectId . '/jobs/' . $jobId);
			$body = $service->request('projects/' . $projectId . '/jobs/' . $jobId . '/targetFile');

			$importer = new Importer(lang_map($jobData['targetLang']));
			$diff = $importer->import($body);
			$changes = $importer->getChanges();

			if (!$isDry) {
				kirby()->impersonate('kirby');
				$importer->update();
			}
		} catch (\Throwable $throwable) {
			$error = $throwable->__toString();
		}

		$isSuccess = empty($error);
		$fileName = date('Ymd-His') . '-' . $jobId . '.json';

		$logData = [
			'jobId' => $jobId,
			'jobFile' => $jobData['filename'] ?? null,
			'jobLang' => $jobData['targetLang'] ?? null,
			'importFile' => $fileName,
			'importDate' => date(DATE_ISO8601),
			'importChanges' => $changes,
			'importError' => $error,
			'isSuccess' => $isSuccess,
			'isDry' => $isDry
		];

		$logData = json_encode($logData, JSON_PRETTY_PRINT);
		$importsDir = option('oblik.memsource.importsDir');
		F::write("$importsDir/$fileName", $logData);

		if ($diff !== null) {
			$diffsDir = option('oblik.memsource.diffsDir');
			$diffJson = json_encode($diff, JSON_UNESCAPED_UNICODE);
			F::write("$diffsDir/$fileName", $diffJson);
		}

		if (!$isSuccess) {
			if (!empty($throwable)) {
				throw $throwable;
			} else {
				return false;
			}
		}

		return true;
	}
];
