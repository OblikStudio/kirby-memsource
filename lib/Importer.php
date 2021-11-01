<?php

namespace Oblik\Memsource;

use Kirby\Cms\ModelWithContent;
use Oblik\Walker\Walker\Importer as ImportWalker;
use Oblik\Walker\Walker\Walker as BaseWalker;

/**
 * @todo should compare arrays with `id` accordingly
 */
function compare($arrA, $arrB)
{
	$result = [];
	foreach ($arrA as $key => $a) {
		$b = $arrB[$key] ?? null;

		if (is_array($a) && is_array($b)) {
			$result[$key] = compare($a, $b);
		} else if ($a !== $b) {
			$result[$key] = [
				'$old' => $a,
				'$new' => $b
			];
		}
	}

	return array_filter($result);
}

class Importer
{
	/**
	 * @var BaseWalker
	 */
	public static $baseWalker = BaseWalker::class;

	/**
	 * @var ImportWalker
	 */
	public static $importWalker = ImportWalker::class;

	public $lang;
	public $site;
	public $pages = [];
	public $files = [];

	public function __construct($lang = null)
	{
		$this->lang = $lang;
	}

	public function importModel(ModelWithContent $model, array $data)
	{
		$tl = $model->translation($this->lang);

		if ($tl && $tl->exists()) {
			$lang = $this->lang;
		} else {
			$lang = null;
		}

		$old = static::$baseWalker::walk($model, $lang);
		$new = static::$importWalker::walk($model, $this->lang, $data);

		$model->update($new, $this->lang);

		return compare($old, $new);
	}

	public function toArray()
	{
		return array_filter([
			'site' => $this->site,
			'pages' => $this->pages,
			'files' => $this->files
		]);
	}
}
