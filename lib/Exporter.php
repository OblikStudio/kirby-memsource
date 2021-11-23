<?php

namespace Oblik\Memsource;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;

class Exporter
{
	/**
	 * @var ExportWalker
	 */
	public static $walker = ExportWalker::class;

	public $lang;
	public $site;
	public $pages = [];
	public $files = [];

	public function __construct($lang = null)
	{
		$this->lang = $lang;
	}

	public function exportSite()
	{
		$data = static::$walker::walk(site(), [
			'lang' => $this->lang
		]);

		if (!empty($data)) {
			$this->site = $data;
		}
	}

	public function exportPages(Pages $pages)
	{
		foreach ($pages as $page) {
			$this->exportPage($page);
		}
	}

	public function exportPage(Page $page)
	{
		$data = static::$walker::walk($page, [
			'lang' => $this->lang
		]);

		if (!empty($data)) {
			$this->pages[$page->id()] = $data;
		}
	}

	public function exportFile(File $file)
	{
		$data = static::$walker::walk($file, [
			'lang' => $this->lang
		]);

		if (!empty($data)) {
			$this->files[$file->id()] = $data;
		}
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
