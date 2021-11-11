<?php

namespace Oblik\Memsource;

use Kirby\Cms\ModelWithContent;
use Oblik\Walker\Walker\Importer as Walker;

class Importer
{
	/**
	 * @var Walker
	 */
	public static $walker = walker::class;

	public $lang;

	public function __construct($lang = null)
	{
		$this->lang = $lang;
	}

	public function importModel(ModelWithContent $model, array $data)
	{
		$data = static::$walker::walk($model, $this->lang, $data);
		return $model->update($data, $this->lang);
	}

	public function import(array $data)
	{
		$site = $data['site'] ?? null;
		$pages = $data['pages'] ?? null;
		$files = $data['files'] ?? null;

		if (is_array($site)) {
			$this->importModel(site(), $site);
		}

		if (is_array($pages)) {
			foreach ($pages as $id => $data) {
				if ($page = page($id)) {
					$this->importModel($page, $data);
				}
			}
		}

		if (is_array($files)) {
			foreach ($files as $id => $data) {
				if ($file = site()->file($id)) {
					$this->importModel($file, $data);
				}
			}
		}
	}
}
