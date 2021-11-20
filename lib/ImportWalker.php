<?php

namespace Oblik\Memsource;

use Oblik\Walker\Serialize\KirbyTags;
use Oblik\Walker\Walker\Importer;

class ImportWalker extends Importer
{
	protected static function walkText($text)
	{
		if (is_string($text)) {
			$text = KirbyTags::encode($text);
		}

		return $text;
	}
}
