<?php

namespace Oblik\Memsource;

use Kirby\Cms\ModelWithContent;

class Importer
{
	public $lang;

	/**
	 * Array holding all models that have been successfully walked over to later
	 * be updated all at once.
	 */
	protected $queue = [];

	/**
	 * @var ImportWalker
	 */
	protected $importWalker;

	/**
	 * @var DiffWalker
	 */
	protected $diffWalker;

	public function __construct(string $lang)
	{
		$this->lang = $lang;

		$this->importWalker = new ImportWalker([
			'options' => option('oblik.memsource.walker')
		]);

		$this->diffWalker = new DiffWalker([
			'lang' => $this->lang
		]);
	}

	public function import(array $input)
	{
		$diff = [];

		if (is_array($site = $input['site'] ?? null)) {
			$diff['site'] = $this->importModel(site(), $site);
		}

		if (is_array($pages = $input['pages'] ?? null)) {
			$diff['pages'] = [];

			foreach ($pages as $id => $pageInput) {
				if ($page = site()->findPageOrDraft($id)) {
					$diff['pages'][$id] = $this->importModel($page, $pageInput);
				}
			}
		}

		if (is_array($files = $input['files'] ?? null)) {
			$diff['files'] = [];

			foreach ($files as $id => $fileInput) {
				if ($file = site()->file($id)) {
					$diff['files'][$id] = $this->importModel($file, $fileInput);
				}
			}
		}

		return !empty($diff) ? $diff : null;
	}

	public function importModel(ModelWithContent $model, array $input)
	{
		$content = $this->importWalker->walk($model, [
			'input' => $input
		]);

		$diff = $this->diffWalker->walk($model, [
			'input' => $content
		]);

		$this->queue[] = [
			'model' => $model,
			'content' => $content
		];

		return $diff;
	}

	public function update()
	{
		foreach ($this->queue as $entry) {
			$entry['model']->update($entry['content'], $this->lang);
		}

		$this->queue = [];
	}

	public function getChanges()
	{
		return $this->diffWalker->changes;
	}
}
