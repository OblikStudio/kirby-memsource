<?php

namespace Oblik\Memsource;

use Oblik\Walker\Serialize\KirbyTags;
use Oblik\Walker\Walker\Importer;

class ImportWalker extends Importer
{
	protected static function walkText(string $text, $context)
	{
		$text = KirbyTags::encode($text);

		return $text;
	}
}
