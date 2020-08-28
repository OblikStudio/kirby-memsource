<?php

namespace Oblik\Memsource;

use Exception;
use Kirby\Cms\Structure;
use Oblik\Walker\Util\Diff;
use Oblik\Walker\Walker\Exporter;
use Oblik\Walker\Walker\Importer;

class MemsourceExporter extends Exporter
{
	protected function fieldPredicate($field, $settings, $input)
	{
		$pass = parent::fieldPredicate($field, $settings, $input);
		$translate = $settings['translate'] ?? true;

		return ($pass && $translate);
	}

	protected function fieldHandler($field, $settings, $input)
	{
		$data = parent::fieldHandler($field, $settings, $input);

		if (empty($data)) {
			$data = null;
		}

		return $data;
	}

	protected function structureHandler(Structure $structure, array $blueprint, $input, $sync)
	{
		$data = parent::structureHandler($structure, $blueprint, $input, $sync);

		// The data is not filtered with `array_filter()` by default because if
		// empty entries are removed, all non-empty entries will be located at a
		// different index. However, if *all* entries are empty, return null.
		if (is_array($data) && count(array_filter($data)) === 0) {
			$data = null;
		}

		return $data;
	}
}

return [
	[
		'pattern' => 'memsource/login',
		'method' => 'POST',
		'action' => function () {
			return (new Service)->login();
		}
	],
	[
		'pattern' => 'memsource/upload/(:any)/(:any)',
		'method' => 'POST',
		'action' => function ($project, $filename) {
			return (new Service)->upload($project, $filename);
		}
	],
	[
		'pattern' => 'memsource/import',
		'method' => 'POST',
		'action' => function () {
			if ($config = $_SERVER['HTTP_MEMSOURCE'] ?? null) {
				$config = json_decode($config, true);
			}

			$language = $config['language'] ?? null;
			$postData = file_get_contents('php://input');
			$content = json_decode($postData, true);

			if (empty($language)) {
				throw new Exception('Missing language', 400);
			}

			if (empty($content)) {
				throw new Exception('Missing content', 400);
			}

			$importer = new Importer(walkerSettings());
			return $importer->import($content, $language);
		}
	],
	[
		'pattern' => 'memsource/export',
		'method' => 'GET',
		'action' => function () {
			$pattern = $_GET['pages'] ?? null;
			$snapshot = $_GET['snapshot'] ?? null;
			$lang = kirby()->defaultLanguage()->code();

			$exporter = new MemsourceExporter(walkerSettings());
			$pages = site()->index();

			if ($pattern) {
				$pages = $pages->filter(function ($page) use ($pattern) {
					return empty($pattern) || preg_match($pattern, $page->id()) === 1;
				});
			}

			$exporter->export(site(), $lang, false);
			$exporter->export($pages, $lang, false);
			$exporter->exportVariables($lang);

			$data = $exporter->data();

			if ($snapshot) {
				$snapshotData = Snapshot::read($snapshot);
				$data = Diff::process($data, $snapshotData);
			}

			if ($data === null) {
				throw new Exception('Nothing to export', 400);
			}

			return $data;
		}
	],
	[
		'pattern' => 'memsource/(:all)',
		'method' => 'GET',
		'action' => function ($resource) {
			return (new Service)->get($resource);
		}
	]
];
