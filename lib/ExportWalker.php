<?php

namespace Oblik\Memsource;

use Oblik\Walker\Serialize\KirbyTags;
use Oblik\Walker\Walker\Exporter;

class ExportWalker extends Exporter
{
	protected static function walkText(string $text, $context)
	{
		$text = KirbyTags::decode($text);

		return $text;
	}
}
