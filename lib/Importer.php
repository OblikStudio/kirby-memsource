<?php

namespace Oblik\Memsource;

use Kirby\Cms\ModelWithContent;
use Oblik\Walker\Walker\Importer as WalkerImporter;

class Importer
{
	public static function importModel(ModelWithContent $model, array $data, string $lang)
	{
		$data = WalkerImporter::walk($model, [
			'options' => option('oblik.memsource.walker'),
			'lang' => kirby()->defaultLanguage()->code(),
			'input' => $data
		]);

		$diff = DiffWalker::walk($model, [
			'lang' => $lang,
			'input' => $data
		]);

		$model->update($data, $lang);

		return $diff;
	}

	public static function import(array $data, string $lang)
	{
		$site = $data['site'] ?? null;
		$pages = $data['pages'] ?? null;
		$files = $data['files'] ?? null;

		if (is_array($site)) {
			$data['site'] = static::importModel(site(), $site, $lang);
		}

		if (is_array($pages)) {
			foreach ($pages as $id => $data) {
				if ($page = page($id)) {
					$data['pages'][$id] = static::importModel($page, $data, $lang);
				}
			}
		}

		if (is_array($files)) {
			foreach ($files as $id => $data) {
				if ($file = site()->file($id)) {
					$data['files'][$id] = static::importModel($file, $data, $lang);
				}
			}
		}

		return $data;
	}
}
