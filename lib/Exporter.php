<?php

namespace Oblik\Memsource;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Oblik\Walker\Walker\Exporter as ExportWalker;

class Exporter
{
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
		$data = static::$walker::walk(site(), $this->lang);

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
		$data = static::$walker::walk($page, $this->lang);

		if (!empty($data)) {
			$this->pages[$page->id()] = $data;
		}
	}

	public function exportFile(File $file)
	{
		$data = static::$walker::walk($file, $this->lang);

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
