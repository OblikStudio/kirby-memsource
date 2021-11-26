<?php

namespace Oblik\Memsource;

use Kirby\Cms\ModelWithContent;
use Oblik\Walker\Walker\Importer as WalkerImporter;

class Importer
{
	public static function import(array $data, array $settings)
	{
		$site = $data['site'] ?? null;
		$pages = $data['pages'] ?? null;
		$files = $data['files'] ?? null;

		if (is_array($site)) {
			$data['site'] = static::importModel(site(), $site, $settings);
		}

		if (is_array($pages)) {
			foreach ($pages as $id => $pageData) {
				if ($page = page($id)) {
					$data['pages'][$id] = static::importModel($page, $pageData, $settings);
				}
			}
		}

		if (is_array($files)) {
			foreach ($files as $id => $fileData) {
				if ($file = site()->file($id)) {
					$data['files'][$id] = static::importModel($file, $fileData, $settings);
				}
			}
		}

		return $data;
	}

	public static function importModel(ModelWithContent $model, array $data, array $settings)
	{
		$importData = WalkerImporter::walk($model, [
			'options' => option('oblik.memsource.walker'),
			'lang' => kirby()->defaultLanguage()->code(),
			'input' => $data
		]);

		$diff = DiffWalker::walk($model, [
			'lang' => $settings['lang'],
			'input' => $importData
		]);

		if (!($settings['dry'] ?? false)) {
			$model->update($importData, $settings['lang']);
		}

		return $diff;
	}
}
