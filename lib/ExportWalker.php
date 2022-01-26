<?php

namespace Oblik\Memsource;

use Oblik\Walker\Walker\Exporter;

class ExportWalker extends Exporter
{
	protected function walkText(string $text, $context)
	{
		$text = parent::walkText($text, $context);

		if (option('oblik.memsource.walker.removeBrTags')) {
			$text = preg_replace('/<br[^>]*>/', '', $text);
		}

		return $text;
	}
}
