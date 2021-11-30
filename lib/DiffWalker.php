<?php

namespace Oblik\Memsource;

use Kirby\Cms\Field;
use Kirby\Cms\ModelWithContent;
use Oblik\Walker\Walker\Exporter;

class DiffWalker extends Exporter
{
	public $changes = 0;

	public function walk(ModelWithContent $model, array $context = [])
	{
		$context = array_replace($this->context, $context);

		$exporter = new Exporter(['lang' => $context['lang']]);
		$context['translation'] = $exporter->walk($model);

		// Set the main walked data to always be the default language.
		$context['lang'] = kirby()->defaultLanguage()->code();

		return parent::walk($model, $context);
	}

	protected function subcontext($key, $context)
	{
		$context = parent::subcontext($key, $context);
		$translation = $context['translation'] ?? null;

		if (is_array($translation)) {
			$context['translation'] = $this->findMatchingEntry($key, $translation, $context);
		}

		return $context;
	}

	protected function walkField(Field $field, $context)
	{
		$value = parent::walkField($field, $context);

		if ($value === null) {
			// If `null`, value is probably not translatable, so there's no need
			// to go any further.
			return null;
		}

		$type = $context['blueprint']['type'] ?? null;

		if (in_array($type, ['structure', 'blocks', 'entity', 'editor'])) {
			return $value;
		}

		if ($type === 'link') {
			$value = $value['text'] ?? null;
			$context['input'] = $context['input']['text'] ?? null;
			$context['translation'] = $context['translation']['text'] ?? null;
		}

		$oldValue = $context['translation'] ?? $value;
		$newValue = $context['input'] ?? $oldValue;

		if ($newValue !== $oldValue) {
			$this->changes++;

			return [
				'$new' => $newValue,
				'$old' => $oldValue
			];
		}
	}

	protected function walkText(string $text, $context)
	{
		// Override base walkText() because applying options such as
		// `parseKirbyTags` is not wanted here.
		return $text;
	}
}
