<?php

namespace Oblik\Memsource;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Oblik\Walker\Walker\Exporter as WalkerExporter;

class Exporter
{
	public $context;
	public $site;
	public $pages = [];
	public $files = [];

	public function __construct(array $context = [])
	{
		$this->context = $context;
	}

	public function exportSite()
	{
		$data = (new WalkerExporter())->walk(site(), $this->context);

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
		$data = (new WalkerExporter())->walk($page, $this->context);

		if (!empty($data)) {
			$this->pages[$page->id()] = $data;
		}
	}

	public function exportFile(File $file)
	{
		$data = (new WalkerExporter())->walk($file, $this->context);

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
