<?php

namespace Oblik\Memsource;

use Kirby\Cms\Field;
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

	protected function walkField(Field $field, $context)
	{
		$value = parent::walkField($field, $context);
		$note = $context['blueprint']['memsource']['note'] ?? null;
		$notesOption = option('oblik.memsource.walker.contextNote');

		if (is_callable($notesOption) && is_string($value)) {
			$generatedNote = $notesOption($value);

			if (is_string($generatedNote)) {
				$note = implode("\n\n", [$note, $generatedNote]);
			}
		}

		if ($note) {
			return [
				'$value' => $value,
				'$note' => trim($note)
			];
		}

		return $value;
	}
}
