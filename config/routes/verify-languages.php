<?php

namespace Oblik\Memsource;

return [
	'pattern' => 'memsource/verify-languages',
	'method' => 'GET',
	'action' => function () {
		$langMap = option('oblik.memsource.langMap');
		$targetLangs = kirby()->request()->data()['targetLangs'];
		$targetLangsArray = array_filter(explode(',', $targetLangs));
		$mappedTargetLangs = array_map(function ($code) use ($langMap) {
			return $langMap[$code] ?? $code;
		}, $targetLangsArray);

		$missingSiteLangs = [];
		foreach ($mappedTargetLangs as $code) {
			if (!kirby()->language($code)) {
				$missingSiteLangs[] = $code;
			}
		}

		$missingProjectLangs = [];
		foreach (kirby()->languages() as $lang) {
			if ($lang->isDefault()) {
				continue;
			}

			$code = $lang->code();

			if (array_search($code, $mappedTargetLangs) === false) {
				$missingProjectLangs[] = $code;
			}
		}

		$validMappedTargetLangs = array_diff(
			$mappedTargetLangs,
			$missingSiteLangs,
			$missingProjectLangs
		);

		$validTargetLangs = [];
		foreach ($targetLangsArray as $code) {
			$mappedCode = $langMap[$code] ?? $code;

			if (array_search($mappedCode, $validMappedTargetLangs) !== false) {
				$validTargetLangs[] = $code;
			}
		}

		return [
			'validTargetLangs' => $validTargetLangs,
			'missingSiteLangs' => $missingSiteLangs,
			'missingProjectLangs' => $missingProjectLangs
		];
	}
];
