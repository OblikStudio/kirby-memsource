<?php

namespace Oblik\Memsource;

use Oblik\Walker\Serialize\KirbyTags;
use Oblik\Walker\Walker\Exporter;

class ExportWalker extends Exporter
{
	protected static function walkText($text)
	{
		if (is_string($text)) {
			$text = KirbyTags::decode($text);
		}

		return $text;
	}
}
