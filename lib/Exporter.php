<?php

namespace Oblik\Memsource;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Oblik\Walker\Walker\Exporter as WalkerExporter;

class Exporter
{
	public $site;
	public $pages = [];
	public $files = [];

	/**
	 * @var WalkerExporter
	 */
	public $walker;

	public function __construct(array $context = [])
	{
		$this->walker = new WalkerExporter($context);
	}

	public function exportSite()
	{
		$data = $this->walker->walk(site());

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
		$data = $this->walker->walk($page);

		if (!empty($data)) {
			$this->pages[$page->id()] = $data;
		}
	}

	public function exportFile(File $file)
	{
		$data = $this->walker->walk($file);

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
