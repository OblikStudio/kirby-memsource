<?php

namespace Oblik\Memsource;

use Kirby\Toolkit\F;

return [
	'pattern' => 'memsource/imports/(:any)',
	'method' => 'GET',
	'action' => function ($importFile) {
		$importsDir = option('oblik.memsource.importsDir');
		$importData = F::read("$importsDir/$importFile");

		if (is_string($importData)) {
			$importData = json_decode($importData, true);
		}

		if (is_array($importData)) {
			$diffsDir = option('oblik.memsource.diffsDir');
			$diffData = F::read("$diffsDir/$importFile");

			if (is_string($diffData)) {
				$importData['importDiff'] = json_decode($diffData, true);
			}

			return $importData;
		}
	}
];
