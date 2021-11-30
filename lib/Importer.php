<?php

namespace Oblik\Memsource;

use Kirby\Cms\ModelWithContent;
use Oblik\Walker\Walker\Importer as WalkerImporter;

class Importer
{
	public $changes = 0;

	public function import(array $data, array $settings)
	{
		$site = $data['site'] ?? null;
		$pages = $data['pages'] ?? null;
		$files = $data['files'] ?? null;

		if (is_array($site)) {
			$data['site'] = $this->importModel(site(), $site, $settings);
		}

		if (is_array($pages)) {
			foreach ($pages as $id => $pageData) {
				if ($page = site()->findPageOrDraft($id)) {
					$data['pages'][$id] = $this->importModel($page, $pageData, $settings);
				}
			}

			$data['pages'] = array_filter($data['pages']);
		}

		if (is_array($files)) {
			foreach ($files as $id => $fileData) {
				if ($file = site()->file($id)) {
					$data['files'][$id] = $this->importModel($file, $fileData, $settings);
				}
			}

			$data['files'] = array_filter($data['files']);
		}

		$data = array_filter($data);

		return !empty($data) ? $data : null;
	}

	public function importModel(ModelWithContent $model, array $data, array $settings)
	{
		$importData = (new WalkerImporter())->walk($model, [
			'options' => option('oblik.memsource.walker'),
			'lang' => kirby()->defaultLanguage()->code(),
			'input' => $data
		]);

		$diffWalker = new DiffWalker();
		$diff = $diffWalker->walk($model, [
			'lang' => $settings['lang'],
			'input' => $importData
		]);

		$this->changes += $diffWalker->changes;

		if (!($settings['dry'] ?? false)) {
			$model->update($importData, $settings['lang']);
		}

		return $diff;
	}
}
