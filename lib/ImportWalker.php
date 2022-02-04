<?php

namespace Oblik\Memsource;

use Kirby\Cms\Field;
use Oblik\Walker\Walker\Importer;

class ImportWalker extends Importer
{
	protected function walkField(Field $field, $context)
	{
		// "Unwraps" the value from a field that has had a context note added by
		// the ExportWalker.
		$nestedValue = $context['input']['$value'] ?? null;

		if ($nestedValue) {
			$context['input'] = $nestedValue;
		}

		return parent::walkField($field, $context);
	}
}
