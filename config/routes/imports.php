<?php

namespace Oblik\Memsource;

use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

return [
	'pattern' => 'memsource/imports',
	'method' => 'GET',
	'action' => function () {
		$page = (int)$this->requestQuery('page');
		$limit = (int)$this->requestQuery('limit');

		$importsDir = option('oblik.memsource.importsDir');
		$entries = Dir::read($importsDir, null, true);
		$collection = (new Collection($entries))->flip();
		$results = $collection->paginate($limit, $page);
		$pagination = $results->pagination();

		$data = [];
		foreach ($results->toArray() as $file) {
			$fileData = F::read($file);
			$data[] = json_decode($fileData, true);
		}

		return [
			'data' => $data,
			'pagination' => [
				'page' => $pagination->page(),
				'limit' => $pagination->limit(),
				'total' => $pagination->total()
			]
		];
	}
];
